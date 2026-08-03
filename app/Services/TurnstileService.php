<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TurnstileService
{
    public function validate(Request $request, string $action): void
    {
        $secret = config('services.turnstile.secret');
        if (!$secret) return;
        if ($request->filled('website') || $request->integer('form_started_at') > now()->subSeconds(2)->timestamp) throw ValidationException::withMessages(['form'=>'No pudimos validar el envío. Intentá nuevamente.']);
        $result = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['secret'=>$secret,'response'=>$request->input('cf-turnstile-response'),'remoteip'=>$request->ip()])->json();
        $hostname = config('services.turnstile.hostname');
        if (!($result['success']??false) || ($hostname && ($result['hostname']??null)!==$hostname) || (($result['action']??$action)!==$action)) throw ValidationException::withMessages(['form'=>'La verificación de seguridad venció o no es válida.']);
    }
}
