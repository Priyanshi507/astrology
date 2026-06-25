<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekadashi extends Model
{
    protected $fillable = [
        'lookup_key', 'name', 'paksha', 'vedic_month_number', 'vedic_month_name',
        'mantra', 'significance_text', 'rituals_list', 'auspicious_time_note',
    ];

    protected $casts = [
        'rituals_list' => 'array',
    ];
}
                                