<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\OrderDestination;
use App\Services\AlmacenSyncService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantaCrudsIntegrationService
{
    private string $apiUrl;

    public function __construct()
    {
        // Usar config() en lugar de env() directamente para mejor rendimiento
        $this->apiUrl = config('services.plantacruds.api_url');
        
        // Validar que la URL esté configurada
        if (empty($this->apiUrl) || $this->apiUrl === 'http://localhost:8001/api') {
            Log::warning('PLANTACRUDS_API_URL no está configurada correctamente en .env', [
                'current_url' => $this->apiUrl,
                'env_value' => env('PLANTACRUDS_API_URL'),
            ]);
        }
        
        Log::info('PlantaCrudsIntegrationService inicializado', [
            'api_url' => $this->apiUrl,
        ]);
    }

    /**
     * Send approved order to PlantaCruds for shipping
     * 
     * @param CustomerOrder $order
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return array Array of results, one per destination
     */
    public function sendOrderToShipping(CustomerOrder $order, ?\App\Models\Storage $storage = null): array
    {
        // Load all relations
        $order->load([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product'
        ]);

        $results = [];

        // Verificar que el pedido tenga destinos
        if ($order->destinations->isEmpty()) {
            Log::warning('Pedido sin destinos, no se puede enviar a plantaCruds', [
                'order_id' => $order->pedido_id,
                'order_number' => $order->numero_pedido,
            ]);
            return [
                [
                    'destination_id' => null,
                    'success' => false,
                    'error' => 'El pedido no tiene destinos configurados',
                ]
            ];
        }

        // Create one Envio per destination
        foreach ($order->destinations as $destination) {
            try {
                $envioData = $this->buildEnvioData($order, $destination, $storage);
                
                Log::info('Enviando datos a plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'destination_id' => $destination->destino_id,
                ]);
                
                $response = $this->createEnvio($envioData);

                // Log la respuesta completa para debugging
                Log::info('Response completa de plantaCruds', [
                    'response_keys' => array_keys($response),
                    'response' => $response,
                ]);

                // La respuesta de /pedido-almacen devuelve envio_id y codigo directamente
                $envioId = $response['envio_id'] ?? $response['data']['id'] ?? $response['data']['envio_id'] ?? null;
                $envioCodigo = $response['codigo'] ?? $response['data']['codigo'] ?? null;
                
                // Crear registro de tracking para poder acceder al envío desde Trazabilidad
                if ($envioId) {
                    try {
                        \App\Models\OrderEnvioTracking::updateOrCreate(
                            [
                                'pedido_id' => $order->pedido_id,
                                'destino_id' => $destination->destino_id,
                            ],
                            [
                                'envio_id' => $envioId,
                                'codigo_envio' => $envioCodigo,
                                'estado' => 'success',
                                'datos_respuesta' => $response,
                            ]
                        );
                        
                        Log::info('Tracking creado/actualizado para envío', [
                            'pedido_id' => $order->pedido_id,
                            'destino_id' => $destination->destino_id,
                            'envio_id' => $envioId,
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Error al crear tracking para envío', [
                            'pedido_id' => $order->pedido_id,
                            'destino_id' => $destination->destino_id,
                            'envio_id' => $envioId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $results[] = [
                    'destination_id' => $destination->destino_id,
                    'success' => true,
                    'envio_id' => $envioId,
                    'envio_codigo' => $envioCodigo,
                    'qr_code' => $response['qr_code'] ?? null,
                    'response' => $response,
                ];

                Log::info('Envio created successfully in plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'order_number' => $order->numero_pedido,
                    'destination_id' => $destination->destino_id,
                    'envio_id' => $envioId,
                    'envio_codigo' => $envioCodigo,
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'destination_id' => $destination->destino_id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to create Envio in plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'order_number' => $order->numero_pedido,
                    'destination_id' => $destination->destino_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Build Envio data from order and destination
     * 
     * @param CustomerOrder $order
     * @param OrderDestination $destination
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return array
     */
    private function buildEnvioData(CustomerOrder $order, OrderDestination $destination, ?\App\Models\Storage $storage = null): array
    {
        // PRIORIDAD 1: Obtener información del almacén desde sistema-almacen-PSIII usando almacen_almacen_id
        $almacenAlmacenId = $destination->almacen_almacen_id;
        $almacenInfo = null;
        
        if ($almacenAlmacenId) {
            // Buscar almacén en sistema-almacen-PSIII usando su API
            $almacenInfo = $this->getAlmacenFromAlmacenSystem($almacenAlmacenId);
            
            if ($almacenInfo) {
                Log::info('Almacén obtenido desde sistema-almacen-PSIII', [
                    'almacen_almacen_id' => $almacenAlmacenId,
                    'nombre' => $almacenInfo['nombre']
                ]);
            }
        }
        
        // PRIORIDAD 2: Si no se encontró desde sistema-almacen-PSIII, extraer nombre desde instrucciones de entrega
        if (!$almacenInfo) {
            // Intentar extraer el nombre del almacén desde las instrucciones de entrega
            $nombreAlmacen = null;
            if ($destination->instrucciones_entrega) {
                // Buscar patrón "Entrega en almacén: [nombre]"
                if (preg_match('/Entrega en almacén:\s*(.+?)(?:\n|$)/i', $destination->instrucciones_entrega, $matches)) {
                    $nombreAlmacen = trim($matches[1]);
                } elseif (preg_match('/almacén:\s*(.+?)(?:\n|$)/i', $destination->instrucciones_entrega, $matches)) {
                    $nombreAlmacen = trim($matches[1]);
                }
            }
            
            // Si se encontró el nombre, usarlo directamente (viene desde sistema-almacen-PSIII)
            if ($nombreAlmacen) {
                $almacenInfo = [
                    'id' => null,
                    'nombre' => $nombreAlmacen, // Usar el nombre exacto del almacén desde sistema-almacen-PSIII
                    'latitud' => $destination->latitud,
                    'longitud' => $destination->longitud,
                    'direccion' => $destination->direccion ?? $nombreAlmacen,
                ];
                
                Log::info('Almacén extraído desde instrucciones de entrega', [
                    'nombre' => $nombreAlmacen,
                    'destination_id' => $destination->destino_id
                ]);
            }
        }
        
        // PRIORIDAD 3: Fallback - usar método anterior (buscar en plantaCruds) solo si no hay otra opción
        if (!$almacenInfo) {
            $almacenSyncService = new AlmacenSyncService();
            
            // Prioridad: almacen_destino_id (seleccionado en UI) > buscar por coordenadas > usar default
            if (!empty($destination->almacen_destino_id)) {
                $almacenId = $destination->almacen_destino_id;
                $almacen = $almacenSyncService->findAlmacenById($almacenId);
                if (!$almacen) {
                    $almacenId = $this->findAlmacenForDestination($destination, $almacenSyncService);
                }
            } else {
                $almacenId = $this->findAlmacenForDestination($destination, $almacenSyncService);
            }
            
            // Construir almacenInfo desde plantaCruds
            $almacen = $almacenSyncService->findAlmacenById($almacenId);
            if ($almacen) {
                $almacenInfo = [
                    'id' => $almacen['id'],
                    'nombre' => $almacen['nombre'],
                    'latitud' => $almacen['latitud'] ?? null,
                    'longitud' => $almacen['longitud'] ?? null,
                    'direccion' => $almacen['direccion'] ?? $almacen['nombre'] ?? null,
                ];
            }
        }
        
        // ÚLTIMO RECURSO: Si aún no hay almacén, usar valores del destino
        if (!$almacenInfo) {
            $almacenInfo = [
                'id' => null,
                'nombre' => $destination->direccion ?? 'Almacén no especificado',
                'latitud' => $destination->latitud,
                'longitud' => $destination->longitud,
                'direccion' => $destination->direccion ?? 'Dirección no especificada',
            ];
        }

        // Build products array con todos los campos necesarios
        $productos = [];
        
        if ($destination->destinationProducts->isEmpty()) {
            Log::warning('Destino sin productos, intentando usar productos del pedido', [
                'destination_id' => $destination->destino_id,
                'order_id' => $order->pedido_id,
            ]);
            
            // Si no hay productos en el destino, usar productos del pedido
            foreach ($order->orderProducts as $orderProduct) {
                $product = $orderProduct->product;
                
                $cantidad = (float) $orderProduct->cantidad;
                $pesoUnitario = (float) ($product->peso ?? 0);
                // Usar precio del OrderProduct, si es 0 usar precio_unitario del Product como fallback
                $precioUnitario = (float) ($orderProduct->precio ?? 0);
                if ($precioUnitario == 0 && $product && $product->precio_unitario) {
                    $precioUnitario = (float) $product->precio_unitario;
                }
                $totalPeso = $cantidad * $pesoUnitario;
                $totalPrecio = $cantidad * $precioUnitario;

                $productos[] = [
                    'producto_id' => $product->producto_id ?? null,
                    'producto_nombre' => $product->nombre ?? 'Producto sin nombre',
                    'cantidad' => $cantidad,
                    'peso_unitario' => $pesoUnitario,
                    'precio_unitario' => $precioUnitario,
                    'total_peso' => $totalPeso,
                    'total_precio' => $totalPrecio,
                ];
            }
        } else {
            foreach ($destination->destinationProducts as $destProduct) {
                $orderProduct = $destProduct->orderProduct;
                $product = $orderProduct->product;
                
                $cantidad = (float) $destProduct->cantidad;
                $pesoUnitario = (float) ($product->peso ?? 0);
                // Usar precio del OrderProduct, si es 0 usar precio_unitario del Product como fallback
                $precioUnitario = (float) ($orderProduct->precio ?? 0);
                if ($precioUnitario == 0 && $product && $product->precio_unitario) {
                    $precioUnitario = (float) $product->precio_unitario;
                }
                $totalPeso = $cantidad * $pesoUnitario;
                $totalPrecio = $cantidad * $precioUnitario;

                $productos[] = [
                    'producto_id' => $product->producto_id ?? null, // ID del producto en Trazabilidad
                    'producto_nombre' => $product->nombre ?? 'Producto sin nombre',
                    'cantidad' => $cantidad,
                    'peso_unitario' => $pesoUnitario,
                    'precio_unitario' => $precioUnitario,
                    'total_peso' => $totalPeso,
                    'total_precio' => $totalPrecio,
                ];
            }
        }
        
        // Validar que hay productos
        if (empty($productos)) {
            throw new \Exception("El destino no tiene productos asignados");
        }

        // Calcular totales
        $totalCantidad = array_sum(array_column($productos, 'cantidad'));
        $totalPeso = array_sum(array_column($productos, 'total_peso'));
        $totalPrecio = array_sum(array_column($productos, 'total_precio'));

        // Usar el mismo código del pedido de almacenes (numero_pedido) como código del envío
        // Esto mantiene el mismo código en todos los sistemas
        $codigoEnvio = $order->numero_pedido ?? 'TRZ-' . $order->pedido_id;
        
        // Obtener dirección de la planta desde configuración (punto de recogida fijo)
        $plantaConfig = config('services.planta');
        $origenLat = (float) ($plantaConfig['latitud'] ?? -17.7833);
        $origenLng = (float) ($plantaConfig['longitud'] ?? -63.1821);
        $origenDireccion = $plantaConfig['direccion'] ?? $plantaConfig['nombre'] ?? 'Planta Principal';
        
        return [
            'codigo_origen' => $codigoEnvio,
            'codigo' => $codigoEnvio, // Agregar código para que se use en plantaCruds
            'almacen_destino' => $almacenInfo['nombre'] ?? 'Almacén no especificado',
            'almacen_destino_lat' => $almacenInfo['latitud'] ?? null,
            'almacen_destino_lng' => $almacenInfo['longitud'] ?? null,
            'almacen_destino_direccion' => $almacenInfo['direccion'] ?? $almacenInfo['nombre'] ?? null,
            'origen_lat' => $origenLat, // Latitud de la planta (punto de recogida)
            'origen_lng' => $origenLng, // Longitud de la planta (punto de recogida)
            'origen_direccion' => $origenDireccion, // Dirección de la planta (punto de recogida)
            'solicitante_id' => $order->customer->cliente_id ?? null,
            'solicitante_nombre' => $order->customer->razon_social ?? 'N/A',
            'solicitante_email' => $order->customer->email ?? null,
            'fecha_requerida' => $order->fecha_entrega ?? now()->addDays(3)->format('Y-m-d'),
            'hora_requerida' => '14:00',
            'observaciones' => $this->buildObservations($order, $destination, $storage),
            'total_cantidad' => $totalCantidad,
            'total_peso' => $totalPeso,
            'total_precio' => $totalPrecio,
            'productos' => $productos,
            'origen' => 'trazabilidad',
            'pedido_trazabilidad_id' => $order->pedido_id,
            'numero_pedido_trazabilidad' => $order->numero_pedido,
            'webhook_url' => $this->buildWebhookUrl($order),
        ];
    }

    /**
     * Obtiene información del almacén desde sistema-almacen-PSIII
     * 
     * @param int $almacenId ID del almacén en sistema-almacen-PSIII
     * @return array|null
     */
    private function getAlmacenFromAlmacenSystem(int $almacenId): ?array
    {
        $almacenApiUrl = env('ALMACEN_API_URL', 'http://localhost:8002/api');
        
        try {
            $response = Http::timeout(10)
                ->get("{$almacenApiUrl}/almacenes/{$almacenId}");
            
            if ($response->successful()) {
                $data = $response->json();
                $almacen = $data['data'] ?? $data;
                
                return [
                    'id' => $almacen['id'] ?? $almacenId,
                    'nombre' => $almacen['nombre'] ?? 'Almacén',
                    'latitud' => $almacen['latitud'] ?? null,
                    'longitud' => $almacen['longitud'] ?? null,
                    'direccion' => $almacen['ubicacion'] ?? $almacen['direccion'] ?? $almacen['nombre'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Error al obtener almacén desde sistema-almacen-PSIII', [
                'almacen_id' => $almacenId,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Build observations text
     * 
     * @param CustomerOrder $order
     * @param OrderDestination $destination
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return string
     */
    private function buildObservations(CustomerOrder $order, OrderDestination $destination, ?\App\Models\Storage $storage = null): string
    {
        $obs = "ORIGEN: TRAZABILIDAD\n";
        $obs .= "Pedido: {$order->numero_pedido}\n";
        
        // Agregar información del pedido de almacenes si existe
        if ($order->pedido_almacen_id) {
            $obs .= "pedido_almacen_id: {$order->pedido_almacen_id}\n";
        }
        
        $obs .= "Cliente: {$order->customer->razon_social}\n";

        if ($order->observaciones) {
            $obs .= "Notas: {$order->observaciones}\n";
        }

        // Agregar información de ubicación de recojo si está disponible
        if ($storage && $storage->direccion_recojo) {
            $obs .= "\nUBICACIÓN DE RECOJO:\n";
            $obs .= "Dirección: {$storage->direccion_recojo}\n";
            if ($storage->referencia_recojo) {
                $obs .= "Referencia: {$storage->referencia_recojo}\n";
            }
            if ($storage->latitud_recojo && $storage->longitud_recojo) {
                $obs .= "Coordenadas: {$storage->latitud_recojo}, {$storage->longitud_recojo}\n";
            }
        }

        if ($destination->instrucciones_entrega) {
            $obs .= "\nInstrucciones de entrega: {$destination->instrucciones_entrega}\n";
        }

        if ($destination->nombre_contacto) {
            $obs .= "Contacto: {$destination->nombre_contacto}";
            if ($destination->telefono_contacto) {
                $obs .= " - Tel: {$destination->telefono_contacto}";
            }
            $obs .= "\n";
        }

        if ($destination->direccion) {
            $obs .= "Dirección de entrega: {$destination->direccion}";
            if ($destination->referencia) {
                $obs .= " ({$destination->referencia})";
            }
        }

        return trim($obs);
    }

    /**
     * Construir URL del webhook para notificar a almacenes
     * 
     * @param CustomerOrder $order
     * @return string|null
     */
    private function buildWebhookUrl(CustomerOrder $order): ?string
    {
        if ($order->origen_sistema !== 'almacen' || !$order->pedido_almacen_id) {
            return null;
        }

        $almacenApiUrl = env('ALMACEN_API_URL', 'http://localhost:8002/api');
        return "{$almacenApiUrl}/pedidos/{$order->pedido_almacen_id}/asignacion-envio";
    }

    /**
     * Buscar almacén para un destino
     * 
     * @param OrderDestination $destination
     * @param AlmacenSyncService $almacenSyncService
     * @return int
     * @throws \Exception
     */
    private function findAlmacenForDestination(OrderDestination $destination, AlmacenSyncService $almacenSyncService): int
    {
        // Si el destino tiene coordenadas, buscar el almacén más cercano
        if ($destination->latitud && $destination->longitud) {
            $nearestAlmacen = $almacenSyncService->findNearestAlmacen(
                $destination->latitud,
                $destination->longitud,
                true // Solo almacenes de destino (no plantas)
            );

            if ($nearestAlmacen) {
                Log::info('Almacén encontrado por proximidad', [
                    'almacen_id' => $nearestAlmacen['id'],
                    'almacen_nombre' => $nearestAlmacen['nombre'],
                    'destination_id' => $destination->destino_id,
                ]);
                return $nearestAlmacen['id'];
            }
        }

        // Si no hay coordenadas o no se encontró, buscar por dirección
        if ($destination->direccion) {
            $almacenes = $almacenSyncService->getDestinoAlmacenes();
            
            foreach ($almacenes as $almacen) {
                $almacenAddress = $almacen['direccion'] ?? $almacen['nombre'] ?? '';
                if (
                    stripos($almacenAddress, $destination->direccion) !== false ||
                    stripos($destination->direccion, $almacenAddress) !== false
                ) {
                    Log::info('Almacén encontrado por coincidencia de dirección', [
                        'almacen_id' => $almacen['id'],
                        'almacen_nombre' => $almacen['nombre'],
                        'destination_id' => $destination->destino_id,
                    ]);
                    return $almacen['id'];
                }
            }
        }

        // Si no se encontró, usar el primer almacén de destino disponible
        $almacenes = $almacenSyncService->getDestinoAlmacenes();
        if (!empty($almacenes)) {
            $firstAlmacen = reset($almacenes);
            Log::warning('No se encontró almacén específico, usando almacén por defecto', [
                'almacen_id' => $firstAlmacen['id'],
                'almacen_nombre' => $firstAlmacen['nombre'],
                'destination_id' => $destination->destino_id,
                'destination_address' => $destination->direccion,
            ]);
            return $firstAlmacen['id'];
        }

        throw new \Exception("No hay almacenes de destino disponibles en plantaCruds para el destino: {$destination->direccion}");
    }


    /**
     * Send POST request to create Envio
     * 
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function createEnvio(array $data): array
    {
        $url = "{$this->apiUrl}/pedido-almacen";
        
        Log::info('Sending envio data to plantaCruds', [
            'url' => $url,
            'api_url' => $this->apiUrl,
            'data_keys' => array_keys($data),
            'productos_count' => count($data['productos'] ?? []),
        ]);

        try {
            // Usar la ruta /pedido-almacen que recibe pedidos desde sistemas externos
            $response = Http::timeout(30)
                ->post($url, $data);

            Log::info('Response from plantaCruds', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('plantaCruds API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'data_sent' => $data,
                ]);
                throw new \Exception("Error al crear envío en plantaCruds (HTTP {$response->status()}): {$errorBody}");
            }

            $result = $response->json();

            // Log detallado de la respuesta
            Log::info('Respuesta JSON de plantaCruds', [
                'result' => $result,
                'result_keys' => is_array($result) ? array_keys($result) : 'not_array',
                'has_envio_id' => isset($result['envio_id']),
                'has_codigo' => isset($result['codigo']),
                'envio_id_value' => $result['envio_id'] ?? 'NOT_SET',
                'codigo_value' => $result['codigo'] ?? 'NOT_SET',
            ]);

            if (!($result['success'] ?? false)) {
                Log::error('plantaCruds API returned unsuccessful response', [
                    'result' => $result,
                    'data_sent' => $data,
                ]);
                throw new \Exception($result['message'] ?? 'Error desconocido al crear envío');
            }

            Log::info('Envio created successfully in plantaCruds (createEnvio method)', [
                'envio_id' => $result['envio_id'] ?? null,
                'codigo' => $result['codigo'] ?? null,
                'full_result' => $result,
            ]);

            return $result;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to plantaCruds', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception("No se pudo conectar con plantaCruds en {$url}: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Exception sending to plantaCruds', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
