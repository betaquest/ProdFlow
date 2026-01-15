<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasCommonScopes;

class AvanceFase extends Model
{
    use LogsActivity, SoftDeletes, HasCommonScopes;

    protected $fillable = [
        'programa_id',
        'fase_id',
        'responsable_id',
        'area_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_liberacion',
        'notas',
        'notas_finalizacion',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_liberacion' => 'datetime',
        'activo' => 'boolean',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function fase()
    {
        return $this->belongsTo(Fase::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Scope para optimizar carga
     */
    public function scopeOptimized($query)
    {
        return $query->with(['programa', 'fase', 'responsable', 'area']);
    }

    /**
     * Scope por programa
     */
    public function scopeByPrograma($query, $programaId)
    {
        return $query->where('programa_id', $programaId);
    }

    /**
     * Scope por fase
     */
    public function scopeByFase($query, $faseId)
    {
        return $query->where('fase_id', $faseId);
    }

    /**
     * Scope para completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('estado', 'done');
    }

    /**
     * Scope para en progreso
     */
    public function scopeInProgress($query)
    {
        return $query->where('estado', 'progress');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'estado',
                'fecha_inicio',
                'fecha_fin',
                'responsable_id',
                'notas_finalizacion'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Avance {$eventName}");
    }
}
