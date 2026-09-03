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
    public function index(Request $request)
    {
        $periodoId = $request->get('periodo_id', 1);
        $carreraId = $request->get('carrera_id');
        $gradoId = $request->get('grado_id');
        $turnoId = $request->get('turno_id');
        $paraleloId = $request->get('paralelo_id');
        $instanciaFiltro = $request->get('instancia');
        $busqueda = $request->get('busqueda');

        $query = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'programacionesExamen'
        ])->where('periodo_id', $periodoId);

        if ($turnoId) $query->where('turno_id', $turnoId);
        if ($paraleloId) $query->where('paralelo_id', $paraleloId);

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

        if ($instanciaFiltro) {
            if ($instanciaFiltro === 'PENDIENTE') {
                $query->whereDoesntHave('programacionesExamen');
            } else {
                $query->whereHas('programacionesExamen', function ($q) use ($instanciaFiltro) {
                    $q->where('instancia', $instanciaFiltro);
                });
            }
        }

        $listaOfertas = $query->get();

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
            'periodoId'
        ));
    }

    public function create(Request $request)
    {
        $ids = $request->has('ofertas_ids') ? $request->get('ofertas_ids', []) : [$request->get('oferta_id')];

        $listaOfertas = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'docenteActual.docente.persona',
            'programacionesExamen'
        ])->whereIn('id', array_filter($ids))->get();

        $listaPersonal = Personal::with('persona')->get();
        $ofertaSeleccionadaId = $request->get('oferta_id');
        $examenesExistentes = ($ofertaSeleccionadaId && count($listaOfertas) === 1)
            ? ProgramacionExamen::where('oferta_id', $ofertaSeleccionadaId)->get()
            : collect();

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

                    ProgramacionExamen::create($request->all());
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
        $instanciaFiltro = $request->get('instancia_filtro');

        if (empty($ids) || empty($instanciaFiltro)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Debe seleccionar ofertas académicas y especificar la instancia evaluativa.');
        }

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
                        [
                            'oferta_id' => $data['oferta_id'],
                            'instancia' => $data['instancia']
                        ],
                        [
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

    public function storeLote(Request $request)
    {
        $request->validate([
            'ofertas_ids'      => 'required|array',
            'ofertas_ids.*'    => 'exists:oferta_academicas,id',
            'instancia_filtro' => 'required|string',
            'responsable_id'   => 'required|exists:personals,id',
            'fecha_programada' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->ofertas_ids as $ofertaId) {
                    // updateOrCreate busca por oferta_id e instancia.
                    // Si existe, actualiza los datos; si no, lo crea nuevo.
                    ProgramacionExamen::updateOrCreate(
                        [
                            'oferta_id' => $ofertaId,
                            'instancia' => $request->instancia_filtro,
                        ],
                        [
                            'modalidad'             => 'directa',
                            'tipo_proceso'          => 'Ordinario',
                            'fecha_programada'      => $request->fecha_programada,
                            'responsable_id'        => $request->responsable_id,
                            'bloqueado'             => false,
                            'observaciones_legales' => $request->instructiva ?? null,
                        ]
                    );
                }
            });

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', '¡Lote de exámenes procesado correctamente!');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Error al procesar el lote: ' . $e->getMessage());
        }
    }

    public function updateLote(Request $request)
    {
        return $this->updateMasivo($request);
    }

    public function destroyLote(Request $request)
    {
        return $this->destroyMasivo($request);
    }

    public function destroyMasivo(Request $request)
    {
        $ids = $request->input('ofertas_ids', []);
        $instanciaFiltro = $request->input('instancia_filtro');

        if (empty($ids) || empty($instanciaFiltro)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Debe seleccionar elementos y especificar la instancia evaluativa.');
        }

        try {
            DB::transaction(function () use ($ids, $instanciaFiltro) {
                $bloqueadasCount = ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->where('bloqueado', true)
                    ->count();

                if ($bloqueadasCount > 0) {
                    throw new \Exception('Uno o más exámenes seleccionados se encuentran BLOQUEADOS.');
                }

                $examenesAEliminar = ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->get();

                ProgramacionExamen::whereIn('oferta_id', $ids)
                    ->where('instancia', $instanciaFiltro)
                    ->delete();

                foreach ($examenesAEliminar as $ex) {
                    Auditoria::create([
                        'user_id'            => Auth::id(),
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
                ->with('success', 'La instancia seleccionada fue enviada a la papelera con éxito.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se pudo procesar el lote: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProgramacionExamen $programacionExamen)
    {
        if ($programacionExamen->bloqueado) {
            return back()->with('error', 'Acción denegada: Este examen se encuentra bloqueado.');
        }

        $request->validate([
            'oferta_id' => 'required|exists:oferta_academicas,id',
            'instancia' => 'required|string|max:255',
            'modalidad' => 'required|string|in:directa,a_ciegas,dictada',
            'tipo_proceso' => 'required|string',
            'fecha_programada' => 'required|date',
            'responsable_id' => 'required|exists:personals,id',
        ]);

        $programacionExamen->update($request->all());

        return redirect()->route('admin.programacion-examenes.index')
            ->with('success', 'Programación actualizada correctamente.');
    }

    public function destroy(ProgramacionExamen $programacionExamen)
    {
        if ($programacionExamen->bloqueado) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se puede eliminar porque está BLOQUEADA.');
        }

        try {
            DB::transaction(function () use ($programacionExamen) {
                $valoresAnteriores = $programacionExamen->toArray();
                $programacionExamen->delete();

                Auditoria::create([
                    'user_id'            => Auth::id(),
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
                ->with('success', 'Programación enviada a la papelera.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Error al procesar: ' . $e->getMessage());
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
                'user_id'            => Auth::id(),
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
                ->with('error', 'No se seleccionaron elementos.');
        }

        try {
            DB::transaction(function () use ($ids) {
                foreach (ProgramacionExamen::onlyTrashed()->whereIn('id', $ids)->get() as $prog) {
                    $deletedAtAnterior = $prog->deleted_at;
                    $prog->restore();

                    Auditoria::create([
                        'user_id'            => Auth::id(),
                        'auditable_id'       => $prog->id,
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
                ->with('success', 'Lote restaurado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.papelera')
                ->with('error', 'Error en restauración masiva: ' . $e->getMessage());
        }
    }

    public function sellarInstancia(int $id)
    {
        ProgramacionExamen::findOrFail($id)->update(['bloqueado' => true]);
        return back()->with('success', 'Instancia sellada definitivamente.');
    }

    public function desbloquear(int $id)
    {
        ProgramacionExamen::findOrFail($id)->update(['bloqueado' => false]);
        return back()->with('success', 'Instancia desbloqueada.');
    }

    public function examenesCalificados(int $id)
    {
        $programacion = ProgramacionExamen::with(['ofertaAcademica.pensum.materia', 'foliosExamen'])->findOrFail($id);
        $folios = $programacion->foliosExamen;
        return view('admin.docente.examenes_calificados', compact('programacion', 'folios'));
    }
}
