<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FolioExamen;
use App\Models\ProgramacionExamen;
use App\Models\Periodo;
use App\Models\Carrera;
use App\Models\Grado;
use App\Models\Turno;
use App\Models\Paralelo;
use App\Models\MatriculacionMateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FolioExamenController extends Controller
{
    /**
     * Muestra el listado general optimizado con filtros y paginación (Estilo 3 columnas).
     */
    public function index(Request $request)
    {
        $periodoId = $request->get('periodo_id');
        $carreraId = $request->get('carrera_id');
        $gradoId = $request->get('grado_id');
        $turnoId = $request->get('turno_id');
        $paraleloId = $request->get('paralelo_id');
        $estadoFiltro = $request->get('estado_folio');
        $busqueda = $request->get('busqueda');

        $query = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.carrera',
            'ofertaAcademica.pensum.grado',
            'ofertaAcademica.turno',
            'ofertaAcademica.paralelo',
            'ofertaAcademica.periodo',
            'foliosExamen'
        ]);

        // Filtrar por Oferta Académica / Relaciones
        $query->whereHas('ofertaAcademica', function ($q) use ($periodoId, $turnoId, $paraleloId, $carreraId, $gradoId, $busqueda) {
            if ($periodoId) $q->where('periodo_id', $periodoId);
            if ($turnoId) $q->where('turno_id', $turnoId);
            if ($paraleloId) $q->where('paralelo_id', $paraleloId);

            if ($carreraId || $gradoId) {
                $q->whereHas('pensum', function ($p) use ($carreraId, $gradoId) {
                    if ($carreraId) $p->where('carrera_id', $carreraId);
                    if ($gradoId) $p->where('grado_id', $gradoId);
                });
            }

            if ($busqueda) {
                $q->whereHas('pensum.materia', function ($m) use ($busqueda) {
                    $m->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('sigla', 'like', "%{$busqueda}%");
                });
            }
        });

        if ($estadoFiltro) {
            $query->whereHas('foliosExamen', function ($f) use ($estadoFiltro) {
                $f->where('estado_folio', $estadoFiltro);
            });
        }

        $listaProgramaciones = $query->paginate(15)->withQueryString();

        $periodos  = Periodo::all();
        $carreras  = Carrera::all();
        $grados    = Grado::all();
        $turnos    = Turno::all();
        $paralelos = Paralelo::all();

        return view('admin.folio_examens.index', compact(
            'listaProgramaciones',
            'periodos',
            'carreras',
            'grados',
            'turnos',
            'paralelos'
        ));
    }

    /**
     * Plantilla de Folios por Estudiante: Redirige al hacer clic en una programación específica
     * para asignar o editar los códigos de foliación numérica y notas.
     */
    public function plantillaFolios(int $id)
    {
        $programacion = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.carrera',
            'ofertaAcademica.pensum.grado',
            'ofertaAcademica.turno',
            'ofertaAcademica.paralelo',
            'responsable.persona',
            'foliosExamen.estudiante.persona' // Trae únicamente los folios que ya fueron guardados
        ])->findOrFail($id);

        // Estudiantes matriculados en este paralelo específico
        $estudiantesParalelo = MatriculacionMateria::where('oferta_id', $programacion->oferta_id)
            ->with('estudiante.persona')
            ->get();

        // Total de filas dinámico basado en la cantidad real de estudiantes del paralelo
        $totalCasillas = $estudiantesParalelo->count();

        return view('admin.folio_examens.plantilla', compact('programacion', 'estudiantesParalelo', 'totalCasillas'));
    }

    public function guardarFolioAjax(Request $request)
    {
        logger('AJAX Solicitud recibida:', $request->all());

        $request->validate([
            'programacion_id' => 'required|exists:programacion_examenes,id',
            'registro'        => 'required|string',
            'folio_id'        => 'required|exists:folio_examens,id',
        ]);

        try {
            $estudianteEncontrado = DB::transaction(function () use ($request) {
                $programacion = ProgramacionExamen::with('ofertaAcademica')->findOrFail($request->programacion_id);

                $inputRu = trim($request->registro);
                logger("Buscando RU input: '{$inputRu}' para oferta_id: {$programacion->oferta_id}");

                // 1. Buscar al estudiante de forma exacta o por aproximación segura
                $estudiante = \App\Models\Estudiante::where('registro_universitario', $inputRu)
                    ->orWhere('registro_universitario', 'LIKE', "%{$inputRu}%")
                    ->first();

                if (!$estudiante) {
                    logger("Estudiante no encontrado con RU: {$inputRu}");
                    throw new \Exception("El RU '{$request->registro}' no existe en el sistema.");
                }

                logger("Estudiante encontrado ID: {$estudiante->id}, persona_id: {$estudiante->persona_id}");

                // 2. Verificar matrícula en la oferta académica correspondiente
                $estaMatriculado = \App\Models\MatriculacionMateria::where('oferta_id', $programacion->oferta_id)
                    ->where('estudiante_id', $estudiante->id)
                    ->exists();

                if (!$estaMatriculado) {
                    logger("Estudiante ID {$estudiante->id} no está matriculado en oferta_id {$programacion->oferta_id}");
                    throw new \Exception("El estudiante con RU '{$request->registro}' no está matriculado en este paralelo.");
                }

                // 3. Verificar que este folio no esté ya tomado por otro estudiante (excepto él mismo si se reescribe)
                $folioActual = FolioExamen::findOrFail($request->folio_id);

                // Si el folio ya pertenecía a otro, validamos duplicados
                $yaAsignado = FolioExamen::where('programacion_id', $request->programacion_id)
                    ->where('estudiante_id', $estudiante->id)
                    ->where('id', '!=', $request->folio_id)
                    ->exists();

                if ($yaAsignado) {
                    throw new \Exception("Este estudiante ya fue asignado en otro folio de esta misma lista.");
                }

                $folioActual->update([
                    'estudiante_id' => $estudiante->id,
                    'estado_folio'  => 'Foliado_Y_Separado'
                ]);

                return $estudiante;
            });

            $estudianteEncontrado->load('persona');

            $nombreCompleto = trim(
                ($estudianteEncontrado->persona->ap_paterno ?? '') . ' ' .
                    ($estudianteEncontrado->persona->ap_materno ?? '') . ', ' .
                    ($estudianteEncontrado->persona->nombres ?? '')
            );

            return response()->json([
                'success' => true,
                'nombre_completo' => $nombreCompleto
            ]);
        } catch (\Exception $e) {
            logger("Error en guardarFolioAjax: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generación Masiva automática de tacos y folios para una programación seleccionada.
     */
    public function generarMasivo(Request $request, int $programacionId)
    {
        try {
            $foliosData = $request->input('folios', []);
            $programacion = ProgramacionExamen::findOrFail($programacionId);

            DB::transaction(function () use ($programacion, $foliosData) {
                foreach ($foliosData as $posicion => $ruEstudiante) {
                    if (!empty($ruEstudiante)) {
                        $estudiante = \App\Models\Estudiante::where('registro_universitario', $ruEstudiante)->first();

                        if ($estudiante) {
                            $codigoFolioGenerado = 'FOLIO-' . str_pad($posicion, 3, '0', STR_PAD_LEFT);

                            FolioExamen::updateOrCreate(
                                [
                                    'programacion_id' => $programacion->id,
                                    'codigo_folio'    => $codigoFolioGenerado,
                                ],
                                [
                                    'estudiante_id'   => $estudiante->id,
                                    'estado_folio'    => 'Foliado_Y_Separado'
                                ]
                            );
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Foliación guardada masivamente con éxito.'
            ]);
        } catch (\Exception $e) {
            logger("Error en generarMasivo: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza la foliación numérica y notas desde la plantilla de estudiantes.
     */
    public function actualizarFoliacion(Request $request, int $programacionId)
    {
        $request->validate([
            'folios'                => 'required|array',
            'folios.*.estudiante_id' => 'required|exists:estudiantes,id',
            'folios.*.codigo_folio' => 'required|string|max:100',
            'folios.*.nota'         => 'nullable|numeric|min:0|max:100',
        ]);

        $programacion = ProgramacionExamen::findOrFail($programacionId);

        if ($programacion->bloqueado) {
            return back()->with('error', 'Acción denegada: Esta programación de examen está bloqueada.');
        }

        try {
            DB::transaction(function () use ($request, $programacionId) {
                $codigosEnviados = collect($request->folios)->pluck('codigo_folio')->filter();
                if ($codigosEnviados->count() !== $codigosEnviados->unique()->count()) {
                    throw new \Exception("Hay códigos de folio repetidos en el formulario. Cada estudiante debe tener un código único.");
                }

                foreach ($request->folios as $data) {
                    $estudianteId = $data['estudiante_id'];
                    $codigoFolio  = trim($data['codigo_folio']);

                    $nuevaNota   = isset($data['nota']) && $data['nota'] !== '' ? $data['nota'] : null;
                    $nuevoEstado = $nuevaNota !== null ? 'Calificado' : 'Foliado_Y_Separado';

                    $existenteConMismoCodigo = FolioExamen::where('programacion_id', $programacionId)
                        ->where('codigo_folio', $codigoFolio)
                        ->where('estudiante_id', '!=', $estudianteId)
                        ->exists();

                    if ($existenteConMismoCodigo) {
                        throw new \Exception("El código de folio '{$codigoFolio}' ya está asignado a otro estudiante en este mismo examen.");
                    }

                    FolioExamen::updateOrCreate(
                        [
                            'programacion_id' => $programacionId,
                            'estudiante_id'   => $estudianteId,
                        ],
                        [
                            'codigo_folio'    => $codigoFolio,
                            'nota'            => $nuevaNota,
                            'estado_folio'    => $nuevoEstado,
                        ]
                    );
                }
            });

            return redirect()->route('admin.folio-examens.plantilla', $programacionId)
                ->with('success', '¡Folios y calificaciones guardados correctamente!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Vista de la Papelera de Reciclaje para folios eliminados (SoftDeletes).
     */
    public function papelera()
    {
        $eliminados = FolioExamen::onlyTrashed()->with([
            'programacionExamen.ofertaAcademica.pensum.materia',
            'estudiante.persona'
        ])->paginate(15);

        return view('admin.folio_examens.papelera', compact('eliminados'));
    }

    /**
     * Restaura un folio eliminado de la papelera.
     */
    public function restaurar(string $id)
    {
        $folio = FolioExamen::onlyTrashed()->findOrFail($id);
        $folio->restore();

        return redirect()->route('admin.folio-examens.papelera')
            ->with('success', 'Folio restaurado exitosamente.');
    }

    /**
     * Envía un folio a la papelera (SoftDelete).
     */
    public function destroy(FolioExamen $folioExamen)
    {
        try {
            $folioExamen->delete();

            return redirect()->route('admin.folio-examens.index')
                ->with('success', 'Folio enviado a la papelera correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.folio-examens.index')
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MÓDULO PORTAL DOCENTE
    // ==========================================

    /**
     * Muestra la lista de materias/exámenes asignados únicamente al docente logueado.
     */
// ==========================================
    // MÓDULO PORTAL DOCENTE
    // ==========================================

    /**
     * Muestra la lista de materias/exámenes asignados únicamente al docente logueado.
     */
    public function docenteSeleccionarMateria()
    {
        // 🔒 Obtenemos el usuario de forma segura con el Facade Auth
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Buscamos el personal vinculado a la persona del usuario actual
        $personalId = optional($user->persona)->personal->id ?? null;

        // Filtramos utilizando estrictamente responsable_id
        $materiasAsignadas = ProgramacionExamen::where('responsable_id', $personalId)
            ->with([
                'ofertaAcademica.pensum.materia',
                'ofertaAcademica.pensum.carrera',
                'ofertaAcademica.pensum.grado',
                'ofertaAcademica.turno',
                'ofertaAcademica.paralelo',
                'ofertaAcademica.periodo'
            ])
            ->get();

        return view('admin.docente.seleccionar_materia', compact('materiasAsignadas'));
    }

    /**
     * Guarda la materia seleccionada en la sesión y redirige a la estación de foliado.
     */
    public function docenteFijarMateria(Request $request)
    {
        $request->validate([
            'programacion_id' => 'required|exists:programacion_examens,id'
        ]);

        $programacionId = $request->programacion_id;

        // 🔒 Obtenemos el usuario de forma segura
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $personalId = optional($user->persona)->personal->id ?? null;

        // Validamos propiedad utilizando responsable_id
        $perteneceAlDocente = ProgramacionExamen::where('id', $programacionId)
            ->where('responsable_id', $personalId)
            ->exists();

        // Verificamos si es administrador usando el método seguro de Spatie
        $esAdmin = method_exists($user, 'hasRole') ? $user->hasRole('Administrador') : false;

        if (!$perteneceAlDocente && !$esAdmin) {
            return back()->with('error', 'No tienes autorización para gestionar esta materia.');
        }

        // Guardamos el ID activo en la sesión del profesor
        session(['docente_programacion_id' => $programacionId]);

        return redirect()->route('docente.foliacion')
            ->with('success', 'Materia seleccionada correctamente. Ya puedes gestionar tus folios.');
    }

    /**
     * Muestra la estación de foliado utilizando el ID guardado en la sesión del docente.
     */
    /**
     * Muestra la estación de foliado a ciegas para el docente.
     */
    /**
     * Muestra la estación de foliado a ciegas para el docente cargando los folios existentes.
     */
    public function docenteEstacionFoliado()
    {
        $programacionId = session('docente_programacion_id');

        if (!$programacionId) {
            return redirect()->route('docente.seleccionar-materia')
                ->with('error', 'Primero debes seleccionar una materia de tu lista.');
        }

        $programacion = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.carrera',
            'ofertaAcademica.pensum.grado',
            'ofertaAcademica.turno',
            'ofertaAcademica.paralelo',
            'foliosExamen' // Trae los folios generados previamente por el admin
        ])->findOrFail($programacionId);

        // Obtenemos directamente la colección de folios ya registrados en la BD para esta programación
        $folios = $programacion->foliosExamen;

        return view('admin.docente.foliacion_ciega', compact('programacion', 'folios'));
    }

    /**
     * Muestra la vista de Registro de Notas para el docente basado en su materia activa en sesión.
     */
    public function docenteRegistroNotas()
    {
        $programacionId = session('docente_programacion_id');

        if (!$programacionId) {
            return redirect()->route('docente.seleccionar-materia')
                ->with('error', 'Primero debes seleccionar una materia para registrar notas.');
        }

        $programacion = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.carrera',
            'ofertaAcademica.pensum.grado',
            'ofertaAcademica.turno',
            'ofertaAcademica.paralelo',
            'foliosExamen.estudiante.persona'
        ])->findOrFail($programacionId);

        $estudiantesMatriculados = MatriculacionMateria::where('oferta_id', $programacion->oferta_id)
            ->with('estudiante.persona')
            ->get();

        return view('admin.docente.registro_notas', compact('programacion', 'estudiantesMatriculados'));
    }

    /**
     * Procesa el guardado masivo de notas ingresadas por el docente mediante Eloquent.
     */
    public function docenteGuardarNotas(Request $request)
    {
        $programacionId = session('docente_programacion_id');

        if (!$programacionId) {
            return redirect()->route('docente.seleccionar-materia')
                ->with('error', 'La sesión de la materia ha expirado.');
        }

        $programacion = ProgramacionExamen::findOrFail($programacionId);

        if ($programacion->bloqueado) {
            return back()->with('error', 'Acción denegada: Esta programación de examen está bloqueada.');
        }

        $request->validate([
            'notas'                 => 'required|array',
            'notas.*.estudiante_id' => 'required|exists:estudiantes,id',
            'notas.*.calificacion'  => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($request, $programacionId, $programacion) {
                foreach ($request->notas as $data) {
                    $estudianteId = $data['estudiante_id'];
                    $nota         = isset($data['calificacion']) && $data['calificacion'] !== '' ? $data['calificacion'] : null;

                    $estadoFolio  = $nota !== null ? 'Calificado' : 'Foliado_Y_Separado';

                    FolioExamen::updateOrCreate(
                        [
                            'programacion_id' => $programacionId,
                            'estudiante_id'   => $estudianteId,
                        ],
                        [
                            'nota'         => $nota,
                            'estado_folio' => $estadoFolio,
                        ]
                    );
                }

                // 🔍 1. COMPROBACIÓN AUTOMÁTICA DE CIERRE DENTRO DE LA TRANSACCIÓN
                // Obtenemos cuántos estudiantes están matriculados en esta oferta académica
                $totalEstudiantes = \App\Models\MatriculacionMateria::where('oferta_id', $programacion->oferta_id)->count();

                // Contamos cuántos folios de esta programación ya tienen una nota registrada (diferente de null)
                $totalConNota = FolioExamen::where('programacion_id', $programacionId)
                    ->whereNotNull('nota')
                    ->count();

                // 🔒 2. SI EL 100% DE LOS ESTUDIANTES TIENEN NOTA, SELLAMOS AUTOMÁTICAMENTE
                if ($totalEstudiantes > 0 && $totalConNota >= $totalEstudiantes) {
                    $programacion->update([
                        'bloqueado' => true
                    ]);
                }
            });

            // Mensaje dinámico según si se selló o no
            $programacion->refresh(); // Actualizamos la instancia para ver su estado actual
            if ($programacion->bloqueado) {
                return redirect()->route('docente.notas')
                    ->with('success', '¡Calificaciones guardadas! Se ha completado el 100% de los registros y el acta ha sido sellada y cerrada automáticamente.');
            }

            return redirect()->route('docente.notas')
                ->with('success', '¡Calificaciones guardadas y consolidadas con éxito!');
        } catch (\Exception $e) {
            logger("Error en docenteGuardarNotas: " . $e->getMessage());
            return back()->with('error', 'Error al guardar las notas: ' . $e->getMessage());
        }
    }
    /**
     * Vista de exámenes calificados exclusiva para el módulo del docente.
     */
    public function verExamenesCalificadosDocente(int $programacionId)
    {
        $programacion = ProgramacionExamen::with([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.grado',
            'ofertaAcademica.paralelo',
            'ofertaAcademica.turno',
            'ofertaAcademica.periodo.gestion'
        ])->findOrFail($programacionId);

        // 🔥 Agregamos 'estudiante.persona' para poder extraer el nombre y RU
        $folios = FolioExamen::with('estudiante.persona')
            ->where('programacion_id', $programacionId)
            ->get();

        return view('admin.docente.examenes_calificados', compact('programacion', 'folios'));
    }
    public function estacion(int $programacionId)
    {
        // Buscamos la programación con sus relaciones necesarias (estudiantes, inscritos, etc.)
        $programacion = ProgramacionExamen::with(['materia', 'docente', 'folios.estudiante'])->findOrFail($programacionId);

        // Opcional: Si quieres validar si es docente y solo puede ver SUS propias materias, puedes poner una condición:
        // if (auth()->user()->hasRole('Docente') && $programacion->docente_id !== auth()->user()->persona_id) {
        //     abort(403, 'No tienes permiso para ver esta programación.');
        // }

        // Retornamos exactamente la misma vista compartida
        return view('admin.folio_examens.estacion', compact('programacion'));
    }
}
