<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TurnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $turnos = Turno::all();
        return view('admin.turnos.index', compact('turnos'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre_create' => strtoupper($request->nombre_create),
        ]);

        $request->validate([
            'nombre_create' => 'required|string|max:255|unique:turnos,nombre',
        ]);

        $turno = new Turno();
        $turno->nombre = $request->nombre_create;
        $turno->save();

        return redirect()->route('admin.turnos.index')
            ->with('mensaje', 'Turno creado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turno $turno)
    {
        $request->merge([
            'nombre_update' => strtoupper($request->nombre_update),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre_update' => 'required|string|max:255|unique:turnos,nombre,' . $turno->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_id', $turno->id);
        }

        $turno->nombre = $request->nombre_update;
        $turno->save();

        return redirect()->route('admin.turnos.index')
            ->with('mensaje', 'Turno actualizado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turno $turno)
    {
        try {
            $turno->delete();
            return redirect()->route('admin.turnos.index')
                ->with('mensaje', 'Turno enviado a la papelera.')
                ->with('icon', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }

    public function papelera()
    {
        $turnos = Turno::onlyTrashed()->get();
        return view('admin.turnos.index', compact('turnos'));
    }

    public function restaurar(int $id)
    {
        $turno = Turno::withTrashed()->findOrFail($id);
        $turno->restore();
        return redirect()->route('admin.turnos.index')
            ->with('mensaje', 'Turno restaurado correctamente.')
            ->with('icon', 'success');
    }
}
