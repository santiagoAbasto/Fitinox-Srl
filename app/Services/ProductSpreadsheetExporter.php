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

            $rows = $this->rows();
            $updated = $this->populateSheet($sheetXml, $rows);
            $zip->addFromString('xl/worksheets/sheet1.xml', $updated);
        } finally {
            $zip->close();
        }

        return $path;
    }

    private function rows(): array
    {
        return Product::query()
            ->with([
                'category.parent.parent:id,name,parent_id',
                'variants' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->flatMap(function (Product $product) {
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
                    'tube' => $variant->tube,
                    'thread' => $variant->thread,
                    'quality' => $variant->quality,
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

    private function populateSheet(string $xml, array $data): string
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
                $this->writeText($document, $row, 'A', $number, $record['category']);
                $this->writeText($document, $row, 'B', $number, $record['subcategory']);
                $this->writeText($document, $row, 'C', $number, $record['third_category']);
                $this->writeText($document, $row, 'D', $number, $record['code']);
                $this->writeText($document, $row, 'E', $number, $record['detail']);
                $this->writeText($document, $row, 'F', $number, $record['tube']);
                $this->writeText($document, $row, 'G', $number, $record['thread']);
                $this->writeText($document, $row, 'H', $number, $record['quality']);
                $this->writeText($document, $row, 'I', $number, $record['photo']);
                $this->writeText($document, $row, 'J', $number, $record['video']);
                $this->writeNumber($document, $row, 'K', $number, $record['list_price']);
                $this->writeNumber($document, $row, 'L', $number, $record['physical_stock']);
                $this->writeNumber($document, $row, 'M', $number, $record['transit_stock']);
            } else {
                foreach (range('A', 'M') as $column) $this->clearCell($row, $column, $number);
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

    private function renumberRow(DOMElement $row, int $number): void
    {
        $row->setAttribute('r', (string) $number);
        foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
            $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
            $cell->setAttribute('r', $column.$number);
        }
    }

    private function cell(DOMDocument $document, DOMElement $row, string $column, int $number): DOMElement
    {
        foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
            if ($cell->getAttribute('r') === $column.$number) return $cell;
        }

        $cell = $document->createElementNS(self::NS, 'c');
        $cell->setAttribute('r', $column.$number);
        $row->appendChild($cell);
        return $cell;
    }

    private function clearCell(DOMElement $row, string $column, int $number): void
    {
        foreach ($row->getElementsByTagNameNS(self::NS, 'c') as $cell) {
            if ($cell->getAttribute('r') !== $column.$number) continue;
            while ($cell->firstChild) $cell->removeChild($cell->firstChild);
            $cell->removeAttribute('t');
            return;
        }
    }

    private function writeText(DOMDocument $document, DOMElement $row, string $column, int $number, mixed $value): void
    {
        $cell = $this->cell($document, $row, $column, $number);
        while ($cell->firstChild) $cell->removeChild($cell->firstChild);
        $cell->setAttribute('t', 'inlineStr');
        $inline = $document->createElementNS(self::NS, 'is');
        $text = $document->createElementNS(self::NS, 't');
        $text->appendChild($document->createTextNode((string) ($value ?? '')));
        $inline->appendChild($text);
        $cell->appendChild($inline);
    }

    private function writeNumber(DOMDocument $document, DOMElement $row, string $column, int $number, mixed $value): void
    {
        $cell = $this->cell($document, $row, $column, $number);
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
