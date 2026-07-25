<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'LICENCIATURA'],
            ['nombre' => 'TECNICO SUPERIOR'],
            ['nombre' => 'MAESTRIA'],
            ['nombre' => 'DOCTORADO'],
        ];

        foreach ($niveles as $nivel) {
            Nivel::updateOrCreate(
                ['nombre' => $nivel['nombre']],
                $nivel
            );
        }
    }
}
