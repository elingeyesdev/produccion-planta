<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoMovimientoSeeder extends Seeder
{
    /**
     * Seed tipos de movimiento
     */
    public function run(): void
    {
        $tiposMovimiento = [
            [
                'tipo_movimiento_id' => 1,
                'codigo' => 'ENTRADA',
                'nombre' => 'Entrada de Material',
                'afecta_stock' => true,
                'es_entrada' => true,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 2,
                'codigo' => 'SALIDA',
                'nombre' => 'Salida de Material',
                'afecta_stock' => true,
                'es_entrada' => false,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 3,
                'codigo' => 'AJUSTE_INV',
                'nombre' => 'Ajuste de Inventario',
                'afecta_stock' => true,
                'es_entrada' => false,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 4,
                'codigo' => 'CONSUMO',
                'nombre' => 'Consumo en Producción',
                'afecta_stock' => true,
                'es_entrada' => false,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 5,
                'codigo' => 'DEVOLUCION',
                'nombre' => 'Devolución de Material',
                'afecta_stock' => true,
                'es_entrada' => true,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 6,
                'codigo' => 'PERDIDA',
                'nombre' => 'Pérdida de Material',
                'afecta_stock' => true,
                'es_entrada' => false,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 7,
                'codigo' => 'TRANSFERENCIA',
                'nombre' => 'Transferencia entre Almacenes',
                'afecta_stock' => false,
                'es_entrada' => false,
                'activo' => true,
            ],
            [
                'tipo_movimiento_id' => 8,
                'codigo' => 'VENCIMIENTO',
                'nombre' => 'Material Vencido',
                'afecta_stock' => true,
                'es_entrada' => false,
                'activo' => true,
            ],
        ];

        foreach ($tiposMovimiento as $tipo) {
            DB::table('tipo_movimiento')->updateOrInsert(
                ['tipo_movimiento_id' => $tipo['tipo_movimiento_id']],
                $tipo
            );
        }
    }
}

