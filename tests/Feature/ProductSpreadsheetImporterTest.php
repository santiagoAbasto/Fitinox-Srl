<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSpreadsheetImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_category_subcategory_photo_and_uploaded_video(): void
    {
        $csv = implode("\n", [
            'CODIGO;DETALLE;CATEGORIA;SUBCATEGORIA;TUBO;ROSCA;CALIDAD;FOTO;VIDEO;PRECIO_LISTA_US;DESCUENTO;PRECIO_FINAL_US;STOCK_FISICO;STOCK_EN_TRANSITO',
            'AL-FSS-200-1-2;Recto Macho;Accesorios para tubos;Rectos macho;1/8";1/8";AISI 316;1-Recto-Macho-F.jpg;1-Recto-Macho-V.mp4;14,69;5;13,96;8;3',
        ]);

        $report = app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('productos.csv', $csv)
        );

        $subcategory = Category::where('slug', 'rectos-macho')->firstOrFail();
        $product = Product::where('slug', 'recto-macho')->firstOrFail();
        $variant = ProductVariant::where('code', 'AL-FSS-200-1-2')->firstOrFail();

        $this->assertSame(1, $report['created']);
        $this->assertSame('accesorios-para-tubos', $subcategory->parent->slug);
        $this->assertSame($subcategory->id, $product->category_id);
        $this->assertSame('1-Recto-Macho-F', $product->photo_import_key);
        $this->assertSame('upload', $product->video_type);
        $this->assertSame('1-Recto-Macho-V', $product->video_import_key);
        $this->assertSame(14.69, (float) $variant->list_price);
        $this->assertSame(0.0, (float) $variant->discount_percent);
        $this->assertSame(14.69, (float) $variant->final_price);
    }

    public function test_it_accepts_a_youtube_url_as_the_product_video(): void
    {
        $csv = implode("\n", [
            'CODIGO;DETALLE;TUBO;ROSCA;VIDEO',
            'AL-FSS-200-1-4;Recto Macho;1/8";1/4";https://youtu.be/example123',
        ]);

        app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('productos.csv', $csv)
        );

        $product = Product::where('slug', 'recto-macho')->firstOrFail();
        $this->assertSame('youtube', $product->video_type);
        $this->assertSame('https://youtu.be/example123', $product->youtube_url);
        $this->assertNull($product->video_path);
    }

    public function test_it_synchronizes_the_inventory_and_removes_absent_products_and_media(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Vieja', 'slug' => 'vieja', 'is_active' => true, 'sort_order' => 0]);
        $obsolete = Product::create([
            'category_id' => $category->id,
            'name' => 'Producto viejo',
            'slug' => 'producto-viejo',
            'main_image' => 'products/images/vieja.jpg',
            'video_path' => 'products/videos/viejo.mp4',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $obsolete->variants()->create(['code' => 'CODIGO-VIEJO', 'is_active' => true]);
        Storage::disk('public')->put('products/images/vieja.jpg', 'image');
        Storage::disk('public')->put('products/videos/viejo.mp4', 'video');

        $csv = implode("\n", [
            'CODIGO;DETALLE;CATEGORIA;SUBCATEGORIA;SUB_SUB_CATEGORIA;TUBO;ROSCA;FOTO;VIDEO',
            'CODIGO-NUEVO;Producto nuevo;Accesorios;Conexiones;Pulgada;1/8";1/8";nueva-foto;nuevo-video',
        ]);

        $report = app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('inventario.csv', $csv)
        );

        $this->assertSame(1, $report['variants_deleted']);
        $this->assertSame(1, $report['products_deleted']);
        $this->assertDatabaseMissing('product_variants', ['code' => 'CODIGO-VIEJO']);
        $this->assertDatabaseMissing('products', ['slug' => 'producto-viejo']);
        $this->assertDatabaseHas('product_variants', ['code' => 'CODIGO-NUEVO']);
        Storage::disk('public')->assertMissing('products/images/vieja.jpg');
        Storage::disk('public')->assertMissing('products/videos/viejo.mp4');
    }
}
