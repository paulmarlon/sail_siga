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
    PersonalController,
    OfertaAcademicaController,
    OfertaDocenteHistorialController,
    EstudianteController,
    InscripcionCarreraController,
    MatriculacionMateriaController
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
            ->parameters(['nivels' => 'nivel'])
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

        // Oferta Académica
        Route::get('oferta-academica/papelera', [OfertaAcademicaController::class, 'papelera'])->name('oferta-academica.papelera')->middleware('can:admin.oferta-academica.index');
        Route::post('oferta-academica/{id}/restaurar', [OfertaAcademicaController::class, 'restaurar'])->name('oferta-academica.restaurar')->middleware('can:admin.oferta-academica.edit');
        Route::resource('oferta-academica', OfertaAcademicaController::class)
            ->parameters(['oferta-academica' => 'oferta_academica'])
            ->middleware('can:admin.oferta-academica.index');

        // --- RUTAS DE HISTORIAL DOCENTE POR OFERTA ACADÉMICA ---
        Route::get('oferta-academica/{oferta}/docentes', [OfertaDocenteHistorialController::class, 'show'])->name('oferta.docentes.show')->middleware('can:admin.oferta-academica.index');
        Route::post('oferta-academica/{oferta}/docentes', [OfertaDocenteHistorialController::class, 'store'])->name('oferta.docentes.store')->middleware('can:admin.oferta-academica.edit');
        Route::put('oferta-docente-historial/{id}/concluir', [OfertaDocenteHistorialController::class, 'concluir'])->name('oferta.docentes.concluir')->middleware('can:admin.oferta-academica.edit');

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

        // Pensums
        Route::get('pensums/{carrera_id}/papelera', [PensumController::class, 'papelera'])->name('pensums.papelera');
        Route::post('pensums/{id}/restaurar', [PensumController::class, 'restaurar'])->name('pensums.restaurar');
        Route::get('pensums', [PensumController::class, 'index'])->name('pensums.index')->middleware('can:admin.pensums.index');
        Route::get('pensums/carrera/{carrera_id}', [PensumController::class, 'index'])->whereNumber('carrera_id')->name('pensums.carrera')->middleware('can:admin.pensums.index');
        Route::post('pensums/update-grado', [PensumController::class, 'updateGrado'])->name('pensums.update-grado')->middleware('can:admin.pensums.edit');
        Route::resource('pensums', PensumController::class)->except(['index'])->middleware('can:admin.pensums.index');

        // --- GESTIÓN DE ESTUDIANTES ---
        // 1. Rutas estáticas y personalizadas PRIMERO
        Route::get('estudiantes/papelera', [EstudianteController::class, 'papelera'])->name('estudiantes.papelera')->middleware('can:admin.estudiantes.index');
        Route::post('estudiantes/{id}/restaurar', [EstudianteController::class, 'restaurar'])->name('estudiantes.restaurar')->middleware('can:admin.estudiantes.edit');

        // 2. Route::resource DESPUÉS
        Route::resource('estudiantes', EstudianteController::class)->middleware('can:admin.estudiantes.index');

        // --- GESTIÓN DE ROLES Y PERMISOS ---
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:admin.roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:admin.roles.create');
        Route::post('roles/create', [RoleController::class, 'store'])->name('roles.store')->middleware('can:admin.roles.store');
        Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:admin.roles.edit');
        Route::get('roles/{id}/permisos', [RoleController::class, 'permisos'])->name('roles.permisos')->middleware('can:admin.roles.permisos');
        Route::post('roles/{id}', [RoleController::class, 'update_permisos'])->name('roles.update_permisos')->middleware('can:admin.roles.update_permisos');
        Route::put('roles/{id}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:admin.roles.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:admin.roles.destroy');
        // --- GESTIÓN DE INSCRIPCIONES A CARRERA ---

        // --- GESTIÓN DE INSCRIPCIONES A CARRERA ---

        // ==========================================
        // RUTAS PERSONALIZADAS (ANTERIORES AL RESOURCE)
        // ==========================================

        // 1. Papelera (No requiere ID)
        // ==========================================
        // RUTAS PERSONALIZADAS DE INSCRIPCIÓN CARRERAS
        // ==========================================

        // 1. Papelera
        Route::get('inscripcion-carreras/papelera', [InscripcionCarreraController::class, 'papelera'])
            ->name('inscripcion-carreras.papelera');

        // 2. Restaurar (Usando el parámetro exacto 'inscripcion_carrera')
        Route::put('inscripcion-carreras/{inscripcion_carrera}/restaurar', [InscripcionCarreraController::class, 'restaurar'])
            ->name('inscripcion-carreras.restaurar');

        // 3. Procesar Retiro
        Route::put('inscripcion-carreras/{inscripcionCarrera}/procesar-retiro', [InscripcionCarreraController::class, 'procesarRetiro'])
            ->name('inscripcion-carreras.procesar-retiro');

        // ==========================================
        // RECURSO PRINCIPAL
        // ==========================================
        Route::resource('inscripcion-carreras', InscripcionCarreraController::class);


        // ==========================================
        // RECURSO PRINCIPAL DE LARAVEL
        // ==========================================
        Route::resource('inscripcion-carreras', InscripcionCarreraController::class);
        // 1. Rutas personalizadas (Papelera y Restauración por SoftDeletes) ANTES del Resource
        Route::get('matriculacion-materias/papelera', [MatriculacionMateriaController::class, 'papelera'])
            ->name('matriculacion-materias.papelera');

        Route::post('matriculacion-materias/{id}/restaurar', [MatriculacionMateriaController::class, 'restaurar'])
            ->name('matriculacion-materias.restaurar');

        // Ruta opcional por si requieres procesar un retiro o baja específica de materia vía PUT
        Route::put('matriculacion-materias/{matriculacionMateria}/procesar-retiro', [MatriculacionMateriaController::class, 'procesarRetiro'])
            ->name('matriculacion-materias.procesar-retiro');

        // 2. El Resource DESPUÉS
        Route::resource('matriculacion-materias', MatriculacionMateriaController::class);
    });
});

require __DIR__ . '/auth.php';
