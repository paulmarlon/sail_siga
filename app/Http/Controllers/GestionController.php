<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;

class GestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Usamos with('estado') para traer la información del estado junto con la gestión
        $gestiones = Gestion::with('estado')->orderBy('nombre', 'ASC')->get();

        return view('admin.gestiones.index', compact('gestiones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estados = \App\Models\Estado::where('contexto', 'academico')->get();

        return view('admin.gestiones.create', compact('estados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|integer|digits:4|min:2025|max:2030|unique:gestions,nombre',
        ]);

        $gestion = new Gestion();
        $gestion->nombre = $request->nombre;
        $gestion->estado_id = 1; // Asigna el ID del estado "Activo" o el que prefieras
        $gestion->save();

        return redirect()->route('admin.gestiones.index')
            ->with('mensaje', 'Gestión creada exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gestion $gestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gestion $gestion)
    {
        $estados = \App\Models\Estado::where('contexto', 'academico')->get();
        return view('admin.gestiones.edit', compact('gestion', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gestion $gestion)
    {
        $request->validate([
            'nombre' => 'required|integer|digits:4|min:2025|max:2030|unique:gestions,nombre,' . $gestion->id,
            // Si vas a permitir cambiar el estado desde el formulario:
            'estado_id' => 'required|exists:estados,id',
        ]);

        $gestion->nombre = $request->nombre;
        $gestion->estado_id = $request->estado_id; // <-- Asigna el valor
        $gestion->save();

        return redirect()->route('admin.gestiones.index')
            ->with('mensaje', 'Gestión actualizada.')
            ->with('icon', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gestion $gestion)
    {
        $gestion->delete();
        return redirect()->route('admin.gestiones.index')
            ->with('mensaje', 'Gestión eliminada exitosamente.')
            ->with('icon', 'success');
    }

    public function papelera()
    {
        $gestiones = Gestion::onlyTrashed()->get();
        return view('admin.gestiones.index', compact('gestiones')); // O una vista nueva: index_papelera
    }

    // Restaurar un registro
    public function restaurar(int $id)
    {
        $gestion = Gestion::withTrashed()->findOrFail($id);
        $gestion->restore();
        return redirect()->route('admin.gestiones.index')->with('mensaje', 'Gestión restaurada correctamente.');
    }
}
