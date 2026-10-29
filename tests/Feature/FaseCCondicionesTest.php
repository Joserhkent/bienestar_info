<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\EstudianteTipoCondicion;
use App\Modules\Bienestar\Models\Usuario;
use App\Modules\Bienestar\Services\CondicionEstudianteService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaseCCondicionesTest extends TestCase
{
    private function getUser(string $correo): Usuario
    {
        return Usuario::with(['rol', 'estudiante'])->where('correo', $correo)->firstOrFail();
    }

    public function test_bienestar_can_assign_condition_with_encrypted_pauta_pedagogica(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        $pautaConfidencial = 'Instrucciones estructuradas por escrito. Permitir tiempo adicional en evaluaciones de laboratorio.';

        $payload = [
            'id_tipo_condicion'       => 1, // TEA
            'grado'                   => 'moderado',
            'requiere_aula_accesible' => 1,
            'certificado_conadis'     => 'CONADIS-2026-9988',
            'pauta_pedagogica'        => $pautaConfidencial,
            'visible_docente'         => 1,
        ];

        $response = $this->actingAs($bienestar)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/condiciones", $payload);

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('success');

        // 1. En base de datos, la pauta debe estar cifrada (no en texto plano)
        $rawPivot = DB::table('bienestar.estudiante_tipo_condicion')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->where('id_tipo_condicion', 1)
            ->first();

        $this->assertNotNull($rawPivot);
        $this->assertNotEquals($pautaConfidencial, $rawPivot->pauta_pedagogica, 'La pauta pedagógica debe almacenarse cifrada.');
        $this->assertStringStartsWith('eyJ', $rawPivot->pauta_pedagogica);

        // 2. Modelo descifra transparentemente
        $pivot = EstudianteTipoCondicion::where('id_estudiante', $estudiante->id_estudiante)
            ->where('id_tipo_condicion', 1)
            ->first();

        $this->assertEquals($pautaConfidencial, $pivot->pauta_pedagogica);
        $this->assertEquals('moderado', $pivot->grado);
        $this->assertTrue($pivot->requiere_aula_accesible);
        $this->assertTrue($pivot->visible_docente);
        $this->assertEquals('CONADIS-2026-9988', $pivot->certificado_conadis);

        // 3. Personal de Bienestar ve la pauta en la ficha
        $showResponse = $this->actingAs($bienestar)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Trastorno del Espectro Autista (TEA)');
        $showResponse->assertSee('Pauta pedagógica confidencial:');
        $showResponse->assertSee($pautaConfidencial);
    }

    public function test_docente_sees_pauta_only_when_visible_docente_is_true(): void
    {
        $docente = $this->getUser('docente@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $service = app(CondicionEstudianteService::class);

        $pautaVisible = 'Pauta visible especial para el docente.';
        $pautaOculta = 'Pauta interna que el docente no debe ver.';

        // Asignar condición 1 visible para docente
        $service->asignarOActualizar($estudiante, [
            'id_tipo_condicion'       => 1,
            'grado'                   => 'leve',
            'requiere_aula_accesible' => false,
            'pauta_pedagogica'        => $pautaVisible,
            'visible_docente'         => true,
        ]);

        // Asignar condición 2 OCULTA para docente
        $service->asignarOActualizar($estudiante, [
            'id_tipo_condicion'       => 2,
            'grado'                   => 'severo',
            'requiere_aula_accesible' => true,
            'pauta_pedagogica'        => $pautaOculta,
            'visible_docente'         => false,
        ]);

        // Docente consulta su dashboard
        $response = $this->actingAs($docente)->get('/bienestar/dashboard');
        $response->assertStatus(200);
        $response->assertSee($pautaVisible);
        $response->assertDontSee($pautaOculta);
    }

    public function test_admin_sees_condition_names_but_never_pauta_pedagogica(): void
    {
        $admin = $this->getUser('admin@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $service = app(CondicionEstudianteService::class);

        $pautaConfidencial = 'Pauta super secreta no visible para admin.';

        $service->asignarOActualizar($estudiante, [
            'id_tipo_condicion'       => 1,
            'grado'                   => 'leve',
            'requiere_aula_accesible' => true,
            'pauta_pedagogica'        => $pautaConfidencial,
            'visible_docente'         => true,
        ]);

        // Admin ve la condición y estado de aula accesible, pero NO la pauta pedagógica ni el botón de asignar
        $response = $this->actingAs($admin)->get("/bienestar/estudiantes/{$estudiante->id_estudiante}");
        $response->assertStatus(200);
        $response->assertSee('Trastorno del Espectro Autista (TEA)');
        $response->assertSee('Aula accesible: Sí');
        $response->assertDontSee('Pauta pedagógica confidencial:');
        $response->assertDontSee($pautaConfidencial);
        $response->assertDontSee('Asignar condición');

        // Admin tiene prohibido modificar o eliminar condiciones
        $this->actingAs($admin)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/condiciones", [
            'id_tipo_condicion' => 1,
            'grado' => 'leve',
        ])->assertStatus(403);

        $this->actingAs($admin)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/condiciones/1/eliminar")
            ->assertStatus(403);
    }

    public function test_bienestar_can_delete_condition(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $service = app(CondicionEstudianteService::class);

        // Asegurar que existe la condición 1
        $service->asignarOActualizar($estudiante, [
            'id_tipo_condicion'       => 1,
            'grado'                   => 'leve',
            'requiere_aula_accesible' => false,
            'visible_docente'         => true,
        ]);

        $response = $this->actingAs($bienestar)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/condiciones/1/eliminar");
        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));

        // Verificar que ya no existe en la base de datos
        $exists = DB::table('bienestar.estudiante_tipo_condicion')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->where('id_tipo_condicion', 1)
            ->exists();

        $this->assertFalse($exists);
    }

    public function test_form_request_validations_for_condicion(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        $response = $this->actingAs($bienestar)->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/condiciones", [
            'id_tipo_condicion' => 9999, // inexistente
            'grado'             => 'invalido',
        ]);

        $response->assertSessionHasErrors(['id_tipo_condicion', 'grado']);
    }
}
