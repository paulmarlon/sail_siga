<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Estado;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CarreraController extends Controller
{
    /**
     * Listado optimizado. Agregamos un filtro opcional para la papelera.
     */
    public function index(Request $request)
    {
        $query = Carrera::with(['estado', 'nivel', 'carreraBase']);

        $carreras = $request->has('papelera')
            ? $query->onlyTrashed()->get()
            : $query->get();

        $estados = Estado::where('contexto', 'academico')->get();
        $niveles = Nivel::all();
        $carrerasBase = Carrera::all();

        return view('admin.carreras.index', compact('carreras', 'estados', 'niveles', 'carrerasBase'));
    }

    /**
     * Refactorizamos la validación para reutilizarla entre store y update.
     */
    private function validateCarrera(Request $request, $carreraId = null)
    {
        return $request->validate([
            'sigla'           => ['required', Rule::unique('carreras', 'sigla')->ignore($carreraId)],
            'nombre'          => 'required|string|max:255',
            'resolucion'      => 'nullable|string|max:255',
            'duracion'        => 'required|integer',
            'titulo'          => 'required|string|max:255',
            'estado_id'       => 'required|exists:estados,id',
            'nivel_id'        => 'required|exists:nivels,id',
            'carrera_base_id' => 'nullable|exists:carreras,id',
            'es_tronco_comun' => 'nullable|boolean',
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'sigla'  => mb_strtoupper($request->sigla),
            'nombre' => mb_strtoupper($request->nombre),
            'titulo' => mb_strtoupper($request->titulo),
        ]);
        $validated = $this->validateCarrera($request);

        // Asegurar el booleano
        $validated['es_tronco_comun'] = $request->has('es_tronco_comun');

        DB::transaction(function () use ($validated) {
            Carrera::create($validated);
        });

        return redirect()->route('admin.carreras.index')->with('success', 'Carrera registrada.');
    }

    public function update(Request $request, Carrera $carrera)
    {
        $request->merge([
            'sigla'  => mb_strtoupper($request->sigla),
            'nombre' => mb_strtoupper($request->nombre),
            'titulo' => mb_strtoupper($request->titulo),
        ]);
        // 1. Validar
        $validated = $this->validateCarrera($request, $carrera->id);

        // 2. Asegurar el booleano
        $validated['es_tronco_comun'] = $request->has('es_tronco_comun');

        // 3. Actualizar
        $carrera->update($validated);

        return redirect()->route('admin.carreras.index')->with('success', 'Carrera actualizada.');
    }

    public function destroy(Carrera $carrera)
    {
        // Verificar si existen carreras hijas (especialidades) que dependan de esta carrera base
        $tieneEspecialidades = Carrera::where('carrera_base_id', $carrera->id)->exists();

        if ($tieneEspecialidades) {
            return redirect()->route('admin.carreras.index')
                ->with('mensaje', 'No se puede enviar a la papelera esta carrera porque tiene especialidades dependiendo de ella.')
                ->with('icon', 'error');
        }

        $carrera->delete();
        return redirect()->route('admin.carreras.index')->with('mensaje', 'Carrera enviada a papelera exitosamente.')->with('icon', 'success');
    }

    public function restaurar(int $id)
    {
        $carrera = Carrera::onlyTrashed()->findOrFail($id);
        $carrera->restore();
        return redirect()->route('admin.carreras.index')->with('success', 'Carrera restaurada.');
    }
}
