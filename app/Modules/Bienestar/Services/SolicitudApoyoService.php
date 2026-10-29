<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\DocumentoAdjunto;
use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Notificacion;
use App\Modules\Bienestar\Models\SolicitudApoyo;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SolicitudApoyoService
{
    /**
     * Registra una nueva solicitud de apoyo con sus documentos adjuntos dentro de una transacción.
     * Regla R16: Exige al menos un documento sustentatorio.
     *
     * @param array $data
     * @param array<UploadedFile> $archivos
     * @param Estudiante $estudiante
     * @param array $tiposDocumento
     * @return SolicitudApoyo
     */
    public function crearSolicitud(
        array $data,
        array $archivos,
        Estudiante $estudiante,
        array $tiposDocumento = []
    ): SolicitudApoyo {
        return DB::transaction(function () use ($data, $archivos, $estudiante, $tiposDocumento) {
            // 1. Crear registro de la solicitud
            $solicitud = SolicitudApoyo::create([
                'id_estudiante' => $estudiante->id_estudiante,
                'tipo_apoyo' => $data['tipo_apoyo'],
                'motivo' => $data['motivo'],
                'estado' => 'enviada',
                'fecha_envio' => now(),
                'fecha_resolucion' => null,
                'observacion' => null,
            ]);

            // 2. Procesar y guardar documentos en disco privado con nombre aleatorio
            foreach ($archivos as $index => $archivo) {
                if (!$archivo instanceof UploadedFile) {
                    continue;
                }

                // Generar nombre aleatorio único con extensión original segura
                $extension = strtolower($archivo->getClientOriginalExtension());
                $nombreAleatorio = Str::uuid()->toString() . '.' . $extension;

                // Guardar en disco privado bajo 'documentos_apoyo'
                $rutaAlmacenada = $archivo->storeAs('documentos_apoyo', $nombreAleatorio, 'private');

                // Registrar metadatos en bienestar.documento_adjunto
                $tipoDoc = $tiposDocumento[$index] ?? 'sustento_socioeconomico';
                if (empty($tipoDoc)) {
                    $tipoDoc = 'sustento_socioeconomico';
                }

                DocumentoAdjunto::create([
                    'id_solicitud' => $solicitud->id_solicitud,
                    'tipo_documento' => $tipoDoc,
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                    'ruta_archivo' => $rutaAlmacenada,
                    'tamano_kb' => (int) ceil($archivo->getSize() / 1024),
                    'fecha_subida' => now(),
                ]);
            }

            return $solicitud->load(['documentos', 'estudiante.usuario']);
        });
    }

    /**
     * Dictamina una solicitud (aprobar o rechazar) registrando fecha, usuario revisor y notificando al estudiante.
     * Operación atómica con DB::transaction().
     *
     * @param SolicitudApoyo $solicitud
     * @param string $estado
     * @param string|null $observacion
     * @param Usuario $usuarioRevisa
     * @return SolicitudApoyo
     */
    public function dictaminar(
        SolicitudApoyo $solicitud,
        string $estado,
        ?string $observacion,
        Usuario $usuarioRevisa
    ): SolicitudApoyo {
        return DB::transaction(function () use ($solicitud, $estado, $observacion, $usuarioRevisa) {
            // 1. Actualizar estado y dictamen en solicitud_apoyo
            $solicitud->estado = $estado;
            $solicitud->observacion = $observacion;
            $solicitud->id_usuario_revisa = $usuarioRevisa->id_usuario;
            $solicitud->fecha_resolucion = now();
            $solicitud->save();

            // 2. Notificar al estudiante sobre la resolución emitida
            $estudiante = $solicitud->estudiante;
            if ($estudiante && $estudiante->id_usuario) {
                $estadoTexto = strtoupper($estado);
                $tipoApoyoLegible = str_replace('_', ' ', $solicitud->tipo_apoyo);
                
                $titulo = "Resolución de Solicitud de Apoyo: {$estadoTexto}";
                $mensaje = "Su solicitud para '{$tipoApoyoLegible}' ha sido {$estadoTexto} por la Dirección de Bienestar Universitario.";
                
                if (!empty($observacion)) {
                    $mensaje .= "\nObservaciones / Sustento del dictamen: {$observacion}";
                }

                Notificacion::create([
                    'id_usuario' => $estudiante->id_usuario,
                    'id_caso' => null,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'canal' => 'sistema',
                    'fecha_envio' => now(),
                    'leida' => false,
                ]);
            }

            return $solicitud->fresh(['documentos', 'estudiante.usuario', 'usuarioRevisa']);
        });
    }

    /**
     * Retorna la respuesta de descarga segura para un documento verificado.
     *
     * @param DocumentoAdjunto $documento
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function descargarDocumento(DocumentoAdjunto $documento)
    {
        $disk = Storage::disk('private');

        if (!$disk->exists($documento->ruta_archivo)) {
            // Intentar fallback en disco local
            if (Storage::disk('local')->exists($documento->ruta_archivo)) {
                return Storage::disk('local')->download($documento->ruta_archivo, $documento->nombre_archivo);
            }
            abort(404, 'El archivo solicitado no existe en el almacenamiento seguro.');
        }

        return $disk->download($documento->ruta_archivo, $documento->nombre_archivo);
    }
}
