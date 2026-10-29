<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\EstudianteTipoCondicion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocentePautaPolicy
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
     * Lectura de pauta pedagógica (Ley 30150):
     * - Docente: Solo si visible_docente = true.
     * - Personal de Bienestar: Sí.
     */
    public function verPauta(Usuario $user, EstudianteTipoCondicion $pivote): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        if ($rol === 'Docente') {
            return (bool) $pivote->visible_docente;
        }

        return false;
    }
}
