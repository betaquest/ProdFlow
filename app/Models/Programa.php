<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = [
        'proyecto_id',
        'nombre',
        'descripcion',
        'fases_configuradas',
        'responsable_inicial_id',
        'notas',
        'activo'
    ];

    protected $casts = [
        'fases_configuradas' => 'array',
        'activo' => 'boolean',
    ];

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

    /**
     * Obtener las fases configuradas para este programa
     * Si no tiene configuración, retorna todas las fases en orden
     */
    public function getFasesConfiguradas()
    {
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            // Array simple de IDs
            return Fase::whereIn('id', $this->fases_configuradas)
                ->orderBy('orden')
                ->get();
        }

        // Por defecto, retornar todas las fases en orden
        return Fase::orderBy('orden')->get();
    }

    /**
     * Obtener array de IDs de fases configuradas
     */
    public function getFasesConfiguradasIds()
    {
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            // Array simple de IDs
            return $this->fases_configuradas;
        }

        return Fase::orderBy('orden')->pluck('id')->toArray();
    }
}
