<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AlmacenSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    private AlmacenSyncService $almacenSyncService;

    public function __construct(AlmacenSyncService $almacenSyncService)
    {
        $this->almacenSyncService = $almacenSyncService;
    }

    /**
     * Obtener todos los almacenes desde plantaCruds
     * GET /api/almacenes
     */
    public function index(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh', false);
        $almacenes = $this->almacenSyncService->getAlmacenes($forceRefresh);

        return response()->json([
            'success' => true,
            'data' => $almacenes,
            'count' => count($almacenes)
        ]);
    }

    /**
     * Obtener almacén planta (origen)
     * GET /api/almacenes/planta
     */
    public function planta(): JsonResponse
    {
        $planta = $this->almacenSyncService->getPlantaAlmacen();

        if (!$planta) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró almacén planta en plantaCruds'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $planta
        ]);
    }

    /**
     * Obtener almacenes de destino (no plantas)
     * GET /api/almacenes/destinos
     */
    public function destinos(): JsonResponse
    {
        $almacenes = $this->almacenSyncService->getDestinoAlmacenes();

        return response()->json([
            'success' => true,
            'data' => array_values($almacenes), // Reindexar array
            'count' => count($almacenes)
        ]);
    }

    /**
     * Buscar almacén más cercano por coordenadas
     * GET /api/almacenes/nearest?latitud=...&longitud=...
     */
    public function nearest(Request $request): JsonResponse
    {
        $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'only_destinos' => 'sometimes|boolean'
        ]);

        $nearest = $this->almacenSyncService->findNearestAlmacen(
            $request->latitud,
            $request->longitud,
            $request->boolean('only_destinos', true)
        );

        if (!$nearest) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún almacén cercano'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $nearest
        ]);
    }

    /**
     * Limpiar cache de almacenes
     * POST /api/almacenes/clear-cache
     */
    public function clearCache(): JsonResponse
    {
        $this->almacenSyncService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Cache de almacenes limpiado exitosamente'
        ]);
    }
}

