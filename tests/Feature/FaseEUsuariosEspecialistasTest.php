<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FaseEUsuariosEspecialistasTest extends TestCase
{
    private function getUser(string $correo): Usuario
    {
        $user = Usuario::with('rol')->where('correo', $correo)->firstOrFail();
        if ($user->debe_cambiar_contrasena) {
            $user->update(['debe_cambiar_contrasena' => false]);
        }
        return $user;
    }

    public function test_bienestar_and_admin_can_view_users_list(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $admin = $this->getUser('admin@unitru.edu.pe');

        $this->actingAs($bienestar)
            ->get('/bienestar/usuarios')
            ->assertStatus(200)
            ->assertSee('Directorio de Cuentas Institucionales');

        $this->actingAs($admin)
            ->get('/bienestar/usuarios')
            ->assertStatus(200)
            ->assertSee('Directorio de Cuentas Institucionales');
    }

    public function test_estudiante_and_docente_forbidden_from_users_list(): void
    {
        $docente = $this->getUser('docente@unitru.edu.pe');
        $estudiante = $this->getUser('estudiante1@unitru.edu.pe');

        $this->actingAs($docente)->get('/bienestar/usuarios')->assertStatus(403);
        $this->actingAs($estudiante)->get('/bienestar/usuarios')->assertStatus(403);
    }

    public function test_bienestar_can_register_specialist_with_generated_credentials_in_transaction(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        // Limpieza previa si existiera
        Especialista::where('nro_colegiatura', 'CPP-991122')->delete();
        Usuario::where('correo', 'celia.vargas@unitru.edu.pe')->delete();

        $payload = [
            'nombres'         => 'cElIa mArInA',
            'apellidos'       => 'vArGaS pEñA',
            'correo'          => 'celia.vargas@unitru.edu.pe',
            'especialidad'    => 'Psicopedagogía Clínica',
            'nro_colegiatura' => 'cpp-991122',
        ];

        $response = $this->actingAs($bienestar)->post('/bienestar/usuarios/especialistas', $payload);

        $response->assertRedirect(route('bienestar.usuarios.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('password_temporal');
        $response->assertSessionHas('correo_generado', 'celia.vargas@unitru.edu.pe');

        // Verificar normalización de texto y usuario creado en base de datos
        $this->assertDatabaseHas('bienestar.usuario', [
            'correo'                  => 'celia.vargas@unitru.edu.pe',
            'id_rol'                  => 3, // Especialista
            'estado'                  => 'activo',
            'debe_cambiar_contrasena' => true,
        ]);

        $this->assertDatabaseHas('bienestar.especialista', [
            'nombres'         => 'Celia Marina',
            'apellidos'       => 'Vargas Peña',
            'especialidad'    => 'Psicopedagogía Clínica',
            'nro_colegiatura' => 'CPP-991122',
            'estado'          => 'activo',
        ]);
    }

    public function test_bienestar_can_suspend_and_activate_estudiante_and_especialista(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = $this->getUser('estudiante1@unitru.edu.pe');

        // 1. Suspender cuenta
        $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$estudiante->id_usuario}/alternar-estado")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('suspendido', $estudiante->fresh()->estado);

        // 2. Reactivar cuenta
        $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$estudiante->id_usuario}/alternar-estado")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('activo', $estudiante->fresh()->estado);
    }

    public function test_suspended_account_cannot_login(): void
    {
        $estudiante = $this->getUser('estudiante1@unitru.edu.pe');
        $estudiante->update(['estado' => 'suspendido']);

        $response = $this->post('/bienestar/login', [
            'correo'   => 'estudiante1@unitru.edu.pe',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('correo');
        $this->assertGuest();

        // Restaurar estado para no alterar otras pruebas
        $estudiante->update(['estado' => 'activo']);
    }

    public function test_bienestar_cannot_suspend_or_reset_password_for_admin_or_docente(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $admin = $this->getUser('admin@unitru.edu.pe');
        $docente = $this->getUser('docente@unitru.edu.pe');

        // Intento de suspender Admin -> warning denegado
        $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$admin->id_usuario}/alternar-estado")
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertEquals('activo', $admin->fresh()->estado);

        // Intento de restablecer password a Docente -> warning denegado
        $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$docente->id_usuario}/restablecer-password")
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_bienestar_can_reset_temporary_password_for_student(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = $this->getUser('estudiante1@unitru.edu.pe');

        $response = $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$estudiante->id_usuario}/restablecer-password");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('password_temporal');

        $freshEst = $estudiante->fresh();
        $this->assertTrue($freshEst->debe_cambiar_contrasena);

        // Restaurar estado
        $freshEst->update(['debe_cambiar_contrasena' => false]);
    }

    public function test_user_cannot_suspend_themselves(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        $this->actingAs($bienestar)
            ->post("/bienestar/usuarios/{$bienestar->id_usuario}/alternar-estado")
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertEquals('activo', $bienestar->fresh()->estado);
    }

    public function test_form_request_validations_for_especialista(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        // Correo ya existente
        $response = $this->actingAs($bienestar)->post('/bienestar/usuarios/especialistas', [
            'nombres'         => 'Test',
            'apellidos'       => 'Test',
            'correo'          => 'bienestar@unitru.edu.pe',
            'especialidad'    => 'Psicología',
            'nro_colegiatura' => 'CPP-999999',
        ]);
        $response->assertSessionHasErrors('correo');
    }

    public function test_404_for_nonexistent_user(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        $this->actingAs($bienestar)
            ->post('/bienestar/usuarios/999999/alternar-estado')
            ->assertStatus(404);

        $this->actingAs($bienestar)
            ->post('/bienestar/usuarios/999999/restablecer-password')
            ->assertStatus(404);
    }
}
