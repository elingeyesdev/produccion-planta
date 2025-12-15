<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Requests\StatusRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StatusResource;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statuss = Status::paginate();

        return StatusResource::collection($statuss);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StatusRequest $request): JsonResponse
    {
        $status = Status::create($request->validated());

        return response()->json(new StatusResource($status), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status): JsonResponse
    {
        return response()->json(new StatusResource($status));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StatusRequest $request, Status $status): JsonResponse
    {
        $status->update($request->validated());

        return response()->json(new StatusResource($status));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Status $status): Response
    {
        $status->delete();

        return response()->noContent();
    }
}