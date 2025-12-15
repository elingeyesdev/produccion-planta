<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessFinalEvaluation;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessFinalEvaluationRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessFinalEvaluationResource;

class ProcessFinalEvaluationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processFinalEvaluations = ProcessFinalEvaluation::paginate();

        return ProcessFinalEvaluationResource::collection($processFinalEvaluations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessFinalEvaluationRequest $request): JsonResponse
    {
        $processFinalEvaluation = ProcessFinalEvaluation::create($request->validated());

        return response()->json(new ProcessFinalEvaluationResource($processFinalEvaluation), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcessFinalEvaluation $processFinalEvaluation): JsonResponse
    {
        return response()->json(new ProcessFinalEvaluationResource($processFinalEvaluation));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessFinalEvaluationRequest $request, ProcessFinalEvaluation $processFinalEvaluation): JsonResponse
    {
        $processFinalEvaluation->update($request->validated());

        return response()->json(new ProcessFinalEvaluationResource($processFinalEvaluation));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(ProcessFinalEvaluation $processFinalEvaluation): Response
    {
        $processFinalEvaluation->delete();

        return response()->noContent();
    }
}