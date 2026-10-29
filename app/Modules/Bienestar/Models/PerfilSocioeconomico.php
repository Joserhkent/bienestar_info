<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilSocioeconomico extends Model
{
    protected $table = 'bienestar.perfil_socioeconomico';
    protected $primaryKey = 'id_perfil';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'clasificacion_sisfoh',
        'violencia_familiar',
        'situacion_orfandad',
        'es_sustento_familiar',
        'nro_dependientes',
        'observacion',
        'fecha_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'violencia_familiar' => 'boolean',
            'es_sustento_familiar' => 'boolean',
            'nro_dependientes' => 'integer',
            // Cifrado AES-256 para observaciones confidenciales de la trabajadora social
            'observacion' => 'encrypted',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    /**
     * R04: ESTUDIANTE (0,1) tiene (1,1) PERFIL_SOCIOECONOMICO
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    // Scopes de Visibilidad y Confidencialidad
    public function scopeVulnerables(Builder $query): Builder
    {
        return $query->where('clasificacion_sisfoh', 'pobre_extremo')
            ->orWhere('es_sustento_familiar', true)
            ->orWhere('situacion_orfandad', '!=', 'ninguna');
    }

    /**
     * Devuelve solo columnas no sensibles para vistas administrativas generales
     */
    public function scopeDatosBasicos(Builder $query): Builder
    {
        return $query->select([
            'id_perfil',
            'id_estudiante',
            'clasificacion_sisfoh',
            'situacion_orfandad',
            'nro_dependientes',
            'fecha_actualizacion',
        ]);
    }
}
