<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionCarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('sql/inscripciones_carreras.sql');

        if (file_exists($path)) {
            DB::unprepared(file_get_contents($path));
            $this->command->info('¡Inscripciones migradas correctamente desde el archivo SQL!');
        } else {
            $this->command->error('No se encontró el archivo SQL en la ruta especificada.');
        }
    }
}
