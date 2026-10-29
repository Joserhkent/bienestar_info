@extends('bienestar::layout')

@section('title', 'Estudiantes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Estudiantes</h4>
        <span class="text-muted small">Padrón de estudiantes registrados en la unidad académica</span>
    </div>
    @can('create', App\Modules\Bienestar\Models\Estudiante::class)
        <a href="{{ route('bienestar.estudiantes.create') }}" class="btn btn-primary btn-sm">
            Registrar estudiante
        </a>
    @endcan
</div>

<div class="card shadow-sm border mb-4">
    <div class="card-body p-3">
        <form action="{{ route('bienestar.estudiantes.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9 col-lg-10">
                <input type="text" 
                       name="buscar" 
                       class="form-control form-control-sm" 
                       placeholder="Buscar por código universitario, DNI, nombres o apellidos..." 
                       value="{{ $termino }}">
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    Buscar
                </button>
                @if(!empty($termino))
                    <a href="{{ route('bienestar.estudiantes.index') }}" class="btn btn-link btn-sm text-decoration-none">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small">
                <tr>
                    <th scope="col" style="width: 120px;">Código</th>
                    <th scope="col">Estudiante</th>
                    <th scope="col" style="width: 110px;">DNI</th>
                    <th scope="col" style="width: 90px;">Ciclo</th>
                    <th scope="col">Procedencia</th>
                    <th scope="col">Correo Institucional</th>
                    <th scope="col" class="text-end" style="width: 130px;">Acciones</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($estudiantes as $estudiante)
                    <tr>
                        <td class="fw-semibold text-secondary">{{ $estudiante->codigo_universitario }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $estudiante->nombre_completo }}</div>
                            <span class="text-muted">{{ $estudiante->sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
                        </td>
                        <td>{{ $estudiante->dni }}</td>
                        <td><span class="badge bg-light text-dark border">Ciclo {{ $estudiante->ciclo_actual }}</span></td>
                        <td>
                            <div>{{ $estudiante->lugar_procedencia }}</div>
                            <span class="text-muted small">({{ $estudiante->zona_procedencia }})</span>
                        </td>
                        <td>{{ $estudiante->usuario->correo ?? 'Sin cuenta' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('bienestar.estudiantes.show', $estudiante->id_estudiante) }}" 
                                   class="btn btn-outline-secondary btn-sm" 
                                   title="Ver expediente">
                                    Ver
                                </a>
                                @can('update', $estudiante)
                                    <a href="{{ route('bienestar.estudiantes.edit', $estudiante->id_estudiante) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Editar datos">
                                        Editar
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No se encontraron registros de estudiantes con los criterios indicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($estudiantes->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $estudiantes->links() }}
        </div>
    @endif
</div>
@endsection
