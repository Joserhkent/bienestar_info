<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estudiante extends Model
{
    protected $table = 'bienestar.estudiante';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo_universitario',
        'dni',
        'nombres',
        'apellidos',
        'sexo',
        'fecha_nacimiento',
        'telefono',
        'direccion_actual',
        'zona_procedencia',
        'lugar_procedencia',
        'ciclo_actual',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'ciclo_actual' => 'integer',
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * R02: USUARIO (0,1) identifica a (1,1) ESTUDIANTE
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R04: ESTUDIANTE (0,1) tiene (1,1) PERFIL_SOCIOECONOMICO
     */
    public function perfilSocioeconomico(): HasOne
    {
        return $this->hasOne(PerfilSocioeconomico::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R05: ESTUDIANTE (0,N) genera (1,1) CASO_BIENESTAR
     */
    public function casos(): HasMany
    {
        return $this->hasMany(CasoBienestar::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R14: ESTUDIANTE (0,N) envía (1,1) SOLICITUD_APOYO
     */
    public function solicitudesApoyo(): HasMany
    {
        return $this->hasMany(SolicitudApoyo::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R19: ESTUDIANTE (0,N) agenda (1,1) CITA
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R24: ESTUDIANTE (0,N) presenta (0,N) TIPO_CONDICION
     */
    public function tiposCondicion(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoCondicion::class,
            'bienestar.estudiante_tipo_condicion',
            'id_estudiante',
            'id_tipo_condicion'
        )->using(EstudianteTipoCondicion::class)
         ->withPivot([
             'grado',
             'requiere_aula_accesible',
             'pauta_pedagogica',
             'certificado_conadis',
             'visible_docente',
             'fecha_registro',
         ]);
    }

    /**
     * R25: ESTUDIANTE (0,N) practica (0,N) DEPORTE
     */
    public function deportes(): BelongsToMany
    {
        return $this->belongsToMany(
            Deporte::class,
            'bienestar.estudiante_deporte',
            'id_estudiante',
            'id_deporte'
        )->using(EstudianteDeporte::class)
         ->withPivot([
             'nivel',
             'disponible_seleccion',
         ]);
    }

    // Scopes
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        if (empty($termino)) {
            return $query;
        }

        $termino = trim($termino);

        return $query->where(function ($q) use ($termino) {
            $q->where('nombres', 'ilike', "%{$termino}%")
              ->orWhere('apellidos', 'ilike', "%{$termino}%")
              ->orWhere('codigo_universitario', 'ilike', "%{$termino}%")
              ->orWhere('dni', 'ilike', "%{$termino}%");
        });
    }

    public function scopePorCiclo(Builder $query, int $ciclo): Builder
    {
        return $query->where('ciclo_actual', $ciclo);
    }

    public function scopePorZona(Builder $query, string $zona): Builder
    {
        return $query->where('zona_procedencia', $zona);
    }
}
