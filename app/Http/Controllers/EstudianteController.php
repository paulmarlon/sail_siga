<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    public function index()
    {
        // CORREGIDO: ppffs ya trae directamente los datos de la persona, no requiere .persona
        $estudiantes = Estudiante::with(['persona', 'estado', 'ppffs'])->get();
        return view('admin.estudiantes.index', compact('estudiantes'));
    }

    public function create()
    {
        $estados = Estado::where('contexto', 'academico')->get();

        // Obtenemos la lista completa de personas para seleccionarlas en los modales
        $ppffs = Persona::all();

        return view('admin.estudiantes.create', compact('estados', 'ppffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'persona_id' => 'required|exists:personas,id|unique:estudiantes,persona_id',
            'registro_universitario' => 'nullable|string|max:50|unique:estudiantes,registro_universitario',
            'estado_id' => 'required|exists:estados,id',
            'ppff_id' => 'nullable|exists:personas,id', // Validar que el apoderado exista en personas
        ]);

        DB::transaction(function () use ($request) {
            $estudiante = Estudiante::create([
                'persona_id' => $request->persona_id,
                'registro_universitario' => $request->registro_universitario,
                'estado_id' => $request->estado_id,
            ]);

            // Asociar el apoderado directamente usando el ID de la persona
            if ($request->has('ppff_id') && !empty($request->ppff_id)) {
                if ($request->persona_id != $request->ppff_id) {
                    $estudiante->ppffs()->attach($request->ppff_id, [
                        'parentesco' => $request->parentesco ?? 'Apoderado',
                        'es_tutor_principal' => $request->es_tutor_principal ?? true,
                    ]);
                }
            }
        });

        return redirect()->route('admin.estudiantes.index')
            ->with('mensaje', 'Estudiante registrado correctamente.')
            ->with('icon', 'success');
    }

    public function show(Estudiante $estudiante)
    {
        // CORREGIDO: Ajustado a ppffs directos
        $estudiante->load(['persona.domicilio', 'estado', 'ppffs']);
        return view('admin.estudiantes.show', compact('estudiante'));
    }

    public function edit(Estudiante $estudiante)
    {
        // CORREGIDO: Ajustado a ppffs directos y traemos todas las personas para los modales de edición si los usas
        $estudiante->load(['persona', 'ppffs']);
        $estados = Estado::where('contexto', 'academico')->get();
        $ppffs = Persona::all();

        return view('admin.estudiantes.edit', compact('estudiante', 'estados', 'ppffs'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'registro_universitario' => 'nullable|string|max:50|unique:estudiantes,registro_universitario,' . $estudiante->id,
            'estado_id' => 'required|exists:estados,id',
            'ppff_id' => 'nullable|exists:personas,id',
        ]);

        DB::transaction(function () use ($request, $estudiante) {
            $estudiante->update([
                'registro_universitario' => $request->registro_universitario,
                'estado_id' => $request->estado_id,
            ]);

            // Actualizar o sincronizar el apoderado si se envía
            if ($request->filled('ppff_id')) {
                if ($estudiante->persona_id != $request->ppff_id) {
                    // Sincroniza la tabla pivote reemplazando el apoderado anterior o asignando uno nuevo
                    $estudiante->ppffs()->sync([
                        $request->ppff_id => [
                            'parentesco' => $request->parentesco ?? 'Apoderado',
                            'es_tutor_principal' => true
                        ]
                    ]);
                }
            } else {
                // Si se quitó el apoderado, desvinculamos todos
                $estudiante->ppffs()->detach();
            }
        });

        return redirect()->route('admin.estudiantes.index')
            ->with('mensaje', 'Información del estudiante actualizada exitosamente.')
            ->with('icon', 'success');
    }

    public function destroy(Estudiante $estudiante)
    {
        $estudiante->delete();
        return redirect()->route('admin.estudiantes.index')
            ->with('mensaje', 'Estudiante enviado a la papelera.')
            ->with('icon', 'success');
    }

    public function papelera()
    {
        $estudiantes = Estudiante::onlyTrashed()->with(['persona', 'estado'])->get();
        return view('admin.estudiantes.papelera', compact('estudiantes'));
    }

    public function restaurar(int $id)
    {
        $estudiante = Estudiante::onlyTrashed()->findOrFail($id);
        $estudiante->restore();

        return redirect()->route('admin.estudiantes.index')
            ->with('mensaje', 'Estudiante restaurado correctamente.')
            ->with('icon', 'success');
    }
}
