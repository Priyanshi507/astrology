<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nakshatra extends Model
{
    protected $fillable = [
        'sort_order', 'name', 'deity', 'lord_planet_id', 'zodiac_sign_id',
        'starting_degree', 'gana', 'yoni', 'nadi', 'tattva', 'guna',
        'muhurta_quality', 'muhurta_auspiciousness_score',
        'vivah_suitability', 'griha_pravesh_suitability',
        'vahana_suitability', 'mundan_suitability', 'sampatti_suitability',
        'is_panchak',
        'good_for', 'avoid', 'display_color',
        'deity_description', 'description', 'muhurta_type_label', 'muhurta_type_desc',
    ];

    protected $casts = [
        'starting_degree' => 'decimal:4',
        'is_panchak'      => 'boolean',
    ];

    public function lordPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'lord_planet_id');
    }

    public function zodiacSign(): BelongsTo
    {
        return $this->belongsTo(ZodiacSign::class, 'zodiac_sign_id');
    }
}
