<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Lista base de permisos por módulo
        $permissions = [
            // CLIENTES
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // PROYECTOS
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'proyectos.eliminar',

            // PROGRAMAS
            'programas.ver',
            'programas.crear',
            'programas.editar',
            'programas.eliminar',

            // FASES
            'fases.ver',
            'fases.editar',

            // DASHBOARDS
            'dashboards.ver',

            // ABASTECIMIENTO
            'abastecimiento.ver',
            'abastecimiento.crear',
            'abastecimiento.editar',
        ];

        // Crear los permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 🔹 Crear roles
        $roles = [
            'Administrador',
            'Ingenieria',
            'Captura',
            'Abastecimiento',
            'Corte',
            'Ensamblado',
            'Instalacion',
            'Finalizado',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 🔹 Asignar permisos a roles
        $admin = Role::where('name', 'Administrador')->first();
        $admin->syncPermissions(Permission::all());

        // Rol: Ingeniería
        $ingenieria = Role::where('name', 'Ingenieria')->first();
        $ingenieria->syncPermissions([
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.editar',
            'proyectos.eliminar',
            'programas.ver',
        ]);

        // Rol: Captura
        $captura = Role::where('name', 'Captura')->first();
        $captura->syncPermissions([
            'programas.ver',
            'programas.crear',
            'programas.editar',
        ]);

        // Rol: Abastecimiento
        $abastecimiento = Role::where('name', 'Abastecimiento')->first();
        $abastecimiento->syncPermissions([
            'programas.ver',
            'abastecimiento.ver',
            'abastecimiento.crear',
            'abastecimiento.editar',
            'dashboards.ver',
            'fases.ver',
        ]);

        // Roles operativos solo visualizan dashboards o fases
        $rolesBasicos = ['Corte', 'Ensamblado', 'Instalacion', 'Finalizado'];
        foreach ($rolesBasicos as $rol) {
            $r = Role::where('name', $rol)->first();
            $r->syncPermissions(['dashboards.ver', 'fases.ver']);
        }
    }
}
