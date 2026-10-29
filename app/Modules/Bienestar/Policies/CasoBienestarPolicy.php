<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class CasoBienestarPolicy
{
    use HandlesAuthorization;

    public function before(Usuario $user, string $ability): ?bool
    {
        if ($user->rol && $user->rol->nombre === 'Administrador') {
            return true;
        }
        return null;
    }

    /**
     * Ver listado de casos: Solo Personal de Bienestar y Administrador
     */
    public function viewAny(Usuario $user): bool
    {
        return $user->rol && in_array($user->rol->nombre, ['Personal de Bienestar'], true);
    }

    /**
     * Ver detalle de un caso:
     * - Personal de Bienestar: Sí
     * - Especialista: Solo si el caso le fue derivado
     * - Estudiante: Solo su propio caso y SIEMPRE que NO sea confidencial
     * - Docente: NUNCA (Estatuto UNT y confidencialidad médica)
     */
    public function view(Usuario $user, CasoBienestar $caso): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Especialista' && $user->especialista) {
            return $caso->derivaciones()
                ->where('id_especialista', $user->especialista->id_especialista)
                ->exists();
        }

        if ($rol === 'Estudiante' && $user->estudiante) {
            return $caso->id_estudiante === $user->estudiante->id_estudiante && !$caso->confidencial;
        }

        return false;
    }

    /**
     * Registrar nuevo caso (P1): Personal de Bienestar
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
