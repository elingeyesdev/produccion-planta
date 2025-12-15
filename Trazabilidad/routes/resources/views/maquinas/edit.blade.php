@extends('layouts.app')

@section('page_title', 'Editar Máquina')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Máquina
                </h3>
                <div class="card-tools">
                    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('maquinas.update', $maquina->machine_id) }}" id="editarMaquinaForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Máquina <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $maquina->name) }}" 
                               placeholder="Ej: Mezcladora Industrial" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada de la máquina...">{{ old('description', $maquina->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="image_file">
                            <i class="fas fa-image mr-1"></i>
                            Imagen de la Máquina
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image_file') is-invalid @enderror" 
                                   id="image_file" name="image_file" accept="image/jpeg,image/jpg,image/png" 
                                   onchange="previewImage(this, 'image_preview')">
                            <label class="custom-file-label" for="image_file">Seleccionar nueva imagen...</label>
                            @error('image_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
                        
                        <!-- Previsualización de nueva imagen -->
                        <div id="image_preview_container" class="mt-3" style="display: none;">
                            <p class="text-muted small">Nueva imagen:</p>
                            <img id="image_preview" src="" alt="Vista previa" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImagePreview('image_preview')">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        
                        <!-- Imagen actual -->
                        <input type="hidden" id="current_image_url" name="current_image_url" value="{{ old('current_image_url', $maquina->image_url) }}">
                        @if($maquina->image_url)
                        <div class="mt-2">
                            <p class="text-muted small">Imagen actual:</p>
                            <img src="{{ $maquina->image_url }}" alt="Imagen actual" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                        </div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" 
                                   {{ old('active', $maquina->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Máquina Activa
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Máquina
                        </button>
                        <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById('image_preview_container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function clearImagePreview(previewId) {
    document.getElementById('image_file').value = '';
    document.getElementById(previewId).src = '';
    document.getElementById('image_preview_container').style.display = 'none';
}

// Manejar envío del formulario con carga de imagen
document.getElementById('editarMaquinaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const imageFile = document.getElementById('image_file').files[0];
    const currentImageUrl = document.getElementById('current_image_url').value;
    
    // Si hay una imagen nueva, subirla primero
    if (imageFile) {
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
            formData.append('image_url', uploadResult.imageUrl);
            formData.delete('image_file');
        } catch (error) {
            alert('Error al subir la imagen: ' + error.message);
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
    const fileName = e.target.files[0]?.name || 'Seleccionar nueva imagen...';
    const label = this.nextElementSibling;
    label.textContent = fileName;
});
</script>
@endpush
