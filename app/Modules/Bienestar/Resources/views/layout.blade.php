<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bienestar Universitario') — UNT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 0.95rem;
        }
        .navbar-institutional {
            background-color: #0c2340;
            border-bottom: 3px solid #d4af37; /* Toque dorado institucional UNT */
        }
        .navbar-institutional .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        .navbar-institutional .nav-link:hover,
        .navbar-institutional .nav-link.active {
            color: #ffffff;
            font-weight: 600;
        }
        .card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            padding: 0.75rem 1rem;
        }
        .table > :not(caption) > * > * {
            padding: 0.6rem 0.75rem;
            border-bottom-color: #e9ecef;
        }
        .table thead th {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            text-transform: none;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .badge-status {
            font-weight: 500;
            font-size: 0.8rem;
            padding: 0.3em 0.6em;
            border-radius: 3px;
        }
        .btn {
            border-radius: 4px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="d-flex flex-column min-h-screen">
    <!-- Barra superior institucional -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-institutional">
        <div class="container">
            <a class="navbar-brand text-white fw-semibold" href="{{ route('bienestar.dashboard') }}">
                Universidad Nacional de Trujillo <span class="fw-normal text-white-50">| Bienestar</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bienestar.dashboard') ? 'active' : '' }}" href="{{ route('bienestar.dashboard') }}">
                                Panel principal
                            </a>
                        </li>
                        @can('viewAny', App\Modules\Bienestar\Models\CasoBienestar::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.casos.*') ? 'active' : '' }}" href="{{ route('bienestar.casos.index') }}">
                                    Casos
                                </a>
                            </li>
                        @endcan
                        @if(in_array(Auth::user()->rol->nombre ?? '', ['Personal de Bienestar', 'Administrador']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.solicitudes.*') ? 'active' : '' }}" href="{{ route('bienestar.solicitudes.index') }}">
                                    Solicitudes
                                </a>
                            </li>
                        @endif
                        @can('viewAny', App\Modules\Bienestar\Models\Cita::class)
                            @if((Auth::user()->rol->nombre ?? '') !== 'Estudiante')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('bienestar.citas.*') && !request()->routeIs('bienestar.citas.horarios*') ? 'active' : '' }}" href="{{ route('bienestar.citas.index') }}">
                                        Citas
                                    </a>
                                </li>
                            @endif
                        @endcan
                        @can('viewAny', App\Modules\Bienestar\Models\Derivacion::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.derivaciones.*') ? 'active' : '' }}" href="{{ route('bienestar.derivaciones.index') }}">
                                    Derivaciones
                                </a>
                            </li>
                        @endcan
                        @can('gestionarHorarios', App\Modules\Bienestar\Models\Cita::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.citas.horarios*') ? 'active' : '' }}" href="{{ route('bienestar.citas.horarios') }}">
                                    Mis horarios
                                </a>
                            </li>
                        @endcan
                        @if((Auth::user()->rol->nombre ?? '') === 'Estudiante')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.solicitudes.*') ? 'active' : '' }}" href="{{ route('bienestar.solicitudes.index') }}">
                                    Mis solicitudes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.citas.index') || request()->routeIs('bienestar.citas.show') ? 'active' : '' }}" href="{{ route('bienestar.citas.index') }}">
                                    Mis citas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.citas.create') ? 'active' : '' }}" href="{{ route('bienestar.citas.create') }}">
                                    Agendar cita
                                </a>
                            </li>
                        @endif
                        @if((Auth::user()->rol->nombre ?? '') === 'Administrador')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('bienestar.reportes.*') ? 'active' : '' }}" href="{{ route('bienestar.reportes.index') }}">
                                    Reportes
                                </a>
                            </li>
                        @endif
                        @php
                            $notifNoLeidas = \App\Modules\Bienestar\Models\Notificacion::where('id_usuario', Auth::id())->where('leida', false)->count();
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bienestar.notificaciones.*') ? 'active' : '' }}" href="{{ route('bienestar.notificaciones.index') }}">
                                Notificaciones
                                @if($notifNoLeidas > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $notifNoLeidas }}</span>
                                @endif
                            </a>
                        </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        <li class="nav-item me-3 text-white-50 small">
                            <span class="text-white">{{ Auth::user()->correo }}</span>
                            <span>({{ Auth::user()->rol->nombre ?? 'Usuario' }})</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('bienestar.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light btn-sm py-1 px-3">
                                    Salir
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('bienestar.login') }}" class="btn btn-outline-light btn-sm py-1 px-3">
                                Iniciar sesión
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-4 flex-grow-1">
        <!-- Mensajes del sistema -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="fw-semibold">Por favor verifique los siguientes campos:</span>
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

    <!-- Pie de página sobrio -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            Universidad Nacional de Trujillo — Dirección de Bienestar Universitario
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
