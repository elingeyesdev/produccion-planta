@extends('layouts.app')

@section('page_title', 'Gestión de Pedidos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Gestión de Pedidos
                </h3>
            </div>
            <div class="card-body">
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $pedidos->total() }}</h3>
                                <p>Total Pedidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $stats['pendientes'] }}</h3>
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['aprobados'] }}</h3>
                                <p>Aprobados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['en_produccion'] }}</h3>
                                <p>En Producción</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ $stats['almacenados'] ?? 0 }}</h3>
                                <p>Almacenados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $stats['rechazados'] }}</h3>
                                <p>Rechazados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="" {{ (!isset($estadoFiltro) || $estadoFiltro == '') ? 'selected' : '' }}>Todos los estados</option>
                            <option value="pendientes_aprobados_almacenados" {{ (isset($estadoFiltro) && $estadoFiltro == 'pendientes_aprobados_almacenados') ? 'selected' : '' }}>Pendientes, Aprobados y Almacenados</option>
                            <option value="pendientes_aprobados" {{ (isset($estadoFiltro) && $estadoFiltro == 'pendientes_aprobados') ? 'selected' : '' }}>Pendientes y Aprobados</option>
                            <option value="pendiente" {{ (isset($estadoFiltro) && $estadoFiltro == 'pendiente') ? 'selected' : '' }}>Pendiente</option>
                            <option value="aprobado" {{ (isset($estadoFiltro) && $estadoFiltro == 'aprobado') ? 'selected' : '' }}>Aprobado</option>
                            <option value="almacenado" {{ (isset($estadoFiltro) && $estadoFiltro == 'almacenado') ? 'selected' : '' }}>Almacenado</option>
                            <option value="rechazado" {{ (isset($estadoFiltro) && $estadoFiltro == 'rechazado') ? 'selected' : '' }}>Rechazado</option>
                            <option value="en_produccion" {{ (isset($estadoFiltro) && $estadoFiltro == 'en_produccion') ? 'selected' : '' }}>En Producción</option>
                            <option value="completado" {{ (isset($estadoFiltro) && $estadoFiltro == 'completado') ? 'selected' : '' }}>Completado</option>
                            <option value="cancelado" {{ (isset($estadoFiltro) && $estadoFiltro == 'cancelado') ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por cliente..." id="buscarCliente" value="{{ request('cliente', '') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha" value="{{ request('fecha', '') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['estado', 'cliente', 'fecha']))
                            <a href="{{ route('gestion-pedidos') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tabla de Pedidos -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nombre del Pedido</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos as $pedido)
                            <tr>
                                <td><strong>{{ $pedido->nombre ?? 'Sin nombre' }}</strong></td>
                                <td>{{ $pedido->customer->razon_social ?? 'N/A' }}</td>
                                <td>{{ $pedido->descripcion ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($pedido->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($pedido->estado == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($pedido->estado == 'almacenado')
                                        <span class="badge badge-primary"><i class="fas fa-warehouse"></i> Almacenado</span>
                                    @elseif($pedido->estado == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif($pedido->estado == 'en_produccion')
                                        <span class="badge badge-info">En Producción</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($pedido->estado) }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($pedido->fecha_creacion)->format('Y-m-d') }}</td>
                                <td>{{ $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('gestion-pedidos.show', $pedido->pedido_id) }}" class="btn btn-info btn-sm" title="Ver Detalles">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay pedidos registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
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
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
function editarPedido(id) {
    // Implementar edición
    window.location.href = '{{ route("gestion-pedidos") }}/' + id + '/edit';
}

function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarCliente').value;
    const fecha = document.getElementById('filtroFecha').value;
    
    const url = new URL(window.location);
    
    // Si hay estado seleccionado, agregarlo (incluyendo pendientes_aprobados)
    if (estado) {
        url.searchParams.set('estado', estado);
    } else {
        url.searchParams.delete('estado');
    }
    
    // Si hay búsqueda de cliente
    if (buscar) {
        url.searchParams.set('cliente', buscar);
    } else {
        url.searchParams.delete('cliente');
    }
    
    // Si hay fecha
    if (fecha) {
        url.searchParams.set('fecha', fecha);
    } else {
        url.searchParams.delete('fecha');
    }
    
    window.location = url;
}

// Permitir aplicar filtros con Enter en los campos de búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const buscarCliente = document.getElementById('buscarCliente');
    const filtroFecha = document.getElementById('filtroFecha');
    const filtroEstado = document.getElementById('filtroEstado');
    
    if (buscarCliente) {
        buscarCliente.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                aplicarFiltros();
            }
        });
    }
    
    if (filtroFecha) {
        filtroFecha.addEventListener('change', function() {
            aplicarFiltros();
        });
    }
    
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function() {
            aplicarFiltros();
        });
    }
});
</script>
@endpush

