<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tara extends Model
{
    protected $fillable = [
        'tara_number', 'name', 'is_auspicious', 'auspiciousness_type',
        'icon_symbol', 'bphs_reference', 'phala_description', 'scoring_bonus',
    ];

    protected $casts = [
        'is_auspicious' => 'boolean',
    ];
}
