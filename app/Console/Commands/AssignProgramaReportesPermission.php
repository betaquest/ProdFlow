<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignProgramaReportesPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:assign-programa-reportes {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna el permiso de ver reportes de programas a un rol específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->argument('role');
        $permissionName = 'programas.ver_reportes';

        // Buscar el rol
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("El rol '{$roleName}' no existe.");

            // Mostrar roles disponibles
            $this->line('');
            $this->line('Roles disponibles:');
            foreach (Role::all() as $availableRole) {
                $this->line("  - {$availableRole->name}");
            }

            return 1;
        }

        // Verificar si ya tiene el permiso
        if ($role->hasPermissionTo($permissionName)) {
            $this->info("El rol '{$roleName}' ya tiene el permiso '{$permissionName}'.");
            return 0;
        }

        // Asignar el permiso
        $role->givePermissionTo($permissionName);

        $this->info("Permiso '{$permissionName}' asignado exitosamente al rol '{$roleName}'.");

        return 0;
    }
}
