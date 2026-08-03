<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        return view('videos.index', [
            'page' => Page::where('slug', 'videos')->where('is_published', true)->firstOrFail(),
            'videos' => Video::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
