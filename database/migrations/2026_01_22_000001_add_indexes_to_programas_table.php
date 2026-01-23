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
            // Índices para mejorar el rendimiento de búsquedas y ordenamiento
            $table->index('activo', 'idx_programas_activo');
            $table->index('updated_at', 'idx_programas_updated_at');
            $table->index('created_at', 'idx_programas_created_at');
            $table->index('perfil_programa_id', 'idx_programas_perfil');
            $table->index('responsable_inicial_id', 'idx_programas_responsable');
            
            // Índice compuesto para filtros comunes
            $table->index(['activo', 'updated_at'], 'idx_programas_activo_updated');
        });

        Schema::table('avance_fases', function (Blueprint $table) {
            // Índices para avance_fases
            $table->index('programa_id', 'idx_avance_programa');
            $table->index('fase_id', 'idx_avance_fase');
            $table->index('estado', 'idx_avance_estado');
            $table->index(['programa_id', 'estado'], 'idx_avance_programa_estado');
            $table->index('updated_at', 'idx_avance_updated_at');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            // Índices para proyectos
            $table->index('cliente_id', 'idx_proyectos_cliente');
            $table->index('activo', 'idx_proyectos_activo');
            $table->index('finalizado', 'idx_proyectos_finalizado');
            $table->index(['activo', 'finalizado'], 'idx_proyectos_activo_finalizado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropIndex('idx_programas_activo');
            $table->dropIndex('idx_programas_updated_at');
            $table->dropIndex('idx_programas_created_at');
            $table->dropIndex('idx_programas_perfil');
            $table->dropIndex('idx_programas_responsable');
            $table->dropIndex('idx_programas_activo_updated');
        });

        Schema::table('avance_fases', function (Blueprint $table) {
            $table->dropIndex('idx_avance_programa');
            $table->dropIndex('idx_avance_fase');
            $table->dropIndex('idx_avance_estado');
            $table->dropIndex('idx_avance_programa_estado');
            $table->dropIndex('idx_avance_updated_at');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropIndex('idx_proyectos_cliente');
            $table->dropIndex('idx_proyectos_activo');
            $table->dropIndex('idx_proyectos_finalizado');
            $table->dropIndex('idx_proyectos_activo_finalizado');
        });
    }
};
