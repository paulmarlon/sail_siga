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
        Route::get('gestiones/papelera', [GestionController::class, 'papelera'])->name('gestiones.papelera');
        Route::post('gestiones/{id}/restaurar', [GestionController::class, 'restaurar'])->name('gestiones.restaurar');
        Route::resource('gestiones', GestionController::class);

        // Niveles
        Route::get('niveles/papelera', [NivelController::class, 'papelera'])->name('niveles.papelera');
        Route::post('niveles/{id}/restaurar', [NivelController::class, 'restaurar'])->name('niveles.restaurar');
        Route::resource('niveles', NivelController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

        // Configuración (Ruta especial)
        Route::get('configuracion/edit', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('configuracion/update', [ConfiguracionController::class, 'update'])->name('configuracion.update');

        // Personas
        Route::get('personas/papelera', [PersonaController::class, 'papelera'])->name('personas.papelera');
        Route::post('personas/{id}/restaurar', [PersonaController::class, 'restaurar'])->name('personas.restaurar');
        Route::get('personas/buscar-autocomplete', [PersonaController::class, 'buscarAutocomplete'])->name('personas.autocomplete');
        Route::resource('personas', PersonaController::class);
        // --- PERSONAL ---
        Route::get('personal/crear', [PersonalController::class, 'create'])->name('personal.create');
        Route::get('personal/papelera/{tipo?}', [PersonalController::class, 'trashed'])->name('personal.trashed');
        Route::post('personal/{id}/restaurar', [PersonalController::class, 'restore'])->name('personal.restore')->whereNumber('id');

        // Resource excluyendo create (porque ya lo manejamos arriba)
        Route::resource('personal', PersonalController::class)->except(['create']);

        // Turnos
        Route::get('turnos/papelera', [TurnoController::class, 'papelera'])->name('turnos.papelera');
        Route::post('turnos/{id}/restaurar', [TurnoController::class, 'restaurar'])->name('turnos.restaurar');
        Route::resource('turnos', TurnoController::class);

        // Paralelos
        Route::get('paralelos/papelera', [ParaleloController::class, 'papelera'])->name('paralelos.papelera');
        Route::post('paralelos/{id}/restaurar', [ParaleloController::class, 'restaurar'])->name('paralelos.restaurar');
        Route::resource('paralelos', ParaleloController::class);

        // Periodos
        Route::get('periodos/papelera', [PeriodoController::class, 'papelera'])->name('periodos.papelera');
        Route::post('periodos/{id}/restaurar', [PeriodoController::class, 'restaurar'])->name('periodos.restaurar');
        Route::resource('periodos', PeriodoController::class);

        // Materias
        Route::get('materias/papelera', [MateriaController::class, 'papelera'])->name('materias.papelera');
        Route::post('materias/{id}/restaurar', [MateriaController::class, 'restaurar'])->name('materias.restaurar');
        Route::resource('materias', MateriaController::class);

        // Grados
        Route::get('grados/papelera', [GradoController::class, 'papelera'])->name('grados.papelera');
        Route::post('grados/{id}/restaurar', [GradoController::class, 'restaurar'])->name('grados.restaurar');
        Route::resource('grados', GradoController::class);

        // Carreras (Bloque duplicado unificado)
        Route::get('carreras/papelera', [CarreraController::class, 'papelera'])->name('carreras.papelera');
        Route::post('carreras/{id}/restaurar', [CarreraController::class, 'restaurar'])->name('carreras.restaurar');
        Route::resource('carreras', CarreraController::class);

        // Pensums
        Route::get('pensums/{carrera_id?}', [PensumController::class, 'index'])->name('pensums.index');
        Route::post('pensums/update-grado', [PensumController::class, 'updateGrado'])->name('pensums.update-grado');
        Route::resource('pensums', PensumController::class)->except(['index']);

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
