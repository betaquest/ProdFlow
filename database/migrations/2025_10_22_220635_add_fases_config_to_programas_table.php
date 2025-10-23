<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            // Guardar array de IDs de fases en el orden configurado
            // Ejemplo: [1, 2, 3, 5, 8] = solo usa esas fases en ese orden
            $table->json('fases_configuradas')->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropColumn('fases_configuradas');
        });
    }
};
