@extends('bienestar::layout')

@section('title', 'Nuevo Caso de Bienestar — UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-file-earmark-plus text-primary me-2"></i>Apertura de Expediente de Bienestar (P1)
                </h5>
                <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al listado
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bienestar.casos.store') }}" method="POST">
                    @csrf

                    <!-- 1. Estudiante -->
                    <div class="mb-3">
                        <label for="id_estudiante" class="form-label fw-semibold">Estudiante de Informática: <span class="text-danger">*</span></label>
                        <select name="id_estudiante" id="id_estudiante" class="form-select @error('id_estudiante') is-invalid @enderror" required>
                            <option value="">-- Seleccionar estudiante --</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id_estudiante }}" {{ old('id_estudiante') == $est->id_estudiante ? 'selected' : '' }}>
                                    {{ $est->apellidos }}, {{ $est->nombres }} — Cod: {{ $est->codigo_universitario }} (Ciclo {{ $est->ciclo_actual }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_estudiante')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- 2. Tipología de Caso -->
                        <div class="col-md-6">
                            <label for="id_tipo_caso" class="form-label fw-semibold">Tipo de Caso: <span class="text-danger">*</span></label>
                            <select name="id_tipo_caso" id="id_tipo_caso" class="form-select @error('id_tipo_caso') is-invalid @enderror" required>
                                <option value="">-- Seleccionar tipo --</option>
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

                        <!-- 3. Prioridad -->
                        <div class="col-md-6">
                            <label for="prioridad" class="form-label fw-semibold">Prioridad de Atención: <span class="text-danger">*</span></label>
                            <select name="prioridad" id="prioridad" class="form-select @error('prioridad') is-invalid @enderror" required>
                                <option value="baja" {{ old('prioridad') === 'baja' ? 'selected' : '' }}>Baja (Preventiva)</option>
                                <option value="media" {{ old('prioridad', 'media') === 'media' ? 'selected' : '' }}>Media (Regular)</option>
                                <option value="alta" {{ old('prioridad') === 'alta' ? 'selected' : '' }}>Alta (Riesgo Académico / Social)</option>
                                <option value="urgente" {{ old('prioridad') === 'urgente' ? 'selected' : '' }}>Urgente (Crisis / Salud mental / Emergencia)</option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 4. Confidencialidad -->
                    <div class="mb-3 p-3 bg-light rounded border">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="confidencial" name="confidencial" value="1" 
                                   {{ old('confidencial', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark" for="confidencial">
                                <i class="bi bi-lock-fill text-danger me-1"></i>Expediente Confidencial (Protegido por Estatuto UNT)
                            </label>
                        </div>
                        <span class="form-text small text-muted">
                            Si está marcado, solo Personal de Bienestar y el Especialista derivado podrán acceder. El docente y terceros no tendrán visibilidad del caso.
                        </span>
                    </div>

                    <!-- 5. Descripción -->
                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-semibold">Resumen y Motivo de Apertura: <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="descripcion" rows="5" 
                                  class="form-control @error('descripcion') is-invalid @enderror" 
                                  placeholder="Detalle la situación observada o reportada por el estudiante..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('bienestar.casos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check2-circle me-1"></i>Registrar Expediente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
