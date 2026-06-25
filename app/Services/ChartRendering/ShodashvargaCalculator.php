<?php

namespace App\Services\ChartRendering;

use Illuminate\Support\Facades\DB;

/**
 * ShodashvargaCalculator — Complete 16 Divisional Charts (Shodashvarga)
 *
 * Mathematical rules follow Brihat Parasara Hora Shastra (BPHS),
 * as systematised in Komilla Sutton's "The Essentials of Vedic Astrology"
 * and B.V. Raman's "Graha and Bhava Balas".
 *
 * All input planet positions must be SIDEREAL (post-ayanamsa) tropical longitudes
 * passed through AstroCalculator::n360($trop - $ayan).
 *
 * Divisional charts included (Shodashvarga = 16 primary vargas):
 *   D1  — Rashi           (Natal / physical body)
 *   D2  — Hora            (Wealth)
 *   D3  — Drekkana        (Siblings / courage)
 *   D4  — Chaturthamsha   (Property / fortune)
 *   D7  — Saptamamsha     (Children / progeny)
 *   D9  — Navamsha        (Spouse / dharma / soul)
 *   D10 — Dashamsha       (Career / profession)
 *   D12 — Dwadashamsha    (Parents)
 *   D16 — Shodasha        (Vehicles / comforts)
 *   D20 — Vimsha          (Spiritual practice)
 *   D24 — Chaturvimsha    (Education / learning)
 *   D27 — Nakshatramsha   (Strength / vitality)
 *   D30 — Trimsha         (Misfortunes / evils)
 *   D40 — Khavedamsha     (Auspicious / inauspicious effects)
 *   D45 — Aksha Vedamsha  (All general indications)
 *   D60 — Shashtiamsha    (Past karma / all matters)
 *
 * Additional high-use vargas also included:
 *   D5  — Panchamamsha    (Fame / authority)
 *   D6  — Shashthamsha    (Enemies / health / debts)
 *   D8  — Ashtamamsha     (Longevity / sudden events)
 *   D11 — Ekadasha        (Gains)
 */
class ShodashvargaCalculator
{
    private static ?array $signs       = null; // [0..11 => 'Mesha', ...]
    private static ?array $signLords   = null; // [0..11 => 'Mars', ...]
    private static ?array $signElement = null; // [0..11 => 0|1|2|3] Fire=0,Earth=1,Air=2,Water=3
    private static ?array $signNature  = null; // [0..11 => 0|1|2] Movable=0,Fixed=1,Dual=2

    private static function loadData(): void
    {
        if (self::$signs !== null) return;
        self::$signs      = [];
        self::$signLords  = [];
        self::$signElement = [];
        self::$signNature  = [];
        $elementMap  = ['Fire' => 0, 'Earth' => 1, 'Air' => 2, 'Water' => 3];
        $modalityMap = ['Cardinal' => 0, 'Fixed' => 1, 'Mutable' => 2];
        $i = 0;
        foreach (
            DB::table('zodiac_signs as z')
                ->join('planets as p', 'z.lord_planet_id', '=', 'p.id')
                ->orderBy('z.id')
                ->get(['z.name', 'z.element', 'z.modality', 'p.name as lord_name'])
            as $s
        ) {
            self::$signs[$i]       = $s->name;
            self::$signLords[$i]   = $s->lord_name;
            self::$signElement[$i] = $elementMap[$s->element]   ?? 0;
            self::$signNature[$i]  = $modalityMap[$s->modality] ?? 0;
            $i++;
        }
    }

    // ── D60 Shashtiamsha names (60 divisions, alternating Odd/Even sign pattern)
    // Odd signs: sequence repeats. Even signs: reversed
    private const SHASHTI_ODD = [
        'Ghora','Rakshasa','Deva','Kubera','Yaksha','Kinnara','Bhrashta',
        'Kulaghna','Garala','Vahni','Maya','Purishaka','Apampathi','Marut',
        'Kaala','Sarpa','Amrita','Indu','Mridu','Komal','Heramba',
        'Brahma','Vishnu','Maheshwara','Deva','Ardra','Kalinasha','Kshem',
        'Utpata','Kaal','Saumya','Komala','Sheetala','Karaladamshtra','Chandramukhi',
        'Praveena','Kaalp avak','Dhanusha','Nirmala','Saumya','Kroora','Atisheetala',
        'Amrita','Payodhi','Brahmana','Vaishnava','Shaiva','Paapi','Googoola',
        'Mrityu','Kaala','Davagni','Ghora','Yama','Kantaka','Sudha',
        'Amrita','Poornachandra','Vishadagdha','Kulanasha',
    ];

    private const SHASHTI_EVEN = [
        'Amrita','Saumya','Komala','Sheetala','Karaladamshtra','Chandramukhi',
        'Praveena','Kaalpavak','Dhanusha','Nirmala','Saumya','Kroora','Atisheetala',
        'Amrita','Payodhi','Brahmana','Vaishnava','Shaiva','Paapi','Googoola',
        'Mrityu','Kaala','Davagni','Ghora','Yama','Kantaka','Sudha',
        'Amrita','Poornachandra','Vishadagdha','Kulanasha','Ghora','Rakshasa','Deva',
        'Kubera','Yaksha','Kinnara','Bhrashta','Kulaghna','Garala','Vahni',
        'Maya','Purishaka','Apampathi','Marut','Kaala','Sarpa','Amrita',
        'Indu','Mridu','Komal','Heramba','Brahma','Vishnu','Maheshwara',
        'Deva','Ardra','Kalinasha','Kshem','Utpata',
    ];

