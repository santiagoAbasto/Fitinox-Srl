<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPriceList extends Model
{
    protected $fillable = ['name', 'file_path', 'file_size', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
