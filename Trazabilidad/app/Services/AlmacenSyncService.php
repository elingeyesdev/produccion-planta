<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AlmacenSyncService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
    }

    /**
     * Obtener almacenes desde plantaCruds
     * 
     * @param bool $forceRefresh Forzar actualización sin usar cache
     * @return array
     */
    public function getAlmacenes(bool $forceRefresh = false): array
    {
        $cacheKey = 'almacenes_planta_cruds';
        
        if (!$forceRefresh) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            // Asegurar que la URL no tenga doble slash
            $url = rtrim($this->apiUrl, '/') . '/almacenes';
            Log::info('Intentando obtener almacenes desde plantaCruds', [
                'url' => $url,
                'api_url' => $this->apiUrl
            ]);

            $response = Http::timeout(10)
                ->withOptions([
                    'verify' => false, // Desactivar verificación SSL en desarrollo
                ])
                ->get($url);

            if ($response->successful()) {
                $result = $response->json();
                $almacenes = $result['data'] ?? [];

                // Cachear por 1 hora
                Cache::put($cacheKey, $almacenes, now()->addHour());

                Log::info('Almacenes sincronizados desde plantaCruds', [
                    'count' => count($almacenes),
                    'url' => $url
                ]);

                return $almacenes;
            } else {
                Log::error('Error al obtener almacenes desde plantaCruds', [
                    'status' => $response->status(),
                    'url' => $url,
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Excepción al obtener almacenes desde plantaCruds', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $url ?? 'N/A'
            ]);
            return [];
        }
    }

    /**
     * Obtener almacén planta (origen)
     * 
     * @return array|null
     */
    public function getPlantaAlmacen(): ?array
    {
        $almacenes = $this->getAlmacenes();
        
        foreach ($almacenes as $almacen) {
            if ($almacen['es_planta'] ?? false) {
                return $almacen;
            }
        }

        return null;
    }

    /**
     * Obtener almacenes de destino (no plantas)
     * 
     * @return array
     */
    public function getDestinoAlmacenes(): array
    {
        $almacenes = $this->getAlmacenes();
        
        $destinos = array_filter($almacenes, function($almacen) {
            return !($almacen['es_planta'] ?? false);
        });
        
        // Reindexar array para que sea numérico
        return array_values($destinos);
    }

    /**
     * Buscar almacén por ID
     * 
     * @param int $almacenId
     * @return array|null
     */
    public function findAlmacenById(int $almacenId): ?array
    {
        $almacenes = $this->getAlmacenes();
        
        foreach ($almacenes as $almacen) {
            if ($almacen['id'] == $almacenId) {
                return $almacen;
            }
        }

        return null;
    }

    /**
     * Buscar almacén más cercano por coordenadas
     * 
     * @param float $latitud
     * @param float $longitud
     * @param bool $onlyDestinos Solo almacenes de destino (no plantas)
     * @return array|null
     */
    public function findNearestAlmacen(float $latitud, float $longitud, bool $onlyDestinos = true): ?array
    {
        $almacenes = $onlyDestinos ? $this->getDestinoAlmacenes() : $this->getAlmacenes();
        
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($almacenes as $almacen) {
            if (!isset($almacen['latitud']) || !isset($almacen['longitud'])) {
                continue;
            }

            $distance = $this->calculateDistance(
                $latitud,
                $longitud,
                $almacen['latitud'],
                $almacen['longitud']
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $almacen;
            }
        }

        return $nearest;
    }

    /**
     * Calcular distancia entre dos coordenadas (Haversine)
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distancia en kilómetros
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radio de la Tierra en km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Limpiar cache de almacenes
     */
    public function clearCache(): void
    {
        Cache::forget('almacenes_planta_cruds');
    }
}

