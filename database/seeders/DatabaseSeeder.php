<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Persona;
use App\Models\Domicilio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EstadoSeeder::class,
        ]);
        DB::transaction(function () {
            // 1. Crear el Domicilio necesario para la Persona
            $domicilio = Domicilio::create([
                'pais' => 'Bolivia',
                'departamento' => 'La Paz',
                'ciudad' => 'La Paz',
            ]);

            // 2. Crear la Persona (los datos reales de Paul)
            $persona = Persona::create([
                'ci' => '12345678',
                'nombres' => 'Paul Marlon',
                'ap_paterno' => 'Quispe',
                'ap_materno' => 'Veizaga',
                'fecha_nacimiento' => '1979-07-19',
                'sexo' => 'M',
                'domicilio_id' => $domicilio->id
            ]);

            // 3. Crear el Usuario vinculado a la Persona
            User::create([
                'persona_id' => $persona->id, // El ID de la persona recién creada
                'email' => 'paul@adhara.tech',
                'password' => Hash::make('7539518520'),
            ]);
        });
    }
}
