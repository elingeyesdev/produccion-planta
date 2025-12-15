@extends('layouts.app')

@section('page_title', 'Código QR del Certificado')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-qrcode mr-1"></i>
                    Código QR del Certificado
                </h3>
                <div class="card-tools">
                    <a href="{{ route('certificados') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Certificados
                    </a>
                        </div>
                            </div>
                            <div class="card-body text-center">
                <div class="bg-white flex flex-column justify-content-center align-items-center p-6 rounded-xl shadow max-w-md mx-auto">
                    <h2 class="text-xl font-bold mb-4 text-primary">
                        Código QR del Certificado
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Escanea este código para ver el certificado del lote #{{ $lote->lote_id }}
                    </p>
                    <div id="qrCode" style="min-height: 256px; display: flex; align-items: center; justify-content: center; margin: 20px auto;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Generando QR...</span>
                                </div>
                            </div>
                    <p class="mt-4 text-xs text-gray-400 break-all" id="urlCertificado"></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // URL pública del certificado (accesible desde QR sin autenticación)
    const url = window.location.origin + '/certificado-publico/{{ $lote->lote_id }}';
    const urlElement = document.getElementById('urlCertificado');
    const qrContainer = document.getElementById('qrCode');
    
    if (urlElement) {
        urlElement.textContent = url;
    }
    
    if (!qrContainer) {
        console.error('Contenedor QR no encontrado');
        return;
    }
    
    // Usar API pública de QR code generation (similar al enfoque simple del proyecto antiguo)
    // Esta es una solución confiable que no depende de librerías externas
    const apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' + encodeURIComponent(url);
    
    const img = document.createElement('img');
    img.src = apiUrl;
    img.alt = 'Código QR del Certificado';
    img.style.cssText = 'max-width: 100%; height: auto; border: 2px solid #ddd; padding: 10px; background: white; display: block; margin: 0 auto;';
    
    img.onload = function() {
        qrContainer.innerHTML = '';
        qrContainer.appendChild(img);
    };
    
    img.onerror = function() {
        // Si la API falla, intentar con otra librería
        qrContainer.innerHTML = '<p class="text-muted">Cargando código QR...</p>';
        loadQRCodeLibrary(url, qrContainer);
    };
    
    // Intentar cargar la imagen
    qrContainer.innerHTML = '';
    qrContainer.appendChild(img);
});

// Función de respaldo: cargar librería QRCode
function loadQRCodeLibrary(url, container) {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
    script.onload = function() {
        if (typeof QRCode !== 'undefined') {
            QRCode.toDataURL(url, {
                width: 300,
                margin: 2,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
            }, function (error, urlData) {
        if (error) {
                    container.innerHTML = '<p class="text-danger">Error al generar el código QR. URL: ' + url + '</p>';
                } else {
                    const img = document.createElement('img');
                    img.src = urlData;
                    img.alt = 'Código QR del Certificado';
                    img.style.cssText = 'max-width: 100%; height: auto; border: 2px solid #ddd; padding: 10px; background: white; display: block; margin: 0 auto;';
                    container.innerHTML = '';
                    container.appendChild(img);
}
            });
        } else {
            container.innerHTML = '<p class="text-danger">No se pudo cargar la librería QRCode. URL del certificado: <br><small>' + url + '</small></p>';
        }
    };
    script.onerror = function() {
        container.innerHTML = '<p class="text-danger">Error: No se pudo cargar la librería QRCode. URL del certificado: <br><small>' + url + '</small></p>';
    };
    document.head.appendChild(script);
}
</script>
@endpush

