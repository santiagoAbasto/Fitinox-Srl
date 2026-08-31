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
        $this->assertNull($product->main_image);
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

    public function test_a_blank_variant_row_does_not_remove_the_products_media_reference(): void
    {
        $csv = implode("\n", [
            'CODIGO;DETALLE;CATEGORIA;FOTO;VIDEO',
            'COD-001;Codo Unión;Accesorios para tubos;12-Codo Union F.jpg;12-Codo Union V.mp4',
            'COD-002;Codo Unión;Accesorios para tubos;;',
        ]);

        app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('variantes.csv', $csv)
        );

        $product = Product::where('slug', 'codo-union')->firstOrFail();
        $this->assertSame('12-Codo Union F', $product->photo_import_key);
        $this->assertSame('12-Codo Union V', $product->video_import_key);
        $this->assertSame('upload', $product->video_type);
        $this->assertCount(2, $product->variants);
    }

    public function test_a_later_spreadsheet_with_blank_media_cells_preserves_uploaded_and_manual_media(): void
    {
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Esféricas',
            'slug' => 'esfericas',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => '2 vías ASTM A351 Serie 1.000',
            'slug' => '2-vias-astm-a351-serie-1000',
            'main_image' => 'products/images/esferica-principal.jpg',
            'photo_import_key' => '1-S1000DY',
            'gallery' => ['products/gallery/esferica-secundaria.jpg'],
            'video_type' => 'upload',
            'video_path' => 'products/videos/esferica.mp4',
            'video_import_key' => '1-S1000DY-V',
            'is_active' => true,
        ]);
        $product->variants()->create(['code' => 'ESF-1000', 'is_active' => true]);
        foreach ([$product->main_image, $product->gallery[0], $product->video_path] as $path) {
            Storage::disk('public')->put($path, 'archivo');
        }

        $csv = implode("\n", [
            'CODIGO;DETALLE;CATEGORIA;FOTO;VIDEO',
            'ESF-1000;2 vías ASTM A351 Serie 1.000;Esféricas;;',
        ]);

        app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('actualizacion.csv', $csv)
        );

        $product->refresh();
        $this->assertSame('products/images/esferica-principal.jpg', $product->main_image);
        $this->assertSame('1-S1000DY', $product->photo_import_key);
        $this->assertSame(['products/gallery/esferica-secundaria.jpg'], $product->gallery);
        $this->assertSame('products/videos/esferica.mp4', $product->video_path);
        $this->assertSame('1-S1000DY-V', $product->video_import_key);
        foreach ([$product->main_image, $product->gallery[0], $product->video_path] as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_a_new_spreadsheet_photo_key_keeps_current_and_gallery_images_until_replacement_succeeds(): void
    {
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Accesorios para tubos',
            'slug' => 'accesorios-para-tubos',
            'is_active' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Buje Reducción',
            'slug' => 'buje-reduccion',
            'main_image' => 'products/images/imagen-equivocada.jpg',
            'gallery' => ['products/gallery/segunda-foto.jpg'],
            'photo_import_key' => null,
            'is_active' => true,
        ]);
        $product->variants()->create(['code' => 'BUJE-001', 'is_active' => true]);
        Storage::disk('public')->put('products/images/imagen-equivocada.jpg', 'anterior');
        Storage::disk('public')->put('products/gallery/segunda-foto.jpg', 'segunda');

        $csv = implode("\n", [
            'CODIGO;DETALLE;CATEGORIA;FOTO',
            'BUJE-001;Buje Reducción;Accesorios para tubos;Buje Reduccion F.jpg',
        ]);

        app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('reemplazo.csv', $csv)
        );

        $product->refresh();
        $this->assertSame('Buje Reduccion F', $product->photo_import_key);
        $this->assertSame('products/images/imagen-equivocada.jpg', $product->main_image);
        $this->assertSame(['products/gallery/segunda-foto.jpg'], $product->gallery);
        Storage::disk('public')->assertExists('products/images/imagen-equivocada.jpg');
        Storage::disk('public')->assertExists('products/gallery/segunda-foto.jpg');
    }

    public function test_it_imports_products_without_tube_or_thread_and_keeps_unlimited_characteristics(): void
    {
        $csv = implode("\n", [
            'CATEGORIA;SUB CATEGORIA;CODIGO;DETALLE;DIAMETRO NOMINAL/SCH;CALIDAD;PRESION MAXIMA',
            'Accesorios para caños;Caños;CA-SC44-04;Sin Costura ASTM A312;1/4" (13,17mm) Sch 40 (2,24mm);AISI 304/304L;1000 PSI',
        ]);

        $report = app(ProductSpreadsheetImporter::class)->import(
            UploadedFile::fake()->createWithContent('productos-dinamicos.csv', $csv)
        );

        $variant = ProductVariant::where('code', 'CA-SC44-04')->firstOrFail();
        $this->assertNull($variant->tube);
        $this->assertNull($variant->thread);
        $this->assertSame('AISI 304/304L', $variant->quality);
        $this->assertSame('1/4" (13,17mm) Sch 40 (2,24mm)', $variant->characteristicValue('diametro_nominal_sch'));
        $this->assertSame('1000 PSI', $variant->characteristicValue('presion_maxima'));
        $this->assertSame(
            ['Diámetro nominal/Sch', 'Calidad', 'PRESION MAXIMA'],
            collect($report['technical_columns'])->pluck('label')->values()->all()
        );
        $this->assertSame(
            ['Diámetro nominal/Sch', 'Calidad', 'PRESION MAXIMA'],
            collect($variant->technicalCharacteristics(false))->pluck('label')->values()->all()
        );

        $product = $variant->product->load('variants');
        $this->assertSame(
            ['Diámetro nominal/Sch', 'Calidad', 'PRESION MAXIMA'],
            collect($product->variantCharacteristicColumns())->pluck('label')->values()->all()
        );
    }

    public function test_it_updates_the_inventory_without_removing_absent_products_or_media(): void
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

        $this->assertSame(0, $report['variants_deleted']);
        $this->assertSame(0, $report['products_deleted']);
        $this->assertSame(0, $report['files_deleted']);
        $this->assertDatabaseHas('product_variants', ['code' => 'CODIGO-VIEJO']);
        $this->assertDatabaseHas('products', ['slug' => 'producto-viejo']);
        $this->assertDatabaseHas('product_variants', ['code' => 'CODIGO-NUEVO']);
        Storage::disk('public')->assertExists('products/images/vieja.jpg');
        Storage::disk('public')->assertExists('products/videos/viejo.mp4');
    }
}
