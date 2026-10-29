<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\HistorialEstadoCaso;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;

class CasoBienestarService
{
    /**
     * Registra un nuevo expediente de bienestar e inserta atómicamente su primer log de historial (P1).
     *
     * @param array $data
     * @param Usuario $usuarioPersonal
     * @return CasoBienestar
     */
    public function registrarCaso(array $data, Usuario $usuarioPersonal): CasoBienestar
    {
        return DB::transaction(function () use ($data, $usuarioPersonal) {
            // 1. Crear el caso
            $caso = CasoBienestar::create([
                'id_estudiante' => $data['id_estudiante'],
                'id_tipo_caso' => $data['id_tipo_caso'],
                'id_usuario_registro' => $usuarioPersonal->id_usuario,
                'descripcion' => $data['descripcion'],
                'prioridad' => $data['prioridad'],
                'confidencial' => (bool) $data['confidencial'],
                'estado' => 'registrado',
                'fecha_registro' => now(),
            ]);

            // 2. Registrar el primer hito en el historial (R08) dentro de la misma transacción
            HistorialEstadoCaso::create([
                'id_caso' => $caso->id_caso,
                'id_usuario' => $usuarioPersonal->id_usuario,
                'estado_anterior' => null,
                'estado_nuevo' => 'registrado',
                'fecha_cambio' => now(),
                'comentario' => 'Apertura inicial del expediente de bienestar estudiantil.',
            ]);

            // 3. Notificación informativa al estudiante (P5: texto genérico sin datos confidenciales)
            $caso->load('estudiante.usuario');
            if ($caso->estudiante?->usuario) {
                Notificacion::create([
                    'id_usuario' => $caso->estudiante->usuario->id_usuario,
                    'id_caso' => $caso->id_caso,
                    'titulo' => 'Apertura de expediente',
                    'mensaje' => 'Se ha aperturado un expediente de bienestar estudiantil a su nombre (#CAS-' . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . ').',
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $caso;
        });
    }

    /**
     * Realiza la transición de estado de un caso y registra el log de auditoría (P1 & P5).
     *
     * @param CasoBienestar $caso
     * @param string $nuevoEstado
     * @param string $comentario
     * @param Usuario $usuario
     * @return CasoBienestar
     */
    public function cambiarEstado(CasoBienestar $caso, string $nuevoEstado, string $comentario, Usuario $usuario): CasoBienestar
    {
        return DB::transaction(function () use ($caso, $nuevoEstado, $comentario, $usuario) {
            $estadoAnterior = $caso->estado;

            // Actualizar caso
            $caso->estado = $nuevoEstado;
            if ($nuevoEstado === 'cerrado') {
                $caso->fecha_cierre = now();
            }
            $caso->save();

            // Insertar auditoría en el historial
            HistorialEstadoCaso::create([
                'id_caso' => $caso->id_caso,
                'id_usuario' => $usuario->id_usuario,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'fecha_cambio' => now(),
                'comentario' => $comentario,
            ]);

            // Notificación de cambio de estado al estudiante (P5: texto genérico)
            $caso->load('estudiante.usuario');
            if ($caso->estudiante?->usuario) {
                $estadoLegible = str_replace('_', ' ', $nuevoEstado);
                Notificacion::create([
                    'id_usuario' => $caso->estudiante->usuario->id_usuario,
                    'id_caso' => $caso->id_caso,
                    'titulo' => 'Actualización de caso',
                    'mensaje' => 'Su expediente #CAS-' . str_pad($caso->id_caso, 4, '0', STR_PAD_LEFT) . ' ha cambiado al estado: ' . $estadoLegible . '.',
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $caso;
        });
    }
}
