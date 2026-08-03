<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'total' => 'decimal:2', 'delivery_date' => 'date'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(ClientOrderItem::class); }
}
