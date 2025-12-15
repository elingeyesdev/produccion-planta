<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialCategory;
use Illuminate\Http\Request;
use App\Http\Requests\RawMaterialCategoryRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RawMaterialCategoryResource;

class RawMaterialCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rawMaterialCategorys = RawMaterialCategory::paginate();

        return RawMaterialCategoryResource::collection($rawMaterialCategorys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RawMaterialCategoryRequest $request): JsonResponse
    {
        $rawMaterialCategory = RawMaterialCategory::create($request->validated());

        return response()->json(new RawMaterialCategoryResource($rawMaterialCategory), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RawMaterialCategory $rawMaterialCategory): JsonResponse
    {
        return response()->json(new RawMaterialCategoryResource($rawMaterialCategory));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RawMaterialCategoryRequest $request, RawMaterialCategory $rawMaterialCategory): JsonResponse
    {
        $rawMaterialCategory->update($request->validated());

        return response()->json(new RawMaterialCategoryResource($rawMaterialCategory));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(RawMaterialCategory $rawMaterialCategory): Response
    {
        $rawMaterialCategory->delete();

        return response()->noContent();
    }
}