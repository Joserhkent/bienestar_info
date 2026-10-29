@extends('bienestar::layout')

@section('title', 'Caso #CAS-' . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . ' — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold text-dark mb-0">Caso #CAS-{{ str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) }}</h4>
            <span class="badge bg-light text-dark border">{{ ucfirst($caso->prioridad) }}</span>
            <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $caso->estado)) }}</span>
            @if($caso->confidencial)
                <span class="badge bg-light text-danger border border-danger-subtle">Confidencial</span>
            @endif
        </div>
        <span class="text-muted small">Registrado el {{ $caso->fecha_registro->format('d/m/Y H:i') }} por {{ $caso->usuarioRegistro->correo }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">
            Volver
        </a>
        @can('cambiarEstado', $caso)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCambiarEstado">
                Cambiar estado
            </button>
        @endcan
    </div>
</div>

<div class="row g-3">
    <!-- Columna izquierda: Datos del caso y estudiante -->
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Datos del estudiante</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Estudiante:</span>
                        <strong class="text-dark">{{ $caso->estudiante->nombres }} {{ $caso->estudiante->apellidos }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Código universitario:</span>
                        <span>{{ $caso->estudiante->codigo_universitario }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">DNI:</span>
                        <span>{{ $caso->estudiante->dni }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Ciclo académico:</span>
                        <span>Ciclo {{ $caso->estudiante->ciclo_actual }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Procedencia:</span>
                        <span>{{ $caso->estudiante->lugar_procedencia }} (Zona {{ $caso->estudiante->zona_procedencia }})</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Perfil socioeconómico:</span>
                        <span>{{ $caso->estudiante->perfilSocioeconomico ? ucfirst(str_replace('_', ' ', $caso->estudiante->perfilSocioeconomico->clasificacion_sisfoh)) : 'No registrado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Descripción ({{ $caso->tipoCaso->nombre }})</span>
            </div>
            <div class="card-body">
                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $caso->descripcion }}</p>
            </div>
            @if($caso->fecha_cierre)
                <div class="card-footer bg-light py-2 text-muted small">
                    Caso cerrado el {{ $caso->fecha_cierre->format('d/m/Y H:i') }}.
                </div>
            @endif
        </div>
    </div>

    <!-- Columna derecha: Historial -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold text-dark">Historial de cambios</span>
                <span class="badge bg-light text-dark border">{{ $caso->historial->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($caso->historial as $h)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $h->estado_nuevo)) }}</span>
                                <small class="text-muted">{{ $h->fecha_cambio->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($h->estado_anterior)
                                <div class="text-muted small mb-1">
                                    Estado anterior: {{ ucfirst(str_replace('_', ' ', $h->estado_anterior)) }}
                                </div>
                            @endif
                            <p class="mb-1 text-dark small">{{ $h->comentario }}</p>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                {{ $h->usuario->correo }}
                            </span>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small">No se registran cambios previos.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
@can('cambiarEstado', $caso)
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bienestar.casos.cambiar-estado', $caso->id_caso) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Actualizar estado del caso</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Estado actual</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ ucfirst(str_replace('_', ' ', $caso->estado)) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="estado_nuevo" class="form-label">Nuevo estado <span class="text-danger">*</span></label>
                        <select name="estado_nuevo" id="estado_nuevo" class="form-select" required>
                            @foreach(['registrado', 'en_atencion', 'derivado', 'en_seguimiento', 'cerrado'] as $st)
                                <option value="{{ $st }}" {{ $caso->estado === $st ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comentario" class="form-label">Motivo del cambio <span class="text-danger">*</span></label>
                        <textarea name="comentario" id="comentario" rows="3" class="form-control" 
                                  placeholder="Indique el motivo del cambio de estado..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
