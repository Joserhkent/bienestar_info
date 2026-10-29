<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeporteSeeder extends Seeder
{
    public function run(): void
    {
        $deportes = [
            ['id_deporte' => 1, 'nombre' => 'Fútbol'],
            ['id_deporte' => 2, 'nombre' => 'Futsal'],
            ['id_deporte' => 3, 'nombre' => 'Básquetbol'],
            ['id_deporte' => 4, 'nombre' => 'Voleibol'],
            ['id_deporte' => 5, 'nombre' => 'Ajedrez'],
            ['id_deporte' => 6, 'nombre' => 'Atletismo'],
            ['id_deporte' => 7, 'nombre' => 'Tenis de Mesa'],
        ];

        foreach ($deportes as $dep) {
            DB::table('bienestar.deporte')->updateOrInsert(
                ['id_deporte' => $dep['id_deporte']],
                $dep
            );
        }
    }
}
