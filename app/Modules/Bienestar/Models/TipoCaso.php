<?php

namespace App\Modules\Bienestar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCaso extends Model
{
    protected $table = 'bienestar.tipo_caso';
    protected $primaryKey = 'id_tipo_caso';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * R06: TIPO_CASO (0,N) clasifica (1,1) CASO_BIENESTAR
     */
    public function casos(): HasMany
    {
        return $this->hasMany(CasoBienestar::class, 'id_tipo_caso', 'id_tipo_caso');
    }
}
