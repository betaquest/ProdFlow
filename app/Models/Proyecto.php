<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = ['cliente_id', 'nombre', 'descripcion', 'activo', 'finalizado'];

    protected $casts = [
        'activo' => 'boolean',
        'finalizado' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function programas()
    {
        return $this->hasMany(Programa::class);
    }
}
