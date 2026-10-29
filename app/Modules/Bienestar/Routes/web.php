<?php

use App\Modules\Bienestar\Http\Controllers\AuthController;
use App\Modules\Bienestar\Http\Controllers\CasoBienestarController;
use App\Modules\Bienestar\Http\Controllers\SolicitudApoyoController;
use Illuminate\Support\Facades\Route;

// El grupo 'web' es obligatorio para inicializar sesiones, cookies y ShareErrorsFromSession ($errors)
Route::middleware('web')->prefix('bienestar')->name('bienestar.')->group(function () {
    // 1. Rutas Públicas de Bienvenida y Autenticación
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 2. Rutas Protegidas por Autenticación General
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        // PROCESO P1: Registro y Gestión de Casos
        Route::middleware('role:Personal de Bienestar,Administrador')->group(function () {
            Route::get('/casos', [CasoBienestarController::class, 'index'])->name('casos.index');
            Route::get('/casos/crear', [CasoBienestarController::class, 'create'])->name('casos.create');
            Route::post('/casos', [CasoBienestarController::class, 'store'])->name('casos.store');
            Route::post('/casos/{caso}/cambiar-estado', [CasoBienestarController::class, 'cambiarEstado'])->name('casos.cambiar-estado');
        });

        // Ver detalle de caso (protegido por CasoBienestarPolicy::view)
        Route::get('/casos/{caso}', [CasoBienestarController::class, 'show'])->name('casos.show');

        // PROCESO P2: Solicitud de Apoyo Socioeconómico
        Route::middleware('role:Personal de Bienestar,Administrador')->group(function () {
            Route::get('/solicitudes/revision', fn() => redirect()->route('bienestar.solicitudes.index'))->name('solicitudes.revision');
        });

        Route::get('/solicitudes', [SolicitudApoyoController::class, 'index'])->name('solicitudes.index');
        Route::get('/solicitudes/crear', [SolicitudApoyoController::class, 'create'])->name('solicitudes.create');
        Route::post('/solicitudes', [SolicitudApoyoController::class, 'store'])->name('solicitudes.store');
        Route::get('/solicitudes/{solicitud}', [SolicitudApoyoController::class, 'show'])->whereNumber('solicitud')->name('solicitudes.show');
        Route::post('/solicitudes/{solicitud}/dictaminar', [SolicitudApoyoController::class, 'dictaminar'])->whereNumber('solicitud')->name('solicitudes.dictaminar');
        Route::get('/solicitudes/documentos/{documento}/descargar', [SolicitudApoyoController::class, 'descargarDocumento'])->whereNumber('documento')->name('solicitudes.documentos.descargar');

        // Aliases para el menú del dashboard
        Route::get('/mis-solicitudes', fn() => redirect()->route('bienestar.solicitudes.index'))->name('estudiante.solicitudes');
        Route::middleware('role:Estudiante')->group(function () {
            Route::get('/mis-citas', fn() => redirect()->route('bienestar.dashboard'))->name('estudiante.citas');
        });

        Route::middleware('role:Especialista')->group(function () {
            Route::get('/agenda', fn() => redirect()->route('bienestar.dashboard'))->name('especialista.agenda');
            Route::get('/seguimientos', fn() => redirect()->route('bienestar.dashboard'))->name('especialista.seguimientos');
        });

        Route::middleware('role:Docente')->group(function () {
            Route::get('/pautas-docente', fn() => redirect()->route('bienestar.dashboard'))->name('docente.pautas');
        });

        Route::middleware('role:Administrador')->group(function () {
            Route::get('/reportes', fn() => redirect()->route('bienestar.dashboard'))->name('admin.reportes');
        });
    });
});
