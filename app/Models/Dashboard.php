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
    ];

    protected $casts = [
        'criterios' => 'array',
        'activo' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug'; // para URLs tipo /dashboards/cocina
    }
}
