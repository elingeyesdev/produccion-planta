<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialMovementLog;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MaterialMovementLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $logs = MaterialMovementLog::with(['material', 'movementType', 'user'])
                ->orderBy('movement_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByMaterial($materialId): JsonResponse
    {
        try {
            $logs = MaterialMovementLog::with(['movementType', 'user'])
                ->where('material_id', $materialId)
                ->orderBy('movement_date', 'desc')
                ->get();

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|integer|exists:raw_material_base,material_id',
            'movement_type_id' => 'required|integer|exists:movement_type,movement_type_id',
            'quantity' => 'required|numeric',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $material = RawMaterialBase::findOrFail($request->material_id);
            $previousBalance = $material->available_quantity;

            // Calculate new balance based on movement type
            $movementType = \App\Models\MovementType::findOrFail($request->movement_type_id);
            $newBalance = $movementType->is_entry 
                ? $previousBalance + $request->quantity
                : $previousBalance - $request->quantity;

            // Update material quantity if movement affects stock
            if ($movementType->affects_stock) {
                $material->available_quantity = max(0, $newBalance);
                $material->save();
            }

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('material_movement_log_seq') as id")->id;
            
            // Create log
            $log = MaterialMovementLog::create([
                'log_id' => $nextId,
                'material_id' => $request->material_id,
                'movement_type_id' => $request->movement_type_id,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'previous_balance' => $previousBalance,
                'new_balance' => $movementType->affects_stock ? $material->available_quantity : $previousBalance,
                'description' => $request->description,
                'movement_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Log creado exitosamente',
                'log_id' => $log->log_id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

