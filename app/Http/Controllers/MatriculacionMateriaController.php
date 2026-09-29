<?php

namespace App\Http\Controllers;

use App\Models\MatriculacionMateria;
use App\Models\Estudiante;
use App\Models\OfertaAcademica;
use App\Models\{Estado, Periodo, Turno, Paralelo, Gestion, Carrera, Grado, CalificacionParcial, CalificacionDetalle};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculacionMateriaController extends Controller
{
    public function index(Request $request)
    {
        $periodos  = Periodo::with('gestion')->orderBy('id', 'desc')->get();
        $carreras  = Carrera::all();
        $grados    = Grado::all();
        $turnos    = Turno::all();
        $paralelos = Paralelo::all();

        foreach ($periodos as $per) {
            $nombreGestion = optional($per->gestion)->nombre ?? '';
            $per->nombre_completo = $per->nombre . ($nombreGestion ? " - Gestión {$nombreGestion}" : '');
        }

        $hayFiltros = $request->filled('periodo_id') ||
            $request->filled('carrera_id') ||
            $request->filled('grado_id') ||
            $request->filled('turno_id') ||
            $request->filled('paralelo_id') ||
            $request->filled('busqueda');

        $matriculaciones = collect();

        if ($hayFiltros) {
            $query = MatriculacionMateria::with([
                'estudiante.persona',
                'oferta.periodo',
                'oferta.paralelo',
                'oferta.turno',
                'oferta.pensum.carrera',
                'oferta.pensum.grado',
                'estado'
            ]);

            if ($request->filled('periodo_id')) {
                $query->whereHas('oferta', fn($o) => $o->where('periodo_id', $request->periodo_id));
            }

            if ($request->filled('carrera_id')) {
                $query->whereHas('oferta.pensum', fn($p) => $p->where('carrera_id', $request->carrera_id));
            }

            if ($request->filled('grado_id')) {
                $query->whereHas('oferta.pensum', fn($p) => $p->where('grado_id', $request->grado_id));
            }

            if ($request->filled('turno_id')) {
                $query->whereHas('oferta', fn($o) => $o->where('turno_id', $request->turno_id));
            }

            if ($request->filled('paralelo_id')) {
                $query->whereHas('oferta', fn($o) => $o->where('paralelo_id', $request->paralelo_id));
            }

            if ($request->filled('busqueda')) {
                $busqueda = trim($request->busqueda);
                $query->whereHas('estudiante', function ($q) use ($busqueda) {
                    $q->where('registro_universitario', 'like', "%{$busqueda}%")
                        ->orWhereHas('persona', function ($q2) use ($busqueda) {
                            $q2->where('nombres', 'like', "%{$busqueda}%")
                                ->orWhere('ap_paterno', 'like', "%{$busqueda}%")
                                ->orWhere('ap_materno', 'like', "%{$busqueda}%")
                                ->orWhere('ci', 'like', "%{$busqueda}%");
                        });
                });
            }

            $matriculaciones = $query->get()
                ->groupBy(function ($item) {
                    $periodoId = $item->oferta->periodo_id ?? 0;
                    return $item->estudiante_id . '-' . $periodoId;
                })
                ->map(function ($group) {
                    $first = $group->first();
                    $first->total_materias = $group->count();
                    return $first;
                })
                ->sort(function ($a, $b) {
                    $paternoA = mb_strtolower(trim($a->estudiante->persona->ap_paterno ?? ''));
                    $paternoB = mb_strtolower(trim($b->estudiante->persona->ap_paterno ?? ''));
                    $resPaterno = strcmp($paternoA, $paternoB);
                    if ($resPaterno !== 0) return $resPaterno;

                    $maternoA = mb_strtolower(trim($a->estudiante->persona->ap_materno ?? ''));
                    $maternoB = mb_strtolower(trim($b->estudiante->persona->ap_materno ?? ''));
                    $resMaterno = strcmp($maternoA, $maternoB);
                    if ($resMaterno !== 0) return $resMaterno;

                    $nombreA = mb_strtolower(trim($a->estudiante->persona->nombres ?? ''));
                    $nombreB = mb_strtolower(trim($b->estudiante->persona->nombres ?? ''));
                    return strcmp($nombreA, $nombreB);
                })
                ->values();
        }

        return view('admin.matriculacion_materias.index', compact(
            'matriculaciones',
            'periodos',
            'carreras',
            'grados',
            'turnos',
            'paralelos'
        ));
    }

    public function create()
    {
        $estudiantes = Estudiante::with('persona')->get();
        $carreras = Carrera::all();
        $grados = Grado::all();

        $periodos = Periodo::with('gestion')->get()->map(function ($p) {
            $p->nombre_completo = $p->nombre . ' - ' . ($p->gestion->nombre ?? 'S/G');
            return $p;
        });
        $turnos = Turno::all();
        $paralelos = Paralelo::all();
        $estados = Estado::where('contexto', 'academico')->get();
        $ofertas = OfertaAcademica::with(['pensum.carrera', 'pensum.grado', 'pensum.materia', 'periodo', 'turno', 'paralelo'])->get();

        return view('admin.matriculacion_materias.create', compact(
            'estudiantes',
            'carreras',
            'grados',
            'periodos',
            'turnos',
            'paralelos',
            'estados',
            'ofertas'
        ));
    }
    protected function inicializarCalificaciones(MatriculacionMateria $matriculacion)
    {
        // Cargamos la relación si no viene cargada
        $matriculacion->loadMissing('oferta.pensum');

        $carreraId = optional($matriculacion->oferta->pensum)->carrera_id;
        if (!$carreraId) return;

        $configParciales = DB::table('configuracion_parcial_meta')
            ->where('carrera_id', $carreraId)
            ->get();

        foreach ($configParciales as $cpm) {
            $calificacionParcial = CalificacionParcial::firstOrCreate(
                [
                    'matriculacion_id' => $matriculacion->id,
                    'nro_parcial'      => $cpm->nro_parcial,
                ],
                [
                    'ponderacion_parcial' => $cpm->ponderacion_parcial,
                    'estado_id'           => $matriculacion->estado_id,
                ]
            );

            $configComponentes = DB::table('configuracion_componente_meta')
                ->where('configuracion_parcial_id', $cpm->id)
                ->get();

            foreach ($configComponentes as $ccm) {
                CalificacionDetalle::firstOrCreate(
                    [
                        'calificacion_parcial_id' => $calificacionParcial->id,
                        'tipo_componente'         => $ccm->tipo_componente,
                    ],
                    [
                        'ponderacion_componente' => $ccm->ponderacion_componente,
                        'nota'                   => 0.00,
                        'modalidad_origen'       => 'directa',
                        'estado_id'              => $matriculacion->estado_id,
                    ]
                );
            }
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_ids'   => 'required|array',
            'estudiante_ids.*' => 'exists:estudiantes,id',
            'oferta_ids'       => 'required|array',
            'oferta_ids.*'     => 'exists:oferta_academicas,id',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        // Aumentamos ligeramente el tiempo límite por seguridad para lotes masivos grandes
        set_time_limit(120);

        try {
            DB::beginTransaction();

            // 1. Precargamos todas las ofertas académicas con sus pensums para evitar consultas repetitivas N+1
            $ofertas = OfertaAcademica::with('pensum')->whereIn('id', $request->oferta_ids)->get()->keyBy('id');

            // 2. Extraemos los carrera_id directamente desde la relación del pensum de cada oferta seleccionada
            $carreraIds = $ofertas->pluck('pensum.carrera_id')->unique()->filter();


            $metasPorCarrera = [];
            foreach ($carreraIds as $carreraId) {
                $parciales = DB::table('configuracion_parcial_meta')
                    ->where('carrera_id', $carreraId)
                    ->get();

                $estructuraParciales = [];
                foreach ($parciales as $p) {
                    $componentes = DB::table('configuracion_componente_meta')
                        ->where('configuracion_parcial_id', $p->id)
                        ->get();

                    $estructuraParciales[] = [
                        'parcial'     => $p,
                        'componentes' => $componentes
                    ];
                }
                $metasPorCarrera[$carreraId] = $estructuraParciales;
            }

            // 3. Procesamos el lote masivo con la información en memoria
            foreach ($request->estudiante_ids as $estudianteId) {
                foreach ($request->oferta_ids as $ofertaId) {
                    $oferta = $ofertas->get($ofertaId);
                    if (!$oferta || !$oferta->pensum) continue;

                    // Obtenemos la carrera correctamente a través del pensum
                    $carreraId = $oferta->pensum->carrera_id;

                    // Creamos o restauramos la matriculación
                    $matriculacion = MatriculacionMateria::withTrashed()->updateOrCreate(
                        [
                            'estudiante_id' => $estudianteId,
                            'oferta_id'     => $ofertaId,
                        ],
                        [
                            'estado_id'      => $request->estado_id,
                            'deleted_at'     => null,
                            'fecha_registro' => now(),
                        ]
                    );

                    // 4. Inicializamos las calificaciones usando la estructura en memoria
                    if ($carreraId && isset($metasPorCarrera[$carreraId])) {
                        foreach ($metasPorCarrera[$carreraId] as $meta) {
                            $cpm = $meta['parcial'];

                            $calificacionParcial = CalificacionParcial::firstOrCreate(
                                [
                                    'matriculacion_id'         => $matriculacion->id,
                                    'configuracion_parcial_id' => $cpm->id,
                                    'nro_parcial'              => $cpm->nro_parcial,
                                ],
                                [
                                    'ponderacion_parcial' => $cpm->ponderacion_parcial,
                                    'estado_id'           => $request->estado_id,
                                ]
                            );

                            foreach ($meta['componentes'] as $ccm) {
                                CalificacionDetalle::firstOrCreate(
                                    [
                                        'calificacion_parcial_id' => $calificacionParcial->id,
                                        'tipo_componente'         => $ccm->tipo_componente,
                                    ],
                                    [
                                        'ponderacion_componente' => $ccm->ponderacion_componente,
                                        'nota'                   => 0.00,
                                        'modalidad_origen'       => 'directa',
                                        'estado_id'              => $request->estado_id,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.matriculacion-materias.index')
                ->with('success', 'Matriculación masiva de bloques procesada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd([
                'error_mensaje' => $e->getMessage(),
                'archivo'       => $e->getFile(),
                'linea'         => $e->getLine(),
                'traza'         => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Ocurrió un error al procesar el lote: ' . $e->getMessage())->withInput();
        }
    }
    public function show(int $estudianteId, int $periodoId)
    {
        $estudiante = Estudiante::with('persona')->findOrFail($estudianteId);
        $periodo = Periodo::findOrFail($periodoId);

        $matriculaciones = MatriculacionMateria::with(['oferta.pensum.materia', 'oferta.pensum.carrera', 'oferta.pensum.grado', 'oferta.turno', 'oferta.paralelo', 'estado'])
            ->where('estudiante_id', $estudianteId)
            ->whereHas('oferta', function ($query) use ($periodoId) {
                $query->where('periodo_id', $periodoId);
            })
            ->get();

        $carreras = Carrera::all();
        $grados = Grado::all();
        $turnos = Turno::all();
        $paralelos = Paralelo::all();
        $estados = Estado::where('contexto', 'academico')->get();
        $ofertas = OfertaAcademica::with(['pensum.materia', 'pensum.carrera', 'pensum.grado', 'turno', 'paralelo'])
            ->where('periodo_id', $periodoId)
            ->get();

        return view('admin.matriculacion_materias.show', compact(
            'estudiante',
            'periodo',
            'matriculaciones',
            'carreras',
            'grados',
            'turnos',
            'paralelos',
            'estados',
            'ofertas'
        ));
    }
    public function storeSingle(Request $request)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'oferta_id'     => 'required|exists:oferta_academicas,id',
            'estado_id'     => 'required|exists:estados,id',
        ]);

        $matriculacion = MatriculacionMateria::withTrashed()
            ->where('estudiante_id', $request->estudiante_id)
            ->where('oferta_id', $request->oferta_id)
            ->first();

        if ($matriculacion) {
            if ($matriculacion->trashed()) {
                $matriculacion->restore();
            }

            $matriculacion->update([
                'estado_id'      => $request->estado_id,
                'fecha_registro' => now(),
            ]);
        } else {
            MatriculacionMateria::create([
                'estudiante_id'  => $request->estudiante_id,
                'oferta_id'      => $request->oferta_id,
                'estado_id'      => $request->estado_id,
                'fecha_registro' => now(),
            ]);
        }

        $periodoIdRedirect = $request->periodo_id;

        return redirect()->route('admin.matriculacion-materias.show', [$request->estudiante_id, $periodoIdRedirect])
            ->with('success', 'Materia añadida correctamente a la carga académica.');
    }
    public function edit(MatriculacionMateria $matriculacionMateria)
    {
        $estudiantes = Estudiante::with('persona')->get();
        $ofertas = OfertaAcademica::with(['pensum.materia', 'periodo', 'turno', 'paralelo'])->get();
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.matriculacion_materias.edit', compact('matriculacionMateria', 'estudiantes', 'ofertas', 'estados'));
    }
    public function update(Request $request, MatriculacionMateria $matriculacionMateria)
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'oferta_id'     => 'required|exists:oferta_academicas,id',
            'estado_id'     => 'required|exists:estados,id',
        ]);

        $matriculacionMateria->update([
            'estudiante_id' => $request->estudiante_id,
            'oferta_id'     => $request->oferta_id,
            'estado_id'     => $request->estado_id,
        ]);

        return redirect()->route('admin.matriculacion-materias.index')
            ->with('success', 'Matriculación actualizada correctamente.');
    }
    public function editGroup(Request $request)
    {
        $estudiantes = Estudiante::with('persona')->get();
        $carreras    = Carrera::all();
        $grados      = Grado::all();
        $periodos    = Periodo::with('gestion')->get()->map(function ($p) {
            $p->nombre_completo = $p->nombre . ' - ' . ($p->gestion->nombre ?? 'S/G');
            return $p;
        });
        $turnos      = Turno::all();
        $paralelos   = Paralelo::all();
        $estados     = Estado::where('contexto', 'academico')->get();
        $ofertas     = OfertaAcademica::with(['pensum.carrera', 'pensum.grado', 'pensum.materia', 'periodo', 'turno', 'paralelo'])->get();

        // Capturamos los filtros del grupo que vienen por URL
        $filtroPeriodoId  = $request->query('periodo_id');
        $filtroCarreraId  = $request->query('carrera_id');
        $filtroGradoId    = $request->query('grado_id');
        $filtroTurnoId    = $request->query('turno_id');
        $filtroParaleloId = $request->query('paralelo_id');

        // Buscamos los IDs de los estudiantes que tienen matriculaciones en este grupo exacto
        $estudiantesGrupoIds = [];
        if ($filtroPeriodoId || $filtroCarreraId) {
            $estudiantesGrupoIds = MatriculacionMateria::whereHas('oferta', function ($q) use ($filtroPeriodoId, $filtroCarreraId, $filtroGradoId, $filtroTurnoId, $filtroParaleloId) {
                if ($filtroPeriodoId)  $q->where('periodo_id', $filtroPeriodoId);
                if ($filtroTurnoId)    $q->where('turno_id', $filtroTurnoId);
                if ($filtroParaleloId) $q->where('paralelo_id', $filtroParaleloId);

                $q->whereHas('pensum', function ($p) use ($filtroCarreraId, $filtroGradoId) {
                    if ($filtroCarreraId) $p->where('carrera_id', $filtroCarreraId);
                    if ($filtroGradoId)   $p->where('grado_id', $filtroGradoId);
                });
            })
                ->pluck('estudiante_id')
                ->unique()
                ->toArray();
        }

        return view('admin.matriculacion_materias.edit_group', compact(
            'estudiantes',
            'carreras',
            'grados',
            'periodos',
            'turnos',
            'paralelos',
            'estados',
            'ofertas',
            'estudiantesGrupoIds',
            'filtroCarreraId',
            'filtroGradoId',
            'filtroPeriodoId',
            'filtroTurnoId',
            'filtroParaleloId' // <--- Asegúrate de incluir estas variables aquí
        ));
    }
    public function updateGroup(Request $request)
    {
        $request->validate([
            'estudiante_ids'   => 'required|array',
            'oferta_ids'       => 'required|array',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        set_time_limit(120);

        try {
            DB::beginTransaction();

            // 1. Precargamos las ofertas y sus pensums UNA SOLA VEZ fuera del ciclo ⚡
            $ofertas = OfertaAcademica::with('pensum')->whereIn('id', $request->oferta_ids)->get()->keyBy('id');
            $pensumIds = $ofertas->pluck('pensum.carrera_id')->unique(); // Ojo, filtramos por pensums afectados

            // 2. Extraemos las estructuras de calificaciones (Parciales y Componentes) por Carrera
            $carreraIds = $ofertas->pluck('pensum.carrera_id')->unique()->filter();
            $metasPorCarrera = [];
            foreach ($carreraIds as $carreraId) {
                $parciales = DB::table('configuracion_parcial_meta')
                    ->where('carrera_id', $carreraId)
                    ->get();

                $estructuraParciales = [];
                foreach ($parciales as $p) {
                    $componentes = DB::table('configuracion_componente_meta')
                        ->where('configuracion_parcial_id', $p->id)
                        ->get();

                    $estructuraParciales[] = [
                        'parcial'     => $p,
                        'componentes' => $componentes
                    ];
                }
                $metasPorCarrera[$carreraId] = $estructuraParciales;
            }

            foreach ($request->estudiante_ids as $estudianteId) {

                // 3. Obtenemos los pensums de las ofertas seleccionadas para este bloque
                $pensumIdsPorOferta = $ofertas->pluck('pensum_id')->unique();

                // 4. ELIMINAR EXCESOS: Borramos las materias de esos pensums que el usuario desmarcó
                MatriculacionMateria::where('estudiante_id', $estudianteId)
                    ->whereHas('oferta', function ($query) use ($pensumIdsPorOferta) {
                        $query->whereIn('pensum_id', $pensumIdsPorOferta);
                    })
                    ->whereNotIn('oferta_id', $request->oferta_ids)
                    ->delete();

                // 5. ACTUALIZAR O CREAR: Registramos o actualizamos las materias seleccionadas
                foreach ($request->oferta_ids as $ofertaId) {
                    $oferta = $ofertas->get($ofertaId);
                    if (!$oferta || !$oferta->pensum) continue;

                    $carreraId = $oferta->pensum->carrera_id;

                    $matriculacion = MatriculacionMateria::withTrashed()->updateOrCreate(
                        [
                            'estudiante_id' => $estudianteId,
                            'oferta_id'     => $ofertaId,
                        ],
                        [
                            'estado_id'      => $request->estado_id,
                            'deleted_at'     => null,
                            'fecha_registro' => now(),
                        ]
                    );

                    // 6. INICIALIZAR CALIFICACIONES (Garantiza que si es nueva, tenga sus parciales listos)
                    if ($carreraId && isset($metasPorCarrera[$carreraId])) {
                        foreach ($metasPorCarrera[$carreraId] as $meta) {
                            $cpm = $meta['parcial'];

                            $calificacionParcial = CalificacionParcial::firstOrCreate(
                                [
                                    'matriculacion_id'         => $matriculacion->id,
                                    'configuracion_parcial_id' => $cpm->id,
                                    'nro_parcial'              => $cpm->nro_parcial,
                                ],
                                [
                                    'ponderacion_parcial' => $cpm->ponderacion_parcial,
                                    'estado_id'           => $request->estado_id,
                                ]
                            );

                            foreach ($meta['componentes'] as $ccm) {
                                CalificacionDetalle::firstOrCreate(
                                    [
                                        'calificacion_parcial_id' => $calificacionParcial->id,
                                        'tipo_componente'         => $ccm->tipo_componente,
                                    ],
                                    [
                                        'ponderacion_componente' => $ccm->ponderacion_componente,
                                        'nota'                   => 0.00,
                                        'modalidad_origen'       => 'directa',
                                        'estado_id'              => $request->estado_id,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.matriculacion-materias.index')
                ->with('success', 'Actualización, sincronización de grupo y calificaciones realizada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al actualizar el grupo: ' . $e->getMessage());
        }
    }
    public function destroy(MatriculacionMateria $matriculacionMateria)
    {
        $matriculacionMateria->delete();

        return redirect()->route('admin.matriculacion-materias.index')
            ->with('success', 'Matriculación enviada a la papelera correctamente.');
    }
    public function papelera(Request $request)
    {
        $matriculacionesEliminadas = MatriculacionMateria::onlyTrashed()
            ->with(['estudiante.persona', 'oferta.pensum.materia', 'oferta.periodo', 'oferta.pensum.carrera'])
            ->latest('deleted_at')
            ->get(); // <--- Cambiado de paginate(15) a get() para que DataTables gestione todos los registros

        return view('admin.matriculacion_materias.papelera', compact('matriculacionesEliminadas'));
    }
    public function restaurar(string $id)
    {
        $matriculacion = MatriculacionMateria::onlyTrashed()->findOrFail($id);
        $matriculacion->restore();

        return redirect()->route('admin.matriculacion-materias.papelera')
            ->with('success', 'Matriculación restaurada con éxito.');
    }
    public function fuerzaDestruccion(int $id)
    {
        $matriculacion = MatriculacionMateria::onlyTrashed()->findOrFail($id);
        $matriculacion->forceDelete();

        return redirect()->route('admin.matriculacion-materias.papelera')
            ->with('success', 'El registro ha sido eliminado permanentemente de la base de datos.');
    }
    public function procesarRetiro(Request $request, MatriculacionMateria $matriculacionMateria)
    {
        $request->validate([
            'estado_id' => 'required|exists:estados,id'
        ]);

        $matriculacionMateria->update([
            'estado_id' => $request->estado_id
        ]);

        return redirect()->route('admin.matriculacion-materias.index')
            ->with('success', 'El estado de la matriculación de la materia ha sido actualizado.');
    }
}
