@extends('layouts.app')

@section('page_title', 'Detalle de Pedido - Gestión')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Detalle del Pedido: {{ $pedido->order_number }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('gestion-pedidos') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
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

                <!-- Información del Pedido -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Pedido</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Nombre:</th>
                                <td>{{ $pedido->nombre ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Cliente:</th>
                                <td>{{ $pedido->customer->razon_social ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    @if($pedido->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($pedido->estado == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($pedido->estado == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif($pedido->estado == 'en_produccion')
                                        <span class="badge badge-info">En Producción</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($pedido->estado) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha Creación:</th>
                                <td>{{ \Carbon\Carbon::parse($pedido->fecha_creacion)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Fecha Entrega:</th>
                                <td>{{ $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Descripción</h5>
                        <p>{{ $pedido->descripcion ?? 'Sin descripción' }}</p>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <h5 class="mb-3">Productos del Pedido</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedido->orderProducts as $orderProduct)
                            <tr>
                                <td>
                                    <strong>{{ $orderProduct->product->nombre }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($orderProduct->product->tipo) }}</span>
                                </td>
                                <td>{{ number_format($orderProduct->cantidad, 4) }}</td>
                                <td>{{ $orderProduct->product->unit->nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($orderProduct->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($orderProduct->estado == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($orderProduct->estado == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay productos en este pedido</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Acciones de Aprobación/Rechazo -->
                @if($pedido->estado == 'pendiente')
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Acciones de Aprobación</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">
                                    <strong>Nota:</strong> Al aprobar o rechazar, se aplicará la acción a todos los productos del pedido de una vez.
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button class="btn btn-success btn-lg btn-block" 
                                                data-toggle="modal" 
                                                data-target="#approveOrderModal">
                                            <i class="fas fa-check"></i> Aprobar Todo el Pedido
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-danger btn-lg btn-block" 
                                                data-toggle="modal" 
                                                data-target="#rejectOrderModal">
                                            <i class="fas fa-times"></i> Rechazar Todo el Pedido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Aprobar Pedido Completo -->
                <div class="modal fade" id="approveOrderModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('gestion-pedidos.approve-order', ['orderId' => $pedido->pedido_id]) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Aprobar Pedido Completo</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de aprobar todo el pedido <strong>{{ $pedido->nombre }}</strong>?</p>
                                    <p class="text-muted">Esta acción aprobará todos los productos del pedido de una vez.</p>
                                    <div class="form-group">
                                        <label>Observaciones (opcional)</label>
                                        <textarea class="form-control" name="observaciones" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Aprobar Todo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Rechazar Pedido Completo -->
                <div class="modal fade" id="rejectOrderModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('gestion-pedidos.reject-order', ['orderId' => $pedido->pedido_id]) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Rechazar Pedido Completo</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de rechazar todo el pedido <strong>{{ $pedido->nombre }}</strong>?</p>
                                    <p class="text-muted">Esta acción rechazará todos los productos del pedido de una vez.</p>
                                    <div class="form-group">
                                        <label>Razón del Rechazo <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Rechazar Todo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @elseif($pedido->estado == 'aprobado')
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Pedido Aprobado</strong><br>
                    Aprobado por: {{ $pedido->approver->nombre ?? 'N/A' }}<br>
                    Fecha: {{ $pedido->aprobado_en ? \Carbon\Carbon::parse($pedido->aprobado_en)->format('d/m/Y H:i') : 'N/A' }}
                    @if($pedido->observaciones)
                    <br>Observaciones: {{ $pedido->observaciones }}
                    @endif
                </div>
                @elseif($pedido->estado == 'rechazado')
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-times-circle"></i>
                    <strong>Pedido Rechazado</strong><br>
                    Rechazado por: {{ $pedido->approver->nombre ?? 'N/A' }}<br>
                    Fecha: {{ $pedido->aprobado_en ? \Carbon\Carbon::parse($pedido->aprobado_en)->format('d/m/Y H:i') : 'N/A' }}
                    @if($pedido->razon_rechazo)
                    <br>Razón: {{ $pedido->razon_rechazo }}
                    @endif
                </div>
                @endif

                <!-- Materias Primas Necesarias para este Pedido -->
                @if(isset($materiasPrimasCreadas) && $materiasPrimasCreadas->count() > 0)
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes mr-2"></i>
                            Materias Primas Necesarias para este Pedido
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Información:</strong> Estas son las materias primas que coinciden con los productos de este pedido. 
                            Verifica que tengas suficiente cantidad disponible antes de comenzar la producción.
                            <br><small class="d-block mt-2">Nota: Las materias primas deben existir previamente en el sistema. Si no aparecen aquí, créalas manualmente en "Materias Prima Base".</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Producto del Pedido</th>
                                        <th>Materia Prima</th>
                                        <th>Código MP</th>
                                        <th>Cantidad Requerida</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Diferencia</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($materiasPrimasCreadas as $mp)
                                    @php
                                        $cantidadDisponible = $mp->rawMaterials()
                                            ->where('conformidad_recepcion', true)
                                            ->sum('cantidad_disponible') ?? 0;
                                        if ($cantidadDisponible == 0 && $mp->rawMaterials->count() == 0) {
                                            $cantidadDisponible = $mp->cantidad_disponible ?? 0;
                                        }
                                        $cantidadRequerida = $mp->cantidad_requerida ?? 0;
                                        $diferencia = $cantidadDisponible - $cantidadRequerida;
                                        $tieneSuficiente = $diferencia >= 0;
                                    @endphp
                                    <tr class="{{ $tieneSuficiente ? '' : 'table-warning' }}">
                                        <td>
                                            <strong>{{ $mp->nombre }}</strong>
                                            @if($mp->producto_pedido)
                                                <br><small class="text-muted">
                                                    Cantidad en pedido: {{ number_format($mp->producto_pedido->cantidad, 2) }} 
                                                    {{ $mp->producto_pedido->product->unit->codigo ?? '' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $mp->nombre }}</strong>
                                            <br><small class="text-muted">{{ $mp->category->nombre ?? 'Sin categoría' }}</small>
                                        </td>
                                        <td><strong class="text-primary">{{ $mp->codigo }}</strong></td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ number_format($cantidadRequerida, 2) }} {{ $mp->unit->codigo ?? 'KG' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="{{ $cantidadDisponible > 0 ? 'text-success' : 'text-danger' }}">
                                                <strong>{{ number_format($cantidadDisponible, 2) }}</strong> {{ $mp->unit->codigo ?? 'KG' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tieneSuficiente)
                                                <span class="badge badge-success">
                                                    +{{ number_format($diferencia, 2) }} {{ $mp->unit->codigo ?? 'KG' }}
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    {{ number_format($diferencia, 2) }} {{ $mp->unit->codigo ?? 'KG' }}
                                                </span>
                                                <br><small class="text-danger">Faltan {{ number_format(abs($diferencia), 2) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tieneSuficiente)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Suficiente
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> Insuficiente
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('materia-prima-base.show', $mp->material_id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver detalles de la materia prima">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Información:</strong> No se encontraron materias primas que coincidan con los productos de este pedido.
                    @if($pedido->orderProducts->count() > 0)
                        <br><br><strong>Productos en este pedido:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($pedido->orderProducts as $op)
                                <li>
                                    <strong>{{ $op->product->nombre ?? 'Producto sin nombre' }}</strong> - 
                                    Cantidad requerida: {{ number_format($op->cantidad, 2) }} {{ $op->product->unit->codigo ?? '' }}
                                </li>
                            @endforeach
                        </ul>
                        <br><small>Para crear las materias primas necesarias, ve a "Materias Prima Base" y créalas manualmente con los mismos nombres que los productos del pedido.</small>
                    @endif
                </div>
                @endif

                <!-- Destinos de Entrega -->
                @if($pedido->destinations->count() > 0)
                <h5 class="mb-3 mt-4">Destinos de Entrega</h5>
                <div class="row">
                    @foreach($pedido->destinations as $destination)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Destino {{ $loop->iteration }}</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Dirección:</strong> {{ $destination->direccion }}</p>
                                @if($destination->referencia)
                                <p><strong>Referencia:</strong> {{ $destination->referencia }}</p>
                                @endif
                                @if($destination->nombre_contacto)
                                <p><strong>Contacto:</strong> {{ $destination->nombre_contacto }}</p>
                                @endif
                                @if($destination->telefono_contacto)
                                <p><strong>Teléfono:</strong> {{ $destination->telefono_contacto }}</p>
                                @endif
                                @if($destination->latitud && $destination->longitud)
                                <p>
                                    <strong>Coordenadas:</strong><br>
                                    Lat: {{ $destination->latitud }}, Lng: {{ $destination->longitud }}
                                </p>
                                @endif
                                
                                <h6>Productos para este destino:</h6>
                                <ul>
                                    @foreach($destination->destinationProducts as $destProduct)
                                    <li>
                                        {{ $destProduct->orderProduct->product->nombre }} - 
                                        Cantidad: {{ number_format($destProduct->cantidad, 4) }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Envíos creados en PlantaCruds -->
                <h5 class="mb-3 mt-4">Envíos en PlantaCruds</h5>
                @if(isset($trackings) && $trackings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Destino ID</th>
                                <th>Envío ID</th>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackings as $t)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $t->destino_id ?? 'N/A' }}</td>
                                <td>{{ $t->envio_id ?? 'N/A' }}</td>
                                <td>{{ $t->codigo_envio ?? 'N/A' }}</td>
                                <td>
                                    @if($t->estado == 'success')
                                        <span class="badge badge-success">Creado</span>
                                    @elseif($t->estado == 'failed')
                                        <span class="badge badge-danger">Error</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($t->estado) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($t->envio_id)
                                        <a href="{{ $plantaBase }}/envios/{{ $t->envio_id }}" target="_blank" class="btn btn-sm btn-primary">Ver en PlantaCruds</a>
                                    @endif
                                    @if($t->estado == 'failed' && $t->mensaje_error)
                                        <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#trackErrorModal{{ $t->id }}">Ver error</button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="trackErrorModal{{ $t->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Error Envío #{{ $t->id }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre style="white-space: pre-wrap;">{{ $t->mensaje_error }}</pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">No se han creado envíos todavía para este pedido.</div>
                @endif

                <!-- Acceso a Endpoints de PlantaCruds -->
                @if($envioId)
                <div class="card mt-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-truck"></i> Gestión de Propuesta de Vehículos (PlantaCruds)
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Este pedido tiene un envío creado en PlantaCruds (ID: <strong>{{ $envioId }}</strong>). 
                            Puedes descargar la propuesta de vehículos y aprobar o rechazar la propuesta.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-file-pdf text-danger"></i> Propuesta de Vehículos</h6>
                                @if($propuestaPdfUrl)
                                    <a href="{{ $propuestaPdfUrl }}" target="_blank" class="btn btn-danger btn-block">
                                        <i class="fas fa-download"></i> Descargar PDF de Propuesta
                                    </a>
                                    <small class="text-muted d-block mt-2">
                                        URL: <code>{{ $propuestaPdfUrl }}</code>
                                    </small>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        No se puede generar la propuesta. El envío aún no está disponible.
                                    </div>
                                @endif
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-check-circle text-success"></i> Aprobar/Rechazar Propuesta</h6>
                                @if($aprobarRechazarUrl && $mostrarAprobarRechazar)
                                    <div class="btn-group btn-block" role="group">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#aprobarPropuestaModal">
                                            <i class="fas fa-check"></i> Aprobar
                                        </button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rechazarPropuestaModal">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        URL: <code>{{ $aprobarRechazarUrl }}</code>
                                    </small>
                                @elseif($aprobarRechazarUrl && !$mostrarAprobarRechazar)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        El envío no está pendiente de aprobación por Trazabilidad. Solo se pueden aprobar/rechazar envíos con estado "pendiente_aprobacion_trazabilidad".
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        No se puede aprobar/rechazar. El envío aún no está disponible.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Aprobar Propuesta -->
                <div class="modal fade" id="aprobarPropuestaModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="aprobarPropuestaForm">
                                @csrf
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-check-circle"></i> Aprobar Propuesta de Vehículos
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de aprobar la propuesta de vehículos para el envío <strong>#{{ $envioId }}</strong>?</p>
                                    <p class="text-muted">Al aprobar, el envío cambiará su estado a "pendiente" y podrá proceder con la asignación del transportista.</p>
                                    <div class="form-group">
                                        <label>Observaciones (opcional)</label>
                                        <textarea class="form-control" name="observaciones" id="aprobarObservaciones" rows="3" placeholder="Comentarios sobre la aprobación..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Aprobar Propuesta
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Rechazar Propuesta -->
                <div class="modal fade" id="rechazarPropuestaModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="rechazarPropuestaForm">
                                @csrf
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-times-circle"></i> Rechazar Propuesta de Vehículos
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de rechazar la propuesta de vehículos para el envío <strong>#{{ $envioId }}</strong>?</p>
                                    <p class="text-muted">Al rechazar, el envío será cancelado.</p>
                                    <div class="form-group">
                                        <label>Observaciones <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="observaciones" id="rechazarObservaciones" rows="3" required placeholder="Razón del rechazo..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Rechazar Propuesta
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Mensaje de Éxito -->
                <div class="modal fade" id="mensajeExitoModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-check-circle"></i> Éxito
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Mensaje de Error -->
                <div class="modal fade" id="mensajeErrorModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-circle"></i> Error
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Mensaje de Advertencia -->
                <div class="modal fade" id="mensajeAdvertenciaModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle"></i> Advertencia
                                </h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-warning" data-dismiss="modal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                $(document).ready(function() {
                    const aprobarUrl = '{{ $aprobarRechazarUrl }}';
                    const rechazarUrl = '{{ $aprobarRechazarUrl }}';
                    const csrfToken = $('meta[name="csrf-token"]').attr('content');

                    // Manejar aprobación
                    let procesandoAprobacion = false;
                    $('#aprobarPropuestaForm').on('submit', function(e) {
                        e.preventDefault();
                        
                        // Prevenir múltiples envíos
                        if (procesandoAprobacion) {
                            return false;
                        }
                        procesandoAprobacion = true;
                        
                        const observaciones = $('#aprobarObservaciones').val();
                        const btnSubmit = $(this).find('button[type="submit"]');
                        const btnCancel = $(this).closest('.modal').find('button[data-dismiss="modal"]');
                        const originalText = btnSubmit.html();
                        
                        // Deshabilitar todos los botones del modal
                        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                        btnCancel.prop('disabled', true);
                        
                        $.ajax({
                            url: aprobarUrl,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            data: JSON.stringify({
                                accion: 'aprobar',
                                observaciones: observaciones || null
                            }),
                            success: function(response) {
                                if (response.success) {
                                    // Cerrar modal de aprobación
                                    $('#aprobarPropuestaModal').modal('hide');
                                    
                                    // Deshabilitar botones de aprobar/rechazar
                                    $('button[data-target="#aprobarPropuestaModal"]').prop('disabled', true).addClass('disabled');
                                    $('button[data-target="#rechazarPropuestaModal"]').prop('disabled', true).addClass('disabled');
                                    
                                    // Mostrar modal de éxito
                                    $('#mensajeExitoModal .modal-body p').text(response.message);
                                    $('#mensajeExitoModal').modal('show');
                                    
                                    // Recargar después de 1.5 segundos
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    procesandoAprobacion = false;
                                    btnSubmit.prop('disabled', false).html(originalText);
                                    btnCancel.prop('disabled', false);
                                    
                                    // Mostrar modal de error
                                    $('#mensajeErrorModal .modal-body p').text(response.message || 'Error desconocido');
                                    $('#mensajeErrorModal').modal('show');
                                }
                            },
                            error: function(xhr) {
                                procesandoAprobacion = false;
                                btnSubmit.prop('disabled', false).html(originalText);
                                btnCancel.prop('disabled', false);
                                
                                const error = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                                
                                // Mostrar modal de error
                                $('#mensajeErrorModal .modal-body p').text(error);
                                $('#mensajeErrorModal').modal('show');
                            }
                        });
                    });

                    // Manejar rechazo
                    let procesandoRechazo = false;
                    $('#rechazarPropuestaForm').on('submit', function(e) {
                        e.preventDefault();
                        
                        // Prevenir múltiples envíos
                        if (procesandoRechazo) {
                            return false;
                        }
                        
                        const observaciones = $('#rechazarObservaciones').val();
                        
                        if (!observaciones || observaciones.trim() === '') {
                            $('#mensajeAdvertenciaModal .modal-body p').text('Por favor, ingrese las observaciones del rechazo.');
                            $('#mensajeAdvertenciaModal').modal('show');
                            return;
                        }
                        
                        procesandoRechazo = true;
                        const btnSubmit = $(this).find('button[type="submit"]');
                        const btnCancel = $(this).closest('.modal').find('button[data-dismiss="modal"]');
                        const originalText = btnSubmit.html();
                        
                        // Deshabilitar todos los botones del modal
                        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                        btnCancel.prop('disabled', true);
                        
                        $.ajax({
                            url: rechazarUrl,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            data: JSON.stringify({
                                accion: 'rechazar',
                                observaciones: observaciones
                            }),
                            success: function(response) {
                                if (response.success) {
                                    // Cerrar modal de rechazo
                                    $('#rechazarPropuestaModal').modal('hide');
                                    
                                    // Deshabilitar botones de aprobar/rechazar
                                    $('button[data-target="#aprobarPropuestaModal"]').prop('disabled', true).addClass('disabled');
                                    $('button[data-target="#rechazarPropuestaModal"]').prop('disabled', true).addClass('disabled');
                                    
                                    // Mostrar modal de éxito
                                    $('#mensajeExitoModal .modal-body p').text(response.message);
                                    $('#mensajeExitoModal').modal('show');
                                    
                                    // Recargar después de 1.5 segundos
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    procesandoRechazo = false;
                                    btnSubmit.prop('disabled', false).html(originalText);
                                    btnCancel.prop('disabled', false);
                                    
                                    // Mostrar modal de error
                                    $('#mensajeErrorModal .modal-body p').text(response.message || 'Error desconocido');
                                    $('#mensajeErrorModal').modal('show');
                                }
                            },
                            error: function(xhr) {
                                procesandoRechazo = false;
                                btnSubmit.prop('disabled', false).html(originalText);
                                btnCancel.prop('disabled', false);
                                
                                const error = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                                
                                // Mostrar modal de error
                                $('#mensajeErrorModal .modal-body p').text(error);
                                $('#mensajeErrorModal').modal('show');
                            }
                        });
                    });
                    
                    // Resetear flags cuando se cierran los modales
                    $('#aprobarPropuestaModal').on('hidden.bs.modal', function() {
                        procesandoAprobacion = false;
                        $('#aprobarPropuestaForm')[0].reset();
                        $(this).find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check"></i> Aprobar Propuesta');
                        $(this).find('button[data-dismiss="modal"]').prop('disabled', false);
                    });
                    
                    $('#rechazarPropuestaModal').on('hidden.bs.modal', function() {
                        procesandoRechazo = false;
                        $('#rechazarPropuestaForm')[0].reset();
                        $(this).find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-times"></i> Rechazar Propuesta');
                        $(this).find('button[data-dismiss="modal"]').prop('disabled', false);
                    });
                });
                </script>
                @endpush
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

