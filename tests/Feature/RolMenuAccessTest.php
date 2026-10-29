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

        // Rutas prohibidas devuelven 403
        $this->actingAs($this->especialista)->get('/bienestar/casos')->assertStatus(403);
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

        // Rutas prohibidas devuelven 403
        $this->actingAs($this->estudiante)->get('/bienestar/casos')->assertStatus(403);
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
}
