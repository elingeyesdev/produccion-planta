@extends('layouts.app')

@section('page_title', 'Gestiรณn de Procesos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Gestiรณn de Procesos
                </h3>
                <div class="card-tools">
                    <a href="{{ route('procesos.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Proceso
                    </a>
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

                <!-- Estadรญsticas -->
                <div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                                <h3>{{ $procesos->total() }}</h3>
                <p>Total Procesos</p>
            </div>
            <div class="icon">
                <i class="fas fa-project-diagram"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                                <h3>{{ $procesos->where('activo', true)->count() }}</h3>
                <p>Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-play-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                                <h3>0</h3>
                <p>En Revisiรณn</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                                <h3>{{ $procesos->where('activo', false)->count() }}</h3>
                <p>Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-pause-circle"></i>
            </div>
        </div>
    </div>
</div>

                <!-- Tabla de Procesos -->
        <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripciรณn</th>
                                <th>Mรกquinas</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                            @forelse($procesos as $proceso)
                    <tr>
                                <td>#{{ $proceso->proceso_id }}</td>
                                <td>{{ $proceso->nombre }}</td>
                                <td>{{ $proceso->descripcion ?? 'Sin descripciรณn' }}</td>
                                <td>
                                    @if($proceso->processMachines && $proceso->processMachines->count() > 0)
                                        <span class="badge badge-info">{{ $proceso->processMachines->count() }} mรกquina(s)</span>
                                    @else
                                        <span class="badge badge-secondary">Sin mรกquinas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($proceso->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                        </td>
                        <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verProceso({{ $proceso->proceso_id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarProceso({{ $proceso->proceso_id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                            onclick="confirmarEliminarProceso({{ $proceso->proceso_id }}, '{{ $proceso->nombre }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                        </td>
                    </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay procesos registrados</td>
                    </tr>
                            @endforelse
                </tbody>
            </table>
                </div>

                <!-- Paginación -->
                @if($procesos->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            Mostrando {{ $procesos->firstItem() }} a {{ $procesos->lastItem() }} de {{ $procesos->total() }} registros
                        </small>
                    </div>
                    {{ $procesos->links() }}
                </div>
                @endif
        </div>
    </div>
    </div>
</div>

<!-- Modal Crear Proceso -->
<div class="modal fade" id="crearProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Crear Nuevo Proceso
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
                <form method="POST" action="{{ route('procesos.store') }}" id="crearProcesoForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre del Proceso <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre') }}" 
                               placeholder="Ej: Mezclado" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                            <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripciรณn
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Descripciรณn detallada del proceso...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Nota:</strong> Las mรกquinas y variables del proceso se pueden configurar despuรฉs de crear el proceso bรกsico.
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Crear Proceso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Proceso -->
<div class="modal fade" id="verProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Proceso
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verProcesoContent">
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

<!-- Modal Confirmar Eliminaciรณn -->
<div class="modal fade" id="confirmarEliminarProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar Eliminaciรณn
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>ยฟEstรก seguro de eliminar este proceso?</p>
                <p class="font-weight-bold" id="procesoNombreEliminar"></p>
                <p class="text-danger"><small>Esta acciรณn no se puede deshacer.</small></p>
                <form method="POST" id="eliminarProcesoForm" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarProceso()">
                    <i class="fas fa-trash mr-1"></i>
                    Sรญ, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Proceso -->
<div class="modal fade" id="editarProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Proceso
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="" id="editarProcesoForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_nombre">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre del Proceso <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="edit_nombre" name="nombre" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_descripcion">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripciรณn
                        </label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="activo" id="edit_activo" value="1">
                            Proceso Activo
                        </label>
                    </div>

                    <hr class="my-4">

                    <!-- Selecciรณn de mรกquinas -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-cogs mr-1"></i>
                            Mรกquinas del Proceso
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona las mรกquinas disponibles y luego agrega variables estรกndar a cada una.
                        </div>

                        <!-- Mรกquinas disponibles -->
                        <div class="mb-4">
                            <h6 class="mb-2">Mรกquinas Disponibles:</h6>
                            <div class="row" id="editMaquinasDisponibles">
                                @foreach($maquinas as $maquina)
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            @if($maquina->imagen_url)
                                            <img src="{{ $maquina->imagen_url }}" alt="{{ $maquina->nombre }}" 
                                                 class="img-fluid mb-2" style="max-height: 80px; object-fit: contain;">
                                            @else
                                            <div class="bg-light p-2 mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-cog fa-2x text-muted"></i>
                                            </div>
                                            @endif
                                            <h6 class="card-title" style="font-size: 0.9rem;">{{ $maquina->nombre }}</h6>
                                            <button type="button" class="btn btn-sm btn-primary edit-agregar-maquina" 
                                                    data-machine-id="{{ $maquina->maquina_id }}"
                                                    data-machine-name="{{ $maquina->nombre }}"
                                                    data-machine-image="{{ $maquina->imagen_url ?? '' }}">
                                                <i class="fas fa-plus mr-1"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Mรกquinas seleccionadas -->
                        <div id="editMaquinasSeleccionadas" class="mb-4">
                            <h6 class="mb-3">Mรกquinas Seleccionadas:</h6>
                            <div id="editListaMaquinas">
                                <p class="text-muted">No hay mรกquinas seleccionadas. Haz clic en "Agregar" para seleccionar mรกquinas.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Proceso
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
function verProceso(id) {
    fetch(`{{ url('procesos') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            let machinesHtml = '';
            if (data.proceso_maquinas && data.proceso_maquinas.length > 0) {
                data.proceso_maquinas.forEach(function(pm) {
                    let variablesHtml = '';
                    if (pm.variables && pm.variables.length > 0) {
                        variablesHtml = '<ul class="mb-0">';
                        pm.variables.forEach(function(v) {
                            variablesHtml += `<li>${v.variable_nombre} (${v.unidad}): ${v.valor_minimo} - ${v.valor_maximo}${v.valor_objetivo ? ' (Objetivo: ' + v.valor_objetivo + ')' : ''} ${v.obligatorio ? '<span class="badge badge-warning">Obligatorio</span>' : ''}</li>`;
                        });
                        variablesHtml += '</ul>';
                    } else {
                        variablesHtml = '<p class="text-muted mb-0">Sin variables</p>';
                    }
                    
                    machinesHtml += `
                        <div class="card mb-2">
                            <div class="card-header">
                                <strong>${pm.nombre}</strong> - ${pm.maquina_nombre} (Paso ${pm.orden_paso})
                            </div>
                            <div class="card-body">
                                <p><strong>Descripciรณn:</strong> ${pm.descripcion || 'Sin descripciรณn'}</p>
                                <p><strong>Tiempo Estimado:</strong> ${pm.tiempo_estimado || 'N/A'} minutos</p>
                                <p><strong>Variables:</strong></p>
                                ${variablesHtml}
                            </div>
                        </div>
                    `;
                });
            } else {
                machinesHtml = '<p class="text-muted">No hay mรกquinas configuradas</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.proceso_id}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.nombre}</td>
                            </tr>
                            <tr>
                                <th>Descripciรณn</th>
                                <td>${data.descripcion || 'Sin descripciรณn'}</td>
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
                                <th>Mรกquinas del Proceso</th>
                                <td>${machinesHtml}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verProcesoContent').innerHTML = content;
            $('#verProcesoModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proceso');
        });
}

let editMaquinasSeleccionadas = [];
let variablesEstandar = @json($variables);

function editarProceso(id) {
    fetch(`{{ url('procesos') }}/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarProcesoForm').action = `{{ url('procesos') }}/${id}`;
            document.getElementById('edit_nombre').value = data.nombre || '';
            document.getElementById('edit_descripcion').value = data.descripcion || '';
            document.getElementById('edit_activo').checked = data.activo || false;
            
            // Cargar mรกquinas existentes
            editMaquinasSeleccionadas = [];
            if (data.proceso_maquinas && data.proceso_maquinas.length > 0) {
                data.proceso_maquinas.forEach(pm => {
                    const maquina = {
                        maquina_id: pm.maquina_id,
                        nombre: pm.nombre,
                        orden_paso: pm.orden_paso,
                        descripcion: pm.descripcion || '',
                        tiempo_estimado: pm.tiempo_estimado || '',
                        variables: []
                    };
                    
                    if (pm.variables && pm.variables.length > 0) {
                        pm.variables.forEach(v => {
                            maquina.variables.push({
                                variable_estandar_id: v.variable_estandar_id,
                                valor_minimo: v.valor_minimo,
                                valor_maximo: v.valor_maximo,
                                valor_objetivo: v.valor_objetivo || ''
                            });
                        });
                    }
                    
                    editMaquinasSeleccionadas.push(maquina);
                });
            }
            
            editRenderizarMaquinas();
            $('#editarProcesoModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proceso para editar');
        });
}

// Agregar mรกquina al proceso (ediciรณn)
$(document).on('click', '.edit-agregar-maquina', function() {
    const machineId = $(this).data('machine-id');
    const machineName = $(this).data('machine-name');
    const machineImage = $(this).data('machine-image');
    
    // Verificar si ya estรก agregada
    if (editMaquinasSeleccionadas.find(m => m.maquina_id === machineId)) {
        alert('Esta mรกquina ya estรก agregada al proceso');
        return;
    }
    
    const stepOrder = editMaquinasSeleccionadas.length + 1;
    const nuevaMaquina = {
        maquina_id: machineId,
        nombre: machineName,
        imagen_url: machineImage,
        orden_paso: stepOrder,
        descripcion: '',
        tiempo_estimado: '',
        variables: []
    };
    
    editMaquinasSeleccionadas.push(nuevaMaquina);
    editRenderizarMaquinas();
});

// Eliminar mรกquina (ediciรณn)
function editEliminarMaquina(index) {
    editMaquinasSeleccionadas.splice(index, 1);
    // Reordenar orden_paso
    editMaquinasSeleccionadas.forEach((m, i) => {
        m.orden_paso = i + 1;
    });
    editRenderizarMaquinas();
}

// Agregar variable a una mรกquina (ediciรณn)
function editAgregarVariable(maquinaIndex) {
    if (!editMaquinasSeleccionadas[maquinaIndex].variables) {
        editMaquinasSeleccionadas[maquinaIndex].variables = [];
    }
    editMaquinasSeleccionadas[maquinaIndex].variables.push({
        variable_estandar_id: '',
        valor_minimo: '',
        valor_maximo: ''
    });
    editRenderizarMaquinas();
}

// Eliminar variable (ediciรณn)
function editEliminarVariable(maquinaIndex, variableIndex) {
    editMaquinasSeleccionadas[maquinaIndex].variables.splice(variableIndex, 1);
    editRenderizarMaquinas();
}

// Actualizar variable (ediciรณn)
function editActualizarVariable(maquinaIndex, variableIndex, campo, valor) {
    if (!editMaquinasSeleccionadas[maquinaIndex] || !editMaquinasSeleccionadas[maquinaIndex].variables || !editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex]) {
        return;
    }
    
    if (campo === 'variable_estandar_id') {
        const variable = variablesEstandar.find(v => v.variable_id == valor);
        editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex].variable_estandar_id = valor;
        if (variable) {
            $('#edit_unidad_' + maquinaIndex + '_' + variableIndex).val(variable.unidad || '');
        }
    } else {
        editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex][campo] = valor;
    }
}

// Sincronizar cuando se cambian los valores en los inputs (ediciรณn)
$(document).on('change', '#editarProcesoModal input[name*="[valor_minimo]"], #editarProcesoModal input[name*="[valor_maximo]"]', function() {
    const name = $(this).attr('name');
    const matches = name.match(/maquinas\[(\d+)\]\[variables\]\[(\d+)\]/);
    if (matches) {
        const mIndex = parseInt(matches[1]);
        const vIndex = parseInt(matches[2]);
        const campo = name.includes('valor_minimo') ? 'valor_minimo' : 'valor_maximo';
        editActualizarVariable(mIndex, vIndex, campo, $(this).val());
    }
});

// Renderizar mรกquinas seleccionadas (ediciรณn)
function editRenderizarMaquinas() {
    const container = $('#editListaMaquinas');
    
    if (editMaquinasSeleccionadas.length === 0) {
        container.html('<p class="text-muted">No hay mรกquinas seleccionadas. Haz clic en "Agregar" para seleccionar mรกquinas.</p>');
        return;
    }
    
    let html = '';
    editMaquinasSeleccionadas.forEach((maquina, mIndex) => {
        html += `
            <div class="card mb-3 maquina-item" data-index="${mIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Paso ${maquina.orden_paso}: ${maquina.nombre}</strong>
                        <input type="hidden" name="maquinas[${mIndex}][maquina_id]" value="${maquina.maquina_id}">
                        <input type="hidden" name="maquinas[${mIndex}][orden_paso]" value="${maquina.orden_paso}">
                        <input type="hidden" name="maquinas[${mIndex}][nombre]" value="${maquina.nombre}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="editEliminarMaquina(${mIndex})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Descripciรณn (opcional)</label>
                            <input type="text" class="form-control" name="maquinas[${mIndex}][descripcion]" 
                                   value="${maquina.descripcion || ''}" placeholder="Descripciรณn de esta mรกquina en el proceso">
                        </div>
                        <div class="col-md-6">
                            <label>Tiempo Estimado (minutos, opcional)</label>
                            <input type="number" class="form-control" name="maquinas[${mIndex}][tiempo_estimado]" 
                                   value="${maquina.tiempo_estimado || ''}" placeholder="Ej: 30" min="0">
                        </div>
                    </div>
                    
                    <h6 class="mb-2">Variables Estรกndar:</h6>
                    <div id="edit_variables_${mIndex}">
        `;
        
        if (maquina.variables && maquina.variables.length > 0) {
            maquina.variables.forEach((variable, vIndex) => {
                const varEstandar = variablesEstandar.find(v => v.variable_id == variable.variable_estandar_id);
                html += `
                    <div class="row mb-2 variable-item">
                        <div class="col-md-4">
                            <label>Variable <span class="text-danger">*</span></label>
                            <select class="form-control" name="maquinas[${mIndex}][variables][${vIndex}][variable_estandar_id]" 
                                    onchange="editActualizarVariable(${mIndex}, ${vIndex}, 'variable_estandar_id', this.value)" required>
                                <option value="">Seleccionar...</option>
                                ${variablesEstandar.map(v => `
                                    <option value="${v.variable_id}" ${variable.variable_estandar_id == v.variable_id ? 'selected' : ''}>
                                        ${v.nombre} ${v.unidad ? '(' + v.unidad + ')' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Unidad</label>
                            <input type="text" class="form-control" id="edit_unidad_${mIndex}_${vIndex}" 
                                   value="${varEstandar ? (varEstandar.unidad || '') : ''}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Mรญnimo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][valor_minimo]" 
                                   value="${variable.valor_minimo || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Mรกximo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][valor_maximo]" 
                                   value="${variable.valor_maximo || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger btn-block" 
                                    onclick="editEliminarVariable(${mIndex}, ${vIndex})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted">No hay variables agregadas. Haz clic en "Agregar Variable" para agregar una.</p>';
        }
        
        html += `
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="editAgregarVariable(${mIndex})">
                        <i class="fas fa-plus mr-1"></i> Agregar Variable
                    </button>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

// Limpiar al cerrar el modal de ediciรณn
$('#editarProcesoModal').on('hidden.bs.modal', function () {
    editMaquinasSeleccionadas = [];
    $('#editListaMaquinas').html('<p class="text-muted">No hay mรกquinas seleccionadas. Haz clic en "Agregar" para seleccionar mรกquinas.</p>');
});

// Variables para el modal de eliminaciรณn de proceso
let procesoIdAEliminar = null;

function confirmarEliminarProceso(id, nombre) {
    procesoIdAEliminar = id;
    document.getElementById('procesoNombreEliminar').textContent = nombre;
    document.getElementById('eliminarProcesoForm').action = '{{ url("procesos") }}/' + id;
    $('#confirmarEliminarProcesoModal').modal('show');
}

function eliminarProceso() {
    if (procesoIdAEliminar) {
        document.getElementById('eliminarProcesoForm').submit();
    }
}
</script>
@endpush
