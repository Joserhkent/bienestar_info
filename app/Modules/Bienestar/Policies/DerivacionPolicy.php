<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class DerivacionPolicy
{
    use HandlesAuthorization;

    public function before(Usuario $user, string $ability): ?bool
    {
        if ($user->rol && $user->rol->nombre === 'Administrador') {
            return true;
        }
        return null;
    }

    public function view(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $derivacion->id_especialista === $user->especialista->id_especialista;
        }

        return false;
    }

    /**
     * Derivar caso a especialista (P4): Personal de Bienestar
     */
    public function create(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Personal de Bienestar';
    }

    /**
     * Registrar sesión de seguimiento (P4): El Especialista asignado
     */
    public function registrarSeguimiento(Usuario $user, Derivacion $derivacion): bool
    {
        return $user->rol && $user->rol->nombre === 'Especialista'
            && $user->especialista
            && $derivacion->id_especialista === $user->especialista->id_especialista;
    }
}
