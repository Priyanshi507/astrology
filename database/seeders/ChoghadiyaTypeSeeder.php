<?php

namespace Database\Seeders;

use App\Models\ChoghadiyaType;
use Illuminate\Database\Seeder;

class ChoghadiyaTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Data from MuhratCalculator::CHOGHADIYA_NAMES/NATURE/PLANET/COLOR
        // sort_order is the index used in the day/night sequence arrays
        $rows = [
            [0, 'Rog',   'Mars',    'Inauspicious'     ],
            [1, 'Char',  'Saturn',  'Neutral'          ],
            [2, 'Labha', 'Jupiter', 'Auspicious'       ],
            [3, 'Amrit', 'Moon',    'Highly Auspicious'],
            [4, 'Kaal',  'Sun',     'Inauspicious'     ],
            [5, 'Shubha','Venus',   'Auspicious'       ],
            [6, 'Udveg', 'Mercury', 'Inauspicious'     ],
        ];

        foreach ($rows as [$order, $name, $planet, $nature]) {
            ChoghadiyaType::updateOrCreate(
                ['sequence_index' => $order],
                [
                    'sequence_index' => $order,
                    'name'          => $name,
                    'ruling_planet' => $planet,
                    'nature'        => $nature,
                ]
            );
        }
    }
}
