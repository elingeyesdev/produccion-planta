<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcesoSeeder extends Seeder
{
    /**
     * Seed procesos
     */
    public function run(): void
    {
        $procesos = [
            [
                'proceso_id' => 1,
                'codigo' => 'PREPARACION',
                'nombre' => 'Preparación de Materias Primas',
                'descripcion' => 'Proceso de preparación y mezcla inicial de materias primas',
                'activo' => true,
            ],
            [
                'proceso_id' => 2,
                'codigo' => 'MEZCLADO',
                'nombre' => 'Mezclado',
                'descripcion' => 'Proceso de mezclado de componentes',
                'activo' => true,
            ],
            [
                'proceso_id' => 3,
                'codigo' => 'EXTRUSION',
                'nombre' => 'Extrusión',
                'descripcion' => 'Proceso de extrusión del material',
                'activo' => true,
            ],
            [
                'proceso_id' => 4,
                'codigo' => 'MOLDEO',
                'nombre' => 'Moldeo',
                'descripcion' => 'Proceso de moldeo del producto',
                'activo' => true,
            ],
            [
                'proceso_id' => 5,
                'codigo' => 'SECADO',
                'nombre' => 'Secado',
                'descripcion' => 'Proceso de secado del producto',
                'activo' => true,
            ],
            [
                'proceso_id' => 6,
                'codigo' => 'TRATAMIENTO',
                'nombre' => 'Tratamiento Térmico',
                'descripcion' => 'Proceso de tratamiento térmico',
                'activo' => true,
            ],
            [
                'proceso_id' => 7,
                'codigo' => 'ENVASADO',
                'nombre' => 'Envasado',
                'descripcion' => 'Proceso de envasado del producto final',
                'activo' => true,
            ],
            [
                'proceso_id' => 8,
                'codigo' => 'ETIQUETADO',
                'nombre' => 'Etiquetado',
                'descripcion' => 'Proceso de etiquetado de productos',
                'activo' => true,
            ],
            [
                'proceso_id' => 9,
                'codigo' => 'EMPAQUETADO',
                'nombre' => 'Empaquetado',
                'descripcion' => 'Proceso de empaquetado final',
                'activo' => true,
            ],
            [
                'proceso_id' => 10,
                'codigo' => 'CONTROL_CALIDAD',
                'nombre' => 'Control de Calidad',
                'descripcion' => 'Proceso de inspección y control de calidad',
                'activo' => true,
            ],
        ];

        foreach ($procesos as $proceso) {
            DB::table('proceso')->updateOrInsert(
                ['proceso_id' => $proceso['proceso_id']],
                $proceso
            );
        }
    }
}

