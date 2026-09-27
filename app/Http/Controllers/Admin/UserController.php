<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use App\Models\Grado;
use App\Models\Gestion;
use App\Models\Periodo;
use App\Models\Turno;
use App\Models\Paralelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Muestra la lista de usuarios con filtros académicos y de roles.
     */
    public function index(Request $request)
    {
        $query = User::with([
            'persona.personal.historialDocente.ofertaAcademica',
            'persona.estudiante.matriculacionesMaterias.oferta',
            'roles'
        ]);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $aplicarFiltros = function ($q) use ($request) {
            if ($request->filled('gestion_id')) {
                $q->whereHas('periodo', function ($sub) use ($request) {
                    $sub->where('gestion_id', $request->gestion_id);
                });
            }
            if ($request->filled('periodo_id')) {
                $q->where('periodo_id', $request->periodo_id);
            }
            if ($request->filled('turno_id')) {
                $q->where('turno_id', $request->turno_id);
            }
            if ($request->filled('paralelo_id')) {
                $q->where('paralelo_id', $request->paralelo_id);
            }
        };

        if ($request->hasAny(['gestion_id', 'periodo_id', 'turno_id', 'paralelo_id'])) {
            $query->where(function ($q) use ($aplicarFiltros) {
                $q->orWhereHas('persona.personal.historialDocente.ofertaAcademica', $aplicarFiltros);
                $q->orWhereHas('persona.estudiante.matriculacionesMaterias.oferta', $aplicarFiltros);
            });
        }

        $usuarios = $query
            ->join('personas', 'users.persona_id', '=', 'personas.id')
            ->orderBy('personas.ap_paterno', 'asc')
            ->orderBy('personas.ap_materno', 'asc')
            ->orderBy('personas.nombres', 'asc')
            ->select('users.*')
            ->get();

        $roles     = Role::all();
        $gestions  = Gestion::all();
        $periodos  = Periodo::all();
        $turnos    = Turno::all();
        $paralelos = Paralelo::all();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'gestions', 'periodos', 'turnos', 'paralelos'));
    }

    public function showChangePasswordForm()
    {
        return view('admin.usuarios.cambiar-password-obligatorio');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Muestra la vista de creación y habilitación masiva de accesos.
     */
    public function create(Request $request)
    {
        $rolesDisponibles = Role::all();
        $roles     = $rolesDisponibles;
        $gestions  = Gestion::all();
        $periodos  = Periodo::all();
        $grados    = Grado::all();
        $turnos    = Turno::all();
        $paralelos = Paralelo::all();

        $tiposPersonalDB = \App\Models\Personal::select('tipo')
            ->distinct()
            ->whereNotNull('tipo')
            ->pluck('tipo')
            ->toArray();

        $tiposPersonaFiltro = array_unique(array_merge(['estudiante'], $tiposPersonalDB));
        $tipoSeleccionado = $request->input('tipo');

        $personasSinUsuario = Persona::query()
            ->whereDoesntHave('user')
            ->when($tipoSeleccionado, function ($query) use ($tipoSeleccionado) {
                if ($tipoSeleccionado === 'estudiante') {
                    $query->whereHas('estudiante');
                } else {
                    $query->whereHas('personal', function ($q) use ($tipoSeleccionado) {
                        $q->where('tipo', $tipoSeleccionado);
                    });
                }
            })
            ->when($request->filled('gestion_id') || $request->filled('periodo_id') || $request->filled('turno_id') || $request->filled('paralelo_id'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {

                    // 1. El primero DEBE ser whereHas (no orWhereHas)
                    $q->whereHas('estudiante.matriculacionesMaterias.oferta', function ($sub) use ($request) {
                        if ($request->filled('gestion_id')) {
                            $sub->whereHas('periodo', fn($p) => $p->where('gestion_id', $request->gestion_id));
                        }
                        if ($request->filled('periodo_id')) {
                            $sub->where('periodo_id', $request->periodo_id);
                        }
                        if ($request->filled('turno_id')) {
                            $sub->where('turno_id', $request->turno_id);
                        }
                        if ($request->filled('paralelo_id')) {
                            $sub->where('paralelo_id', $request->paralelo_id);
                        }
                    });

                    // 2. Los siguientes ya pueden usar orWhereHas con total normalidad
                    $q->orWhereHas('personal.historialDocente.ofertaAcademica', function ($sub) use ($request) {
                        if ($request->filled('gestion_id')) {
                            $sub->whereHas('periodo', fn($p) => $p->where('gestion_id', $request->gestion_id));
                        }
                        if ($request->filled('periodo_id')) {
                            $sub->where('periodo_id', $request->periodo_id);
                        }
                        if ($request->filled('turno_id')) {
                            $sub->where('turno_id', $request->turno_id);
                        }
                        if ($request->filled('paralelo_id')) {
                            $sub->where('paralelo_id', $request->paralelo_id);
                        }
                    });
                });
            })
            ->orderBy('ap_paterno', 'asc')
            ->orderBy('ap_materno', 'asc')
            ->orderBy('nombres', 'asc')
            ->get();

        return view('admin.usuarios.create', compact(
            'personasSinUsuario',
            'rolesDisponibles',
            'roles',
            'tiposPersonaFiltro',
            'gestions',
            'periodos',
            'grados',
            'turnos',
            'paralelos'
        ));
    }

    /**
     * Guarda la habilitación masiva o individual de accesos.
     */
    public function store(Request $request)
    {
        set_time_limit(90);

        $request->validate([
            'personas'              => 'required|array|min:1',
            'personas.*.persona_id' => 'required|exists:personas,id|unique:users,persona_id',
            'personas.*.email'      => 'required|email|distinct',
            'roles'                 => 'required|array|min:1',
        ]);

        if (count($request->personas) > 50) {
            return back()->with('error', 'Seleccione un máximo de 50 usuarios por lote.')->withInput();
        }

        try {
            DB::beginTransaction();

            $usarCiPassword = $request->has('usar_ci_password');
            $passwordGlobal = $request->input('password');

            if (!$usarCiPassword && empty($passwordGlobal)) {
                return back()->with('error', 'Debe proporcionar una contraseña temporal o marcar la opción de usar el CI.')->withInput();
            }

            foreach ($request->personas as $data) {
                $passwordClara = $usarCiPassword ? Persona::find($data['persona_id'])->ci : $passwordGlobal;

                $user = User::create([
                    'persona_id' => $data['persona_id'],
                    'email'      => $data['email'],
                    'password'   => Hash::make($passwordClara),
                    'must_change_password' => true,
                ]);

                $user->assignRole($request->roles);
            }

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Accesos al sistema habilitados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un problema: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.usuarios.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'roles'    => 'required|array',
            'password' => 'nullable|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $data = ['email' => $request->email];
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            $user->syncRoles($request->roles);

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Credenciales y roles actualizados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function prepararEdicionMasiva(Request $request)
    {
        $ids = $request->input('usuarios_ids', []);
        if (empty($ids)) {
            return redirect()->route('admin.usuarios.index')->with('error', 'Seleccione al menos un usuario.');
        }
        session(['ids_usuarios_edicion' => $ids]);
        return redirect()->route('admin.usuarios.vistaEdicionMasiva');
    }

    public function vistaEdicionMasiva()
    {
        $ids = session('ids_usuarios_edicion', []);
        if (empty($ids)) {
            return redirect()->route('admin.usuarios.index')->with('error', 'No hay registros en sesión.');
        }

        $usuarios = User::with([
            'persona.estudiante.matriculacionesMaterias.oferta.periodo.gestion',
            'persona.personal.historialDocente.ofertaAcademica.periodo.gestion',
            'roles',
            'persona'
        ])->whereIn('id', $ids)->get();

        foreach ($usuarios as $user) {
            $user->tiene_clave_por_defecto = ($user->persona && !empty($user->persona->ci))
                ? Hash::check($user->persona->ci, $user->password)
                : false;
        }

        $roles = Role::all();
        return view('admin.usuarios.edicion-masiva', compact('usuarios', 'roles'));
    }

    public function updateMasivo(Request $request)
    {
        $request->validate([
            'usuarios'                => 'required|array|min:1',
            'usuarios.*.user_id'      => 'required|exists:users,id',
            'usuarios.*.email'        => 'required|email',
            'restablecer_ci_password' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();
            $restablecerGlobal = $request->has('restablecer_ci_password');

            foreach ($request->usuarios as $data) {
                $user = User::with('persona')->findOrFail($data['user_id']);
                $updateData = ['email' => $data['email']];

                if ($restablecerGlobal && $user->persona && !empty($user->persona->ci)) {
                    $updateData['password'] = Hash::make($user->persona->ci);
                    $updateData['must_change_password'] = true;
                } elseif (isset($data['cambiar_password']) && $data['cambiar_password'] == '1' && !empty($data['password'])) {
                    $updateData['password'] = Hash::make($data['password']);
                    $updateData['must_change_password'] = false;
                }

                $user->update($updateData);
                $user->syncRoles($data['roles'] ?? []);
            }

            DB::commit();
            return redirect()->route('admin.usuarios.index')->with('success', 'Actualización masiva completada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en actualización masiva: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        $user->delete();
        return redirect()->route('admin.usuarios.index')->with('success', 'Acceso revocado.');
    }

    public function destroyMasivo(Request $request)
    {
        $ids = $request->input('usuarios_ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No se seleccionó ningún usuario.');
        }
        if (in_array(Auth::id(), $ids)) {
            return redirect()->back()->with('error', 'No puedes revocar tu propio acceso.');
        }

        User::whereIn('id', $ids)->delete();
        return redirect()->route('admin.usuarios.index')->with('success', 'Accesos revocados exitosamente.');
    }
}
