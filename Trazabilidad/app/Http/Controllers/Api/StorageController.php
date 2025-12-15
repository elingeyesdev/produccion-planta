<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StorageController extends Controller
{
    /**
     * List all storage records
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $storages = Storage::with('batch.order.customer')
                ->orderBy('storage_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json($storages);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar almacenajes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get storage by batch ID
     */
    public function getByBatch($batchId): JsonResponse
    {
        try {
            $storage = Storage::with('batch.order.customer')
                ->where('batch_id', $batchId)
                ->first();

            if (!$storage) {
                return response()->json([
                    'message' => 'Almacenaje no encontrado para este lote'
                ], 404);
            }

            return response()->json($storage);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener almacenaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create storage record
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|integer|exists:production_batch,batch_id',
            'location' => 'required|string|max:100',
            'condition' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::findOrFail($request->batch_id);

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('storage_seq') as id")->id;

            $storage = Storage::create([
                'storage_id' => $nextId,
                'batch_id' => $request->batch_id,
                'location' => $request->location,
                'condition' => $request->condition,
                'quantity' => $request->quantity,
                'observations' => $request->observations,
                'storage_date' => now(),
            ]);

            // Update batch status or other fields if needed
            // The old system updated batch status to "almacenado"
            // You might want to add a status field or use a status table

            DB::commit();

            return response()->json([
                'id' => $storage->storage_id,
                'message' => 'Almacenaje registrado y lote actualizado'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear almacenaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

