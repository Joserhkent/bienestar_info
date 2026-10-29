@extends('bienestar::layout')

@section('title', 'Panel de control — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">Panel de control</h4>
        <span class="text-muted small">{{ $rol }}</span>
    </div>
    @if($usuario->ultimo_acceso)
        <div class="text-muted small">
            Último acceso: {{ $usuario->ultimo_acceso->format('d/m/Y H:i') }}
        </div>
    @endif
</div>

{{-- PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(in_array($rol, ['Personal de Bienestar', 'Administrador']))
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body p-3">
                    <span class="text-muted small">Total de casos</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $totalCasos }}</h3>
                    <span class="text-muted small">Registrados</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body p-3">
                    <span class="text-muted small">Casos en atención</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $casosAbiertos }}</h3>
                    <span class="text-muted small">En seguimiento activo</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('bienestar.solicitudes.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small">Solicitudes de apoyo</span>
                        <h3 class="fw-bold my-1 text-dark">{{ $solicitudesPendientes }}</h3>
                        <span class="text-primary small">Pendientes de dictamen</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body p-3">
                    <span class="text-muted small">Estudiantes</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $totalEstudiantes }}</h3>
                    <span class="text-muted small">Padrón de informática</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Casos recientes -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold text-dark">Casos recientes</span>
            <div class="d-flex gap-2">
                <a href="{{ route('bienestar.casos.create') }}" class="btn btn-primary btn-sm">
                    Nuevo caso
                </a>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">
                    Ver todos ({{ $totalCasos }})
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>N° de caso</th>
                            <th>Estudiante</th>
                            <th>Tipo de caso</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Confidencial</th>
                            <th>Fecha</th>
                            <th class="text-end pe-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimosCasos as $caso)
                            <tr>
                                <td class="fw-medium text-dark">#CAS-{{ str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <strong>{{ $caso->estudiante->nombres }} {{ $caso->estudiante->apellidos }}</strong>
                                    <span class="d-block text-muted small">Código: {{ $caso->estudiante->codigo_universitario }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $caso->tipoCaso->nombre }}</span></td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ ucfirst($caso->prioridad) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $caso->estado)) }}</span>
                                </td>
                                <td>
                                    @if($caso->confidencial)
                                        <span class="badge bg-light text-danger border border-danger-subtle">Sí</span>
                                    @else
                                        <span class="text-muted small">No</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $caso->fecha_registro->format('d/m/Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('bienestar.casos.show', $caso->id_caso) }}" class="btn btn-outline-secondary btn-sm">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No se registran casos recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- ESPECIALISTA --}}
@if($rol === 'Especialista')
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-3">
                    <span class="text-muted small">Citas solicitadas</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $citasPendientes }}</h3>
                    <span class="text-muted small">Pendientes de confirmación</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-3">
                    <span class="text-muted small">Casos derivados</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $casosDerivados }}</h3>
                    <span class="text-muted small">En atención</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-2">
            <span class="fw-semibold text-dark">Agenda y atención de casos</span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-0">Gestión de consultas programadas y registro de evolución de estudiantes derivados.</p>
        </div>
    </div>
@endif

{{-- ESTUDIANTE --}}
@if($rol === 'Estudiante')
    <div class="row g-4 mb-4">
        <!-- Mis solicitudes -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold text-dark">Mis solicitudes</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bienestar.solicitudes.create') }}" class="btn btn-primary btn-sm">
                            Nueva solicitud
                        </a>
                        <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm">
                            Ver todas
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($misSolicitudes as $sol)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <a href="{{ route('bienestar.solicitudes.show', $sol->id_solicitud) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $sol->tipo_apoyo)) }}
                                    </a>
                                    <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $sol->estado)) }}</span>
                                </div>
                                <p class="text-muted small mb-1">{{ Str::limit($sol->motivo, 90) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Enviado: {{ $sol->fecha_envio->format('d/m/Y') }}</span>
                                    <a href="{{ route('bienestar.solicitudes.show', $sol->id_solicitud) }}" class="text-decoration-none small">
                                        Ver detalle
                                    </a>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted small">No tiene solicitudes enviadas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mis citas -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white py-2">
                    <span class="fw-semibold text-dark">Mis citas</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($misCitas as $cita)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $cita->tipo_cita)) }}</span>
                                    <span class="badge bg-light text-dark border">{{ ucfirst($cita->estado) }}</span>
                                </div>
                                <p class="text-muted small mb-1">
                                    Especialista: {{ $cita->horario?->especialista?->nombres }} {{ $cita->horario?->especialista?->apellidos }}
                                </p>
                                <span class="text-muted small">Fecha: {{ $cita->horario?->fecha }} ({{ $cita->horario?->hora_inicio }} - {{ $cita->horario?->hora_fin }})</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted small">No tiene citas agendadas actualmente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- DOCENTE --}}
@if($rol === 'Docente')
    <div class="card">
        <div class="card-header bg-white py-2">
            <span class="fw-semibold text-dark">Pautas pedagógicas</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Código</th>
                            <th>Pauta pedagógica asignada</th>
                            <th>Fecha de registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pautasEstudiantes as $pivote)
                            <tr>
                                <td class="fw-medium text-dark">
                                    {{ $pivote->estudiante->nombres }} {{ $pivote->estudiante->apellidos }}
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $pivote->estudiante->codigo_universitario }}</span></td>
                                <td class="text-dark">
                                    {{ $pivote->pauta_pedagogica }}
                                </td>
                                <td class="text-muted small">{{ $pivote->fecha_registro->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No existen pautas pedagógicas registradas actualmente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
