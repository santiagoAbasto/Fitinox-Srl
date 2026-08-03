<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Page;
use App\Support\OrderCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DistributorController extends Controller
{
    public function edit()
    {
        return Inertia::render('Distributors/Edit', [
            'page' => Page::where('slug', 'distribuidores')->firstOrFail(),
            'distributors' => Distributor::orderBy('sort_order')->orderBy('id')->get(),
            'nextOrder' => OrderCode::next(Distributor::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'distribuidores')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('distributors/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Distribuidores guardada correctamente.');
    }

    public function store(Request $request)
    {
        Distributor::create($this->validated($request));
        return back()->with('success', 'Distribuidor agregado correctamente.');
    }

    public function update(Request $request, Distributor $distributor)
    {
        $distributor->update($this->validated($request));
        return back()->with('success', 'Distribuidor actualizado correctamente.');
    }

    public function destroy(Distributor $distributor)
    {
        $distributor->delete();
        return back()->with('success', 'Distribuidor eliminado correctamente.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'country' => ['required', 'max:100'],
            'name' => ['required', 'max:200'],
            'address' => ['required', 'max:255'],
            'phone' => ['nullable', 'max:150'],
            'email' => ['nullable', 'email', 'max:180'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'marker_x' => ['required', 'integer', 'between:0,100'],
            'marker_y' => ['required', 'integer', 'between:0,100'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
