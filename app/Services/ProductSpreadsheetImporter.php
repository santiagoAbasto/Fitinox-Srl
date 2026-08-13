<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use App\Support\OrderCode;
use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;

class ProductSpreadsheetImporter
{
    public function __construct(private readonly ProductInventoryCleaner $cleaner)
    {
    }

    public function import(UploadedFile $file): array
    {
        $rows = strtolower($file->getClientOriginalExtension()) === 'csv'
            ? $this->readCsv($file->getRealPath())
            : $this->readXlsx($file->getRealPath());

        [$headers, $rows, $firstDataRow] = $this->extractTable($rows);
        $required = ['codigo', 'detalle', 'tubo', 'rosca'];
        foreach ($required as $column) {
            if (!in_array($column, $headers, true)) throw new RuntimeException("Falta la columna obligatoria: {$column}.");
        }

        $filesToDelete = [];
        $report = DB::transaction(function () use ($rows, $headers, $firstDataRow, $file, &$filesToDelete) {
            $report = [
                'file' => $file->getClientOriginalName(),
                'processed' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0,
                'products_created' => 0, 'duplicates' => 0, 'inferred_details' => 0,
                'variants_deleted' => 0, 'products_deleted' => 0, 'categories_deleted' => 0, 'files_deleted' => 0,
                'changes' => [], 'errors' => [],
            ];
            $defaultCategory = Category::firstOrCreate(
                ['slug' => 'accesorios-para-tubos'],
                ['name' => 'Accesorios para tubos', 'is_active' => true, 'sort_order' => 'A']
            );
            $nextProductOrder = Product::orderByDesc('sort_order')->value('sort_order');

            $prepared = [];
            foreach ($rows as $offset => $row) {
                $excelRow = $firstDataRow + $offset;
                $values = array_pad(array_values($row), count($headers), null);
                $data = array_combine($headers, array_slice($values, 0, count($headers)));
                if (blank($data['codigo'] ?? null) && blank($data['detalle'] ?? null)) continue;
                if (blank($data['codigo'] ?? null)) {
                    $report['skipped']++;
                    $this->append($report['errors'], ['row' => $excelRow, 'message' => 'Código vacío.']);
                    continue;
                }
                $code = trim((string) $data['codigo']);
                if (isset($prepared[$code])) $report['duplicates']++;
                $prepared[$code] = ['row' => $excelRow, 'data' => $data];
            }

            if ($prepared === []) {
                throw new RuntimeException('El archivo no contiene códigos válidos. No se modificó el inventario.');
            }

            foreach ($prepared as $code => $record) {
                $excelRow = $record['row'];
                $data = $record['data'];
                $report['processed']++;
                $detail = trim((string) ($data['detalle'] ?? ''));
                if ($detail === '') {
                    $detail = $this->inferredDetail($code);
                    $report['inferred_details']++;
                }
                $category = $this->resolveCategory($data, $defaultCategory);
                $product = Product::where('slug', Str::slug($detail))->first();
                if (!$product) {
                    $nextProductOrder = OrderCode::next($nextProductOrder);
                    $product = Product::create([
                        'category_id' => $category->id,
                        'name' => $detail,
                        'slug' => Str::slug($detail),
                        'main_image' => $this->defaultImage($detail),
                        'is_active' => true,
                        'sort_order' => $nextProductOrder,
                    ]);
                    $report['products_created']++;
                }

                $productAttributes = ['category_id' => $category->id];
                if ($photo = $this->nullableString($data['foto'] ?? null)) {
                    $newPhotoKey = pathinfo(basename(str_replace('\\', '/', $photo)), PATHINFO_FILENAME);
                    if ($product->photo_import_key && $product->photo_import_key !== $newPhotoKey && $product->main_image) {
                        $filesToDelete[] = $product->main_image;
                        $productAttributes['main_image'] = null;
                    }
                    $productAttributes['photo_import_key'] = $newPhotoKey;
                } else {
                    if ($product->main_image) $filesToDelete[] = $product->main_image;
                    $productAttributes += ['photo_import_key' => null, 'main_image' => null];
                }
                if ($video = $this->nullableString($data['video'] ?? null)) {
                    if ($this->isYouTubeUrl($video)) {
                        if ($product->video_path) $filesToDelete[] = $product->video_path;
                        $productAttributes += [
                            'video_type' => 'youtube',
                            'youtube_url' => $video,
                            'video_path' => null,
                            'video_import_key' => null,
                        ];
                    } else {
                        $newVideoKey = pathinfo(basename(str_replace('\\', '/', $video)), PATHINFO_FILENAME);
                        if ($product->video_import_key && $product->video_import_key !== $newVideoKey && $product->video_path) {
                            $filesToDelete[] = $product->video_path;
                            $productAttributes['video_path'] = null;
                        }
                        $productAttributes += [
                            'video_type' => 'upload',
                            'video_import_key' => $newVideoKey,
                            'youtube_url' => null,
                        ];
                    }
                } else {
                    if ($product->video_path) $filesToDelete[] = $product->video_path;
                    $productAttributes += ['video_type' => null, 'video_path' => null, 'video_import_key' => null, 'youtube_url' => null];
                }
                $product->fill($productAttributes);
                if ($product->isDirty()) $product->save();

                $attributes = [
                    'product_id' => $product->id,
                    'detail' => $detail,
                    'tube' => $this->nullableString($data['tubo'] ?? null),
                    'thread' => $this->nullableString($data['rosca'] ?? null),
                    'quality' => $this->nullableString($data['calidad'] ?? null),
                    'list_price' => $this->decimal($data['precio_lista_us'] ?? $data['precio_lista'] ?? null),
                    'discount_percent' => 0,
                    'final_price' => $this->decimal($data['precio_lista_us'] ?? $data['precio_lista'] ?? null),
                    'physical_stock' => $this->integer($data['stock_fisico'] ?? null),
                    'transit_stock' => $this->integer($data['stock_en_transito'] ?? null),
                    'is_active' => true,
                ];

                $variant = ProductVariant::where('code', $code)->first();
                if (!$variant) {
                    $variant = ProductVariant::create(['code' => $code, ...$attributes]);
                    $report['created']++;
                    $this->append($report['changes'], [
                        'row' => $excelRow, 'code' => $code, 'action' => 'Creado',
                        'fields' => array_keys($attributes),
                    ]);
                    continue;
                }

                $variant->fill($attributes);
                $dirty = array_keys($variant->getDirty());
                if (!$dirty) {
                    $report['unchanged']++;
                    continue;
                }
                $variant->save();
                $report['updated']++;
                $this->append($report['changes'], [
                    'row' => $excelRow, 'code' => $code, 'action' => 'Actualizado', 'fields' => $dirty,
                ]);
            }

            $incomingCodes = array_keys($prepared);
            $obsoleteVariants = ProductVariant::query()->whereNotIn('code', $incomingCodes)->get();
            $report['variants_deleted'] = $obsoleteVariants->count();
            ProductVariant::query()->whereNotIn('code', $incomingCodes)->delete();

            $obsoleteProducts = Product::query()->doesntHave('variants')->get();
            foreach ($obsoleteProducts as $obsoleteProduct) {
                $filesToDelete = array_merge($filesToDelete, $this->cleaner->mediaPaths($obsoleteProduct));
            }
            $report['products_deleted'] = $obsoleteProducts->count();
            Product::query()->whereKey($obsoleteProducts->modelKeys())->delete();

            do {
                $emptyCategories = Category::query()->doesntHave('products')->doesntHave('children')->pluck('id');
                $deleted = $emptyCategories->count();
                if ($deleted) Category::query()->whereKey($emptyCategories)->delete();
                $report['categories_deleted'] += $deleted;
            } while ($deleted > 0);

            $report['files_deleted'] = count(array_unique(array_filter($filesToDelete)));

            return $report;
        });

        $this->cleaner->deleteFiles($filesToDelete);

        return $report;
    }

