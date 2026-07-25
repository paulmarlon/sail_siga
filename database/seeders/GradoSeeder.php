<?php

namespace Database\Seeders;

use App\Models\Estado;
use App\Models\Nivel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos o aseguramos el estado 'vigente' para contexto académico
        $estadoVigente = Estado::firstOrCreate(
            ['slug' => 'vigente', 'contexto' => 'academico'],
            [
                'nombre' => 'Vigente',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#17a2b8'
            ]
        );

        // 2. Buscamos o aseguramos un nivel por defecto para evitar que falle si la tabla está vacía
        $nivelSuperior = Nivel::firstOrCreate(
            ['nombre' => 'LICENCIATURA']
        );

        $grados = [
            // Ciclo 1: Tronco Común
            ['nombre' => 'PRIMER SEMESTRE', 'orden' => 1, 'ciclo' => 1],
            ['nombre' => 'SEGUNDO SEMESTRE', 'orden' => 2, 'ciclo' => 1],

            // Ciclo 2: Especialidad
            ['nombre' => 'TERCER SEMESTRE', 'orden' => 3, 'ciclo' => 2],
            ['nombre' => 'CUARTO SEMESTRE', 'orden' => 4, 'ciclo' => 2],
            ['nombre' => 'QUINTO SEMESTRE', 'orden' => 5, 'ciclo' => 2],
            ['nombre' => 'SEXTO SEMESTRE', 'orden' => 6, 'ciclo' => 2],
            ['nombre' => 'SEPTIMO SEMESTRE', 'orden' => 7, 'ciclo' => 2],
            ['nombre' => 'OCTAVO SEMESTRE', 'orden' => 8, 'ciclo' => 2],
        ];

        foreach ($grados as $grado) {
            DB::table('grados')->updateOrInsert(
                ['orden' => $grado['orden']],
                [
                    'nombre'     => mb_strtoupper($grado['nombre']),
                    'ciclo'      => $grado['ciclo'],
                    'nivel_id'   => $nivelSuperior->id,
                    'estado_id'  => $estadoVigente->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
