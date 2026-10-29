<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Rol;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EstudianteService
{
    /**
     * Registra un estudiante y su usuario de acceso con contraseña temporal en una sola transacción.
     *
     * @param array $data
     * @return array{estudiante: Estudiante, password_temporal: string}
     */
    public function registrarEstudiante(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Obtener ID del rol Estudiante
            $rolEstudiante = Rol::where('nombre', 'Estudiante')->first();
            $idRol = $rolEstudiante ? $rolEstudiante->id_rol : 1;

            // 2. Generar contraseña temporal legible (ej. UNT-A8K2F9)
            $passwordTemporal = 'UNT-' . strtoupper(Str::random(6));

            // 3. Crear el usuario asociado con obligación de cambio de contraseña
            $usuario = Usuario::create([
                'id_rol'                  => $idRol,
                'correo'                  => strtolower(trim($data['correo'])),
                'contrasena_hash'         => Hash::make($passwordTemporal),
                'estado'                  => 'activo',
                'debe_cambiar_contrasena' => true,
                'fecha_creacion'          => now(),
            ]);

            // 4. Crear el registro del estudiante
            $estudiante = Estudiante::create([
                'id_usuario'           => $usuario->id_usuario,
                'codigo_universitario' => trim($data['codigo_universitario']),
                'dni'                  => trim($data['dni']),
                'nombres'              => trim($data['nombres']),
                'apellidos'            => trim($data['apellidos']),
                'sexo'                 => $data['sexo'],
                'fecha_nacimiento'     => $data['fecha_nacimiento'],
                'telefono'             => !empty($data['telefono']) ? trim($data['telefono']) : null,
                'direccion_actual'     => !empty($data['direccion_actual']) ? trim($data['direccion_actual']) : null,
                'zona_procedencia'     => trim($data['zona_procedencia']),
                'lugar_procedencia'    => trim($data['lugar_procedencia']),
                'ciclo_actual'         => (int) $data['ciclo_actual'],
                'fecha_registro'       => now(),
            ]);

            return [
                'estudiante'        => $estudiante,
                'password_temporal' => $passwordTemporal,
            ];
        });
    }

    /**
     * Actualiza los datos del estudiante y el correo de su cuenta en una transacción.
     */
    public function actualizarEstudiante(Estudiante $estudiante, array $data): Estudiante
    {
        return DB::transaction(function () use ($estudiante, $data) {
            // Actualizar correo del usuario si cambió
            if (isset($data['correo']) && $estudiante->usuario) {
                $nuevoCorreo = strtolower(trim($data['correo']));
                if ($nuevoCorreo !== $estudiante->usuario->correo) {
                    $estudiante->usuario->update(['correo' => $nuevoCorreo]);
                }
            }

            // Actualizar datos del estudiante
            $estudiante->update([
                'codigo_universitario' => trim($data['codigo_universitario']),
                'dni'                  => trim($data['dni']),
                'nombres'              => trim($data['nombres']),
                'apellidos'            => trim($data['apellidos']),
                'sexo'                 => $data['sexo'],
                'fecha_nacimiento'     => $data['fecha_nacimiento'],
                'telefono'             => !empty($data['telefono']) ? trim($data['telefono']) : null,
                'direccion_actual'     => !empty($data['direccion_actual']) ? trim($data['direccion_actual']) : null,
                'zona_procedencia'     => trim($data['zona_procedencia']),
                'lugar_procedencia'    => trim($data['lugar_procedencia']),
                'ciclo_actual'         => (int) $data['ciclo_actual'],
            ]);

            return $estudiante->fresh(['usuario']);
        });
    }

    /**
     * Elimina a un estudiante y su usuario de acceso en una transacción limpia.
     */
    public function eliminarEstudiante(Estudiante $estudiante): void
    {
        DB::transaction(function () use ($estudiante) {
            $usuario = $estudiante->usuario;

            // Eliminar registros dependientes
            $estudiante->perfilSocioeconomico()?->delete();
            DB::table('bienestar.estudiante_tipo_condicion')->where('id_estudiante', $estudiante->id_estudiante)->delete();
            DB::table('bienestar.estudiante_deporte')->where('id_estudiante', $estudiante->id_estudiante)->delete();

            $estudiante->delete();

            if ($usuario) {
                $usuario->delete();
            }
        });
    }
}
