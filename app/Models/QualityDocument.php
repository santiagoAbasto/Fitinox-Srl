<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityDocument extends Model
{
    protected $fillable = ['title', 'file', 'logo', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
