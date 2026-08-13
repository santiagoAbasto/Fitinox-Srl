<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductInventoryCleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductInventoryCleanerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_removes_all_inventory_records_and_product_media(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Categoría', 'slug' => 'categoria', 'is_active' => true, 'sort_order' => 0]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Producto',
            'slug' => 'producto',
            'main_image' => 'products/images/producto.jpg',
            'gallery' => ['products/gallery/producto-2.jpg'],
            'video_path' => 'products/videos/producto.mp4',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product->variants()->create(['code' => 'PR-1', 'is_active' => true]);
        foreach (['products/images/producto.jpg', 'products/gallery/producto-2.jpg', 'products/videos/producto.mp4'] as $path) {
            Storage::disk('public')->put($path, 'content');
        }

        $report = app(ProductInventoryCleaner::class)->clear();

        $this->assertSame(1, $report['products']);
        $this->assertSame(1, $report['variants']);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('categories', 0);
        Storage::disk('public')->assertDirectoryEmpty('products');
    }
}
