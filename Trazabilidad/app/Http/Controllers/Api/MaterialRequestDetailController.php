<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequestDetail;
use Illuminate\Http\Request;
use App\Http\Requests\MaterialRequestDetailRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MaterialRequestDetailResource;

class MaterialRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $materialRequestDetails = MaterialRequestDetail::paginate();

        return MaterialRequestDetailResource::collection($materialRequestDetails);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MaterialRequestDetailRequest $request): JsonResponse
    {
        $materialRequestDetail = MaterialRequestDetail::create($request->validated());

        return response()->json(new MaterialRequestDetailResource($materialRequestDetail), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialRequestDetail $materialRequestDetail): JsonResponse
    {
        return response()->json(new MaterialRequestDetailResource($materialRequestDetail));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MaterialRequestDetailRequest $request, MaterialRequestDetail $materialRequestDetail): JsonResponse
    {
        $materialRequestDetail->update($request->validated());

        return response()->json(new MaterialRequestDetailResource($materialRequestDetail));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(MaterialRequestDetail $materialRequestDetail): Response
    {
        $materialRequestDetail->delete();

        return response()->noContent();
    }
}