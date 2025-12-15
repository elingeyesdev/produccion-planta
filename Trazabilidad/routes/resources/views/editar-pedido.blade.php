@extends('layouts.app')

@section('page_title', 'Editar Pedido')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Pedido
                </h3>
                <div class="card-tools">
                    <a href="{{ route('mis-pedidos') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5><i class="icon fas fa-ban"></i> Errores de validación:</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <form id="pedidoForm" method="POST" action="{{ route('mis-pedidos.update', $pedido->pedido_id) }}" onsubmit="return handleFormSubmit(event)">
                    @csrf
                    @method('PUT')
                    
                    <!-- Paso 1: Información Básica y Productos -->
                    <div id="step1" class="step-content">
                        <h4 class="mb-3"><i class="fas fa-info-circle"></i> Información del Pedido y Productos</h4>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre del Pedido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="{{ old('nombre', $pedido->nombre) }}" required placeholder="Ej: Pedido Enero 2025">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_entrega">Fecha de Entrega Deseada</label>
                                    <input type="date" class="form-control" id="fecha_entrega" 
                                           name="fecha_entrega" value="{{ old('fecha_entrega', $pedido->fecha_entrega ? $pedido->fecha_entrega->format('Y-m-d') : '') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="2" placeholder="Descripción general del pedido...">{{ old('descripcion', $pedido->descripcion) }}</textarea>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="mb-3"><i class="fas fa-box"></i> Productos</h5>
                        
                        <div id="productsContainer">
                            @foreach($pedido->orderProducts as $index => $orderProduct)
                            <div class="product-item card mb-3" data-index="{{ $index }}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Producto <span class="text-danger">*</span></label>
                                                <select class="form-control product-select" name="products[{{ $index }}][product_id]" required>
                                                    <option value="">Seleccione un producto</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->producto_id }}" 
                                                                data-type="{{ $product->tipo }}"
                                                                data-weight="{{ $product->peso }}"
                                                                data-unit="{{ $product->unit->nombre ?? '' }}"
                                                                data-precio="{{ $product->precio_unitario ?? 0 }}"
                                                                {{ old('products.' . $index . '.product_id', $orderProduct->producto_id) == $product->producto_id ? 'selected' : '' }}>
                                                            {{ $product->nombre }} @if($product->precio_unitario) - Bs. {{ number_format($product->precio_unitario, 2) }} @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control product-quantity" 
                                                       name="products[{{ $index }}][quantity]" step="1" min="1" 
                                                       value="{{ old('products.' . $index . '.quantity', intval($orderProduct->cantidad)) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Unidad</label>
                                                <input type="text" class="form-control product-unit" 
                                                       value="{{ $orderProduct->product->unit->nombre ?? '' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Unitario</label>
                                                <input type="text" class="form-control product-precio-unitario" readonly 
                                                       value="Bs. {{ number_format($orderProduct->product->precio_unitario ?? 0, 2) }}" 
                                                       style="font-weight: bold; color: #28a745;">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Total</label>
                                                <input type="text" class="form-control product-precio-total" readonly 
                                                       value="Bs. {{ number_format($orderProduct->precio ?? ($orderProduct->product->precio_unitario ?? 0) * $orderProduct->cantidad, 2) }}" 
                                                       style="font-weight: bold; color: #007bff;">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-block remove-product" 
                                                        onclick="removeProduct({{ $index }})" 
                                                        style="{{ $pedido->orderProducts->count() <= 1 ? 'display: none;' : '' }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Observaciones</label>
                                                <textarea class="form-control" name="products[{{ $index }}][observations]" rows="2">{{ old('products.' . $index . '.observations', $orderProduct->observations) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <button type="button" class="btn btn-success mb-3" onclick="addProduct()">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                        
                        <!-- Resumen de Precios -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-calculator"></i> Resumen del Pedido</h5>
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total de Productos:</strong></td>
                                                    <td><span id="totalProductos">{{ $pedido->orderProducts->count() }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cantidad Total:</strong></td>
                                                    <td><span id="cantidadTotal">{{ number_format($pedido->orderProducts->sum('cantidad'), 2) }}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-money-bill-wave"></i> Total del Pedido</h5>
                                        <div class="alert alert-info mb-0">
                                            <h3 class="mb-0" id="precioTotalPedido">Bs. {{ number_format($pedido->orderProducts->sum('precio'), 2) }}</h3>
                                            <small>Precio total calculado automáticamente</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="float-right">
                            <button type="button" class="btn btn-primary" onclick="nextStep()">
                                Siguiente: Destinos <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Destinos -->
                    <div id="step2" class="step-content" style="display: none;">
                        <h4 class="mb-3"><i class="fas fa-map-marker-alt"></i> Paso 2: Seleccionar Destinos de Entrega</h4>
                        
                        <div id="destinationsContainer">
                            <!-- Los destinos se agregarán dinámicamente -->
                        </div>
                        
                        <button type="button" class="btn btn-success mb-3" onclick="addDestination()" id="btnAddDestination">
                            <i class="fas fa-plus"></i> Agregar Destino
                        </button>
                        
                        <div class="float-right">
                            <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-check"></i> Actualizar Pedido
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Seleccionar Producto -->
<div class="modal fade" id="productSelectorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Producto</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Seleccione el producto a agregar:</label>
                    <select class="form-control" id="selectedProductIndex">
                        <option value="">Seleccione un producto</option>
                    </select>
                </div>
                <div id="productInfo" class="alert alert-info" style="display: none;">
                    <strong>Información del producto:</strong><br>
                    <span id="productInfoText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddProduct()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Mapa -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Ubicación en el Mapa</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px; width: 100%;"></div>
                <div class="mt-3">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="mapAddress">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" class="form-control" id="mapLatitude" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" class="form-control" id="mapLongitude" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveMapLocation()">Guardar Ubicación</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let currentStep = 1;
let productIndex = {{ $pedido->orderProducts->count() > 0 ? $pedido->orderProducts->count() : 0 }};
let destinationIndex = 0;
let currentDestinationIndex = null;
let map = null;
let marker = null;
let selectedProducts = [];
let destinationProducts = {}; // {destinationIndex: [{index, productId, productName, quantity, assignedQuantity}]}
let productAssignments = {}; // {productIndex: totalAssigned} - Para rastrear cuánto se ha asignado de cada producto
let currentProductSelectorDestination = null;
let isSubmitting = false;

// Función para actualizar precio unitario y total de un producto
function updateProductPrice(selectElement) {
    const productItem = selectElement.closest('.product-item');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const precioUnitario = parseFloat(selectedOption.dataset.precio || 0);
    const cantidadInput = productItem.querySelector('.product-quantity');
    const cantidad = parseFloat(cantidadInput.value || 0);
    
    const precioUnitarioInput = productItem.querySelector('.product-precio-unitario');
    const precioTotalInput = productItem.querySelector('.product-precio-total');
    
    if (precioUnitarioInput) {
        precioUnitarioInput.value = `Bs. ${precioUnitario.toFixed(2)}`;
    }
    
    if (precioTotalInput) {
        const precioTotal = precioUnitario * cantidad;
        precioTotalInput.value = `Bs. ${precioTotal.toFixed(2)}`;
    }
    
    // Recalcular totales
    calculateTotals();
}

// Función para calcular totales del pedido
function calculateTotals() {
    let totalProductos = 0;
    let cantidadTotal = 0;
    let precioTotalPedido = 0;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const select = item.querySelector('.product-select');
        const cantidadInput = item.querySelector('.product-quantity');
        
        if (select && select.value && cantidadInput && cantidadInput.value) {
            const selectedOption = select.options[select.selectedIndex];
            const precioUnitario = parseFloat(selectedOption.dataset.precio || 0);
            const cantidad = parseFloat(cantidadInput.value || 0);
            
            if (cantidad > 0) {
                totalProductos++;
                cantidadTotal += cantidad;
                precioTotalPedido += precioUnitario * cantidad;
            }
        }
    });
    
    // Actualizar resumen
    const totalProductosEl = document.getElementById('totalProductos');
    const cantidadTotalEl = document.getElementById('cantidadTotal');
    const precioTotalPedidoEl = document.getElementById('precioTotalPedido');
    
    if (totalProductosEl) totalProductosEl.textContent = totalProductos;
    if (cantidadTotalEl) cantidadTotalEl.textContent = cantidadTotal.toFixed(2);
    if (precioTotalPedidoEl) precioTotalPedidoEl.textContent = `Bs. ${precioTotalPedido.toFixed(2)}`;
}

// Función para agregar event listeners a un input de cantidad
function checkAndAdjustProductAssignments(productIndex, newQuantity) {
    // Calcular cuánto está asignado actualmente de este producto
    let totalAssigned = 0;
    const assignmentsByDestination = {};
    
    Object.keys(destinationProducts).forEach(destIdx => {
        if (destinationProducts[destIdx] && Array.isArray(destinationProducts[destIdx])) {
            destinationProducts[destIdx].forEach(p => {
                if (p.index === productIndex) {
                    const assignedQty = parseFloat(p.assignedQuantity || 0);
                    totalAssigned += assignedQty;
                    if (!assignmentsByDestination[destIdx]) {
                        assignmentsByDestination[destIdx] = [];
                    }
                    assignmentsByDestination[destIdx].push({
                        product: p,
                        assignedQty: assignedQty
                    });
                }
            });
        }
    });
    
    // Si la cantidad asignada excede la nueva cantidad, ajustar o eliminar asignaciones
    if (totalAssigned > newQuantity) {
        const excess = totalAssigned - newQuantity;
        let remainingToRemove = excess;
        
        // Eliminar asignaciones empezando por los últimos destinos hasta cubrir el exceso
        const destIndices = Object.keys(assignmentsByDestination).sort((a, b) => parseInt(b) - parseInt(a));
        
        for (let destIdx of destIndices) {
            if (remainingToRemove <= 0) break;
            
            const assignments = assignmentsByDestination[destIdx];
            for (let assignment of assignments) {
                if (remainingToRemove <= 0) break;
                
                const toRemove = Math.min(remainingToRemove, assignment.assignedQty);
                const productInDest = destinationProducts[destIdx].find(p => 
                    p.index === productIndex && 
                    Math.abs(parseFloat(p.assignedQuantity || 0) - assignment.assignedQty) < 0.0001
                );
                
                if (productInDest) {
                    const newAssignedQty = parseFloat(productInDest.assignedQuantity || 0) - toRemove;
                    if (newAssignedQty <= 0.0001) {
                        // Eliminar completamente la asignación
                        destinationProducts[destIdx] = destinationProducts[destIdx].filter(p => 
                            !(p.index === productIndex && 
                              Math.abs(parseFloat(p.assignedQuantity || 0) - assignment.assignedQty) < 0.0001)
                        );
                    } else {
                        // Reducir la cantidad asignada
                        productInDest.assignedQuantity = newAssignedQty;
                    }
                    remainingToRemove -= toRemove;
                }
            }
            
            // Actualizar la vista del destino
            updateDestinationProducts(parseInt(destIdx));
        }
        
        // Recalcular asignaciones después de los ajustes
        recalculateAllAssignments();
        updateAddDestinationButton();
        
        console.log(`Ajustadas asignaciones del producto ${productIndex}: eliminado ${excess.toFixed(4)} unidades del exceso`);
    }
}

function attachQuantityListeners(input) {
    if (!input) return;
    
    // Agregar listeners directamente
    input.addEventListener('input', function() {
        const productItem = this.closest('.product-item');
        if (!productItem) return;
        
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        
        // Verificar y ajustar asignaciones si la cantidad cambió
        const productIndex = parseInt(productItem.getAttribute('data-index') || '0');
        const newQuantity = parseFloat(this.value || 0);
        if (productIndex !== undefined && newQuantity >= 0) {
            checkAndAdjustProductAssignments(productIndex, newQuantity);
        }
        
        updateSelectedProducts();
    });
    
    input.addEventListener('change', function() {
        const productItem = this.closest('.product-item');
        if (!productItem) return;
        
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        
        // Verificar y ajustar asignaciones si la cantidad cambió
        const productIndex = parseInt(productItem.getAttribute('data-index') || '0');
        const newQuantity = parseFloat(this.value || 0);
        if (productIndex !== undefined && newQuantity >= 0) {
            checkAndAdjustProductAssignments(productIndex, newQuantity);
        }
        
        updateSelectedProducts();
    });
}

// Función para inicializar todos los event listeners de productos
function initializeProductListeners() {
    // Agregar listeners a todos los selects de productos
    document.querySelectorAll('.product-select').forEach(select => {
        // Remover listeners anteriores si existen
        const newSelect = select.cloneNode(true);
        select.parentNode.replaceChild(newSelect, select);
        
        newSelect.addEventListener('change', function() {
            updateProductUnit(this);
            updateProductPrice(this);
            updateSelectedProducts();
        });
    });
    
    // Agregar listeners a todos los inputs de cantidad
    document.querySelectorAll('.product-quantity').forEach(input => {
        attachQuantityListeners(input);
    });
}

// Inicializar productos existentes
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando...');
    
    // Inicializar todos los event listeners
    initializeProductListeners();
    
    // Calcular totales iniciales
    calculateTotals();
    
    // Actualizar precios de productos ya seleccionados
    document.querySelectorAll('.product-select').forEach(select => {
        if (select.value) {
            updateProductPrice(select);
        }
    });
    
    updateSelectedProducts();
    
    console.log('Inicialización completa');
    
    // Cargar destinos existentes
    const destinationsContainer = document.getElementById('destinationsContainer');
    @foreach($pedido->destinations as $destIndex => $destination)
    {
        destinationIndex = {{ $destIndex + 1 }};
        const destinationHtml = `
            <div class="destination-item card mb-3" data-index="${destinationIndex}" data-form-index="{{ $destIndex }}">
                <div class="card-header">
                    <h5>Destino ${destinationIndex}</h5>
                    <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeDestination(${destinationIndex})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Dirección <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control destination-address" 
                                   name="destinations[{{ $destIndex }}][address]" 
                                   value="{{ old('destinations.' . $destIndex . '.address', $destination->direccion) }}" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" onclick="openMap(${destinationIndex})">
                                    <i class="fas fa-map-marker-alt"></i> Mapa
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" class="destination-latitude" 
                           name="destinations[{{ $destIndex }}][latitude]" 
                           value="{{ old('destinations.' . $destIndex . '.latitude', $destination->latitud) }}">
                    <input type="hidden" class="destination-longitude" 
                           name="destinations[{{ $destIndex }}][longitude]" 
                           value="{{ old('destinations.' . $destIndex . '.longitude', $destination->longitud) }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Referencia</label>
                                <input type="text" class="form-control" 
                                       name="destinations[{{ $destIndex }}][reference]" 
                                       value="{{ old('destinations.' . $destIndex . '.reference', $destination->referencia) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contacto</label>
                                <input type="text" class="form-control" 
                                       name="destinations[{{ $destIndex }}][contact_name]" 
                                       value="{{ old('destinations.' . $destIndex . '.contact_name', $destination->nombre_contacto) }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" class="form-control" 
                               name="destinations[{{ $destIndex }}][contact_phone]" 
                               value="{{ old('destinations.' . $destIndex . '.contact_phone', $destination->telefono_contacto) }}">
                    </div>
                    
                    <div class="form-group">
                        <label>Instrucciones de Entrega</label>
                        <textarea class="form-control" 
                                  name="destinations[{{ $destIndex }}][delivery_instructions]" 
                                  rows="2">{{ old('destinations.' . $destIndex . '.delivery_instructions', $destination->instrucciones_entrega) }}</textarea>
                    </div>
                    
                    <h6>Productos para este destino:</h6>
                    <div class="destination-products" data-destination-index="${destinationIndex}" data-form-index="{{ $destIndex }}">
                        <p class="text-muted">Cargando productos...</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" onclick="showProductSelector(${destinationIndex})" id="btnAddProduct${destinationIndex}">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                </div>
            </div>
        `;
        destinationsContainer.insertAdjacentHTML('beforeend', destinationHtml);
        destinationProducts[destinationIndex] = [];
        
        // Agregar productos asignados a este destino
        @foreach($destination->destinationProducts as $dpIndex => $destProduct)
            @php
                // Buscar el índice del producto en orderProducts
                $productIndexInOrder = $pedido->orderProducts->search(function($op) use ($destProduct) {
                    return $op->producto_pedido_id == $destProduct->producto_pedido_id;
                });
            @endphp
            @if($productIndexInOrder !== false)
                destinationProducts[{{ $destIndex + 1 }}].push({
                    index: {{ $productIndexInOrder }},
                    productId: '{{ $destProduct->orderProduct->producto_id }}',
                    productName: '{{ addslashes($destProduct->orderProduct->product->nombre) }}',
                    quantity: {{ intval($destProduct->orderProduct->cantidad) }},
                    assignedQuantity: {{ intval($destProduct->cantidad) }}
                });
            @endif
        @endforeach
        
        // Actualizar vista después de un pequeño delay para asegurar que selectedProducts esté actualizado
        setTimeout(function() {
            updateDestinationProducts({{ $destIndex + 1 }});
        }, 100);
    }
    @endforeach
    
    // Actualizar índice de destino
    if ({{ $pedido->destinations->count() }} > 0) {
        destinationIndex = {{ $pedido->destinations->count() }};
    }
    
    // Recalcular asignaciones después de un delay para asegurar que todos los destinos se hayan cargado
    setTimeout(function() {
        console.log('Recalculando asignaciones después de cargar destinos...');
        recalculateAllAssignments();
        console.log('productAssignments después de recalcular:', productAssignments);
        updateAddDestinationButton();
    }, 500);
});

