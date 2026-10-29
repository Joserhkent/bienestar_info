<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $table = 'bienestar.reporte';
    protected $primaryKey = 'id_reporte';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tipo_reporte',
        'periodo_inicio',
        'periodo_fin',
        'formato',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'periodo_fin' => 'date',
            'fecha_generacion' => 'datetime',
        ];
    }

    /**
     * R23: USUARIO (0,N) genera (1,1) REPORTE
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
