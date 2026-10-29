@extends('bienestar::layout')

@section('title', 'Editar Estudiante')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">Editar Estudiante</h4>
                <span class="text-muted small">Actualización de datos académicos y personales</span>
            </div>
            <a href="{{ route('bienestar.estudiantes.show', $estudiante->id_estudiante) }}" class="btn btn-outline-secondary btn-sm">
                Volver a la ficha
            </a>
        </div>

        <div class="card shadow-sm border">
            <div class="card-body p-4">
                <form action="{{ route('bienestar.estudiantes.update', $estudiante->id_estudiante) }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3 small">
                        1. Identificación y Cuenta
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="codigo_universitario" class="form-label small fw-semibold">Código Universitario <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="codigo_universitario" 
                                   id="codigo_universitario" 
                                   maxlength="10" 
                                   class="form-control form-control-sm @error('codigo_universitario') is-invalid @enderror" 
                                   value="{{ old('codigo_universitario', $estudiante->codigo_universitario) }}" 
                                   required>
                            @error('codigo_universitario')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="dni" class="form-label small fw-semibold">DNI (8 dígitos) <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="dni" 
                                   id="dni" 
                                   maxlength="8" 
                                   class="form-control form-control-sm @error('dni') is-invalid @enderror" 
                                   value="{{ old('dni', $estudiante->dni) }}" 
                                   required>
                            @error('dni')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="correo" class="form-label small fw-semibold">Correo Institucional <span class="text-danger">*</span></label>
                            <input type="email" 
                                   name="correo" 
                                   id="correo" 
                                   maxlength="100" 
                                   class="form-control form-control-sm @error('correo') is-invalid @enderror" 
                                   value="{{ old('correo', $estudiante->usuario->correo ?? '') }}" 
                                   required>
                            @error('correo')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3 small">
                        2. Datos Personales
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nombres" class="form-label small fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nombres" 
                                   id="nombres" 
                                   maxlength="80" 
                                   class="form-control form-control-sm @error('nombres') is-invalid @enderror" 
                                   value="{{ old('nombres', $estudiante->nombres) }}" 
                                   required>
                            @error('nombres')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label small fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="apellidos" 
                                   id="apellidos" 
                                   maxlength="80" 
                                   class="form-control form-control-sm @error('apellidos') is-invalid @enderror" 
                                   value="{{ old('apellidos', $estudiante->apellidos) }}" 
                                   required>
                            @error('apellidos')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="sexo" class="form-label small fw-semibold">Sexo <span class="text-danger">*</span></label>
                            <select name="sexo" id="sexo" class="form-select form-select-sm @error('sexo') is-invalid @enderror" required>
                                <option value="M" {{ old('sexo', $estudiante->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $estudiante->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                            @error('sexo')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label small fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="fecha_nacimiento" 
                                   id="fecha_nacimiento" 
                                   class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror" 
                                   value="{{ old('fecha_nacimiento', $estudiante->fecha_nacimiento ? $estudiante->fecha_nacimiento->format('Y-m-d') : '') }}" 
                                   required>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="telefono" class="form-label small fw-semibold">Teléfono / Celular</label>
                            <input type="text" 
                                   name="telefono" 
                                   id="telefono" 
                                   maxlength="15" 
                                   class="form-control form-control-sm @error('telefono') is-invalid @enderror" 
                                   value="{{ old('telefono', $estudiante->telefono) }}">
                            @error('telefono')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3 small">
                        3. Residencia y Estado Académico
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="ciclo_actual" class="form-label small fw-semibold">Ciclo Académico Actual <span class="text-danger">*</span></label>
                            <select name="ciclo_actual" id="ciclo_actual" class="form-select form-select-sm @error('ciclo_actual') is-invalid @enderror" required>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ (int) old('ciclo_actual', $estudiante->ciclo_actual) === $i ? 'selected' : '' }}>Ciclo {{ $i }}</option>
                                @endfor
                            </select>
                            @error('ciclo_actual')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="zona_procedencia" class="form-label small fw-semibold">Zona de Procedencia <span class="text-danger">*</span></label>
                            <select name="zona_procedencia" id="zona_procedencia" class="form-select form-select-sm @error('zona_procedencia') is-invalid @enderror" required>
                                <option value="Costa" {{ old('zona_procedencia', $estudiante->zona_procedencia) === 'Costa' ? 'selected' : '' }}>Costa</option>
                                <option value="Sierra" {{ old('zona_procedencia', $estudiante->zona_procedencia) === 'Sierra' ? 'selected' : '' }}>Sierra</option>
                                <option value="Selva" {{ old('zona_procedencia', $estudiante->zona_procedencia) === 'Selva' ? 'selected' : '' }}>Selva</option>
                            </select>
                            @error('zona_procedencia')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="lugar_procedencia" class="form-label small fw-semibold">Lugar de Procedencia <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="lugar_procedencia" 
                                   id="lugar_procedencia" 
                                   maxlength="100" 
                                   class="form-control form-control-sm @error('lugar_procedencia') is-invalid @enderror" 
                                   value="{{ old('lugar_procedencia', $estudiante->lugar_procedencia) }}" 
                                   required>
                            @error('lugar_procedencia')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="direccion_actual" class="form-label small fw-semibold">Dirección Actual de Residencia</label>
                            <input type="text" 
                                   name="direccion_actual" 
                                   id="direccion_actual" 
                                   maxlength="255" 
                                   class="form-control form-control-sm @error('direccion_actual') is-invalid @enderror" 
                                   value="{{ old('direccion_actual', $estudiante->direccion_actual) }}">
                            @error('direccion_actual')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bienestar.estudiantes.show', $estudiante->id_estudiante) }}" class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
