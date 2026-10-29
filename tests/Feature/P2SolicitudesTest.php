<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\DocumentoAdjunto;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class P2SolicitudesTest extends TestCase
{
    protected Usuario $personalBienestar;
    protected Usuario $docente;
    protected Usuario $estudiante1;
    protected Usuario $estudiante2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personalBienestar = Usuario::with('rol')->where('correo', 'bienestar@unitru.edu.pe')->firstOrFail();
        $this->docente = Usuario::with('rol')->where('correo', 'docente@unitru.edu.pe')->firstOrFail();
        $this->estudiante1 = Usuario::with('estudiante')->where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $this->estudiante2 = Usuario::with('estudiante')->where('correo', 'estudiante2@unitru.edu.pe')->firstOrFail();
    }

    /**
     * Test P2.1: Estudiante envía solicitud con adjuntos válidos guardados en disco privado con nombre aleatorio.
     */
    public function test_student_can_submit_application_with_valid_document_attachment(): void
    {
        Storage::fake('private');

        $archivo = UploadedFile::fake()->create('boleta_ingresos.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'comedor_universitario',
            'motivo' => 'Solicito el beneficio de comedor debido a vulnerabilidad socioeconómica familiar acreditada.',
            'documentos' => [$archivo],
            'tipo_documento' => ['Boleta de Ingresos'],
        ]);

        $solicitud = SolicitudApoyo::where('id_estudiante', $this->estudiante1->estudiante->id_estudiante)
            ->where('tipo_apoyo', 'comedor_universitario')
            ->latest('id_solicitud')
            ->firstOrFail();

        $response->assertRedirect("/bienestar/solicitudes/{$solicitud->id_solicitud}");
        $this->assertEquals('enviada', $solicitud->estado);
        $this->assertNull($solicitud->fecha_resolucion);

        // Verificar el documento adjunto asociado
        $documento = DocumentoAdjunto::where('id_solicitud', $solicitud->id_solicitud)->firstOrFail();
        $this->assertEquals('boleta_ingresos.pdf', $documento->nombre_archivo);
        $this->assertEquals('Boleta de Ingresos', $documento->tipo_documento);

        // Verificar que la ruta en disco NO usa el nombre original (nombre aleatorio seguro)
        $this->assertNotEquals('boleta_ingresos.pdf', basename($documento->ruta_archivo));
        $this->assertStringStartsWith('documentos_apoyo/', $documento->ruta_archivo);
        Storage::disk('private')->assertExists($documento->ruta_archivo);
    }

    /**
     * Test P2.2: Regla R16 obligatoria: Rechazo si se envía la solicitud sin ningún archivo adjunto.
     */
    public function test_rule_r16_enforced_requires_at_least_one_document(): void
    {
        $response = $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'beca_alimentaria',
            'motivo' => 'Solicito beca pero no adjunto documentos para probar la validación de la Regla R16.',
            // documentos omitido
        ]);

        $response->assertSessionHasErrors([
            'documentos' => 'Debe adjuntar al menos un documento sustentatorio (Regla R16).',
        ]);
    }

    /**
     * Test P2.3: Validación estricta de tipo MIME y tamaño máximo (5MB) en Form Request.
     */
    public function test_form_request_rejects_invalid_file_extension_and_oversized_files(): void
    {
        $archivoInvalido = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $archivoPesado = UploadedFile::fake()->create('documento_gigante.pdf', 6000, 'application/pdf'); // 6MB

        $response = $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'subsidio_transporte',
            'motivo' => 'Solicitud con archivos no permitidos para verificar validaciones de seguridad.',
            'documentos' => [$archivoInvalido, $archivoPesado],
        ]);

        $response->assertSessionHasErrors([
            'documentos.0' => 'Solo se permiten archivos en formato PDF, JPG, JPEG o PNG.',
            'documentos.1' => 'El tamaño máximo por documento es de 5 MB.',
        ]);
    }

    /**
     * Test P2.4: Personal de Bienestar aprueba la solicitud en DB::transaction() y crea notificación.
     */
    public function test_personal_bienestar_can_approve_application_and_notification_is_created(): void
    {
        Storage::fake('private');
        $archivo = UploadedFile::fake()->create('sustento.pdf', 200, 'application/pdf');

        $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'exoneracion_pago',
            'motivo' => 'Solicitud para evaluación de exoneración de derechos de matrícula.',
            'documentos' => [$archivo],
        ]);

        $solicitud = SolicitudApoyo::where('id_estudiante', $this->estudiante1->estudiante->id_estudiante)
            ->where('tipo_apoyo', 'exoneracion_pago')
            ->latest('id_solicitud')
            ->firstOrFail();

        // Dictamen favorable por Personal de Bienestar
        $responseDictamen = $this->actingAs($this->personalBienestar)
            ->post("/bienestar/solicitudes/{$solicitud->id_solicitud}/dictaminar", [
                'estado' => 'aprobada',
                'observacion' => 'Evaluación socioeconómica favorable. Califica para exoneración.',
            ]);

        $responseDictamen->assertRedirect("/bienestar/solicitudes/{$solicitud->id_solicitud}");

        $solicitud->refresh();
        $this->assertEquals('aprobada', $solicitud->estado);
        $this->assertEquals($this->personalBienestar->id_usuario, $solicitud->id_usuario_revisa);
        $this->assertNotNull($solicitud->fecha_resolucion);

        // Verificar que se emitió la Notificación al estudiante
        $notificacion = Notificacion::where('id_usuario', $this->estudiante1->id_usuario)
            ->latest('id_notificacion')
            ->firstOrFail();

        $this->assertStringContainsString('Actualización en tu solicitud', $notificacion->titulo);
        $this->assertStringContainsString('aprobada', $notificacion->mensaje);
        $this->assertStringContainsString('Evaluación socioeconómica favorable', $notificacion->mensaje);
    }

    /**
     * Test P2.5: Al rechazar una solicitud, la observación/motivo es estrictamente obligatorio.
     */
    public function test_rejection_requires_mandatory_observation_motive(): void
    {
        Storage::fake('private');
        $archivo = UploadedFile::fake()->create('sustento2.pdf', 200, 'application/pdf');

        $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'otro',
            'motivo' => 'Solicitud de apoyo complementario para pruebas de rechazo sin motivo.',
            'documentos' => [$archivo],
        ]);

        $solicitud = SolicitudApoyo::latest('id_solicitud')->firstOrFail();

        // Intento de rechazar sin observación
        $responseRechazo = $this->actingAs($this->personalBienestar)
            ->post("/bienestar/solicitudes/{$solicitud->id_solicitud}/dictaminar", [
                'estado' => 'rechazada',
                'observacion' => '', // Vacío
            ]);

        $responseRechazo->assertSessionHasErrors([
            'observacion' => 'El motivo u observación es obligatorio al rechazar una solicitud.',
        ]);
    }

    /**
     * Test P2.6: Seguridad y 403 para Docente (no puede gestionar ni dictaminar solicitudes).
     */
    public function test_docente_is_forbidden_403_to_access_or_dictaminar_applications(): void
    {
        $responseIndex = $this->actingAs($this->docente)->get('/bienestar/solicitudes');
        $responseIndex->assertStatus(403);

        $responseCreate = $this->actingAs($this->docente)->get('/bienestar/solicitudes/crear');
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->docente)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'comedor_universitario',
            'motivo' => 'Intento de envío de solicitud desde rol Docente.',
        ]);
        $responseStore->assertStatus(403);
    }

    /**
     * Test P2.7: Aislamiento por ID: Un estudiante no puede ver la solicitud de otro estudiante.
     */
    public function test_student_cannot_view_another_students_application_by_changing_id(): void
    {
        Storage::fake('private');
        $archivo = UploadedFile::fake()->create('sustento_est1.pdf', 100, 'application/pdf');

        // Estudiante 1 crea solicitud
        $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'beca_alimentaria',
            'motivo' => 'Solicitud confidencial de estudiante 1.',
            'documentos' => [$archivo],
        ]);

        $solicitudEst1 = SolicitudApoyo::where('id_estudiante', $this->estudiante1->estudiante->id_estudiante)->latest('id_solicitud')->firstOrFail();

        // Estudiante 2 intenta consultar la solicitud de Estudiante 1 por ID
        $response = $this->actingAs($this->estudiante2)->get("/bienestar/solicitudes/{$solicitudEst1->id_solicitud}");
        $response->assertStatus(403);
    }

    /**
     * Test P2.8: Aislamiento por ID: Un estudiante no puede descargar el documento adjunto de otro estudiante.
     */
    public function test_student_cannot_download_another_students_document_by_changing_id(): void
    {
        Storage::fake('private');
        $archivo = UploadedFile::fake()->create('documento_privado_est1.pdf', 150, 'application/pdf');

        $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'subsidio_transporte',
            'motivo' => 'Solicitud con documento privado para prueba de descarga no autorizada.',
            'documentos' => [$archivo],
        ]);

        $documentoEst1 = DocumentoAdjunto::latest('id_documento')->firstOrFail();

        // Estudiante 2 intenta descargar el documento de Estudiante 1
        $response = $this->actingAs($this->estudiante2)->get("/bienestar/solicitudes/documentos/{$documentoEst1->id_documento}/descargar");
        $response->assertStatus(403);
    }

    /**
     * Test P2.9: Estudiante dueño y Personal de Bienestar pueden descargar el documento correctamente.
     */
    public function test_authorized_student_and_bienestar_can_download_document(): void
    {
        Storage::fake('private');
        $archivo = UploadedFile::fake()->create('declaracion_jurada.pdf', 120, 'application/pdf');

        $this->actingAs($this->estudiante1)->post('/bienestar/solicitudes', [
            'tipo_apoyo' => 'comedor_universitario',
            'motivo' => 'Solicitud para prueba de descarga autorizada.',
            'documentos' => [$archivo],
        ]);

        $documento = DocumentoAdjunto::latest('id_documento')->firstOrFail();

        // 1. Estudiante dueño descarga su documento
        $responseEstudiante = $this->actingAs($this->estudiante1)->get("/bienestar/solicitudes/documentos/{$documento->id_documento}/descargar");
        $responseEstudiante->assertStatus(200);

        // 2. Personal de Bienestar descarga el documento para revisión
        $responseBienestar = $this->actingAs($this->personalBienestar)->get("/bienestar/solicitudes/documentos/{$documento->id_documento}/descargar");
        $responseBienestar->assertStatus(200);
    }
}
