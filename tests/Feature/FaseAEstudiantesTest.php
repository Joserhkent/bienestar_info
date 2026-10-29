<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FaseAEstudiantesTest extends TestCase
{
    private function getUser(string $correo): Usuario
    {
        return Usuario::with(['rol', 'estudiante'])->where('correo', $correo)->firstOrFail();
    }

    public function test_bienestar_can_view_students_list_and_create_form(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        $responseList = $this->actingAs($bienestar)->get('/bienestar/estudiantes');
        $responseList->assertStatus(200);
        $responseList->assertSee('Estudiantes');
        $responseList->assertSee('Registrar estudiante');

        $responseCreate = $this->actingAs($bienestar)->get('/bienestar/estudiantes/crear');
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Registrar Estudiante');
    }

    public function test_bienestar_can_register_student_with_generated_credentials_in_transaction(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        $nuevoCodigo = '1022998877';
        $nuevoDni = '78965412';
        $nuevoCorreo = 'nuevo.alumno.test@unitru.edu.pe';

        $payload = [
            'codigo_universitario' => $nuevoCodigo,
            'dni'                  => $nuevoDni,
            'nombres'              => 'Pedro Alonso',
            'apellidos'            => 'Castillo Silva',
            'correo'               => $nuevoCorreo,
            'sexo'                 => 'M',
            'fecha_nacimiento'     => '2004-05-15',
            'telefono'             => '944888777',
            'direccion_actual'     => 'Av. Juan Pablo II 340',
            'zona_procedencia'     => 'Costa',
            'lugar_procedencia'    => 'Trujillo - La Libertad',
            'ciclo_actual'         => 3,
        ];

        $response = $this->actingAs($bienestar)->post('/bienestar/estudiantes', $payload);

        $estudiante = Estudiante::with('usuario')->where('codigo_universitario', $nuevoCodigo)->first();
        $this->assertNotNull($estudiante, 'El estudiante debe haber sido creado en BD.');

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('password_temporal');

        // Verificar el usuario asociado
        $usuario = $estudiante->usuario;
        $this->assertNotNull($usuario);
        $this->assertEquals($nuevoCorreo, $usuario->correo);
        $this->assertEquals(1, $usuario->id_rol); // Rol Estudiante
        $this->assertEquals('activo', $usuario->estado);
        $this->assertTrue($usuario->debe_cambiar_contrasena);

        // La contraseña temporal generada debe ser válida contra el hash
        $passwordTemporal = session('password_temporal');
        $this->assertTrue(Hash::check($passwordTemporal, $usuario->contrasena_hash));

        // Limpieza
        $estudiante->delete();
        $usuario->delete();
    }

    public function test_form_request_rejects_duplicate_and_invalid_data(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        // Código duplicado existente (estudiante1 tiene 1021400121)
        $payloadInvalido = [
            'codigo_universitario' => '1021400121', // ya existe
            'dni'                  => '1234',       // no tiene 8 dígitos
            'nombres'              => '',
            'apellidos'            => '',
            'correo'               => 'correo-invalido',
            'sexo'                 => 'X',
            'fecha_nacimiento'     => '2099-01-01', // fecha futura
            'zona_procedencia'     => '',
            'lugar_procedencia'    => '',
            'ciclo_actual'         => 99,           // fuera de rango
        ];

        $response = $this->actingAs($bienestar)->post('/bienestar/estudiantes', $payloadInvalido);
        $response->assertSessionHasErrors([
            'codigo_universitario',
            'dni',
            'nombres',
            'apellidos',
            'correo',
            'sexo',
            'fecha_nacimiento',
            'zona_procedencia',
            'lugar_procedencia',
            'ciclo_actual',
        ]);
    }

    public function test_admin_is_read_only_and_forbidden_from_create_and_edit(): void
    {
        $admin = $this->getUser('admin@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        // Admin puede ver lista y show
        $this->actingAs($admin)->get('/bienestar/estudiantes')->assertStatus(200);
        $this->actingAs($admin)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}")->assertStatus(200);

        // Admin prohibido en creación y edición
        $this->actingAs($admin)->get('/bienestar/estudiantes/crear')->assertStatus(403);
        $this->actingAs($admin)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}/editar")->assertStatus(403);
        $this->actingAs($admin)->post('/bienestar/estudiantes', [])->assertStatus(403);
    }

    public function test_student_and_docente_access_controls(): void
    {
        $docente = $this->getUser('docente@unitru.edu.pe');
        $estudiante1 = $this->getUser('estudiante1@unitru.edu.pe');
        $estudiante2 = $this->getUser('estudiante2@unitru.edu.pe');

        $idEst1 = $estudiante1->estudiante->id_estudiante;
        $idEst2 = $estudiante2->estudiante->id_estudiante;

        // Docente recibe 403 en lista y 404 en show
        $this->actingAs($docente)->get('/bienestar/estudiantes')->assertStatus(403);
        $this->actingAs($docente)->get("/bienestar/estudiantes/{$idEst1}")->assertStatus(404);

        // Estudiante recibe 403 en lista y crear
        $this->actingAs($estudiante1)->get('/bienestar/estudiantes')->assertStatus(403);
        $this->actingAs($estudiante1)->get('/bienestar/estudiantes/crear')->assertStatus(403);

        // Estudiante puede ver su propia ficha
        $this->actingAs($estudiante1)->get("/bienestar/estudiantes/{$idEst1}")->assertStatus(200);

        // Estudiante recibe 404 al intentar ver ficha ajena
        $this->actingAs($estudiante1)->get("/bienestar/estudiantes/{$idEst2}")->assertStatus(404);
    }

    public function test_mandatory_password_change_flow_for_temporary_password(): void
    {
        // Crear un usuario de prueba con debe_cambiar_contrasena = true
        $tempUser = Usuario::create([
            'id_rol'                  => 4,
            'correo'                  => 'alumno.cambio@unitru.edu.pe',
            'contrasena_hash'         => Hash::make('ClaveTemp123!'),
            'estado'                  => 'activo',
            'debe_cambiar_contrasena' => true,
        ]);

        try {
            // Intentar acceder a dashboard es interceptado y redirige a cambiar-contrasena
            $responseIntercept = $this->actingAs($tempUser)->get('/bienestar/dashboard');
            $responseIntercept->assertRedirect(route('bienestar.cambiar-contrasena'));

            // Puede ver la pantalla de cambio de contraseña
            $responseForm = $this->actingAs($tempUser)->get('/bienestar/cambiar-contrasena');
            $responseForm->assertStatus(200);
            $responseForm->assertSee('Actualización Obligatoria de Contraseña');

            // Enviar cambio con contraseña actual errónea falla
            $responseError = $this->actingAs($tempUser)->post('/bienestar/cambiar-contrasena', [
                'password_actual'       => 'ClaveIncorrecta',
                'password'              => 'Definitiva2026!#',
                'password_confirmation' => 'Definitiva2026!#',
            ]);
            $responseError->assertSessionHasErrors(['password_actual']);

            // Enviar cambio correcto
            $responseSuccess = $this->actingAs($tempUser)->post('/bienestar/cambiar-contrasena', [
                'password_actual'       => 'ClaveTemp123!',
                'password'              => 'Definitiva2026!#',
                'password_confirmation' => 'Definitiva2026!#',
            ]);
            $responseSuccess->assertRedirect(route('bienestar.dashboard'));

            // Verificar que la bandera se desactiva y el hash se actualiza
            $tempUser->refresh();
            $this->assertFalse($tempUser->debe_cambiar_contrasena);
            $this->assertTrue(Hash::check('Definitiva2026!#', $tempUser->contrasena_hash));

            // Ahora puede acceder normalmente a dashboard
            $responseDashboard = $this->actingAs($tempUser)->get('/bienestar/dashboard');
            $responseDashboard->assertStatus(200);
        } finally {
            $tempUser->delete();
        }
    }
}
