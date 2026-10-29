<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Derivacion extends Model
{
    protected $table = 'bienestar.derivacion';
    protected $primaryKey = 'id_derivacion';
    public $timestamps = false;

    protected $fillable = [
        'id_caso',
        'id_especialista',
        'id_usuario_deriva',
        'motivo',
        'estado',
        'fecha_derivacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_derivacion' => 'datetime',
        ];
    }

    /**
     * R10: USUARIO (0,N) deriva (1,1) DERIVACION
     */
    public function usuarioDeriva(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_deriva', 'id_usuario');
    }

    /**
     * R11: CASO_BIENESTAR (0,N) se deriva (1,1) DERIVACION
     */
    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoBienestar::class, 'id_caso', 'id_caso');
    }

    /**
     * R12: ESPECIALISTA (0,N) recibe (1,1) DERIVACION
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'id_especialista', 'id_especialista');
    }

    /**
     * R13: DERIVACION (0,N) tiene (1,1) SEGUIMIENTO
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'id_derivacion', 'id_derivacion')->orderBy('fecha_sesion', 'desc');
    }

    // Scopes
    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorEspecialista(Builder $query, int $idEspecialista): Builder
    {
        return $query->where('id_especialista', $idEspecialista);
    }
}
