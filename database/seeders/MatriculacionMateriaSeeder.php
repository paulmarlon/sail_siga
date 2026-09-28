<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\MatriculacionMateria;
use App\Services\CalificacionService;

class MatriculacionMateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ruta al archivo SQL externo
        $path = database_path('sql/matriculacion_materias.sql');

        if (File::exists($path)) {
            // 1. Ejecuta el contenido SQL directamente
            DB::unprepared(File::get($path));

            // 2. Inicializar las libretas de calificación para todas las matriculaciones importadas
            $calificacionService = new CalificacionService();

            MatriculacionMateria::chunk(100, function ($matriculaciones) use ($calificacionService) {
                foreach ($matriculaciones as $matriculacion) {
                    try {
                        $calificacionService->inicializarLibretaEstudiante($matriculacion);
                    } catch (\Exception $e) {
                        // Opcional: puedes registrar una advertencia si alguna matrícula tiene datos huérfanos
                        // Log::warning("No se pudo inicializar libreta para matrícula {$matriculacion->id}: " . $e->getMessage());
                    }
                }
            });
        } else {
            throw new \Exception("No se encontró el archivo SQL en la ruta: {$path}");
        }
    }
}
