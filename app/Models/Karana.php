<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karana extends Model
{
    protected $fillable = [
        'name', 'ruling_lord', 'nature',
        'karana_type', 'presiding_deity', 'favourable_activities',
    ];
}
