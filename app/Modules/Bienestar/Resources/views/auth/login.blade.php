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
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
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
