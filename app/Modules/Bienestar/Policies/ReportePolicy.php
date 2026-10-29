<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportePolicy
{
    use HandlesAuthorization;

    /**
     * Generación y visualización de reportes consolidados (P5):
     * Exclusivo de Administrador / Dirección de Escuela.
     */
    public function generate(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Administrador';
    }
}
