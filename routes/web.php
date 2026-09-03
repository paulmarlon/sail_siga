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
    MatriculacionMateriaController,
    ProgramacionExamenController,
    AsistenciaController,
    FolioExamenController,
    Admin\UserController,
};
use Illuminate\Support\Facades\Route;

// Ruta principal
Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de Perfil y Administración
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- RUTAS PARA EL CAMBIO OBLIGATORIO DE CONTRASEÑA (Libres de bloqueo) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('cambiar-password-obligatorio', [UserController::class, 'showChangePasswordForm'])->name('password.change.form');
        Route::post('cambiar-password-obligatorio', [UserController::class, 'updatePassword'])->name('password.change.update');
    });

    // --- GRUPO ADMINISTRATIVO PROTEGIDO (Con prevent-back-history y force.password) ---
    Route::prefix('admin')->name('admin.')->middleware(['prevent-back-history', 'force.password'])->group(function () {

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
        Route::get('estudiantes/papelera', [EstudianteController::class, 'papelera'])->name('estudiantes.papelera')->middleware('can:admin.estudiantes.index');
        Route::post('estudiantes/{id}/restaurar', [EstudianteController::class, 'restaurar'])->name('estudiantes.restaurar')->middleware('can:admin.estudiantes.edit');
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

        // --- RUTAS DE USUARIOS ---
        Route::post('usuarios/destroy-masivo', [UserController::class, 'destroyMasivo'])->name('usuarios.destroy-masivo');
        Route::post('usuarios/preparar-edicion-masiva', [UserController::class, 'prepararEdicionMasiva'])->name('usuarios.prepararEdicionMasiva');
        Route::get('usuarios/vista-edicion-masiva', [UserController::class, 'vistaEdicionMasiva'])->name('usuarios.vistaEdicionMasiva');
        Route::put('usuarios/update-masivo', [UserController::class, 'updateMasivo'])->name('usuarios.updateMasivo');
        Route::resource('usuarios', UserController::class);

        // --- GESTIÓN DE INSCRIPCIONES A CARRERA ---
        Route::get('inscripcion-carreras/papelera', [InscripcionCarreraController::class, 'papelera'])->name('inscripcion-carreras.papelera');
        Route::put('inscripcion-carreras/{inscripcion_carrera}/restaurar', [InscripcionCarreraController::class, 'restaurar'])->name('inscripcion-carreras.restaurar');
        Route::put('inscripcion-carreras/{inscripcionCarrera}/procesar-retiro', [InscripcionCarreraController::class, 'procesarRetiro'])->name('inscripcion-carreras.procesar-retiro');
        Route::get('inscripcion-carreras/{id}/edit-individual', [InscripcionCarreraController::class, 'editIndividual'])->name('inscripcion-carreras.edit-individual');
        Route::put('inscripcion-carreras/{id}/individual', [InscripcionCarreraController::class, 'updateIndividual'])->name('inscripcion-carreras.update-individual');
        Route::resource('inscripcion-carreras', InscripcionCarreraController::class);

        // --- GESTIÓN DE MATRICULACIÓN DE MATERIAS ---
        Route::get('matriculacion-materias/detalle/{estudianteId}/{periodoId}', [MatriculacionMateriaController::class, 'show'])->name('matriculacion-materias.show');
        Route::post('matriculacion-materias/store-single', [MatriculacionMateriaController::class, 'storeSingle'])->name('matriculacion-materias.store-single');
        Route::get('matriculacion-materias/grupo/actualizar', [MatriculacionMateriaController::class, 'editGroup'])->name('matriculacion-materias.edit-group');
        Route::put('matriculacion-materias/grupo/actualizar', [MatriculacionMateriaController::class, 'updateGroup'])->name('matriculacion-materias.update-group');
        Route::get('matriculacion-materias/papelera', [MatriculacionMateriaController::class, 'papelera'])->name('matriculacion-materias.papelera');
        Route::post('matriculacion-materias/{id}/restaurar', [MatriculacionMateriaController::class, 'restaurar'])->name('matriculacion-materias.restaurar');
        Route::delete('matriculacion-materias/{id}/fuerza-destruccion', [MatriculacionMateriaController::class, 'fuerzaDestruccion'])->name('matriculacion-materias.fuerza-destruccion');
        Route::put('matriculacion-materias/{matriculacionMateria}/procesar-retiro', [MatriculacionMateriaController::class, 'procesarRetiro'])->name('matriculacion-materias.procesar-retiro');
        Route::resource('matriculacion-materias', MatriculacionMateriaController::class)->except(['show']);

        // --- GESTIÓN DE PROGRAMACIÓN DE EXÁMENES ---
        Route::get('programacion-examenes/papelera', [ProgramacionExamenController::class, 'papelera'])->name('programacion-examenes.papelera')->middleware('can:admin.programacion-examenes.index');
        Route::post('programacion-examenes/restaurar-masivo', [ProgramacionExamenController::class, 'restaurarMasivo'])->name('programacion-examenes.restaurar-masivo')->middleware('can:admin.programacion-examenes.edit');
        Route::post('programacion-examenes/{id}/restaurar', [ProgramacionExamenController::class, 'restaurar'])->name('programacion-examenes.restaurar')->middleware('can:admin.programacion-examenes.edit');
        Route::get('programacion-examenes/edit-masivo', [ProgramacionExamenController::class, 'editMasivo'])->name('programacion-examenes.edit-masivo')->middleware('can:admin.programacion-examenes.edit');
        Route::put('programacion-examenes/update-masivo', [ProgramacionExamenController::class, 'updateMasivo'])->name('programacion-examenes.update-masivo')->middleware('can:admin.programacion-examenes.edit');
        Route::post('programacion-examenes/destroy-masivo', [ProgramacionExamenController::class, 'destroyMasivo'])->name('programacion-examenes.destroy-masivo')->middleware('can:admin.programacion-examenes.destroy');

        // 🚀 AÑADE ESTAS TRES LÍNEAS DE LOTE AQUÍ:
        Route::post('programacion-examenes/store-lote', [ProgramacionExamenController::class, 'storeLote'])->name('programacion-examenes.store-lote')->middleware('can:admin.programacion-examenes.edit');
        Route::put('programacion-examenes/update-lote', [ProgramacionExamenController::class, 'updateLote'])->name('programacion-examenes.update-lote')->middleware('can:admin.programacion-examenes.edit');
        Route::delete('programacion-examenes/destroy-lote', [ProgramacionExamenController::class, 'destroyLote'])->name('programacion-examenes.destroy-lote')->middleware('can:admin.programacion-examenes.destroy');

        Route::resource('programacion-examenes', ProgramacionExamenController::class)->parameters(['programacion-examenes' => 'programacion_examen'])->middleware('can:admin.programacion-examenes.index');
        Route::patch('programacion-examenes/{id}/sellar', [ProgramacionExamenController::class, 'sellarInstancia'])->name('programacion-examenes.sellar')->middleware('can:admin.programacion-examenes.edit');
        Route::patch('programacion-examenes/{id}/desbloquear', [ProgramacionExamenController::class, 'desbloquear'])->name('programacion-examenes.desbloquear')->middleware('can:admin.programacion-examenes.edit');
        Route::get('programacion-examenes/{id}/examenes-calificados', [ProgramacionExamenController::class, 'examenesCalificados'])->name('programacion-examenes.examenes-calificados')->middleware('can:admin.programacion-examenes.index');      // --- GESTIÓN DE ASISTENCIAS ---
        Route::get('asistencias/reporte-admin', [AsistenciaController::class, 'reporteAdmin'])->name('asistencias.reporte')->middleware('can:admin.asistencias.index');
        Route::get('asistencias/oferta-fecha', [AsistenciaController::class, 'porOfertaYFecha'])->name('asistencias.por-oferta-fecha')->middleware('can:admin.asistencias.index');
        Route::post('asistencias/masiva', [AsistenciaController::class, 'storeMasiva'])->name('asistencias.store-masiva')->middleware('can:admin.asistencias.edit');
        Route::get('asistencias/historial/{matriculacionId}', [AsistenciaController::class, 'historialPorMatriculacion'])->name('asistencias.historial')->middleware('can:admin.asistencias.index');
        Route::resource('asistencias', AsistenciaController::class)->middleware('can:admin.asistencias.index');

        // --- GESTIÓN DE FOLIOS DE EXAMEN ---
        // 1. RUTAS FIJAS Y ESPECIALES (PRIMERO SIEMPRE)
        Route::get('folio-examens/papelera', [FolioExamenController::class, 'papelera'])->name('folio-examens.papelera')->middleware('can:admin.folio-examens.index');
        Route::post('folio-examens/restaurar/{id}', [FolioExamenController::class, 'restaurar'])->name('folio-examens.restaurar')->middleware('can:admin.folio-examens.edit');
        Route::get('folio-examens/plantilla/{programacionId}', [FolioExamenController::class, 'plantillaFolios'])->name('folio-examens.plantilla')->middleware('can:admin.folio-examens.index');
        Route::post('folio-examens/generar-masivo/{programacionId}', [FolioExamenController::class, 'generarMasivo'])->name('folio-examens.generar-masivo')->middleware('can:admin.folio-examens.edit');
        Route::post('folio-examens/actualizar/{programacionId}', [FolioExamenController::class, 'actualizarFoliacion'])->name('folio-examens.actualizar')->middleware('can:admin.folio-examens.edit');

        // RUTA AJAX (Bien ubicada antes del resource)
        Route::post('folio-examens/guardar-folio-ajax', [FolioExamenController::class, 'guardarFolioAjax'])->name('folio-examens.guardar-folio-ajax')->middleware('can:admin.folio-examens.edit');

        // 2. RESOURCE (AL FINAL PARA QUE NO INTERCEPTE LAS OTRAS RUTAS)
        Route::resource('folio-examens', FolioExamenController::class)->parameters(['folio-examens' => 'folioExamen'])->middleware('can:admin.folio-examens.index');
    }); // Cierre del grupo de administración
}); // Cierre del grupo auth general

