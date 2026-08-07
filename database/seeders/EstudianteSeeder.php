<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/csv/estudiantes.csv');

        if (!file_exists($csvFile)) {
            return;
        }

        $fileHandle = fopen($csvFile, 'r');

        // Saltar cabecera
        fgetcsv($fileHandle);

        DB::transaction(function () use ($fileHandle) {
            while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
                if (empty($row[0])) continue;

                // 1. Crear Persona
                $persona = Persona::create([
                    'nombres' => $row[0],
                    'ap_paterno' => $row[1],
                    'ap_materno' => $row[2],
                    'ci' => $row[3],
                    'fecha_nacimiento' => !empty($row[4]) ? date('Y-m-d', strtotime($row[4])) : null,
                    'sexo' => $row[5],
                    'celular' => $row[6],
                    'email_personal' => $row[7],
                ]);

                // 2. Crear Estudiante con los campos exactos de tu migración
                Estudiante::create([
                    'persona_id' => $persona->id,
                    'registro_universitario' => $row[8],
                    'estado_id' => $row[9] ?? 1, // Por defecto 1 o el ID correspondiente en tu tabla estados
                ]);
            }
        });

        fclose($fileHandle);
    }
}
