<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\Page;
use App\Models\Setting;

class DistributorController extends Controller
{
    public function index()
    {
        return view('distributors.index', [
            'page' => Page::where('slug', 'distribuidores')->where('is_published', true)->firstOrFail(),
            'distributors' => Distributor::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
