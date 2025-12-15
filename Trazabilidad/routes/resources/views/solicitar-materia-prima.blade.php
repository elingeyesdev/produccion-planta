@extends('layouts.app')

@section('page_title', 'Solicitar Materia Prima')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Solicitar Materia Prima
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearSolicitudModal">
                        <i class="fas fa-plus"></i> Nueva Solicitud
                    </button>
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $stats['total'] ?? 0 }}</h3>
                                <p>Total Solicitudes</p>
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
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['completadas'] ?? 0 }}</h3>
                                <p>Completadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $pedidos->count() }}</h3>
                                <p>Pedidos Sin Solicitud</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="entregada">Entregada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por solicitante..." id="buscarSolicitante">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Solicitudes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Materia Prima</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                            <tr>
                                <td>#{{ $solicitud->solicitud_id }}</td>
                                <td>{{ $solicitud->order && $solicitud->order->customer ? $solicitud->order->customer->razon_social : ($solicitud->order ? 'N/A' : 'Sin pedido') }}</td>
                                <td>
                                    @if($solicitud->details && $solicitud->details->count() > 0)
                                        @foreach($solicitud->details as $detail)
                                            {{ $detail->material ? $detail->material->nombre : 'N/A' }}<br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Sin detalles</span>
                                    @endif
                                </td>
                                <td>
                                    @if($solicitud->details && $solicitud->details->count() > 0)
                                        @foreach($solicitud->details as $detail)
                                            {{ number_format($detail->cantidad_solicitada ?? 0, 2) }} {{ $detail->material && $detail->material->unit ? $detail->material->unit->codigo : '' }}<br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Verificar si la solicitud está completada basándose en si todos los detalles tienen cantidad aprobada
                                        $completada = false;
                                        if ($solicitud->details && $solicitud->details->count() > 0) {
                                            $completada = $solicitud->details->every(function($detail) {
                                                return ($detail->cantidad_aprobada ?? 0) >= ($detail->cantidad_solicitada ?? 0) && ($detail->cantidad_solicitada ?? 0) > 0;
                                            });
                                        }
                                    @endphp
                                    @if($completada && $solicitud->details && $solicitud->details->count() > 0)
                                        <span class="badge badge-success">Completada</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $solicitud->fecha_solicitud ? \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $solicitud->fecha_requerida ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay solicitudes registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($solicitudes->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} registros
                        </small>
                    </div>
                    {{ $solicitudes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Solicitud -->
<style>
    #crearSolicitudModal .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #crearSolicitudModal .modal-dialog {
        max-height: calc(100vh - 50px);
        margin: 25px auto;
    }
    
    #crearSolicitudModal .modal-content {
        max-height: calc(100vh - 50px);
        display: flex;
        flex-direction: column;
    }
    
    #crearSolicitudModal .modal-footer {
        flex-shrink: 0;
    }
    
    #crearSolicitudModal .modal-header {
        flex-shrink: 0;
    }
</style>

