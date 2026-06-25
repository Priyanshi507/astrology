<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoghadiyaSequence extends Model
{
    protected $fillable = [
        'weekday_id', 'is_night', 'choghadiya_type_id',
    ];

    protected $casts = [
        'is_night' => 'boolean',
    ];

    public function weekday(): BelongsTo
    {
        return $this->belongsTo(Weekday::class, 'weekday_id');
    }

    public function choghadiyaType(): BelongsTo
    {
        return $this->belongsTo(ChoghadiyaType::class, 'choghadiya_type_id');
    }
}