function nextStep() {
    console.log('=== nextStep llamado ===');
    console.log('currentStep:', currentStep);
    
    if (currentStep === 1) {
        // Validar paso 1 (información básica)
        console.log('Validando paso 1...');
        if (!validateStep1()) {
            console.log('❌ Validación paso 1 falló');
            return false;
        }
        console.log('✅ Paso 1 validado correctamente');
        
        // Validar productos (paso 2)
        console.log('Validando productos...');
        if (!validateStep2()) {
            console.log('❌ Validación paso 2 (productos) falló');
            return false;
        }
        console.log('✅ Productos validados correctamente');
        
        // Actualizar productos seleccionados
        updateSelectedProducts();
        console.log('Productos seleccionados:', selectedProducts);
        
        // Cambiar al paso 2 (destinos)
        try {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            
            console.log('step1 encontrado:', !!step1);
            console.log('step2 encontrado:', !!step2);
            
            if (!step1) {
                console.error('❌ No se encontró step1');
                alert('Error: No se encontró el paso 1. Por favor recargue la página.');
                return false;
            }
            
            if (!step2) {
                console.error('❌ No se encontró step2');
                alert('Error: No se encontró el paso 2. Por favor recargue la página.');
                return false;
            }
            
            console.log('Ocultando step1, mostrando step2...');
            step1.style.display = 'none';
            step2.style.display = 'block';
            currentStep = 2;
            console.log('✅ Cambiado a paso 2 (destinos), currentStep ahora es:', currentStep);
            
            // Recalcular asignaciones cuando se entra al paso 2 (destinos)
            recalculateAllAssignments();
            updateAddDestinationButton();
            
            // Actualizar todos los destinos existentes
            Object.keys(destinationProducts).forEach(destIdx => {
                updateDestinationProducts(parseInt(destIdx));
            });
            
            return true;
        } catch (e) {
            console.error('❌ Error en nextStep:', e);
            console.error('Stack:', e.stack);
            alert('Error al cambiar de paso: ' + e.message);
            return false;
        }
    }
    
    console.log('⚠️ nextStep: currentStep no es 1, retornando false');
    return false;
}

