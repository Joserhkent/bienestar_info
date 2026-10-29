<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class CitaPolicy
{
    use HandlesAuthorization;

    public function before(Usuario $user, string $ability): ?bool
    {
        if ($user->rol && $user->rol->nombre === 'Administrador') {
            return true;
        }
        return null;
    }

    public function view(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Estudiante' && $user->estudiante) {
            return $cita->id_estudiante === $user->estudiante->id_estudiante;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $cita->horario && $cita->horario->id_especialista === $user->especialista->id_especialista;
        }

        return false;
    }

    /**
     * Agendar cita (P3): Exclusivo del Estudiante
     */
    public function agendar(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Estudiante';
    }

    /**
     * Confirmar cita (P3): El especialista dueño del horario
     */
    public function confirmar(Usuario $user, Cita $cita): bool
    {
        return $user->rol && $user->rol->nombre === 'Especialista'
            && $user->especialista
            && $cita->horario
            && $cita->horario->id_especialista === $user->especialista->id_especialista;
    }

    /**
     * Cancelar cita: El estudiante dueño o el especialista correspondiente
     */
    public function cancelar(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Estudiante' && $user->estudiante) {
            return $cita->id_estudiante === $user->estudiante->id_estudiante;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $cita->horario && $cita->horario->id_especialista === $user->especialista->id_especialista;
        }

        return false;
    }
}
