<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonScopes;
use Illuminate\Support\Collection;

class Fase extends Model
{
    use HasCommonScopes;
    protected $fillable = [
        'nombre', 
        'alias', 
        'orden', 
        'area_id', 
        'requiere_aprobacion', 
        'estado', 
        'activo',
        'requiere_comentario_inicio',
        'requiere_comentario_liberacion',
        'requiere_comentario_finalizacion',
    ];

    protected $casts = [
        'requiere_aprobacion' => 'boolean',
        'activo' => 'boolean',
        'requiere_comentario_inicio' => 'boolean',
        'requiere_comentario_liberacion' => 'boolean',
        'requiere_comentario_finalizacion' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'fase_user');
    }

    public function avances()
    {
        return $this->hasMany(AvanceFase::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Obtener la siguiente fase en orden (solo fases activas)
     */
    public function siguienteFase()
    {
        return self::where('orden', '>', $this->orden)
            ->where('activo', true)
            ->orderBy('orden', 'asc')
            ->first();
    }

    /**
     * Obtener la fase anterior en orden (solo fases activas)
     */
    public function faseAnterior()
    {
        return self::where('orden', '<', $this->orden)
            ->where('activo', true)
            ->orderBy('orden', 'desc')
            ->first();
    }

    /**
     * Verificar si la fase anterior está completada para un programa específico
     * OPTIMIZADO: Acepta colección precargada de avances para evitar queries adicionales
     */
    public function puedeAvanzar($programaId, Collection $avances = null): bool
    {
        $faseAnterior = $this->faseAnterior();

        if (!$faseAnterior) {
            return true; // Es la primera fase
        }

        if ($avances) {
            // Usar datos precargados
            $avanceAnterior = $avances->firstWhere(fn($a) => 
                $a->programa_id === $programaId && 
                $a->fase_id === $faseAnterior->id
            );
        } else {
            // Fallback: solo si no tenemos datos precargados
            $avanceAnterior = AvanceFase::where('programa_id', $programaId)
                ->where('fase_id', $faseAnterior->id)
                ->first();
        }

        return $avanceAnterior && $avanceAnterior->estado === 'done';
    }

    /**
     * Determinar el área para esta fase de forma inteligente
     * Prioridad:
     * 1. Si la fase tiene area_id configurado, usar ese
     * 2. Buscar área cuyo nombre coincida exactamente con el nombre de la fase
     * 3. Buscar área cuyo nombre coincida parcialmente con el nombre o alias
     * 4. Buscar el primer usuario con rol que coincida con el nombre de la fase y usar su área
     * 5. Retornar null si no se encuentra
     */
    public function determinarArea(): ?int
    {
        // Prioridad 1: Si ya tiene área asignada
        if ($this->area_id) {
            return $this->area_id;
        }

        // Prioridad 2: Buscar área con nombre exacto
        $area = Area::where('nombre', $this->nombre)->first();
        if ($area) {
            return $area->id;
        }

        // Prioridad 3: Buscar área con nombre parcial (ignorando mayúsculas/minúsculas)
        $area = Area::whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->nombre) . '%'])->first();
        if ($area) {
            return $area->id;
        }

        // También intentar con el alias
        if ($this->alias) {
            $area = Area::whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->alias) . '%'])->first();
            if ($area) {
                return $area->id;
            }
        }

        // Prioridad 4: Buscar usuario con rol que coincida con el nombre de la fase
        $usuario = User::role($this->nombre)->first();
        if ($usuario && $usuario->area_id) {
            return $usuario->area_id;
        }

        // No se encontró área
        return null;
    }
}
