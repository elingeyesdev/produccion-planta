<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use App\Http\Requests\OperatorRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OperatorResource;
use Illuminate\Support\Facades\DB;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $operators = Operator::paginate();

        return OperatorResource::collection($operators);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OperatorRequest $request): JsonResponse
    {
        // Obtener el siguiente ID de la secuencia
        $nextId = DB::selectOne("SELECT nextval('operator_seq') as id")->id;
        
        $data = $request->validated();
        $data['operator_id'] = $nextId;
        
        // Si viene password, hashearlo
        if (isset($data['password'])) {
            $data['password_hash'] = \Hash::make($data['password']);
            unset($data['password']);
        }
        
        $operator = Operator::create($data);

        return response()->json(new OperatorResource($operator), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Operator $operator): JsonResponse
    {
        return response()->json(new OperatorResource($operator));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OperatorRequest $request, Operator $operator): JsonResponse
    {
        $operator->update($request->validated());

        return response()->json(new OperatorResource($operator));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Operator $operator): Response
    {
        $operator->delete();

        return response()->noContent();
    }
}