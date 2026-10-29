<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    protected $table = 'bienestar.cita';
    protected $primaryKey = 'id_cita';
    public $timestamps = false;

    protected $fillable = [
        'id_horario',
        'id_estudiante',
        'id_caso',
        'tipo_cita',
        'estado',
        'motivo',
        'fecha_solicitud',
        'fecha_confirmacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_confirmacion' => 'datetime',
            // Cifrado de motivo de consulta psicológica / médica
            'motivo' => 'encrypted',
        ];
    }

    /**
     * R18: HORARIO_DISPONIBLE (0,1) se reserva en (1,1) CITA
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(HorarioDisponible::class, 'id_horario', 'id_horario');
    }

    /**
     * R19: ESTUDIANTE (0,N) agenda (1,1) CITA
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R20: CASO_BIENESTAR (0,N) origina (0,1) CITA (Nullable)
     */
    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoBienestar::class, 'id_caso', 'id_caso');
    }

    // Scopes
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', '!=', 'cancelada');
    }

    public function scopeConfirmadas(Builder $query): Builder
    {
        return $query->where('estado', 'confirmada');
    }

    public function scopePorEstudiante(Builder $query, int $idEstudiante): Builder
    {
        return $query->where('id_estudiante', $idEstudiante);
    }
}
