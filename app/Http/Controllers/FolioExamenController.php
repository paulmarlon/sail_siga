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
                // Limpiar folios previos de esta programación para sobreescribir limpiamente
                FolioExamen::where('programacion_id', $programacion->id)->delete();

                foreach ($foliosData as $posicion => $ruEstudiante) {
                    if (!empty($ruEstudiante)) {
                        $estudiante = \App\Models\Estudiante::where('registro_universitario', $ruEstudiante)->first();

                        if ($estudiante) {
                            FolioExamen::create([
                                'programacion_id' => $programacion->id,
                                'estudiante_id'   => $estudiante->id,
                                'codigo_folio'    => 'FOLIO-' . str_pad($posicion, 3, '0', STR_PAD_LEFT),
                                'estado_folio'    => 'Foliado_Y_Separado'
                            ]);
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
                'message' => $e->getMessage()
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
            'folios.*.id'           => 'required|exists:folio_examens,id',
            'folios.*.codigo_folio' => 'required|string|max:100',
            'folios.*.nota'         => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->folios as $data) {
                    $folio = FolioExamen::findOrFail($data['id']);

                    $nuevaNota = $data['nota'] !== null && $data['nota'] !== '' ? $data['nota'] : null;
                    $nuevoEstado = $nuevaNota !== null ? 'Calificado' : 'Foliado_Y_Separado';

                    $folio->update([
                        'codigo_folio' => $data['codigo_folio'],
                        'nota'         => $nuevaNota,
                        'estado_folio' => $nuevoEstado
                    ]);
                }
            });

            return redirect()->route('admin.folio-examens.plantilla', $programacionId)
                ->with('success', '¡Folios y calificaciones guardados correctamente!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar los datos (verifique que el código de folio no esté repetido): ' . $e->getMessage());
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
}
