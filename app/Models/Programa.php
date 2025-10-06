<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = ['proyecto_id', 'nombre', 'descripcion'];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function avances()
    {
        return $this->hasMany(AvanceFase::class);
    }
}
