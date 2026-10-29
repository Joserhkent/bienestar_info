<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoAdjunto extends Model
{
    protected $table = 'bienestar.documento_adjunto';
    protected $primaryKey = 'id_documento';
    public $timestamps = false;

    protected $fillable = [
        'id_solicitud',
        'tipo_documento',
        'nombre_archivo',
        'ruta_archivo',
        'tamano_kb',
        'fecha_subida',
    ];

    protected function casts(): array
    {
        return [
            'tamano_kb' => 'integer',
            'fecha_subida' => 'datetime',
        ];
    }

    /**
     * R16: SOLICITUD_APOYO (1,N) adjunta (1,1) DOCUMENTO_ADJUNTO
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudApoyo::class, 'id_solicitud', 'id_solicitud');
    }
}
