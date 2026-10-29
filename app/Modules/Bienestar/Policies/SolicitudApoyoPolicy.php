<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class SolicitudApoyoPolicy
{
    use HandlesAuthorization;

    public function viewAny(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return in_array($rol, ['Estudiante', 'Personal de Bienestar', 'Administrador'], true);
    }

    public function view(Usuario $user, SolicitudApoyo $solicitud): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Administrador' || $rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Estudiante' && $user->estudiante) {
            return $solicitud->id_estudiante === $user->estudiante->id_estudiante;
        }

        return false;
    }

    /**
     * Enviar solicitud (P2): Exclusivo del Estudiante
     */
    public function create(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Estudiante';
    }

    /**
     * Dictaminar / Revisar solicitud (P2): Exclusivo de Personal de Bienestar
     */
    public function dictaminar(Usuario $user, SolicitudApoyo $solicitud): bool
    {
        return $user->rol && $user->rol->nombre === 'Personal de Bienestar';
    }
}
