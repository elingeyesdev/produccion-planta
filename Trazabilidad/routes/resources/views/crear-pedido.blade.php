@extends('layouts.app')

@section('page_title', 'Crear Nuevo Pedido')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Crear Nuevo Pedido
                </h3>
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

                <form id="pedidoForm" method="POST" action="{{ route('mis-pedidos.store') }}" onsubmit="return handleFormSubmit(event)">
                    @csrf
                    
                    <!-- Paso 1: Información Básica y Productos -->
                    <div id="step1" class="step-content">
                        <h4 class="mb-3"><i class="fas fa-info-circle"></i> Información del Pedido y Productos</h4>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre del Pedido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name') }}" required placeholder="Ej: Pedido Enero 2025">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega Deseada</label>
                                    <input type="date" class="form-control" id="delivery_date" 
                                           name="delivery_date" value="{{ old('delivery_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="2" placeholder="Descripción general del pedido...">{{ old('description') }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3"><i class="fas fa-box"></i> Productos</h5>
                        
                        <div id="productsContainer">
                            <div class="product-item card mb-3" data-index="0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Producto <span class="text-danger">*</span></label>
                                                <select class="form-control product-select" name="products[0][product_id]" required>
                                                    <option value="">Seleccione un producto</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->producto_id }}" 
                                                                data-type="{{ $product->tipo }}"
                                                                data-weight="{{ $product->peso }}"
                                                                data-unit="{{ $product->unit->nombre ?? '' }}"
                                                                data-precio="{{ $product->precio_unitario ?? 0 }}">
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
                                                       name="products[0][quantity]" step="1" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Unidad</label>
                                                <input type="text" class="form-control product-unit" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Unitario</label>
                                                <input type="text" class="form-control product-precio-unitario" readonly 
                                                       value="Bs. 0.00" style="font-weight: bold; color: #28a745;">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Total</label>
                                                <input type="text" class="form-control product-precio-total" readonly 
                                                       value="Bs. 0.00" style="font-weight: bold; color: #007bff;">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-block remove-product" onclick="removeProduct(0)" style="display: none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Observaciones</label>
                                                <textarea class="form-control" name="products[0][observations]" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                                    <td><span id="totalProductos">0</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cantidad Total:</strong></td>
                                                    <td><span id="cantidadTotal">0</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-money-bill-wave"></i> Total del Pedido</h5>
                                        <div class="alert alert-info mb-0">
                                            <h3 class="mb-0" id="precioTotalPedido">Bs. 0.00</h3>
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
                                <i class="fas fa-check"></i> Crear Pedido
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
let productIndex = 0;
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
    document.getElementById('totalProductos').textContent = totalProductos;
    document.getElementById('cantidadTotal').textContent = cantidadTotal.toFixed(2);
    document.getElementById('precioTotalPedido').textContent = `Bs. ${precioTotalPedido.toFixed(2)}`;
}

// Inicializar productos seleccionados
document.querySelectorAll('.product-select').forEach(select => {
    select.addEventListener('change', function() {
        updateProductUnit(this);
        updateProductPrice(this);
        updateSelectedProducts();
    });
});

document.querySelectorAll('.product-quantity').forEach(input => {
    input.addEventListener('input', function() {
        const productItem = this.closest('.product-item');
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        updateSelectedProducts();
    });
    
    input.addEventListener('change', function() {
        const productItem = this.closest('.product-item');
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        updateSelectedProducts();
    });
});

