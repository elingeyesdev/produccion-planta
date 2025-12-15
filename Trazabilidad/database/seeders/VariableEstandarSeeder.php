<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariableEstandarSeeder extends Seeder
{
    /**
     * Seed variables estándar
     */
    public function run(): void
    {
        $variables = [
            [
                'variable_id' => 1,
                'codigo' => 'TEMPERATURA',
                'nombre' => 'Temperatura',
                'unidad' => '°C',
                'descripcion' => 'Temperatura del proceso',
                'activo' => true,
            ],
            [
                'variable_id' => 2,
                'codigo' => 'PRESION',
                'nombre' => 'Presión',
                'unidad' => 'PSI',
                'descripcion' => 'Presión del proceso',
                'activo' => true,
            ],
            [
                'variable_id' => 3,
                'codigo' => 'VELOCIDAD',
                'nombre' => 'Velocidad',
                'unidad' => 'RPM',
                'descripcion' => 'Velocidad de la máquina',
                'activo' => true,
            ],
            [
                'variable_id' => 4,
                'codigo' => 'TIEMPO',
                'nombre' => 'Tiempo de Proceso',
                'unidad' => 'min',
                'descripcion' => 'Tiempo de duración del proceso',
                'activo' => true,
            ],
            [
                'variable_id' => 5,
                'codigo' => 'HUMEDAD',
                'nombre' => 'Humedad',
                'unidad' => '%',
                'descripcion' => 'Nivel de humedad',
                'activo' => true,
            ],
            [
                'variable_id' => 6,
                'codigo' => 'PH',
                'nombre' => 'pH',
                'unidad' => 'pH',
                'descripcion' => 'Nivel de acidez/alcalinidad',
                'activo' => true,
            ],
            [
                'variable_id' => 7,
                'codigo' => 'PESO',
                'nombre' => 'Peso',
                'unidad' => 'kg',
                'descripcion' => 'Peso del producto',
                'activo' => true,
            ],
            [
                'variable_id' => 8,
                'codigo' => 'VOLUMEN',
                'nombre' => 'Volumen',
                'unidad' => 'L',
                'descripcion' => 'Volumen del producto',
                'activo' => true,
            ],
            [
                'variable_id' => 9,
                'codigo' => 'DENSIDAD',
                'nombre' => 'Densidad',
                'unidad' => 'g/cm³',
                'descripcion' => 'Densidad del material',
                'activo' => true,
            ],
            [
                'variable_id' => 10,
                'codigo' => 'VISCOSIDAD',
                'nombre' => 'Viscosidad',
                'unidad' => 'cP',
                'descripcion' => 'Viscosidad del fluido',
                'activo' => true,
            ],
            [
                'variable_id' => 11,
                'codigo' => 'CALIDAD',
                'nombre' => 'Calidad Visual',
                'unidad' => 'Escala 1-10',
                'descripcion' => 'Evaluación visual de calidad',
                'activo' => true,
            ],
            [
                'variable_id' => 12,
                'codigo' => 'COLOR',
                'nombre' => 'Color',
                'unidad' => 'Código',
                'descripcion' => 'Código de color del producto',
                'activo' => true,
            ],
        ];

        foreach ($variables as $variable) {
            DB::table('variable_estandar')->updateOrInsert(
                ['variable_id' => $variable['variable_id']],
                $variable
            );
        }
    }
}

