<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Deporte;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FaseDDeportesTest extends TestCase
{
    private function getUser(string $correo): Usuario
    {
        return Usuario::with('rol')->where('correo', $correo)->firstOrFail();
    }

    public function test_bienestar_can_enroll_student_in_sport_in_transaction(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $deporte = Deporte::firstOrFail();

        // Limpiar registro previo si existiera para asegurar prueba limpia
        DB::table('bienestar.estudiante_deporte')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->where('id_deporte', $deporte->id_deporte)
            ->delete();

        $payload = [
            'id_deporte'           => $deporte->id_deporte,
            'nivel'                => 'Avanzado',
            'disponible_seleccion' => 1,
        ];

        $response = $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", $payload);

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bienestar.estudiante_deporte', [
            'id_estudiante'        => $estudiante->id_estudiante,
            'id_deporte'           => $deporte->id_deporte,
            'nivel'                => 'Avanzado',
            'disponible_seleccion' => true,
        ]);
    }

    public function test_bienestar_can_update_existing_sport_level_and_selection(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $deporte = Deporte::firstOrFail();

        // Inscribir inicial
        $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", [
                'id_deporte'           => $deporte->id_deporte,
                'nivel'                => 'Principiante',
                'disponible_seleccion' => 1,
            ]);

        // Actualizar nivel a Intermedio y no disponible
        $response = $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", [
                'id_deporte'           => $deporte->id_deporte,
                'nivel'                => 'Intermedio',
                'disponible_seleccion' => 0,
            ]);

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bienestar.estudiante_deporte', [
            'id_estudiante'        => $estudiante->id_estudiante,
            'id_deporte'           => $deporte->id_deporte,
            'nivel'                => 'Intermedio',
            'disponible_seleccion' => false,
        ]);
    }

    public function test_bienestar_can_remove_sport_enrollment(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $deporte = Deporte::firstOrFail();

        // Inscribir
        $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", [
                'id_deporte'           => $deporte->id_deporte,
                'nivel'                => 'Avanzado',
                'disponible_seleccion' => 1,
            ]);

        // Eliminar
        $response = $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes/{$deporte->id_deporte}/eliminar");

        $response->assertRedirect(route('bienestar.estudiantes.show', $estudiante->id_estudiante));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bienestar.estudiante_deporte', [
            'id_estudiante' => $estudiante->id_estudiante,
            'id_deporte'    => $deporte->id_deporte,
        ]);
    }

    public function test_admin_docente_and_estudiante_are_forbidden_from_managing_sports(): void
    {
        $admin = $this->getUser('admin@unitru.edu.pe');
        $docente = $this->getUser('docente@unitru.edu.pe');
        $estudianteUser = $this->getUser('estudiante1@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();
        $deporte = Deporte::firstOrFail();

        $payload = [
            'id_deporte'           => $deporte->id_deporte,
            'nivel'                => 'Principiante',
            'disponible_seleccion' => 1,
        ];

        // Admin -> 403 Forbidden
        $this->actingAs($admin)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes/{$deporte->id_deporte}/eliminar")
            ->assertStatus(403);

        // Docente -> 403 Forbidden
        $this->actingAs($docente)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", $payload)
            ->assertStatus(403);

        // Estudiante -> 403 Forbidden
        $this->actingAs($estudianteUser)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", $payload)
            ->assertStatus(403);
    }

    public function test_form_request_validations_for_sports(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');
        $estudiante = Estudiante::firstOrFail();

        // Deporte inexistente
        $response = $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", [
                'id_deporte'           => 9999,
                'nivel'                => 'Avanzado',
                'disponible_seleccion' => 1,
            ]);
        $response->assertSessionHasErrors('id_deporte');

        // Nivel inválido
        $responseNivel = $this->actingAs($bienestar)
            ->post("/bienestar/estudiantes/{$estudiante->id_estudiante}/deportes", [
                'id_deporte'           => 1,
                'nivel'                => 'NivelExpertoInvalido',
                'disponible_seleccion' => 1,
            ]);
        $responseNivel->assertSessionHasErrors('nivel');
    }

    public function test_404_for_nonexistent_student(): void
    {
        $bienestar = $this->getUser('bienestar@unitru.edu.pe');

        $this->actingAs($bienestar)
            ->post('/bienestar/estudiantes/999999/deportes', [
                'id_deporte'           => 1,
                'nivel'                => 'Principiante',
                'disponible_seleccion' => 1,
            ])
            ->assertStatus(404);
    }
}
