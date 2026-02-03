<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionRole
{
    /* Propósito: Restringe el acceso a rutas según el rol guardado en sesión. */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->session()->get('auth.user');

        if (!$user) {
            return redirect()->route('login');
        }

        $role = (string)($user['role'] ?? '');

        if (!in_array($role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
