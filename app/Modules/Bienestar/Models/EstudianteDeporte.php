<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteDeporte extends Pivot
{
    protected $table = 'bienestar.estudiante_deporte';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id_estudiante',
        'id_deporte',
        'nivel',
        'disponible_seleccion',
    ];

    protected function casts(): array
    {
        return [
            'disponible_seleccion' => 'boolean',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function deporte(): BelongsTo
    {
        return $this->belongsTo(Deporte::class, 'id_deporte', 'id_deporte');
    }

    public function scopeSeleccionables(Builder $query): Builder
    {
        return $query->where('disponible_seleccion', true);
    }
}
