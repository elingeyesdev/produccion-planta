@extends('layouts.app')

@section('page_title', 'Rutas en Tiempo Real')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-route mr-1"></i>
                        Rutas en Tiempo Real
                    </h3>
                    <div>
                        <span id="ultimo-update" class="badge badge-secondary mr-2">Última actualización: --</span>
                        <span id="estado-conexion" class="badge badge-success"><i class="fas fa-circle"></i> Conectado</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Envíos Activos -->
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
                                <h3 class="card-title text-white mb-0"><i class="fas fa-list"></i> Estado de Envíos</h3>
                                <button class="btn btn-sm btn-light" onclick="actualizarEnvios()" title="Actualizar ahora">
                                    <i class="fas fa-sync-alt" id="btn-sync-icon"></i>
                                </button>
                            </div>
                            <div class="card-body" id="lista-envios" style="max-height: 600px; overflow-y: auto;">
                                <!-- Se carga dinámicamente -->
                                <div class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                    <p class="mt-2">Cargando envíos...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-gradient-primary">
                                <h3 class="card-title text-white"><i class="fas fa-map"></i> Mapa de Rutas en Tiempo Real</h3>
                            </div>
                            <div class="card-body">
                                <div id="info-panel" class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle"></i> Los envíos en tránsito se mostrarán automáticamente cuando el transportista inicie la ruta desde la app
                                </div>
                                <div id="map" style="height: 500px; border-radius: 8px;"></div>
                            </div>
                        </div>

                        <!-- Panel de Control -->
                        <div class="card shadow mt-3" id="control-panel" style="display: none;">
                            <div class="card-header bg-gradient-success">
                                <h3 class="card-title text-white"><i class="fas fa-truck-moving"></i> Seguimiento Activo</h3>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5>Envío: <span id="envio-codigo">-</span></h5>
                                        <p class="mb-0">Estado: <span id="envio-estado" class="badge badge-info">-</span></p>
                                        <p class="mb-0 mt-2"><small>Progreso: <span id="progreso-texto">0%</span></small></p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button class="btn btn-secondary" onclick="cerrarSeguimiento()">
                                            <i class="fas fa-times"></i> Cerrar
                                        </button>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 25px;">
                                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .envio-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    .envio-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }
    .envio-card.activo {
        border: 3px solid #ffc107 !important;
    }
    .leaflet-container {
        font-family: inherit;
    }
    .nuevo-envio {
        animation: highlight 2s ease-out;
    }
    @keyframes highlight {
        0% { background-color: #ffeb3b; }
        100% { background-color: inherit; }
    }
</style>
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script>
// Configuración
const PLANTA_COORDS = [-17.783333, -63.182778];
const INTERVALO_ACTUALIZACION = 10000; // 10 segundos como backup (WebSocket es principal)
const SOCKET_URL = 'http://192.168.0.129:3001/tracking'; // WebSocket server (Node.js)
const PLANTA_CRUDS_API_URL = '{{ config("services.plantacruds.api_url", "http://localhost:8001/api") }}'; // API de plantaCruds

// Variables globales
let map;
let marcadores = {};
let rutasPolylines = {};
let envioSeleccionado = null;
let intervaloActualizacion = null;
let ultimosEnviosIds = new Set();
let seguimientoCache = {}; // Cache de puntos de seguimiento
let indiceAnimacion = {}; // Índice actual de animación por envío
let socket = null; // WebSocket connection
let rutasCompletas = {}; // Rutas completas recibidas por WebSocket
let rutasOSRM = {}; // Cache de rutas OSRM
let posicionesWebSocket = {}; // Posiciones en tiempo real del WebSocket
let ultimaActualizacionWS = {}; // Timestamp de última actualización por WebSocket
let ultimoProgresoWS = {}; // Último progreso recibido por WebSocket (para evitar saltos hacia atrás)

// Obtener ruta real usando OSRM (Open Source Routing Machine) - API gratuita
async function obtenerRutaOSRM(origen, destino) {
    const cacheKey = `${origen[0]},${origen[1]}-${destino[0]},${destino[1]}`;
    
    // Usar cache si existe
    if (rutasOSRM[cacheKey]) {
        return rutasOSRM[cacheKey];
    }
    
    try {
        // OSRM espera coordenadas en formato lng,lat (inverso a leaflet)
        const url = `https://router.project-osrm.org/route/v1/driving/${origen[1]},${origen[0]};${destino[1]},${destino[0]}?overview=full&geometries=geojson&steps=true`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
            // Convertir coordenadas GeoJSON [lng, lat] a Leaflet [lat, lng]
            const coordinates = data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
            rutasOSRM[cacheKey] = coordinates;
            console.log(`🛣️ Ruta OSRM obtenida: ${coordinates.length} puntos`);
            return coordinates;
        }
    } catch (error) {
        console.warn('⚠️ Error obteniendo ruta OSRM:', error);
    }
    
    // Fallback: línea recta
    return [origen, destino];
}

