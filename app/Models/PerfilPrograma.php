<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilPrograma extends Model
{
    protected $table = 'perfiles_programa';

    protected $fillable = [
        'nombre',
        'descripcion',
        'configuracion',
        'activo',
        'predeterminado',
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
        'predeterminado' => 'boolean',
    ];

    /**
     * Relación con programas
     */
    public function programas()
    {
        return $this->hasMany(Programa::class, 'perfil_programa_id');
    }

    /**
     * Obtener las fases ordenadas según la configuración del perfil
     */
    public function getFasesOrdenadas()
    {
        if (!$this->configuracion || !isset($this->configuracion['fases'])) {
            return collect();
        }

        return collect($this->configuracion['fases'])
            ->sortBy('orden')
            ->map(function ($faseConfig) {
                return [
                    'fase' => Fase::find($faseConfig['fase_id']),
                    'area_id' => $faseConfig['area_id'],
                    'orden' => $faseConfig['orden'],
                    'requiere_aprobacion' => $faseConfig['requiere_aprobacion'] ?? true,
                ];
            });
    }

    /**
     * Obtener solo los IDs de las fases configuradas
     */
    public function getFasesIds()
    {
        if (!$this->configuracion || !isset($this->configuracion['fases'])) {
            return [];
        }

        return collect($this->configuracion['fases'])
            ->sortBy('orden')
            ->pluck('fase_id')
            ->toArray();
    }

    /**
     * Obtener el área asignada para una fase específica en este perfil
     */
    public function getAreaParaFase($faseId)
    {
        if (!$this->configuracion || !isset($this->configuracion['fases'])) {
            return null;
        }

        $faseConfig = collect($this->configuracion['fases'])
            ->firstWhere('fase_id', $faseId);

        return $faseConfig['area_id'] ?? null;
    }

    /**
     * Obtener la primera fase del perfil
     */
    public function getPrimeraFase()
    {
        $fasesOrdenadas = $this->getFasesOrdenadas();

        if ($fasesOrdenadas->isEmpty()) {
            return null;
        }

        return $fasesOrdenadas->first();
    }

    /**
     * Obtener la siguiente fase según el orden del perfil (NO el orden global de fases)
     */
    public function getSiguienteFase($faseIdActual)
    {
        if (!$this->configuracion || !isset($this->configuracion['fases'])) {
            return null;
        }

        // Encontrar el orden de la fase actual en el perfil
        $faseActualConfig = collect($this->configuracion['fases'])
            ->firstWhere('fase_id', $faseIdActual);

        if (!$faseActualConfig) {
            return null;
        }

        $ordenActual = $faseActualConfig['orden'];

        // Buscar la siguiente fase en el perfil por orden
        $siguienteFaseConfig = collect($this->configuracion['fases'])
            ->sortBy('orden')
            ->firstWhere('orden', '>', $ordenActual);

        if (!$siguienteFaseConfig) {
            return null;
        }

        // Retornar en el mismo formato que getPrimeraFase()
        return [
            'fase' => Fase::find($siguienteFaseConfig['fase_id']),
            'area_id' => $siguienteFaseConfig['area_id'],
            'orden' => $siguienteFaseConfig['orden'],
            'requiere_aprobacion' => $siguienteFaseConfig['requiere_aprobacion'] ?? true,
        ];
    }

    /**
     * Verificar si hay una siguiente fase después de la dada
     */
    public function tieneSiguienteFase($faseIdActual)
    {
        return $this->getSiguienteFase($faseIdActual) !== null;
    }

    /**
     * Scope para obtener solo perfiles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener el perfil predeterminado
     */
    public function scopePredeterminado($query)
    {
        return $query->where('predeterminado', true)->where('activo', true);
    }
}
