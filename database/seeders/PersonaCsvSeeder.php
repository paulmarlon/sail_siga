<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domicilio;
use App\Models\Persona;
use App\Models\Personal;
use App\Models\Estado;

class PersonaCsvSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener un estado activo de forma segura
        // Buscamos si existe alguno con nombre similar a Activo o Vigente en el contexto laboral,
        // o si no, agarramos el primer estado que exista en la tabla.
        $estadoActivo = Estado::where('contexto', 'laboral')
            ->where(function ($query) {
                $query->where('nombre', 'LIKE', '%Activo%')
                    ->orWhere('nombre', 'LIKE', '%Vigente%')
                    ->orWhere('nombre', 'LIKE', '%Habilitado%');
            })->first()
            ?? Estado::where('contexto', 'laboral')->first()
            ?? Estado::first();

        $filePath = database_path('seeders/csv/personas.csv');

        if (!file_exists($filePath)) {
            $this->command->error("El archivo CSV no existe en la ruta: {$filePath}");
            return;
        }

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ',');

            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Orden de columnas según tu estructura:
                // 0: ci, 1: nombres, 2: ap_paterno, 3: ap_materno, 4: fecha_nacimiento, 5: sexo, 6: tipo_personal, 7: profesion
                $ci              = $row[0] ?? null;
                $nombres         = $row[1] ?? null;
                $apPaterno       = $row[2] ?? null;
                $apMaterno       = $row[3] ?? null;
                // Si la fecha viene vacía en el CSV, ponemos la provisional para cumplir con la BD
                $fechaNacimiento = (!empty($row[4])) ? $row[4] : '2000-01-01';
                $sexo            = $row[5] ?? 'M';
                $tipoPersonal    = $row[6] ?? 'docente'; // 'docente' o 'administrativo'
                $profesion       = $row[7] ?? null;      // Opcional / nullable en tu migración

                if (!$ci || !$nombres) {
                    continue;
                }

                // 2. Crear Domicilio genérico por registro
                $domicilio = Domicilio::create([
                    'pais' => 'Bolivia',
                    'departamento' => 'NN',
                    'ciudad' => 'NN',
                ]);

                // 3. Crear la Persona (Datos biográficos)
                $persona = Persona::create([
                    'ci' => $ci,
                    'nombres' => $nombres,
                    'ap_paterno' => $apPaterno,
                    'ap_materno' => $apMaterno,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'sexo' => $sexo,
                    'domicilio_id' => $domicilio->id
                ]);

                // 4. Crear el registro en la tabla Personal (Vinculado a la persona, sin usuario ya que fue desacoplado)
                Personal::create([
                    'persona_id' => $persona->id,
                    'tipo' => $tipoPersonal,
                    'profesion' => $profesion,
                    'estado_id' => $estadoActivo ? $estadoActivo->id : null,
                ]);
            }

            fclose($handle);
            $this->command->info("¡Personas y registros de personal importados masivamente con éxito desde el CSV!");
        }
    }
}
