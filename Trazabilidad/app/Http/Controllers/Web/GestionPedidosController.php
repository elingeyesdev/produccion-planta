<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\OrderProduct;
use App\Models\OrderEnvioTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestionPedidosController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerOrder::with(['customer', 'orderProducts.product']);
        
        // Filtro por estado - por defecto mostrar todos los estados
        $estadoFiltro = $request->get('estado', '');
        
        if ($estadoFiltro === 'pendientes_aprobados') {
            $query->whereIn('estado', ['pendiente', 'aprobado']);
        } elseif ($estadoFiltro === 'pendientes_aprobados_almacenados') {
            $query->whereIn('estado', ['pendiente', 'aprobado', 'almacenado']);
        } elseif ($estadoFiltro && $estadoFiltro !== '') {
            $query->where('estado', $estadoFiltro);
        }
        // Si $estadoFiltro está vacío, no se aplica filtro y se muestran todos los estados
        
        // Filtro por cliente
        if ($request->has('cliente') && $request->cliente) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->cliente . '%')
                  ->orWhere('nombre_comercial', 'like', '%' . $request->cliente . '%');
            });
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_creacion', $request->fecha);
        }
        
        $pedidos = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        $clientes = Customer::where('activo', true)->get();

        // Estadísticas
        $stats = [
            'total' => CustomerOrder::count(),
            'pendientes' => CustomerOrder::where('estado', 'pendiente')->count(),
            'aprobados' => CustomerOrder::where('estado', 'aprobado')->count(),
            'rechazados' => CustomerOrder::where('estado', 'rechazado')->count(),
            'en_produccion' => CustomerOrder::where('estado', 'en_produccion')->count(),
            'almacenados' => CustomerOrder::where('estado', 'almacenado')->count(),
        ];

        return view('gestion-pedidos', compact('pedidos', 'clientes', 'stats', 'estadoFiltro'));
    }

    public function show($id)
    {
        $pedido = CustomerOrder::with([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product',
            'approver'
        ])->findOrFail($id);

        $trackings = OrderEnvioTracking::where('pedido_id', $pedido->pedido_id)->orderBy('created_at', 'desc')->get();

        // Base web URL of PlantaCruds (try to derive from API URL)
        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
        $plantaBase = rtrim(str_replace('/api', '', $apiUrl), '/');

        // URLs de acceso a endpoints de plantaCruds usando los métodos helper
        $propuestaPdfUrl = $pedido->getPropuestaVehiculosPdfUrl();
        $aprobarRechazarUrl = $pedido->getAprobarRechazarUrl();
        $envioId = $pedido->getPlantaCrudsEnvioId();
        
        // Verificar si el envío está pendiente de aprobación por trazabilidad
        // Solo mostramos los botones si el estado es realmente "pendiente_aprobacion_trazabilidad"
        $mostrarAprobarRechazar = false;
        if ($envioId && $aprobarRechazarUrl) {
            $mostrarAprobarRechazar = $pedido->isEnvioPendienteAprobacionTrazabilidad();
        }

        // Buscar materias primas que coincidan con los productos del pedido
        // Esto es solo para mostrar información, NO crea materias primas automáticamente
        $nombresProductos = $pedido->orderProducts->pluck('product.nombre')->filter()->unique()->toArray();
        
        $materiasPrimasCreadas = collect();
        
        if (!empty($nombresProductos)) {
            // Buscar materias primas existentes que coincidan con los nombres de productos
            $materiasPrimasExistentes = \App\Models\RawMaterialBase::where('activo', true)
                ->whereIn('nombre', $nombresProductos)
                ->with(['category', 'unit', 'rawMaterials'])
                ->get();
            
            // Mapear las materias primas con información del pedido
            $materiasPrimasCreadas = $materiasPrimasExistentes->map(function($mp) use ($pedido) {
                // Agregar información del producto del pedido correspondiente
                $productoPedido = $pedido->orderProducts->first(function($op) use ($mp) {
                    return $op->product && $op->product->nombre === $mp->nombre;
                });
                
                $mp->cantidad_requerida = $productoPedido ? $productoPedido->cantidad : 0;
                $mp->producto_pedido = $productoPedido;
                return $mp;
            });
        }

        return view('gestion-pedidos-detalle', compact('pedido', 'trackings', 'plantaBase', 'propuestaPdfUrl', 'aprobarRechazarUrl', 'envioId', 'mostrarAprobarRechazar', 'materiasPrimasCreadas'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_entrega' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $pedido = CustomerOrder::findOrFail($id);
            $pedido->update([
                'fecha_entrega' => $request->fecha_entrega,
                'descripcion' => $request->descripcion,
                'observaciones' => $request->observaciones,
            ]);

            return redirect()->route('gestion-pedidos')
                ->with('success', 'Pedido actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar pedido: ' . $e->getMessage());
        }
    }

    public function approveOrder(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $order = CustomerOrder::findOrFail($orderId);
            
            if ($order->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden aprobar pedidos pendientes');
            }

            // Aprobar todos los productos del pedido
            OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'aprobado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                    'observaciones' => $request->observations,
                ]);

            // Aprobar el pedido completo
            $order->update([
                'estado' => 'aprobado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'observaciones' => $request->observations,
            ]);

            DB::commit();

            // Notificar a almacén si el pedido viene de ahí
            if ($order->origen_sistema === 'almacen' && $order->pedido_almacen_id) {
                $this->notifyAlmacen($order, 'aprobado');
            }

            // NOTA: Los envíos se crean únicamente cuando se almacena el lote en AlmacenajeController,
            // no cuando se aprueba el pedido.

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Pedido aprobado exitosamente. El envío se creará cuando se almacene el lote.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al aprobar pedido: ' . $e->getMessage());
        }
    }

    public function rejectOrder(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $order = CustomerOrder::findOrFail($orderId);
            
            if ($order->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden rechazar pedidos pendientes');
            }

            // Rechazar todos los productos del pedido
            OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'rechazado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                    'razon_rechazo' => $request->rejection_reason,
                ]);

            // Rechazar el pedido completo
            $order->update([
                'estado' => 'rechazado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'razon_rechazo' => $request->rejection_reason,
            ]);

            DB::commit();

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Pedido rechazado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al rechazar pedido: ' . $e->getMessage());
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
        $almacenApiUrl = env('ALMACEN_API_URL', 'http://localhost:8002/api');
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

