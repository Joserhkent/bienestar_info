<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HorarioDisponible extends Model
{
    protected $table = 'bienestar.horario_disponible';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'id_especialista',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /**
     * R17: ESPECIALISTA (0,N) ofrece (1,1) HORARIO_DISPONIBLE
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'id_especialista', 'id_especialista');
    }

    /**
     * R18: HORARIO_DISPONIBLE (0,1) se reserva en (1,1) CITA
     */
    public function cita(): HasOne
    {
        return $this->hasOne(Cita::class, 'id_horario', 'id_horario');
    }

    // Scopes
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'disponible');
    }

    public function scopePorFecha(Builder $query, string $fecha): Builder
    {
        return $query->where('fecha', $fecha);
    }
}
