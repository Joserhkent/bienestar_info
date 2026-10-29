<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Bandeja de notificaciones del usuario autenticado (P5).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Notificacion::class);

        $usuario = $request->user();
        $soloNoLeidas = $request->query('filtro') === 'no_leidas';

        $query = Notificacion::where('id_usuario', $usuario->id_usuario)
            ->orderBy('fecha_envio', 'desc');

        if ($soloNoLeidas) {
            $query->where('leida', false);
        }

        $notificaciones = $query->paginate(15)->withQueryString();
        $totalNoLeidas = Notificacion::where('id_usuario', $usuario->id_usuario)
            ->where('leida', false)
            ->count();

        return view('bienestar::notificaciones.index', compact('notificaciones', 'totalNoLeidas', 'soloNoLeidas'));
    }

    /**
     * Marcar una notificación individual como leída.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        $this->authorize('update', $notificacion);

        $notificacion->leida = true;
        $notificacion->save();

        return redirect()
            ->route('bienestar.notificaciones.index')
            ->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marcar todas las notificaciones pendientes del usuario como leídas.
     */
    public function marcarTodasLeidas(Request $request)
    {
        $this->authorize('viewAny', Notificacion::class);

        Notificacion::where('id_usuario', $request->user()->id_usuario)
            ->where('leida', false)
            ->update(['leida' => true]);

        return redirect()
            ->route('bienestar.notificaciones.index')
            ->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }
}