// Iconos personalizados
const iconos = {
    planta: L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    }),
    destino: L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    }),
    vehiculo: L.divIcon({
        html: '<div style="background: #2196F3; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-truck" style="color: white; font-size: 14px;"></i></div>',
        className: 'custom-truck-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    })
};

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    inicializarMapa();
    inicializarWebSocket();
    actualizarEnvios();
    iniciarActualizacionAutomatica();
});

// Inicializar WebSocket para recibir actualizaciones en tiempo real
function inicializarWebSocket() {
    try {
        socket = io(SOCKET_URL, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 1000
        });

        socket.on('connect', () => {
            console.log('🔌 WebSocket conectado al servidor de tracking');
            document.getElementById('estado-conexion').className = 'badge badge-success';
            document.getElementById('estado-conexion').innerHTML = '<i class="fas fa-circle"></i> WebSocket Conectado';
        });

        socket.on('disconnect', () => {
            console.log('❌ WebSocket desconectado');
            document.getElementById('estado-conexion').className = 'badge badge-warning';
            document.getElementById('estado-conexion').innerHTML = '<i class="fas fa-exclamation-circle"></i> Reconectando...';
        });

        // Escuchar cuando se inicia una simulación
        socket.on('simulacion-iniciada', async (data) => {
            console.log('🚀 Simulación iniciada recibida:', data);
            const { envioId, rutaPuntos } = data;
            
            // IMPORTANTE: Limpiar todos los datos anteriores de este envío
            posicionesWebSocket[envioId] = [];
            ultimaActualizacionWS[envioId] = Date.now();
            ultimoProgresoWS[envioId] = 0; // Reiniciar progreso
            
            // Limpiar marcadores anteriores
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
                delete marcadores[envioId];
            }
            
            // Guardar la ruta completa de la app móvil
            if (rutaPuntos && rutaPuntos.length > 0) {
                // Convertir a formato Leaflet [lat, lng]
                const rutaLeaflet = rutaPuntos.map(punto => {
                    const lat = punto.latitude || punto.lat;
                    const lng = punto.longitude || punto.lng;
                    return [lat, lng];
                }).filter(p => p[0] && p[1]); // Filtrar puntos inválidos
                
                rutasCompletas[envioId] = rutaLeaflet;
                
                console.log(`📍 Ruta recibida de la app: ${rutaLeaflet.length} puntos`);
                
                // Inicializar con el primer punto
                if (rutaLeaflet.length > 0) {
                    posicionesWebSocket[envioId] = [rutaLeaflet[0]];
                    
                    // Crear marcadores inmediatamente con la ruta de la app
                    const primerPunto = rutaLeaflet[0];
                    const ultimoPunto = rutaLeaflet[rutaLeaflet.length - 1];
                    
                    // Marcador del destino
                    const marcadorDestino = L.marker(ultimoPunto, { icon: iconos.destino })
                        .addTo(map)
                        .bindPopup(`<b>📦 Destino</b><br>Envío ${envioId}`);
                    
                    // Marcador del vehículo
                    const marcadorVehiculo = L.marker(primerPunto, { icon: iconos.vehiculo })
                        .addTo(map)
                        .bindPopup(`<b>🚚 Envío ${envioId}</b><br>Iniciando ruta...`);
                    
                    // Dibujar ruta COMPLETA en azul punteado
                    const lineaRutaCompleta = L.polyline(rutaLeaflet, {
                        color: '#2196F3',
                        weight: 5,
                        opacity: 0.5,
                        dashArray: '10, 10',
                        smoothFactor: 1.0
                    }).addTo(map);
                    
                    // Ruta recorrida (empezando vacía)
                    const lineaRutaRecorrida = L.polyline([primerPunto], {
                        color: '#4CAF50',
                        weight: 6,
                        opacity: 0.9,
                        smoothFactor: 1.0
                    }).addTo(map);
                    
                    marcadores[envioId] = { 
                        vehiculo: marcadorVehiculo, 
                        destino: marcadorDestino,
                        ruta: lineaRutaCompleta,
                        rutaRecorrida: lineaRutaRecorrida
                    };
                    
                    // Ajustar mapa para mostrar la ruta
                    map.fitBounds(L.latLngBounds(rutaLeaflet), { padding: [50, 50] });
                }
            }
            
            // Unirse a la room de este envío para recibir actualizaciones
            socket.emit('join', `envio-${envioId}`);
            
            // Mostrar notificación
            mostrarNotificacion(`🚚 Envío ${envioId} ha iniciado la ruta`);
            
            // Actualizar lista de envíos
            actualizarEnvios();
        });

        // Escuchar actualizaciones de posición en tiempo real
        socket.on('posicion-actualizada', (data) => {
            console.log('📍 Posición actualizada:', data);
            const { envioId, posicion, progreso } = data;
            
            // Actualizar posición del camión en el mapa instantáneamente
            actualizarPosicionCamion(envioId, posicion, progreso);
        });

        // Escuchar cuando un envío se completa
        socket.on('envio-completado', (data) => {
            console.log('✅ Envío completado:', data);
            const { envioId } = data;
            
            mostrarNotificacion(`✅ Envío ${envioId} ha llegado a su destino`);
            
            // Limpiar el marcador
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
                // Mantener el marcador de destino para mostrar que llegó
            }
            
            // Limpiar todos los datos de tracking
            delete posicionesWebSocket[envioId];
            delete ultimaActualizacionWS[envioId];
            delete ultimoProgresoWS[envioId];
            delete rutasCompletas[envioId];
            delete seguimientoCache[envioId];
            
            // Actualizar lista
            actualizarEnvios();
        });

        socket.on('connect_error', (error) => {
            console.error('Error de conexión WebSocket:', error);
        });

    } catch (error) {
        console.error('Error inicializando WebSocket:', error);
    }
}

