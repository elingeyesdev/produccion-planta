<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessMachineRecord;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessMachineRecordRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessMachineRecordResource;
use Illuminate\Support\Facades\DB;

class ProcessMachineRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processMachineRecords = ProcessMachineRecord::paginate();

        return ProcessMachineRecordResource::collection($processMachineRecords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessMachineRecordRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $operator = auth()->user();
            $processMachine = \App\Models\ProcessMachine::with('variables.standardVariable', 'process')
                ->findOrFail($request->process_machine_id);

            // Validate that if there are other records, they are from the same process
            $existingRecords = ProcessMachineRecord::where('batch_id', $request->batch_id)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.process_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($processMachine->process_id)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Esta máquina pertenece a un proceso diferente al ya registrado en este lote'
                    ], 400);
                }
            }

            // Validate sequential order: verify that previous machines are completed
            $allProcessMachines = \App\Models\ProcessMachine::where('process_id', $processMachine->process_id)
                ->orderBy('step_order')
                ->get();
            
            $currentStep = $processMachine->step_order;
            $previousMachines = $allProcessMachines->where('step_order', '<', $currentStep);
            
            foreach ($previousMachines as $prevMachine) {
                $prevRecord = $existingRecords->firstWhere('process_machine_id', $prevMachine->process_machine_id);
                if (!$prevRecord) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Debe completar la máquina '{$prevMachine->name}' (paso {$prevMachine->step_order}) antes de continuar"
                    ], 400);
                }
            }

            // Validate variables
            $enteredVariables = $request->entered_variables ?? [];
            $meetsStandard = true;

            foreach ($processMachine->variables as $variable) {
                $varName = $variable->standardVariable->code ?? $variable->standardVariable->name;
                $enteredValue = $enteredVariables[$varName] ?? null;

                if ($variable->mandatory && $enteredValue === null) {
                    $meetsStandard = false;
                    break;
                }

                if ($enteredValue !== null) {
                    // Only validate min_value if it's set
                    if ($variable->min_value !== null && $enteredValue < $variable->min_value) {
                        $meetsStandard = false;
                        break;
                    }
                    // Only validate max_value if it's set
                    if ($variable->max_value !== null && $enteredValue > $variable->max_value) {
                        $meetsStandard = false;
                        break;
                    }
                }
            }

            // Check if record already exists
            $existingRecord = ProcessMachineRecord::where('batch_id', $request->batch_id)
                ->where('process_machine_id', $request->process_machine_id)
                ->first();

            if ($existingRecord) {
                // Update existing record
                $existingRecord->update([
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => $enteredVariables,
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => now(),
                    'end_time' => now(),
                    'record_date' => now(),
                ]);
                
                DB::commit();
                return response()->json(new \App\Http\Resources\ProcessMachineRecordResource($existingRecord));
            } else {
                // Create new record
                $maxId = ProcessMachineRecord::max('record_id') ?? 0;
                $nextId = $maxId + 1;
                
                $processMachineRecord = ProcessMachineRecord::create([
                    'record_id' => $nextId,
                    'batch_id' => $request->batch_id,
                    'process_machine_id' => $request->process_machine_id,
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => $enteredVariables,
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => now(),
                    'end_time' => now(),
                    'record_date' => now(),
                ]);

                DB::commit();
                return response()->json(new \App\Http\Resources\ProcessMachineRecordResource($processMachineRecord), 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcessMachineRecord $processMachineRecord): JsonResponse
    {
        return response()->json(new ProcessMachineRecordResource($processMachineRecord));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessMachineRecordRequest $request, ProcessMachineRecord $processMachineRecord): JsonResponse
    {
        $processMachineRecord->update($request->validated());

        return response()->json(new ProcessMachineRecordResource($processMachineRecord));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(ProcessMachineRecord $processMachineRecord): Response
    {
        $processMachineRecord->delete();

        return response()->noContent();
    }
}