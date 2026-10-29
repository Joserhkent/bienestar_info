<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\StoreDerivacionRequest;
use App\Modules\Bienestar\Http\Requests\StoreSeguimientoRequest;
use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Services\DerivacionService;
use Illuminate\Http\Request;

class DerivacionController extends Controller
{
    public function __construct(
        protected DerivacionService $derivacionService
    ) {}

    /**
     * Listado de derivaciones según rol (Bienestar / Especialista / Admin).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Derivacion::class);

        $usuario = $request->user();
        $rol = $usuario->rol ? $usuario->rol->nombre : '';

        $query = Derivacion::with(['caso.estudiante', 'especialista', 'seguimientos'])
            ->orderBy('fecha_derivacion', 'desc');

        if ($rol === 'Especialista') {
            if (!$usuario->especialista) {
                abort(403, 'No tiene un perfil de especialista asociado.');
            }
            $query->where('id_especialista', $usuario->especialista->id_especialista);
        } else {
            if ($request->filled('estado')) {
                $query->where('estado', $request->input('estado'));
            }
            if ($request->filled('id_especialista')) {
                $query->where('id_especialista', $request->input('id_especialista'));
            }
        }

        $derivaciones = $query->paginate(10)->withQueryString();
        $especialistas = Especialista::where('estado', 'activo')->get();

        return view('bienestar::derivaciones.index', compact('derivaciones', 'especialistas'));
    }

    /**
     * Formulario para derivar un caso (Personal de Bienestar / Admin).
     */
    public function create(Request $request)
    {
        $this->authorize('create', Derivacion::class);

        $casoSeleccionado = null;
        if ($request->filled('id_caso')) {
            $casoSeleccionado = CasoBienestar::with('estudiante')->find($request->input('id_caso'));
        }

        $casos = CasoBienestar::with('estudiante')
            ->whereIn('estado', ['registrado', 'en_atencion'])
            ->orderBy('id_caso', 'desc')
            ->get();

        $especialistas = Especialista::where('estado', 'activo')->get();

        return view('bienestar::derivaciones.create', compact('casos', 'especialistas', 'casoSeleccionado'));
    }

    /**
     * Registra la derivación y transiciona el caso a 'derivado' en DB::transaction().
     */
    public function store(StoreDerivacionRequest $request)
    {
        $derivacion = $this->derivacionService->derivarCaso(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('bienestar.derivaciones.show', $derivacion->id_derivacion)
            ->with('success', 'El caso ha sido derivado correctamente al especialista.');
    }

    /**
     * Ver detalle de la derivación y su historial de sesiones de seguimiento.
     */
    public function show(Derivacion $derivacion)
    {
        $this->authorize('view', $derivacion);

        $derivacion->load(['caso.estudiante.usuario', 'especialista.usuario', 'usuarioDeriva', 'seguimientos']);

        return view('bienestar::derivaciones.show', compact('derivacion'));
    }

    /**
     * Registrar sesión de seguimiento (Especialista asignado).
     */
    public function storeSeguimiento(StoreSeguimientoRequest $request, Derivacion $derivacion)
    {
        $this->authorize('registrarSeguimiento', $derivacion);

        $this->derivacionService->registrarSeguimiento(
            $derivacion,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('bienestar.derivaciones.show', $derivacion->id_derivacion)
            ->with('success', 'Sesión de seguimiento registrada correctamente.');
    }

    /**
     * Especialista acepta la derivación asignada.
     */
    public function aceptar(Derivacion $derivacion)
    {
        $this->authorize('update', $derivacion);

        $this->derivacionService->aceptarDerivacion($derivacion);

        return redirect()
            ->route('bienestar.derivaciones.show', $derivacion->id_derivacion)
            ->with('success', 'La derivación ha sido aceptada.');
    }

    /**
     * Concluir la derivación.
     */
    public function finalizar(Derivacion $derivacion)
    {
        $this->authorize('update', $derivacion);

        $this->derivacionService->finalizarDerivacion($derivacion);

        return redirect()
            ->route('bienestar.derivaciones.show', $derivacion->id_derivacion)
            ->with('success', 'La derivación ha sido finalizada.');
    }
}
