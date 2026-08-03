<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['es', 'en'], true), 404);
        $request->session()->put('locale', $locale);

        return back()->withCookie(cookie('fitinox_locale', $locale, 60 * 24 * 365));
    }
}
