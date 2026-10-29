<?php

namespace App\Modules\Bienestar\Services;

use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\Rol;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioService
{
    /**
     * Registra un nuevo especialista con su cuenta institucional y credencial temporal en una transacción.
     *
     * @return array{usuario: Usuario, especialista: Especialista, password_temporal: string}
     */
    public function registrarEspecialista(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Obtener ID del rol Especialista
            $rol = Rol::where('nombre', 'Especialista')->first();
            $idRol = $rol ? $rol->id_rol : 3;

            // 2. Generar credencial temporal institucional (ej. UNT-B7M9X2)
            $passwordTemporal = 'UNT-' . strtoupper(Str::random(6));

            // 3. Crear usuario con cambio obligatorio de contraseña en primer ingreso
            $usuario = Usuario::create([
                'id_rol'                  => $idRol,
                'correo'                  => strtolower(trim($data['correo'])),
                'contrasena_hash'         => Hash::make($passwordTemporal),
                'estado'                  => 'activo',
                'debe_cambiar_contrasena' => true,
                'fecha_creacion'          => now(),
            ]);

            // 4. Crear el perfil profesional del especialista
            $especialista = Especialista::create([
                'id_usuario'      => $usuario->id_usuario,
                'nombres'         => trim($data['nombres']),
                'apellidos'       => trim($data['apellidos']),
                'especialidad'    => trim($data['especialidad']),
                'nro_colegiatura' => trim($data['nro_colegiatura']),
                'estado'          => 'activo',
            ]);

            return [
                'usuario'           => $usuario,
                'especialista'      => $especialista,
                'password_temporal' => $passwordTemporal,
            ];
        });
    }

    /**
     * Alterna el estado de una cuenta entre 'activo' y 'suspendido' en transacción.
     */
    public function alternarEstado(Usuario $usuario): string
    {
        return DB::transaction(function () use ($usuario) {
            $nuevoEstado = $usuario->estado === 'activo' ? 'suspendido' : 'activo';

            $usuario->update(['estado' => $nuevoEstado]);

            if ($usuario->especialista) {
                $usuario->especialista->update(['estado' => $nuevoEstado]);
            }

            return $nuevoEstado;
        });
    }

    /**
     * Restablece la contraseña temporal de un usuario y activa la obligación de cambio.
     */
    public function restablecerPassword(Usuario $usuario): string
    {
        return DB::transaction(function () use ($usuario) {
            $passwordTemporal = 'UNT-' . strtoupper(Str::random(6));

            $usuario->update([
                'contrasena_hash'         => Hash::make($passwordTemporal),
                'debe_cambiar_contrasena' => true,
            ]);

            return $passwordTemporal;
        });
    }
}
