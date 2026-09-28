<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionParcialMeta;
use App\Models\ConfiguracionComponenteMeta;

class ConfiguracionEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        // Supongamos un estado ID 1 (Activo)
        $estadoActivoId = 1;
        // Carrera ID 1 (Ej: Ing. Sistemas, o null si es institucional por defecto)
        $carreraId = 1;

        // 1. Primer Parcial (30%)
        $p1 = ConfiguracionParcialMeta::create([
            'carrera_id' => $carreraId,
            'nro_parcial' => 1,
            'nombre' => 'Primer Parcial',
            'ponderacion_parcial' => 30.00,
            'estado_id' => $estadoActivoId,
        ]);

        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p1->id,
            'tipo_componente' => 'examen',
            'ponderacion_componente' => 75.00,
        ]);
        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p1->id,
            'tipo_componente' => 'trabajo_practico',
            'ponderacion_componente' => 25.00,
        ]);

        // 2. Segundo Parcial (30%)
        $p2 = ConfiguracionParcialMeta::create([
            'carrera_id' => $carreraId,
            'nro_parcial' => 2,
            'nombre' => 'Segundo Parcial',
            'ponderacion_parcial' => 30.00,
            'estado_id' => $estadoActivoId,
        ]);

        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p2->id,
            'tipo_componente' => 'examen',
            'ponderacion_componente' => 75.00,
        ]);
        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p2->id,
            'tipo_componente' => 'trabajo_practico',
            'ponderacion_componente' => 25.00,
        ]);

        // 3. Tercer Parcial (40%)
        $p3 = ConfiguracionParcialMeta::create([
            'carrera_id' => $carreraId,
            'nro_parcial' => 3,
            'nombre' => 'Tercer Parcial',
            'ponderacion_parcial' => 40.00,
            'estado_id' => $estadoActivoId,
        ]);

        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p3->id,
            'tipo_componente' => 'examen',
            'ponderacion_componente' => 75.00,
        ]);
        ConfiguracionComponenteMeta::create([
            'configuracion_parcial_id' => $p3->id,
            'tipo_componente' => 'trabajo_practico',
            'ponderacion_componente' => 25.00,
        ]);
    }
}
