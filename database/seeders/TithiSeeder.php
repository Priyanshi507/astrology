<?php

namespace Database\Seeders;

use App\Models\Tithi;
use Illuminate\Database\Seeder;

class TithiSeeder extends Seeder
{
    public function run(): void
    {
        // Columns: [name, paksha, num, lord, nature, deity,
        //           vrat_name, vrat_deity, vrat_benefit, vrat_ritual, vrat_mantra, vrat_color,
        //           vivah_suitability, shraddha_name]
        // sort_order: 0-indexed (0=Shukla Pratipada … 14=Purnima, 15=Krishna Pratipada … 29=Amavasya)
        // vivah_suitability: Uttam for Shukla 2,3,5,7,10,11,12 (tithi_number); Varjit for all others.
        // shraddha_name: set for Krishna tithis 1–14 (Pitru Paksha observance names).
        $rows = [
            // --- Shukla Paksha ---
            ['Pratipada',   'Shukla',  1,  'Agni',      'Nanda (Auspicious)',   'Brahma',
             'Pratipada Vrat',       'Brahma',           'New beginnings, success in new ventures',
             'Worship Brahma at sunrise. Light a lamp.',
             'Om Brahmane Namah',    '#c48a2f',
             'Varjit', null],

            ['Dwitiya',     'Shukla',  2,  'Brahma',    'Bhadra (Prosperous)',  'Vidhatr',
             null, null, null, null, null, null,
             'Uttam', null],

            ['Tritiya',     'Shukla',  3,  'Kartikeya', 'Jaya (Victorious)',    'Gauri',
             null, null, null, null, null, null,
             'Uttam', null],

            ['Chaturthi',   'Shukla',  4,  'Yama',      'Rikta (Inauspicious)', 'Ganesh',
             'Vinayak Chaturthi',    'Ganesha',          'Removal of obstacles, wisdom, prosperity',
             'Offer modak to Ganesha. Recite Ganapati Atharvasheersham.',
             'Om Gam Ganapataye Namah', '#2e7a6e',
             'Varjit', null],

            ['Panchami',    'Shukla',  5,  'Moon',      'Purna (Full)',         'Naga',
             'Naga Panchami',        'Nagas',            'Protection from snake bite, family welfare',
             'Offer milk to Naga idol. Draw snake images with turmeric.',
             'Om Nagebhyo Namah',    '#1a5a1a',
             'Uttam', null],

            ['Shashthi',    'Shukla',  6,  'Kartikeya', 'Nanda',                'Kartikeya',
             'Skanda Shashthi',      'Kartikeya',        'Health of children, victory over enemies',
             'Observe fast. Worship Kartikeya with red flowers.',
             'Om Shanmukhaya Namah', '#b83020',
             'Varjit', null],

            ['Saptami',     'Shukla',  7,  'Sun',       'Bhadra',               'Surya',
             null, null, null, null, null, null,
             'Uttam', null],

            ['Ashtami',     'Shukla',  8,  'Shiva',     'Rikta',                'Rudra',
             'Durgashtami',          'Durga',            'Courage, destruction of evil, protection',
             'Special Durga puja. Recite Durga Saptashati.',
             'Om Durgayai Namah',    '#8e3a7a',
             'Varjit', null],

            ['Navami',      'Shukla',  9,  'Durga',     'Jaya',                 'Durga',
             null, null, null, null, null, null,
             'Varjit', null],

            ['Dashami',     'Shukla',  10, 'Yama',      'Purna',                'Dharma',
             null, null, null, null, null, null,
             'Uttam', null],

            ['Ekadashi',    'Shukla',  11, 'Vishnu',    'Jaya',                 'Vishnu',
             'Ekadashi Vrat',        'Vishnu',           'Cleansing of all sins, liberation (Moksha)',
             'Complete fast — no grains. Worship Vishnu. Night vigil.',
             'Om Namo Bhagavate Vasudevaya', '#1d4e6f',
             'Uttam', null],

            ['Dwadashi',    'Shukla',  12, 'Vishnu',    'Nanda',                'Hari',
             null, null, null, null, null, null,
             'Uttam', null],

            ['Trayodashi',  'Shukla',  13, 'Kama',      'Jaya',                 'Kama',
             'Trayodashi / Pradosh', 'Shiva',            'Relief from diseases, sins, great spiritual merit',
             'Pradosh fast. Visit Shiva temple at dusk.',
             'Om Namah Shivaya',     '#4a4060',
             'Varjit', null],

            ['Chaturdashi', 'Shukla',  14, 'Shiva',     'Rikta',                'Shiva',
             'Chaturdashi Vrat',     'Shiva',            'Destroys sins accumulated over lifetimes',
             'Worship Shiva with bel leaves. Fast till sunset.',
             'Om Namah Shivaya',     '#4a4060',
             'Varjit', null],

            ['Purnima',     'Shukla',  15, 'Moon',      'Purna',                'Moon',
             'Purnima Vrat',         'Vishnu / Moon',    'Wealth, fullness, blessings from ancestors',
             'Take sacred bath. Offer arghya to Moon. Charity.',
             'Om Namo Bhagavate Vasudevaya', '#1d4e6f',
             'Varjit', null],

            // --- Krishna Paksha ---
            ['Pratipada',   'Krishna', 1,  'Agni',      'Nanda',                'Brahma',
             null, null, null, null, null, null,
             'Varjit', 'Pratipad Shraddha'],

            ['Dwitiya',     'Krishna', 2,  'Brahma',    'Bhadra',               'Vidhatr',
             null, null, null, null, null, null,
             'Varjit', 'Dwitiya Shraddha'],

            ['Tritiya',     'Krishna', 3,  'Kartikeya', 'Jaya',                 'Gauri',
             null, null, null, null, null, null,
             'Varjit', 'Tritiya Shraddha'],

            ['Chaturthi',   'Krishna', 4,  'Yama',      'Rikta',                'Ganesh',
             'Sankashti Chaturthi',  'Ganesha',          'Removal of obstacles and hardships',
             'Fast till moonrise. Offer durva to Ganesha. See moon.',
             'Om Gam Ganapataye Namah', '#2e7a6e',
             'Varjit', 'Chaturthi Shraddha'],

            ['Panchami',    'Krishna', 5,  'Moon',      'Purna',                'Naga',
             null, null, null, null, null, null,
             'Varjit', 'Panchami Shraddha'],

            ['Shashthi',    'Krishna', 6,  'Kartikeya', 'Nanda',                'Kartikeya',
             null, null, null, null, null, null,
             'Varjit', 'Shashthi Shraddha'],

            ['Saptami',     'Krishna', 7,  'Sun',       'Bhadra',               'Surya',
             null, null, null, null, null, null,
             'Varjit', 'Saptami Shraddha'],

            ['Ashtami',     'Krishna', 8,  'Shiva',     'Rikta',                'Rudra',
             'Kalashtami / Bhairav Ashtami', 'Kaal Bhairav', 'Protection from enemies, fear, evil',
             'Worship Bhairav with sindoor. Night vigil.',
             'Om Kala Bhairavaya Namah', '#4a4060',
             'Varjit', 'Ashtami Shraddha'],

            ['Navami',      'Krishna', 9,  'Durga',     'Jaya',                 'Durga',
             null, null, null, null, null, null,
             'Varjit', 'Navami Shraddha (Avidhava Navami)'],

            ['Dashami',     'Krishna', 10, 'Yama',      'Purna',                'Dharma',
             null, null, null, null, null, null,
             'Varjit', 'Dashami Shraddha'],

            ['Ekadashi',    'Krishna', 11, 'Vishnu',    'Jaya',                 'Vishnu',
             'Ekadashi Vrat',        'Vishnu',           'Liberation from cycle of birth and death',
             'Complete fast — no grains. Night-long kirtan.',
             'Om Namo Bhagavate Vasudevaya', '#1d4e6f',
             'Varjit', 'Ekadashi Shraddha'],

            ['Dwadashi',    'Krishna', 12, 'Vishnu',    'Nanda',                'Hari',
             null, null, null, null, null, null,
             'Varjit', 'Dwadashi Shraddha'],

            ['Trayodashi',  'Krishna', 13, 'Kama',      'Jaya',                 'Kama',
             'Masik Shivratri',      'Shiva',            'Blessings of Shiva, release from Pasa (bondage)',
             'Fast and Shiva puja. Abhisheka with milk and honey.',
             'Om Namah Shivaya',     '#4a4060',
             'Varjit', 'Trayodashi Shraddha (Magha)'],

            ['Chaturdashi', 'Krishna', 14, 'Shiva',     'Rikta',                'Shiva',
             'Maha Shivratri / Shivratri', 'Shiva',      'Liberation, union with Shiva, destruction of ego',
             'All-night Shiva vigil. Abhisheka in 4 praharas.',
             'Om Namah Shivaya',     '#4a4060',
             'Varjit', 'Chaturdashi Shraddha (Ghata Chaturdashi)'],

            ['Amavasya',    'Krishna', 15, 'Pitrs',     'Nanda',                'Pitrs',
             'Amavasya Vrat',        'Pitrs / Ancestors', 'Peace for ancestors, relief from Pitru dosha',
             'Tarpana for ancestors. Light lamp at dusk. Charity.',
             'Om Pitribhyah Namah',  '#1a3a1a',
             'Varjit', null],
        ];

        foreach ($rows as $idx => [$name, $paksha, $num, $lord, $nature, $deity,
                           $vratName, $vratDeity, $vratBenefit, $vratRitual, $vratMantra, $vratColor,
                           $vivah, $shraddha]) {
            Tithi::updateOrCreate(
                ['paksha' => $paksha, 'tithi_number' => $num],
                [
                    'sort_order'      => $idx,
                    'name'            => $name,
                    'paksha'          => $paksha,
                    'tithi_number'    => $num,
                    'ruling_lord'     => $lord,
                    'nature'          => $nature,
                    'presiding_deity' => $deity,
                    'vrat_name'       => $vratName,
                    'vrat_deity'      => $vratDeity,
                    'vrat_benefit'    => $vratBenefit,
                    'vrat_ritual'     => $vratRitual,
                    'vrat_mantra'     => $vratMantra,
                    'vrat_color'      => $vratColor,
                    'vivah_suitability' => $vivah,
                    'shraddha_name'   => $shraddha,
                ]
            );
        }
    }
}
