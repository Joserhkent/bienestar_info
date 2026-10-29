@extends('bienestar::layout')

@section('title', 'Solicitudes de Apoyo Socioeconómico — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-check-fill text-primary me-2"></i>Solicitudes de Apoyo Socioeconómico (Proceso P2)
        </h2>
        <p class="text-muted mb-0">Gestión de becas alimentarias, comedor universitario y subsidios estudiantiles — Informática UNT</p>
    </div>
    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante')
        <div>
            <a href="{{ route('bienestar.solicitudes.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud de Apoyo
            </a>
        </div>
    @endif
</div>

{{-- FILTROS PARA PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(Auth::user()->rol && in_array(Auth::user()->rol->nombre, ['Personal de Bienestar', 'Administrador']))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bienestar.solicitudes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Buscar Estudiante</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control border-start-0" placeholder="Nombre, apellido o código..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Tipo de Apoyo</label>
                    <select name="tipo_apoyo" class="form-select">
                        <option value="">-- Todos los tipos --</option>
                        <option value="comedor_universitario" {{ request('tipo_apoyo') === 'comedor_universitario' ? 'selected' : '' }}>Comedor Universitario</option>
                        <option value="beca_alimentaria" {{ request('tipo_apoyo') === 'beca_alimentaria' ? 'selected' : '' }}>Beca Alimentaria</option>
                        <option value="subsidio_transporte" {{ request('tipo_apoyo') === 'subsidio_transporte' ? 'selected' : '' }}>Subsidio de Transporte</option>
                        <option value="exoneracion_pago" {{ request('tipo_apoyo') === 'exoneracion_pago' ? 'selected' : '' }}>Exoneración de Pagos</option>
                        <option value="otro" {{ request('tipo_apoyo') === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Estado del Dictamen</label>
                    <select name="estado" class="form-select">
                        <option value="">-- Todos los estados --</option>
                        <option value="enviada" {{ request('estado') === 'enviada' ? 'selected' : '' }}>Enviada</option>
                        <option value="en_revision" {{ request('estado') === 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                        <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- TABLA DE SOLICITUDES --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-list-task text-primary me-2"></i>
            {{ Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante' ? 'Mis Solicitudes Presentadas' : 'Padrón de Solicitudes Recibidas' }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $solicitudes->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Solicitud</th>
                        @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                            <th>Estudiante Solicitante</th>
                        @endif
                        <th>Tipo de Apoyo</th>
                        <th>Fecha Envío</th>
                        <th>Adjuntos (R16)</th>
                        <th>Estado</th>
                        <th>Resolución</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $solicitud)
                        @php
                            $estadoBadge = match($solicitud->estado) {
                                'aprobada' => 'bg-success',
                                'rechazada' => 'bg-danger',
                                'en_revision' => 'bg-warning text-dark',
                                default => 'bg-primary'
                            };
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary">#SOL-{{ str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) }}</td>
                            @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                                <td>
                                    <strong>{{ $solicitud->estudiante->nombres }} {{ $solicitud->estudiante->apellidos }}</strong>
                                    <span class="d-block small text-muted">Cod: {{ $solicitud->estudiante->codigo_universitario }}</span>
                                </td>
                            @endif
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucwords(str_replace('_', ' ', $solicitud->tipo_apoyo)) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $solicitud->fecha_envio->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-paperclip me-1"></i>{{ $solicitud->documentos->count() }} archivo(s)
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $estadoBadge }}">
                                    {{ strtoupper(str_replace('_', ' ', $solicitud->estado)) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                @if($solicitud->fecha_resolucion)
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $solicitud->fecha_resolucion->format('d/m/Y') }}
                                @else
                                    <span class="text-warning"><i class="bi bi-clock me-1"></i>Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.solicitudes.show', $solicitud->id_solicitud) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                    <i class="bi bi-eye me-1"></i>Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                                No se encontraron solicitudes de apoyo registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($solicitudes->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $solicitudes->links() }}
        </div>
    @endif
</div>
@endsection
