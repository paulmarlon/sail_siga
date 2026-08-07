<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OfertaDocenteHistorialSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('sql/oferta_docente_historials.sql');

        if (File::exists($path)) {
            $sql = File::get($path);
            DB::unprepared($sql);
            $this->command->info('¡Historial de docentes importado exitosamente desde el archivo SQL!');
        } else {
            $this->command->error("No se encontró el archivo SQL en la ruta: {$path}");
        }
    }
}
