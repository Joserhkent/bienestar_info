@extends('bienestar::layout')

@section('title', 'Derivaciones — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Derivaciones</h4>
    </div>
    @can('create', App\Modules\Bienestar\Models\Derivacion::class)
        <a href="{{ route('bienestar.derivaciones.create') }}" class="btn btn-primary btn-sm">
            Nueva derivación
        </a>
    @endcan
</div>

{{-- FILTROS PARA PERSONAL DE BIENESTAR / ADMINISTRADOR --}}
@if(Auth::user()->rol && in_array(Auth::user()->rol->nombre, ['Personal de Bienestar', 'Administrador']))
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('bienestar.derivaciones.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted small mb-1">Especialista:</label>
                    <select name="id_especialista" class="form-select form-select-sm">
                        <option value="">Todos los especialistas</option>
                        @foreach($especialistas as $esp)
                            <option value="{{ $esp->id_especialista }}" {{ request('id_especialista') == $esp->id_especialista ? 'selected' : '' }}>
                                {{ $esp->nombres }} {{ $esp->apellidos }} ({{ $esp->especialidad }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Estado:</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="aceptada" {{ request('estado') === 'aceptada' ? 'selected' : '' }}>Aceptada</option>
                        <option value="en_seguimiento" {{ request('estado') === 'en_seguimiento' ? 'selected' : '' }}>En seguimiento</option>
                        <option value="finalizada" {{ request('estado') === 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                    <a href="{{ route('bienestar.derivaciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Tabla de derivaciones -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold text-dark">Casos en atención especializada</span>
        <span class="badge bg-light text-dark border">{{ $derivaciones->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° de derivación</th>
                        <th>Expediente</th>
                        <th>Estudiante</th>
                        <th>Especialista</th>
                        <th>Fecha de derivación</th>
                        <th>Sesiones</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($derivaciones as $der)
                        <tr>
                            <td class="fw-medium text-dark">#DER-{{ str_pad($der->id_derivacion, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <a href="{{ route('bienestar.casos.show', $der->id_caso) }}" class="text-decoration-none">
                                    #CAS-{{ str_pad($der->id_caso, 4, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $der->caso?->estudiante?->nombres }} {{ $der->caso?->estudiante?->apellidos }}</strong>
                                <span class="d-block text-muted small">Código: {{ $der->caso?->estudiante?->codigo_universitario }}</span>
                            </td>
                            <td>
                                {{ $der->especialista?->nombres }} {{ $der->especialista?->apellidos }}
                                <span class="d-block text-muted small">{{ $der->especialista?->especialidad }}</span>
                            </td>
                            <td class="text-muted small">{{ $der->fecha_derivacion->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $der->seguimientos->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $der->estado)) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.derivaciones.show', $der->id_derivacion) }}" class="btn btn-outline-secondary btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                No se encontraron derivaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($derivaciones->hasPages())
        <div class="card-footer bg-white border-0 py-2">
            {{ $derivaciones->links() }}
        </div>
    @endif
</div>
@endsection
