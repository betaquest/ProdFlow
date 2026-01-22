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
            $table->string('paginacion_actualizacion_tipo')->default('por_vuelta')->after('paginacion_tiempo');
            $table->integer('paginacion_actualizacion_vueltas')->default(1)->after('paginacion_actualizacion_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['paginacion_actualizacion_tipo', 'paginacion_actualizacion_vueltas']);
        });
    }
};
