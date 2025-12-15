<?php
namespace App\Http\Controllers;
use App\Models\Maquina;
use Illuminate\Http\Request;
class MaquinaController extends Controller {
    public function index() { return Maquina::all(); }
    public function show($id) { return Maquina::findOrFail($id); }
    public function store(Request $request) { return Maquina::create($request->all()); }
    public function update(Request $request, $id) { $maquina = Maquina::findOrFail($id); $maquina->update($request->all()); return $maquina; }
    public function destroy($id) { Maquina::destroy($id); return response()->json(['message'=>'Eliminado']); }
}
