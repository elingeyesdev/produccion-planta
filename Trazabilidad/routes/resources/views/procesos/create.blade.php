@extends('layouts.app')

@section('page_title', 'Crear Nuevo Proceso')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Crear Nuevo Proceso
                </h3>
                <div class="card-tools">
                    <a href="{{ route('procesos.index') }}" class="btn btn-secondary btn-sm">
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

                <form method="POST" action="{{ route('procesos.store') }}" id="crearProcesoForm">
  @csrf
                    
                    <!-- Información básica del proceso -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">
                                    <i class="fas fa-tag mr-1"></i>
                                    Nombre del Proceso <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                       placeholder="Ej: Proceso de Mezclado" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="descripcion">
                                    <i class="fas fa-align-left mr-1"></i>
                                    Descripción
                                </label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" name="descripcion" rows="2" 
                                          placeholder="Descripción del proceso...">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Selección de máquinas -->
                    <div class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-cogs mr-1"></i>
                            Máquinas del Proceso
                        </h4>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona las máquinas disponibles y luego agrega variables estándar a cada una.
                        </div>

                        <!-- Máquinas disponibles -->
                        <div class="mb-4">
                            <h5 class="mb-2">Máquinas Disponibles:</h5>
                            <div class="row" id="maquinasDisponibles">
                                @foreach($maquinas as $maquina)
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            @if($maquina->imagen_url)
                                            <img src="{{ $maquina->imagen_url }}" alt="{{ $maquina->nombre }}" 
                                                 class="img-fluid mb-2" style="max-height: 100px; object-fit: contain;">
                                            @else
                                            <div class="bg-light p-3 mb-2" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-cog fa-3x text-muted"></i>
                                            </div>
                                            @endif
                                            <h6 class="card-title">{{ $maquina->nombre }}</h6>
                                            <button type="button" class="btn btn-sm btn-primary agregar-maquina" 
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

                        <!-- Máquinas seleccionadas -->
                        <div id="maquinasSeleccionadas" class="mb-4">
                            <h5 class="mb-3">Máquinas Seleccionadas:</h5>
                            <div id="listaMaquinas">
                                <p class="text-muted">No hay máquinas seleccionadas. Haz clic en "Agregar" para seleccionar máquinas.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('procesos.index') }}'">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Guardar Proceso
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
let maquinasSeleccionadas = [];
let variablesEstandar = @json($variables);

// Agregar máquina al proceso
$(document).on('click', '.agregar-maquina', function() {
    const machineId = $(this).data('machine-id');
    const machineName = $(this).data('machine-name');
    const machineImage = $(this).data('machine-image');
    
    // Verificar si ya está agregada
    if (maquinasSeleccionadas.find(m => m.machine_id === machineId)) {
        alert('Esta máquina ya está agregada al proceso');
        return;
    }
    
    const stepOrder = maquinasSeleccionadas.length + 1;
    const nuevaMaquina = {
        machine_id: machineId,
        name: machineName,
        image_url: machineImage,
        step_order: stepOrder,
        variables: []
    };
    
    maquinasSeleccionadas.push(nuevaMaquina);
    renderizarMaquinas();
});

// Eliminar máquina
function eliminarMaquina(index) {
    maquinasSeleccionadas.splice(index, 1);
    // Reordenar step_order
    maquinasSeleccionadas.forEach((m, i) => {
        m.step_order = i + 1;
    });
    renderizarMaquinas();
}

// Agregar variable a una máquina
function agregarVariable(maquinaIndex) {
    if (!maquinasSeleccionadas[maquinaIndex].variables) {
        maquinasSeleccionadas[maquinaIndex].variables = [];
    }
    maquinasSeleccionadas[maquinaIndex].variables.push({
        standard_variable_id: '',
        min_value: '',
        max_value: ''
    });
    renderizarMaquinas();
}

// Eliminar variable
function eliminarVariable(maquinaIndex, variableIndex) {
    maquinasSeleccionadas[maquinaIndex].variables.splice(variableIndex, 1);
    renderizarMaquinas();
}

