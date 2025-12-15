@extends('layouts.app')

@section('page_title', 'Gestión de Proveedores')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i>
                    Gestión de Proveedores
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearProveedorModal">
                        <i class="fas fa-plus"></i> Nuevo Proveedor
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
                                <h3>{{ $proveedores->total() }}</h3>
                                <p>Total Proveedores</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $proveedores->where('activo', true)->count() }}</h3>
                                <p>Activos</p>
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
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $proveedores->where('activo', false)->count() }}</h3>
                                <p>Inactivos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Proveedores -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Comercial</th>
                                <th>Razón Social</th>
                                <th>Persona de Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proveedores as $proveedor)
                            <tr>
                                <td>#{{ $proveedor->proveedor_id }}</td>
                                <td>{{ $proveedor->nombre_comercial ?? $proveedor->razon_social }}</td>
                                <td>{{ $proveedor->razon_social }}</td>
                                <td>{{ $proveedor->contacto ?? 'N/A' }}</td>
                                <td>{{ $proveedor->telefono ?? 'N/A' }}</td>
                                <td>{{ $proveedor->email ?? 'N/A' }}</td>
                                <td>
                                    @if($proveedor->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="btn-group-vertical btn-group-sm d-md-none" role="group">
                                        <button onclick="verProveedor({{ $proveedor->proveedor_id }})" 
                                                class="btn btn-info mb-1" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editarProveedor({{ $proveedor->proveedor_id }})" 
                                                class="btn btn-warning mb-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmarEliminarProveedor({{ $proveedor->proveedor_id }}, '{{ $proveedor->razon_social }}')" 
                                                class="btn btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group btn-group-sm d-none d-md-inline-flex" role="group">
                                        <button onclick="verProveedor({{ $proveedor->proveedor_id }})" 
                                                class="btn btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editarProveedor({{ $proveedor->proveedor_id }})" 
                                                class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmarEliminarProveedor({{ $proveedor->proveedor_id }}, '{{ $proveedor->razon_social }}')" 
                                                class="btn btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay proveedores registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($proveedores->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $proveedores->firstItem() }} a {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                        </small>
                    </div>
                    {{ $proveedores->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Proveedor -->
<div class="modal fade" id="crearProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-truck mr-1"></i>
                    Crear Nuevo Proveedor
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
                <form method="POST" action="{{ route('proveedores.web.store') }}" id="crearProveedorForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="business_name">
                                    <i class="fas fa-building mr-1"></i>
                                    Razón Social <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                       id="razon_social" name="razon_social" value="{{ old('razon_social') }}" 
                                       placeholder="Ej: Empresa ABC S.A." required>
                                @error('razon_social')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="trading_name">
                                    <i class="fas fa-store mr-1"></i>
                                    Nombre Comercial
                                </label>
                                <input type="text" class="form-control @error('nombre_comercial') is-invalid @enderror" 
                                       id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial') }}" 
                                       placeholder="Ej: ABC Comercial">
                                @error('nombre_comercial')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_id">
                                    <i class="fas fa-id-card mr-1"></i>
                                    RUC/NIT
                                </label>
                                <input type="text" class="form-control @error('nit') is-invalid @enderror" 
                                       id="nit" name="nit" value="{{ old('nit') }}" 
                                       placeholder="Ej: 12345678901">
                                @error('nit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contacto">
                                    <i class="fas fa-user mr-1"></i>
                                    Persona de Contacto
                                </label>
                                <input type="text" class="form-control @error('contacto') is-invalid @enderror" 
                                       id="contacto" name="contacto" value="{{ old('contacto') }}" 
                                       placeholder="Ej: Juan Pérez">
                                @error('contacto')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" name="telefono" value="{{ old('telefono') }}" 
                                       placeholder="Ej: +1 234-567-8900">
                                @error('telefono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="contacto@proveedor.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </label>
                        <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                  id="direccion" name="direccion" rows="2" 
                                  placeholder="Dirección completa del proveedor...">{{ old('direccion') }}</textarea>
                        @error('direccion')
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
                            Crear Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Proveedor -->
<div class="modal fade" id="verProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Proveedor
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verProveedorContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando detalles...</p>
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
                <p>¿Está seguro de eliminar este proveedor?</p>
                <p class="font-weight-bold" id="proveedorNombreEliminar"></p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                <form method="POST" id="eliminarProveedorForm" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarProveedor()">
                    <i class="fas fa-trash mr-1"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="editarProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Proveedor
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
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
                <form method="POST" action="" id="editarProveedorForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_business_name">
                                    <i class="fas fa-building mr-1"></i>
                                    Razón Social <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                       id="edit_razon_social" name="razon_social" required>
                                @error('razon_social')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_trading_name">
                                    <i class="fas fa-store mr-1"></i>
                                    Nombre Comercial
                                </label>
                                <input type="text" class="form-control @error('nombre_comercial') is-invalid @enderror" 
                                       id="edit_nombre_comercial" name="nombre_comercial">
                                @error('nombre_comercial')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tax_id">
                                    <i class="fas fa-id-card mr-1"></i>
                                    RUC/NIT
                                </label>
                                <input type="text" class="form-control @error('nit') is-invalid @enderror" 
                                       id="edit_nit" name="nit">
                                @error('nit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_contact_person">
                                    <i class="fas fa-user mr-1"></i>
                                    Persona de Contacto
                                </label>
                                <input type="text" class="form-control @error('contacto') is-invalid @enderror" 
                                       id="edit_contacto" name="contacto">
                                @error('contacto')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone">
                                    <i class="fas fa-phone mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="edit_telefono" name="telefono">
                                @error('telefono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="edit_email" name="email">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_address">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </label>
                        <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                  id="edit_direccion" name="direccion" rows="2"></textarea>
                        @error('direccion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <input type="hidden" name="activo" value="0" id="edit_activo_hidden">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="edit_activo" value="1"
                                   onchange="document.getElementById('edit_activo_hidden').value = this.checked ? '1' : '0'">
                            <label class="custom-control-label" for="edit_activo">
                                <i class="fas fa-check-circle mr-1"></i>
                                Proveedor Activo
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
                            Actualizar Proveedor
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
const proveedoresBaseUrl = '{{ url("proveedores") }}';

function verProveedor(id) {
    $('#verProveedorModal').modal('show');
    $('#verProveedorContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    fetch(`${proveedoresBaseUrl}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.proveedor_id}</td>
                            </tr>
                            <tr>
                                <th>Razón Social</th>
                                <td>${data.razon_social || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Nombre Comercial</th>
                                <td>${data.nombre_comercial || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>RUC/NIT</th>
                                <td>${data.nit || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Persona de Contacto</th>
                                <td>${data.contacto || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Teléfono</th>
                                <td>${data.telefono || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>${data.email || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Dirección</th>
                                <td>${data.direccion || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.activo 
                                        ? '<span class="badge badge-success">Activo</span>' 
                                        : '<span class="badge badge-danger">Inactivo</span>'}
                                </td>
                            </tr>
                            <tr>
                                <th>Materias Primas Recibidas</th>
                                <td>${data.raw_materials_count || 0}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            $('#verProveedorContent').html(content);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#verProveedorContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los datos del proveedor
                </div>
            `);
        });
}

function editarProveedor(id) {
    fetch(`${proveedoresBaseUrl}/${id}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarProveedorForm').action = `${proveedoresBaseUrl}/${id}`;
            document.getElementById('edit_razon_social').value = data.razon_social || '';
            document.getElementById('edit_nombre_comercial').value = data.nombre_comercial || '';
            document.getElementById('edit_nit').value = data.nit || '';
            document.getElementById('edit_contacto').value = data.contacto || '';
            document.getElementById('edit_telefono').value = data.telefono || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_direccion').value = data.direccion || '';
            const activoCheckbox = document.getElementById('edit_activo');
            const activoHidden = document.getElementById('edit_activo_hidden');
            activoCheckbox.checked = data.activo || false;
            activoHidden.value = data.activo ? '1' : '0';
            $('#editarProveedorModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proveedor para editar');
        });
}

function confirmarEliminarProveedor(id, nombre) {
    document.getElementById('proveedorNombreEliminar').textContent = nombre;
    document.getElementById('eliminarProveedorForm').action = `${proveedoresBaseUrl}/${id}`;
    $('#confirmarEliminarModal').modal('show');
}

function eliminarProveedor() {
    document.getElementById('eliminarProveedorForm').submit();
}
</script>
@endpush
