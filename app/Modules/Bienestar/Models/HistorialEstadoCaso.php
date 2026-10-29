<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoCaso extends Model
{
    protected $table = 'bienestar.historial_estado_caso';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_caso',
        'id_usuario',
        'estado_anterior',
        'estado_nuevo',
        'fecha_cambio',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
        ];
    }

    /**
     * R08: CASO_BIENESTAR (1,N) registra cambios (1,1) HISTORIAL_ESTADO_CASO
     */
    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoBienestar::class, 'id_caso', 'id_caso');
    }

    /**
     * R09: USUARIO (0,N) realiza (1,1) HISTORIAL_ESTADO_CASO
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
