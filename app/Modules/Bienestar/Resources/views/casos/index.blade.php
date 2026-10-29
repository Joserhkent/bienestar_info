@extends('bienestar::layout')

@section('title', 'Casos — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Casos</h4>
    </div>
    @can('create', App\Modules\Bienestar\Models\CasoBienestar::class)
        <a href="{{ route('bienestar.casos.create') }}" class="btn btn-primary btn-sm">
            Nuevo caso
        </a>
    @endcan
</div>

<!-- Filtros de búsqueda -->
<div class="card mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('bienestar.casos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1">Buscar estudiante:</label>
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, apellido o código..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    @foreach(['registrado', 'en_atencion', 'derivado', 'en_seguimiento', 'cerrado'] as $st)
                        <option value="{{ $st }}" {{ request('estado') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Prioridad:</label>
                <select name="prioridad" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach(['baja', 'media', 'alta', 'urgente'] as $pr)
                        <option value="{{ $pr }}" {{ request('prioridad') === $pr ? 'selected' : '' }}>{{ ucfirst($pr) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Tipo:</label>
                <select name="id_tipo_caso" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposCaso as $tc)
                        <option value="{{ $tc->id_tipo_caso }}" {{ request('id_tipo_caso') == $tc->id_tipo_caso ? 'selected' : '' }}>{{ $tc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de casos -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° de caso</th>
                        <th>Estudiante</th>
                        <th>Tipo</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Confidencial</th>
                        <th>Fecha de registro</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casos as $caso)
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
                            <td class="text-muted small">{{ $caso->fecha_registro->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.casos.show', $caso->id_caso) }}" class="btn btn-outline-secondary btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">
                                No se encontraron casos registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($casos->hasPages())
        <div class="card-footer bg-white border-0 py-2">
            {{ $casos->links() }}
        </div>
    @endif
</div>
@endsection
