<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaquinaSeeder extends Seeder
{
    /**
     * Seed máquinas
     */
    public function run(): void
    {
        $maquinas = [
            [
                'maquina_id' => 1,
                'codigo' => 'MEZCLADORA_01',
                'nombre' => 'Mezcladora Principal',
                'descripcion' => 'Mezcladora de alta capacidad para materias primas',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 2,
                'codigo' => 'EXTRUSORA_01',
                'nombre' => 'Extrusora de Plástico',
                'descripcion' => 'Máquina extrusora para procesamiento de plástico',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 3,
                'codigo' => 'HORNO_01',
                'nombre' => 'Horno de Secado',
                'descripcion' => 'Horno para secado y tratamiento térmico',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 4,
                'codigo' => 'ENVASADORA_01',
                'nombre' => 'Envasadora Automática',
                'descripcion' => 'Máquina automática para envasado de productos',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 5,
                'codigo' => 'ETIQUETADORA_01',
                'nombre' => 'Etiquetadora',
                'descripcion' => 'Máquina para etiquetado de productos',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 6,
                'codigo' => 'EMPAQUETADORA_01',
                'nombre' => 'Empaquetadora',
                'descripcion' => 'Máquina para empaquetado final de productos',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 7,
                'codigo' => 'MOLINO_01',
                'nombre' => 'Molino de Martillos',
                'descripcion' => 'Molino para trituración de materiales',
                'imagen_url' => null,
                'activo' => true,
            ],
            [
                'maquina_id' => 8,
                'codigo' => 'TAMIZADORA_01',
                'nombre' => 'Tamizadora',
                'descripcion' => 'Máquina para tamizado y clasificación',
                'imagen_url' => null,
                'activo' => true,
            ],
        ];

        foreach ($maquinas as $maquina) {
            DB::table('maquina')->updateOrInsert(
                ['maquina_id' => $maquina['maquina_id']],
                $maquina
            );
        }
    }
}

