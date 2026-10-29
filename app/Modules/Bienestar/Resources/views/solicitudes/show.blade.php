@extends('bienestar::layout')

@section('title', "Solicitud #SOL-" . str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) . " — Bienestar UNT")

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Barra Superior de Navegación y Estado -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left me-1"></i>Regresar
                </a>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">
                        Solicitud #SOL-{{ str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) }}
                    </h3>
                    <small class="text-muted">Proceso P2 — Solicitud de Apoyo Socioeconómico</small>
                </div>
            </div>
            <div>
                @php
                    $estadoBadge = match($solicitud->estado) {
                        'aprobada' => 'bg-success',
                        'rechazada' => 'bg-danger',
                        'en_revision' => 'bg-warning text-dark',
                        default => 'bg-primary'
                    };
                @endphp
                <span class="badge {{ $estadoBadge }} fs-6 px-3 py-2">
                    ESTADO: {{ strtoupper(str_replace('_', ' ', $solicitud->estado)) }}
                </span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna Izquierda: Información de la Solicitud y Estudiante -->
            <div class="col-md-7">
                <!-- Tarjeta del Estudiante -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-badge text-primary me-2"></i>Datos del Estudiante Solicitante
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Nombres y Apellidos</span>
                                <strong class="text-dark">{{ $solicitud->estudiante->nombres }} {{ $solicitud->estudiante->apellidos }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Código Universitario</span>
                                <span class="badge bg-light text-dark border">{{ $solicitud->estudiante->codigo_universitario }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">DNI</span>
                                <span>{{ $solicitud->estudiante->dni }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Correo Institucional</span>
                                <span>{{ $solicitud->estudiante->usuario?->correo ?? 'No asignado' }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Ciclo Académico</span>
                                <span>Ciclo {{ $solicitud->estudiante->ciclo_actual }}</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Procedencia</span>
                                <span>{{ $solicitud->estudiante->lugar_procedencia ?? 'Trujillo' }} (Zona {{ $solicitud->estudiante->zona_procedencia }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle del Apoyo y Exposición de Motivos -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-card-text text-primary me-2"></i>Detalle de la Petición
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Tipo de Apoyo Requerido:</span>
                            <span class="fs-5 fw-bold text-primary">
                                {{ ucwords(str_replace('_', ' ', $solicitud->tipo_apoyo)) }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Fecha y Hora de Envío:</span>
                            <span class="text-dark"><i class="bi bi-clock me-1"></i>{{ $solicitud->fecha_envio->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Fundamento y Justificación:</span>
                            <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-wrap;">{{ $solicitud->motivo }}</div>
                        </div>
                    </div>
                </div>

                <!-- Documentos Sustentatorios Adjuntos (Regla R16) -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-paperclip text-primary me-2"></i>Documentos Sustentatorios Adjuntos (Regla R16)
                        </h6>
                        <span class="badge bg-secondary">{{ $solicitud->documentos->count() }} documento(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($solicitud->documentos as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 fs-3 text-danger">
                                            @if(str_contains(strtolower($doc->nombre_archivo), '.pdf'))
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            @else
                                                <i class="bi bi-file-earmark-image-fill text-info"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block">{{ $doc->nombre_archivo }}</strong>
                                            <span class="badge bg-light text-dark border me-2">{{ $doc->tipo_documento }}</span>
                                            <small class="text-muted">{{ $doc->tamano_kb }} KB &bull; Subido el {{ $doc->fecha_subida->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('bienestar.solicitudes.documentos.descargar', $doc->id_documento) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                            <i class="bi bi-cloud-arrow-down-fill me-1"></i>Descargar Seguro
                                        </a>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-4 text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>No se registran documentos adjuntos.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-light py-2 small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i>Descarga cifrada protegida por Policy (solo accesible por el solicitante y bienestar).
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Dictamen y Resolución -->
            <div class="col-md-5">
                <!-- Estado y Dictamen Oficial -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-award-fill text-primary me-2"></i>Dictamen y Resolución
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($solicitud->fecha_resolucion)
                            <!-- Resolución ya emitida -->
                            <div class="alert {{ $solicitud->estado === 'aprobada' ? 'alert-success' : 'alert-danger' }} border-0 shadow-sm mb-3">
                                <h6 class="fw-bold mb-1">
                                    <i class="bi {{ $solicitud->estado === 'aprobada' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                                    DICTAMEN: {{ strtoupper($solicitud->estado) }}
                                </h6>
                                <p class="mb-0 small">
                                    Resuelto el {{ $solicitud->fecha_resolucion->format('d/m/Y H:i') }} por 
                                    <strong>{{ $solicitud->usuarioRevisa?->correo ?? 'Dirección de Bienestar' }}</strong>.
                                </p>
                            </div>

                            @if($solicitud->observacion)
                                <div class="mb-3">
                                    <span class="text-muted small d-block mb-1">Fundamento u Observación del Dictamen:</span>
                                    <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap;">{{ $solicitud->observacion }}</div>
                                </div>
                            @endif
                        @else
                            <!-- Aún pendiente de resolución -->
                            <div class="alert alert-warning border-0 shadow-sm mb-3">
                                <h6 class="fw-bold mb-1"><i class="bi bi-hourglass-split me-2"></i>En Espera de Dictamen</h6>
                                <p class="mb-0 small">La solicitud ha sido recibida y se encuentra pendiente de evaluación por la comisión de Bienestar Estudiantil.</p>
                            </div>
                        @endif

                        {{-- Formulario para emitir Dictamen (Solo Personal de Bienestar y Administrador) --}}
                        @can('dictaminar', $solicitud)
                            <hr>
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-pen-fill text-primary me-1"></i>Emitir / Actualizar Dictamen Oficial
                            </h6>
                            <form action="{{ route('bienestar.solicitudes.dictaminar', $solicitud->id_solicitud) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Decisión del Comité <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="estado" id="estadoAprobada" value="aprobada" {{ old('estado', $solicitud->estado) === 'aprobada' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-success fw-bold" for="estadoAprobada">
                                                <i class="bi bi-check-circle me-1"></i>Aprobar Apoyo
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="estado" id="estadoRechazada" value="rechazada" {{ old('estado', $solicitud->estado) === 'rechazada' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger fw-bold" for="estadoRechazada">
                                                <i class="bi bi-x-circle me-1"></i>Rechazar Solicitud
                                            </label>
                                        </div>
                                    </div>
                                    @error('estado')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="observacion" class="form-label small fw-bold">
                                        Fundamento / Observaciones del Dictamen
                                        <span class="text-danger" id="span-requerido" style="display: none;">(Obligatorio al rechazar)</span>
                                    </label>
                                    <textarea name="observacion" id="observacion" rows="3" class="form-control @error('observacion') is-invalid @enderror" placeholder="Indique las consideraciones de la comisión evaluadora o el motivo de rechazo...">{{ old('observacion', $solicitud->observacion) }}</textarea>
                                    @error('observacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary fw-semibold">
                                        <i class="bi bi-check2-all me-1"></i>Registrar Dictamen y Notificar
                                    </button>
                                </div>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const radioRechazada = document.getElementById('estadoRechazada');
    const radioAprobada = document.getElementById('estadoAprobada');
    const spanRequerido = document.getElementById('span-requerido');
    const textareaObs = document.getElementById('observacion');

    function actualizarRequerido() {
        if (radioRechazada && radioRechazada.checked) {
            spanRequerido.style.display = 'inline';
            textareaObs.required = true;
        } else if (spanRequerido && textareaObs) {
            spanRequerido.style.display = 'none';
            textareaObs.required = false;
        }
    }

    if (radioRechazada && radioAprobada) {
        radioRechazada.addEventListener('change', actualizarRequerido);
        radioAprobada.addEventListener('change', actualizarRequerido);
        actualizarRequerido();
    }
</script>
@endpush
@endsection
