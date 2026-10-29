<?php

namespace App\Modules\Bienestar\Providers;

use Illuminate\Support\ServiceProvider;

class BienestarServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registro de bindings y servicios del módulo Bienestar
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Cargar migraciones del módulo
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // 2. Cargar rutas web con prefijo y middlewares
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // 3. Cargar vistas Blade con namespace 'bienestar' (ej. view('bienestar::dashboard'))
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'bienestar');
    }
}
