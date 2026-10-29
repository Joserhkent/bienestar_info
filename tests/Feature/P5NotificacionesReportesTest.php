<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\HorarioDisponible;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Reporte;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use App\Modules\Bienestar\Services\CasoBienestarService;
use App\Modules\Bienestar\Services\CitaService;
use App\Modules\Bienestar\Services\SolicitudApoyoService;
use Tests\TestCase;

class P5NotificacionesReportesTest extends TestCase
{
    protected Usuario $admin;
    protected Usuario $bienestar;
    protected Usuario $especialista;
    protected Usuario $docente;
    protected Usuario $estudiante1;
    protected Usuario $estudiante2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Usuario::with('rol')->where('correo', 'admin@unitru.edu.pe')->firstOrFail();
        $this->bienestar = Usuario::with('rol')->where('correo', 'bienestar@unitru.edu.pe')->firstOrFail();
        $this->especialista = Usuario::with('especialista')->where('correo', 'psicologia@unitru.edu.pe')->firstOrFail();
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        $this->estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $this->estudiante2 = Usuario::with('estudiante')->where('correo', 'estudiante2@unitru.edu.pe')->firstOrFail();
    }

    /**
     * Test P5.1: Usuario puede consultar su bandeja de notificaciones.
     */
    public function test_user_can_view_own_notifications(): void
    {
        // Notificación para estudiante 1
        $notif1 = Notificacion::create([
            'id_usuario' => $this->estudiante1->id_usuario,
            'titulo' => 'Aviso institucional 1',
            'mensaje' => 'Actualización de trámite de prueba 1.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        // Notificación para estudiante 2
        $notif2 = Notificacion::create([
            'id_usuario' => $this->estudiante2->id_usuario,
            'titulo' => 'Aviso institucional 2',
            'mensaje' => 'Actualización de trámite de prueba 2.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        $response = $this->actingAs($this->estudiante1)->get('/bienestar/notificaciones');

        $response->assertStatus(200);
        $response->assertViewIs('bienestar::notificaciones.index');
        $response->assertSee('Aviso institucional 1');
        // No debe ver las notificaciones de otro estudiante
        $response->assertDontSee('Aviso institucional 2');
    }

    /**
     * Test P5.2: Aislamiento: Un usuario no puede marcar como leída la notificación de otro (403).
     */
    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $notifEstudiante1 = Notificacion::create([
            'id_usuario' => $this->estudiante1->id_usuario,
            'titulo' => 'Aviso confidencial',
            'mensaje' => 'Notificación privada del estudiante 1.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        // Estudiante 2 intenta marcar como leída la notificación de Estudiante 1
        $response = $this->actingAs($this->estudiante2)->post("/bienestar/notificaciones/{$notifEstudiante1->id_notificacion}/leer");

        $response->assertStatus(403);
        $notifEstudiante1->refresh();
        $this->assertFalse($notifEstudiante1->leida);
    }

    /**
     * Test P5.3: Usuario puede marcar su propia notificación como leída.
     */
    public function test_user_can_mark_own_notification_as_read(): void
    {
        $notif = Notificacion::create([
            'id_usuario' => $this->estudiante1->id_usuario,
            'titulo' => 'Aviso de prueba',
            'mensaje' => 'Contenido de prueba.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        $response = $this->actingAs($this->estudiante1)->post("/bienestar/notificaciones/{$notif->id_notificacion}/leer");

        $response->assertRedirect('/bienestar/notificaciones');
        $notif->refresh();
        $this->assertTrue($notif->leida);
    }

    /**
     * Test P5.4: Usuario puede marcar todas sus notificaciones como leídas en bloque.
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        Notificacion::create([
            'id_usuario' => $this->estudiante1->id_usuario,
            'titulo' => 'Aviso bloque 1',
            'mensaje' => 'Contenido 1.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        Notificacion::create([
            'id_usuario' => $this->estudiante1->id_usuario,
            'titulo' => 'Aviso bloque 2',
            'mensaje' => 'Contenido 2.',
            'canal' => 'sistema',
            'fecha_envio' => now(),
            'leida' => false,
        ]);

        $response = $this->actingAs($this->estudiante1)->post('/bienestar/notificaciones/leer-todas');

        $response->assertRedirect('/bienestar/notificaciones');

        $pendientes = Notificacion::where('id_usuario', $this->estudiante1->id_usuario)
            ->where('leida', false)
            ->count();

        $this->assertEquals(0, $pendientes);
    }

    /**
     * Test P5.5: Cambio de estado de un caso emite notificación genérica al estudiante.
     */
    public function test_case_state_change_creates_generic_notification_without_clinical_data(): void
    {
        $caso = CasoBienestar::firstOrCreate(
            ['id_caso' => 995],
            [
                'id_caso' => 995,
                'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
                'id_usuario_registro' => $this->bienestar->id_usuario,
                'id_tipo_caso' => 1,
                'descripcion' => 'Descripción reservada del caso.',
                'prioridad' => 'media',
                'estado' => 'registrado',
                'fecha_registro' => now(),
            ]
        );

        $casoService = app(CasoBienestarService::class);
        $casoService->cambiarEstado($caso, 'en_atencion', 'Pasa a atención de bienestar.', $this->bienestar);

        $notif = Notificacion::where('id_usuario', $this->estudiante1->id_usuario)
            ->where('id_caso', $caso->id_caso)
            ->latest('id_notificacion')
            ->firstOrFail();

        $this->assertEquals('Actualización de caso', $notif->titulo);
        $this->assertStringContainsString('en atencion', $notif->mensaje);
        // Garantizar que no expone descripción reservada
        $this->assertStringNotContainsString('Descripción reservada', $notif->mensaje);
    }

    /**
     * Test P5.6: Administrador accede a reportes institucionales y la consulta se registra en tabla reporte.
     */
    public function test_admin_can_access_reports_and_log_is_saved_in_table_reporte(): void
    {
        $conteoPrevio = Reporte::where('id_usuario', $this->admin->id_usuario)->count();

        $response = $this->actingAs($this->admin)->get('/bienestar/reportes?fecha_inicio=2026-01-01&fecha_fin=2026-12-31&tipo_reporte=general');

        $response->assertStatus(200);
        $response->assertViewIs('bienestar::reportes.index');
        $response->assertViewHasAll(['casos', 'solicitudes', 'citas', 'historialReportes']);

        // Se verifica que se guardó en la tabla reporte
        $conteoNuevo = Reporte::where('id_usuario', $this->admin->id_usuario)->count();
        $this->assertEquals($conteoPrevio + 1, $conteoNuevo);

        $ultimoReporte = Reporte::where('id_usuario', $this->admin->id_usuario)->latest('id_reporte')->firstOrFail();
        $this->assertEquals('general', $ultimoReporte->tipo_reporte);
        $this->assertEquals('pantalla', $ultimoReporte->formato);
    }

    /**
     * Test P5.7: Administrador exporta CSV con datos agregados sin exponer datos personales ni clínicos.
     */
    public function test_admin_can_export_csv_without_personal_or_clinical_data(): void
    {
        $response = $this->actingAs($this->admin)->get('/bienestar/reportes/exportar-csv?fecha_inicio=2026-01-01&fecha_fin=2026-12-31');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csvContent = $response->streamedContent();

        // Debe contener encabezados agregados
        $this->assertStringContainsString('Modulo,Dimension,"Categoria / Indicador",Total,Porcentaje', $csvContent);
        $this->assertStringContainsString('Casos', $csvContent);
        $this->assertStringContainsString('Solicitudes de Apoyo', $csvContent);
        $this->assertStringContainsString('Citas de Atencion', $csvContent);

        // NUNCA debe contener nombres de estudiantes ni diagnósticos individuales
        $this->assertStringNotContainsString('Carlos Alberto Mendoza', $csvContent);
        $this->assertStringNotContainsString('Andrea Beatriz Paredes', $csvContent);
        $this->assertStringNotContainsString('72345671', $csvContent);

        // Se verifica que se registró con formato CSV en la tabla reporte
        $reporteCsv = Reporte::where('id_usuario', $this->admin->id_usuario)
            ->latest('id_reporte')
            ->firstOrFail();
        $this->assertEquals('csv', $reporteCsv->formato);
    }

    /**
     * Test P5.8: Roles no autorizados (Bienestar, Docente, Estudiante, Especialista) reciben 403 en reportes.
     */
    public function test_non_admin_roles_forbidden_from_reports(): void
    {
        // 1. Personal de Bienestar -> 403
        $this->actingAs($this->bienestar)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->bienestar)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);

        // 2. Docente -> 403
        $this->actingAs($this->docente)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->docente)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);

        // 3. Estudiante -> 403
        $this->actingAs($this->estudiante1)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->estudiante1)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);

        // 4. Especialista -> 403
        $this->actingAs($this->especialista)->get('/bienestar/reportes')->assertStatus(403);
        $this->actingAs($this->especialista)->get('/bienestar/reportes/exportar-csv')->assertStatus(403);
    }
}
