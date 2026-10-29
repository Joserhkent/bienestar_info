@extends('bienestar::layout')

@section('title', 'Derivar caso — Bienestar UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0 text-dark">Derivar caso</h4>
            <a href="{{ route('bienestar.derivaciones.index') }}" class="btn btn-outline-secondary btn-sm">
                Volver
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Asignación a especialista</span>
            </div>

            <form action="{{ route('bienestar.derivaciones.store') }}" method="POST" class="card-body p-4">
                @csrf

                <!-- Expediente / Caso -->
                <div class="mb-3">
                    <label for="id_caso" class="form-label">
                        Expediente a derivar <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('id_caso') is-invalid @enderror" id="id_caso" name="id_caso" required>
                        <option value="">Seleccione un expediente...</option>
                        @foreach($casos as $c)
                            <option value="{{ $c->id_caso }}" {{ (old('id_caso') == $c->id_caso || ($casoSeleccionado && $casoSeleccionado->id_caso == $c->id_caso)) ? 'selected' : '' }}>
                                #CAS-{{ str_pad($c->id_caso, 4, '0', STR_PAD_LEFT) }} — {{ $c->estudiante?->nombres }} {{ $c->estudiante?->apellidos }} ({{ ucfirst($c->prioridad) }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_caso')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Especialista asignado -->
                <div class="mb-3">
                    <label for="id_especialista" class="form-label">
                        Especialista destinatario <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('id_especialista') is-invalid @enderror" id="id_especialista" name="id_especialista" required>
                        <option value="">Seleccione especialista...</option>
                        @foreach($especialistas as $esp)
                            <option value="{{ $esp->id_especialista }}" {{ old('id_especialista') == $esp->id_especialista ? 'selected' : '' }}>
                                {{ $esp->nombres }} {{ $esp->apellidos }} — {{ $esp->especialidad }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_especialista')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Motivo de la derivación -->
                <div class="mb-4">
                    <label for="motivo" class="form-label">
                        Motivo y objetivos de la derivación <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('motivo') is-invalid @enderror" id="motivo" name="motivo" rows="4" placeholder="Indique las razones clínicas, psicológicas o de tutoría por las que se canaliza el caso..." required>{{ old('motivo') }}</textarea>
                    <div class="form-text text-muted small">
                        Mínimo 10 caracteres. Al registrar la derivación, el caso pasará automáticamente a estado derivado y se notificará al especialista.
                    </div>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('bienestar.derivaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        Derivar caso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
