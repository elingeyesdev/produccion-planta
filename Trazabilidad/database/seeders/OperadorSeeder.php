<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class OperadorSeeder extends Seeder
{
    /**
     * Seed operadores
     * NOTA: Ya no usamos role_id porque Spatie maneja los roles
     */
    public function run(): void
    {
        $operadores = [
            [
                'operador_id' => 1,
                'nombre' => 'jhair',
                'apellido' => 'aguilar',
                'usuario' => 'jhair',
                'password_hash' => '$2y$12$PsWcWtGV3nuBopkEusDKFup5.T5/FrHW0jeUV2ElAjeRJMa7Jgczq', // password: password
                'email' => 'jhair@gmail.com',
                'activo' => true,
            ],
            [
                'operador_id' => 2,
                'nombre' => 'Admin',
                'apellido' => 'User',
                'usuario' => 'admin',
                'password_hash' => '$2y$12$R5QvNQItfWqSalSFzAoyGeUHA9lAwGyfpw50IgsleDgibPNWFNOby', // password: admin
                'email' => 'admin@admin.com',
                'activo' => true,
            ],
        ];

        foreach ($operadores as $operadorData) {
            // Sincronizar secuencia de PostgreSQL si es necesario
            if (DB::getDriverName() === 'pgsql') {
                $maxId = DB::table('operador')->max('operador_id');
                if ($maxId !== null && $maxId > 0) {
                    DB::statement("SELECT setval('operador_seq', {$maxId}, true)");
                }
            }

            // Crear o actualizar operador
            DB::table('operador')->updateOrInsert(
                ['operador_id' => $operadorData['operador_id']],
                $operadorData
            );

            // Asignar roles de Spatie usando el modelo Operator
            // El modelo Operator debe tener la tabla 'operador' configurada
            try {
                // Buscar el operador usando DB directamente y luego obtener el modelo
                $operadorRecord = DB::table('operador')
                    ->where('operador_id', $operadorData['operador_id'])
                    ->first();
                
                if ($operadorRecord) {
                    // Usar el modelo Operator que debe estar configurado para usar tabla 'operador'
                    // Si el modelo aún usa 'operator', intentar ambos
                    $operador = null;
                    try {
                        // Intentar con el modelo Operator (puede estar usando tabla 'operador' o 'operator')
                        $operador = \App\Models\Operator::where('operador_id', $operadorData['operador_id'])->first();
                    } catch (\Exception $e) {
                        // Si falla, crear una instancia temporal usando el ID
                        $operador = new \App\Models\Operator();
                        $operador->setAttribute('operador_id', $operadorData['operador_id']);
                        $operador->exists = true;
                    }
                    
                    if ($operador) {
                        // Asignar rol de admin a ambos operadores por defecto
                        $adminRole = Role::where('name', 'admin')->first();
                        if ($adminRole) {
                            $operador->syncRoles([$adminRole]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Si el modelo aún no está actualizado, continuar sin asignar roles
                // Los roles se pueden asignar después cuando el modelo esté actualizado
                \Log::warning('No se pudo asignar rol a operador ' . $operadorData['operador_id'] . ': ' . $e->getMessage());
            }
        }
    }
}

