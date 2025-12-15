@extends('layouts.app')

@section('page_title', 'Certificar Lote')

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

<!-- Estadísticas de Certificación -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $lotes->count() }}</h3>
                <p>Lotes Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $lotes->where('latestFinalEvaluation', '!=', null)->filter(function($l) { return !str_contains(strtolower($l->latestFinalEvaluation->reason ?? ''), 'falló'); })->count() }}</h3>
                <p>Certificados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $lotes->where('start_time', '!=', null)->where('end_time', null)->count() }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $lotes->filter(function($l) { return $l->latestFinalEvaluation && str_contains(strtolower($l->latestFinalEvaluation->reason ?? ''), 'falló'); })->count() }}</h3>
                <p>No Certificados</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Lotes para Certificar -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Lotes Pendientes de Certificación
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID Lote</th>
                        <th>Nombre</th>
                        <th>Cliente</th>
                        <th>Fecha Producción</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $lote)
                    <tr>
                        <td>#{{ $lote->codigo_lote ?? $lote->lote_id }}</td>
                        <td>{{ $lote->nombre ?? 'Sin nombre' }}</td>
                        <td>{{ $lote->order->customer->razon_social ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($lote->fecha_creacion)->format('d/m/Y') }}</td>
                        <td>
                            @if($lote->latestFinalEvaluation)
                                @if(str_contains(strtolower($lote->latestFinalEvaluation->reason ?? ''), 'falló'))
                                    <span class="badge badge-danger">No Certificado</span>
                                @else
                                    <span class="badge badge-success">Certificado</span>
                                @endif
                            @elseif($lote->processMachineRecords->isNotEmpty())
                                <span class="badge badge-warning">Listo para Certificar</span>
                            @else
                                <span class="badge badge-info">Pendiente - Sin Proceso</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button onclick="verLote({{ $lote->lote_id }})" class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(!$lote->latestFinalEvaluation)
                                @if($lote->processMachineRecords->isEmpty())
                                    <a href="{{ route('proceso-transformacion', $lote->lote_id) }}" class="btn btn-sm btn-warning" title="Ir a Proceso de Transformación">
                                        <i class="fas fa-cogs"></i> Proceso
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('certificar-lote.finalizar', $lote->lote_id) }}" style="display: inline;" id="formCertificarLote{{ $lote->lote_id }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success" title="Certificar" data-toggle="modal" data-target="#modalConfirmarCertificacion{{ $lote->lote_id }}">
                                            <i class="fas fa-check"></i> Certificar
                                        </button>
                                    </form>
                                    
                                    <!-- Modal de Confirmación para Certificar Lote -->
                                    <div class="modal fade" id="modalConfirmarCertificacion{{ $lote->lote_id }}" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarCertificacionLabel{{ $lote->lote_id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="modalConfirmarCertificacionLabel{{ $lote->lote_id }}">
                                                        <i class="fas fa-check-circle mr-2"></i>Confirmar Certificación
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-center mb-3">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                        <h5>¿Desea certificar este lote?</h5>
                                                        <p class="text-muted mt-3">
                                                            Esta acción certificará el lote <strong>{{ $lote->codigo_lote ?? $lote->lote_id }}</strong>. 
                                                            Una vez certificado, el lote estará disponible para almacenamiento.
                                                        </p>
                                                        <div class="alert alert-info mt-3">
                                                            <strong>Lote:</strong> {{ $lote->codigo_lote ?? $lote->lote_id }}<br>
                                                            <strong>Nombre:</strong> {{ $lote->nombre ?? 'Sin nombre' }}<br>
                                                            @if($lote->order)
                                                                <strong>Pedido:</strong> {{ $lote->order->numero_pedido ?? 'N/A' }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        <i class="fas fa-times mr-1"></i> Cancelar
                                                    </button>
                                                    <button type="button" class="btn btn-success btnConfirmarCertificacion" data-form-id="formCertificarLote{{ $lote->lote_id }}">
                                                        <i class="fas fa-check-circle mr-1"></i> Sí, Certificar Lote
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay lotes pendientes de certificación</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ver Detalle de Lote -->
<div class="modal fade" id="verLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Lote
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verLoteContent">
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

<!-- Modal para Certificar Lote -->
<div class="modal fade" id="modalCertificarLote" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Certificar Lote</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idLote">ID del Lote</label>
                                <input type="text" class="form-control" id="idLote" value="#L001" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaCertificacion">Fecha de Certificación</label>
                                <input type="date" class="form-control" id="fechaCertificacion">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones sobre la certificación..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="certificador">Certificador</label>
                        <select class="form-control" id="certificador">
                            <option>Seleccionar Certificador</option>
                            <option>Ana García - Supervisor</option>
                            <option>Carlos López - Inspector</option>
                            <option>María Rodríguez - Auditor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cumpleEstándares">
                            <label class="form-check-label" for="cumpleEstándares">
                                El lote cumple con todos los estándares de calidad
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success">Certificar Lote</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const gestionLotesBaseUrl = '{{ url("gestion-lotes") }}';

function verLote(id) {
    $('#verLoteModal').modal('show');
    $('#verLoteContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    fetch(`${gestionLotesBaseUrl}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            let rawMaterialsHtml = '';
            if (data.raw_materials && data.raw_materials.length > 0) {
                rawMaterialsHtml = '<ul class="list-unstyled">';
                data.raw_materials.forEach(function(rm) {
                    rawMaterialsHtml += `<li class="mb-2">
                        <strong>${rm.material_name}</strong> - 
                        Planificada: ${parseFloat(rm.planned_quantity).toFixed(2)} ${rm.unit}, 
                        Usada: ${parseFloat(rm.used_quantity).toFixed(2)} ${rm.unit}<br>
                        <small class="text-muted">Proveedor: ${rm.supplier}</small>
                    </li>`;
                });
                rawMaterialsHtml += '</ul>';
            } else {
                rawMaterialsHtml = '<p class="text-muted">No hay materias primas registradas</p>';
            }
            
            let evaluationHtml = '';
            if (data.evaluation) {
                evaluationHtml = `
                    <tr>
                        <th>Razón de Evaluación</th>
                        <td>${data.evaluation.reason || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Observaciones</th>
                        <td>${data.evaluation.observations || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Fecha de Evaluación</th>
                        <td>${data.evaluation.evaluation_date ? new Date(data.evaluation.evaluation_date).toLocaleDateString('es-ES') : 'N/A'}</td>
                    </tr>
                `;
            } else {
                evaluationHtml = '<tr><td colspan="2" class="text-center text-muted">Aún no evaluado</td></tr>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID Lote</th>
                                <td>#${data.batch_id}</td>
                            </tr>
                            <tr>
                                <th>Código de Lote</th>
                                <td><span class="badge badge-primary">${data.batch_code || 'N/A'}</span></td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name || 'Sin nombre'}</td>
                            </tr>
                            <tr>
                                <th>Cliente</th>
                                <td>${data.customer_name || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Pedido</th>
                                <td>${data.order_number || 'N/A'} - ${data.order_name || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación</th>
                                <td>${data.creation_date ? new Date(data.creation_date).toLocaleDateString('es-ES') : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Hora de Inicio</th>
                                <td>${data.start_time || 'No iniciado'}</td>
                            </tr>
                            <tr>
                                <th>Hora de Fin</th>
                                <td>${data.end_time || 'No finalizado'}</td>
                            </tr>
                            <tr>
                                <th>Cantidad Objetivo</th>
                                <td>${data.target_quantity ? parseFloat(data.target_quantity).toFixed(2) : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Cantidad Producida</th>
                                <td>${data.produced_quantity ? parseFloat(data.produced_quantity).toFixed(2) : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Observaciones</th>
                                <td>${data.observations || 'Sin observaciones'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5><i class="fas fa-boxes mr-2"></i>Materias Primas</h5>
                        ${rawMaterialsHtml}
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5><i class="fas fa-clipboard-check mr-2"></i>Evaluación Final</h5>
                        <table class="table table-bordered">
                            ${evaluationHtml}
                        </table>
                    </div>
                </div>
            `;
            $('#verLoteContent').html(content);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#verLoteContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los datos del lote
                </div>
            `);
        });
}

$(document).ready(function() {
    // Cuando se confirma en el modal, enviar el formulario correspondiente
    $('.btnConfirmarCertificacion').on('click', function() {
        var formId = $(this).data('form-id');
        $('#' + formId).submit();
    });
});
</script>
@endpush

@endsection
