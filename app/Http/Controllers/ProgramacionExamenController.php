<?php

namespace App\Http\Controllers;

use App\Models\ProgramacionExamen;
use App\Models\OfertaAcademica;
use App\Models\Personal;
use App\Models\Periodo;
use App\Models\Carrera;
use App\Models\Grado;
use App\Models\Turno;
use App\Models\Paralelo;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramacionExamenController extends Controller
{
    /**
     * Display a listing of the resource (Optimizado con Paginación).
     */
    public function index(Request $request)
    {
        // 1. Capturamos los filtros de la interfaz
        $periodoId = $request->get('periodo_id');
        $carreraId = $request->get('carrera_id');
        $gradoId = $request->get('grado_id');
        $turnoId = $request->get('turno_id');
        $paraleloId = $request->get('paralelo_id');
        $instanciaFiltro = $request->get('instancia');
        $busqueda = $request->get('busqueda');

        // 🔥 2. ESTRATEGIA INTELIGENTE DE PERIODO:
        // Si el usuario no selecciona un periodo en la interfaz,
        // tomamos por defecto el ID 1 (o el periodo más reciente registrado) para que no cargue datos de otros años.
        if (!$periodoId) {
            $periodoId = 1; // O puedes usar: Periodo::latest('id')->value('id') ?? 1;
        }

        // 3. Construimos la consulta base con sus relaciones
        $query = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'programacionesExamen'
        ]);

        // 4. APLICAMOS FILTROS ESSENCIALES (Aquí es donde ordenamos la casa)

        // Filtro obligatorio por periodo para no mezclar gestiones
        $query->where('periodo_id', $periodoId);

        if ($turnoId) {
            $query->where('turno_id', $turnoId);
        }

        if ($paraleloId) {
            $query->where('paralelo_id', $paraleloId);
        }

        if ($carreraId || $gradoId) {
            $query->whereHas('pensum', function ($q) use ($carreraId, $gradoId) {
                if ($carreraId) $q->where('carrera_id', $carreraId);
                if ($gradoId) $q->where('grado_id', $gradoId);
            });
        }

        if ($busqueda) {
            $query->whereHas('pensum.materia', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('sigla', 'like', "%{$busqueda}%");
            });
        }

        // Filtro opcional de estado de programación
        if ($instanciaFiltro) {
            if ($instanciaFiltro === 'PENDIENTE') {
                $query->whereDoesntHave('programacionesExamen');
            } else {
                $query->whereHas('programacionesExamen', function ($q) use ($instanciaFiltro) {
                    $q->where('instancia', $instanciaFiltro);
                });
            }
        }

        // 5. Obtenemos los resultados filtrados pero completos de esa gestión
        $listaOfertas = $query->get();

        // 6. Catálogos para alimentar los selects de los filtros en la vista
        $periodos = Periodo::with('gestion')->get();
        $carreras = Carrera::all();
        $grados = Grado::all();
        $turnos = Turno::all();
        $paralelos = Paralelo::all();

        return view('admin.programacion_examenes.index', compact(
            'listaOfertas',
            'periodos',
            'carreras',
            'grados',
            'turnos',
            'paralelos',
            'periodoId' // Útil para mantener seleccionado el select en la vista
        ));
    }

    public function create(Request $request)
    {
        $ids = [];
        if ($request->has('ofertas_ids')) {
            $ids = $request->get('ofertas_ids', []);
        } elseif ($request->has('oferta_id')) {
            $ids = [$request->get('oferta_id')];
        }

        $listaOfertas = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'docenteActual.docente.persona',
            'programacionesExamen' // <--- Importante cargar esto para evaluar el avance por cada oferta
        ])->whereIn('id', array_filter($ids))->get();

        $listaPersonal = Personal::with('persona')->get();

        $ofertaSeleccionadaId = $request->get('oferta_id');
        $examenesExistentes = collect();
        if ($ofertaSeleccionadaId && count($listaOfertas) === 1) {
            $examenesExistentes = ProgramacionExamen::where('oferta_id', $ofertaSeleccionadaId)->get();
        }

        return view('admin.programacion_examenes.create', compact(
            'listaOfertas',
            'listaPersonal',
            'ofertaSeleccionadaId',
            'examenesExistentes'
        ));
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if ($request->has('programaciones')) {
                    foreach ($request->programaciones as $data) {
                        ProgramacionExamen::create([
                            'oferta_id'             => $data['oferta_id'],
                            'instancia'             => $data['instancia'],
                            'modalidad'             => $data['modalidad'] ?? 'directa',
                            'tipo_proceso'          => $data['tipo_proceso'] ?? 'Ordinario',
                            'fecha_programada'      => $data['fecha_programada'],
                            'responsable_id'        => $data['responsable_id'],
                            'bloqueado'             => false,
                            'observaciones_legales' => $data['observaciones'] ?? null,
                        ]);
                    }
                } else {
                    $request->validate([
                        'oferta_id' => 'required|exists:oferta_academicas,id',
                        'instancia' => 'required|string',
                    ]);

                    ProgramacionExamen::create([
                        'oferta_id'             => $request->oferta_id,
                        'instancia'             => $request->instancia,
                        'modalidad'             => $request->modalidad,
                        'tipo_proceso'          => $request->tipo_proceso,
                        'fecha_programada'      => $request->fecha_programada,
                        'responsable_id'        => $request->responsable_id,
                        'bloqueado'             => $request->has('bloqueado'),
                        'observaciones_legales' => $request->observaciones,
                    ]);
                }
            });

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', 'Programación de exámenes guardada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar: ' . $e->getMessage())->withInput();
        }
    }

    public function show(ProgramacionExamen $programacionExamen)
    {
        $programacionExamen->load(['ofertaAcademica.pensum.materia', 'responsable.persona']);
        return view('admin.programacion_examenes.show', compact('programacionExamen'));
    }

    public function editMasivo(Request $request)
    {
        $ids = $request->get('ofertas_ids', []);
        $instanciaFiltro = $request->get('instancia_filtro'); // Capturamos la instancia enviada por el modal (ej: P1, P2, EF, 2T)

        if (empty($ids)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se seleccionaron ofertas académicas para editar en lote.');
        }

        if (empty($instanciaFiltro)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Debe especificar la instancia evaluativa que desea editar.');
        }

        // Cargamos las ofertas y filtramos las programaciones de examen exclusivamente para la instancia elegida
        $listaOfertas = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'programacionesExamen' => function ($query) use ($instanciaFiltro) {
                $query->where('instancia', $instanciaFiltro);
            }
        ])->whereIn('id', $ids)->get();

        $listaPersonal = Personal::with('persona')->get();

        return view('admin.programacion_examenes.edit', compact('listaOfertas', 'listaPersonal', 'instanciaFiltro'));
    }

    public function updateMasivo(Request $request)
    {
        $request->validate([
            'programaciones' => 'required|array',
            'programaciones.*.oferta_id' => 'required|exists:oferta_academicas,id',
            'programaciones.*.instancia' => 'required|string',
            'programaciones.*.fecha_programada' => 'required|date',
            'programaciones.*.responsable_id' => 'required|exists:personals,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->programaciones as $data) {
                    ProgramacionExamen::updateOrCreate(
                        ['id' => $data['programacion_id'] ?? null],
                        [
                            'oferta_id'             => $data['oferta_id'],
                            'instancia'             => $data['instancia'],
                            'modalidad'             => $data['modalidad'] ?? 'directa',
                            'tipo_proceso'          => $data['tipo_proceso'] ?? 'Ordinario',
                            'fecha_programada'      => $data['fecha_programada'],
                            'responsable_id'        => $data['responsable_id'],
                            'bloqueado'             => isset($data['bloqueado']),
                            'observaciones_legales' => $data['observaciones'] ?? null,
                        ]
                    );
                }
            });

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', 'Actualización masiva de exámenes realizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error en la actualización masiva: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyMasivo(Request $request)
    {
        $ids = $request->input('ofertas_ids', []);
        $instanciaFiltro = $request->input('instancia_filtro'); // <-- Capturamos la instancia del modal

        if (empty($ids)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se seleccionaron elementos para eliminar en lote.');
        }

        if (empty($instanciaFiltro)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Debe especificar la instancia evaluativa que desea eliminar.');
        }

        try {
            DB::transaction(function () use ($ids, $instanciaFiltro) {
                // Verificar si alguna de las programaciones de ESTA instancia está bloqueada
                $bloqueadasCount = ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->where('bloqueado', true)
                    ->count();

                if ($bloqueadasCount > 0) {
                    throw new \Exception('Uno o más exámenes seleccionados de esta instancia se encuentran BLOQUEADOS.');
                }

                // Obtener únicamente los exámenes de la oferta Y de la instancia seleccionada
                $examenesAEliminar = ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->get();

                // Eliminar aplicando los filtros correspondientes
                ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->delete();

                // Registrar la auditoría por cada registro afectado
                foreach ($examenesAEliminar as $ex) {
                    Auditoria::create([
                        'user_id' => Auth::id(),
                        'auditable_id'       => $ex->id,
                        'auditable_type'     => ProgramacionExamen::class,
                        'accion'             => 'SOFT_DELETE_MASIVO',
                        'valores_anteriores' => $ex->toArray(),
                        'valores_nuevos'     => ['deleted_at' => now()->toDateTimeString()],
                        'ip_address'         => request()->ip(),
                        'user_agent'         => request()->header('User-Agent'),
                    ]);
                }
            });

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', 'La instancia seleccionada del lote de exámenes fue enviada a la papelera con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se pudo procesar el lote: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProgramacionExamen $programacionExamen)
    {
        if ($programacionExamen->bloqueado) {
            return back()->with('error', 'Acción denegada: Este examen se encuentra bloqueado legalmente.');
        }

        $request->validate([
            'oferta_id' => 'required|exists:oferta_academicas,id',
            'instancia' => 'required|string|max:255',
            'modalidad' => 'required|string|in:directa,a_ciegas,dictada',
            'tipo_proceso' => 'required|string',
            'fecha_programada' => 'required|date',
            'responsable_id' => 'required|exists:personals,id',
            'bloqueado' => 'boolean',
            'observaciones_legales' => 'nullable|string',
        ]);

        $programacionExamen->update($request->all());

        return redirect()->route('admin.programacion-examenes.index')
            ->with('success', 'Programación de examen actualizada correctamente.');
    }

    public function destroy(ProgramacionExamen $programacionExamen)
    {
        if ($programacionExamen->bloqueado) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se puede eliminar la programación porque se encuentra BLOQUEADA.');
        }

        try {
            DB::transaction(function () use ($programacionExamen) {
                $valoresAnteriores = $programacionExamen->toArray();
                $programacionExamen->delete();

                Auditoria::create([
                    'user_id' => Auth::id(),
                    'auditable_id'       => $programacionExamen->id,
                    'auditable_type'     => ProgramacionExamen::class,
                    'accion'             => 'SOFT_DELETE',
                    'valores_anteriores' => $valoresAnteriores,
                    'valores_nuevos'     => ['deleted_at' => now()->toDateTimeString()],
                    'ip_address'         => request()->ip(),
                    'user_agent'         => request()->header('User-Agent'),
                ]);
            });

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', 'Programación enviada a la papelera correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function papelera()
    {
        $eliminados = ProgramacionExamen::onlyTrashed()
            ->with(['ofertaAcademica.pensum.materia', 'responsable.persona'])
            ->paginate(10);

        return view('admin.programacion_examenes.papelera', compact('eliminados'));
    }

    public function restaurar(string $id)
    {
        $programacion = ProgramacionExamen::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($programacion) {
            $deletedAtAnterior = $programacion->deleted_at;
            $programacion->restore();

            Auditoria::create([
                'user_id' => Auth::id(),
                'auditable_id'       => $programacion->id,
                'auditable_type'     => ProgramacionExamen::class,
                'accion'             => 'RESTORE',
                'valores_anteriores' => ['deleted_at' => $deletedAtAnterior],
                'valores_nuevos'     => ['deleted_at' => null],
                'ip_address'         => request()->ip(),
                'user_agent'         => request()->header('User-Agent'),
            ]);
        });

        return redirect()->route('admin.programacion-examenes.papelera')
            ->with('success', 'Programación restaurada exitosamente.');
    }
    public function restaurarMasivo(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.programacion-examenes.papelera')
                ->with('error', 'No se seleccionaron elementos para restaurar.');
        }

        try {
            DB::transaction(function () use ($ids) {
                $registros = ProgramacionExamen::onlyTrashed()->whereIn('id', $ids)->get();

                foreach ($registros as $programacion) {
                    $deletedAtAnterior = $programacion->deleted_at;
                    $programacion->restore();

                    Auditoria::create([
                        'user_id' => Auth::id(),
                        'auditable_id'       => $programacion->id,
                        'auditable_type'     => ProgramacionExamen::class,
                        'accion'             => 'RESTORE_MASIVO',
                        'valores_anteriores' => ['deleted_at' => $deletedAtAnterior],
                        'valores_nuevos'     => ['deleted_at' => null],
                        'ip_address'         => request()->ip(),
                        'user_agent'         => request()->header('User-Agent'),
                    ]);
                }
            });

            return redirect()->route('admin.programacion-examenes.papelera')
                ->with('success', 'El lote de exámenes seleccionado fue restaurado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.papelera')
                ->with('error', 'No se pudo procesar la restauración masiva: ' . $e->getMessage());
        }
    }
}
