<?php

namespace App\Modules\Bienestar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Bienestar\Models\Reporte;
use App\Modules\Bienestar\Services\ReporteService;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService
    ) {}

    /**
     * Pantalla de reportes consolidados institucional (P5: solo Administrador).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Reporte::class);

        $filtros = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'tipo_reporte' => ['nullable', 'string', 'in:general,casos,solicitudes,citas'],
        ]);

        $data = $this->reporteService->generarReporte($filtros, $request->user());

        return view('bienestar::reportes.index', $data);
    }

    /**
     * Exportación de métricas agregadas a formato CSV (P5).
     */
    public function exportarCsv(Request $request)
    {
        $this->authorize('export', Reporte::class);

        $filtros = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'tipo_reporte' => ['nullable', 'string', 'in:general,casos,solicitudes,citas'],
        ]);

        return $this->reporteService->exportarCsv($filtros, $request->user());
    }
}
