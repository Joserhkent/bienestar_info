@extends('bienestar::layout')

@section('title', 'Horarios de atención — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Horarios de atención</h4>
    </div>
    <a href="{{ route('bienestar.citas.index') }}" class="btn btn-outline-secondary btn-sm">
        Ver citas
    </a>
</div>

<div class="row g-3">
    <!-- Registrar nuevo horario -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Registrar turno disponible</span>
            </div>
            <form action="{{ route('bienestar.citas.horarios.store') }}" method="POST" class="card-body p-3">
                @csrf

                @if(Auth::user()->rol && in_array(Auth::user()->rol->nombre, ['Personal de Bienestar', 'Administrador']))
                    <div class="mb-2">
                        <label for="id_especialista" class="form-label text-muted small">Especialista <span class="text-danger">*</span></label>
                        <select name="id_especialista" id="id_especialista" class="form-select form-select-sm @error('id_especialista') is-invalid @enderror" required>
                            <option value="">Seleccione especialista...</option>
                            @foreach($especialistas as $esp)
                                <option value="{{ $esp->id_especialista }}" {{ old('id_especialista') == $esp->id_especialista ? 'selected' : '' }}>
                                    {{ $esp->nombres }} {{ $esp->apellidos }} ({{ $esp->especialidad }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_especialista')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="mb-2">
                    <label for="fecha" class="form-label text-muted small">Fecha de atención <span class="text-danger">*</span></label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm @error('fecha') is-invalid @enderror" 
                           value="{{ old('fecha', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                    @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="hora_inicio" class="form-label text-muted small">Hora inicio <span class="text-danger">*</span></label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="form-control form-control-sm @error('hora_inicio') is-invalid @enderror" 
                               value="{{ old('hora_inicio', '09:00') }}" required>
                        @error('hora_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="hora_fin" class="form-label text-muted small">Hora fin <span class="text-danger">*</span></label>
                        <input type="time" name="hora_fin" id="hora_fin" class="form-control form-control-sm @error('hora_fin') is-invalid @enderror" 
                               value="{{ old('hora_fin', '10:00') }}" required>
                        @error('hora_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100">
                    Guardar turno
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de horarios -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold text-dark">Turnos programados</span>
                <span class="badge bg-light text-dark border">{{ $horarios->total() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Especialista</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($horarios as $h)
                                <tr>
                                    <td>{{ $h->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $h->hora_inicio }} - {{ $h->hora_fin }}</td>
                                    <td>
                                        {{ $h->especialista?->nombres }} {{ $h->especialista?->apellidos }}
                                        <span class="d-block text-muted small">{{ $h->especialista?->especialidad }}</span>
                                    </td>
                                    <td>
                                        @if($h->estado === 'disponible')
                                            <span class="badge bg-light text-success border border-success-subtle">Disponible</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ ucfirst($h->estado) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No hay turnos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($horarios->hasPages())
                <div class="card-footer bg-white border-0 py-2">
                    {{ $horarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
