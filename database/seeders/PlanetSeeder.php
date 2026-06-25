<?php

namespace Database\Seeders;

use App\Models\Planet;
use Illuminate\Database\Seeder;

class PlanetSeeder extends Seeder
{
    public function run(): void
    {
        $planets = [
            [
                'vimshottari_order'      => 0,
                'name'                   => 'Ketu',
                'vedic_name'             => 'Ketu',
                'symbol'                 => '☋',
                'abbreviation'           => 'Ke',
                'color_hex'              => '#a0440e',
                'vimshottari_dasha_years'=> 7,
                'is_always_retrograde'   => true,
            ],
            [
                'vimshottari_order'      => 1,
                'name'                   => 'Venus',
                'vedic_name'             => 'Shukra',
                'symbol'                 => '♀',
                'abbreviation'           => 'Ve',
                'color_hex'              => '#9c2d8a',
                'vimshottari_dasha_years'=> 20,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 2,
                'name'                   => 'Sun',
                'vedic_name'             => 'Surya',
                'symbol'                 => '☀',
                'abbreviation'           => 'Su',
                'color_hex'              => '#c47000',
                'vimshottari_dasha_years'=> 6,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 3,
                'name'                   => 'Moon',
                'vedic_name'             => 'Chandra',
                'symbol'                 => '☽',
                'abbreviation'           => 'Mo',
                'color_hex'              => '#1a7ab5',
                'vimshottari_dasha_years'=> 10,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 4,
                'name'                   => 'Mars',
                'vedic_name'             => 'Mangala',
                'symbol'                 => '♂',
                'abbreviation'           => 'Ma',
                'color_hex'              => '#c0311f',
                'vimshottari_dasha_years'=> 7,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 5,
                'name'                   => 'Rahu',
                'vedic_name'             => 'Rahu',
                'symbol'                 => '☊',
                'abbreviation'           => 'Ra',
                'color_hex'              => '#1a7a3a',
                'vimshottari_dasha_years'=> 18,
                'is_always_retrograde'   => true,
            ],
            [
                'vimshottari_order'      => 6,
                'name'                   => 'Jupiter',
                'vedic_name'             => 'Guru',
                'symbol'                 => '♃',
                'abbreviation'           => 'Ju',
                'color_hex'              => '#b36000',
                'vimshottari_dasha_years'=> 16,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 7,
                'name'                   => 'Saturn',
                'vedic_name'             => 'Shani',
                'symbol'                 => '♄',
                'abbreviation'           => 'Sa',
                'color_hex'              => '#5a4a8a',
                'vimshottari_dasha_years'=> 19,
                'is_always_retrograde'   => false,
            ],
            [
                'vimshottari_order'      => 8,
                'name'                   => 'Mercury',
                'vedic_name'             => 'Budha',
                'symbol'                 => '☿',
                'abbreviation'           => 'Me',
                'color_hex'              => '#0a8c5a',
                'vimshottari_dasha_years'=> 17,
                'is_always_retrograde'   => false,
            ],
        ];

        foreach ($planets as $data) {
            Planet::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
