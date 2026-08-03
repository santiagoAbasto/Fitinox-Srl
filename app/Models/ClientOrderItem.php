<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['list_price' => 'decimal:2', 'discount_percent' => 'decimal:2', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    public function order() { return $this->belongsTo(ClientOrder::class, 'client_order_id'); }
    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
