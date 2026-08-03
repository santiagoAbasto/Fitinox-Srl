<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomeSlide;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            'page' => Page::where('slug', 'inicio')->firstOrFail(),
            'slides' => HomeSlide::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'categories' => Category::where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->get(),
            'products' => Product::with('category')
                ->where('is_active', true)
                ->where('is_featured', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->orderBy('sort_order')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
