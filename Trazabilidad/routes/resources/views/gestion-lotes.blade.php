@extends('layouts.app')

@section('page_title', 'Gestión de Lotes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes mr-1"></i>
                    Gestión de Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearLoteModal">
                        <i class="fas fa-plus"></i> Crear Lote
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $stats['total'] }}</h3>
                                <p>Total Lotes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
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
                                <h3>{{ $stats['completados'] }}</h3>
                                <p>Completados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['en_proceso'] }}</h3>
                                <p>En Proceso</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Lotes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Pedido Asociado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $lote)
                            <tr>
                                <td>#{{ $lote->lote_id }}</td>
                                <td>{{ $lote->nombre ?? 'Sin nombre' }}</td>
                                <td>
                                    @php
                                        $evaluation = $lote->finalEvaluation->first();
                                    @endphp
                                    @if($evaluation)
                                        @if(str_contains(strtolower($evaluation->razon ?? ''), 'falló'))
                                            <span class="badge badge-danger">No Certificado</span>
                                        @else
                                            <span class="badge badge-success">Certificado</span>
                                        @endif
                                    @elseif($lote->hora_inicio && !$lote->hora_fin)
                                        <span class="badge badge-warning">En Proceso</span>
                                    @else
                                        <span class="badge badge-info">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lote->order)
                                        {{ $lote->order->nombre ?? 'Sin nombre' }} - {{ $lote->order->customer->razon_social ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">Sin pedido</span>
                                    @endif
                                </td>
                                <td>{{ $lote->fecha_creacion ? \Carbon\Carbon::parse($lote->fecha_creacion)->format('Y-m-d') : 'N/A' }}</td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verLote({{ $lote->lote_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarLote({{ $lote->lote_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay lotes registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lotes->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $lotes->firstItem() }} a {{ $lotes->lastItem() }} de {{ $lotes->total() }} registros
                        </small>
                    </div>
                    {{ $lotes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Lote -->
<div class="modal fade" id="crearLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Lote</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
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
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('gestion-lotes') }}" id="crearLoteForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Nombre del Lote <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name') }}" 
                                       placeholder="Ej: Lote de producción #001" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="order_id">Pedido Asociado</label>
                                <select class="form-control" id="pedido_id" name="pedido_id" onchange="mostrarInfoReferenciaPedido(this)">
                                    <option value="">Sin pedido asociado</option>
                                    @foreach($pedidos as $pedido)
                                        <option value="{{ $pedido->pedido_id }}" 
                                                data-pedido-info="{{ json_encode([
                                                    'nombre' => $pedido->nombre ?? 'Sin nombre',
                                                    'fecha_entrega' => $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') : 'N/A',
                                                    'productos' => $pedido->orderProducts->map(function($op) {
                                                        return [
                                                            'nombre' => $op->product->nombre ?? 'N/A',
                                                            'tipo' => $op->product->tipo ?? 'N/A',
                                                            'cantidad' => number_format($op->cantidad, 2),
                                                            'unidad' => $op->product->unit->codigo ?? 'N/A'
                                                        ];
                                                    })->toArray()
                                                ]) }}"
                                                {{ old('pedido_id') == $pedido->pedido_id ? 'selected' : '' }}>
                                            {{ $pedido->nombre ?? 'Sin nombre' }} - {{ $pedido->customer->razon_social ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="referencia_pedido" class="mt-2" style="display: none;">
                                    <small class="text-muted">
                                        <strong>Información del pedido seleccionado:</strong><br>
                                        <span id="referencia_pedido_info"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_quantity">Cantidad Objetivo</label>
                                <input type="number" class="form-control" id="target_quantity" 
                                       name="target_quantity" value="{{ old('target_quantity') }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                <div id="referencia_cantidad" class="mt-2" style="display: none;">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <span id="referencia_cantidad_info"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materias Primas <span class="text-danger">*</span></label>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="materiasPrimasTable">
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-sm materia-prima-select" 
                                                    name="raw_materials[0][material_id]" 
                                                    onchange="updateAvailableQuantity(this)" required>
                                                <option value="">Seleccionar materia prima...</option>
                                                @foreach($materias_primas as $mp)
                                                    @php
                                                        $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
                                                    @endphp
                                                    <option value="{{ $mp->material_id }}" data-available="{{ $available }}">
                                                        {{ $mp->name }} ({{ $mp->unit->code ?? 'N/A' }}) - Disponible: {{ number_format($available, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted available-quantity" style="display: none;"></small>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm cantidad-input" 
                                                   name="raw_materials[0][planned_quantity]" 
                                                   placeholder="0.00" step="0.01" min="0" 
                                                   onchange="validateQuantity(this)" required>
                                            <small class="text-danger quantity-error" style="display: none;"></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMateriaPrima(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addMateriaPrima()">
                            <i class="fas fa-plus"></i> Agregar Materia Prima
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations') }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('crearLoteForm').submit();">Crear Lote</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Lote -->
<div class="modal fade" id="verLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Lote
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verLoteContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
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

<!-- Modal Editar Lote -->
<div class="modal fade" id="editarLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Lote
                </h4>
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
                <form method="POST" action="" id="editarLoteForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre del Lote <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="edit_name" name="name" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_order_id">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Pedido Asociado
                                </label>
                                <select class="form-control" id="edit_pedido_id" name="pedido_id">
                                    <option value="">Sin pedido asociado</option>
                                    @foreach($pedidos as $pedido)
                                        <option value="{{ $pedido->pedido_id }}">
                                            {{ $pedido->nombre ?? 'Sin nombre' }} - {{ $pedido->customer->razon_social ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_target_quantity">
                                    <i class="fas fa-weight mr-1"></i>
                                    Cantidad Objetivo
                                </label>
                                <input type="number" class="form-control" id="edit_target_quantity" 
                                       name="target_quantity" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_observations">
                            <i class="fas fa-comment-alt mr-1"></i>
                            Observaciones
                        </label>
                        <textarea class="form-control" id="edit_observations" name="observations" rows="3"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Lote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let materiaPrimaIndex = 1;

const materiasPrimasData = @json($materias_primas_json);

// Función para mostrar información de referencia del pedido
function mostrarInfoReferenciaPedido(select) {
    const selectedOption = select.options[select.selectedIndex];
    const referenciaPedido = document.getElementById('referencia_pedido');
    const referenciaPedidoInfo = document.getElementById('referencia_pedido_info');
    const referenciaCantidad = document.getElementById('referencia_cantidad');
    const referenciaCantidadInfo = document.getElementById('referencia_cantidad_info');
    
    if (select.value && selectedOption.getAttribute('data-pedido-info')) {
        try {
            const pedidoInfo = JSON.parse(selectedOption.getAttribute('data-pedido-info'));
            
            // Construir información de productos
            let productosInfo = '';
            if (pedidoInfo.productos && pedidoInfo.productos.length > 0) {
                productosInfo = pedidoInfo.productos.map(prod => {
                    return `• ${prod.nombre} (${prod.tipo}): ${prod.cantidad} ${prod.unidad}`;
                }).join('<br>');
            } else {
                productosInfo = 'No hay productos en este pedido';
            }
            
            // Mostrar información del pedido
            referenciaPedidoInfo.innerHTML = `
                <strong>Fecha requerida:</strong> ${pedidoInfo.fecha_entrega}<br>
                <strong>Productos solicitados:</strong><br>
                ${productosInfo}
            `;
            referenciaPedido.style.display = 'block';
            
            // Calcular total de cantidad requerida
            let totalCantidad = 0;
            if (pedidoInfo.productos && pedidoInfo.productos.length > 0) {
                pedidoInfo.productos.forEach(prod => {
                    const cantidad = parseFloat(prod.cantidad.replace(/,/g, '')) || 0;
                    totalCantidad += cantidad;
                });
            }
            
            // Mostrar referencia de cantidad
            if (totalCantidad > 0) {
                referenciaCantidadInfo.innerHTML = `Cantidad total requerida por el almacén: <strong>${totalCantidad.toFixed(2)}</strong>`;
                referenciaCantidad.style.display = 'block';
            } else {
                referenciaCantidad.style.display = 'none';
            }
        } catch (e) {
            console.error('Error al parsear información del pedido:', e);
            referenciaPedido.style.display = 'none';
            referenciaCantidad.style.display = 'none';
        }
    } else {
        referenciaPedido.style.display = 'none';
        referenciaCantidad.style.display = 'none';
    }
}

// Ejecutar al cargar la página si hay un pedido seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const pedidoSelect = document.getElementById('pedido_id');
    if (pedidoSelect && pedidoSelect.value) {
        mostrarInfoReferenciaPedido(pedidoSelect);
    }
});

// Agregar materia prima al formulario
function addMateriaPrima() {
    const tbody = document.getElementById('materiasPrimasTable');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm materia-prima-select" 
                    name="raw_materials[${materiaPrimaIndex}][material_id]" 
                    onchange="updateAvailableQuantity(this)" required>
                <option value="">Seleccionar materia prima...</option>
                ${materiasPrimasData.map(mp => `
                    <option value="${mp.material_id}" data-available="${mp.available}">
                        ${mp.name} (${mp.unit_code}) - Disponible: ${parseFloat(mp.available).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </option>
                `).join('')}
            </select>
            <small class="text-info available-quantity" style="display: none;"></small>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm cantidad-input" 
                   name="raw_materials[${materiaPrimaIndex}][planned_quantity]" 
                   placeholder="0.00" step="0.01" min="0" 
                   onchange="validateQuantity(this)" required>
            <small class="text-danger quantity-error" style="display: none;"></small>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMateriaPrima(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    materiaPrimaIndex++;
}

// Eliminar materia prima del formulario
function removeMateriaPrima(button) {
    const row = button.closest('tr');
    if (document.getElementById('materiasPrimasTable').rows.length > 1) {
        row.remove();
    } else {
        alert('Debe tener al menos una materia prima');
    }
}

// Actualizar cantidad disponible cuando se selecciona una materia prima
function updateAvailableQuantity(select) {
    const row = select.closest('tr');
    const selectedOption = select.options[select.selectedIndex];
    const available = parseFloat(selectedOption.getAttribute('data-available')) || 0;
    const availableSpan = row.querySelector('.available-quantity');
    
    if (select.value) {
        availableSpan.textContent = `Disponible: ${available.toFixed(2)}`;
        availableSpan.style.display = 'block';
        availableSpan.className = 'text-info available-quantity';
        
        // Actualizar el max del input de cantidad
        const cantidadInput = row.querySelector('.cantidad-input');
        if (cantidadInput) {
            cantidadInput.setAttribute('max', available);
            cantidadInput.setAttribute('data-available', available);
            
            // Validar cantidad si ya hay un valor
            if (cantidadInput.value) {
                validateQuantity(cantidadInput);
            }
        }
    } else {
        if (availableSpan) {
            availableSpan.style.display = 'none';
        }
    }
}

// Validar cantidad ingresada
function validateQuantity(input) {
    const row = input.closest('tr');
    const select = row.querySelector('.materia-prima-select');
    const errorSpan = row.querySelector('.quantity-error');
    const cantidad = parseFloat(input.value) || 0;
    
    if (!select.value) {
        errorSpan.textContent = 'Debe seleccionar una materia prima primero';
        errorSpan.style.display = 'block';
        input.classList.add('is-invalid');
        return false;
    }
    
    const available = parseFloat(select.options[select.selectedIndex].getAttribute('data-available')) || 0;
    
    if (cantidad <= 0) {
        errorSpan.textContent = 'La cantidad debe ser mayor a 0';
        errorSpan.style.display = 'block';
        input.classList.add('is-invalid');
        return false;
    }
    
    if (cantidad > available) {
        errorSpan.textContent = `La cantidad no puede ser mayor a ${available}`;
        errorSpan.style.display = 'block';
        input.classList.add('is-invalid');
        return false;
    }
    
    errorSpan.style.display = 'none';
    input.classList.remove('is-invalid');
    return true;
}

// Validar formulario antes de enviar
document.getElementById('crearLoteForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#materiasPrimasTable tr');
    let isValid = true;
    
    if (rows.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos una materia prima');
        return false;
    }
    
    rows.forEach(row => {
        const select = row.querySelector('.materia-prima-select');
        const input = row.querySelector('.cantidad-input');
        
        if (!select.value || !input.value || !validateQuantity(input)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Por favor complete correctamente todos los campos de materias primas');
        return false;
    }
    
    return true;
});

// Inicializar primera fila cuando se carga el modal
$('#crearLoteModal').on('shown.bs.modal', function() {
    materiaPrimaIndex = 1;
    const firstSelect = document.querySelector('#materiasPrimasTable .materia-prima-select');
    if (firstSelect) {
        firstSelect.innerHTML = `
            <option value="">Seleccionar materia prima...</option>
            ${materiasPrimasData.map(mp => `
                <option value="${mp.material_id}" data-available="${mp.available}">
                    ${mp.name} (${mp.unit_code}) - Disponible: ${parseFloat(mp.available).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </option>
            `).join('')}
        `;
    }
});

function verLote(id) {
    fetch(`{{ url('gestion-lotes') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            let rawMaterialsHtml = '';
            if (data.raw_materials && data.raw_materials.length > 0) {
                rawMaterialsHtml = '<table class="table table-sm table-bordered"><thead><tr><th>Materia Prima</th><th>Proveedor</th><th>Cantidad Planificada</th><th>Cantidad Usada</th></tr></thead><tbody>';
                data.raw_materials.forEach(function(rm) {
                    rawMaterialsHtml += `<tr><td>${rm.material_name} (${rm.unit})</td><td>${rm.supplier}</td><td>${rm.planned_quantity}</td><td>${rm.used_quantity || 0}</td></tr>`;
                });
                rawMaterialsHtml += '</tbody></table>';
            } else {
                rawMaterialsHtml = '<p class="text-muted">No hay materias primas asignadas</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.batch_id}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name}</td>
                            </tr>
                            <tr>
                                <th>Pedido</th>
                                <td>${data.order_name ? data.order_name + ' - ' + (data.customer_name || 'N/A') : 'Sin pedido'}</td>
                            </tr>
                            <tr>
                                <th>Fecha Creación</th>
                                <td>${data.creation_date}</td>
                            </tr>
                            <tr>
                                <th>Fecha Inicio</th>
                                <td>${data.start_time || 'No iniciado'}</td>
                            </tr>
                            <tr>
                                <th>Fecha Fin</th>
                                <td>${data.end_time || 'No finalizado'}</td>
                            </tr>
                            <tr>
                                <th>Cantidad Objetivo</th>
                                <td>${data.target_quantity || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Cantidad Producida</th>
                                <td>${data.produced_quantity || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Materias Primas</th>
                                <td>${rawMaterialsHtml}</td>
                            </tr>
                            ${data.evaluation ? `
                            <tr>
                                <th>Evaluación Final</th>
                                <td>
                                    <strong>Razón:</strong> ${data.evaluation.reason}<br>
                                    <strong>Observaciones:</strong> ${data.evaluation.observations || 'N/A'}<br>
                                    <strong>Fecha:</strong> ${data.evaluation.evaluation_date}
                                </td>
                            </tr>
                            ` : ''}
                            <tr>
                                <th>Observaciones</th>
                                <td>${data.observations || 'Sin observaciones'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verLoteContent').innerHTML = content;
            $('#verLoteModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del lote');
        });
}

function editarLote(id) {
    fetch(`{{ url('gestion-lotes') }}/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarLoteForm').action = `{{ url('gestion-lotes') }}/${id}`;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_pedido_id').value = data.order_id || '';
            document.getElementById('edit_target_quantity').value = data.target_quantity || '';
            document.getElementById('edit_observations').value = data.observations || '';
            $('#editarLoteModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del lote para editar');
        });
}

function addMateriaPrima() {
    const table = document.getElementById('materiasPrimasTable');
    const row = table.insertRow();
    let optionsHtml = '<option value="">Seleccionar materia prima...</option>';
    materiasPrimasData.forEach(function(mp) {
        optionsHtml += `<option value="${mp.material_id}" data-available="${mp.available}">${mp.name} (${mp.unit_code}) - Disponible: ${parseFloat(mp.available).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</option>`;
    });
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm" name="raw_materials[${materiaPrimaIndex}][material_id]" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" 
                   name="raw_materials[${materiaPrimaIndex}][planned_quantity]" 
                   placeholder="0.00" step="0.01" min="0" required>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMateriaPrima(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    materiaPrimaIndex++;
}

function removeMateriaPrima(button) {
    const row = button.closest('tr');
    if (document.getElementById('materiasPrimasTable').rows.length > 1) {
        row.remove();
    } else {
        alert('Debe tener al menos una materia prima');
    }
}
</script>
@endpush

