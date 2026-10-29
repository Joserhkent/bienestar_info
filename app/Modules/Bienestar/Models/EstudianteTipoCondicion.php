<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteTipoCondicion extends Pivot
{
    protected $table = 'bienestar.estudiante_tipo_condicion';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_estudiante',
        'id_tipo_condicion',
        'grado',
        'requiere_aula_accesible',
        'pauta_pedagogica',
        'certificado_conadis',
        'visible_docente',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'requiere_aula_accesible' => 'boolean',
            'visible_docente' => 'boolean',
            'fecha_registro' => 'datetime',
            // Cifrado de la pauta pedagógica (Ley 30150 TEA)
            'pauta_pedagogica' => 'encrypted',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function tipoCondicion(): BelongsTo
    {
        return $this->belongsTo(TipoCondicion::class, 'id_tipo_condicion', 'id_tipo_condicion');
    }

    /**
     * Scope estricto para Docente (Ley 30150 TEA):
     * Devuelve ÚNICAMENTE las columnas permitidas para la labor docente.
     * EXCLUYE deliberadamente grado, certificado_conadis y datos clínicos protegidos.
     */
    public function scopeVisibleParaDocente(Builder $query): Builder
    {
        return $query->where('visible_docente', true)
            ->select([
                'id_estudiante',
                'id_tipo_condicion',
                'pauta_pedagogica',
                'fecha_registro',
            ]);
    }

    /**
     * Scope para asignación de infraestructura accesible (Ley 29973)
     */
    public function scopeRequiereAulaAccesible(Builder $query): Builder
    {
        return $query->where('requiere_aula_accesible', true)
            ->select([
                'id_estudiante',
                'requiere_aula_accesible',
            ]);
    }
}
