<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use Illuminate\Http\Request;

class LotesAlmacenadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Storage::with([
                'batch.order.customer', 
                'batch.latestFinalEvaluation',
                'batch.processMachineRecords.processMachine.machine'
            ]);
        
        // Filtro por búsqueda de lote (código o nombre)
        if ($request->has('lote') && $request->lote) {
            $query->whereHas('batch', function($q) use ($request) {
                $q->where('codigo_lote', 'like', '%' . $request->lote . '%')
                  ->orWhere('nombre', 'like', '%' . $request->lote . '%');
            });
        }
        
        // Filtro por condición
        if ($request->has('condicion') && $request->condicion) {
            $query->whereRaw("LOWER(condicion) LIKE ?", ['%' . strtolower($request->condicion) . '%']);
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_almacenaje', $request->fecha);
        }
        
        $lotes_almacenados = $query->orderBy('fecha_almacenaje', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Estadísticas
        $stats = [
            'total' => Storage::count(),
            'buen_estado' => Storage::whereRaw("LOWER(condicion) LIKE '%buen%' OR LOWER(condicion) LIKE '%excelente%'")->count(),
            'regular' => Storage::whereRaw("LOWER(condicion) LIKE '%regular%' OR LOWER(condicion) LIKE '%aceptable%'")->count(),
            'total_cantidad' => Storage::sum('cantidad'),
        ];

        return view('lotes-almacenados', compact('lotes_almacenados', 'stats'));
    }

    public function obtenerAlmacenajesPorLote($batchId)
    {
        $almacenajes = Storage::with(['batch.order.customer'])
            ->where('lote_id', $batchId)
            ->orderBy('fecha_almacenaje', 'desc')
            ->get()
            ->map(function($almacenaje) {
                return [
                    'almacenaje_id' => $almacenaje->almacenaje_id,
                    'lote_id' => $almacenaje->lote_id,
                    'codigo_lote' => $almacenaje->batch->codigo_lote ?? null,
                    'nombre_lote' => $almacenaje->batch->nombre ?? null,
                    'ubicacion' => $almacenaje->ubicacion ?? 'N/A',
                    'condicion' => $almacenaje->condicion ?? 'N/A',
                    'cantidad' => $almacenaje->cantidad ?? 0,
                    'observaciones' => $almacenaje->observaciones ?? null,
                    'fecha_almacenaje' => $almacenaje->fecha_almacenaje ? $almacenaje->fecha_almacenaje->format('Y-m-d H:i:s') : null,
                    'fecha_retiro' => $almacenaje->fecha_retiro ? $almacenaje->fecha_retiro->format('Y-m-d H:i:s') : null,
                    'direccion_recojo' => $almacenaje->direccion_recojo ?? null,
                    'referencia_recojo' => $almacenaje->referencia_recojo ?? null,
                    'latitud_recojo' => $almacenaje->latitud_recojo ?? null,
                    'longitud_recojo' => $almacenaje->longitud_recojo ?? null,
                ];
            });

        return response()->json($almacenajes);
    }
}

