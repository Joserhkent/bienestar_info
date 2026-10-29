@extends('bienestar::layout')

@section('title', "Cita #CIT-" . str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) . " — Bienestar UNT")

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('bienestar.citas.index') }}" class="btn btn-outline-secondary btn-sm">
            Volver
        </a>
        <h4 class="fw-bold mb-0 text-dark">
            Cita #CIT-{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}
        </h4>
        <span class="badge bg-light text-dark border">
            {{ ucfirst($cita->estado) }}
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Columna izquierda: Datos principales de la cita -->
    <div class="col-md-7">
        <!-- Datos del estudiante -->
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Estudiante</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Nombres y apellidos:</span>
                        <strong class="text-dark">{{ $cita->estudiante->nombres }} {{ $cita->estudiante->apellidos }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Código universitario:</span>
                        <span>{{ $cita->estudiante->codigo_universitario }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">DNI:</span>
                        <span>{{ $cita->estudiante->dni }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Ciclo académico:</span>
                        <span>Ciclo {{ $cita->estudiante->ciclo_actual }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de la cita -->
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Detalle de la atención</span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Tipo de atención:</span>
                        <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $cita->tipo_cita)) }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Especialista:</span>
                        <span>{{ $cita->horario?->especialista?->nombres }} {{ $cita->horario?->especialista?->apellidos }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Fecha:</span>
                        <span>{{ $cita->horario?->fecha?->format('d/m/Y') }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Horario:</span>
                        <span>{{ $cita->horario?->hora_inicio }} - {{ $cita->horario?->hora_fin }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Fecha de solicitud:</span>
                        <span class="text-muted small">{{ $cita->fecha_solicitud->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($cita->fecha_confirmacion)
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Fecha de confirmación:</span>
                            <span class="text-muted small">{{ $cita->fecha_confirmacion->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                <div>
                    <span class="text-muted small d-block mb-1">Motivo de consulta (desencriptado):</span>
                    <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap;">{{ $cita->motivo }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Estado y Acciones -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Gestión de la cita</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">Estado actual:</span>
                    <span class="badge bg-light text-dark border fs-6">
                        {{ ucfirst($cita->estado) }}
                    </span>
                </div>

                <!-- Confirmar cita (Especialista) -->
                @if($cita->estado === 'solicitada')
                    @can('confirmar', $cita)
                        <form action="{{ route('bienestar.citas.confirmar', $cita->id_cita) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                Confirmar cita
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- Marcar como realizada (Especialista) -->
                @if($cita->estado === 'confirmada')
                    @can('atender', $cita)
                        <form action="{{ route('bienestar.citas.atender', $cita->id_cita) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                Marcar como realizada
                            </button>
                        </form>
                    @endcan
                @endif

                <!-- Cancelar cita (Estudiante o Especialista) -->
                @if(!in_array($cita->estado, ['cancelada', 'realizada']))
                    @can('cancelar', $cita)
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                            Cancelar cita
                        </button>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal cancelar cita -->
@can('cancelar', $cita)
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bienestar.citas.cancelar', $cita->id_cita) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Cancelar cita</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="text-muted small mb-3">
                        Al cancelar la cita, el turno de atención quedará liberado automáticamente para que otro estudiante pueda agendarlo.
                    </p>
                    <div class="mb-3">
                        <label for="motivo_cancelacion" class="form-label text-muted small">Motivo de cancelación (opcional)</label>
                        <textarea name="motivo_cancelacion" id="motivo_cancelacion" rows="3" class="form-control form-control-sm" placeholder="Indique la razón de la cancelación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        Confirmar cancelación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
