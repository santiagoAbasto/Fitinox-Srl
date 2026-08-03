<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;

class AboutController extends Controller
{
    public function __invoke()
    {
        return view('about', [
            'page' => Page::where('slug', 'nosotros')->where('is_published', true)->firstOrFail(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
