<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'template', 'hero_title', 'hero_image', 'content', 'sections', 'seo_title', 'seo_description', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'is_published' => 'boolean'];
    }
}
