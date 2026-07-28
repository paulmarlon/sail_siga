<?php

namespace App\Http\Controllers;

use App\Models\OfertaAcademica;
use App\Models\Periodo;
use App\Models\Paralelo;
use App\Models\Turno;
use App\Models\Pensum;
use App\Models\Estado;
use Illuminate\Http\Request;

class OfertaAcademicaController extends Controller
{
    public function index()
    {
        $ofertas = OfertaAcademica::with([
            'periodo',
            'paralelo',
            'turno',
            'pensum.materia',
            'pensum.carrera',
            'estado'
        ])->get();

        return view('admin.oferta_academica.index', compact('ofertas'));
    }

    public function create()
    {
        $periodos = Periodo::all();
        $paralelos = Paralelo::all();
        $turnos = Turno::all();
        $pensums = Pensum::with(['materia', 'carrera'])->get();
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.oferta_academica.create', compact('periodos', 'paralelos', 'turnos', 'pensums', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'paralelo_id' => 'required|exists:paralelos,id',
            'turno_id' => 'required|exists:turnos,id',
            'pensum_id' => 'required|array|min:1', // Validamos que sea un arreglo con al menos 1 elemento
            'pensum_id.*' => 'exists:pensums,id',    // Validamos que cada ID exista en la tabla pensums
            'cupo_maximo' => 'required|integer|min:1',
            'estado_id' => 'required|exists:estados,id',
        ]);

        // Iteramos sobre cada ID de pensum seleccionado en la vista multicolumna
        foreach ($request->pensum_id as $pensumId) {
            OfertaAcademica::create([
                'periodo_id'   => $request->periodo_id,
                'paralelo_id'  => $request->paralelo_id,
                'turno_id'     => $request->turno_id,
                'pensum_id'    => $pensumId,
                'cupo_maximo'  => $request->cupo_maximo,
                'estado_id'    => $request->estado_id,
            ]);
        }

        return redirect()->route('admin.oferta-academica.index')
            ->with('success', 'Ofertas académicas creadas masivamente con éxito.');
    }

    public function show(OfertaAcademica $ofertaAcademica)
    {
        $ofertaAcademica->load(['periodo', 'paralelo', 'turno', 'pensum.materia', 'pensum.carrera', 'estado']);

        return view('admin.oferta_academica.show', compact('ofertaAcademica'));
    }

    public function edit(OfertaAcademica $ofertaAcademica)
    {
        $periodos = Periodo::all();
        $paralelos = Paralelo::all();
        $turnos = Turno::all();
        $pensums = Pensum::with(['materia', 'carrera'])->get();
        $estados = Estado::all();

        // Variables de selección actual para los selects
        $periodoActualId = $ofertaAcademica->periodo_id;
        $turnoActualId = $ofertaAcademica->turno_id;
        $paraleloActualId = $ofertaAcademica->paralelo_id;

        // Variables de contexto visual y de grupo
        $grupoPeriodo = $ofertaAcademica->periodo->nombre ?? 'N/A';
        $grupoTurno = $ofertaAcademica->turno->nombre ?? 'N/A';
        $grupoParalelo = $ofertaAcademica->paralelo->nombre ?? 'N/A';
        $identificadorGrupo = $ofertaAcademica->id;

        $ofertasDelGrupo = OfertaAcademica::with(['pensum.materia', 'pensum.carrera'])
            ->where('periodo_id', $ofertaAcademica->periodo_id)
            ->where('turno_id', $ofertaAcademica->turno_id)
            ->where('paralelo_id', $ofertaAcademica->paralelo_id)
            ->get();

        return view('admin.oferta_academica.edit', compact(
            'ofertaAcademica',
            'periodos',
            'paralelos',
            'turnos',
            'pensums',
            'estados',
            'periodoActualId',
            'turnoActualId',
            'paraleloActualId',
            'grupoPeriodo',
            'grupoTurno',
            'grupoParalelo',
            'identificadorGrupo',
            'ofertasDelGrupo'
        ));
    }

    public function update(Request $request, OfertaAcademica $ofertaAcademica)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'paralelo_id' => 'required|exists:paralelos,id',
            'turno_id' => 'required|exists:turnos,id',
            'pensum_id' => 'required|exists:pensums,id',
            'cupo_maximo' => 'required|integer|min:1',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $ofertaAcademica->update($request->all());

        return redirect()->route('admin.oferta-academica.index')
            ->with('success', 'Oferta académica actualizada exitosamente.');
    }

    public function destroy(OfertaAcademica $ofertaAcademica)
    {
        $ofertaAcademica->delete();

        return redirect()->route('admin.oferta-academica.index')
            ->with('success', 'Oferta académica enviada a la papelera.');
    }

    public function papelera()
    {
        $ofertas = OfertaAcademica::onlyTrashed()->with([
            'periodo',
            'paralelo',
            'turno',
            'pensum.materia',
            'pensum.carrera',
            'estado'
        ])->paginate(10);

        return view('admin.oferta_academica.papelera', compact('ofertas'));
    }

    public function restaurar(int $id)
    {
        $oferta = OfertaAcademica::onlyTrashed()->findOrFail($id);
        $oferta->restore();

        return redirect()->route('admin.oferta-academica.papelera')
            ->with('success', 'Oferta académica restaurada exitosamente.');
    }
}
