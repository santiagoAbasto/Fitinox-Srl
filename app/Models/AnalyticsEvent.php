<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = ['analytics_session_id', 'event_uuid', 'event_name', 'path', 'properties', 'is_bot', 'country_code', 'created_at'];
    protected function casts(): array { return ['properties'=>'array','is_bot'=>'boolean','created_at'=>'datetime']; }
}
