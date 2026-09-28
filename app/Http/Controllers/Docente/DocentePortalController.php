<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionExamen;
use App\Models\FolioExamen;
use App\Models\OfertaAcademica;
use App\Models\Personal;
use App\Models\MatriculacionMateria;
use App\Models\CalificacionParcial;
use App\Models\CalificacionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocentePortalController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $personal = Personal::where('persona_id', $user->persona_id)->first();
        $personalId = $personal->id ?? null;

        if ($user->hasRole('Administrador')) {
            $ofertasAsignadas = OfertaAcademica::with([
                'pensum.materia',
                'pensum.carrera',
                'pensum.grado',
                'turno',
                'paralelo',
                'periodo.gestion',
                'programacionesExamen.folios',
                'docenteActual.docente.persona'
            ])->get();
        } else {
            if (!$personalId) {
                return back()->with('error', 'Su usuario no está vinculado a un registro de Personal docente.');
            }

            $ofertasAsignadas = OfertaAcademica::whereHas('historialDocentes', function ($q) use ($personalId) {
                $q->where('docente_id', $personalId)
                    ->whereNull('fecha_fin');
            })
                ->with([
                    'pensum.materia',
                    'pensum.carrera',
                    'pensum.grado',
                    'turno',
                    'paralelo',
                    'periodo.gestion',
                    'programacionesExamen.folios',
                    'docenteActual.docente.persona'
                ])
                ->get();
        }

        $periodos = \App\Models\Periodo::with('gestion')->get();
        $carreras = \App\Models\Carrera::all();
        $grados = \App\Models\Grado::all();

        return view('docente.dashboard', compact('ofertasAsignadas', 'periodos', 'carreras', 'grados'));
    }

    public function llenarNotas(ProgramacionExamen $programacion)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $personalId = Personal::where('persona_id', $user->persona_id)->value('id');

        // Control de acceso
        if (!$user->hasRole('Administrador')) {
            $esSuOferta = $programacion->ofertaAcademica->historialDocentes()
                ->where('docente_id', $personalId)
                ->whereNull('fecha_fin')
                ->exists();

            if (!$esSuOferta) {
                abort(403, 'No tiene autorización para calificar esta programación de examen.');
            }
        }

        $programacion->load([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.pensum.carrera',
            'ofertaAcademica.paralelo',
            'ofertaAcademica.periodo.gestion',
            'ofertaAcademica.turno',
            'ofertaAcademica.matriculaciones.estudiante.persona'
        ]);

        $esTrabajoPractico = str_contains(strtoupper($programacion->instancia), 'TP');

        // =========================================================================
        // BIFURCACIÓN SEGÚN LA MODALIDAD O SI ES TRABAJO PRÁCTICO
        // =========================================================================
        if ($esTrabajoPractico) {
            $nroParcialMeta = 1;
            preg_match('/\d+/', $programacion->instancia, $matches);
            if (isset($matches[0])) {
                $nroParcialMeta = (int)$matches[0];
            }

            $tipoComponente = 'trabajo_practico';
            $estadoVigenteId = DB::table('estados')->where('slug', 'vigente')->where('contexto', 'academico')->value('id') ?? 1;

            // Asegurar que cada estudiante matriculado tenga su CalificacionDetalle para este TP
            foreach ($programacion->ofertaAcademica->matriculaciones as $mat) {
                $calificacionParcial = CalificacionParcial::firstOrCreate(
                    [
                        'matriculacion_id' => $mat->id,
                        'nro_parcial'      => $nroParcialMeta,
                    ],
                    [
                        'configuracion_parcial_id' => $programacion->configuracion_parcial_id ?? 1,
                        'ponderacion_parcial'      => 30.00,
                        'estado_id'                => $estadoVigenteId,
                    ]
                );

                // Solución: Usamos firstOrCreate separando las condiciones de búsqueda
                // para evitar insertar 'null' en una columna con restricciones Not Null en PostgreSQL.
                CalificacionDetalle::firstOrCreate(
                    [
                        'calificacion_parcial_id' => $calificacionParcial->id,
                        'tipo_componente'         => $tipoComponente,
                    ],
                    [
                        'ponderacion_componente'  => 25.00,
                        'nota'                    => 0.00, // Valor por defecto inicial para cumplir con la DB
                        'modalidad_origen'        => 'directa',
                        'estado_id'               => $estadoVigenteId,
                    ]
                );
            }

            // Consultamos la lista de detalles con sus estudiantes listos para la vista
            $folios = CalificacionDetalle::whereHas('calificacionParcial', function ($q) use ($programacion, $nroParcialMeta) {
                $q->where('nro_parcial', $nroParcialMeta)
                    ->whereHas('matriculacion', function ($subQ) use ($programacion) {
                        $subQ->where('oferta_id', $programacion->oferta_id);
                    });
            })
                ->where('tipo_componente', $tipoComponente)
                ->with(['calificacionParcial.matriculacion.estudiante.persona'])
                ->get();

            // Reutilizamos la misma vista compacta de notas directas/detalles
            return view('docente.folio_examens.llenar-notas', compact('programacion', 'folios'));
        }

        if (isset($programacion->modalidad) && $programacion->modalidad === 'directa') {
            $matriculaciones = MatriculacionMateria::with([
                'estudiante.persona',
                'calificacionesParciales' => function ($q) use ($programacion) {
                    $q->where('nro_parcial', $programacion->nro_parcial ?? 1);
                }
            ])
                ->where('oferta_id', $programacion->oferta_id)
                ->get();

            return view('docente.folio_examens.llenar-notas-directa', compact('programacion', 'matriculaciones'));
        }

        // Modalidad a Ciegas tradicional por folios
        $folios = FolioExamen::where('programacion_id', $programacion->id)
            ->orderBy('codigo_folio', 'asc')
            ->get();

        return view('docente.folio_examens.llenar-notas', compact('programacion', 'folios'));
    }

    public function guardarNotas(Request $request, ProgramacionExamen $programacion)
    {
        if ($programacion->bloqueado) {
            return response()->json([
                'success' => false,
                'message' => 'Esta evaluación está bloqueada por la administración.'
            ], 403);
        }

        if (isset($programacion->modalidad) && $programacion->modalidad === 'directa') {
            return $this->guardarNotasDirectasProceso($request, $programacion);
        }

        return $this->guardarNotasACiegasProceso($request, $programacion);
    }

    private function guardarNotasACiegasProceso(Request $request, ProgramacionExamen $programacion)
    {
        $notasData = $request->input('notas', []);
        $observacionesData = $request->input('observaciones', []);
        $userId = Auth::id();

        try {
            DB::beginTransaction();
            $cambiosRealizados = 0;
            foreach ($notasData as $folioId => $notaVal) {
                $folio = FolioExamen::where('id', $folioId)
                    ->where('programacion_id', $programacion->id)
                    ->first();

                if (!$folio) continue;


                $notaAsignada = ($notaVal !== '' && $notaVal !== null) ? $notaVal : null;
                $nuevoEstado = $notaAsignada !== null ? 'Calificado' : 'Foliado_Y_Separado';

                $folio->nota = $notaAsignada;
                $folio->estado_folio = $nuevoEstado;
                $folio->observacion = $observacionesData[$folioId] ?? $folio->observacion;

                if ($folio->isDirty(['nota', 'estado_folio', 'observacion'])) {
                    $folio->calificado_por_user_id = $userId;
                    $folio->save();
                    $cambiosRealizados++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "¡Calificaciones guardadas! Se actualizaron {$cambiosRealizados} cambios."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar notas: ' . $e->getMessage()
            ], 422);
        }
    }

    private function guardarNotasDirectasProceso(Request $request, ProgramacionExamen $programacion)
    {
        $notasData = $request->input('notas', []); // [ detalle_id o matriculacion_id => nota ]
        $observacionesData = $request->input('observaciones', []);
        $userId = Auth::id();

        $esTrabajoPractico = str_contains(strtoupper($programacion->instancia), 'TP');
        $tipoComponente = $esTrabajoPractico ? 'trabajo_practico' : 'examen';

        // Determinar número de parcial
        $nroParcial = $programacion->nro_parcial ?? 1;
        if ($esTrabajoPractico) {
            preg_match('/\d+/', $programacion->instancia, $matches);
            $nroParcial = isset($matches[0]) ? (int)$matches[0] : 1;
        }

        // Obtener carrera para buscar su configuración meta de parcial
        $carreraId = $programacion->ofertaAcademica->pensum->carrera_id ?? null;

        $configParcialMeta = DB::table('configuracion_parcial_meta')
            ->where('nro_parcial', $nroParcial)
            ->where(function ($q) use ($carreraId) {
                $q->where('carrera_id', $carreraId)->orWhereNull('carrera_id');
            })
            ->first();

        $configParcialId = $configParcialMeta->id ?? 1;
        $ponderacionParcial = $configParcialMeta->ponderacion_parcial ?? 30.00;

        // Obtener ponderación del componente
        $componenteMeta = DB::table('configuracion_componente_meta')
            ->where('configuracion_parcial_id', $configParcialId)
            ->where('tipo_componente', $tipoComponente)
            ->first();

        $ponderacionComponente = $componenteMeta->ponderacion_componente ?? ($esTrabajoPractico ? 25.00 : 75.00);

        $estadoVigenteId = DB::table('estados')
            ->where('slug', 'vigente')
            ->where('contexto', 'academico')
            ->value('id') ?? 1;

        try {
            DB::beginTransaction();
            $cambiosRealizados = 0;

            foreach ($notasData as $idKey => $notaVal) {
                $nuevaNota = ($notaVal !== '' && $notaVal !== null) ? $notaVal : null;
                $observacionActual = $observacionesData[$idKey] ?? 'Actualizado por modalidad directa';

                if ($esTrabajoPractico) {
                    // Si viene desde la vista de trabajos prácticos, $idKey es el ID del CalificacionDetalle
                    $detalle = CalificacionDetalle::find($idKey);
                    if (!$detalle) continue;

                    $detalle->nota = $nuevaNota;
                    $detalle->registrado_por_user_id = $userId;
                    $detalle->observacion = $observacionActual;

                    if ($detalle->isDirty('nota')) {
                        $detalle->save();
                        $cambiosRealizados++;
                    }

                    $calificacionParcial = $detalle->calificacionParcial;
                } else {
                    // Si viene por matriculación directa
                    $matriculacionId = $idKey;

                    $calificacionParcial = CalificacionParcial::firstOrCreate(
                        [
                            'matriculacion_id' => $matriculacionId,
                            'nro_parcial'      => $nroParcial,
                        ],
                        [
                            'configuracion_parcial_id' => $configParcialId,
                            'ponderacion_parcial'      => $ponderacionParcial,
                            'estado_id'                => $estadoVigenteId,
                        ]
                    );

                    $detalle = CalificacionDetalle::updateOrCreate(
                        [
                            'calificacion_parcial_id' => $calificacionParcial->id,
                            'tipo_componente'         => $tipoComponente,
                        ],
                        [
                            'ponderacion_componente'  => $ponderacionComponente,
                            'nota'                    => $nuevaNota,
                            'modalidad_origen'        => 'directa',
                            'estado_id'               => $estadoVigenteId,
                            'registrado_por_user_id'  => $userId,
                            'observacion'             => $observacionActual,
                        ]
                    );

                    if ($detalle->wasRecentlyCreated || $detalle->wasChanged()) {
                        $cambiosRealizados++;
                    }
                }

                // Recalcular la nota ponderada total del parcial
                if ($calificacionParcial) {
                    $detallesTotales = $calificacionParcial->detalles()->where('estado_id', $estadoVigenteId)->get();
                    $suma = 0;
                    foreach ($detallesTotales as $det) {
                        if ($det->nota !== null) {
                            $suma += ($det->nota * ($det->ponderacion_componente / 100));
                        }
                    }
                    $calificacionParcial->nota_parcial_calculada = round($suma, 2);
                    $calificacionParcial->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "¡Notas guardadas con éxito! Se actualizaron {$cambiosRealizados} registros."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar notas directas: ' . $e->getMessage()
            ], 422);
        }
    }
}
