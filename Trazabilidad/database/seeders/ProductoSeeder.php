<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Seed productos
     */
    public function run(): void
    {
        $productos = [
            ['producto_id' => 1, 'codigo' => 'CAFE-UNIVALLE-500G', 'nombre' => 'Café Univalle Orgánico 500 g', 'tipo' => 'marca_univalle', 'peso' => 0.5, 'precio_unitario' => 45.00, 'unidad_id' => 1, 'descripcion' => 'Café orgánico marca Univalle', 'activo' => true],
            ['producto_id' => 2, 'codigo' => 'MIEL-UNIVALLE-350G', 'nombre' => 'Miel Univalle Pura 350 g', 'tipo' => 'marca_univalle', 'peso' => 0.35, 'precio_unitario' => 28.50, 'unidad_id' => 1, 'descripcion' => 'Miel pura marca Univalle', 'activo' => true],
            ['producto_id' => 3, 'codigo' => 'GRANOLA-UNIVALLE-750G', 'nombre' => 'Granola Univalle Natural 750 g', 'tipo' => 'marca_univalle', 'peso' => 0.75, 'precio_unitario' => 32.00, 'unidad_id' => 1, 'descripcion' => 'Granola natural marca Univalle', 'activo' => true],
            ['producto_id' => 4, 'codigo' => 'YOGUR-BIO-NATURAL-1L', 'nombre' => 'Yogur Univalle Bio Natural 1 L', 'tipo' => 'marca_univalle', 'peso' => 1.0, 'precio_unitario' => 18.50, 'unidad_id' => 3, 'descripcion' => 'Yogur bio natural marca Univalle', 'activo' => true],
            ['producto_id' => 5, 'codigo' => 'YOGUR-BIO-FRUTILLA-1L', 'nombre' => 'Yogur Univalle Bio Frutilla 1 L', 'tipo' => 'marca_univalle', 'peso' => 1.0, 'precio_unitario' => 19.00, 'unidad_id' => 3, 'descripcion' => 'Yogur bio frutilla marca Univalle', 'activo' => true],
            ['producto_id' => 6, 'codigo' => 'HARINA-INTEGRAL-1KG', 'nombre' => 'Harina Integral Univalle Vital 1 kg', 'tipo' => 'marca_univalle', 'peso' => 1.0, 'precio_unitario' => 15.50, 'unidad_id' => 1, 'descripcion' => 'Harina integral marca Univalle', 'activo' => true],
            ['producto_id' => 7, 'codigo' => 'AVENA-ORGANICA-900G', 'nombre' => 'Avena Univalle Orgánica 900 g', 'tipo' => 'organico', 'peso' => 0.9, 'precio_unitario' => 22.00, 'unidad_id' => 1, 'descripcion' => 'Avena orgánica marca Univalle', 'activo' => true],
            ['producto_id' => 8, 'codigo' => 'CHOCOLATE-AMARGO-100G', 'nombre' => 'Chocolate Amargo Univalle 70% 100 g', 'tipo' => 'marca_univalle', 'peso' => 0.1, 'precio_unitario' => 12.50, 'unidad_id' => 1, 'descripcion' => 'Chocolate amargo 70% marca Univalle', 'activo' => true],
            ['producto_id' => 9, 'codigo' => 'QUINUA-REAL-1KG', 'nombre' => 'Quinua Real Univalle 1 kg', 'tipo' => 'marca_univalle', 'peso' => 1.0, 'precio_unitario' => 38.00, 'unidad_id' => 1, 'descripcion' => 'Quinua real marca Univalle', 'activo' => true],
            ['producto_id' => 10, 'codigo' => 'ARROZ-INTEGRAL-1KG', 'nombre' => 'Arroz Integral Univalle 1 kg', 'tipo' => 'marca_univalle', 'peso' => 1.0, 'precio_unitario' => 16.00, 'unidad_id' => 1, 'descripcion' => 'Arroz integral marca Univalle', 'activo' => true],
            ['producto_id' => 11, 'codigo' => 'ACEITE-COCO-300ML', 'nombre' => 'Aceite de Coco Univalle 300 ml', 'tipo' => 'marca_univalle', 'peso' => 0.3, 'precio_unitario' => 42.00, 'unidad_id' => 3, 'descripcion' => 'Aceite de coco marca Univalle', 'activo' => true],
            ['producto_id' => 12, 'codigo' => 'PAN-INTEGRAL-600G', 'nombre' => 'Pan Integral Univalle 600 g', 'tipo' => 'marca_univalle', 'peso' => 0.6, 'precio_unitario' => 14.50, 'unidad_id' => 1, 'descripcion' => 'Pan integral marca Univalle', 'activo' => true],
            ['producto_id' => 13, 'codigo' => 'FRUTOS-SECOS-MIX-250G', 'nombre' => 'Frutos Secos Univalle Mix 250 g', 'tipo' => 'marca_univalle', 'peso' => 0.25, 'precio_unitario' => 35.00, 'unidad_id' => 1, 'descripcion' => 'Mix de frutos secos marca Univalle', 'activo' => true],
            ['producto_id' => 14, 'codigo' => 'GALLETAS-INTEGRALES-200G', 'nombre' => 'Galletas Integrales Univalle 200 g', 'tipo' => 'marca_univalle', 'peso' => 0.2, 'precio_unitario' => 11.50, 'unidad_id' => 1, 'descripcion' => 'Galletas integrales marca Univalle', 'activo' => true],
            ['producto_id' => 15, 'codigo' => 'SIROPE-AGAVE-250ML', 'nombre' => 'Sirope de Agave Univalle 250 ml', 'tipo' => 'marca_univalle', 'peso' => 0.25, 'precio_unitario' => 24.00, 'unidad_id' => 3, 'descripcion' => 'Sirope de agave marca Univalle', 'activo' => true],
            ['producto_id' => 16, 'codigo' => 'TE-VERDE-20SOBRES', 'nombre' => 'Té Verde Univalle Orgánico 20 sobres', 'tipo' => 'organico', 'peso' => 0.05, 'precio_unitario' => 18.00, 'unidad_id' => 2, 'descripcion' => 'Té verde orgánico marca Univalle', 'activo' => true],
            ['producto_id' => 17, 'codigo' => 'MANTEQUILLA-MANI-350G', 'nombre' => 'Mantequilla de Maní Univalle 350 g', 'tipo' => 'marca_univalle', 'peso' => 0.35, 'precio_unitario' => 26.50, 'unidad_id' => 1, 'descripcion' => 'Mantequilla de maní marca Univalle', 'activo' => true],
            ['producto_id' => 18, 'codigo' => 'LENTEJAS-ORGANICAS-900G', 'nombre' => 'Lentejas Univalle Orgánicas 900 g', 'tipo' => 'organico', 'peso' => 0.9, 'precio_unitario' => 20.00, 'unidad_id' => 1, 'descripcion' => 'Lentejas orgánicas marca Univalle', 'activo' => true],
            ['producto_id' => 19, 'codigo' => 'CEREAL-MAIZ-500G', 'nombre' => 'Cereal de Maíz Univalle 500 g', 'tipo' => 'marca_univalle', 'peso' => 0.5, 'precio_unitario' => 13.50, 'unidad_id' => 1, 'descripcion' => 'Cereal de maíz marca Univalle', 'activo' => true],
            ['producto_id' => 20, 'codigo' => 'PASTA-INTEGRAL-500G', 'nombre' => 'Pasta Integral Univalle 500 g', 'tipo' => 'marca_univalle', 'peso' => 0.5, 'precio_unitario' => 17.00, 'unidad_id' => 1, 'descripcion' => 'Pasta integral marca Univalle', 'activo' => true],
        ];

        foreach ($productos as $producto) {
            DB::table('producto')->updateOrInsert(
                ['producto_id' => $producto['producto_id']],
                $producto
            );
        }
    }
}

