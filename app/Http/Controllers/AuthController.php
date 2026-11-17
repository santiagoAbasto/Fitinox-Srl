<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TurnstileService;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, TurnstileService $turnstile)
    {
        $turnstile->validate($request, 'login');
        $data = $request->validate([
            'login' => ['nullable', 'string', 'max:150', 'required_without:email'],
            'email' => ['nullable', 'email', 'required_without:login'],
            'password' => ['required'],
        ]);
        $identifier = $data['login'] ?? $data['email'];
        $credentials = [filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'name' => $identifier, 'password' => $data['password']];
        $credentials['is_active'] = true;
        $clientAccess = $request->input('access') === 'client';
        $guard = $clientAccess ? Auth::guard('client') : Auth::guard('web');

        if (! $guard->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Usuario o contraseña incorrectos.'])->onlyInput('login', 'email');
        }
        $request->session()->regenerate();

        if ($clientAccess && $guard->user()->role !== 'client') {
            $guard->logout();

            return back()->withErrors(['login' => 'Este acceso es exclusivo para clientes. Para administrar el sitio ingresá desde el panel.'])->onlyInput('login', 'email');
        }

        // Las cuentas de clientes nunca deben heredar una URL administrativa en
        // "intended". Su destino único es la Zona Clientes.
        if ($guard->user()->role === 'client') {
            return redirect()->route('client.dashboard');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        // El cierre desde el panel debe abandonar por completo la aplicación
        // administrativa. No redirigimos al login, porque se presenta como un
        // modal y deja el panel visible detrás.
        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente.');
    }

    public function destroyClient(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sesión de cliente cerrada correctamente.');
    }
}
