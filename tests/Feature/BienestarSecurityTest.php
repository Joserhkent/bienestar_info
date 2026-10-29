<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BienestarSecurityTest extends TestCase
{
    /**
     * 1. GET /bienestar/login responde 200 y el bloque demo solo aparece si APP_ENV=local
     */
    public function test_login_page_renders_status_200_and_demo_block_respects_env(): void
    {
        // En entorno local: debe renderizar 200 y mostrar el bloque de acceso rápido
        config(['app.env' => 'local']);
        $this->app['env'] = 'local';
        $responseLocal = $this->get('/bienestar/login');
        $responseLocal->assertStatus(200);
        $responseLocal->assertSee('Acceso al Sistema');
        $responseLocal->assertSee('Acceso Rápido para Demostración');

        // En entorno producción: debe renderizar 200 y NO mostrar el bloque de acceso rápido
        config(['app.env' => 'production']);
        $this->app['env'] = 'production';
        $responseProd = $this->get('/bienestar/login');
        $responseProd->assertStatus(200);
        $responseProd->assertDontSee('Acceso Rápido para Demostración');
    }

    /**
     * 2. POST login con credenciales correctas inicia sesión y redirige al dashboard
     */
    public function test_login_post_authenticates_user_and_redirects(): void
    {
        $response = $this->post('/bienestar/login', [
            'correo' => 'bienestar@unitru.edu.pe',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/bienestar/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * 3. Login de los 5 roles y carga correcta de cada dashboard
     */
    public function test_all_five_roles_can_login_and_access_dashboard(): void
    {
        $roles = [
            'admin@unitru.edu.pe' => 'TOTAL CASOS',
            'bienestar@unitru.edu.pe' => 'TOTAL CASOS',
            'psicologia@unitru.edu.pe' => 'CITAS SOLICITADAS',
            'docente@unitru.edu.pe' => 'Acceso Pedagógico Regulado — Ley N° 30150',
            'estudiante1@unitru.edu.pe' => 'Mis Solicitudes de Apoyo',
        ];

        foreach ($roles as $correo => $expectedText) {
            $user = Usuario::with('rol')->where('correo', $correo)->firstOrFail();
            $response = $this->actingAs($user)->get('/bienestar/dashboard');
            $response->assertStatus(200);
            $response->assertSee($expectedText);
        }
    }

    /**
     * 4. Docente recibe 403 en rutas de casos y solicitudes
     */
    public function test_docente_is_forbidden_403_on_cases_and_applications(): void
    {
        $docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();

        // Intento de acceso a casos
        $responseCasos = $this->actingAs($docente)->get('/bienestar/casos');
        $responseCasos->assertStatus(403);

        // Intento de acceso a solicitudes
        $responseSolicitudes = $this->actingAs($docente)->get('/bienestar/solicitudes/revision');
        $responseSolicitudes->assertStatus(403);
    }

    /**
     * 5. Un estudiante no puede ver datos de otro cambiando el ID (Policy de autorización)
     */
    public function test_student_cannot_view_another_students_confidential_case_or_application(): void
    {
        $estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $estudiante2 = Usuario::with('estudiante')->where('correo', 'estudiante2@unitru.edu.pe')->firstOrFail();

        // Caso perteneciente a Estudiante 1
        $casoEstudiante1 = CasoBienestar::where('id_estudiante', $estudiante1->estudiante->id_estudiante)->firstOrFail();

        // Estudiante 2 intenta autorizarse para ver el caso de Estudiante 1
        $puedeEstudiante2VerCaso = Gate::forUser($estudiante2)->allows('view', $casoEstudiante1);
        $this->assertFalse($puedeEstudiante2VerCaso, 'Un estudiante no debe tener autorización para ver casos de otro estudiante.');

        // Solicitud perteneciente a Estudiante 1
        $solicitudEstudiante1 = SolicitudApoyo::where('id_estudiante', $estudiante1->estudiante->id_estudiante)->firstOrFail();

        // Estudiante 2 intenta autorizarse para ver la solicitud de Estudiante 1
        $puedeEstudiante2VerSolicitud = Gate::forUser($estudiante2)->allows('view', $solicitudEstudiante1);
        $this->assertFalse($puedeEstudiante2VerSolicitud, 'Un estudiante no debe tener autorización para ver solicitudes de otro estudiante.');
    }
}
