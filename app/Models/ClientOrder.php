<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrder extends Model
{
    protected $fillable = ['user_id', 'number', 'status', 'delivery_date', 'subtotal', 'discount_total', 'total', 'notes', 'delivery_method', 'transport_name', 'transport_address', 'transport_phone', 'delivery_destination', 'attachment_path'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'total' => 'decimal:2', 'delivery_date' => 'date'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(ClientOrderItem::class); }
}
