@extends('bienestar::layout')

@section('title', 'Gestión de Usuarios y Especialistas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Gestión de Usuarios</h4>
        <p class="text-muted small mb-0">Control institucional de cuentas de acceso, roles y estados de servicio.</p>
    </div>
    @can('createEspecialista', App\Modules\Bienestar\Models\Usuario::class)
        <a href="{{ route('bienestar.usuarios.especialistas.create') }}" class="btn btn-primary btn-sm">
            Registrar especialista
        </a>
    @endcan
</div>

@if(session('password_temporal'))
    <div class="alert alert-warning border-warning shadow-sm mb-4">
        <h6 class="alert-heading fw-bold mb-1">Credencial Temporal Generada</h6>
        <p class="mb-2 small">
            Se ha configurado la contraseña temporal para el usuario. Comunique esta clave al titular. El sistema le exigirá actualizarla en su primer inicio de sesión.
        </p>
        <div class="d-inline-flex align-items-center gap-2 p-2 bg-white rounded border border-warning">
            <span id="clave_temporal_usr" class="font-monospace text-dark fw-bold fs-6">{{ session('password_temporal') }}</span>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2 btn-toggle-password border-0" type="button" 
                    id="btn_toggle_temp_usr"
                    onclick="toggleClaveUsuario('{{ session('password_temporal') }}')" 
                    title="Ocultar o mostrar contraseña" 
                    aria-label="Ocultar o mostrar contraseña">
                <svg class="eye-open d-none" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                </svg>
                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                    <path d="M11.297 9.174a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                    <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
                </svg>
            </button>
        </div>
        <div class="small text-muted mt-1">
            Cuenta: <strong>{{ session('correo_generado') }}</strong> (Esta clave se muestra únicamente en esta pantalla).
        </div>
    </div>
@endif

<!-- Filtros de búsqueda -->
<div class="card shadow-sm mb-4 border">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('bienestar.usuarios.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="buscar" value="{{ $termino ?? '' }}" 
                       class="form-control form-control-sm" 
                       placeholder="Buscar por correo, nombre, código o colegiatura...">
            </div>
            <div class="col-md-3">
                <select name="rol" class="form-select form-select-sm">
                    <option value="">Todos los roles...</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_rol }}" {{ (string)($idRol ?? '') === (string)$r->id_rol ? 'selected' : '' }}>
                            {{ $r->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos los estados...</option>
                    <option value="activo" {{ ($estado ?? '') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="suspendido" {{ ($estado ?? '') === 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filtrar</button>
                @if(!empty($termino) || !empty($idRol) || !empty($estado))
                    <a href="{{ route('bienestar.usuarios.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabla de cuentas institucionales -->
<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold text-dark">Directorio de Cuentas Institucionales</h6>
        <span class="text-muted small">Total: {{ $usuarios->total() }} registros</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr>
                    <th scope="col" style="width: 25%;">Correo Institucional</th>
                    <th scope="col" style="width: 25%;">Titular / Identidad</th>
                    <th scope="col" style="width: 15%;">Rol</th>
                    <th scope="col" style="width: 10%;">Estado</th>
                    <th scope="col" style="width: 10%;">Último acceso</th>
                    <th scope="col" style="width: 15%;" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usr)
                    <tr>
                        <td class="fw-semibold text-dark">
                            {{ $usr->correo }}
                            @if($usr->debe_cambiar_contrasena)
                                <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.7rem;">Cambio pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if($usr->estudiante)
                                <a href="{{ route('bienestar.estudiantes.show', $usr->estudiante->id_estudiante) }}" class="text-decoration-none fw-semibold">
                                    {{ $usr->estudiante->nombre_completo }}
                                </a>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Código: {{ $usr->estudiante->codigo_universitario }}</span>
                            @elseif($usr->especialista)
                                <strong class="text-dark">{{ $usr->especialista->nombre_completo }}</strong>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $usr->especialista->especialidad }} ({{ $usr->especialista->nro_colegiatura }})</span>
                            @else
                                <span class="text-muted fst-italic">Cuenta institucional interna</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($usr->rol?->nombre) {
                                    'Administrador' => 'bg-dark text-white',
                                    'Personal de Bienestar' => 'bg-primary text-white',
                                    'Especialista' => 'bg-info text-dark',
                                    'Docente' => 'bg-secondary text-white',
                                    'Estudiante' => 'bg-light text-dark border',
                                    default => 'bg-light text-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $usr->rol?->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td>
                            @if($usr->estado === 'activo')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    Activo
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    Suspendido
                                </span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ $usr->ultimo_acceso ? $usr->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @can('restablecerPassword', $usr)
                                    <form action="{{ route('bienestar.usuarios.restablecer-password', $usr->id_usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desea restablecer la contraseña temporal de {{ $usr->correo }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Restablecer contraseña temporal">
                                            Restablecer
                                        </button>
                                    </form>
                                @endcan

                                @can('alternarEstado', $usr)
                                    <form action="{{ route('bienestar.usuarios.alternar-estado', $usr->id_usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de cambiar el estado de acceso de {{ $usr->correo }}?');">
                                        @csrf
                                        @if($usr->estado === 'activo')
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Suspender acceso">
                                                Suspender
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-outline-success btn-sm py-0 px-2" title="Activar acceso">
                                                Activar
                                            </button>
                                        @endif
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No se encontraron cuentas de usuario que coincidan con los criterios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages())
        <div class="card-footer bg-white border-top py-2">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleClaveUsuario(rawPassword) {
    const box = document.getElementById('clave_temporal_usr');
    const btn = document.getElementById('btn_toggle_temp_usr');
    if (!box || !btn) return;
    const isMasked = box.textContent === '••••••••••••';
    box.textContent = isMasked ? rawPassword : '••••••••••••';
    const open = btn.querySelector('.eye-open');
    const closed = btn.querySelector('.eye-closed');
    if (open && closed) {
        open.classList.toggle('d-none', isMasked);
        closed.classList.toggle('d-none', !isMasked);
    }
}
</script>
@endpush
@endsection
