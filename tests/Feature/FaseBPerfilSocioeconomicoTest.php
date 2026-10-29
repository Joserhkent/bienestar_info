<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\PerfilSocioeconomico;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaseBPerfilSocioeconomicoTest extends TestCase
{
    private function getUser(string $correo): Usuario
    {
        return Usuario::with(['rol', 'estudiante'])->where('correo', $correo)->firstOrFail();
    }

    public function test_bienestar_can_save_perfil_socioeconomico_with_encrypted_observation(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        $observacionSensible = 'Estudiante vive en alquiler con apoyo de familiar enfermo. Requiere subsidio urgente.';

        $payload = [
            'clasificacion_sisfoh' => 'pobre_extremo',
            'violencia_familiar'   => 1,
            'situacion_orfandad'   => 'padre',
            'es_sustento_familiar' => 1,
            'nro_dependientes'     => 3,
            'observacion'          => $observacionSensible,
        ];

        $response = $this->actingAs($bienestar)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/perfil-socioeconomico", $payload);

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('success');

        // 1. Verificar en base de datos que la observación se guardó cifrada (no en texto plano)
        $rawPerfil = DB::table('bienestar.perfil_socioeconomico')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->first();

        $this->assertNotNull($rawPerfil);
        $this->assertNotEquals($observacionSensible, $rawPerfil->observacion, 'La observación debe estar cifrada en la base de datos.');
        $this->assertStringStartsWith('eyJ', $rawPerfil->observacion, 'El valor cifrado debe tener el formato serializado de Laravel Crypt.');

        // 2. A través del modelo Eloquent se descifra transparentemente
        $perfil = PerfilSocioeconomico::where('id_estudiante', $estudiante->id_estudiante)->first();
        $this->assertEquals($observacionSensible, $perfil->observacion);
        $this->assertEquals('pobre_extremo', $perfil->clasificacion_sisfoh);
        $this->assertTrue($perfil->violencia_familiar);
        $this->assertEquals('padre', $perfil->situacion_orfandad);
        $this->assertTrue($perfil->es_sustento_familiar);
        $this->assertEquals(3, $perfil->nro_dependientes);

        // 3. Personal de Bienestar ve la observación en la ficha
        $showResponse = $this->actingAs($bienestar)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Observación Social Confidencial');
        $showResponse->assertSee($observacionSensible);
    }

    public function test_admin_sees_perfil_but_never_confidential_observation(): void
    {
        $admin = $this->getUser('admin@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        $observacionSensible = 'Observacion ultra secreta de prueba.';
        $estudiante->perfilSocioeconomico()->updateOrCreate(
            ['id_estudiante' => $estudiante->id_estudiante],
            [
                'clasificacion_sisfoh' => 'pobre',
                'violencia_familiar'   => false,
                'situacion_orfandad'   => 'ninguna',
                'es_sustento_familiar' => false,
                'nro_dependientes'     => 1,
                'observacion'          => $observacionSensible,
                'fecha_actualizacion'  => now(),
            ]
        );

        // Administrador visualiza la ficha pero NO la observación confidencial
        $response = $this->actingAs($admin)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}");
        $response->assertStatus(200);
        $response->assertSee('Perfil Socioeconómico');
        $response->assertSee('Pobre');
        $response->assertDontSee('Observación Social Confidencial');
        $response->assertDontSee($observacionSensible);
        $response->assertDontSee('Actualizar perfil');

        // Administrador tiene prohibido modificar el perfil
        $this->actingAs($admin)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/perfil-socioeconomico", [
            'clasificacion_sisfoh' => 'no_pobre',
            'situacion_orfandad'   => 'ninguna',
            'nro_dependientes'     => 0,
        ])->assertStatus(403);
    }

    public function test_estudiante_and_docente_forbidden_from_updating_perfil(): void
    {
        $estudiante = $this->getUser('estudiante1@unitru.edu.pe');
        $docente = $this->getUser('docente@unitru.edu.pe');
        $idEst = $estudiante->estudiante->id_estudiante;

        // Estudiante recibe 403 al intentar postear perfil
        $this->actingAs($estudiante)->post("/bienestar/estudiantes/{$idEst}/perfil-socioeconomico", [
            'clasificacion_sisfoh' => 'pobre_extremo',
            'situacion_orfandad'   => 'ninguna',
            'nro_dependientes'     => 1,
        ])->assertStatus(403);

        // Docente recibe 403 al intentar postear perfil
        $this->actingAs($docente)->post("/bienestar/estudiantes/{$idEst}/perfil-socioeconomico", [
            'clasificacion_sisfoh' => 'pobre_extremo',
            'situacion_orfandad'   => 'ninguna',
            'nro_dependientes'     => 1,
        ])->assertStatus(403);
    }

    public function test_perfil_form_request_validations(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        // Enviar clasificación inválida y dependientes negativos
        $response = $this->actingAs($bienestar)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/perfil-socioeconomico", [
            'clasificacion_sisfoh' => 'clasificacion_inexistente',
            'situacion_orfandad'   => 'invalido',
            'nro_dependientes'     => -5,
        ]);

        $response->assertSessionHasErrors([
            'clasificacion_sisfoh',
            'situacion_orfandad',
            'nro_dependientes',
        ]);
    }
}
