<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Estado;
use App\Models\Nivel;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function index()
    {
        $grados = Grado::with(['estado', 'nivel'])->get();
        $niveles = Nivel::all();
        $estados = Estado::where('contexto', 'academico')->get();
        return view('admin.grados.index', compact('grados', 'niveles', 'estados'));
    }

    public function papelera()
    {
        $grados = Grado::onlyTrashed()->with(['estado', 'nivel'])->get();
        // Necesitamos niveles y estados para que los modales de creación sigan funcionando en la vista index
        $niveles = Nivel::all();
        $estados = Estado::where('contexto', 'academico')->get();
        return view('admin.grados.index', compact('grados', 'niveles', 'estados'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer',
            'ciclo' => 'required|integer',
            'nivel_id' => 'required|exists:nivels,id',
            'estado_id' => 'required|exists:estados,id',
        ]);

        Grado::create($validated);
        return redirect()->route('admin.grados.index')->with('mensaje', 'Grado creado correctamente');
    }

    public function update(Request $request, int $id)
    {
        // 1. Validar los datos
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'orden'     => 'required|integer',
            'ciclo'     => 'required|integer',
            'nivel_id'  => 'required|exists:nivels,id', // O 'niveles', verifica el nombre de tu tabla
            'estado_id' => 'required|exists:estados,id',
        ]);

        // 2. Buscar y actualizar
        $grado = Grado::findOrFail($id);
        $grado->update($validated);

        // 3. Retornar con mensaje
        return redirect()->route('admin.grados.index')->with('mensaje', 'Grado actualizado con éxito');
    }

    public function destroy(int $id)
    {
        Grado::findOrFail($id)->delete();
        return redirect()->route('admin.grados.index')->with('mensaje', 'Grado enviado a papelera');
    }

    public function restaurar(int $id)
    {
        Grado::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.grados.index')->with('mensaje', 'Grado restaurado');
    }
}
