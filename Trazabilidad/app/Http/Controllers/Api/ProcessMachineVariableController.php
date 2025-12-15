<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessMachineVariable;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessMachineVariableRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessMachineVariableResource;

class ProcessMachineVariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processMachineVariables = ProcessMachineVariable::paginate();

        return ProcessMachineVariableResource::collection($processMachineVariables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessMachineVariableRequest $request): JsonResponse
    {
        $processMachineVariable = ProcessMachineVariable::create($request->validated());

        return response()->json(new ProcessMachineVariableResource($processMachineVariable), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcessMachineVariable $processMachineVariable): JsonResponse
    {
        return response()->json(new ProcessMachineVariableResource($processMachineVariable));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessMachineVariableRequest $request, ProcessMachineVariable $processMachineVariable): JsonResponse
    {
        $processMachineVariable->update($request->validated());

        return response()->json(new ProcessMachineVariableResource($processMachineVariable));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(ProcessMachineVariable $processMachineVariable): Response
    {
        $processMachineVariable->delete();

        return response()->noContent();
    }
}