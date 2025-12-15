@extends('layouts.app')

@section('page_title', 'Gestión de Usuarios')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Gestión de Usuarios
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearUsuarioModal">
                        <i class="fas fa-plus"></i> Crear Usuario
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
                                <h3>{{ $usuarios->total() }}</h3>
                                <p>Total Usuarios</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $usuarios->where('activo', true)->count() }}</h3>
                                <p>Activos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>0</h3>
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $usuarios->where('activo', false)->count() }}</h3>
                                <p>Inactivos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroRol">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar usuario..." id="buscarUsuario" value="{{ request('buscar', '') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['rol', 'estado', 'buscar']))
                            <a href="{{ route('usuarios') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuarioItem)
                            <tr>
                                <td>#{{ $usuarioItem->operador_id }}</td>
                                <td>{{ $usuarioItem->nombre }} {{ $usuarioItem->apellido }}</td>
                                <td>{{ $usuarioItem->email ?? 'N/A' }}</td>
                                <td><span class="badge badge-primary">{{ $usuarioItem->roles->first()->name ?? 'N/A' }}</span></td>
                                <td>
                                    @if($usuarioItem->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('usuarios.edit', $usuarioItem->operador_id) }}" 
                                       class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" title="Eliminar"
                                            onclick="abrirModalEliminar({{ $usuarioItem->operador_id }}, {{ json_encode($usuarioItem->nombre . ' ' . $usuarioItem->apellido) }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay usuarios registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($usuarios->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} registros
                        </small>
                    </div>
                    {{ $usuarios->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Usuario</h4>
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
                <form method="POST" action="{{ route('usuarios') }}" id="crearUsuarioForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                       placeholder="Ej: Juan" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('apellido') is-invalid @enderror" 
                                       id="apellido" name="apellido" value="{{ old('apellido') }}" 
                                       placeholder="Ej: Pérez" required>
                                @error('apellido')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario">Usuario <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('usuario') is-invalid @enderror" 
                                       id="usuario" name="usuario" value="{{ old('usuario') }}" 
                                       placeholder="Ej: juan.perez" required>
                                @error('usuario')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="juan.perez@empresa.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rol">Rol <span class="text-danger">*</span></label>
                                <select class="form-control @error('rol') is-invalid @enderror" 
                                        id="rol" name="rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->name }}" {{ old('rol') == $rol->name ? 'selected' : '' }}>
                                            {{ ucfirst($rol->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rol')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
@if(isset($usuario) && isset($editing) && $editing)
<div class="modal fade show" id="editarUsuarioModal" tabindex="-1" role="dialog" style="display: block;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Usuario</h4>
                <button type="button" class="close" onclick="cerrarModalEdicion()">
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
                <form method="POST" action="{{ route('usuarios.update', $usuario->operador_id) }}" id="editarUsuarioForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="edit_nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" 
                                       placeholder="Ej: Juan" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_apellido">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('apellido') is-invalid @enderror" 
                                       id="edit_apellido" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" 
                                       placeholder="Ej: Pérez" required>
                                @error('apellido')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_usuario">Usuario <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('usuario') is-invalid @enderror" 
                                       id="edit_usuario" name="usuario" value="{{ old('usuario', $usuario->usuario) }}" 
                                       placeholder="Ej: juan.perez" required>
                                @error('usuario')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="edit_email" name="email" value="{{ old('email', $usuario->email ?? '') }}" 
                                       placeholder="juan.perez@empresa.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_password">Contraseña</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="edit_password" name="password" placeholder="Dejar en blanco para no cambiar">
                                <small class="form-text text-muted">Dejar en blanco si no desea cambiar la contraseña</small>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_rol">Rol <span class="text-danger">*</span></label>
                                <select class="form-control @error('rol') is-invalid @enderror" 
                                        id="edit_rol" name="rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    @php
                                        $rolActual = old('rol');
                                        if ($rolActual === null) {
                                            $rolActual = $usuario->roles->first()->name ?? '';
                                        }
                                    @endphp
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->name }}" 
                                                {{ $rolActual == $rol->name ? 'selected' : '' }}>
                                            {{ ucfirst($rol->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rol')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    @php
                                        $activoValue = old('activo');
                                        if ($activoValue === null) {
                                            $activoValue = $usuario->activo ?? false;
                                        } else {
                                            $activoValue = (bool)$activoValue;
                                        }
                                    @endphp
                                    <input type="checkbox" class="form-check-input" id="edit_activo" name="activo" value="1" 
                                           {{ $activoValue ? 'checked' : '' }}>
                                    <label class="form-check-label" for="edit_activo">
                                        Usuario Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalEdicion()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
@endif

<!-- Modal Eliminar Usuario -->
<div class="modal fade" id="eliminarUsuarioModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                </div>
                <p>¿Está seguro de que desea eliminar al usuario <strong id="nombreUsuarioEliminar"></strong>?</p>
                <p class="text-muted">Se eliminarán todos los datos asociados a este usuario.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="formEliminarUsuario" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function cerrarModalEdicion() {
    window.location.href = '{{ route("usuarios") }}';
}

function abrirModalEliminar(id, nombre) {
    // Establecer el nombre del usuario en el modal
    document.getElementById('nombreUsuarioEliminar').textContent = nombre;
    
    // Establecer la acción del formulario
    const form = document.getElementById('formEliminarUsuario');
    form.action = '{{ url("usuarios") }}/' + id;
    
    // Abrir el modal
    $('#eliminarUsuarioModal').modal('show');
}

function aplicarFiltros() {
    const rol = document.getElementById('filtroRol').value;
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarUsuario').value;
    
    const url = new URL(window.location);
    if (rol) url.searchParams.set('rol', rol);
    else url.searchParams.delete('rol');
    if (estado) url.searchParams.set('estado', estado);
    else url.searchParams.delete('estado');
    if (buscar) url.searchParams.set('buscar', buscar);
    else url.searchParams.delete('buscar');
    window.location = url;
}

// Limpiar formulario cuando se cierra el modal
$(document).ready(function() {
    // Limpiar formulario cuando se cierra el modal
    $('#crearUsuarioModal').on('hidden.bs.modal', function () {
        $('#crearUsuarioForm')[0].reset();
        // Limpiar mensajes de error
        $('#crearUsuarioForm .is-invalid').removeClass('is-invalid');
        $('#crearUsuarioForm .invalid-feedback').remove();
        $('.alert-danger').remove();
    });

    // Si hay un mensaje de éxito, cerrar el modal y limpiar el formulario
    @if(session('success'))
        $('#crearUsuarioModal').modal('hide');
        $('#crearUsuarioForm')[0].reset();
    @endif

    // Si hay errores, mantener el modal abierto
    @if($errors->any())
        $('#crearUsuarioModal').modal('show');
    @endif
});
</script>
@endpush

