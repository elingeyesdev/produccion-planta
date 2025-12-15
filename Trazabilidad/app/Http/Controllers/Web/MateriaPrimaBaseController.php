<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialBase;
use App\Models\RawMaterialCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MateriaPrimaBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = RawMaterialBase::with(['category', 'unit', 'rawMaterials'])
            ->where('activo', true);
        
        // Filtro por categoría
        if ($request->has('categoria') && $request->categoria) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->categoria . '%');
            });
        }
        
        // Filtro por estado (disponible, bajo_stock, agotado)
        if ($request->has('estado') && $request->estado) {
            // Este filtro se aplicará después de calcular las cantidades
        }
        
        // Filtro por búsqueda (nombre, código)
        if ($request->has('buscar') && $request->buscar) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'like', '%' . $request->buscar . '%');
            });
        }
        
        $materias_primas = $query->orderBy('nombre', 'asc')
            ->paginate(15)
            ->appends($request->query());

        // Calcular cantidad_disponible dinámicamente desde las materias primas relacionadas
        $materias_primas->getCollection()->transform(function ($mp) {
            // Usar la relación cargada o hacer una nueva consulta si no está cargada
            if ($mp->relationLoaded('rawMaterials')) {
                $calculated = $mp->rawMaterials
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
            } else {
                $calculated = $mp->rawMaterials()
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
            }
            
            // Si no hay materias primas recibidas, usar el valor almacenado en materia_prima_base
            if ($calculated == 0 && ($mp->rawMaterials->count() == 0 || !$mp->relationLoaded('rawMaterials'))) {
                $mp->calculated_available_quantity = $mp->cantidad_disponible ?? 0;
            } else {
                $mp->calculated_available_quantity = $calculated;
            }
            return $mp;
        });
        
        // Aplicar filtro por estado después de calcular las cantidades
        if ($request->has('estado') && $request->estado) {
            $estado = $request->estado;
            $materias_primas->getCollection()->transform(function ($mp) use ($estado) {
                $available = $mp->calculated_available_quantity ?? 0;
                $minimum = $mp->stock_minimo ?? 0;
                
                $mp->should_show = false;
                if ($estado === 'disponible') {
                    $mp->should_show = $available > $minimum;
                } elseif ($estado === 'bajo_stock') {
                    $mp->should_show = $minimum > 0 && $available > 0 && $available <= $minimum;
                } elseif ($estado === 'agotado') {
                    $mp->should_show = $available <= 0;
                }
                return $mp;
            });
            
            // Filtrar la colección
            $materias_primas->setCollection(
                $materias_primas->getCollection()->filter(function($mp) {
                    return !isset($mp->should_show) || $mp->should_show;
                })
            );
        }

        $categorias = RawMaterialCategory::where('activo', true)->get();
        $unidades = UnitOfMeasure::where('activo', true)->get();

        // Estadísticas basadas en calculated_available_quantity
        $allMaterias = RawMaterialBase::with('rawMaterials')
            ->where('activo', true)
            ->get()
            ->map(function ($mp) {
                // Usar la relación cargada
                $calculated = $mp->rawMaterials
                    ->where('conformidad_recepcion', true)
                    ->sum('cantidad_disponible') ?? 0;
                
                // Si no hay materias primas recibidas, usar el valor almacenado en materia_prima_base
                if ($calculated == 0 && $mp->rawMaterials->count() == 0) {
                    $mp->calculated_available_quantity = $mp->cantidad_disponible ?? 0;
                } else {
                    $mp->calculated_available_quantity = $calculated;
                }
                return $mp;
            });

        $disponibles = 0;
        $bajo_stock = 0;
        $agotadas = 0;

        foreach ($allMaterias as $mp) {
            $available = $mp->calculated_available_quantity ?? 0;
            $minimum = $mp->stock_minimo ?? 0;
            
            if ($available <= 0) {
                $agotadas++;
            } elseif ($minimum > 0 && $available <= $minimum) {
                $bajo_stock++;
            } else {
                $disponibles++;
            }
        }

        $stats = [
            'total' => $allMaterias->count(),
            'disponibles' => $disponibles,
            'bajo_stock' => $bajo_stock,
            'agotadas' => $agotadas,
        ];

        return view('materia-prima-base', compact('materias_primas', 'categorias', 'unidades', 'stats'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoria_id' => 'required|integer|exists:categoria_materia_prima,categoria_id',
            'unidad_id' => 'required|integer|exists:unidad_medida,unidad_id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'stock_minimo' => 'nullable|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Verificar que la categoría existe
            $categoria = RawMaterialCategory::findOrFail($request->categoria_id);
            
            // Verificar que la unidad existe
            $unidad = UnitOfMeasure::findOrFail($request->unidad_id);
            
            // Sincronizar la secuencia y obtener el siguiente ID
            $maxId = DB::table('materia_prima_base')->max('material_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxId !== null && $maxId > 0) {
                // Sincronizar la secuencia con el máximo ID existente
                // El tercer parámetro 'true' hace que el siguiente nextval devuelva maxId + 1
                DB::statement("SELECT setval('materia_prima_base_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('materia_prima_base_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'MP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Crear usando SQL directo para evitar conflictos
            $materialId = DB::selectOne("
                INSERT INTO materia_prima_base (material_id, categoria_id, unidad_id, codigo, nombre, descripcion, cantidad_disponible, stock_minimo, stock_maximo, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING material_id
            ", [
                $nextId,
                $request->categoria_id,
                $request->unidad_id,
                $code,
                $request->nombre,
                $request->descripcion,
                0,
                $request->stock_minimo ?? 0,
                $request->stock_maximo,
                true
            ])->material_id;

            DB::commit();

            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Materia prima creada exitosamente',
                    'material_id' => $nextId
                ]);
            }

            return redirect()->route('materia-prima-base')
                ->with('success', 'Materia prima base creada exitosamente. NOTA: Para tener disponibilidad, debe recibir materia prima usando el formulario de "Recepción de Materia Prima".');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear materia prima base: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al crear materia prima base: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $materia = RawMaterialBase::with(['category', 'unit', 'rawMaterials'])->findOrFail($id);
            
            // Calcular stock actual
            $calculated = $materia->rawMaterials
                ->where('conformidad_recepcion', true)
                ->sum('cantidad_disponible') ?? 0;
            
            if ($calculated == 0 && $materia->rawMaterials->count() == 0) {
                $calculated = $materia->cantidad_disponible ?? 0;
            }
            
            return response()->json([
                'material_id' => $materia->material_id,
                'code' => $materia->codigo,
                'name' => $materia->nombre,
                'category_id' => $materia->categoria_id,
                'unit_id' => $materia->unidad_id,
                'description' => $materia->descripcion,
                'minimum_stock' => $materia->stock_minimo,
                'maximum_stock' => $materia->stock_maximo,
                'current_stock' => $calculated,
                'available_quantity' => number_format($calculated, 2),
                'active' => $materia->activo,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Materia prima no encontrada'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'categoria_id' => 'required|integer|exists:categoria_materia_prima,categoria_id',
            'unidad_id' => 'required|integer|exists:unidad_medida,unidad_id',
            'descripcion' => 'nullable|string|max:255',
            'stock_minimo' => 'nullable|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $materia = RawMaterialBase::findOrFail($id);
            
            $updateData = [
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
                'unidad_id' => $request->unidad_id,
                'descripcion' => $request->descripcion ?? null,
                'stock_minimo' => $request->stock_minimo ?? 0,
                'stock_maximo' => $request->stock_maximo ?: null,
            ];
            
            // Manejar activo: si se envía, usar el valor; si no se envía, mantener el valor actual
            if ($request->has('activo')) {
                $updateData['activo'] = (bool)$request->activo;
            }
            
            $materia->update($updateData);

            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Materia prima base actualizada exitosamente'
                ]);
            }

            return redirect()->route('materia-prima-base')
                ->with('success', 'Materia prima base actualizada exitosamente');
        } catch (\Exception $e) {
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}

