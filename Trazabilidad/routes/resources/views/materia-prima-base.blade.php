@extends('layouts.app')

@section('page_title', 'Materia Prima Base')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-seedling mr-1"></i>
                    Materia Prima Base
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearMateriaPrimaModal">
                        <i class="fas fa-plus"></i> Crear Materia Prima
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
                                <h3>{{ $materias_primas->total() }}</h3>
                                <p>Total Materias</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['disponibles'] ?? 0 }}</h3>
                                <p>Disponibles</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $stats['bajo_stock'] ?? 0 }}</h3>
                                <p>Bajo Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $stats['agotadas'] ?? 0 }}</h3>
                                <p>Agotadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-12 col-md-6 col-lg-3 mb-2 mb-md-0">
                        <select class="form-control" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->nombre }}" {{ request('categoria') == $categoria->nombre ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-2 mb-md-0">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="bajo_stock" {{ request('estado') == 'bajo_stock' ? 'selected' : '' }}>Bajo Stock</option>
                            <option value="agotado" {{ request('estado') == 'agotado' ? 'selected' : '' }}>Agotado</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-2 mb-md-0">
                        <input type="text" class="form-control" placeholder="Buscar por nombre..." id="buscarMateria" value="{{ request('buscar', '') }}">
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <button class="btn btn-info btn-block" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['categoria', 'estado', 'buscar']))
                            <a href="{{ route('materia-prima-base') }}" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tabla de Materia Prima -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Máximo</th>
                                <th>Estado</th>
                                <th>Código</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materias_primas as $mp)
                            @php
                                $available = $mp->calculated_available_quantity ?? ($mp->cantidad_disponible ?? 0);
                                $minimum = $mp->stock_minimo ?? 0;
                                $maximum = $mp->stock_maximo ?? null;
                            @endphp
                            <tr>
                                <td>#{{ $mp->material_id }}</td>
                                <td>{{ $mp->nombre }}</td>
                                <td>{{ $mp->category->nombre ?? 'N/A' }}</td>
                                <td>{{ $mp->unit->codigo ?? 'N/A' }}</td>
                                <td>
                                    <strong class="{{ $available <= 0 ? 'text-danger' : ($minimum > 0 && $available <= $minimum ? 'text-warning' : 'text-success') }}">
                                        {{ number_format($available, 2) }}
                                    </strong>
                                    <small class="text-muted"> {{ $mp->unit->codigo ?? '' }}</small>
                                </td>
                                <td>{{ number_format($minimum, 2) }}</td>
                                <td>{{ $maximum !== null && $maximum > 0 ? number_format($maximum, 2) : 'N/A' }}</td>
                                <td>
                                    @if($available <= 0)
                                        <span class="badge badge-danger">Agotado</span>
                                    @elseif($minimum > 0 && $available <= $minimum)
                                        <span class="badge badge-warning">Bajo Stock</span>
                                    @else
                                        <span class="badge badge-success">Disponible</span>
                                    @endif
                                </td>
                                <td>{{ $mp->codigo }}</td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verMateriaPrima({{ $mp->material_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarMateriaPrima({{ $mp->material_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No hay materias primas registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($materias_primas->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $materias_primas->firstItem() }} a {{ $materias_primas->lastItem() }} de {{ $materias_primas->total() }} registros
                        </small>
                    </div>
                    {{ $materias_primas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Materia Prima -->
<div class="modal fade" id="crearMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nueva Materia Prima</h4>
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
                <form method="POST" action="{{ route('materia-prima-base') }}" id="crearMateriaPrimaForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                       placeholder="Ej: Harina de Trigo" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoria_id">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control @error('categoria_id') is-invalid @enderror" 
                                        id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->categoria_id }}" {{ old('categoria_id') == $cat->categoria_id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unidad_id">Unidad de Medida <span class="text-danger">*</span></label>
                                <select class="form-control @error('unidad_id') is-invalid @enderror" 
                                        id="unidad_id" name="unidad_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->unidad_id }}" {{ old('unidad_id') == $unidad->unidad_id ? 'selected' : '' }}>
                                            {{ $unidad->nombre }} ({{ $unidad->codigo }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidad_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock_minimo">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" 
                                       name="stock_minimo" value="{{ old('stock_minimo', 0) }}" 
                                       placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock_maximo">Stock Máximo</label>
                                <input type="number" class="form-control" id="stock_maximo" 
                                       name="stock_maximo" value="{{ old('stock_maximo') }}" 
                                       placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="3" placeholder="Descripción de la materia prima...">{{ old('descripcion') }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="crearMateriaPrimaBtn" onclick="submitCrearMateriaPrima()">
                    <i class="fas fa-save mr-1"></i> Crear Materia Prima
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modal Ver Materia Prima -->
<div class="modal fade" id="verMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles de la Materia Prima
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verMateriaPrimaContent">
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

<!-- Modal Editar Materia Prima -->
<div class="modal fade" id="editarMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Materia Prima
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
                <form method="POST" action="" id="editarMateriaPrimaForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="edit_name" name="nombre" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category_id">
                                    <i class="fas fa-folder mr-1"></i>
                                    Categoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('categoria_id') is-invalid @enderror" 
                                        id="edit_categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->categoria_id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unidad_id">
                                    <i class="fas fa-ruler mr-1"></i>
                                    Unidad de Medida <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('unidad_id') is-invalid @enderror" 
                                        id="edit_unidad_id" name="unidad_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->unidad_id }}">{{ $unidad->nombre }} ({{ $unidad->codigo }})</option>
                                    @endforeach
                                </select>
                                @error('unidad_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_minimum_stock">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    Stock Mínimo
                                </label>
                                <input type="number" class="form-control" id="edit_minimum_stock" 
                                       name="stock_minimo" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_maximum_stock">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    Stock Máximo
                                </label>
                                <input type="number" class="form-control" id="edit_maximum_stock" 
                                       name="stock_maximo" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control" id="edit_description" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="activo" id="edit_active" value="1">
                            Materia Prima Activa
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Materia Prima
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const categorias = @json($categorias);
const unidades = @json($unidades);

function verMateriaPrima(id) {
    fetch(`{{ url('materia-prima-base') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            // Buscar categoría usando categoria_id (nombre correcto de la propiedad)
            const categoria = categorias.find(c => c.categoria_id == data.category_id);
            // Buscar unidad usando unidad_id (nombre correcto de la propiedad)
            const unidad = unidades.find(u => u.unidad_id == data.unit_id);
            const stockActual = data.available_quantity || '0.00';
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.material_id}</td>
                            </tr>
                            <tr>
                                <th>Código</th>
                                <td><span class="badge badge-primary">${data.code}</span></td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name}</td>
                            </tr>
                            <tr>
                                <th>Categoría</th>
                                <td>${categoria ? categoria.nombre : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Unidad</th>
                                <td>${unidad ? unidad.nombre + ' (' + unidad.codigo + ')' : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Stock Actual</th>
                                <td>
                                    <strong>${stockActual}</strong>
                                    <small class="text-muted"> ${unidad ? '(' + unidad.codigo + ')' : ''}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Stock Mínimo</th>
                                <td>${data.minimum_stock || 0}</td>
                            </tr>
                            <tr>
                                <th>Stock Máximo</th>
                                <td>${data.maximum_stock || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.description || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.active 
                                        ? '<span class="badge badge-success">Activa</span>' 
                                        : '<span class="badge badge-danger">Inactiva</span>'}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verMateriaPrimaContent').innerHTML = content;
            $('#verMateriaPrimaModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la materia prima');
        });
}

function editarMateriaPrima(id) {
    fetch(`{{ url('materia-prima-base') }}/${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar los datos');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('editarMateriaPrimaForm').action = `{{ url('materia-prima-base') }}/${id}`;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_categoria_id').value = data.category_id || '';
            document.getElementById('edit_unidad_id').value = data.unit_id || '';
            document.getElementById('edit_minimum_stock').value = data.minimum_stock || 0;
            document.getElementById('edit_maximum_stock').value = data.maximum_stock || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_active').checked = data.active || false;
            
            $('#editarMateriaPrimaModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la materia prima: ' + error.message);
        });
}

async function submitCrearMateriaPrima() {
    const form = document.getElementById('crearMateriaPrimaForm');
    const formData = new FormData(form);
    const submitButton = document.getElementById('crearMateriaPrimaBtn');
    
    // Validar campos requeridos antes de enviar
    const name = form.querySelector('#nombre').value.trim();
    const categoryId = form.querySelector('#categoria_id').value;
    const unitId = form.querySelector('#unidad_id').value;
    
    if (!name || !categoryId || !unitId) {
        alert('Por favor complete todos los campos requeridos');
        return;
    }
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creando...';
    
    try {
        // Obtener el token CSRF del meta tag o del formulario
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         form.querySelector('input[name="_token"]')?.value || 
                         '{{ csrf_token() }}';
        
        // Enviar formulario
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const contentType = response.headers.get('content-type');
        
        if (response.ok) {
            // Si la respuesta es JSON (AJAX)
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error al crear la materia prima');
                }
            } else {
                // Si es un redirect HTML, recargar la página
                window.location.reload();
            }
        } else {
            // Manejar errores de validación
            if (contentType && contentType.includes('application/json')) {
                const errorData = await response.json();
                let errorMessage = 'Error al crear la materia prima';
                
                if (errorData.errors) {
                    const errors = Object.values(errorData.errors).flat();
                    errorMessage = errors.join('\n');
                } else if (errorData.message) {
                    errorMessage = errorData.message;
                }
                
                alert(errorMessage);
            } else {
                // Si es un redirect con errores, recargar para mostrar los errores
                window.location.reload();
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + (error.message || 'Error al crear la materia prima. Por favor intente nuevamente.'));
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Crear Materia Prima';
    }
}

// Manejar envío del formulario de edición
document.getElementById('editarMateriaPrimaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    
    // Asegurar que activo se envíe correctamente (0 si no está marcado, 1 si está marcado)
    if (!formData.has('activo')) {
        formData.append('activo', '0');
    }
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...';
    
    try {
        formData.append('_method', 'PUT');
        
        // Obtener el token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         this.querySelector('input[name="_token"]')?.value || 
                         '{{ csrf_token() }}';
        
        // Enviar formulario
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const contentType = response.headers.get('content-type');
        
        if (response.ok) {
            // Si la respuesta es JSON (AJAX)
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Error al actualizar la materia prima');
                }
            } else {
                // Si es un redirect HTML, recargar la página
                window.location.reload();
            }
        } else {
            // Manejar errores de validación
            if (contentType && contentType.includes('application/json')) {
                const errorData = await response.json();
                let errorMessage = 'Error al actualizar la materia prima';
                
                if (errorData.errors) {
                    const errors = Object.values(errorData.errors).flat();
                    errorMessage = errors.join('\n');
                } else if (errorData.message) {
                    errorMessage = errorData.message;
                }
                
                alert(errorMessage);
            } else {
                // Si es un redirect con errores, recargar para mostrar los errores
                window.location.reload();
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + (error.message || 'Error al actualizar la materia prima. Por favor intente nuevamente.'));
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Actualizar Materia Prima';
    }
});

function aplicarFiltros() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarMateria').value;
    
    const url = new URL(window.location);
    if (categoria) url.searchParams.set('categoria', categoria);
    else url.searchParams.delete('categoria');
    if (estado) url.searchParams.set('estado', estado);
    else url.searchParams.delete('estado');
    if (buscar) url.searchParams.set('buscar', buscar);
    else url.searchParams.delete('buscar');
    window.location = url;
}
</script>
@endpush

