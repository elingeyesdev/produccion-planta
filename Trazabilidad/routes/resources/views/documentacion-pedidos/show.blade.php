@extends('layouts.app')

@section('page_title', 'Documentación del Pedido')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt mr-1"></i>
                        Documentación del Pedido: {{ $pedido->numero_pedido }}
                    </h3>
                    <a href="{{ route('documentacion-pedidos') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Información del Pedido -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Número de Pedido</span>
                                <span class="info-box-number">{{ $pedido->numero_pedido }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Estado</span>
                                <span class="info-box-number">
                                    @php
                                        $estadoClass = match($pedido->estado) {
                                            'entregado' => 'success',
                                            'en_produccion' => 'info',
                                            'aprobado' => 'primary',
                                            'pendiente' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $estadoClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($documentaciones->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se encontró documentación para este pedido.
                    </div>
                @else
                    @foreach($documentaciones as $index => $documentacion)
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-pdf mr-1"></i>
                                Documentación de Entrega #{{ $index + 1 }}
                            </h3>
                            <div class="card-tools">
                                <small class="text-muted">
                                    Recibido: {{ \Carbon\Carbon::parse($documentacion->created_at)->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Código Envío:</strong><br>
                                    <code>{{ $documentacion->envio_codigo }}</code>
                                </div>
                                <div class="col-md-4">
                                    <strong>Transportista:</strong><br>
                                    {{ $documentacion->transportista_nombre ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Fecha de Entrega:</strong><br>
                                    {{ \Carbon\Carbon::parse($documentacion->fecha_entrega)->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3">
                                <i class="fas fa-folder-open mr-1"></i>
                                Documentos Disponibles
                            </h5>

                            <div class="row">
                                @if(isset($documentacion->documentos['propuesta_vehiculos']))
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                                            <h5 class="card-title">Propuesta de Vehículos</h5>
                                            <p class="card-text text-muted">Documento con la propuesta de vehículos para el envío</p>
                                            <a href="{{ route('documentacion-pedidos.descargar', ['pedido' => $pedido->pedido_id, 'tipo' => 'propuesta_vehiculos']) }}" 
                                               target="_blank"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(isset($documentacion->documentos['nota_entrega']))
                                <div class="col-md-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-clipboard-check fa-3x text-success mb-3"></i>
                                            <h5 class="card-title">Nota de Entrega</h5>
                                            <p class="card-text text-muted">Documento con checklist y firma del transportista</p>
                                            <a href="{{ route('documentacion-pedidos.descargar', ['pedido' => $pedido->pedido_id, 'tipo' => 'nota_entrega']) }}" 
                                               target="_blank"
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(isset($documentacion->documentos['trazabilidad_completa']))
                                <div class="col-md-4 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <i class="fas fa-route fa-3x text-info mb-3"></i>
                                            <h5 class="card-title">Trazabilidad Completa</h5>
                                            <p class="card-text text-muted">Documento con todas las fechas y eventos del envío</p>
                                            <a href="{{ route('documentacion-pedidos.descargar', ['pedido' => $pedido->pedido_id, 'tipo' => 'trazabilidad_completa']) }}" 
                                               target="_blank"
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if(empty($documentacion->documentos))
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No hay documentos disponibles para esta entrega.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

