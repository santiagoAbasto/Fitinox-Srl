<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== 'client' || ! $user?->is_active) {
            if (in_array($user?->role, ['super_admin', 'admin', 'editor'], true)) {
                return redirect()->route('admin.dashboard');
            }

            abort(403);
        }

        return $next($request);
    }
}
