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
        'mostrar_reloj',
        'color_fondo',
        'clientes_ids',
        'todos_clientes',
        'fases_ids',
        'todas_fases',
    ];

    protected $casts = [
        'criterios' => 'array',
        'activo' => 'boolean',
        'mostrar_logotipo' => 'boolean',
        'mostrar_reloj' => 'boolean',
        'clientes_ids' => 'array',
        'todos_clientes' => 'boolean',
        'fases_ids' => 'array',
        'todas_fases' => 'boolean',
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
