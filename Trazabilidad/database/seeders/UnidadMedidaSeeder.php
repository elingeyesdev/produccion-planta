<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadMedidaSeeder extends Seeder
{
    /**
     * Seed unidades de medida
     */
    public function run(): void
    {
        $unidades = [
            [
                'unidad_id' => 1,
                'codigo' => 'KG',
                'nombre' => 'Kilogramo',
                'descripcion' => 'Unidad de masa en el Sistema Internacional',
                'activo' => true,
            ],
            [
                'unidad_id' => 2,
                'codigo' => 'G',
                'nombre' => 'Gramo',
                'descripcion' => 'Unidad de masa equivalente a una milésima de kilogramo',
                'activo' => true,
            ],
            [
                'unidad_id' => 3,
                'codigo' => 'L',
                'nombre' => 'Litro',
                'descripcion' => 'Unidad de volumen en el Sistema Internacional',
                'activo' => true,
            ],
            [
                'unidad_id' => 4,
                'codigo' => 'ML',
                'nombre' => 'Mililitro',
                'descripcion' => 'Unidad de volumen equivalente a una milésima de litro',
                'activo' => true,
            ],
            [
                'unidad_id' => 5,
                'codigo' => 'M',
                'nombre' => 'Metro',
                'descripcion' => 'Unidad de longitud en el Sistema Internacional',
                'activo' => true,
            ],
            [
                'unidad_id' => 6,
                'codigo' => 'CM',
                'nombre' => 'Centímetro',
                'descripcion' => 'Unidad de longitud equivalente a una centésima de metro',
                'activo' => true,
            ],
            [
                'unidad_id' => 7,
                'codigo' => 'UN',
                'nombre' => 'Unidad',
                'descripcion' => 'Unidad de conteo o pieza',
                'activo' => true,
            ],
            [
                'unidad_id' => 8,
                'codigo' => 'M2',
                'nombre' => 'Metro Cuadrado',
                'descripcion' => 'Unidad de área',
                'activo' => true,
            ],
            [
                'unidad_id' => 9,
                'codigo' => 'M3',
                'nombre' => 'Metro Cúbico',
                'descripcion' => 'Unidad de volumen',
                'activo' => true,
            ],
            [
                'unidad_id' => 10,
                'codigo' => 'BOLSA',
                'nombre' => 'Bolsa',
                'descripcion' => 'Unidad de empaque en bolsa',
                'activo' => true,
            ],
        ];

        foreach ($unidades as $unidad) {
            DB::table('unidad_medida')->updateOrInsert(
                ['unidad_id' => $unidad['unidad_id']],
                $unidad
            );
        }
    }
}

