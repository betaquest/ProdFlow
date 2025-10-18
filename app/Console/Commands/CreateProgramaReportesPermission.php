<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreateProgramaReportesPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:create-programa-reportes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea el permiso para ver reportes de programas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissionName = 'programas.ver_reportes';

        // Verificar si el permiso ya existe
        if (Permission::where('name', $permissionName)->exists()) {
            $this->info("El permiso '{$permissionName}' ya existe.");
            return;
        }

        // Crear el permiso
        Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        $this->info("Permiso '{$permissionName}' creado exitosamente.");
        $this->line('');
        $this->line('Ahora puedes asignar este permiso a roles o usuarios usando:');
        $this->line('  - En Filament: Ir a Roles y Permisos');
        $this->line('  - Por código: $role->givePermissionTo("programas.ver_reportes")');
    }
}
