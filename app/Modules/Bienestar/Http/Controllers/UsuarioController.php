<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\StoreEspecialistaRequest;
use App\Modules\Bienestar\Models\Rol;
use App\Modules\Bienestar\Models\Usuario;
use App\Modules\Bienestar\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Listado general de cuentas de usuario con filtros de búsqueda, rol y estado.
     */
    public function index(Request $request): View
    {
        if ($request->user()->cannot('viewAny', Usuario::class)) {
            abort(403, 'No tiene autorización para acceder a la gestión de usuarios.');
        }

        $termino = $request->query('buscar');
        $idRol = $request->query('rol');
        $estado = $request->query('estado');

        $query = Usuario::with(['rol', 'estudiante', 'especialista'])
            ->orderBy('id_usuario', 'desc');

        if (!empty($termino)) {
            $t = trim($termino);
            $query->where(function ($q) use ($t) {
                $q->where('correo', 'ilike', "%{$t}%")
                  ->orWhereHas('estudiante', function ($sq) use ($t) {
                      $sq->where('nombres', 'ilike', "%{$t}%")
                         ->orWhere('apellidos', 'ilike', "%{$t}%")
                         ->orWhere('codigo_universitario', 'like', "%{$t}%");
                  })
                  ->orWhereHas('especialista', function ($sq) use ($t) {
                      $sq->where('nombres', 'ilike', "%{$t}%")
                         ->orWhere('apellidos', 'ilike', "%{$t}%")
                         ->orWhere('nro_colegiatura', 'like', "%{$t}%");
                  });
            });
        }

        if (!empty($idRol)) {
            $query->where('id_rol', (int) $idRol);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $roles = Rol::orderBy('id_rol')->get();

        return view('bienestar::usuarios.index', compact('usuarios', 'roles', 'termino', 'idRol', 'estado'));
    }

    /**
     * Formulario para registrar un nuevo especialista institucional.
     */
    public function createEspecialista(Request $request): View
    {
        if ($request->user()->cannot('createEspecialista', Usuario::class)) {
            abort(403, 'No tiene autorización para registrar especialistas.');
        }

        return view('bienestar::usuarios.create-especialista');
    }

    /**
     * Procesa el registro de un nuevo especialista con credencial temporal en una transacción.
     */
    public function storeEspecialista(StoreEspecialistaRequest $request, UsuarioService $service): RedirectResponse
    {
        $resultado = $service->registrarEspecialista($request->validated());

        return redirect()->route('bienestar.usuarios.index')
            ->with('success', 'Especialista registrado correctamente en el sistema institucional.')
            ->with('password_temporal', $resultado['password_temporal'])
            ->with('correo_generado', $resultado['usuario']->correo);
    }

    /**
     * Alterna el estado de una cuenta entre activo y suspendido.
     */
    public function alternarEstado(Request $request, int $id, UsuarioService $service): RedirectResponse
    {
        $usuario = Usuario::with('rol')->find($id);

        if (!$usuario) {
            abort(404, 'Usuario no encontrado.');
        }

        if ($request->user()->cannot('alternarEstado', $usuario)) {
            return back()->with('warning', 'No tiene permisos para modificar el estado de esta cuenta de usuario.');
        }

        $nuevoEstado = $service->alternarEstado($usuario);

        $accion = $nuevoEstado === 'activo' ? 'activada' : 'suspendida';

        return back()->with('success', "La cuenta institucional ({$usuario->correo}) ha sido {$accion} correctamente.");
    }

    /**
     * Restablece la contraseña temporal de un usuario y exige su cambio en el primer ingreso.
     */
    public function restablecerPassword(Request $request, int $id, UsuarioService $service): RedirectResponse
    {
        $usuario = Usuario::with('rol')->find($id);

        if (!$usuario) {
            abort(404, 'Usuario no encontrado.');
        }

        if ($request->user()->cannot('restablecerPassword', $usuario)) {
            return back()->with('warning', 'No tiene permisos para restablecer la contraseña de esta cuenta.');
        }

        $passwordTemporal = $service->restablecerPassword($usuario);

        return back()
            ->with('success', 'Contraseña temporal restablecida correctamente.')
            ->with('password_temporal', $passwordTemporal)
            ->with('correo_generado', $usuario->correo);
    }
}
