<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\GuardarPerfilSocioeconomicoRequest;
use App\Modules\Bienestar\Http\Requests\StoreEstudianteRequest;
use App\Modules\Bienestar\Http\Requests\UpdateEstudianteRequest;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Services\EstudianteService;
use App\Modules\Bienestar\Services\PerfilSocioeconomicoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstudianteController extends Controller
{
    /**
     * Listado general de estudiantes con búsqueda.
     */
    public function index(Request $request): View
    {
        if ($request->user()->cannot('viewAny', Estudiante::class)) {
            abort(403, 'No tiene autorización para acceder al registro de estudiantes.');
        }

        $termino = $request->query('buscar');

        $estudiantes = Estudiante::buscar($termino)
            ->with(['usuario', 'perfilSocioeconomico', 'tiposCondicion', 'deportes'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(15)
            ->withQueryString();

        return view('bienestar::estudiantes.index', compact('estudiantes', 'termino'));
    }

    /**
     * Formulario de registro de estudiante.
     */
    public function create(Request $request): View
    {
        if ($request->user()->cannot('create', Estudiante::class)) {
            abort(403, 'No tiene autorización para registrar nuevos estudiantes.');
        }

        return view('bienestar::estudiantes.create');
    }

    /**
     * Procesa el registro de estudiante y usuario asociado en transacción.
     */
    public function store(StoreEstudianteRequest $request, EstudianteService $service): RedirectResponse
    {
        $resultado = $service->registrarEstudiante($request->validated());

        return redirect()->route('bienestar.estudiantes.show', $resultado['estudiante']->id_estudiante)
            ->with('success', 'Estudiante registrado correctamente en el sistema.')
            ->with('password_temporal', $resultado['password_temporal'])
            ->with('correo_generado', $resultado['estudiante']->usuario->correo);
    }

    /**
     * Ficha detallada del estudiante.
     */
    public function show(Request $request, int $id): View
    {
        $estudiante = Estudiante::with([
            'usuario',
            'perfilSocioeconomico',
            'tiposCondicion',
            'deportes',
            'casos.tipoCaso',
            'solicitudesApoyo',
            'citas.horario.especialista',
        ])->find($id);

        if (!$estudiante) {
            abort(404, 'Estudiante no encontrado.');
        }

        if ($request->user()->cannot('view', $estudiante)) {
            abort(404, 'Estudiante no encontrado.');
        }

        return view('bienestar::estudiantes.show', compact('estudiante'));
    }

    /**
     * Formulario de edición de estudiante.
     */
    public function edit(Request $request, int $id): View
    {
        $estudiante = Estudiante::with('usuario')->find($id);

        if (!$estudiante) {
            abort(404, 'Estudiante no encontrado.');
        }

        if ($request->user()->cannot('update', $estudiante)) {
            abort(403, 'No tiene autorización para editar datos del estudiante.');
        }

        return view('bienestar::estudiantes.edit', compact('estudiante'));
    }

    /**
     * Procesa la actualización de datos del estudiante.
     */
    public function update(UpdateEstudianteRequest $request, int $id, EstudianteService $service): RedirectResponse
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            abort(404, 'Estudiante no encontrado.');
        }

        if ($request->user()->cannot('update', $estudiante)) {
            return back()->with('warning', 'No tiene permisos para modificar la información del estudiante.');
        }

        $service->actualizarEstudiante($estudiante, $request->validated());

        return redirect()->route('bienestar.estudiantes.show', $estudiante->id_estudiante)
            ->with('success', 'Información del estudiante actualizada correctamente.');
    }

    /**
     * Registra o actualiza el perfil socioeconómico del estudiante (Fase B).
     */
    public function guardarPerfil(GuardarPerfilSocioeconomicoRequest $request, int $id, PerfilSocioeconomicoService $service): RedirectResponse
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            abort(404, 'Estudiante no encontrado.');
        }

        if ($request->user()->cannot('gestionarPerfil', $estudiante)) {
            return back()->with('warning', 'No tiene permisos para modificar el perfil socioeconómico.');
        }

        $service->guardarOActualizar($estudiante, $request->validated());

        return redirect()->route('bienestar.estudiantes.show', $estudiante->id_estudiante)
            ->with('success', 'Perfil socioeconómico guardado correctamente.');
    }
}
