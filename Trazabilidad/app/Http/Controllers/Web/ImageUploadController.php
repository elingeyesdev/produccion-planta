<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary as CloudinarySDK;
use Cloudinary\Configuration\Configuration;

class ImageUploadController extends Controller
{
    /**
     * Subir imagen a Cloudinary
     */
    public function upload(Request $request)
    {
        // Aceptar tanto 'image' como 'imagen' para compatibilidad
        $file = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
        } elseif ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
        }
        
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibió ningún archivo. Asegúrese de seleccionar una imagen.'
            ], 400);
        }

        // Validar el archivo
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }

        $folder = $request->input('folder', 'trazabilidad');

        try {
            // Paso 1: Verificar que Cloudinary esté configurado
            \Log::info('Paso 1: Verificando variables de entorno');
            // Usar config() en lugar de env() para que funcione con caché de configuración
            $cloudName = config('cloudinary.cloud_name') ?: env('CLOUDINARY_CLOUD_NAME');
            $apiKey = config('cloudinary.api_key') ?: env('CLOUDINARY_API_KEY');
            $apiSecret = config('cloudinary.api_secret') ?: env('CLOUDINARY_API_SECRET');

            \Log::info('Variables de entorno', [
                'cloud_name_exists' => !empty($cloudName),
                'api_key_exists' => !empty($apiKey),
                'api_secret_exists' => !empty($apiSecret),
                'cloud_name' => $cloudName ? substr($cloudName, 0, 3) . '...' : 'null',
            ]);

            if (!$cloudName || !$apiKey || !$apiSecret) {
                \Log::error('Variables de entorno faltantes', [
                    'cloud_name' => $cloudName ? 'existe' : 'faltante',
                    'api_key' => $apiKey ? 'existe' : 'faltante',
                    'api_secret' => $apiSecret ? 'existe' : 'faltante',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cloudinary no está configurado. Por favor, configure las variables de entorno CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY y CLOUDINARY_API_SECRET en el archivo .env y ejecute: php artisan config:cache'
                ], 500);
            }

            // Paso 2: Crear instancia de Cloudinary SDK con configuración directa
            // Esto es más confiable que usar Configuration::instance()
            \Log::info('Paso 2: Creando instancia de Cloudinary SDK con configuración directa');
            try {
                $cloudinary = new CloudinarySDK([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret,
                    ],
                    'url' => [
                        'secure' => true
                    ]
                ]);
                \Log::info('Instancia de Cloudinary creada exitosamente');
            } catch (\Exception $instanceException) {
                \Log::error('Error al crear instancia de Cloudinary', [
                    'error' => $instanceException->getMessage(),
                    'trace' => $instanceException->getTraceAsString(),
                    'file' => $instanceException->getFile(),
                    'line' => $instanceException->getLine()
                ]);
                throw $instanceException;
            }

            // Paso 3: Verificar que el archivo existe
            \Log::info('Paso 3: Verificando archivo', [
                'file_path' => $file->getRealPath(),
                'file_exists' => file_exists($file->getRealPath()),
                'file_size' => filesize($file->getRealPath()),
            ]);

            if (!file_exists($file->getRealPath())) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no existe en la ruta temporal'
                ], 500);
            }

            // Paso 4: Subir imagen a Cloudinary
            \Log::info('Paso 4: Subiendo imagen a Cloudinary', [
                'folder' => $folder,
            ]);
            
            try {
                $uploadResult = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => $folder,
                    'resource_type' => 'image',
                ]);
                \Log::info('Imagen subida exitosamente', [
                    'result_type' => gettype($uploadResult),
                    'has_secure_url' => isset($uploadResult['secure_url']),
                    'has_url' => isset($uploadResult['url']),
                ]);
            } catch (\Exception $uploadException) {
                \Log::error('Error al subir imagen', [
                    'error' => $uploadException->getMessage(),
                    'trace' => $uploadException->getTraceAsString(),
                    'file' => $uploadException->getFile(),
                    'line' => $uploadException->getLine()
                ]);
                throw $uploadException;
            }

            // Paso 5: Extraer URL de la imagen
            \Log::info('Paso 5: Extrayendo URL de la imagen');
            $imageUrl = $uploadResult['secure_url'] ?? $uploadResult['url'] ?? null;
            $publicId = $uploadResult['public_id'] ?? null;

            \Log::info('Resultado de la subida', [
                'image_url' => $imageUrl ? substr($imageUrl, 0, 50) . '...' : 'null',
                'public_id' => $publicId ?? 'null',
            ]);

            if (!$imageUrl) {
                \Log::error('Cloudinary upload result - no URL found', [
                    'result' => $uploadResult,
                    'result_type' => gettype($uploadResult),
                    'result_keys' => is_array($uploadResult) ? array_keys($uploadResult) : 'N/A'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error: No se pudo obtener la URL de la imagen subida. Verifique la configuración de Cloudinary.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'imageUrl' => $imageUrl,
                'publicId' => $publicId,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al subir imagen a Cloudinary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen de Cloudinary
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar que Cloudinary esté configurado
            // Usar config() en lugar de env() para que funcione con caché de configuración
            $cloudName = config('cloudinary.cloud_name') ?: env('CLOUDINARY_CLOUD_NAME');
            $apiKey = config('cloudinary.api_key') ?: env('CLOUDINARY_API_KEY');
            $apiSecret = config('cloudinary.api_secret') ?: env('CLOUDINARY_API_SECRET');

            if (!$cloudName || !$apiKey || !$apiSecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cloudinary no está configurado. Por favor, configure las variables de entorno CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY y CLOUDINARY_API_SECRET en el archivo .env y ejecute: php artisan config:cache'
                ], 500);
            }

            // Crear instancia de Cloudinary SDK
            $cloudinary = new CloudinarySDK([
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                ],
                'url' => [
                    'secure' => true
                ]
            ]);

            // Eliminar la imagen usando el public_id
            $publicId = $request->input('public_id');
            $result = $cloudinary->uploadApi()->destroy($publicId);
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada exitosamente',
                'result' => $result
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar imagen de Cloudinary', [
                'error' => $e->getMessage(),
                'public_id' => $request->input('public_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}

