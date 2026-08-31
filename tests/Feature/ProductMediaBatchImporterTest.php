<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductMediaBatchImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductMediaBatchImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_matches_media_by_name_while_ignoring_extension_case_and_symbols(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Accesorios', 'slug' => 'accesorios', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Recto Macho',
            'slug' => 'recto-macho',
            'photo_import_key' => '1-Recto Macho F',
            'video_import_key' => '1-Recto Macho V',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('1 recto macho f.JPG', 800, 800);
        $video = UploadedFile::fake()->create('1-RECTO-MACHO-V.mp4', 5000, 'video/mp4');

        $images = app(ProductMediaBatchImporter::class)->import([$image], 'images');
        $videos = app(ProductMediaBatchImporter::class)->import([$video], 'videos');
        $product->refresh();

        $this->assertSame(1, $images['matched']);
        $this->assertSame(1, $videos['matched']);
        $this->assertMatchesRegularExpression('#^products/images/1-recto-macho-f-[a-f0-9]{12}\.jpg$#', $product->main_image);
        $this->assertMatchesRegularExpression('#^products/videos/1-recto-macho-v-[a-f0-9]{12}\.mp4$#', $product->video_path);
        $this->assertSame('upload', $product->video_type);
        Storage::disk('public')->assertExists($product->main_image);
        Storage::disk('public')->assertExists($product->video_path);
    }

    public function test_it_reports_files_without_a_spreadsheet_match(): void
    {
        Storage::fake('public');
        $report = app(ProductMediaBatchImporter::class)->import([
            UploadedFile::fake()->image('sin-match.jpg'),
        ], 'images');

        $this->assertSame(0, $report['matched']);
        $this->assertSame(1, $report['unmatched']);
    }

    public function test_it_replaces_existing_media_with_a_new_public_url_and_removes_the_old_file(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Accesorios', 'slug' => 'accesorios', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Codo Macho',
            'slug' => 'codo-macho',
            'photo_import_key' => '2-Codo Macho F',
            'main_image' => 'products/images/foto-anterior.jpg',
            'is_active' => true,
        ]);
        Storage::disk('public')->put($product->main_image, 'contenido anterior');

        $report = app(ProductMediaBatchImporter::class)->import([
            UploadedFile::fake()->image('2-Codo Macho F.jpg', 496, 496),
        ], 'images');
        $product->refresh();

        $this->assertSame(1, $report['matched']);
        $this->assertSame(1, $report['replaced']);
        $this->assertNotSame('products/images/foto-anterior.jpg', $product->main_image);
        Storage::disk('public')->assertMissing('products/images/foto-anterior.jpg');
        Storage::disk('public')->assertExists($product->main_image);
    }
}
