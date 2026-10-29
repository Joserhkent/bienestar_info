@extends('bienestar::layout')

@section('title', 'Panel de Control — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-grid-fill text-primary me-2"></i>Panel de Control — {{ $rol }}
        </h2>
        <p class="text-muted mb-0">Portal de Gestión de Dirección de Escuela — Informática UNT</p>
    </div>
    <div class="text-end">
        <span class="badge bg-secondary p-2">
            <i class="bi bi-clock-history me-1"></i>Último acceso: {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y H:i') : 'Primer ingreso' }}
        </span>
    </div>
</div>

{{-- 1. CARRIL: PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(in_array($rol, ['Personal de Bienestar', 'Administrador']))
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm border-0 border-primary h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold">TOTAL CASOS (P1)</span>
                    <h3 class="fw-bold my-2 text-primary">{{ $totalCasos }}</h3>
                    <span class="small text-muted"><i class="bi bi-folder-fill me-1"></i>Expedientes registrados</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm border-0 border-warning h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold">CASOS EN ATENCIÓN</span>
                    <h3 class="fw-bold my-2 text-warning">{{ $casosAbiertos }}</h3>
                    <span class="small text-muted"><i class="bi bi-hourglass-split me-1"></i>En seguimiento activo</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('bienestar.solicitudes.index') }}" class="text-decoration-none">
                <div class="card card-stat shadow-sm border-0 border-info h-100">
                    <div class="card-body">
                        <span class="text-muted small fw-bold">SOLICITUDES APOYO (P2)</span>
                        <h3 class="fw-bold my-2 text-info">{{ $solicitudesPendientes }}</h3>
                        <span class="small text-muted"><i class="bi bi-file-earmark-text me-1"></i>Por dictaminar &rarr;</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm border-0 border-success h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold">ESTUDIANTES</span>
                    <h3 class="fw-bold my-2 text-success">{{ $totalEstudiantes }}</h3>
                    <span class="small text-muted"><i class="bi bi-people-fill me-1"></i>Padrón de Informática</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Casos Recientes -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-journal-medical text-primary me-2"></i>Expedientes de Casos Recientes (Proceso P1)
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ route('bienestar.casos.create') }}" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Caso
                </a>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">
                    Ver todos ({{ $totalCasos }})
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Caso</th>
                            <th>Estudiante</th>
                            <th>Tipo de Caso</th>
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
                                <td class="fw-bold text-primary">#CAS-{{ str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <strong>{{ $caso->estudiante->nombres }} {{ $caso->estudiante->apellidos }}</strong>
                                    <span class="d-block small text-muted">Cod: {{ $caso->estudiante->codigo_universitario }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $caso->tipoCaso->nombre }}</span></td>
                                <td>
                                    @php
                                        $prioridadBadge = match($caso->prioridad) {
                                            'urgente' => 'bg-danger',
                                            'alta' => 'bg-warning text-dark',
                                            'media' => 'bg-info text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $prioridadBadge }}">{{ strtoupper($caso->prioridad) }}</span>
                                </td>
                                <td><span class="badge bg-primary">{{ str_replace('_', ' ', strtoupper($caso->estado)) }}</span></td>
                                <td>
                                    @if($caso->confidencial)
                                        <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Sí</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $caso->fecha_registro->format('d/m/Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('bienestar.casos.show', $caso->id_caso) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No se registran casos activos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- 2. CARRIL: ESPECIALISTA (PSICÓLOGO / TUTOR) --}}
@if($rol === 'Especialista')
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card card-stat shadow-sm border-0 border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold">CITAS SOLICITADAS (P3)</span>
                            <h3 class="fw-bold my-2 text-info">{{ $citasPendientes }}</h3>
                            <span class="small text-muted">Pendientes de confirmación</span>
                        </div>
                        <i class="bi bi-calendar2-check display-5 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-stat shadow-sm border-0 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold">CASOS DERIVADOS (P4)</span>
                            <h3 class="fw-bold my-2 text-primary">{{ $casosDerivados }}</h3>
                            <span class="small text-muted">Bajo tu seguimiento clínico/tutoría</span>
                        </div>
                        <i class="bi bi-arrow-left-right display-5 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-calendar-week text-primary me-2"></i>Agenda y Sesiones de Atención (P3 & P4)
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Como especialista, gestionas los horarios de consulta psicológica y registras las notas de evolución confidenciales amparadas por el Estatuto UNT.</p>
        </div>
    </div>
@endif

{{-- 3. CARRIL: ESTUDIANTE --}}
@if($rol === 'Estudiante')
    <div class="row g-4 mb-4">
        <!-- Mis Solicitudes de Apoyo -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-card-checklist text-success me-2"></i>Mis Solicitudes de Apoyo (P2)
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bienestar.solicitudes.create') }}" class="btn btn-outline-success btn-sm fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i>Nueva
                        </a>
                        <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-light btn-sm border">
                            Ver todas
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($misSolicitudes as $sol)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <a href="{{ route('bienestar.solicitudes.show', $sol->id_solicitud) }}" class="fw-bold text-decoration-none text-primary">
                                        {{ ucwords(str_replace('_', ' ', $sol->tipo_apoyo)) }}
                                    </a>
                                    @php
                                        $solBadge = match($sol->estado) {
                                            'aprobada' => 'bg-success',
                                            'rechazada' => 'bg-danger',
                                            'en_revision' => 'bg-warning text-dark',
                                            default => 'bg-primary'
                                        };
                                    @endphp
                                    <span class="badge {{ $solBadge }}">{{ strtoupper($sol->estado) }}</span>
                                </div>
                                <p class="text-muted small mb-1">{{ Str::limit($sol->motivo, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>Enviado: {{ $sol->fecha_envio->format('d/m/Y') }}</span>
                                    <a href="{{ route('bienestar.solicitudes.show', $sol->id_solicitud) }}" class="small fw-semibold text-primary">
                                        Ver detalle &rarr;
                                    </a>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted">No tienes solicitudes enviadas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mis Citas Agendadas -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-calendar-event text-primary me-2"></i>Mis Citas de Tutoría y Psicología (P3)
                    </h5>
                    <span class="badge bg-primary">{{ $misCitas->count() }} Cita(s)</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($misCitas as $cita)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">{{ ucwords(str_replace('_', ' ', $cita->tipo_cita)) }}</span>
                                    <span class="badge bg-info text-dark">{{ strtoupper($cita->estado) }}</span>
                                </div>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-person-badge me-1"></i>Especialista: {{ $cita->horario?->especialista?->nombres }} {{ $cita->horario?->especialista?->apellidos }}
                                </p>
                                <span class="small text-muted"><i class="bi bi-clock me-1"></i>Fecha: {{ $cita->horario?->fecha }} ({{ $cita->horario?->hora_inicio }} - {{ $cita->horario?->hora_fin }})</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted">No tienes citas agendadas actualmente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- 4. CARRIL: DOCENTE (LEY 30150 TEA) --}}
@if($rol === 'Docente')
    <div class="alert alert-info shadow-sm border-0 mb-4" role="alert">
        <h5 class="alert-heading fw-bold">
            <i class="bi bi-shield-check me-2"></i>Acceso Pedagógico Regulado — Ley N° 30150 (TEA)
        </h5>
        <p class="mb-0 small">
            Como docente universitario, tiene acceso exclusivamente a las <strong>pautas pedagógicas y metodológicas</strong> autorizadas por Bienestar Estudiantil. En estricto cumplimiento del <strong>Estatuto UNT</strong> y la privacidad del estudiante, los diagnósticos clínicos, expedientes y condiciones socioeconómicas no son divulgados.
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-journal-bookmark-fill text-warning me-2"></i>Pautas Metodológicas y Adaptaciones Activas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Estudiante</th>
                            <th>Código</th>
                            <th>Pauta Pedagógica Asignada (Desencriptada en memoria)</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pautasEstudiantes as $pivote)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $pivote->estudiante->nombres }} {{ $pivote->estudiante->apellidos }}
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $pivote->estudiante->codigo_universitario }}</span></td>
                                <td class="text-primary fw-semibold">
                                    <i class="bi bi-quote text-muted me-1"></i>{{ $pivote->pauta_pedagogica }}
                                </td>
                                <td class="small text-muted">{{ $pivote->fecha_registro->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No existen pautas pedagógicas marcadas como visibles actualmente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
