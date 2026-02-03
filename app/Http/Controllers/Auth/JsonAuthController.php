<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JsonAuthController
{
    /* Propósito: Muestra el formulario de inicio de sesión. */
    public function showLogin()
    {
        return view('auth.login');
    }

    /* Propósito: Autentica contra la base de datos y crea sesión con rol para el sistema. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()
                ->withErrors(['email' => 'Credenciales inválidas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        $request->session()->put('auth.user', [
            'id' => (int)$user->id,
            'email' => (string)$user->email,
            'name' => (string)$user->name,
            'role' => (string)$user->role,
        ]);

        return redirect($this->redirectPathByRole((string)$user->role));
    }

    /* Propósito: Cierra sesión y elimina datos de autenticación del sistema. */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget('auth.user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* Propósito: Obtiene la ruta destino según el rol. */
    private function redirectPathByRole(string $role): string
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'maestro':
                return '/maestro/dashboard';
            case 'padre':
                return '/alumnado/dashboard';
            default:
                return '/alumnado/dashboard';
        }
    }
}
