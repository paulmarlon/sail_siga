<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use App\Models\Grado;

use App\Models\Estado;
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
     * Muestra la lista de usuarios del sistema con sus datos personales, roles y filtros avanzados.
     */
    public function index(Request $request)
    {
        $query = User::with([
            'persona.personal.historialDocente.ofertaAcademica',
            'persona.estudiante.matriculacionesMaterias.oferta',
            'roles'
        ]);

        // 1. Filtrar por Rol (si se seleccionó en la vista)
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // 2. Función que aplica los filtros académicos a Docentes o Estudiantes
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

        // 3. Si hay filtros académicos, aplicamos la lógica OR (Docente o Estudiante)
        if ($request->hasAny(['gestion_id', 'periodo_id', 'turno_id', 'paralelo_id'])) {
            $query->where(function ($q) use ($aplicarFiltros) {
                // Camino 1: Docentes
                $q->orWhereHas('persona.personal.historialDocente.ofertaAcademica', $aplicarFiltros);

                // Camino 2: Estudiantes
                $q->orWhereHas('persona.estudiante.matriculacionesMaterias.oferta', $aplicarFiltros);
            });
        }

        // 4. Obtener resultados ordenados alfabéticamente por Apellidos y Nombres (Estándar institucional)
        $usuarios = $query
            ->join('personas', 'users.persona_id', '=', 'personas.id')
            ->orderBy('personas.ap_paterno', 'asc')
            ->orderBy('personas.ap_materno', 'asc')
            ->orderBy('personas.nombres', 'asc')
            ->select('users.*')
            ->get();

        // Catálogos necesarios para los selects de la vista
        $roles     = \Spatie\Permission\Models\Role::all();
        $gestions  = \App\Models\Gestion::all();
        $periodos  = \App\Models\Periodo::all();
        $turnos    = \App\Models\Turno::all();
        $paralelos = \App\Models\Paralelo::all();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'gestions', 'periodos', 'turnos', 'paralelos'));
    }
    public function showChangePasswordForm()
    {
        return view('admin.usuarios.cambiar-password-obligatorio');
    }

    // Procesa el cambio y desactiva la bandera
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // O también: $request->user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Muestra el formulario avanzado de habilitación masiva de accesos.
     */
    public function create(Request $request)
    {
        // 1. Catálogos para la vista
        $rolesDisponibles = \Spatie\Permission\Models\Role::all();
        $roles     = $rolesDisponibles;
        $gestions  = Gestion::all();
        $periodos  = Periodo::all();
        $grados    = Grado::all();
        $turnos    = Turno::all();
        $paralelos = Paralelo::all();

        // 2. Extraer dinámicamente TODOS los tipos reales de la tabla 'personal'
        $tiposPersonalDB = \App\Models\Personal::select('tipo')
            ->distinct()
            ->whereNotNull('tipo')
            ->pluck('tipo')
            ->toArray();

        $tiposPersonaFiltro = array_unique(array_merge(['estudiante'], $tiposPersonalDB));

        $tipoSeleccionado = $request->input('tipo');

        // 3. Consulta principal optimizada y ordenada alfabéticamente por Apellidos y Nombres
        $personasSinUsuario = Persona::query()
            ->whereDoesntHave('user')

            // Filtro dinámico basado en lo que el usuario elija en el selector
            ->when($tipoSeleccionado, function ($query) use ($tipoSeleccionado) {
                if ($tipoSeleccionado === 'estudiante') {
                    $query->whereHas('estudiante');
                } else {
                    $query->whereHas('personal', function ($q) use ($tipoSeleccionado) {
                        $q->where('tipo', $tipoSeleccionado);
                    });
                }
            })

            // Filtros académicos opcionales
            ->when($request->filled('gestion_id') || $request->filled('periodo_id') || $request->filled('turno_id') || $request->filled('paralelo_id'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->orWhereHas('estudiante.matriculacionesMaterias.oferta', function ($sub) use ($request) {
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
            // Ordenamiento alfabético aplicado para el formulario de habilitación (create)
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
     * Almacena los nuevos usuarios creados de forma masiva o individual.
     */
    /**
     * Almacena los nuevos usuarios creados de forma masiva o individual.
     */
    public function store(Request $request)
    {
        // Aumentar prudencialmente el tiempo de ejecución para lotes
        set_time_limit(90);

        $request->validate([
            'personas'              => 'required|array|min:1',
            'personas.*.persona_id' => 'required|exists:personas,id|unique:users,persona_id',
            'personas.*.email'      => 'required|email|distinct',
            'roles'                 => 'required|array|min:1',
        ]);

        // Límite de seguridad por lote para evitar saturar el servidor con Hash::make()
        if (count($request->personas) > 50) {
            return back()->with('error', 'Por motivos de rendimiento y seguridad, por favor seleccione un máximo de 50 usuarios por lote.')->withInput();
        }

        try {
            DB::beginTransaction();

            $usarCiPassword = $request->has('usar_ci_password');
            $passwordGlobal = $request->input('password');

            if (!$usarCiPassword && empty($passwordGlobal)) {
                return back()->with('error', 'Debe proporcionar una contraseña temporal o marcar la opción de usar el CI.')->withInput();
            }

            foreach ($request->personas as $data) {
                if ($usarCiPassword) {
                    $persona = Persona::find($data['persona_id']);
                    $passwordClara = $persona->ci;
                } else {
                    $passwordClara = $passwordGlobal;
                }

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
                ->with('success', 'Accesos al sistema habilitados correctamente para las personas seleccionadas.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Mensaje amigable y controlado si ocurre cualquier excepción o bloqueo
            return back()
                ->with('error', 'Ocurrió un problema al procesar los accesos (es posible que el lote sea muy grande o se haya agotado el tiempo de servidor). Intente con menos registros. Detalle: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Muestra el formulario para editar credenciales o roles de un usuario existente.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.usuarios.edit', compact('user', 'roles'));
    }

    /**
     * Actualiza la cuenta de acceso y sus roles (Individual).
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'roles'    => 'required|array',
            'password' => 'nullable|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'email' => $request->email,
            ];

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
            return back()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Prepara y muestra la vista matricial (edición masiva de los registros seleccionados).
     */
    public function prepararEdicionMasiva(Request $request)
    {
        $ids = $request->input('usuarios_ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'Por favor, seleccione al menos un usuario para la edición masiva.');
        }

        session(['ids_usuarios_edicion' => $ids]);

        return redirect()->route('admin.usuarios.vistaEdicionMasiva');
    }

    /**
     * Carga los datos usando la sesión y muestra la vista matricial.
     */
    public function vistaEdicionMasiva()
    {
        $ids = session('ids_usuarios_edicion', []);

        if (empty($ids)) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No hay registros seleccionados o la sesión ha expirado.');
        }

        $usuarios = User::with([
            'persona.estudiante.matriculacionesMaterias.oferta.periodo.gestion',
            'persona.estudiante.matriculacionesMaterias.oferta.turno',
            'persona.estudiante.matriculacionesMaterias.oferta.paralelo',
            'persona.personal.historialDocente.ofertaAcademica.periodo.gestion',
            'persona.personal.historialDocente.ofertaAcademica.turno',
            'persona.personal.historialDocente.ofertaAcademica.paralelo',
            'roles',
            'persona'
        ])
            ->whereIn('id', $ids)
            ->get();
        foreach ($usuarios as $user) {
            if ($user->persona && !empty($user->persona->ci)) {
                // Compara si la clave guardada encriptada corresponde al número de CI
                $user->tiene_clave_por_defecto = \Illuminate\Support\Facades\Hash::check($user->persona->ci, $user->password);
            } else {
                $user->tiene_clave_por_defecto = false;
            }
        }

        $roles = Role::all();

        return view('admin.usuarios.edicion-masiva', compact('usuarios', 'roles'));
    }

    /**
     * Actualiza masivamente los datos, correos, roles independientes y contraseñas de la matriz.
     */
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

                $updateData = [
                    'email' => $data['email'],
                ];
                if ($restablecerGlobal && $user->persona && !empty($user->persona->ci)) {
                    $updateData['password'] = Hash::make($user->persona->ci);
                    $updateData['must_change_password'] = true; // <--- Forzar cambio si se resetea al CI
                } elseif (isset($data['cambiar_password']) && $data['cambiar_password'] == '1' && !empty($data['password'])) {
                    $updateData['password'] = Hash::make($data['password']);
                    $updateData['must_change_password'] = false; // Si el admin le pone una clave personalizada, opcionalmente se la quitamos o la dejamos
                }

                $user->update($updateData);

                $rolesNuevos = $data['roles'] ?? [];
                $user->syncRoles($rolesNuevos);
            }

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Actualización masiva de usuarios procesada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar la actualización masiva: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Revoca/Elimina el acceso al sistema (sin borrar los datos de la persona).
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de acceso.');
        }

        $user->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Acceso al sistema revocado correctamente.');
    }

    /**
     * Revoca accesos en lote.
     */
    public function destroyMasivo(Request $request)
    {
        $ids = $request->input('usuarios_ids');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No se ha seleccionado ningún usuario para revocar acceso.');
        }

        $currentUserId = Auth::id();

        if (in_array($currentUserId, $ids)) {
            return redirect()->back()->with('error', 'No puedes revocar tu propio acceso al sistema.');
        }

        User::whereIn('id', $ids)->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Accesos revocados exitosamente para los usuarios seleccionados.');
    }
}
