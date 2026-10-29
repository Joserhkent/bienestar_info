<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\PerfilSocioeconomico;
use Illuminate\Support\Facades\DB;

class PerfilSocioeconomicoService
{
    /**
     * Registra o actualiza el perfil socioeconómico del estudiante en una transacción.
     * La observación confidencial se cifra automáticamente mediante el cast 'encrypted'.
     */
    public function guardarOActualizar(Estudiante $estudiante, array $data): PerfilSocioeconomico
    {
        return DB::transaction(function () use ($estudiante, $data) {
            return PerfilSocioeconomico::updateOrCreate(
                ['id_estudiante' => $estudiante->id_estudiante],
                [
                    'clasificacion_sisfoh' => $data['clasificacion_sisfoh'],
                    'violencia_familiar'   => (bool) ($data['violencia_familiar'] ?? false),
                    'situacion_orfandad'   => $data['situacion_orfandad'],
                    'es_sustento_familiar' => (bool) ($data['es_sustento_familiar'] ?? false),
                    'nro_dependientes'     => (int) $data['nro_dependientes'],
                    'observacion'          => !empty($data['observacion']) ? trim($data['observacion']) : null,
                    'fecha_actualizacion'  => now(),
                ]
            );
        });
    }
}
