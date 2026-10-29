<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Usuario;

class EstudiantePolicy
{
    /**
     * Ver listado general de estudiantes:
     * Solo Personal de Bienestar y Administrador (Dirección).
     */
    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol?->nombre, ['Personal de Bienestar', 'Administrador'], true);
    }

    /**
     * Ver la ficha detallada de un estudiante:
     * Personal de Bienestar, Administrador o el propio estudiante autenticado.
     */
    public function view(Usuario $user, Estudiante $estudiante): bool
    {
        if (in_array($user->rol?->nombre, ['Personal de Bienestar', 'Administrador'], true)) {
            return true;
        }

        if ($user->rol?->nombre === 'Estudiante') {
            return $user->estudiante && $user->estudiante->id_estudiante === $estudiante->id_estudiante;
        }

        return false;
    }

    /**
     * Registrar nuevo estudiante:
     * Exclusivo para Personal de Bienestar (Proceso 1 BPMN).
     */
    public function create(Usuario $user): bool
    {
        return $user->rol?->nombre === 'Personal de Bienestar';
    }

    /**
     * Editar datos de un estudiante:
     * Exclusivo para Personal de Bienestar.
     */
    public function update(Usuario $user, Estudiante $estudiante): bool
    {
        return $user->rol?->nombre === 'Personal de Bienestar';
    }

    /**
     * Crear o editar perfil socioeconómico:
     * Exclusivo para Personal de Bienestar.
     */
    public function gestionarPerfil(Usuario $user, Estudiante $estudiante): bool
    {
        return $user->rol?->nombre === 'Personal de Bienestar';
    }

    /**
     * Ver observación social confidencial:
     * Exclusivo para Personal de Bienestar. El Administrador NO la ve.
     */
    public function verObservacionSocioeconomica(Usuario $user, Estudiante $estudiante): bool
    {
        return $user->rol?->nombre === 'Personal de Bienestar';
    }

    /**
     * Eliminación deshabilitada por integridad y auditoría.
     */
    public function delete(Usuario $user, Estudiante $estudiante): bool
    {
        return false;
    }
}
