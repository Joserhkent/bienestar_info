<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\CancelarCitaRequest;
use App\Modules\Bienestar\Http\Requests\StoreCitaRequest;
use App\Modules\Bienestar\Http\Requests\StoreHorarioRequest;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\HorarioDisponible;
use App\Modules\Bienestar\Services\CitaService;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function __construct(
        protected CitaService $citaService
    ) {}

    /**
     * Listado de citas según el rol autenticado.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cita::class);

        $usuario = $request->user();
        $rol = $usuario->rol ? $usuario->rol->nombre : '';

        $query = Cita::with(['horario.especialista', 'estudiante.usuario'])
            ->orderBy('fecha_solicitud', 'desc');

        if ($rol === 'Estudiante') {
            if (!$usuario->estudiante) {
                abort(403, 'No tiene un perfil de estudiante vinculado.');
            }
            $query->where('id_estudiante', $usuario->estudiante->id_estudiante);
        } elseif ($rol === 'Especialista') {
            if (!$usuario->especialista) {
                abort(403, 'No tiene un perfil de especialista vinculado.');
            }
            $query->whereHas('horario', function ($q) use ($usuario) {
                $q->where('id_especialista', $usuario->especialista->id_especialista);
            });
        } else {
            // Personal de Bienestar y Administrador
            if ($request->filled('estado')) {
                $query->where('estado', $request->input('estado'));
            }
            if ($request->filled('tipo_cita')) {
                $query->where('tipo_cita', $request->input('tipo_cita'));
            }
        }

        $citas = $query->paginate(10)->withQueryString();

        return view('bienestar::citas.index', compact('citas'));
    }

    /**
     * Formulario para que el estudiante agende una nueva cita.
     */
    public function create()
    {
        $this->authorize('agendar', Cita::class);

        $horarios = HorarioDisponible::with('especialista')
            ->disponibles()
            ->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return view('bienestar::citas.create', compact('horarios'));
    }

    /**
     * Almacena una nueva cita y bloquea el horario en DB::transaction().
     */
    public function store(StoreCitaRequest $request)
    {
        $this->authorize('agendar', Cita::class);

        $usuario = $request->user();
        $estudiante = $usuario->estudiante;

        if (!$estudiante) {
            abort(403, 'No tiene un perfil de estudiante vinculado.');
        }

        $cita = $this->citaService->agendarCita(
            $request->validated(),
            $estudiante,
            $request->input('id_caso')
        );

        return redirect()
            ->route('bienestar.citas.show', $cita->id_cita)
            ->with('success', 'Su cita ha sido reservada correctamente.');
    }

    /**
     * Ver detalle de la cita (acceso restringido; 404 ante id ajeno).
     */
    public function show(Request $request, Cita $cita)
    {
        if ($request->user()->cannot('view', $cita)) {
            abort(404, 'La cita solicitada no existe o no se encuentra disponible.');
        }

        $cita->load(['horario.especialista', 'estudiante.usuario', 'caso']);

        return view('bienestar::citas.show', compact('cita'));
    }

    /**
     * Confirmar una cita solicitada (Exclusivo Especialista).
     */
    public function confirmar(Request $request, Cita $cita)
    {
        if ($request->user()->cannot('confirmar', $cita)) {
            return back()->with('warning', 'No tienes autorización para confirmar esta cita.');
        }

        if ($cita->estado !== 'solicitada') {
            return back()->with('warning', 'Solo se pueden confirmar citas en estado solicitada.');
        }

        $this->citaService->confirmarCita($cita);

        return redirect()
            ->route('bienestar.citas.show', $cita->id_cita)
            ->with('success', 'La cita ha sido confirmada.');
    }

    /**
     * Cancelar cita y liberar horario en DB::transaction().
     */
    public function cancelar(CancelarCitaRequest $request, Cita $cita)
    {
        if ($request->user()->cannot('cancelar', $cita)) {
            return back()->with('warning', 'No tienes autorización para cancelar esta cita.');
        }

        if ($cita->estado !== 'solicitada' && $cita->estado !== 'confirmada') {
            return back()->with('warning', 'Esta cita no puede ser cancelada en su estado actual.');
        }

        $this->citaService->cancelarCita(
            $cita,
            $request->user(),
            $request->input('motivo_cancelacion')
        );

        return redirect()
            ->route('bienestar.citas.show', $cita->id_cita)
            ->with('success', 'La cita ha sido cancelada y el horario ha sido liberado.');
    }

    /**
     * Marcar cita como realizada tras la atención (Exclusivo Especialista).
     */
    public function atender(Request $request, Cita $cita)
    {
        if ($request->user()->cannot('atender', $cita)) {
            return back()->with('warning', 'No tienes autorización para marcar esta cita como realizada.');
        }

        if ($cita->estado !== 'confirmada') {
            return back()->with('warning', 'Solo se pueden registrar como atendidas las citas confirmadas.');
        }

        $this->citaService->atenderCita($cita);

        return redirect()
            ->route('bienestar.citas.show', $cita->id_cita)
            ->with('success', 'La cita ha sido marcada como realizada.');
    }

    /**
     * Gestión de turnos y disponibilidad horaria.
     */
    public function horarios(Request $request)
    {
        $this->authorize('gestionarHorarios', Cita::class);

        $usuario = $request->user();
        $query = HorarioDisponible::with('especialista')->orderBy('fecha', 'desc')->orderBy('hora_inicio');

        if ($usuario->rol && $usuario->rol->nombre === 'Especialista' && $usuario->especialista) {
            $query->where('id_especialista', $usuario->especialista->id_especialista);
        }

        $horarios = $query->paginate(15);
        $especialistas = Especialista::where('estado', 'activo')->get();

        return view('bienestar::citas.horarios', compact('horarios', 'especialistas'));
    }

    /**
     * Registra un nuevo turno disponible.
     */
    public function guardarHorario(StoreHorarioRequest $request)
    {
        $this->authorize('crearHorario', Cita::class);

        $usuario = $request->user();
        $idEspecialista = $request->input('id_especialista');

        if (!$idEspecialista && $usuario->especialista) {
            $idEspecialista = $usuario->especialista->id_especialista;
        }

        if (!$idEspecialista) {
            return back()->withErrors(['id_especialista' => 'Debe indicar un especialista.']);
        }

        $this->citaService->crearHorario($request->validated(), $idEspecialista);

        return redirect()
            ->route('bienestar.citas.horarios')
            ->with('success', 'Horario de atención registrado correctamente.');
    }
}