// Actualizar variable
function actualizarVariable(maquinaIndex, variableIndex, campo, valor) {
    if (!maquinasSeleccionadas[maquinaIndex] || !maquinasSeleccionadas[maquinaIndex].variables || !maquinasSeleccionadas[maquinaIndex].variables[variableIndex]) {
        return;
    }
    
    if (campo === 'variable_estandar_id') {
        const variable = variablesEstandar.find(v => v.variable_id == valor);
        // Guardar en el array interno con el nombre que usa el código
        maquinasSeleccionadas[maquinaIndex].variables[variableIndex].standard_variable_id = valor;
        if (variable) {
            // Actualizar unidad automáticamente si existe
            $('#unidad_' + maquinaIndex + '_' + variableIndex).val(variable.unidad || '');
        }
    } else {
        // Mapear nombres de campos para el array interno
        const campoMap = {
            'valor_minimo': 'min_value',
            'valor_maximo': 'max_value'
        };
        const campoInterno = campoMap[campo] || campo;
        maquinasSeleccionadas[maquinaIndex].variables[variableIndex][campoInterno] = valor;
    }
}

// Sincronizar cuando se cambian los valores en los inputs
$(document).on('change', 'input[name*="[valor_minimo]"], input[name*="[valor_maximo]"]', function() {
    const name = $(this).attr('name');
    const matches = name.match(/maquinas\[(\d+)\]\[variables\]\[(\d+)\]/);
    if (matches) {
        const mIndex = parseInt(matches[1]);
        const vIndex = parseInt(matches[2]);
        const campo = name.includes('valor_minimo') ? 'valor_minimo' : 'valor_maximo';
        actualizarVariable(mIndex, vIndex, campo, $(this).val());
    }
});

