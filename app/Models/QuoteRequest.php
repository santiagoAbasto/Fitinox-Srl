<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(QuoteRequestItem::class);
    }
}
