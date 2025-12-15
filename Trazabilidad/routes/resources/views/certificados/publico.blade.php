<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado de Calidad - Lote #{{ $lote->batch_id }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px 0;
        }
        .certificado-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-certificado {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-certificado h1 {
            color: #007bff;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section-title {
            color: #007bff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        .info-item {
            margin-bottom: 8px;
            font-size: 1rem;
        }
        .badge-certificado {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 20px 0;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
        }
        .error-message i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="certificado-container">
            @if(isset($error))
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h2>{{ $error }}</h2>
                    <p>El lote #{{ $lote->batch_id }} aún no ha sido certificado.</p>
                </div>
            @else
                <!-- Encabezado institucional -->
                <header class="header-certificado">
                    <h1>Certificado de Calidad</h1>
                    <p class="text-muted">Sistema de Trazabilidad y Producción</p>
                    <p class="text-muted small">
                        Fecha de evaluación: {{ $lote->latestFinalEvaluation ? \Carbon\Carbon::parse($lote->latestFinalEvaluation->evaluation_date)->format('d/m/Y') : 'N/A' }}
                    </p>
                </header>

                <!-- Información general -->
                <section>
                    <h2 class="section-title">Información del Lote</h2>
                    <div class="info-item"><strong>ID:</strong> {{ $lote->batch_id }}</div>
                    <div class="info-item"><strong>Código:</strong> {{ $lote->batch_code ?? 'N/A' }}</div>
                    <div class="info-item"><strong>Nombre:</strong> {{ $lote->name ?? 'Sin nombre' }}</div>
                    <div class="info-item"><strong>Fecha de creación:</strong> {{ \Carbon\Carbon::parse($lote->creation_date)->format('d/m/Y') }}</div>
                    @if($lote->order && $lote->order->customer)
                    <div class="info-item"><strong>Cliente:</strong> {{ $lote->order->customer->business_name }}</div>
                    @endif
                </section>

                <!-- Materias primas -->
                @if($lote->rawMaterials && $lote->rawMaterials->count() > 0)
                <section>
                    <h2 class="section-title">Materias Primas Utilizadas</h2>
                    <ul class="list-group">
                        @foreach($lote->rawMaterials as $rawMaterial)
                        <li class="list-group-item">
                            <strong>{{ $rawMaterial->rawMaterial->materialBase->name ?? 'N/A' }}</strong> – 
                            {{ $rawMaterial->quantity ?? 'N/A' }} 
                            {{ $rawMaterial->rawMaterial->materialBase->unit ?? '' }}
                        </li>
                        @endforeach
                    </ul>
                </section>
                @endif

                <!-- Proceso por máquinas -->
                @if($lote->processMachineRecords && $lote->processMachineRecords->count() > 0)
                <section>
                    <h2 class="section-title">Proceso de Transformación</h2>
                    <div class="row">
                        @foreach($lote->processMachineRecords->sortBy(function($record) {
                            return $record->processMachine ? $record->processMachine->step_order : 999;
                        }) as $record)
                        @php
                            $cumpleEstandar = $record->meets_standard ?? false;
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card {{ $cumpleEstandar ? 'border-success' : 'border-danger' }}">
                                <div class="card-header {{ $cumpleEstandar ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                    <h5 class="mb-0">
                                        {{ $record->processMachine ? $record->processMachine->step_order : 'N/A' }}. 
                                        {{ $record->processMachine ? $record->processMachine->name : 'N/A' }}
                                        @if($cumpleEstandar)
                                            <span class="badge badge-light ml-2">✓ Éxito</span>
                                        @else
                                            <span class="badge badge-light ml-2">✗ Error</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($record->entered_variables && is_array($record->entered_variables))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($record->entered_variables as $key => $value)
                                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted mb-0">Sin variables registradas</p>
                                    @endif
                                    @if($record->observations)
                                    <p class="text-muted mt-2 mb-0"><small><strong>Observaciones:</strong> {{ $record->observations }}</small></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Resultado final -->
                <section class="text-center mt-5">
                    @php
                        $estado = str_contains(strtolower($lote->latestFinalEvaluation->reason ?? ''), 'falló') ? 'No Certificado' : 'Certificado';
                        $motivo = $lote->latestFinalEvaluation->reason ?? 'N/A';
                    @endphp
                    <div class="badge-certificado {{ $estado === 'Certificado' ? 'badge-success' : 'badge-danger' }}">
                        @if($estado === 'Certificado')
                            ✓ {{ $estado }}
                        @else
                            ✗ {{ $estado }}
                        @endif
                    </div>
                    <p class="mt-3">{{ $motivo }}</p>
                    @if($lote->latestFinalEvaluation && $lote->latestFinalEvaluation->observations)
                    <p class="mt-2 text-muted"><strong>Observaciones:</strong> {{ $lote->latestFinalEvaluation->observations }}</p>
                    @endif
                    @if($lote->latestFinalEvaluation && $lote->latestFinalEvaluation->inspector)
                    <p class="mt-3 text-muted small">
                        <strong>Inspector:</strong> {{ $lote->latestFinalEvaluation->inspector->first_name }} {{ $lote->latestFinalEvaluation->inspector->last_name }}
                    </p>
                    @endif
                </section>
            @endif
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

