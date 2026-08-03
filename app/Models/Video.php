<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->youtube_url) return null;
        if (preg_match('~(?:youtube\\.com/(?:watch\\?v=|embed/|shorts/)|youtu\\.be/)([A-Za-z0-9_-]{6,})~', $this->youtube_url, $match)) {
            return 'https://www.youtube.com/embed/'.$match[1].'?autoplay=1&rel=0';
        }
        return $this->youtube_url;
    }
}
