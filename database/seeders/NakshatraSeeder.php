<?php

namespace Database\Seeders;

use App\Models\Nakshatra;
use App\Models\Planet;
use App\Models\ZodiacSign;
use Illuminate\Database\Seeder;

class NakshatraSeeder extends Seeder
{
    public function run(): void
    {
        $planetId = Planet::all()->mapWithKeys(fn($p) => [strtolower($p->name) => $p->id]);
        $signIds  = ZodiacSign::orderBy('id')->pluck('id')->toArray();

        // Columns: [name, deity, lord_key, sign_index(0–11),
        //           gana, yoni, nadi, tattva, guna, muhurta_quality, muhurta_auspiciousness_score,
        //           vivah, griha, vahana, mundan, sampatti, is_panchak,
        //           good_for, avoid, display_color]
        // vivah: Uttam/Madhyam/Varjit  griha/vahana/mundan/sampatti: Good/Bad/Neutral
        $rows = [
            // 0 Ashwini
            ['Ashwini',          'Ashwini Kumaras',  'ketu',    0,  'Deva',     'Horse',    'Adi',    'Fire',  'Sattvic', 'Kshipra (Quick)',  0,
             'Madhyam', 'Neutral', 'Good',    'Good',    'Neutral', false,
             'Medical treatments, quick journeys, learning',        'Long-term commitments',          '#1a5a1a'],
            // 1 Bharani
            ['Bharani',          'Yama',             'venus',   0,  'Manushya', 'Elephant', 'Madhya', 'Earth', 'Rajasic', 'Ugra (Fierce)',    4,
             'Varjit',  'Bad',     'Bad',     'Bad',     'Bad',     false,
             'Bold actions, fire rituals, destruction',             'Auspicious ceremonies',          '#8a2010'],
            // 2 Krittika
            ['Krittika',         'Agni',             'sun',     0,  'Rakshasa', 'Sheep',    'Antya',  'Ether', 'Tamasic', 'Mishra (Mixed)',   2,
             'Varjit',  'Bad',     'Bad',     'Neutral', 'Neutral', false,
             'Fire ceremonies, cooking, purification',              'Sensitive negotiations',          '#c56408'],
            // 3 Rohini
            ['Rohini',           'Brahma',           'moon',    1,  'Manushya', 'Serpent',  'Antya',  'Air',   'Sattvic', 'Dhruva (Fixed)',   0,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Coronations, sowing seeds, long-term starts',         'Nothing — highly auspicious',    '#2e7a2e'],
            // 4 Mrigashira
            ['Mrigashira',       'Soma',             'mars',    1,  'Deva',     'Serpent',  'Madhya', 'Water', 'Rajasic', 'Mridu (Soft)',     1,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Romantic activities, arts, learning music',           'Harsh confrontations',           '#1d6090'],
            // 5 Ardra
            ['Ardra',            'Rudra',            'rahu',    2,  'Manushya', 'Dog',      'Adi',    'Fire',  'Tamasic', 'Tikshna (Sharp)',  4,
             'Varjit',  'Bad',     'Bad',     'Bad',     'Bad',     false,
             'Destruction, surgery, bold actions',                  'Auspicious new beginnings',      '#6a2090'],
            // 6 Punarvasu
            ['Punarvasu',        'Aditi',            'jupiter', 2,  'Deva',     'Cat',      'Adi',    'Earth', 'Sattvic', 'Chara (Movable)',  1,
             'Madhyam', 'Good',    'Good',    'Good',    'Neutral', false,
             'Return journeys, second chances, healing',            'Permanent decisions',            '#1d8090'],
            // 7 Pushya
            ['Pushya',           'Brihaspati',       'saturn',  3,  'Deva',     'Sheep',    'Madhya', 'Ether', 'Rajasic', 'Mridu (Soft)',     0,
             'Madhyam', 'Good',    'Good',    'Good',    'Good',    false,
             'All auspicious works — the best nakshatra',           'Marriage (traditionally)',       '#2e7a6e'],
            // 8 Ashlesha
            ['Ashlesha',         'Nagas',            'mercury', 3,  'Rakshasa', 'Cat',      'Antya',  'Air',   'Tamasic', 'Tikshna (Sharp)',  3,
             'Varjit',  'Bad',     'Bad',     'Bad',     'Bad',     false,
             'Occult practices, trapping enemies',                  'New relationships, journeys',    '#4a1a6a'],
            // 9 Magha
            ['Magha',            'Pitris',           'ketu',    4,  'Rakshasa', 'Rat',      'Antya',  'Water', 'Sattvic', 'Ugra (Fierce)',    3,
             'Uttam',   'Neutral', 'Neutral', 'Good',    'Neutral', false,
             'Ancestor worship, authority, government work',        'New ventures',                   '#6a3800'],
            // 10 Purva Phalguni
            ['Purva Phalguni',   'Bhaga',            'venus',   4,  'Manushya', 'Rat',      'Madhya', 'Fire',  'Rajasic', 'Ugra (Fierce)',    3,
             'Varjit',  'Neutral', 'Neutral', 'Neutral', 'Neutral', false,
             'Pleasure, relaxation, arts',                          'Serious disciplined work',       '#c56408'],
            // 11 Uttara Phalguni
            ['Uttara Phalguni',  'Aryaman',          'sun',     4,  'Manushya', 'Cow',      'Adi',    'Earth', 'Tamasic', 'Dhruva (Fixed)',   0,
             'Uttam',   'Neutral', 'Good',    'Good',    'Good',    false,
             'Marriage, long-term undertakings',                    'Nothing — very auspicious',      '#2e8060'],
            // 12 Hasta
            ['Hasta',            'Savitar',          'moon',    5,  'Deva',     'Buffalo',  'Adi',    'Ether', 'Sattvic', 'Kshipra (Quick)',  0,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Trade, craft, healing, dexterous work',               'Destructive actions',            '#1d6aaa'],
            // 13 Chitra
            ['Chitra',           'Vishwakarma',      'mars',    5,  'Rakshasa', 'Tiger',    'Madhya', 'Air',   'Rajasic', 'Mridu (Soft)',     2,
             'Madhyam', 'Good',    'Good',    'Good',    'Neutral', false,
             'Art, jewellery-making, decoration',                   'Confrontations',                 '#8e3a7a'],
            // 14 Swati
            ['Swati',            'Vayu',             'rahu',    6,  'Deva',     'Buffalo',  'Antya',  'Water', 'Tamasic', 'Chara (Movable)',  1,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Commerce, travel, learning new skills',               'Permanent constructions',        '#1d4e8f'],
            // 15 Vishakha
            ['Vishakha',         'Indra-Agni',       'jupiter', 6,  'Rakshasa', 'Tiger',    'Antya',  'Fire',  'Sattvic', 'Mishra (Mixed)',   2,
             'Madhyam', 'Neutral', 'Neutral', 'Neutral', 'Neutral', false,
             'Competitive activities, goal-setting',                'Partnership agreements',         '#b83020'],
            // 16 Anuradha
            ['Anuradha',         'Mitra',            'saturn',  7,  'Deva',     'Deer',     'Madhya', 'Earth', 'Rajasic', 'Mridu (Soft)',     1,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Friendship, groups, travel southward',                'Solitary activities',            '#2e6090'],
            // 17 Jyeshtha
            ['Jyeshtha',         'Indra',            'mercury', 7,  'Rakshasa', 'Deer',     'Adi',    'Ether', 'Tamasic', 'Tikshna (Sharp)',  3,
             'Varjit',  'Bad',     'Bad',     'Bad',     'Bad',     false,
             'Bold leadership, mantras, protection rituals',        'New relationships',              '#7a3a10'],
            // 18 Moola
            ['Moola',            'Nirrti',           'ketu',    8,  'Rakshasa', 'Dog',      'Adi',    'Air',   'Sattvic', 'Tikshna (Sharp)',  4,
             'Varjit',  'Bad',     'Bad',     'Bad',     'Bad',     false,
             'Medicine, investigating hidden matters',               'New positive beginnings',        '#5a1a1a'],
            // 19 Purva Ashadha
            ['Purva Ashadha',    'Apas',             'venus',   8,  'Manushya', 'Monkey',   'Madhya', 'Water', 'Rajasic', 'Ugra (Fierce)',    3,
             'Varjit',  'Bad',     'Neutral', 'Neutral', 'Neutral', false,
             'Invigoration, water activities, bold moves',          'Peaceful negotiations',          '#1a5a8a'],
            // 20 Uttara Ashadha
            ['Uttara Ashadha',   'Vishvedevas',      'sun',     8,  'Manushya', 'Mongoose', 'Antya',  'Fire',  'Tamasic', 'Dhruva (Fixed)',   0,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    false,
             'Permanent works, victory in competition',             'Nothing — highly auspicious',    '#2e7a40'],
            // 21 Shravana
            ['Shravana',         'Vishnu',           'moon',    9,  'Deva',     'Monkey',   'Antya',  'Earth', 'Sattvic', 'Chara (Movable)',  0,
             'Madhyam', 'Good',    'Good',    'Good',    'Good',    false,
             'Learning, listening, spiritual study',                'Aggressive activities',          '#1d4e6f'],
            // 22 Dhanishta
            ['Dhanishta',        'Ashta Vasus',      'mars',    9,  'Rakshasa', 'Lion',     'Madhya', 'Ether', 'Rajasic', 'Chara (Movable)',  1,
             'Madhyam', 'Good',    'Good',    'Good',    'Neutral', true,
             'Music, courage, financial activities',                'Marriage (traditionally)',       '#b83020'],
            // 23 Shatabhisha
            ['Shatabhisha',      'Varuna',           'rahu',    10, 'Rakshasa', 'Horse',    'Adi',    'Air',   'Tamasic', 'Chara (Movable)',  3,
             'Varjit',  'Neutral', 'Neutral', 'Neutral', 'Neutral', true,
             'Healing, occult, Vedic learning',                     'Public dealings',                '#1a3a8a'],
            // 24 Purva Bhadrapada
            ['Purva Bhadrapada', 'Aja Ekapada',      'jupiter', 10, 'Manushya', 'Lion',     'Adi',    'Water', 'Sattvic', 'Ugra (Fierce)',    3,
             'Varjit',  'Bad',     'Neutral', 'Neutral', 'Neutral', true,
             'Fierce tasks, ascetic practices',                     'Social events',                  '#4a2a8a'],
            // 25 Uttara Bhadrapada
            ['Uttara Bhadrapada','Ahir Budhyana',    'saturn',  11, 'Manushya', 'Cow',      'Madhya', 'Fire',  'Rajasic', 'Dhruva (Fixed)',   0,
             'Uttam',   'Neutral', 'Good',    'Good',    'Good',    true,
             'Marriage, charitable works, rain prayers',            'Nothing — auspicious',           '#1d6070'],
            // 26 Revati
            ['Revati',           'Pushan',           'mercury', 11, 'Deva',     'Elephant', 'Antya',  'Earth', 'Tamasic', 'Mridu (Soft)',     0,
             'Uttam',   'Good',    'Good',    'Good',    'Good',    true,
             'Travel, completion, nurturing activities',             'Starting from scratch',          '#6a3a8a'],
        ];

        foreach ($rows as $i => [$name, $deity, $lordKey, $signIdx,
                                  $gana, $yoni, $nadi, $tattva, $guna, $quality, $score,
                                  $vivah, $griha, $vahana, $mundan, $sampatti, $panchak,
                                  $goodFor, $avoid, $color]) {
            Nakshatra::updateOrCreate(
                ['name' => $name],
                [
                    'sort_order'                   => $i + 1,
                    'name'                         => $name,
                    'deity'                        => $deity,
                    'lord_planet_id'               => $planetId[$lordKey],
                    'zodiac_sign_id'               => $signIds[$signIdx],
                    'starting_degree'              => round($i * (360.0 / 27), 4),
                    'gana'                         => $gana,
                    'yoni'                         => $yoni,
                    'nadi'                         => $nadi,
                    'tattva'                       => $tattva,
                    'guna'                         => $guna,
                    'muhurta_quality'              => $quality,
                    'muhurta_auspiciousness_score' => $score,
                    'vivah_suitability'            => $vivah,
                    'griha_pravesh_suitability'    => $griha,
                    'vahana_suitability'           => $vahana,
                    'mundan_suitability'           => $mundan,
                    'sampatti_suitability'         => $sampatti,
                    'is_panchak'                   => $panchak,
                    'good_for'                     => $goodFor,
                    'avoid'                        => $avoid,
                    'display_color'                => $color,
                ]
            );
        }
    }
}
