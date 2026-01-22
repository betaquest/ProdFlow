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
        Schema::table('fases', function (Blueprint $table) {
            $table->boolean('requiere_comentario_inicio')->default(false)->after('activo');
            $table->boolean('requiere_comentario_liberacion')->default(false)->after('requiere_comentario_inicio');
            $table->boolean('requiere_comentario_finalizacion')->default(false)->after('requiere_comentario_liberacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fases', function (Blueprint $table) {
            $table->dropColumn([
                'requiere_comentario_inicio',
                'requiere_comentario_liberacion',
                'requiere_comentario_finalizacion'
            ]);
        });
    }
};
