<?php

use App\Modules\Bienestar\Http\Controllers\AuthController;
use App\Modules\Bienestar\Http\Controllers\CasoBienestarController;
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

        // Módulos de los siguientes procesos (P2 a P5)
        Route::middleware('role:Personal de Bienestar,Administrador')->group(function () {
            Route::get('/solicitudes/revision', fn() => redirect()->route('bienestar.dashboard'))->name('solicitudes.revision');
        });

        Route::middleware('role:Estudiante')->group(function () {
            Route::get('/mis-solicitudes', fn() => redirect()->route('bienestar.dashboard'))->name('estudiante.solicitudes');
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
