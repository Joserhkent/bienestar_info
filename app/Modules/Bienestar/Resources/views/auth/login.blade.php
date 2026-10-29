@extends('bienestar::layout')

@section('title', 'Iniciar Sesión — Bienestar UNT')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-white text-center py-4 border-0">
                <i class="bi bi-shield-lock-fill text-primary display-4"></i>
                <h4 class="fw-bold mt-2 text-dark">Acceso al Sistema</h4>
                <p class="text-muted small mb-0">Gestión de Bienestar Estudiantil — UNT</p>
            </div>
            <div class="card-body p-4 pt-0">
                <form action="{{ route('bienestar.login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="correo" class="form-label fw-semibold">Correo Institucional:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('correo') is-invalid @enderror" 
                                   id="correo" name="correo" value="{{ old('correo') }}" 
                                   placeholder="usuario@unitru.edu.pe" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Contraseña:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Portal
                    </button>
                </form>

                <hr class="my-4 text-muted">

                <!-- Accesos Rápidos para la Exposición Oral (Solo en entorno local) -->
                @if(app()->isLocal() || config('app.env') === 'local')
                    <div class="bg-light p-3 rounded border">
                        <span class="small fw-bold text-secondary d-block mb-2 text-center">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Acceso Rápido para Demostración (Expo):
                        </span>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" 
                                    onclick="fillCreds('admin@unitru.edu.pe', 'password123')">
                                <span class="badge bg-dark me-2">Admin</span> admin@unitru.edu.pe
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm text-start" 
                                    onclick="fillCreds('bienestar@unitru.edu.pe', 'password123')">
                                <span class="badge bg-primary me-2">Bienestar</span> bienestar@unitru.edu.pe
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm text-start text-dark" 
                                    onclick="fillCreds('psicologia@unitru.edu.pe', 'password123')">
                                <span class="badge bg-info text-dark me-2">Especialista</span> psicologia@unitru.edu.pe
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm text-start text-dark" 
                                    onclick="fillCreds('docente@unitru.edu.pe', 'password123')">
                                <span class="badge bg-warning text-dark me-2">Docente</span> docente@unitru.edu.pe
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm text-start" 
                                    onclick="fillCreds('estudiante1@unitru.edu.pe', 'password123')">
                                <span class="badge bg-success me-2">Estudiante</span> estudiante1@unitru.edu.pe
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillCreds(correo, pass) {
    document.getElementById('correo').value = correo;
    document.getElementById('password').value = pass;
}
</script>
@endpush
@endsection
