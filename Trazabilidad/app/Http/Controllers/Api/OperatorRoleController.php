<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperatorRole;
use Illuminate\Http\Request;
use App\Http\Requests\OperatorRoleRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OperatorRoleResource;

class OperatorRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $operatorRoles = OperatorRole::paginate();

        return OperatorRoleResource::collection($operatorRoles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OperatorRoleRequest $request): JsonResponse
    {
        $operatorRole = OperatorRole::create($request->validated());

        return response()->json(new OperatorRoleResource($operatorRole), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OperatorRole $operatorRole): JsonResponse
    {
        return response()->json(new OperatorRoleResource($operatorRole));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OperatorRoleRequest $request, OperatorRole $operatorRole): JsonResponse
    {
        $operatorRole->update($request->validated());

        return response()->json(new OperatorRoleResource($operatorRole));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(OperatorRole $operatorRole): Response
    {
        $operatorRole->delete();

        return response()->noContent();
    }
}