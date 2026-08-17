<?php

namespace App\Services;

use App\Models\Product;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class ProductSpreadsheetExporter
{
    private const TEMPLATE = 'app/private/templates/Plantilla_importacion_masiva_productos_FITINOX.xlsx';

    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const STANDARD_CHARACTERISTICS = [
        'tubo' => 'Tubo',
        'rosca' => 'Rosca',
        'diametro_nominal_sch' => 'Diámetro nominal/Sch',
        'calidad' => 'Calidad',
    ];

    public function export(): string
    {
        $template = storage_path(self::TEMPLATE);
        if (!is_file($template)) {
            throw new RuntimeException('No se encontró la plantilla de importación de productos.');
        }

        $directory = storage_path('app/private/tmp');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo preparar la descarga de productos.');
        }

        $path = tempnam($directory, 'fitinox-productos-');
        if ($path === false || !copy($template, $path)) {
            throw new RuntimeException('No se pudo crear el archivo de productos.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            @unlink($path);
            throw new RuntimeException('No se pudo abrir la plantilla de productos.');
        }

        try {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) throw new RuntimeException('La plantilla no contiene la hoja de importación.');

            $characteristics = $this->characteristicColumns();
            $rows = $this->rows($characteristics);
            $updated = $this->populateSheet($sheetXml, $rows, $characteristics);
            $zip->addFromString('xl/worksheets/sheet1.xml', $updated);
        } finally {
            $zip->close();
        }

        return $path;
    }

    private function characteristicColumns(): array
    {
        $columns = self::STANDARD_CHARACTERISTICS;
        Product::query()->with('variants')->orderBy('sort_order')->orderBy('id')->get()
            ->each(function (Product $product) use (&$columns) {
                foreach ($product->variants as $variant) {
                    foreach ($variant->technicalCharacteristics() as $characteristic) {
                        $columns[$characteristic['key']] ??= $characteristic['label'];
                    }
                }
            });

        return $columns;
    }

    private function rows(array $characteristicColumns): array
    {
        return Product::query()
            ->with([
                'category.parent.parent:id,name,parent_id',
                'variants' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->flatMap(function (Product $product) use ($characteristicColumns) {
                $category = $product->category;
                $parent = $category?->parent;
                $grandparent = $parent?->parent;
                $photo = $product->photo_import_key ?: $this->mediaStem($product->main_image, 'products/images/');
                $video = match ($product->video_type) {
                    'youtube' => $product->youtube_url ?: '',
                    'upload' => $product->video_import_key ?: $this->mediaStem($product->video_path, 'products/videos/'),
                    default => '',
                };

                return $product->variants->map(fn ($variant) => [
                    'code' => $variant->code,
                    'detail' => $variant->detail ?: $product->name,
                    'category' => $grandparent?->name ?: $parent?->name ?: $category?->name ?: '',
                    'subcategory' => $grandparent ? $parent->name : ($parent ? $category->name : ''),
                    'third_category' => $grandparent ? $category->name : '',
                    'characteristics' => collect($characteristicColumns)
                        ->mapWithKeys(fn ($label, $key) => [$key => $variant->characteristicValue($key)])
                        ->all(),
                    'photo' => $photo,
                    'video' => $video,
                    'list_price' => $variant->list_price,
                    'physical_stock' => $variant->physical_stock,
                    'transit_stock' => $variant->transit_stock,
                ]);
            })
            ->values()
            ->all();
    }

    private function populateSheet(string $xml, array $data, array $characteristicColumns): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        if (!$document->loadXML($xml)) throw new RuntimeException('No se pudo leer la hoja de importación.');

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('x', self::NS);
        $sheetData = $xpath->query('//x:sheetData')->item(0);
        $sourceRow = $xpath->query('//x:sheetData/x:row[@r="2"]')->item(0);
        if (!$sheetData instanceof DOMElement || !$sourceRow instanceof DOMElement) {
            throw new RuntimeException('La estructura de la plantilla no es válida.');
        }

        $headers = [
            'Categoría', 'Sub categoría', 'Sub sub categoría', 'Código', 'Detalle',
            ...array_values($characteristicColumns),
            'Foto', 'Video', 'Precio lista u$s', 'Stock físico', 'Stock en tránsito',
        ];
        $lastColumnNumber = count($headers);
        $lastColumn = $this->columnName($lastColumnNumber);
        $headerRow = $xpath->query('//x:sheetData/x:row[@r="1"]')->item(0);
        if (!$headerRow instanceof DOMElement) throw new RuntimeException('La plantilla no contiene encabezados.');
        foreach ($headers as $index => $header) {
            $this->writeText($document, $headerRow, $this->columnName($index + 1), 1, $header, 1);
        }

        $dimension = $xpath->query('//x:dimension')->item(0);
        if ($dimension instanceof DOMElement) $dimension->setAttribute('ref', "A1:{$lastColumn}".max(315, count($data) + 50));
        $this->configureColumns($document, $xpath, $headers);

        $lastRow = max(315, count($data) + 50);
        for ($number = 2; $number <= $lastRow; $number++) {
            $row = $xpath->query("//x:sheetData/x:row[@r=\"{$number}\"]")->item(0);
            if (!$row instanceof DOMElement) {
                $row = $sourceRow->cloneNode(true);
                $sheetData->appendChild($row);
            }
            $this->renumberRow($row, $number);

            $record = $data[$number - 2] ?? null;
            if ($record) {
                $values = [
                    $record['category'], $record['subcategory'], $record['third_category'], $record['code'], $record['detail'],
                    ...array_map(fn ($key) => $record['characteristics'][$key] ?? null, array_keys($characteristicColumns)),
                    $record['photo'], $record['video'],
                ];
                foreach ($values as $index => $value) {
                    $this->writeText($document, $row, $this->columnName($index + 1), $number, $value, $index % 2 === 0 ? 2 : 3);
                }
                $numericStart = count($values) + 1;
                $this->writeNumber($document, $row, $this->columnName($numericStart), $number, $record['list_price'], 4);
                $this->writeNumber($document, $row, $this->columnName($numericStart + 1), $number, $record['physical_stock'], 5);
                $this->writeNumber($document, $row, $this->columnName($numericStart + 2), $number, $record['transit_stock'], 6);
            } else {
                for ($column = 1; $column <= $lastColumnNumber; $column++) {
                    $style = $column > $lastColumnNumber - 3 ? 3 + ($column - ($lastColumnNumber - 3)) : ($column % 2 === 1 ? 2 : 3);
                    $this->clearCell($document, $row, $this->columnName($column), $number, $style);
                }
            }
        }

        foreach ($xpath->query("//x:sheetData/x:row[number(@r) > {$lastRow}]") as $extraRow) {
            $extraRow->parentNode?->removeChild($extraRow);
        }

        foreach ($xpath->query('//*[@sqref]') as $node) {
            $node->setAttribute('sqref', preg_replace('/315\b/', (string) $lastRow, $node->getAttribute('sqref')));
        }

        return $document->saveXML();
    }

    private function configureColumns(DOMDocument $document, DOMXPath $xpath, array $headers): void
    {
        $columns = $xpath->query('//x:cols')->item(0);
        if (!$columns instanceof DOMElement) return;
        while ($columns->firstChild) $columns->removeChild($columns->firstChild);
        foreach ($headers as $index => $header) {
            $width = match ($index) {
                0, 1, 2 => 24,
                3 => 20,
                4 => 28,
                default => match ($header) {
                    'Foto', 'Video' => 24,
                    'Precio lista u$s' => 17,
                    'Stock físico', 'Stock en tránsito' => 16,
                    default => min(28, max(14, mb_strlen($header) + 4)),
                },
            };
            $column = $document->createElementNS(self::NS, 'col');
            $column->setAttribute('min', (string) ($index + 1));
            $column->setAttribute('max', (string) ($index + 1));
            $column->setAttribute('customWidth', 'true');
            $column->setAttribute('width', (string) $width);
            $columns->appendChild($column);
        }
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function renumberRow(DOMElement $row, int $number): void
    {
        $row->setAttribute('r', (string) $number);
        foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
            $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
            $cell->setAttribute('r', $column.$number);
        }
    }

    private function cell(DOMDocument $document, DOMElement $row, string $column, int $number, ?int $style = null): DOMElement
    {
        foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
            if ($cell->getAttribute('r') === $column.$number) {
                if ($style !== null) $cell->setAttribute('s', (string) $style);
                return $cell;
            }
        }

        $cell = $document->createElementNS(self::NS, 'c');
        $cell->setAttribute('r', $column.$number);
        if ($style !== null) $cell->setAttribute('s', (string) $style);
        $row->appendChild($cell);
        return $cell;
    }

    private function clearCell(DOMDocument $document, DOMElement $row, string $column, int $number, ?int $style = null): void
    {
        $cell = $this->cell($document, $row, $column, $number, $style);
        while ($cell->firstChild) $cell->removeChild($cell->firstChild);
        $cell->removeAttribute('t');
    }

    private function writeText(DOMDocument $document, DOMElement $row, string $column, int $number, mixed $value, ?int $style = null): void
    {
        $cell = $this->cell($document, $row, $column, $number, $style);
        while ($cell->firstChild) $cell->removeChild($cell->firstChild);
        $cell->setAttribute('t', 'inlineStr');
        $inline = $document->createElementNS(self::NS, 'is');
        $text = $document->createElementNS(self::NS, 't');
        $text->appendChild($document->createTextNode((string) ($value ?? '')));
        $inline->appendChild($text);
        $cell->appendChild($inline);
    }

    private function writeNumber(DOMDocument $document, DOMElement $row, string $column, int $number, mixed $value, ?int $style = null): void
    {
        $cell = $this->cell($document, $row, $column, $number, $style);
        while ($cell->firstChild) $cell->removeChild($cell->firstChild);
        $cell->removeAttribute('t');
        if ($value !== null && $value !== '') {
            $cell->appendChild($document->createElementNS(self::NS, 'v', (string) (float) $value));
        }
    }

    private function mediaStem(?string $path, string $prefix): string
    {
        if (!$path || !str_starts_with($path, $prefix)) return '';

        return pathinfo(basename($path), PATHINFO_FILENAME);
    }
}
