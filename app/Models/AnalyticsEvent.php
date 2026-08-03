<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['properties'=>'array','is_bot'=>'boolean','created_at'=>'datetime']; }
}
