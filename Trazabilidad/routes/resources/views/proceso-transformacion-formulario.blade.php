@extends('layouts.app')

@section('page_title', 'Completar Formulario de Máquina')

@section('content')
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Formulario: {{ $processMachine->nombre }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('proceso-transformacion', $batch->lote_id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(!$canAccess)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ $errorMessage }}
                </div>
                @else
                <!-- Información de la Máquina -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Lote</h5>
                        <p><strong>Lote:</strong> #{{ $batch->codigo_lote ?? $batch->lote_id }}</p>
                        <p><strong>Nombre:</strong> {{ $batch->nombre ?? 'Sin nombre' }}</p>
                        <p><strong>Cliente:</strong> {{ $batch->order->customer->razon_social ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Información de la Máquina</h5>
                        <p><strong>Paso:</strong> {{ $processMachine->orden_paso }}</p>
                        <p><strong>Nombre:</strong> {{ $processMachine->nombre }}</p>
                        <p><strong>Proceso:</strong> {{ $processMachine->process->nombre ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($processMachine->machine && $processMachine->machine->imagen_url)
                <div class="text-center mb-4">
                    <img src="{{ $processMachine->machine->imagen_url }}" 
                         alt="{{ $processMachine->nombre }}" 
                         class="img-fluid" 
                         style="max-height: 200px; object-fit: contain;">
                </div>
                @endif

                <!-- Formulario de Variables -->
                <form method="POST" action="{{ route('proceso-transformacion.registrar', [$batch->lote_id, $processMachine->proceso_maquina_id]) }}" id="formularioMaquina">
                    @csrf
                    
                    <h5 class="mb-3">Variables Estándar</h5>
                    
                    @if($processMachine->variables->isEmpty())
                    <div class="alert alert-warning">
                        Esta máquina no tiene variables configuradas.
                    </div>
                    @else
                    <div class="row">
                        @foreach($processMachine->variables as $variable)
                        @php
                            $varName = $variable->standardVariable->codigo ?? $variable->standardVariable->nombre;
                            $oldValue = old('entered_variables.' . $varName);
                            if (!$oldValue && $record && isset($record->variables_ingresadas[$varName])) {
                                $oldValue = $record->variables_ingresadas[$varName];
                            }
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="variable_{{ $variable->variable_id }}">
                                    {{ $variable->standardVariable->nombre ?? 'N/A' }}
                                    @if($variable->standardVariable->unidad)
                                        <small class="text-muted">({{ $variable->standardVariable->unidad }})</small>
                                    @endif
                                    @if($variable->obligatorio)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control @error('entered_variables.' . $varName) is-invalid @enderror" 
                                           id="variable_{{ $variable->variable_id }}"
                                           name="entered_variables[{{ $varName }}]" 
                                           value="{{ $oldValue }}"
                                           min="{{ $variable->valor_minimo }}"
                                           max="{{ $variable->valor_maximo }}"
                                           @if($variable->obligatorio) required @endif
                                           placeholder="Rango: {{ $variable->valor_minimo }} - {{ $variable->valor_maximo }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            Min: {{ $variable->valor_minimo }} | Max: {{ $variable->valor_maximo }}
                                        </span>
                                    </div>
                                </div>
                                @error('entered_variables.' . $varName)
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    @if($variable->valor_objetivo)
                                        Valor objetivo: {{ $variable->valor_objetivo }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observations">Observaciones (opcional)</label>
                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                  id="observations" 
                                  name="observations" 
                                  rows="3" 
                                  placeholder="Observaciones sobre este proceso...">{{ old('observations', $record->observaciones ?? '') }}</textarea>
                        @error('observations')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group text-right">
                        <a href="{{ route('proceso-transformacion', $batch->lote_id) }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Guardar Formulario
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Validación en tiempo real
document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('blur', function() {
        const min = parseFloat(this.getAttribute('min'));
        const max = parseFloat(this.getAttribute('max'));
        const value = parseFloat(this.value);
        
        if (this.value && !isNaN(value)) {
            if (value < min || value > max) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });
});
</script>
@endpush