    private function extractTable(array $rows): array
    {
        if (count($rows) < 2) throw new RuntimeException('El archivo no contiene filas para importar.');
        $headerIndex = null;
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($value) => $this->normalize((string) $value), $row);
            if (in_array('codigo', $normalized, true) && in_array('detalle', $normalized, true)) {
                $headerIndex = $index;
                break;
            }
        }
        if ($headerIndex === null) throw new RuntimeException('No se encontró la fila de encabezados (Código / Detalle).');

        $base = array_values($rows[$headerIndex]);
        $units = array_values($rows[$headerIndex + 1] ?? []);
        $hasUnitRow = blank($units[0] ?? null) && blank($units[1] ?? null)
            && collect($units)->filter(fn ($value) => filled($value))->isNotEmpty();
        $headers = [];
        foreach ($base as $column => $value) {
            $header = $this->normalize((string) $value);
            $unit = $hasUnitRow ? $this->normalize((string) ($units[$column] ?? '')) : '';
            if ($unit && !in_array($unit, ['porcentaje'], true)) $header .= '_'.$unit;
            $headers[] = match ($header) {
                'precio_lista_u_s', 'precio_lista_us' => 'precio_lista_us',
                'precio_final_u_s', 'precio_final_us' => 'precio_final_us',
                'stock_fisico' => 'stock_fisico',
                'stock_en_transito' => 'stock_en_transito',
                'cantidad_solicitada' => 'cantidad_solicitada',
                default => $header,
            };
        }
        $dataIndex = $headerIndex + ($hasUnitRow ? 2 : 1);

        return [$headers, array_slice($rows, $dataIndex), $dataIndex + 1];
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) === 1) $row = str_getcsv($row[0], ',');
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new RuntimeException('No se pudo abrir el archivo XLSX.');
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sharedDocument = new DOMDocument();
            $sharedDocument->loadXML($xml);
            $sharedXPath = new DOMXPath($sharedDocument);
            $sharedXPath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($sharedXPath->query('//x:si') as $item) {
                $parts = [];
                foreach ($sharedXPath->query('.//x:t', $item) as $text) $parts[] = $text->textContent;
                $shared[] = implode('', $parts);
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) throw new RuntimeException('El XLSX no contiene una primera hoja.');
        $document = new DOMDocument();
        $document->loadXML($sheetXml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];
        foreach ($xpath->query('//x:sheetData/x:row') as $row) {
            $values = [];
            foreach ($xpath->query('./x:c', $row) as $cell) {
                if (!$cell instanceof DOMElement) continue;
                $reference = $cell->getAttribute('r');
                preg_match('/[A-Z]+/', $reference, $match);
                $index = $this->columnIndex($match[0]) - 1;
                $value = $xpath->query('./x:v', $cell)->item(0)?->textContent ?? '';
                if ($cell->getAttribute('t') === 's') $value = $shared[(int) $value] ?? '';
                if ($cell->getAttribute('t') === 'inlineStr') {
                    $parts = [];
                    foreach ($xpath->query('./x:is//x:t', $cell) as $text) $parts[] = $text->textContent;
                    $value = implode('', $parts);
                }
                $values[$index] = $value;
            }
            if ($values) {
                ksort($values);
                $rows[] = array_replace(array_fill(0, max(array_keys($values)) + 1, null), $values);
            }
        }
        return $rows;
    }

    private function columnIndex(string $letters): int
    {
        $number = 0;
        foreach (str_split($letters) as $letter) $number = $number * 26 + ord($letter) - 64;
        return $number;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return round((float) $value, 2);
        $number = str_replace(['%', ' ', "\xc2\xa0"], '', trim((string) $value));
        if (str_contains($number, ',') && str_contains($number, '.')) {
            $number = strrpos($number, ',') > strrpos($number, '.')
                ? str_replace(['.', ','], ['', '.'], $number)
                : str_replace(',', '', $number);
        } else {
            $number = str_replace(',', '.', $number);
        }
        return is_numeric($number) ? round((float) $number, 2) : null;
    }

    private function percentage(mixed $value): float
    {
        $percentage = $this->decimal($value) ?? 0;
        return $percentage > 0 && $percentage <= 1 ? round($percentage * 100, 2) : $percentage;
    }

    private function integer(mixed $value): ?int
    {
        $number = $this->decimal($value);
        return $number === null ? null : (int) round($number);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') return null;
        return trim((string) $value);
    }

    private function resolveCategory(array $data, Category $default): Category
    {
        $categoryName = $this->nullableString($data['categoria'] ?? null);
        if (!$categoryName) return $default;

        $parent = Category::firstOrCreate(
            ['parent_id' => null, 'name' => $categoryName],
            ['slug' => Str::slug($categoryName), 'is_active' => true, 'sort_order' => 0]
        );

        $subcategoryName = $this->nullableString($data['sub_categoria'] ?? $data['subcategoria'] ?? null);
        if (!$subcategoryName) return $parent;

        $subcategory = Category::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $subcategoryName],
            ['slug' => $this->availableSlug(Str::slug($subcategoryName), $parent->id, $categoryName.'-'.$subcategoryName), 'is_active' => true, 'sort_order' => 0]
        );

        $thirdName = $this->nullableString($data['sub_sub_categoria'] ?? null);
        if (!$thirdName) return $subcategory;

        return Category::firstOrCreate(
            ['parent_id' => $subcategory->id, 'name' => $thirdName],
            ['slug' => $this->availableSlug(Str::slug($thirdName), $subcategory->id, $categoryName.'-'.$subcategoryName.'-'.$thirdName), 'is_active' => true, 'sort_order' => 0]
        );
    }

    private function availableSlug(string $base, int $parentId, string $fallback): string
    {
        $existing = Category::where('slug', $base)->first();
        return !$existing || (int) $existing->parent_id === $parentId ? $base : Str::slug($fallback);
    }

    private function isYouTubeUrl(string $value): bool
    {
        $host = parse_url($value, PHP_URL_HOST);
        if (!$host) return false;

        $host = Str::lower($host);
        return $host === 'youtu.be' || $host === 'youtube.com' || Str::endsWith($host, '.youtube.com');
    }

    private function append(array &$items, array $item, int $limit = 60): void
    {
        if (count($items) < $limit) $items[] = $item;
    }

    private function inferredDetail(string $code): string
    {
        $parts = collect(explode('-', $code))
            ->map(fn ($part) => trim($part))
            ->filter(fn ($part) => $part !== '' && !preg_match('/^\d+(?:\/\d+)?$/', $part))
            ->values();
        $family = $parts->take(4)->join('-');

        return 'Artículo '.($family ?: Str::beforeLast($code, '-'));
    }

    private function defaultImage(string $detail): string
    {
        if (Str::contains(Str::lower($detail), 'codo')) return 'figma/product-codo.png';
        if (Str::contains(Str::lower($detail), 'recto')) return 'figma/product-recto.png';
        return 'products/product-placeholder.svg';
    }
}
