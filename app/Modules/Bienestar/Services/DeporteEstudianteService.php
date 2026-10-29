<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\EstudianteDeporte;
use Illuminate\Support\Facades\DB;

class DeporteEstudianteService
{
    /**
     * Inscribe o actualiza la práctica deportiva de un estudiante en una transacción atómica.
     */
    public function inscribirOActualizar(Estudiante $estudiante, array $data): EstudianteDeporte
    {
        return DB::transaction(function () use ($estudiante, $data) {
            $idDeporte = (int) $data['id_deporte'];

            $campos = [
                'nivel'                => $data['nivel'],
                'disponible_seleccion' => (bool) ($data['disponible_seleccion'] ?? true),
            ];

            $existe = DB::table('bienestar.estudiante_deporte')
                ->where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_deporte', $idDeporte)
                ->exists();

            if ($existe) {
                DB::table('bienestar.estudiante_deporte')
                    ->where('id_estudiante', $estudiante->id_estudiante)
                    ->where('id_deporte', $idDeporte)
                    ->update($campos);
            } else {
                $campos['id_estudiante'] = $estudiante->id_estudiante;
                $campos['id_deporte'] = $idDeporte;
                DB::table('bienestar.estudiante_deporte')->insert($campos);
            }

            return EstudianteDeporte::where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_deporte', $idDeporte)
                ->firstOrFail();
        });
    }

    /**
     * Retira la inscripción deportiva del estudiante en una transacción atómica.
     */
    public function desinscribirDeporte(Estudiante $estudiante, int $idDeporte): void
    {
        DB::transaction(function () use ($estudiante, $idDeporte) {
            DB::table('bienestar.estudiante_deporte')
                ->where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_deporte', $idDeporte)
                ->delete();
        });
    }
}
