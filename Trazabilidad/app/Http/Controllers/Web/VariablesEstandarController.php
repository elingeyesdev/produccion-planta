<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StandardVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VariablesEstandarController extends Controller
{
    public function index()
    {
        $variables = StandardVariable::orderBy('nombre', 'asc')
            ->paginate(15);

        return view('variables-estandar', compact('variables'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'unidad' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Sincronizar secuencia de variable_estandar si es necesario
            $maxVariableId = DB::table('variable_estandar')->max('variable_id') ?? 0;
            if ($maxVariableId > 0) {
                DB::statement("SELECT setval('variable_estandar_seq', {$maxVariableId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('variable_estandar_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'VAR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            StandardVariable::create([
                'variable_id' => $nextId,
                'codigo' => $code,
                'nombre' => $request->nombre,
                'unidad' => $request->unidad,
                'descripcion' => $request->descripcion,
                'activo' => true,
            ]);

            return redirect()->route('variables-estandar')
                ->with('success', 'Variable estándar creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear variable: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $variable = StandardVariable::findOrFail($id);
            return response()->json([
                'variable_id' => $variable->variable_id,
                'codigo' => $variable->codigo,
                'nombre' => $variable->nombre,
                'unidad' => $variable->unidad,
                'descripcion' => $variable->descripcion,
                'activo' => $variable->activo,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Variable no encontrada'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'unidad' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $variable = StandardVariable::findOrFail($id);
            $variable->update($request->only(['nombre', 'unidad', 'descripcion', 'activo']));

            return redirect()->route('variables-estandar')
                ->with('success', 'Variable actualizada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar variable: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $variable = StandardVariable::findOrFail($id);
            
            // Verificar si la variable está siendo usada en procesos
            $usos = $variable->processMachineVariables()->count();
            if ($usos > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la variable porque está siendo utilizada en ' . $usos . ' proceso(s).');
            }
            
            // Eliminar físicamente de la base de datos
            $variable->delete();

            return redirect()->route('variables-estandar')
                ->with('success', 'Variable eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar variable: ' . $e->getMessage());
        }
    }
}

