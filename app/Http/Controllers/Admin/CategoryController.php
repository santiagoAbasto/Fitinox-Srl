<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\OrderCode;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Categories/Index', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
            'nextOrder' => OrderCode::next(Category::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return back()->with('success', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category->loadCount('products'),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'La categoría contiene productos.');
        $category->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'max:160'],
            'description' => ['nullable'],
            'image_file' => [$category ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
        ]);
        unset($data['image_file']);
        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('categories/images', 'public');
        }
        $data['slug'] = Str::slug($data['name']);

        return $data;
    }
}
