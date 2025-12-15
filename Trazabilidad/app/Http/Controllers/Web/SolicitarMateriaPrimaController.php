<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolicitarMateriaPrimaController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialRequest::with([
                'order.customer', 
                'details.material.unit',
                'details' => function($q) {
                    $q->orderBy('detalle_id', 'asc');
                }
            ]);
        
        // Filtro por estado (basado en detalles)
        if ($request->has('estado') && $request->estado) {
            $estado = $request->estado;
            if ($estado === 'pendiente') {
                // Solicitudes con al menos un detalle sin completar
                $query->whereHas('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            } elseif ($estado === 'completada' || $estado === 'entregada') {
                // Solicitudes donde todos los detalles están completos
                $query->whereDoesntHave('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            }
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_solicitud', $request->fecha);
        }
        
        // Filtro por solicitante (cliente)
        if ($request->has('solicitante') && $request->solicitante) {
            $query->whereHas('order.customer', function($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->solicitante . '%')
                  ->orWhere('nombre_comercial', 'like', '%' . $request->solicitante . '%');
            });
        }
        
        $solicitudes = $query->orderBy('fecha_solicitud', 'desc')
            ->orderBy('solicitud_id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Calcular estadísticas correctas basadas en detalles
        $allSolicitudes = MaterialRequest::with('details')->get();
        
        $totalSolicitudes = $allSolicitudes->count();
        $completadas = 0;
        $pendientes = 0;
        
        foreach ($allSolicitudes as $solicitud) {
            $todosCompletos = true;
            foreach ($solicitud->details as $detail) {
                $cantidadAprobada = $detail->cantidad_aprobada ?? 0;
                $cantidadSolicitada = $detail->cantidad_solicitada ?? 0;
                if ($cantidadAprobada < $cantidadSolicitada) {
                    $todosCompletos = false;
                    break;
                }
            }
            if ($todosCompletos && $solicitud->details->isNotEmpty()) {
                $completadas++;
            } else {
                $pendientes++;
            }
        }

        // Obtener IDs de pedidos que ya tienen solicitudes de materia prima
        $pedidosConSolicitud = MaterialRequest::pluck('pedido_id')->unique()->toArray();

        // Excluir pedidos que ya tienen solicitudes
        // Mostrar pedidos pendientes y aprobados que aún no tienen solicitud
        $pedidos = CustomerOrder::whereIn('estado', ['pendiente', 'aprobado'])
            ->whereNotIn('pedido_id', $pedidosConSolicitud)
            ->with('customer')
            ->orderBy('fecha_creacion', 'desc')
            ->get();
            
        $materias_primas = RawMaterialBase::where('activo', true)
            ->with('unit')
            ->orderBy('nombre', 'asc')
            ->get();
        
        // Preparar datos para JavaScript (array)
        $materias_primas_json = $materias_primas->map(function($mp) {
            return [
                'material_id' => $mp->material_id,
                'nombre' => $mp->nombre,
                'unit' => $mp->unit ? [
                    'codigo' => $mp->unit->codigo,
                    'nombre' => $mp->unit->nombre,
                ] : null
            ];
        });

        $stats = [
            'total' => $totalSolicitudes,
            'completadas' => $completadas,
            'pendientes' => $pendientes,
        ];

        return view('solicitar-materia-prima', compact('solicitudes', 'pedidos', 'materias_primas', 'materias_primas_json', 'stats'));
    }

    /**
     * Obtiene las materias primas necesarias para un pedido específico
     */
    public function getMateriasPrimasPorPedido($pedidoId)
    {
        try {
            $pedido = CustomerOrder::with(['orderProducts.product.unit'])->findOrFail($pedidoId);
            
            // Obtener nombres de productos del pedido
            $nombresProductos = $pedido->orderProducts->pluck('product.nombre')->filter()->unique()->toArray();
            
            // Buscar materias primas que coincidan con los nombres de productos
            $materiasPrimas = RawMaterialBase::where('activo', true)
                ->whereIn('nombre', $nombresProductos)
                ->with(['unit', 'rawMaterials'])
                ->get()
                ->map(function($mp) use ($pedido) {
                    // Encontrar el producto del pedido correspondiente
                    $productoPedido = $pedido->orderProducts->first(function($op) use ($mp) {
                        return $op->product && $op->product->nombre === $mp->nombre;
                    });
                    
                    // Calcular cantidad disponible
                    $cantidadDisponible = $mp->rawMaterials()
                        ->where('conformidad_recepcion', true)
                        ->sum('cantidad_disponible') ?? 0;
                    if ($cantidadDisponible == 0 && $mp->rawMaterials->count() == 0) {
                        $cantidadDisponible = $mp->cantidad_disponible ?? 0;
                    }
                    
                    // Cantidad requerida del pedido
                    $cantidadRequerida = $productoPedido ? $productoPedido->cantidad : 0;
                    
                    // Cantidad mínima a solicitar (requerida - disponible, mínimo 0)
                    $cantidadMinimaSolicitar = max(0, $cantidadRequerida - $cantidadDisponible);
                    
                    return [
                        'material_id' => $mp->material_id,
                        'nombre' => $mp->nombre,
                        'codigo' => $mp->codigo,
                        'cantidad_requerida' => $cantidadRequerida,
                        'cantidad_disponible' => $cantidadDisponible,
                        'cantidad_minima_solicitar' => $cantidadMinimaSolicitar,
                        'unidad' => [
                            'codigo' => $mp->unit->codigo ?? 'KG',
                            'nombre' => $mp->unit->nombre ?? 'Kilogramo'
                        ],
                        'tiene_suficiente' => $cantidadDisponible >= $cantidadRequerida
                    ];
                })
                ->filter(function($mp) {
                    // Solo incluir materias primas que realmente necesiten ser solicitadas
                    // (tienen cantidad mínima a solicitar > 0 o no tienen suficiente)
                    return $mp['cantidad_minima_solicitar'] > 0 || !$mp['tiene_suficiente'];
                })
                ->values();
            
            return response()->json([
                'success' => true,
                'materias_primas' => $materiasPrimas,
                'pedido' => [
                    'numero_pedido' => $pedido->numero_pedido,
                    'nombre' => $pedido->nombre,
                    'fecha_entrega' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('Y-m-d') : null,
                    'fecha_entrega_formatted' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : null,
                    'fecha_creacion' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('d/m/Y') : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materias primas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => 'required|integer|exists:pedido_cliente,pedido_id',
            'fecha_requerida' => ['required', 'date', 'after_or_equal:today'],
            'direccion' => 'required|string|max:500',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'materials.*.cantidad_solicitada' => 'required|numeric|min:0',
        ], [
            'fecha_requerida.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',
            'direccion.required' => 'La dirección de entrega es obligatoria.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Sincronizar secuencia y obtener el siguiente ID
            $maxId = DB::table('solicitud_material')->max('solicitud_id');
            if ($maxId !== null && $maxId > 0) {
                DB::statement("SELECT setval('solicitud_material_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('solicitud_material_seq') as id")->id;
            
            // Generar número de solicitud automáticamente
            $requestNumber = 'SOL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $materialRequest = MaterialRequest::create([
                'solicitud_id' => $nextId,
                'pedido_id' => $request->pedido_id,
                'numero_solicitud' => $requestNumber,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_requerida' => $request->fecha_requerida,
                'observaciones' => $request->observaciones ?? null,
                'direccion' => $request->direccion,
                'latitud' => $request->latitud ?? null,
                'longitud' => $request->longitud ?? null,
            ]);

            $details = [];
            foreach ($request->materials as $material) {
                // Sincronizar secuencia y obtener el siguiente ID para detail
                $maxDetailId = DB::table('detalle_solicitud_material')->max('detalle_id');
                if ($maxDetailId !== null && $maxDetailId > 0) {
                    DB::statement("SELECT setval('detalle_solicitud_material_seq', {$maxDetailId}, true)");
                }
                
                $detailId = DB::selectOne("SELECT nextval('detalle_solicitud_material_seq') as id")->id;
                
                $detail = MaterialRequestDetail::create([
                    'detalle_id' => $detailId,
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'material_id' => $material['material_id'],
                    'cantidad_solicitada' => $material['cantidad_solicitada'],
                ]);
                
                $details[] = $detail;
            }

            DB::commit();

            // Enviar solicitud al endpoint externo (no debe afectar si falla)
            try {
                $this->enviarSolicitudAProductores($materialRequest, $details);
            } catch (\Exception $e) {
                // Log del error pero no afectar la creación de la solicitud
                Log::warning('Error al enviar solicitud a productores API', [
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Recargar la página después de crear la solicitud y enviarla a productores
            return redirect()->route('solicitar-materia-prima')
                ->with('success', 'Solicitud de materia prima creada exitosamente y enviada a productores');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Envía la solicitud de materia prima al endpoint de productores
     */
    private function enviarSolicitudAProductores(MaterialRequest $materialRequest, array $details)
    {
        $apiUrl = env('PRODUCTORES_API_URL', 'http://127.0.0.1:8003/api');
        
        if (empty($apiUrl)) {
            Log::warning('PRODUCTORES_API_URL no está configurada, omitiendo envío');
            return;
        }

        // Obtener los IDs de materiales para cargar sus nombres
        $materialIds = array_map(function($detail) {
            return $detail->material_id;
        }, $details);
        
        // Cargar las materias primas con sus nombres
        $materiasPrimas = RawMaterialBase::whereIn('material_id', $materialIds)
            ->pluck('nombre', 'material_id')
            ->toArray();
        
        // Construir el array de detalles con el nuevo formato
        $detallesArray = [];
        foreach ($details as $detail) {
            $nombreProducto = $materiasPrimas[$detail->material_id] ?? null;
            if ($nombreProducto) {
                $detallesArray[] = [
                    'cultivo_personalizado' => $nombreProducto,
                    'cantidad' => (float) $detail->cantidad_solicitada,
                    'observaciones' => '' // Campo opcional, puede estar vacío
                ];
            }
        }

        // Construir el JSON según el nuevo formato especificado
        $data = [
            'numero_solicitud' => $materialRequest->numero_solicitud,
            'nombre_planta' => 'Planta',
            'latitud' => $materialRequest->latitud ? (float) $materialRequest->latitud : null,
            'longitud' => $materialRequest->longitud ? (float) $materialRequest->longitud : null,
            'direccion_texto' => $materialRequest->direccion,
            'estado' => 'pendiente',
            'fechaEntregaDeseada' => $materialRequest->fecha_requerida->format('Y-m-d'),
            'observaciones' => $materialRequest->observaciones ?? '',
            'detalles' => $detallesArray
        ];

        $url = rtrim($apiUrl, '/') . '/pedidos';

        Log::info('Enviando solicitud a productores API', [
            'url' => $url,
            'solicitud_id' => $materialRequest->solicitud_id,
            'numero_solicitud' => $materialRequest->numero_solicitud,
            'data' => $data
        ]);

        try {
            $response = Http::timeout(10)
                ->post($url, $data);

            if ($response->successful()) {
                Log::info('Solicitud enviada exitosamente a productores API', [
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
            } else {
                Log::warning('Error al enviar solicitud a productores API', [
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('No se pudo conectar con productores API', [
                'solicitud_id' => $materialRequest->solicitud_id,
                'error' => $e->getMessage(),
                'url' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción al enviar solicitud a productores API', [
                'solicitud_id' => $materialRequest->solicitud_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

