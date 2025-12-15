@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="row">
    <!-- KPIs Row -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_pedidos'] }}</h3>
                <p>Pedidos Totales</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_lotes'] }}</h3>
                <p>Lotes Totales</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pedidos_pendientes'] }}</h3>
                <p>Pedidos Pendientes</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['lotes_completados'] }}</h3>
                <p>Lotes Completados</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfica de Estado de Pedidos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Estado de Pedidos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="pedidosChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfica de Estado de Lotes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Estado de Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="lotesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimos Pedidos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Últimos Pedidos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos_recientes as $pedido)
                            @php
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
                            @endphp
                            <tr>
                                <td>#{{ $pedido->numero_pedido ?? $pedido->pedido_id }}</td>
                                <td>{{ $pedido->customer->razon_social ?? 'N/A' }}</td>
                                <td>
                                    @if($estadoPedido == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($estadoPedido == 'certificado')
                                        <span class="badge badge-success">Certificado</span>
                                    @elseif($estadoPedido == 'en_proceso')
                                        <span class="badge badge-primary">En Proceso</span>
                                    @elseif($estadoPedido == 'lote_creado')
                                        <span class="badge badge-info">Lote Creado</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($estadoPedido) }}</span>
                                    @endif
                                </td>
                                <td>{{ $pedido->fecha_creacion ? \Carbon\Carbon::parse($pedido->fecha_creacion)->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay pedidos recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Lotes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes mr-1"></i>
                    Últimos Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes_recientes as $lote)
                            <tr>
                                <td>#{{ $lote->codigo_lote ?? $lote->lote_id }}</td>
                                <td>{{ $lote->nombre ?? 'Sin nombre' }}</td>
                                <td>
                                    @if($lote->latestFinalEvaluation)
                                        @if(str_contains(strtolower($lote->latestFinalEvaluation->razon ?? ''), 'falló'))
                                            <span class="badge badge-danger">No Certificado</span>
                                        @else
                                            <span class="badge badge-success">Certificado</span>
                                        @endif
                                    @elseif($lote->hora_inicio && !$lote->hora_fin)
                                        <span class="badge badge-warning">En Proceso</span>
                                    @elseif($lote->processMachineRecords && $lote->processMachineRecords->isNotEmpty())
                                        <span class="badge badge-primary">En Transformación</span>
                                    @else
                                        <span class="badge badge-info">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $lote->fecha_creacion ? \Carbon\Carbon::parse($lote->fecha_creacion)->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay lotes recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfica de Pedidos (Doughnut)
var pedidosCtx = document.getElementById('pedidosChart').getContext('2d');
var pedidosChart = new Chart(pedidosCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pendiente', 'Materia Prima Solicitada', 'En Proceso', 'Producción Finalizada', 'Almacenado', 'Cancelado'],
        datasets: [{
            data: [
                {{ $pedidosPorEstado['pendiente'] ?? 0 }},
                {{ $pedidosPorEstado['materia_prima_solicitada'] ?? 0 }},
                {{ $pedidosPorEstado['en_proceso'] ?? 0 }},
                {{ $pedidosPorEstado['produccion_finalizada'] ?? 0 }},
                {{ $pedidosPorEstado['almacenado'] ?? 0 }},
                {{ $pedidosPorEstado['cancelado'] ?? 0 }}
            ],
            backgroundColor: [
                '#facc15',
                '#fb923c',
                '#60a5fa',
                '#22c55e',
                '#a78bfa',
                '#f87171'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica de Lotes (Bar)
var lotesCtx = document.getElementById('lotesChart').getContext('2d');
var lotesChart = new Chart(lotesCtx, {
    type: 'bar',
    data: {
        labels: ['Pendiente', 'En Proceso', 'Certificado', 'No Certificado', 'Almacenado'],
        datasets: [{
            label: 'Cantidad',
            data: [
                {{ $lotesPorEstado['pendiente'] ?? 0 }},
                {{ $lotesPorEstado['en_proceso'] ?? 0 }},
                {{ $lotesPorEstado['certificado'] ?? 0 }},
                {{ $lotesPorEstado['no_certificado'] ?? 0 }},
                {{ $lotesPorEstado['almacenado'] ?? 0 }}
            ],
            backgroundColor: [
                '#facc15',
                '#60a5fa',
                '#22c55e',
                '#f87171',
                '#a78bfa'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Polling cada 2 segundos para actualizar datos en tiempo real
let pollingInterval = null;

function actualizarDashboard() {
    fetch('{{ route("dashboard.data") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Actualizar KPIs
        $('.small-box.bg-info .inner h3').text(data.stats.total_pedidos || 0);
        $('.small-box.bg-success .inner h3').text(data.stats.total_lotes || 0);
        $('.small-box.bg-warning .inner h3').text(data.stats.pedidos_pendientes || 0);
        $('.small-box.bg-danger .inner h3').text(data.stats.lotes_completados || 0);
        
        // Actualizar gráfica de pedidos
        pedidosChart.data.datasets[0].data = [
            data.pedidosPorEstado.pendiente || 0,
            data.pedidosPorEstado.materia_prima_solicitada || 0,
            data.pedidosPorEstado.en_proceso || 0,
            data.pedidosPorEstado.produccion_finalizada || 0,
            data.pedidosPorEstado.almacenado || 0,
            data.pedidosPorEstado.cancelado || 0
        ];
        pedidosChart.update('none'); // 'none' para animación suave
        
        // Actualizar gráfica de lotes
        lotesChart.data.datasets[0].data = [
            data.lotesPorEstado.pendiente || 0,
            data.lotesPorEstado.en_proceso || 0,
            data.lotesPorEstado.certificado || 0,
            data.lotesPorEstado.no_certificado || 0,
            data.lotesPorEstado.almacenado || 0
        ];
        lotesChart.update('none');
        
        // Actualizar tabla de pedidos recientes
        const pedidosTbody = $('table:first tbody');
        if (data.pedidos_recientes && data.pedidos_recientes.length > 0) {
            let pedidosHtml = '';
            data.pedidos_recientes.forEach(pedido => {
                let estadoBadge = '';
                if (pedido.estado === 'pendiente') {
                    estadoBadge = '<span class="badge badge-warning">Pendiente</span>';
                } else if (pedido.estado === 'certificado') {
                    estadoBadge = '<span class="badge badge-success">Certificado</span>';
                } else if (pedido.estado === 'en_proceso') {
                    estadoBadge = '<span class="badge badge-primary">En Proceso</span>';
                } else if (pedido.estado === 'lote_creado') {
                    estadoBadge = '<span class="badge badge-info">Lote Creado</span>';
                } else {
                    estadoBadge = '<span class="badge badge-info">' + pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1) + '</span>';
                }
                
                pedidosHtml += `
                    <tr>
                        <td>#${pedido.id}</td>
                        <td>${pedido.cliente}</td>
                        <td>${estadoBadge}</td>
                        <td>${pedido.fecha}</td>
                    </tr>
                `;
            });
            pedidosTbody.html(pedidosHtml);
        } else {
            pedidosTbody.html('<tr><td colspan="4" class="text-center">No hay pedidos recientes</td></tr>');
        }
        
        // Actualizar tabla de lotes recientes
        const lotesTbody = $('table:last tbody');
        if (data.lotes_recientes && data.lotes_recientes.length > 0) {
            let lotesHtml = '';
            data.lotes_recientes.forEach(lote => {
                let estadoBadge = '';
                if (lote.estado === 'Certificado') {
                    estadoBadge = '<span class="badge badge-success">Certificado</span>';
                } else if (lote.estado === 'No Certificado') {
                    estadoBadge = '<span class="badge badge-danger">No Certificado</span>';
                } else if (lote.estado === 'En Proceso') {
                    estadoBadge = '<span class="badge badge-warning">En Proceso</span>';
                } else if (lote.estado === 'En Transformación') {
                    estadoBadge = '<span class="badge badge-primary">En Transformación</span>';
                } else {
                    estadoBadge = '<span class="badge badge-info">Pendiente</span>';
                }
                
                lotesHtml += `
                    <tr>
                        <td>#${lote.id}</td>
                        <td>${lote.nombre}</td>
                        <td>${estadoBadge}</td>
                        <td>${lote.fecha}</td>
                    </tr>
                `;
            });
            lotesTbody.html(lotesHtml);
        } else {
            lotesTbody.html('<tr><td colspan="4" class="text-center">No hay lotes recientes</td></tr>');
        }
    })
    .catch(error => {
        console.error('Error al actualizar dashboard:', error);
    });
}

// Iniciar polling cuando la página esté lista
$(document).ready(function() {
    // Primera actualización después de 2 segundos
    setTimeout(actualizarDashboard, 2000);
    
    // Luego actualizar cada 2 segundos
    pollingInterval = setInterval(actualizarDashboard, 2000);
});

// Detener polling cuando se sale de la página
$(window).on('beforeunload', function() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>
@endpush