<div class="modal fade" id="crearSolicitudModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Solicitud de Materia Prima</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('solicitar-materia-prima') }}" id="crearSolicitudForm">
                    @csrf
                    
                    <!-- Pedido Asociado -->
                    <div class="form-group">
                        <label for="pedido_id">
                            <i class="fas fa-shopping-cart mr-1"></i>
                            Pedido Asociado <span class="text-danger">*</span>
                        </label>
                        <select class="form-control @error('pedido_id') is-invalid @enderror" 
                                id="pedido_id" name="pedido_id" required onchange="cargarMateriasPrimasDelPedido(this.value)">
                            <option value="">Seleccionar pedido...</option>
                            @foreach($pedidos as $pedido)
                                <option value="{{ $pedido->pedido_id }}" {{ old('pedido_id') == $pedido->pedido_id ? 'selected' : '' }}>
                                    {{ $pedido->numero_pedido ?? $pedido->pedido_id }} - {{ $pedido->nombre ?? 'Sin nombre' }} - {{ $pedido->customer->razon_social ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('pedido_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Seleccione el pedido al que pertenece esta solicitud</small>
                    </div>
                    
                    <!-- Recordatorio del Pedido -->
                    <div id="recordatorioPedido" class="alert alert-info" style="display: none;">
                        <h6 class="mb-2"><i class="fas fa-info-circle mr-2"></i><strong>Recordatorio del Pedido:</strong></h6>
                        <div id="recordatorioContenido"></div>
                    </div>
                    
                    <!-- Fecha -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_requerida">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Fecha Requerida <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('fecha_requerida') is-invalid @enderror" 
                                       id="fecha_requerida" name="fecha_requerida" 
                                       value="{{ old('fecha_requerida') }}" required 
                                       min="{{ date('Y-m-d') }}">
                                @error('fecha_requerida')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted" id="fechaRequeridaHelp">
                                    Seleccione una fecha entre hoy y la fecha de entrega del pedido
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dirección de Entrega -->
                    <div class="form-group">
                        <label for="direccion_entrega">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección de Entrega <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion_entrega" name="direccion" 
                                   value="{{ old('direccion') }}" required 
                                   placeholder="Ingrese la dirección donde debe llegar la materia prima">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" onclick="openMapSolicitud()">
                                    <i class="fas fa-map"></i> Mapa
                                </button>
                            </div>
                        </div>
                        @error('direccion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Puede ingresar la dirección manualmente o seleccionarla en el mapa</small>
                        <input type="hidden" id="latitud_solicitud" name="latitud" value="{{ old('latitud') }}">
                        <input type="hidden" id="longitud_solicitud" name="longitud" value="{{ old('longitud') }}">
                    </div>
                    
                    <!-- Materias Primas -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-boxes mr-1"></i>
                            Materias Primas <span class="text-danger">*</span>
                        </label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 60%;">Materia Prima</th>
                                        <th style="width: 30%;">Cantidad</th>
                                        <th style="width: 10%;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="materialsTable">
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-sm" name="materials[0][material_id]" required>
                                                <option value="">Seleccionar materia prima...</option>
                                                @foreach($materias_primas as $mp)
                                                    <option value="{{ $mp->material_id }}">
                                                        {{ $mp->nombre }} ({{ $mp->unit->codigo ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" 
                                                       name="materials[0][cantidad_solicitada]" 
                                                       placeholder="0.00" step="0.01" min="0" required>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="addMaterial()">
                            <i class="fas fa-plus"></i> Agregar Materia Prima
                        </button>
                        <small class="form-text text-muted d-block mt-1">Agregue al menos una materia prima a la solicitud</small>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observations">
                            <i class="fas fa-comment-alt mr-1"></i>
                            Observaciones
                        </label>
                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                  id="observations" name="observations" 
                                  rows="3" placeholder="Ingrese observaciones adicionales sobre la solicitud...">{{ old('observations') }}</textarea>
                        @error('observations')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" form="crearSolicitudForm" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Crear Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Mapa -->
<div class="modal fade" id="mapModalSolicitud" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Ubicación en el Mapa</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="mapSolicitud" style="height: 400px; width: 100%;"></div>
                <div class="mt-3">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="mapAddressSolicitud">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" class="form-control" id="mapLatitudeSolicitud" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" class="form-control" id="mapLongitudeSolicitud" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveMapLocationSolicitud()">Guardar Ubicación</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let materialIndex = 1;
const materiasPrimas = @json($materias_primas_json ?? []);
let pedidoMateriasPrimasData = {}; // Almacenar datos de materias primas del pedido

// Función para cargar materias primas del pedido seleccionado
function cargarMateriasPrimasDelPedido(pedidoId) {
    if (!pedidoId) {
        // Si no hay pedido seleccionado, limpiar la tabla
        limpiarTablaMateriasPrimas();
        ocultarRecordatorio();
        return;
    }
    
    // Mostrar indicador de carga
    const table = document.getElementById('materialsTable');
    table.innerHTML = '<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando información del pedido...</td></tr>';
    
    // Hacer petición AJAX
    fetch(`{{ url('solicitar-materia-prima/pedido') }}/${pedidoId}/materias-primas`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Almacenar datos de materias primas del pedido para uso posterior
            pedidoMateriasPrimasData = {};
            if (data.materias_primas && data.materias_primas.length > 0) {
                data.materias_primas.forEach(function(mp) {
                    pedidoMateriasPrimasData[mp.material_id] = mp;
                });
            }
            
            // Mostrar recordatorio del pedido
            mostrarRecordatorioPedido(data.pedido, data.materias_primas || []);
            
            // Actualizar fecha requerida y límites si viene del pedido
            const fechaInput = document.getElementById('fecha_requerida');
            const fechaHelp = document.getElementById('fechaRequeridaHelp');
            
            if (data.pedido && data.pedido.fecha_entrega) {
                // Establecer el máximo como la fecha de entrega del pedido
                fechaInput.setAttribute('max', data.pedido.fecha_entrega);
                
                // Actualizar el texto de ayuda
                if (fechaHelp) {
                    const fechaEntregaFormatted = data.pedido.fecha_entrega_formatted || data.pedido.fecha_entrega;
                    fechaHelp.textContent = `Seleccione una fecha entre hoy y ${fechaEntregaFormatted} (fecha de entrega del pedido)`;
                    fechaHelp.className = 'form-text text-info';
                }
                
                // Si no hay fecha seleccionada, establecer la fecha de entrega como valor por defecto
                if (fechaInput && !fechaInput.value) {
                    fechaInput.value = data.pedido.fecha_entrega;
                }
            } else {
                // Si no hay fecha de entrega, quitar el límite máximo
                fechaInput.removeAttribute('max');
                if (fechaHelp) {
                    fechaHelp.textContent = 'Seleccione una fecha a partir de hoy';
                    fechaHelp.className = 'form-text text-muted';
                }
            }
            
            if (data.materias_primas && data.materias_primas.length > 0) {
                // Limpiar tabla
                table.innerHTML = '';
                materialIndex = 0;
                
                // Agregar cada materia prima necesaria
                data.materias_primas.forEach(function(mp) {
                    addMaterialWithData(mp);
                });
            } else {
                // No hay materias primas necesarias o no se encontraron - mostrar fila vacía
                table.innerHTML = `
                    <tr>
                        <td>
                            <select class="form-control form-control-sm" name="materials[0][material_id]" required>
                                <option value="">Seleccionar materia prima...</option>
                                @foreach($materias_primas as $mp)
                                    <option value="{{ $mp->material_id }}">
                                        {{ $mp->nombre }} ({{ $mp->unit->codigo ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" 
                                       name="materials[0][cantidad_solicitada]" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                materialIndex = 1;
            }
        } else {
            ocultarRecordatorio();
            table.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Error al cargar información del pedido.
                        </div>
                    </td>
                </tr>
            `;
            materialIndex = 1;
        }
    })
    .catch(error => {
        console.error('Error al cargar materias primas:', error);
        ocultarRecordatorio();
        table.innerHTML = `
            <tr>
                <td colspan="3" class="text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error al cargar materias primas. Puede agregarlas manualmente.
                    </div>
                </td>
            </tr>
        `;
        materialIndex = 1;
    });
}

// Función para agregar una materia prima con datos predefinidos
function addMaterialWithData(mpData) {
    const table = document.getElementById('materialsTable');
    const row = table.insertRow();
    
    let optionsHtml = '<option value="">Seleccionar materia prima...</option>';
    materiasPrimas.forEach(function(mp) {
        const selected = mp.material_id == mpData.material_id ? 'selected' : '';
        optionsHtml += `<option value="${mp.material_id}" ${selected}>${mp.nombre} (${mp.unit ? mp.unit.codigo : 'N/A'})</option>`;
    });
    
    // Usar la cantidad mínima a solicitar como valor por defecto
    const cantidadDefault = mpData.cantidad_minima_solicitar > 0 ? mpData.cantidad_minima_solicitar : mpData.cantidad_requerida;
    
    const rowIndex = materialIndex;
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm" name="materials[${rowIndex}][material_id]" required onchange="mostrarCantidadNecesaria(this, ${rowIndex})">
                ${optionsHtml}
            </select>
            <small class="text-muted d-block mt-1" id="cantidadNecesaria_${rowIndex}">
                <i class="fas fa-info-circle text-info"></i> 
                <strong>Recordatorio:</strong> Para este pedido necesitas <strong>${mpData.cantidad_requerida.toFixed(2)} ${mpData.unidad.codigo}</strong> de esta materia prima
            </small>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control" 
                       name="materials[${rowIndex}][cantidad_solicitada]" 
                       value="${cantidadDefault.toFixed(2)}"
                       placeholder="0.00" step="0.01" min="0" required>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle text-info"></i> 
                <strong>Recordatorio:</strong> Disponible: ${mpData.cantidad_disponible.toFixed(2)} ${mpData.unidad.codigo}, 
                Mínimo sugerido: ${mpData.cantidad_minima_solicitar.toFixed(2)} ${mpData.unidad.codigo}
            </small>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    materialIndex++;
}

// Función para limpiar la tabla de materias primas
function limpiarTablaMateriasPrimas() {
    const table = document.getElementById('materialsTable');
    table.innerHTML = `
        <tr>
            <td>
                <select class="form-control form-control-sm" name="materials[0][material_id]" required onchange="mostrarCantidadNecesaria(this, 0)">
                    <option value="">Seleccionar materia prima...</option>
                    @foreach($materias_primas as $mp)
                        <option value="{{ $mp->material_id }}">
                            {{ $mp->nombre }} ({{ $mp->unit->codigo ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1" id="cantidadNecesaria_0" style="display: none;">
                    <i class="fas fa-info-circle text-info"></i> 
                    <strong>Recordatorio:</strong> <span id="cantidadTexto_0"></span>
                </small>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" 
                           name="materials[0][cantidad_solicitada]" 
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    materialIndex = 1;
    
    // Limpiar límites de fecha requerida
    const fechaInput = document.getElementById('fecha_requerida');
    const fechaHelp = document.getElementById('fechaRequeridaHelp');
    if (fechaInput) {
        fechaInput.removeAttribute('max');
        fechaInput.value = '';
    }
    if (fechaHelp) {
        fechaHelp.textContent = 'Seleccione una fecha a partir de hoy';
        fechaHelp.className = 'form-text text-muted';
    }
    
    // Ocultar recordatorio
    ocultarRecordatorio();
}

function addMaterial() {
    const table = document.getElementById('materialsTable');
    const row = table.insertRow();
    
    let optionsHtml = '<option value="">Seleccionar materia prima...</option>';
    materiasPrimas.forEach(function(mp) {
        optionsHtml += `<option value="${mp.material_id}">${mp.nombre} (${mp.unit ? mp.unit.codigo : 'N/A'})</option>`;
    });
    
    const rowIndex = materialIndex;
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm" name="materials[${rowIndex}][material_id]" required onchange="mostrarCantidadNecesaria(this, ${rowIndex})">
                ${optionsHtml}
            </select>
            <small class="text-muted d-block mt-1" id="cantidadNecesaria_${rowIndex}" style="display: none;">
                <i class="fas fa-info-circle text-info"></i> 
                <strong>Recordatorio:</strong> <span id="cantidadTexto_${rowIndex}"></span>
            </small>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control" 
                       name="materials[${rowIndex}][cantidad_solicitada]" 
                       placeholder="0.00" step="0.01" min="0" required>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    materialIndex++;
}

// Función para mostrar la cantidad necesaria cuando se selecciona una materia prima
function mostrarCantidadNecesaria(selectElement, rowIndex) {
    const materialId = selectElement.value;
    const cantidadNecesariaDiv = document.getElementById(`cantidadNecesaria_${rowIndex}`);
    const cantidadTexto = document.getElementById(`cantidadTexto_${rowIndex}`);
    
    if (materialId && pedidoMateriasPrimasData[materialId]) {
        const mpData = pedidoMateriasPrimasData[materialId];
        cantidadTexto.textContent = `Para este pedido necesitas ${mpData.cantidad_requerida.toFixed(2)} ${mpData.unidad.codigo} de esta materia prima`;
        cantidadNecesariaDiv.style.display = 'block';
    } else {
        cantidadNecesariaDiv.style.display = 'none';
    }
}

function removeMaterial(button) {
    const row = button.closest('tr');
    const table = document.getElementById('materialsTable');
    if (table.rows.length > 1) {
        row.remove();
        // Reindexar los nombres de los campos
        reindexMaterials();
    } else {
        alert('Debe tener al menos una materia prima en la solicitud');
    }
}

function reindexMaterials() {
    const table = document.getElementById('materialsTable');
    const rows = table.querySelectorAll('tr');
    rows.forEach(function(row, index) {
        const materialSelect = row.querySelector('select[name*="[material_id]"]');
        const quantityInput = row.querySelector('input[name*="[cantidad_solicitada]"]');
        
        if (materialSelect) {
            materialSelect.name = `materials[${index}][material_id]`;
        }
        if (quantityInput) {
            quantityInput.name = `materials[${index}][cantidad_solicitada]`;
        }
    });
    materialIndex = rows.length;
}

function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const fecha = document.getElementById('filtroFecha').value;
    const solicitante = document.getElementById('buscarSolicitante').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    else url.searchParams.delete('estado');
    if (fecha) url.searchParams.set('fecha', fecha);
    else url.searchParams.delete('fecha');
    if (solicitante) url.searchParams.set('solicitante', solicitante);
    else url.searchParams.delete('solicitante');
    window.location = url;
}

// Validar formulario antes de enviar
document.getElementById('crearSolicitudForm').addEventListener('submit', function(e) {
    const materialsTable = document.getElementById('materialsTable');
    const rows = materialsTable.querySelectorAll('tr');
    let hasValidMaterial = false;
    
    rows.forEach(function(row) {
        const materialSelect = row.querySelector('select[name*="[material_id]"]');
        const quantityInput = row.querySelector('input[name*="[cantidad_solicitada]"]');
        
        if (materialSelect && materialSelect.value && quantityInput && quantityInput.value > 0) {
            hasValidMaterial = true;
        }
    });
    
    if (!hasValidMaterial) {
        e.preventDefault();
        alert('Por favor, agregue al menos una materia prima con cantidad válida');
        return false;
    }
    
    // Mostrar indicador de carga
    const submitBtn = document.querySelector('button[type="submit"][form="crearSolicitudForm"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';
    }
    
    // Cerrar el modal
    $('#crearSolicitudModal').modal('hide');
    
    // El formulario se enviará normalmente y el servidor hará redirect
    // El redirect ya recarga la página automáticamente
});

// Función para mostrar el recordatorio del pedido
function mostrarRecordatorioPedido(pedido, materiasPrimas) {
    const recordatorioDiv = document.getElementById('recordatorioPedido');
    const contenidoDiv = document.getElementById('recordatorioContenido');
    
    if (!recordatorioDiv || !contenidoDiv) return;
    
    let html = `
        <div class="row">
            <div class="col-md-12 mb-2">
                <strong>Pedido:</strong> ${pedido.numero_pedido} - ${pedido.nombre || 'Sin nombre'}
            </div>
    `;
    
    if (pedido.fecha_entrega_formatted) {
        html += `
            <div class="col-md-12 mb-2">
                <i class="fas fa-calendar-alt text-warning"></i> 
                <strong>Fecha requerida por el almacén:</strong> 
                <span class="badge badge-warning badge-lg">${pedido.fecha_entrega_formatted}</span>
            </div>
        `;
    }
    
    if (materiasPrimas && materiasPrimas.length > 0) {
        html += `
            <div class="col-md-12">
                <strong class="d-block mb-2"><i class="fas fa-boxes"></i> Cantidades mínimas necesarias de materias primas:</strong>
                <ul class="mb-0">
        `;
        
        materiasPrimas.forEach(function(mp) {
            html += `
                <li class="mb-1">
                    <strong>${mp.nombre}</strong>: 
                    <span class="badge badge-warning badge-lg">${mp.cantidad_requerida.toFixed(2)} ${mp.unidad.codigo}</span>
                    ${mp.cantidad_disponible > 0 ? `<small class="text-muted">(Disponible: ${mp.cantidad_disponible.toFixed(2)} ${mp.unidad.codigo})</small>` : ''}
                </li>
            `;
        });
        
        html += `
                </ul>
            </div>
        `;
    } else {
        html += `
            <div class="col-md-12">
                <small class="text-muted">No se encontraron materias primas relacionadas con este pedido.</small>
            </div>
        `;
    }
    
    html += `</div>`;
    
    contenidoDiv.innerHTML = html;
    recordatorioDiv.style.display = 'block';
}

// Función para ocultar el recordatorio
function ocultarRecordatorio() {
    const recordatorioDiv = document.getElementById('recordatorioPedido');
    if (recordatorioDiv) {
        recordatorioDiv.style.display = 'none';
    }
}

// Si hay un pedido preseleccionado (por old), cargar sus materias primas
@if(old('pedido_id'))
    document.addEventListener('DOMContentLoaded', function() {
        const pedidoSelect = document.getElementById('pedido_id');
        if (pedidoSelect && pedidoSelect.value) {
            cargarMateriasPrimasDelPedido(pedidoSelect.value);
        }
    });
@endif

// También cargar si el modal se abre con un pedido ya seleccionado
$('#crearSolicitudModal').on('shown.bs.modal', function() {
    const pedidoSelect = document.getElementById('pedido_id');
    if (pedidoSelect && pedidoSelect.value) {
        cargarMateriasPrimasDelPedido(pedidoSelect.value);
    }
});

// Variables para el mapa de solicitud
let mapSolicitud = null;
let markerSolicitud = null;

// Función para abrir el mapa de solicitud
function openMapSolicitud() {
    $('#mapModalSolicitud').modal('show');
    
    setTimeout(() => {
        if (!mapSolicitud) {
            mapSolicitud = L.map('mapSolicitud').setView([-17.8146, -63.1561], 13); // Santa Cruz de la Sierra, Bolivia
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapSolicitud);
            
            mapSolicitud.on('click', function(e) {
                if (markerSolicitud) {
                    mapSolicitud.removeLayer(markerSolicitud);
                }
                markerSolicitud = L.marker([e.latlng.lat, e.latlng.lng]).addTo(mapSolicitud);
                document.getElementById('mapLatitudeSolicitud').value = e.latlng.lat;
                document.getElementById('mapLongitudeSolicitud').value = e.latlng.lng;
            });
        }
    }, 300);
}

// Función para guardar la ubicación del mapa de solicitud
function saveMapLocationSolicitud() {
    if (markerSolicitud) {
        const lat = document.getElementById('mapLatitudeSolicitud').value;
        const lng = document.getElementById('mapLongitudeSolicitud').value;
        const address = document.getElementById('mapAddressSolicitud').value;
        
        document.getElementById('latitud_solicitud').value = lat;
        document.getElementById('longitud_solicitud').value = lng;
        if (address) {
            document.getElementById('direccion_entrega').value = address;
        }
        
        $('#mapModalSolicitud').modal('hide');
    } else {
        alert('Por favor, seleccione una ubicación en el mapa haciendo clic');
    }
}

// Limpiar el mapa cuando se cierra el modal
$('#mapModalSolicitud').on('hidden.bs.modal', function() {
    // No limpiar el mapa para mantener la selección
});
</script>
@endpush

