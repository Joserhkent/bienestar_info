@extends('bienestar::layout')

@section('title', "Solicitud #SOL-" . str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) . " — Bienestar UNT")

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm">
            Volver
        </a>
        <h4 class="fw-bold mb-0 text-dark">
            Solicitud #SOL-{{ str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) }}
        </h4>
        <span class="badge bg-light text-dark border">
            {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Columna izquierda: Información del estudiante y solicitud -->
    <div class="col-md-7">
        <!-- Datos del estudiante -->
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Datos del estudiante</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Estudiante:</span>
                        <strong class="text-dark">{{ $solicitud->estudiante->nombres }} {{ $solicitud->estudiante->apellidos }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Código universitario:</span>
                        <span>{{ $solicitud->estudiante->codigo_universitario }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">DNI:</span>
                        <span>{{ $solicitud->estudiante->dni }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Correo:</span>
                        <span>{{ $solicitud->estudiante->usuario?->correo ?? 'No asignado' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Ciclo académico:</span>
                        <span>Ciclo {{ $solicitud->estudiante->ciclo_actual }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Procedencia:</span>
                        <span>{{ $solicitud->estudiante->lugar_procedencia ?? 'Trujillo' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle del pedido -->
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Detalle de la solicitud</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small d-block">Tipo de apoyo:</span>
                    <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $solicitud->tipo_apoyo)) }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Fecha de envío:</span>
                    <span>{{ $solicitud->fecha_envio->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-muted small d-block mb-1">Motivo y justificación:</span>
                    <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap;">{{ $solicitud->motivo }}</div>
                </div>
            </div>
        </div>

        <!-- Documentos sustentatorios -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold text-dark">Documentos adjuntos</span>
                <span class="badge bg-light text-dark border">{{ $solicitud->documentos->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($solicitud->documentos as $doc)
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <span class="fw-medium text-dark d-block">{{ $doc->nombre_archivo }}</span>
                                <span class="text-muted small">{{ $doc->tipo_documento }} &bull; {{ $doc->tamano_kb }} KB</span>
                            </div>
                            @can('download', $doc)
                                <a href="{{ route('bienestar.solicitudes.documentos.descargar', $doc->id_documento) }}" class="btn btn-outline-secondary btn-sm">
                                    Descargar
                                </a>
                            @endcan
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small">No se registran documentos adjuntos.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Dictamen -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Dictamen</span>
            </div>
            <div class="card-body">
                @if($solicitud->fecha_resolucion)
                    <div class="mb-3">
                        <span class="text-muted small d-block">Resultado:</span>
                        <span class="badge bg-light text-dark border fs-6">
                            {{ ucfirst($solicitud->estado) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block">Revisado por:</span>
                        <span>{{ $solicitud->usuarioRevisa?->correo ?? 'Personal de Bienestar' }}</span>
                        <span class="text-muted small d-block">Fecha: {{ $solicitud->fecha_resolucion->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($solicitud->observacion)
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Observaciones:</span>
                            <div class="p-2 bg-light rounded border text-dark small">{{ $solicitud->observacion }}</div>
                        </div>
                    @endif
                @else
                    <div class="text-muted small mb-3">
                        La solicitud se encuentra pendiente de revisión.
                    </div>
                @endif

                @can('dictaminar', $solicitud)
                    <hr>
                    <span class="fw-semibold text-dark d-block mb-2">Emitir dictamen</span>
                    <form action="{{ route('bienestar.solicitudes.dictaminar', $solicitud->id_solicitud) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small text-muted">Decisión <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="estado" id="estadoAprobada" value="aprobada" {{ old('estado', $solicitud->estado) === 'aprobada' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="estadoAprobada">Aprobar</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="estado" id="estadoRechazada" value="rechazada" {{ old('estado', $solicitud->estado) === 'rechazada' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="estadoRechazada">Rechazar</label>
                                </div>
                            </div>
                            @error('estado')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observacion" class="form-label small text-muted">
                                Observaciones / Motivo
                                <span class="text-danger" id="span-requerido" style="display: none;">(obligatorio al rechazar)</span>
                            </label>
                            <textarea name="observacion" id="observacion" rows="3" class="form-control form-control-sm @error('observacion') is-invalid @enderror" placeholder="Fundamento del dictamen...">{{ old('observacion', $solicitud->observacion) }}</textarea>
                            @error('observacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            Guardar dictamen
                        </button>
                    </form>
                @endcan
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