function prevStep() {
    if (currentStep === 2) {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        currentStep = 1;
    }
}

function validateStep1() {
    const nameInput = document.getElementById('nombre');
    if (!nameInput) {
        console.error('No se encontró el campo nombre');
        return false;
    }
    const name = nameInput.value;
    if (!name || !name.trim()) {
        alert('El nombre del pedido es requerido');
        return false;
    }
    return true;
}

function validateStep2() {
    const products = document.querySelectorAll('.product-select');
    let hasProducts = false;
    let errorMessage = '';
    
    for (let i = 0; i < products.length; i++) {
        const select = products[i];
        if (select.value) {
            const quantityInput = select.closest('.product-item').querySelector('.product-quantity');
            if (!quantityInput) {
                errorMessage = 'Error: No se encontró el campo de cantidad para el producto ' + (i + 1);
                break;
            }
            
            const quantity = quantityInput.value;
            if (!quantity || quantity.trim() === '') {
                errorMessage = 'El producto ' + (i + 1) + ' no tiene cantidad asignada. Por favor ingrese una cantidad.';
                break;
            }
            
            const quantityNum = parseInt(quantity);
            if (isNaN(quantityNum) || quantityNum <= 0) {
                errorMessage = 'La cantidad del producto ' + (i + 1) + ' debe ser un número mayor a 0. Valor ingresado: "' + quantity + '"';
                break;
            }
            
            hasProducts = true;
        }
    }
    
    if (errorMessage) {
        alert(errorMessage);
        return false;
    }
    
    if (!hasProducts) {
        alert('Debe agregar al menos un producto. Por favor:\n1. Seleccione un producto de la lista\n2. Ingrese una cantidad mayor a 0');
        return false;
    }
    
    return true;
}