// Actualizar posición del camión en tiempo real (WebSocket)
function actualizarPosicionCamion(envioId, posicion, progreso) {
    const lat = posicion.latitude || posicion.lat;
    const lng = posicion.longitude || posicion.lng;
    
    if (!lat || !lng) return;
    
    // Protección contra saltos hacia atrás (evitar teletransportes)
    if (ultimoProgresoWS[envioId] !== undefined && progreso < ultimoProgresoWS[envioId] - 0.05) {
        console.warn(`⚠️ Ignorando posición fuera de orden para envío ${envioId}: progreso ${progreso} < ${ultimoProgresoWS[envioId]}`);
        return;
    }
    
    const nuevaPosicion = [lat, lng];
    
    // Inicializar array de posiciones si no existe
    if (!posicionesWebSocket[envioId]) {
        posicionesWebSocket[envioId] = [];
    }
    
    // Evitar duplicados: solo agregar si es diferente a la última posición
    const ultimaPosicion = posicionesWebSocket[envioId][posicionesWebSocket[envioId].length - 1];
    if (!ultimaPosicion || 
        Math.abs(ultimaPosicion[0] - nuevaPosicion[0]) > 0.00001 || 
        Math.abs(ultimaPosicion[1] - nuevaPosicion[1]) > 0.00001) {
        posicionesWebSocket[envioId].push(nuevaPosicion);
    }
    
    ultimaActualizacionWS[envioId] = Date.now();
    ultimoProgresoWS[envioId] = progreso;
    
    // Si existe el marcador, moverlo
    if (marcadores[envioId] && marcadores[envioId].vehiculo) {
        // Mover el camión a la nueva posición
        marcadores[envioId].vehiculo.setLatLng(nuevaPosicion);
        
        // Actualizar la ruta recorrida SOLO con puntos del WebSocket
        if (marcadores[envioId].rutaRecorrida && posicionesWebSocket[envioId].length > 0) {
            marcadores[envioId].rutaRecorrida.setLatLngs(posicionesWebSocket[envioId]);
        }
        
        // Actualizar popup
        marcadores[envioId].vehiculo.setPopupContent(
            `<b>🚚 Envío ${envioId}</b><br>
             Progreso: ${Math.round(progreso * 100)}%<br>
             <small>🔴 En vivo</small>`
        );
    }
    
    // Actualizar barra de progreso si está seleccionado
    if (envioSeleccionado == envioId) {
        const progresoPercent = Math.round(progreso * 100);
        document.getElementById('progress-bar').style.width = progresoPercent + '%';
        document.getElementById('progress-bar').textContent = progresoPercent + '%';
        document.getElementById('progreso-texto').textContent = progresoPercent + '%';
    }
    
    // Actualizar timestamp
    const ahora = new Date();
    document.getElementById('ultimo-update').textContent = 
        'Última actualización: ' + ahora.toLocaleTimeString() + ' (en vivo)';
    
    // Indicar conexión en vivo
    document.getElementById('estado-conexion').className = 'badge badge-danger';
    document.getElementById('estado-conexion').innerHTML = '<i class="fas fa-circle"></i> EN VIVO';
}

