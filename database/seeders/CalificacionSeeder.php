<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MatriculacionMateria;
use App\Models\CalificacionParcial;
use App\Models\CalificacionDetalle;
use Illuminate\Support\Facades\DB;

class CalificacionSeeder extends Seeder
{
    public function run()
    {
        // 1. Cargamos las matriculaciones con sus relaciones anidadas para evitar consultas N+1
        $matriculaciones = MatriculacionMateria::with('oferta.pensum')->get();

        foreach ($matriculaciones as $matriculacion) {
            // Saltamos usando Eloquent puro a través de tus relaciones
            $carreraId = optional($matriculacion->oferta->pensum)->carrera_id;

            if (!$carreraId) {
                continue;
            }

            // 2. Buscamos en la Meta los parciales configurados para esta carrera
            // (Nota: Si tienes un modelo Eloquent para la Meta, puedes usarlo aquí. Usamos DB si es tabla suelta).
            $configParciales = DB::table('configuracion_parcial_meta')
                ->where('carrera_id', $carreraId)
                ->get();

            foreach ($configParciales as $cpm) {
                // 3. Creamos o recuperamos la cabecera (Snapshot) usando Eloquent
                $calificacionParcial = CalificacionParcial::firstOrCreate(
                    [
                        'matriculacion_id' => $matriculacion->id,
                        'configuracion_parcial_id' => $cpm->id, // <-- ¡Esto faltaba amarrar aquí!
                        'nro_parcial'      => $cpm->nro_parcial,
                    ],
                    [
                        'ponderacion_parcial' => $cpm->ponderacion_parcial,
                        'estado_id'           => $matriculacion->estado_id,
                    ]
                );

                // 4. Buscamos los componentes meta de este parcial
                $configComponentes = DB::table('configuracion_componente_meta')
                    ->where('configuracion_parcial_id', $cpm->id)
                    ->get();

                foreach ($configComponentes as $ccm) {
                    // 5. Creamos los detalles hijos (Snapshot de componentes) usando Eloquent
                    CalificacionDetalle::firstOrCreate(
                        [
                            'calificacion_parcial_id' => $calificacionParcial->id,
                            'tipo_componente'         => $ccm->tipo_componente,
                        ],
                        [
                            'ponderacion_componente' => $ccm->ponderacion_componente,
                            'nota'                   => 0.00,
                            'modalidad_origen'       => 'directa',
                            'estado_id'              => $matriculacion->estado_id,
                        ]
                    );
                }
            }
        }
    }
}
