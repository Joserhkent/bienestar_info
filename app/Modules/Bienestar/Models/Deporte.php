<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deporte extends Model
{
    protected $table = 'bienestar.deporte';
    protected $primaryKey = 'id_deporte';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    /**
     * R25: ESTUDIANTE (0,N) practica (0,N) DEPORTE
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Estudiante::class,
            'bienestar.estudiante_deporte',
            'id_deporte',
            'id_estudiante'
        )->using(EstudianteDeporte::class)
         ->withPivot([
             'nivel',
             'disponible_seleccion',
         ]);
    }
}
