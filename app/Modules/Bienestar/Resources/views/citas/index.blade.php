@extends('bienestar::layout')

@section('title', 'Citas — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">
            {{ Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante' ? 'Mis citas' : 'Citas' }}
        </h4>
    </div>
    <div class="d-flex gap-2">
        @if(Auth::user()->rol && Auth::user()->rol->nombre === 'Estudiante')
            <a href="{{ route('bienestar.citas.create') }}" class="btn btn-primary btn-sm">
                Agendar cita
            </a>
        @endif
        @can('gestionarHorarios', App\Modules\Bienestar\Models\Cita::class)
            <a href="{{ route('bienestar.citas.horarios') }}" class="btn btn-outline-secondary btn-sm">
                Gestionar horarios
            </a>
        @endcan
    </div>
</div>

{{-- FILTROS PARA PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(Auth::user()->rol && in_array(Auth::user()->rol->nombre, ['Personal de Bienestar', 'Administrador']))
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('bienestar.citas.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Tipo de cita:</label>
                    <select name="tipo_cita" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        <option value="psicologia" {{ request('tipo_cita') === 'psicologia' ? 'selected' : '' }}>Psicología</option>
                        <option value="tutoria_academica" {{ request('tipo_cita') === 'tutoria_academica' ? 'selected' : '' }}>Tutoría académica</option>
                        <option value="apoyo_social" {{ request('tipo_cita') === 'apoyo_social' ? 'selected' : '' }}>Apoyo social</option>
                        <option value="adaptada_tea" {{ request('tipo_cita') === 'adaptada_tea' ? 'selected' : '' }}>Atención adaptada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Estado:</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="solicitada" {{ request('estado') === 'solicitada' ? 'selected' : '' }}>Solicitada</option>
                        <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                        <option value="realizada" {{ request('estado') === 'realizada' ? 'selected' : '' }}>Realizada</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                    <a href="{{ route('bienestar.citas.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Tabla de citas -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold text-dark">Registro de atenciones</span>
        <span class="badge bg-light text-dark border">{{ $citas->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° de cita</th>
                        @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                            <th>Estudiante</th>
                        @endif
                        @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Especialista')
                            <th>Especialista</th>
                        @endif
                        <th>Tipo</th>
                        <th>Fecha y horario</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citas as $cita)
                        <tr>
                            <td class="fw-medium text-dark">#CIT-{{ str_pad($cita->id_cita, 4, '0', STR_PAD_LEFT) }}</td>
                            @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Estudiante')
                                <td>
                                    <strong>{{ $cita->estudiante->nombres }} {{ $cita->estudiante->apellidos }}</strong>
                                    <span class="d-block text-muted small">Código: {{ $cita->estudiante->codigo_universitario }}</span>
                                </td>
                            @endif
                            @if(Auth::user()->rol && Auth::user()->rol->nombre !== 'Especialista')
                                <td>
                                    {{ $cita->horario?->especialista?->nombres }} {{ $cita->horario?->especialista?->apellidos }}
                                    <span class="d-block text-muted small">{{ $cita->horario?->especialista?->especialidad }}</span>
                                </td>
                            @endif
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $cita->tipo_cita)) }}
                                </span>
                            </td>
                            <td>
                                <span class="d-block">{{ $cita->horario?->fecha?->format('d/m/Y') }}</span>
                                <span class="text-muted small">{{ $cita->horario?->hora_inicio }} - {{ $cita->horario?->hora_fin }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($cita->estado) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.citas.show', $cita->id_cita) }}" class="btn btn-outline-secondary btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                No se encontraron citas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($citas->hasPages())
        <div class="card-footer bg-white border-0 py-2">
            {{ $citas->links() }}
        </div>
    @endif
</div>
@endsection
