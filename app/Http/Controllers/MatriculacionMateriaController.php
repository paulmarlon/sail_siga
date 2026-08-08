<?php

namespace App\Http\Controllers;

use App\Models\MatriculacionMateria;
use App\Models\Estudiante;
use App\Models\OfertaAcademica;
use App\Models\{Estado, Periodo, Turno, Paralelo, Gestion, Carrera, Grado};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculacionMateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'estudiante_ids'   => 'required|array',
            'estudiante_ids.*' => 'exists:estudiantes,id',
            'oferta_ids'       => 'required|array',
            'oferta_ids.*'     => 'exists:oferta_academicas,id',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->estudiante_ids as $estudianteId) {
                foreach ($request->oferta_ids as $ofertaId) {
                    MatriculacionMateria::withTrashed()->updateOrCreate(
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
                }
            }

            DB::commit();

            return redirect()->route('admin.matriculacion-materias.index')
                ->with('success', 'Matriculación masiva de bloques procesada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el lote: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
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

    /**
     * Almacenar una única materia de forma quirúrgica desde la vista de detalle.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MatriculacionMateria $matriculacionMateria)
    {
        $estudiantes = Estudiante::with('persona')->get();
        $ofertas = OfertaAcademica::with(['pensum.materia', 'periodo', 'turno', 'paralelo'])->get();
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.matriculacion_materias.edit', compact('matriculacionMateria', 'estudiantes', 'ofertas', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
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
    /**
     * Muestra la vista de actualización masiva por grupos.
     */
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
            'filtroParaleloId'
        ));
    }

    /**
     * Procesa la actualización masiva por grupo en paralelo.
     */
    public function updateGroup(Request $request)
    {
        $request->validate([
            'estudiante_ids'   => 'required|array',
            'oferta_ids'       => 'required|array',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->estudiante_ids as $estudianteId) {

                // 1. Identificar a qué "pensums" o bloque académico pertenecen las materias que estás enviando
                $nuevasOfertas = OfertaAcademica::whereIn('id', $request->oferta_ids)->get();
                $pensumIds = $nuevasOfertas->pluck('pensum_id')->unique();

                // 2. ELIMINAR EXCESOS (Si antes tenía 13 y ahora solo seleccionaste 12 del mismo bloque,
                // borramos las que ya no vienen en la lista del formulario)
                MatriculacionMateria::where('estudiante_id', $estudianteId)
                    ->whereHas('oferta', function ($query) use ($pensumIds) {
                        $query->whereIn('pensum_id', $pensumIds);
                    })
                    ->whereNotIn('oferta_id', $request->oferta_ids) // <- La clave: borra lo que ya no está seleccionado
                    ->delete();

                // 3. ACTUALIZAR O CREAR las materias seleccionadas (ahora serán exactamente las 12 que elegiste)
                foreach ($request->oferta_ids as $ofertaId) {
                    MatriculacionMateria::withTrashed()->updateOrCreate(
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
                }
            }

            DB::commit();
            return redirect()->route('admin.matriculacion-materias.index')
                ->with('success', 'Actualización y sincronización de grupo realizada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al actualizar el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(MatriculacionMateria $matriculacionMateria)
    {
        $matriculacionMateria->delete();

        return redirect()->route('admin.matriculacion-materias.index')
            ->with('success', 'Matriculación enviada a la papelera correctamente.');
    }

    /**
     * Display a listing of soft deleted resources (Papelera).
     */
    public function papelera(Request $request)
    {
        $matriculacionesEliminadas = MatriculacionMateria::onlyTrashed()
            ->with(['estudiante.persona', 'oferta.pensum.materia', 'oferta.periodo', 'oferta.pensum.carrera'])
            ->latest('deleted_at')
            ->get(); // <--- Cambiado de paginate(15) a get() para que DataTables gestione todos los registros

        return view('admin.matriculacion_materias.papelera', compact('matriculacionesEliminadas'));
    }

    /**
     * Restore the specified soft deleted resource.
     */
    public function restaurar(string $id)
    {
        $matriculacion = MatriculacionMateria::onlyTrashed()->findOrFail($id);
        $matriculacion->restore();

        return redirect()->route('admin.matriculacion-materias.papelera')
            ->with('success', 'Matriculación restaurada con éxito.');
    }

    /**
     * Destruir permanentemente (Force Delete).
     */
    public function fuerzaDestruccion(int $id)
    {
        $matriculacion = MatriculacionMateria::onlyTrashed()->findOrFail($id);
        $matriculacion->forceDelete();

        return redirect()->route('admin.matriculacion-materias.papelera')
            ->with('success', 'El registro ha sido eliminado permanentemente de la base de datos.');
    }

    /**
     * Procesar retiro de materia (Cambio de estado o baja lógica).
     */
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