function addProduct() {
    productIndex++;
    const container = document.getElementById('productsContainer');
    const firstProduct = container.querySelector('.product-item');
    const newProduct = firstProduct.cloneNode(true);
    newProduct.setAttribute('data-index', productIndex);
    
    // Actualizar nombres de campos
    newProduct.querySelectorAll('select, input, textarea').forEach(input => {
        if (input.name) {
            // Reemplazar cualquier índice numérico entre corchetes
            input.name = input.name.replace(/\[(\d+)\]/g, '[' + productIndex + ']');
        }
        if (input.id) {
            // Reemplazar cualquier número en el ID
            input.id = input.id.replace(/\d+/g, productIndex);
        }
        // Limpiar valores excepto para campos readonly
        if (!input.hasAttribute('readonly')) {
            input.value = '';
        }
    });
    
    // Limpiar campos de precio y unidad
    const precioUnitarioInput = newProduct.querySelector('.product-precio-unitario');
    const precioTotalInput = newProduct.querySelector('.product-precio-total');
    const unitInput = newProduct.querySelector('.product-unit');
    
    if (precioUnitarioInput) precioUnitarioInput.value = 'Bs. 0.00';
    if (precioTotalInput) precioTotalInput.value = 'Bs. 0.00';
    if (unitInput) unitInput.value = '';
    
    // Limpiar el select de producto
    const productSelect = newProduct.querySelector('.product-select');
    if (productSelect) {
        productSelect.value = '';
    }
    
    // Mostrar botón eliminar
    const removeBtn = newProduct.querySelector('.remove-product');
    if (removeBtn) {
        removeBtn.style.display = 'block';
        removeBtn.setAttribute('onclick', 'removeProduct(' + productIndex + ')');
    }
    
    // Agregar event listeners
    const selectElement = newProduct.querySelector('.product-select');
    const quantityInput = newProduct.querySelector('.product-quantity');
    
    if (selectElement) {
        selectElement.addEventListener('change', function() {
            updateProductUnit(this);
            updateProductPrice(this);
            updateSelectedProducts();
        });
    }
    
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const productItem = this.closest('.product-item');
            const select = productItem.querySelector('.product-select');
            if (select && select.value) {
                updateProductPrice(select);
            }
            
            // Verificar y ajustar asignaciones si la cantidad cambió
            const productIndex = parseInt(productItem.getAttribute('data-index') || '0');
            const newQuantity = parseFloat(this.value || 0);
            if (productIndex !== undefined && newQuantity >= 0) {
                checkAndAdjustProductAssignments(productIndex, newQuantity);
            }
            
            updateSelectedProducts();
        });
        
        quantityInput.addEventListener('change', function() {
            const productItem = this.closest('.product-item');
            const select = productItem.querySelector('.product-select');
            if (select && select.value) {
                updateProductPrice(select);
            }
            
            // Verificar y ajustar asignaciones si la cantidad cambió
            const productIndex = parseInt(productItem.getAttribute('data-index') || '0');
            const newQuantity = parseFloat(this.value || 0);
            if (productIndex !== undefined && newQuantity >= 0) {
                checkAndAdjustProductAssignments(productIndex, newQuantity);
            }
            
            updateSelectedProducts();
        });
    }
    
    // Agregar al contenedor
    container.appendChild(newProduct);
    
    // Recalcular totales después de agregar producto
    calculateTotals();
}

function removeProduct(index) {
    // No permitir eliminar si solo hay un producto
    const productItems = document.querySelectorAll('.product-item');
    if (productItems.length <= 1) {
        alert('Debe tener al menos un producto en el pedido');
        return;
    }
    
    const product = document.querySelector(`.product-item[data-index="${index}"]`);
    if (product) {
        // Verificar si este producto está asignado a algún destino
        let isAssigned = false;
        Object.keys(destinationProducts).forEach(destIdx => {
            if (destinationProducts[destIdx].some(p => p.index === index)) {
                isAssigned = true;
            }
        });
        
        if (isAssigned) {
            if (!confirm('Este producto está asignado a uno o más destinos. ¿Desea eliminarlo de todos los destinos también?')) {
                return;
            }
            // Eliminar de todos los destinos
            Object.keys(destinationProducts).forEach(destIdx => {
                destinationProducts[destIdx] = destinationProducts[destIdx].filter(p => p.index !== index);
            });
            // Recalcular asignaciones
            recalculateAllAssignments();
            // Actualizar todos los destinos
            Object.keys(destinationProducts).forEach(destIdx => {
                updateDestinationProducts(parseInt(destIdx));
            });
            updateAddDestinationButton();
        }
        
        product.remove();
        updateSelectedProducts();
        calculateTotals(); // Recalcular totales después de eliminar
    }
}

