<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\OrderCode;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['parent.parent'])->withCount('products')->orderBy('sort_order')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'parentOptions' => $this->parentOptions($categories),
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
            'category' => $category->load(['parent.parent'])->loadCount('products'),
            'parentOptions' => $this->parentOptions(
                Category::with(['parent.parent'])->where('id', '!=', $category->id)->orderBy('sort_order')->get()
            ),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $previousImage = $category->image;
        $category->update($data);

        if ($category->parent_id && $previousImage) {
            Storage::disk('public')->delete($previousImage);
        }

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'La categoría contiene productos.');
        abort_if($category->children()->exists(), 422, 'La categoría contiene subcategorías.');
        $category->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;
        $parent = $parentId ? Category::with('parent')->find($parentId) : null;

        if ($category && $parentId === $category->id) {
            throw ValidationException::withMessages(['parent_id' => 'Una categoría no puede depender de sí misma.']);
        }
        if ($parent && $parent->parent?->parent_id) {
            throw ValidationException::withMessages(['parent_id' => 'La jerarquía admite como máximo categoría, subcategoría y sub-subcategoría.']);
        }
        if ($category && $parent && $this->isDescendantOf($parent, $category)) {
            throw ValidationException::withMessages(['parent_id' => 'No se puede mover una categoría dentro de una de sus descendientes.']);
        }

        $requiresImage = !$parentId && (!$category || !$category->image);
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'max:160'],
            'description' => ['nullable'],
            'image_file' => [$requiresImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
        ]);
        unset($data['image_file']);
        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('categories/images', 'public');
        }
        if ($parentId) {
            $data['image'] = null;
            $data['is_featured'] = false;
        }
        $data['slug'] = $this->availableSlug($data['name'], $parent, $category);

        return $data;
    }

    private function parentOptions($categories): array
    {
        return $categories->filter(fn (Category $category) => !$category->parent?->parent_id)
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'level' => $category->parent_id ? 2 : 1,
                'path' => $category->parent ? $category->parent->name.' › '.$category->name : $category->name,
            ])->values()->all();
    }

    private function isDescendantOf(Category $candidate, Category $category): bool
    {
        while ($candidate->parent) {
            if ($candidate->parent->id === $category->id) return true;
            $candidate = $candidate->parent;
        }
        return false;
    }

    private function availableSlug(string $name, ?Category $parent, ?Category $category): string
    {
        $base = Str::slug($name);
        $query = Category::where('slug', $base);
        if ($category) $query->where('id', '!=', $category->id);
        if (!$query->exists()) return $base;

        return Str::slug(($parent?->name ? $parent->name.'-' : '').$name);
    }
}
