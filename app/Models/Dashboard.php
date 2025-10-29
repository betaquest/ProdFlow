<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
        'criterios',
        'tiempo_actualizacion',
        'mostrar_logotipo',
        'mostrar_titulo',
        'mostrar_reloj',
        'mostrar_estadisticas',
        'mostrar_barra_progreso',
        'ocultar_footer',
        'color_fondo',
        'clientes_ids',
        'todos_clientes',
        'fases_ids',
        'todas_fases',
        'mostrar_solo_en_proceso',
        'orden_programas',
        'ocultar_finalizados_antiguos',
        'ocultar_completamente_finalizados',
        'usar_alias_fases',
        'alerta_antiguedad_activa',
        'alerta_antiguedad_dias',
        'auto_scroll_activo',
        'auto_scroll_velocidad',
        'auto_scroll_pausa',
    ];

    protected $casts = [
        'criterios' => 'array',
        'activo' => 'boolean',
        'mostrar_logotipo' => 'boolean',
        'mostrar_titulo' => 'boolean',
        'mostrar_reloj' => 'boolean',
        'mostrar_estadisticas' => 'boolean',
        'mostrar_barra_progreso' => 'boolean',
        'ocultar_footer' => 'boolean',
        'clientes_ids' => 'array',
        'todos_clientes' => 'boolean',
        'fases_ids' => 'array',
        'todas_fases' => 'boolean',
        'mostrar_solo_en_proceso' => 'boolean',
        'ocultar_finalizados_antiguos' => 'boolean',
        'ocultar_completamente_finalizados' => 'boolean',
        'usar_alias_fases' => 'boolean',
        'alerta_antiguedad_activa' => 'boolean',
        'alerta_antiguedad_dias' => 'integer',
        'auto_scroll_activo' => 'boolean',
        'auto_scroll_velocidad' => 'integer',
        'auto_scroll_pausa' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug'; // para URLs tipo /dashboards/cocina
    }

    // Relación muchos a muchos con clientes
    public function clientes()
    {
        return $this->belongsToMany(\App\Models\Cliente::class, 'dashboard_cliente', 'dashboard_id', 'cliente_id');
    }
}
