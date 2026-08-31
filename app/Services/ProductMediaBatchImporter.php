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
        $report = ['processed' => 0, 'matched' => 0, 'unmatched' => 0, 'replaced' => 0, 'files' => []];

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

            $storedName = $this->versionedFileName($file, $stem);
            $stored = Storage::disk('public')->putFileAs($directory, $file, $storedName);
            $oldPaths = $matches
                ->pluck($pathField)
                ->filter(fn (?string $path) => filled($path) && $path !== $stored)
                ->unique()
                ->values();

            foreach ($matches as $product) {
                $attributes = [$pathField => $stored, $keyField => $stem];
                if (!$isImage) {
                    $attributes += ['video_type' => 'upload', 'youtube_url' => null];
                }
                $product->update($attributes);
            }

            foreach ($oldPaths as $oldPath) {
                $stillReferenced = Product::query()->where($pathField, $oldPath)->exists();
                if (!$stillReferenced) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $report['matched']++;
            $report['replaced'] += $oldPaths->count();
            $report['files'][] = [
                'name' => $original,
                'status' => 'matched',
                'stored_as' => $stored,
                'products' => $matches->pluck('id')->values()->all(),
            ];
        }

        return $report;
    }

    private function normalize(string $value): string
    {
        return Str::of(str_replace(['°', 'º'], '', $value))
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function versionedFileName(UploadedFile $file, string $stem): string
    {
        $safeStem = Str::slug($stem) ?: 'archivo';
        $fingerprint = substr(hash_file('sha256', $file->getRealPath()), 0, 12);
        $extension = Str::lower($file->getClientOriginalExtension());

        return "{$safeStem}-{$fingerprint}.{$extension}";
    }
}
