<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class CasoBienestarPolicy
{
    use HandlesAuthorization;

    /**
     * Ver listado de casos: Personal de Bienestar y Administrador (Supervisión institucional)
     */
    public function viewAny(Usuario $user): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';
        return in_array($rol, ['Personal de Bienestar', 'Administrador'], true);
    }

    /**
     * Ver detalle de un caso:
     * - Administrador y Personal de Bienestar: Sí (Supervisión y gestión institucional)
     * - Especialista: NUNCA (bloqueado; atiende directamente desde derivaciones.show)
     * - Estudiante: NUNCA (interactúa exclusivamente mediante solicitudes y citas)
     * - Docente: NUNCA
     */
    public function view(Usuario $user, CasoBienestar $caso): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        return in_array($rol, ['Administrador', 'Personal de Bienestar'], true);
    }

    /**
     * Registrar nuevo caso (P1): Exclusivo de Personal de Bienestar
     */
    public function create(Usuario $user): bool
    {
        return $user->rol && $user->rol->nombre === 'Personal de Bienestar';
    }

    /**
     * Actualizar o cambiar estado de caso: Personal de Bienestar
     */
    public function update(Usuario $user, CasoBienestar $caso): bool
    {
        return $user->rol && $user->rol->nombre === 'Personal de Bienestar';
    }

    public function cambiarEstado(Usuario $user, CasoBienestar $caso): bool
    {
        return $this->update($user, $caso);
    }
}
