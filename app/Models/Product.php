<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'sku', 'summary', 'description', 'main_image', 'photo_import_key', 'gallery', 'technical_data', 'datasheet', 'video_type', 'video_path', 'video_import_key', 'youtube_url', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'technical_data' => 'array', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function relatedProducts()
    {
        return $this->belongsToMany(self::class, 'product_related', 'product_id', 'related_product_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('product_related.sort_order');
    }
}
