<?php

namespace Database\Seeders;

use App\Models\Planet;
use App\Models\ZodiacSign;
use Illuminate\Database\Seeder;

class ZodiacSignSeeder extends Seeder
{
    public function run(): void
    {
        $planetId = Planet::all()->mapWithKeys(fn($p) => [strtolower($p->name) => $p->id]);

        // Seeded in sign order; sort_order (0-indexed) and id (1-indexed) are the positional keys.
        // vasya_signs: 0-indexed sign indices that this sign commands in Vasya Koota.
        // varna: social order for Ashtakoot Varna Koota (Brahmin/Kshatriya/Vaishya/Shudra).
        $signs = [
            // [name, english, symbol, abbr, lord, element, modality, sort_order, varna, vasya_signs]
            ['Mesha',      'Aries',       '♈', 'Ar', 'mars',    'Fire',  'Cardinal', 0,  'Kshatriya', [4, 7]     ],
            ['Vrishabha',  'Taurus',      '♉', 'Ta', 'venus',   'Earth', 'Fixed',    1,  'Vaishya',   [3, 9]     ],
            ['Mithuna',    'Gemini',      '♊', 'Ge', 'mercury', 'Air',   'Mutable',  2,  'Shudra',    [5]        ],
            ['Karka',      'Cancer',      '♋', 'Cn', 'moon',    'Water', 'Cardinal', 3,  'Brahmin',   [7]        ],
            ['Simha',      'Leo',         '♌', 'Le', 'sun',     'Fire',  'Fixed',    4,  'Kshatriya', [0]        ],
            ['Kanya',      'Virgo',       '♍', 'Vi', 'mercury', 'Earth', 'Mutable',  5,  'Vaishya',   [2, 9]     ],
            ['Tula',       'Libra',       '♎', 'Li', 'venus',   'Air',   'Cardinal', 6,  'Shudra',    [9]        ],
            ['Vrishchika', 'Scorpio',     '♏', 'Sc', 'mars',    'Water', 'Fixed',    7,  'Brahmin',   [3]        ],
            ['Dhanu',      'Sagittarius', '♐', 'Sg', 'jupiter', 'Fire',  'Mutable',  8,  'Kshatriya', [11]       ],
            ['Makara',     'Capricorn',   '♑', 'Cp', 'saturn',  'Earth', 'Cardinal', 9,  'Vaishya',   [0, 3]     ],
            ['Kumbha',     'Aquarius',    '♒', 'Aq', 'saturn',  'Air',   'Fixed',    10, 'Shudra',    [0]        ],
            ['Meena',      'Pisces',      '♓', 'Pi', 'jupiter', 'Water', 'Mutable',  11, 'Brahmin',   [9]        ],
        ];

        foreach ($signs as [$name, $english, $symbol, $abbr, $lordKey, $element, $modality, $sortOrder, $varna, $vasya]) {
            ZodiacSign::updateOrCreate(
                ['name' => $name],
                [
                    'name'           => $name,
                    'english_name'   => $english,
                    'symbol'         => $symbol,
                    'abbreviation'   => $abbr,
                    'lord_planet_id' => $planetId[$lordKey],
                    'element'        => $element,
                    'modality'       => $modality,
                    'sort_order'     => $sortOrder,
                    'varna'          => $varna,
                    'vasya_signs'    => json_encode($vasya),
                ]
            );
        }
    }
}
