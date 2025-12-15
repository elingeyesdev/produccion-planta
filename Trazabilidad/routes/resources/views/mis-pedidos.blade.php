@extends('layouts.app')

@section('page_title', 'Mis Pedidos')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<!-- Estadísticas de Mis Pedidos -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $pedidos->total() }}</h3>
                <p>Mis Pedidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pendientes'] ?? 0 }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['en_proceso'] ?? 0 }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
        <div class="inner">
                <h3>{{ $stats['completados'] ?? 0 }}</h3>
                <p>Completados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Mis Pedidos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Mis Pedidos
        </h3>
        <div class="card-tools">
            <a href="{{ route('crear-pedido') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Nombre del Pedido</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Progreso</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    @php
                        // Usar estado_real si está disponible, sino usar estado
                        $estadoMostrar = $pedido->estado_real ?? $pedido->estado;
                    @endphp
                    <tr>
                        <td><strong>{{ $pedido->nombre ?? 'Sin nombre' }}</strong></td>
                        <td>{{ $pedido->descripcion ?? 'Sin descripción' }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->fecha_creacion)->format('d/m/Y') }}</td>
                        <td>
                            @if($estadoMostrar == 'completado')
                                <span class="badge badge-success">Completado</span>
                            @elseif($estadoMostrar == 'aprobado')
                                <span class="badge badge-info">Aprobado</span>
                            @elseif($estadoMostrar == 'rechazado')
                                <span class="badge badge-danger">Rechazado</span>
                            @elseif($estadoMostrar == 'en_proceso')
                                <span class="badge badge-primary">En Proceso</span>
                            @elseif($estadoMostrar == 'cancelado')
                                <span class="badge badge-secondary">Cancelado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <div class="progress progress-sm">
                                @php
                                    $progreso = 0;
                                    if($estadoMostrar == 'completado') {
                                        $progreso = 100;
                                    } elseif($estadoMostrar == 'en_proceso') {
                                        $progreso = 80;
                                    } elseif($estadoMostrar == 'aprobado') {
                                        $progreso = 50;
                                    } elseif($estadoMostrar == 'pendiente') {
                                        $progreso = 20;
                                    } elseif($estadoMostrar == 'cancelado' || $estadoMostrar == 'rechazado') {
                                        $progreso = 0;
                                    }
                                @endphp
                                @if($estadoMostrar == 'completado')
                                    <div class="progress-bar bg-success" style="width: {{ $progreso }}%"></div>
                                @elseif($estadoMostrar == 'en_proceso')
                                    <div class="progress-bar bg-primary" style="width: {{ $progreso }}%"></div>
                                @elseif($estadoMostrar == 'aprobado')
                                    <div class="progress-bar bg-info" style="width: {{ $progreso }}%"></div>
                                @elseif($estadoMostrar == 'pendiente')
                                    <div class="progress-bar bg-warning" style="width: {{ $progreso }}%"></div>
                                @else
                                    <div class="progress-bar bg-danger" style="width: {{ $progreso }}%"></div>
                                @endif
                            </div>
                            <small class="text-muted">{{ $progreso }}%</small>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles" onclick="verPedido({{ $pedido->pedido_id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            @php
                                $puedeEditar = $pedido->estado === 'pendiente' && $pedido->canBeEdited();
                            @endphp
                            @if($puedeEditar)
                                <a href="{{ route('mis-pedidos.edit', $pedido->pedido_id) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mis-pedidos.cancel', $pedido->pedido_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de cancelar este pedido?');">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Cancelar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @elseif($pedido->status == 'aprobado' || $pedido->status == 'en_produccion')
                                <span class="badge badge-info" title="Su producto ya está siendo preparado, no puede editar o cancelar">
                                    <i class="fas fa-info-circle"></i> En Preparación
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No tienes pedidos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pedidos->hasPages())
    <div class="card-footer clearfix">
        <div class="float-left">
            <small class="text-muted">
                Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} registros
            </small>
        </div>
        {{ $pedidos->links() }}
    </div>
    @endif
</div>

<!-- Modal Ver Pedido -->
<div class="modal fade" id="verPedidoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Pedido
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verPedidoContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const misPedidosBaseUrl = '{{ url("mis-pedidos") }}';

function verPedido(id) {
    $('#verPedidoModal').modal('show');
    $('#verPedidoContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    fetch(`${misPedidosBaseUrl}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            let productsHtml = '';
            if (data.products && data.products.length > 0) {
                productsHtml = '<table class="table table-sm table-bordered"><thead><tr><th>Producto</th><th>Cantidad</th><th>Estado</th></tr></thead><tbody>';
                data.products.forEach(function(product) {
                    const statusBadge = product.status === 'aprobado' 
                        ? '<span class="badge badge-success">Aprobado</span>'
                        : product.status === 'rechazado'
                        ? '<span class="badge badge-danger">Rechazado</span>'
                        : '<span class="badge badge-warning">Pendiente</span>';
                    productsHtml += `<tr><td>${product.product_name}</td><td>${product.quantity} ${product.unit}</td><td>${statusBadge}</td></tr>`;
                });
                productsHtml += '</tbody></table>';
            } else {
                productsHtml = '<p class="text-muted">No hay productos</p>';
            }
            
            let destinationsHtml = '';
            if (data.destinations && data.destinations.length > 0) {
                destinationsHtml = '<ul class="mb-0">';
                data.destinations.forEach(function(dest) {
                    destinationsHtml += `<li><strong>${dest.address}</strong>${dest.reference ? ' - ' + dest.reference : ''}${dest.contact_name ? '<br>Contacto: ' + dest.contact_name + (dest.contact_phone ? ' (' + dest.contact_phone + ')' : '') : ''}</li>`;
                });
                destinationsHtml += '</ul>';
            } else {
                destinationsHtml = '<p class="text-muted">No hay destinos</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID Pedido</th>
                                <td>#${data.order_id || data.order_number || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Número de Pedido</th>
                                <td>${data.order_number || data.order_id || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name || 'Sin nombre'}</td>
                            </tr>
                            ${data.almacen_nombre ? `
                            <tr>
                                <th>Almacén</th>
                                <td><strong>${data.almacen_nombre}</strong></td>
                            </tr>
                            ` : ''}
                            <tr>
                                <th>Descripción</th>
                                <td>${data.description || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.status === 'completado' || data.status === 'almacenado'
                                        ? '<span class="badge badge-success">Completado</span>' 
                                        : data.status === 'aprobado'
                                        ? '<span class="badge badge-info">Aprobado</span>'
                                        : data.status === 'en_produccion'
                                        ? '<span class="badge badge-primary">En Producción</span>'
                                        : data.status === 'rechazado'
                                        ? '<span class="badge badge-danger">Rechazado</span>'
                                        : data.status === 'cancelado'
                                        ? '<span class="badge badge-secondary">Cancelado</span>'
                                        : '<span class="badge badge-warning">Pendiente</span>'}
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación</th>
                                <td>${data.creation_date || 'N/A'}</td>
                            </tr>
                            ${data.delivery_date ? `
                            <tr>
                                <th>Fecha de Entrega</th>
                                <td>${data.delivery_date}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <th>Productos</th>
                                <td>${productsHtml}</td>
                            </tr>
                            <tr>
                                <th>Destinos</th>
                                <td>${destinationsHtml}</td>
                            </tr>
                            ${data.observations ? `
                            <tr>
                                <th>Observaciones</th>
                                <td>${data.observations}</td>
                            </tr>
                            ` : ''}
                            ${data.approved_at ? `
                            <tr>
                                <th>Fecha de Aprobación</th>
                                <td>${data.approved_at}</td>
                            </tr>
                            ` : ''}
                            ${data.status === 'rechazado' && data.rejection_reason ? `
                            <tr>
                                <th>Motivo del Rechazo</th>
                                <td>
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <strong>${data.rejection_reason}</strong>
                                    </div>
                                </td>
                            </tr>
                            ` : ''}
                        </table>
                    </div>
                </div>
            `;
            $('#verPedidoContent').html(content);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#verPedidoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los datos del pedido
                </div>
            `);
        });
}

</script>
@endpush


