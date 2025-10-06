<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cliente extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nombre',
        'alias',
        'activo',
        'notas',
        'contacto',
        'telefono',
        'rfc',
    ];

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    // 🔹 Accessor opcional para mostrar nombre con alias
    public function getDisplayNameAttribute()
    {
        return $this->alias
            ? "{$this->nombre} ({$this->alias})"
            : $this->nombre;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'alias', 'activo', 'notas', 'contacto', 'telefono', 'rfc'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
