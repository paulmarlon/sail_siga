<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Estado;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    public function index()
    {
        $materias = Materia::with('estado')->get();
        // Solo traemos estados de contexto académico
        $estados = Estado::where('contexto', 'academico')->get();
        return view('admin.materias.index', compact('materias', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sigla'            => 'required|unique:materias,sigla',
            'nombre'           => 'required',
            'descripcion'      => 'required',
            'horas_academicas' => 'required|integer',
            'tipo_materia'     => 'required|in:Teorica,Practica,Teorica-Practica',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        Materia::create([
            'sigla'            => strtoupper($request->sigla),
            'nombre'           => strtoupper($request->nombre),
            'descripcion'      =>  strtoupper($request->descripcion),
            'horas_academicas' => $request->horas_academicas,
            'tipo_materia'     => $request->tipo_materia,
            'es_comun'         => $request->has('es_comun'),
            'estado_id'        => $request->estado_id,
        ]);

        return redirect()->route('admin.materias.index')->with('mensaje', 'Materia creada con éxito');
    }

    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'sigla'            => 'required|unique:materias,sigla,' . $materia->id,
            'nombre'           => 'required',
            'descripcion'      => 'required',
            'horas_academicas' => 'required|integer',
            'tipo_materia'     => 'required|in:Teorica,Practica,Teorica-Practica',
            'estado_id'        => 'required|exists:estados,id',
        ]);

        $materia->update([
            'sigla'            => strtoupper($request->sigla),
            'nombre'           => strtoupper($request->nombre),
            'descripcion'      => strtoupper($request->descripcion),
            'horas_academicas' => $request->horas_academicas,
            'tipo_materia'     => $request->tipo_materia,
            'es_comun'         => $request->has('es_comun'),
            'estado_id'        => $request->estado_id,
        ]);

        return redirect()->route('admin.materias.index')->with('mensaje', 'Materia actualizada con éxito');
    }

    public function destroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->route('admin.materias.index')->with('mensaje', 'Materia enviada a reciclaje');
    }

    public function papelera()
    {
        $materias = Materia::onlyTrashed()->with('estado')->get();
        $estados = Estado::where('contexto', 'academico')->get();
        return view('admin.materias.index', compact('materias', 'estados'));
    }

    public function restaurar(int $id)
    {
        $materia = Materia::onlyTrashed()->findOrFail($id);
        $materia->restore();
        return redirect()->route('admin.materias.index')->with('mensaje', 'Materia restaurada');
    }
}
