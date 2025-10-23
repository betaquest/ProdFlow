<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar roles para que coincidan con los nuevos nombres de fases

        // 1. Renombrar "Ensamblado" a "Enchape"
        DB::table('roles')
            ->where('name', 'Ensamblado')
            ->update(['name' => 'Enchape']);

        // 2. Renombrar "Finalizado" a "Completado"
        DB::table('roles')
            ->where('name', 'Finalizado')
            ->update(['name' => 'Completado']);

        // 3. Crear roles para las nuevas fases: Entrega y Armado
        $guardName = 'web'; // Asume que usas el guard 'web'

        // Verificar si ya existe antes de crear
        if (!DB::table('roles')->where('name', 'Entrega')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Entrega',
                'guard_name' => $guardName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('roles')->where('name', 'Armado')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Armado',
                'guard_name' => $guardName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        DB::table('roles')
            ->where('name', 'Enchape')
            ->update(['name' => 'Ensamblado']);

        DB::table('roles')
            ->where('name', 'Completado')
            ->update(['name' => 'Finalizado']);

        // Eliminar los roles nuevos
        DB::table('roles')->where('name', 'Entrega')->delete();
        DB::table('roles')->where('name', 'Armado')->delete();
    }
};
