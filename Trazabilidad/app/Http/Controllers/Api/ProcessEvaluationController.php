<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachineRecord;
use App\Models\ProcessFinalEvaluation;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProcessEvaluationController extends Controller
{
    /**
     * Finalize and evaluate a batch process
     */
    public function finalize(Request $request, $batchId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
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

            // Get all process machines for this batch's process
            // You may need to adjust this based on how processes are linked to batches
            $processMachines = ProcessMachine::whereHas('records', function($query) use ($batchId) {
                $query->where('batch_id', $batchId);
            })->get();

            $expectedCount = $processMachines->count();
            $records = ProcessMachineRecord::where('batch_id', $batchId)->get();
            $actualCount = $records->count();

            if ($actualCount < $expectedCount) {
                DB::rollBack();
                return response()->json([
                    'message' => "Faltan formularios. Solo hay {$actualCount} de {$expectedCount} máquinas.",
                ], 400);
            }

            // Check if any machine failed
            $failed = $records->firstWhere('meets_standard', false);
            $status = $failed ? 'No Certificado' : 'Certificado';
            $machineName = $failed && $failed->processMachine ? $failed->processMachine->name : 'N/A';
            $reason = $failed 
                ? "Falló en la máquina {$machineName}"
                : ($request->reason ?? 'Todas las máquinas cumplen los valores estándar');

            // Create or update final evaluation
            $evaluation = ProcessFinalEvaluation::updateOrCreate(
                ['batch_id' => $batchId],
                [
                    'inspector_id' => auth()->id(),
                    'reason' => $reason,
                    'observations' => $request->observations,
                    'evaluation_date' => now(),
                ]
            );

            DB::commit();

            return response()->json([
                'message' => $status . ' el proceso ha sido finalizado',
                'motivo' => $reason,
                'evaluation_id' => $evaluation->evaluation_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluation log for a batch
     */
    public function getLog($batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.machine',
                'processMachineRecords.operator',
                'finalEvaluation.inspector',
                'rawMaterials.rawMaterial.materialBase'
            ])->findOrFail($batchId);

            $evaluation = $batch->finalEvaluation()->first();
            
            if (!$evaluation) {
                return response()->json([
                    'message' => 'El lote aún no ha sido evaluado'
                ], 404);
            }

            return response()->json([
                'batch' => $batch,
                'evaluation' => $evaluation,
                'records' => $batch->processMachineRecords,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

