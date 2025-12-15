<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlmacenajeController extends Controller
{
    public function index()
    {
        // Mostrar TODOS los lotes certificados (incluyendo los ya almacenados)
        $lotes = ProductionBatch::whereHas('latestFinalEvaluation', function($query) {
                $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
            })
            ->with([
                'order.customer', 
                'order.orderProducts.product.unit',
                'order.destinations.destinationProducts.orderProduct.product',
                'latestFinalEvaluation', 
                'storage'
            ])
            ->orderBy('fecha_creacion', 'desc')
            ->get();
        
        // Preparar datos de pedidos para JavaScript
        $ordersData = [];
        foreach ($lotes as $lote) {
            if ($lote->order) {
                $ordersData[$lote->pedido_id] = [
                    'numero_pedido' => $lote->order->numero_pedido ?? 'N/A',
                    'destinations' => $lote->order->destinations->map(function($dest) {
                        return [
                            'address' => $dest->direccion ?? 'N/A',
                            'reference' => $dest->referencia ?? '-',
                            'contact_name' => $dest->nombre_contacto ?? '-',
                            'contact_phone' => $dest->telefono_contacto ?? '-',
                            'delivery_instructions' => $dest->instrucciones_entrega ?? '-',
                        ];
                    })->toArray()
                ];
            }
        }

        // Calcular estadísticas sobre TODOS los lotes, no solo los filtrados
        $allLotes = ProductionBatch::with(['latestFinalEvaluation', 'storage'])->get();
        
        // Lotes disponibles para almacenar (certificados sin almacenar)
        $lotesDisponibles = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            $esCertificado = $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
            return $esCertificado && $lote->storage->isEmpty();
        });
        
        // Lotes certificados (todos los que tienen evaluación exitosa)
        $lotesCertificados = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
        });
        
        // Lotes sin certificar (sin evaluación o evaluación fallida)
        $lotesSinCertificar = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return !$eval || str_contains(strtolower($eval->razon ?? ''), 'falló');
        });
        
        // Lotes ya almacenados
        $lotesAlmacenados = $allLotes->filter(function($lote) {
            return $lote->storage->isNotEmpty();
        });

        $stats = [
            'disponibles' => $lotesDisponibles->count(),
            'certificados' => $lotesCertificados->count(),
            'sin_certificar' => $lotesSinCertificar->count(),
            'almacenados' => $lotesAlmacenados->count(),
        ];

        // Pasar configuración de la planta a la vista
        $plantaConfig = [
            'nombre' => config('services.planta.nombre', 'Planta Principal'),
            'direccion' => config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia'),
            'latitud' => config('services.planta.latitud', '-17.8146'),
            'longitud' => config('services.planta.longitud', '-63.1561'),
        ];

        return view('almacenaje', compact('lotes', 'stats', 'ordersData', 'plantaConfig'));
    }

    public function obtenerAlmacenajesPorLote($batchId)
    {
        $almacenajes = Storage::with([
                'batch.order.customer',
                'batch.order.orderProducts.product.unit',
                'batch.order.destinations.destinationProducts.orderProduct.product'
            ])
            ->where('lote_id', $batchId)
            ->orderBy('fecha_almacenaje', 'desc')
            ->get()
            ->map(function($almacenaje) {
                // Obtener productos del pedido
                $productos = [];
                if ($almacenaje->batch->order && $almacenaje->batch->order->orderProducts) {
                    $productos = $almacenaje->batch->order->orderProducts->map(function($orderProduct) {
                        return [
                            'producto_id' => $orderProduct->producto_id,
                            'producto_pedido_id' => $orderProduct->producto_pedido_id,
                            'nombre' => $orderProduct->product->nombre ?? 'N/A',
                            'cantidad' => $orderProduct->cantidad ?? 0,
                            'unidad' => $orderProduct->product->unit->nombre ?? 'N/A',
                            'codigo' => $orderProduct->product->codigo ?? null,
                            'precio' => $orderProduct->precio ?? 0,
                            'estado' => $orderProduct->estado ?? null,
                            'observaciones' => $orderProduct->observaciones ?? null,
                        ];
                    })->toArray();
                }
                
                // Obtener destinos del pedido
                $destinos = [];
                if ($almacenaje->batch->order && $almacenaje->batch->order->destinations) {
                    $destinos = $almacenaje->batch->order->destinations->map(function($destino) {
                        $productosDestino = [];
                        if ($destino->destinationProducts) {
                            $productosDestino = $destino->destinationProducts->map(function($destProd) {
                                return [
                                    'producto_nombre' => $destProd->orderProduct->product->nombre ?? 'N/A',
                                    'cantidad' => $destProd->cantidad ?? 0,
                                ];
                            })->toArray();
                        }
                        
                        return [
                            'destino_id' => $destino->destino_id,
                            'direccion' => $destino->direccion ?? null,
                            'referencia' => $destino->referencia ?? null,
                            'latitud' => $destino->latitud ?? null,
                            'longitud' => $destino->longitud ?? null,
                            'nombre_contacto' => $destino->nombre_contacto ?? null,
                            'telefono_contacto' => $destino->telefono_contacto ?? null,
                            'instrucciones_entrega' => $destino->instrucciones_entrega ?? null,
                            'productos' => $productosDestino,
                        ];
                    })->toArray();
                }
                
                return [
                    'almacenaje_id' => $almacenaje->almacenaje_id,
                    'lote_id' => $almacenaje->lote_id,
                    'codigo_lote' => $almacenaje->batch->codigo_lote ?? null,
                    'nombre_lote' => $almacenaje->batch->nombre ?? null,
                    'cantidad_producida' => $almacenaje->batch->cantidad_producida ?? 0,
                    'cantidad_objetivo' => $almacenaje->batch->cantidad_objetivo ?? 0,
                    'ubicacion' => $almacenaje->ubicacion ?? 'N/A',
                    'condicion' => $almacenaje->condicion ?? 'N/A',
                    'cantidad' => $almacenaje->cantidad ?? 0,
                    'observaciones' => $almacenaje->observaciones ?? null,
                    'fecha_almacenaje' => $almacenaje->fecha_almacenaje ? $almacenaje->fecha_almacenaje->format('Y-m-d H:i:s') : null,
                    'fecha_retiro' => $almacenaje->fecha_retiro ? $almacenaje->fecha_retiro->format('Y-m-d H:i:s') : null,
                    // Información de transporte (ubicación de recojo)
                    'direccion_recojo' => $almacenaje->direccion_recojo ?? null,
                    'referencia_recojo' => $almacenaje->referencia_recojo ?? null,
                    'latitud_recojo' => $almacenaje->latitud_recojo ?? null,
                    'longitud_recojo' => $almacenaje->longitud_recojo ?? null,
                    // Información del pedido
                    'pedido_id' => $almacenaje->batch->pedido_id ?? null,
                    'numero_pedido' => $almacenaje->batch->order->numero_pedido ?? null,
                    'nombre_pedido' => $almacenaje->batch->order->nombre ?? null,
                    'descripcion_pedido' => $almacenaje->batch->order->descripcion ?? null,
                    'observaciones_pedido' => $almacenaje->batch->order->observaciones ?? null,
                    'fecha_creacion_pedido' => $almacenaje->batch->order->fecha_creacion ? $almacenaje->batch->order->fecha_creacion->format('Y-m-d') : null,
                    'fecha_entrega_pedido' => $almacenaje->batch->order->fecha_entrega ? $almacenaje->batch->order->fecha_entrega->format('Y-m-d') : null,
                    'estado_pedido' => $almacenaje->batch->order->estado ?? null,
                    // Información del cliente
                    'cliente_id' => $almacenaje->batch->order->customer->cliente_id ?? null,
                    'cliente_razon_social' => $almacenaje->batch->order->customer->razon_social ?? null,
                    'cliente_nombre_comercial' => $almacenaje->batch->order->customer->nombre_comercial ?? null,
                    'cliente_nit' => $almacenaje->batch->order->customer->nit ?? null,
                    'cliente_direccion' => $almacenaje->batch->order->customer->direccion ?? null,
                    'cliente_telefono' => $almacenaje->batch->order->customer->telefono ?? null,
                    'cliente_email' => $almacenaje->batch->order->customer->email ?? null,
                    'cliente_contacto' => $almacenaje->batch->order->customer->contacto ?? null,
                    // Productos del pedido
                    'productos' => $productos,
                    // Destinos del pedido
                    'destinos' => $destinos,
                    // Información de envíos creados en PlantaCruds
                    'envios' => $almacenaje->batch->order ? 
                        \App\Models\OrderEnvioTracking::where('pedido_id', $almacenaje->batch->order->pedido_id)
                            ->where('estado', 'success')
                            ->get()
                            ->map(function($envio) {
                                return [
                                    'envio_id' => $envio->envio_id,
                                    'codigo_envio' => $envio->codigo_envio,
                                    'destino_id' => $envio->destino_id,
                                ];
                            }) : collect(),
                ];
            });

        return response()->json($almacenajes);
    }

    public function almacenar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lote_id' => 'required|integer|exists:lote_produccion,lote_id',
            'condicion' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'pickup_latitude' => 'required|numeric|between:-90,90',
            'pickup_longitude' => 'required|numeric|between:-180,180',
            'pickup_address' => 'required|string|max:500',
            'pickup_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::with('storage')->findOrFail($request->lote_id);

            // Verificar que el lote no tenga almacenajes previos
            if ($batch->storage->isNotEmpty()) {
                return redirect()->back()
                    ->with('error', 'Este lote ya ha sido almacenado. Solo se permite almacenar una vez toda la cantidad.')
                    ->withInput();
            }

            // La cantidad se toma del lote (producida o objetivo)
            $producedQuantity = $batch->cantidad_producida ?? 0;
            $targetQuantity = $batch->cantidad_objetivo ?? 0;
            $quantityToStore = ($producedQuantity > 0) ? $producedQuantity : $targetQuantity;

            // Sincronizar la secuencia con el máximo ID existente
            $maxStorageId = DB::table('almacenaje')->max('almacenaje_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxStorageId !== null && $maxStorageId > 0) {
                DB::statement("SELECT setval('almacenaje_seq', {$maxStorageId}, true)");
            }

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('almacenaje_seq') as id")->id;

            // Usar ubicación de la configuración de la planta
            $plantaDireccion = config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia');
            $plantaLat = config('services.planta.latitud', '-17.8146');
            $plantaLng = config('services.planta.longitud', '-63.1561');
            
            $storage = Storage::create([
                'almacenaje_id' => $nextId,
                'lote_id' => $request->lote_id,
                'ubicacion' => 'Almacén Principal', // Valor por defecto ya que no se usa
                'condicion' => $request->condicion,
                'cantidad' => $quantityToStore,
                'observaciones' => $request->observaciones,
                'latitud_recojo' => $plantaLat,
                'longitud_recojo' => $plantaLng,
                'direccion_recojo' => $plantaDireccion,
                'referencia_recojo' => null, // Ya no se usa referencia
                'fecha_almacenaje' => now(),
            ]);

            // Actualizar estado del pedido a "almacenado" cuando se almacena el lote
            if ($batch->pedido_id) {
                $pedido = CustomerOrder::find($batch->pedido_id);
                if ($pedido) {
                    $pedido->update(['estado' => 'almacenado']);
                }
            }

            DB::commit();

            // Enviar pedido a plantaCruds para crear envío con ubicación de recojo
            try {
                $order = ProductionBatch::with([
                    'order.customer',
                    'order.orderProducts.product.unit',
                    'order.destinations.destinationProducts.orderProduct.product'
                ])->findOrFail($request->lote_id)->order;
                
                if ($order && $order->destinations && $order->destinations->count() > 0) {
                    Log::info('Iniciando integración con PlantaCruds para crear envíos', [
                        'pedido_id' => $order->pedido_id,
                        'numero_pedido' => $order->numero_pedido,
                        'destinos_count' => $order->destinations->count(),
                        'storage_id' => $storage->almacenaje_id,
                        'api_url' => config('services.plantacruds.api_url'),
                    ]);
                    
                    $integration = new PlantaCrudsIntegrationService();
                    $results = $integration->sendOrderToShipping($order, $storage);

                    // Guardar tracking por cada resultado
                    foreach ($results as $res) {
                        OrderEnvioTracking::create([
                            'pedido_id' => $order->pedido_id,
                            'destino_id' => $res['destination_id'] ?? null,
                            'envio_id' => $res['envio_id'] ?? null,
                            'codigo_envio' => $res['envio_codigo'] ?? null,
                            'estado' => $res['success'] ? 'success' : 'failed',
                            'mensaje_error' => $res['success'] ? null : ($res['error'] ?? 'Unknown error'),
                            'datos_solicitud' => $res['response']['request'] ?? null,
                            'datos_respuesta' => $res['response'] ?? null,
                        ]);
                    }
                    
                    $successful = collect($results)->where('success', true)->count();
                    $failed = collect($results)->where('success', false)->count();
                    
                    Log::info('Integración con PlantaCruds completada', [
                        'pedido_id' => $order->pedido_id,
                        'results_count' => count($results),
                        'successful' => $successful,
                        'failed' => $failed,
                    ]);
                    
                    if ($failed > 0) {
                        Log::warning('Algunos envíos fallaron al crear en PlantaCruds', [
                            'pedido_id' => $order->pedido_id,
                            'failed_count' => $failed,
                            'errors' => collect($results)->where('success', false)->pluck('error')->toArray(),
                        ]);
                    }
                } else {
                    Log::warning('No se pudo enviar pedido a PlantaCruds: pedido sin destinos', [
                        'lote_id' => $request->lote_id,
                        'pedido_id' => $order->pedido_id ?? null,
                        'has_order' => $order !== null,
                        'has_destinations' => $order && $order->destinations ? $order->destinations->count() : 0,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error integrando con plantaCruds al almacenar lote', [
                    'lote_id' => $request->lote_id,
                    'almacenaje_id' => $nextId,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // No fallar el almacenamiento si falla la integración
            }

            return redirect()->route('almacenaje')
                ->with('success', 'Lote almacenado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al almacenar lote: ' . $e->getMessage())
                ->withInput();
        }
    }
}

