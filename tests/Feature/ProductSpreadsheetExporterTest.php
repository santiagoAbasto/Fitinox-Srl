<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductSpreadsheetExporter;
use App\Services\ProductSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class ProductSpreadsheetExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_current_products_in_an_importable_workbook(): void
    {
        $parent = Category::create([
            'name' => 'Accesorios para tubos',
            'slug' => 'accesorios-para-tubos',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $subcategory = Category::create([
            'parent_id' => $parent->id,
            'name' => 'Conectores AJA-LOCK',
            'slug' => 'conectores-aja-lock',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $thirdCategory = Category::create([
            'parent_id' => $subcategory->id,
            'name' => 'Pulgada',
            'slug' => 'pulgada',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::create([
            'category_id' => $thirdCategory->id,
            'name' => 'Recto Macho',
            'slug' => 'recto-macho',
            'main_image' => 'products/images/1-Recto-Macho-F.jpg',
            'video_type' => 'upload',
            'video_path' => 'products/videos/1-Recto-Macho-V.mp4',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product->variants()->create([
            'code' => 'AL-FSS-200-1-2',
            'detail' => 'Recto Macho',
            'tube' => '1/8"',
            'thread' => '1/8"',
            'quality' => 'AISI 316',
            'characteristics' => [
                'tubo' => ['label' => 'Tubo', 'value' => '1/8"'],
                'rosca' => ['label' => 'Rosca', 'value' => '1/8"'],
                'diametro_nominal_sch' => ['label' => 'Diámetro nominal/Sch', 'value' => null],
                'calidad' => ['label' => 'Calidad', 'value' => 'AISI 316'],
                'presion_maxima' => ['label' => 'Presión máxima', 'value' => '1000 PSI'],
            ],
            'list_price' => 14.69,
            'discount_percent' => 5,
            'final_price' => 13.96,
            'physical_stock' => 8,
            'transit_stock' => 3,
            'is_active' => true,
        ]);

        $path = app(ProductSpreadsheetExporter::class)->export();

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertStringContainsString('AL-FSS-200-1-2', $sheet);
        $this->assertStringContainsString('Conectores AJA-LOCK', $sheet);
        $this->assertStringContainsString('Pulgada', $sheet);
        $this->assertStringContainsString('1-Recto-Macho-F', $sheet);
        $this->assertStringContainsString('1-Recto-Macho-V', $sheet);
        $this->assertStringContainsString('Diámetro nominal/Sch', $sheet);
        $this->assertStringContainsString('Presión máxima', $sheet);
        $this->assertStringContainsString('1000 PSI', $sheet);

        $report = app(ProductSpreadsheetImporter::class)->import(new UploadedFile(
            $path,
            'Productos_FITINOX.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        ));

        $this->assertSame(1, $report['processed']);
        $this->assertSame(1, $report['updated']);
        $variant = $product->variants()->firstOrFail();
        $this->assertSame(0.0, (float) $variant->discount_percent);
        $this->assertSame(14.69, (float) $variant->final_price);
        $this->assertSame('1000 PSI', $variant->characteristicValue('presion_maxima'));
        @unlink($path);
    }
}
