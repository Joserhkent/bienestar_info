<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCondicionSeeder extends Seeder
{
    public function run(): void
    {
        $condiciones = [
            [
                'id_tipo_condicion' => 1,
                'nombre' => 'Trastorno del Espectro Autista (TEA)',
                'categoria' => 'neurodivergencia_tea',
                'base_legal' => 'Ley 30150 (Ley de Protección a las Personas con TEA)',
            ],
            [
                'id_tipo_condicion' => 2,
                'nombre' => 'Discapacidad Motriz - Usuario de Silla de Ruedas',
                'categoria' => 'fisica_movilidad',
                'base_legal' => 'Ley 29973 (Ley General de la Persona con Discapacidad)',
            ],
            [
                'id_tipo_condicion' => 3,
                'nombre' => 'Hipoacusia Bilateral Neurosensorial',
                'categoria' => 'sensorial_auditiva',
                'base_legal' => 'Ley 29973 (Ley General de la Persona con Discapacidad)',
            ],
            [
                'id_tipo_condicion' => 4,
                'nombre' => 'Baja Visión Severa',
                'categoria' => 'sensorial_visual',
                'base_legal' => 'Ley 29973 (Ley General de la Persona con Discapacidad)',
            ],
        ];

        foreach ($condiciones as $cond) {
            DB::table('bienestar.tipo_condicion')->updateOrInsert(
                ['id_tipo_condicion' => $cond['id_tipo_condicion']],
                $cond
            );
        }
    }
}
