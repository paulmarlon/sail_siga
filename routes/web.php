<?php

use App\Http\Controllers\{
    ProfileController,
    GestionController,
    NivelController,
    ConfiguracionController,
    PersonaController,
    MateriaController,
    TurnoController,
    ParaleloController,
    PeriodoController,
    GradoController,
    CarreraController,
    PensumController,
    RoleController,
    PersonalController
};
use Illuminate\Support\Facades\Route;

// Ruta principal
Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- GRUPO ADMINISTRATIVO ---
    Route::prefix('admin')->name('admin.')->middleware(['prevent-back-history'])->group(function () {

        // Gestiones
        Route::get('gestiones/papelera', [GestionController::class, 'papelera'])->name('gestiones.papelera')->middleware('can:admin.gestiones.index');
        Route::post('gestiones/{id}/restaurar', [GestionController::class, 'restaurar'])->name('gestiones.restaurar')->middleware('can:admin.gestiones.edit');
        Route::resource('gestiones', GestionController::class)->parameters(['gestiones' => 'gestion'])->middleware('can:admin.gestiones.index');

        // Niveles
        Route::get('niveles/papelera', [NivelController::class, 'papelera'])->name('niveles.papelera')->middleware('can:admin.niveles.index');
        Route::post('niveles/{id}/restaurar', [NivelController::class, 'restaurar'])->name('niveles.restaurar')->middleware('can:admin.niveles.edit');
        Route::resource('niveles', NivelController::class)
            ->parameters(['nivels' => 'nivel']) // <--- ¡Agrégalo aquí!
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->middleware('can:admin.niveles.index');

        // Configuración
        Route::get('configuracion/edit', [ConfiguracionController::class, 'edit'])->name('configuracion.edit')->middleware('can:admin.configuracion.edit');
        Route::put('configuracion/update', [ConfiguracionController::class, 'update'])->name('configuracion.update')->middleware('can:admin.configuracion.edit');

        // Personas
        Route::get('personas/papelera', [PersonaController::class, 'papelera'])->name('personas.papelera')->middleware('can:admin.personas.index');
        Route::post('personas/{id}/restaurar', [PersonaController::class, 'restaurar'])->name('personas.restaurar')->middleware('can:admin.personas.edit');
        Route::get('personas/buscar-autocomplete', [PersonaController::class, 'buscarAutocomplete'])->name('personas.autocomplete')->middleware('can:admin.personas.index');
        Route::resource('personas', PersonaController::class)->middleware('can:admin.personas.index');

        // Personal
        Route::get('personal/crear', [PersonalController::class, 'create'])->name('personal.create')->middleware('can:admin.personal.create');
        Route::get('personal/papelera/{tipo?}', [PersonalController::class, 'trashed'])->name('personal.trashed')->middleware('can:admin.personal.index');
        Route::post('personal/{id}/restaurar', [PersonalController::class, 'restore'])->name('personal.restore')->whereNumber('id')->middleware('can:admin.personal.edit');
        Route::resource('personal', PersonalController::class)->except(['create'])->middleware('can:admin.personal.index');

        // Turnos
        Route::get('turnos/papelera', [TurnoController::class, 'papelera'])->name('turnos.papelera')->middleware('can:admin.turnos.index');
        Route::post('turnos/{id}/restaurar', [TurnoController::class, 'restaurar'])->name('turnos.restaurar')->middleware('can:admin.turnos.edit');
        Route::resource('turnos', TurnoController::class)->middleware('can:admin.turnos.index');

        // Paralelos
        Route::get('paralelos/papelera', [ParaleloController::class, 'papelera'])->name('paralelos.papelera')->middleware('can:admin.paralelos.index');
        Route::post('paralelos/{id}/restaurar', [ParaleloController::class, 'restaurar'])->name('paralelos.restaurar')->middleware('can:admin.paralelos.edit');
        Route::resource('paralelos', ParaleloController::class)->middleware('can:admin.paralelos.index');

        // Periodos
        Route::get('periodos/papelera', [PeriodoController::class, 'papelera'])->name('periodos.papelera')->middleware('can:admin.periodos.index');
        Route::post('periodos/{id}/restaurar', [PeriodoController::class, 'restaurar'])->name('periodos.restaurar')->middleware('can:admin.periodos.edit');
        Route::resource('periodos', PeriodoController::class)->middleware('can:admin.periodos.index');

        // Materias
        Route::get('materias/papelera', [MateriaController::class, 'papelera'])->name('materias.papelera')->middleware('can:admin.materias.index');
        Route::post('materias/{id}/restaurar', [MateriaController::class, 'restaurar'])->name('materias.restaurar')->middleware('can:admin.materias.edit');
        Route::resource('materias', MateriaController::class)->middleware('can:admin.materias.index');

        // Grados
        Route::get('grados/papelera', [GradoController::class, 'papelera'])->name('grados.papelera')->middleware('can:admin.grados.index');
        Route::post('grados/{id}/restaurar', [GradoController::class, 'restaurar'])->name('grados.restaurar')->middleware('can:admin.grados.edit');
        Route::resource('grados', GradoController::class)->middleware('can:admin.grados.index');

        // Carreras
        Route::get('carreras/papelera', [CarreraController::class, 'papelera'])->name('carreras.papelera')->middleware('can:admin.carreras.index');
        Route::post('carreras/{id}/restaurar', [CarreraController::class, 'restaurar'])->name('carreras.restaurar')->middleware('can:admin.carreras.edit');
        Route::resource('carreras', CarreraController::class)->middleware('can:admin.carreras.index');

        // 1. Rutas específicas de la papelera y restauración (SIEMPRE antes del resource)
        Route::get('pensums/{carrera_id}/papelera', [PensumController::class, 'papelera'])->name('pensums.papelera');
        Route::post('pensums/{id}/restaurar', [PensumController::class, 'restaurar'])->name('pensums.restaurar');

        // 2. Ruta GET estática explícita (soluciona el error 405 del conflicto con el POST del resource)
        Route::get('pensums', [PensumController::class, 'index'])->name('pensums.index')->middleware('can:admin.pensums.index');

        // 3. (Opcional) Si en algún lugar accedes entrando directamente a /admin/pensums/1
        Route::get('pensums/carrera/{carrera_id}', [PensumController::class, 'index'])->whereNumber('carrera_id')->name('pensums.carrera')->middleware('can:admin.pensums.index');

        // 4. Rutas de actualización y resource
        Route::post('pensums/update-grado', [PensumController::class, 'updateGrado'])->name('pensums.update-grado')->middleware('can:admin.pensums.edit');
        Route::resource('pensums', PensumController::class)->except(['index'])->middleware('can:admin.pensums.index');

        // --- GESTIÓN DE ROLES Y PERMISOS ---
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:admin.roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:admin.roles.create');
        Route::post('roles/create', [RoleController::class, 'store'])->name('roles.store')->middleware('can:admin.roles.store');
        Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:admin.roles.edit');
        Route::get('roles/{id}/permisos', [RoleController::class, 'permisos'])->name('roles.permisos')->middleware('can:admin.roles.permisos');
        Route::post('roles/{id}', [RoleController::class, 'update_permisos'])->name('roles.update_permisos')->middleware('can:admin.roles.update_permisos');
        Route::put('roles/{id}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:admin.roles.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:admin.roles.destroy');
    });
});

require __DIR__ . '/auth.php';
