<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudApoyo extends Model
{
    protected $table = 'bienestar.solicitud_apoyo';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_usuario_revisa',
        'tipo_apoyo',
        'motivo',
        'estado',
        'fecha_envio',
        'fecha_resolucion',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
            'fecha_resolucion' => 'datetime',
        ];
    }

    /**
     * R14: ESTUDIANTE (0,N) envía (1,1) SOLICITUD_APOYO
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    /**
     * R15: USUARIO (0,N) revisa (0,1) SOLICITUD_APOYO
     */
    public function usuarioRevisa(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_revisa', 'id_usuario');
    }

    /**
     * R16: SOLICITUD_APOYO (1,N) adjunta (1,1) DOCUMENTO_ADJUNTO
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoAdjunto::class, 'id_solicitud', 'id_solicitud');
    }

    // Scopes
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', ['enviada', 'en_revision']);
    }

    public function scopeAprobadas(Builder $query): Builder
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopePorTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_apoyo', $tipo);
    }
}
