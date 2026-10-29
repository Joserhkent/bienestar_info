<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Usuario;

class UsuarioPolicy
{
    /**
     * Ver listado institucional de cuentas de usuario:
     * Solo Personal de Bienestar y Administrador.
     */
    public function viewAny(Usuario $authUser): bool
    {
        return in_array($authUser->rol?->nombre, ['Personal de Bienestar', 'Administrador'], true);
    }

    /**
     * Crear cuenta y perfil de Especialista:
     * Personal de Bienestar y Administrador.
     */
    public function createEspecialista(Usuario $authUser): bool
    {
        return in_array($authUser->rol?->nombre, ['Personal de Bienestar', 'Administrador'], true);
    }

    /**
     * Activar o suspender una cuenta de usuario:
     * - Ningún usuario puede suspender su propia cuenta activa.
     * - Personal de Bienestar solo gestiona cuentas de Estudiante y Especialista.
     * - Administrador gestiona todos los roles.
     */
    public function alternarEstado(Usuario $authUser, Usuario $targetUser): bool
    {
        // No auto-suspensión
        if ($authUser->id_usuario === $targetUser->id_usuario) {
            return false;
        }

        $rolAuth = $authUser->rol?->nombre;
        $rolTarget = $targetUser->rol?->nombre;

        if ($rolAuth === 'Personal de Bienestar') {
            return in_array($rolTarget, ['Estudiante', 'Especialista'], true);
        }

        if ($rolAuth === 'Administrador') {
            return true;
        }

        return false;
    }

    /**
     * Restablecer contraseña temporal:
     * - Personal de Bienestar solo restablece a Estudiante y Especialista.
     * - Administrador gestiona y restablece a todos los roles.
     */
    public function restablecerPassword(Usuario $authUser, Usuario $targetUser): bool
    {
        $rolAuth = $authUser->rol?->nombre;
        $rolTarget = $targetUser->rol?->nombre;

        if ($rolAuth === 'Personal de Bienestar') {
            return in_array($rolTarget, ['Estudiante', 'Especialista'], true);
        }

        if ($rolAuth === 'Administrador') {
            return true;
        }

        return false;
    }
}
