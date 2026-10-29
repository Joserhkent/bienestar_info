<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\HorarioDisponible;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CitaService
{
    /**
     * Agenda una cita dentro de una transacción atómica:
     * 1. Bloquea y reserva el horario disponible.
     * 2. Registra la cita (con motivo cifrado).
     * 3. Emite notificación al especialista.
     */
    public function agendarCita(array $data, Estudiante $estudiante, ?int $idCaso = null): Cita
    {
        return DB::transaction(function () use ($data, $estudiante, $idCaso) {
            // 1. Bloqueo pesimista para evitar concurrencia de reservas
            $horario = HorarioDisponible::where('id_horario', $data['id_horario'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($horario->estado !== 'disponible') {
                throw ValidationException::withMessages([
                    'id_horario' => 'El horario seleccionado ya no se encuentra disponible.',
                ]);
            }

            // 2. Marcar horario como reservado
            $horario->estado = 'reservado';
            $horario->save();

            // 3. Crear cita
            $cita = Cita::create([
                'id_horario' => $horario->id_horario,
                'id_estudiante' => $estudiante->id_estudiante,
                'id_caso' => $idCaso,
                'tipo_cita' => $data['tipo_cita'],
                'estado' => 'solicitada',
                'motivo' => $data['motivo'],
                'fecha_solicitud' => now(),
                'fecha_confirmacion' => null,
            ]);

            // 4. Notificar al especialista asignado
            if ($horario->especialista && $horario->especialista->id_usuario) {
                Notificacion::create([
                    'id_usuario' => $horario->especialista->id_usuario,
                    'id_caso' => $idCaso,
                    'titulo' => 'Nueva cita solicitada',
                    'mensaje' => "El estudiante {$estudiante->nombres} {$estudiante->apellidos} ha reservado una cita para el " . $horario->fecha->format('d/m/Y') . " ({$horario->hora_inicio} - {$horario->hora_fin}).",
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $cita->load(['horario.especialista', 'estudiante.usuario']);
        });
    }

    /**
     * Cancela una cita y libera el horario para que pueda volver a ser agendado.
     * Operación de varios pasos protegida en DB::transaction().
     */
    public function cancelarCita(Cita $cita, Usuario $usuarioCancela, ?string $motivo = null): Cita
    {
        return DB::transaction(function () use ($cita, $usuarioCancela, $motivo) {
            // 1. Cambiar estado de la cita a cancelada
            $cita->estado = 'cancelada';
            $cita->save();

            // 2. Liberar el horario para reutilización (aprovecha el índice parcial)
            $horario = HorarioDisponible::where('id_horario', $cita->id_horario)
                ->lockForUpdate()
                ->first();

            if ($horario) {
                $horario->estado = 'disponible';
                $horario->save();
            }

            // 3. Notificación cruzada al afectado
            $esEstudiante = $cita->estudiante && $cita->estudiante->id_usuario === $usuarioCancela->id_usuario;

            if ($esEstudiante && $horario?->especialista?->id_usuario) {
                // Estudiante canceló -> notificar a especialista
                Notificacion::create([
                    'id_usuario' => $horario->especialista->id_usuario,
                    'id_caso' => $cita->id_caso,
                    'titulo' => 'Cita cancelada por estudiante',
                    'mensaje' => "La cita del " . $horario->fecha->format('d/m/Y') . " ha sido cancelada por el estudiante." . ($motivo ? " Motivo: {$motivo}" : ""),
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            } elseif (!$esEstudiante && $cita->estudiante?->id_usuario) {
                // Especialista o Bienestar canceló -> notificar a estudiante
                Notificacion::create([
                    'id_usuario' => $cita->estudiante->id_usuario,
                    'id_caso' => $cita->id_caso,
                    'titulo' => 'Cita cancelada',
                    'mensaje' => "Su cita programada para el " . ($horario ? $horario->fecha->format('d/m/Y') : 'la fecha agendada') . " ha sido cancelada." . ($motivo ? " Motivo: {$motivo}" : ""),
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $cita->fresh(['horario.especialista', 'estudiante.usuario']);
        });
    }

    /**
     * Especialista confirma la cita en DB::transaction() y notifica al estudiante.
     */
    public function confirmarCita(Cita $cita): Cita
    {
        return DB::transaction(function () use ($cita) {
            $cita->estado = 'confirmada';
            $cita->fecha_confirmacion = now();
            $cita->save();

            if ($cita->estudiante && $cita->estudiante->id_usuario) {
                $nombreEspecialista = $cita->horario?->especialista
                    ? $cita->horario->especialista->nombres . ' ' . $cita->horario->especialista->apellidos
                    : 'el especialista';

                $fechaTxt = $cita->horario?->fecha ? $cita->horario->fecha->format('d/m/Y') : '';

                Notificacion::create([
                    'id_usuario' => $cita->estudiante->id_usuario,
                    'id_caso' => $cita->id_caso,
                    'titulo' => 'Cita confirmada',
                    'mensaje' => "Su cita con {$nombreEspecialista} para el {$fechaTxt} ha sido confirmada.",
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $cita->fresh(['horario.especialista', 'estudiante.usuario']);
        });
    }

    /**
     * Marca la cita como realizada tras la atención.
     */
    public function atenderCita(Cita $cita): Cita
    {
        $cita->estado = 'realizada';
        $cita->save();

        return $cita->fresh();
    }

    /**
     * Registra un nuevo horario disponible para un especialista.
     */
    public function crearHorario(array $data, int $idEspecialista): HorarioDisponible
    {
        return HorarioDisponible::create([
            'id_especialista' => $idEspecialista,
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'estado' => 'disponible',
        ]);
    }
}
