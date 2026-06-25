<?php

namespace Database\Seeders;

use App\Models\Planet;
use App\Models\Weekday;
use Illuminate\Database\Seeder;

class WeekdaySeeder extends Seeder
{
    public function run(): void
    {
        $planetId = Planet::all()->mapWithKeys(fn($p) => [strtolower($p->name) => $p->id]);

        // Muhurta timing values — day divided into 8 equal parts from sunrise to sunset.
        // rahu_kala_part / yamaganda_part / gulika_part: which 1/8 part is inauspicious (1-based).
        // Rahu Kaal  = [8, 2, 7, 5, 6, 4, 3] by dow 0-6 (verified: commit a43935f)
        // Yamaganda  = [5, 4, 3, 2, 1, 7, 6] by dow 0-6 (verified: commit a43935f)
        // Gulika     = [7, 6, 5, 4, 3, 2, 1] by dow 0-6 (verified: commit a43935f)
        // durmuhurta_parts: array of inauspicious 1/8 day part numbers for that weekday.
        // gowri_sequence: 8 choghadiya_type sequence_index values — same as day choghadiya sequence.
        // vivah_suitability: Uttam = Mon/Wed/Thu/Fri; Varjit = Sun/Tue/Sat.

        $rows = [
            [
                'dow_index'            => 0,
                'name'                 => 'Ravivara',
                'english_name'         => 'Sunday',
                'lord_planet_id'       => $planetId['sun'],
                'symbol'               => '☀',
                'nature'               => 'Ugra (Fierce)',
                'presiding_deity'      => 'Surya',
                'deity_note'           => 'Lord of light and soul',
                'classification'       => 'Ugra',
                'classification_note'  => 'Fierce — suited for bold acts',
                'auspicious_activities'=> 'Travel, authority, medicine',
                'info_text'            => 'Sunday is ruled by the Sun (Surya). Excellent for activities relating to government, authority, father, medicine, and gold. The Sun-hora at sunrise amplifies power and confidence. Avoid confrontational disputes.',
                'vrats'                => json_encode([
                    ['name' => 'Surya Vrat', 'deity' => 'Surya (Sun)', 'benefit' => 'Health, vitality, success in work', 'ritual' => 'Offer red flowers to Sun at sunrise. Chant Aditya Hridayam.', 'mantra' => 'Om Suryaya Namah', 'color' => '#d4760a'],
                ]),
                'rahu_kala_part'       => 8,
                'yamaganda_part'       => 5,
                'gulika_part'          => 7,
                'durmuhurta_parts'     => json_encode([4]),
                'gowri_sequence'       => json_encode([6, 1, 2, 3, 4, 5, 0, 6]),
                'vivah_suitability'    => 'Varjit',
            ],
            [
                'dow_index'            => 1,
                'name'                 => 'Somavara',
                'english_name'         => 'Monday',
                'lord_planet_id'       => $planetId['moon'],
                'symbol'               => '☽',
                'nature'               => 'Saumya (Gentle)',
                'presiding_deity'      => 'Chandra',
                'deity_note'           => 'Lord of mind and emotions',
                'classification'       => 'Saumya',
                'classification_note'  => 'Gentle — suited for nurturing acts',
                'auspicious_activities'=> 'Family, travel, agriculture, healing',
                'info_text'            => 'Monday is ruled by the Moon (Chandra / Soma). Ideal for activities related to mother, home, water, emotions, and agriculture. Favourable for starting journeys northward.',
                'vrats'                => json_encode([
                    ['name' => 'Somvar Vrat', 'deity' => 'Shiva', 'benefit' => 'Fulfilment of desires, marital happiness', 'ritual' => 'Fast from sunrise to sunset. Offer milk and bel leaves to Shivalinga.', 'mantra' => 'Om Namah Shivaya', 'color' => '#1d4e6f'],
                    ['name' => 'Pradosh Vrat', 'deity' => 'Shiva-Parvati', 'benefit' => 'Removal of sins, peace and prosperity', 'ritual' => 'If Trayodashi tithi falls today — full Pradosh fast.', 'mantra' => 'Om Namah Shivaya', 'color' => '#2a6d9c', 'conditional' => 'trayodashi'],
                ]),
                'rahu_kala_part'       => 2,
                'yamaganda_part'       => 4,
                'gulika_part'          => 6,
                'durmuhurta_parts'     => json_encode([5]),
                'gowri_sequence'       => json_encode([3, 4, 5, 0, 6, 1, 2, 3]),
                'vivah_suitability'    => 'Uttam',
            ],
            [
                'dow_index'            => 2,
                'name'                 => 'Mangalavara',
                'english_name'         => 'Tuesday',
                'lord_planet_id'       => $planetId['mars'],
                'symbol'               => '♂',
                'nature'               => 'Ugra (Fierce)',
                'presiding_deity'      => 'Mangala',
                'deity_note'           => 'Lord of energy and courage',
                'classification'       => 'Ugra',
                'classification_note'  => 'Fierce — suited for courageous acts',
                'auspicious_activities'=> 'Physical work, surgery, law enforcement',
                'info_text'            => 'Tuesday is ruled by Mars (Mangala). Strong for activities requiring courage, physical exertion, surgery, and military matters.',
                'vrats'                => json_encode([
                    ['name' => 'Mangalvar Vrat', 'deity' => 'Hanuman / Mangal', 'benefit' => 'Courage, protection from enemies, strength', 'ritual' => 'Offer sindoor and red flowers to Hanuman. Eat once.', 'mantra' => 'Om Ham Hanumate Namah', 'color' => '#b83020'],
                    ['name' => 'Kaal Bhairav Vrat', 'deity' => 'Kaal Bhairav', 'benefit' => 'Protection, removal of fear', 'ritual' => 'If Ashtami falls today — special Bhairav puja.', 'mantra' => 'Om Kala Bhairavaya Namah', 'color' => '#8a1810', 'conditional' => 'ashtami'],
                ]),
                'rahu_kala_part'       => 7,
                'yamaganda_part'       => 3,
                'gulika_part'          => 5,
                'durmuhurta_parts'     => json_encode([3, 7]),
                'gowri_sequence'       => json_encode([0, 6, 1, 2, 3, 4, 5, 0]),
                'vivah_suitability'    => 'Varjit',
            ],
            [
                'dow_index'            => 3,
                'name'                 => 'Budhavara',
                'english_name'         => 'Wednesday',
                'lord_planet_id'       => $planetId['mercury'],
                'symbol'               => '☿',
                'nature'               => 'Saumya (Gentle)',
                'presiding_deity'      => 'Budha',
                'deity_note'           => 'Lord of intellect and communication',
                'classification'       => 'Saumya',
                'classification_note'  => 'Gentle — suited for intellectual acts',
                'auspicious_activities'=> 'Business, communication, education, trade',
                'info_text'            => 'Wednesday is ruled by Mercury (Budha). Excellent for trade, communication, writing, education, and business contracts.',
                'vrats'                => json_encode([
                    ['name' => 'Budhvar Vrat', 'deity' => 'Ganesha / Budha', 'benefit' => 'Intelligence, education, business success', 'ritual' => 'Worship Ganesha with green durva grass. Observe fast till noon.', 'mantra' => 'Om Gam Ganapataye Namah', 'color' => '#2e7a6e'],
                ]),
                'rahu_kala_part'       => 5,
                'yamaganda_part'       => 2,
                'gulika_part'          => 4,
                'durmuhurta_parts'     => json_encode([5, 7]),
                'gowri_sequence'       => json_encode([2, 3, 4, 5, 0, 6, 1, 2]),
                'vivah_suitability'    => 'Uttam',
            ],
            [
                'dow_index'            => 4,
                'name'                 => 'Guruvara',
                'english_name'         => 'Thursday',
                'lord_planet_id'       => $planetId['jupiter'],
                'symbol'               => '♃',
                'nature'               => 'Guru (Auspicious)',
                'presiding_deity'      => 'Brihaspati',
                'deity_note'           => 'Lord of wisdom and dharma',
                'classification'       => 'Guru',
                'classification_note'  => 'Auspicious — best for sacred acts',
                'auspicious_activities'=> 'Rituals, education, guru worship, marriage',
                'info_text'            => 'Thursday is ruled by Jupiter (Guru/Brihaspati). The most auspicious day for beginning spiritual practices, religious ceremonies, education, and marriage.',
                'vrats'                => json_encode([
                    ['name' => 'Guruvar Vrat', 'deity' => 'Vishnu / Brihaspati', 'benefit' => 'Knowledge, wealth, spiritual growth, good children', 'ritual' => 'Wear yellow. Offer yellow flowers to Vishnu. Eat once.', 'mantra' => 'Om Namo Bhagavate Vasudevaya', 'color' => '#7a5a10'],
                ]),
                'rahu_kala_part'       => 6,
                'yamaganda_part'       => 1,
                'gulika_part'          => 3,
                'durmuhurta_parts'     => json_encode([6]),
                'gowri_sequence'       => json_encode([5, 0, 6, 1, 2, 3, 4, 5]),
                'vivah_suitability'    => 'Uttam',
            ],
            [
                'dow_index'            => 5,
                'name'                 => 'Shukravara',
                'english_name'         => 'Friday',
                'lord_planet_id'       => $planetId['venus'],
                'symbol'               => '♀',
                'nature'               => 'Saumya (Gentle)',
                'presiding_deity'      => 'Shukra',
                'deity_note'           => 'Lord of love, arts, and luxury',
                'classification'       => 'Saumya',
                'classification_note'  => 'Gentle — suited for arts and love',
                'auspicious_activities'=> 'Marriage, arts, beauty, romance, luxury',
                'info_text'            => 'Friday is ruled by Venus (Shukra). Ideal for love, art, music, beauty treatments, and sensory pleasures.',
                'vrats'                => json_encode([
                    ['name' => 'Shukravar Vrat', 'deity' => 'Lakshmi / Shukra', 'benefit' => 'Prosperity, love, beauty, happiness in marriage', 'ritual' => 'Worship Lakshmi with lotus. Offer white flowers. Eat once.', 'mantra' => 'Om Shreem Mahalakshmyai Namah', 'color' => '#8e3a7a'],
                    ['name' => 'Santoshi Mata Vrat', 'deity' => 'Santoshi Mata', 'benefit' => 'Family harmony, fulfilment of all desires', 'ritual' => 'On every Friday — fast and listen to Santoshi Mata Katha.', 'mantra' => 'Om Santoshi Mata Namah', 'color' => '#c47a20'],
                ]),
                'rahu_kala_part'       => 4,
                'yamaganda_part'       => 7,
                'gulika_part'          => 2,
                'durmuhurta_parts'     => json_encode([2]),
                'gowri_sequence'       => json_encode([1, 2, 3, 4, 5, 0, 6, 1]),
                'vivah_suitability'    => 'Uttam',
            ],
            [
                'dow_index'            => 6,
                'name'                 => 'Shanivara',
                'english_name'         => 'Saturday',
                'lord_planet_id'       => $planetId['saturn'],
                'symbol'               => '♄',
                'nature'               => 'Sthira (Stable)',
                'presiding_deity'      => 'Shani',
                'deity_note'           => 'Lord of karma and discipline',
                'classification'       => 'Sthira',
                'classification_note'  => 'Stable — suited for enduring acts',
                'auspicious_activities'=> 'Long-term planning, discipline, oil treatments',
                'info_text'            => 'Saturday is ruled by Saturn (Shani). Best for activities requiring persistence, discipline, and long-term commitment.',
                'vrats'                => json_encode([
                    ['name' => 'Shanivar Vrat', 'deity' => 'Shani (Saturn)', 'benefit' => 'Relief from Sade-Sati, karmic protection', 'ritual' => 'Offer sesame oil to Shani idol. Feed crows. Wear black.', 'mantra' => 'Om Sham Shanaischaraya Namah', 'color' => '#4a4060'],
                    ['name' => 'Hanuman Puja', 'deity' => 'Hanuman', 'benefit' => 'Strength, protection, removal of Shani dosha', 'ritual' => 'Read Hanuman Chalisa 11 times. Offer sindoor and oil.', 'mantra' => 'Om Ham Hanumate Namah', 'color' => '#b83020'],
                ]),
                'rahu_kala_part'       => 3,
                'yamaganda_part'       => 6,
                'gulika_part'          => 1,
                'durmuhurta_parts'     => json_encode([6, 8]),
                'gowri_sequence'       => json_encode([4, 5, 0, 6, 1, 2, 3, 4]),
                'vivah_suitability'    => 'Varjit',
            ],
        ];

        foreach ($rows as $data) {
            Weekday::updateOrCreate(
                ['dow_index' => $data['dow_index']],
                $data
            );
        }
    }
}
