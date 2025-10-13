<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza permisos y roles desde el archivo de configuración permissions.php';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Sincronizando permisos y roles...');

        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Sincronizar permisos
        $this->syncPermissions();

        // 2. Sincronizar roles
        $this->syncRoles();

        // 3. Asignar permisos a roles
        $this->assignPermissionsToRoles();

        $this->info('✅ Sincronización completada exitosamente!');

        return Command::SUCCESS;
    }

    /**
     * Sincroniza permisos desde la configuración
     */
    protected function syncPermissions(): void
    {
        $this->line('');
        $this->info('📋 Sincronizando permisos...');

        $resources = config('permissions.resources', []);
        $permissionsCreated = 0;

        foreach ($resources as $resource => $config) {
            $actions = $config['actions'] ?? [];

            foreach ($actions as $action) {
                $permissionName = "{$resource}.{$action}";

                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);

                if ($permission->wasRecentlyCreated) {
                    $this->comment("  ✓ Permiso creado: {$permissionName}");
                    $permissionsCreated++;
                }
            }
        }

        if ($permissionsCreated > 0) {
            $this->info("  ✅ {$permissionsCreated} permisos nuevos creados");
        } else {
            $this->comment("  ℹ️  Todos los permisos ya existen");
        }
    }

    /**
     * Sincroniza roles desde la configuración
     */
    protected function syncRoles(): void
    {
        $this->line('');
        $this->info('👥 Sincronizando roles...');

        $roles = config('permissions.roles', []);
        $rolesCreated = 0;

        foreach ($roles as $roleName => $config) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            if ($role->wasRecentlyCreated) {
                $this->comment("  ✓ Rol creado: {$roleName}");
                $rolesCreated++;
            }
        }

        if ($rolesCreated > 0) {
            $this->info("  ✅ {$rolesCreated} roles nuevos creados");
        } else {
            $this->comment("  ℹ️  Todos los roles ya existen");
        }
    }

    /**
     * Asigna permisos a roles según la configuración
     */
    protected function assignPermissionsToRoles(): void
    {
        $this->line('');
        $this->info('🔐 Asignando permisos a roles...');

        $rolesConfig = config('permissions.roles', []);

        foreach ($rolesConfig as $roleName => $config) {
            $role = Role::findByName($roleName, 'web');

            $permissions = $config['permissions'] ?? [];

            // Si el rol es Administrador y tiene '*', asignar todos los permisos
            if ($permissions === '*') {
                $allPermissions = Permission::all();
                $role->syncPermissions($allPermissions);
                $this->comment("  ✓ {$roleName}: Todos los permisos asignados");
                continue;
            }

            // Asignar permisos específicos
            if (is_array($permissions) && count($permissions) > 0) {
                $role->syncPermissions($permissions);
                $this->comment("  ✓ {$roleName}: " . count($permissions) . " permisos asignados");
            }
        }

        $this->info("  ✅ Permisos asignados correctamente");
    }
}
