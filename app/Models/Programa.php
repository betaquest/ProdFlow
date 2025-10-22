<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = ['proyecto_id', 'nombre', 'descripcion', 'responsable_inicial_id', 'notas', 'activo'];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function avances()
    {
        return $this->hasMany(AvanceFase::class);
    }

    public function responsableInicial()
    {
        return $this->belongsTo(User::class, 'responsable_inicial_id');
    }
}
