<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PensumSeeder extends Seeder
{
    public function run(): void
    {
        // Ruta donde tengas guardado tu archivo .sql
        // Por ejemplo: database/sql/tus_datos.sql
        $path = database_path('sql/pensum.sql');

        if (File::exists($path)) {
            $sql = File::get($path);
            DB::unprepared($sql);
            $this->command->info('¡Datos del archivo SQL importados con éxito!');
        } else {
            $this->command->error("No se encontró el archivo SQL en la ruta: {$path}");
        }
    }
}
