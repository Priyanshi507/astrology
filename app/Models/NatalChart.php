<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NatalChart extends Model
{
    protected $fillable = [
        'user_id', 'chart_label', 'birth_date', 'birth_time',
        'birth_city_name', 'birth_latitude', 'birth_longitude',
        'birth_utc_offset', 'birth_timezone_identifier',
        'planet_positions_json', 'ascendant_degree', 'ascendant_zodiac_sign_id',
        'ayanamsa_value', 'vimshottari_dasha_balance_json', 'is_primary_chart',
    ];

    protected $casts = [
        'planet_positions_json'          => 'array',
        'vimshottari_dasha_balance_json' => 'array',
        'is_primary_chart'               => 'boolean',
        'birth_latitude'                 => 'decimal:6',
        'birth_longitude'                => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ascendantSign(): BelongsTo
    {
        return $this->belongsTo(ZodiacSign::class, 'ascendant_zodiac_sign_id');
    }
}
