<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCasoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'id_tipo_caso' => 1,
                'nombre' => 'Apoyo Psicológico y Salud Mental',
                'descripcion' => 'Ansiedad, depresión, estrés académico y contención emocional.',
            ],
            [
                'id_tipo_caso' => 2,
                'nombre' => 'Tutoría y Rendimiento Académico',
                'descripcion' => 'Riesgo de desaprobación, deserción o dificultades de adaptación curricular.',
            ],
            [
                'id_tipo_caso' => 3,
                'nombre' => 'Vulnerabilidad Socioeconómica y Comedor',
                'descripcion' => 'Casos SISFOH pobre extremo, orfandad o sustento del hogar en riesgo.',
            ],
            [
                'id_tipo_caso' => 4,
                'nombre' => 'Accesibilidad e Inclusión (TEA / Discapacidad)',
                'descripcion' => 'Gestión de aulas accesibles y aplicación de pautas pedagógicas (Leyes 29973 y 30150).',
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('bienestar.tipo_caso')->updateOrInsert(
                ['id_tipo_caso' => $tipo['id_tipo_caso']],
                $tipo
            );
        }
    }
}
