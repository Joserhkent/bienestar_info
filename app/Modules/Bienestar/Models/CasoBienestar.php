<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasoBienestar extends Model
{
    protected $table = 'bienestar.caso_bienestar';
    protected $primaryKey = 'id_caso';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_tipo_caso',
        'id_usuario_registro',
        'descripcion',
        'estado',
        'prioridad',
        'confidencial',
        'fecha_registro',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'confidencial' => 'boolean',
            'fecha_registro' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    /**
     * R05: ESTUDIANTE (0,N) genera (1,1) CASO_BIENESTAR
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R06: TIPO_CASO (0,N) clasifica (1,1) CASO_BIENESTAR
     */
    public function tipoCaso(): BelongsTo
    {
        return $this->belongsTo(TipoCaso::class, 'id_tipo_caso', 'id_tipo_caso');
    }

    /**
     * R07: USUARIO (0,N) registra (1,1) CASO_BIENESTAR
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registro', 'id_usuario');
    }

    /**
     * R08: CASO_BIENESTAR (1,N) registra cambios (1,1) HISTORIAL_ESTADO_CASO
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstadoCaso::class, 'id_caso', 'id_caso')->orderBy('fecha_cambio', 'desc');
    }

    /**
     * R11: CASO_BIENESTAR (0,N) se deriva (1,1) DERIVACION
     */
    public function derivaciones(): HasMany
    {
        return $this->hasMany(Derivacion::class, 'id_caso', 'id_caso');
    }

    /**
     * R20: CASO_BIENESTAR (0,N) origina (0,1) CITA
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'id_caso', 'id_caso');
    }

    /**
     * R22: CASO_BIENESTAR (0,N) dispara (0,1) NOTIFICACION
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_caso', 'id_caso');
    }

    // Scopes de Confidencialidad y Gestión
    public function scopeAbiertos(Builder $query): Builder
    {
        return $query->whereIn('estado', ['registrado', 'en_atencion', 'derivado', 'en_seguimiento']);
    }

    public function scopeCerrados(Builder $query): Builder
    {
        return $query->where('estado', 'cerrado');
    }

    public function scopePrioritarios(Builder $query): Builder
    {
        return $query->whereIn('prioridad', ['alta', 'urgente']);
    }

    public function scopeNoConfidenciales(Builder $query): Builder
    {
        return $query->where('confidencial', false);
    }
}
