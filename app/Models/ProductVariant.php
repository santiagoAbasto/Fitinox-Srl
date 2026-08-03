<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'list_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'final_price' => 'decimal:2',
            'physical_stock' => 'integer',
            'transit_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
