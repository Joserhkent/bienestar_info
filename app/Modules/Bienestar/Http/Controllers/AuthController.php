<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\EstudianteTipoCondicion;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('bienestar.dashboard');
        }

        return view('bienestar::auth.login');
    }

    /**
     * Procesa la autenticación del usuario
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'correo' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'correo.required' => 'El correo institucional es obligatorio.',
            'correo.email' => 'Ingrese un formato de correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::with('rol')->where('correo', $credentials['correo'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->contrasena_hash)) {
            return back()->withInput($request->only('correo'))
                ->withErrors(['correo' => 'Las credenciales no coinciden con nuestros registros.']);
        }

        if ($usuario->estado !== 'activo') {
            return back()->withErrors(['correo' => 'Su cuenta se encuentra inactiva o bloqueada. Contacte a la Dirección de Escuela.']);
        }

        // Iniciar sesión y actualizar último acceso
        Auth::login($usuario);
        $usuario->update(['ultimo_acceso' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('bienestar.dashboard'))
            ->with('success', "Bienvenido(a), {$usuario->correo} [Rol: {$usuario->rol->nombre}]");
    }

    /**
     * Cierra la sesión
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('bienestar.login')
            ->with('info', 'Has cerrado sesión satisfactoriamente.');
    }

    /**
     * Dashboard general adaptado por rol
     */
    public function dashboard(): View
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user()->load('rol');
        $rol = $usuario->rol ? $usuario->rol->nombre : 'Sin Rol';

        $datos = [
            'usuario' => $usuario,
            'rol' => $rol,
        ];

        // Cargar métricas según el carril del BPMN
        switch ($rol) {
            case 'Personal de Bienestar':
            case 'Administrador':
                $datos['totalCasos'] = CasoBienestar::count();
                $datos['casosAbiertos'] = CasoBienestar::abiertos()->count();
                $datos['solicitudesPendientes'] = SolicitudApoyo::pendientes()->count();
                $datos['totalEstudiantes'] = Estudiante::count();
                $datos['ultimosCasos'] = CasoBienestar::with(['estudiante', 'tipoCaso'])->latest('fecha_registro')->take(5)->get();
                break;

            case 'Especialista':
                $idEspecialista = $usuario->especialista?->id_especialista ?? 0;
                $datos['citasPendientes'] = Cita::whereHas('horario', fn($q) => $q->where('id_especialista', $idEspecialista))
                    ->where('estado', 'solicitada')->count();
                $datos['casosDerivados'] = CasoBienestar::whereHas('derivaciones', fn($q) => $q->where('id_especialista', $idEspecialista))->count();
                break;

            case 'Estudiante':
                $idEstudiante = $usuario->estudiante?->id_estudiante ?? 0;
                $datos['misSolicitudes'] = SolicitudApoyo::where('id_estudiante', $idEstudiante)->get();
                $datos['misCitas'] = Cita::with(['horario.especialista'])->where('id_estudiante', $idEstudiante)->get();
                $datos['misCasos'] = CasoBienestar::where('id_estudiante', $idEstudiante)->where('confidencial', false)->get();
                break;

            case 'Docente':
                // Solo pautas visibles (Ley 30150)
                $datos['pautasEstudiantes'] = EstudianteTipoCondicion::visibleParaDocente()
                    ->with(['estudiante'])
                    ->get();
                break;
        }

        return view('bienestar::dashboard', $datos);
    }
}
