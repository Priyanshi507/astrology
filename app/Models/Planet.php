<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planet extends Model
{
    protected $fillable = [
        'vimshottari_order', 'name', 'vedic_name', 'symbol',
        'abbreviation', 'color_hex', 'vimshottari_dasha_years',
        'is_always_retrograde',
    ];

    protected $casts = [
        'is_always_retrograde' => 'boolean',
    ];

    public function nakshatras(): HasMany
    {
        return $this->hasMany(Nakshatra::class, 'lord_planet_id');
    }

    public function zodiacSigns(): HasMany
    {
        return $this->hasMany(ZodiacSign::class, 'lord_planet_id');
    }

    public function weekdays(): HasMany
    {
        return $this->hasMany(Weekday::class, 'lord_planet_id');
    }
}
