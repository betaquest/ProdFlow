<?php

namespace Database\Seeders;

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
            'programas.reiniciar',
            'programas.ver_historial_reinicios',
            'programas.restaurar_avances',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar todos los permisos al rol Administrador
        $adminRole = Role::where('name', 'Administrador')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            $this->command->info('✓ Permisos de programas asignados al rol Administrador');
        } else {
            $this->command->warn('⚠ No se encontró el rol Administrador');
        }

        // Buscar el rol de Ingenieria (sin tilde)
        $ingenieriaRole = Role::where('name', 'Ingenieria')->first();

        if ($ingenieriaRole) {
            // Asignar todos los permisos de programas al rol de Ingenieria
            $ingenieriaRole->givePermissionTo($permissions);

            $this->command->info('✓ Permisos de programas asignados al rol Ingenieria');
        } else {
            $this->command->warn('⚠ No se encontró el rol Ingenieria');
        }

        // Asignar permisos al rol Captura (incluyendo ver reportes)
        $capturaRole = Role::where('name', 'Captura')->first();

        if ($capturaRole) {
            $capturaRole->givePermissionTo([
                'programas.ver',
                'programas.crear',
                'programas.editar',
                'programas.ver_reportes',
            ]);

            $this->command->info('✓ Permisos de programas asignados al rol Captura');
        } else {
            $this->command->warn('⚠ No se encontró el rol Captura');
        }

        // Remover permiso ver_reportes de roles que NO deberían tenerlo
        $rolessinReportes = [
            'Instalacion',
            'Finalizado',
            'Ensamblado',
            'Corte',
            'Abastecimiento',
            'Completado',  // Por si existe este rol antiguo
            'Armado',      // Por si existe este rol antiguo
            'Entrega',     // Agregar más roles si es necesario
        ];

        foreach ($rolessinReportes as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->revokePermissionTo('programas.ver_reportes');
                $this->command->info("✓ Permiso ver_reportes removido del rol {$roleName}");
            }
        }

    }
}
