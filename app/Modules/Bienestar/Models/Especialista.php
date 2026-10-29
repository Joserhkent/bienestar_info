<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialista extends Model
{
    protected $table = 'bienestar.especialista';
    protected $primaryKey = 'id_especialista';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombres',
        'apellidos',
        'especialidad',
        'nro_colegiatura',
        'estado',
    ];

    /**
     * R03: USUARIO (0,1) identifica a (1,1) ESPECIALISTA
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * R12: ESPECIALISTA (0,N) recibe (1,1) DERIVACION
     */
    public function derivaciones(): HasMany
    {
        return $this->hasMany(Derivacion::class, 'id_especialista', 'id_especialista');
    }

    /**
     * R17: ESPECIALISTA (0,N) ofrece (1,1) HORARIO_DISPONIBLE
     */
    public function horariosDisponibles(): HasMany
    {
        return $this->hasMany(HorarioDisponible::class, 'id_especialista', 'id_especialista');
    }

    // Scopes
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorEspecialidad(Builder $query, string $esp): Builder
    {
        return $query->where('especialidad', 'like', "%{$esp}%");
    }
}
