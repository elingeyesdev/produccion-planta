<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StandardVariable;
use Illuminate\Http\Request;
use App\Http\Requests\StandardVariableRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StandardVariableResource;

class StandardVariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $standardVariables = StandardVariable::paginate();

        return StandardVariableResource::collection($standardVariables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StandardVariableRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Manual ID generation if not auto-increment
        if (empty($data['variable_id'])) {
            $maxId = StandardVariable::max('variable_id') ?? 0;
            $nextId = $maxId + 1;
            $data['variable_id'] = $nextId;
            
            // Generate code automatically if not provided
            if (empty($data['code'])) {
                $data['code'] = 'VAR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        }

        $standardVariable = StandardVariable::create($data);

        return response()->json(new StandardVariableResource($standardVariable), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(StandardVariable $standardVariable): JsonResponse
    {
        return response()->json(new StandardVariableResource($standardVariable));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StandardVariableRequest $request, StandardVariable $standardVariable): JsonResponse
    {
        $standardVariable->update($request->validated());

        return response()->json(new StandardVariableResource($standardVariable));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(StandardVariable $standardVariable): Response
    {
        $standardVariable->delete();

        return response()->noContent();
    }
}