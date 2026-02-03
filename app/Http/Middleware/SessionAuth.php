<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionAuth
{
    /* Propósito: Verifica que exista un usuario autenticado en sesión. */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('auth.user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
