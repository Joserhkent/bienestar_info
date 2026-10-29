@extends('bienestar::layout')

@section('title', 'Expedientes de Casos — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-folder2-open text-primary me-2"></i>Gestión de Casos de Bienestar (Proceso P1)
        </h2>
        <p class="text-muted mb-0">Registro, priorización y control de expedientes estudiantiles — Informática UNT</p>
    </div>
    @can('create', App\Modules\Bienestar\Models\CasoBienestar::class)
        <a href="{{ route('bienestar.casos.create') }}" class="btn btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-circle me-1"></i>Registrar Nuevo Caso
        </a>
    @endcan
</div>

<!-- Filtros y Búsqueda -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light rounded">
        <form method="GET" action="{{ route('bienestar.casos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Buscar por estudiante o código:</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="buscar" class="form-control" placeholder="Ej. Carlos Mendoza o 1021400121" value="{{ request('buscar') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Estado:</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach(['registrado', 'en_atencion', 'derivado', 'en_seguimiento', 'cerrado'] as $st)
                        <option value="{{ $st }}" {{ request('estado') === $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Prioridad:</label>
                <select name="prioridad" class="form-select">
                    <option value="">Todas</option>
                    @foreach(['baja', 'media', 'alta', 'urgente'] as $pr)
                        <option value="{{ $pr }}" {{ request('prioridad') === $pr ? 'selected' : '' }}>{{ strtoupper($pr) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Tipología:</label>
                <select name="id_tipo_caso" class="form-select">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposCaso as $tc)
                        <option value="{{ $tc->id_tipo_caso }}" {{ request('id_tipo_caso') == $tc->id_tipo_caso ? 'selected' : '' }}>{{ $tc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Filtrar</button>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Casos -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Caso</th>
                        <th>Estudiante</th>
                        <th>Tipología</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Confidencial</th>
                        <th>Fecha Registro</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casos as $caso)
                        <tr>
                            <td class="fw-bold text-primary">#CAS-{{ str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <strong>{{ $caso->estudiante->nombres }} {{ $caso->estudiante->apellidos }}</strong>
                                <span class="d-block small text-muted">Cod: {{ $caso->estudiante->codigo_universitario }} | Ciclo: {{ $caso->estudiante->ciclo_actual }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $caso->tipoCaso->nombre }}</span></td>
                            <td>
                                @php
                                    $prBadge = match($caso->prioridad) {
                                        'urgente' => 'bg-danger',
                                        'alta' => 'bg-warning text-dark',
                                        'media' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $prBadge }}">{{ strtoupper($caso->prioridad) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ strtoupper(str_replace('_', ' ', $caso->estado)) }}</span>
                            </td>
                            <td>
                                @if($caso->confidencial)
                                    <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Sí</span>
                                @else
                                    <span class="badge bg-success">No</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $caso->fecha_registro->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('bienestar.casos.show', $caso->id_caso) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                    <i class="bi bi-eye-fill me-1"></i>Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2 text-secondary opacity-50"></i>
                                No se encontraron expedientes con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($casos->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $casos->links() }}
        </div>
    @endif
</div>
@endsection
