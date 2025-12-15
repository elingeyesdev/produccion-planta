@extends('layouts.app')

@section('page_title', 'Gestión de Máquinas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-1"></i>
                    Gestión de Máquinas
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearMaquinaModal">
                        <i class="fas fa-plus"></i> Nueva Máquina
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
                                <h3>{{ $maquinas->total() }}</h3>
                <p>Total Máquinas</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                                <h3>{{ $maquinas->where('activo', true)->count() }}</h3>
                <p>Operativas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                                <h3>0</h3>
                <p>Mantenimiento</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                                <h3>{{ $maquinas->where('activo', false)->count() }}</h3>
                <p>Fuera de Servicio</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

                <!-- Tabla de Máquinas -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maquinas as $maquina)
                            <tr>
                                <td>
                                    @if($maquina->imagen_url)
                                        <img src="{{ $maquina->imagen_url }}" alt="{{ $maquina->nombre }}" 
                                             class="img-thumbnail" style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-image text-muted"></i>
        </div>
                                    @endif
                                </td>
                                <td>#{{ $maquina->maquina_id }}</td>
                                <td>{{ $maquina->nombre }}</td>
                                <td>{{ $maquina->descripcion ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($maquina->activo)
                                        <span class="badge badge-success">Operativa</span>
                                    @else
                                        <span class="badge badge-danger">Fuera de Servicio</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('maquinas.show', $maquina->maquina_id) }}" 
                                       class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" title="Editar" 
                                            onclick="editarMaquina({{ $maquina->maquina_id }}, '{{ $maquina->nombre }}', '{{ $maquina->descripcion ?? '' }}', '{{ $maquina->imagen_url ?? '' }}', {{ $maquina->activo ? 'true' : 'false' }})">
                                        <i class="fas fa-edit"></i>
                                </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                            onclick="confirmarEliminar({{ $maquina->maquina_id }}, '{{ $maquina->nombre }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay máquinas registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                            </div>

                <!-- Paginación -->
                @if($maquinas->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $maquinas->firstItem() }} a {{ $maquinas->lastItem() }} de {{ $maquinas->total() }} registros
                        </small>
                    </div>
                    {{ $maquinas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
                        </div>

<!-- Modal Crear Máquina -->
<div class="modal fade" id="crearMaquinaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-cogs mr-1"></i>
                    Crear Nueva Máquina
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
                <form method="POST" action="{{ route('maquinas.store') }}" id="crearMaquinaForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Máquina <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre') }}" 
                               placeholder="Ej: Mezcladora Industrial" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Descripción detallada de la máquina...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
            </div>

                    <div class="form-group">
                        <label for="image_file">
                            <i class="fas fa-image mr-1"></i>
                            Imagen de la Máquina <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image_file') is-invalid @enderror" 
                                   id="image_file" name="image_file" accept="image/jpeg,image/jpg,image/png" 
                                   onchange="previewImage(this, 'image_preview')">
                            <label class="custom-file-label" for="image_file">Seleccionar imagen...</label>
                            @error('image_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG (máx. 5MB)</small>
                        
                        <!-- Previsualización de imagen -->
                        <div id="image_preview_container" class="mt-3" style="display: none;">
                            <img id="image_preview" src="" alt="Vista previa" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImagePreview('image_preview')">
                                <i class="fas fa-times"></i> Eliminar
                                </button>
                            </div>
                        
                        <!-- Imagen actual si está editando -->
                        <input type="hidden" id="current_image_url" name="current_image_url" value="{{ old('current_image_url') }}">
                        @if(old('current_image_url'))
                        <div class="mt-2">
                            <p class="text-muted small">Imagen actual:</p>
                            <img src="{{ old('current_image_url') }}" alt="Imagen actual" 
                                 class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                        @endif
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Crear Máquina
                        </button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar Eliminación
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar esta máquina?</p>
                <p class="font-weight-bold" id="maquinaNombreEliminar"></p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                <form method="POST" id="eliminarMaquinaForm" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarMaquina()">
                    <i class="fas fa-trash mr-1"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Máquina -->
<div class="modal fade" id="editarMaquinaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Máquina
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editarMaquinaForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                            <div class="form-group">
                        <label for="edit_nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Máquina <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" 
                               id="edit_nombre" name="nombre" required>
                            </div>
                    
                            <div class="form-group">
                        <label for="edit_descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control" 
                                  id="edit_descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                            <div class="form-group">
                        <label for="edit_image_file">
                            <i class="fas fa-image mr-1"></i>
                            Imagen de la Máquina
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" 
                                   id="edit_image_file" name="image_file" accept="image/jpeg,image/jpg,image/png" 
                                   onchange="previewImage(this, 'edit_image_preview')">
                            <label class="custom-file-label" for="edit_image_file">Seleccionar nueva imagen...</label>
                            </div>
                        <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
                        
                        <!-- Previsualización de nueva imagen -->
                        <div id="edit_image_preview_container" class="mt-3" style="display: none;">
                            <p class="text-muted small">Nueva imagen:</p>
                            <img id="edit_image_preview" src="" alt="Vista previa" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImagePreview('edit_image_preview')">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        
                        <!-- Imagen actual -->
                        <input type="hidden" id="edit_current_image_url" name="current_image_url">
                        <div id="edit_current_image_container" class="mt-2" style="display: none;">
                            <p class="text-muted small">Imagen actual:</p>
                            <img id="edit_current_image" src="" alt="Imagen actual" 
                                 class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>

                            <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_activo" name="activo" value="1">
                            <label class="form-check-label" for="edit_activo">
                                Máquina Activa
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Máquina
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
let uploadingImage = false;

function previewImage(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            const container = previewId === 'image_preview' ? 'image_preview_container' : 'edit_image_preview_container';
            document.getElementById(container).style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function clearImagePreview(previewId) {
    const inputId = previewId === 'image_preview' ? 'image_file' : 'edit_image_file';
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).src = '';
    const container = previewId === 'image_preview' ? 'image_preview_container' : 'edit_image_preview_container';
    document.getElementById(container).style.display = 'none';
}

function editarMaquina(id, nombre, descripcion, imagenUrl, activo) {
    document.getElementById('editarMaquinaForm').action = '{{ url("maquinas") }}/' + id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion || '';
    document.getElementById('edit_current_image_url').value = imagenUrl || '';
    document.getElementById('edit_activo').checked = activo;
    
    // Mostrar imagen actual si existe
    if (imagenUrl) {
        document.getElementById('edit_current_image').src = imagenUrl;
        document.getElementById('edit_current_image_container').style.display = 'block';
    } else {
        document.getElementById('edit_current_image_container').style.display = 'none';
    }
    
    // Limpiar previsualización de nueva imagen
    document.getElementById('edit_image_file').value = '';
    document.getElementById('edit_image_preview_container').style.display = 'none';
    
    $('#editarMaquinaModal').modal('show');
}

// Manejar envío del formulario de creación con carga de imagen
document.getElementById('crearMaquinaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const imageFile = document.getElementById('image_file').files[0];
    
    if (!imageFile) {
        alert('Por favor, seleccione una imagen');
        return;
    }
    
    // Subir imagen primero
    uploadingImage = true;
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Subiendo imagen...';
    
    try {
        const uploadFormData = new FormData();
        uploadFormData.append('image', imageFile);
        uploadFormData.append('folder', 'maquinas');
        
        const uploadResponse = await fetch('{{ route("upload-image") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: uploadFormData
        });
        
        const uploadResult = await uploadResponse.json();
        
        if (!uploadResult.success) {
            throw new Error(uploadResult.message || 'Error al subir la imagen');
        }
        
        // Agregar la URL de la imagen al formulario
        formData.append('imagen_url', uploadResult.imageUrl);
        formData.delete('image_file'); // Eliminar el archivo del FormData
        
        // Crear un formulario temporal para enviar
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = this.action;
        tempForm.style.display = 'none';
        
        // Agregar CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        tempForm.appendChild(csrfInput);
        
        // Agregar otros campos
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
    } catch (error) {
        alert('Error al subir la imagen: ' + error.message);
        uploadingImage = false;
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Crear Máquina';
    }
});

// Manejar envío del formulario de edición con carga de imagen
document.getElementById('editarMaquinaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const imageFile = document.getElementById('edit_image_file').files[0];
    const currentImageUrl = document.getElementById('edit_current_image_url').value;
    
    // Si hay una imagen nueva, subirla primero
    if (imageFile) {
        uploadingImage = true;
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Subiendo imagen...';
        
        try {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('folder', 'maquinas');
            
            const uploadResponse = await fetch('{{ route("upload-image") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: uploadFormData
            });
            
            const uploadResult = await uploadResponse.json();
            
            if (!uploadResult.success) {
                throw new Error(uploadResult.message || 'Error al subir la imagen');
            }
            
            // Agregar la URL de la imagen al formulario
            formData.append('imagen_url', uploadResult.imageUrl);
            formData.delete('image_file');
        } catch (error) {
            alert('Error al subir la imagen: ' + error.message);
            uploadingImage = false;
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Actualizar Máquina';
            return;
        }
    } else {
        // Mantener la imagen actual
        formData.append('current_image_url', currentImageUrl);
    }
    
    // Crear un formulario temporal para enviar
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = this.action;
    tempForm.style.display = 'none';
    
    // Agregar CSRF token y método PUT
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrfInput);
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';
    tempForm.appendChild(methodInput);
    
    // Agregar otros campos
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
});

