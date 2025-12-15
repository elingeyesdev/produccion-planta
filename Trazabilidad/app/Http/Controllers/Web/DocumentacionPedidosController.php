<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentacionPedidosController extends Controller
{
    /**
     * Listar pedidos que tienen documentación
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener pedidos que tienen documentación
        // Nota: pedido_documentos_entrega.pedido_id es VARCHAR, pedido_cliente.pedido_id es INTEGER
        // Necesitamos hacer un cast para el JOIN en PostgreSQL
        $pedidosConDocumentos = DB::table('pedido_documentos_entrega')
            ->join('pedido_cliente', function($join) {
                $join->on(DB::raw('pedido_documentos_entrega.pedido_id::integer'), '=', 'pedido_cliente.pedido_id');
            })
            ->select(
                'pedido_cliente.pedido_id',
                'pedido_cliente.numero_pedido',
                'pedido_cliente.nombre',
                'pedido_cliente.estado',
                'pedido_cliente.fecha_creacion',
                'pedido_documentos_entrega.envio_id',
                'pedido_documentos_entrega.envio_codigo',
                'pedido_documentos_entrega.fecha_entrega',
                'pedido_documentos_entrega.transportista_nombre',
                'pedido_documentos_entrega.documentos',
                'pedido_documentos_entrega.created_at'
            )
            ->orderBy('pedido_documentos_entrega.created_at', 'desc')
            ->get()
            ->map(function ($pedido) {
                $pedido->documentos = json_decode($pedido->documentos, true) ?? [];
                return $pedido;
            });

        return view('documentacion-pedidos.index', compact('pedidosConDocumentos'));
    }

    /**
     * Mostrar documentos de un pedido específico
     * 
     * @param CustomerOrder $pedido
     * @return \Illuminate\View\View
     */
    public function show(CustomerOrder $pedido)
    {
        // Obtener todas las documentaciones de este pedido
        $documentaciones = DB::table('pedido_documentos_entrega')
            ->where('pedido_id', $pedido->pedido_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                $doc->documentos = json_decode($doc->documentos, true) ?? [];
                return $doc;
            });

        return view('documentacion-pedidos.show', compact('pedido', 'documentaciones'));
    }

    /**
     * Descargar un documento específico
     * 
     * @param CustomerOrder $pedido
     * @param string $tipo Tipo de documento: propuesta_vehiculos, nota_entrega, trazabilidad_completa
     * @return \Illuminate\Http\Response
     */
    public function descargarDocumento(CustomerOrder $pedido, $tipo)
    {
        try {
            // Validar tipo de documento
            $tiposValidos = ['propuesta_vehiculos', 'nota_entrega', 'trazabilidad_completa'];
            if (!in_array($tipo, $tiposValidos)) {
                abort(400, 'Tipo de documento inválido');
            }

            // Obtener la documentación más reciente del pedido
            $documentacion = DB::table('pedido_documentos_entrega')
                ->where('pedido_id', $pedido->pedido_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$documentacion) {
                abort(404, 'Documentación no encontrada');
            }

            $documentos = json_decode($documentacion->documentos, true) ?? [];
            
            if (!isset($documentos[$tipo])) {
                abort(404, 'Documento no encontrado');
            }

            $rutaArchivo = $documentos[$tipo];

            if (!Storage::exists($rutaArchivo)) {
                abort(404, 'Archivo no encontrado en el servidor');
            }

            $contenido = Storage::get($rutaArchivo);
            $nombreArchivo = basename($rutaArchivo);

            return response($contenido, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"');
        } catch (\Exception $e) {
            Log::error('Error descargando documento', [
                'pedido_id' => $pedido->pedido_id,
                'tipo' => $tipo,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error al descargar el documento');
        }
    }
}

