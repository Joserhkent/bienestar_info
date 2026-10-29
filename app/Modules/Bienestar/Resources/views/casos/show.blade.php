@extends('bienestar::layout')

@section('title', 'Expediente #CAS-' . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . ' — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="fw-bold text-dark mb-0">Expediente #CAS-{{ str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) }}</h2>
            @php
                $prBadge = match($caso->prioridad) {
                    'urgente' => 'bg-danger',
                    'alta' => 'bg-warning text-dark',
                    'media' => 'bg-info text-dark',
                    default => 'bg-secondary'
                };
            @endphp
            <span class="badge {{ $prBadge }} fs-6">{{ strtoupper($caso->prioridad) }}</span>
            <span class="badge bg-primary fs-6">{{ strtoupper(str_replace('_', ' ', $caso->estado)) }}</span>
            @if($caso->confidencial)
                <span class="badge bg-danger fs-6"><i class="bi bi-lock-fill me-1"></i>CONFIDENCIAL</span>
            @endif
        </div>
        <p class="text-muted mb-0">Apertura: {{ $caso->fecha_registro->format('d/m/Y H:i') }} | Registrado por: {{ $caso->usuarioRegistro->correo }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        @can('cambiarEstado', $caso)
            <button type="button" class="btn btn-warning fw-semibold shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#modalCambiarEstado">
                <i class="bi bi-arrow-repeat me-1"></i>Cambiar Estado
            </button>
        @endcan
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Información del Estudiante y Caso -->
    <div class="col-md-7">
        <!-- Ficha del Estudiante -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i>Datos del Estudiante
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Nombres y Apellidos:</span>
                        <strong class="fs-6">{{ $caso->estudiante->nombres }} {{ $caso->estudiante->apellidos }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Código Universitario:</span>
                        <strong class="fs-6 text-primary">{{ $caso->estudiante->codigo_universitario }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">DNI:</span>
                        <span>{{ $caso->estudiante->dni }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Ciclo Académico:</span>
                        <span class="badge bg-light text-dark border">{{ $caso->estudiante->ciclo_actual }}° Ciclo</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Procedencia:</span>
                        <span>{{ $caso->estudiante->lugar_procedencia }} ({{ ucfirst($caso->estudiante->zona_procedencia) }})</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Clasificación SISFOH:</span>
                        @if($caso->estudiante->perfilSocioeconomico)
                            <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_', ' ', $caso->estudiante->perfilSocioeconomico->clasificacion_sisfoh)) }}</span>
                        @else
                            <span class="text-muted small">Sin perfil socioeconómico</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle del Caso -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-card-text text-primary me-2"></i>Descripción del Caso ({{ $caso->tipoCaso->nombre }})
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $caso->descripcion }}</p>
            </div>
            @if($caso->fecha_cierre)
                <div class="card-footer bg-light py-2 text-muted small">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>Expediente concluido y archivado el {{ $caso->fecha_cierre->format('d/m/Y H:i') }}.
                </div>
            @endif
        </div>
    </div>

    <!-- Columna Derecha: Historial de Estados (Auditoría R08/R09) -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Historial de Estados
                </h5>
                <span class="badge bg-light text-dark border">{{ $caso->historial->count() }} registro(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($caso->historial as $h)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary">{{ strtoupper(str_replace('_', ' ', $h->estado_nuevo)) }}</span>
                                <small class="text-muted">{{ $h->fecha_cambio->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($h->estado_anterior)
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-arrow-right-short"></i>De: <span class="text-secondary">{{ strtoupper(str_replace('_', ' ', $h->estado_anterior)) }}</span>
                                </div>
                            @endif
                            <p class="mb-1 text-dark small fw-medium">{{ $h->comentario }}</p>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-person me-1"></i>{{ $h->usuario->correo }} ({{ $h->usuario->rol->nombre ?? 'Sistema' }})
                            </span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">No se registran transiciones.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Estado del Caso -->
@can('cambiarEstado', $caso)
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('bienestar.casos.cambiar-estado', $caso->id_caso) }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalCambiarEstadoLabel">
                        <i class="bi bi-arrow-repeat text-warning me-2"></i>Actualizar Estado del Caso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado Actual:</label>
                        <input type="text" class="form-control bg-light" value="{{ strtoupper(str_replace('_', ' ', $caso->estado)) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="estado_nuevo" class="form-label fw-semibold">Nuevo Estado: <span class="text-danger">*</span></label>
                        <select name="estado_nuevo" id="estado_nuevo" class="form-select" required>
                            @foreach(['registrado', 'en_atencion', 'derivado', 'en_seguimiento', 'cerrado'] as $st)
                                <option value="{{ $st }}" {{ $caso->estado === $st ? 'selected' : '' }}>
                                    {{ strtoupper(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comentario" class="form-label fw-semibold">Justificación / Motivo del Cambio: <span class="text-danger">*</span></label>
                        <textarea name="comentario" id="comentario" rows="3" class="form-control" 
                                  placeholder="Explique las acciones tomadas o el motivo de la transición..." required></textarea>
                        <span class="form-text small text-muted">Este comentario quedará registrado de forma inmutable en el historial de auditoría del caso.</span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i>Guardar Cambio de Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
