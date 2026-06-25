<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Master reference data — no FK dependencies
            PlanetSeeder::class,

            // Depends on planets
            ZodiacSignSeeder::class,

            // Depends on planets + zodiac_signs
            NakshatraSeeder::class,

            // Independent panchanga master tables
            TithiSeeder::class,
            YogaSeeder::class,
            KaranaSeeder::class,

            // Depends on planets
            WeekdaySeeder::class,

            // Independent
            ChoghadiyaTypeSeeder::class,

            // Depends on weekdays + choghadiya_types
            ChoghadiyaSequenceSeeder::class,

            // Tarabala / Murti Nirnaya master tables
            TaraSeeder::class,
            MurtiSeeder::class,

            // Ekadashi catalog
            EkadashiSeeder::class,

            // Festival rules (tithi-based, gregorian, sankranti triggers)
            FestivalRuleSeeder::class,

            // Astrological houses reference table
            AstrologicalHousesSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name'  => 'Test User', 'password' => bcrypt('password')]
        );
    }
}
