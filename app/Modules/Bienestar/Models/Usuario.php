<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'bienestar.usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'correo',
        'contrasena_hash',
        'estado',
        'fecha_creacion',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'contrasena_hash',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }

    /**
     * R01: ROL (0,N) asigna (1,1) USUARIO
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * R02: USUARIO (0,1) identifica a (1,1) ESTUDIANTE
     */
    public function estudiante(): HasOne
    {
        return $this->hasOne(Estudiante::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R03: USUARIO (0,1) identifica a (1,1) ESPECIALISTA
     */
    public function especialista(): HasOne
    {
        return $this->hasOne(Especialista::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R07: USUARIO (0,N) registra (1,1) CASO_BIENESTAR
     */
    public function casosRegistrados(): HasMany
    {
        return $this->hasMany(CasoBienestar::class, 'id_usuario_registro', 'id_usuario');
    }

    /**
     * R09: USUARIO (0,N) realiza (1,1) HISTORIAL_ESTADO_CASO
     */
    public function historialesRealizados(): HasMany
    {
        return $this->hasMany(HistorialEstadoCaso::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R10: USUARIO (0,N) deriva (1,1) DERIVACION
     */
    public function derivacionesEmitidas(): HasMany
    {
        return $this->hasMany(Derivacion::class, 'id_usuario_deriva', 'id_usuario');
    }

    /**
     * R15: USUARIO (0,N) revisa (0,1) SOLICITUD_APOYO
     */
    public function solicitudesRevisadas(): HasMany
    {
        return $this->hasMany(SolicitudApoyo::class, 'id_usuario_revisa', 'id_usuario');
    }

    /**
     * R21: USUARIO (0,N) recibe (1,1) NOTIFICACION
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R23: USUARIO (0,N) genera (1,1) REPORTE
     */
    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'id_usuario', 'id_usuario');
    }

    // Scopes
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorRol(Builder $query, string $nombreRol): Builder
    {
        return $query->whereHas('rol', function ($q) use ($nombreRol) {
            $q->where('nombre', $nombreRol);
        });
    }
}
