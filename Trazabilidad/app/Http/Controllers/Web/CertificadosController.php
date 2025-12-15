<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessFinalEvaluation;
use Illuminate\Http\Request;

class CertificadosController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionBatch::whereHas('finalEvaluation')
            ->with([
                'order.customer',
                'latestFinalEvaluation.inspector',
                'processMachineRecords.processMachine.machine',
                'rawMaterials.rawMaterial.materialBase',
                'storage'
            ]);
        
        // Filtro por estado del certificado
        if ($request->has('estado') && $request->estado) {
            $estado = $request->estado;
            if ($estado === 'valido') {
                $query->whereHas('latestFinalEvaluation', function($q) {
                    $q->whereRaw("LOWER(COALESCE(razon, '')) NOT LIKE '%falló%'");
                });
            } elseif ($estado === 'por_vencer') {
                $query->whereHas('latestFinalEvaluation', function($q) {
                    $q->whereDate('fecha_vencimiento', '>=', now())
                      ->whereDate('fecha_vencimiento', '<=', now()->addDays(30));
                });
            } elseif ($estado === 'vencido') {
                $query->whereHas('latestFinalEvaluation', function($q) {
                    $q->whereDate('fecha_vencimiento', '<', now());
                });
            } elseif ($estado === 'revocado') {
                $query->whereHas('latestFinalEvaluation', function($q) {
                    $q->whereRaw("LOWER(COALESCE(razon, '')) LIKE '%falló%'");
                });
            }
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_creacion', $request->fecha);
        }
        
        // Filtro por lote (código o nombre)
        if ($request->has('lote') && $request->lote) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_lote', 'like', '%' . $request->lote . '%')
                  ->orWhere('nombre', 'like', '%' . $request->lote . '%');
            });
        }
        
        $certificados = $query->orderBy('fecha_creacion', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('certificados', compact('certificados'));
    }

    public function show($id)
    {
        $lote = ProductionBatch::with([
            'order.customer',
            'order.orderProducts.product.unit',
            'latestFinalEvaluation.inspector',
            'processMachineRecords.processMachine.machine',
            'processMachineRecords.processMachine.process',
            'processMachineRecords.processMachine.variables.standardVariable',
            'processMachineRecords.operator',
            'rawMaterials.rawMaterial.materialBase.unit',
            'rawMaterials.rawMaterial.supplier',
            'storage'
        ])->findOrFail($id);

        if (!$lote->latestFinalEvaluation) {
            return redirect()->route('certificados')
                ->with('error', 'Este lote aún no ha sido certificado');
        }

        return view('certificados.show', compact('lote'));
    }

    public function qr($id)
    {
        $lote = ProductionBatch::with([
            'order.customer',
            'latestFinalEvaluation.inspector',
            'processMachineRecords.processMachine.machine',
            'rawMaterials.rawMaterial.materialBase',
            'storage'
        ])->findOrFail($id);

        return view('codigo-qr', compact('lote'));
    }

    /**
     * Mostrar certificado de forma pública (sin autenticación)
     * Accesible desde código QR
     */
    public function showPublic($id)
    {
        $lote = ProductionBatch::with([
            'order.customer',
            'latestFinalEvaluation.inspector',
            'processMachineRecords.processMachine.machine',
            'processMachineRecords.processMachine.process',
            'processMachineRecords.operator',
            'rawMaterials.rawMaterial.materialBase',
            'storage'
        ])->findOrFail($id);

        if (!$lote->latestFinalEvaluation) {
            return view('certificados.publico', [
                'lote' => $lote,
                'error' => 'Este lote aún no ha sido certificado'
            ]);
        }

        return view('certificados.publico', compact('lote'));
    }
}

