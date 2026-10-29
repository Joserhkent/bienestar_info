@extends('bienestar::layout')

@section('title', 'Solicitudes — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Solicitudes</h4>
    </div>
    @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante')
        <div>
            <a href="{{ route('bienestar.solicitudes.create') }}" class="btn btn-primary btn-sm">
                Nueva solicitud
            </a>
        </div>
    @endif
</div>

{{-- FILTROS PARA PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(Auth::user()->rol && in_array(Auth::user()->rol->nombre, ['Personal de Bienestar', 'Administrador']))
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('bienestar.solicitudes.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Buscar estudiante:</label>
                    <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, apellido o código..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Tipo de apoyo:</label>
                    <select name="tipo_apoyo" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        <option value="comedor_universitario" {{ request('tipo_apoyo') === 'comedor_universitario' ? 'selected' : '' }}>Comedor universitario</option>
                        <option value="beca_alimentaria" {{ request('tipo_apoyo') === 'beca_alimentaria' ? 'selected' : '' }}>Beca alimentaria</option>
                        <option value="subsidio_transporte" {{ request('tipo_apoyo') === 'subsidio_transporte' ? 'selected' : '' }}>Subsidio de transporte</option>
                        <option value="exoneracion_pago" {{ request('tipo_apoyo') === 'exoneracion_pago' ? 'selected' : '' }}>Exoneración de pagos</option>
                        <option value="otro" {{ request('tipo_apoyo') === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Estado:</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="enviada" {{ request('estado') === 'enviada' ? 'selected' : '' }}>Enviada</option>
                        <option value="en_revision" {{ request('estado') === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                        <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                    <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Tabla de solicitudes -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold text-dark">
            {{ Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante' ? 'Mis solicitudes' : 'Solicitudes recibidas' }}
        </span>
        <span class="badge bg-light text-dark border">{{ $solicitudes->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° de solicitud</th>
                        @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                            <th>Estudiante</th>
                        @endif
                        <th>Tipo de apoyo</th>
                        <th>Fecha de envío</th>
                        <th>Adjuntos</th>
                        <th>Estado</th>
                        <th>Resolución</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $solicitud)
                        <tr>
                            <td class="fw-medium text-dark">#SOL-{{ str_pad($solicitud->id_solicitud, 4, '0', STR_PAD_LEFT) }}</td>
                            @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                                <td>
                                    <strong>{{ $solicitud->estudiante->nombres }} {{ $solicitud->estudiante->apellidos }}</strong>
                                    <span class="d-block text-muted small">Código: {{ $solicitud->estudiante->codigo_universitario }}</span>
                                </td>
                            @endif
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $solicitud->tipo_apoyo)) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $solicitud->fecha_envio->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $solicitud->documentos->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                @if($solicitud->fecha_resolucion)
                                    {{ $solicitud->fecha_resolucion->format('d/m/Y') }}
                                @else
                                    <span class="text-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.solicitudes.show', $solicitud->id_solicitud) }}" class="btn btn-outline-secondary btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                No se encontraron solicitudes registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($solicitudes->hasPages())
        <div class="card-footer bg-white border-0 py-2">
            {{ $solicitudes->links() }}
        </div>
    @endif
</div>
@endsection
