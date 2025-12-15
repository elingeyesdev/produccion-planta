<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaMateriaPrimaSeeder extends Seeder
{
    /**
     * Seed categorías de materia prima
     */
    public function run(): void
    {
        $categorias = [
            [
                'categoria_id' => 1,
                'codigo' => 'CAT-GRANOS',
                'nombre' => 'Granos y Cereales',
                'descripcion' => 'Trigo, maíz, arroz, avena, cebada, etc.',
                'activo' => true
            ],
            [
                'categoria_id' => 2,
                'codigo' => 'CAT-LACTEOS',
                'nombre' => 'Lácteos y Derivados',
                'descripcion' => 'Leche, queso, mantequilla, yogurt, suero, etc.',
                'activo' => true
            ],
            [
                'categoria_id' => 3,
                'codigo' => 'CAT-CARNICOS',
                'nombre' => 'Cárnicos y Embutidos',
                'descripcion' => 'Carne de res, cerdo, pollo, pescado, embutidos.',
                'activo' => true
            ],
            [
                'categoria_id' => 4,
                'codigo' => 'CAT-FRUTAS',
                'nombre' => 'Frutas y Verduras',
                'descripcion' => 'Frutas frescas, verduras, hortalizas, tubérculos.',
                'activo' => true
            ],
            [
                'categoria_id' => 5,
                'codigo' => 'CAT-ACEITES',
                'nombre' => 'Aceites y Grasas',
                'descripcion' => 'Aceite vegetal, manteca, margarina, grasas animales.',
                'activo' => true
            ],
            [
                'categoria_id' => 6,
                'codigo' => 'CAT-ESPECIAS',
                'nombre' => 'Especias y Condimentos',
                'descripcion' => 'Sal, pimienta, orégano, comino, salsas, vinagres.',
                'activo' => true
            ],
            [
                'categoria_id' => 7,
                'codigo' => 'CAT-AZUCARES',
                'nombre' => 'Azúcares y Edulcorantes',
                'descripcion' => 'Azúcar blanca, morena, miel, jarabes, edulcorantes.',
                'activo' => true
            ],
            [
                'categoria_id' => 8,
                'codigo' => 'CAT-ADITIVOS',
                'nombre' => 'Aditivos Alimentarios',
                'descripcion' => 'Conservantes, colorantes, saborizantes, estabilizantes.',
                'activo' => true
            ],
            [
                'categoria_id' => 9,
                'codigo' => 'CAT-EMPAQUES',
                'nombre' => 'Empaques y Embalajes',
                'descripcion' => 'Bolsas, cajas, etiquetas, frascos, tapas.',
                'activo' => true
            ],
            [
                'categoria_id' => 10,
                'codigo' => 'CAT-OTROS',
                'nombre' => 'Otros Insumos',
                'descripcion' => 'Insumos que no encajan en las categorías anteriores.',
                'activo' => true
            ],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categoria_materia_prima')->updateOrInsert(
                ['categoria_id' => $categoria['categoria_id']],
                $categoria
            );
        }
    }
}

