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

class ProgramacionExamenController extends Controller
{
    public function index(Request $request)
    {
        $periodoId = $request->get('periodo_id');
        $carreraId = $request->get('carrera_id');
        $gradoId = $request->get('grado_id');
        $turnoId = $request->get('turno_id');
        $paraleloId = $request->get('paralelo_id');
        $instanciaFiltro = $request->get('instancia');
        $busqueda = $request->get('busqueda');

        // Consulta base partiendo de OfertaAcademica
        $query = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'programacionesExamen'
        ]);

        if ($periodoId) {
            $query->where('periodo_id', $periodoId);
        }

        if ($turnoId) {
            $query->where('turno_id', $turnoId);
        }

        if ($paraleloId) {
            $query->where('paralelo_id', $paraleloId);
        }

        if ($carreraId || $gradoId) {
            $query->whereHas('pensum', function ($q) use ($carreraId, $gradoId) {
                if ($carreraId) {
                    $q->where('carrera_id', $carreraId);
                }
                if ($gradoId) {
                    $q->where('grado_id', $gradoId);
                }
            });
        }

        if ($busqueda) {
            $query->whereHas('pensum.materia', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('sigla', 'like', "%{$busqueda}%");
            });
        }

        if ($instanciaFiltro) {
            $query->whereHas('programacionesExamen', function ($q) use ($instanciaFiltro) {
                $q->where('instancia', $instanciaFiltro);
            });
        }

        $listaOfertas = $query->get();

        $periodos = Periodo::all();
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
            'paralelos'
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
            'docenteActual.docente.persona'
        ])->whereIn('id', array_filter($ids))->get();

        $listaPersonal = Personal::with('persona')->get();

        $ofertaSeleccionadaId = $request->get('oferta_id');

        // Capturamos la instancia, observaciones y la fecha sugerida (con la fecha de hoy por defecto)
        $instanciaSugerida = $request->get('instancia_sugerida', 'P1');
        $observacionesSugeridas = $request->get('observaciones_sugeridas');
        $fechaSugerida = $request->get('fecha_sugerida', now()->format('Y-m-d\TH:i'));

        $examenesExistentes = collect();
        if ($ofertaSeleccionadaId && count($listaOfertas) === 1) {
            $examenesExistentes = ProgramacionExamen::where('oferta_id', $ofertaSeleccionadaId)->get();
        }

        return view('admin.programacion_examenes.create', compact(
            'listaOfertas',
            'listaPersonal',
            'ofertaSeleccionadaId',
            'examenesExistentes',
            'instanciaSugerida',
            'observacionesSugeridas',
            'fechaSugerida'
        ));
    }

    public function store(Request $request)
    {
        if ($request->has('programaciones')) {
            foreach ($request->programaciones as $data) {
                // Usamos updateOrCreate para buscar si ya existe la oferta y la instancia.
                // Si existe, la actualiza; si no, la crea.
                ProgramacionExamen::updateOrCreate(
                    [
                        'oferta_id' => $data['oferta_id'],
                        'instancia' => $data['instancia'],
                    ],
                    [
                        'modalidad'             => $data['modalidad'] ?? 'directa',
                        'tipo_proceso'          => $data['tipo_proceso'] ?? 'Ordinario',
                        'fecha_programada'      => $data['fecha_programada'],
                        'responsable_id'        => $data['responsable_id'],
                        'bloqueado'             => false,
                        'observaciones_legales' => $data['observaciones'] ?? null,
                    ]
                );
            }
            return redirect()->route('admin.programacion-examenes.index')->with('success', 'Programación masiva exitosa.');
        }

        $request->validate([
            'oferta_id' => 'required|exists:oferta_academicas,id',
            'instancia' => 'required|string',
        ]);

        // También aplicamos updateOrCreate en el modo individual por seguridad
        ProgramacionExamen::updateOrCreate(
            [
                'oferta_id' => $request->oferta_id,
                'instancia' => $request->instancia,
            ],
            [
                'modalidad'             => $request->modalidad,
                'tipo_proceso'          => $request->tipo_proceso,
                'fecha_programada'      => $request->fecha_programada,
                'responsable_id'        => $request->responsable_id,
                'bloqueado'             => $request->has('bloqueado'),
                'observaciones_legales' => $request->observaciones,
            ]
        );

        return redirect()->route('admin.programacion-examenes.index')->with('success', 'Examen programado correctamente.');
    }

    public function show(ProgramacionExamen $programacionExamen)
    {
        $programacionExamen->load(['ofertaAcademica.pensum.materia', 'responsable.persona']);
        return view('admin.programacion_examenes.show', compact('programacionExamen'));
    }

    public function editMasivo(Request $request)
    {
        $ids = $request->get('ofertas_ids', []);
        $instanciaSeleccionada = $request->get('instancia_a_editar', 'P1');

        if (empty($ids)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se seleccionaron ofertas académicas para editar.');
        }

        $listaOfertas = OfertaAcademica::with([
            'pensum.materia',
            'pensum.carrera',
            'pensum.grado',
            'turno',
            'paralelo',
            'programacionesExamen' => function ($query) use ($instanciaSeleccionada) {
                $query->where('instancia', $instanciaSeleccionada);
            }
        ])->whereIn('id', $ids)->get();

        $listaPersonal = Personal::with('persona')->get();

        return view('admin.programacion_examenes.edit', compact('listaOfertas', 'listaPersonal', 'instanciaSeleccionada'));
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

        foreach ($request->programaciones as $data) {
            ProgramacionExamen::updateOrCreate(
                [
                    'id' => $data['programacion_id'] ?? null,
                ],
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

        return redirect()->route('admin.programacion-examenes.index')
            ->with('success', 'Actualización masiva de exámenes realizada correctamente.');
    }

    public function destroyMasivo(Request $request)
    {
        $ids = $request->input('ofertas_ids', []);
        $instancia = $request->input('instancia_a_eliminar', 'TODAS');

        if (empty($ids)) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se seleccionaron elementos para eliminar en lote.');
        }

        $clientIp = $request->ip();
        $userAgent = $request->header('User-Agent');

        try {
            $query = ProgramacionExamen::whereIn('oferta_id', $ids);

            if ($instancia !== 'TODAS') {
                $query->where('instancia', $instancia);
            }

            $bloqueadasCount = (clone $query)->where('bloqueado', true)->count();

            if ($bloqueadasCount > 0) {
                return redirect()->route('admin.programacion-examenes.index')
                    ->with('error', 'No se puede procesar el lote: uno o más exámenes seleccionados se encuentran BLOQUEADOS (notas cerradas o auditadas).');
            }

            $examenesAEliminar = $query->get();

            if ($examenesAEliminar->isEmpty()) {
                return redirect()->route('admin.programacion-examenes.index')
                    ->with('error', 'No se encontraron registros activos para las instancias y materias seleccionadas.');
            }

            $query->delete();

            foreach ($examenesAEliminar as $ex) {
                Auditoria::create([
                    'user_id'            => Auth::id(),
                    'auditable_id'       => $ex->id,
                    'auditable_type'     => ProgramacionExamen::class,
                    'accion'             => 'SOFT_DELETE_MASIVO',
                    'valores_anteriores' => (object) $ex->toArray(),
                    'valores_nuevos'     => (object) ['deleted_at' => now()->toDateTimeString()],
                    'ip_address'         => $clientIp,
                    'user_agent'         => $userAgent,
                ]);
            }

            return redirect()->route('admin.programacion-examenes.index')
                ->with('success', 'El lote de exámenes seleccionado fue enviado a la papelera correctamente con registro de auditoría.');
        } catch (\Exception $e) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'Ocurrió un error al procesar la eliminación masiva: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProgramacionExamen $programacionExamen)
    {
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

        $programacionExamen->update([
            'oferta_id' => $request->oferta_id,
            'instancia' => $request->instancia,
            'modalidad' => $request->modalidad,
            'tipo_proceso' => $request->tipo_proceso,
            'fecha_programada' => $request->fecha_programada,
            'responsable_id' => $request->responsable_id,
            'bloqueado' => $request->has('bloqueado'),
            'observaciones_legales' => $request->observaciones_legales,
        ]);

        return redirect()->route('admin.programacion-examenes.index')
            ->with('success', 'Programación de examen actualizada correctamente.');
    }

    public function destroy(Request $request, ProgramacionExamen $programacionExamen)
    {
        if ($programacionExamen->bloqueado) {
            return redirect()->route('admin.programacion-examenes.index')
                ->with('error', 'No se puede eliminar la programación porque se encuentra BLOQUEADA (notas cerradas o auditadas).');
        }

        $clientIp = $request->ip();
        $userAgent = $request->header('User-Agent');

        try {
            $valoresAnteriores = $programacionExamen->toArray();
            $programacionExamen->delete();

            Auditoria::create([
                'user_id'            => Auth::id(),
                'auditable_id'       => $programacionExamen->id,
                'auditable_type'     => ProgramacionExamen::class,
                'accion'             => 'SOFT_DELETE',
                'valores_anteriores' => (object) $valoresAnteriores,
                'valores_nuevos'     => (object) ['deleted_at' => now()->toDateTimeString()],
                'ip_address'         => $clientIp,
                'user_agent'         => $userAgent,
            ]);

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
            ->with([
                'ofertaAcademica.pensum.materia',
                'responsable.persona'
            ])
            ->paginate(10);

        return view('admin.programacion_examenes.papelera', compact('eliminados'));
    }

    public function restaurar(Request $request, string $id)
    {
        $programacion = ProgramacionExamen::onlyTrashed()->findOrFail($id);
        $deletedAtAnterior = $programacion->deleted_at;
        $programacion->restore();

        Auditoria::create([
            'user_id'            => Auth::id(),
            'auditable_id'       => $programacion->id,
            'auditable_type'     => ProgramacionExamen::class,
            'accion'             => 'RESTORE',
            'valores_anteriores' => (object) ['deleted_at' => $deletedAtAnterior],
            'valores_nuevos'     => (object) ['deleted_at' => null],
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->header('User-Agent'),
        ]);

        return redirect()->route('admin.programacion-examenes.papelera')
            ->with('success', 'Programación restaurada exitosamente.');
    }
}
