<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoCondicion extends Model
{
    protected $table = 'bienestar.tipo_condicion';
    protected $primaryKey = 'id_tipo_condicion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'categoria',
        'base_legal',
    ];

    /**
     * R24: ESTUDIANTE (0,N) presenta (0,N) TIPO_CONDICION
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Estudiante::class,
            'bienestar.estudiante_tipo_condicion',
            'id_tipo_condicion',
            'id_estudiante'
        )->using(EstudianteTipoCondicion::class)
         ->withPivot([
             'grado',
             'requiere_aula_accesible',
             'pauta_pedagogica',
             'certificado_conadis',
             'visible_docente',
             'fecha_registro',
         ]);
    }

    public function scopePorCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }
}
