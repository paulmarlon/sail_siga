<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
            // Ejecuta el contenido SQL directamente asegurando compatibilidad con PostgreSQL
            DB::unprepared(File::get($path));
        } else {
            throw new \Exception("No se encontró el archivo SQL en la ruta: {$path}");
        }
    }
}
