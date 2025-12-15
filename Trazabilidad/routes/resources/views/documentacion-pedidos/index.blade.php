@extends('layouts.app')

@section('page_title', 'Documentación de Pedidos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-1"></i>
                    Documentación de Pedidos
                </h3>
            </div>
            <div class="card-body">
                @if($pedidosConDocumentos->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No hay pedidos con documentación disponible.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>N° Pedido</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Código Envío</th>
                                    <th>Transportista</th>
                                    <th>Fecha Entrega</th>
                                    <th>Documentos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedidosConDocumentos as $pedido)
                                <tr>
                                    <td>
                                        <strong>{{ $pedido->numero_pedido }}</strong>
                                        <br>
                                        <small class="text-muted">ID: {{ $pedido->pedido_id }}</small>
                                    </td>
                                    <td>{{ $pedido->nombre }}</td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <code>{{ $pedido->envio_codigo }}</code>
                                    </td>
                                    <td>{{ $pedido->transportista_nombre ?? 'N/A' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @php
                                            $documentos = $pedido->documentos ?? [];
                                            $count = count(array_filter($documentos));
                                        @endphp
                                        <span class="badge badge-info">{{ $count }} documento(s)</span>
                                        @if(isset($documentos['propuesta_vehiculos']))
                                            <i class="fas fa-check-circle text-success" title="Propuesta de Vehículos"></i>
                                        @endif
                                        @if(isset($documentos['nota_entrega']))
                                            <i class="fas fa-check-circle text-success" title="Nota de Entrega"></i>
                                        @endif
                                        @if(isset($documentos['trazabilidad_completa']))
                                            <i class="fas fa-check-circle text-success" title="Trazabilidad Completa"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('documentacion-pedidos.show', $pedido->pedido_id) }}" 
                                           class="btn btn-primary btn-sm" 
                                           title="Ver Documentación">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

