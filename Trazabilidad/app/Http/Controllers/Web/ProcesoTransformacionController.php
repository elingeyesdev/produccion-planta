<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachine;
use App\Models\ProcessMachineRecord;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcesoTransformacionController extends Controller
{
    public function index($batchId)
    {
        $batch = ProductionBatch::with([
            'order.customer',
            'rawMaterials.rawMaterial.materialBase',
            'processMachineRecords.processMachine.machine',
            'processMachineRecords.processMachine.process',
            'processMachineRecords.processMachine.variables.standardVariable',
            'processMachineRecords.operator'
        ])->findOrFail($batchId);

        // Obtener el proceso del lote a través de los registros existentes o sesión
        $processId = null;
        $processMachines = collect();
        $formulariosCompletados = [];
        
        // Si hay registros, obtener el proceso_id del primer registro
        if ($batch->processMachineRecords->isNotEmpty()) {
            $firstRecord = $batch->processMachineRecords->first();
            if ($firstRecord->processMachine) {
                $processId = $firstRecord->processMachine->proceso_id;
            }
        }
        
        // Si no hay registros pero hay un proceso seleccionado en sesión, usarlo
        if (!$processId) {
            $processId = session('selected_process_' . $batchId);
        }

        // Si hay un proceso identificado, obtener todas sus máquinas
        if ($processId) {
            $processMachines = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->where('proceso_id', $processId)
                ->orderBy('orden_paso')
                ->get();
            
            // Verificar qué máquinas tienen formularios completados
            foreach ($processMachines as $pm) {
                $record = $batch->processMachineRecords->firstWhere('proceso_maquina_id', $pm->proceso_maquina_id);
                $formulariosCompletados[$pm->proceso_maquina_id] = $record ? true : false;
            }
        }

        // Obtener todos los procesos disponibles para asignar
        $procesos = Process::where('activo', true)->get();

        // Calcular progreso
        $totalCompletados = count(array_filter($formulariosCompletados));
        $totalMaquinas = $processMachines->count();
        $procesoListo = $totalCompletados === $totalMaquinas && $totalMaquinas > 0;

        return view('proceso-transformacion', compact(
            'batch', 
            'processMachines', 
            'procesos', 
            'processId',
            'formulariosCompletados',
            'totalCompletados',
            'totalMaquinas',
            'procesoListo'
        ));
    }

    public function asignarProceso(Request $request, $batchId)
    {
        $validator = Validator::make($request->all(), [
            'proceso_id' => 'required|integer|exists:proceso,proceso_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $batch = ProductionBatch::findOrFail($batchId);
            
            // Verificar que no haya registros de otro proceso
            $existingRecords = ProcessMachineRecord::where('lote_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.proceso_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($request->proceso_id)) {
                    return redirect()->back()
                        ->with('error', 'Este lote ya tiene registros de otro proceso. No se puede cambiar.');
                }
            }
            
            // Verificar que el proceso tenga máquinas
            $processMachines = ProcessMachine::where('proceso_id', $request->proceso_id)->count();
            if ($processMachines === 0) {
                return redirect()->back()
                    ->with('error', 'El proceso seleccionado no tiene máquinas configuradas.');
            }
            
            // El proceso se "asigna" implícitamente cuando se registra el primer formulario
            // Guardamos el proceso_id en la sesión para que esté disponible en la vista
            session(['selected_process_' . $batchId => $request->proceso_id]);
            
            return redirect()->route('proceso-transformacion', $batchId)
                ->with('success', 'Proceso seleccionado. Puede comenzar a registrar formularios.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al asignar proceso: ' . $e->getMessage());
        }
    }

    public function registrarFormulario(Request $request, $batchId, $processMachineId)
    {
        $validator = Validator::make($request->all(), [
            'entered_variables' => 'required|array',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $operator = Auth::user();
            $processMachine = ProcessMachine::with('variables.standardVariable', 'process')
                ->findOrFail($processMachineId);

            // Validar que si hay otros registros, sean del mismo proceso
            $existingRecords = ProcessMachineRecord::where('lote_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.proceso_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($processMachine->proceso_id)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Esta máquina pertenece a un proceso diferente al ya registrado en este lote.')
                        ->withInput();
                }
            } else {
                // Si es el primer registro, guardar el proceso en sesión
                session(['selected_process_' . $batchId => $processMachine->proceso_id]);
            }

            // Validar orden secuencial: verificar que las máquinas anteriores estén completadas
            $allProcessMachines = ProcessMachine::where('proceso_id', $processMachine->proceso_id)
                ->orderBy('orden_paso')
                ->get();
            
            $currentStep = $processMachine->orden_paso;
            $previousMachines = $allProcessMachines->where('orden_paso', '<', $currentStep);
            
            foreach ($previousMachines as $prevMachine) {
                $prevRecord = $existingRecords->firstWhere('proceso_maquina_id', $prevMachine->proceso_maquina_id);
                if (!$prevRecord) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Debe completar la máquina '{$prevMachine->nombre}' (paso {$prevMachine->orden_paso}) antes de continuar.")
                        ->withInput();
                }
            }

            // Validar variables
            $enteredVariables = $request->entered_variables;
            $meetsStandard = true;

            foreach ($processMachine->variables as $variable) {
                $varName = $variable->standardVariable->codigo ?? $variable->standardVariable->nombre;
                $enteredValue = $enteredVariables[$varName] ?? null;

                if ($variable->obligatorio && $enteredValue === null) {
                    $meetsStandard = false;
                    break;
                }

                if ($enteredValue !== null) {
                    if ($enteredValue < $variable->valor_minimo || $enteredValue > $variable->valor_maximo) {
                        $meetsStandard = false;
                        break;
                    }
                }
            }

            // Buscar si ya existe un registro para esta combinación
            $existingRecord = ProcessMachineRecord::where('lote_id', $batchId)
                ->where('proceso_maquina_id', $processMachineId)
                ->first();

            if ($existingRecord) {
                // Actualizar registro existente
                $existingRecord->update([
                    'operador_id' => $operator->operador_id,
                    'variables_ingresadas' => $enteredVariables, // El cast 'array' maneja la conversión
                    'cumple_estandar' => $meetsStandard,
                    'observaciones' => $request->observations,
                    'hora_inicio' => now(),
                    'hora_fin' => now(),
                    'fecha_registro' => now(),
                ]);
            } else {
                // Crear nuevo registro
                $maxId = DB::table('registro_proceso_maquina')->max('registro_id') ?? 0;
                if ($maxId > 0) {
                    DB::statement("SELECT setval('registro_proceso_maquina_seq', {$maxId}, true)");
                }
                $nextId = DB::selectOne("SELECT nextval('registro_proceso_maquina_seq') as id")->id;
                
                ProcessMachineRecord::create([
                    'registro_id' => $nextId,
                    'lote_id' => $batchId,
                    'proceso_maquina_id' => $processMachineId,
                    'operador_id' => $operator->operador_id,
                    'variables_ingresadas' => $enteredVariables, // El cast 'array' maneja la conversión
                    'cumple_estandar' => $meetsStandard,
                    'observaciones' => $request->observations,
                    'hora_inicio' => now(),
                    'hora_fin' => now(),
                    'fecha_registro' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('proceso-transformacion', $batchId)
                ->with('success', $meetsStandard ? 'Proceso completado correctamente' : 'Proceso completado con advertencias');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar formulario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario para completar una máquina específica
     */
    public function mostrarFormulario($batchId, $processMachineId)
    {
        try {
            $batch = ProductionBatch::with(['order.customer'])->findOrFail($batchId);
            $processMachine = ProcessMachine::with([
                'machine', 
                'variables.standardVariable',
                'process'
            ])->findOrFail($processMachineId);

            // Obtener registro existente si existe
            $record = ProcessMachineRecord::where('lote_id', $batchId)
                ->where('proceso_maquina_id', $processMachineId)
                ->first();

            // Validar orden secuencial
            $allProcessMachines = ProcessMachine::where('proceso_id', $processMachine->proceso_id)
                ->orderBy('orden_paso')
                ->get();

            $currentMachineIndex = $allProcessMachines->search(function ($item) use ($processMachineId) {
                return $item->proceso_maquina_id === $processMachineId;
            });

            $canAccess = true;
            $errorMessage = null;

            if ($currentMachineIndex > 0) {
                $previousMachine = $allProcessMachines[$currentMachineIndex - 1];
                $previousRecordExists = ProcessMachineRecord::where('lote_id', $batchId)
                    ->where('proceso_maquina_id', $previousMachine->proceso_maquina_id)
                    ->exists();

                if (!$previousRecordExists) {
                    $canAccess = false;
                    $errorMessage = 'Debe completar el formulario de la máquina anterior (' . $previousMachine->nombre . ') primero.';
                }
            }

            return view('proceso-transformacion-formulario', compact(
                'batch', 
                'processMachine', 
                'record',
                'canAccess',
                'errorMessage'
            ));
        } catch (\Exception $e) {
            return redirect()->route('proceso-transformacion', $batchId)
                ->with('error', 'Error al cargar formulario: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el formulario de una máquina específica (API)
     */
    public function obtenerFormulario($batchId, $processMachineId)
    {
        try {
            $batch = ProductionBatch::findOrFail($batchId);
            $processMachine = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->findOrFail($processMachineId);

            $record = ProcessMachineRecord::where('lote_id', $batchId)
                ->where('proceso_maquina_id', $processMachineId)
                ->first();

            return response()->json([
                'process_machine' => $processMachine,
                'record' => $record,
                'has_record' => $record !== null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener las máquinas de un proceso específico (para cuando se selecciona un proceso)
     */
    public function obtenerMaquinasProceso($processId)
    {
        try {
            $processMachines = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->where('proceso_id', $processId)
                ->orderBy('orden_paso')
                ->get();

            return response()->json($processMachines);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener máquinas: ' . $e->getMessage()
            ], 500);
        }
    }
}
