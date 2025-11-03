<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProgramaPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para el recurso 'programas'
        $permissions = [
            'programas.ver',
            'programas.crear',
            'programas.editar',
            'programas.eliminar',
            'programas.ver_reportes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buscar el rol de Ingeniería
        $ingenieriaRole = Role::where('name', 'Ingeniería')->first();

        if ($ingenieriaRole) {
            // Asignar todos los permisos de programas al rol de Ingeniería
            $ingenieriaRole->givePermissionTo($permissions);

            $this->command->info('✓ Permisos de programas asignados al rol Ingeniería');
        } else {
            $this->command->warn('⚠ No se encontró el rol Ingeniería');
        }

        // Opcional: Asignar permisos a otros roles si es necesario
        $roles = [
            'Instalación' => ['programas.ver'],
            'Completado' => ['programas.ver'],
            'Armado' => ['programas.ver'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($rolePermissions);
                $this->command->info("✓ Permisos asignados al rol {$roleName}");
            }
        }
    }
}
