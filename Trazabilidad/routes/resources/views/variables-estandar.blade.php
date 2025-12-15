@extends('layouts.app')

@section('page_title', 'Variables Estándar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Variables Estándar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearVariableModal">
                        <i class="fas fa-plus"></i> Crear Variable
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
                                <h3>{{ $variables->total() }}</h3>
                                <p>Total Variables</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $variables->where('activo', true)->count() }}</h3>
                                <p>Activas</p>
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
                                <p>En Revisión</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $variables->where('activo', false)->count() }}</h3>
                                <p>Inactivas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <option value="temperatura">Temperatura</option>
                            <option value="humedad">Humedad</option>
                            <option value="presion">Presión</option>
                            <option value="tiempo">Tiempo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                            <option value="revision">En Revisión</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar..." id="buscarVariable">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Variables -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($variables as $variable)
                            <tr>
                                <td>#{{ $variable->variable_id }}</td>
                                <td>{{ $variable->nombre }}</td>
                                <td>{{ $variable->unidad ?? 'N/A' }}</td>
                                <td>{{ $variable->descripcion ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($variable->activo)
                                        <span class="badge badge-success">Activa</span>
                                    @else
                                        <span class="badge badge-danger">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verVariable({{ $variable->variable_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarVariable({{ $variable->variable_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                            onclick="confirmarEliminar({{ $variable->variable_id }}, '{{ $variable->nombre }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay variables estándar registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($variables->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $variables->firstItem() }} a {{ $variables->lastItem() }} de {{ $variables->total() }} registros
                        </small>
                    </div>
                    {{ $variables->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Variable -->
<div class="modal fade" id="crearVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Crear Nueva Variable Estándar
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
                <form method="POST" action="{{ route('variables-estandar') }}" id="crearVariableForm">
                    @csrf
                    
                            <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Variable <span class="text-danger">*</span>
                        </label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                       placeholder="Ej: Temperatura de Cocción" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="unidad">
                            <i class="fas fa-ruler mr-1"></i>
                            Unidad de Medida
                        </label>
                        <input type="text" class="form-control @error('unidad') is-invalid @enderror" 
                               id="unidad" name="unidad" value="{{ old('unidad') }}" 
                               placeholder="Ej: °C, %, min, kg, etc.">
                        @error('unidad')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Unidad en la que se mide esta variable (opcional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Descripción detallada de la variable estándar...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Crear Variable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modal Ver Variable -->
<div class="modal fade" id="verVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles de la Variable
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verVariableContent">
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
                <p>¿Está seguro de eliminar esta variable estándar?</p>
                <p class="font-weight-bold" id="variableNombreEliminar"></p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                <form method="POST" id="eliminarVariableForm" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarVariable()">
                    <i class="fas fa-trash mr-1"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Variable -->
<div class="modal fade" id="editarVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Variable
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
                <form method="POST" action="" id="editarVariableForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Variable <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="edit_nombre" name="nombre" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unidad">
                                    <i class="fas fa-ruler mr-1"></i>
                                    Unidad
                                </label>
                                <input type="text" class="form-control @error('unidad') is-invalid @enderror" 
                                       id="edit_unidad" name="unidad" value="{{ old('unidad') }}" 
                                       placeholder="Ej: °C, %, min">
                                @error('unidad')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="edit_descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="activo" id="edit_activo" value="1">
                            Variable Activa
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Variable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function verVariable(id) {
    fetch(`{{ url('variables-estandar') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.variable_id}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.nombre}</td>
                            </tr>
                            <tr>
                                <th>Unidad</th>
                                <td>${data.unidad || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.descripcion || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.activo 
                                        ? '<span class="badge badge-success">Activa</span>' 
                                        : '<span class="badge badge-danger">Inactiva</span>'}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verVariableContent').innerHTML = content;
            $('#verVariableModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la variable');
        });
}

function editarVariable(id) {
    fetch(`{{ url('variables-estandar') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarVariableForm').action = `{{ url('variables-estandar') }}/${id}`;
            document.getElementById('edit_nombre').value = data.nombre || '';
            document.getElementById('edit_unidad').value = data.unidad || '';
            document.getElementById('edit_descripcion').value = data.descripcion || '';
            document.getElementById('edit_activo').checked = data.activo || false;
            $('#editarVariableModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la variable');
        });
}

function aplicarFiltros() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarVariable').value;
    
    const url = new URL(window.location);
    if (categoria) url.searchParams.set('categoria', categoria);
    if (estado) url.searchParams.set('estado', estado);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}

// Variables para el modal de eliminación
let variableIdAEliminar = null;

function confirmarEliminar(id, nombre) {
    variableIdAEliminar = id;
    document.getElementById('variableNombreEliminar').textContent = nombre;
    document.getElementById('eliminarVariableForm').action = '{{ url("variables-estandar") }}/' + id;
    $('#confirmarEliminarModal').modal('show');
}

function eliminarVariable() {
    if (variableIdAEliminar) {
        document.getElementById('eliminarVariableForm').submit();
    }
}
</script>
@endpush