// Mostrar notificación
function mostrarNotificacion(mensaje) {
    const container = document.getElementById('lista-envios');
    const notif = document.createElement('div');
    notif.className = 'alert alert-info alert-dismissible fade show';
    notif.innerHTML = `${mensaje} <button type="button" class="close" data-dismiss="alert">&times;</button>`;
    container.insertBefore(notif, container.firstChild);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        if (notif.parentNode) {
            notif.remove();
        }
    }, 5000);
}

function inicializarMapa() {
    map = L.map('map').setView(PLANTA_COORDS, 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);
    
    // Marcador de la planta
    L.marker(PLANTA_COORDS, { icon: iconos.planta })
        .addTo(map)
        .bindPopup('<b>🏭 Planta - Origen</b><br>Santa Cruz de la Sierra');
}

function iniciarActualizacionAutomatica() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    intervaloActualizacion = setInterval(actualizarEnvios, INTERVALO_ACTUALIZACION);
    console.log('✅ Actualización automática iniciada (cada ' + (INTERVALO_ACTUALIZACION/1000) + 's)');
}

async function actualizarEnvios() {
    const btnIcon = document.getElementById('btn-sync-icon');
    if (btnIcon) btnIcon.classList.add('fa-spin');
    
    try {
        // Consumir API de plantaCruds
        const response = await fetch(`${PLANTA_CRUDS_API_URL}/rutas/envios-activos`);
        
        if (!response.ok) throw new Error('Error en respuesta de plantaCruds');
        
        const data = await response.json();
        
        renderizarListaEnvios(data.en_transito || [], data.esperando || []);
        
        // Actualizar mapa con envíos (esperar a que termine)
        await actualizarMapaConEnvios(data.en_transito || []);
        
        // Actualizar timestamp
        const ahora = new Date();
        document.getElementById('ultimo-update').textContent = 
            'Última actualización: ' + ahora.toLocaleTimeString();
        document.getElementById('estado-conexion').className = 'badge badge-success';
        document.getElementById('estado-conexion').innerHTML = '<i class="fas fa-circle"></i> Conectado';
        
    } catch (error) {
        console.error('Error actualizando desde plantaCruds:', error);
        document.getElementById('estado-conexion').className = 'badge badge-danger';
        document.getElementById('estado-conexion').innerHTML = '<i class="fas fa-exclamation-circle"></i> Error conexión';
    } finally {
        if (btnIcon) btnIcon.classList.remove('fa-spin');
    }
}

