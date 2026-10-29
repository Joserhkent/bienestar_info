<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\HistorialEstadoCaso;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\Seguimiento;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P4DerivacionesTest extends TestCase
{
    protected Usuario $bienestar;
    protected Usuario $especialista1;
    protected Usuario $especialista2;
    protected Usuario $docente;
    protected Usuario $estudiante1;
    protected CasoBienestar $casoPrueba;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bienestar = Usuario::with('rol')->where('correo', 'bienestar@unitru.edu.pe')->firstOrFail();
        $this->especialista1 = Usuario::with('especialista')->where('correo', 'psicologia@unitru.edu.pe')->firstOrFail();
        $this->especialista2 = Usuario::with('especialista')->where('correo', 'tutor@unitru.edu.pe')->firstOrFail();
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        $this->estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();

        // Caso base para pruebas de derivación
        $this->casoPrueba = CasoBienestar::firstOrCreate(
            ['id_caso' => 991],
            [
                'id_caso' => 991,
                'id_estudiante' => $this->estudiante1->estudiante->id_estudiante,
                'id_usuario_registro' => $this->bienestar->id_usuario,
                'id_tipo_caso' => 1,
                'descripcion' => 'Caso de prueba para derivación y seguimiento clínico.',
                'prioridad' => 'media',
                'estado' => 'registrado',
                'fecha_registro' => now(),
            ]
        );
    }

    /**
     * Test P4.1: Personal de Bienestar puede listar derivaciones.
     */
    public function test_bienestar_can_view_derivaciones_list(): void
    {
        $response = $this->actingAs($this->bienestar)->get('/bienestar/derivaciones');

        $response->assertStatus(200);
        $response->assertViewIs('bienestar::derivaciones.index');
        $response->assertViewHas('derivaciones');
        $response->assertViewHas('especialistas');
    }

    /**
     * Test P4.2: Personal de Bienestar puede abrir formulario de nueva derivación.
     */
    public function test_bienestar_can_open_create_derivacion_form(): void
    {
        $response = $this->actingAs($this->bienestar)->get('/bienestar/derivaciones/crear?id_caso=' . $this->casoPrueba->id_caso);

        $response->assertStatus(200);
        $response->assertViewIs('bienestar::derivaciones.create');
        $response->assertViewHas('casos');
        $response->assertViewHas('especialistas');
        $response->assertViewHas('casoSeleccionado');
    }

    /**
     * Test P4.3: Personal de Bienestar deriva caso dentro de DB::transaction() con historial y notificación.
     */
    public function test_bienestar_can_refer_case_to_specialist_in_transaction(): void
    {
        $motivoDerivacion = 'Se remite el expediente para evaluación psicológica y diagnóstico de apoyo emocional.';

        $response = $this->actingAs($this->bienestar)->post('/bienestar/derivaciones', [
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'motivo' => $motivoDerivacion,
        ]);

        $derivacion = Derivacion::where('id_caso', $this->casoPrueba->id_caso)
            ->latest('id_derivacion')
            ->firstOrFail();

        $response->assertRedirect("/bienestar/derivaciones/{$derivacion->id_derivacion}");
        $this->assertEquals('pendiente', $derivacion->estado);
        $this->assertEquals($this->especialista1->especialista->id_especialista, $derivacion->id_especialista);
        $this->assertEquals($motivoDerivacion, $derivacion->motivo);

        // Caso debe cambiar a 'derivado'
        $this->casoPrueba->refresh();
        $this->assertEquals('derivado', $this->casoPrueba->estado);

        // Historial de estados registrado
        $historial = HistorialEstadoCaso::where('id_caso', $this->casoPrueba->id_caso)
            ->latest('id_historial')
            ->firstOrFail();
        $this->assertEquals('derivado', $historial->estado_nuevo);
        $this->assertEquals($this->bienestar->id_usuario, $historial->id_usuario);

        // Notificación al especialista emitida
        $notificacion = Notificacion::where('id_usuario', $this->especialista1->id_usuario)
            ->latest('id_notificacion')
            ->firstOrFail();
        $this->assertStringContainsString('Nuevo caso derivado', $notificacion->titulo);
    }

    /**
     * Test P4.4: Especialista asignado puede ver la derivación.
     */
    public function test_assigned_specialist_can_view_derivacion(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación para evaluación psicológica integral.',
            'estado' => 'pendiente',
            'fecha_derivacion' => now(),
        ]);

        $response = $this->actingAs($this->especialista1)->get("/bienestar/derivaciones/{$derivacion->id_derivacion}");

        $response->assertStatus(200);
        $response->assertViewIs('bienestar::derivaciones.show');
        $response->assertSee('Derivación para evaluación psicológica integral.');
    }

    /**
     * Test P4.5: Especialista asignado puede aceptar la derivación.
     */
    public function test_assigned_specialist_can_accept_derivacion(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación para plan de sesiones psicopedagógicas.',
            'estado' => 'pendiente',
            'fecha_derivacion' => now(),
        ]);

        $response = $this->actingAs($this->especialista1)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/aceptar");

        $response->assertRedirect("/bienestar/derivaciones/{$derivacion->id_derivacion}");
        $derivacion->refresh();
        $this->assertEquals('aceptada', $derivacion->estado);
    }

    /**
     * Test P4.6: Especialista registra seguimiento clínico en DB::transaction() con notas cifradas.
     */
    public function test_assigned_specialist_can_record_follow_up_with_encrypted_notes(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación para seguimiento de rendimiento y adaptación.',
            'estado' => 'aceptada',
            'fecha_derivacion' => now(),
        ]);

        $notaClinica = 'Se realizó la primera entrevista clínica. Estudiante refiere estrés por carga horaria.';
        $acuerdos = 'Aplicación de técnica de respiración diafragmática y organización de horarios.';
        $proximaFecha = now()->addDays(7)->toDateString();

        $response = $this->actingAs($this->especialista1)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
            'fecha_sesion' => now()->toDateString(),
            'nota_avance' => $notaClinica,
            'acuerdos' => $acuerdos,
            'proxima_fecha' => $proximaFecha,
        ]);

        $response->assertRedirect("/bienestar/derivaciones/{$derivacion->id_derivacion}");

        $seguimiento = Seguimiento::where('id_derivacion', $derivacion->id_derivacion)->firstOrFail();

        // Verificar que los datos en memoria se leen descifrados correctamente por Eloquent
        $this->assertEquals($notaClinica, $seguimiento->nota_avance);
        $this->assertEquals($acuerdos, $seguimiento->acuerdos);

        // Verificar que en base de datos cruda el campo no está en texto plano (está cifrado)
        $rawSeguimiento = DB::table('bienestar.seguimiento')
            ->where('id_seguimiento', $seguimiento->id_seguimiento)
            ->first();
        $this->assertNotEquals($notaClinica, $rawSeguimiento->nota_avance);
        $this->assertStringStartsWith('eyJ', $rawSeguimiento->nota_avance);

        // Derivación pasa a 'en_seguimiento'
        $derivacion->refresh();
        $this->assertEquals('en_seguimiento', $derivacion->estado);

        // Caso pasa a 'en_seguimiento' y se registra en historial
        $this->casoPrueba->refresh();
        $this->assertEquals('en_seguimiento', $this->casoPrueba->estado);

        $historial = HistorialEstadoCaso::where('id_caso', $this->casoPrueba->id_caso)
            ->latest('id_historial')
            ->firstOrFail();
        $this->assertEquals('en_seguimiento', $historial->estado_nuevo);
    }

    /**
     * Test P4.7: Especialista asignado puede finalizar la derivación.
     */
    public function test_assigned_specialist_can_finalize_derivacion(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación para cierre de caso.',
            'estado' => 'en_seguimiento',
            'fecha_derivacion' => now(),
        ]);

        $response = $this->actingAs($this->especialista1)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/finalizar");

        $response->assertRedirect("/bienestar/derivaciones/{$derivacion->id_derivacion}");
        $derivacion->refresh();
        $this->assertEquals('finalizada', $derivacion->estado);
    }

    /**
     * Test P4.8: Docente recibe 403 en todas las rutas de derivación y seguimiento.
     */
    public function test_docente_forbidden_from_derivaciones(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación reservada de bienestar.',
            'estado' => 'pendiente',
            'fecha_derivacion' => now(),
        ]);

        // GET index
        $this->actingAs($this->docente)->get('/bienestar/derivaciones')->assertStatus(403);

        // GET crear
        $this->actingAs($this->docente)->get('/bienestar/derivaciones/crear')->assertStatus(403);

        // POST crear
        $this->actingAs($this->docente)->post('/bienestar/derivaciones', [
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'motivo' => 'Intento de docente no autorizado.',
        ])->assertStatus(403);

        // GET detalle
        $this->actingAs($this->docente)->get("/bienestar/derivaciones/{$derivacion->id_derivacion}")->assertStatus(403);

        // POST seguimiento
        $this->actingAs($this->docente)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
            'nota_avance' => 'Nota no autorizada de docente.',
        ])->assertStatus(403);
    }

    /**
     * Test P4.9: Estudiante recibe 403 en todas las rutas de derivación y seguimiento clínico.
     */
    public function test_estudiante_forbidden_from_derivaciones(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación confidencial.',
            'estado' => 'pendiente',
            'fecha_derivacion' => now(),
        ]);

        $this->actingAs($this->estudiante1)->get('/bienestar/derivaciones')->assertStatus(403);
        $this->actingAs($this->estudiante1)->get('/bienestar/derivaciones/crear')->assertStatus(403);
        $this->actingAs($this->estudiante1)->get("/bienestar/derivaciones/{$derivacion->id_derivacion}")->assertStatus(403);
        $this->actingAs($this->estudiante1)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
            'nota_avance' => 'Estudiante intentando escribir notas clínicas.',
        ])->assertStatus(403);
    }

    /**
     * Test P4.10: Especialista 2 no puede acceder ni registrar notas en derivación de Especialista 1 (Aislamiento).
     */
    public function test_specialist_cannot_access_or_modify_other_specialists_derivation(): void
    {
        // Derivación asignada a Especialista 1 (Psicología)
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación privada a especialista 1.',
            'estado' => 'pendiente',
            'fecha_derivacion' => now(),
        ]);

        // Especialista 2 intenta ver el detalle -> 403
        $this->actingAs($this->especialista2)
            ->get("/bienestar/derivaciones/{$derivacion->id_derivacion}")
            ->assertStatus(403);

        // Especialista 2 intenta registrar una nota de seguimiento -> 403
        $this->actingAs($this->especialista2)
            ->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
                'nota_avance' => 'Intento de registro cruzado por otro especialista.',
            ])
            ->assertStatus(403);
    }

    /**
     * Test P4.11: Validaciones estrictas de Form Request en derivación.
     */
    public function test_derivation_validation_rules(): void
    {
        // Caso faltante, especialista faltante, motivo corto (<10 caracteres)
        $response = $this->actingAs($this->bienestar)->post('/bienestar/derivaciones', [
            'id_caso' => '',
            'id_especialista' => '',
            'motivo' => 'Corto',
        ]);

        $response->assertSessionHasErrors(['id_caso', 'id_especialista', 'motivo']);
    }

    /**
     * Test P4.12: Validaciones estrictas de Form Request en seguimiento clínico.
     */
    public function test_seguimiento_validation_rules(): void
    {
        $derivacion = Derivacion::create([
            'id_caso' => $this->casoPrueba->id_caso,
            'id_especialista' => $this->especialista1->especialista->id_especialista,
            'id_usuario_deriva' => $this->bienestar->id_usuario,
            'motivo' => 'Derivación para validación de notas.',
            'estado' => 'aceptada',
            'fecha_derivacion' => now(),
        ]);

        // Nota menor a 10 caracteres y fecha pasada
        $response = $this->actingAs($this->especialista1)->post("/bienestar/derivaciones/{$derivacion->id_derivacion}/seguimientos", [
            'nota_avance' => 'abc',
            'proxima_fecha' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['nota_avance', 'proxima_fecha']);
    }
}
