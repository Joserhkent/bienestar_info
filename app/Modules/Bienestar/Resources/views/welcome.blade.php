<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo Bienestar Estudiantil — UNT</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .hero-banner { background: linear-gradient(135deg, #0d6efd 0%, #0a4296 100%); color: white; padding: 2.5rem 1rem; border-radius: 0.5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-heart-pulse-fill me-2"></i>Bienestar Universitario UNT
            </a>
            <span class="navbar-text text-white-50">Grupo 11 — Informática</span>
        </div>
    </nav>

    <div class="container my-4">
        <div class="hero-banner mb-4 shadow">
            <h1 class="display-6 fw-bold">Portal de Gestión de Dirección de Escuela</h1>
            <p class="lead mb-0">Módulo de Gestión de Bienestar Estudiantil (BienestarTutoriaModule)</p>
        </div>

        <div class="row g-4">
            <!-- Estado del Sistema -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-database-check text-success me-2"></i>Estado del Entorno y Base de Datos
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Motor de BD:
                                <span class="badge bg-success">PostgreSQL 16 (Docker)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Esquema dedicado:
                                <span class="badge bg-primary">bienestar</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Backend Framework:
                                <span class="badge bg-danger">Laravel 13 (PHP 8.3)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Modularidad:
                                <span class="badge bg-info text-dark">Nativa (app/Modules/Bienestar)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Frontend:
                                <span class="badge bg-secondary">Bootstrap 5 + Blade</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Migraciones aplicadas:
                                <span class="badge bg-success">20 tablas creadas</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Cuentas de Prueba -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-people-fill text-primary me-2"></i>Cuentas Homologadas para Pruebas (Seeders)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rol</th>
                                        <th>Correo</th>
                                        <th>Contraseña</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-dark">Admin</span></td>
                                        <td>admin@unitru.edu.pe</td>
                                        <td><code>password123</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary">Bienestar</span></td>
                                        <td>bienestar@unitru.edu.pe</td>
                                        <td><code>password123</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-info text-dark">Especialista</span></td>
                                        <td>psicologia@unitru.edu.pe</td>
                                        <td><code>password123</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning text-dark">Docente</span></td>
                                        <td>docente@unitru.edu.pe</td>
                                        <td><code>password123</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">Estudiante</span></td>
                                        <td>estudiante1@unitru.edu.pe</td>
                                        <td><code>password123</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