    // ── Trimsha (D30) lords — separate for Odd and Even signs ────
    // Odd signs (Mesha, Mithuna, Simha, Tula, Dhanu, Kumbha):
    //   Mars 0–5°, Saturn 5–10°, Jupiter 10–18°, Mercury 18–25°, Venus 25–30°
    // Even signs (Vrishabha, Karka, Kanya, Vrishchika, Makara, Meena):
    //   Venus 0–5°, Mercury 5–12°, Jupiter 12–20°, Moon 20–25°, Mars 25–30°
    private const TRIMSHA_ODD  = [
        ['Mars',   0,  5],
        ['Saturn', 5,  10],
        ['Jupiter',10, 18],
        ['Mercury',18, 25],
        ['Venus',  25, 30],
    ];
    private const TRIMSHA_EVEN = [
        ['Venus',  0,  5],
        ['Mercury',5,  12],
        ['Jupiter',12, 20],
        ['Moon',   20, 25],
        ['Mars',   25, 30],
    ];

    // ── Hora (D2) rule ────────────────────────────────────────────
    // Odd sign, first 15°  → Sun's Hora (Leo)
    // Odd sign, second 15° → Moon's Hora (Cancer)
    // Even sign, first 15° → Moon's Hora (Cancer)
    // Even sign, second 15°→ Sun's Hora (Leo)

    // ── Drekkana (D3) rule ────────────────────────────────────────
    // 1st drekkana (0–10°)  → same sign
    // 2nd drekkana (10–20°) → 5th sign from it (trine)
    // 3rd drekkana (20–30°) → 9th sign from it (trine)

    // ── Navamsha (D9) ─────────────────────────────────────────────
    // Each sign = 9 navamshas of 3°20′ each
    // Fire signs start from Mesha, Earth from Makara, Air from Tula, Water from Karka

    private const NAVAMSHA_START = [
        0,  // Mesha    (Fire)  → starts at Mesha  (0)
        9,  // Vrishabha(Earth) → starts at Makara (9)
        6,  // Mithuna  (Air)   → starts at Tula   (6)
        3,  // Karka    (Water) → starts at Karka  (3)
        0,  // Simha    (Fire)
        9,  // Kanya    (Earth)
        6,  // Tula     (Air)
        3,  // Vrishchika(Water)
        0,  // Dhanu    (Fire)
        9,  // Makara   (Earth)
        6,  // Kumbha   (Air)
        3,  // Meena    (Water)
    ];

    // ── Vimsha (D20) start signs ─────────────────────────────────
    // Movable signs → Mesha (0), Fixed → Sagittarius (8), Dual → Leo (4)
    private const VIMSHA_START = [0, 4, 8]; // indexed by SIGN_NATURE

    // ── Chaturvimsha (D24) start signs ───────────────────────────
    // Odd signs → Leo (4), Even signs → Cancer (3)
    private const D24_START = [4, 3]; // odd=Leo, even=Cancer

    // ── Nakshatramsha (D27) start signs ──────────────────────────
    // Fire signs → Mesha(0), Earth → Karka(3), Air → Tula(6), Water → Makara(9)  — alternate: (0,6,0,3)
    // Actually by BPHS: Fire→Mesha, Earth→Makara, Air→Tula, Water→Karka
    private const D27_START = [0, 9, 6, 3]; // Fire, Earth, Air, Water

    // ── Khavedamsha (D40) start signs ────────────────────────────
    // Odd signs → Mesha (0), Even signs → Tula (6)
    private const D40_START = [0, 6];

    // ── Aksha Vedamsha (D45) start signs ─────────────────────────
    // Movable → Mesha (0), Fixed → Leo (4), Dual → Sagittarius (8)
    private const D45_START = [0, 4, 8];


    
    // ════════════════════════════════════════════════════════════
    //  PUBLIC ENTRY POINT
    // ════════════════════════════════════════════════════════════

    /**
     * Calculate all 20 divisional charts for all planets + Ascendant.
     *
     * @param array  $planets  Associative array from AstroCalculator::calculate()
     *                         Each entry has 'sider' => float (sidereal longitude 0–360)
     * @param float  $ascSider Sidereal ascendant longitude (0–360)
     * @return array           Structured varga data
     */
    public static function calculateAll(array $planets, float $ascSider): array
    {
        self::loadData();
        $vargas = [];

        // Build a unified point list: ascendant + all planets
        $points = ['ascendant' => $ascSider];
        foreach ($planets as $pid => $pdata) {
            $points[$pid] = $pdata['sider'];
        }

        $VARGA_LIST = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            11, 12, 16, 20, 24, 27, 30, 40, 45, 60,
        ];

        foreach ($VARGA_LIST as $d) {
            $vargas['D'.$d] = self::calculateVarga($d, $points, $planets);
        }

        // Add Varga Viswa (composite dignity score) per planet
        $vargas['dignities'] = self::calculateVargaDignities($vargas, $points);

