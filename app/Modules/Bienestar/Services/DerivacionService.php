<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\HistorialEstadoCaso;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Seguimiento;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;

class DerivacionService
{
    /**
     * Deriva un caso hacia un especialista dentro de una transacción atómica:
     * 1. Registra en bienestar.derivacion.
     * 2. Actualiza caso_bienestar a estado 'derivado'.
     * 3. Registra el registro de auditoría en historial_estado_caso.
     * 4. Notifica al especialista asignado.
     */
    public function derivarCaso(array $data, Usuario $usuarioDeriva): Derivacion
    {
        return DB::transaction(function () use ($data, $usuarioDeriva) {
            $caso = CasoBienestar::findOrFail($data['id_caso']);
            $especialista = Especialista::findOrFail($data['id_especialista']);

            // 1. Crear derivación
            $derivacion = Derivacion::create([
                'id_caso' => $caso->id_caso,
                'id_especialista' => $especialista->id_especialista,
                'id_usuario_deriva' => $usuarioDeriva->id_usuario,
                'motivo' => $data['motivo'],
                'estado' => 'pendiente',
                'fecha_derivacion' => now(),
            ]);

            // 2. Actualizar estado del caso
            $estadoAnterior = $caso->estado;
            $caso->estado = 'derivado';
            $caso->save();

            // 3. Registro de auditoría de cambio de estado
            $nombreEsp = "{$especialista->nombres} {$especialista->apellidos}";
            HistorialEstadoCaso::create([
                'id_caso' => $caso->id_caso,
                'id_usuario' => $usuarioDeriva->id_usuario,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'derivado',
                'fecha_cambio' => now(),
                'comentario' => "Caso derivado a {$nombreEsp}. Motivo: {$data['motivo']}",
            ]);

            // 4. Notificar al especialista
            if ($especialista->id_usuario) {
                Notificacion::create([
                    'id_usuario' => $especialista->id_usuario,
                    'id_caso' => $caso->id_caso,
                    'titulo' => 'Nuevo caso derivado',
                    'mensaje' => "Se le ha asignado el caso #CAS-" . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . " para atención especializada.",
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $derivacion->load(['caso.estudiante', 'especialista', 'usuarioDeriva']);
        });
    }

    /**
     * Registra una sesión de seguimiento clínico/tutoría dentro de una transacción atómica:
     * 1. Inserta la sesión en bienestar.seguimiento (cifrando notas y acuerdos).
     * 2. Actualiza la derivación a 'en_seguimiento'.
     * 3. Si el caso no estaba en seguimiento, lo actualiza y audita en historial_estado_caso.
     */
    public function registrarSeguimiento(Derivacion $derivacion, array $data, Usuario $usuarioEspecialista): Seguimiento
    {
        return DB::transaction(function () use ($derivacion, $data, $usuarioEspecialista) {
            // 1. Guardar sesión con campos cifrados
            $seguimiento = Seguimiento::create([
                'id_derivacion' => $derivacion->id_derivacion,
                'fecha_sesion' => $data['fecha_sesion'] ?? now(),
                'nota_avance' => $data['nota_avance'],
                'acuerdos' => $data['acuerdos'] ?? null,
                'proxima_fecha' => $data['proxima_fecha'] ?? null,
            ]);

            // 2. Actualizar estado de la derivación
            $derivacion->estado = 'en_seguimiento';
            $derivacion->save();

            // 3. Sincronizar estado del caso si corresponde
            $caso = $derivacion->caso;
            if ($caso && $caso->estado !== 'en_seguimiento') {
                $estadoAnterior = $caso->estado;
                $caso->estado = 'en_seguimiento';
                $caso->save();

                HistorialEstadoCaso::create([
                    'id_caso' => $caso->id_caso,
                    'id_usuario' => $usuarioEspecialista->id_usuario,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => 'en_seguimiento',
                    'fecha_cambio' => now(),
                    'comentario' => 'Sesión de atención registrada por especialista. Caso pasa a seguimiento activo.',
                ]);
            }

            return $seguimiento->load('derivacion.caso');
        });
    }

    /**
     * Acepta formalmente la derivación por parte del especialista.
     */
    public function aceptarDerivacion(Derivacion $derivacion): Derivacion
    {
        $derivacion->estado = 'aceptada';
        $derivacion->save();

        return $derivacion->fresh();
    }

    /**
     * Finaliza la derivación tras concluir el plan de atención:
     * 1. Actualiza la derivación a 'finalizada'.
     * 2. Pasa el caso a estado 'cerrado' en DB::transaction().
     * 3. Registra el registro de auditoría en historial_estado_caso con el informe de conclusión.
     * 4. Emite notificación al usuario que derivó el caso.
     */
    public function finalizarDerivacion(Derivacion $derivacion, string $informeConclusion, Usuario $usuario): Derivacion
    {
        return DB::transaction(function () use ($derivacion, $informeConclusion, $usuario) {
            $derivacion->estado = 'finalizada';
            $derivacion->save();

            $caso = $derivacion->caso;
            if ($caso) {
                $estadoAnterior = $caso->estado;
                $caso->estado = 'cerrado';
                $caso->save();

                HistorialEstadoCaso::create([
                    'id_caso' => $caso->id_caso,
                    'id_usuario' => $usuario->id_usuario,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => 'cerrado',
                    'fecha_cambio' => now(),
                    'comentario' => "Caso cerrado por especialista tras finalizar derivación. Informe: {$informeConclusion}",
                ]);

                if ($derivacion->id_usuario_deriva) {
                    Notificacion::create([
                        'id_usuario' => $derivacion->id_usuario_deriva,
                        'id_caso' => $caso->id_caso,
                        'titulo' => 'Caso cerrado por especialista',
                        'mensaje' => 'Se ha concluido la atención especializada del caso #CAS-' . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . ' y el expediente ha sido cerrado.',
                        'canal' => 'sistema',
                        'fecha_envio' => now(),
                        'leida' => false,
                    ]);
                }
            }

            return $derivacion->fresh(['caso']);
        });
    }
}
