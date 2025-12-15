<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachineRecord;
use App\Models\ProcessFinalEvaluation;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CertificarLoteController extends Controller
{
    public function index()
    {
        // Mostrar todos los lotes que pueden ser certificados
        // Incluye lotes sin registros de proceso (para que puedan ir a proceso-transformacion primero)
        // y lotes con registros pero sin certificar
        $lotes = ProductionBatch::with([
            'order.customer', 
            'processMachineRecords.processMachine.process',
            'latestFinalEvaluation'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return view('certificar-lote', compact('lotes'));
    }

    public function finalizar($loteId)
    {
        DB::beginTransaction();
        try {
            $batch = ProductionBatch::findOrFail($loteId);

            // ✅ 1. Obtener el proceso del lote a través de los registros existentes
            $records = ProcessMachineRecord::where('lote_id', $loteId)
                ->with('processMachine.process')
                ->get();

            if ($records->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'El lote no tiene registros de proceso. Debe registrar formularios primero.');
            }

            // Obtener el proceso_id del primer registro (todos deben ser del mismo proceso)
            $firstRecord = $records->first();
            if (!$firstRecord->processMachine || !$firstRecord->processMachine->proceso_id) {
                return redirect()->back()
                    ->with('error', 'No se pudo identificar el proceso del lote.');
            }

            $procesoId = $firstRecord->processMachine->proceso_id;

            // Verificar que todos los registros sean del mismo proceso
            $procesoIds = $records->pluck('processMachine.proceso_id')->unique()->filter();
            if ($procesoIds->count() > 1) {
                return redirect()->back()
                    ->with('error', 'El lote tiene registros de múltiples procesos. Esto no es válido.');
            }

            // ✅ 2. Obtener cantidad real de máquinas del proceso asignado al lote (como en proyecto antiguo)
            $processMachines = ProcessMachine::where('proceso_id', $procesoId)
                ->orderBy('orden_paso')
                ->get();
            
            $expectedCount = $processMachines->count();
            $actualCount = $records->count();

            if ($actualCount < $expectedCount) {
                return redirect()->back()
                    ->with('error', "Faltan formularios. Solo hay {$actualCount} de {$expectedCount} máquinas.");
            }

            // ✅ 3. Evaluar si alguna máquina falló
            $failed = $records->firstWhere('cumple_estandar', false);
            $status = $failed ? 'No Certificado' : 'Certificado';
            
            $machineName = 'N/A';
            if ($failed && $failed->processMachine) {
                $machineName = $failed->processMachine->nombre;
            }
            
            $reason = $failed 
                ? "Falló en la máquina {$machineName}"
                : 'Todas las máquinas cumplen los valores estándar';

            // ✅ 4. Guardar evaluación final
            $existingEvaluation = ProcessFinalEvaluation::where('lote_id', $loteId)->first();
            
            if ($existingEvaluation) {
                // Actualizar evaluación existente
                $existingEvaluation->update([
                    'inspector_id' => Auth::id(),
                    'razon' => $reason,
                    'observaciones' => request('observaciones'),
                    'fecha_evaluacion' => now(),
                ]);
            } else {
                // Obtener el siguiente ID de la secuencia
                $maxId = DB::table('evaluacion_final_proceso')->max('evaluacion_id') ?? 0;
                if ($maxId > 0) {
                    DB::statement("SELECT setval('evaluacion_final_proceso_seq', {$maxId}, true)");
                }
                $nextId = DB::selectOne("SELECT nextval('evaluacion_final_proceso_seq') as id")->id;
                
                // Crear evaluación final
                ProcessFinalEvaluation::create([
                    'evaluacion_id' => $nextId,
                    'lote_id' => $loteId,
                    'inspector_id' => Auth::id(),
                    'razon' => $reason,
                    'observaciones' => request('observaciones'),
                    'fecha_evaluacion' => now(),
                ]);
            }

            // Actualizar estado del pedido si el lote fue certificado
            if ($status === 'Certificado') {
                $batch = ProductionBatch::findOrFail($loteId);
                if ($batch->pedido_id) {
                    $pedido = \App\Models\CustomerOrder::find($batch->pedido_id);
                    if ($pedido) {
                        // Solo actualizar si no está ya en un estado más avanzado
                        if (in_array($pedido->estado, ['pendiente', 'en_proceso'])) {
                            $pedido->update(['estado' => 'produccion_finalizada']);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('certificados')
                ->with('success', $status . ' - El proceso ha sido finalizado');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al finalizar proceso: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el log completo del proceso (similar al proyecto antiguo)
     */
    public function obtenerLog($loteId)
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.machine',
                'processMachineRecords.processMachine.process',
                'finalEvaluation.inspector'
            ])->findOrFail($loteId);

            if (!$batch->finalEvaluation) {
                return response()->json([
                    'message' => 'El lote aún no ha sido evaluado'
                ], 404);
            }

            // Obtener registros de máquinas ordenados por orden_paso
            $records = ProcessMachineRecord::where('lote_id', $loteId)
                ->with(['processMachine.machine', 'processMachine.process', 'operator'])
                ->get()
                ->sortBy(function($record) {
                    return $record->processMachine ? $record->processMachine->orden_paso : 999;
                })
                ->values();

            // Formatear máquinas similar al proyecto antiguo
            $maquinas = $records->map(function($record) {
                return [
                    'NumeroMaquina' => $record->processMachine ? $record->processMachine->orden_paso : null,
                    'NombreMaquina' => $record->processMachine ? $record->processMachine->nombre : 'N/A',
                    'VariablesIngresadas' => $record->variables_ingresadas ?? [],
                    'CumpleEstandar' => $record->cumple_estandar ?? false,
                    'FechaRegistro' => $record->fecha_registro ? $record->fecha_registro->toDateTimeString() : null,
                ];
            });

            // Formatear resultado final
            $resultadoFinal = [
                'EstadoFinal' => str_contains(strtolower($batch->finalEvaluation->razon ?? ''), 'falló') 
                    ? 'No Certificado' 
                    : 'Certificado',
                'Motivo' => $batch->finalEvaluation->razon ?? 'N/A',
                'FechaEvaluacion' => $batch->finalEvaluation->fecha_evaluacion 
                    ? $batch->finalEvaluation->fecha_evaluacion->toDateTimeString() 
                    : null,
                'Inspector' => $batch->finalEvaluation->inspector 
                    ? $batch->finalEvaluation->inspector->nombre 
                    : 'N/A',
            ];

            return response()->json([
                'Maquinas' => $maquinas,
                'ResultadoFinal' => $resultadoFinal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener log: ' . $e->getMessage()
            ], 500);
        }
    }
}

