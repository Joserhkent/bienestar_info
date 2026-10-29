<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Reporte;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteService
{
    /**
     * Genera métricas agregadas institucionales y registra la consulta en bienestar.reporte (P5).
     */
    public function generarReporte(array $filtros, Usuario $admin, string $formato = 'pantalla'): array
    {
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->startOfYear()->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $tipoReporte = $filtros['tipo_reporte'] ?? 'general';

        $inicioTs = Carbon::parse($fechaInicio)->startOfDay();
        $finTs = Carbon::parse($fechaFin)->endOfDay();

        // 1. Guardar registro auditable de generación en bienestar.reporte
        $reporteGuardado = Reporte::create([
            'id_usuario' => $admin->id_usuario,
            'tipo_reporte' => $tipoReporte,
            'periodo_inicio' => $fechaInicio,
            'periodo_fin' => $fechaFin,
            'formato' => $formato,
            'fecha_generacion' => now(),
        ]);

        // 2. Métricas agregadas de Casos
        $casosQuery = CasoBienestar::whereBetween('fecha_registro', [$inicioTs, $finTs]);
        $totalCasos = (clone $casosQuery)->count();

        $casosPorEstado = (clone $casosQuery)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $casosPorTipo = (clone $casosQuery)
            ->join('bienestar.tipo_caso', 'bienestar.caso_bienestar.id_tipo_caso', '=', 'bienestar.tipo_caso.id_tipo_caso')
            ->select('bienestar.tipo_caso.nombre as tipo', DB::raw('count(*) as total'))
            ->groupBy('bienestar.tipo_caso.nombre')
            ->pluck('total', 'tipo')
            ->toArray();

        $casosPorPrioridad = (clone $casosQuery)
            ->select('prioridad', DB::raw('count(*) as total'))
            ->groupBy('prioridad')
            ->pluck('total', 'prioridad')
            ->toArray();

        // 3. Métricas agregadas de Solicitudes de Apoyo
        $solicitudesQuery = SolicitudApoyo::whereBetween('fecha_envio', [$inicioTs, $finTs]);
        $totalSolicitudes = (clone $solicitudesQuery)->count();

        $solicitudesPorEstado = (clone $solicitudesQuery)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $solicitudesPorTipo = (clone $solicitudesQuery)
            ->select('tipo_apoyo', DB::raw('count(*) as total'))
            ->groupBy('tipo_apoyo')
            ->pluck('total', 'tipo_apoyo')
            ->toArray();

        $aprobadas = $solicitudesPorEstado['aprobada'] ?? 0;
        $rechazadas = $solicitudesPorEstado['rechazada'] ?? 0;
        $totalDictaminadas = $aprobadas + $rechazadas;
        $tasaAprobacion = $totalDictaminadas > 0 ? round(($aprobadas / $totalDictaminadas) * 100, 1) : 0;

        // 4. Métricas agregadas de Citas de Atención
        $citasQuery = Cita::whereBetween('fecha_solicitud', [$inicioTs, $finTs]);
        $totalCitas = (clone $citasQuery)->count();

        $citasPorEstado = (clone $citasQuery)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $citasPorTipo = (clone $citasQuery)
            ->select('tipo_cita', DB::raw('count(*) as total'))
            ->groupBy('tipo_cita')
            ->pluck('total', 'tipo_cita')
            ->toArray();

        // 5. Historial reciente de reportes emitidos
        $historialReportes = Reporte::with('usuario')
            ->orderBy('fecha_generacion', 'desc')
            ->limit(10)
            ->get();

        return [
            'reporte' => $reporteGuardado,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo_reporte' => $tipoReporte,
            ],
            'casos' => [
                'total' => $totalCasos,
                'por_estado' => $casosPorEstado,
                'por_tipo' => $casosPorTipo,
                'por_prioridad' => $casosPorPrioridad,
            ],
            'solicitudes' => [
                'total' => $totalSolicitudes,
                'por_estado' => $solicitudesPorEstado,
                'por_tipo' => $solicitudesPorTipo,
                'tasa_aprobacion' => $tasaAprobacion,
            ],
            'citas' => [
                'total' => $totalCitas,
                'por_estado' => $citasPorEstado,
                'por_tipo' => $citasPorTipo,
            ],
            'historialReportes' => $historialReportes,
        ];
    }

    /**
     * Exporta las métricas consolidadas en formato CSV sin exponer datos personales ni clínicos.
     */
    public function exportarCsv(array $filtros, Usuario $admin): StreamedResponse
    {
        $data = $this->generarReporte($filtros, $admin, 'csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_bienestar_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Modulo', 'Dimension', 'Categoria / Indicador', 'Total', 'Porcentaje']);

            // 1. Casos por Estado
            $totalCasos = $data['casos']['total'];
            foreach ($data['casos']['por_estado'] as $estado => $cant) {
                fputcsv($handle, [
                    'Casos',
                    'Estado',
                    ucfirst(str_replace('_', ' ', $estado)),
                    self::formatearCantidad((int) $cant),
                    self::formatearPorcentaje((int) $cant, (int) $totalCasos),
                ]);
            }

            // 2. Casos por Tipo
            foreach ($data['casos']['por_tipo'] as $tipo => $cant) {
                fputcsv($handle, [
                    'Casos',
                    'Tipo de Caso',
                    $tipo,
                    self::formatearCantidad((int) $cant),
                    self::formatearPorcentaje((int) $cant, (int) $totalCasos),
                ]);
            }

            // 3. Solicitudes por Estado
            $totalSol = $data['solicitudes']['total'];
            foreach ($data['solicitudes']['por_estado'] as $estado => $cant) {
                fputcsv($handle, [
                    'Solicitudes de Apoyo',
                    'Estado',
                    ucfirst(str_replace('_', ' ', $estado)),
                    self::formatearCantidad((int) $cant),
                    self::formatearPorcentaje((int) $cant, (int) $totalSol),
                ]);
            }

            // 4. Citas por Estado
            $totalCitas = $data['citas']['total'];
            foreach ($data['citas']['por_estado'] as $estado => $cant) {
                fputcsv($handle, [
                    'Citas de Atencion',
                    'Estado',
                    ucfirst(str_replace('_', ' ', $estado)),
                    self::formatearCantidad((int) $cant),
                    self::formatearPorcentaje((int) $cant, (int) $totalCitas),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Ofuscación de k-anonimato: Muestra 'menos de 5' cuando una categoría tiene pocos casos.
     */
    public static function formatearCantidad(int $cant): string
    {
        if ($cant > 0 && $cant < 5) {
            return 'menos de 5';
        }
        return (string) $cant;
    }

    public static function formatearPorcentaje(int $cant, int $total): string
    {
        if ($cant > 0 && $cant < 5) {
            return '< 5%';
        }
        return $total > 0 ? round(($cant / $total) * 100, 1) . '%' : '0%';
    }
}
