@extends('layouts.app')

@section('page_title', 'Mi Ubicación')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    Configurar Ubicación de la Planta
                </h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Importante:</strong> Esta ubicación será utilizada como punto de recojo fijo para todos los almacenajes. 
                    Esta es la dirección desde donde se recogerán los productos para su envío.
                </div>
                
                <form method="POST" action="{{ route('planta-ubicacion.update') }}" id="ubicacionForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre de la Planta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" 
                                       value="{{ old('nombre', $plantaConfig['nombre']) }}" 
                                       placeholder="Ej: Planta Principal" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="direccion">Dirección de Recojo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                       id="direccion" name="direccion" 
                                       value="{{ old('direccion', $plantaConfig['direccion']) }}" 
                                       placeholder="Ej: Av. Ejemplo 123, Santa Cruz, Bolivia" required>
                                @error('direccion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Dirección completa donde se recogerá el producto</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="latitud">Latitud <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('latitud') is-invalid @enderror" 
                                       id="latitud" name="latitud" 
                                       value="{{ old('latitud', $plantaConfig['latitud']) }}" 
                                       step="0.000001" min="-90" max="90" required>
                                @error('latitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="longitud">Longitud <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('longitud') is-invalid @enderror" 
                                       id="longitud" name="longitud" 
                                       value="{{ old('longitud', $plantaConfig['longitud']) }}" 
                                       step="0.000001" min="-180" max="180" required>
                                @error('longitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Seleccionar Ubicación en el Mapa <span class="text-danger">*</span></label>
                        <div style="position: relative; overflow: hidden; border: 1px solid #ddd; border-radius: 4px; height: 500px; width: 100%;">
                            <div id="map" style="height: 100%; width: 100%; position: relative;"></div>
                        </div>
                        <small class="form-text text-muted mt-2 d-block">Haz clic en el mapa para seleccionar la ubicación exacta de la planta</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Guardar Ubicación
                        </button>
                        <a href="{{ route('almacenaje') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    #map {
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
let map;
let marker;

// Inicializar mapa
function initMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('Elemento del mapa no encontrado');
        return;
    }
    
    // Si el mapa ya existe, removerlo y crear uno nuevo
    if (map) {
        try {
            map.remove();
        } catch(e) {
            console.log('Error removiendo mapa:', e);
        }
        map = null;
        marker = null;
    }
    
    // Obtener coordenadas actuales o usar valores por defecto
    const currentLat = parseFloat(document.getElementById('latitud').value) || -17.8146;
    const currentLng = parseFloat(document.getElementById('longitud').value) || -63.1561;
    
    // Crear mapa
    map = L.map('map', {
        zoomControl: true,
        attributionControl: true,
        preferCanvas: false
    });
    
    // Agregar capa de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Establecer la vista
    map.setView([currentLat, currentLng], 15);
    
    // Invalidar tamaño
    map.invalidateSize(true);
    
    setTimeout(() => {
        if (map) {
            map.invalidateSize(true);
            map.setView(map.getCenter(), map.getZoom());
        }
    }, 100);
    
    // Agregar marcador inicial
    setTimeout(() => {
        if (map) {
            addMarker(currentLat, currentLng);
        }
    }, 200);
    
    // Agregar marcador al hacer clic
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        addMarker(lat, lng);
        document.getElementById('latitud').value = lat.toFixed(6);
        document.getElementById('longitud').value = lng.toFixed(6);
        
        // Intentar obtener dirección usando geocodificación inversa
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
            headers: {
                'User-Agent': 'TrazabilidadApp/1.0'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.address) {
                    const addressParts = [];
                    if (data.address.road) addressParts.push(data.address.road);
                    if (data.address.house_number) addressParts.push(data.address.house_number);
                    if (data.address.suburb) addressParts.push(data.address.suburb);
                    if (data.address.city || data.address.town) addressParts.push(data.address.city || data.address.town);
                    if (data.address.state) addressParts.push(data.address.state);
                    if (data.address.country) addressParts.push(data.address.country);
                    
                    if (addressParts.length > 0) {
                        document.getElementById('direccion').value = addressParts.join(', ');
                    }
                }
            })
            .catch(err => console.log('Error obteniendo dirección:', err));
    });
}

function addMarker(lat, lng) {
    if (!map) {
        console.error('Mapa no inicializado');
        return;
    }
    
    // Remover marcador anterior si existe
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    
    // Crear nuevo marcador arrastrable
    marker = L.marker([lat, lng], {
        draggable: true
    }).addTo(map);
    
    // Actualizar coordenadas al arrastrar
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        document.getElementById('latitud').value = position.lat.toFixed(6);
        document.getElementById('longitud').value = position.lng.toFixed(6);
        
        // Obtener dirección actualizada
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.lat}&lon=${position.lng}`, {
            headers: {
                'User-Agent': 'TrazabilidadApp/1.0'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.address) {
                    const addressParts = [];
                    if (data.address.road) addressParts.push(data.address.road);
                    if (data.address.house_number) addressParts.push(data.address.house_number);
                    if (data.address.suburb) addressParts.push(data.address.suburb);
                    if (data.address.city || data.address.town) addressParts.push(data.address.city || data.address.town);
                    if (data.address.state) addressParts.push(data.address.state);
                    if (data.address.country) addressParts.push(data.address.country);
                    
                    if (addressParts.length > 0) {
                        document.getElementById('direccion').value = addressParts.join(', ');
                    }
                }
            })
            .catch(err => console.log('Error obteniendo dirección:', err));
    });
    
    // Agregar popup
    marker.bindPopup('<strong>Ubicación de la Planta</strong><br>Arrastra el marcador para ajustar la posición').openPopup();
}

// Inicializar mapa cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initMap();
    }, 300);
});

// Redimensionar mapa cuando la ventana cambia de tamaño
$(window).on('resize', function() {
    if (map) {
        setTimeout(function() {
            if (map) {
                map.invalidateSize(true);
            }
        }, 100);
    }
});
</script>
@endpush

