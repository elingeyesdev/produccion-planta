@extends('layouts.app')

@section('page_title', 'Recepción de Materia Prima')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i>
                    Recepción de Materia Prima
                </h3>
                <div class="card-tools">
                    <form action="{{ route('recepcion-materia-prima.sync-envios') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Desea sincronizar envíos desde la API de Trazabilidad? Esto procesará automáticamente las recepciones de envíos entregados.');">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fas fa-sync-alt"></i> Sincronizar Envíos API
                        </button>
                    </form>
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
                                <h3>{{ $stats['total_recepciones'] }}</h3>
                                <p>Total Recepciones</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['completadas'] }}</h3>
                                <p>Completadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $stats['pendientes'] }}</h3>
                                <p>Solicitudes Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $materias_primas->count() }}</h3>
                                <p>Recientes</p>
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
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha" value="{{ request('fecha', '') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por proveedor..." id="buscarProveedor" value="{{ request('proveedor', '') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['estado', 'fecha', 'proveedor']))
                            <a href="{{ route('recepcion-materia-prima') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tabla de Solicitudes Pendientes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Solicitudes Pendientes de Recepción</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Solicitud</th>
                                        <th>Pedido</th>
                                        <th>Materias Primas</th>
                                        <th>Cantidades</th>
                                        <th>Fecha Requerida</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($solicitudes as $solicitud)
                                    <tr>
                                        <td>{{ $solicitud->solicitud_id }}</td>
                                        <td>{{ $solicitud->order->nombre ?? 'Sin nombre' }}</td>
                                        <td>
                                            @foreach($solicitud->details as $detail)
                                                {{ $detail->material->nombre ?? 'N/A' }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($solicitud->details as $detail)
                                                {{ number_format($detail->cantidad_solicitada, 2) }} {{ $detail->material->unit->codigo ?? '' }}<br>
                                            @endforeach
                                        </td>
                                        <td>{{ $solicitud->fecha_requerida ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @foreach($solicitud->details as $detail)
                                                <button class="btn btn-primary btn-sm mb-1" 
                                                        onclick="recepcionarMaterial({{ $solicitud->solicitud_id }}, {{ $detail->material_id }}, '{{ $detail->material->nombre }}', {{ $detail->cantidad_solicitada }}, '{{ $detail->material->unit->codigo ?? '' }}')" 
                                                        title="Recepcionar {{ $detail->material->nombre }}">
                                                    <i class="fas fa-check"></i> Recepcionar {{ $detail->material->name }}
                                                </button><br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay solicitudes pendientes</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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

                <!-- Tabla de Recepciones Recientes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recepciones Recientes</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Proveedor</th>
                                        <th>Materia Prima</th>
                                        <th>Cantidad</th>
                                        <th>Fecha Recepción</th>
                                        <th>Conforme</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($materias_primas as $mp)
                                    <tr>
                                        <td>#{{ $mp->materia_prima_id }}</td>
                                        <td>{{ $mp->supplier->razon_social ?? 'N/A' }}</td>
                                        <td>{{ $mp->materialBase->nombre ?? 'N/A' }}</td>
                                        <td>{{ number_format($mp->cantidad, 2) }} {{ $mp->materialBase->unit->codigo ?? '' }}</td>
                                        <td>{{ $mp->fecha_recepcion ? \Carbon\Carbon::parse($mp->fecha_recepcion)->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @if($mp->conformidad_recepcion)
                                                <span class="badge badge-success">Sí</span>
                                            @else
                                                <span class="badge badge-danger">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm" title="Ver" onclick="verDetalleRecepcion({{ $mp->materia_prima_id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay recepciones registradas</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Recepcionar desde Solicitud (similar al proyecto antiguo) -->
<div class="modal fade" id="recepcionarSolicitudModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Recepción de Materia Prima</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="recepcionError" class="alert alert-danger" style="display: none;"></div>
                <div id="recepcionSuccess" class="alert alert-success" style="display: none;"></div>
                
                <form method="POST" action="{{ route('recepcion-materia-prima') }}" id="recepcionarSolicitudForm">
                    @csrf
                    <input type="hidden" id="recepcion_solicitud_id" name="solicitud_id" value="">
                    <input type="hidden" id="recepcion_material_id" name="material_id" value="">
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Información del Material</h5>
                            <br>
                            <p class="mb-1"><strong>Nombre:</strong> <span id="recepcion_material_name">-</span></p>
                            <p class="mb-1"><strong>Cantidad Solicitada:</strong> <span id="recepcion_requested_quantity">-</span> <span id="recepcion_unit">-</span></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recepcion_supplier_id">
                                    <i class="fas fa-truck mr-1"></i>
                                    Proveedor <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="recepcion_proveedor_id" name="proveedor_id" required>
                                    <option value="">Seleccionar proveedor...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->proveedor_id }}">
                                            {{ $prov->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recepcion_quantity">
                                    <i class="fas fa-weight mr-1"></i>
                                    Cantidad Recibida <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" 
                                       id="recepcion_cantidad" name="cantidad" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recepcion_receipt_date">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Fecha de Recepción <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" 
                                       id="recepcion_fecha_recepcion" name="fecha_recepcion" 
                                       value="{{ date('Y-m-d') }}"
                                       min="{{ date('Y-m-d') }}"
                                       title="Seleccione una fecha entre la fecha de solicitud y la fecha requerida"
                                       required>
                                <small class="form-text text-muted" id="fechaRecepcionHelp">
                                    Seleccione una fecha a partir de hoy
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recepcion_invoice_number">
                                    <i class="fas fa-file-invoice mr-1"></i>
                                    Número de Factura <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" 
                                       id="recepcion_numero_factura" name="numero_factura" 
                                       placeholder="Ej: FACT-001" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="recepcion_conformidad" name="conformidad_recepcion" 
                                   value="1" checked>
                            <label class="custom-control-label" for="recepcion_conformidad">
                                <i class="fas fa-check-circle mr-1"></i>
                                Recepción conforme
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-signature mr-1"></i>
                            Firma de Recepción <span class="text-danger">*</span>
                        </label>
                        <div class="border rounded p-2 mb-2" style="background-color: #f8f9fa; position: relative;">
                            <canvas id="signatureCanvas" style="border: 1px solid #ddd; cursor: crosshair; display: block; width: 100%; touch-action: none;"></canvas>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature()">
                            <i class="fas fa-eraser mr-1"></i> Limpiar Firma
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="recepcion_observations">
                            <i class="fas fa-comment-alt mr-1"></i>
                            Observaciones
                        </label>
                        <textarea class="form-control" id="recepcion_observaciones" name="observaciones" 
                                  rows="2" placeholder="Observaciones sobre la recepción..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarRecepcion()">
                    <i class="fas fa-save mr-1"></i> Guardar Recepción
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalle de Recepción -->
<div class="modal fade" id="verDetalleRecepcionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">Detalle de Recepción</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>ID de Recepción:</strong></label>
                            <p id="detalle_raw_material_id">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Materia Prima:</strong></label>
                            <p id="detalle_material_name">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Proveedor:</strong></label>
                            <p id="detalle_supplier_name">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Cantidad:</strong></label>
                            <p id="detalle_quantity">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Cantidad Disponible:</strong></label>
                            <p id="detalle_available_quantity">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Fecha de Recepción:</strong></label>
                            <p id="detalle_receipt_date">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Número de Factura:</strong></label>
                            <p id="detalle_invoice_number">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Recepción Conforme:</strong></label>
                            <p id="detalle_receipt_conformity">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><strong>Observaciones:</strong></label>
                            <p id="detalle_observations">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.9/dist/signature_pad.umd.min.js"></script>
<script>
const solicitudes = @json($solicitudesJson);
const recepciones = @json($recepcionesJson ?? []);
let signaturePad = null;

// Función para ajustar el tamaño del canvas
function resizeCanvas() {
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;
    
    const container = canvas.parentElement;
    const rect = container.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    
    // Establecer el tamaño interno del canvas (alto DPI)
    canvas.width = rect.width * dpr;
    canvas.height = 150 * dpr;
    
    // Establecer el tamaño CSS (tamaño visual)
    canvas.style.width = rect.width + 'px';
    canvas.style.height = '150px';
    
    // Escalar el contexto para que coincida con el DPR
    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    
    // Si ya existe signaturePad, restaurar la firma
    if (signaturePad && !signaturePad.isEmpty()) {
        const data = signaturePad.toData();
        signaturePad.clear();
        signaturePad.fromData(data);
    }
}

// Inicializar canvas de firma cuando se abre el modal
$('#recepcionarSolicitudModal').on('shown.bs.modal', function () {
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        // Ajustar tamaño del canvas primero
        resizeCanvas();
        
        if (!signaturePad) {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                throttle: 16
            });
        }
    }
});

// Manejar redimensionamiento de ventana
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        if (document.getElementById('signatureCanvas')) {
            resizeCanvas();
        }
    }, 100);
});

// Limpiar canvas cuando se cierra el modal
$('#recepcionarSolicitudModal').on('hidden.bs.modal', function () {
    if (signaturePad) {
        signaturePad.clear();
    }
    document.getElementById('recepcionError').style.display = 'none';
    document.getElementById('recepcionSuccess').style.display = 'none';
});

function clearSignature() {
    if (signaturePad) {
        signaturePad.clear();
    }
}

function recepcionarMaterial(requestId, materialId, materialName, requestedQuantity, unit) {
    // Establecer valores en el formulario
    document.getElementById('recepcion_solicitud_id').value = requestId;
    document.getElementById('recepcion_material_id').value = materialId;
    document.getElementById('recepcion_material_name').textContent = materialName;
    document.getElementById('recepcion_requested_quantity').textContent = requestedQuantity;
    document.getElementById('recepcion_unit').textContent = unit;
    document.getElementById('recepcion_cantidad').value = requestedQuantity;
    
    // Buscar la solicitud para obtener las fechas
    const solicitud = solicitudes.find(s => s.request_id == requestId);
    const fechaInput = document.getElementById('recepcion_fecha_recepcion');
    const fechaHelp = document.getElementById('fechaRecepcionHelp');
    
    if (solicitud && solicitud.fecha_solicitud && solicitud.fecha_requerida) {
        // Establecer el mínimo como la fecha de solicitud (o hoy si es más reciente)
        const hoy = new Date().toISOString().split('T')[0];
        const fechaMin = solicitud.fecha_solicitud >= hoy ? solicitud.fecha_solicitud : hoy;
        const fechaMax = solicitud.fecha_requerida;
        
        fechaInput.setAttribute('min', fechaMin);
        fechaInput.setAttribute('max', fechaMax);
        
        // Actualizar el texto de ayuda
        if (fechaHelp) {
            fechaHelp.textContent = `Seleccione una fecha entre ${solicitud.fecha_solicitud_formatted || solicitud.fecha_solicitud} (fecha de solicitud) y ${solicitud.fecha_requerida_formatted || solicitud.fecha_requerida} (fecha requerida)`;
            fechaHelp.className = 'form-text text-info';
        }
        
        // Establecer la fecha de hoy como valor por defecto si está dentro del rango
        if (hoy >= fechaMin && hoy <= fechaMax) {
            fechaInput.value = hoy;
        } else if (hoy < fechaMin) {
            fechaInput.value = fechaMin;
        } else {
            fechaInput.value = fechaMax;
        }
    } else {
        // Si no hay fechas de solicitud, usar solo el mínimo de hoy
        const hoy = new Date().toISOString().split('T')[0];
        fechaInput.setAttribute('min', hoy);
        fechaInput.removeAttribute('max');
        fechaInput.value = hoy;
        if (fechaHelp) {
            fechaHelp.textContent = 'Seleccione una fecha a partir de hoy';
            fechaHelp.className = 'form-text text-muted';
        }
    }
    
    // Limpiar otros campos
    document.getElementById('recepcion_proveedor_id').value = '';
    document.getElementById('recepcion_numero_factura').value = '';
    document.getElementById('recepcion_observaciones').value = '';
    document.getElementById('recepcion_conformidad').checked = true;
    
    // Limpiar firma si existe
    if (signaturePad) {
        signaturePad.clear();
    }
    
    // Mostrar modal
    $('#recepcionarSolicitudModal').modal('show');
}

function guardarRecepcion() {
    const errorDiv = document.getElementById('recepcionError');
    const successDiv = document.getElementById('recepcionSuccess');
    
    // Ocultar mensajes anteriores
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';
    
    // Validar campos requeridos
    const proveedorId = document.getElementById('recepcion_proveedor_id').value;
    const cantidad = document.getElementById('recepcion_cantidad').value;
    const numeroFactura = document.getElementById('recepcion_numero_factura').value;
    
    if (!proveedorId || !cantidad || !numeroFactura) {
        errorDiv.textContent = 'Por favor complete todos los campos requeridos';
        errorDiv.style.display = 'block';
        return;
    }
    
    // Enviar formulario
    document.getElementById('recepcionarSolicitudForm').submit();
}


function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const fecha = document.getElementById('filtroFecha').value;
    const proveedor = document.getElementById('buscarProveedor').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    else url.searchParams.delete('estado');
    if (fecha) url.searchParams.set('fecha', fecha);
    else url.searchParams.delete('fecha');
    if (proveedor) url.searchParams.set('proveedor', proveedor);
    else url.searchParams.delete('proveedor');
    window.location = url;
}


function verDetalleRecepcion(materiaPrimaId) {
    // Buscar la recepción en los datos cargados
    const recepcion = recepciones.find(r => r.materia_prima_id == materiaPrimaId || r.raw_material_id == materiaPrimaId);
    
    if (!recepcion) {
        alert('No se encontró la recepción');
        return;
    }
    
    // Llenar los campos del modal con los datos de la recepción
    document.getElementById('detalle_raw_material_id').textContent = recepcion.materia_prima_id || recepcion.raw_material_id;
    document.getElementById('detalle_material_name').textContent = recepcion.material_name;
    document.getElementById('detalle_supplier_name').textContent = recepcion.supplier_name;
    document.getElementById('detalle_quantity').textContent = recepcion.quantity + ' ' + recepcion.unit;
    document.getElementById('detalle_available_quantity').textContent = recepcion.available_quantity + ' ' + recepcion.unit;
    document.getElementById('detalle_receipt_date').textContent = recepcion.receipt_date || 'N/A';
    document.getElementById('detalle_invoice_number').textContent = recepcion.invoice_number;
    
    // Mostrar estado de conformidad
    const conformidadHtml = recepcion.receipt_conformity 
        ? '<span class="badge badge-success">Sí</span>' 
        : '<span class="badge badge-danger">No</span>';
    document.getElementById('detalle_receipt_conformity').innerHTML = conformidadHtml;
    
    document.getElementById('detalle_observations').textContent = recepcion.observations || 'Sin observaciones';
    
    // Mostrar modal
    $('#verDetalleRecepcionModal').modal('show');
}
</script>
@endpush

