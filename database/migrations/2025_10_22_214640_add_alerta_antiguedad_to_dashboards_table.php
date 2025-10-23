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
            $table->boolean('alerta_antiguedad_activa')->default(false)->after('usar_alias_fases');
            $table->integer('alerta_antiguedad_dias')->default(7)->after('alerta_antiguedad_activa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['alerta_antiguedad_activa', 'alerta_antiguedad_dias']);
        });
    }
};
