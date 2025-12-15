<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos (usar updateOrCreate para evitar duplicados)
        $permissions = [
            // Paneles
            'ver panel control',
            'ver panel cliente',
            
            // Pedidos
            'crear pedidos',
            'ver mis pedidos',
            'editar mis pedidos',
            'cancelar mis pedidos',
            'gestionar pedidos',
            'aprobar pedidos',
            'rechazar pedidos',
            
            // Materia Prima
            'ver materia prima',
            'solicitar materia prima',
            'recepcionar materia prima',
            'gestionar proveedores',
            
            // Lotes
            'gestionar lotes',
            
            // Procesos
            'gestionar maquinas',
            'gestionar procesos',
            'gestionar variables estandar',
            
            // Certificaciones
            'certificar lotes',
            'ver certificados',
            
            // Almacenes
            'almacenar lotes',
            
            // Administración
            'gestionar usuarios',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Rol: Cliente - Solo puede ver panel cliente, crear/ver/editar/cancelar pedidos y ver certificados
        $clienteRole = Role::updateOrCreate(
            ['name' => 'cliente']
        );
        $clienteRole->syncPermissions([
            'ver panel cliente',
            'crear pedidos',
            'ver mis pedidos',
            'editar mis pedidos',
            'cancelar mis pedidos',
            'ver certificados',
        ]);

        // Rol: Operador - Puede hacer todo EXCEPTO gestionar usuarios
        $operadorRole = Role::updateOrCreate(
            ['name' => 'operador']
        );
        $operadorRole->syncPermissions([
            'ver panel control',
            'ver materia prima',
            'solicitar materia prima',
            'recepcionar materia prima',
            'gestionar proveedores',
            'gestionar lotes',
            'gestionar maquinas',
            'gestionar procesos',
            'gestionar variables estandar',
            'certificar lotes',
            'ver certificados',
            'almacenar lotes',
            'gestionar pedidos',
            'aprobar pedidos',
            'rechazar pedidos',
        ]);

        // Rol: Admin - Todos los permisos
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin']
        );
        $adminRole->syncPermissions(Permission::all());
    }
}




