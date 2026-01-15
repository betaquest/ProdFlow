<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agregar índices críticos para optimización de rendimiento
     * Solo agrega índices si NO existen ya
     */
    public function up(): void
    {
        // Helper para agregar índice de forma segura
        $safeIndex = function($table, $columns, $indexName) {
            if (!Schema::hasIndex($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($columns) {
                    $t->index($columns);
                });
            }
        };

        // ÍNDICES EN avance_fases (tabla que si tiene estas columnas)
        $safeIndex('avance_fases', 'programa_id', 'avance_fases_programa_id_index');
        $safeIndex('avance_fases', 'fase_id', 'avance_fases_fase_id_index');
        $safeIndex('avance_fases', 'responsable_id', 'avance_fases_responsable_id_index');
        $safeIndex('avance_fases', 'activo', 'avance_fases_activo_index');
        $safeIndex('avance_fases', 'updated_at', 'avance_fases_updated_at_index');
        $safeIndex('avance_fases', ['programa_id', 'fase_id'], 'avance_fases_programa_id_fase_id_index');

        // ÍNDICES EN programas
        $safeIndex('programas', 'proyecto_id', 'programas_proyecto_id_index');
        $safeIndex('programas', 'activo', 'programas_activo_index');

        // ÍNDICES EN proyectos
        $safeIndex('proyectos', 'cliente_id', 'proyectos_cliente_id_index');
        $safeIndex('proyectos', 'activo', 'proyectos_activo_index');

        // ÍNDICES EN fases
        $safeIndex('fases', 'activo', 'fases_activo_index');

        // ÍNDICES EN clientes
        $safeIndex('clientes', 'activo', 'clientes_activo_index');

        // ÍNDICES EN activity_log (sin columnas inexistentes)
        $safeIndex('activity_log', ['subject_type', 'subject_id'], 'activity_log_subject_type_subject_id_index');
        $safeIndex('activity_log', 'created_at', 'activity_log_created_at_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Apenas se revierte si algo salió mal
        $indexes = [
            ['avance_fases', 'avance_fases_programa_id_index'],
            ['avance_fases', 'avance_fases_fase_id_index'],
            ['avance_fases', 'avance_fases_responsable_id_index'],
            ['avance_fases', 'avance_fases_activo_index'],
            ['avance_fases', 'avance_fases_updated_at_index'],
            ['avance_fases', 'avance_fases_programa_id_fase_id_index'],
            ['programas', 'programas_proyecto_id_index'],
            ['programas', 'programas_activo_index'],
            ['proyectos', 'proyectos_cliente_id_index'],
            ['proyectos', 'proyectos_activo_index'],
            ['fases', 'fases_activo_index'],
            ['clientes', 'clientes_activo_index'],
            ['activity_log', 'activity_log_subject_type_subject_id_index'],
            ['activity_log', 'activity_log_created_at_index'],
        ];

        foreach ($indexes as [$table, $index]) {
            if (Schema::hasIndex($table, $index)) {
                Schema::table($table, function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            }
        }
    }
};
