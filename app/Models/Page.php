<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['sections' => 'array', 'is_published' => 'boolean'];
    }
}
