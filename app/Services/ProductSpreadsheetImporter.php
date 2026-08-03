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
use ZipArchive;

class ProductSpreadsheetImporter
{
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

        return DB::transaction(function () use ($rows, $headers, $firstDataRow, $file) {
            $report = [
                'file' => $file->getClientOriginalName(),
                'processed' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0,
                'products_created' => 0, 'duplicates' => 0, 'inferred_details' => 0,
                'changes' => [], 'errors' => [],
            ];
            $category = Category::firstOrCreate(
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

            foreach ($prepared as $code => $record) {
                $excelRow = $record['row'];
                $data = $record['data'];
                $report['processed']++;
                $detail = trim((string) ($data['detalle'] ?? ''));
                if ($detail === '') {
                    $detail = $this->inferredDetail($code);
                    $report['inferred_details']++;
                }
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

                $attributes = [
                    'product_id' => $product->id,
                    'detail' => $detail,
                    'tube' => $this->nullableString($data['tubo'] ?? null),
                    'thread' => $this->nullableString($data['rosca'] ?? null),
                    'quality' => $this->nullableString($data['calidad'] ?? null),
                    'list_price' => $this->decimal($data['precio_lista_us'] ?? $data['precio_lista'] ?? null),
                    'discount_percent' => $this->percentage($data['descuento'] ?? $data['descuento_porcentaje'] ?? 0),
                    'final_price' => $this->decimal($data['precio_final_us'] ?? $data['precio_final'] ?? null),
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

            return $report;
        });
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
            foreach (simplexml_load_string($xml)->si as $item) {
                $parts = [];
                if (isset($item->t)) $parts[] = (string) $item->t;
                foreach ($item->r as $run) $parts[] = (string) $run->t;
                $shared[] = implode('', $parts);
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) throw new RuntimeException('El XLSX no contiene una primera hoja.');
        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/[A-Z]+/', $reference, $match);
                $index = $this->columnIndex($match[0]) - 1;
                $value = (string) $cell->v;
                if ((string) $cell['t'] === 's') $value = $shared[(int) $value] ?? '';
                if ((string) $cell['t'] === 'inlineStr') $value = (string) $cell->is->t;
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
