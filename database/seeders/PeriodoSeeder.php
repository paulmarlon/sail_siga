<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos la gestión actual (ej. 2026) y un estado académico válido
        $gestionActual = DB::table('gestions')->where('nombre', '2026')->first();

        $estadoVigente = Estado::where('contexto', 'academico')
            ->where('slug', 'vigente')
            ->first();

        $estadoFinalizado = Estado::where('contexto', 'academico')
            ->where('slug', 'finalizado')
            ->first();

        if (!$gestionActual || !$estadoVigente) {
            return;
        }

        $periodos = [
            [
                'nombre'       => 'PRIMER PERIODO',
                'gestion_id'   => $gestionActual->id,
                'fecha_inicio' => '2026-02-01',
                'fecha_fin'    => '2026-06-30',
                'estado_id'    => $estadoFinalizado ? $estadoFinalizado->id : $estadoVigente->id,
            ],
            [
                'nombre'       => 'SEGUNDO PERIODO',
                'gestion_id'   => $gestionActual->id,
                'fecha_inicio' => '2026-08-01',
                'fecha_fin'    => '2026-12-15',
                'estado_id'    => $estadoVigente->id, // El semestre activo actualmente
            ],
        ];

        foreach ($periodos as $periodo) {
            DB::table('periodos')->updateOrInsert(
                [
                    'nombre'     => $periodo['nombre'],
                    'gestion_id' => $periodo['gestion_id']
                ],
                [
                    'fecha_inicio' => $periodo['fecha_inicio'],
                    'fecha_fin'    => $periodo['fecha_fin'],
                    'estado_id'    => $periodo['estado_id'],
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ]
            );
        }
    }
}
