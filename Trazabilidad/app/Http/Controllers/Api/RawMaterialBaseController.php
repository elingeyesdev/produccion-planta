<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RawMaterialBaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $materials = RawMaterialBase::with(['category', 'unit'])
                ->where('activo', true)
                ->paginate($request->get('per_page', 15));

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener materias primas base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $material = RawMaterialBase::with(['category', 'unit'])->findOrFail($id);
            return response()->json($material);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Materia prima base no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:raw_material_category,category_id',
            'unit_id' => 'required|integer|exists:unit_of_measure,unit_id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Nombre y unidad son requeridos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Get the next ID manually since sequence doesn't exist
            $maxId = RawMaterialBase::max('material_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Generate code
            $code = 'MP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            // Create material with manual ID
            $material = RawMaterialBase::create([
                'material_id' => $nextId,
                'categoria_id' => $request->category_id,
                'unidad_id' => $request->unit_id,
                'codigo' => $code,
                'nombre' => $request->name,
                'descripcion' => $request->description,
                'cantidad_disponible' => 0,
                'stock_minimo' => $request->minimum_stock ?? 0,
                'stock_maximo' => $request->maximum_stock,
                'activo' => true,
            ]);

            return response()->json([
                'id' => $material->material_id,
                'message' => 'Materia prima base creada'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $material = RawMaterialBase::findOrFail($id);
            
            $data = [];
            if ($request->has('name')) $data['nombre'] = $request->name;
            if ($request->has('description')) $data['descripcion'] = $request->description;
            if ($request->has('minimum_stock')) $data['stock_minimo'] = $request->minimum_stock;
            if ($request->has('maximum_stock')) $data['stock_maximo'] = $request->maximum_stock;
            if ($request->has('active')) $data['activo'] = $request->active;

            $material->update($data);

            return response()->json([
                'message' => 'Materia prima base actualizada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $material = RawMaterialBase::findOrFail($id);
            $material->update(['activo' => false]);

            return response()->json([
                'message' => 'Materia prima base eliminada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

