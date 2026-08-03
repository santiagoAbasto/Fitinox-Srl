<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $guarded = [];

    public function deliveries()
    {
        return $this->hasMany(NewsletterDelivery::class);
    }
}
