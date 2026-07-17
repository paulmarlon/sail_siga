<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            // Contexto: Usuario (Control de acceso)
            [
                'nombre' => 'Activo',
                'slug' => 'activo',
                'contexto' => 'user',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#28a745' // Verde
            ],
            [
                'nombre' => 'Suspendido',
                'slug' => 'suspendido',
                'contexto' => 'user',
                'permite_login' => false,
                'permite_procesos_academicos' => false,
                'color_hex' => '#dc3545' // Rojo
            ],

            // Contexto: Académico (Control de procesos)
            [
                'nombre' => 'En Curso',
                'slug' => 'en-curso',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => true,
                'color_hex' => '#007bff' // Azul
            ],
            [
                'nombre' => 'Finalizado',
                'slug' => 'finalizado',
                'contexto' => 'academico',
                'permite_login' => true,
                'permite_procesos_academicos' => false,
                'color_hex' => '#6c757d' // Gris
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
