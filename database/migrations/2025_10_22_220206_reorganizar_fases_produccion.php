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
        // Orden deseado:
        // 1. Ingenieria
        // 2. Captura
        // 3. Abastecimiento
        // 4. Corte
        // 5. Enchape (antes: Ensamblado)
        // 6. Entrega (nueva)
        // 7. Armado (nueva)
        // 8. Instalacion
        // 9. Completado (antes: Finalizacion)

        // 1. Renombrar "Ensamblado" a "Enchape" (ID 5)
        DB::table('fases')
            ->where('id', 5)
            ->update(['nombre' => 'Enchape']);

        // 2. Renombrar "Finalizacion" a "Completado" (ID 7)
        DB::table('fases')
            ->where('id', 7)
            ->update(['nombre' => 'Completado']);

        // 3. Reordenar las fases existentes para hacer espacio
        // Instalacion (ID 6) pasa de orden 6 a orden 8
        DB::table('fases')
            ->where('id', 6)
            ->update(['orden' => 8]);

        // Completado (ID 7) pasa de orden 7 a orden 9
        DB::table('fases')
            ->where('id', 7)
            ->update(['orden' => 9]);

        // 4. Insertar fase "Entrega" en orden 6
        DB::table('fases')->insert([
            'nombre' => 'Entrega',
            'alias' => null,
            'orden' => 6,
            'requiere_aprobacion' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Insertar fase "Armado" en orden 7
        DB::table('fases')->insert([
            'nombre' => 'Armado',
            'alias' => null,
            'orden' => 7,
            'requiere_aprobacion' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las nuevas fases
        DB::table('fases')->where('nombre', 'Entrega')->delete();
        DB::table('fases')->where('nombre', 'Armado')->delete();

        // Restaurar nombres originales
        DB::table('fases')
            ->where('id', 5)
            ->update(['nombre' => 'Ensamblado']);

        DB::table('fases')
            ->where('id', 7)
            ->update(['nombre' => 'Finalizacion']);

        // Restaurar orden original
        DB::table('fases')
            ->where('id', 6)
            ->update(['orden' => 6]);

        DB::table('fases')
            ->where('id', 7)
            ->update(['orden' => 7]);
    }
};
