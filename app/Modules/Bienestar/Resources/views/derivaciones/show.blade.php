@extends('bienestar::layout')

@section('title', "Derivación #DER-" . str_pad($derivacion->id_derivacion, 4, '0', STR_PAD_LEFT) . " — Bienestar UNT")

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('bienestar.derivaciones.index') }}" class="btn btn-outline-secondary btn-sm">
            Volver
        </a>
        <h4 class="fw-bold mb-0 text-dark">
            Derivación #DER-{{ str_pad($derivacion->id_derivacion, 4, '0', STR_PAD_LEFT) }}
        </h4>
        <span class="badge bg-light text-dark border">
            {{ ucfirst(str_replace('_', ' ', $derivacion->estado)) }}
        </span>
    </div>
    <div class="d-flex gap-2">
        @can('update', $derivacion)
            @if($derivacion->estado === 'pendiente' && Auth::user()->especialista && $derivacion->id_especialista === Auth::user()->especialista->id_especialista)
                <form action="{{ route('bienestar.derivaciones.aceptar', $derivacion->id_derivacion) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        Aceptar derivación
                    </button>
                </form>
            @endif
            @if($derivacion->estado !== 'finalizada')
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                    Concluir derivación y cerrar caso
                </button>
            @endif
        @endcan
    </div>
</div>

@can('update', $derivacion)
    @if($derivacion->estado !== 'finalizada')
        <div class="modal fade" id="modalFinalizar" tabindex="-1" aria-labelledby="modalFinalizarLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('bienestar.derivaciones.finalizar', $derivacion->id_derivacion) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-semibold text-dark" id="modalFinalizarLabel">Conclusión de derivación</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Al concluir la atención especializada, el expediente del caso pasará a estado <strong>cerrado</strong> y se registrará el informe final en el historial.</p>
                        <div class="mb-2">
                            <label for="informe_conclusion" class="form-label text-muted small">Informe de conclusión <span class="text-danger">*</span></label>
                            <textarea name="informe_conclusion" id="informe_conclusion" rows="4" class="form-control form-control-sm" placeholder="Resumen de intervenciones realizadas, logros y recomendaciones finales..." required minlength="10"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Confirmar cierre</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan

<div class="row g-3">
    <!-- Columna izquierda: Datos del caso y derivación -->
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Datos del expediente</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small d-block">Caso:</span>
                    <a href="{{ route('bienestar.casos.show', $derivacion->id_caso) }}" class="fw-semibold text-decoration-none">
                        #CAS-{{ str_pad($derivacion->id_caso, 4, '0', STR_PAD_LEFT) }} ({{ $derivacion->caso?->tipoCaso?->nombre }})
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Estudiante:</span>
                    <strong class="text-dark">{{ $derivacion->caso?->estudiante?->nombres }} {{ $derivacion->caso?->estudiante?->apellidos }}</strong>
                    <span class="text-muted small d-block">Código: {{ $derivacion->caso?->estudiante?->codigo_universitario }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Especialista asignado:</span>
                    <strong class="text-dark">{{ $derivacion->especialista?->nombres }} {{ $derivacion->especialista?->apellidos }}</strong>
                    <span class="text-muted small d-block">{{ $derivacion->especialista?->especialidad }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Derivado por:</span>
                    <span>{{ $derivacion->usuarioDeriva?->correo }}</span>
                    <span class="text-muted small d-block">{{ $derivacion->fecha_derivacion->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block mb-1">Motivo de derivación:</span>
                    <div class="p-2 bg-light rounded border text-dark small" style="white-space: pre-wrap;">{{ $derivacion->motivo }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Sesiones de seguimiento -->
    <div class="col-md-7">
        <!-- Registrar nueva sesión (Especialista asignado) -->
        @can('registrarSeguimiento', $derivacion)
            @if($derivacion->estado !== 'finalizada')
                <div class="card mb-3">
                    <div class="card-header bg-white py-2">
                        <span class="fw-semibold text-dark">Registrar sesión de seguimiento</span>
                    </div>
                    <form action="{{ route('bienestar.derivaciones.seguimientos.store', $derivacion->id_derivacion) }}" method="POST" class="card-body p-3">
                        @csrf
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label for="fecha_sesion" class="form-label text-muted small">Fecha de sesión</label>
                                <input type="datetime-local" name="fecha_sesion" id="fecha_sesion" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="proxima_fecha" class="form-label text-muted small">Próxima sesión (opcional)</label>
                                <input type="date" name="proxima_fecha" id="proxima_fecha" class="form-control form-control-sm @error('proxima_fecha') is-invalid @enderror" min="{{ now()->addDay()->toDateString() }}">
                                @error('proxima_fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="nota_avance" class="form-label text-muted small">Nota de evolución clínica / tutoría <span class="text-danger">*</span></label>
                            <textarea name="nota_avance" id="nota_avance" rows="3" class="form-control form-control-sm @error('nota_avance') is-invalid @enderror" placeholder="Registre la evolución, intervenciones aplicadas y avances..." required>{{ old('nota_avance') }}</textarea>
                            @error('nota_avance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="acuerdos" class="form-label text-muted small">Acuerdos y compromisos adoptados (opcional)</label>
                            <textarea name="acuerdos" id="acuerdos" rows="2" class="form-control form-control-sm @error('acuerdos') is-invalid @enderror" placeholder="Tareas, compromisos del estudiante o pautas acordadas...">{{ old('acuerdos') }}</textarea>
                            @error('acuerdos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            Guardar sesión
                        </button>
                    </form>
                </div>
            @endif
        @endcan

        <!-- Historial de sesiones registradas -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold text-dark">Historial de sesiones</span>
                <span class="badge bg-light text-dark border">{{ $derivacion->seguimientos->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($derivacion->seguimientos as $seg)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">Sesión de atención</span>
                                <small class="text-muted">{{ $seg->fecha_sesion->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Nota de evolución (desencriptada):</span>
                                <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $seg->nota_avance }}</p>
                            </div>
                            @if($seg->acuerdos)
                                <div class="p-2 bg-light rounded border text-muted small mb-2">
                                    <strong class="text-dark">Acuerdos:</strong> {{ $seg->acuerdos }}
                                </div>
                            @endif
                            @if($seg->proxima_fecha)
                                <span class="text-muted small">
                                    Próxima sesión prevista: {{ $seg->proxima_fecha->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted small">No se registran sesiones de seguimiento aún.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
