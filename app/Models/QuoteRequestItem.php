<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequestItem extends Model
{
    protected $fillable = ['quote_request_id', 'product_id', 'product_variant_id', 'product_name', 'category_name', 'code', 'tube', 'thread', 'quantity'];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }
}
