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

    public function viewAny(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return in_array($rol, ['Personal de Bienestar', 'Especialista', 'Administrador'], true);
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
     * Derivar caso a especialista: Personal de Bienestar
     */
    public function create(Usuario $user): bool
    {
        return $user->rol && in_array($user->rol->nombre, ['Personal de Bienestar', 'Administrador'], true);
    }

    /**
     * Actualizar estado o gestionar la derivación (aceptar, finalizar)
     */
    public function update(Usuario $user, Derivacion $derivacion): bool
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
     * Registrar sesión de seguimiento: El Especialista asignado o Personal de Bienestar
     */
    public function registrarSeguimiento(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        return $rol === 'Especialista'
            && $user->especialista
            && $derivacion->id_especialista === $user->especialista->id_especialista;
    }
}
