<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GestionLotesController extends Controller
{
    public function index()
    {
        $lotes = ProductionBatch::with([
            'order.customer', 
            'rawMaterials.rawMaterial.materialBase',
            'rawMaterials.rawMaterial.supplier',
            'finalEvaluation'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => ProductionBatch::count(),
            'pendientes' => ProductionBatch::whereNull('hora_inicio')->count(),
            'en_proceso' => ProductionBatch::whereNotNull('hora_inicio')
                ->whereNull('hora_fin')->count(),
            'completados' => ProductionBatch::whereNotNull('hora_fin')->count(),
            'certificados' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
            })->count(),
        ];

        // Datos para formularios - pedidos con estado pendiente o aprobado
        // Cargar también los productos del pedido para mostrar información de referencia
        $pedidos = CustomerOrder::whereIn('estado', ['pendiente', 'aprobado'])
            ->with(['customer', 'orderProducts.product.unit'])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        // Materias primas base activas (todas las guardadas)
        $materias_primas = RawMaterialBase::where('activo', true)
            ->with('unit')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($mp) {
                // Calcular cantidad disponible dinámicamente desde las materias primas relacionadas
                $mp->calculated_available_quantity = $mp->rawMaterials()
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
                return $mp;
            });

        // Preparar datos para JavaScript
        $materias_primas_json = $materias_primas->map(function($mp) {
            $available = $mp->calculated_available_quantity ?? ($mp->cantidad_disponible ?? 0);
            return [
                'material_id' => $mp->material_id,
                'name' => $mp->nombre,
                'unit_code' => $mp->unit->codigo ?? 'N/A',
                'available' => (float)$available // Pasar como número, no como string formateado
            ];
        });

        $procesos = Process::where('activo', true)->get();

        return view('gestion-lotes', compact('lotes', 'stats', 'pedidos', 'materias_primas', 'materias_primas_json', 'procesos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'pedido_id' => 'nullable|integer|exists:pedido_cliente,pedido_id',
            'target_quantity' => 'nullable|numeric|min:0',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'raw_materials.*.planned_quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Validar que el pedido exista si se proporciona
            if ($request->pedido_id) {
                $order = CustomerOrder::find($request->pedido_id);
                if (!$order) {
                    throw new \Exception('El pedido especificado no existe');
                }
            }

            // Si no hay pedido_id, crear un pedido genérico o usar uno por defecto
            $orderId = $request->pedido_id;
            if (!$orderId) {
                // Sincronizar secuencia y obtener el siguiente ID para order
                $maxOrderId = DB::table('pedido_cliente')->max('pedido_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxOrderId !== null && $maxOrderId > 0) {
                    DB::statement("SELECT setval('pedido_cliente_seq', {$maxOrderId}, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $orderNextId = DB::selectOne("SELECT nextval('pedido_cliente_seq') as id")->id;
                $orderNumber = 'INTERNO-' . str_pad($orderNextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
                
                // Crear un pedido genérico interno usando SQL directo
                $orderId = DB::selectOne("
                    INSERT INTO pedido_cliente (pedido_id, numero_pedido, cliente_id, fecha_creacion, estado, descripcion)
                    VALUES (?, ?, ?, ?, ?, ?)
                    RETURNING pedido_id
                ", [
                    $orderNextId,
                    $orderNumber,
                    1, // Cliente por defecto
                    now()->toDateString(),
                    'pendiente',
                    'Pedido interno generado automáticamente'
                ])->pedido_id;
            }

            // Sincronizar secuencia y obtener el siguiente ID para batch
            $maxBatchId = DB::table('lote_produccion')->max('lote_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            if ($maxBatchId !== null && $maxBatchId > 0) {
                DB::statement("SELECT setval('lote_produccion_seq', {$maxBatchId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $batchNextId = DB::selectOne("SELECT nextval('lote_produccion_seq') as id")->id;
            
            // Generar código de lote automáticamente
            $batchCode = 'LOTE-' . str_pad($batchNextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');

            // Crear batch usando SQL directo
            $batchId = DB::selectOne("
                INSERT INTO lote_produccion (lote_id, pedido_id, codigo_lote, nombre, fecha_creacion, cantidad_objetivo, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                RETURNING lote_id
            ", [
                $batchNextId,
                $orderId,
                $batchCode,
                $request->name ?? 'Unnamed Batch',
                now()->toDateString(),
                $request->target_quantity,
                $request->observations
            ])->lote_id;
            
            $batch = ProductionBatch::find($batchId);

            // Crear batch raw materials - buscar o crear instancias de RawMaterial
            foreach ($request->raw_materials as $rm) {
                $materialBase = RawMaterialBase::with('rawMaterials')->findOrFail($rm['material_id']);
                
                // Calcular cantidad disponible dinámicamente desde las materias primas recibidas
                $calculatedAvailable = $materialBase->rawMaterials
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
                
                // Si no hay materias primas recibidas, usar el valor almacenado
                if ($calculatedAvailable == 0 && $materialBase->rawMaterials->count() == 0) {
                    $calculatedAvailable = $materialBase->cantidad_disponible ?? 0;
                }
                
                // Verificar disponibilidad
                if ($calculatedAvailable < $rm['planned_quantity']) {
                    throw new \Exception("No hay suficiente cantidad disponible de {$materialBase->nombre}. Disponible: {$calculatedAvailable}");
                }

                // Buscar una instancia de RawMaterial disponible para esta materia prima base
                $rawMaterial = \App\Models\RawMaterial::where('material_id', $rm['material_id'])
                    ->where('cantidad_disponible', '>=', $rm['planned_quantity'])
                    ->orderBy('fecha_recepcion', 'asc') // FIFO
                    ->first();

                if (!$rawMaterial) {
                    // Si no hay instancia disponible, crear una genérica o lanzar error
                    throw new \Exception("No hay materia prima recibida disponible para {$materialBase->nombre}. Debe recibir materia prima primero.");
                }

                // Sincronizar secuencia y crear batch raw material
                $maxBatchMaterialId = DB::table('lote_materia_prima')->max('lote_material_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxBatchMaterialId !== null && $maxBatchMaterialId > 0) {
                    DB::statement("SELECT setval('lote_materia_prima_seq', {$maxBatchMaterialId}, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $batchMaterialNextId = DB::selectOne("SELECT nextval('lote_materia_prima_seq') as id")->id;
                
                $batchMaterialId = DB::selectOne("
                    INSERT INTO lote_materia_prima (lote_material_id, lote_id, materia_prima_id, cantidad_planificada, cantidad_usada)
                    VALUES (?, ?, ?, ?, ?)
                    RETURNING lote_material_id
                ", [
                    $batchMaterialNextId,
                    $batch->lote_id,
                    $rawMaterial->materia_prima_id,
                    $rm['planned_quantity'],
                    0
                ])->lote_material_id;

                // Descontar de la materia prima base
                $materialBase->cantidad_disponible -= $rm['planned_quantity'];
                $materialBase->save();

                // Descontar de la instancia de raw material
                $rawMaterial->cantidad_disponible -= $rm['planned_quantity'];
                $rawMaterial->save();

                // Sincronizar secuencia y registrar en log de movimientos
                $maxLogId = DB::table('registro_movimiento_material')->max('registro_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxLogId !== null && $maxLogId > 0) {
                    DB::statement("SELECT setval('registro_movimiento_material_seq', {$maxLogId}, true)");
                }
                
                // Obtener el siguiente ID del log
                $logNextId = DB::selectOne("SELECT nextval('registro_movimiento_material_seq') as id")->id;
                
                DB::selectOne("
                    INSERT INTO registro_movimiento_material (registro_id, material_id, tipo_movimiento_id, operador_id, cantidad, saldo_anterior, saldo_nuevo, descripcion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    RETURNING registro_id
                ", [
                    $logNextId,
                    $rm['material_id'],
                    2, // Salida
                    auth()->id(),
                    $rm['planned_quantity'],
                    $materialBase->cantidad_disponible + $rm['planned_quantity'],
                    $materialBase->cantidad_disponible,
                    "Descuento por creación de lote (Código: {$batch->codigo_lote})"
                ]);
            }

            // Actualizar estado del pedido a "en_proceso" cuando se crea un lote
            if ($request->pedido_id) {
                $pedido = \App\Models\CustomerOrder::find($request->pedido_id);
                if ($pedido && $pedido->estado == 'pendiente') {
                    $pedido->update(['estado' => 'en_proceso']);
                }
            }

            DB::commit();

            return redirect()->route('gestion-lotes')
                ->with('success', 'Lote creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear el lote: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $lote = ProductionBatch::with([
                'order.customer',
                'rawMaterials.rawMaterial.materialBase.unit',
                'rawMaterials.rawMaterial.supplier',
                'processMachineRecords.processMachine.machine',
                'finalEvaluation.inspector',
                'storage'
            ])->findOrFail($id);
            
            return response()->json([
                'batch_id' => $lote->lote_id,
                'batch_code' => $lote->codigo_lote,
                'name' => $lote->nombre,
                'order_id' => $lote->pedido_id,
                'order_number' => $lote->order->numero_pedido ?? null,
                'order_name' => $lote->order->nombre ?? null,
                'customer_name' => $lote->order->customer->razon_social ?? null,
                'creation_date' => $lote->fecha_creacion,
                'start_time' => $lote->hora_inicio,
                'end_time' => $lote->hora_fin,
                'target_quantity' => $lote->cantidad_objetivo,
                'produced_quantity' => $lote->cantidad_producida,
                'observations' => $lote->observaciones,
                'raw_materials' => $lote->rawMaterials->map(function($rm) {
                    return [
                        'material_name' => $rm->rawMaterial->materialBase->nombre ?? 'N/A',
                        'unit' => $rm->rawMaterial->materialBase->unit->codigo ?? 'N/A',
                        'supplier' => $rm->rawMaterial->supplier->razon_social ?? 'N/A',
                        'planned_quantity' => $rm->cantidad_planificada,
                        'used_quantity' => $rm->cantidad_usada,
                    ];
                }),
                'evaluation' => $lote->finalEvaluation->first() ? [
                    'reason' => $lote->finalEvaluation->first()->razon,
                    'observations' => $lote->finalEvaluation->first()->observaciones,
                    'evaluation_date' => $lote->finalEvaluation->first()->fecha_evaluacion,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }
    }

    public function edit($id)
    {
        try {
            $lote = ProductionBatch::with([
                'order.customer',
                'rawMaterials.rawMaterial.materialBase.unit',
            ])->findOrFail($id);
            
            return response()->json([
                'batch_id' => $lote->lote_id,
                'name' => $lote->nombre,
                'order_id' => $lote->pedido_id,
                'target_quantity' => $lote->cantidad_objetivo,
                'observations' => $lote->observaciones,
                'raw_materials' => $lote->rawMaterials->map(function($rm) {
                    return [
                        'material_id' => $rm->rawMaterial->material_id,
                        'planned_quantity' => $rm->cantidad_planificada,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'pedido_id' => 'nullable|integer|exists:pedido_cliente,pedido_id',
            'target_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $lote = ProductionBatch::findOrFail($id);
            
            // Solo permitir editar si el lote no ha comenzado
            if ($lote->hora_inicio) {
                throw new \Exception('No se puede editar un lote que ya ha comenzado su producción');
            }

            $lote->update([
                'nombre' => $request->name,
                'pedido_id' => $request->pedido_id ?? $lote->pedido_id,
                'cantidad_objetivo' => $request->target_quantity,
                'observaciones' => $request->observations,
            ]);

            DB::commit();

            return redirect()->route('gestion-lotes')
                ->with('success', 'Lote actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar el lote: ' . $e->getMessage())
                ->withInput();
        }
    }
}

