<?php
namespace App\Http\Controllers;
use App\Models\Operador;
use Illuminate\Http\Request;
class OperadorController extends Controller {
    public function index() { return Operador::all(); }
    public function show($id) { return Operador::findOrFail($id); }
    public function store(Request $request) { return Operador::create($request->all()); }
    public function update(Request $request, $id) { $operador = Operador::findOrFail($id); $operador->update($request->all()); return $operador; }
    public function destroy($id) { Operador::destroy($id); return response()->json(['message'=>'Eliminado']); }
}
