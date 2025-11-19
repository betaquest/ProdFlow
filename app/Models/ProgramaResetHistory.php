<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaResetHistory extends Model
{
    protected $table = 'programa_reset_history';

    protected $fillable = [
        'programa_id',
        'programa_nombre',
        'ejecutado_por',
        'datos_respaldo',
        'total_avances_eliminados',
        'motivo',
    ];

    protected $casts = [
        'datos_respaldo' => 'array',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function ejecutadoPor()
    {
        return $this->belongsTo(User::class, 'ejecutado_por');
    }
}
