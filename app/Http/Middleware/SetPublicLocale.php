<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetPublicLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', $request->cookie('fitinox_locale', 'es'));
        $locale = in_array($locale, ['es', 'en'], true) ? $locale : 'es';
        app()->setLocale($locale);

        return $next($request);
    }
}
