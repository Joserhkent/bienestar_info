<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\EstudianteTipoCondicion;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CondicionEstudianteService
{
    /**
     * Asigna o actualiza una condición de inclusión para el estudiante en transacción.
     * La pauta pedagógica se cifra explícitamente mediante Crypt::encryptString.
     */
    public function asignarOActualizar(Estudiante $estudiante, array $data): EstudianteTipoCondicion
    {
        return DB::transaction(function () use ($estudiante, $data) {
            $idTipo = (int) $data['id_tipo_condicion'];

            $pautaCifrada = !empty($data['pauta_pedagogica'])
                ? Crypt::encryptString(trim($data['pauta_pedagogica']))
                : null;

            $campos = [
                'grado'                   => $data['grado'],
                'requiere_aula_accesible' => (bool) ($data['requiere_aula_accesible'] ?? false),
                'certificado_conadis'     => !empty($data['certificado_conadis']) ? trim($data['certificado_conadis']) : null,
                'pauta_pedagogica'        => $pautaCifrada,
                'visible_docente'         => (bool) ($data['visible_docente'] ?? false),
                'fecha_registro'          => now(),
            ];

            $existe = DB::table('bienestar.estudiante_tipo_condicion')
                ->where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_tipo_condicion', $idTipo)
                ->exists();

            if ($existe) {
                DB::table('bienestar.estudiante_tipo_condicion')
                    ->where('id_estudiante', $estudiante->id_estudiante)
                    ->where('id_tipo_condicion', $idTipo)
                    ->update($campos);
            } else {
                $campos['id_estudiante'] = $estudiante->id_estudiante;
                $campos['id_tipo_condicion'] = $idTipo;
                DB::table('bienestar.estudiante_tipo_condicion')->insert($campos);
            }

            return EstudianteTipoCondicion::where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_tipo_condicion', $idTipo)
                ->firstOrFail();
        });
    }

    /**
     * Desvincula una condición del expediente del estudiante en transacción.
     */
    public function eliminarCondicion(Estudiante $estudiante, int $idTipoCondicion): void
    {
        DB::transaction(function () use ($estudiante, $idTipoCondicion) {
            DB::table('bienestar.estudiante_tipo_condicion')
                ->where('id_estudiante', $estudiante->id_estudiante)
                ->where('id_tipo_condicion', $idTipoCondicion)
                ->delete();
        });
    }
}
