<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductMediaBatchImporter
{
    public function import(array $files, string $type): array
    {
        $isImage = $type === 'images';
        $keyField = $isImage ? 'photo_import_key' : 'video_import_key';
        $pathField = $isImage ? 'main_image' : 'video_path';
        $directory = $isImage ? 'products/images' : 'products/videos';
        $products = Product::query()->get(['id', $keyField, $pathField]);
        $report = ['processed' => 0, 'matched' => 0, 'unmatched' => 0, 'files' => []];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $report['processed']++;
            $original = basename($file->getClientOriginalName());
            $stem = pathinfo($original, PATHINFO_FILENAME);
            $normalized = $this->normalize($stem);

            $matches = $products->filter(function (Product $product) use ($keyField, $pathField, $normalized) {
                $reference = $product->{$keyField};
                if (!$reference && $product->{$pathField}) {
                    $reference = pathinfo(basename($product->{$pathField}), PATHINFO_FILENAME);
                }

                return $reference && hash_equals($this->normalize($reference), $normalized);
            });

            if ($matches->isEmpty()) {
                $report['unmatched']++;
                $report['files'][] = ['name' => $original, 'status' => 'unmatched'];
                continue;
            }

            $stored = Storage::disk('public')->putFileAs($directory, $file, $original);
            foreach ($matches as $product) {
                $attributes = [$pathField => $stored, $keyField => $stem];
                if (!$isImage) {
                    $attributes += ['video_type' => 'upload', 'youtube_url' => null];
                }
                $product->update($attributes);
            }

            $report['matched']++;
            $report['files'][] = [
                'name' => $original,
                'status' => 'matched',
                'products' => $matches->pluck('id')->values()->all(),
            ];
        }

        return $report;
    }

    private function normalize(string $value): string
    {
        return Str::of(str_replace('°', 'u', $value))
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
