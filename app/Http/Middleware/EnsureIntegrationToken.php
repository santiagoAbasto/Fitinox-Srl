<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIntegrationToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = (string) config('integration.token', '');

        if ($configuredToken === '') {
            return response()->json([
                'message' => 'La integración no está configurada en este entorno.',
                'code' => 'integration_not_configured',
            ], 503);
        }

        $providedToken = (string) $request->bearerToken();
        if ($providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json([
                'message' => 'Credenciales de integración inválidas.',
                'code' => 'invalid_integration_token',
            ], 401);
        }

        return $next($request);
    }
}
