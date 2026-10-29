<?php

namespace App\Modules\Bienestar\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\DocumentoAdjunto;
use App\Modules\Bienestar\Models\EstudianteTipoCondicion;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Reporte;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Policies\CasoBienestarPolicy;
use App\Modules\Bienestar\Policies\CitaPolicy;
use App\Modules\Bienestar\Policies\DerivacionPolicy;
use App\Modules\Bienestar\Policies\DocentePautaPolicy;
use App\Modules\Bienestar\Policies\DocumentoAdjuntoPolicy;
use App\Modules\Bienestar\Policies\NotificacionPolicy;
use App\Modules\Bienestar\Policies\ReportePolicy;
use App\Modules\Bienestar\Policies\SolicitudApoyoPolicy;

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

        // 3. Cargar vistas Blade con namespace 'bienestar'
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'bienestar');

        // 4. Registrar Policies de autorización por proceso (P1 a P5)
        Gate::policy(CasoBienestar::class, CasoBienestarPolicy::class);
        Gate::policy(SolicitudApoyo::class, SolicitudApoyoPolicy::class);
        Gate::policy(DocumentoAdjunto::class, DocumentoAdjuntoPolicy::class);
        Gate::policy(Cita::class, CitaPolicy::class);
        Gate::policy(Derivacion::class, DerivacionPolicy::class);
        Gate::policy(EstudianteTipoCondicion::class, DocentePautaPolicy::class);
        Gate::policy(Notificacion::class, NotificacionPolicy::class);
        Gate::policy(Reporte::class, ReportePolicy::class);
    }
}
