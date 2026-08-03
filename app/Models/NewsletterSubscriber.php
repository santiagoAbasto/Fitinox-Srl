<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'is_subscribed', 'subscribed_at', 'unsubscribed_at', 'unsubscribe_source'];

    protected function casts(): array
    {
        return ['is_subscribed' => 'boolean', 'subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }
}
