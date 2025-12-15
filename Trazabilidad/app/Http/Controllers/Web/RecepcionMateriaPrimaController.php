<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialBase;
use App\Models\Supplier;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecepcionMateriaPrimaController extends Controller
{
    public function index(Request $request)
    {
        // Obtener solicitudes pendientes (con detalles de materiales)
        $query = MaterialRequest::with(['order.customer', 'details.material.unit']);
        
        // Filtro por estado
        if ($request->has('estado') && $request->estado) {
            $estado = $request->estado;
            if ($estado === 'pendiente') {
                $query->whereHas('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            } elseif ($estado === 'en_proceso') {
                $query->whereHas('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) > 0')
                      ->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            } elseif ($estado === 'completada') {
                $query->whereDoesntHave('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            }
        } else {
            // Por defecto, mostrar solo pendientes
            $query->whereHas('details', function($q) {
                $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
            });
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_solicitud', $request->fecha);
        }
        
        // Filtro por proveedor (buscar en materias primas recibidas relacionadas)
        if ($request->has('proveedor') && $request->proveedor) {
            $query->whereHas('details.material.rawMaterials.supplier', function($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->proveedor . '%')
                  ->orWhere('nombre_comercial', 'like', '%' . $request->proveedor . '%');
            });
        }
        
        $solicitudes = $query->orderBy('fecha_requerida', 'asc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener materias primas recibidas (recientes)
        $materias_primas = RawMaterial::with(['materialBase.unit', 'supplier'])
            ->orderBy('fecha_recepcion', 'desc')
            ->limit(20)
            ->get();

        $materias_base = RawMaterialBase::where('activo', true)
            ->with('unit')
            ->orderBy('nombre', 'asc')
            ->get();
        $proveedores = Supplier::where('activo', true)
            ->orderBy('razon_social', 'asc')
            ->get();

        // Calcular estadísticas correctas
        $totalRecepciones = RawMaterial::count();
        
        // Solicitudes completadas: todas las cantidades aprobadas >= solicitadas
        $allSolicitudes = MaterialRequest::with('details')->get();
        $solicitudesCompletadas = 0;
        $solicitudesPendientes = 0;
        
        foreach ($allSolicitudes as $solicitud) {
            $todosCompletos = true;
            if ($solicitud->details->isEmpty()) {
                $solicitudesPendientes++;
                continue;
            }
            
            foreach ($solicitud->details as $detail) {
                $cantidadAprobada = $detail->cantidad_aprobada ?? 0;
                $cantidadSolicitada = $detail->cantidad_solicitada ?? 0;
                if ($cantidadAprobada < $cantidadSolicitada) {
                    $todosCompletos = false;
                    break;
                }
            }
            
            if ($todosCompletos) {
                $solicitudesCompletadas++;
            } else {
                $solicitudesPendientes++;
            }
        }
        
        // Estadísticas
        $stats = [
            'total_recepciones' => $totalRecepciones,
            'completadas' => $solicitudesCompletadas,
            'pendientes' => $solicitudesPendientes,
        ];

        // Preparar datos de solicitudes para JavaScript
        $solicitudesJson = $solicitudes->getCollection()->map(function($s) {
            $details = $s->details->map(function($d) {
                return [
                    'material_id' => $d->material_id,
                    'requested_quantity' => (float)$d->cantidad_solicitada,
                    'approved_quantity' => (float)($d->cantidad_aprobada ?? 0),
                ];
            })->values()->toArray();
            
            return [
                'request_id' => $s->solicitud_id,
                'request_number' => $s->numero_solicitud,
                'fecha_solicitud' => $s->fecha_solicitud ? $s->fecha_solicitud->format('Y-m-d') : null,
                'fecha_requerida' => $s->fecha_requerida ? $s->fecha_requerida->format('Y-m-d') : null,
                'fecha_solicitud_formatted' => $s->fecha_solicitud ? $s->fecha_solicitud->format('d/m/Y') : null,
                'fecha_requerida_formatted' => $s->fecha_requerida ? $s->fecha_requerida->format('d/m/Y') : null,
                'details' => $details,
            ];
        })->values()->toArray();

        // Preparar datos de recepciones para JavaScript
        $recepcionesJson = $materias_primas->map(function($mp) {
            return [
                'materia_prima_id' => $mp->materia_prima_id,
                'material_name' => $mp->materialBase->nombre ?? 'N/A',
                'supplier_name' => $mp->supplier->razon_social ?? 'N/A',
                'quantity' => (float)$mp->cantidad,
                'available_quantity' => (float)$mp->cantidad_disponible,
                'unit' => $mp->materialBase->unit->codigo ?? '',
                'receipt_date' => $mp->fecha_recepcion ? $mp->fecha_recepcion->format('Y-m-d') : null,
                'expiration_date' => $mp->fecha_vencimiento ? $mp->fecha_vencimiento->format('Y-m-d') : null,
                'invoice_number' => $mp->numero_factura ?? 'N/A',
                'supplier_batch' => $mp->lote_proveedor ?? 'N/A',
                'receipt_conformity' => $mp->conformidad_recepcion ?? false,
                'observations' => $mp->observaciones ?? '',
            ];
        })->values()->toArray();

        return view('recepcion-materia-prima', compact('solicitudes', 'materias_primas', 'materias_base', 'proveedores', 'stats', 'solicitudesJson', 'recepcionesJson'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'proveedor_id' => 'required|integer|exists:proveedor,proveedor_id',
            'lote_proveedor' => 'nullable|string|max:100',
            'numero_factura' => 'required|string|max:100',
            'fecha_recepcion' => ['required', 'date', 'after_or_equal:today'],
            'fecha_vencimiento' => 'nullable|date|after:fecha_recepcion',
            'cantidad' => 'required|numeric|min:0',
            'conformidad_recepcion' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:500',
            'solicitud_id' => 'nullable|integer|exists:solicitud_material,solicitud_id',
        ], [
            'fecha_recepcion.after_or_equal' => 'La fecha de recepción no puede ser anterior a hoy.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Verificar que la materia prima base existe
            $materialBase = RawMaterialBase::findOrFail($request->material_id);
            
            // Verificar que el proveedor existe
            $supplier = Supplier::findOrFail($request->proveedor_id);
            
            // Sincronizar la secuencia y obtener el siguiente ID
            $maxId = DB::table('materia_prima')->max('materia_prima_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxId !== null && $maxId > 0) {
                // Sincronizar la secuencia con el máximo ID existente
                // El tercer parámetro 'true' hace que el siguiente nextval devuelva maxId + 1
                DB::statement("SELECT setval('materia_prima_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('materia_prima_seq') as id")->id;
            
            // Convertir conformidad_recepcion a boolean correctamente
            // El formulario envía "1" o "0" como string
            $receiptConformity = true; // Por defecto true
            if ($request->has('conformidad_recepcion')) {
                $receiptConformity = $request->conformidad_recepcion == '1' || $request->conformidad_recepcion === 1 || $request->conformidad_recepcion === true;
            }
            
            // Guardar el balance anterior antes de actualizar
            $previousBalance = $materialBase->cantidad_disponible ?? 0;
            
            // Crear registro en materia_prima usando SQL directo para evitar conflictos
            $rawMaterialId = DB::selectOne("
                INSERT INTO materia_prima (materia_prima_id, material_id, proveedor_id, lote_proveedor, numero_factura, fecha_recepcion, fecha_vencimiento, cantidad, cantidad_disponible, conformidad_recepcion, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING materia_prima_id
            ", [
                $nextId,
                $request->material_id,
                $request->proveedor_id,
                $request->lote_proveedor,
                $request->numero_factura,
                $request->fecha_recepcion,
                $request->fecha_vencimiento,
                $request->cantidad,
                $request->cantidad,
                $receiptConformity,
                $request->observaciones
            ])->materia_prima_id;
            
            $rawMaterial = RawMaterial::find($rawMaterialId);

            // Actualizar cantidad disponible en materia prima base solo si conformidad_recepcion es true
            if ($receiptConformity) {
                $materialBase->cantidad_disponible = ($materialBase->cantidad_disponible ?? 0) + $request->cantidad;
            $materialBase->save();
            }

            // Si se recepciona desde una solicitud, actualizar el detalle y verificar si está completa
            if ($request->has('solicitud_id') && $request->solicitud_id) {
                $materialRequest = MaterialRequest::with('details')->findOrFail($request->solicitud_id);
                
                // Buscar el detalle correspondiente al material recepcionado
                $detail = $materialRequest->details->firstWhere('material_id', $request->material_id);
                
                if ($detail) {
                    // Actualizar cantidad_aprobada sumando la cantidad recepcionada
                    $currentApproved = $detail->cantidad_aprobada ?? 0;
                    $detail->cantidad_aprobada = $currentApproved + $request->cantidad;
                    $detail->save();
                }
                
                // Verificar si todos los detalles de la solicitud han sido recepcionados completamente
                $allCompleted = true;
                foreach ($materialRequest->details as $det) {
                    $approvedQty = $det->cantidad_aprobada ?? 0;
                    $requestedQty = $det->cantidad_solicitada ?? 0;
                    if ($approvedQty < $requestedQty) {
                        $allCompleted = false;
                        break;
                    }
                }
                
                // Si todos los materiales han sido recepcionados, la solicitud se considera completada
                // (Ya no usamos campo priority, el estado se maneja por las cantidades aprobadas)
            }

            // Sincronizar secuencia y registrar en log de movimientos
            $maxLogId = DB::table('registro_movimiento_material')->max('registro_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            if ($maxLogId !== null && $maxLogId > 0) {
                DB::statement("SELECT setval('registro_movimiento_material_seq', {$maxLogId}, true)");
            }
            
            // Obtener el siguiente ID del log
            $logNextId = DB::selectOne("SELECT nextval('registro_movimiento_material_seq') as id")->id;
            
            DB::selectOne("
                INSERT INTO registro_movimiento_material (registro_id, material_id, tipo_movimiento_id, operador_id, cantidad, saldo_anterior, saldo_nuevo, descripcion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING registro_id
            ", [
                $logNextId,
                $request->material_id,
                1, // Entrada
                auth()->id(),
                $request->cantidad,
                $previousBalance,
                $materialBase->cantidad_disponible,
                'Recepción de materia prima' . ($receiptConformity ? ' (Conforme)' : ' (No conforme)')
            ]);

            DB::commit();

            return redirect()->route('recepcion-materia-prima')
                ->with('success', 'Materia prima recibida exitosamente. Registro creado en materia_prima con ID: ' . $nextId);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al recibir materia prima: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Sincroniza envíos desde la API de Trazabilidad
     */
    public function syncEnvios()
    {
        try {
            // Ejecutar el comando de sincronización
            \Artisan::call('trazabilidad:sync-envios');
            $output = \Artisan::output();

            return redirect()->route('recepcion-materia-prima')
                ->with('success', 'Sincronización de envíos completada. ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('recepcion-materia-prima')
                ->with('error', 'Error al sincronizar envíos: ' . $e->getMessage());
        }
    }
}

