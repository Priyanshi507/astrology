<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AstrologicalHousesSeeder extends Seeder
{
    public function run(): void
    {
        $houses = [
            [1,  'self, health, mind & vitality',         'Physical body, personality, life force, general health, head, appearance'],
            [2,  'wealth, family & speech',               'Accumulated wealth, family, speech, food, right eye, face, values'],
            [3,  'courage, siblings & efforts',           'Younger siblings, courage, short travels, communication, hands, efforts'],
            [4,  'home, mother, property & peace',        'Mother, homeland, property, vehicles, heart, inner peace, education'],
            [5,  'children, intellect, romance & studies','Children, intelligence, creativity, romance, speculation, past merit'],
            [6,  'enemies, debts, competition & health',  'Enemies, disease, debts, service, daily routine, conflict, digestion'],
            [7,  'marriage, partnership & travel',        'Spouse, partnerships, open enemies, business, long journeys, desire'],
            [8,  'obstacles, sudden change & longevity',  'Longevity, transformation, inheritance, hidden matters, occult, crises'],
            [9,  'fortune, dharma, father & wisdom',      'Father, fortune, higher learning, religion, philosophy, long journeys'],
            [10, 'career, status & authority',            'Career, reputation, public standing, government, father, ambition'],
            [11, 'gains, income & fulfilment of desires', 'Income, elder siblings, social circle, goals, aspirations, profits'],
            [12, 'expenses, losses, foreign & moksha',    'Expenditure, foreign lands, liberation, isolation, hidden enemies, sleep'],
        ];

        foreach ($houses as [$num, $desc, $themes]) {
            DB::table('astrological_houses')->updateOrInsert(
                ['house_number' => $num],
                ['description_en' => $desc, 'key_themes' => $themes]
            );
        }
    }
}
