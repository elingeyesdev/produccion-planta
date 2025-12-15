<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas para el dashboard
        $totalLotes = ProductionBatch::count();
        $lotesPendientes = ProductionBatch::whereNull('hora_inicio')->count();
        $lotesEnProceso = ProductionBatch::whereNotNull('hora_inicio')
            ->whereNull('hora_fin')->count();
        $lotesCompletados = ProductionBatch::whereNotNull('hora_fin')->count();
        $lotesCertificados = ProductionBatch::whereHas('finalEvaluation', function($query) {
            $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
        })->count();
        
        $totalPedidos = CustomerOrder::count();
        $pedidosPendientes = CustomerOrder::where('estado', 'pendiente')->count();
        
        // Calcular estadísticas de materia prima correctamente
        $allMaterias = RawMaterialBase::with('rawMaterials')
            ->where('activo', true)
            ->get()
            ->map(function ($mp) {
                // Calcular cantidad disponible desde materias primas recibidas
                $calculated = $mp->rawMaterials
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
                
                // Si no hay materias primas recibidas, usar el valor almacenado
                if ($calculated == 0 && $mp->rawMaterials->count() == 0) {
                    $mp->calculated_available_quantity = $mp->cantidad_disponible ?? 0;
                } else {
                    $mp->calculated_available_quantity = $calculated;
                }
                return $mp;
            });
        
        $materiasConStockBajo = $allMaterias->filter(function($mp) {
            $available = $mp->calculated_available_quantity ?? 0;
            $minimum = $mp->stock_minimo ?? 0;
            return $minimum > 0 && $available > 0 && $available <= $minimum;
        })->count();
        
        $stats = [
            'total_lotes' => $totalLotes,
            'lotes_pendientes' => $lotesPendientes,
            'lotes_en_proceso' => $lotesEnProceso,
            'lotes_completados' => $lotesCompletados,
            'lotes_certificados' => $lotesCertificados,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'materias_primas' => $allMaterias->count(),
            'stock_bajo' => $materiasConStockBajo,
        ];

        // Lotes recientes
        $lotes_recientes = ProductionBatch::with([
            'order.customer', 
            'latestFinalEvaluation',
            'processMachineRecords'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get();

        // Pedidos recientes con sus lotes
        $pedidos_recientes = CustomerOrder::with([
            'customer',
            'batches.latestFinalEvaluation',
            'batches.processMachineRecords'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get();

        // Calcular estados de pedidos basados en lotes
        $pedidosPendientesCount = CustomerOrder::where('estado', 'pendiente')
            ->whereDoesntHave('batches')
            ->count();
        
        $pedidosConLoteEnProceso = CustomerOrder::whereHas('batches', function($query) {
            $query->whereHas('processMachineRecords')
                ->whereDoesntHave('finalEvaluation');
        })->count();
        
        $pedidosCertificados = CustomerOrder::whereHas('batches', function($query) {
            $query->whereHas('finalEvaluation', function($q) {
                $q->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
            });
        })->count();
        
        $pedidosConLotes = CustomerOrder::whereHas('batches')->count();
        
        $pedidosAlmacenados = CustomerOrder::whereHas('batches.storage')->count();
        
        $pedidosCancelados = CustomerOrder::where('estado', 'cancelado')->count();

        // Estadísticas para gráficas
        $pedidosPorEstado = [
            'pendiente' => $pedidosPendientesCount,
            'materia_prima_solicitada' => CustomerOrder::whereHas('materialRequests')->count(),
            'en_proceso' => $pedidosConLoteEnProceso,
            'produccion_finalizada' => $pedidosCertificados,
            'almacenado' => $pedidosAlmacenados,
            'cancelado' => $pedidosCancelados,
        ];

        $lotesPorEstado = [
            'pendiente' => $lotesPendientes,
            'en_proceso' => $lotesEnProceso,
            'certificado' => $lotesCertificados,
            'no_certificado' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(razon) LIKE '%falló%'");
            })->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
        ];

        return view('dashboard', compact('stats', 'lotes_recientes', 'pedidos_recientes', 'pedidosPorEstado', 'lotesPorEstado'));
    }

    public function getData()
    {
        // Reutilizar la misma lógica del método index pero devolver JSON
        $totalLotes = ProductionBatch::count();
        $lotesPendientes = ProductionBatch::whereNull('hora_inicio')->count();
        $lotesEnProceso = ProductionBatch::whereNotNull('hora_inicio')
            ->whereNull('hora_fin')->count();
        $lotesCompletados = ProductionBatch::whereNotNull('hora_fin')->count();
        $lotesCertificados = ProductionBatch::whereHas('finalEvaluation', function($query) {
            $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
        })->count();
        
        $totalPedidos = CustomerOrder::count();
        $pedidosPendientes = CustomerOrder::where('estado', 'pendiente')->count();
        
        $stats = [
            'total_lotes' => $totalLotes,
            'lotes_completados' => $lotesCompletados,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
        ];

        // Lotes recientes
        $lotes_recientes = ProductionBatch::with([
            'order.customer', 
            'latestFinalEvaluation',
            'processMachineRecords'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get()
            ->map(function($lote) {
                $estado = 'Pendiente';
                if ($lote->latestFinalEvaluation) {
                    if (str_contains(strtolower($lote->latestFinalEvaluation->razon ?? ''), 'falló')) {
                        $estado = 'No Certificado';
                    } else {
                        $estado = 'Certificado';
                    }
                } elseif ($lote->hora_inicio && !$lote->hora_fin) {
                    $estado = 'En Proceso';
                } elseif ($lote->processMachineRecords && $lote->processMachineRecords->isNotEmpty()) {
                    $estado = 'En Transformación';
                }
                
                return [
                    'id' => $lote->codigo_lote ?? $lote->lote_id,
                    'nombre' => $lote->nombre ?? 'Sin nombre',
                    'estado' => $estado,
                    'fecha' => $lote->fecha_creacion ? $lote->fecha_creacion->format('Y-m-d') : 'N/A',
                ];
            });

        // Pedidos recientes
        $pedidos_recientes = CustomerOrder::with([
            'customer',
            'batches.latestFinalEvaluation',
            'batches.processMachineRecords'
        ])
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get()
            ->map(function($pedido) {
                $estadoPedido = $pedido->estado ?? 'pendiente';
                if ($pedido->batches && $pedido->batches->isNotEmpty()) {
                    $loteCertificado = $pedido->batches->some(function($batch) {
                        $eval = $batch->latestFinalEvaluation;
                        return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
                    });
                    $loteEnProceso = $pedido->batches->some(function($batch) {
                        return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
                    });
                    if ($loteCertificado) {
                        $estadoPedido = 'certificado';
                    } elseif ($loteEnProceso) {
                        $estadoPedido = 'en_proceso';
                    } elseif ($pedido->batches->isNotEmpty()) {
                        $estadoPedido = 'lote_creado';
                    }
                }
                
                return [
                    'id' => $pedido->numero_pedido ?? $pedido->pedido_id,
                    'cliente' => $pedido->customer->razon_social ?? 'N/A',
                    'estado' => $estadoPedido,
                    'fecha' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('Y-m-d') : 'N/A',
                ];
            });

        // Estadísticas para gráficas
        $pedidosPendientesCount = CustomerOrder::where('estado', 'pendiente')
            ->whereDoesntHave('batches')
            ->count();
        
        $pedidosConLoteEnProceso = CustomerOrder::whereHas('batches', function($query) {
            $query->whereHas('processMachineRecords')
                ->whereDoesntHave('finalEvaluation');
        })->count();
        
        $pedidosCertificados = CustomerOrder::whereHas('batches', function($query) {
            $query->whereHas('finalEvaluation', function($q) {
                $q->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
            });
        })->count();
        
        $pedidosAlmacenados = CustomerOrder::whereHas('batches.storage')->count();
        $pedidosCancelados = CustomerOrder::where('estado', 'cancelado')->count();

        $pedidosPorEstado = [
            'pendiente' => $pedidosPendientesCount,
            'materia_prima_solicitada' => CustomerOrder::whereHas('materialRequests')->count(),
            'en_proceso' => $pedidosConLoteEnProceso,
            'produccion_finalizada' => $pedidosCertificados,
            'almacenado' => $pedidosAlmacenados,
            'cancelado' => $pedidosCancelados,
        ];

        $lotesPorEstado = [
            'pendiente' => $lotesPendientes,
            'en_proceso' => $lotesEnProceso,
            'certificado' => $lotesCertificados,
            'no_certificado' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(razon) LIKE '%falló%'");
            })->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'lotes_recientes' => $lotes_recientes,
            'pedidos_recientes' => $pedidos_recientes,
            'pedidosPorEstado' => $pedidosPorEstado,
            'lotesPorEstado' => $lotesPorEstado,
        ]);
    }
}

