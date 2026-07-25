<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Grado;
use App\Models\Materia;
use App\Models\Pensum;
use Illuminate\Http\Request;

class PensumController extends Controller
{
    public function index(Request $request)
    {
        $carrera_id = $request->input('carrera_id', Carrera::first()->id);
        $carrera = Carrera::findOrFail($carrera_id);
        $carreras = Carrera::all();

        // 1. Mostrar grados según el tipo de carrera
        if ($carrera->es_tronco_comun) {
            $grados = Grado::where('ciclo', 1)->orderBy('orden')->get();
        } else {
            $grados = Grado::orderBy('orden')->get();
        }

        $materias_disponibles = Materia::all();

        // 2. Consulta corregida para especialidades y tronco común
        $pensumsQuery = Pensum::with(['materia', 'carrera']);

        if (!$carrera->es_tronco_comun) {
            $carreraTroncoComun = Carrera::where('es_tronco_comun', true)->first();

            $pensumsQuery->where(function ($query) use ($carrera_id, $carreraTroncoComun) {
                // Trae tanto lo propio de la especialidad COMO lo del tronco común
                $query->where('carrera_id', $carrera_id);

                if ($carreraTroncoComun) {
                    $query->orWhere('carrera_id', $carreraTroncoComun->id);
                }
            });
        } else {
            $pensumsQuery->where('carrera_id', $carrera_id);
        }

        $pensums = $pensumsQuery->get()->groupBy('grado_id');

        // 3. Grados bloqueados (ciclo 1 si es especialidad)
        $gradosBloqueadosIds = [];
        if (!$carrera->es_tronco_comun) {
            $gradosBloqueadosIds = Grado::where('ciclo', 1)->pluck('id')->toArray();
        }

        return view('admin.pensums.index', compact(
            'carrera',
            'carreras',
            'grados',
            'materias_disponibles',
            'pensums',
            'gradosBloqueadosIds'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'materia_id' => 'required|exists:materias,id',
            'grado_id'   => 'required|exists:grados,id',
        ]);

        $grado = Grado::findOrFail($validated['grado_id']);
        $carrera = Carrera::findOrFail($validated['carrera_id']);

        if (!$carrera->es_tronco_comun && $grado->ciclo == 1) {
            return response()->json(['message' => 'Acción no permitida en ciclos de tronco común.'], 403);
        }

        try {
            // Buscamos un estado activo por defecto para cumplir con la llave foránea
            $estadoPorDefecto = \App\Models\Estado::first();
            $estadoId = $estadoPorDefecto ? $estadoPorDefecto->id : 1;

            $pensum = Pensum::withTrashed()
                ->where('carrera_id', $validated['carrera_id'])
                ->where('materia_id', $validated['materia_id'])
                ->first();

            if ($pensum) {
                if ($pensum->trashed()) {
                    $pensum->restore();
                }

                $pensum->update([
                    'grado_id' => $validated['grado_id'],
                    'estado_id' => $estadoId,
                    'es_obligatoria' => true
                ]);
            } else {
                $pensum = Pensum::create([
                    'carrera_id' => $validated['carrera_id'],
                    'materia_id' => $validated['materia_id'],
                    'grado_id'   => $validated['grado_id'],
                    'estado_id'  => $estadoId,
                    'es_obligatoria' => true
                ]);
            }

            return response()->json([
                'id' => $pensum->id,
                'message' => 'Materia asignada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGrado(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pensums,id',
            'grado_id' => 'required|exists:grados,id',
            'carrera_contexto_id' => 'required|exists:carreras,id'
        ]);

        $pensum = Pensum::findOrFail($request->id);
        $carreraContexto = Carrera::findOrFail($request->carrera_contexto_id);
        $nuevoGrado = Grado::findOrFail($request->grado_id);

        if (!$carreraContexto->es_tronco_comun) {
            if ($pensum->carrera_id != $carreraContexto->id) {
                return response()->json(['message' => 'No puedes mover materias heredadas del tronco común.'], 403);
            }
            if ($nuevoGrado->ciclo == 1) {
                return response()->json(['message' => 'No puedes reubicar materias en el ciclo bloqueado de tronco común.'], 403);
            }
        }

        $pensum->update(['grado_id' => $request->grado_id]);

        return response()->json(['message' => 'Posición actualizada correctamente']);
    }

    public function destroy(Request $request, int $id)
    {
        $pensum = Pensum::findOrFail($id);
        $carreraContextoId = $request->input('carrera_contexto_id');
        $carreraContexto = Carrera::findOrFail($carreraContextoId);

        if (!$carreraContexto->es_tronco_comun && $pensum->carrera_id != $carreraContextoId) {
            return response()->json(['message' => 'No puedes eliminar materias que pertenecen al tronco común base.'], 403);
        }

        $pensum->delete();

        return response()->json(['message' => 'Materia removida con éxito']);
    }
}
