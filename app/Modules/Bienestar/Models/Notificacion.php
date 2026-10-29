<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'bienestar.notificacion';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_caso',
        'titulo',
        'mensaje',
        'canal',
        'fecha_envio',
        'leida',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha_envio' => 'datetime',
        ];
    }

    /**
     * R21: USUARIO (0,N) recibe (1,1) NOTIFICACION
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R22: CASO_BIENESTAR (0,N) dispara (0,1) NOTIFICACION (Nullable)
     */
    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoBienestar::class, 'id_caso', 'id_caso');
    }

    // Scopes
    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->where('leida', false);
    }

    public function scopeParaUsuario(Builder $query, int $idUsuario): Builder
    {
        return $query->where('id_usuario', $idUsuario)->orderBy('fecha_envio', 'desc');
    }
}
