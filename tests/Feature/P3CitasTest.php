<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\HorarioDisponible;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Usuario;
use Tests\TestCase;

class P3CitasTest extends TestCase
{
    protected Usuario $especialista1;
    protected Usuario $especialista2;
    protected Usuario $docente;
    protected Usuario $estudiante1;
    protected Usuario $estudiante2;

    protected function setUp(): void
    {
        parent::setUp();

        // Especialista 1: Silvia Marina Vargas Rojas (Psicología)
        $this->especialista1 = Usuario::with('especialista')->where('correo', 'psicologia@unitru.edu.pe')->firstOrFail();
        // Especialista 2: Jorge Luis Alva Mantilla (Tutoría)
        $this->especialista2 = Usuario::with('especialista')->where('correo', 'tutor@unitru.edu.pe')->firstOrFail();
        // Docente
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        // Estudiantes
        $this->estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $this->estudiante2 = Usuario::with('estudiante')->where('correo', 'estudiante2@unitru.edu.pe')->firstOrFail();
    }

    /**
     * Test P3.1: Estudiante agenda cita en horario disponible dentro de una transacción atómica.
     */
    public function test_student_can_book_appointment_in_transaction(): void
    {
        // Crear un horario disponible nuevo para prueba
        $horario = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(2)->toDateString(),
            'hora_inicio' => '14:00',
            'hora_fin' => '15:00',
            'estado' => 'disponible',
        ]);

        $response = $this->actingAs($this->estudiante1)->post('/bienestar/citas', [
            'id_horario' => $horario->id_horario,
            'tipo_cita' => 'psicologia',
            'motivo' => 'Presento episodios de ansiedad y estrés antes de las evaluaciones parciales.',
        ]);

        $cita = Cita::where('id_horario', $horario->id_horario)->firstOrFail();

        $response->assertRedirect("/bienestar/citas/{$cita->id_cita}");
        $this->assertEquals('solicitada', $cita->estado);
        $this->assertEquals($this->estudiante1->estudiante->id_estudiante, $cita->id_estudiante);

        // Verificar motivo desencriptado
        $this->assertEquals('Presento episodios de ansiedad y estrés antes de las evaluaciones parciales.', $cita->motivo);

        // Verificar que el horario se actualizó a 'reservado'
        $horario->refresh();
        $this->assertEquals('reservado', $horario->estado);

        // Verificar que se emitió notificación al especialista
        $notificacion = Notificacion::where('id_usuario', $this->especialista1->id_usuario)
            ->latest('id_notificacion')
            ->firstOrFail();

        $this->assertStringContainsString('Nueva cita solicitada', $notificacion->titulo);
    }

    /**
     * Test P3.2: Rechazo al intentar reservar un horario ya reservado o inexistente.
     */
    public function test_cannot_book_already_reserved_or_invalid_slot(): void
    {
        // Horario reservado previamente
        $horarioOcupado = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(3)->toDateString(),
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'estado' => 'reservado',
        ]);

        $response = $this->actingAs($this->estudiante1)->post('/bienestar/citas', [
            'id_horario' => $horarioOcupado->id_horario,
            'tipo_cita' => 'tutoria_academica',
            'motivo' => 'Intento de reserva en un turno ocupado.',
        ]);

        $response->assertSessionHasErrors([
            'id_horario' => 'El horario seleccionado ya no se encuentra disponible o no existe.',
        ]);
    }

    /**
     * Test P3.3: Operación de varios pasos: Cancelar cita y liberar horario en DB::transaction().
     */
    public function test_canceling_appointment_frees_the_slot_in_transaction(): void
    {
        // 1. Crear horario y cita inicial
        $horario = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(4)->toDateString(),
            'hora_inicio' => '11:00',
            'hora_fin' => '12:00',
            'estado' => 'reservado',
        ]);

        $cita = Cita::create([
            'id_horario' => $horario->id_horario,
            'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
            'tipo_cita' => 'psicologia',
            'estado' => 'solicitada',
            'motivo' => 'Consulta inicial que será cancelada por cruce de horario.',
            'fecha_solicitud' => now(),
        ]);

        // 2. Estudiante cancela la cita
        $response = $this->actingAs($this->estudiante1)
            ->post("/bienestar/citas/{$cita->id_cita}/cancelar", [
                'motivo_cancelacion' => 'Cruce de horario con práctica de laboratorio.',
            ]);

        $response->assertRedirect("/bienestar/citas/{$cita->id_cita}");

        // 3. Verificar que la cita pasa a cancelada
        $cita->refresh();
        $this->assertEquals('cancelada', $cita->estado);

        // 4. Verificar que el horario quedó liberado (disponible)
        $horario->refresh();
        $this->assertEquals('disponible', $horario->estado);

        // 5. Verificar que otro estudiante puede reservar este mismo horario liberado
        $responseReagendar = $this->actingAs($this->estudiante2)->post('/bienestar/citas', [
            'id_horario' => $horario->id_horario,
            'tipo_cita' => 'tutoria_academica',
            'motivo' => 'Reserva en turno previamente liberado por cancelación.',
        ]);

        $responseReagendar->assertSessionHasNoErrors();
        $horario->refresh();
        $this->assertEquals('reservado', $horario->estado);
    }

    /**
     * Test P3.4: Especialista confirma la cita en DB::transaction() y notifica al estudiante.
     */
    public function test_specialist_can_confirm_appointment_in_transaction(): void
    {
        $horario = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(5)->toDateString(),
            'hora_inicio' => '16:00',
            'hora_fin' => '17:00',
            'estado' => 'reservado',
        ]);

        $cita = Cita::create([
            'id_horario' => $horario->id_horario,
            'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
            'tipo_cita' => 'psicologia',
            'estado' => 'solicitada',
            'motivo' => 'Orientación vocacional y manejo de estrés.',
            'fecha_solicitud' => now(),
        ]);

        $response = $this->actingAs($this->especialista1)
            ->post("/bienestar/citas/{$cita->id_cita}/confirmar");

        $response->assertRedirect("/bienestar/citas/{$cita->id_cita}");

        $cita->refresh();
        $this->assertEquals('confirmada', $cita->estado);
        $this->assertNotNull($cita->fecha_confirmacion);

        // Notificación al estudiante
        $notificacion = Notificacion::where('id_usuario', $this->estudiante1->id_usuario)
            ->latest('id_notificacion')
            ->firstOrFail();

        $this->assertStringContainsString('Cita confirmada', $notificacion->titulo);
    }

    /**
     * Test P3.5: Especialista marca cita como realizada tras la atención.
     */
    public function test_specialist_can_mark_appointment_as_completed(): void
    {
        $horario = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->toDateString(),
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
            'estado' => 'reservado',
        ]);

        $cita = Cita::create([
            'id_horario' => $horario->id_horario,
            'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
            'tipo_cita' => 'psicologia',
            'estado' => 'confirmada',
            'motivo' => 'Sesión de contención ejecutada hoy.',
            'fecha_solicitud' => now()->subDay(),
            'fecha_confirmacion' => now(),
        ]);

        $response = $this->actingAs($this->especialista1)
            ->post("/bienestar/citas/{$cita->id_cita}/atender");

        $response->assertRedirect("/bienestar/citas/{$cita->id_cita}");

        $cita->refresh();
        $this->assertEquals('realizada', $cita->estado);
    }

    /**
     * Test P3.6: Seguridad y 403 para Docente (sin acceso a citas clínicas o tutorías individuales).
     */
    public function test_docente_is_forbidden_403_to_access_or_book_appointments(): void
    {
        $responseIndex = $this->actingAs($this->docente)->get('/bienestar/citas');
        $responseIndex->assertStatus(403);

        $responseCreate = $this->actingAs($this->docente)->get('/bienestar/citas/crear');
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->docente)->post('/bienestar/citas', [
            'id_horario' => 1,
            'tipo_cita' => 'psicologia',
            'motivo' => 'Intento de reserva por docente.',
        ]);
        $responseStore->assertStatus(403);
    }

    /**
     * Test P3.7: Aislamiento por ID: Un estudiante no puede ver la cita de otro estudiante.
     */
    public function test_student_cannot_view_another_students_confidential_appointment_by_changing_id(): void
    {
        $horario = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(6)->toDateString(),
            'hora_inicio' => '15:00',
            'hora_fin' => '16:00',
            'estado' => 'reservado',
        ]);

        $citaEst1 = Cita::create([
            'id_horario' => $horario->id_horario,
            'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
            'tipo_cita' => 'psicologia',
            'estado' => 'solicitada',
            'motivo' => 'Consulta médica/psicológica privada de estudiante 1.',
            'fecha_solicitud' => now(),
        ]);

        // Estudiante 2 intenta acceder a la cita de Estudiante 1 cambiando el ID
        $response = $this->actingAs($this->estudiante2)->get("/bienestar/citas/{$citaEst1->id_cita}");
        $response->assertStatus(403);
    }

    /**
     * Test P3.8: Especialista no puede gestionar ni confirmar citas de otro especialista.
     */
    public function test_specialist_cannot_manage_another_specialists_appointment(): void
    {
        $horarioEsp1 = HorarioDisponible::create([
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'fecha' => now()->addDays(7)->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'estado' => 'reservado',
        ]);

        $citaEsp1 = Cita::create([
            'id_horario' => $horarioEsp1->id_horario,
            'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
            'tipo_cita' => 'psicologia',
            'estado' => 'solicitada',
            'motivo' => 'Consulta asignada a Especialista 1.',
            'fecha_solicitud' => now(),
        ]);

        // Especialista 2 intenta confirmar la cita de Especialista 1
        $response = $this->actingAs($this->especialista2)
            ->post("/bienestar/citas/{$citaEsp1->id_cita}/confirmar");

        $response->assertStatus(403);
    }
}
