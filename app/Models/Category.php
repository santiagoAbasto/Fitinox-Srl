<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'image', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolvedImage(): ?string
    {
        // Cada tarjeta representa una categoría distinta; no hereda la foto
        // de su padre, que podría haber sido actualizada para otra categoría.
        return $this->image;
    }
}
