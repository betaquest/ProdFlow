<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar índices críticos para optimización de rendimiento
     * Ejecutar: php artisan migrate
     */
    public function up(): void
    {
        // ÍNDICES EN avance_fases
        Schema::table('avance_fases', function (Blueprint $table) {
            // Índices simples
            $table->index('programa_id');
            $table->index('fase_id');
            $table->index('responsable_id');
            $table->index('estado');
            $table->index('activo');
            
            // Índices compuestos para búsquedas frecuentes
            $table->index(['programa_id', 'fase_id']);
            $table->index(['programa_id', 'estado']);
            $table->index(['fase_id', 'estado']);
            
            // Índices para ordenamientos
            $table->index('updated_at');
            $table->index('fecha_fin');
        });

        // ÍNDICES EN programas
        Schema::table('programas', function (Blueprint $table) {
            $table->index('proyecto_id');
            $table->index('activo');
            $table->index('perfil_programa_id');
            $table->index('responsable_inicial_id');
            $table->index('creado_por');
            
            // Índices compuestos
            $table->index(['proyecto_id', 'activo']);
            $table->index(['perfil_programa_id', 'activo']);
        });

        // ÍNDICES EN proyectos
        Schema::table('proyectos', function (Blueprint $table) {
            $table->index('cliente_id');
            $table->index('activo');
            $table->index('finalizado');
        });

        // ÍNDICES EN fases
        Schema::table('fases', function (Blueprint $table) {
            $table->index('area_id');
            $table->index('activo');
            $table->index('orden');
            $table->index('estado');
        });

        // ÍNDICES EN clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->index('activo');
        });

        // ÍNDICES EN users
        Schema::table('users', function (Blueprint $table) {
            $table->index('area_id');
            $table->index('fase_id');
            $table->index('active');
        });

        // ÍNDICES EN RELACIONES
        Schema::table('fase_user', function (Blueprint $table) {
            $table->index('fase_id');
            $table->index('user_id');
            $table->unique(['fase_id', 'user_id']);
        });

        // ÍNDICES EN activity_log
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices (rollback)
        Schema::table('avance_fases', function (Blueprint $table) {
            $table->dropIndex('avance_fases_programa_id_index');
            $table->dropIndex('avance_fases_fase_id_index');
            $table->dropIndex('avance_fases_responsable_id_index');
            $table->dropIndex('avance_fases_estado_index');
            $table->dropIndex('avance_fases_activo_index');
            $table->dropIndex('avance_fases_programa_id_fase_id_index');
            $table->dropIndex('avance_fases_programa_id_estado_index');
            $table->dropIndex('avance_fases_fase_id_estado_index');
            $table->dropIndex('avance_fases_updated_at_index');
            $table->dropIndex('avance_fases_fecha_fin_index');
        });

        Schema::table('programas', function (Blueprint $table) {
            $table->dropIndex('programas_proyecto_id_index');
            $table->dropIndex('programas_activo_index');
            $table->dropIndex('programas_perfil_programa_id_index');
            $table->dropIndex('programas_responsable_inicial_id_index');
            $table->dropIndex('programas_creado_por_index');
            $table->dropIndex('programas_proyecto_id_activo_index');
            $table->dropIndex('programas_perfil_programa_id_activo_index');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropIndex('proyectos_cliente_id_index');
            $table->dropIndex('proyectos_activo_index');
            $table->dropIndex('proyectos_finalizado_index');
        });

        Schema::table('fases', function (Blueprint $table) {
            $table->dropIndex('fases_area_id_index');
            $table->dropIndex('fases_activo_index');
            $table->dropIndex('fases_orden_index');
            $table->dropIndex('fases_estado_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_activo_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_area_id_index');
            $table->dropIndex('users_fase_id_index');
            $table->dropIndex('users_active_index');
        });

        Schema::table('fase_user', function (Blueprint $table) {
            $table->dropIndex('fase_user_fase_id_index');
            $table->dropIndex('fase_user_user_id_index');
            $table->dropUnique('fase_user_fase_id_user_id_unique');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_subject_type_subject_id_index');
            $table->dropIndex('activity_log_created_by_index');
            $table->dropIndex('activity_log_created_at_index');
        });
    }
};
