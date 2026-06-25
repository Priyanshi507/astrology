<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChoghadiyaType extends Model
{
    protected $fillable = [
        'sequence_index', 'name', 'ruling_planet', 'nature',
    ];

    public function sequences(): HasMany
    {
        return $this->hasMany(ChoghadiyaSequence::class, 'choghadiya_type_id');
    }
}
