<?php

namespace App\Http\Controllers;

use App\Models\InscripcionCarrera;
use App\Models\Estudiante;
use App\Models\Carrera;
use App\Models\Periodo;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InscripcionCarreraController extends Controller
{
    public function index()
    {
        // Trae todas las inscripciones con sus respectivas relaciones
        $inscripciones = InscripcionCarrera::with([
            'estudiante.persona',
            'carrera',
            'periodo.gestion',
            'estado',
            'registradoPor'
        ])->get();

        return view('admin.inscripcion_carreras.index', compact('inscripciones'));
    }

    public function create()
    {
        $estudiantes = Estudiante::with('persona')->get();
        $carreras = Carrera::all();
        // Cargamos los periodos incluyendo su gestión relacionada y ordenados
        $periodos = Periodo::with('gestion')->orderBy('gestion_id', 'desc')->get();
        // Filtramos opcionalmente los estados que correspondan al contexto académico
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.inscripcion_carreras.create', compact('estudiantes', 'carreras', 'periodos', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'estudiante_id'          => 'required|array|min:1',
            'estudiante_id.*'        => 'exists:estudiantes,id',
            'carrera_id'             => 'required|exists:carreras,id',
            'periodo_id'             => 'required|exists:periodos,id',
            'fecha_inscripcion'      => 'required|date',
            'es_especialidad_activa' => 'boolean',
            'estado_id'              => 'required|exists:estados,id',
        ]);

        $inscritosCount = 0;
        $omitidosCount = 0;

        foreach ($request->estudiante_id as $estudianteId) {
            try {
                $inscripcion = InscripcionCarrera::firstOrCreate(
                    [
                        'estudiante_id' => $estudianteId,
                        'carrera_id'    => $request->carrera_id,
                        'periodo_id'    => $request->periodo_id,
                    ],
                    [
                        'fecha_inscripcion'      => $request->fecha_inscripcion,
                        'es_especialidad_activa' => $request->has('es_especialidad_activa'),
                        'registrado_por_user_id' => Auth::id(),
                        'estado_id'              => $request->estado_id,
                    ]
                );

                if ($inscripcion->wasRecentlyCreated) {
                    $inscritosCount++;
                } else {
                    $omitidosCount++;
                }
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $omitidosCount++;
            }
        }

        $mensaje = "Se registraron {$inscritosCount} inscripciones correctamente.";
        if ($omitidosCount > 0) {
            $mensaje .= " ({$omitidosCount} estudiantes se omitieron por ya estar inscritos en la misma carrera y periodo).";
        }

        return redirect()->route('admin.inscripcion-carreras.index')->with([
            'mensaje' => $mensaje,
            'icon'    => $inscritosCount > 0 ? 'success' : 'warning'
        ]);
    }

    public function show(InscripcionCarrera $inscripcionCarrera)
    {
        $inscripcionCarrera->load(['estudiante.persona', 'carrera', 'periodo.gestion', 'estado', 'registradoPor']);
        return view('admin.inscripcion_carreras.show', compact('inscripcionCarrera'));
    }

    // CARGA LA VISTA PARA EDITAR UN SOLO REGISTRO (CON PERIODO Y GESTIÓN)
    public function editIndividual($id)
    {
        $inscripcionCarrera = InscripcionCarrera::with(['estudiante.persona', 'carrera', 'periodo.gestion', 'estado'])->findOrFail($id);
        $estados = Estado::where('contexto', 'academico')->get();
        $carreras = Carrera::all();

        // Cargamos los periodos incluyendo su gestión relacionada y ordenados por gestión descendente
        $periodos = Periodo::with('gestion')->orderBy('gestion_id', 'desc')->get();

        return view('admin.inscripcion_carreras.edit-individual', compact('inscripcionCarrera', 'estados', 'carreras', 'periodos'));
    }

    public function edit(InscripcionCarrera $inscripcionCarrera)
    {
        $estudiantesSeleccionadosIds = InscripcionCarrera::where('carrera_id', $inscripcionCarrera->carrera_id)
            ->where('periodo_id', $inscripcionCarrera->periodo_id)
            ->pluck('estudiante_id')
            ->toArray();

        $estudiantes = Estudiante::with('persona')->get();
        $carreras = Carrera::all();
        // Cargamos los periodos con su gestión relacionada
        $periodos = Periodo::with('gestion')->orderBy('gestion_id', 'desc')->get();
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.inscripcion_carreras.edit', compact(
            'inscripcionCarrera',
            'estudiantesSeleccionadosIds',
            'estudiantes',
            'carreras',
            'periodos',
            'estados'
        ));
    }

    // PROCESA LA ACTUALIZACIÓN INDIVIDUAL
    public function updateIndividual(Request $request, $id)
    {
        $inscripcion = InscripcionCarrera::findOrFail($id);

        $request->validate([
            'carrera_id'             => 'required|exists:carreras,id',
            'periodo_id'             => 'required|exists:periodos,id',
            'estado_id'              => 'required|exists:estados,id',
            'fecha_inscripcion'      => 'required|date',
            'es_especialidad_activa' => 'boolean',
        ]);

        $inscripcion->update([
            'carrera_id'             => $request->carrera_id,
            'periodo_id'             => $request->periodo_id,
            'estado_id'              => $request->estado_id,
            'fecha_inscripcion'      => $request->fecha_inscripcion,
            'es_especialidad_activa' => $request->has('es_especialidad_activa'),
            'registrado_por_user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.inscripcion-carreras.index')
            ->with(['mensaje' => 'Actualización individual exitosa.', 'icon' => 'success']);
    }

    public function update(Request $request, string  $id)
    {
        // Buscamos el registro de forma segura y explícita
        $inscripcionCarrera = InscripcionCarrera::findOrFail($id);

        $request->validate([
            'estudiante_id'          => 'required|array',
            'estudiante_id.*'        => 'exists:estudiantes,id',
            'carrera_id'             => 'required|exists:carreras,id',
            'periodo_id'             => 'required|exists:periodos,id',
            'fecha_inscripcion'      => 'required|date',
            'es_especialidad_activa' => 'boolean',
            'estado_id'              => 'required|exists:estados,id',
        ]);

        $carreraAnterior = $inscripcionCarrera->carrera_id;
        $periodoAnterior = $inscripcionCarrera->periodo_id;

        $nuevosEstudiantesIds = array_unique($request->estudiante_id);
        $userId = auth()->id();

        $estudiantesAnterioresIds = InscripcionCarrera::where('carrera_id', $carreraAnterior)
            ->where('periodo_id', $periodoAnterior)
            ->pluck('estudiante_id')
            ->toArray();

        $aEliminar = array_diff($estudiantesAnterioresIds, $nuevosEstudiantesIds);

        if (!empty($aEliminar)) {
            InscripcionCarrera::where('carrera_id', $carreraAnterior)
                ->where('periodo_id', $periodoAnterior)
                ->whereIn('estudiante_id', $aEliminar)
                ->delete();
        }

        foreach ($nuevosEstudiantesIds as $estudianteId) {
            $inscripcionExistente = InscripcionCarrera::where('estudiante_id', $estudianteId)
                ->where('periodo_id', $request->periodo_id)
                ->first();

            if ($inscripcionExistente) {
                $inscripcionExistente->update([
                    'carrera_id'             => $request->carrera_id,
                    'fecha_inscripcion'      => $request->fecha_inscripcion,
                    'es_especialidad_activa' => $request->has('es_especialidad_activa'),
                    'estado_id'              => $request->estado_id,
                    'registrado_por_user_id' => $userId,
                ]);
            } else {
                try {
                    InscripcionCarrera::create([
                        'estudiante_id'          => $estudianteId,
                        'carrera_id'             => $request->carrera_id,
                        'periodo_id'             => $request->periodo_id,
                        'fecha_inscripcion'      => $request->fecha_inscripcion,
                        'es_especialidad_activa' => $request->has('es_especialidad_activa'),
                        'registrado_por_user_id' => $userId,
                        'estado_id'              => $request->estado_id,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Control de concurrencia
                }
            }
        }

        return redirect()->route('admin.inscripcion-carreras.index')->with([
            'mensaje' => 'Inscripción masiva actualizada correctamente.',
            'icon'    => 'success'
        ]);
    }

    public function destroy(InscripcionCarrera $inscripcionCarrera)
    {
        try {
            // Envía la inscripción a la papelera manteniendo el historial
            $inscripcionCarrera->delete();

            return redirect()->route('admin.inscripcion-carreras.index')->with([
                'mensaje' => 'Inscripción enviada a la papelera correctamente.',
                'icon'    => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.inscripcion-carreras.index')->with([
                'mensaje' => 'Ocurrió un error al intentar eliminar la inscripción.',
                'icon'    => 'error'
            ]);
        }
    }

    public function papelera()
    {
        $inscripciones = InscripcionCarrera::onlyTrashed()->with(['estudiante.persona', 'carrera', 'periodo.gestion'])->get();
        return view('admin.inscripcion_carreras.papelera', compact('inscripciones'));
    }

    public function restaurar(string $id)
    {
        $inscripcion = InscripcionCarrera::onlyTrashed()->findOrFail($id);
        $inscripcion->restore();

        return redirect()->route('admin.inscripcion-carreras.papelera')->with([
            'mensaje' => 'Inscripción restaurada con éxito.',
            'icon'    => 'success'
        ]);
    }

    public function procesarRetiro(Request $request, InscripcionCarrera $inscripcionCarrera)
    {
        $request->validate([
            'estado_id' => 'required|exists:estados,id'
        ]);

        // Cambiamos el estado institucional y desactivamos la especialidad activa
        $inscripcionCarrera->update([
            'estado_id'              => $request->estado_id,
            'es_especialidad_activa' => false
        ]);

        return redirect()->route('admin.inscripcion-carreras.index')->with([
            'mensaje' => 'La baja del estudiante se ha procesado correctamente. Búsquelo en el listado general o ajuste el filtro de estado para visualizarlo.',
            'icon'    => 'success'
        ]);
    }
}
