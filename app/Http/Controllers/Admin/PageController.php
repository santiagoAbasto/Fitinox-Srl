<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        return Inertia::render('Pages/Index', ['pages' => Page::orderBy('sort_order')->get()]);
    }

    public function edit(Page $page)
    {
        return Inertia::render('Pages/Edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'max:160'],
            'hero_title' => ['nullable', 'max:255'],
            'content' => ['nullable'],
            'seo_title' => ['nullable', 'max:255'],
            'seo_description' => ['nullable'],
            'is_published' => ['boolean'],
        ]);
        $data['slug'] = $page->slug ?: Str::slug($data['title']);
        $page->update($data);

        return back()->with('success', 'Página actualizada.');
    }
}
