<?php

namespace Database\Seeders;

use App\Models\Tara;
use Illuminate\Database\Seeder;

class TaraSeeder extends Seeder
{
    public function run(): void
    {
        // Data from TarabalMurtiService::TARA_DATA (BPHS Chapter 26)
        $rows = [
            [1, 'Janma',    false, 'Neutral',           '⚠', 'BPHS Ch.26: Janma Tara — bodily affliction and financial loss possible. Avoid for Muhurta.',                         'Fear, illness, bodily distress. Avoid for travel, marriage, new ventures.',                                                                    -5],
            [2, 'Sampat',   true,  'Auspicious',        '✦', 'BPHS Ch.26: Sampat Tara — wealth, prosperity and happiness. Highly auspicious.',                                    'Increase in wealth, prosperity, success in endeavours. Excellent for all Muhurtas.',                                                           10],
            [3, 'Vipat',    false, 'Inauspicious',      '✗', 'BPHS Ch.26: Vipat Tara — adversity, increase of enemies, financial loss. Avoid for Muhurta.',                       'Enemy threat, sudden adversity, obstacles in work. Avoid for all auspicious activities.',                                                     -10],
            [4, 'Kshema',   true,  'Auspicious',        '✦', 'BPHS Ch.26: Kshema Tara — welfare, health, happiness in family. Auspicious.',                                       'Health, family welfare, stability. Especially auspicious for travel and business.',                                                             8],
            [5, 'Pratyari', false, 'Inauspicious',      '✗', 'BPHS Ch.26: Pratyari Tara — enmity, discord, loss. Highly inauspicious.',                                           'Increase in enemies, quarrels, litigation. Especially avoided in marriage (more harmful than Naidhana).',                                    -12],
            [6, 'Sadhaka',  true,  'Auspicious',        '✦', 'BPHS Ch.26: Sadhaka Tara — achievement of purpose, success in endeavours. Auspicious.',                            'Fulfilment of desires, attainment, success. Excellent for initiation, study, beginning of business.',                                          8],
            [7, 'Naidhana', false, 'Inauspicious',      '✗', 'BPHS Ch.26: Naidhana Tara — death-like suffering, heavy loss. Avoid for Muhurta.',                                  'Life-threatening danger, heavy financial loss, separation from loved ones. Avoid all Muhurtas.',                                             -12],
            [8, 'Mitra',    true,  'Auspicious',        '✦', 'BPHS Ch.26: Mitra Tara — gain of friends, cooperation, prosperity. Auspicious.',                                    'Increase in friendships, social status, support. Excellent for partnerships and alliances.',                                                   8],
            [9, 'Atimitra', true,  'Highly Auspicious', '★', 'BPHS Ch.26: Atimitra Tara — great success, supreme auspiciousness. MC: Best of all Muhurtas.',                     'Supreme attainment, victory, great prosperity. Best for any endeavour.',                                                                      12],
        ];

        foreach ($rows as [$num, $name, $auspicious, $type, $icon, $bphs, $phala, $bonus]) {
            Tara::updateOrCreate(
                ['tara_number' => $num],
                [
                    'tara_number'        => $num,
                    'name'               => $name,
                    'is_auspicious'      => $auspicious,
                    'auspiciousness_type'=> $type,
                    'icon_symbol'        => $icon,
                    'bphs_reference'     => $bphs,
                    'phala_description'  => $phala,
                    'scoring_bonus'      => $bonus,
                ]
            );
        }
    }
}
