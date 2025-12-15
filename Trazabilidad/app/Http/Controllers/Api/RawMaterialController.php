<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Http\Resources\RawMaterialResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $materials = RawMaterial::with(['materialBase.unit', 'supplier'])
                ->orderBy('fecha_recepcion', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json(RawMaterialResource::collection($materials)->response()->getData());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener materias primas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $material = RawMaterial::with(['materialBase.unit', 'supplier'])->findOrFail($id);
            return response()->json(new RawMaterialResource($material));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Materia prima no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|integer|exists:raw_material_base,material_id',
            'supplier_id' => 'required|integer|exists:supplier,supplier_id',
            'supplier_batch' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'receipt_conformity' => 'nullable|boolean',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos o inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Get the next ID manually since sequence doesn't exist
            $maxId = RawMaterial::max('raw_material_id') ?? 0;
            $nextId = $maxId + 1;
            
            $material = RawMaterial::create([
                'raw_material_id' => $nextId,
                'material_id' => $request->material_id,
                'supplier_id' => $request->supplier_id,
                'supplier_batch' => $request->supplier_batch,
                'invoice_number' => $request->invoice_number,
                'receipt_date' => $request->receipt_date,
                'expiration_date' => $request->expiration_date,
                'quantity' => $request->quantity,
                'available_quantity' => $request->quantity,
                'receipt_conformity' => $request->receipt_conformity,
                'observations' => $request->observations,
            ]);

            return response()->json([
                'message' => 'Materia prima creada exitosamente',
                'raw_material_id' => $material->raw_material_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_batch' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'quantity' => 'nullable|numeric|min:0',
            'available_quantity' => 'nullable|numeric|min:0',
            'receipt_conformity' => 'nullable|boolean',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $material = RawMaterial::findOrFail($id);
            $material->update($request->only([
                'supplier_batch', 'invoice_number', 'receipt_date',
                'expiration_date', 'quantity', 'available_quantity',
                'receipt_conformity', 'observations'
            ]));

            return response()->json([
                'message' => 'Materia prima actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $material = RawMaterial::findOrFail($id);
            $material->delete();

            return response()->json([
                'message' => 'Materia prima eliminada satisfactoriamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

