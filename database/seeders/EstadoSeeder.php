<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            // ==========================================
            // Contexto: Académico
            // ==========================================
            [
                'nombre' => 'Vigente',
                'slug' => 'vigente',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#17a2b8'
            ],
            [
                'nombre' => 'En Curso',
                'slug' => 'en-curso',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#007bff'
            ],
            [
                'nombre' => 'Finalizado',
                'slug' => 'finalizado',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#6c757d'
            ],
            [
                'nombre' => 'Descontinuada',
                'slug' => 'descontinuada',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#343a40'
            ],

            // ==========================================
            // Contexto: Laboral / Personal (Nuevos)
            // ==========================================
            [
                'nombre' => 'Activo',
                'slug' => 'activo-laboral',
                'contexto' => 'laboral',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#28a745' // Verde (Trabajando normalmente)
            ],
            [
                'nombre' => 'Licencia',
                'slug' => 'licencia-laboral',
                'contexto' => 'laboral',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#ffc107' // Amarillo (Permiso temporal)
            ],
            [
                'nombre' => 'Suspendido',
                'slug' => 'suspendido-laboral',
                'contexto' => 'laboral',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#dc3545' // Rojo (Sin acceso temporal)
            ],
            [
                'nombre' => 'Cesado / Retirado',
                'slug' => 'cesado-laboral',
                'contexto' => 'laboral',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#6c757d' // Gris (Ya no labora)
            ],
        ];

        foreach ($estados as $estado) {
            Estado::updateOrCreate(
                ['slug' => $estado['slug'], 'contexto' => $estado['contexto']],
                $estado
            );
        }
    }
}