function updateProductUnit(select) {
    const productItem = select.closest('.product-item');
    const unitInput = productItem.querySelector('.product-unit');
    const option = select.options[select.selectedIndex];
    if (option.dataset.unit) {
        unitInput.value = option.dataset.unit;
    }
    // También actualizar precio cuando cambia el producto
    updateProductPrice(select);
}

function updateSelectedProducts() {
    selectedProducts = [];
    productAssignments = {};
    document.querySelectorAll('.product-item').forEach((item) => {
        const select = item.querySelector('.product-select');
        const quantity = item.querySelector('.product-quantity').value;
        const itemIndex = parseInt(item.getAttribute('data-index') || '0');
        if (select.value && quantity) {
            selectedProducts.push({
                index: itemIndex,
                productId: select.value,
                productName: select.options[select.selectedIndex].text,
                quantity: parseInt(quantity)
            });
            productAssignments[itemIndex] = 0;
        }
    });
    // Actualizar todos los destinos cuando cambian los productos
    Object.keys(destinationProducts).forEach(destIdx => {
        updateDestinationProducts(parseInt(destIdx));
    });
    // Actualizar botón de agregar destino (pero solo si estamos en el paso 2)
    if (currentStep === 2) {
        updateAddDestinationButton();
    }
}

function addDestination(silent = false) {
    // Asegurarse de que los productos estén actualizados
    updateSelectedProducts();
    
    // Verificar si hay productos disponibles (solo si no es modo silencioso)
    if (!silent) {
        const availableProducts = getAvailableProducts();
        if (availableProducts.length === 0) {
            // Verificar si hay productos seleccionados en absoluto
            const hasAnyProducts = document.querySelectorAll('.product-item').length > 0 && 
                                   Array.from(document.querySelectorAll('.product-select')).some(select => select.value);
            if (!hasAnyProducts) {
                alert('Debe agregar al menos un producto antes de crear destinos.');
                return;
            }
            // Solo mostrar alerta si hay destinos existentes (significa que todos están asignados)
            if (Object.keys(destinationProducts).length > 0) {
                alert('No hay productos disponibles para asignar. Todos los productos ya han sido asignados completamente a otros destinos.');
                return;
            }
        }
    }
    
    destinationIndex++;
    const container = document.getElementById('destinationsContainer');
    
    if (!container) {
        console.error('No se encontró el contenedor de destinos');
        if (!silent) {
            alert('Error: No se encontró el contenedor de destinos');
        }
        return;
    }
    
    // Calcular el índice del formulario (basado en 0) contando los destinos existentes
    const existingDestinations = container.querySelectorAll('.destination-item');
    const formIndex = existingDestinations.length;
    
    const destinationHtml = `
        <div class="destination-item card mb-3" data-index="${destinationIndex}" data-form-index="${formIndex}">
            <div class="card-header">
                <h5>Destino ${destinationIndex}</h5>
                <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeDestination(${destinationIndex})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Dirección <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control destination-address" 
                               name="destinations[${formIndex}][address]" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" onclick="openMap(${destinationIndex})">
                                <i class="fas fa-map-marker-alt"></i> Mapa
                            </button>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" class="destination-latitude" name="destinations[${formIndex}][latitude]">
                <input type="hidden" class="destination-longitude" name="destinations[${formIndex}][longitude]">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Referencia</label>
                            <input type="text" class="form-control" name="destinations[${formIndex}][reference]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contacto</label>
                            <input type="text" class="form-control" name="destinations[${formIndex}][contact_name]">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Teléfono de Contacto</label>
                    <input type="text" class="form-control" name="destinations[${formIndex}][contact_phone]">
                </div>
                
                <div class="form-group">
                    <label>Instrucciones de Entrega</label>
                    <textarea class="form-control" name="destinations[${formIndex}][delivery_instructions]" rows="2"></textarea>
                </div>
                
                <h6>Productos para este destino:</h6>
                <div class="destination-products" data-destination-index="${destinationIndex}" data-form-index="${formIndex}">
                    <p class="text-muted">No hay productos asignados aún. Use el botón "Agregar Producto" para asignar.</p>
                </div>
                <button type="button" class="btn btn-sm btn-success mt-2" onclick="showProductSelector(${destinationIndex})" id="btnAddProduct${destinationIndex}">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>
        </div>
    `;
    
    try {
        container.insertAdjacentHTML('beforeend', destinationHtml);
        destinationProducts[destinationIndex] = [];
        updateDestinationProducts(destinationIndex);
        updateAddDestinationButton();
        console.log('Destino agregado exitosamente, índice:', destinationIndex);
    } catch (e) {
        console.error('Error al agregar destino:', e);
        if (!silent) {
            alert('Error al agregar destino: ' + e.message);
        }
    }
}

function removeDestination(index) {
    const destination = document.querySelector(`.destination-item[data-index="${index}"]`);
    if (destination) {
        // Liberar las cantidades asignadas de los productos
        if (destinationProducts[index]) {
            destinationProducts[index].forEach(product => {
                if (productAssignments[product.index] !== undefined) {
                    productAssignments[product.index] -= parseFloat(product.assignedQuantity || 0);
                }
            });
        }
        delete destinationProducts[index];
        destination.remove();
        
        // Reindexar los destinos restantes para mantener índices consecutivos en el formulario
        const container = document.getElementById('destinationsContainer');
        const remainingDestinations = container.querySelectorAll('.destination-item');
        remainingDestinations.forEach((dest, newIndex) => {
            dest.setAttribute('data-form-index', newIndex);
            const formIndex = newIndex;
            
            // Actualizar todos los campos name del destino
            dest.querySelectorAll('input, textarea, select').forEach(input => {
                if (input.name && input.name.startsWith('destinations[')) {
                    input.name = input.name.replace(/destinations\[\d+\]/, `destinations[${formIndex}]`);
                }
            });
            
            // Actualizar el contenedor de productos
            const productsContainer = dest.querySelector('.destination-products');
            if (productsContainer) {
                productsContainer.setAttribute('data-form-index', formIndex);
            }
        });
        
        // Actualizar botones de otros destinos
        Object.keys(destinationProducts).forEach(destIdx => {
            updateAddProductButton(parseInt(destIdx));
        });
        updateAddDestinationButton();
    }
}

