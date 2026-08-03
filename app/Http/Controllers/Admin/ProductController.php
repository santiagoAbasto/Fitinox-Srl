<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductSpreadsheetImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\OrderCode;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->withCount('variants')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')->paginate(20)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'is_active']),
            'filters' => $request->only('search'),
            'nextOrder' => OrderCode::next(Product::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return back()->with('success', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product->load(['category', 'variants', 'relatedProducts.category']),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'is_active']),
            'relatedCandidates' => Product::query()
                ->with('category:id,name,is_active')
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'category_id', 'name', 'main_image']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    public function syncRelated(Request $request, Product $product)
    {
        $data = $request->validate([
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        $requestedIds = collect($data['related_product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($requestedIds->contains($product->id)) {
            throw ValidationException::withMessages([
                'related_product_ids' => 'Un producto no puede relacionarse consigo mismo.',
            ]);
        }

        $visibleIds = Product::query()
            ->whereIn('id', $requestedIds)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->pluck('id');

        if ($visibleIds->count() !== $requestedIds->count()) {
            throw ValidationException::withMessages([
                'related_product_ids' => 'Solo se pueden vincular productos y categorías visibles en la web.',
            ]);
        }

        $orderedRelations = $requestedIds
            ->values()
            ->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]])
            ->all();

        $product->relatedProducts()->sync($orderedRelations);

        return back()->with('success', 'Productos relacionados actualizados.');
    }

    public function import(Request $request, ProductSpreadsheetImporter $importer)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240']]);
        try {
            $report = $importer->import($request->file('file'));
        } catch (\Throwable $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        $summary = "{$report['processed']} filas procesadas: {$report['created']} creadas, {$report['updated']} actualizadas y {$report['unchanged']} sin cambios.";
        return back()->with('success', $summary)->with('import_report', $report);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $product->variants()->create($this->validatedVariant($request));

        return back()->with('success', 'Código agregado al producto.');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);
        $variant->update($this->validatedVariant($request, $variant));

        return back()->with('success', "Código {$variant->code} actualizado.");
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);
        $variant->delete();

        return back()->with('success', 'Código eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'max:180'],
            'sku' => ['nullable', 'max:100'],
            'summary' => ['nullable'],
            'description' => ['nullable'],
            'main_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video_type' => ['nullable', 'in:upload,youtube'],
            'video_file' => ['nullable', 'mimes:mp4,webm,mov', 'max:102400'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'gallery_files' => ['nullable', 'array', 'max:8'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'datasheet_file' => ['nullable', 'mimes:pdf', 'max:20480'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
        ]);
        unset($data['main_image_file'], $data['video_file'], $data['gallery_files'], $data['datasheet_file']);
        if ($request->hasFile('main_image_file')) {
            $data['main_image'] = $request->file('main_image_file')->store('products/images', 'public');
        }
        if ($request->hasFile('video_file')) {
            $data['video_type'] = 'upload';
            $data['video_path'] = $request->file('video_file')->store('products/videos', 'public');
        }
        if ($request->hasFile('gallery_files')) {
            $gallery = collect($request->file('gallery_files'))
                ->map(fn ($file) => $file->store('products/gallery', 'public'))
                ->values()->all();
            $data['gallery'] = array_values(array_merge($request->input('existing_gallery', []), $gallery));
        }
        if ($request->hasFile('datasheet_file')) {
            $data['datasheet'] = $request->file('datasheet_file')->store('products/datasheets', 'public');
        }
        $data['slug'] = Str::slug($data['name']);

        return $data;
    }

    private function validatedVariant(Request $request, ?ProductVariant $variant = null): array
    {
        return $request->validate([
            'code' => ['required', 'max:120', Rule::unique('product_variants', 'code')->ignore($variant?->id)],
            'detail' => ['nullable', 'max:180'],
            'tube' => ['nullable', 'max:80'],
            'thread' => ['nullable', 'max:80'],
            'quality' => ['nullable', 'max:100'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'physical_stock' => ['nullable', 'integer', 'min:0'],
            'transit_stock' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
