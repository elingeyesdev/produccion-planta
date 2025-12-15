<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PedidoDocumentosController extends Controller
{
    /**
     * Recibir documentos de entrega desde plantaCruds
     * POST /api/pedidos/{pedido}/documentos-entrega
     * 
     * @param Request $request
     * @param CustomerOrder $pedido
     * @return \Illuminate\Http\JsonResponse
     */
    public function recibirDocumentos(Request $request, CustomerOrder $pedido)
    {
        try {
            Log::info('📄 [PedidoDocumentosController] Recibiendo documentos de entrega desde plantaCruds', [
                'pedido_id' => $pedido->pedido_id,
                'envio_id' => $request->input('envio_id'),
                'envio_codigo' => $request->input('envio_codigo'),
            ]);

            // Validar datos recibidos
            $request->validate([
                'envio_id' => 'required|integer',
                'envio_codigo' => 'required|string',
                'fecha_entrega' => 'required|date',
                'transportista_nombre' => 'nullable|string',
                'documentos' => 'required|array',
                'documentos.propuesta_vehiculos' => 'nullable|string',
                'documentos.nota_entrega' => 'nullable|string',
                'documentos.trazabilidad_completa' => 'nullable|string',
            ]);

            $envioId = $request->input('envio_id');
            $envioCodigo = $request->input('envio_codigo');
            $fechaEntrega = $request->input('fecha_entrega');
            $transportistaNombre = $request->input('transportista_nombre', 'N/A');
            $documentos = $request->input('documentos', []);

            // Crear directorio para documentos si no existe
            $directorio = "pedidos/{$pedido->id}/documentos-entrega";
            if (!Storage::exists($directorio)) {
                Storage::makeDirectory($directorio);
            }

            $documentosGuardados = [];

            // Guardar cada documento si está presente
            if (!empty($documentos['propuesta_vehiculos'])) {
                $ruta = $this->guardarDocumento(
                    $documentos['propuesta_vehiculos'],
                    $directorio,
                    "propuesta-vehiculos-{$envioCodigo}.pdf"
                );
                if ($ruta) {
                    $documentosGuardados['propuesta_vehiculos'] = $ruta;
                }
            }

            if (!empty($documentos['nota_entrega'])) {
                $ruta = $this->guardarDocumento(
                    $documentos['nota_entrega'],
                    $directorio,
                    "nota-entrega-{$envioCodigo}.pdf"
                );
                if ($ruta) {
                    $documentosGuardados['nota_entrega'] = $ruta;
                }
            }

            if (!empty($documentos['trazabilidad_completa'])) {
                $ruta = $this->guardarDocumento(
                    $documentos['trazabilidad_completa'],
                    $directorio,
                    "trazabilidad-completa-{$envioCodigo}.pdf"
                );
                if ($ruta) {
                    $documentosGuardados['trazabilidad_completa'] = $ruta;
                }
            }

            // Guardar información de entrega en la base de datos
            DB::table('pedido_documentos_entrega')->updateOrInsert(
                [
                    'pedido_id' => $pedido->pedido_id,
                    'envio_id' => $envioId,
                ],
                [
                    'envio_codigo' => $envioCodigo,
                    'fecha_entrega' => $fechaEntrega,
                    'transportista_nombre' => $transportistaNombre,
                    'documentos' => json_encode($documentosGuardados),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Actualizar estado del pedido si está relacionado con este envío
            $tracking = DB::table('seguimiento_envio_pedido')
                ->where('pedido_id', $pedido->pedido_id)
                ->where('envio_id', $envioId)
                ->first();

            if ($tracking) {
                // Actualizar estado del tracking
                DB::table('order_envio_tracking')
                    ->where('id', $tracking->id)
                    ->update([
                        'estado' => 'entregado',
                        'fecha_entrega' => $fechaEntrega,
                        'updated_at' => now(),
                    ]);
            }

            Log::info('✅ [PedidoDocumentosController] Documentos recibidos y guardados exitosamente', [
                'pedido_id' => $pedido->id,
                'envio_id' => $envioId,
                'documentos_guardados' => array_keys($documentosGuardados),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documentos recibidos y guardados exitosamente',
                'data' => [
                    'pedido_id' => $pedido->pedido_id,
                    'envio_id' => $envioId,
                    'envio_codigo' => $envioCodigo,
                    'fecha_entrega' => $fechaEntrega,
                    'documentos_guardados' => array_keys($documentosGuardados),
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ [PedidoDocumentosController] Error de validación', [
                'pedido_id' => $pedido->id,
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ [PedidoDocumentosController] Error recibiendo documentos', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al recibir documentos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guardar documento PDF desde base64
     * 
     * @param string $base64Content
     * @param string $directorio
     * @param string $nombreArchivo
     * @return string|null Ruta del archivo guardado
     */
    private function guardarDocumento(string $base64Content, string $directorio, string $nombreArchivo): ?string
    {
        try {
            // Decodificar base64
            $pdfContent = base64_decode($base64Content);
            
            if ($pdfContent === false) {
                Log::error('Error decodificando base64', [
                    'archivo' => $nombreArchivo,
                ]);
                return null;
            }

            // Guardar archivo
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            Storage::put($rutaCompleta, $pdfContent);

            Log::info('Documento guardado exitosamente', [
                'archivo' => $nombreArchivo,
                'ruta' => $rutaCompleta,
                'tamaño' => strlen($pdfContent),
            ]);

            return $rutaCompleta;
        } catch (\Exception $e) {
            Log::error('Error guardando documento', [
                'archivo' => $nombreArchivo,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