function getAvailableProducts() {
    // Primero recalcular asignaciones para asegurar que estén actualizadas
    recalculateAllAssignments();
    
    const available = [];
    // Calcular directamente desde el DOM y usar productAssignments actualizado
    
    // Usar los índices correctos basados en data-index
    document.querySelectorAll('.product-item').forEach(item => {
        const itemIndex = parseInt(item.getAttribute('data-index') || '0');
        const select = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.product-quantity');
        
        if (select.value && quantityInput && quantityInput.value) {
            const productId = select.value;
            const productName = select.options[select.selectedIndex].text;
            const quantity = parseFloat(quantityInput.value) || 0;
            
            // Calcular total asignado desde destinationProducts directamente
            let totalAssigned = 0;
            Object.keys(destinationProducts).forEach(destIdx => {
                if (destinationProducts[destIdx] && Array.isArray(destinationProducts[destIdx])) {
                    destinationProducts[destIdx].forEach(p => {
                        if (p.index === itemIndex) {
                            totalAssigned += parseFloat(p.assignedQuantity || 0);
                        }
                    });
                }
            });
            
            // Usar productAssignments como respaldo si está disponible
            if (productAssignments[itemIndex] !== undefined) {
                const assignedFromMap = parseFloat(productAssignments[itemIndex] || 0);
                // Usar el mayor valor para asegurar que no se subestime
                totalAssigned = Math.max(totalAssigned, assignedFromMap);
            }
            
            const remaining = quantity - totalAssigned;
            
            // Permitir pequeñas diferencias de redondeo (0.0001)
            if (remaining > 0.0001) {
                available.push({
                    index: itemIndex,
                    productId: productId,
                    productName: productName,
                    quantity: quantity,
                    remaining: remaining
                });
            }
        }
    });
    
    console.log('getAvailableProducts - Productos disponibles:', available);
    console.log('getAvailableProducts - productAssignments:', productAssignments);
    console.log('getAvailableProducts - destinationProducts:', destinationProducts);
    
    return available;
}

