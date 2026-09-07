<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationClient extends Model
{
    protected $fillable = [
        'external_code', 'user_id', 'business_name', 'email_raw', 'email_candidates',
        'tax_id', 'source_updated_at', 'source_hash', 'source_payload', 'last_received_at',
    ];

    protected function casts(): array
    {
        return [
            'email_candidates' => 'array',
            'source_payload' => 'array',
            'source_updated_at' => 'datetime',
            'last_received_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
