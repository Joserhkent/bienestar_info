<?php

namespace App\Modules\Bienestar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('bienestar.login')
                ->with('warning', 'Debe iniciar sesión para acceder a este módulo.');
        }

        /** @var \App\Modules\Bienestar\Models\Usuario $usuario */
        $usuario = Auth::user();
        $rolUsuario = $usuario->rol ? $usuario->rol->nombre : null;

        // Si se especificaron roles y el rol del usuario no coincide
        if (!empty($roles) && !in_array($rolUsuario, $roles, true)) {
            abort(403, 'Acceso no autorizado a este recurso.');
        }

        return $next($request);
    }
}
