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
        $pensums = Pensum::with(['materia', 'carrera', 'grado'])->get();
        $estados = Estado::where('contexto', 'academico')->get();

        return view('admin.oferta_academica.create', compact('periodos', 'paralelos', 'turnos', 'pensums', 'estados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'paralelo_id' => 'required|exists:paralelos,id',
            'turno_id' => 'required|exists:turnos,id',
            'pensum_id' => 'required|array|min:1',
            'pensum_id.*' => 'exists:pensums,id',
            'cupo_maximo' => 'required|integer|min:1',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $duplicadas = 0;
        $creadas = 0;

        foreach ($request->pensum_id as $pensumId) {
            $existe = OfertaAcademica::where('pensum_id', $pensumId)
                ->where('periodo_id', $request->periodo_id)
                ->where('turno_id', $request->turno_id)
                ->where('paralelo_id', $request->paralelo_id)
                ->exists();

            if ($existe) {
                $duplicadas++;
                continue; // Salta esta materia y sigue con la siguiente
            }

            OfertaAcademica::create([
                'periodo_id'   => $request->periodo_id,
                'paralelo_id'  => $request->paralelo_id,
                'turno_id'     => $request->turno_id,
                'pensum_id'    => $pensumId,
                'cupo_maximo'  => $request->cupo_maximo,
                'estado_id'    => $request->estado_id,
            ]);

            $creadas++;
        }

        // CASO 1: NINGUNA SE CREÓ (Todas estaban duplicadas) -> Se queda en el create
        if ($creadas === 0 && $duplicadas > 0) {
            return redirect()->back()
                ->withInput()
                ->with('mensaje', "No se registró ninguna oferta. Las {$duplicadas} materias seleccionadas ya existen en este periodo, turno y paralelo.")
                ->with('icon', 'warning');
        }

        // CASO 2: CREACIÓN MIXTA (Algunas nuevas, algunas duplicadas) -> Avanza al index
        if ($creadas > 0 && $duplicadas > 0) {
            return redirect()->route('admin.oferta-academica.index')
                ->with('mensaje', "Se crearon {$creadas} ofertas exitosamente. Se omitieron {$duplicadas} materias duplicadas.")
                ->with('icon', 'warning');
        }

        // CASO 3: TODO OK (Todas creadas sin duplicados) -> Avanza al index
        return redirect()->route('admin.oferta-academica.index')
            ->with('mensaje', "Se crearon {$creadas} ofertas académicas exitosamente.")
            ->with('icon', 'success');
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
        $pensums = Pensum::with(['materia', 'carrera', 'grado'])->get();
        $estados = Estado::all();

        $periodoActualId = $ofertaAcademica->periodo_id;
        $turnoActualId = $ofertaAcademica->turno_id;
        $paraleloActualId = $ofertaAcademica->paralelo_id;

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
            'pensum_id' => 'required|array|min:1',
            'pensum_id.*' => 'exists:pensums,id',
            'cupo_maximo' => 'required|integer|min:1',
            'estado_id' => 'required|exists:estados,id',
        ]);

        // 1. Identificar el grupo original al que pertenecía este registro antes de editar
        $periodoAnterior = $ofertaAcademica->periodo_id;
        $turnoAnterior = $ofertaAcademica->turno_id;
        $paraleloAnterior = $ofertaAcademica->paralelo_id;

        // 2. Obtener los IDs actuales que el usuario envió en la vista
        $nuevosPensumIds = $request->pensum_id;

        // 3. Validar duplicados cruzados con OTROS grupos existentes
        foreach ($nuevosPensumIds as $pensumId) {
            $existeDuplicado = OfertaAcademica::where('pensum_id', $pensumId)
                ->where('periodo_id', $request->periodo_id)
                ->where('turno_id', $request->turno_id)
                ->where('paralelo_id', $request->paralelo_id)
                // Excluimos a todos los miembros que formaban parte de este mismo grupo originalmente
                ->whereNot(function ($query) use ($periodoAnterior, $turnoAnterior, $paraleloAnterior) {
                    $query->where('periodo_id', $periodoAnterior)
                        ->where('turno_id', $turnoAnterior)
                        ->where('paralelo_id', $paraleloAnterior);
                })
                ->exists();

            if ($existeDuplicado) {
                return redirect()->back()
                    ->withInput()
                    ->with('mensaje', 'No se pudo actualizar. Una de las materias seleccionadas ya existe en otro grupo con el mismo periodo, turno y paralelo.')
                    ->with('icon', 'warning');
            }
        }

        // 4. Sincronización del grupo: Eliminar los registros anteriores de este grupo y recrearlos con los nuevos datos
        OfertaAcademica::where('periodo_id', $periodoAnterior)
            ->where('turno_id', $turnoAnterior)
            ->where('paralelo_id', $paraleloAnterior)
            ->delete();

        foreach ($nuevosPensumIds as $pensumId) {
            OfertaAcademica::create([
                'periodo_id'  => $request->periodo_id,
                'paralelo_id' => $request->paralelo_id,
                'turno_id'    => $request->turno_id,
                'pensum_id'   => $pensumId,
                'cupo_maximo' => $request->cupo_maximo,
                'estado_id'   => $request->estado_id,
            ]);
        }

        return redirect()->route('admin.oferta-academica.index')
            ->with('mensaje', 'Oferta académica actualizada y sincronizada exitosamente.')
            ->with('icon', 'success');
    }

    public function destroy(OfertaAcademica $ofertaAcademica)
    {
        $ofertaAcademica->delete();

        return redirect()->route('admin.oferta-academica.index')
            ->with('mensaje', 'Oferta académica enviada a la papelera.')
            ->with('icon', 'success');
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
            ->with('mensaje', 'Oferta académica restaurada exitosamente.')
            ->with('icon', 'success');
    }
}
