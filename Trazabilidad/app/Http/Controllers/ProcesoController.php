<?php
namespace App\Http\Controllers;
use App\Models\Proceso;
use Illuminate\Http\Request;
class ProcesoController extends Controller {
    public function index() { return Proceso::all(); }
    public function show($id) { return Proceso::findOrFail($id); }
    public function store(Request $request) { return Proceso::create($request->all()); }
    public function update(Request $request, $id) { $proceso = Proceso::findOrFail($id); $proceso->update($request->all()); return $proceso; }
    public function destroy($id) { Proceso::destroy($id); return response()->json(['message'=>'Eliminado']); }
}
