<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProveedorWebController extends Controller
{
    public function index()
    {
        $proveedores = Supplier::orderBy('proveedor_id','desc')
            ->paginate(15);
        return view('proveedores', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social' => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'nit' => 'nullable|string|max:20|unique:proveedor,nit',
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);
        
        try {
            // Sincronizar secuencia y obtener el siguiente ID
            $maxId = DB::table('proveedor')->max('proveedor_id');
            if ($maxId !== null && $maxId > 0) {
                DB::statement("SELECT setval('proveedor_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('proveedor_seq') as id")->id;
            
            $data['proveedor_id'] = $nextId;
            $data['activo'] = true;
            
            // Si nit está vacío, establecerlo como null
            if (empty($data['nit']) || trim($data['nit']) === '') {
                $data['nit'] = null;
            }
            
            Supplier::create($data);
            return redirect()->route('proveedores.web.index')->with('success', 'Proveedor creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear proveedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $proveedor = Supplier::with('rawMaterials.materialBase')->findOrFail($id);
        
        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'proveedor_id' => $proveedor->proveedor_id,
                'razon_social' => $proveedor->razon_social,
                'nombre_comercial' => $proveedor->nombre_comercial,
                'nit' => $proveedor->nit,
                'contacto' => $proveedor->contacto,
                'telefono' => $proveedor->telefono,
                'email' => $proveedor->email,
                'direccion' => $proveedor->direccion,
                'activo' => $proveedor->activo,
                'raw_materials_count' => $proveedor->rawMaterials->count(),
            ]);
        }
        
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Supplier::findOrFail($id);
        
        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'proveedor_id' => $proveedor->proveedor_id,
                'razon_social' => $proveedor->razon_social,
                'nombre_comercial' => $proveedor->nombre_comercial,
                'nit' => $proveedor->nit,
                'contacto' => $proveedor->contacto,
                'telefono' => $proveedor->telefono,
                'email' => $proveedor->email,
                'direccion' => $proveedor->direccion,
                'activo' => $proveedor->activo,
            ]);
        }
        
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Supplier::findOrFail($id);
        
        $rules = [
            'razon_social' => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ];
        
        // Validar nit con unicidad solo si tiene valor
        $nit = $request->input('nit');
        if (!empty($nit) && trim($nit) !== '') {
            $rules['nit'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('proveedor', 'nit')->ignore($id, 'proveedor_id')
            ];
        } else {
            $rules['nit'] = 'nullable|string|max:20';
        }
        
        $data = $request->validate($rules);
        
        // Manejar el campo activo (si no viene en el request o es "0", es false)
        $data['activo'] = $request->has('activo') && ($request->activo == '1' || $request->activo === true || $request->activo === 1);
        
        // Si nit está vacío, establecerlo como null
        if (empty($data['nit']) || trim($data['nit']) === '') {
            $data['nit'] = null;
        }
        
        $proveedor->update($data);
        return redirect()->route('proveedores.web.index')->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy($id)
    {
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update(['activo' => false]);
        return redirect()->route('proveedores.web.index')->with('success', 'Proveedor eliminado exitosamente');
    }
}



