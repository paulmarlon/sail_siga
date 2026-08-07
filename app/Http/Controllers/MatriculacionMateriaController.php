<?php

namespace App\Http\Controllers;

use App\Models\MatriculacionMateria;
use App\Models\Estudiante;
use App\Models\OfertaAcademica;
use App\Models\{Estado, Periodo, Turno, Paralelo, Pensum, Carrera, Grado};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculacionMateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Listamos las matriculaciones activas con sus relaciones principales
        $matriculaciones = MatriculacionMateria::with(['estudiante.persona', 'oferta.pensum.materia', 'estado'])->get();

        return view('admin.matriculacion_materias.index', compact('matriculaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estudiantes = Estudiante::with('persona')->get();
        $carreras = Carrera::all();
        $grados = Grado::all();          // <-- ¡AQUÍ ESTÁ LA SOLUCIÓN! Pasa los grados a la vista

        $periodos = Periodo::with('gestion')->get()->map(function ($p) {
            $p->nombre_completo = $p->nombre . ' - ' . ($p->gestion->nombre ?? 'S/G');
            return $p;
        });
        $turnos = Turno::all();
        $paralelos = Paralelo::all();
        $estados = Estado::where('contexto', 'academico')->get(); // o como manejes tus estados
        $ofertas = OfertaAcademica::with(['pensum.carrera', 'pensum.grado', 'pensum.materia', 'periodo', 'turno', 'paralelo'])->get();

        return view('admin.matriculacion_materias.create', compact(
            'estudiantes',
            'carreras',
            'grados',     // <-- Inclúyelo en el compact
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

            // Bucle anidado para matricular a cada estudiante seleccionado en cada materia elegida
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
    public function show(MatriculacionMateria $matriculacionMateria)
    {
        $matriculacionMateria->load(['estudiante.persona', 'oferta.pensum.materia', 'oferta.periodo', 'estado']);
        return view('admin.matriculacion_materias.show', compact('matriculacionMateria'));
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
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(MatriculacionMateria $matriculacionMateria)
    {
        // Esto realiza un Soft Delete gracias al trait SoftDeletes en el modelo
        $matriculacionMateria->delete();

        return redirect()->route('admin.matriculacion-materias.index')
            ->with('success', 'Matriculación enviada a la papelera correctamente.');
    }

    /**
     * Display a listing of soft deleted resources (Papelera).
     */
    public function papelera()
    {
        $matriculaciones = MatriculacionMateria::onlyTrashed()
            ->with(['estudiante.persona', 'oferta.pensum.materia', 'estado'])
            ->get();

        return view('admin.matriculacion_materias.papelera', compact('matriculaciones'));
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
