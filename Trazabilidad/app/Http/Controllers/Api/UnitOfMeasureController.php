<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use App\Http\Requests\UnitOfMeasureRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UnitOfMeasureResource;

class UnitOfMeasureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $unitOfMeasures = UnitOfMeasure::paginate();

        return UnitOfMeasureResource::collection($unitOfMeasures);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitOfMeasureRequest $request): JsonResponse
    {
        $unitOfMeasure = UnitOfMeasure::create($request->validated());

        return response()->json(new UnitOfMeasureResource($unitOfMeasure), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        return response()->json(new UnitOfMeasureResource($unitOfMeasure));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnitOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        $unitOfMeasure->update($request->validated());

        return response()->json(new UnitOfMeasureResource($unitOfMeasure));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(UnitOfMeasure $unitOfMeasure): Response
    {
        $unitOfMeasure->delete();

        return response()->noContent();
    }
}

