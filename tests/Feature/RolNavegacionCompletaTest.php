<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Models\Usuario;
use Tests\TestCase;

class RolNavegacionCompletaTest extends TestCase
{
    /**
     * Prueba automática: para cada uno de los 5 roles, inicia sesión y sigue
     * TODOS los enlaces GET visibles desde su pantalla de inicio (menú, tarjetas,
     * tablas, detalles y notificaciones). Falla si alguno responde 403, 404 o 500.
     */
    public function test_all_five_roles_can_navigate_all_visible_links_without_errors(): void
    {
        $cuentas = [
            'Administrador' => 'admin@unitru.edu.pe',
            'Personal de Bienestar' => 'bienestar@unitru.edu.pe',
            'Especialista' => 'psicologia@unitru.edu.pe',
            'Estudiante' => 'estudiante1@unitru.edu.pe',
            'Docente' => 'docente@unitru.edu.pe',
        ];

        foreach ($cuentas as $rolNombre => $correo) {
            $user = Usuario::with(['rol', 'especialista', 'estudiante'])->where('correo', $correo)->firstOrFail();

            // 1. Acceso a la pantalla de inicio (Dashboard)
            $response = $this->actingAs($user)->get('/bienestar/dashboard');
            $this->assertEquals(200, $response->getStatusCode(), "Dashboard falló para {$rolNombre}");

            $html = $response->getContent();

            // 2. Extraer todos los enlaces href visibles
            preg_match_all('/href="([^"]+)"/i', $html, $matches);
            $enlaces = array_unique($matches[1] ?? []);

            $enlacesAuditados = 0;

            foreach ($enlaces as $enlace) {
                // Filtrar enlaces externos, javascript, anclas o logout
                if (str_starts_with($enlace, '#') || str_starts_with($enlace, 'javascript:') || str_contains($enlace, 'cdn.jsdelivr.net') || str_contains($enlace, 'logout')) {
                    continue;
                }

                // Normalizar URL a path relativo si contiene dominio
                $path = parse_url($enlace, PHP_URL_PATH);
                if (empty($path) || !str_starts_with($path, '/bienestar')) {
                    continue;
                }

                $query = parse_url($enlace, PHP_URL_QUERY);
                $urlFinal = $query ? "{$path}?{$query}" : $path;

                // 3. Seguir cada enlace visible desde la pantalla de inicio
                $linkResponse = $this->actingAs($user)->get($urlFinal);
                $status = $linkResponse->getStatusCode();

                $this->assertNotEquals(
                    403,
                    $status,
                    "ERROR 403: El rol '{$rolNombre}' ve un enlace visible que le está prohibido: {$urlFinal}"
                );
                $this->assertNotEquals(
                    404,
                    $status,
                    "ERROR 404: El rol '{$rolNombre}' ve un enlace roto: {$urlFinal}"
                );
                $this->assertNotEquals(
                    500,
                    $status,
                    "ERROR 500: El rol '{$rolNombre}' provocó un error del servidor al visitar: {$urlFinal}"
                );

                // Debe ser 200 o redirección 302 hacia una ruta válida
                if ($status === 302) {
                    $redirectUrl = $linkResponse->headers->get('Location');
                    $followResp = $this->actingAs($user)->get($redirectUrl);
                    $this->assertEquals(
                        200,
                        $followResp->getStatusCode(),
                        "La redirección desde {$urlFinal} hacia {$redirectUrl} para '{$rolNombre}' no respondió 200."
                    );
                } else {
                    $this->assertEquals(
                        200,
                        $status,
                        "El enlace {$urlFinal} para '{$rolNombre}' no respondió 200 (respondió {$status})."
                    );
                }

                $enlacesAuditados++;
            }

            $this->assertGreaterThan(
                0,
                $enlacesAuditados,
                "No se encontraron enlaces auditables para el rol {$rolNombre}."
            );
        }
    }

    /**
     * Prueba automática: verifica el destino de las redirecciones tras acciones POST
     * garantizando que el usuario regrese a una vista válida con código 200.
     */
    public function test_post_redirections_land_on_valid_pages(): void
    {
        // 1. Notificación leída: redirige a bandeja con 200
        $user = Usuario::where('correo', 'estudiante1@unitru.edu.pe')->firstOrFail();
        $notif = \App\Modules\Bienestar\Models\Notificacion::where('id_usuario', $user->id_usuario)->first();

        if ($notif) {
            $resp = $this->actingAs($user)->post("/bienestar/notificaciones/{$notif->id_notificacion}/leer");
            $resp->assertRedirect();
            $dest = $resp->headers->get('Location');
            $this->actingAs($user)->get($dest)->assertStatus(200);
        }

        // 2. Notificaciones marcar todas leídas
        $respTodas = $this->actingAs($user)->post('/bienestar/notificaciones/leer-todas');
        $respTodas->assertRedirect();
        $this->actingAs($user)->get($respTodas->headers->get('Location'))->assertStatus(200);
    }
}
