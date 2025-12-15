<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\OrderEnvioTracking;

class OrderEnvioTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si hay pedidos y destinos en la base de datos
        $orders = DB::table('pedido_cliente')->pluck('pedido_id')->toArray();
        $destinations = DB::table('destino_pedido')->pluck('destino_id')->toArray();

        if (empty($orders) || empty($destinations)) {
            $this->command->warn('No hay pedidos o destinos en la base de datos. Los seeders de tracking requieren que existan pedidos y destinos primero.');
            $this->command->info('Ejecuta primero los seeders de pedidos y destinos.');
            return;
        }

        // Datos de ejemplo para tracking
        $trackings = [
            [
                'pedido_id' => $orders[0] ?? 1,
                'destino_id' => $destinations[0] ?? 1,
                'envio_id' => 1001,
                'codigo_envio' => 'ENV-' . date('ymd') . '-000001',
                'estado' => 'success',
                'mensaje_error' => null,
                'datos_solicitud' => json_encode([
                    'almacen_destino_id' => 1,
                    'categoria' => 'general',
                    'fecha_estimada_entrega' => date('Y-m-d', strtotime('+7 days')),
                ]),
                'datos_respuesta' => json_encode([
                    'envio_id' => 1001,
                    'codigo' => 'ENV-' . date('ymd') . '-000001',
                    'estado' => 'created',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pedido_id' => $orders[0] ?? 1,
                'destino_id' => isset($destinations[1]) ? $destinations[1] : $destinations[0],
                'envio_id' => 1002,
                'codigo_envio' => 'ENV-' . date('ymd') . '-000002',
                'estado' => 'success',
                'mensaje_error' => null,
                'datos_solicitud' => json_encode([
                    'almacen_destino_id' => 2,
                    'categoria' => 'urgente',
                    'fecha_estimada_entrega' => date('Y-m-d', strtotime('+3 days')),
                ]),
                'datos_respuesta' => json_encode([
                    'envio_id' => 1002,
                    'codigo' => 'ENV-' . date('ymd') . '-000002',
                    'estado' => 'created',
                ]),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'pedido_id' => isset($orders[1]) ? $orders[1] : $orders[0],
                'destino_id' => $destinations[0] ?? 1,
                'envio_id' => null,
                'codigo_envio' => null,
                'estado' => 'pending',
                'mensaje_error' => null,
                'datos_solicitud' => json_encode([
                    'almacen_destino_id' => 1,
                    'categoria' => 'general',
                ]),
                'datos_respuesta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pedido_id' => isset($orders[1]) ? $orders[1] : $orders[0],
                'destino_id' => isset($destinations[1]) ? $destinations[1] : $destinations[0],
                'envio_id' => null,
                'codigo_envio' => null,
                'estado' => 'failed',
                'mensaje_error' => 'Error al conectar con el servicio de envíos',
                'datos_solicitud' => json_encode([
                    'almacen_destino_id' => 2,
                    'categoria' => 'general',
                ]),
                'datos_respuesta' => json_encode([
                    'error' => 'Connection timeout',
                    'message' => 'No se pudo conectar con el servicio',
                ]),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ];

        foreach ($trackings as $tracking) {
            // Verificar que el pedido_id y destino_id existen
            $orderExists = DB::table('pedido_cliente')->where('pedido_id', $tracking['pedido_id'])->exists();
            $destinationExists = DB::table('destino_pedido')->where('destino_id', $tracking['destino_id'])->exists();

            if ($orderExists && $destinationExists) {
                OrderEnvioTracking::updateOrCreate(
                    [
                        'pedido_id' => $tracking['pedido_id'],
                        'destino_id' => $tracking['destino_id'],
                        'codigo_envio' => $tracking['codigo_envio'] ?? null,
                    ],
                    $tracking
                );
            }
        }

        $this->command->info('Seeders de tracking cargados exitosamente!');
    }
}
