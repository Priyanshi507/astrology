<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Weekday extends Model
{
    protected $fillable = [
        'dow_index', 'name', 'english_name', 'lord_planet_id',
        'symbol', 'nature', 'presiding_deity',
        'deity_note', 'classification', 'classification_note',
        'auspicious_activities', 'info_text', 'vrats',
        'rahu_kala_part', 'yamaganda_part', 'gulika_part',
        'durmuhurta_parts', 'gowri_sequence', 'vivah_suitability',
    ];

    public function lordPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'lord_planet_id');
    }

    public function choghadiyaSequences(): HasMany
    {
        return $this->hasMany(ChoghadiyaSequence::class, 'weekday_id');
    }

    public function dayChoghadiya(): HasMany
    {
        return $this->hasMany(ChoghadiyaSequence::class, 'weekday_id')
            ->where('is_night', false);
    }

    public function nightChoghadiya(): HasMany
    {
        return $this->hasMany(ChoghadiyaSequence::class, 'weekday_id')
            ->where('is_night', true);
    }
}
