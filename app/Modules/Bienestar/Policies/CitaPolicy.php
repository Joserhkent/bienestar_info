<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class CitaPolicy
{
    use HandlesAuthorization;

    public function viewAny(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        // Docente no tiene acceso a citas clínicas ni de tutoría individual
        return in_array($rol, ['Personal de Bienestar', 'Especialista', 'Estudiante', 'Administrador'], true);
    }

    public function view(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Administrador' || $rol === 'Personal de Bienestar') {
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
     * Agendar cita: Exclusivo del Estudiante
     */
    public function agendar(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Estudiante';
    }

    /**
     * Confirmar cita: Exclusivo del Especialista asignado
     */
    public function confirmar(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return $rol === 'Especialista'
            && $user->especialista
            && $cita->horario
            && $cita->horario->id_especialista === $user->especialista->id_especialista;
    }

    /**
     * Cancelar cita: El estudiante dueño o el especialista asignado
     */
    public function cancelar(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Estudiante' && $user->estudiante) {
            return $cita->id_estudiante === $user->estudiante->id_estudiante;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $cita->horario && $cita->horario->id_especialista === $user->especialista->id_especialista;
        }

        return false;
    }

    /**
     * Registrar atención realizada: Exclusivo del Especialista asignado
     */
    public function atender(Usuario $user, Cita $cita): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return $rol === 'Especialista'
            && $user->especialista
            && $cita->horario
            && $cita->horario->id_especialista === $user->especialista->id_especialista;
    }

    /**
     * Registrar nuevo horario disponible para citas (P3): Exclusivo del Especialista
     */
    public function crearHorario(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return $rol === 'Especialista';
    }

    /**
     * Gestión de horarios disponibles de atención: Exclusivo del Especialista
     */
    public function gestionarHorarios(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return $rol === 'Especialista';
    }
}
