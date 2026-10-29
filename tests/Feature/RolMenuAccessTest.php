<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Tests\TestCase;

class RolMenuAccessTest extends TestCase
{
    protected Usuario $admin;
    protected Usuario $bienestar;
    protected Usuario $especialista;
    protected Usuario $docente;
    protected Usuario $estudiante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Usuario::with('rol')->where('correo', 'admin@unitru.edu.pe')->firstOrFail();
        $this->bienestar = Usuario::with('rol')->where('correo', 'bienestar@unitru.edu.pe')->firstOrFail();
        $this->especialista = Usuario::with('especialista')->where('correo', 'psicologia@unitru.edu.pe')->firstOrFail();
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        $this->estudiante = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
    }

    /**
     * Test: Administrador ve enlaces de gestión institucional y accede a todas las secciones.
     */
    public function test_admin_sees_appropriate_menu_links_and_can_access(): void
    {
        $response = $this->actingAs($this->admin)->get('/bienestar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Panel principal');
        $response->assertSee('Casos');
        $response->assertSee('Solicitudes');
        $response->assertSee('Citas');
        $response->assertSee('Derivaciones');
        $response->assertSee('Reportes');
        $response->assertSee('Notificaciones');

        // Acceso exitoso a reportes
        $this->actingAs($this->admin)->get('/bienestar/reportes')->assertStatus(200);
    }

    /**
     * Test: Personal de Bienestar ve gestión de casos y solicitudes, pero NUNCA Reportes (403).
     */
    public function test_bienestar_sees_management_links_but_never_reports(): void
    {
        $response = $this->actingAs($this->bienestar)->get('/bienestar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Casos');
        $response->assertSee('Solicitudes');
        $response->assertSee('Citas');
        $response->assertSee('Derivaciones');
        $response->assertSee('Notificaciones');
        // No debe ver enlace a Reportes
        $response->assertDontSee('Reportes');

        // Ruta prohibida devuelve 403
        $this->actingAs($this->bienestar)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->bienestar)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);
        $this->actingAs($this->bienestar)->get('/bienestar/horarios')->assertStatus(403);

        // Acciones clínicas de especialista denegadas a Bienestar (BPMN)
        $cita = Cita::first();
        $derivacion = Derivacion::first();

        if ($cita) {
            $this->actingAs($this->bienestar)->post("/bienestar/citas/{$cita->id_cita}/confirmar")
                ->assertRedirect()->assertSessionHas('warning');
            $this->actingAs($this->bienestar)->post("/bienestar/citas/{$cita->id_cita}/atender")
                ->assertRedirect()->assertSessionHas('warning');
        }

        if ($derivacion) {
            $this->actingAs($this->bienestar)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/aceptar")
                ->assertRedirect()->assertSessionHas('warning');
            $this->actingAs($this->bienestar)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
                'nota_avance' => 'Nota bienestar intento',
            ])->assertStatus(403);

            // Bienestar no ve notas clínicas confidenciales del especialista
            $respDeriv = $this->actingAs($this->bienestar)->get("/bienestar/derivaciones/{$derivacion->id_derivacion}");
            $respDeriv->assertStatus(200);
            $respDeriv->assertSee('Contenido reservado para el especialista tratante');
            $respDeriv->assertDontSee('Nota de evolución:');
        }
    }

    /**
     * Test: Especialista ve Citas, Derivaciones y Horarios, pero NUNCA Casos, Solicitudes ni Reportes (403).
     */
    public function test_especialista_sees_citas_and_derivaciones_only(): void
    {
        $response = $this->actingAs($this->especialista)->get('/bienestar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Citas');
        $response->assertSee('Derivaciones');
        $response->assertSee('Mis horarios');
        $response->assertSee('Notificaciones');

        // No ve Casos generales, Solicitudes ni Reportes
        $response->assertDontSee('Solicitudes');
        $response->assertDontSee('Reportes');

        // Rutas prohibidas devuelven 403 (módulo general) o 404 (ID ajeno)
        $this->actingAs($this->especialista)->get('/bienestar/casos')->assertStatus(403);
        $this->actingAs($this->especialista)->get('/bienestar/casos/1')->assertStatus(404);
        $this->actingAs($this->especialista)->get('/bienestar/casos/crear')->assertStatus(403);
        $this->actingAs($this->especialista)->get('/bienestar/solicitudes')->assertStatus(403);
        $this->actingAs($this->especialista)->get('/bienestar/reportes')->assertStatus(403);
    }

    /**
     * Test: Estudiante ve únicamente sus propios trámites (Mis solicitudes, Mis citas, Agendar cita).
     */
    public function test_estudiante_sees_personal_services_only(): void
    {
        $response = $this->actingAs($this->estudiante)->get('/bienestar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Mis solicitudes');
        $response->assertSee('Mis citas');
        $response->assertSee('Agendar cita');
        $response->assertSee('Notificaciones');

        // No ve secciones administrativas ni especializadas
        $response->assertDontSee('Derivaciones');
        $response->assertDontSee('Horarios');
        $response->assertDontSee('Reportes');

        // Rutas prohibidas devuelven 403 (módulo general) o 404 (ID ajeno)
        $this->actingAs($this->estudiante)->get('/bienestar/casos')->assertStatus(403);
        $this->actingAs($this->estudiante)->get('/bienestar/casos/1')->assertStatus(404);
        $this->actingAs($this->estudiante)->get('/bienestar/casos/crear')->assertStatus(403);
        $this->actingAs($this->estudiante)->get('/bienestar/derivaciones')->assertStatus(403);
        $this->actingAs($this->estudiante)->get('/bienestar/derivaciones/crear')->assertStatus(403);
        $this->actingAs($this->estudiante)->get('/bienestar/reportes')->assertStatus(403);
    }

    /**
     * Test: Docente NUNCA ve Casos, Solicitudes, Citas, Derivaciones, Horarios ni Reportes.
     */
    public function test_docente_sees_minimal_menu_and_all_confidential_routes_return_403(): void
    {
        $response = $this->actingAs($this->docente)->get('/bienestar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Panel principal');
        $response->assertSee('Notificaciones');

        // Docente NO debe ver ningún módulo de bienestar confidencial
        $response->assertDontSee('Casos');
        $response->assertDontSee('Solicitudes');
        $response->assertDontSee('Mis solicitudes');
        $response->assertDontSee('Citas');
        $response->assertDontSee('Mis citas');
        $response->assertDontSee('Derivaciones');
        $response->assertDontSee('Horarios');
        $response->assertDontSee('Reportes');

        // Toda ruta confidencial devuelve 403 Forbidden
        $this->actingAs($this->docente)->get('/bienestar/casos')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/casos/crear')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/solicitudes')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/solicitudes/crear')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/citas')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/citas/crear')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/derivaciones')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/derivaciones/crear')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);
    }

    /**
     * Test: Administrador tiene supervisión de SOLO LECTURA y recibe 403 en todas las rutas de escritura.
     */
    public function test_admin_is_strictly_read_only_and_forbidden_on_all_write_actions(): void
    {
        $caso = CasoBienestar::first();
        $solicitud = SolicitudApoyo::first();
        $cita = Cita::first();
        $derivacion = Derivacion::first();

        // 1. Escritura en Casos -> 403
        $this->actingAs($this->admin)->get('/bienestar/casos/crear')->assertStatus(403);
        $this->actingAs($this->admin)->post('/bienestar/casos', [
            'id_estudiante' => 1,
            'id_tipo_caso' => 1,
            'descripcion' => 'Intento de registro admin',
            'prioridad' => 'media',
        ])->assertStatus(403);

        if ($caso) {
            $this->actingAs($this->admin)->post("/bienestar/casos/{$caso->id_caso}/cambiar-estado", [
                'estado_nuevo' => 'cerrado',
                'comentario' => 'Intento admin',
            ])->assertStatus(403);
        }

        // 2. Escritura en Solicitudes -> 403
        $this->actingAs($this->admin)->get('/bienestar/solicitudes/crear')->assertStatus(403);
        $this->actingAs($this->admin)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'comedor',
            'motivo' => 'Intento admin',
        ])->assertStatus(403);

        if ($solicitud) {
            $this->actingAs($this->admin)->post("/bienestar/solicitudes/{$solicitud->id_solicitud}/dictaminar", [
                'estado' => 'aprobada',
                'observacion' => 'Dictamen admin',
            ])->assertRedirect()->assertSessionHas('warning');
        }

        // 3. Escritura en Citas y Horarios -> 403
        $this->actingAs($this->admin)->get('/bienestar/citas/crear')->assertStatus(403);
        $this->actingAs($this->admin)->post('/bienestar/citas', [
            'id_horario' => 1,
            'tipo_cita' => 'psicologica',
            'motivo' => 'Intento cita admin',
        ])->assertStatus(403);

        if ($cita) {
            $this->actingAs($this->admin)->post("/bienestar/citas/{$cita->id_cita}/confirmar")
                ->assertRedirect()->assertSessionHas('warning');
            $this->actingAs($this->admin)->post("/bienestar/citas/{$cita->id_cita}/cancelar", [
                'motivo_cancelacion' => 'Cancelación admin',
            ])->assertRedirect()->assertSessionHas('warning');
            $this->actingAs($this->admin)->post("/bienestar/citas/{$cita->id_cita}/atender")
                ->assertRedirect()->assertSessionHas('warning');
        }

        $this->actingAs($this->admin)->get('/bienestar/horarios')->assertStatus(403);
        $this->actingAs($this->admin)->post('/bienestar/horarios', [
            'fecha' => now()->addDays(2)->toDateString(),
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'id_especialista' => 1,
        ])->assertStatus(403);

        // 4. Escritura en Derivaciones -> 403
        $this->actingAs($this->admin)->get('/bienestar/derivaciones/crear')->assertStatus(403);
        $this->actingAs($this->admin)->post('/bienestar/derivaciones', [
            'id_caso' => 1,
            'id_especialista' => 1,
            'motivo' => 'Derivacion admin',
        ])->assertStatus(403);

        if ($derivacion) {
            $this->actingAs($this->admin)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/aceptar")
                ->assertRedirect()->assertSessionHas('warning');
            $this->actingAs($this->admin)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
                'nota_avance' => 'Nota admin',
            ])->assertStatus(403);
            $this->actingAs($this->admin)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/finalizar", [
                'informe_conclusion' => 'Informe de conclusion del caso por admin.',
            ])->assertRedirect()->assertSessionHas('warning');

            // Verificación: Administrador puede ver la derivación pero NO sus notas clínicas
            $response = $this->actingAs($this->admin)->get("/bienestar/derivaciones/{$derivacion->id_derivacion}");
            $response->assertStatus(200);
            $response->assertSee('Historial de sesiones');
            $response->assertSee('Contenido reservado para el especialista tratante');
            $response->assertDontSee('Nota de evolución:');
        }

        // 5. Administrador NO puede descargar documentos adjuntos (exclusivo estudiante dueño y bienestar -> 404)
        $this->actingAs($this->admin)->get('/bienestar/solicitudes/documentos/1/descargar')->assertStatus(404);

        // 6. En casos confidenciales, el Administrador ve tipo, estado y fechas pero NO la descripción
        if ($caso) {
            $caso->update(['confidencial' => true, 'descripcion' => 'Descripcion confidencial sensible']);
            $respCaso = $this->actingAs($this->admin)->get("/bienestar/casos/{$caso->id_caso}");
            $respCaso->assertStatus(200);
            $respCaso->assertSee('Contenido reservado por confidencialidad del caso');
            $respCaso->assertDontSee('Descripcion confidencial sensible');
        }
    }
}
