<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');

        if ($request->isSecure() && config('security.hsts.enabled')) {
            $hsts = 'max-age='.(int) config('security.hsts.max_age', 31536000);
            if (config('security.hsts.include_subdomains')) $hsts .= '; includeSubDomains';
            if (config('security.hsts.preload')) $hsts .= '; preload';
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        if ($this->isHtml($response)) {
            $header = config('security.csp.enforce')
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only';
            $response->headers->set($header, implode('; ', config('security.csp.directives')));
        }

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        return str_contains(strtolower($response->headers->get('Content-Type', '')), 'text/html');
    }
}
