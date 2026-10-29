<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class DerivacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return in_array($rol, ['Personal de Bienestar', 'Especialista', 'Administrador'], true);
    }

    public function view(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Administrador' || $rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $derivacion->id_especialista === $user->especialista->id_especialista;
        }

        return false;
    }

    /**
     * Ver notas clínicas confidenciales y acuerdos: Exclusivo del especialista tratante asignado.
     * Bienestar y Administrador solo supervisan fechas, sesiones y estado (secreto profesional médico).
     */
    public function verNotasClinicas(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return $rol === 'Especialista'
            && $user->especialista
            && $derivacion->id_especialista === $user->especialista->id_especialista;
    }

    /**
     * Derivar caso a especialista: Exclusivo de Personal de Bienestar
     */
    public function create(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Personal de Bienestar';
    }

    /**
     * Actualizar estado o gestionar la derivación (aceptar, finalizar): Exclusivo del Especialista asignado
     */
    public function update(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return $rol === 'Especialista'
            && $user->especialista
            && $derivacion->id_especialista === $user->especialista->id_especialista;
    }

    /**
     * Registrar sesión de seguimiento: Exclusivo del Especialista asignado
     */
    public function registrarSeguimiento(Usuario $user, Derivacion $derivacion): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return $rol === 'Especialista'
            && $user->especialista
            && $derivacion->id_especialista === $user->especialista->id_especialista;
    }
}