function nextStep() {
    console.log('nextStep llamado, currentStep:', currentStep);
    
    if (currentStep === 1) {
        // Validar paso 1 (información básica)
        if (!validateStep1()) {
            console.log('Validación paso 1 falló');
            return false;
        }
        
        // Validar paso 2 (productos)
        if (!validateStep2()) {
            console.log('Validación paso 2 falló');
            return false;
        }
        
        // Actualizar productos seleccionados
        updateSelectedProducts();
        console.log('Productos seleccionados:', selectedProducts);
        
        // Cambiar al paso 2
        try {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            
            if (!step1 || !step2) {
                console.error('No se encontraron los elementos step1 o step2');
                alert('Error: No se pudo cambiar de paso. Por favor recargue la página.');
                return false;
            }
            
            step1.style.display = 'none';
            step2.style.display = 'block';
            currentStep = 2;
            console.log('Cambiado a paso 2');
            
            // Recalcular asignaciones cuando se entra al paso 2 (destinos)
            recalculateAllAssignments();
            updateAddDestinationButton();
            
            // Solo agregar el primer destino si no hay ninguno (modo silencioso para evitar alertas)
            if (Object.keys(destinationProducts).length === 0) {
                console.log('Agregando primer destino...');
                try {
                    addDestination(true); // Modo silencioso
                    console.log('Primer destino agregado');
                } catch (e) {
                    console.error('Error al agregar destino:', e);
                    // Continuar de todas formas, el usuario puede agregar destinos manualmente
                }
            } else {
                // Actualizar todos los destinos existentes
                Object.keys(destinationProducts).forEach(destIdx => {
                    updateDestinationProducts(parseInt(destIdx));
                });
            }
            
            return true;
        } catch (e) {
            console.error('Error en nextStep:', e);
            alert('Error al cambiar de paso: ' + e.message);
            return false;
        }
    }
    
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
    const name = document.getElementById('name').value;
    if (!name.trim()) {
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
    const newProduct = container.querySelector('.product-item').cloneNode(true);
    newProduct.setAttribute('data-index', productIndex);
    
    // Actualizar nombres de campos
    newProduct.querySelectorAll('select, input, textarea').forEach(input => {
        if (input.name) {
            input.name = input.name.replace('[0]', '[' + productIndex + ']');
        }
        if (input.id) {
            input.id = input.id.replace('0', productIndex);
        }
        input.value = '';
    });
    
    // Mostrar botón eliminar
    newProduct.querySelector('.remove-product').style.display = 'block';
    newProduct.querySelector('.remove-product').setAttribute('onclick', 'removeProduct(' + productIndex + ')');
    
    // Agregar event listener
    newProduct.querySelector('.product-select').addEventListener('change', function() {
        updateProductUnit(this);
        updateProductPrice(this);
        updateSelectedProducts();
    });
    
    newProduct.querySelector('.product-quantity').addEventListener('input', function() {
        const productItem = this.closest('.product-item');
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        updateSelectedProducts();
    });
    
    newProduct.querySelector('.product-quantity').addEventListener('change', function() {
        const productItem = this.closest('.product-item');
        const select = productItem.querySelector('.product-select');
        if (select && select.value) {
            updateProductPrice(select);
        }
        updateSelectedProducts();
    });
    
    // Limpiar campos de precio
    newProduct.querySelector('.product-precio-unitario').value = 'Bs. 0.00';
    newProduct.querySelector('.product-precio-total').value = 'Bs. 0.00';
    
    container.appendChild(newProduct);
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
                index: itemIndex, // Usar el índice del data-index, no el índice del forEach
                productId: select.value,
                productName: select.options[select.selectedIndex].text,
                quantity: parseInt(quantity)
            });
            productAssignments[itemIndex] = 0; // Inicializar asignación en 0
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
    
    const destinationHtml = `
        <div class="destination-item card mb-3" data-index="${destinationIndex}">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-warehouse"></i> Destino ${destinationIndex}</h5>
                <button type="button" class="btn btn-sm btn-light float-right" onclick="removeDestination(${destinationIndex})">
                    <i class="fas fa-times text-danger"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Dirección <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control destination-address" 
                               name="destinations[${destinationIndex}][address]" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" onclick="openMap(${destinationIndex})">
                                <i class="fas fa-map-marker-alt"></i> Mapa
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Puede editar la dirección manualmente o usar el mapa</small>
                </div>
                
                <input type="hidden" class="destination-latitude" name="destinations[${destinationIndex}][latitude]">
                <input type="hidden" class="destination-longitude" name="destinations[${destinationIndex}][longitude]">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Referencia</label>
                            <input type="text" class="form-control" name="destinations[${destinationIndex}][reference]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contacto</label>
                            <input type="text" class="form-control" name="destinations[${destinationIndex}][contact_name]">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Teléfono de Contacto</label>
                    <input type="text" class="form-control" name="destinations[${destinationIndex}][contact_phone]">
                </div>
                
                <div class="form-group">
                    <label>Instrucciones de Entrega</label>
                    <textarea class="form-control" name="destinations[${destinationIndex}][delivery_instructions]" rows="2"></textarea>
                </div>
                
                <h6><i class="fas fa-box"></i> Productos para este destino:</h6>
                <div class="destination-products" data-destination-index="${destinationIndex}">
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
        // Actualizar botones de otros destinos
        Object.keys(destinationProducts).forEach(destIdx => {
            updateAddProductButton(parseInt(destIdx));
        });
    }
}

function getAvailableProducts() {
    const available = [];
    // NO llamar a updateSelectedProducts() aquí para evitar recursión infinita
    // En su lugar, calcular directamente desde el DOM y usar productAssignments existente
    
    // Usar los índices correctos basados en data-index
    document.querySelectorAll('.product-item').forEach(item => {
        const itemIndex = parseInt(item.getAttribute('data-index') || '0');
        const select = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.product-quantity');
        
        if (select.value && quantityInput && quantityInput.value) {
            const productId = select.value;
            const productName = select.options[select.selectedIndex].text;
            const quantity = parseInt(quantityInput.value);
            
            // Usar productAssignments que ya debería estar actualizado
            const totalAssigned = productAssignments[itemIndex] || 0;
            const remaining = quantity - totalAssigned;
            
            if (remaining > 0) {
                available.push({
                    index: itemIndex, // Usar el índice del data-index
                    productId: productId,
                    productName: productName,
                    quantity: quantity,
                    remaining: remaining
                });
            }
        }
    });
    return available;
}

function updateDestinationProducts(destinationIndex) {
    const container = document.querySelector(`.destination-products[data-destination-index="${destinationIndex}"]`);
    if (!container) return;
    
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
                    <input type="hidden" name="destinations[${destinationIndex}][products][${idx}][order_product_index]" value="${product.index}">
                    <small class="text-muted d-block">Total pedido: ${Math.floor(productQuantity)} | Disponible: ${Math.floor(remaining)}</small>
                </div>
                <div class="col-md-5">
                    <label>Cantidad para este destino <span class="text-danger">*</span></label>
                    <input type="number" class="form-control destination-product-quantity" 
                           name="destinations[${destinationIndex}][products][${idx}][quantity]" 
                           step="1" min="1" max="${Math.floor(maxQuantity)}" 
                           value="${product.assignedQuantity || ''}" 
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
        destinationProducts[destIdx].forEach(p => {
            const qty = parseInt(p.assignedQuantity || 0);
            if (productAssignments[p.index] === undefined) {
                productAssignments[p.index] = 0;
            }
            productAssignments[p.index] += qty;
        });
    });
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
    
    // Actualizar la cantidad del producto
    product.assignedQuantity = newQuantity;
    
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
                
                // Geocodificación inversa (opcional, requiere API)
            });
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
        return false; // Prevenir doble envío
    }
    
    // Validar paso 1 (información básica y productos)
    if (!validateStep1() || !validateStep2()) {
        // Si no está en el paso 2, ir al paso 1
        if (currentStep === 1) {
            return false;
        } else {
            // Ir al paso 1 para mostrar errores
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
        // Asegurar que estemos en el paso 2
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
                
                // Validar que no exceda el máximo
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
        // Asegurar que estemos en el paso 2
        if (currentStep === 1) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            currentStep = 2;
        }
        return false;
    }
    
    if (!hasProducts) {
        alert('Debe asignar al menos un producto a un destino');
        // Asegurar que estemos en el paso 2
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
    // Deshabilitar el botón de envío
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }
    
    // Enviar el formulario
    const form = document.getElementById('pedidoForm');
    if (form) {
        form.submit();
    } else {
        console.error('No se encontró el formulario');
        alert('Error: No se encontró el formulario. Por favor recargue la página.');
    }
    return false;
}

// Inicializar precios al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Calcular totales iniciales
    calculateTotals();
    
    // Actualizar precios de productos ya seleccionados
    document.querySelectorAll('.product-select').forEach(select => {
        if (select.value) {
            updateProductPrice(select);
        }
    });
});

// Asegurar que el paso 2 esté visible si hay errores
document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any() || old('destinations'))
        currentStep = 2;
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        
        // Primero actualizar productos seleccionados
        updateSelectedProducts();
        
        // Recargar destinos si existen (sin mostrar alertas)
        @if(old('destinations'))
            @foreach(old('destinations') as $destIdx => $dest)
                // Agregar destino sin validar productos disponibles
                destinationIndex = {{ $destIdx }};
                const container = document.getElementById('destinationsContainer');
                const destinationHtml = `
                    <div class="destination-item card mb-3" data-index="${destinationIndex}">
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
                                           name="destinations[${destinationIndex}][address]" 
                                           value="{{ old('destinations.' . $destIdx . '.address', '') }}" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" onclick="openMap(${destinationIndex})">
                                            <i class="fas fa-map-marker-alt"></i> Mapa
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Puede editar la dirección manualmente o usar el mapa</small>
                            </div>
                            
                            <input type="hidden" class="destination-latitude" 
                                   name="destinations[${destinationIndex}][latitude]" 
                                   value="{{ old('destinations.' . $destIdx . '.latitude', '') }}">
                            <input type="hidden" class="destination-longitude" 
                                   name="destinations[${destinationIndex}][longitude]" 
                                   value="{{ old('destinations.' . $destIdx . '.longitude', '') }}">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Referencia</label>
                                        <input type="text" class="form-control" 
                                               name="destinations[${destinationIndex}][reference]" 
                                               value="{{ old('destinations.' . $destIdx . '.reference', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contacto</label>
                                        <input type="text" class="form-control" 
                                               name="destinations[${destinationIndex}][contact_name]" 
                                               value="{{ old('destinations.' . $destIdx . '.contact_name', '') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Teléfono de Contacto</label>
                                <input type="text" class="form-control" 
                                       name="destinations[${destinationIndex}][contact_phone]" 
                                       value="{{ old('destinations.' . $destIdx . '.contact_phone', '') }}">
                            </div>
                            
                            <div class="form-group">
                                <label>Instrucciones de Entrega</label>
                                <textarea class="form-control" 
                                          name="destinations[${destinationIndex}][delivery_instructions]" 
                                          rows="2">{{ old('destinations.' . $destIdx . '.delivery_instructions', '') }}</textarea>
                            </div>
                            
                            <h6>Productos para este destino:</h6>
                            <div class="destination-products" data-destination-index="${destinationIndex}">
                                <p class="text-muted">Cargando productos...</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="showProductSelector(${destinationIndex})" id="btnAddProduct${destinationIndex}">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', destinationHtml);
                destinationProducts[destinationIndex] = [];
                
                // Restaurar productos asignados a este destino después de un pequeño delay
                // para asegurar que selectedProducts esté actualizado
                setTimeout(function() {
                    @if(isset($dest['products']) && is_array($dest['products']))
                        @foreach($dest['products'] as $prodIdx => $prod)
                            @php
                                $prodIndex = old('destinations.' . $destIdx . '.products.' . $prodIdx . '.order_product_index', -1);
                                $prodQty = old('destinations.' . $destIdx . '.products.' . $prodIdx . '.quantity', 0);
                            @endphp
                            const productIndex_{{ $destIdx }}_{{ $prodIdx }} = {{ $prodIndex }};
                            const productQuantity_{{ $destIdx }}_{{ $prodIdx }} = {{ $prodQty }};
                            
                            // Buscar el producto en selectedProducts
                            const product_{{ $destIdx }}_{{ $prodIdx }} = selectedProducts.find(p => p.index === productIndex_{{ $destIdx }}_{{ $prodIdx }});
                            if (product_{{ $destIdx }}_{{ $prodIdx }}) {
                                destinationProducts[{{ $destIdx }}].push({
                                    index: product_{{ $destIdx }}_{{ $prodIdx }}.index,
                                    productId: product_{{ $destIdx }}_{{ $prodIdx }}.productId,
                                    productName: product_{{ $destIdx }}_{{ $prodIdx }}.productName,
                                    quantity: product_{{ $destIdx }}_{{ $prodIdx }}.quantity,
                                    assignedQuantity: productQuantity_{{ $destIdx }}_{{ $prodIdx }}
                                });
                            }
                        @endforeach
                        // Recalcular asignaciones y actualizar vista
                        recalculateAllAssignments();
                        updateDestinationProducts({{ $destIdx }});
                    @endif
                }, 100);
            @endforeach
            updateAddDestinationButton();
        @else
            // Si no hay destinos guardados, agregar uno nuevo
            if (Object.keys(destinationProducts).length === 0) {
                addDestination();
            }
        @endif
    @endif
});

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

