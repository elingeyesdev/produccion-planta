<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierResponse;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierResponseRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SupplierResponseResource;

class SupplierResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $supplierResponses = SupplierResponse::paginate();

        return SupplierResponseResource::collection($supplierResponses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierResponseRequest $request): JsonResponse
    {
        $supplierResponse = SupplierResponse::create($request->validated());

        return response()->json(new SupplierResponseResource($supplierResponse), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierResponse $supplierResponse): JsonResponse
    {
        return response()->json(new SupplierResponseResource($supplierResponse));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierResponseRequest $request, SupplierResponse $supplierResponse): JsonResponse
    {
        $supplierResponse->update($request->validated());

        return response()->json(new SupplierResponseResource($supplierResponse));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(SupplierResponse $supplierResponse): Response
    {
        $supplierResponse->delete();

        return response()->noContent();
    }
}