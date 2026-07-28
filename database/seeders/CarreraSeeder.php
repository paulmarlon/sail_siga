<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Estado;
use App\Models\Nivel;
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

        // Buscamos un nivel por defecto (asumiendo que ya tienes niveles creados)
        $nivelSuperior = Nivel::first();

        if (!$estadoVigente || !$nivelSuperior) {
            return; // Evita errores si faltan los seeders previos de estados o niveles
        }

        // 1. Creamos primero el Tronco Común con todos sus campos requeridos (incluyendo sigla)
        $troncoComun = Carrera::updateOrCreate(
            ['sigla' => 'BAS-POL'],
            [
                'sigla'           => 'FB',
                'nombre'          => 'FORMACION BASE CIENCIAS POLICIALES',
                'resolucion'      => 'RES-MIN-000/2026',
                'duracion'        => 2, // Semestres iniciales compartidos
                'titulo'          => 'CERTIFICADO NOTAS',
                'es_tronco_comun' => true,
                'carrera_base_id' => null,
                'nivel_id'        => $nivelSuperior->id,
                'estado_id'       => $estadoVigente->id,
            ]
        );

        // 2. Definimos las carreras que se desprenden a partir del 3er semestre
        $carrerasEjemplo = [
            [
                'sigla'           => 'TV',
                'nombre'          => 'TRANSITO Y VIALIDAD',
                'resolucion'      => 'RES-MIN-001/2024',
                'duracion'        => 6, // Semestres totales
                'titulo'          => 'LICENCIATURA EN INGENIERIA DE TRANSITO Y VIALIDAD',
                'es_tronco_comun' => false,
                'carrera_base_id' => $troncoComun->id, // Enlazado al tronco común
            ],
            [
                'sigla'           => 'IC',
                'nombre'          => 'INVESTIGACION CRIMINAL',
                'resolucion'      => 'RES-MIN-002/2024',
                'duracion'        => 6,
                'titulo'          => 'LICENCIATURA EN INVESTIGACION CRIMINAL',
                'es_tronco_comun' => false,
                'carrera_base_id' => $troncoComun->id, // Enlazado al tronco común
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
            Carrera::updateOrCreate(
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
        }
    }
}
