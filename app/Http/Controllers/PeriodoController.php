<?php

namespace App\Http\Controllers;

use App\Models\{Periodo, Gestion, Estado};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodos = Periodo::with(['gestion', 'estado'])->get();
        $gestiones = Gestion::all();

        // Filtrar solo los estados cuyo contexto sea 'academico'
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.periodos.index', compact('periodos', 'gestiones', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_create' => 'required|string|max:255',
            'gestion_id'    => 'required|exists:gestions,id',
            'estado_id' => [
                'required',
                'exists:estados,id',
                function ($attribute, $value, $fail) {
                    $esAcademico = \App\Models\Estado::where('id', $value)
                        ->where('contexto', 'academico')
                        ->exists();
                    if (!$esAcademico) {
                        $fail('El estado seleccionado no es válido para procesos académicos.');
                    }
                },
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
        ]);

        // Usamos el estado_id que viene del formulario validado
        Periodo::create([
            'nombre'       => strtoupper($request->nombre_create),
            'gestion_id'   => $request->gestion_id,
            'estado_id'    => $request->estado_id, // Usar el valor del request
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,
        ]);

        return redirect()->route('admin.periodos.index')
            ->with('mensaje', 'Periodo creado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Periodo $periodo)
    {
        $request->merge([
            'nombre_update' => strtoupper($request->nombre_update),
        ]);

        // 1. Validamos incluyendo estado_id y la restricción de contexto
        $validator = Validator::make($request->all(), [
            'nombre_update' => 'required|string|max:255|unique:periodos,nombre,' . $periodo->id,
            'gestion_id'    => 'required|exists:gestions,id',
            'estado_id'     => [
                'required',
                'exists:estados,id',
                function ($attribute, $value, $fail) {
                    $esAcademico = \App\Models\Estado::where('id', $value)
                        ->where('contexto', 'academico')
                        ->exists();
                    if (!$esAcademico) {
                        $fail('El estado seleccionado no es válido.');
                    }
                },
            ],
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_id', $periodo->id);
        }

        // 2. Actualizamos incluyendo estado_id
        $periodo->nombre = $request->nombre_update;
        $periodo->gestion_id = $request->gestion_id;
        $periodo->estado_id = $request->estado_id; // <--- ¡Esto faltaba!
        $periodo->fecha_inicio = $request->fecha_inicio;
        $periodo->fecha_fin = $request->fecha_fin;
        $periodo->save();

        return redirect()->route('admin.periodos.index')
            ->with('mensaje', 'Periodo actualizado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Periodo $periodo)
    {
        try {
            // SoftDelete: Llena la columna deleted_at
            $periodo->delete();

            return redirect()->route('admin.periodos.index')
                ->with('mensaje', 'Periodo enviado a la papelera exitosamente.')
                ->with('icon', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }

    public function papelera()
    {
        $periodos = Periodo::onlyTrashed()->with('gestion')->get();
        $gestiones = Gestion::all();
        $estados = \App\Models\Estado::where('contexto', 'academico')->get(); // <--- ESTO FALTA
        return view('admin.periodos.index', compact('periodos', 'gestiones', 'estados'));
    }

    public function restaurar(int $id)
    {
        $periodo = Periodo::withTrashed()->findOrFail($id);
        $periodo->restore();

        return redirect()->route('admin.periodos.index')
            ->with('mensaje', 'Periodo restaurado correctamente.')
            ->with('icon', 'success');
    }
}
