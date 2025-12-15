<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrderApprovalController extends Controller
{
    /**
     * Obtener pedidos pendientes de aprobación
     */
    public function pendingOrders(Request $request): JsonResponse
    {
        try {
            $orders = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'orderProducts' => function($query) {
                    $query->where('estado', 'pendiente');
                }
            ])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_creacion', 'desc')
            ->paginate($request->get('per_page', 15));

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos pendientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un pedido para aprobación
     */
    public function show($id): JsonResponse
    {
        try {
            $order = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'destinations.destinationProducts.orderProduct.product',
                'approver'
            ])->findOrFail($id);

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pedido no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Aprobar un producto específico del pedido
     */
    public function approveProduct(Request $request, $orderId, $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderProduct = OrderProduct::where('order_id', $orderId)
                ->where('order_product_id', $productId)
                ->where('status', 'pendiente')
                ->firstOrFail();

            $orderProduct->update([
                'estado' => 'aprobado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'observaciones' => $request->observations,
            ]);

            // Verificar si todos los productos están aprobados
            $pendingProducts = OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->count();

            $enviosCreated = [];
            $integrationErrors = [];
            $pedidoCompletado = false;

            if ($pendingProducts === 0) {
                // Todos los productos están aprobados, aprobar el pedido completo
                $order = CustomerOrder::findOrFail($orderId);
                $order->update([
                    'estado' => 'aprobado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                ]);

                $pedidoCompletado = true;

                // NOTA: La integración con plantaCruds se hace cuando se almacena el pedido,
                // no cuando se aprueba. Ver AlmacenajeController.
                
                // Notificar a almacén si el pedido viene de ahí
                if ($order->origen_sistema === 'almacen' && $order->pedido_almacen_id) {
                    $this->notifyAlmacen($order, 'aprobado');
                }
            }

            DB::commit();

            $response = [
                'message' => 'Producto aprobado exitosamente',
                'order_product' => $orderProduct->load('product', 'approver')
            ];

            // Si se completó la aprobación del pedido
            if ($pedidoCompletado) {
                $response['pedido_completado'] = true;
                $response['message'] = 'Producto y pedido aprobados exitosamente. El envío se creará cuando se almacene el pedido.';
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al aprobar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un producto específico del pedido
     */
    public function rejectProduct(Request $request, $orderId, $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'La razón de rechazo es requerida',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderProduct = OrderProduct::where('pedido_id', $orderId)
                ->where('producto_pedido_id', $productId)
                ->where('estado', 'pendiente')
                ->firstOrFail();

            $orderProduct->update([
                'estado' => 'rechazado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'razon_rechazo' => $request->rejection_reason,
            ]);

            // Verificar si todos los productos están rechazados
            $order = CustomerOrder::findOrFail($orderId);
            $productosAprobados = OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'aprobado')
                ->count();
            
            $productosPendientes = OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->count();

            // Si no hay productos aprobados ni pendientes, rechazar el pedido completo
            if ($productosAprobados === 0 && $productosPendientes === 0) {
                $order->update([
                    'estado' => 'rechazado',
                    'razon_rechazo' => 'Todos los productos fueron rechazados'
                ]);
                
                // Notificar a almacén si el pedido viene de ahí
                if ($order->origen_sistema === 'almacen' && $order->pedido_almacen_id) {
                    $this->notifyAlmacen($order, 'rechazado');
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Producto rechazado exitosamente',
                'order_product' => $orderProduct->load('product', 'approver')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al rechazar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar todo el pedido (todos los productos pendientes)
     */
    public function approveOrder(Request $request, $orderId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = CustomerOrder::with('orderProducts')
                ->where('estado', 'pendiente')
                ->findOrFail($orderId);

            $pendingProducts = $order->orderProducts()
                ->where('estado', 'pendiente')
                ->get();

            if ($pendingProducts->isEmpty()) {
                return response()->json([
                    'message' => 'No hay productos pendientes para aprobar'
                ], 400);
            }

            // Aprobar todos los productos pendientes
            OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'aprobado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                ]);

            // Aprobar el pedido completo
            $order->update([
                'estado' => 'aprobado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
            ]);

            DB::commit();

            // NOTA: La integración con plantaCruds se hace cuando se almacena el pedido,
            // no cuando se aprueba. Ver AlmacenajeController.
            
            // Notificar a almacén si el pedido viene de ahí
            if ($order->origen_sistema === 'almacen' && $order->pedido_almacen_id) {
                $this->notifyAlmacen($order, 'aprobado');
            }

            return response()->json([
                'message' => 'Pedido aprobado exitosamente',
                'order' => $order->load('orderProducts.product', 'approver'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al aprobar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notifica a sistema-almacen-PSIII sobre cambios de estado del pedido
     * 
     * @param CustomerOrder $order
     * @param string $estado 'aprobado' o 'rechazado'
     * @return void
     */
    private function notifyAlmacen(CustomerOrder $order, string $estado): void
    {
        $almacenApiUrl = env('ALMACEN_API_URL', 'http://localhost:8000/api');
        $pedidoAlmacenId = $order->pedido_almacen_id;

        if (!$pedidoAlmacenId) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->post("{$almacenApiUrl}/pedidos/{$pedidoAlmacenId}/actualizar-estado", [
                    'estado' => $estado,
                    'tracking_id' => $order->pedido_id,
                    'message' => $estado === 'aprobado' 
                        ? 'Pedido aprobado en Trazabilidad' 
                        : 'Pedido rechazado en Trazabilidad'
                ]);

            if ($response->successful()) {
                Log::info('Notificación enviada a sistema-almacen-PSIII', [
                    'pedido_almacen_id' => $pedidoAlmacenId,
                    'pedido_trazabilidad_id' => $order->pedido_id,
                    'estado' => $estado
                ]);
            } else {
                Log::warning('Error al notificar a sistema-almacen-PSIII', [
                    'pedido_almacen_id' => $pedidoAlmacenId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción al notificar a sistema-almacen-PSIII', [
                'pedido_almacen_id' => $pedidoAlmacenId,
                'error' => $e->getMessage()
            ]);
        }
    }
}










