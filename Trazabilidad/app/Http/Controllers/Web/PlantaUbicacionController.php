<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PlantaUbicacionController extends Controller
{
    public function index()
    {
        $plantaConfig = [
            'nombre' => config('services.planta.nombre', 'Planta Principal'),
            'direccion' => config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia'),
            'latitud' => config('services.planta.latitud', '-17.8146'),
            'longitud' => config('services.planta.longitud', '-63.1561'),
        ];
        
        return view('planta-ubicacion', compact('plantaConfig'));
    }
    
    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Leer el archivo .env
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return redirect()->back()
                ->with('error', 'No se encontró el archivo .env');
        }
        
        $envContent = File::get($envPath);
        
        // Actualizar o agregar las variables
        $envVars = [
            'PLANTA_NOMBRE' => $request->nombre,
            'PLANTA_DIRECCION' => $request->direccion,
            'PLANTA_LATITUD' => $request->latitud,
            'PLANTA_LONGITUD' => $request->longitud,
        ];
        
        foreach ($envVars as $key => $value) {
            // Escapar comillas y caracteres especiales
            $escapedValue = str_replace(['"', "'"], ['\"', "\'"], $value);
            
            // Buscar si la variable ya existe
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Reemplazar la variable existente
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$escapedValue}\"",
                    $envContent
                );
            } else {
                // Agregar la variable al final del archivo
                $envContent .= "\n{$key}=\"{$escapedValue}\"";
            }
        }
        
        // Guardar el archivo .env
        File::put($envPath, $envContent);
        
        // Limpiar la caché de configuración
        \Artisan::call('config:clear');
        
        return redirect()->route('planta-ubicacion')
            ->with('success', 'Ubicación de la planta actualizada correctamente');
    }
}

