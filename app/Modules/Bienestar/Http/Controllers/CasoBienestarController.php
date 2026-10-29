<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\CambiarEstadoCasoRequest;
use App\Modules\Bienestar\Http\Requests\StoreCasoRequest;
use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\TipoCaso;
use App\Modules\Bienestar\Services\CasoBienestarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CasoBienestarController extends Controller
{
    protected CasoBienestarService $casoService;

    public function __construct(CasoBienestarService $casoService)
    {
        $this->casoService = $casoService;
    }

    /**
     * Listado general de casos (P1)
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CasoBienestar::class);

        $query = CasoBienestar::with(['estudiante', 'tipoCaso', 'usuarioRegistro'])
            ->latest('fecha_registro');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }
        if ($request->filled('id_tipo_caso')) {
            $query->where('id_tipo_caso', $request->id_tipo_caso);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('estudiante', function ($q) use ($buscar) {
                $q->where('nombres', 'ilike', "%{$buscar}%")
                  ->orWhere('apellidos', 'ilike', "%{$buscar}%")
                  ->orWhere('codigo_universitario', 'like', "%{$buscar}%");
            });
        }

        $casos = $query->paginate(10)->withQueryString();
        $tiposCaso = TipoCaso::all();

        return view('bienestar::casos.index', compact('casos', 'tiposCaso'));
    }

    /**
     * Formulario para registrar un nuevo expediente (P1)
     */
    public function create(): View
    {
        $this->authorize('create', CasoBienestar::class);

        $estudiantes = Estudiante::orderBy('apellidos')->get();
        $tiposCaso = TipoCaso::all();

        return view('bienestar::casos.create', compact('estudiantes', 'tiposCaso'));
    }

    /**
     * Almacena el nuevo caso e inicia su historial de trazabilidad
     */
    public function store(StoreCasoRequest $request): RedirectResponse
    {
        $this->authorize('create', CasoBienestar::class);

        $caso = $this->casoService->registrarCaso($request->validated(), Auth::user());

        return redirect()->route('bienestar.casos.show', $caso->id_caso)
            ->with('success', "Expediente de Bienestar #CAS-{$caso->id_caso} registrado exitosamente.");
    }

    /**
     * Vista de detalle del expediente y su historial de estados
     */
    public function show(Request $request, CasoBienestar $caso): View
    {
        if ($request->user()->cannot('view', $caso)) {
            abort(404, 'El expediente solicitado no existe o no se encuentra disponible.');
        }

        $caso->load([
            'estudiante.perfilSocioeconomico',
            'tipoCaso',
            'usuarioRegistro.rol',
            'historial.usuario.rol',
            'derivaciones.especialista',
        ]);

        return view('bienestar::casos.show', compact('caso'));
    }

    /**
     * Cambia el estado del caso con registro de auditoría en HistorialEstadoCaso
     */
    public function cambiarEstado(CambiarEstadoCasoRequest $request, CasoBienestar $caso): RedirectResponse
    {
        if ($request->user()->cannot('cambiarEstado', $caso)) {
            return back()->with('warning', 'No tienes autorización para cambiar el estado de este caso.');
        }

        $this->casoService->cambiarEstado(
            $caso,
            $request->estado_nuevo,
            $request->comentario,
            Auth::user()
        );

        return redirect()->route('bienestar.casos.show', $caso->id_caso)
            ->with('success', "Estado del expediente actualizado a '" . strtoupper($request->estado_nuevo) . "' correctamente.");
    }
}
