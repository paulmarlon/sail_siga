<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionExamen;
use App\Models\FolioExamen;
use App\Models\OfertaAcademica;
use App\Models\Personal; // <--- Asegúrate de tener importado el modelo Personal
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocentePortalController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Buscamos el registro de Personal usando la persona_id del usuario logueado (respetando tus conexiones actuales)
        $personal = Personal::where('persona_id', $user->persona_id)->first();
        $personalId = $personal->id ?? null;

        // Si es Administrador, puede ver todas las ofertas académicas que tengan programaciones
        if ($user->hasRole('Administrador')) {
            $ofertasAsignadas = OfertaAcademica::with([
                'pensum.materia',
                'pensum.carrera',
                'pensum.grado',
                'turno',
                'paralelo',
                'periodo.gestion',
                'programacionesExamen.folios', // <--- IMPORTANTE: Preca
                'docenteActual.docente.persona'
            ])
                ->has('programacionesExamen')
                ->get();
        } else {
            // Si es Docente (o cualquier otro rol que no sea Admin)
            if (!$personalId) {
                return back()->with('error', 'Su usuario no está vinculado a un registro de Personal docente.');
            }

            // Filtramos estrictamente usando la relación 'historialDocentes'
            $ofertasAsignadas = OfertaAcademica::whereHas('historialDocentes', function ($q) use ($personalId) {
                $q->where('docente_id', $personalId)
                    ->whereNull('fecha_fin');
            })
                ->has('programacionesExamen')
                ->with([
                    'pensum.materia',
                    'pensum.carrera',
                    'pensum.grado',
                    'turno',
                    'paralelo',
                    'periodo.gestion',
                    'programacionesExamen',
                    'docenteActual.docente.persona'
                ])
                ->get();
        }

        return view('docente.dashboard', compact('ofertasAsignadas'));
    }

    public function llenarNotas(ProgramacionExamen $programacion)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Obtener el personal_id utilizando la relación persona_id de la arquitectura actual
        $personalId = Personal::where('persona_id', $user->persona_id)->value('id');

        // Control de acceso: Verificar que el docente sea el asignado actualmente
        if (!$user->hasRole('Administrador')) {
            $esSuOferta = $programacion->ofertaAcademica->historialDocentes()
                ->where('docente_id', $personalId)
                ->whereNull('fecha_fin')
                ->exists();

            if (!$esSuOferta) {
                abort(403, 'No tiene autorización para calificar esta programación de examen.');
            }
        }

        // Cargar relaciones extendidas para la vista
        $programacion->load([
            'ofertaAcademica.pensum.materia',
            'ofertaAcademica.paralelo',
            'ofertaAcademica.periodo.gestion',
            'ofertaAcademica.turno',
            'ofertaAcademica.historialDocentes.docente.persona'
        ]);

        // Cargar los folios de evaluación para la programación seleccionada
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

                $nuevaNota = ($notaVal !== '' && $notaVal !== null) ? $notaVal : null;
                $nuevoEstado = $nuevaNota !== null ? 'Calificado' : 'Foliado_Y_Separado';
                $nuevaObservacion = $observacionesData[$folioId] ?? $folio->observacion;

                $folio->nota = $nuevaNota;
                $folio->estado_folio = $nuevoEstado;
                $folio->observacion = $nuevaObservacion;

                if ($folio->isDirty(['nota', 'estado_folio', 'observacion'])) {
                    if ($folio->isDirty('nota') && $folio->getOriginal('nota') !== null) {
                        $notaAnterior = $folio->getOriginal('nota');
                        $fechaModificacion = now()->format('d/m/Y H:i');
                        $trazaAuditoria = "[Modificado el {$fechaModificacion} - Nota anterior: {$notaAnterior}]";

                        if (!empty($folio->observacion)) {
                            $folio->observacion = $trazaAuditoria . " " . $folio->observacion;
                        } else {
                            $folio->observacion = $trazaAuditoria;
                        }
                    }

                    $folio->calificado_por_user_id = $userId;
                    $folio->save();
                    $cambiosRealizados++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "¡Calificaciones guardadas! Se actualizaron {$cambiosRealizados} cambios reales."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar notas: ' . $e->getMessage()
            ], 422);
        }
    }
}
