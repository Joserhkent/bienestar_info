<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BienestarProduccionSeeder extends Seeder
{
    /**
     * Seeder exclusivo para entorno de producción:
     * Carga únicamente catálogos esenciales del sistema y una cuenta inicial de Administrador.
     * Cero expedientes ficticios, cero citas dummy y cero solicitudes de prueba.
     */
    public function run(): void
    {
        // 1. Catálogos base institucionales
        $this->call([
            RolSeeder::class,
            TipoCondicionSeeder::class,
            DeporteSeeder::class,
            TipoCasoSeeder::class,
        ]);

        // 2. Cuenta inicial de Administrador / Dirección de Escuela
        $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@unitru.edu.pe');
        $adminPassword = env('INITIAL_ADMIN_PASSWORD');

        if (empty($adminPassword)) {
            if ($this->command) {
                $adminPassword = $this->command->secret('Ingrese la contrasena para el Administrador inicial de produccion:');
            }
            if (empty($adminPassword)) {
                throw new \InvalidArgumentException('Debe definir la variable de entorno INITIAL_ADMIN_PASSWORD o ingresar una contrasena interactiva para ejecutar el seeder de produccion.');
            }
        }

        if ($adminPassword === 'password123') {
            throw new \InvalidArgumentException('Por razones de seguridad, la contrasena de produccion no puede ser la clave predeterminada de prueba "password123".');
        }

        DB::table('bienestar.usuario')->updateOrInsert(
            ['correo' => $adminEmail],
            [
                'id_rol' => 5, // Rol Administrador
                'correo' => $adminEmail,
                'contrasena_hash' => Hash::make($adminPassword),
                'estado' => 'activo',
            ]
        );

        // 3. Sincronizar secuencias seriales de PostgreSQL
        $tablasBase = [
            'rol' => 'id_rol',
            'usuario' => 'id_usuario',
            'tipo_condicion' => 'id_tipo_condicion',
            'deporte' => 'id_deporte',
            'tipo_caso' => 'id_tipo_caso',
            'reporte' => 'id_reporte',
            'notificacion' => 'id_notificacion',
        ];

        foreach ($tablasBase as $tabla => $pk) {
            DB::statement("SELECT setval(pg_get_serial_sequence('bienestar.{$tabla}', '{$pk}'), COALESCE((SELECT MAX({$pk}) FROM bienestar.{$tabla}), 1));");
        }
    }
}
