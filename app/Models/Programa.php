<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCommonScopes;

class Programa extends Model
{
    use SoftDeletes, HasCommonScopes;

    protected $fillable = [
        'proyecto_id',
        'perfil_programa_id',
        'nombre',
        'descripcion',
        'fases_configuradas',
        'responsable_inicial_id',
        'notas',
        'activo',
        'creado_por'
    ];

    protected $casts = [
        'fases_configuradas' => 'array',
        'activo' => 'boolean',
    ];

    protected $appends = ['fase_actual', 'estado_proceso'];

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

    public function perfilPrograma()
    {
        return $this->belongsTo(PerfilPrograma::class, 'perfil_programa_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function resetHistory()
    {
        return $this->hasMany(ProgramaResetHistory::class);
    }

    /**
     * Scope para optimizar carga con eager loading
     */
    public function scopeWithOptimizations($query)
    {
        return $query->with([
            'proyecto:id,nombre,cliente_id',
            'proyecto.cliente:id,nombre',
            'avances' => function ($query) {
                $query->select('id', 'programa_id', 'fase_id', 'estado', 'updated_at')
                      ->with('fase:id,nombre,orden');
            },
            'perfilPrograma:id,nombre',
            'responsableInicial:id,name',
        ]);
    }

    /**     * Obtener las fases configuradas para este programa (solo activas)
     * Prioridad: 1) Perfil asignado, 2) fases_configuradas, 3) Todas las fases activas
     */
    public function getFasesConfiguradas()
    {
        // 1. Si tiene perfil asignado, usar las fases del perfil
        if ($this->perfil_programa_id && $this->perfilPrograma) {
            return $this->perfilPrograma->getFasesOrdenadas()
                ->map(fn($item) => $item['fase'])
                ->filter(); // Remover nulls
        }

        // 2. Si tiene fases configuradas manualmente
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            return Fase::whereIn('id', $this->fases_configuradas)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();
        }

        // 3. Por defecto, todas las fases activas en orden
        return Fase::where('activo', true)->orderBy('orden')->get();
    }

    /**
     * Obtener array de IDs de fases configuradas (solo activas) - OPTIMIZADO
     */
    public function getFasesConfiguradasIds()
    {
        // 1. Si tiene perfil asignado y está precargado
        if ($this->perfil_programa_id && $this->relationLoaded('perfilPrograma') && $this->perfilPrograma) {
            return $this->perfilPrograma->getFasesIds();
        }

        // 2. Si tiene fases configuradas manualmente
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            return $this->fases_configuradas;
        }

        // 3. Fallback: cargar solo si es necesario
        return Fase::where('activo', true)->orderBy('orden')->pluck('id')->toArray();
    }

    /**
     * Obtener el área asignada para una fase específica según el perfil
     */
    public function getAreaParaFase($faseId)
    {
        if ($this->perfil_programa_id && $this->perfilPrograma) {
            return $this->perfilPrograma->getAreaParaFase($faseId);
        }

        // Fallback: usar el área configurada en la fase misma
        $fase = Fase::find($faseId);
        return $fase?->determinarArea();
    }

    /**
     * Obtener la siguiente fase según el perfil o configuración del programa
     */
    public function getSiguienteFase($faseIdActual)
    {
        // 1. Si tiene perfil asignado, usar la siguiente fase del perfil
        if ($this->perfil_programa_id && $this->perfilPrograma) {
            return $this->perfilPrograma->getSiguienteFase($faseIdActual);
        }

        // 2. Si tiene fases configuradas manualmente, buscar la siguiente
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            $fasesConfiguradas = Fase::whereIn('id', $this->fases_configuradas)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            $faseActual = Fase::find($faseIdActual);
            if ($faseActual) {
                $siguienteFase = $fasesConfiguradas->where('orden', '>', $faseActual->orden)->first();
                if ($siguienteFase) {
                    return [
                        'fase' => $siguienteFase,
                        'area_id' => $siguienteFase->determinarArea(),
                    ];
                }
            }
        }

        // 3. Fallback: siguiente fase activa global
        $faseActual = Fase::find($faseIdActual);
        if ($faseActual) {
            $siguienteFase = $faseActual->siguienteFase();
            if ($siguienteFase) {
                return [
                    'fase' => $siguienteFase,
                    'area_id' => $siguienteFase->determinarArea(),
                ];
            }
        }

        return null;
    }

    /**
     * Obtener la fase actual del programa (optimizado para tabla)
     */
    public function getFaseActualAttribute()
    {
        if (!$this->relationLoaded('avances')) {
            return 'N/A';
        }

        // Buscar avance en progreso
        $avanceEnProgreso = $this->avances->firstWhere('estado', 'progress');
        if ($avanceEnProgreso && $avanceEnProgreso->relationLoaded('fase')) {
            return $avanceEnProgreso->fase->nombre;
        }

        // Buscar última fase completada
        $ultimoAvance = $this->avances
            ->where('estado', 'done')
            ->sortByDesc('updated_at')
            ->first();
        
        if ($ultimoAvance && $ultimoAvance->relationLoaded('fase')) {
            return $ultimoAvance->fase->nombre . ' ✓';
        }

        return 'Sin iniciar';
    }

    /**
     * Obtener el estado del proceso (optimizado para tabla)
     */
    public function getEstadoProcesoAttribute()
    {
        if (!$this->relationLoaded('avances')) {
            return '⬜ Sin Iniciar';
        }

        $totalAvances = $this->avances->count();
        
        if ($totalAvances === 0) {
            return '⬜ Sin Iniciar';
        }

        $enProgreso = $this->avances->where('estado', 'progress')->count();
        
        if ($enProgreso > 0) {
            return '⏳ En Progreso';
        }
        
        $completadas = $this->avances->where('estado', 'done')->count();
        
        if ($completadas > 0) {
            return '✅ Avanzando';
        }
        
        return '⬜ Sin Iniciar';
    }
}
