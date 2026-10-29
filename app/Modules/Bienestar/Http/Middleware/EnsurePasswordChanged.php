<?php

namespace App\Modules\Bienestar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Intercepta peticiones para forzar el cambio de contraseña temporal en el primer acceso.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->debe_cambiar_contrasena) {
            $rutasPermitidas = [
                'bienestar.cambiar-contrasena',
                'bienestar.cambiar-contrasena.post',
                'bienestar.logout',
            ];

            $rutaActual = $request->route() ? $request->route()->getName() : null;

            if (!in_array($rutaActual, $rutasPermitidas, true)) {
                return redirect()->route('bienestar.cambiar-contrasena')
                    ->with('warning', 'Por motivos de seguridad institucional, debe actualizar su contraseña temporal para acceder al sistema.');
            }
        }

        return $next($request);
    }
}
