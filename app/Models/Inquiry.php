<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = ['type', 'name', 'company', 'email', 'phone', 'message', 'payload', 'status'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}
