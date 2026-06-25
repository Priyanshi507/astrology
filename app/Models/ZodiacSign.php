<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZodiacSign extends Model
{
    protected $fillable = [
        'sort_order', 'name', 'english_name', 'symbol', 'abbreviation',
        'lord_planet_id', 'element', 'modality', 'varna', 'vasya_signs',
    ];

    public function lordPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'lord_planet_id');
    }

    public function nakshatras(): HasMany
    {
        return $this->hasMany(Nakshatra::class, 'zodiac_sign_id');
    }

    public function natalCharts(): HasMany
    {
        return $this->hasMany(NatalChart::class, 'ascendant_zodiac_sign_id');
    }
}
