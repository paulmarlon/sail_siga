<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Personal;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(?string $tipo = null)
    {
        $query = Personal::with(['persona', 'estado']);

        // Si la ruta especifica un tipo (ej. docente, administrativo), filtramos
        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $personals = $query->get();

        return view('admin.personal.index', compact('personals', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(?string $tipo = 'docente')
    {
        $estados = Estado::where('contexto', 'laboral')->get();

        return view('admin.personal.create', compact('tipo', 'estados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'persona_id' => [
                'required',
                'exists:personas,id',
                Rule::unique('personals', 'persona_id')->withoutTrashed(),
            ],
            'tipo' => 'required|string',
            'profesion' => 'nullable|string|max:100',
            'estado_id' => 'required|exists:estados,id',
        ]);

        return DB::transaction(function () use ($request) {

            Personal::create([
                'persona_id' => $request->persona_id,
                'tipo' => $request->tipo,
                'profesion' => $request->profesion,
                'estado_id' => $request->estado_id,
            ]);

            return redirect()->route('admin.personal.index', $request->tipo)
                ->with('mensaje', 'El personal ' . $request->tipo . ' se ha registrado correctamente')
                ->with('icono', 'success');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $personal = Personal::with(['persona.domicilio', 'estado'])->findOrFail($id);
        return view('admin.personal.show', compact('personal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $personal = Personal::with(['persona', 'estado'])->findOrFail($id);
        $estados = Estado::where('contexto', 'laboral')->get();

        return view('admin.personal.edit', compact('personal', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $personal = Personal::findOrFail($id);

        $request->validate([
            'tipo' => 'required|string',
            'profesion' => 'nullable|string|max:100',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $personal->update([
            'tipo' => $request->tipo,
            'profesion' => $request->profesion,
            'estado_id' => $request->estado_id,
        ]);

        return redirect()->route('admin.personal.index', $personal->tipo)
            ->with('mensaje', 'El personal ' . $personal->tipo . ' se ha actualizado correctamente')
            ->with('icono', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $personal = Personal::findOrFail($id);
        $tipo = $personal->tipo;

        $personal->delete();

        return redirect()->route('admin.personal.index', $tipo)
            ->with('mensaje', 'El personal ' . $tipo . ' se ha enviado a la papelera correctamente')
            ->with('icono', 'success');
    }

    /**
     * Display a listing of trashed (soft-deleted) resources.
     */
    public function trashed(string $tipo)
    {
        $personals = Personal::onlyTrashed()
            ->with(['persona' => function ($query) {
                $query->withTrashed();
            }, 'estado'])
            ->where('tipo', $tipo)
            ->get();

        return view('admin.personal.trashed', compact('personals', 'tipo'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $personal = Personal::onlyTrashed()->findOrFail($id);
        $tipo = $personal->tipo;

        $personal->restore();

        return redirect()->route('admin.personal.index', $tipo)
            ->with('mensaje', 'El personal ' . $tipo . ' se ha restaurado correctamente')
            ->with('icono', 'success');
    }
}
