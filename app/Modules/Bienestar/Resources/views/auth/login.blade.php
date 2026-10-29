@extends('bienestar::layout')

@section('title', 'Iniciar sesión — Bienestar UNT')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark">Acceso al sistema</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bienestar.login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo institucional</label>
                        <input type="email" class="form-control @error('correo') is-invalid @enderror" 
                               id="correo" name="correo" value="{{ old('correo') }}" 
                               placeholder="usuario@unitru.edu.pe" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group has-validation">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-toggle-password="password" title="Ver contraseña" aria-label="Ver contraseña" tabindex="-1">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg>
                                <svg class="eye-closed d-none" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                                    <path d="M11.297 9.174a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                                    <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
                                </svg>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Ingresar
                    </button>
                </form>

                @if(app()->isLocal() || config('app.env') === 'local')
                    <div class="mt-4 pt-3 border-top">
                        <label for="selector_demo" class="form-label text-muted small">Acceso rápido para demostración:</label>
                        <select id="selector_demo" class="form-select form-select-sm text-secondary" onchange="completarCredenciales(this.value, 'password123')">
                            <option value="">Seleccione una cuenta institucional...</option>
                            <option value="admin@unitru.edu.pe">Administrador (admin@unitru.edu.pe)</option>
                            <option value="bienestar@unitru.edu.pe">Personal de Bienestar (bienestar@unitru.edu.pe)</option>
                            <option value="psicologia@unitru.edu.pe">Especialista (psicologia@unitru.edu.pe)</option>
                            <option value="docente@unitru.edu.pe">Docente (docente@unitru.edu.pe)</option>
                            <option value="estudiante1@unitru.edu.pe">Estudiante (estudiante1@unitru.edu.pe)</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function completarCredenciales(correo, clave) {
    if (correo) {
        document.getElementById('correo').value = correo;
        document.getElementById('password').value = clave;
    }
}
</script>
@endpush
@endsection
