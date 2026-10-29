<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bienestar Estudiantil') — UNT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .navbar-unt { background: linear-gradient(90deg, #0d47a1 0%, #1976d2 100%); }
        .card-stat { border-left: 4px solid; transition: transform .15s; }
        .card-stat:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="d-flex flex-column min-h-screen">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-unt shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('bienestar.dashboard') }}">
                <i class="bi bi-heart-pulse-fill text-danger me-2 fs-4"></i>
                <span>Bienestar Universitario UNT</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bienestar.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('bienestar.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i>Panel Principal
                            </a>
                        </li>
                        @if(in_array(Auth::user()->rol->nombre ?? '', ['Personal de Bienestar', 'Administrador']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.casos.*') ? 'active fw-bold' : '' }}" href="{{ route('bienestar.casos.index') }}">
                                    <i class="bi bi-folder2-open me-1"></i>Casos de Bienestar (P1)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.solicitudes.*') ? 'active fw-bold' : '' }}" href="{{ route('bienestar.solicitudes.index') }}">
                                    <i class="bi bi-file-earmark-check me-1"></i>Solicitudes de Apoyo (P2)
                                </a>
                            </li>
                        @endif
                        @if((Auth::user()->rol->nombre ?? '') === 'Estudiante')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.solicitudes.index') ? 'active fw-bold' : '' }}" href="{{ route('bienestar.solicitudes.index') }}">
                                    <i class="bi bi-card-checklist me-1"></i>Mis Solicitudes (P2)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.solicitudes.create') ? 'active fw-bold' : '' }}" href="{{ route('bienestar.solicitudes.create') }}">
                                    <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        @php
                            $rolNombre = Auth::user()->rol->nombre ?? 'Usuario';
                            $badgeColor = match($rolNombre) {
                                'Administrador' => 'bg-dark',
                                'Personal de Bienestar' => 'bg-primary',
                                'Especialista' => 'bg-info text-dark',
                                'Docente' => 'bg-warning text-dark',
                                'Estudiante' => 'bg-success',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <li class="nav-item me-3 text-white">
                            <span class="small opacity-75 d-block text-end">{{ Auth::user()->correo }}</span>
                            <div class="text-end">
                                <span class="badge {{ $badgeColor }}">{{ $rolNombre }}</span>
                            </div>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('bienestar.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('bienestar.login') }}" class="btn btn-light btn-sm fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="container my-4 flex-grow-1">
        <!-- Alertas Flash -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>Atención:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            <span>Universidad Nacional de Trujillo — Dirección de Escuela de Informática — Proyecto de Competencia (Grupo 11)</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
