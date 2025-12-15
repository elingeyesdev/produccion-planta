<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
use App\Services\AlmacenPedidoTransformService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AlmacenPedidoController extends Controller
{
    protected AlmacenPedidoTransformService $transformService;

    public function __construct(AlmacenPedidoTransformService $transformService)
    {
        $this->transformService = $transformService;
    }

    /**
     * Recibe un pedido completo desde sistema-almacen-PSIII
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => 'required|integer',
            'codigo_comprobante' => 'required|string',
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date',
            'almacen' => 'required|array',
            'almacen.nombre' => 'required|string',
            'almacen.latitud' => 'nullable|numeric',
            'almacen.longitud' => 'nullable|numeric',
            'almacen.direccion' => 'nullable|string',
            'administrador' => 'required|array',
            'administrador.full_name' => 'required|string',
            'administrador.email' => 'nullable|email',
            'productos' => 'required|array|min:1',
            'productos.*.producto_nombre' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'operador' => 'nullable|array',
            'transportista' => 'nullable|array',
            'proveedor_id' => 'nullable',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $pedidoData = $request->all();

            // Verificar si el pedido ya existe (por pedido_almacen_id)
            $existingOrder = CustomerOrder::where('pedido_almacen_id', $pedidoData['pedido_id'])
                ->where('origen_sistema', 'almacen')
                ->first();

            if ($existingOrder) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido ya fue recibido anteriormente',
                    'tracking_id' => $existingOrder->pedido_id
                ], 409);
            }

            // Transformar datos a estructura de Trazabilidad
            $orderData = $this->transformService->transformToCustomerOrder($pedidoData);

            // Crear CustomerOrder
            $order = CustomerOrder::create($orderData);

            // Crear productos del pedido
            $orderProducts = $this->transformService->createOrderProducts($order, $pedidoData['productos']);

            // Crear destino (almacén)
            $destination = $this->transformService->createOrderDestination(
                $order,
                $pedidoData['almacen'],
                $pedidoData['operador'] ?? null
            );

            // Asignar productos al destino
            // Generar producto_destino_id manualmente (no es auto-increment)
            $lastDestinationProduct = OrderDestinationProduct::orderBy('producto_destino_id', 'desc')->first();
            $nextProductoDestinoId = $lastDestinationProduct ? ($lastDestinationProduct->producto_destino_id + 1) : 1;
            
            foreach ($orderProducts as $orderProduct) {
                OrderDestinationProduct::create([
                    'producto_destino_id' => $nextProductoDestinoId++,
                    'destino_id' => $destination->destino_id,
                    'producto_pedido_id' => $orderProduct->producto_pedido_id,
                    'cantidad' => $orderProduct->cantidad,
                ]);
            }

            DB::commit();

            Log::info('Pedido recibido desde sistema-almacen-PSIII', [
                'pedido_almacen_id' => $pedidoData['pedido_id'],
                'pedido_trazabilidad_id' => $order->pedido_id,
                'numero_pedido' => $order->numero_pedido,
                'productos_count' => count($orderProducts)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pedido recibido y creado exitosamente',
                'tracking_id' => $order->pedido_id,
                'numero_pedido' => $order->numero_pedido,
                'estado' => $order->estado,
                'productos_count' => count($orderProducts),
                'destination_id' => $destination->destino_id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al recibir pedido desde sistema-almacen-PSIII', [
                'pedido_id' => $request->input('pedido_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar pedido: ' . $e->getMessage()
            ], 500);
        }
    }
}

