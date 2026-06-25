<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tithi extends Model
{
    protected $fillable = [
        'sort_order', 'name', 'paksha', 'tithi_number',
        'ruling_lord', 'nature', 'presiding_deity',
        'vrat_name', 'vrat_deity', 'vrat_benefit', 'vrat_ritual', 'vrat_mantra', 'vrat_color',
        'vivah_suitability', 'shraddha_name',
    ];
}
