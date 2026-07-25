<?php

namespace App\Http\Controllers;

use App\Models\Paralelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParaleloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paralelos = Paralelo::all();
        return view('admin.paralelos.index', compact('paralelos'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre_create' => strtoupper($request->nombre_create),
        ]);

        $request->validate([
            'nombre_create' => 'required|string|max:255|unique:paralelos,nombre',
        ]);

        $paralelo = new Paralelo();
        $paralelo->nombre = $request->nombre_create;
        $paralelo->save();

        return redirect()->route('admin.paralelos.index')
            ->with('mensaje', 'Paralelo creado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paralelo $paralelo)
    {
        $request->merge([
            'nombre_update' => strtoupper($request->nombre_update),
        ]);

        $validator = Validator::make($request->all(), [
            'nombre_update' => 'required|string|max:255|unique:paralelos,nombre,' . $paralelo->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_id', $paralelo->id);
        }

        $paralelo->nombre = $request->nombre_update;
        $paralelo->save();

        return redirect()->route('admin.paralelos.index')
            ->with('mensaje', 'Paralelo actualizado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paralelo $paralelo)
    {
        try {
            $paralelo->delete();
            return redirect()->route('admin.paralelos.index')
                ->with('mensaje', 'Paralelo enviado a la papelera.')
                ->with('icon', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }

    public function papelera()
    {
        $paralelos = Paralelo::onlyTrashed()->get();
        return view('admin.paralelos.index', compact('paralelos'));
    }

    public function restaurar(int $id)
    {
        $paralelo = Paralelo::withTrashed()->findOrFail($id);
        $paralelo->restore();
        return redirect()->route('admin.paralelos.index')
            ->with('mensaje', 'Paralelo restaurado correctamente.')
            ->with('icon', 'success');
    }
}
