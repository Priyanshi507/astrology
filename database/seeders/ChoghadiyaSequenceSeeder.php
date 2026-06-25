<?php

namespace Database\Seeders;

use App\Models\ChoghadiyaSequence;
use App\Models\ChoghadiyaType;
use App\Models\Weekday;
use Illuminate\Database\Seeder;

class ChoghadiyaSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $weekdayId = Weekday::pluck('id', 'dow_index');
        $typeId    = ChoghadiyaType::pluck('id', 'sequence_index');

        // Values are sequence_index into choghadiya_types: 0=Rog 1=Char 2=Labha 3=Amrit 4=Kaal 5=Shubha 6=Udveg
        $daySequences = [
            0 => [6, 1, 2, 3, 4, 5, 0, 6], // Sunday day
            1 => [3, 4, 5, 0, 6, 1, 2, 3], // Monday day
            2 => [0, 6, 1, 2, 3, 4, 5, 0], // Tuesday day
            3 => [2, 3, 4, 5, 0, 6, 1, 2], // Wednesday day
            4 => [5, 0, 6, 1, 2, 3, 4, 5], // Thursday day
            5 => [1, 2, 3, 4, 5, 0, 6, 1], // Friday day
            6 => [4, 5, 0, 6, 1, 2, 3, 4], // Saturday day
        ];

        $nightSequences = [
            0 => [5, 3, 1, 0, 4, 2, 6, 5], // Sunday night
            1 => [1, 0, 4, 2, 6, 5, 3, 1], // Monday night
            2 => [4, 2, 6, 5, 3, 1, 0, 4], // Tuesday night
            3 => [6, 5, 3, 1, 0, 4, 2, 6], // Wednesday night
            4 => [3, 1, 0, 4, 2, 6, 5, 3], // Thursday night
            5 => [0, 4, 2, 6, 5, 3, 1, 0], // Friday night
            6 => [2, 6, 5, 3, 1, 0, 4, 2], // Saturday night
        ];

        ChoghadiyaSequence::truncate();

        $rows = [];
        foreach ($daySequences as $dow => $slots) {
            foreach ($slots as $typeIdx) {
                $rows[] = [
                    'weekday_id'         => $weekdayId[$dow],
                    'is_night'           => false,
                    'choghadiya_type_id' => $typeId[$typeIdx],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        }

        foreach ($nightSequences as $dow => $slots) {
            foreach ($slots as $typeIdx) {
                $rows[] = [
                    'weekday_id'         => $weekdayId[$dow],
                    'is_night'           => true,
                    'choghadiya_type_id' => $typeId[$typeIdx],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            ChoghadiyaSequence::insert($chunk);
        }
    }
}
