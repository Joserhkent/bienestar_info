@extends('bienestar::layout')

@section('title', 'Registrar Especialista Institucional')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Registrar Especialista</h4>
                <p class="text-muted small mb-0">Alta de profesional de atención y generación de credencial institucional.</p>
            </div>
            <a href="{{ route('bienestar.usuarios.index') }}" class="btn btn-outline-secondary btn-sm">
                Volver al directorio
            </a>
        </div>

        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">Datos del Profesional</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bienestar.usuarios.especialistas.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombres" class="form-label small fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nombres" 
                                   id="nombres" 
                                   class="form-control form-control-sm @error('nombres') is-invalid @enderror" 
                                   value="{{ old('nombres') }}" 
                                   placeholder="Ej. Silvia Marina" 
                                   required 
                                   autofocus>
                            @error('nombres')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label small fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="apellidos" 
                                   id="apellidos" 
                                   class="form-control form-control-sm @error('apellidos') is-invalid @enderror" 
                                   value="{{ old('apellidos') }}" 
                                   placeholder="Ej. Vargas Rojas" 
                                   required>
                            @error('apellidos')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label small fw-semibold">Correo institucional <span class="text-danger">*</span></label>
                        <input type="email" 
                               name="correo" 
                               id="correo" 
                               class="form-control form-control-sm @error('correo') is-invalid @enderror" 
                               value="{{ old('correo') }}" 
                               placeholder="especialista@unitru.edu.pe" 
                               required>
                        <div class="form-text small">Se asociará una cuenta de acceso con rol Especialista.</div>
                        @error('correo')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="especialidad" class="form-label small fw-semibold">Especialidad <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="especialidad" 
                                   id="especialidad" 
                                   class="form-control form-control-sm @error('especialidad') is-invalid @enderror" 
                                   value="{{ old('especialidad') }}" 
                                   placeholder="Ej. Psicología Clínica y Educativa" 
                                   required>
                            @error('especialidad')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nro_colegiatura" class="form-label small fw-semibold">Nro. de Colegiatura <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nro_colegiatura" 
                                   id="nro_colegiatura" 
                                   class="form-control form-control-sm @error('nro_colegiatura') is-invalid @enderror" 
                                   value="{{ old('nro_colegiatura') }}" 
                                   placeholder="Ej. CPP-18452" 
                                   required>
                            @error('nro_colegiatura')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info border-info small py-2 mb-4">
                        <strong>Nota de seguridad:</strong> El sistema generará automáticamente una contraseña temporal para el especialista y marcará el cambio obligatorio en su primer inicio de sesión. La contraseña se mostrará una única vez tras completar el registro.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('bienestar.usuarios.index') }}" class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Guardar especialista
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
