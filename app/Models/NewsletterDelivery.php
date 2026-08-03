<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterDelivery extends Model
{
    protected $fillable = ['newsletter_campaign_id', 'newsletter_subscriber_id', 'email', 'status', 'error_message', 'sent_at'];
}
