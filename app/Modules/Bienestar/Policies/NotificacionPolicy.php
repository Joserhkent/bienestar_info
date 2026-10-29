<?php

namespace App\Modules\Bienestar\Policies;

use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificacionPolicy
{
    use HandlesAuthorization;

    public function viewAny(Usuario $user): bool
    {
        return $user !== null;
    }

    public function view(Usuario $user, Notificacion $notificacion): bool
    {
        return $notificacion->id_usuario === $user->id_usuario;
    }

    public function update(Usuario $user, Notificacion $notificacion): bool
    {
        return $notificacion->id_usuario === $user->id_usuario;
    }

    public function delete(Usuario $user, Notificacion $notificacion): bool
    {
        return $notificacion->id_usuario === $user->id_usuario;
    }
}
