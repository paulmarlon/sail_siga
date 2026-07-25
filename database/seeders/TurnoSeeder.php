<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turnos = [
            ['nombre' => 'MAÑANA'],
        ];

        foreach ($turnos as $turno) {
            DB::table('turnos')->updateOrInsert(
                ['nombre' => $turno['nombre']],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
