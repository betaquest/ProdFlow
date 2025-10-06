<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AvanceFase extends Model
{
    use LogsActivity;

    protected $fillable = [
        'programa_id',
        'fase_id',

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Avance {$eventName}");
    }
}
