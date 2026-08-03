<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSlide extends Model
{
    protected $fillable = ['title', 'button_label', 'button_url', 'media_type', 'media_path', 'youtube_url', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([^?&/]+)~', $this->youtube_url, $matches);

        return isset($matches[1])
            ? "https://www.youtube-nocookie.com/embed/{$matches[1]}?autoplay=1&mute=1&controls=0&loop=1&playlist={$matches[1]}"
            : null;
    }
}
