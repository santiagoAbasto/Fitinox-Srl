<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'code', 'detail', 'tube', 'thread', 'quality', 'characteristics', 'list_price', 'discount_percent', 'final_price', 'physical_stock', 'transit_stock', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'list_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'final_price' => 'decimal:2',
            'physical_stock' => 'decimal:3',
            'transit_stock' => 'integer',
            'characteristics' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function technicalCharacteristics(bool $includeEmpty = true): array
    {
        $characteristics = [];
        foreach ($this->characteristics ?? [] as $key => $item) {
            if (is_array($item)) {
                $label = trim((string) ($item['label'] ?? str($key)->replace('_', ' ')->title()));
                $value = isset($item['value']) ? trim((string) $item['value']) : null;
            } else {
                $label = str($key)->replace('_', ' ')->title()->toString();
                $value = $item === null ? null : trim((string) $item);
            }

            $characteristics[(string) $key] = [
                'key' => (string) $key,
                'label' => $label,
                'value' => $value === '' ? null : $value,
            ];
        }

        foreach (['tubo' => ['Tubo', $this->tube], 'rosca' => ['Rosca', $this->thread], 'calidad' => ['Calidad', $this->quality]] as $key => [$label, $value]) {
            $characteristics[$key] = [
                'key' => $key,
                'label' => $characteristics[$key]['label'] ?? $label,
                'value' => filled($value) ? trim((string) $value) : ($characteristics[$key]['value'] ?? null),
            ];
        }

        return array_values(array_filter(
            $characteristics,
            fn (array $item) => $includeEmpty || filled($item['value'])
        ));
    }

    public function characteristicValue(string $key): ?string
    {
        foreach ($this->technicalCharacteristics() as $characteristic) {
            if ($characteristic['key'] === $key) return $characteristic['value'];
        }

        return null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function integrationArticle()
    {
        return $this->hasOne(IntegrationArticle::class);
    }
}
