<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * ORDEN DE EJECUCIÓN:
     * 1. Spatie (roles y permisos)
     * 2. Tablas paramétricas básicas
     * 3. Operadores (usuarios)
     * 4. Productos
     */
    public function run(): void
    {
        // 1. Primero crear roles y permisos de Spatie
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 2. Seeders de tablas paramétricas (en orden de dependencias)
        $this->call([
            UnidadMedidaSeeder::class,
            TipoMovimientoSeeder::class,
            CategoriaMateriaPrimaSeeder::class,
            VariableEstandarSeeder::class,
            MaquinaSeeder::class,
            ProcesoSeeder::class,
        ]);

        // 3. Operadores (después de roles de Spatie)
        $this->call([
            OperadorSeeder::class,
        ]);

        // 4. Productos (después de unidades de medida)
        $this->call([
            ProductoSeeder::class,
        ]);
    }
}
