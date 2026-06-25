<?php

namespace Database\Seeders;

use App\Models\Karana;
use Illuminate\Database\Seeder;

class KaranaSeeder extends Seeder
{
    public function run(): void
    {
        // Seeded in karana order — id becomes the positional lookup key
        // Chara (movable): id 1–7 (Bava…Vishti), Sthira (fixed): id 8–11 (Kimstughna…Naga)
        // Natural key for updateOrCreate: name (all 11 karanas have unique names)
        $rows = [
            ['Bava',        'Indra',   'Movable',      'Chara',  'Indra',   'Auspicious acts, travel'   ],
            ['Balava',      'Brahma',  'Movable',      'Chara',  'Brahma',  'Creative work, rituals'     ],
            ['Kaulava',     'Mitra',   'Movable',      'Chara',  'Mitra',   'Friendship, partnerships'   ],
            ['Taitila',     'Aryama',  'Movable',      'Chara',  'Aryaman', 'Domestic activities'        ],
            ['Garija',      'Prithvi', 'Movable',      'Chara',  'Bhumi',   'Agriculture, earth work'    ],
            ['Vanija',      'Lakshmi', 'Movable',      'Chara',  'Lakshmi', 'Trade, commerce, prosperity'],
            ['Vishti',      'Yama',    'Inauspicious', 'Chara',  'Yama',    'Avoid new beginnings'       ],
            ['Kimstughna',  'Sun',     'Auspicious',   'Sthira', 'Surya',   'Auspicious acts'            ],
            ['Shakuni',     'Vishnu',  'Mixed',        'Sthira', 'Vishnu',  'Mixed results'              ],
            ['Chatushpada', 'Brahma',  'Auspicious',   'Sthira', 'Rudra',   'Stability, rituals'         ],
            ['Naga',        'Vasuki',  'Inauspicious', 'Sthira', 'Naga',    'Avoid new acts'             ],
        ];

        foreach ($rows as [$name, $lord, $nature, $type, $deity, $favour]) {
            Karana::updateOrCreate(
                ['name' => $name],
                [
                    'name'                 => $name,
                    'ruling_lord'          => $lord,
                    'nature'               => $nature,
                    'karana_type'          => $type,
                    'presiding_deity'      => $deity,
                    'favourable_activities'=> $favour,
                ]
            );
        }
    }
}
