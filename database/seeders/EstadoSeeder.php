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
            // Nuevos Estados de Retiro Académico
            [
                'nombre' => 'Retiro Voluntario',
                'slug' => 'retiro-voluntario',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#ffc107'
            ],
            [
                'nombre' => 'Baja por Insuficiencia',
                'slug' => 'baja-insuficiencia',
                'contexto' => 'academico',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#dc3545'
            ],
            [
                'nombre' => 'Baja Disciplinaria',
                'slug' => 'baja-disciplinaria',
                'contexto' => 'academico',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#212529'
            ],
            [
                'nombre' => 'Baja por Salud',
                'slug' => 'baja-salud',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#17a2b8'
            ],

            // ==========================================
            // Contexto: Laboral / Personal
            // ==========================================
            [
                'nombre' => 'Activo',
                'slug' => 'activo-laboral',
                'contexto' => 'laboral',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#28a745'
            ],
            [
                'nombre' => 'Licencia',
                'slug' => 'licencia-laboral',
                'contexto' => 'laboral',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#ffc107'
            ],
            [
                'nombre' => 'Suspendido',
                'slug' => 'suspendido-laboral',
                'contexto' => 'laboral',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#dc3545'
            ],
            [
                'nombre' => 'Cesado / Retirado',
                'slug' => 'cesado-laboral',
                'contexto' => 'laboral',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#6c757d'
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
