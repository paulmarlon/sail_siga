<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $request->merge([
            'nombre_create' => strtoupper($request->nombre_create),
        ]);

        // Validamos 'nombre_create' que es el name del input en tu modal de creación
        $request->validate([
            'nombre_create' => 'required|string|max:255|unique:nivels,nombre',
        ]);

        $nivel = new Nivel();
        $nivel->nombre = $request->nombre_create; // Guardamos el valor
        $nivel->save();

        return redirect()->route('admin.niveles.index')
            ->with('mensaje', 'Nivel creado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nivel $nivel)
    {
        $request->merge([
            'nombre_update' => strtoupper($request->nombre_update),
        ]);
        // Validamos 'nombre_update' que es el name del input en tu modal de edición
        $validator = Validator::make($request->all(), [
            'nombre_update' => 'required|string|max:255|unique:nivels,nombre,' . $nivel->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_id', $nivel->id); // Esto activará el script JS que abre el modal correcto
        }

        $nivel->nombre = $request->nombre_update; // Actualizamos con el nuevo nombre
        $nivel->save();

        return redirect()->route('admin.niveles.index')
            ->with('mensaje', 'Nivel actualizado exitosamente.')
            ->with('icon', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nivel $nivel)
    {
        try {
            // Al usar SoftDeletes, el método delete() simplemente marca la fecha
            // en la columna 'deleted_at' sin borrar el registro de la DB.
            $nivel->delete();

            // Si estás trabajando con Livewire o una respuesta JSON:
            return redirect()->route('admin.niveles.index')
                ->with('mensaje', 'Nivel eliminado exitosamente.')
                ->with('icon', 'success');
        } catch (\Exception $e) {
            // En caso de error, siempre es bueno registrarlo o notificarlo
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
