<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Http\Requests\DictaminarSolicitudRequest;
use App\Modules\Bienestar\Http\Requests\StoreSolicitudApoyoRequest;
use App\Modules\Bienestar\Models\DocumentoAdjunto;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Services\SolicitudApoyoService;
use Illuminate\Http\Request;

class SolicitudApoyoController extends Controller
{
    public function __construct(
        protected SolicitudApoyoService $solicitudService
    ) {}

    /**
     * Listado de solicitudes de apoyo.
     * Estudiante: solo ve sus propias solicitudes.
     * Personal de Bienestar / Administrador: ve todas con filtros.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SolicitudApoyo::class);

        $usuario = $request->user();
        $query = SolicitudApoyo::with(['estudiante.usuario', 'documentos'])
            ->orderBy('fecha_envio', 'desc');

        if ($usuario->rol && $usuario->rol->nombre === 'Estudiante') {
            if (!$usuario->estudiante) {
                abort(403, 'No tiene un perfil de estudiante vinculado.');
            }
            $query->where('id_estudiante', $usuario->estudiante->id_estudiante);
        } else {
            // Filtros para Personal de Bienestar / Admin
            if ($request->filled('estado')) {
                $query->where('estado', $request->input('estado'));
            }
            if ($request->filled('tipo_apoyo')) {
                $query->where('tipo_apoyo', $request->input('tipo_apoyo'));
            }
            if ($request->filled('buscar')) {
                $termino = '%' . $request->input('buscar') . '%';
                $query->whereHas('estudiante', function ($q) use ($termino) {
                    $q->where('nombres', 'ILIKE', $termino)
                      ->orWhere('apellidos', 'ILIKE', $termino)
                      ->orWhere('codigo_universitario', 'ILIKE', $termino);
                });
            }
        }

        $solicitudes = $query->paginate(10)->withQueryString();

        return view('bienestar::solicitudes.index', compact('solicitudes'));
    }

    /**
     * Muestra el formulario para registrar solicitud de apoyo (Solo Estudiantes).
     */
    public function create()
    {
        $this->authorize('create', SolicitudApoyo::class);

        return view('bienestar::solicitudes.create');
    }

    /**
     * Almacena una nueva solicitud de apoyo con documentos sustentatorios obligatorios (R16).
     */
    public function store(StoreSolicitudApoyoRequest $request)
    {
        $usuario = $request->user();
        $estudiante = $usuario->estudiante;

        if (!$estudiante) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'No se encontró el registro de estudiante asociado a su cuenta.']);
        }

        $solicitud = $this->solicitudService->crearSolicitud(
            $request->validated(),
            $request->file('documentos', []),
            $estudiante,
            $request->input('tipo_documento', [])
        );

        return redirect()
            ->route('bienestar.solicitudes.show', $solicitud->id_solicitud)
            ->with('success', "Su solicitud de apoyo #{$solicitud->id_solicitud} ha sido enviada con éxito para revisión.");
    }

    /**
     * Ver el detalle de una solicitud, sus adjuntos y resolución.
     * Protegido por SolicitudApoyoPolicy (aislamiento estricto por ID).
     */
    public function show(SolicitudApoyo $solicitud)
    {
        $this->authorize('view', $solicitud);

        $solicitud->load(['estudiante.usuario', 'documentos', 'usuarioRevisa']);

        return view('bienestar::solicitudes.show', compact('solicitud'));
    }

    /**
     * Dictaminar solicitud (Aprobar / Rechazar con motivo).
     * Exclusivo de Personal de Bienestar (P2).
     */
    public function dictaminar(DictaminarSolicitudRequest $request, SolicitudApoyo $solicitud)
    {
        $this->solicitudService->dictaminar(
            $solicitud,
            $request->validated('estado'),
            $request->validated('observacion'),
            $request->user()
        );

        $accion = $request->validated('estado') === 'aprobada' ? 'aprobada' : 'rechazada';

        return redirect()
            ->route('bienestar.solicitudes.show', $solicitud->id_solicitud)
            ->with('success', "La solicitud #{$solicitud->id_solicitud} ha sido {$accion} correctamente y se notificó al estudiante.");
    }

    /**
     * Descarga segura de un documento sustentatorio.
     * Protegido por DocumentoAdjuntoPolicy (solo estudiante dueño y bienestar/admin).
     */
    public function descargarDocumento(DocumentoAdjunto $documento)
    {
        $this->authorize('view', $documento);

        return $this->solicitudService->descargarDocumento($documento);
    }
}
