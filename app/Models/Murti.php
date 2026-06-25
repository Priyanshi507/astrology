<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Murti extends Model
{
    protected $fillable = [
        'murti_index', 'name', 'symbol',
        'quality_description', 'rank_order', 'bphs_reference',
        'phala_description', 'upaya_remedy',
    ];
}
