<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos los estados académicos necesarios ('vigente' o 'finalizado')
        $estadoVigente = Estado::where('contexto', 'academico')
            ->where('slug', 'vigente')
            ->first();

        $estadoFinalizado = Estado::where('contexto', 'academico')
            ->where('slug', 'finalizado')
            ->first();

        if (!$estadoVigente) {
            return;
        }

        // Definimos gestiones de ejemplo (ajusta los años según necesites)
        $gestiones =
            [
                /*            [
                'nombre'    => '2025',
                'estado_id' => $estadoFinalizado ? $estadoFinalizado->id : $estadoVigente->id,
            ],*/
                [
                    'nombre'    => '2026',
                    'estado_id' => $estadoVigente->id, // La gestión actual en curso
                ],
            ];

        foreach ($gestiones as $gestion) {
            DB::table('gestions')->updateOrInsert(
                ['nombre' => $gestion['nombre']],
                [
                    'estado_id' => $gestion['estado_id'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
