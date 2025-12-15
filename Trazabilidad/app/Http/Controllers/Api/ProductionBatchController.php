<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\BatchRawMaterial;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use App\Http\Requests\ProductionBatchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductionBatchResource;

class ProductionBatchController extends Controller
{
    /**
     * List all production batches
     */
    public function index(Request $request)
    {
        $batches = ProductionBatch::with([
            'order.customer', 
            'rawMaterials.rawMaterial.materialBase',
            'finalEvaluation',
            'processMachineRecords.operator'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->orderBy('lote_id', 'desc')
            ->paginate($request->get('per_page', 15));

        return ProductionBatchResource::collection($batches);
    }

    /**
     * Get batch by ID
     */
    public function show(ProductionBatch $productionBatch): JsonResponse
    {
        // Only load relationships for tables that exist
        $productionBatch->load([
            'order.customer',
            'rawMaterials.rawMaterial.materialBase',
            'finalEvaluation',
            // Commented out until these tables are created:
            // 'processMachineRecords.processMachine.machine',
            // 'finalEvaluation.inspector',
            // 'storage'
        ]);

        return response()->json(new ProductionBatchResource($productionBatch));
    }

    /**
     * Create a new production batch
     */
    public function store(ProductionBatchRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:pedido_cliente,pedido_id',
            'name' => 'nullable|string|max:100',
            'target_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.raw_material_id' => 'required|integer|exists:materia_prima,materia_prima_id',
            'raw_materials.*.planned_quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos o inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get the next ID manually since sequence doesn't exist
            // Get the next ID manually since sequence doesn't exist
            $maxId = ProductionBatch::max('lote_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Generar código de lote automáticamente
            $batchCode = 'LOTE-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $batch = ProductionBatch::create([
                'lote_id' => $nextId,
                'pedido_id' => $request->order_id,
                'codigo_lote' => $batchCode,
                'nombre' => $request->name ?? 'Unnamed Batch',
                'fecha_creacion' => now()->toDateString(),
                'cantidad_objetivo' => $request->target_quantity,
                'observaciones' => $request->observations,
            ]);

            // Create batch raw materials
            if ($request->has('raw_materials')) {
                foreach ($request->raw_materials as $rm) {
                    // Get the next ID manually
                    $maxBatchMaterialId = BatchRawMaterial::max('lote_material_id') ?? 0;
                    $batchMaterialId = $maxBatchMaterialId + 1;
                    
                    BatchRawMaterial::create([
                        'lote_material_id' => $batchMaterialId,
                        'lote_id' => $batch->lote_id,
                        'materia_prima_id' => $rm['raw_material_id'],
                        'cantidad_planificada' => $rm['planned_quantity'],
                        'cantidad_usada' => 0,
                    ]);
                }
            }

            // Update order status
            $this->updateOrderStatus($request->order_id);

            DB::commit();

            return response()->json([
                'message' => 'Lote de producción creado exitosamente',
                'batch_id' => $batch->lote_id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear lote de producción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update production batch
     */
    public function update(ProductionBatchRequest $request, ProductionBatch $productionBatch): JsonResponse
    {
        $productionBatch->update($request->validated());

        return response()->json(new ProductionBatchResource($productionBatch));
    }

    /**
     * Delete production batch
     */
    public function destroy(ProductionBatch $productionBatch)
    {
        DB::beginTransaction();
        try {
            // Delete related records manually to avoid foreign key constraints
            $productionBatch->rawMaterials()->delete();
            $productionBatch->processMachineRecords()->delete();
            $productionBatch->finalEvaluation()->delete();
            $productionBatch->storage()->delete();

            $orderId = $productionBatch->pedido_id;
            
            $productionBatch->delete();

            // Update parent order status
            $this->updateOrderStatus($orderId);

            DB::commit();
            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            // Return error as JSON response with 500 status, but since return type is Response, 
            // we might need to change return type or just return response()->json(...)
            // However, the signature says : Response. Let's change it to match what Laravel expects or just return response
            return response()->json(['message' => 'Error al eliminar lote: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get batches pending certification
     */
    public function getPendingCertification(Request $request): JsonResponse
    {
        $batches = ProductionBatch::with([
            'order.customer',
            'processMachineRecords.processMachine.process',
           'finalEvaluation'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return response()->json(ProductionBatchResource::collection($batches));
    }

    /**
     * Assign a process to a batch
     */
    public function assignProcess(Request $request, $batchId): JsonResponse
    {
        // Accept both process_id and proceso_id from frontend
        $processId = $request->process_id ?? $request->proceso_id;
        
        $validator = Validator::make(['proceso_id' => $processId], [
            'proceso_id' => 'required|integer|exists:proceso,proceso_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $batch = ProductionBatch::findOrFail($batchId);
            
            // Verify no records from another process exist
            $existingRecords = \App\Models\ProcessMachineRecord::where('lote_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.proceso_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($processId)) {
                    return response()->json([
                        'message' => 'Este lote ya tiene registros de otro proceso'
                    ], 400);
                }
            }
            
            // Verify process has machines
            $processMachines = \App\Models\ProcessMachine::with([
                'machine',
                'variables.standardVariable',
                'process'
            ])
                ->where('proceso_id', $processId)
                ->orderBy('orden_paso')
                ->get();
                
            if ($processMachines->isEmpty()) {
                return response()->json([
                    'message' => 'El proceso seleccionado no tiene máquinas configuradas'
                ], 400);
            }

            // Update batch start time if not set
            if (!$batch->hora_inicio) {
                $batch->update([
                    'hora_inicio' => now(),
                ]);
            }
            
            // Return success with process machines
            return response()->json([
                'message' => 'Proceso asignado exitosamente',
                'process_id' => $processId,
                'process_machines' => $processMachines,
                'completed_records' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al asignar proceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get process machines for a batch
     */
    public function getProcessMachines(Request $request, $batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.process'
            ])->findOrFail($batchId);

            // Get process_id from query parameter, existing records, or return empty
            $processId = $request->query('process_id');
            
            if (!$processId && $batch->processMachineRecords->isNotEmpty()) {
                $firstRecord = $batch->processMachineRecords->first();
                if ($firstRecord->processMachine) {
                    $processId = $firstRecord->processMachine->proceso_id;
                }
            }

            if (!$processId) {
                return response()->json([
                    'process_machines' => [],
                    'completed_records' => []
                ]);
            }

            // Get all machines for the process
            $processMachines = \App\Models\ProcessMachine::with([
                'machine',
                'variables.standardVariable',
                'process'
            ])
                ->where('proceso_id', $processId)
                ->orderBy('orden_paso')
                ->get();

            // Get completed records
            $completedRecords = $batch->processMachineRecords->pluck('proceso_maquina_id')->toArray();

            return response()->json([
                'process_machines' => $processMachines,
                'completed_records' => $completedRecords,
                'process_id' => $processId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener máquinas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize batch certification
     */
    public function finalizeCertification(Request $request, $batchId): JsonResponse
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

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::findOrFail($batchId);

            // Get process records
            $records = \App\Models\ProcessMachineRecord::where('lote_id', $batchId)
                ->with('processMachine.process')
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'message' => 'El lote no tiene registros de proceso'
                ], 400);
            }

            // Get process_id
            $firstRecord = $records->first();
            if (!$firstRecord->processMachine || !$firstRecord->processMachine->proceso_id) {
                return response()->json([
                    'message' => 'No se pudo identificar el proceso del lote'
                ], 400);
            }

            $processId = $firstRecord->processMachine->proceso_id;

            // Verify all records are from same process
            $processIds = $records->pluck('processMachine.proceso_id')->unique()->filter();
            if ($processIds->count() > 1) {
                return response()->json([
                    'message' => 'El lote tiene registros de múltiples procesos'
                ], 400);
            }

            // Get expected machine count
            $processMachines = \App\Models\ProcessMachine::where('proceso_id', $processId)
                ->orderBy('orden_paso')
                ->get();
            
            $expectedCount = $processMachines->count();
            $actualCount = $records->count();

            if ($actualCount < $expectedCount) {
                return response()->json([
                    'message' => "Faltan formularios. Solo hay {$actualCount} de {$expectedCount} máquinas"
                ], 400);
            }

            // Evaluate if any machine failed
            $failed = $records->firstWhere('cumple_estandar', false);
            $status = $failed ? 'No Certificado' : 'Certificado';
            
            $machineName = 'N/A';
            if ($failed && $failed->processMachine) {
                $machineName = $failed->processMachine->nombre;
            }
            
            $reason = $failed 
                ? "Falló en la máquina {$machineName}"
                : 'Todas las máquinas cumplen los valores estándar';

            // Save final evaluation
            $existingEvaluation = \App\Models\ProcessFinalEvaluation::where('lote_id', $batchId)->first();
            
            if ($existingEvaluation) {
                $existingEvaluation->update([
                    'inspector_id' => auth()->id(),
                    'razon' => $reason,
                    'observaciones' => $request->observations,
                    'fecha_evaluacion' => now(),
                ]);
            } else {
                $maxId = \App\Models\ProcessFinalEvaluation::max('evaluacion_id') ?? 0;
                $nextId = $maxId + 1;
                
                \App\Models\ProcessFinalEvaluation::create([
                    'evaluacion_id' => $nextId,
                    'lote_id' => $batchId,
                    'inspector_id' => auth()->id(),
                    'razon' => $reason,
                    'observaciones' => $request->observations,
                    'fecha_evaluacion' => now(),
                ]);
            }

            // Update batch status (set end time)
            $updated = $batch->update([
                'hora_fin' => now(),
            ]);
            
            \Illuminate\Support\Facades\Log::info("Batch {$batchId} finalized. Update result: " . ($updated ? 'true' : 'false'));
            \Illuminate\Support\Facades\Log::info("Batch {$batchId} fresh data: " . json_encode($batch->fresh()));

            // Update parent order status
            $this->updateOrderStatus($batch->pedido_id);

            DB::commit();

            return response()->json([
                'message' => $status . ' - El proceso ha sido finalizado',
                'status' => $status,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al finalizar proceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get certification log for a batch
     */
    public function getCertificationLog($batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.machine',
                'processMachineRecords.processMachine.process',
                'finalEvaluation.inspector'
            ])->findOrFail($batchId);

            // Get the final evaluation (it's a collection, so get first)
            $finalEvaluation = $batch->finalEvaluation->first();
            
            if (!$finalEvaluation) {
                return response()->json([
                    'message' => 'El lote aún no ha sido evaluado'
                ], 404);
            }

            // Get records ordered by step_order
            $records = $batch->processMachineRecords->sortBy(function($record) {
                return $record->processMachine ? $record->processMachine->orden_paso : 999;
            })->values();

            // Format machines
            $machines = $records->map(function($record) {
                return [
                    'orden_paso' => $record->processMachine ? $record->processMachine->orden_paso : null,
                    'nombre_maquina' => $record->processMachine ? $record->processMachine->nombre : 'N/A',
                    'variables_registradas' => $record->variables_ingresadas ?? [],
                    'cumple_estandar' => $record->cumple_estandar ?? false,
                    'fecha_registro' => $record->fecha_registro ? $record->fecha_registro->toDateTimeString() : null,
                ];
            });

            // Format final result
            $finalResult = [
                'estado' => str_contains(strtolower($finalEvaluation->razon ?? ''), 'falló') 
                    ? 'No Certificado' 
                    : 'Certificado',
                'razon' => $finalEvaluation->razon ?? 'N/A',
                'fecha_evaluacion' => $finalEvaluation->fecha_evaluacion 
                    ? $finalEvaluation->fecha_evaluacion->toDateTimeString() 
                    : null,
                'inspector' => $finalEvaluation->inspector 
                    ? $finalEvaluation->inspector->nombre 
                    : 'N/A',
            ];

            return response()->json([
                'machines' => $machines,
                'final_result' => $finalResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update parent order status based on batches
     */
    private function updateOrderStatus($orderId)
    {
        $order = CustomerOrder::find($orderId);
        if (!$order) return;

        $batches = ProductionBatch::where('pedido_id', $orderId)->get();
        
        if ($batches->isEmpty()) {
            return;
        }

        $allCompleted = $batches->every(function ($batch) {
            return !is_null($batch->hora_fin);
        });

        $anyStarted = $batches->contains(function ($batch) {
            return !is_null($batch->hora_inicio) || !is_null($batch->hora_fin);
        });

        if ($allCompleted) {
            $order->update(['estado' => 'completado']);
        } elseif ($anyStarted) {
            $order->update(['estado' => 'en_produccion']);
        }
    }
}

