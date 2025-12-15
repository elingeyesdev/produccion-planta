<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessMachine;
use App\Models\Machine;
use App\Models\StandardVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcesoWebController extends Controller
{
    public function index()
    {
        $procesos = Process::with('processMachines.machine')
            ->orderBy('proceso_id','desc')
            ->paginate(15);
        $maquinas = Machine::where('activo', true)->get();
        $variables = StandardVariable::where('activo', true)->get();
        return view('procesos', compact('procesos', 'maquinas', 'variables'));
    }

    public function create()
    {
        $maquinas = Machine::where('activo', true)->orderBy('nombre')->get();
        $variables = StandardVariable::where('activo', true)->orderBy('nombre')->get();
        return view('procesos.create', compact('maquinas', 'variables'));
    }

    public function store(Request $request)
    {
        // Validación básica para creación simple desde modal (sin máquinas o con array vacío)
        if (!$request->has('maquinas') || !is_array($request->maquinas) || count($request->maquinas) === 0) {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                // Obtener el siguiente ID de la secuencia
                $maxId = DB::table('proceso')->max('proceso_id') ?? 0;
                if ($maxId > 0) {
                    DB::statement("SELECT setval('proceso_seq', {$maxId}, true)");
                }
                $nextId = DB::selectOne("SELECT nextval('proceso_seq') as id")->id;
                
                // Generar código automáticamente
                $code = 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                
                Process::create([
                    'proceso_id' => $nextId,
                    'codigo' => $code,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'activo' => true,
                ]);

                return redirect()->route('procesos.index')
                    ->with('success', 'Proceso creado exitosamente');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Error al crear proceso: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Validación completa para creación con máquinas y variables
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'maquinas' => 'required|array|min:1',
            'maquinas.*.maquina_id' => 'required|integer|exists:maquina,maquina_id',
            'maquinas.*.orden_paso' => 'required|integer|min:1',
            'maquinas.*.nombre' => 'required|string|max:100',
            'maquinas.*.variables' => 'required|array|min:1',
            'maquinas.*.variables.*.variable_estandar_id' => 'required|integer|exists:variable_estandar,variable_id',
            'maquinas.*.variables.*.valor_minimo' => 'required|numeric',
            'maquinas.*.variables.*.valor_maximo' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Obtener el siguiente ID de la secuencia
            $maxId = DB::table('proceso')->max('proceso_id') ?? 0;
            if ($maxId > 0) {
                DB::statement("SELECT setval('proceso_seq', {$maxId}, true)");
            }
            $nextId = DB::selectOne("SELECT nextval('proceso_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            $proceso = Process::create([
                'proceso_id' => $nextId,
                'codigo' => $code,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => true,
            ]);

            foreach ($request->maquinas as $maquinaData) {
                // Obtener el siguiente ID de la secuencia para proceso_maquina
                $maxProcessMachineId = DB::table('proceso_maquina')->max('proceso_maquina_id') ?? 0;
                if ($maxProcessMachineId > 0) {
                    DB::statement("SELECT setval('proceso_maquina_seq', {$maxProcessMachineId}, true)");
                }
                $processMachineId = DB::selectOne("SELECT nextval('proceso_maquina_seq') as id")->id;
                
                $processMachine = ProcessMachine::create([
                    'proceso_maquina_id' => $processMachineId,
                    'proceso_id' => $proceso->proceso_id,
                    'maquina_id' => $maquinaData['maquina_id'],
                    'orden_paso' => $maquinaData['orden_paso'],
                    'nombre' => $maquinaData['nombre'],
                    'descripcion' => $maquinaData['descripcion'] ?? null,
                    'tiempo_estimado' => $maquinaData['tiempo_estimado'] ?? null,
                ]);

                foreach ($maquinaData['variables'] as $variableData) {
                    // Validar que valor_maximo sea mayor que valor_minimo
                    if (floatval($variableData['valor_maximo']) <= floatval($variableData['valor_minimo'])) {
                        throw new \Exception("El valor máximo debe ser mayor que el valor mínimo para la variable en la máquina '{$maquinaData['nombre']}'");
                    }
                    
                    // Obtener el siguiente ID de la secuencia para variable_proceso_maquina
                    $maxVariableId = DB::table('variable_proceso_maquina')->max('variable_id') ?? 0;
                    if ($maxVariableId > 0) {
                        DB::statement("SELECT setval('variable_proceso_maquina_seq', {$maxVariableId}, true)");
                    }
                    $variableId = DB::selectOne("SELECT nextval('variable_proceso_maquina_seq') as id")->id;
                    
                    \App\Models\ProcessMachineVariable::create([
                        'variable_id' => $variableId,
                        'proceso_maquina_id' => $processMachine->proceso_maquina_id,
                        'variable_estandar_id' => $variableData['variable_estandar_id'],
                        'valor_minimo' => $variableData['valor_minimo'],
                        'valor_maximo' => $variableData['valor_maximo'],
                        'valor_objetivo' => null,
                        'obligatorio' => true, // Todas las variables son obligatorias
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('procesos.index')
                ->with('success', 'Proceso creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear proceso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $proceso = Process::with(['processMachines.machine', 'processMachines.variables.standardVariable'])
                ->findOrFail($id);
            
            return response()->json([
                'proceso_id' => $proceso->proceso_id,
                'codigo' => $proceso->codigo,
                'nombre' => $proceso->nombre,
                'descripcion' => $proceso->descripcion,
                'activo' => $proceso->activo,
                'proceso_maquinas' => $proceso->processMachines->map(function($pm) {
                    return [
                        'nombre' => $pm->nombre,
                        'maquina_nombre' => $pm->machine->nombre ?? 'N/A',
                        'orden_paso' => $pm->orden_paso,
                        'descripcion' => $pm->descripcion,
                        'tiempo_estimado' => $pm->tiempo_estimado,
                        'variables' => $pm->variables->map(function($v) {
                            return [
                                'variable_nombre' => $v->standardVariable->nombre ?? 'N/A',
                                'unidad' => $v->standardVariable->unidad ?? 'N/A',
                                'valor_minimo' => $v->valor_minimo,
                                'valor_maximo' => $v->valor_maximo,
                                'valor_objetivo' => $v->valor_objetivo,
                                'obligatorio' => $v->obligatorio,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }
    }

    public function edit($id)
    {
        try {
            $proceso = Process::with(['processMachines.machine', 'processMachines.variables.standardVariable'])->findOrFail($id);
            
            return response()->json([
                'proceso_id' => $proceso->proceso_id,
                'nombre' => $proceso->nombre,
                'descripcion' => $proceso->descripcion,
                'activo' => $proceso->activo,
                'proceso_maquinas' => $proceso->processMachines->map(function($pm) {
                    return [
                        'proceso_maquina_id' => $pm->proceso_maquina_id,
                        'maquina_id' => $pm->maquina_id,
                        'maquina_nombre' => $pm->machine->nombre ?? 'N/A',
                        'orden_paso' => $pm->orden_paso,
                        'nombre' => $pm->nombre,
                        'descripcion' => $pm->descripcion,
                        'tiempo_estimado' => $pm->tiempo_estimado,
                        'variables' => $pm->variables->map(function($v) {
                            return [
                                'variable_id' => $v->variable_id,
                                'variable_estandar_id' => $v->variable_estandar_id,
                                'variable_nombre' => $v->standardVariable->nombre ?? 'N/A',
                                'unidad' => $v->standardVariable->unidad ?? 'N/A',
                                'valor_minimo' => $v->valor_minimo,
                                'valor_maximo' => $v->valor_maximo,
                                'valor_objetivo' => $v->valor_objetivo,
                                'obligatorio' => $v->obligatorio,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        // Validación básica si no hay máquinas
        if (!$request->has('maquinas')) {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
                'activo' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();
            try {
                $proceso = Process::findOrFail($id);
                $proceso->update($request->only(['nombre', 'descripcion', 'activo']));

                DB::commit();

                return redirect()->route('procesos.index')
                    ->with('success', 'Proceso actualizado exitosamente');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Error al actualizar proceso: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Validación completa para actualización con máquinas y variables
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'maquinas' => 'required|array|min:1',
            'maquinas.*.maquina_id' => 'required|integer|exists:maquina,maquina_id',
            'maquinas.*.orden_paso' => 'required|integer|min:1',
            'maquinas.*.nombre' => 'required|string|max:100',
            'maquinas.*.variables' => 'required|array|min:1',
            'maquinas.*.variables.*.variable_estandar_id' => 'required|integer|exists:variable_estandar,variable_id',
            'maquinas.*.variables.*.valor_minimo' => 'required|numeric',
            'maquinas.*.variables.*.valor_maximo' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $proceso = Process::with(['processMachines.variables'])->findOrFail($id);
            
            // Actualizar datos básicos del proceso
            $proceso->update($request->only(['nombre', 'descripcion', 'activo']));

            // Eliminar máquinas existentes y sus variables
            foreach ($proceso->processMachines as $processMachine) {
                foreach ($processMachine->variables as $variable) {
                    $variable->delete();
                }
                $processMachine->delete();
            }

            // Crear nuevas máquinas y variables
            foreach ($request->maquinas as $maquinaData) {
                // Obtener el siguiente ID de la secuencia para proceso_maquina
                $maxProcessMachineId = DB::table('proceso_maquina')->max('proceso_maquina_id') ?? 0;
                if ($maxProcessMachineId > 0) {
                    DB::statement("SELECT setval('proceso_maquina_seq', {$maxProcessMachineId}, true)");
                }
                $processMachineId = DB::selectOne("SELECT nextval('proceso_maquina_seq') as id")->id;
                
                $processMachine = ProcessMachine::create([
                    'proceso_maquina_id' => $processMachineId,
                    'proceso_id' => $proceso->proceso_id,
                    'maquina_id' => $maquinaData['maquina_id'],
                    'orden_paso' => $maquinaData['orden_paso'],
                    'nombre' => $maquinaData['nombre'],
                    'descripcion' => $maquinaData['descripcion'] ?? null,
                    'tiempo_estimado' => $maquinaData['tiempo_estimado'] ?? null,
                ]);

                foreach ($maquinaData['variables'] as $variableData) {
                    // Validar que valor_maximo sea mayor que valor_minimo
                    if (floatval($variableData['valor_maximo']) <= floatval($variableData['valor_minimo'])) {
                        throw new \Exception("El valor máximo debe ser mayor que el valor mínimo para la variable en la máquina '{$maquinaData['nombre']}'");
                    }
                    
                    // Obtener el siguiente ID de la secuencia para variable_proceso_maquina
                    $maxVariableId = DB::table('variable_proceso_maquina')->max('variable_id') ?? 0;
                    if ($maxVariableId > 0) {
                        DB::statement("SELECT setval('variable_proceso_maquina_seq', {$maxVariableId}, true)");
                    }
                    $variableId = DB::selectOne("SELECT nextval('variable_proceso_maquina_seq') as id")->id;
                    
                    \App\Models\ProcessMachineVariable::create([
                        'variable_id' => $variableId,
                        'proceso_maquina_id' => $processMachine->proceso_maquina_id,
                        'variable_estandar_id' => $variableData['variable_estandar_id'],
                        'valor_minimo' => $variableData['valor_minimo'],
                        'valor_maximo' => $variableData['valor_maximo'],
                        'valor_objetivo' => $variableData['valor_objetivo'] ?? null,
                        'obligatorio' => isset($variableData['obligatorio']) ? (bool)$variableData['obligatorio'] : true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('procesos.index')
                ->with('success', 'Proceso actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar proceso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $proceso = Process::with(['processMachines.variables'])->findOrFail($id);
            
            // Eliminar variables de máquinas primero
            foreach ($proceso->processMachines as $processMachine) {
                foreach ($processMachine->variables as $variable) {
                    $variable->delete();
                }
            }
            
            // Eliminar máquinas del proceso
            foreach ($proceso->processMachines as $processMachine) {
                $processMachine->delete();
            }
            
            // Eliminar el proceso
            $proceso->delete();
            
            DB::commit();
            
            return redirect()->route('procesos.index')
                ->with('success', 'Proceso eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar proceso: ' . $e->getMessage());
        }
    }
}



