<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaquinaWebController extends Controller
{
    public function index()
    {
        $maquinas = Machine::orderBy('maquina_id','desc')
            ->paginate(15);
        return view('maquinas', compact('maquinas'));
    }

    public function create()
    {
        return view('maquinas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'imagen_url' => 'nullable|string|max:500', // Cambiado de url a string para aceptar URLs de Cloudinary
        ]);
        
        try {
            // Sincronizar la secuencia con el máximo ID existente (si hay registros)
            // Esto asegura que la secuencia siempre esté al día
            $maxId = DB::table('maquina')->max('maquina_id') ?? 0;
            if ($maxId > 0) {
                DB::statement("SELECT setval('maquina_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('maquina_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'MAQ-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            Machine::create([
                'maquina_id' => $nextId,
                'codigo' => $code,
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'imagen_url' => $data['imagen_url'] ?? null,
                'activo' => true,
            ]);
            
            return redirect()->route('maquinas.index')->with('success', 'Máquina creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear máquina: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $maquina = Machine::findOrFail($id);
        return view('maquinas.show', compact('maquina'));
    }

    public function edit($id)
    {
        $maquina = Machine::findOrFail($id);
        return view('maquinas.edit', compact('maquina'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'imagen_url' => 'nullable|string|max:500', // Cambiado de url a string para aceptar URLs de Cloudinary
            'current_image_url' => 'nullable|string|max:500', // Para mantener la imagen actual si no se sube una nueva
            'activo' => 'nullable|boolean',
        ]);
        
        $maquina = Machine::findOrFail($id);
        
        // Si no se proporciona una nueva imagen, mantener la actual
        if (empty($data['imagen_url']) && !empty($data['current_image_url'])) {
            $data['imagen_url'] = $data['current_image_url'];
        }
        
        unset($data['current_image_url']); // Eliminar del array antes de actualizar
        
        $maquina->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen_url' => $data['imagen_url'] ?? null,
            'activo' => $data['activo'] ?? $maquina->activo,
        ]);
        return redirect()->route('maquinas.index')->with('success', 'Máquina actualizada exitosamente');
    }

    public function destroy($id)
    {
        try {
            $maquina = Machine::findOrFail($id);
            
            // Eliminar la imagen de Cloudinary si existe
            if ($maquina->imagen_url && strpos($maquina->imagen_url, 'cloudinary.com') !== false) {
                try {
                    // Extraer el public_id de la URL de Cloudinary
                    preg_match('/\/v\d+\/(.+)$/', $maquina->imagen_url, $matches);
                    if (isset($matches[1])) {
                        $publicId = pathinfo($matches[1], PATHINFO_FILENAME);
                        $folder = 'maquinas';
                        $fullPublicId = $folder . '/' . $publicId;
                        
                        // Eliminar usando el controlador de carga de imágenes
                        $deleteRequest = new Request(['public_id' => $fullPublicId]);
                        $imageUploadController = new \App\Http\Controllers\Web\ImageUploadController();
                        $imageUploadController->delete($deleteRequest);
                    }
                } catch (\Exception $e) {
                    // Si falla la eliminación de la imagen, continuar con la eliminación del registro
                    \Log::warning('No se pudo eliminar la imagen de la máquina: ' . $e->getMessage());
                }
            }
            
            // Eliminar el registro de la base de datos
            $maquina->delete();
            
            return redirect()->route('maquinas.index')->with('success', 'Máquina eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('maquinas.index')
                ->with('error', 'Error al eliminar la máquina: ' . $e->getMessage());
        }
    }
}



