@extends('layouts.app')

@section('page_title', 'Gestión de Almacenaje')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-warehouse mr-1"></i>
                    Gestión de Almacenaje
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
                                <h3>{{ $stats['disponibles'] ?? 0 }}</h3>
                                <p>Disponibles para Almacenar</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['certificados'] ?? 0 }}</h3>
                                <p>Lotes Certificados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['almacenados'] ?? 0 }}</h3>
                                <p>Ya Almacenados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ $stats['sin_certificar'] ?? 0 }}</h3>
                                <p>Sin Certificar</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Lotes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Código Lote</th>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha Creación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $lote)
                                @php
                                    $eval = $lote->latestFinalEvaluation;
                                    $esCertificado = $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
                                    $yaAlmacenado = $lote->storage->isNotEmpty();
                                    $cantidadParaAlmacenar = ($lote->cantidad_producida ?? 0) > 0 ? $lote->cantidad_producida : ($lote->cantidad_objetivo ?? 0);
                                    $esCantidadObjetivo = ($lote->cantidad_producida ?? 0) <= 0;
                                @endphp
                                <tr>
                                    <td>{{ $lote->codigo_lote ?? $lote->lote_id }}</td>
                                    <td>{{ $lote->nombre ?? 'Sin nombre' }}</td>
                                    <td>
                                        @if($lote->cantidad_producida)
                                            {{ number_format($lote->cantidad_producida, 2) }}
                                            <small class="text-muted">(Producida)</small>
                                        @else
                                            {{ number_format($lote->cantidad_objetivo ?? 0, 2) }}
                                            <small class="text-muted">(Objetivo)</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lote->order)
                                            <strong>{{ $lote->order->numero_pedido ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $lote->order->nombre ?? '' }}</small>
                                        @else
                                            <span class="text-muted">Sin pedido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lote->order && $lote->order->customer)
                                            {{ $lote->order->customer->razon_social ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $lote->fecha_creacion ? $lote->fecha_creacion->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if($yaAlmacenado)
                                            <span class="badge badge-almacenado">
                                                <i class="fas fa-warehouse"></i> Almacenado
                                            </span>
                                        @elseif($esCertificado)
                                            <span class="badge badge-certificado">
                                                <i class="fas fa-certificate"></i> Certificado
                                            </span>
                                        @else
                                            <span class="badge badge-sin-certificar">
                                                <i class="fas fa-times-circle"></i> Sin Certificar
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($esCertificado)
                                            @if($yaAlmacenado)
                                                @php
                                                    $almacenaje = $lote->storage->first();
                                                @endphp
                                                <button class="btn btn-info btn-sm" title="Ver Detalles" onclick="verAlmacenaje({{ $lote->lote_id }})">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            @else
                                                <button class="btn btn-primary btn-sm" title="Almacenar" onclick="almacenarLote({{ $lote->lote_id }}, '{{ $lote->codigo_lote ?? $lote->lote_id }}', '{{ $lote->nombre ?? 'Sin nombre' }}', {{ $cantidadParaAlmacenar }}, {{ $esCantidadObjetivo ? 'true' : 'false' }}, {{ $lote->pedido_id ?? 'null' }})">
                                                    <i class="fas fa-warehouse"></i> Almacenar
                                                </button>
                                            @endif
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled title="No certificado">
                                                <i class="fas fa-lock"></i> No disponible
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> No hay lotes certificados disponibles
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Registrar Almacenaje -->
<div class="modal fade" id="registrarAlmacenajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-warehouse"></i> Registrar Almacenaje
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('almacenaje.store') }}" id="registrarAlmacenajeForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="lote_id" name="lote_id">
                    
                    <!-- Información del Lote -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="fas fa-box"></i> Información del Lote</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Código:</strong> <span id="modal_batch_code"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Nombre:</strong> <span id="modal_batch_name"></span></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cantidad a Almacenar:</strong> <span id="modal_quantity"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Pedido:</strong> <span id="modal_order_number"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Pedido y Destinos -->
                    <div id="destinations_table_container" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-map-marked-alt"></i> Destinos del Pedido</strong>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Dirección</th>
                                                <th>Referencia</th>
                                                <th>Contacto</th>
                                                <th>Teléfono</th>
                                            </tr>
                                        </thead>
                                        <tbody id="destinations_table_body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Almacenaje -->
                    <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> Datos de Almacenaje</h5>
                    
                    <div class="form-group">
                        <label for="condicion">
                            Condición <span class="text-danger">*</span>
                        </label>
                        <select class="form-control @error('condicion') is-invalid @enderror" 
                                id="condicion" name="condicion" required>
                            <option value="">Seleccione una condición</option>
                            <option value="Excelente">Excelente</option>
                            <option value="Buena">Buena</option>
                            <option value="Regular">Regular</option>
                            <option value="Deficiente">Deficiente</option>
                        </select>
                        @error('condicion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Ingrese observaciones adicionales (opcional)"></textarea>
                        @error('observaciones')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ubicación de Recojo -->
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Ubicación de Recojo</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Ubicación de la planta:</strong> Esta es la ubicación configurada de tu planta donde se recogerá el producto.
                    </div>
                    
                    <div class="form-group">
                        <label>
                            Dirección de Recojo <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('pickup_address') is-invalid @enderror" 
                                   id="pickup_address" name="pickup_address" 
                                   value="{{ old('pickup_address', $plantaConfig['direccion']) }}" 
                                   required readonly
                                   placeholder="Ingrese la dirección donde se recogerá el producto">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" onclick="openMapAlmacenaje()">
                                    <i class="fas fa-map"></i> Mapa
                                </button>
                            </div>
                        </div>
                        @error('pickup_address')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Dirección completa donde se recogerá el producto</small>
                        <input type="hidden" id="pickup_latitude" name="pickup_latitude" value="{{ old('pickup_latitude', $plantaConfig['latitud']) }}">
                        <input type="hidden" id="pickup_longitude" name="pickup_longitude" value="{{ old('pickup_longitude', $plantaConfig['longitud']) }}">
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Importante:</strong> La cantidad a almacenar se toma automáticamente del lote. Solo se puede almacenar una vez. Al almacenar, se creará automáticamente el envío en PlantaCruds con la ubicación de recojo seleccionada.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Almacenaje
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Mapa -->
<div class="modal fade" id="mapModalAlmacenaje" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Ubicación en el Mapa</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="mapAlmacenaje" style="height: 400px; width: 100%;"></div>
                <div class="mt-3">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="mapAddressAlmacenaje">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" class="form-control" id="mapLatitudeAlmacenaje" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" class="form-control" id="mapLongitudeAlmacenaje" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveMapLocationAlmacenaje()">Guardar Ubicación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles de Almacenaje -->
<div class="modal fade" id="verAlmacenajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles de Almacenaje
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verAlmacenajeContent">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@push('scripts')
<script>
let currentBatchId = null;
let mapAlmacenaje = null;
let markerAlmacenaje = null;

// Datos de pedidos cargados desde el backend
const ordersData = @json($ordersData ?? []);

function almacenarLote(loteId, codigoLote, nombreLote, quantity, isTargetQuantity, pedidoId) {
    currentBatchId = loteId;
    $('#lote_id').val(loteId);
    $('#modal_batch_code').text(codigoLote);
    $('#modal_batch_name').text(nombreLote);
    $('#condicion').val('');
    
    // Establecer la cantidad automáticamente y mostrar referencia
    const qty = parseFloat(quantity) || 0;
    $('#modal_quantity').text(qty.toFixed(2) + ' ' + (isTargetQuantity ? '(Objetivo)' : '(Producida)'));
    
    $('#observaciones').val('');
    // Inicializar con valores por defecto de la configuración de la planta
    $('#pickup_address').val('{{ $plantaConfig['direccion'] }}');
    $('#pickup_latitude').val('{{ $plantaConfig['latitud'] }}');
    $('#pickup_longitude').val('{{ $plantaConfig['longitud'] }}');
    
    // Cargar información del pedido
    if (pedidoId && pedidoId !== 'null' && pedidoId !== null) {
        loadOrderInfo(pedidoId);
    } else {
        $('#modal_order_number').text('N/A');
        $('#destinations_table_container').hide();
    }
    
    $('#registrarAlmacenajeModal').modal('show');
}

function loadOrderInfo(pedidoId) {
    // Obtener información del pedido desde los datos cargados
    const orderData = ordersData[pedidoId];
    
    if (orderData) {
        $('#modal_order_number').text(orderData.numero_pedido || 'N/A');
        
        // Mostrar destinos si existen
        if (orderData.destinations && orderData.destinations.length > 0) {
            $('#destinations_table_body').empty();
            orderData.destinations.forEach(dest => {
                $('#destinations_table_body').append(`
                    <tr>
                        <td>${dest.address || 'N/A'}</td>
                        <td>${dest.reference || '-'}</td>
                        <td>${dest.contact_name || '-'}</td>
                        <td>${dest.contact_phone || '-'}</td>
                    </tr>
                `);
            });
            $('#destinations_table_container').show();
        } else {
            $('#destinations_table_container').hide();
        }
    } else {
        $('#modal_order_number').text('N/A');
        $('#destinations_table_container').hide();
    }
}

// Función para abrir el mapa de almacenaje
function openMapAlmacenaje() {
    $('#mapModalAlmacenaje').modal('show');
    
    setTimeout(() => {
        if (!mapAlmacenaje) {
            mapAlmacenaje = L.map('mapAlmacenaje').setView([{{ $plantaConfig['latitud'] }}, {{ $plantaConfig['longitud'] }}], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapAlmacenaje);
            
            mapAlmacenaje.on('click', function(e) {
                if (markerAlmacenaje) {
                    mapAlmacenaje.removeLayer(markerAlmacenaje);
                }
                markerAlmacenaje = L.marker([e.latlng.lat, e.latlng.lng]).addTo(mapAlmacenaje);
                document.getElementById('mapLatitudeAlmacenaje').value = e.latlng.lat;
                document.getElementById('mapLongitudeAlmacenaje').value = e.latlng.lng;
            });
        }
    }, 300);
}

// Función para guardar la ubicación del mapa de almacenaje
function saveMapLocationAlmacenaje() {
    if (markerAlmacenaje) {
        const lat = document.getElementById('mapLatitudeAlmacenaje').value;
        const lng = document.getElementById('mapLongitudeAlmacenaje').value;
        const address = document.getElementById('mapAddressAlmacenaje').value;
        
        document.getElementById('pickup_latitude').value = lat;
        document.getElementById('pickup_longitude').value = lng;
        if (address) {
            document.getElementById('pickup_address').value = address;
        }
        
        $('#mapModalAlmacenaje').modal('hide');
    } else {
        alert('Por favor, seleccione una ubicación en el mapa haciendo clic');
    }
}

// Limpiar modal al cerrar
$('#registrarAlmacenajeModal').on('hidden.bs.modal', function () {
    currentBatchId = null;
    $('#registrarAlmacenajeForm')[0].reset();
});

// Limpiar el mapa cuando se cierra el modal
$('#mapModalAlmacenaje').on('hidden.bs.modal', function() {
    // No limpiar el mapa para mantener la selección
});

// Función para ver detalles de almacenaje
function verAlmacenaje(batchId) {
    fetch(`{{ url('almacenaje') }}/lote/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const almacenaje = data[0];
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong><i class="fas fa-box"></i> Información del Lote</strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Código Lote:</strong> ${almacenaje.codigo_lote || 'N/A'}</p>
                                    <p><strong>Nombre:</strong> ${almacenaje.nombre_lote || 'N/A'}</p>
                                    <p><strong>Cantidad Almacenada:</strong> ${almacenaje.cantidad || 0}</p>
                                    <p><strong>Condición:</strong> <span class="badge badge-info">${almacenaje.condicion || 'N/A'}</span></p>
                                    <p><strong>Fecha Almacenaje:</strong> ${almacenaje.fecha_almacenaje ? new Date(almacenaje.fecha_almacenaje).toLocaleString('es-ES') : 'N/A'}</p>
                                    ${almacenaje.observaciones ? `<p><strong>Observaciones:</strong> ${almacenaje.observaciones}</p>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong><i class="fas fa-map-marker-alt"></i> Ubicación de Recojo</strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Dirección:</strong> ${almacenaje.direccion_recojo || 'N/A'}</p>
                                    ${almacenaje.latitud_recojo && almacenaje.longitud_recojo ? `
                                        <p><strong>Coordenadas:</strong> ${almacenaje.latitud_recojo}, ${almacenaje.longitud_recojo}</p>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${almacenaje.numero_pedido ? `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-shopping-cart"></i> Información del Pedido</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Número Pedido:</strong> ${almacenaje.numero_pedido}</p>
                                        ${almacenaje.nombre_pedido ? `<p><strong>Nombre:</strong> ${almacenaje.nombre_pedido}</p>` : ''}
                                        <p><strong>Estado:</strong> <span class="badge badge-primary">${almacenaje.estado_pedido || 'N/A'}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        ${almacenaje.cliente_razon_social ? `<p><strong>Cliente:</strong> ${almacenaje.cliente_razon_social}</p>` : ''}
                                        ${almacenaje.fecha_entrega_pedido ? `<p><strong>Fecha Entrega:</strong> ${new Date(almacenaje.fecha_entrega_pedido).toLocaleDateString('es-ES')}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${almacenaje.destinos && almacenaje.destinos.length > 0 ? `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-map-marked-alt"></i> Destinos del Pedido</strong>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Dirección</th>
                                                <th>Referencia</th>
                                                <th>Contacto</th>
                                                <th>Teléfono</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${almacenaje.destinos.map(dest => `
                                                <tr>
                                                    <td>${dest.direccion || 'N/A'}</td>
                                                    <td>${dest.referencia || '-'}</td>
                                                    <td>${dest.nombre_contacto || '-'}</td>
                                                    <td>${dest.telefono_contacto || '-'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                `;
                
                $('#verAlmacenajeContent').html(html);
                $('#verAlmacenajeModal').modal('show');
            } else {
                alert('No se encontraron datos de almacenaje para este lote');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del almacenaje');
        });
}
</script>
@endpush
