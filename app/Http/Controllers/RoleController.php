<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('admin.roles.index')->with('mensaje', 'Se registró el rol de la manera correcta')->with('icono', 'success');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return redirect()->route('admin.roles.index')->with('mensaje', 'Se actualizó el rol de la manera correcta')->with('icono', 'success');
    }
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('mensaje', 'Se eliminó el rol de la manera correcta')->with('icono', 'success');
    }
    public function permisos(string $id)
    {
        $rol = Role::findOrFail($id);

        // Agrupamos los permisos por módulo (puedes ajustar cómo los agrupa tu base de datos o estructura)
        // Si manejas los nombres como 'modulo.accion', puedes usar esto:
        $permisos = Permission::all()->groupBy(function ($item) {
            // Ejemplo: si el permiso es 'admin.users.index', extrae 'users' o el prefijo
            $partes = explode('.', $item->name);
            return count($partes) > 1 ? $partes[1] : 'General';
        });

        return view('admin.roles.permisos', compact('rol', 'permisos'));
    }

    public function update_permisos(Request $request, string  $id)
    {
        $rol = Role::findOrFail($id);

        // Obtenemos los IDs de los permisos seleccionados en el formulario
        $permisosIds = $request->input('permisos', []);

        // Sincronizamos usando la relación Eloquent (que acepta IDs numéricos)
        $rol->permissions()->sync($permisosIds);

        // Limpiamos la caché de permisos de Spatie para que tome efecto de inmediato
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with([
            'mensaje' => 'Se actualizó los permisos del rol correctamente',
            'icono' => 'success'
        ]);
    }
}
