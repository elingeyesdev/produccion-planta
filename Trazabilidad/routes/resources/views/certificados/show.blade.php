@extends('layouts.app')

@section('page_title', 'Certificado de Calidad')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-certificate mr-1"></i>
                    Certificado de Calidad
                </h3>
                <div class="card-tools">
                    <a href="{{ route('certificados') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Certificados
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="descargarPDF()">
                        <i class="fas fa-download mr-1"></i> Descargar PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="certificado-pdf" class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-8 relative">
                    <!-- Encabezado institucional -->
                    <header class="text-center border-b pb-4 mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 uppercase">
                            Certificado de Calidad
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Sistema de Trazabilidad y Producción
                        </p>
                        <p class="text-xs text-gray-400">
                            Fecha de evaluación: {{ $lote->latestFinalEvaluation && $lote->latestFinalEvaluation->fecha_evaluacion ? \Carbon\Carbon::parse($lote->latestFinalEvaluation->fecha_evaluacion)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </header>

                    <!-- Información general -->
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-2">
                            Información del Lote
                        </h2>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p><strong>ID:</strong> {{ $lote->lote_id }}</p>
                            <p><strong>Código:</strong> {{ $lote->codigo_lote ?? 'N/A' }}</p>
                            <p><strong>Nombre:</strong> {{ $lote->nombre ?? 'Sin nombre' }}</p>
                            <p><strong>Fecha de creación:</strong> {{ $lote->fecha_creacion ? \Carbon\Carbon::parse($lote->fecha_creacion)->format('d/m/Y') : 'N/A' }}</p>
                            @if($lote->order && $lote->order->customer)
                            <p><strong>Cliente:</strong> {{ $lote->order->customer->razon_social }}</p>
                            @endif
                            @if($lote->order)
                            <p><strong>Pedido:</strong> {{ $lote->order->nombre }} ({{ $lote->order->numero_pedido }})</p>
                            @endif
                        </div>
                    </section>

                    <!-- Productos del Pedido -->
                    @if($lote->order && $lote->order->orderProducts && $lote->order->orderProducts->count() > 0)
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-2">
                            Productos del Pedido
                        </h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lote->order->orderProducts as $orderProduct)
                                    <tr>
                                        <td>{{ $orderProduct->product->nombre ?? 'N/A' }}</td>
                                        <td>{{ number_format($orderProduct->cantidad, 2) }} {{ $orderProduct->product->unit->codigo ?? '' }}</td>
                                        <td>${{ number_format($orderProduct->precio / $orderProduct->cantidad, 2) }}</td>
                                        <td>${{ number_format($orderProduct->precio, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                    @endif

                    <!-- Materias primas -->
                    @if($lote->rawMaterials && $lote->rawMaterials->count() > 0)
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-2">
                            Materias Primas Utilizadas
                        </h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Cantidad Planificada</th>
                                        <th>Cantidad Usada</th>
                                        <th>Unidad</th>
                                        <th>Proveedor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lote->rawMaterials as $rawMaterial)
                                    <tr>
                                        <td>{{ $rawMaterial->rawMaterial->materialBase->nombre ?? 'N/A' }}</td>
                                        <td>{{ number_format($rawMaterial->cantidad_planificada ?? 0, 2) }}</td>
                                        <td>{{ number_format($rawMaterial->cantidad_usada ?? 0, 2) }}</td>
                                        <td>{{ $rawMaterial->rawMaterial->materialBase->unit->codigo ?? 'N/A' }}</td>
                                        <td>{{ $rawMaterial->rawMaterial->supplier->razon_social ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                    @endif

                    <!-- Proceso por máquinas -->
                    <section class="mb-6">
                        <h2 class="text-lg font-semibold text-primary mb-4">
                            Proceso de Transformación
                            @if($lote->processMachineRecords->isNotEmpty() && $lote->processMachineRecords->first()->processMachine && $lote->processMachineRecords->first()->processMachine->process)
                            <span class="text-muted text-sm">({{ $lote->processMachineRecords->first()->processMachine->process->nombre }})</span>
                            @endif
                        </h2>
                        <div class="row">
                            @foreach($lote->processMachineRecords->sortBy(function($record) {
                                return $record->processMachine ? $record->processMachine->orden_paso : 999;
                            }) as $record)
                            @php
                                $cumpleEstandar = $record->cumple_estandar ?? false;
                                $processMachine = $record->processMachine;
                                $variables = $record->variables_ingresadas ?? [];
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card {{ $cumpleEstandar ? 'border-success' : 'border-danger' }}">
                                    <div class="card-header {{ $cumpleEstandar ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        <h3 class="font-semibold mb-0">
                                            Paso {{ $processMachine ? $processMachine->orden_paso : 'N/A' }}. 
                                            {{ $processMachine ? $processMachine->nombre : 'N/A' }}
                                            @if($cumpleEstandar)
                                                <span class="badge badge-light ml-2">✓ Éxito</span>
                                            @else
                                                <span class="badge badge-light ml-2">✗ Error</span>
                                            @endif
                                        </h3>
                                        @if($processMachine && $processMachine->machine)
                                        <small class="d-block mt-1">Máquina: {{ $processMachine->machine->nombre }}</small>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if($processMachine && $processMachine->variables && $processMachine->variables->count() > 0)
                                        <h6 class="font-weight-bold mb-2">Variables Registradas:</h6>
                                        <ul class="list-unstyled mb-0 text-sm">
                                            @foreach($processMachine->variables as $variable)
                                            @php
                                                $varName = $variable->standardVariable->codigo ?? $variable->standardVariable->nombre;
                                                $enteredValue = $variables[$varName] ?? null;
                                                $unit = $variable->standardVariable->unidad ?? '';
                                            @endphp
                                            <li class="mb-1">
                                                <strong>{{ $variable->standardVariable->nombre ?? 'N/A' }}</strong>
                                                @if($unit): <span class="text-muted">({{ $unit }})</span>@endif
                                                <br>
                                                <span class="ml-3">
                                                    Valor: <strong>{{ $enteredValue ?? 'N/A' }}</strong>
                                                    @if($enteredValue !== null)
                                                        | Rango: {{ $variable->valor_minimo }} - {{ $variable->valor_maximo }}
                                                        @if($enteredValue < $variable->valor_minimo || $enteredValue > $variable->valor_maximo)
                                                            <span class="badge badge-danger">Fuera de rango</span>
                                                        @else
                                                            <span class="badge badge-success">OK</span>
                                                        @endif
                                                    @endif
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @elseif(!empty($variables))
                                        <h6 class="font-weight-bold mb-2">Variables Registradas:</h6>
                                        <ul class="list-unstyled mb-0 text-sm">
                                            @foreach($variables as $key => $value)
                                            <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <p class="text-muted mb-0">Sin variables registradas</p>
                                        @endif
                                        @if($record->operator)
                                        <p class="text-muted mt-2 mb-0"><small><strong>Operador:</strong> {{ $record->operator->nombre }} {{ $record->operator->apellido }}</small></p>
                                        @endif
                                        @if($record->fecha_registro)
                                        <p class="text-muted mb-0"><small><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($record->fecha_registro)->format('d/m/Y H:i') }}</small></p>
                                        @endif
                                        @if($record->observaciones)
                                        <p class="text-muted mt-2 mb-0"><small><strong>Observaciones:</strong> {{ $record->observaciones }}</small></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>

                    <!-- Resultado final -->
                    <section class="text-center mt-10">
                        @php
                            $estado = str_contains(strtolower($lote->latestFinalEvaluation->razon ?? ''), 'falló') ? 'No Certificado' : 'Certificado';
                            $motivo = $lote->latestFinalEvaluation->razon ?? 'N/A';
                        @endphp
                        <div class="inline-block px-6 py-3 rounded-full font-semibold text-white {{ $estado === 'Certificado' ? 'bg-success' : 'bg-danger' }} shadow">
                            @if($estado === 'Certificado')
                                ✓ {{ $estado }}
                            @else
                                ✗ {{ $estado }}
                            @endif
                        </div>
                        <p class="mt-3 text-sm text-gray-600">{{ $motivo }}</p>
                        @if($lote->latestFinalEvaluation->inspector)
                        <p class="mt-2 text-sm text-gray-500"><strong>Inspector:</strong> {{ $lote->latestFinalEvaluation->inspector->nombre }} {{ $lote->latestFinalEvaluation->inspector->apellido }}</p>
                        @endif
                        @if($lote->latestFinalEvaluation->observaciones)
                        <p class="mt-2 text-sm text-gray-500"><strong>Observaciones:</strong> {{ $lote->latestFinalEvaluation->observaciones }}</p>
                        @endif
                    </section>

                    <!-- Sello visual (simulado) -->
                    <div class="absolute right-6 bottom-6 opacity-20 rotate-[-15deg]">
                        <p class="text-5xl font-extrabold text-primary">CERTIFICADO</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function descargarPDF() {
    const elemento = document.getElementById("certificado-pdf");

    html2canvas(elemento, {
        scale: 2,
        useCORS: true,
        scrollX: 0,
        scrollY: 0,
        allowTaint: false,
    }).then(function(canvas) {
        const imgData = canvas.toDataURL("image/jpeg", 1.0);
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF("p", "mm", "a4");

        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const imgWidth = pageWidth;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        let position = 0;

        if (imgHeight < pageHeight) {
            pdf.addImage(imgData, "JPEG", 0, 0, imgWidth, imgHeight);
        } else {
            let heightLeft = imgHeight;
            while (heightLeft > 0) {
                pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                position -= pageHeight;
                if (heightLeft > 0) pdf.addPage();
            }
        }

        pdf.save(`certificado-lote-{{ $lote->lote_id }}.pdf`);
    }).catch(function(err) {
        console.error("Error al generar PDF:", err);
        alert("Error al generar el certificado. Intenta nuevamente.");
    });
}
</script>
@endpush

