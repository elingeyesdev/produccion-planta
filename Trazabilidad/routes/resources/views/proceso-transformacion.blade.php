@extends('layouts.app')

@section('page_title', 'Proceso de Transformación')

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
                    <i class="fas fa-cogs mr-1"></i>
                    Proceso de Transformación – Lote #{{ $batch->codigo_lote ?? $batch->lote_id }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('gestion-lotes') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Información del Lote -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> {{ $batch->nombre ?? 'Sin nombre' }}</p>
                        <p><strong>Cliente:</strong> {{ $batch->order->customer->razon_social ?? 'N/A' }}</p>
                        <p><strong>Fecha de Creación:</strong> {{ \Carbon\Carbon::parse($batch->fecha_creacion)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Cantidad Objetivo:</strong> {{ $batch->cantidad_objetivo ?? 'N/A' }}</p>
                        <p><strong>Cantidad Producida:</strong> {{ $batch->cantidad_producida ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Selección de Proceso -->
                @if(!$processId)
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle mr-1"></i> Selecciona un Proceso</h5>
                    <form method="POST" action="{{ route('proceso-transformacion.asignar', $batch->lote_id) }}" class="mt-3">
                        @csrf
                        <div class="form-group">
                            <label for="proceso_id">Proceso:</label>
                            <select name="proceso_id" id="proceso_id" class="form-control" required>
                                <option value="">-- Escoge un proceso --</option>
                                @foreach($procesos as $proceso)
                                <option value="{{ $proceso->proceso_id }}">{{ $proceso->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check mr-1"></i> Asignar Proceso
                        </button>
                    </form>
                </div>
                @else
                <!-- Progreso -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-sm text-muted">
                            Progreso: {{ $totalCompletados }} / {{ $totalMaquinas }} máquinas completadas
                        </span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $totalMaquinas > 0 ? ($totalCompletados / $totalMaquinas * 100) : 0 }}%"
                             aria-valuenow="{{ $totalCompletados }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $totalMaquinas }}">
                            {{ $totalMaquinas > 0 ? round($totalCompletados / $totalMaquinas * 100) : 0 }}%
                        </div>
                    </div>
                </div>

                <!-- Máquinas del Proceso -->
                <div class="row">
                    @foreach($processMachines as $index => $processMachine)
                    @php
                        $completada = isset($formulariosCompletados[$processMachine->proceso_maquina_id]) && $formulariosCompletados[$processMachine->proceso_maquina_id];
                        $bloqueada = $index > 0 && (!isset($formulariosCompletados[$processMachines[$index-1]->proceso_maquina_id]) || !$formulariosCompletados[$processMachines[$index-1]->proceso_maquina_id]);
                    @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 {{ $completada ? 'border-success' : ($bloqueada ? 'border-secondary opacity-50' : 'border-primary') }}">
                            <div class="card-header {{ $completada ? 'bg-success text-white' : ($bloqueada ? 'bg-secondary text-white' : 'bg-primary text-white') }}">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog mr-1"></i>
                                    Paso {{ $processMachine->orden_paso }}: {{ $processMachine->nombre }}
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($processMachine->machine && $processMachine->machine->imagen_url)
                                <img src="{{ $processMachine->machine->imagen_url }}" 
                                     alt="{{ $processMachine->nombre }}" 
                                     class="img-fluid mb-3" 
                                     style="max-height: 150px; object-fit: contain;">
                                @endif
                                
                                <p class="text-muted">{{ $processMachine->descripcion ?? 'Sin descripción' }}</p>
                                
                                @if($completada)
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle mr-1"></i> Completada
                                </div>
                                @elseif($bloqueada)
                                <div class="alert alert-secondary mb-0">
                                    <i class="fas fa-lock mr-1"></i> Bloqueada - Complete la máquina anterior primero
                                </div>
                                @else
                                <a href="{{ route('proceso-transformacion.mostrar-formulario', [$batch->lote_id, $processMachine->proceso_maquina_id]) }}" 
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-edit mr-1"></i> Completar Formulario
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Botón Finalizar Proceso -->
                @if($procesoListo)
                <div class="text-center mt-4">
                    <form method="POST" action="{{ route('certificar-lote.finalizar', $batch->lote_id) }}" id="finalizarProcesoForm">
                        @csrf
                        <div class="form-group">
                            <label for="observaciones">Observaciones (opcional):</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" 
                                      placeholder="Observaciones sobre la certificación..."></textarea>
                        </div>
                        <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#modalConfirmarFinalizacion">
                            <i class="fas fa-check-circle mr-1"></i> Finalizar Proceso
                        </button>
                    </form>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Finalizar Proceso -->
<div class="modal fade" id="modalConfirmarFinalizacion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarFinalizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalConfirmarFinalizacionLabel">
                    <i class="fas fa-check-circle mr-2"></i>Confirmar Finalización y Certificación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>¿Desea finalizar y certificar este proceso?</h5>
                    <p class="text-muted mt-3">
                        Esta acción finalizará el proceso de transformación y certificará el lote. 
                        Una vez certificado, el lote estará disponible para almacenamiento.
                    </p>
                    <div class="alert alert-info mt-3">
                        <strong>Lote:</strong> {{ $batch->codigo_lote ?? $batch->lote_id }}<br>
                        <strong>Nombre:</strong> {{ $batch->nombre ?? 'Sin nombre' }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarFinalizacion">
                    <i class="fas fa-check-circle mr-1"></i> Sí, Finalizar y Certificar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Cuando se confirma en el modal, enviar el formulario
    $('#btnConfirmarFinalizacion').on('click', function() {
        $('#finalizarProcesoForm').submit();
    });
});
</script>
@endpush

