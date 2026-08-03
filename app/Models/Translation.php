<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = ['hash', 'source_locale', 'target_locale', 'source_text', 'translated_text'];
}
