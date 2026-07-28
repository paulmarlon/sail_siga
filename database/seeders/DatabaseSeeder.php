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
                // Roles y Permisos
                'admin.roles.index',
                'admin.roles.create',
                'admin.roles.store',
                'admin.roles.edit',
                'admin.roles.permisos',
                'admin.roles.update_permisos',
                'admin.roles.update',
                'admin.roles.destroy',

                // Carreras
                'admin.carreras.index',
                'admin.carreras.create',
                'admin.carreras.store',
                'admin.carreras.show',
                'admin.carreras.edit',
                'admin.carreras.update',
                'admin.carreras.destroy',

                // Gestiones
                'admin.gestiones.index',
                'admin.gestiones.create',
                'admin.gestiones.store',
                'admin.gestiones.show',
                'admin.gestiones.edit',
                'admin.gestiones.update',
                'admin.gestiones.destroy',

                // Niveles
                'admin.niveles.index',
                'admin.niveles.store',
                'admin.niveles.edit',
                'admin.niveles.update',
                'admin.niveles.destroy',

                // Configuración
                'admin.configuracion.edit',

                // Personas
                'admin.personas.index',
                'admin.personas.create',
                'admin.personas.store',
                'admin.personas.show',
                'admin.personas.edit',
                'admin.personas.update',
                'admin.personas.destroy',

                // Personal
                'admin.personal.index',
                'admin.personal.create',
                'admin.personal.store',
                'admin.personal.show',
                'admin.personal.edit',
                'admin.personal.update',
                'admin.personal.destroy',

                // Turnos
                'admin.turnos.index',
                'admin.turnos.create',
                'admin.turnos.store',
                'admin.turnos.show',
                'admin.turnos.edit',
                'admin.turnos.update',
                'admin.turnos.destroy',

                // Paralelos
                'admin.paralelos.index',
                'admin.paralelos.create',
                'admin.paralelos.store',
                'admin.paralelos.show',
                'admin.paralelos.edit',
                'admin.paralelos.update',
                'admin.paralelos.destroy',

                // Periodos
                'admin.periodos.index',
                'admin.periodos.create',
                'admin.periodos.store',
                'admin.periodos.show',
                'admin.periodos.edit',
                'admin.periodos.update',
                'admin.periodos.destroy',

                // Materias
                'admin.materias.index',
                'admin.materias.create',
                'admin.materias.store',
                'admin.materias.show',
                'admin.materias.edit',
                'admin.materias.update',
                'admin.materias.destroy',

                // Grados
                'admin.grados.index',
                'admin.grados.create',
                'admin.grados.store',
                'admin.grados.show',
                'admin.grados.edit',
                'admin.grados.update',
                'admin.grados.destroy',

                // Pensums
                'admin.pensums.index',
                'admin.pensums.create',
                'admin.pensums.store',
                'admin.pensums.show',
                'admin.pensums.edit',
                'admin.pensums.update',
                'admin.pensums.destroy',
                // Oferta Académica
                'admin.oferta-academica.index',
                'admin.oferta-academica.create',
                'admin.oferta-academica.store',
                'admin.oferta-academica.show',
                'admin.oferta-academica.edit',
                'admin.oferta-academica.update',
                'admin.oferta-academica.destroy',
                'admin.oferta-academica.papelera',
                'admin.oferta-academica.restaurar',
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
        $this->call([
            PensumSeeder::class,
        ]);
    }
}
