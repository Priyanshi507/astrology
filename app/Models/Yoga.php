<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yoga extends Model
{
    protected $fillable = [
        'name', 'nature', 'ruling_lord',
        'presiding_deity', 'classification', 'description',
        'advice_text', 'mood_label', 'color_hex',
    ];
}