// Actualizar label del input file
document.getElementById('image_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar imagen...';
    const label = this.nextElementSibling;
    label.textContent = fileName;
});

document.getElementById('edit_image_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar nueva imagen...';
    const label = this.nextElementSibling;
    label.textContent = fileName;
});

// Limpiar formularios al cerrar modales
$('#crearMaquinaModal').on('hidden.bs.modal', function () {
    document.getElementById('crearMaquinaForm').reset();
    document.getElementById('image_preview_container').style.display = 'none';
    document.getElementById('image_file').nextElementSibling.textContent = 'Seleccionar imagen...';
});

$('#editarMaquinaModal').on('hidden.bs.modal', function () {
    document.getElementById('editarMaquinaForm').reset();
    document.getElementById('edit_image_preview_container').style.display = 'none';
    document.getElementById('edit_current_image_container').style.display = 'none';
    document.getElementById('edit_image_file').nextElementSibling.textContent = 'Seleccionar nueva imagen...';
});

// Variables para el modal de eliminación
let maquinaIdAEliminar = null;

function confirmarEliminar(id, nombre) {
    maquinaIdAEliminar = id;
    document.getElementById('maquinaNombreEliminar').textContent = nombre;
    document.getElementById('eliminarMaquinaForm').action = '{{ url("maquinas") }}/' + id;
    $('#confirmarEliminarModal').modal('show');
}

function eliminarMaquina() {
    if (maquinaIdAEliminar) {
        document.getElementById('eliminarMaquinaForm').submit();
    }
}
</script>
@endpush
