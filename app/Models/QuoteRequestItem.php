<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequestItem extends Model
{
    protected $guarded = [];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }
}
