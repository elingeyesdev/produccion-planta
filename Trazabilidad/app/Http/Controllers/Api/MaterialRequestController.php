<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use App\Http\Requests\MaterialRequestRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MaterialRequestResource;

class MaterialRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $materialRequests = MaterialRequest::paginate();

        return MaterialRequestResource::collection($materialRequests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MaterialRequestRequest $request): JsonResponse
    {
        $materialRequest = MaterialRequest::create($request->validated());

        return response()->json(new MaterialRequestResource($materialRequest), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialRequest $materialRequest): JsonResponse
    {
        return response()->json(new MaterialRequestResource($materialRequest));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MaterialRequestRequest $request, MaterialRequest $materialRequest): JsonResponse
    {
        $materialRequest->update($request->validated());

        return response()->json(new MaterialRequestResource($materialRequest));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(MaterialRequest $materialRequest): Response
    {
        $materialRequest->delete();

        return response()->noContent();
    }
}