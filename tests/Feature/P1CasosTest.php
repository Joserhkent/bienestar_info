<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\HistorialEstadoCaso;
use App\Modules\Bienestar\Models\TipoCaso;
use App\Modules\Bienestar\Models\Usuario;
use Tests\TestCase;

class P1CasosTest extends TestCase
{
    protected Usuario $personalBienestar;
    protected Usuario $docente;
    protected Usuario $estudiante1;
    protected Usuario $estudiante2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personalBienestar = Usuario::with('rol')->where('correo', 'bienestar@unitru.edu.pe')->firstOrFail();
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        $this->estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $this->estudiante2 = Usuario::with('estudiante')->where('correo', 'estudiante2@unitru.edu.pe')->firstOrFail();
    }

    /**
     * Test P1.1: Flujo completo de registro de caso con inserción atómica de historial (DB::transaction)
     */
    public function test_personal_bienestar_can_create_case_and_initial_history_is_recorded(): void
    {
        $estudiante = Estudiante::where('id_usuario', $this->estudiante2->id_usuario)->firstOrFail();
        $tipoCaso = TipoCaso::firstOrFail();

        // 1. Cargar formulario de creación
        $responseCreate = $this->actingAs($this->personalBienestar)->get('/bienestar/casos/crear');
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Nuevo caso');

        // 2. Enviar formulario con datos válidos
        $payload = [
            'id_estudiante' => $estudiante->id_estudiante,
            'id_tipo_caso' => $tipoCaso->id_tipo_caso,
            'prioridad' => 'alta',
            'confidencial' => '1',
            'descripcion' => 'Estudiante reporta dificultades persistentes de concentración y estrés severo por evaluaciones.',
        ];

        $responseStore = $this->actingAs($this->personalBienestar)->post('/bienestar/casos', $payload);

        // 3. Verificar redirección y existencia en BD
        $caso = CasoBienestar::where('id_estudiante', $estudiante->id_estudiante)
            ->where('prioridad', 'alta')
            ->latest('id_caso')
            ->firstOrFail();

        $responseStore->assertRedirect("/bienestar/casos/{$caso->id_caso}");
        $this->assertEquals('registrado', $caso->estado);
        $this->assertTrue($caso->confidencial);

        // 4. Verificar que se insertó el primer log en historial_estado_caso
        $historialInicial = HistorialEstadoCaso::where('id_caso', $caso->id_caso)->firstOrFail();
        $this->assertNull($historialInicial->estado_anterior);
        $this->assertEquals('registrado', $historialInicial->estado_nuevo);
        $this->assertEquals($this->personalBienestar->id_usuario, $historialInicial->id_usuario);
    }

    /**
     * Test P1.2: Transición de estado con auditoría inmutable en historial_estado_caso
     */
    public function test_personal_bienestar_can_change_case_state_and_audit_history_is_appended(): void
    {
        $caso = CasoBienestar::firstOrFail();

        // Cambiar estado a 'en_atencion'
        $responseCambio = $this->actingAs($this->personalBienestar)
            ->post("/bienestar/casos/{$caso->id_caso}/cambiar-estado", [
                'estado_nuevo' => 'en_atencion',
                'comentario' => 'Caso evaluado preliminarmente. Se inicia intervención de contención.',
            ]);

        $responseCambio->assertRedirect("/bienestar/casos/{$caso->id_caso}");
        $caso->refresh();
        $this->assertEquals('en_atencion', $caso->estado);

        // Verificar el nuevo registro en el historial
        $ultimoHistorial = HistorialEstadoCaso::where('id_caso', $caso->id_caso)->latest('id_historial')->firstOrFail();
        $this->assertEquals('en_atencion', $ultimoHistorial->estado_nuevo);
        $this->assertEquals('Caso evaluado preliminarmente. Se inicia intervención de contención.', $ultimoHistorial->comentario);

        // Cambiar estado a 'cerrado' y verificar fecha_cierre
        $responseCierre = $this->actingAs($this->personalBienestar)
            ->post("/bienestar/casos/{$caso->id_caso}/cambiar-estado", [
                'estado_nuevo' => 'cerrado',
                'comentario' => 'Objetivos de contención alcanzados satisfactoriamente.',
            ]);

        $responseCierre->assertRedirect("/bienestar/casos/{$caso->id_caso}");
        $caso->refresh();
        $this->assertEquals('cerrado', $caso->estado);
        $this->assertNotNull($caso->fecha_cierre);
    }

    /**
     * Test P1.3: Form Request rechaza datos inválidos con mensajes en español
     */
    public function test_form_request_validates_required_fields_and_min_length(): void
    {
        $response = $this->actingAs($this->personalBienestar)->post('/bienestar/casos', [
            'id_estudiante' => 99999, // Inexistente
            'id_tipo_caso' => '',
            'prioridad' => 'invalida',
            'confidencial' => '',
            'descripcion' => 'Corto', // Menos de 10 caracteres
        ]);

        $response->assertSessionHasErrors([
            'id_estudiante' => 'El estudiante seleccionado no se encuentra registrado en el padrón.',
            'id_tipo_caso' => 'Debe clasificar el caso según una tipología de bienestar.',
            'prioridad' => 'La prioridad debe ser: baja, media, alta o urgente.',
            'descripcion' => 'La descripción del caso debe contener al menos 10 caracteres.',
        ]);
    }

    /**
     * Test P1.4: Seguridad y 403 para Docente (Ley 30150 y Estatuto UNT)
     */
    public function test_docente_is_forbidden_403_to_access_cases(): void
    {
        $responseIndex = $this->actingAs($this->docente)->get('/bienestar/casos');
        $responseIndex->assertStatus(403);

        $responseCreate = $this->actingAs($this->docente)->get('/bienestar/casos/crear');
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->docente)->post('/bienestar/casos', [
            'id_estudiante' => 1,
            'id_tipo_caso' => 1,
            'prioridad' => 'media',
            'confidencial' => 1,
            'descripcion' => 'Intento de registro no autorizado',
        ]);
        $responseStore->assertStatus(403);
    }

    /**
     * Test P1.5: Un estudiante no puede ver casos confidenciales de otro alumno
     */
    public function test_student_cannot_view_another_students_case_by_changing_id(): void
    {
        // Caso perteneciente a Estudiante 1 y marcado como confidencial
        $casoEstudiante1 = CasoBienestar::where('id_estudiante', $this->estudiante1->estudiante->id_estudiante)->firstOrFail();

        // Estudiante 2 intenta consultar la URL directa del caso de Estudiante 1
        $response = $this->actingAs($this->estudiante2)->get("/bienestar/casos/{$casoEstudiante1->id_caso}");
        $response->assertStatus(403);
    }
}
