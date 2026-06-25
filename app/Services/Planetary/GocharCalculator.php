<?php

namespace App\Services\Planetary;

use Illuminate\Support\Facades\DB;

/**
 * GocharCalculator — Planetary Transit Results (Gochar Phala)
 *
 * Mathematical basis:
 *   • Brihat Parashara Hora Shastra (BPHS) — Gochara Adhyaya
 *   • Transit (Gochar) of each graha is judged by counting its house
 *     FROM the Janma Rashi (natal Moon sign). Each planet has a fixed
 *     set of houses in which its transit gives auspicious results.
 *   • Saturn transiting the 12th, 1st or 2nd from Moon = Sade Sati.
 *     Saturn in the 4th or 8th from Moon = Dhaiya (Kantaka / Ashtama).
 *
 * No external API — every value is derived arithmetically from the
 * sidereal (Lahiri) planetary longitudes computed by AstroCalculator
 * (Jean Meeus algorithms).
 */
class GocharCalculator
{
    // ── Display order (algorithmic constant) ─────────────────────
    private const ORDER = ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu'];

    // ── Life-area categories with house groupings (logic constant) ─
    private const CATEGORIES = [
        ['en' => 'Wealth & Finance',     'houses' => [2, 11, 12]],
        ['en' => 'Career & Profession',  'houses' => [10, 6]],
        ['en' => 'Health & Vitality',    'houses' => [1, 6, 8]],
        ['en' => 'Family & Home',        'houses' => [2, 4]],
        ['en' => 'Love & Relationships', 'houses' => [5, 7]],
        ['en' => 'Education & Mind',     'houses' => [4, 5, 9]],
    ];

    // ── DB-backed static caches ───────────────────────────────────
    private static ?array $ausp        = null; // [planet_key => [house, ...]]
    private static ?array $planetMeta  = null; // [planet_key => ['en','sym','col']]
    private static ?array $houses      = null; // [house_number => 'description']
    private static ?array $rashiEn     = null; // [0..11 => sign name]
    private static ?array $rashiLord   = null; // [0..11 => planet name]

    private static function loadFromDB(): void
    {
        if (self::$ausp !== null) return;

        // Planet names, symbols, colors, and gochar auspicious houses
        $planets = DB::table('planets')
            ->get(['name', 'symbol', 'color_hex', 'gochar_auspicious_houses']);

        self::$ausp       = [];
        self::$planetMeta = [];
        foreach ($planets as $p) {
            $key = strtolower($p->name);
            self::$planetMeta[$key] = [
                'en'  => $p->name,
                'sym' => $p->symbol ?? '',
                'col' => $p->color_hex ?? '#888888',
            ];
            if ($p->gochar_auspicious_houses) {
                self::$ausp[$key] = json_decode($p->gochar_auspicious_houses, true) ?? [];
            }
        }

        // House descriptions
        self::$houses = [];
        $houseRows = DB::table('astrological_houses')
            ->orderBy('house_number')
            ->get(['house_number', 'description_en']);
        foreach ($houseRows as $h) {
            self::$houses[(int)$h->house_number] = $h->description_en;
        }

        // Zodiac sign names and ruling planets (ordered 0=Mesha … 11=Meena)
        self::$rashiEn   = [];
        self::$rashiLord = [];
        $signs = DB::table('zodiac_signs')
            ->join('planets', 'zodiac_signs.lord_planet_id', '=', 'planets.id')
            ->orderBy('zodiac_signs.sort_order')
            ->get(['zodiac_signs.name as sign_name', 'planets.name as lord_name']);
        foreach ($signs as $s) {
            self::$rashiEn[]   = $s->sign_name;
            self::$rashiLord[] = $s->lord_name;
        }
    }

