<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Services\TurnstileService;

class ClientRegistrationController extends Controller
{
    public function store(Request $request, TurnstileService $turnstile)
    {
        $turnstile->validate($request, 'registration');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create($data);
        $user->forceFill([
            'role' => 'client',
            'is_active' => false,
            'approval_status' => 'pending',
            'account_source' => 'web',
        ])->save();

        return back()->with('client_registration_success', 'Recibimos tu solicitud. El equipo Fitinox te avisará cuando habilite tu cuenta.');
    }
}