// Renderizar máquinas seleccionadas
function renderizarMaquinas() {
    const container = $('#listaMaquinas');
    
    if (maquinasSeleccionadas.length === 0) {
        container.html('<p class="text-muted">No hay máquinas seleccionadas. Haz clic en "Agregar" para seleccionar máquinas.</p>');
        return;
    }
    
    let html = '';
    maquinasSeleccionadas.forEach((maquina, mIndex) => {
        html += `
            <div class="card mb-3 maquina-item" data-index="${mIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Paso ${maquina.step_order}: ${maquina.name}</strong>
                        <input type="hidden" name="maquinas[${mIndex}][maquina_id]" value="${maquina.machine_id}">
                        <input type="hidden" name="maquinas[${mIndex}][orden_paso]" value="${maquina.step_order}">
                        <input type="hidden" name="maquinas[${mIndex}][nombre]" value="${maquina.name}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarMaquina(${mIndex})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Descripción (opcional)</label>
                            <input type="text" class="form-control" name="maquinas[${mIndex}][descripcion]" 
                                   placeholder="Descripción de esta máquina en el proceso">
                        </div>
                        <div class="col-md-6">
                            <label>Tiempo Estimado (minutos, opcional)</label>
                            <input type="number" class="form-control" name="maquinas[${mIndex}][tiempo_estimado]" 
                                   placeholder="Ej: 30" min="0">
                        </div>
                    </div>
                    
                    <h6 class="mb-2">Variables Estándar:</h6>
                    <div id="variables_${mIndex}">
        `;
        
        if (maquina.variables && maquina.variables.length > 0) {
            maquina.variables.forEach((variable, vIndex) => {
                const varEstandar = variablesEstandar.find(v => v.variable_id == variable.standard_variable_id);
                html += `
                    <div class="row mb-2 variable-item">
                        <div class="col-md-4">
                            <label>Variable <span class="text-danger">*</span></label>
                            <select class="form-control" name="maquinas[${mIndex}][variables][${vIndex}][variable_estandar_id]" 
                                    onchange="actualizarVariable(${mIndex}, ${vIndex}, 'variable_estandar_id', this.value)" required>
                                <option value="">Seleccionar...</option>
                                ${variablesEstandar.map(v => `
                                    <option value="${v.variable_id}" ${variable.standard_variable_id == v.variable_id ? 'selected' : ''}>
                                        ${v.nombre} ${v.unidad ? '(' + v.unidad + ')' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Unidad</label>
                            <input type="text" class="form-control" id="unidad_${mIndex}_${vIndex}" 
                                   value="${varEstandar ? (varEstandar.unidad || '') : ''}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Mínimo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][valor_minimo]" 
                                   value="${variable.min_value || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Máximo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][valor_maximo]" 
                                   value="${variable.max_value || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger btn-block" 
                                    onclick="eliminarVariable(${mIndex}, ${vIndex})">
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
                    <button type="button" class="btn btn-sm btn-success" onclick="agregarVariable(${mIndex})">
                        <i class="fas fa-plus mr-1"></i> Agregar Variable
                    </button>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

// Sincronizar valores del formulario con el array antes de validar
function sincronizarValoresFormulario() {
    maquinasSeleccionadas.forEach((maquina, mIndex) => {
        if (maquina.variables) {
            maquina.variables.forEach((variable, vIndex) => {
                // Obtener valores del formulario
                const selectVariable = $(`select[name="maquinas[${mIndex}][variables][${vIndex}][variable_estandar_id]"]`);
                const inputMin = $(`input[name="maquinas[${mIndex}][variables][${vIndex}][valor_minimo]"]`);
                const inputMax = $(`input[name="maquinas[${mIndex}][variables][${vIndex}][valor_maximo]"]`);
                
                if (selectVariable.length) {
                    variable.standard_variable_id = selectVariable.val() || '';
                }
                if (inputMin.length) {
                    variable.min_value = inputMin.val() || '';
                }
                if (inputMax.length) {
                    variable.max_value = inputMax.val() || '';
                }
            });
        }
    });
}

// Validar formulario antes de enviar
$('#crearProcesoForm').on('submit', function(e) {
    // Sincronizar valores del formulario con el array
    sincronizarValoresFormulario();
    
    if (maquinasSeleccionadas.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos una máquina al proceso');
        return false;
    }
    
    // Validar que cada máquina tenga al menos una variable
    for (let i = 0; i < maquinasSeleccionadas.length; i++) {
        if (!maquinasSeleccionadas[i].variables || maquinasSeleccionadas[i].variables.length === 0) {
            e.preventDefault();
            alert(`La máquina "${maquinasSeleccionadas[i].name}" debe tener al menos una variable estándar`);
            return false;
        }
        
        // Validar que cada variable tenga valores min y max
        for (let j = 0; j < maquinasSeleccionadas[i].variables.length; j++) {
            // Leer valores directamente del formulario HTML
            const selectVariable = $(`select[name="maquinas[${i}][variables][${j}][variable_estandar_id]"]`);
            const inputMin = $(`input[name="maquinas[${i}][variables][${j}][valor_minimo]"]`);
            const inputMax = $(`input[name="maquinas[${i}][variables][${j}][valor_maximo]"]`);
            
            const standardVariableId = selectVariable.val();
            const minValue = inputMin.val();
            const maxValue = inputMax.val();
            
            // Verificar que todos los campos requeridos estén llenos
            if (!standardVariableId || standardVariableId === '' || 
                !minValue || minValue === '' || minValue === null ||
                !maxValue || maxValue === '' || maxValue === null) {
                e.preventDefault();
                alert(`La variable #${j + 1} de la máquina "${maquinasSeleccionadas[i].name}" está incompleta. Por favor complete todos los campos (Variable, Valor Mínimo y Valor Máximo).`);
                // Resaltar el campo que falta
                if (!standardVariableId || standardVariableId === '') {
                    selectVariable.focus().addClass('is-invalid');
                } else if (!minValue || minValue === '') {
                    inputMin.focus().addClass('is-invalid');
                } else if (!maxValue || maxValue === '') {
                    inputMax.focus().addClass('is-invalid');
                }
                return false;
            }
            
            // Validar que min sea menor que max
            if (parseFloat(minValue) >= parseFloat(maxValue)) {
                e.preventDefault();
                alert(`En la máquina "${maquinasSeleccionadas[i].name}", el valor mínimo debe ser menor que el valor máximo`);
                inputMin.focus().addClass('is-invalid');
                inputMax.addClass('is-invalid');
                return false;
            }
        }
    }
    
    return true;
});
</script>
@endpush
