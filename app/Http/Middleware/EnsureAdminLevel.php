<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminLevel
{
    public function handle(Request $request, Closure $next, string $minimum = 'admin'): Response
    {
        $roles = $minimum === 'super_admin' ? ['super_admin'] : ['super_admin', 'admin'];
        abort_unless($request->user()?->is_active && in_array($request->user()?->role, $roles, true), 403);
        return $next($request);
    }
}