// ==========================================
// RUTAS DEL PORTAL DOCENTE (FUERA DEL ADMIN)
// ==========================================
Route::middleware(['auth', 'force.password', 'can:docente.panel'])->prefix('docente')->name('docente.')->group(function () {

    // 1. Selector de materias
    Route::get('seleccionar-materia', [FolioExamenController::class, 'docenteSeleccionarMateria'])
        ->name('seleccionar-materia');

    // 2. Acción POST para fijar la materia en sesión
    Route::post('fijar-materia', [FolioExamenController::class, 'docenteFijarMateria'])
        ->name('fijar-materia');

    // 3. Estación de Foliado del Docente
    Route::get('foliacion', [FolioExamenController::class, 'docenteEstacionFoliado'])
        ->name('foliacion');

    // 4. Endpoints de guardado reutilizados para el docente
    Route::post('foliacion/generar-masivo/{programacionId}', [FolioExamenController::class, 'generarMasivo'])
        ->name('foliacion.generar-masivo');

    Route::post('foliacion/actualizar/{programacionId}', [FolioExamenController::class, 'actualizarFoliacion'])
        ->name('foliacion.actualizar');

    Route::post('foliacion/guardar-folio-ajax', [FolioExamenController::class, 'guardarFolioAjax'])
        ->name('foliacion.ajax');

    // 5. Vista de Registro de Notas del Docente
    Route::get('notas', [FolioExamenController::class, 'docenteRegistroNotas'])
        ->name('notas');

    // 6. Acción POST para guardar las notas masivamente
    Route::post('notas/guardar', [FolioExamenController::class, 'docenteGuardarNotas'])
        ->name('notas.guardar');
    Route::get('/materias/{id}/calificados', [FolioExamenController::class, 'verExamenesCalificadosDocente'])->name('examenes.calificados');
});

require __DIR__ . '/auth.php';
