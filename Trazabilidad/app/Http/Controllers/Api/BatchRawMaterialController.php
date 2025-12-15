<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatchRawMaterial;
use Illuminate\Http\Request;
use App\Http\Requests\BatchRawMaterialRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BatchRawMaterialResource;

class BatchRawMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $batchRawMaterials = BatchRawMaterial::paginate();

        return BatchRawMaterialResource::collection($batchRawMaterials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BatchRawMaterialRequest $request): JsonResponse
    {
        $batchRawMaterial = BatchRawMaterial::create($request->validated());

        return response()->json(new BatchRawMaterialResource($batchRawMaterial), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BatchRawMaterial $batchRawMaterial): JsonResponse
    {
        return response()->json(new BatchRawMaterialResource($batchRawMaterial));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BatchRawMaterialRequest $request, BatchRawMaterial $batchRawMaterial): JsonResponse
    {
        $batchRawMaterial->update($request->validated());

        return response()->json(new BatchRawMaterialResource($batchRawMaterial));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(BatchRawMaterial $batchRawMaterial): Response
    {
        $batchRawMaterial->delete();

        return response()->noContent();
    }
}