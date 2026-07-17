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
        $gestiones = Gestion::orderBy('nombre', 'ASC')->get();
        return view('admin.gestiones.index', compact('gestiones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.gestiones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //$datos = request()->all();
        //return response()->json($datos);
        $request->validate([
            'nombre' => 'required|integer|digits:4|min:2025|max:2030|unique:gestions,nombre',
        ]);
        $gestion = new Gestion();
        $gestion->nombre = $request->nombre;
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
        return view('admin.gestiones.edit', compact('gestion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gestion $gestion)
    {
        $request->validate([
            'nombre' => 'required|integer|digits:4|min:2025|max:2030|unique:gestions,nombre,' . $gestion->id,
        ]);

        $gestion->nombre = $request->nombre;
        $gestion->save();

        return redirect()->route('admin.gestiones.index')
            ->with('mensaje', 'Gestión actualizada exitosamente.')
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
