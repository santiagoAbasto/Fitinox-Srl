<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TurnstileService
{
    public function validate(Request $request, string $action): void
    {
        if ($request->filled('website') || $request->integer('form_started_at') > now()->subSeconds(2)->timestamp) {
            $this->reject($request, $action, 'honeypot_or_timing');
        }

        $secret = config('services.turnstile.secret');
        if (!$secret) {
            if (app()->isProduction() && config('security.turnstile.required_in_production')) {
                $this->reject($request, $action, 'missing_server_configuration');
            }
            return;
        }

        $token = (string) $request->input('cf-turnstile-response');
        if ($token === '' || strlen($token) > 4096) $this->reject($request, $action, 'missing_or_oversized_token');

        try {
            $result = Http::asForm()->timeout(5)->retry(1, 100)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                ['secret' => $secret, 'response' => $token, 'remoteip' => $request->ip()]
            )->throw()->json();
        } catch (\Throwable $exception) {
            report($exception);
            $this->reject($request, $action, 'verification_unavailable');
        }
        $hostname = config('services.turnstile.hostname');
        if (!($result['success'] ?? false)
            || ($hostname && ($result['hostname'] ?? null) !== $hostname)
            || (($result['action'] ?? null) !== $action)) {
            $this->reject($request, $action, 'invalid_challenge');
        }
    }

    private function reject(Request $request, string $action, string $reason): never
    {
        Log::warning('security.turnstile_rejected', [
            'action' => str_replace(["\r", "\n"], '', $action),
            'reason' => $reason,
            'ip_hash' => hash('sha256', (string) $request->ip()),
        ]);

        throw ValidationException::withMessages(['form' => 'La verificación de seguridad venció o no es válida.']);
    }
}
