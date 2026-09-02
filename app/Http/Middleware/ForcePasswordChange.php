<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        // Si hay un usuario autenticado y debe cambiar su contraseña...
        if (Auth::check() && Auth::user()->must_change_password) {

            // Permitimos que acceda a las rutas de "cambiar contraseña" y de "logout"
            // usando la verificación por nombre de ruta o por patrón de URL exacto
            if (!$request->routeIs('admin.password.change.*') && !$request->is('logout')) {
                return redirect()->route('admin.password.change.form')
                    ->with('warning', 'Por seguridad, debes cambiar tu contraseña temporal antes de continuar.');
            }
        }

        return $next($request);
    }
}
