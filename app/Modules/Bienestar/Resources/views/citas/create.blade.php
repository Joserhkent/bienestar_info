@extends('bienestar::layout')

@section('title', 'Agendar cita — Bienestar UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0 text-dark">Agendar cita</h4>
            <a href="{{ route('bienestar.citas.index') }}" class="btn btn-outline-secondary btn-sm">
                Volver
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-2">
                <span class="text-muted small">Estudiante:</span>
                <strong class="text-dark">{{ Auth::user()->estudiante?->nombres }} {{ Auth::user()->estudiante?->apellidos }}</strong>
                <span class="text-muted small ms-2">({{ Auth::user()->estudiante?->codigo_universitario }})</span>
            </div>

            <form action="{{ route('bienestar.citas.store') }}" method="POST" class="card-body p-4">
                @csrf

                <!-- Tipo de cita -->
                <div class="mb-3">
                    <label for="tipo_cita" class="form-label">
                        Tipo de atención <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('tipo_cita') is-invalid @enderror" id="tipo_cita" name="tipo_cita" required>
                        <option value="">Seleccione tipo de atención...</option>
                        <option value="psicologia" {{ old('tipo_cita') === 'psicologia' ? 'selected' : '' }}>Consulta psicológica</option>
                        <option value="tutoria_academica" {{ old('tipo_cita') === 'tutoria_academica' ? 'selected' : '' }}>Tutoría académica</option>
                        <option value="apoyo_social" {{ old('tipo_cita') === 'apoyo_social' ? 'selected' : '' }}>Evaluación de servicio social</option>
                        <option value="adaptada_tea" {{ old('tipo_cita') === 'adaptada_tea' ? 'selected' : '' }}>Atención adaptada e inclusión</option>
                    </select>
                    @error('tipo_cita')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Turno disponible -->
                <div class="mb-3">
                    <label for="id_horario" class="form-label">
                        Horario de atención disponible <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('id_horario') is-invalid @enderror" id="id_horario" name="id_horario" required>
                        <option value="">Seleccione un turno disponible...</option>
                        @forelse($horarios as $h)
                            <option value="{{ $h->id_horario }}" {{ old('id_horario') == $h->id_horario ? 'selected' : '' }}>
                                {{ $h->fecha->format('d/m/Y') }} — {{ $h->hora_inicio }} a {{ $h->hora_fin }} | {{ $h->especialista?->nombres }} {{ $h->especialista?->apellidos }} ({{ $h->especialista?->especialidad }})
                            </option>
                        @empty
                            <option value="" disabled>No hay turnos disponibles actualmente</option>
                        @endforelse
                    </select>
                    @error('id_horario')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Motivo de la cita -->
                <div class="mb-4">
                    <label for="motivo" class="form-label">
                        Motivo de consulta <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('motivo') is-invalid @enderror" id="motivo" name="motivo" rows="4" placeholder="Describa brevemente el motivo de su solicitud de atención..." required>{{ old('motivo') }}</textarea>
                    <div class="form-text text-muted small">
                        Mínimo 10 caracteres. Esta información se almacena con cifrado y confidencialidad médica/psicológica.
                    </div>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('bienestar.citas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        Reservar cita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