        // Add Vimshopaka Bala (20-point strength) per planet
        $vargas['vimshopaka'] = self::calculateVimshopaka($vargas, $points);

        return $vargas;
    }

    /**
     * Calculate a single Varga (divisional chart).
     *
     * @param int   $d      Division number (1, 2, 3 … 60)
     * @param array $points ['pid' => siderealLongitude, ...]
     * @param array $planets Full planet array (for retrograde etc.)
     * @return array
     */
    public static function calculateVarga(int $d, array $points, array $planets = []): array
    {
        self::loadData();
        $result = [
            'division'    => $d,
            'name'        => self::vargaName($d),
            'signification'=> self::vargaSignification($d),
            'planets'     => [],
        ];

        foreach ($points as $pid => $sider) {
            $signIdx  = (int)floor($sider / 30.0);       // 0–11
            $degInSign= fmod($sider, 30.0);              // 0–30

            $vargaSign = match($d) {
                1  => self::d1($signIdx),
                2  => self::d2($signIdx, $degInSign),
                3  => self::d3($signIdx, $degInSign),
                4  => self::d4($signIdx, $degInSign),
                5  => self::d5($signIdx, $degInSign),
                6  => self::d6($signIdx, $degInSign),
                7  => self::d7($signIdx, $degInSign),
                8  => self::d8($signIdx, $degInSign),
                9  => self::d9($signIdx, $degInSign),
                10 => self::d10($signIdx, $degInSign),
                11 => self::d11($signIdx, $degInSign),
                12 => self::d12($signIdx, $degInSign),
                16 => self::d16($signIdx, $degInSign),
                20 => self::d20($signIdx, $degInSign),
                24 => self::d24($signIdx, $degInSign),
                27 => self::d27($signIdx, $degInSign),
                30 => self::d30($signIdx, $degInSign),
                40 => self::d40($signIdx, $degInSign),
                45 => self::d45($signIdx, $degInSign),
                60 => self::d60($signIdx, $degInSign),
                default => $signIdx,
            };

            $vargaSign = (($vargaSign % 12) + 12) % 12;
            $dignity   = self::getPlanetDignity($pid, $vargaSign);

            $entry = [
                'sider'       => $sider,
                'signIdx'     => $signIdx,
                'sign'        => self::$signs[$signIdx],
                'degInSign'   => round($degInSign, 4),
                'vargaSignIdx'=> $vargaSign,
                'vargaSign'   => self::$signs[$vargaSign],
                'vargaLord'   => self::$signLords[$vargaSign],
                'dignity'     => $dignity,
            ];

            // Add special data for specific vargas
            if ($d === 30) {
                $entry['trimshaLord'] = self::getTrimshaLord($signIdx, $degInSign);
            }
            if ($d === 60) {
                $entry['shashtiName'] = self::getShashtiName($signIdx, $degInSign);
                $entry['shashtiNature'] = self::getShashtiNature($signIdx, $degInSign);
            }
            if (isset($planets[$pid])) {
                $entry['retro'] = $planets[$pid]['retro'] ?? false;
            }

            $result['planets'][$pid] = $entry;
        }

        return $result;
    }


    // ════════════════════════════════════════════════════════════
    //  DIVISIONAL CHART ALGORITHMS (BPHS rules)
    // ════════════════════════════════════════════════════════════

    /**
     * D1 — Rashi (Natal chart)
     * Simply the birth sign itself.
     */
    private static function d1(int $signIdx): int
    {
        return $signIdx;
    }

    /**
     * D2 — Hora (Wealth)
     *
     * Rule (BPHS Ch.6):
     *   Odd signs (1,3,5…): first 15° = Sun's Hora (Leo=4), last 15° = Moon's Hora (Cancer=3)
     *   Even signs (2,4,6…): first 15° = Moon's Hora (Cancer=3), last 15° = Sun's Hora (Leo=4)
     *
     * Signifies: accumulation of wealth, financial resources
     */
    private static function d2(int $signIdx, float $deg): int
    {
        $isOdd    = ($signIdx % 2 === 0); // 0-based, so index 0 (Mesha) = odd
        $isFirst  = ($deg < 15.0);

        if ($isOdd) {
            return $isFirst ? 4 : 3;  // Leo : Cancer
        } else {
            return $isFirst ? 3 : 4;  // Cancer : Leo
        }
    }

    /**
     * D3 — Drekkana (Siblings, courage, travels)
     *
     * Rule (BPHS Ch.6):
     *   1st drekkana (0–10°)  → same sign
     *   2nd drekkana (10–20°) → 5th sign counted from the birth sign
     *   3rd drekkana (20–30°) → 9th sign counted from the birth sign
     */
    private static function d3(int $signIdx, float $deg): int
    {
        $part = (int)floor($deg / 10.0); // 0, 1, or 2
        return ($signIdx + $part * 4) % 12;
    }

    /**
     * D4 — Chaturthamsha (Property, fixed assets, fortune)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 4 parts of 7°30′ each.
     *   Movable signs: Mesha, Karka, Tula, Makara (every 4th starting sign)
     *   Fixed signs:   Leo, Scorpio, Aquarius, Taurus (offset by 4)
     *   Dual signs:    Sagittarius, Pisces, Gemini, Virgo (offset by 8)
     *
     *   Count the Chaturthamsha from: Movable→Mesha, Fixed→Leo, Dual→Sagittarius
     */
    private static function d4(int $signIdx, float $deg): int
    {
        $part     = (int)floor($deg / 7.5); // 0–3
        $nature   = self::$signNature[$signIdx]; // 0=Movable,1=Fixed,2=Dual
        $startMap = [0 => 0, 1 => 4, 2 => 8]; // Mesha, Leo, Sagittarius
        return ($startMap[$nature] + $part) % 12;
    }

    /**
     * D5 — Panchamamsha (Fame, authority, power)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 5 parts of 6° each.
     *   Odd signs: count from Mesha (0)
     *   Even signs: count from Tula (6)
     */
    private static function d5(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / 6.0); // 0–4
        $isOdd = ($signIdx % 2 === 0);   // 0-indexed; Mesha(0) is odd
        $start = $isOdd ? 0 : 6;         // Mesha or Tula
        return ($start + $part) % 12;
    }

    /**
     * D6 — Shashthamsha (Enemies, diseases, debts)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 6 parts of 5° each.
     *   Odd signs: count from Mesha (0)
     *   Even signs: count from Tula (6)
     *   (Same starting points as D5 but 6 divisions of 5°)
     */
    private static function d6(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / 5.0); // 0–5
        $isOdd = ($signIdx % 2 === 0);
        $start = $isOdd ? 0 : 6;
        return ($start + $part) % 12;
    }

    /**
     * D7 — Saptamamsha (Children, grandchildren, progeny)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 7 parts of 4°17′08.57″ each.
     *   Odd signs: count from the same sign (self)
     *   Even signs: count from the 7th sign (opposite sign)
     */
    private static function d7(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / (30.0 / 7.0)); // 0–6
        $isOdd = ($signIdx % 2 === 0);
        $start = $isOdd ? $signIdx : ($signIdx + 6) % 12;
        return ($start + $part) % 12;
    }

    /**
     * D8 — Ashtamamsha (Longevity, sudden events, hidden matters)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 8 parts of 3°45′ each.
     *   Movable signs: count from Mesha (0)
     *   Fixed signs:   count from Sagittarius (8)
     *   Dual signs:    count from Leo (4)
     *
     *   Note: This is also used for Ashtaka Varga calculations.
     */
    private static function d8(int $signIdx, float $deg): int
    {
        $part     = (int)floor($deg / 3.75); // 0–7
        $nature   = self::$signNature[$signIdx];
        $startMap = [0 => 0, 1 => 8, 2 => 4]; // Movable→Mesha, Fixed→Sagittarius, Dual→Leo
        return ($startMap[$nature] + $part) % 12;
    }

    /**
     * D9 — Navamsha (Spouse, dharma, soul quality, inner nature)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 9 parts of 3°20′ each.
     *   Fire signs  (Mesha, Simha, Dhanu)     : count from Mesha (0)
     *   Earth signs (Vrishabha, Kanya, Makara) : count from Makara (9)
     *   Air signs   (Mithuna, Tula, Kumbha)   : count from Tula (6)
     *   Water signs (Karka, Vrishchika, Meena): count from Karka (3)
     *
     *   This is the MOST important divisional chart after D1.
     */
    private static function d9(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / (30.0 / 9.0)); // 0–8 (each = 3°20′)
        $start = self::NAVAMSHA_START[$signIdx];
        return ($start + $part) % 12;
    }

    /**
     * D10 — Dashamsha (Career, profession, social status, actions in world)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 10 parts of 3° each.
     *   Odd signs:  count from the same sign
     *   Even signs: count from 9th sign from it
     *
     *   This is the primary chart for career and professional analysis.
     */
    private static function d10(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / 3.0); // 0–9
        $isOdd = ($signIdx % 2 === 0);
        $start = $isOdd ? $signIdx : ($signIdx + 8) % 12; // same sign OR 9th from it
        return ($start + $part) % 12;
    }

    /**
     * D11 — Ekadasha (Gains, profits, elder siblings, aspirations)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 11 parts of 2°43′38″ each.
     *   Odd signs:  count from the same sign
     *   Even signs: count from the 3rd sign from it (odd signs start from self,
     *               even signs start from the 3rd sign)
     *
     *   Note: Some texts say Even signs count from the sign itself +2.
     *         The standard rule is: Odd → self, Even → 11th from self.
     */
    private static function d11(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / (30.0 / 11.0)); // 0–10
        $isOdd = ($signIdx % 2 === 0);
        $start = $isOdd ? $signIdx : ($signIdx + 10) % 12; // odd=self, even=11th from self
        return ($start + $part) % 12;
    }

    /**
     * D12 — Dwadashamsha (Parents, grandparents, ancestry)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 12 parts of 2°30′ each.
     *   Count from the same sign itself.
     *   1st dwadashamsha → same sign, 2nd → next sign, etc.
     */
    private static function d12(int $signIdx, float $deg): int
    {
        $part = (int)floor($deg / 2.5); // 0–11
        return ($signIdx + $part) % 12;
    }

    /**
     * D16 — Shodasha / Kalamsha (Vehicles, comforts, luxuries, happiness)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 16 parts of 1°52′30″ each.
     *   Movable signs: count from Mesha (0)
     *   Fixed signs:   count from Leo (4)
     *   Dual signs:    count from Sagittarius (8)
     */
    private static function d16(int $signIdx, float $deg): int
    {
        $part     = (int)floor($deg / 1.875); // 0–15
        $nature   = self::$signNature[$signIdx];
        $startMap = [0 => 0, 1 => 4, 2 => 8]; // Movable→Mesha, Fixed→Leo, Dual→Sagittarius
        return ($startMap[$nature] + $part) % 12;
    }

    /**
     * D20 — Vimsha (Spiritual practice, worship, piety, religious bent)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 20 parts of 1°30′ each.
     *   Movable signs: count from Mesha (0)
     *   Fixed signs:   count from Sagittarius (8)
     *   Dual signs:    count from Leo (4)
     */
    private static function d20(int $signIdx, float $deg): int
    {
        $part   = (int)floor($deg / 1.5); // 0–19
        $nature = self::$signNature[$signIdx];
        return (self::VIMSHA_START[$nature] + $part) % 12;
    }

    /**
     * D24 — Chaturvimsha / Siddhamsha (Education, learning, wisdom, achievements)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 24 parts of 1°15′ each.
     *   Odd signs:  count from Leo (4)
     *   Even signs: count from Cancer (3)
     */
    private static function d24(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / 1.25); // 0–23
        $isOdd = ($signIdx % 2 === 0);    // 0-indexed, Mesha=odd
        return (self::D24_START[$isOdd ? 0 : 1] + $part) % 12;
    }

    /**
     * D27 — Nakshatramsha / Bhamsha (Strength, vitality, inherent nature)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 27 parts of 1°6′40″ each.
     *   Fire signs  (Mesha, Simha, Dhanu)     : count from Mesha (0)
     *   Earth signs (Vrishabha, Kanya, Makara) : count from Makara (9)  ← some texts say Karka
     *   Air signs   (Mithuna, Tula, Kumbha)   : count from Tula (6)
     *   Water signs (Karka, Vrishchika, Meena): count from Karka (3)
     *
     *   This gives the same element-based start as D9 but divided into 27.
     */
    private static function d27(int $signIdx, float $deg): int
    {
        $part    = (int)floor($deg / (30.0 / 27.0)); // 0–26
        $element = self::$signElement[$signIdx];
        return (self::D27_START[$element] + $part) % 12;
    }

    /**
     * D30 — Trimsha (Misfortunes, evil effects, calamities)
     *
     * Rule (BPHS Ch.6) — UNIQUE: not equal divisions.
     *
     *   Odd signs: Mars 0–5°, Saturn 5–10°, Jupiter 10–18°, Mercury 18–25°, Venus 25–30°
     *              Mapped to: Mesha, Kumbha, Dhanu, Mithuna, Tula
     *   Even signs: Venus 0–5°, Mercury 5–12°, Jupiter 12–20°, Moon 20–25°, Mars 25–30°
     *              Mapped to: Vrishabha, Kanya, Meena, Vrishchika, Mesha
     *
     *   Important: The Sun is NOT a Trimsha lord. Rahu/Ketu use the sign they're in.
     */
    private static function d30(int $signIdx, float $deg): int
    {
        $isOdd = ($signIdx % 2 === 0); // 0-indexed

        // Odd sign Trimsha sign assignments (planet → varga sign)
        static $oddSignMap  = ['Mars'=>0, 'Saturn'=>10, 'Jupiter'=>8, 'Mercury'=>2, 'Venus'=>6];
        // Even sign Trimsha sign assignments
        static $evenSignMap = ['Venus'=>1, 'Mercury'=>5, 'Jupiter'=>10, 'Moon'=>7,  'Mars'=>0];

        $lord = self::getTrimshaLord($signIdx, $deg);

        if ($isOdd) {
            return $oddSignMap[$lord] ?? 0;
        } else {
            return $evenSignMap[$lord] ?? 0;
        }
    }

    /**
     * D40 — Khavedamsha (Auspicious/inauspicious effects, general happiness)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 40 parts of 0°45′ each.
     *   Odd signs:  count from Mesha (0)
     *   Even signs: count from Tula (6)
     */
    private static function d40(int $signIdx, float $deg): int
    {
        $part  = (int)floor($deg / 0.75); // 0–39
        $isOdd = ($signIdx % 2 === 0);
        return (self::D40_START[$isOdd ? 0 : 1] + $part) % 12;
    }

    /**
     * D45 — Aksha Vedamsha (General indications, all matters, overall life picture)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 45 parts of 0°40′ each.
     *   Movable signs: count from Mesha (0)
     *   Fixed signs:   count from Leo (4)
     *   Dual signs:    count from Sagittarius (8)
     */
    private static function d45(int $signIdx, float $deg): int
    {
        $part   = (int)floor($deg / (30.0 / 45.0)); // 0–44, each = 0°40′
        $nature = self::$signNature[$signIdx];
        return (self::D45_START[$nature] + $part) % 12;
    }

    /**
     * D60 — Shashtiamsha (Past karma, all matters — MOST POTENT divisional)
     *
     * Rule (BPHS Ch.6):
     *   Divide sign into 60 parts of 0°30′ each.
     *   Odd signs:  use standard sequence (self::SHASHTI_ODD)
     *   Even signs: use reversed sequence (self::SHASHTI_EVEN)
     *
     *   The Shashtiamsha lord is the most powerful indicator of past karma.
     *   Each Shashti has a name and nature (benefic/malefic/mixed).
     */
    private static function d60(int $signIdx, float $deg): int
    {
        // Each shashtiamsha = 0°30′. The varga sign cycles through all 12 signs.
        $part  = (int)floor($deg / 0.5); // 0–59
        $isOdd = ($signIdx % 2 === 0);
        $start = $isOdd ? $signIdx : ($signIdx + 6) % 12; // odd=self, even=7th
        return ($start + $part) % 12;
    }


    // ════════════════════════════════════════════════════════════
    //  DIGNITY ASSESSMENT
    // ════════════════════════════════════════════════════════════

    /**
     * Get a planet's dignity in a given sign.
     *
     * Dignities: Exalted > Own > Moolatrikona > Friend > Neutral > Enemy > Debilitated
     *
     * @param string $pid      Planet ID (sun, moon, mercury, venus, mars, jupiter, saturn, rahu, ketu)
     * @param int    $signIdx  0-based sign index
     * @return string
     */
    private static function getPlanetDignity(string $pid, int $signIdx): string
    {
        static $EXALT = [
            'sun'     => 0,   // Mesha
            'moon'    => 1,   // Vrishabha
            'mercury' => 5,   // Kanya
            'venus'   => 11,  // Meena
            'mars'    => 9,   // Makara
            'jupiter' => 3,   // Karka
            'saturn'  => 6,   // Tula
            'rahu'    => 2,   // Mithuna (Taurus by some)
            'ketu'    => 8,   // Dhanu (Scorpio by some)
        ];
        static $DEBIL = [
            'sun'     => 6,   // Tula
            'moon'    => 7,   // Vrishchika
            'mercury' => 11,  // Meena
            'venus'   => 5,   // Kanya
            'mars'    => 3,   // Karka
            'jupiter' => 9,   // Makara
            'saturn'  => 0,   // Mesha
            'rahu'    => 8,   // Dhanu
            'ketu'    => 2,   // Mithuna
        ];
        // Own signs (Moolatrikona sign listed first where different)
        static $OWN = [
            'sun'     => [4],        // Simha
            'moon'    => [3],        // Karka
            'mercury' => [2, 5],     // Mithuna, Kanya
            'venus'   => [1, 6],     // Vrishabha, Tula
            'mars'    => [0, 7],     // Mesha, Vrishchika
            'jupiter' => [8, 11],    // Dhanu, Meena
            'saturn'  => [9, 10],    // Makara, Kumbha
            'rahu'    => [],         // No own sign consensus; omit
            'ketu'    => [],
        ];
        // Moolatrikona signs
        static $MOOLATRIKONA = [
            'sun'     => 4,   // Simha (first 20°)
            'moon'    => 1,   // Vrishabha (first 3°)
            'mercury' => 5,   // Kanya (15°–20°)
            'venus'   => 6,   // Tula
            'mars'    => 0,   // Mesha
            'jupiter' => 8,   // Dhanu
            'saturn'  => 10,  // Kumbha
        ];
        // Friendship table (permanent / natural)
        // friends[planet] = [list of friend planets]
        static $FRIENDS = [
            'sun'     => ['moon','mars','jupiter'],
            'moon'    => ['sun','mercury'],
            'mercury' => ['sun','venus'],
            'venus'   => ['mercury','saturn'],
            'mars'    => ['sun','moon','jupiter'],
            'jupiter' => ['sun','moon','mars'],
            'saturn'  => ['mercury','venus','rahu'],
            'rahu'    => ['venus','saturn','mercury'],
            'ketu'    => ['mars','venus','saturn'],
        ];
        static $ENEMIES = [
            'sun'     => ['venus','saturn'],
            'moon'    => ['none'],
            'mercury' => ['moon'],
            'venus'   => ['sun','moon'],
            'mars'    => ['mercury'],
            'jupiter' => ['mercury','venus'],
            'saturn'  => ['sun','moon','mars'],
            'rahu'    => ['sun','moon','mars'],
            'ketu'    => ['moon','sun'],
        ];

        if (!isset($EXALT[$pid])) return 'Neutral';

        if ($EXALT[$pid] === $signIdx)  return 'Exalted';
        if ($DEBIL[$pid] === $signIdx)  return 'Debilitated';
        if (in_array($signIdx, $OWN[$pid] ?? []))        return 'Own Sign';
        if (($MOOLATRIKONA[$pid] ?? -1) === $signIdx)    return 'Moolatrikona';

        // Determine sign lord and compare friendship
        $signLord = strtolower(self::$signLords[$signIdx]);
        if ($signLord === $pid) return 'Own Sign';

        $friends = array_map('strtolower', $FRIENDS[$pid] ?? []);
        $enemies = array_map('strtolower', $ENEMIES[$pid] ?? []);

        if (in_array($signLord, $friends))  return 'Friendly';
        if (in_array($signLord, $enemies))  return 'Inimical';
        return 'Neutral';
    }

    /**
     * Get Trimsha lord and sign for D30.
     */
    private static function getTrimshaLord(int $signIdx, float $deg): string
    {
        $isOdd  = ($signIdx % 2 === 0);
        $ranges = $isOdd ? self::TRIMSHA_ODD : self::TRIMSHA_EVEN;

        foreach ($ranges as [$lord, $from, $to]) {
            if ($deg >= $from && $deg < $to) {
                return $lord;
            }
        }
        // Edge case: exactly 30°
        return $ranges[count($ranges)-1][0];
    }

    /**
     * Get Shashtiamsha name.
     */
    private static function getShashtiName(int $signIdx, float $deg): string
    {
        $part   = (int)floor($deg / 0.5); // 0–59
        $isOdd  = ($signIdx % 2 === 0);
        $idx    = min($part, 59);

        if ($isOdd) {
            return self::SHASHTI_ODD[$idx]  ?? 'Unknown';
        } else {
            // Even signs count in reverse
            return self::SHASHTI_EVEN[59 - $idx] ?? 'Unknown';
        }
    }

    /**
     * Get Shashtiamsha nature (benefic/malefic/mixed).
     */
    private static function getShashtiNature(int $signIdx, float $deg): string
    {
        static $BENEFIC = [
            'Deva','Kubera','Amrita','Indu','Mridu','Komal','Brahma','Vishnu',
            'Maheshwara','Saumya','Komala','Sheetala','Chandramukhi','Nirmala',
            'Saumya','Poornachandra','Sudha','Payodhi','Vaishnava','Praveena',
        ];
        static $MALEFIC = [
            'Ghora','Rakshasa','Kulaghna','Garala','Vahni','Maya','Purishaka',
            'Kaala','Sarpa','Heramba','Bhrashta','Kaal','Kroora','Mrityu',
            'Davagni','Ghora','Yama','Kantaka','Paapi','Googoola',
            'Kalinasha','Utpata','Vishadagdha','Kulanasha','Atisheetala',
            'Karaladamshtra','Kaalpavak','Apampathi',
        ];

        $name = self::getShashtiName($signIdx, $deg);

        if (in_array($name, $BENEFIC)) return 'Benefic';
        if (in_array($name, $MALEFIC)) return 'Malefic';
        return 'Mixed';
    }


    // ════════════════════════════════════════════════════════════
    //  VIMSHOPAKA BALA (20-point Varga strength)
    // ════════════════════════════════════════════════════════════

    /**
     * Calculate Vimshopaka Bala (20-point strength) for each planet.
     *
     * Three Vimshopaka schemes exist per BPHS:
     *   Shadvarga   (6 vargas):  D1,D2,D3,D9,D12,D30
     *   Saptavarga  (7 vargas):  D1,D2,D3,D7,D9,D12,D30
     *   Dasavarga   (10 vargas): D1,D2,D3,D7,D9,D10,D12,D16,D30,D60
     *   Shodashvarga(16 vargas): All 16 main Vargas
     *
     * Points per dignity:
     *   Exalted=20, Own=15, Moolatrikona=12, Friendly=10,
     *   Neutral=8, Inimical=5, Debilitated=2 (each scaled by varga weight)
     *
     * We implement the Shodashvarga (16-varga) scheme weights:
     */
    private static array $SHODASHVARGA_WEIGHTS = [
        'D1'  => 3.5,
        'D2'  => 1.0,
        'D3'  => 1.0,
        'D4'  => 0.5,
        'D7'  => 0.5,
        'D9'  => 3.0,
        'D10' => 0.5,
        'D12' => 0.5,
        'D16' => 2.0,
        'D20' => 0.5,
        'D24' => 0.5,
        'D27' => 0.5,
        'D30' => 1.0,
        'D40' => 0.5,
        'D45' => 0.5,
        'D60' => 4.0,
        // Total = 20
    ];

    private static array $DIGNITY_POINTS = [
        'Exalted'      => 20,
        'Own Sign'     => 15,
        'Moolatrikona' => 12,
        'Friendly'     => 10,
        'Neutral'      => 8,
        'Inimical'     => 5,
        'Debilitated'  => 2,
    ];

    public static function calculateVimshopaka(array $vargas, array $points): array
    {
        $result = [];
        $pids   = array_keys($points);

        foreach ($pids as $pid) {
            $totalWeight = 0;
            $weightedPoints = 0;

            foreach (self::$SHODASHVARGA_WEIGHTS as $vargaKey => $weight) {
                if (!isset($vargas[$vargaKey]['planets'][$pid])) continue;

                $dignity = $vargas[$vargaKey]['planets'][$pid]['dignity'] ?? 'Neutral';
                $pts     = self::$DIGNITY_POINTS[$dignity] ?? 8;
                $weightedPoints += $pts * $weight;
                $totalWeight    += $weight * 20; // max points
            }

            $score = $totalWeight > 0 ? round(($weightedPoints / $totalWeight) * 20, 2) : 0;

            $result[$pid] = [
                'score'   => $score,
                'percent' => round($score / 20 * 100, 1),
                'grade'   => self::vimshopakGrade($score),
            ];
        }

        return $result;
    }

    private static function vimshopakGrade(float $score): string
    {
        if ($score >= 15) return 'Excellent';
        if ($score >= 12) return 'Good';
        if ($score >= 9)  return 'Average';
        if ($score >= 6)  return 'Below Average';
        return 'Weak';
    }

    /**
     * Calculate Varga Viswa (broad dignity assessment across vargas).
     */
    public static function calculateVargaDignities(array $vargas, array $points): array
    {
        $result = [];
        foreach (array_keys($points) as $pid) {
            $counts = ['Exalted'=>0,'Own Sign'=>0,'Moolatrikona'=>0,'Friendly'=>0,'Neutral'=>0,'Inimical'=>0,'Debilitated'=>0];
            $total  = 0;
            foreach ($vargas as $vKey => $vData) {
                if (!isset($vData['planets'][$pid])) continue;
                $dig = $vData['planets'][$pid]['dignity'] ?? 'Neutral';
                if (isset($counts[$dig])) {
                    $counts[$dig]++;
                    $total++;
                }
            }
            $result[$pid] = ['counts' => $counts, 'total' => $total];
        }
        return $result;
    }


    // ════════════════════════════════════════════════════════════
    //  UTILITY / METADATA
    // ════════════════════════════════════════════════════════════

    public static function vargaName(int $d): string
    {
        return match($d) {
            1  => 'Rashi',
            2  => 'Hora',
            3  => 'Drekkana',
            4  => 'Chaturthamsha',
            5  => 'Panchamamsha',
            6  => 'Shashthamsha',
            7  => 'Saptamamsha',
            8  => 'Ashtamamsha',
            9  => 'Navamsha',
            10 => 'Dashamsha',
            11 => 'Ekadasha',
            12 => 'Dwadashamsha',
            16 => 'Shodasha (Kalamsha)',
            20 => 'Vimsha',
            24 => 'Chaturvimsha (Siddhamsha)',
            27 => 'Nakshatramsha (Bhamsha)',
            30 => 'Trimsha',
            40 => 'Khavedamsha',
            45 => 'Aksha Vedamsha',
            60 => 'Shashtiamsha',
            default => "D{$d}",
        };
    }

    public static function vargaSignification(int $d): string
    {
        return match($d) {
            1  => 'Physical body, overall life, personality',
            2  => 'Wealth, financial accumulation, family',
            3  => 'Siblings, courage, short travels, valor',
            4  => 'Property, fixed assets, mother, education',
            5  => 'Fame, authority, power, past-life merit',
            6  => 'Enemies, diseases, debts, obstacles',
            7  => 'Children, progeny, creativity, grandchildren',
            8  => 'Longevity, sudden events, hidden matters, obstacles',
            9  => 'Spouse, dharma, soul quality, inner nature, fortune',
            10 => 'Career, profession, status, actions in world',
            11 => 'Gains, profits, elder siblings, aspirations, income',
            12 => 'Parents, grandparents, ancestry, past lives',
            16 => 'Vehicles, comforts, luxuries, conveyances, happiness',
            20 => 'Spiritual practice, worship, piety, religious bent',
            24 => 'Education, learning, wisdom, academic achievements',
            27 => 'Strength, vitality, inherent constitution, physical strength',
            30 => 'Misfortunes, evil effects, calamities, afflictions',
            40 => 'Auspicious and inauspicious effects, maternal legacy',
            45 => 'All general indications, paternal legacy, overall life',
            60 => 'Past karma, all matters — the most powerful varga',
            default => "Division by {$d}",
        };
    }

    /**
     * Format a single planet's varga position for display.
     */
    public static function formatPlanetVarga(array $vargaData, string $pid): string
    {
        $p = $vargaData['planets'][$pid] ?? null;
        if (!$p) return '—';
        return sprintf(
            '%s (%s) · Lord: %s · Dignity: %s',
            $p['vargaSign'],
            self::vargaName($vargaData['division']),
            $p['vargaLord'],
            $p['dignity']
        );
    }

    /**
     * Build a complete summary array for API responses.
     * Returns only the sign + dignity for each planet across all vargas.
     */
    public static function buildSummary(array $allVargas): array
    {
        $summary = [];
        $VARGA_KEYS = array_filter(array_keys($allVargas), fn($k) => str_starts_with($k, 'D'));

        foreach ($VARGA_KEYS as $vk) {
            $varga = $allVargas[$vk];
            $summary[$vk] = [
                'name'          => $varga['name'],
                'signification' => $varga['signification'],
                'planets'       => [],
            ];
            foreach ($varga['planets'] as $pid => $pdata) {
                $summary[$vk]['planets'][$pid] = [
                    'sign'    => $pdata['vargaSign'],
                    'lord'    => $pdata['vargaLord'],
                    'dignity' => $pdata['dignity'],
                    'retro'   => $pdata['retro'] ?? false,
                ];
                if (isset($pdata['shashtiName'])) {
                    $summary[$vk]['planets'][$pid]['shashtiName']   = $pdata['shashtiName'];
                    $summary[$vk]['planets'][$pid]['shashtiNature'] = $pdata['shashtiNature'];
                }
                if (isset($pdata['trimshaLord'])) {
                    $summary[$vk]['planets'][$pid]['trimshaLord'] = $pdata['trimshaLord'];
                }
            }
        }

        $summary['vimshopaka'] = $allVargas['vimshopaka'] ?? [];
        $summary['dignities']  = $allVargas['dignities']  ?? [];

        return $summary;
    }
}
