<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Persona;
use App\Models\Domicilio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->call([
            EstadoSeeder::class,
            GestionSeeder::class,
            PeriodoSeeder::class,
            TurnoSeeder::class,
            ParaleloSeeder::class,
            GradoSeeder::class,
            NivelSeeder::class,
        ]);
        DB::transaction(function () {
            // 1. Crear Roles y Permisos básicos del sistema
            $roleAdmin = Role::firstOrCreate(['name' => 'Administrador']);
            $roleDocente = Role::firstOrCreate(['name' => 'Docente']);

            // Crear algunos permisos esenciales de rutas de administración si los usas
            $permisos = [
                'admin.roles.index',
                'admin.roles.create',
                'admin.roles.store',
                'admin.roles.edit',
                'admin.roles.permisos',
                'admin.roles.update_permisos',
                'admin.roles.update',
                'admin.roles.destroy',
            ];

            foreach ($permisos as $permiso) {
                Permission::firstOrCreate(['name' => $permiso]);
            }

            // Asignar todos los permisos al rol de Administrador
            $roleAdmin->givePermissionTo(Permission::all());

            // 2. Crear el Domicilio necesario para la Persona
            $domicilio = Domicilio::create([
                'pais' => 'Bolivia',
                'departamento' => 'La Paz',
                'ciudad' => 'La Paz',
            ]);

            // 3. Crear la Persona
            $persona = Persona::create([
                'ci' => '12345678',
                'nombres' => 'Paul Marlon',
                'ap_paterno' => 'Quispe',
                'ap_materno' => 'Veizaga',
                'fecha_nacimiento' => '1979-07-19',
                'sexo' => 'M',
                'domicilio_id' => $domicilio->id
            ]);

            // 4. Crear el Usuario vinculado a la Persona
            $user = User::create([
                'persona_id' => $persona->id,
                'email' => 'paul@adhara.tech',
                'password' => Hash::make('7539518520'),
            ]);

            // 5. Asignar el rol de Administrador al usuario principal
            $user->assignRole($roleAdmin);
        });
        $this->call([
            MateriaSeeder::class,
            CarreraSeeder::class,
        ]);
    }
}
