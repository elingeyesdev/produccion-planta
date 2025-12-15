<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovementType;
use Illuminate\Http\Request;
use App\Http\Requests\MovementTypeRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MovementTypeResource;

class MovementTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $movementTypes = MovementType::paginate();

        return MovementTypeResource::collection($movementTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovementTypeRequest $request): JsonResponse
    {
        $movementType = MovementType::create($request->validated());

        return response()->json(new MovementTypeResource($movementType), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MovementType $movementType): JsonResponse
    {
        return response()->json(new MovementTypeResource($movementType));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovementTypeRequest $request, MovementType $movementType): JsonResponse
    {
        $movementType->update($request->validated());

        return response()->json(new MovementTypeResource($movementType));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(MovementType $movementType): Response
    {
        $movementType->delete();

        return response()->noContent();
    }
}