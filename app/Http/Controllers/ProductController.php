<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DeepLTranslationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $normalizedQuery = $this->normalizeSearchText($query);
        $tokens = collect(preg_split('/\s+/u', $normalizedQuery) ?: [])
            ->filter(fn ($token) => mb_strlen($token) > 1)
            ->reject(fn ($token) => in_array($token, ['para', 'con', 'por', 'del', 'las', 'los', 'una', 'uno', 'que', 'necesito', 'quiero', 'buscar']))
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return response()->json([
                'answer' => 'Escribí el nombre, código, medida, rosca o aplicación que necesitás.',
                'results' => [],
            ]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($category) => $category->where('is_active', true))
            ->with([
                'category:id,name,slug',
                'variants' => fn ($variants) => $variants->where('is_active', true)->select([
                    'id', 'product_id', 'code', 'detail', 'tube', 'thread', 'quality', 'characteristics',
                ]),
            ])
            ->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $token).'%';
                    $builder->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(sku, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(summary, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$like])
                        ->orWhereHas('category', fn ($category) => $category->whereRaw('LOWER(name) LIKE ?', [$like]))
                        ->orWhereHas('variants', fn ($variants) => $variants
                            ->where('is_active', true)
                            ->where(function ($variant) use ($like) {
                                $variant->whereRaw('LOWER(code) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(detail, \'\')) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(tube, \'\')) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(thread, \'\')) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(quality, \'\')) LIKE ?', [$like])
                                    ->orWhere('characteristics', 'like', $like);
                            }));
                }
            })
            ->limit(100)
            ->get()
            ->map(function (Product $product) use ($tokens, $normalizedQuery) {
                $name = $this->normalizeSearchText($product->name);
                $category = $this->normalizeSearchText($product->category?->name ?? '');
                $summary = $this->normalizeSearchText(implode(' ', [
                    $product->sku,
                    $product->summary,
                    $product->description,
                    json_encode($product->technical_data),
                ]));
                $variantText = $this->normalizeSearchText($product->variants->map(fn ($variant) => implode(' ', [
                    $variant->code, $variant->detail, $variant->tube, $variant->thread, $variant->quality, json_encode($variant->characteristics),
                ]))->implode(' '));

                $score = 0;
                $match = 'Coincide con tu búsqueda';
                foreach ($tokens as $token) {
                    if ($name === $token || str_starts_with($name, $token)) {
                        $score += 80;
                        $match = 'Coincide por nombre de producto';
                    } elseif (str_contains($name, $token)) {
                        $score += 50;
                        $match = 'Coincide por nombre de producto';
                    }
                    if (str_contains($variantText, $token)) {
                        $score += 45;
                        $match = str_contains($variantText, $normalizedQuery) ? 'Coincide por código o especificación técnica' : $match;
                    }
                    if (str_contains($category, $token)) {
                        $score += 24;
                        $match = 'Coincide por categoría';
                    }
                    if (str_contains($summary, $token)) {
                        $score += 12;
                    }
                }

                return [
                    'score' => $score,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'match' => $match,
                    'image' => $product->main_image ? asset('storage/'.$product->main_image) : asset('storage/products/product-placeholder.svg'),
                    'url' => route('products.show', $product),
                ];
            })
            ->filter(fn ($product) => $product['score'] > 0)
            ->sortByDesc('score')
            ->take(7)
            ->values();

        return response()->json([
            'answer' => $products->isEmpty()
                ? 'No encontré una coincidencia exacta. Probá con el nombre, código, tubo, rosca o medida.'
                : 'Encontré '.$products->count().' opción'.($products->count() === 1 ? ' relevante' : 'es relevantes').' para “'.$query.'”.',
            'results' => $products,
        ]);
    }

    public function index()
    {
        return view('products.index', [
            'categories' => Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        $category->loadMissing(['parent.parent']);

        $activeCategoryIds = [];
        $activeCategory = $category;
        while ($activeCategory) {
            $activeCategoryIds[] = $activeCategory->id;
            $activeCategory = $activeCategory->parent;
        }

        return view('products.category', [
            'category' => $category,
            'categories' => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['children' => fn ($children) => $children->where('is_active', true)])])
                ->orderBy('sort_order')
                ->get(),
            'activeCategoryIds' => $activeCategoryIds,
            'childCategories' => Category::where('is_active', true)
                ->where('parent_id', $category->id)
                ->with(['parent.parent'])
                ->orderBy('sort_order')
                ->get(),
            'products' => Product::with('category')->whereIn('category_id', [$category->id, ...$this->descendantIds($category)])->where('is_active', true)->orderBy('sort_order')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    private function descendantIds(Category $category): array
    {
        $ids = [];
        $pending = [$category->id];
        while ($pending) {
            $children = Category::whereIn('parent_id', $pending)->pluck('id')->all();
            $pending = array_values(array_diff($children, $ids));
            $ids = array_values(array_unique(array_merge($ids, $pending)));
        }
        return $ids;
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active && $product->category?->is_active, 404);
        $product->load(['category', 'variants' => fn ($query) => $query->where('is_active', true)]);
        $characteristicColumns = collect($product->variantCharacteristicColumns());
        $splitVariantTables = $characteristicColumns->count() <= 3;

        return view('products.show', [
            'product' => $product,
            'characteristicColumns' => $characteristicColumns,
            'splitVariantTables' => $splitVariantTables,
            'variantGroups' => $splitVariantTables
                ? $product->variants->chunk(max(1, (int) ceil($product->variants->count() / 2)))
                : collect([$product->variants]),
            'related' => $product->relatedProducts()
                ->with('category')
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function datasheet(Product $product)
    {
        abort_unless($product->is_active && $product->category?->is_active, 404);
        $product->load(['category', 'variants' => fn ($query) => $query->where('is_active', true)]);
        $locale = app()->getLocale();
        $translations = $this->pdfTranslations([
            $product->name,
            $product->category?->name,
            $product->summary,
            $product->description,
            ...$product->variants->pluck('detail')->all(),
            ...$product->variants->pluck('quality')->all(),
            ...$product->variants->flatMap(fn ($variant) => collect($variant->technicalCharacteristics())
                ->flatMap(fn ($characteristic) => [$characteristic['label'], $characteristic['value']]))->all(),
        ], $locale);
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('products.datasheet', [
            'product' => $product,
            'logo' => $this->dataUri('figma/header-footer/header-logo.png'),
            'image' => $this->dataUri($product->main_image ?: 'products/product-placeholder.svg'),
            'generatedAt' => now(),
            'locale' => $locale,
            'translations' => $translations,
        ])->render());
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(515, 805, $locale === 'en' ? 'Page {PAGE_NUM} of {PAGE_COUNT}' : 'Página {PAGE_NUM} de {PAGE_COUNT}', null, 7, [0.42, 0.47, 0.49]);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ficha-tecnica-'.Str::slug($product->name).'.pdf"',
        ]);
    }

    private function dataUri(string $path): ?string
    {
        $absolute = storage_path('app/public/'.$path);
        if (!is_file($absolute)) return null;
        return 'data:'.mime_content_type($absolute).';base64,'.base64_encode(file_get_contents($absolute));
    }

    /** Los PDFs se generan con Dompdf, fuera del middleware de HTML público. */
    private function pdfTranslations(array $texts, string $locale): array
    {
        if ($locale !== 'en') return [];

        return app(DeepLTranslationService::class)->translateMany(array_filter(
            $texts,
            fn ($text) => is_string($text) && trim($text) !== ''
        ));
    }

    private function normalizeSearchText(?string $value): string
    {
        return mb_strtolower(Str::ascii((string) $value));
    }
}
