<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NivelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $niveles = Nivel::All();
        return view('admin.niveles.index', compact('niveles'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre_create' => 'required|string|max:255|unique:nivels,nombre',
        ]);

        Nivel::create([
            'nombre' => $request->nombre_create,
        ]);

        return redirect()->route('admin.niveles.index')
            ->with('mensaje', 'Nivel creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $nivel = Nivel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre_update' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('nivels', 'nombre')->ignore($nivel->id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_id', $nivel->id); // Esto reabre el modal correcto
        }

        // Ojo aquí: capturamos 'nombre_update' tal como viene de la vista
        $nivel->nombre = $request->nombre_update;
        $nivel->save();

        return redirect()->route('admin.niveles.index')
            ->with('mensaje', 'Nivel actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) // Recibimos el $id directamente
    {
        try {
            $nivel = Nivel::findOrFail($id); // Buscamos de forma segura
            $nivel->delete();

            return redirect()->route('admin.niveles.index')
                ->with('mensaje', 'Nivel eliminado exitosamente.')
                ->with('icon', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }
    public function papelera()
    {
        // Solo traemos los que tienen 'deleted_at' lleno
        $niveles = Nivel::onlyTrashed()->get();
        return view('admin.niveles.index', compact('niveles'));
    }

    public function restaurar(int $id)
    {
        $nivel = Nivel::withTrashed()->findOrFail($id);
        $nivel->restore();
        return redirect()->route('admin.niveles.index')
            ->with('mensaje', 'Nivel restaurado correctamente.')
            ->with('icon', 'success');
    }
}
