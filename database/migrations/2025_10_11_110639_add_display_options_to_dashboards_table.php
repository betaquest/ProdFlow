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
            $table->boolean('mostrar_logotipo')->default(true)->after('activo');
            $table->boolean('mostrar_reloj')->default(true)->after('mostrar_logotipo');
            $table->string('color_fondo')->nullable()->after('mostrar_reloj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['mostrar_logotipo', 'mostrar_reloj', 'color_fondo']);
        });
    }
};
