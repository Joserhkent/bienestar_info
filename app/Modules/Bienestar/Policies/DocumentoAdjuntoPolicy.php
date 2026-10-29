<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\DocumentoAdjunto;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentoAdjuntoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede descargar o visualizar el documento.
     */
    public function view(Usuario $user, DocumentoAdjunto $documento): bool
    {
        $rol = $user->rol ? $user->rol->nombre : '';

        // Personal de Bienestar puede ver todos los sustentos adjuntos para dictaminar
        if ($rol === 'Personal de Bienestar') {
            return true;
        }

        // Estudiante solo puede ver y descargar sus propios documentos
        if ($rol === 'Estudiante' && $user->estudiante) {
            return $documento->solicitud && $documento->solicitud->id_estudiante === $user->estudiante->id_estudiante;
        }

        return false;
    }
}
