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
        Schema::table('dashboards', function (Blueprint $table) {
            $table->boolean('mostrar_solo_en_proceso')->default(false)->after('todas_fases');
            $table->string('orden_programas')->default('nombre')->after('mostrar_solo_en_proceso');
            $table->boolean('ocultar_finalizados_antiguos')->default(false)->after('orden_programas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['mostrar_solo_en_proceso', 'orden_programas', 'ocultar_finalizados_antiguos']);
        });
    }
};
