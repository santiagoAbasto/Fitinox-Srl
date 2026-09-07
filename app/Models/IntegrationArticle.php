<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationArticle extends Model
{
    protected $fillable = [
        'external_code', 'product_variant_id', 'configuration', 'model', 'article_type',
        'manufacturing_type', 'source_category', 'stock', 'unit', 'source_updated_at',
        'source_hash', 'source_payload', 'last_received_at',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:3',
            'source_payload' => 'array',
            'source_updated_at' => 'datetime',
            'last_received_at' => 'datetime',
        ];
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
