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
            $table->string('modo_visualizacion')->default('scroll')->after('auto_scroll_pausa');
            $table->integer('paginacion_cantidad')->default(5)->after('modo_visualizacion');
            $table->integer('paginacion_tiempo')->default(10)->after('paginacion_cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['modo_visualizacion', 'paginacion_cantidad', 'paginacion_tiempo']);
        });
    }
};
