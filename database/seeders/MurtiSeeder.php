<?php

namespace Database\Seeders;

use App\Models\Murti;
use Illuminate\Database\Seeder;

class MurtiSeeder extends Seeder
{
    public function run(): void
    {
        // Data from TarabalMurtiService::MURTI_DATA (BPHS Ch.87 + Muhurta Chintamani)
        // Formula: (Vara index + Birth Nak index + Moon Nak index) mod 4
        $rows = [
            [0, 'Swarna (Gold)',   '◈', 'Highly Auspicious', 1, 'BPHS Ch.87: Swarna Murti — most excellent state. Divine grace, great fortune, abundant wealth.',      'Marriage: highly auspicious, prosperous union. Travel: complete success. Business: great profit. Long life and fame.',   'Donate gold, offer Surya Arghya, recite Shri Sukta'],
            [1, 'Rajata (Silver)', '◇', 'Auspicious',        2, 'BPHS Ch.87: Rajata Murti — auspicious state. Moderate to good results. Satisfactory outcomes.',        'Marriage: auspicious, happy life. Travel: successful. Business: profitable. Good health.',                               'Donate silver, offer Chandra Arghya, recite Chandranamaashtaka'],
            [2, 'Tamra (Copper)',  '◉', 'Moderate',          3, 'BPHS Ch.87: Tamra Murti — moderate state. Some obstacles possible; remedies make it auspicious.',       'Marriage: moderate, some discord possible. Travel: obstacles possible. Business: moderate profit. Remedies advised.',    'Mars worship, donate red cloth, recite Hanuman Chalisa'],
            [3, 'Loha (Iron)',     '◆', 'Inauspicious',      4, 'BPHS Ch.87: Loha Murti — inauspicious state. Strong possibility of suffering, loss and obstruction.',   'Marriage: inauspicious, avoid. Travel: dangerous. Business: loss. Better to postpone activities.',                       'Saturn worship, donate black sesame, recite Shani Stotra, Mahamrityunjaya Japa'],
        ];

        foreach ($rows as [$idx, $name, $symbol, $quality, $rank, $bphs, $phala, $upaya]) {
            Murti::updateOrCreate(
                ['murti_index' => $idx],
                [
                    'murti_index'         => $idx,
                    'name'                => $name,
                    'symbol'              => $symbol,
                    'quality_description' => $quality,
                    'rank_order'          => $rank,
                    'bphs_reference'      => $bphs,
                    'phala_description'   => $phala,
                    'upaya_remedy'        => $upaya,
                ]
            );
        }
    }
}
