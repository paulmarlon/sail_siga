<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Personal;
use App\Models\User;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personals = Personal::with(['persona', 'usuario.roles', 'estado'])->get();
        return view('admin.personal.index', compact('personals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create(?string $tipo = 'docente') // <-- Añadimos el signo de interrogación y un valor por defecto
    {
        $roles = Role::all();
        // Filtramos solo los estados que pertenecen al contexto laboral
        $estados = Estado::where('contexto', 'laboral')->get();

        return view('admin.personal.create', compact('tipo', 'roles', 'estados'));
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
                Rule::unique('personals', 'persona_id')->withoutTrashed(), // Evita registrar a la misma persona dos veces como personal
            ],
            'rol' => 'required',
            'profesion' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'tipo' => 'required|string',
            'estado_id' => 'required|exists:estados,id',
        ]);

        return DB::transaction(function () use ($request) {

            // 1. Obtenemos la persona ya existente en el sistema
            $persona = Persona::findOrFail($request->persona_id);

            // 2. Crear el Usuario del sistema vinculado a esa persona
            $usuario = User::create([
                'persona_id' => $persona->id,
                'email' => $request->email,
                'password' => Hash::make($persona->ci), // Contraseña por defecto el carnet de identidad
            ]);

            // Asignamos el rol mediante Spatie
            $usuario->assignRole(trim($request->rol));

            // 3. Crear el registro de Personal vinculando todo
            Personal::create([
                'persona_id' => $persona->id,
                'usuario_id' => $usuario->id,
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
        $personal = Personal::with(['persona.domicilio', 'usuario.roles', 'estado'])->findOrFail($id);
        return view('admin.personal.show', compact('personal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $personal = Personal::with(['persona', 'usuario', 'estado'])->findOrFail($id);
        $roles = Role::all();
        $estados = Estado::where('contexto', 'laboral')->get();

        return view('admin.personal.edit', compact('personal', 'roles', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $personal = Personal::with(['persona', 'usuario'])->findOrFail($id);
        $usuarioId = $personal->usuario?->id;

        $request->validate([
            'rol' => 'required',
            'profesion' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $usuarioId,
            'estado_id' => 'required|exists:estados,id',
        ]);

        return DB::transaction(function () use ($request, $personal) {

            // 1. Actualizar el Usuario del sistema si existe
            if ($personal->usuario) {
                $usuario = $personal->usuario;
                $usuario->email = $request->email;
                $usuario->save();
                $usuario->syncRoles(trim($request->rol));
            }

            // 2. Actualizar los datos propios del Personal (profesión y estado laboral)
            $personal->update([
                'profesion' => $request->profesion,
                'estado_id' => $request->estado_id,
            ]);

            return redirect()->route('admin.personal.index', $personal->tipo)
                ->with('mensaje', 'El personal ' . $personal->tipo . ' se ha actualizado correctamente')
                ->with('icono', 'success');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $personal = Personal::with(['persona', 'usuario'])->findOrFail($id);
        $tipo = $personal->tipo;

        return DB::transaction(function () use ($personal, $tipo) {
            // Nota: Con SoftDeletes NO eliminamos la persona biográfica principal
            // porque puede seguir existiendo como tutor, estudiante o en otros módulos.
            // Solo damos de baja el acceso (usuario) y el cargo (personal).

            // 1. Eliminación lógica del usuario del sistema (si lo tiene)
            if ($personal->usuario) {
                $personal->usuario->delete();
            }

            // 2. Eliminación lógica del registro de personal
            $personal->delete();

            return redirect()->route('admin.personal.index', $tipo)
                ->with('mensaje', 'El personal ' . $tipo . ' se ha enviado a la papelera correctamente')
                ->with('icono', 'success');
        });
    }

    /**
     * Display a listing of trashed (soft-deleted) resources.
     */
    public function trashed(string $tipo)
    {
        $personals = Personal::onlyTrashed()
            ->with(['persona' => function ($query) {
                $query->withTrashed();
            }, 'usuario' => function ($query) {
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
        return DB::transaction(function () use ($id) {
            $personal = Personal::onlyTrashed()->with(['persona', 'usuario'])->findOrFail($id);
            $tipo = $personal->tipo;

            // 1. Restaurar el usuario del sistema asociado (si fue eliminado)
            if ($personal->usuario && $personal->usuario->trashed()) {
                $personal->usuario->restore();
            }

            // 2. Restaurar el registro principal de personal
            $personal->restore();

            return redirect()->route('admin.personal.index', $tipo)
                ->with('mensaje', 'El personal ' . $tipo . ' se ha restaurado correctamente')
                ->with('icono', 'success');
        });
    }
}
