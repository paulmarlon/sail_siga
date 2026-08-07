<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OfertaAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        // Ruta del archivo SQL para la oferta académica
        $path = database_path('sql/oferta_academicas.sql');

        if (File::exists($path)) {
            $sql = File::get($path);
            DB::unprepared($sql);
            $this->command->info('¡Datos de oferta académica importados exitosamente desde el archivo SQL!');
        } else {
            $this->command->error("No se encontró el archivo SQL en la ruta: {$path}");
        }
    }
}
