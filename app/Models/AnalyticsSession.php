<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    protected $fillable = ['session_uuid', 'visitor_hash', 'ip_hash', 'ip_version', 'started_at', 'last_seen_at', 'ended_at', 'landing_path', 'exit_path', 'referrer_host', 'traffic_source', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'device_type', 'browser', 'os', 'country_code', 'country_name', 'city', 'latitude', 'longitude', 'is_bot', 'page_views', 'duration_seconds'];
    protected function casts(): array { return ['started_at'=>'datetime','last_seen_at'=>'datetime','ended_at'=>'datetime','is_bot'=>'boolean']; }
}
