<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard', ['stats' => [
            'pages' => Page::count(), 'categories' => Category::count(),
            'products' => Product::count(), 'inquiries' => Inquiry::where('status', 'new')->count(),
        ]]);
    }
}
