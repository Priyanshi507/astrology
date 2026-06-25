<?php

namespace Database\Seeders;

use App\Models\Yoga;
use Illuminate\Database\Seeder;

class YogaSeeder extends Seeder
{
    public function run(): void
    {
        // Seeded in yoga order — id becomes the positional lookup key (1=Vishkambha … 27=Vaidhriti)
        // advice_text and mood_label/color_hex are keyed to classification.
        $clsMeta = [
            'Subha'     => ['Auspicious',          'Favorable yoga for all positive activities.',            '#2e7a40'],
            'Ashubha'   => ['Inauspicious',         'Exercise caution; delay important decisions.',           '#c47a20'],
            'Mahavisha' => ['Highly Inauspicious',  'Highly unfavorable — avoid all new beginnings today.',  '#b83020'],
        ];

        $rows = [
            ['Vishkambha', 'Inauspicious', 'Saturn',  'Yama',        'Mahavisha', 'Obstructed progress; avoid starting important work'   ],
            ['Priti',      'Auspicious',   'Mercury', 'Vishnu',      'Subha',     'Love and affection flourish; good for relationships'   ],
            ['Ayushman',   'Auspicious',   'Saturn',  'Brahma',      'Subha',     'Long life and health; good for medical treatments'     ],
            ['Saubhagya',  'Auspicious',   'Jupiter', 'Lakshmi',     'Subha',     'Fortune and prosperity; excellent for all undertakings'],
            ['Shobhana',   'Auspicious',   'Mars',    'Brihaspati',  'Subha',     'Brilliance and beauty; good for arts and beautification'],
            ['Atiganda',   'Inauspicious', 'Sun',     'Moon',        'Ashubha',   'Accidents and obstacles; proceed with caution'         ],
            ['Sukarma',    'Auspicious',   'Jupiter', 'Indra',       'Subha',     'Good deeds rewarded; excellent for charitable acts'    ],
            ['Dhriti',     'Auspicious',   'Saturn',  'Apsaras',     'Subha',     'Steadfastness and resolve; good for commitments'       ],
            ['Shoola',     'Inauspicious', 'Mars',    'Rudra',       'Ashubha',   'Sharp pain and conflict; avoid confrontations'         ],
            ['Ganda',      'Inauspicious', 'Sun',     'Agni',        'Ashubha',   'Danger and strife; be cautious with fire and sharp tools'],
            ['Vriddhi',    'Auspicious',   'Moon',    'Jaya',        'Subha',     'Growth and increase; excellent for investments and gains'],
            ['Dhruva',     'Auspicious',   'Mars',    'Brahma',      'Subha',     'Permanence and stability; good for laying foundations' ],
            ['Vyaghata',   'Inauspicious', 'Sun',     'Vayu',        'Ashubha',   'Sudden losses; avoid new ventures and travel'          ],
            ['Harshana',   'Auspicious',   'Mercury', 'Bhaga',       'Subha',     'Joy and delight; good for celebrations and entertainment'],
            ['Vajra',      'Inauspicious', 'Jupiter', 'Varuna',      'Ashubha',   'Thunderbolt — harsh results; be careful with water'    ],
            ['Siddhi',     'Auspicious',   'Venus',   'Ganesha',     'Subha',     'Accomplishment; best yoga for beginning any important act'],
            ['Vyatipata',  'Inauspicious', 'Rahu',    'Rudra',       'Mahavisha', 'Calamity; a very inauspicious yoga — avoid all new starts'],
            ['Variyana',   'Auspicious',   'Venus',   'Kubera',      'Subha',     'Wealth and comfort; good for luxury and financial matters'],
            ['Parigha',    'Inauspicious', 'Sun',     'Vishwakarma', 'Ashubha',   'Barrier and obstruction; difficult to complete tasks'  ],
            ['Shiva',      'Auspicious',   'Mercury', 'Shiva',       'Subha',     'Divine grace; excellent for spiritual worship and puja'],
            ['Siddha',     'Auspicious',   'Jupiter', 'Ganesha',     'Subha',     'Perfect accomplishment; all works succeed with ease'   ],
            ['Sadhya',     'Auspicious',   'Venus',   'Chandra',     'Subha',     'Achievable goals; moderate effort yields good results' ],
            ['Shubha',     'Auspicious',   'Mercury', 'Lakshmi',     'Subha',     'Pure auspiciousness; very good for all activities'     ],
            ['Shukla',     'Auspicious',   'Moon',    'Parvati',     'Subha',     'Brightness and clarity; excellent for creative work'   ],
            ['Brahma',     'Auspicious',   'Moon',    'Brahma',      'Subha',     'Creative power; excellent for starting new projects'   ],
            ['Indra',      'Auspicious',   'Sun',     'Indra',       'Subha',     'Kingly victory; good for competitive and bold endeavours'],
            ['Vaidhriti',  'Inauspicious', 'Saturn',  'Mitra',       'Mahavisha', 'Portends loss; a very inauspicious yoga — use caution'],
        ];

        foreach ($rows as [$name, $nature, $lord, $deity, $cls, $desc]) {
            [$mood, $advice, $color] = $clsMeta[$cls];
            Yoga::updateOrCreate(
                ['name' => $name],
                [
                    'name'            => $name,
                    'nature'          => $nature,
                    'ruling_lord'     => $lord,
                    'presiding_deity' => $deity,
                    'classification'  => $cls,
                    'description'     => $desc,
                    'mood_label'      => $mood,
                    'advice_text'     => $advice,
                    'color_hex'       => $color,
                ]
            );
        }
    }
}