function updateDestinationProducts(destinationIndex) {
    const container = document.querySelector(`.destination-products[data-destination-index="${destinationIndex}"]`);
    if (!container) return;
    
    // Obtener el índice del formulario (basado en 0) desde el atributo data-form-index
    const formIndex = container.getAttribute('data-form-index');
    if (formIndex === null) {
        // Si no tiene data-form-index, buscar el destino padre y obtener su índice
        const destinationItem = container.closest('.destination-item');
        if (destinationItem) {
            const formIdx = destinationItem.getAttribute('data-form-index');
            if (formIdx !== null) {
                // Usar el índice del formulario
                var actualFormIndex = parseInt(formIdx);
            } else {
                // Si no existe, usar destinationIndex - 1 (convertir de 1-based a 0-based)
                var actualFormIndex = destinationIndex - 1;
            }
        } else {
            var actualFormIndex = destinationIndex - 1;
        }
    } else {
        var actualFormIndex = parseInt(formIndex);
    }
    
    if (!destinationProducts[destinationIndex] || destinationProducts[destinationIndex].length === 0) {
        container.innerHTML = '<p class="text-muted">No hay productos asignados aún. Use el botón "Agregar Producto" para asignar.</p>';
        updateAddProductButton(destinationIndex);
        return;
    }
    
    // Obtener información actualizada de los productos desde el DOM
    const productInfoMap = {};
    document.querySelectorAll('.product-item').forEach(item => {
        const itemIndex = parseInt(item.getAttribute('data-index') || '0');
        const select = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.product-quantity');
        
        if (select.value && quantityInput.value) {
            productInfoMap[itemIndex] = {
                productId: select.value,
                productName: select.options[select.selectedIndex].text,
                quantity: parseInt(quantityInput.value)
            };
        }
    });
    
    // Mostrar productos ya asignados
    let html = '';
    destinationProducts[destinationIndex].forEach((product, idx) => {
        // Obtener información actualizada del producto
        const currentProductInfo = productInfoMap[product.index];
        if (!currentProductInfo) {
            // El producto fue eliminado, saltarlo
            return;
        }
        
        const totalAssigned = productAssignments[product.index] || 0;
        const productQuantity = currentProductInfo.quantity;
        const remaining = productQuantity - totalAssigned;
        const maxQuantity = remaining + parseFloat(product.assignedQuantity || 0);
        
        html += `
            <div class="form-group row product-assignment border-bottom pb-2 mb-2" data-product-index="${product.index}">
                <div class="col-md-5">
                    <label><strong>${currentProductInfo.productName}</strong></label>
                    <input type="hidden" name="destinations[${actualFormIndex}][products][${idx}][order_product_index]" value="${product.index}">
                    <small class="text-muted d-block">Total pedido: ${Math.floor(productQuantity)} | Disponible: ${Math.floor(remaining)}</small>
                </div>
                <div class="col-md-5">
                    <label>Cantidad para este destino <span class="text-danger">*</span></label>
                    <input type="number" class="form-control destination-product-quantity" 
                           name="destinations[${actualFormIndex}][products][${idx}][quantity]" 
                           step="1" min="1" max="${Math.floor(maxQuantity)}" 
                           value="${Math.floor(product.assignedQuantity || 0)}" 
                           required
                           onchange="validateAndUpdateQuantity(this, ${destinationIndex}, ${idx}, ${Math.floor(maxQuantity)})"
                           oninput="validateQuantityInput(this, ${Math.floor(maxQuantity)})"
                           data-product-index="${product.index}">
                    <small class="text-muted">Máximo: ${Math.floor(maxQuantity)}</small>
                    <small class="text-danger quantity-error" style="display: none;"></small>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger btn-block" onclick="removeProductFromDestination(${destinationIndex}, ${idx})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updateAddProductButton(destinationIndex);
}

function recalculateAllAssignments() {
    // Recalcular todas las asignaciones desde cero
    Object.keys(productAssignments).forEach(key => {
        productAssignments[key] = 0;
    });
    
    // Recalcular asignaciones de todos los destinos
    Object.keys(destinationProducts).forEach(destIdx => {
        if (destinationProducts[destIdx] && Array.isArray(destinationProducts[destIdx])) {
            destinationProducts[destIdx].forEach(p => {
                const qty = parseFloat(p.assignedQuantity || 0);
                const productIndex = parseInt(p.index);
                if (productAssignments[productIndex] === undefined) {
                    productAssignments[productIndex] = 0;
                }
                productAssignments[productIndex] += qty;
            });
        }
    });
    
    console.log('recalculateAllAssignments - productAssignments actualizado:', productAssignments);
}

function validateQuantityInput(input, maxQuantity) {
    const value = parseInt(input.value || 0);
    const errorElement = input.parentElement.querySelector('.quantity-error');
    
    if (value < 1) {
        input.value = '';
        if (errorElement) {
            errorElement.textContent = 'La cantidad debe ser mayor a 0';
            errorElement.style.display = 'block';
        }
        input.classList.add('is-invalid');
        return false;
    }
    
    if (value > maxQuantity) {
        input.value = maxQuantity;
        if (errorElement) {
            errorElement.textContent = `La cantidad no puede ser mayor a ${maxQuantity}`;
            errorElement.style.display = 'block';
        }
        input.classList.add('is-invalid');
        return false;
    }
    
    if (errorElement) {
        errorElement.style.display = 'none';
    }
    input.classList.remove('is-invalid');
    return true;
}

function validateAndUpdateQuantity(input, destinationIndex, productIdx, maxQuantity) {
    if (!validateQuantityInput(input, maxQuantity)) {
        return;
    }
    
    const newQuantity = input.value;
    updateProductAssignment(destinationIndex, productIdx, newQuantity);
}

function updateProductAssignment(destinationIndex, productIdx, newQuantity) {
    const product = destinationProducts[destinationIndex][productIdx];
    if (!product) return;
    
    const qty = parseInt(newQuantity || 0);
    if (qty < 1) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }
    
    // Actualizar la cantidad del producto (guardar como entero)
    product.assignedQuantity = qty;
    
    // Recalcular todas las asignaciones
    recalculateAllAssignments();
    
    // Validar que no se exceda
    const totalAssigned = productAssignments[product.index] || 0;
    if (totalAssigned > product.quantity) {
        alert(`La cantidad total asignada (${totalAssigned}) excede la cantidad pedida (${product.quantity}) para el producto "${product.productName}"`);
        // Ajustar a la cantidad máxima disponible
        const available = product.quantity - (totalAssigned - qty);
        product.assignedQuantity = Math.max(1, Math.floor(available));
        recalculateAllAssignments();
    }
    
    // Actualizar todos los destinos para reflejar las cantidades disponibles
    Object.keys(destinationProducts).forEach(destIdx => {
        updateDestinationProducts(parseInt(destIdx));
    });
    
    updateAddDestinationButton();
}

function showProductSelector(destinationIndex) {
    const availableProducts = getAvailableProducts();
    
    if (availableProducts.length === 0) {
        alert('No hay más productos disponibles para asignar. Todos los productos ya han sido asignados completamente.');
        return;
    }
    
    currentProductSelectorDestination = destinationIndex;
    
    // Crear opciones para el selector
    let optionsHtml = '<option value="">Seleccione un producto</option>';
    availableProducts.forEach(product => {
        optionsHtml += `<option value="${product.index}" data-remaining="${product.remaining}" data-quantity="${product.quantity}">${product.productName} (Disponible: ${product.remaining.toFixed(4)} de ${product.quantity.toFixed(4)})</option>`;
    });
    
    const select = document.getElementById('selectedProductIndex');
    // Remover event listeners anteriores
    const newSelect = select.cloneNode(true);
    select.parentNode.replaceChild(newSelect, select);
    
    newSelect.innerHTML = optionsHtml;
    newSelect.value = '';
    document.getElementById('productInfo').style.display = 'none';
    
    // Event listener para mostrar información del producto
    newSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const remaining = parseFloat(option.dataset.remaining);
            const quantity = parseFloat(option.dataset.quantity);
            document.getElementById('productInfoText').innerHTML = 
                `Cantidad total pedida: ${quantity}<br>` +
                `Cantidad disponible: ${remaining}`;
            document.getElementById('productInfo').style.display = 'block';
        } else {
            document.getElementById('productInfo').style.display = 'none';
        }
    });
    
    $('#productSelectorModal').modal('show');
}

function confirmAddProduct() {
    const select = document.getElementById('selectedProductIndex');
    const productIndexValue = select.value;
    
    if (!productIndexValue) {
        alert('Por favor seleccione un producto');
        return;
    }
    
    if (!currentProductSelectorDestination) {
        alert('Error: No se identificó el destino');
        return;
    }
    
    const availableProducts = getAvailableProducts();
    const productToAdd = availableProducts.find(p => p.index.toString() === productIndexValue);
    
    if (!productToAdd) {
        alert('Producto no disponible. Puede que haya sido eliminado o ya no tenga cantidad disponible.');
        return;
    }
    
    if (!destinationProducts[currentProductSelectorDestination]) {
        destinationProducts[currentProductSelectorDestination] = [];
    }
    
    // Verificar que el producto no esté ya agregado a este destino
    const alreadyAdded = destinationProducts[currentProductSelectorDestination].some(p => p.index.toString() === productIndexValue);
    if (alreadyAdded) {
        alert('Este producto ya está agregado a este destino');
        return;
    }
    
    destinationProducts[currentProductSelectorDestination].push({
        index: productToAdd.index,
        productId: productToAdd.productId,
        productName: productToAdd.productName,
        quantity: productToAdd.quantity,
        assignedQuantity: ''
    });
    
    updateDestinationProducts(currentProductSelectorDestination);
    updateAddDestinationButton();
    $('#productSelectorModal').modal('hide');
    currentProductSelectorDestination = null;
}

function updateAddProductButton(destinationIndex) {
    const btn = document.getElementById(`btnAddProduct${destinationIndex}`);
    const availableProducts = getAvailableProducts();
    
    if (btn) {
        if (availableProducts.length === 0) {
            btn.disabled = true;
            btn.classList.add('disabled');
            btn.innerHTML = '<i class="fas fa-ban"></i> Sin productos disponibles';
        } else {
            btn.disabled = false;
            btn.classList.remove('disabled');
            btn.innerHTML = '<i class="fas fa-plus"></i> Agregar Producto';
        }
    }
}

function removeProductFromDestination(destinationIndex, productIdx) {
    if (destinationProducts[destinationIndex] && destinationProducts[destinationIndex][productIdx]) {
        destinationProducts[destinationIndex].splice(productIdx, 1);
        
        // Recalcular todas las asignaciones
        recalculateAllAssignments();
        
        updateDestinationProducts(destinationIndex);
        
        // Actualizar botones de otros destinos
        Object.keys(destinationProducts).forEach(destIdx => {
            if (parseInt(destIdx) !== destinationIndex) {
                updateDestinationProducts(parseInt(destIdx));
            }
        });
        
        updateAddDestinationButton();
    }
}

function openMap(destIndex) {
    currentDestinationIndex = destIndex;
    $('#mapModal').modal('show');
    
    setTimeout(() => {
        if (!map) {
            map = L.map('map').setView([-17.8146, -63.1561], 13); // Santa Cruz de la Sierra, Bolivia
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            map.on('click', function(e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
                document.getElementById('mapLatitude').value = e.latlng.lat;
                document.getElementById('mapLongitude').value = e.latlng.lng;
            });
        }
        
        // Cargar coordenadas y dirección existentes si las hay
        const destination = document.querySelector(`.destination-item[data-index="${destIndex}"]`);
        if (destination) {
            const lat = destination.querySelector('.destination-latitude').value;
            const lng = destination.querySelector('.destination-longitude').value;
            const address = destination.querySelector('.destination-address').value;
            
            // Cargar dirección en el modal
            if (address) {
                document.getElementById('mapAddress').value = address;
            }
            
            if (lat && lng) {
                map.setView([lat, lng], 15);
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker([lat, lng]).addTo(map);
                document.getElementById('mapLatitude').value = lat;
                document.getElementById('mapLongitude').value = lng;
            } else if (address) {
                // Si hay dirección pero no coordenadas, centrar el mapa en una ubicación por defecto
                // y permitir que el usuario seleccione el punto
                map.setView([-17.8146, -63.1561], 13);
            }
        } else {
            // Limpiar campos del modal si no hay destino
            document.getElementById('mapAddress').value = '';
            document.getElementById('mapLatitude').value = '';
            document.getElementById('mapLongitude').value = '';
        }
    }, 300);
}

function saveMapLocation() {
    if (currentDestinationIndex !== null && marker) {
        const lat = document.getElementById('mapLatitude').value;
        const lng = document.getElementById('mapLongitude').value;
        const address = document.getElementById('mapAddress').value;
        
        const destination = document.querySelector(`.destination-item[data-index="${currentDestinationIndex}"]`);
        destination.querySelector('.destination-latitude').value = lat;
        destination.querySelector('.destination-longitude').value = lng;
        if (address) {
            destination.querySelector('.destination-address').value = address;
        }
        
        $('#mapModal').modal('hide');
    }
}

function handleFormSubmit(event) {
    event.preventDefault();
    
    if (isSubmitting) {
        return false;
    }
    
    // Validar paso 1 (información básica y productos)
    if (!validateStep1() || !validateStep2()) {
        if (currentStep === 1) {
            return false;
        } else {
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step1').style.display = 'block';
            currentStep = 1;
            return false;
        }
    }
    
    // Validar paso 2 (destinos)
    const destinations = document.querySelectorAll('.destination-item');
    if (destinations.length === 0) {
        alert('Debe agregar al menos un destino');
        if (currentStep === 1) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            currentStep = 2;
        }
        return false;
    }
    
    let hasProducts = false;
    let hasErrors = false;
    let errorMessages = [];
    
    destinations.forEach((dest, destIdx) => {
        const address = dest.querySelector('.destination-address');
        if (!address || !address.value.trim()) {
            hasErrors = true;
            errorMessages.push(`El destino ${destIdx + 1} requiere una dirección`);
        }
        
        const products = dest.querySelectorAll('.destination-product-quantity');
        let destHasProducts = false;
        products.forEach(input => {
            const value = parseInt(input.value || 0);
            if (value > 0) {
                destHasProducts = true;
                hasProducts = true;
                
                const max = parseInt(input.getAttribute('max') || 0);
                if (value > max) {
                    hasErrors = true;
                    errorMessages.push(`La cantidad del producto en el destino ${destIdx + 1} excede la cantidad disponible`);
                }
            } else if (input.hasAttribute('required')) {
                hasErrors = true;
                errorMessages.push(`El destino ${destIdx + 1} tiene productos sin cantidad asignada`);
            }
        });
        
        if (!destHasProducts) {
            hasErrors = true;
            errorMessages.push(`El destino ${destIdx + 1} no tiene productos asignados`);
        }
    });
    
    if (hasErrors) {
        alert('Errores de validación:\n\n' + errorMessages.join('\n'));
        if (currentStep === 1) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            currentStep = 2;
        }
        return false;
    }
    
    if (!hasProducts) {
        alert('Debe asignar al menos un producto a un destino');
        if (currentStep === 1) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            currentStep = 2;
        }
        return false;
    }
    
    // Validar que todas las cantidades de productos estén asignadas
    let hasUnassigned = false;
    selectedProducts.forEach(product => {
        const totalAssigned = productAssignments[product.index] || 0;
        if (Math.abs(totalAssigned - product.quantity) > 1) {
            const remaining = product.quantity - totalAssigned;
            if (remaining > 0) {
                hasUnassigned = true;
            }
        }
    });
    
    if (hasUnassigned) {
        const unassignedProducts = selectedProducts.filter(product => {
            const totalAssigned = productAssignments[product.index] || 0;
            return Math.abs(totalAssigned - product.quantity) > 1 && (product.quantity - totalAssigned) > 0;
        });
        const message = unassignedProducts.map(p => {
            const totalAssigned = productAssignments[p.index] || 0;
            return `${p.productName}: ${p.quantity - totalAssigned} unidades sin asignar`;
        }).join('\n');
        
        if (!confirm(`Los siguientes productos tienen unidades sin asignar:\n\n${message}\n\n¿Desea continuar de todas formas?`)) {
            return false;
        }
    }
    
    isSubmitting = true;
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
    }
    
    const form = document.getElementById('pedidoForm');
    if (form) {
        form.submit();
    } else {
        console.error('No se encontró el formulario');
        alert('Error: No se encontró el formulario. Por favor recargue la página.');
    }
    return false;
}

// Actualizar botón de agregar destino
function updateAddDestinationButton() {
    const btn = document.getElementById('btnAddDestination');
    const availableProducts = getAvailableProducts();
    
    if (btn) {
        if (availableProducts.length === 0) {
            btn.disabled = true;
            btn.classList.add('disabled');
            btn.innerHTML = '<i class="fas fa-ban"></i> Sin productos disponibles';
        } else {
            btn.disabled = false;
            btn.classList.remove('disabled');
            btn.innerHTML = '<i class="fas fa-plus"></i> Agregar Destino';
        }
    }
}
</script>
@endpush
@endsection

