<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationOutboxEvent extends Model
{
    protected $fillable = [
        'uuid', 'event_type', 'aggregate_type', 'aggregate_id', 'payload',
        'occurred_at', 'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }
}
