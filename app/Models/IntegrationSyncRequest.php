<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSyncRequest extends Model
{
    protected $fillable = ['resource', 'idempotency_key', 'request_hash', 'http_status', 'response_payload'];

    protected function casts(): array
    {
        return ['response_payload' => 'array'];
    }
}
