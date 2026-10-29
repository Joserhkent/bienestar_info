@extends('bienestar::layout')

@section('title', 'Nuevo caso — Bienestar UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold text-dark">Nuevo caso</span>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">
                    Volver
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bienestar.casos.store') }}" method="POST">
                    @csrf

                    <!-- Estudiante -->
                    <div class="mb-3">
                        <label for="id_estudiante" class="form-label">Estudiante <span class="text-danger">*</span></label>
                        <select name="id_estudiante" id="id_estudiante" class="form-select @error('id_estudiante') is-invalid @enderror" required>
                            <option value="">Seleccione un estudiante...</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id_estudiante }}" {{ old('id_estudiante') == $est->id_estudiante ? 'selected' : '' }}>
                                    {{ $est->apellidos }}, {{ $est->nombres }} — {{ $est->codigo_universitario }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_estudiante')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Tipo de caso -->
                        <div class="col-md-6">
                            <label for="id_tipo_caso" class="form-label">Tipo de caso <span class="text-danger">*</span></label>
                            <select name="id_tipo_caso" id="id_tipo_caso" class="form-select @error('id_tipo_caso') is-invalid @enderror" required>
                                <option value="">Seleccione tipo...</option>
                                @foreach($tiposCaso as $tc)
                                    <option value="{{ $tc->id_tipo_caso }}" {{ old('id_tipo_caso') == $tc->id_tipo_caso ? 'selected' : '' }}>
                                        {{ $tc->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo_caso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prioridad -->
                        <div class="col-md-6">
                            <label for="prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select name="prioridad" id="prioridad" class="form-select @error('prioridad') is-invalid @enderror" required>
                                <option value="baja" {{ old('prioridad') === 'baja' ? 'selected' : '' }}>Baja</option>
                                <option value="media" {{ old('prioridad', 'media') === 'media' ? 'selected' : '' }}>Media</option>
                                <option value="alta" {{ old('prioridad') === 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ old('prioridad') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Confidencialidad -->
                    <div class="mb-3 p-3 bg-light border rounded">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confidencial" name="confidencial" value="1" 
                                   {{ old('confidencial', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="confidencial">
                                Caso confidencial
                            </label>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            El acceso estará restringido exclusivamente a personal autorizado.
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripción del caso <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="descripcion" rows="4" 
                                  class="form-control @error('descripcion') is-invalid @enderror" 
                                  placeholder="Detalle la situación observada o reportada..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
