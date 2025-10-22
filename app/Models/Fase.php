<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    protected $fillable = [
        'nombre', 'alias', 'orden', 'requiere_aprobacion', 'estado',
    ];

    protected $casts = [
        'requiere_aprobacion' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'fase_user');
    }

    public function avances()
    {
        return $this->hasMany(AvanceFase::class);
    }

    /**
     * Obtener la siguiente fase en orden
     */
    public function siguienteFase()
    {
        return self::where('orden', '>', $this->orden)
            ->orderBy('orden', 'asc')
            ->first();
    }

    /**
     * Obtener la fase anterior en orden
     */
    public function faseAnterior()
    {
        return self::where('orden', '<', $this->orden)
            ->orderBy('orden', 'desc')
            ->first();
    }

    /**
     * Verificar si la fase anterior está completada para un programa específico
     */
    public function puedeAvanzar($programaId): bool
    {
        $faseAnterior = $this->faseAnterior();

        if (!$faseAnterior) {
            return true; // Es la primera fase
        }

        $avanceAnterior = AvanceFase::where('programa_id', $programaId)
            ->where('fase_id', $faseAnterior->id)
            ->first();

        return $avanceAnterior && $avanceAnterior->estado === 'done';
    }
}