    /**
     * @param int   $natalMoonSign  0..11 (Janma Rashi)
     * @param array $transit        [pid => ['sign'=>0..11,'signName'=>string,'retro'=>bool]]
     */
    public static function calculate(int $natalMoonSign, array $transit): array
    {
        self::loadFromDB();

        $ratingColors = [
            'Favourable'  => '#2e7d52',
            'Challenging' => '#b13e3e',
            'Mixed'       => '#c48a2f',
            'Stable'      => '#1d6aa0',
        ];

        $rashis = [];
        for ($r = 0; $r < 12; $r++) {
            $rows = [];
            $good = 0;
            foreach (self::ORDER as $pid) {
                $t     = $transit[$pid];
                $house = (($t['sign'] - $r + 12) % 12) + 1;
                $ausp  = in_array($house, self::$ausp[$pid] ?? [], true);
                if ($ausp) $good++;

                $hEn = self::$houses[$house] ?? '';
                $pEn = self::$planetMeta[$pid]['en'] ?? ucfirst($pid);
                $ord = self::ordinal($house);

                $rows[] = [
                    'pid'         => $pid,
                    'en'          => $pEn,
                    'sym'         => self::$planetMeta[$pid]['sym'] ?? '',
                    'col'         => self::$planetMeta[$pid]['col'] ?? '#888888',
                    'signName'    => $t['signName'],
                    'retro'       => $t['retro'],
                    'house'       => $house,
                    'ausp'        => $ausp,
                    'phalEn'      => $ausp
                        ? "Favourable transit through your {$ord} house of {$hEn}."
                        : "Testing transit through your {$ord} house of {$hEn} — stay patient.",
                    'ordinalText' => $ord,
                    'auspColor'   => $ausp ? '#2e7d52' : '#b13e3e',
                    'auspBg'      => $ausp ? '#eaf6ef' : '#fbecec',
                    'auspLabel'   => $ausp ? 'Auspicious' : 'Inauspicious',
                ];
            }

            $satHouse = (($transit['saturn']['sign'] - $r + 12) % 12) + 1;
            $sade     = in_array($satHouse, [12, 1, 2], true);
            $dhaiya   = in_array($satHouse, [4, 8], true);

            $sadePhase = '';
            if ($sade) {
                $sadePhase = $satHouse === 12 ? 'Rising Phase (12th from Moon)'
                          : ($satHouse === 1  ? 'Peak Phase (1st from Moon)'
                                              : 'Setting Phase (2nd from Moon)');
            }
            $dhaiyaType = '';
            if ($dhaiya) {
                $dhaiyaType = $satHouse === 4 ? 'Kantaka Shani (4th from Moon)'
                                             : 'Ashtama Shani (8th from Moon)';
            }

            // Life-area outlook
            $cats = [];
            foreach (self::CATEGORIES as $c) {
                $members = [];
                $net = 0;
                foreach ($rows as $row) {
                    if (in_array($row['house'], $c['houses'], true)) {
                        $net += $row['ausp'] ? 1 : -1;
                        $members[] = [
                            'sym'         => $row['sym'],
                            'col'         => $row['col'],
                            'en'          => $row['en'],
                            'house'       => $row['house'],
                            'fav'         => $row['ausp'],
                            'mc'          => $row['ausp'] ? '#2e7d52' : '#b13e3e',
                            'ordinalText' => $row['ordinalText'],
                        ];
                    }
                }
                if (empty($members))  { $rating = 'Stable'; }
                elseif ($net > 0)     { $rating = 'Favourable'; }
                elseif ($net < 0)     { $rating = 'Challenging'; }
                else                  { $rating = 'Mixed'; }

                if (empty($members)) {
                    $noteEn = 'No major transit influencing this area at present — conditions stay steady.';
                } else {
                    $favEn = []; $cauEn = [];
                    foreach ($members as $m) {
                        if ($m['fav']) $favEn[] = $m['en'].' ('.$m['ordinalText'].')';
                        else           $cauEn[] = $m['en'].' ('.$m['ordinalText'].')';
                    }
                    $noteEn = ($favEn ? 'Supported by '.implode(', ', $favEn).'. ' : '')
                            . ($cauEn ? 'Caution from '.implode(', ', $cauEn).'.' : '');
                }

                $cats[] = [
                    'en'          => $c['en'],
                    'members'     => $members,
                    'rating'      => $rating,
                    'ratingColor' => $ratingColors[$rating] ?? '#1d6aa0',
                    'noteEn'      => $noteEn,
                ];
            }

            if ($good >= 6)     { $vLabel = 'Excellent';   $vCol = '#2e7d52'; }
            elseif ($good >= 4) { $vLabel = 'Favourable';  $vCol = '#1d6aa0'; }
            elseif ($good >= 2) { $vLabel = 'Mixed';       $vCol = '#c48a2f'; }
            else                { $vLabel = 'Challenging';  $vCol = '#b13e3e'; }

            $rashis[] = [
                'idx'             => $r,
                'en'              => self::$rashiEn[$r] ?? '',
                'lord'            => self::$rashiLord[$r] ?? '',
                'good'            => $good,
                'rows'            => $rows,
                'cats'            => $cats,
                'satHouse'        => $satHouse,
                'sade'            => $sade,
                'sadePhase'       => $sadePhase,
                'dhaiya'          => $dhaiya,
                'dhaiyaType'      => $dhaiyaType,
                'isNatal'         => ($r === $natalMoonSign),
                'visible'         => ($r === $natalMoonSign),
                'vLabel'          => $vLabel,
                'vCol'            => $vCol,
                'satHouseOrdinal' => self::ordinal($satHouse),
            ];
        }

        return [
            'natalMoonSign' => $natalMoonSign,
            'natalName'     => self::$rashiEn[$natalMoonSign] ?? '',
            'rashis'        => $rashis,
        ];
    }

    private static function ordinal(int $n): string
    {
        $s = ['th', 'st', 'nd', 'rd'];
        $v = $n % 100;
        return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
    }
}
