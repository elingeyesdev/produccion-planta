<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProveedorController extends Controller {
    public function index()
    {
        $proveedores = Supplier::where('activo', true)->get();
        return response()->json($proveedores);
    }
    public function show($id) { 
        return response()->json(Supplier::findOrFail($id)); 
    }
    public function store(Request $request) { 
        $data = $request->validate([
            'razon_social' => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'nit' => 'nullable|string|max:20|unique:proveedor,nit',
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);
        
        $maxId = DB::table('proveedor')->max('proveedor_id') ?? 0;
        if ($maxId > 0) {
            DB::statement("SELECT setval('proveedor_seq', {$maxId}, true)");
        }
        $nextId = DB::selectOne("SELECT nextval('proveedor_seq') as id")->id;
        $data['proveedor_id'] = $nextId;
        $data['activo'] = true;
        
        return response()->json(Supplier::create($data), 201); 
    }
    public function update(Request $request, $id) { 
        $proveedor = Supplier::findOrFail($id); 
        $data = $request->validate([
            'razon_social' => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'nit' => 'nullable|string|max:20|unique:proveedor,nit,' . $id . ',proveedor_id',
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);
        $proveedor->update($data); 
        return response()->json($proveedor); 
    }
    public function destroy($id) { 
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update(['activo' => false]);
        return response()->json(['message'=>'Eliminado']); 
    }
}
