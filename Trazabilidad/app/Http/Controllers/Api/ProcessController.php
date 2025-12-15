<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessResource;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Process::query();
        
        // Optionally include process machines
        if ($request->query('include') === 'machines') {
            $query->with(['processMachines.machine']);
        }
        
        $processes = $query->paginate();

        return ProcessResource::collection($processes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            \Log::info('Process creation - validated data', ['data' => $data]);
            
            // Manual ID generation if not auto-increment
            $maxId = Process::max('proceso_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Map English field names to Spanish column names
            $processData = [
                'proceso_id' => $nextId,
                'codigo' => $data['code'] ?? 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT),
                'nombre' => $data['name'],
                'descripcion' => $data['description'] ?? null,
                'activo' => $data['active'] ?? true,
            ];

            // Extract process machines data
            $processMachinesData = $data['process_machines'] ?? [];

            // Create the process
            $process = Process::create($processData);

            // Create process machines if provided
            if (!empty($processMachinesData)) {
                foreach ($processMachinesData as $index => $machineData) {
                    $maxMachineId = ProcessMachine::max('proceso_maquina_id') ?? 0;
                    
                    // Map English field names to Spanish column names
                    $processMachineData = [
                        'proceso_maquina_id' => $maxMachineId + 1,
                        'proceso_id' => $process->proceso_id,
                        'maquina_id' => $machineData['machine_id'],
                        'orden_paso' => $machineData['step_order'] ?? ($index + 1),
                        'nombre' => $machineData['name'],
                        'descripcion' => $machineData['description'] ?? null,
                        'tiempo_estimado' => $machineData['estimated_time'] ?? null,
                    ];
                    
                    // Extract variables data
                    $variablesData = $machineData['variables'] ?? [];
                    
                    // Create process machine
                    $processMachine = ProcessMachine::create($processMachineData);
                    
                    // Create process machine variables if provided
                    \Log::info('Variables data for machine ' . $processMachine->proceso_maquina_id, ['variables' => $variablesData]);
                    
                    if (!empty($variablesData)) {
                        foreach ($variablesData as $variableData) {
                            $maxVariableId = \App\Models\ProcessMachineVariable::max('variable_id') ?? 0;
                            
                            // Map English field names to Spanish column names
                            $variableRecord = [
                                'variable_id' => $maxVariableId + 1,
                                'proceso_maquina_id' => $processMachine->proceso_maquina_id,
                                'variable_estandar_id' => $variableData['standard_variable_id'],
                                'valor_minimo' => $variableData['min_value'] ?? null,
                                'valor_maximo' => $variableData['max_value'] ?? null,
                                'valor_objetivo' => $variableData['target_value'] ?? null,
                                'obligatorio' => $variableData['mandatory'] ?? true,
                            ];
                            
                            \Log::info('Creating variable', ['data' => $variableRecord]);
                            $createdVar = \App\Models\ProcessMachineVariable::create($variableRecord);
                            \Log::info('Variable created', ['id' => $createdVar->variable_id]);
                        }
                    }
                }
            }

            DB::commit();

            // Load relationships for response
            $process->load(['processMachines.machine']);

            return response()->json(new ProcessResource($process), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Process creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to create process: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Process $process): JsonResponse
    {
        // Eager load process machines with their associated machines
        $process->load(['processMachines.machine']);
        
        return response()->json(new ProcessResource($process));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessRequest $request, Process $process): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Extract process machines data
            $processMachinesData = $data['process_machines'] ?? null;
            
            // Map English field names to Spanish column names for process update
            $processUpdateData = [
                'nombre' => $data['name'],
                'descripcion' => $data['description'] ?? null,
                'activo' => $data['active'] ?? true,
            ];
            
            // Update the process
            $process->update($processUpdateData);

            // Handle process machines update if provided
            if ($processMachinesData !== null) {
                // Delete existing process machines (and their variables via cascade)
                ProcessMachine::where('proceso_id', $process->proceso_id)->delete();
                
                // Create new process machines
                foreach ($processMachinesData as $index => $machineData) {
                    $maxMachineId = ProcessMachine::max('proceso_maquina_id') ?? 0;
                    
                    // Map English to Spanish column names
                    $processMachineData = [
                        'proceso_maquina_id' => $maxMachineId + 1,
                        'proceso_id' => $process->proceso_id,
                        'maquina_id' => $machineData['machine_id'],
                        'orden_paso' => $machineData['step_order'] ?? ($index + 1),
                        'nombre' => $machineData['name'],
                        'descripcion' => $machineData['description'] ?? null,
                        'tiempo_estimado' => $machineData['estimated_time'] ?? null,
                    ];
                    
                    // Extract variables data
                    $variablesData = $machineData['variables'] ?? [];
                    
                    $processMachine = ProcessMachine::create($processMachineData);
                    
                    // Create process machine variables if provided
                    if (!empty($variablesData)) {
                        foreach ($variablesData as $variableData) {
                            $maxVariableId = \App\Models\ProcessMachineVariable::max('variable_id') ?? 0;
                            
                            $variableRecord = [
                                'variable_id' => $maxVariableId + 1,
                                'proceso_maquina_id' => $processMachine->proceso_maquina_id,
                                'variable_estandar_id' => $variableData['standard_variable_id'],
                                'valor_minimo' => $variableData['min_value'] ?? null,
                                'valor_maximo' => $variableData['max_value'] ?? null,
                                'valor_objetivo' => $variableData['target_value'] ?? null,
                                'obligatorio' => $variableData['mandatory'] ?? true,
                            ];
                            
                            \App\Models\ProcessMachineVariable::create($variableRecord);
                        }
                    }
                }
            }

            DB::commit();

            // Reload relationships
            $process->load(['processMachines.machine']);

            return response()->json(new ProcessResource($process));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Process update failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update process: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Process $process): Response
    {
        try {
            DB::beginTransaction();
            
            // Delete associated process machines first
            ProcessMachine::where('process_id', $process->process_id)->delete();
            
            // Delete the process
            $process->delete();
            
            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete process: ' . $e->getMessage()], 500);
        }
    }
}