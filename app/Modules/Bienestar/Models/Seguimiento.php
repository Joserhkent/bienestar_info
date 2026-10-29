<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seguimiento extends Model
{
    protected $table = 'bienestar.seguimiento';
    protected $primaryKey = 'id_seguimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_derivacion',
        'fecha_sesion',
        'nota_avance',
        'acuerdos',
        'proxima_fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha_sesion' => 'datetime',
            'proxima_fecha' => 'datetime',
            // Cifrado de notas clínicas y acuerdos confidenciales del especialista
            'nota_avance' => 'encrypted',
            'acuerdos' => 'encrypted',
        ];
    }

    /**
     * R13: DERIVACION (0,N) tiene (1,1) SEGUIMIENTO
     */
    public function derivacion(): BelongsTo
    {
        return $this->belongsTo(Derivacion::class, 'id_derivacion', 'id_derivacion');
    }
}
