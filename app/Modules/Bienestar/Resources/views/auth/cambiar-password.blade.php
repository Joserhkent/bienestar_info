@extends('bienestar::layout')

@section('title', 'Actualización de Contraseña')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-dark fw-bold">Actualización Obligatoria de Contraseña</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    Por motivos de seguridad institucional, debe reemplazar su contraseña temporal por una contraseña definitiva antes de continuar utilizando el portal.
                </p>

                <form action="{{ route('bienestar.cambiar-contrasena.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="password_actual" class="form-label small fw-semibold">Contraseña temporal actual</label>
                        <input type="password" 
                               name="password_actual" 
                               id="password_actual" 
                               class="form-control form-control-sm @error('password_actual') is-invalid @enderror" 
                               required>
                        @error('password_actual')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Nueva contraseña</label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control form-control-sm @error('password') is-invalid @enderror" 
                               placeholder="Mínimo 8 caracteres"
                               required>
                        @error('password')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label small fw-semibold">Confirmar nueva contraseña</label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               class="form-control form-control-sm" 
                               required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Guardar nueva contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
