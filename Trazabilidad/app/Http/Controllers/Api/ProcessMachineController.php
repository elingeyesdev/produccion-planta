<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessMachineRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessMachineResource;

class ProcessMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processMachines = ProcessMachine::paginate();

        return ProcessMachineResource::collection($processMachines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessMachineRequest $request): JsonResponse
    {
        $processMachine = ProcessMachine::create($request->validated());

        return response()->json(new ProcessMachineResource($processMachine), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcessMachine $processMachine): JsonResponse
    {
        return response()->json(new ProcessMachineResource($processMachine));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessMachineRequest $request, ProcessMachine $processMachine): JsonResponse
    {
        $processMachine->update($request->validated());

        return response()->json(new ProcessMachineResource($processMachine));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(ProcessMachine $processMachine): Response
    {
        $processMachine->delete();

        return response()->noContent();
    }
}