// Obtener puntos de seguimiento de un envío desde la BD de plantaCruds
async function obtenerSeguimientoEnvio(envioId) {
    try {
        const response = await fetch(`${PLANTA_CRUDS_API_URL}/rutas/seguimiento/${envioId}`);
        if (response.ok) {
            const data = await response.json();
            if (data.data && data.data.length > 0) {
                seguimientoCache[envioId] = data.data;
                console.log(`📍 Seguimiento envío ${envioId}: ${data.data.length} puntos`);
            }
        }
    } catch (error) {
        console.warn(`No se pudo obtener seguimiento de envío ${envioId}:`, error);
    }
}

function renderizarListaEnvios(enTransito, esperando) {
    const container = document.getElementById('lista-envios');
    let html = '';
    
    // Envíos en tránsito
    html += `<h6 class="text-info mt-2"><i class="fas fa-truck-moving"></i> En Tránsito (${enTransito.length})</h6>`;
    
    if (enTransito.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="fas fa-info-circle"></i> No hay envíos en tránsito</div>`;
    } else {
        enTransito.forEach(envio => {
            const esNuevo = !ultimosEnviosIds.has(envio.id);
            const claseNuevo = esNuevo ? 'nuevo-envio' : '';
            ultimosEnviosIds.add(envio.id);
            
            const progreso = calcularProgreso(envio.id, envio.fecha_inicio_transito);
            
            html += `
                <div class="envio-card mb-2 p-3 border rounded bg-info text-white ${claseNuevo} ${envioSeleccionado == envio.id ? 'activo' : ''}" 
                     onclick="seleccionarEnvio(${envio.id}, '${envio.codigo}', ${envio.destino_lat || -17.78}, ${envio.destino_lng || -63.18}, this)">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge badge-warning mb-1">🚚 EN RUTA</span>
                            <p class="mb-1"><strong>${envio.codigo}</strong></p>
                            <p class="mb-1 small">📦 ${envio.almacen_nombre || 'N/A'}</p>
                            <p class="mb-1 small">📍 Destino: ${envio.direccion_completa || 'N/A'}</p>
                            ${envio.transportista_nombre ? `<p class="mb-0 small">👤 ${envio.transportista_nombre}</p>` : ''}
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: ${Math.round(progreso * 100)}%"></div>
                            </div>
                            <small>${Math.round(progreso * 100)}% completado</small>
                        </div>
                        <button class="btn btn-sm btn-light" onclick="event.stopPropagation(); verEnMapa(${envio.id}, '${envio.codigo}', ${envio.destino_lat || -17.78}, ${envio.destino_lng || -63.18})">
                            <i class="fas fa-map-marker-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    }
    
    // Envíos esperando
    html += `<h6 class="text-warning mt-3"><i class="fas fa-clock"></i> Esperando Inicio (${esperando.length})</h6>`;
    
    if (esperando.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="fas fa-check-circle"></i> No hay envíos esperando</div>`;
    } else {
        esperando.forEach(envio => {
            const estadoClass = envio.estado === 'aceptado' ? 'success' : 'secondary';
            html += `
                <div class="envio-card mb-2 p-2 border rounded bg-light" style="opacity: 0.9;">
                    <span class="badge badge-${estadoClass}">${(envio.estado || '').toUpperCase()}</span>
                    <p class="mb-1 mt-1"><strong>${envio.codigo}</strong></p>
                    <p class="mb-0 small text-muted">📦 ${envio.almacen_nombre || 'N/A'}</p>
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Esperando inicio del transportista</small>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
}

let actualizandoMapa = false; // Flag para evitar actualizaciones simultáneas

async function actualizarMapaConEnvios(enviosEnTransito) {
    // Evitar actualizaciones simultáneas
    if (actualizandoMapa) {
        console.log('⏳ Actualización en progreso, saltando...');
        return;
    }
    actualizandoMapa = true;
    
    try {
        // Procesar cada envío
        for (const envio of enviosEnTransito) {
            const envioId = envio.id;
            
            // Si hay actualizaciones recientes del WebSocket (menos de 5 segundos), NO tocar este envío
            if (ultimaActualizacionWS[envioId] && (Date.now() - ultimaActualizacionWS[envioId]) < 5000) {
                console.log(`⏭️ Envío ${envioId} tiene datos WebSocket recientes, saltando polling...`);
                continue;
            }
            
            // Si ya tiene marcadores creados por WebSocket, no recrear
            if (marcadores[envioId] && marcadores[envioId].vehiculo && rutasCompletas[envioId]) {
                console.log(`⏭️ Envío ${envioId} ya tiene ruta de la app móvil, saltando...`);
                continue;
            }
            
            // Si ya tiene marcadores del WebSocket, no recrear
            const tieneDataWebSocket = posicionesWebSocket[envioId] && posicionesWebSocket[envioId].length > 0;
            if (marcadores[envioId] && marcadores[envioId].vehiculo && tieneDataWebSocket) {
                console.log(`⏭️ Envío ${envioId} ya tiene marcador con datos WebSocket, saltando...`);
                continue;
            }
            
            // Limpiar marcadores anteriores de este envío SOLO si vamos a recrearlos
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
            }
            
            const destinoLat = parseFloat(envio.destino_lat) || -17.78;
            const destinoLng = parseFloat(envio.destino_lng) || -63.18;
            const destino = [destinoLat, destinoLng];
            
            // PRIORIDAD: Usar ruta de la app móvil si existe, sino OSRM
            let rutaCompleta;
            if (rutasCompletas[envioId] && rutasCompletas[envioId].length > 0) {
                rutaCompleta = rutasCompletas[envioId];
                console.log(`📍 Usando ruta de la app móvil para envío ${envioId}: ${rutaCompleta.length} puntos`);
            } else {
                rutaCompleta = await obtenerRutaOSRM(PLANTA_COORDS, destino);
                console.log(`📍 Usando ruta OSRM para envío ${envioId}: ${rutaCompleta.length} puntos`);
            }
            
            // Calcular progreso basado en tiempo
            const progreso = calcularProgreso(envioId, envio.fecha_inicio_transito);
            
            // Calcular posición actual del camión basado en el progreso
            const indiceCamion = Math.max(0, Math.min(
                Math.floor(progreso * (rutaCompleta.length - 1)),
                rutaCompleta.length - 1
            ));
            
            // Usar la posición calculada
            let posActual = rutaCompleta[indiceCamion] || PLANTA_COORDS;
            let rutaRecorridaPuntos = rutaCompleta.slice(0, indiceCamion + 1);
            
            // Inicializar posicionesWebSocket con la posición inicial
            if (!posicionesWebSocket[envioId]) {
                posicionesWebSocket[envioId] = [posActual];
            }
            
            // Marcador del destino (almacén)
            const marcadorDestino = L.marker(destino, { icon: iconos.destino })
                .addTo(map)
                .bindPopup(`<b>📦 ${envio.almacen_nombre}</b><br>${envio.direccion_completa || 'Destino del envío'}`);
            
            // Marcador del vehículo (posición actual)
            const marcadorVehiculo = L.marker(posActual, { icon: iconos.vehiculo })
                .addTo(map)
                .bindPopup(`<b>🚚 ${envio.codigo}</b><br>
                            Progreso: ${Math.round(progreso * 100)}%<br>
                            ${envio.transportista_nombre || ''}<br>
                            ${envio.vehiculo_placa ? `Placa: ${envio.vehiculo_placa}` : ''}`);
            
            // Dibujar ruta COMPLETA en azul punteado
            const lineaRutaCompleta = L.polyline(rutaCompleta, {
                color: '#2196F3',
                weight: 5,
                opacity: 0.5,
                dashArray: '10, 10',
                smoothFactor: 1.0
            }).addTo(map);
            
            // Dibujar la parte recorrida en verde sólido
            const lineaRutaRecorrida = L.polyline(rutaRecorridaPuntos, {
                color: '#4CAF50',
                weight: 6,
                opacity: 0.9,
                smoothFactor: 1.0
            }).addTo(map);
            
            marcadores[envioId] = { 
                vehiculo: marcadorVehiculo, 
                destino: marcadorDestino,
                ruta: lineaRutaCompleta,
                rutaRecorrida: lineaRutaRecorrida
            };
            
            // Actualizar barra de progreso si está seleccionado
            if (envioSeleccionado == envioId) {
                document.getElementById('progress-bar').style.width = Math.round(progreso * 100) + '%';
                document.getElementById('progress-bar').textContent = Math.round(progreso * 100) + '%';
                document.getElementById('progreso-texto').textContent = Math.round(progreso * 100) + '%';
            }
        }
        
        // Ajustar vista si hay envíos y no hay uno seleccionado
        if (enviosEnTransito.length > 0 && !envioSeleccionado) {
            const bounds = [PLANTA_COORDS];
            enviosEnTransito.forEach(e => {
                if (e.destino_lat && e.destino_lng) {
                    bounds.push([parseFloat(e.destino_lat), parseFloat(e.destino_lng)]);
                }
            });
            if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    } finally {
        actualizandoMapa = false;
    }
}

function calcularProgreso(envioId, fechaInicio) {
    if (!fechaInicio) return 0;
    
    const inicio = new Date(fechaInicio).getTime();
    const ahora = Date.now();
    const duracionTotal = 60 * 1000; // 1 minuto sincronizado con la app
    const transcurrido = ahora - inicio;
    
    return Math.min(1, Math.max(0, transcurrido / duracionTotal));
}

function seleccionarEnvio(id, codigo, lat, lng, element) {
    envioSeleccionado = id;
    verEnMapa(id, codigo, lat, lng);
    
    // Resaltar en la lista
    document.querySelectorAll('.envio-card').forEach(card => card.classList.remove('activo'));
    if (element) element.classList.add('activo');
}

function verEnMapa(id, codigo, lat, lng) {
    envioSeleccionado = id;
    const destino = [lat, lng];
    
    // LIMPIAR TODAS LAS RUTAS Y MARCADORES EXCEPTO EL SELECCIONADO
    Object.keys(marcadores).forEach(envioId => {
        if (envioId != id) {
            if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
            if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
            if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
            if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
        }
    });
    
    // Asegurar que el envío seleccionado esté visible en el mapa
    if (marcadores[id]) {
        if (marcadores[id].vehiculo && !map.hasLayer(marcadores[id].vehiculo)) {
            marcadores[id].vehiculo.addTo(map);
        }
        if (marcadores[id].destino && !map.hasLayer(marcadores[id].destino)) {
            marcadores[id].destino.addTo(map);
        }
        if (marcadores[id].ruta && !map.hasLayer(marcadores[id].ruta)) {
            marcadores[id].ruta.addTo(map);
        }
        if (marcadores[id].rutaRecorrida && !map.hasLayer(marcadores[id].rutaRecorrida)) {
            marcadores[id].rutaRecorrida.addTo(map);
        }
    }
    
    // Centrar mapa en el camión si existe
    if (marcadores[id] && marcadores[id].vehiculo) {
        const pos = marcadores[id].vehiculo.getLatLng();
        map.setView([pos.lat, pos.lng], 14);
        marcadores[id].vehiculo.openPopup();
    } else {
        map.setView(destino, 14);
    }
    
    // Mostrar panel de control
    document.getElementById('control-panel').style.display = 'block';
    document.getElementById('envio-codigo').textContent = codigo;
    document.getElementById('envio-estado').textContent = 'EN TRÁNSITO';
    document.getElementById('envio-estado').className = 'badge badge-info';
    
    // Actualizar info panel
    document.getElementById('info-panel').innerHTML = 
        `<i class="fas fa-truck"></i> Siguiendo envío <strong>${codigo}</strong> en tiempo real - Actualizando cada 2 segundos`;
    document.getElementById('info-panel').className = 'alert alert-success mb-3';
}

function cerrarSeguimiento() {
    envioSeleccionado = null;
    document.getElementById('control-panel').style.display = 'none';
    document.getElementById('info-panel').innerHTML = 
        '<i class="fas fa-info-circle"></i> Los envíos en tránsito se mostrarán automáticamente';
    document.getElementById('info-panel').className = 'alert alert-info mb-3';
    
    document.querySelectorAll('.envio-card').forEach(card => card.classList.remove('activo'));
    
    map.setView(PLANTA_COORDS, 13);
}

// Limpiar al salir
window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    if (socket) {
        socket.disconnect();
    }
});
</script>
@endpush

