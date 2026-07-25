<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParaleloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paralelos = [
            ['nombre' => 'A'],
            ['nombre' => 'B'],
            ['nombre' => 'C'],
            ['nombre' => 'D'],
            ['nombre' => 'E'],
            ['nombre' => 'F'],
            ['nombre' => 'G'],
            ['nombre' => 'H'],
        ];

        foreach ($paralelos as $paralelo) {
            DB::table('paralelos')->updateOrInsert(
                ['nombre' => $paralelo['nombre']],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
