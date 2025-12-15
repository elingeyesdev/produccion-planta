<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use App\Models\RawMaterialBase;
use App\Models\RawMaterial;
use App\Models\Supplier;

class SyncTrazabilidadEnvios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trazabilidad:sync-envios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza envíos de la API de Trazabilidad y procesa recepciones automáticas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización de envíos desde API de Trazabilidad...');

        $apiUrl = env('TRAZABILIDAD_API_URL');
        
        if (!$apiUrl) {
            $this->error('TRAZABILIDAD_API_URL no está configurada en .env');
            return 1;
        }

        try {
            // Obtener todos los envíos de la API
            $response = Http::timeout(30)
                ->get(rtrim($apiUrl, '/') . '/public/envios/all');

            if (!$response->successful()) {
                $this->error('Error al obtener envíos de la API: ' . $response->status());
                Log::error('Error al obtener envíos de Trazabilidad API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return 1;
            }

            $envios = $response->json();

            if (!is_array($envios)) {
                $this->error('La API no devolvió un array válido');
                return 1;
            }

            $this->info('Encontrados ' . count($envios) . ' envíos en la API');

            $procesados = 0;
            $actualizados = 0;
            $errores = 0;

            foreach ($envios as $envio) {
                // Solo procesar envíos entregados que tengan numero_solicitud
                if ($envio['estado'] !== 'Entregado' || empty($envio['numero_solicitud'])) {
                    continue;
                }

                $numeroSolicitud = $envio['numero_solicitud'];

                // Buscar la solicitud de materia prima por numero_solicitud
                $materialRequest = MaterialRequest::where('numero_solicitud', $numeroSolicitud)
                    ->with('details.material')
                    ->first();

                if (!$materialRequest) {
                    $this->warn("No se encontró solicitud con número: {$numeroSolicitud}");
                    continue;
                }

                // Verificar si ya fue procesada (todos los detalles tienen cantidad_aprobada >= cantidad_solicitada)
                $todosCompletos = true;
                foreach ($materialRequest->details as $detail) {
                    $cantidadAprobada = $detail->cantidad_aprobada ?? 0;
                    $cantidadSolicitada = $detail->cantidad_solicitada ?? 0;
                    if ($cantidadAprobada < $cantidadSolicitada) {
                        $todosCompletos = false;
                        break;
                    }
                }

                if ($todosCompletos) {
                    $this->info("Solicitud {$numeroSolicitud} ya está completamente recepcionada, omitiendo...");
                    continue;
                }

                // Procesar la recepción automática
                try {
                    DB::beginTransaction();

                    $this->info("Procesando recepción automática para solicitud: {$numeroSolicitud}");

                    // Obtener o crear un proveedor genérico para recepciones automáticas
                    $proveedor = Supplier::where('razon_social', 'like', '%Sistema Automático%')
                        ->orWhere('razon_social', 'like', '%Recepción Automática%')
                        ->first();

                    if (!$proveedor) {
                        // Crear proveedor genérico
                        $maxProveedorId = DB::table('proveedor')->max('proveedor_id') ?? 0;
                        if ($maxProveedorId > 0) {
                            DB::statement("SELECT setval('proveedor_seq', {$maxProveedorId}, true)");
                        }
                        $proveedorNextId = DB::selectOne("SELECT nextval('proveedor_seq') as id")->id;

                        $proveedorId = DB::selectOne("
                            INSERT INTO proveedor (proveedor_id, razon_social, nombre_comercial, activo)
                            VALUES (?, ?, ?, ?)
                            RETURNING proveedor_id
                        ", [
                            $proveedorNextId,
                            'Sistema Automático - Recepción API',
                            'Recepción Automática',
                            true
                        ])->proveedor_id;

                        $proveedor = Supplier::find($proveedorId);
                    }

                    // Procesar cada detalle de la solicitud
                    foreach ($materialRequest->details as $detail) {
                        $cantidadAprobada = $detail->cantidad_aprobada ?? 0;
                        $cantidadSolicitada = $detail->cantidad_solicitada ?? 0;
                        $cantidadPendiente = $cantidadSolicitada - $cantidadAprobada;

                        if ($cantidadPendiente <= 0) {
                            continue; // Ya está recepcionado completamente
                        }

                        $materialBase = $detail->material;
                        if (!$materialBase) {
                            $this->warn("Material base no encontrado para detalle ID: {$detail->detalle_id}");
                            continue;
                        }

                        // Crear registro en materia_prima
                        $maxMateriaPrimaId = DB::table('materia_prima')->max('materia_prima_id') ?? 0;
                        if ($maxMateriaPrimaId > 0) {
                            DB::statement("SELECT setval('materia_prima_seq', {$maxMateriaPrimaId}, true)");
                        }
                        $materiaPrimaNextId = DB::selectOne("SELECT nextval('materia_prima_seq') as id")->id;

                        $fechaRecepcion = $envio['fecha_entrega'] ?? $envio['fecha_creacion'] ?? now()->toDateString();

                        $rawMaterialId = DB::selectOne("
                            INSERT INTO materia_prima (
                                materia_prima_id, material_id, proveedor_id, lote_proveedor, 
                                numero_factura, fecha_recepcion, fecha_vencimiento, 
                                cantidad, cantidad_disponible, conformidad_recepcion, observaciones
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            RETURNING materia_prima_id
                        ", [
                            $materiaPrimaNextId,
                            $materialBase->material_id,
                            $proveedor->proveedor_id,
                            'AUTO-' . $numeroSolicitud,
                            'AUTO-' . $numeroSolicitud,
                            $fechaRecepcion,
                            null, // fecha_vencimiento
                            $cantidadPendiente,
                            $cantidadPendiente,
                            true, // conformidad_recepcion = true
                            "Recepción automática desde API Trazabilidad - Envío ID: {$envio['id']}"
                        ])->materia_prima_id;

                        // Actualizar cantidad disponible en materia prima base
                        $previousBalance = $materialBase->cantidad_disponible ?? 0;
                        $materialBase->cantidad_disponible = $previousBalance + $cantidadPendiente;
                        $materialBase->save();

                        // Actualizar cantidad_aprobada en el detalle
                        $detail->cantidad_aprobada = $cantidadSolicitada; // Completar la recepción
                        $detail->save();

                        // Registrar en log de movimientos
                        $maxLogId = DB::table('registro_movimiento_material')->max('registro_id') ?? 0;
                        if ($maxLogId > 0) {
                            DB::statement("SELECT setval('registro_movimiento_material_seq', {$maxLogId}, true)");
                        }
                        $logNextId = DB::selectOne("SELECT nextval('registro_movimiento_material_seq') as id")->id;

                        // Obtener el primer operador activo o usar null
                        $operadorId = DB::table('operador')
                            ->where('activo', true)
                            ->value('operador_id') ?? null;

                        DB::selectOne("
                            INSERT INTO registro_movimiento_material (
                                registro_id, material_id, tipo_movimiento_id, operador_id, 
                                cantidad, saldo_anterior, saldo_nuevo, descripcion
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            RETURNING registro_id
                        ", [
                            $logNextId,
                            $materialBase->material_id,
                            1, // tipo_movimiento_id = 1 (Entrada)
                            $operadorId, // operador_id (puede ser null)
                            $cantidadPendiente,
                            $previousBalance,
                            $materialBase->cantidad_disponible,
                            "Recepción automática desde API Trazabilidad - Solicitud: {$numeroSolicitud} - Envío ID: {$envio['id']}"
                        ]);

                        $this->info("  ✓ Material {$materialBase->nombre}: +{$cantidadPendiente} unidades");
                    }

                    DB::commit();
                    $procesados++;
                    $this->info("✓ Solicitud {$numeroSolicitud} procesada exitosamente");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $errores++;
                    $this->error("Error al procesar solicitud {$numeroSolicitud}: " . $e->getMessage());
                    Log::error('Error al procesar recepción automática', [
                        'numero_solicitud' => $numeroSolicitud,
                        'envio_id' => $envio['id'] ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->info("\n=== Resumen ===");
            $this->info("Procesados: {$procesados}");
            $this->info("Errores: {$errores}");
            $this->info("Total envíos revisados: " . count($envios));

            return 0;

        } catch (\Exception $e) {
            $this->error('Error general: ' . $e->getMessage());
            Log::error('Error en sincronización de envíos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
