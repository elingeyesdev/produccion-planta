<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardClienteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Obtener pedidos del cliente actual (buscando por customer relacionado con el operador)
        $customerId = null;
        $customer = null;
        
        // Buscar por email
        if ($user->email) {
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->cliente_id : null;
        }
        
        // Si no se encontró un cliente, crear uno automáticamente para este usuario
        if (!$customerId) {
            try {
                // Sincronizar secuencia de customer si es necesario
                $maxCustomerId = \App\Models\Customer::max('cliente_id') ?? 0;
                try {
                    $seqResult = \Illuminate\Support\Facades\DB::selectOne("SELECT last_value FROM cliente_seq");
                    $seqValue = $seqResult->last_value ?? 0;
                } catch (\Exception $e) {
                    $seqValue = 0;
                }
                
                if ($seqValue < $maxCustomerId) {
                    \Illuminate\Support\Facades\DB::statement("SELECT setval('cliente_seq', $maxCustomerId, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $nextId = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('cliente_seq') as id")->id;
                
                // Crear un Customer automáticamente para este operador
                $customer = \App\Models\Customer::create([
                    'cliente_id' => $nextId,
                    'razon_social' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                    'nombre_comercial' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                    'email' => $user->email ?? null,
                    'contacto' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: $user->usuario,
                    'activo' => true,
                ]);
                
                $customerId = $customer->cliente_id;
            } catch (\Exception $e) {
                // Si falla, usar el primer cliente activo como fallback
                $customer = \App\Models\Customer::where('activo', true)->first();
                $customerId = $customer ? $customer->cliente_id : null;
            }
        }
        
        // Si aún no hay customerId, mostrar pedidos vacíos
        if (!$customerId) {
            $pedidos = collect([]);
            $ultimoPedido = null;
        } else {
            // Obtener todos los pedidos del cliente ordenados por fecha de creación descendente
            $pedidos = CustomerOrder::where('cliente_id', $customerId)
                ->with([
                    'batches.latestFinalEvaluation',
                    'batches.processMachineRecords.processMachine',
                    'batches.storage',
                    'materialRequests'
                ])
                ->orderBy('fecha_creacion', 'desc')
                ->orderBy('pedido_id', 'desc') // Ordenar también por ID para asegurar consistencia
                ->get();
            
            // Obtener el último pedido (el más reciente) para seguimiento
            // Usar first() ya que está ordenado por fecha_creacion desc
            $ultimoPedido = $pedidos->first();
            
            // Si hay último pedido, cargar más información completa
            if ($ultimoPedido) {
                // Recargar con todas las relaciones necesarias para el timeline
                $ultimoPedido = CustomerOrder::where('pedido_id', $ultimoPedido->pedido_id)
                    ->with([
                        'customer',
                        'orderProducts.product.unit',
                        'batches.latestFinalEvaluation.inspector',
                        'batches.processMachineRecords.processMachine.machine',
                        'batches.processMachineRecords.processMachine.process',
                        'batches.processMachineRecords.processMachine.variables.standardVariable',
                        'batches.processMachineRecords.operator',
                        'batches.storage',
                        'batches.rawMaterials.rawMaterial.materialBase',
                        'materialRequests.details.material',
                        'destinations'
                    ])
                    ->first();
                
                // Ordenar lotes por fecha de creación para mostrar el más reciente primero
                if ($ultimoPedido && $ultimoPedido->batches) {
                    $ultimoPedido->batches = $ultimoPedido->batches->sortByDesc('fecha_creacion')->values();
                }
            }
        }

        // Calcular estadísticas reales
        $totalPedidos = $pedidos->count();
        $pedidosPendientes = $pedidos->filter(function($pedido) {
            // Pendiente si no tiene lotes o todos los lotes están pendientes
            if ($pedido->batches->isEmpty()) {
                return true;
            }
            // Si tiene lotes, verificar si alguno está en proceso
            return $pedido->batches->some(function($batch) {
                return !$batch->latestFinalEvaluation && $batch->processMachineRecords->isNotEmpty();
            });
        })->count();
        
        $pedidosCompletados = $pedidos->filter(function($pedido) {
            // Completado si tiene al menos un lote certificado
            return $pedido->batches->some(function($batch) {
                $eval = $batch->latestFinalEvaluation;
                return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
            });
        })->count();
        
        $pedidosEnProceso = $pedidos->filter(function($pedido) {
            // En proceso si tiene lotes con registros pero sin certificar
            return $pedido->batches->some(function($batch) {
                return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
            });
        })->count();

        $stats = [
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'pedidos_completados' => $pedidosCompletados,
            'pedidos_en_proceso' => $pedidosEnProceso,
        ];

        return view('dashboard-cliente', compact('pedidos', 'stats', 'ultimoPedido', 'customer'));
    }

    public function getData()
    {
        $user = Auth::user();
        
        // Obtener pedidos del cliente actual
        $customerId = null;
        if ($user->email) {
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->cliente_id : null;
        }
        
        if (!$customerId) {
            return response()->json([
                'stats' => [
                    'total_pedidos' => 0,
                    'pedidos_pendientes' => 0,
                    'pedidos_completados' => 0,
                    'pedidos_en_proceso' => 0,
                ],
                'pedidos' => [],
                'ultimoPedido' => null,
            ]);
        }
        
        // Obtener todos los pedidos del cliente
        $pedidos = CustomerOrder::where('cliente_id', $customerId)
            ->with([
                'batches.latestFinalEvaluation',
                'batches.processMachineRecords',
                'batches.storage',
                'materialRequests'
            ])
            ->orderBy('fecha_creacion', 'desc')
            ->orderBy('pedido_id', 'desc')
            ->get();
        
        $ultimoPedido = $pedidos->first();
        
        // Si hay último pedido, cargar más información completa
        if ($ultimoPedido) {
            $ultimoPedido = CustomerOrder::where('pedido_id', $ultimoPedido->pedido_id)
                ->with([
                    'customer',
                    'orderProducts.product.unit',
                    'batches.latestFinalEvaluation.inspector',
                    'batches.processMachineRecords.processMachine.machine',
                    'batches.processMachineRecords.processMachine.process',
                    'batches.processMachineRecords.operator',
                    'batches.storage',
                    'batches.rawMaterials.rawMaterial.materialBase',
                    'materialRequests.details.material',
                    'destinations'
                ])
                ->first();
            
            if ($ultimoPedido && $ultimoPedido->batches) {
                $ultimoPedido->batches = $ultimoPedido->batches->sortByDesc('fecha_creacion')->values();
            }
        }

        // Calcular estadísticas
        $totalPedidos = $pedidos->count();
        $pedidosPendientes = $pedidos->filter(function($pedido) {
            if ($pedido->batches->isEmpty()) {
                return true;
            }
            return $pedido->batches->some(function($batch) {
                return !$batch->latestFinalEvaluation && $batch->processMachineRecords->isNotEmpty();
            });
        })->count();
        
        $pedidosCompletados = $pedidos->filter(function($pedido) {
            return $pedido->batches->some(function($batch) {
                $eval = $batch->latestFinalEvaluation;
                return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
            });
        })->count();
        
        $pedidosEnProceso = $pedidos->filter(function($pedido) {
            return $pedido->batches->some(function($batch) {
                return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
            });
        })->count();

        $stats = [
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'pedidos_completados' => $pedidosCompletados,
            'pedidos_en_proceso' => $pedidosEnProceso,
        ];

        // Formatear pedidos para JSON
        $pedidosFormateados = $pedidos->map(function($pedido) {
            $estadoPedido = 'Pendiente';
            $tieneLotes = $pedido->batches->isNotEmpty();
            $loteCertificado = false;
            
            if ($tieneLotes) {
                $loteCertificado = $pedido->batches->some(function($batch) {
                    $eval = $batch->latestFinalEvaluation;
                    return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
                });
                
                $loteEnProceso = $pedido->batches->some(function($batch) {
                    return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
                });
                
                if ($loteCertificado) {
                    $estadoPedido = 'Certificado';
                } elseif ($loteEnProceso) {
                    $estadoPedido = 'En Proceso';
                } elseif ($tieneLotes) {
                    $estadoPedido = 'Lote Creado';
                }
            }
            
            return [
                'pedido_id' => $pedido->pedido_id,
                'numero_pedido' => $pedido->numero_pedido ?? $pedido->pedido_id,
                'nombre' => $pedido->nombre ?? ($pedido->descripcion ?? 'Sin descripción'),
                'fecha_creacion' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('d/m/Y') : 'N/A',
                'fecha_entrega' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : 'N/A',
                'estado' => $estadoPedido,
                'tiene_lotes' => $tieneLotes,
                'cantidad_lotes' => $pedido->batches->count(),
                'lote_certificado' => $loteCertificado,
            ];
        });

        // Formatear último pedido para JSON (solo datos básicos para el timeline)
        $ultimoPedidoData = null;
        if ($ultimoPedido) {
            $ultimoPedidoData = [
                'pedido_id' => $ultimoPedido->pedido_id,
                'numero_pedido' => $ultimoPedido->numero_pedido ?? $ultimoPedido->pedido_id,
                'nombre' => $ultimoPedido->nombre ?? 'Sin nombre',
                'descripcion' => $ultimoPedido->descripcion ?? 'Sin descripción',
                'fecha_creacion' => $ultimoPedido->fecha_creacion ? $ultimoPedido->fecha_creacion->format('d/m/Y') : 'N/A',
                'fecha_entrega' => $ultimoPedido->fecha_entrega ? $ultimoPedido->fecha_entrega->format('d/m/Y') : null,
                'tiene_lotes' => $ultimoPedido->batches && $ultimoPedido->batches->isNotEmpty(),
                'cantidad_lotes' => $ultimoPedido->batches ? $ultimoPedido->batches->count() : 0,
                'tiene_solicitudes' => $ultimoPedido->materialRequests && $ultimoPedido->materialRequests->isNotEmpty(),
                'tiene_lotes_almacenados' => $ultimoPedido->batches && $ultimoPedido->batches->some(function($batch) {
                    return $batch->storage->isNotEmpty();
                }),
            ];
        }

        return response()->json([
            'stats' => $stats,
            'pedidos' => $pedidosFormateados,
            'ultimoPedido' => $ultimoPedidoData,
        ]);
    }

    public function obtenerDetallesPedido($orderId)
    {
        $user = Auth::user();
        
        // Buscar customer del usuario
        $customerId = null;
        if ($user->email) {
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->cliente_id : null;
        }
        
        // Obtener el pedido solo si pertenece al cliente
        $pedido = CustomerOrder::where('pedido_id', $orderId)
            ->where('cliente_id', $customerId)
            ->with([
                'batches.latestFinalEvaluation.inspector',
                'batches.processMachineRecords.processMachine.machine',
                'batches.processMachineRecords.processMachine.process',
                'batches.processMachineRecords.operator',
                'batches.storage',
                'batches.rawMaterials.rawMaterial.materialBase',
                'materialRequests.details.material'
            ])
            ->first();
        
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado o no autorizado'], 404);
        }
        
        return response()->json([
            'pedido' => [
                'order_id' => $pedido->pedido_id,
                'order_number' => $pedido->numero_pedido,
                'name' => $pedido->nombre,
                'description' => $pedido->descripcion,
                'creation_date' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('d/m/Y') : null,
                'delivery_date' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : null,
                'observations' => $pedido->observaciones,
            ],
            'lotes' => $pedido->batches->map(function($batch) {
                $eval = $batch->latestFinalEvaluation;
                return [
                    'batch_id' => $batch->lote_id,
                    'batch_code' => $batch->codigo_lote,
                    'name' => $batch->nombre,
                    'creation_date' => $batch->fecha_creacion->format('d/m/Y'),
                    'start_time' => $batch->hora_inicio ? $batch->hora_inicio->format('d/m/Y H:i') : null,
                    'end_time' => $batch->hora_fin ? $batch->hora_fin->format('d/m/Y H:i') : null,
                    'target_quantity' => $batch->cantidad_objetivo,
                    'produced_quantity' => $batch->cantidad_producida,
                    'estado' => $eval 
                        ? (str_contains(strtolower($eval->razon ?? ''), 'falló') ? 'No Certificado' : 'Certificado')
                        : ($batch->processMachineRecords->isNotEmpty() ? 'En Proceso' : 'Pendiente'),
                    'certificacion' => $eval ? [
                        'evaluation_date' => $eval->fecha_evaluacion->format('d/m/Y H:i'),
                        'reason' => $eval->razon,
                        'inspector' => $eval->inspector ? $eval->inspector->nombre . ' ' . $eval->inspector->apellido : 'N/A',
                    ] : null,
                    'maquinas' => $batch->processMachineRecords->map(function($record) {
                        return [
                            'nombre' => $record->processMachine->nombre ?? 'N/A',
                            'maquina' => $record->processMachine->machine->nombre ?? 'N/A',
                            'cumple_estandar' => $record->cumple_estandar ?? false,
                            'fecha' => $record->fecha_registro ? $record->fecha_registro->format('d/m/Y H:i') : null,
                        ];
                    }),
                    'almacenamiento' => $batch->storage->map(function($st) {
                        return [
                            'location' => $st->ubicacion,
                            'condition' => $st->condicion,
                            'quantity' => $st->cantidad,
                            'storage_date' => $st->fecha_almacenaje ? $st->fecha_almacenaje->format('d/m/Y H:i') : null,
                        ];
                    }),
                ];
            }),
            'solicitudes_materia_prima' => $pedido->materialRequests->map(function($req) {
                return [
                    'request_number' => $req->numero_solicitud,
                    'request_date' => $req->fecha_solicitud->format('d/m/Y'),
                    'required_date' => $req->fecha_requerida->format('d/m/Y'),
                    'estado' => 'Pendiente', // Las solicitudes siempre están pendientes hasta que se completen
                    'materiales' => $req->details->map(function($det) {
                        return [
                            'material' => $det->material->nombre,
                            'cantidad_solicitada' => $det->cantidad_solicitada,
                            'cantidad_aprobada' => $det->cantidad_aprobada ?? 0,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
