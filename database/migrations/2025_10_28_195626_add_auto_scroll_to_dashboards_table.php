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
            $table->boolean('auto_scroll_activo')->default(false)->after('alerta_antiguedad_dias');
            $table->integer('auto_scroll_velocidad')->default(30)->after('auto_scroll_activo')->comment('Duración del scroll en segundos');
            $table->integer('auto_scroll_pausa')->default(3)->after('auto_scroll_velocidad')->comment('Pausa en segundos al llegar arriba/abajo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['auto_scroll_activo', 'auto_scroll_velocidad', 'auto_scroll_pausa']);
        });
    }
};
