<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = ['subject', 'heading', 'body', 'attachment_path', 'recipient_count', 'sent_count', 'failed_count', 'created_by', 'sent_at'];

    public function deliveries()
    {
        return $this->hasMany(NewsletterDelivery::class);
    }
}
