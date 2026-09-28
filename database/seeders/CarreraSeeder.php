<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Estado;
use App\Models\Nivel;
use App\Models\ConfiguracionParcialMeta;
use App\Models\ConfiguracionComponenteMeta;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos un estado académico válido (por ejemplo, 'vigente')
        $estadoVigente = Estado::where('contexto', 'academico')
            ->where('slug', 'vigente')
            ->first();

        // Buscamos un nivel por defecto
        $nivelSuperior = Nivel::first();

        if (!$estadoVigente || !$nivelSuperior) {
            return;
        }

        // 1. Creamos primero el Tronco Común
        $troncoComun = Carrera::updateOrCreate(
            ['sigla' => 'FB'],
            [
                'sigla'           => 'FB',
                'nombre'          => 'FORMACION BASE CIENCIAS POLICIALES',
                'resolucion'      => 'RES-MIN-000/2026',
                'duracion'        => 2,
                'titulo'          => 'CERTIFICADO NOTAS',
                'es_tronco_comun' => true,
                'carrera_base_id' => null,
                'nivel_id'        => $nivelSuperior->id,
                'estado_id'       => $estadoVigente->id,
            ]
        );

        // Creamos su configuración de notas por defecto
        $this->crearConfiguracionParcialParaCarrera($troncoComun->id, $estadoVigente->id);

        // 2. Definimos las carreras que se desprenden
        $carrerasEjemplo = [
            [
                'sigla'           => 'TV',
                'nombre'          => 'TRANSITO Y VIALIDAD',
                'resolucion'      => 'RES-MIN-001/2024',
                'duracion'        => 6,
                'titulo'          => 'LICENCIATURA EN INGENIERIA DE TRANSITO Y VIALIDAD',
                'es_tronco_comun' => false,
                'carrera_base_id' => $troncoComun->id,
            ],
            [
                'sigla'           => 'IC',
                'nombre'          => 'INVESTIGACION CRIMINAL',
                'resolucion'      => 'RES-MIN-002/2024',
                'duracion'        => 6,
                'titulo'          => 'LICENCIATURA EN INVESTIGACION CRIMINAL',
                'es_tronco_comun' => false,
                'carrera_base_id' => $troncoComun->id,
            ],
            [
                'sigla'           => 'AP',
                'nombre'          => 'ADMINISTRACION POLICIAL',
                'resolucion'      => 'RES-MIN-003/2024',
                'duracion'        => 6,
                'titulo'          => 'LICENCIATURA EN ADMINISTRACION POLICIAL',
                'es_tronco_comun' => false,
                'carrera_base_id' => null,
            ],
            [
                'sigla'           => 'OS',
                'nombre'          => 'ORDEN Y SEGURIDAD',
                'resolucion'      => 'RES-MIN-004/2024',
                'duracion'        => 6,
                'titulo'          => 'LICENCIATURA EN ORDEN Y SEGURIDAD',
                'es_tronco_comun' => false,
                'carrera_base_id' => null,
            ],
        ];

        foreach ($carrerasEjemplo as $carreraData) {
            $carrera = Carrera::updateOrCreate(
                ['sigla' => $carreraData['sigla']],
                [
                    'sigla'           => $carreraData['sigla'],
                    'nombre'          => $carreraData['nombre'],
                    'resolucion'      => $carreraData['resolucion'],
                    'duracion'        => $carreraData['duracion'],
                    'titulo'          => $carreraData['titulo'],
                    'es_tronco_comun' => $carreraData['es_tronco_comun'],
                    'carrera_base_id' => $carreraData['carrera_base_id'],
                    'nivel_id'        => $nivelSuperior->id,
                    'estado_id'       => $estadoVigente->id,
                ]
            );

            // Creamos su configuración de notas asociada a esta carrera
            $this->crearConfiguracionParcialParaCarrera($carrera->id, $estadoVigente->id);
        }
    }

    /**
     * Método auxiliar para evitar duplicar código de configuración de notas
     */
    private function crearConfiguracionParcialParaCarrera(int $carreraId, int $estadoId): void
    {
        // Si ya existen configuraciones para esta carrera, las omitimos para evitar duplicados en seeders repetidos
        if (ConfiguracionParcialMeta::where('carrera_id', $carreraId)->exists()) {
            return;
        }

        // 1. Primer Parcial (30%)
        $p1 = ConfiguracionParcialMeta::create([
            'carrera_id'          => $carreraId,
            'nro_parcial'         => 1,
            'nombre'              => 'Primer Parcial',
            'ponderacion_parcial' => 30.00,
            'estado_id'           => $estadoId,
        ]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p1->id, 'tipo_componente' => 'examen', 'ponderacion_componente' => 75.00]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p1->id, 'tipo_componente' => 'trabajo_practico', 'ponderacion_componente' => 25.00]);

        // 2. Segundo Parcial (30%)
        $p2 = ConfiguracionParcialMeta::create([
            'carrera_id'          => $carreraId,
            'nro_parcial'         => 2,
            'nombre'              => 'Segundo Parcial',
            'ponderacion_parcial' => 30.00,
            'estado_id'           => $estadoId,
        ]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p2->id, 'tipo_componente' => 'examen', 'ponderacion_componente' => 75.00]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p2->id, 'tipo_componente' => 'trabajo_practico', 'ponderacion_componente' => 25.00]);

        // 3. Tercer Parcial (40%)
        $p3 = ConfiguracionParcialMeta::create([
            'carrera_id'          => $carreraId,
            'nro_parcial'         => 3,
            'nombre'              => 'Tercer Parcial',
            'ponderacion_parcial' => 40.00,
            'estado_id'           => $estadoId,
        ]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p3->id, 'tipo_componente' => 'examen', 'ponderacion_componente' => 75.00]);
        ConfiguracionComponenteMeta::create(['configuracion_parcial_id' => $p3->id, 'tipo_componente' => 'trabajo_practico', 'ponderacion_componente' => 25.00]);
    }
}
