<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductInventoryCleaner
{
    public function clear(): array
    {
        $products = Product::query()->get();
        $paths = $products->flatMap(fn (Product $product) => $this->mediaPaths($product))->unique()->values();
        $productCount = $products->count();
        $variantCount = $products->sum(fn (Product $product) => $product->variants()->count());

        DB::transaction(function (): void {
            Product::query()->delete();
            Category::query()->delete();
        });

        Storage::disk('public')->delete($paths->all());

        return [
            'products' => $productCount,
            'variants' => $variantCount,
            'files' => $paths->count(),
        ];
    }

    public function mediaPaths(Product $product): array
    {
        return collect([
            $product->main_image,
            $product->video_path,
            $product->datasheet,
            ...($product->gallery ?? []),
        ])->filter(fn ($path) => is_string($path) && str_starts_with($path, 'products/'))
            ->unique()
            ->values()
            ->all();
    }

    public function deleteFiles(array $paths): void
    {
        Storage::disk('public')->delete(array_values(array_unique(array_filter($paths))));
    }
}
