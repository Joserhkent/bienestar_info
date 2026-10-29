<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id_rol' => 1,
                'nombre' => 'Estudiante',
                'descripcion' => 'Estudiante matriculado con acceso a solicitudes de apoyo, citas y consulta de expediente personal.',
            ],
            [
                'id_rol' => 2,
                'nombre' => 'Personal de Bienestar',
                'descripcion' => 'Responsable de registrar casos, evaluar solicitudes socioeconómicas y derivar a especialistas.',
            ],
            [
                'id_rol' => 3,
                'nombre' => 'Especialista',
                'descripcion' => 'Psicólogo o Tutor encargado de publicar horarios, atender citas y registrar notas de seguimiento.',
            ],
            [
                'id_rol' => 4,
                'nombre' => 'Docente',
                'descripcion' => 'Docente con acceso de solo lectura exclusivo a las pautas pedagógicas de inclusión académica (Ley 30150).',
            ],
            [
                'id_rol' => 5,
                'nombre' => 'Administrador',
                'descripcion' => 'Dirección de Escuela con privilegios de auditoría y parametrización de reportes de gestión.',
            ],
        ];

        foreach ($roles as $rol) {
            DB::table('bienestar.rol')->updateOrInsert(
                ['id_rol' => $rol['id_rol']],
                $rol
            );
        }
    }
}
