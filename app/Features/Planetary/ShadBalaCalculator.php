<?php

namespace App\Features\Planetary;

/**
 * ShadBalaCalculator — Complete Six-fold Planetary Strength
 *
 * Implements all 6 Balas per:
 *   - Brihat Parashara Hora Shastra (BPHS), Ch. 27–35
 *   - Graha and Bhava Balas — B.V. Raman
 *   - Komilla Sutton, "The Essentials of Vedic Astrology"
 *
 * ┌─────────────────────────────────────────────────────┐
 * │  1. Sthana Bala   — Positional strength             │
 * │  2. Dig Bala      — Directional strength            │
 * │  3. Kaala Bala    — Temporal strength               │
 * │  4. Chesta Bala   — Motional strength               │
 * │  5. Naisargika Bala — Natural / permanent strength  │
 * │  6. Drig Bala     — Aspectual strength              │
 * └─────────────────────────────────────────────────────┘
 *
 * All values in Shashtiamshas (Virupas).
 * Minimum required strengths (Rupas = Shashtiamshas / 60):
 *   Su Mo Ma Me Ju Ve Sa
 *    5  6  5  7  6.5 5.5 5  (Rupas)
 */
class ShadBalaCalculator
{
    // ── Constants ─────────────────────────────────────────────
    private const DEG  = M_PI / 180.0;
    private const PLANETS = ['sun','moon','mars','mercury','jupiter','venus','saturn'];

    // ── Naisargika Bala (Natural permanent strength, BPHS Ch.27)
    // Fixed values in Shashtiamshas
    private const NAISARGIKA = [
        'sun'     => 60.0,
        'moon'    => 51.43,
        'venus'   => 42.86,
        'jupiter' => 34.29,
        'mercury' => 25.71,
        'mars'    => 17.14,
        'saturn'  => 8.57,
    ];

    // Minimum required Shadbala in Rupas (BPHS)
    public const MIN_RUPAS = [
        'sun'     => 5.0,
        'moon'    => 6.0,
        'mars'    => 5.0,
        'mercury' => 7.0,
        'jupiter' => 6.5,
        'venus'   => 5.5,
        'saturn'  => 5.0,
    ];

    // ── Exaltation degrees (BPHS Ch.3) ───────────────────────
    private const EXALT_DEG = [
        'sun'     => 10.0,   // Mesha 10°
        'moon'    => 33.0,   // Vrishabha 3°
        'mars'    => 298.0,  // Makara 28°
        'mercury' => 165.0,  // Kanya 15°
        'jupiter' => 95.0,   // Karka 5°
        'venus'   => 357.0,  // Meena 27°
        'saturn'  => 200.0,  // Tula 20°
    ];

    // Moolatrikona ranges [sign_idx, from_deg, to_deg]
    private const MOOLATRIKONA = [
        'sun'     => [4,  0,  20],  // Simha 0–20°
        'moon'    => [1,  3,  30],  // Vrishabha 3–30°
        'mars'    => [0,  0,  12],  // Mesha 0–12°
        'mercury' => [5, 15,  20],  // Kanya 15–20°
        'jupiter' => [8,  0,  10],  // Dhanu 0–10°
        'venus'   => [6,  0,  15],  // Tula 0–15°
        'saturn'  => [10, 0,  20],  // Kumbha 0–20°
    ];

    // Own signs
    private const OWN_SIGNS = [
        'sun'     => [4],
        'moon'    => [3],
        'mars'    => [0, 7],
        'mercury' => [2, 5],
        'jupiter' => [8, 11],
        'venus'   => [1, 6],
        'saturn'  => [9, 10],
    ];

    // Dig Bala — directional strength house (BPHS Ch.29)
    // Planet is strongest in this house number (1-based)
    private const DIG_BALA_HOUSE = [
        'sun'     => 10,   // Strongest at MC (10th)
        'moon'    => 4,    // Strongest at IC (4th)
        'mars'    => 10,   // Strongest at MC (10th)
        'mercury' => 1,    // Strongest at ASC (1st)
        'jupiter' => 1,    // Strongest at ASC (1st)
        'venus'   => 4,    // Strongest at IC (4th)
        'saturn'  => 7,    // Strongest at DSC (7th)
    ];

    // Dig Bala reference longitudes (cusp of strongest house)
    // Weakest house is exactly opposite (180° away)
    private const DIG_BALA_STRONG_LON = [
        'sun'     => 'mc',
        'moon'    => 'ic',
        'mars'    => 'mc',
        'mercury' => 'asc',
        'jupiter' => 'asc',
        'venus'   => 'ic',
        'saturn'  => 'dsc',
    ];

    // Aspect strengths (Graha Drishti) in fractions (BPHS Ch.26)
    // All planets aspect 7th house fully
    // Mars aspects 4th and 8th (75%), Jupiter 5th and 9th (full), Saturn 3rd and 10th (75%)
    private const ASPECT_FRACTION = [
        'full'         => 1.0,
        'threequarter' => 0.75,
        'half'         => 0.5,
        'quarter'      => 0.25,
    ];

    // ── Soft palette matching existing planet tiles ───────────
    private const SOFT = [
        'sun'     => ['bg'=>'#fff8ee','border'=>'#f5c870','text'=>'#7a3a02','accent'=>'#d4760a','abbr'=>'Su'],
        'moon'    => ['bg'=>'#eaf4fb','border'=>'#90c8e8','text'=>'#0e3050','accent'=>'#1a7ab5','abbr'=>'Mo'],
        'mercury' => ['bg'=>'#e8f7f5','border'=>'#a0d8d0','text'=>'#1e5a52','accent'=>'#0a8c5a','abbr'=>'Me'],
        'venus'   => ['bg'=>'#f9edf7','border'=>'#d8a0d0','text'=>'#6a1858','accent'=>'#9c2d8a','abbr'=>'Ve'],
        'mars'    => ['bg'=>'#fce8e6','border'=>'#e8a0a0','text'=>'#8a1810','accent'=>'#c0311f','abbr'=>'Ma'],
        'jupiter' => ['bg'=>'#fdf6e3','border'=>'#d8c080','text'=>'#5a4000','accent'=>'#b36000','abbr'=>'Ju'],
        'saturn'  => ['bg'=>'#eeecf5','border'=>'#b0a8d0','text'=>'#30284a','accent'=>'#5a4a8a','abbr'=>'Sa'],
    ];

    // ════════════════════════════════════════════════════════════
    //  PRIMARY ENTRY POINT
    // ════════════════════════════════════════════════════════════

    /**
     * Calculate complete Shadbala for all 7 classical planets.
     *
     * @param array $planets   From AstroCalculator — each has 'sider','trop','retro'
     * @param float $ascSider  Ascendant sidereal longitude
     * @param float $jd        Julian Day of birth
     * @param float $lat       Geographic latitude
     * @param array $angles    From AstroCalculator::computeAngles() — has 'asc','mc','ic','dsc'
     * @param int   $birthHour Local birth hour (0–23)
     * @param int   $birthDow  Day of week (0=Sun…6=Sat)
     * @param bool  $isDayBirth Whether birth is during daytime
     * @return array
     */
    public static function calculate(
        array $planets,
        float $ascSider,
        float $jd,
        float $lat,
        array $angles,
        int   $birthHour    = 12,
        int   $birthDow     = 0,
        bool  $isDayBirth   = true
    ): array {
        $result = [];
        $n360   = fn(float $x) => fmod(fmod($x, 360.0) + 360.0, 360.0);

        // Precompute sidereal angle positions
        $ascLon = $ascSider;
        $mcLon  = isset($angles['mc'])
            ? $n360($angles['mc'] - (isset($planets['sun']) ? ($planets['sun']['trop'] - $planets['sun']['sider']) : 0.0))
            : $n360($ascLon + 270.0);
        $icLon  = $n360($mcLon + 180.0);
        $dscLon = $n360($ascLon + 180.0);

        // House cusps (whole-sign, sidereal)
        $ascSignIdx = (int)floor($ascSider / 30.0);
        $houseCusps = [];
        for ($h = 1; $h <= 12; $h++) {
            $houseCusps[$h] = (($ascSignIdx + $h - 1) % 12) * 30.0;
        }

        foreach (self::PLANETS as $pid) {
            if (!isset($planets[$pid])) continue;
            $p     = $planets[$pid];
            $sider = $p['sider'];
            $retro = $p['retro'] ?? false;
            $signIdx = (int)floor($sider / 30.0);
            $degInSign = fmod($sider, 30.0);

            // House position (whole-sign)
            $houseNum = (($signIdx - $ascSignIdx + 12) % 12) + 1;

            // ── 1. STHANA BALA ────────────────────────────────────
            $sthanaBala = self::calcSthanaBala($pid, $sider, $signIdx, $degInSign);

            // ── 2. DIG BALA ───────────────────────────────────────
            $digBala = self::calcDigBala($pid, $sider, $ascLon, $mcLon, $icLon, $dscLon, $n360);

            // ── 3. KAALA BALA ─────────────────────────────────────
            $kaalaBala = self::calcKaalaBala(
                $pid, $sider, $isDayBirth, $birthDow, $birthHour,
                $jd, $lat, $n360
            );

            // ── 4. CHESTA BALA ────────────────────────────────────
            $chestaBala = self::calcChestaBala($pid, $retro, $sider, $jd, $n360);

            // ── 5. NAISARGIKA BALA ────────────────────────────────
            $naisargikaBala = self::NAISARGIKA[$pid];

            // ── 6. DRIG BALA ──────────────────────────────────────
            $drigBala = self::calcDrigBala($pid, $sider, $planets, $n360);

            // ── Total Shadbala ────────────────────────────────────
            $total = $sthanaBala['total']
                   + $digBala
                   + $kaalaBala['total']
                   + $chestaBala
                   + $naisargikaBala
                   + $drigBala;

            $rupas   = $total / 60.0;
            $minReq  = self::MIN_RUPAS[$pid];
            $ratio   = $minReq > 0 ? $rupas / $minReq : 0;
            $percent = min(100, round($ratio * 100, 1));

            $result[$pid] = [
                'sider'          => $sider,
                'signIdx'        => $signIdx,
                'houseNum'       => $houseNum,

                // Individual balas (Shashtiamshas)
                'sthanaBala'     => $sthanaBala,
                'digBala'        => round($digBala, 2),
                'kaalaBala'      => $kaalaBala,
                'chestaBala'     => round($chestaBala, 2),
                'naisargikaBala' => round($naisargikaBala, 2),
                'drigBala'       => round($drigBala, 2),

                // Summary
                'total'          => round($total, 2),
                'rupas'          => round($rupas, 3),
                'minRupas'       => $minReq,
                'ratio'          => round($ratio, 3),
                'percent'        => $percent,
                'isStrong'       => $rupas >= $minReq,
                'grade'          => self::gradeStrength($ratio),
            ];
        }

        // Relative ranks
        uasort($result, fn($a, $b) => $b['total'] <=> $a['total']);
        $rank = 1;
        foreach ($result as $pid => &$r) { $r['rank'] = $rank++; }
        unset($r);

        return $result;
    }

    // ════════════════════════════════════════════════════════════
    //  1. STHANA BALA — Positional Strength
    //
    //  Components (BPHS Ch.27):
    //    a) Uchcha Bala       — Exaltation strength
    //    b) Saptavargaja Bala — 7-divisional dignity strength
    //    c) Ojayugma Bala     — Odd/even sign strength
    //    d) Kendra Bala       — Angular house strength
    //    e) Drekkana Bala     — Decanate strength
    // ════════════════════════════════════════════════════════════

    private static function calcSthanaBala(
        string $pid,
        float  $sider,
        int    $signIdx,
        float  $degInSign
    ): array {
        $n360 = fn($x) => fmod(fmod($x, 360.0) + 360.0, 360.0);

        // a) Uchcha Bala (0–60 Shashtiamshas)
        // Max at exaltation degree, 0 at debilitation (180° away)
        $exaltDeg = self::EXALT_DEG[$pid] ?? 0.0;
        $debitDeg = $n360($exaltDeg + 180.0);
        $diff = abs($sider - $exaltDeg);
        if ($diff > 180.0) $diff = 360.0 - $diff;
        $uchcha = (180.0 - $diff) / 3.0;   // max 60 at 0° diff

        // b) Saptavargaja Bala — dignity across D1,D2,D3,D7,D9,D12,D30
        // Values: Exalted=45, Moolatrikona=22.5, OwnSign=15,
        //         FriendSign=7.5, NeutralSign=3.75, EnemySign=1.875, Debilitated=0
        $saptaBala = self::calcSaptavargajaBala($pid, $sider);

        // c) Ojayugma Bala — Sun/Mars stronger in odd signs, Moon/Venus in even, others both
        $isOdd = ($signIdx % 2 === 0); // 0-indexed: Mesha(0)=odd
        $ojaBala = 0.0;
        if (in_array($pid, ['sun', 'mars', 'jupiter', 'saturn', 'mercury'])) {
            $ojaBala = $isOdd ? 15.0 : 0.0;
        } elseif (in_array($pid, ['moon', 'venus'])) {
            $ojaBala = $isOdd ? 0.0 : 15.0;
        }

        // d) Kendra Bala — angular house strength
        // Kendra (1,4,7,10)=60, Panapara (2,5,8,11)=30, Apoklima (3,6,9,12)=15
        $kendraSign = (int)floor($sider / 30.0);
        $kendraDist = $kendraSign % 3;
        $kendraBala = match($kendraDist) {
            0 => 60.0,
            1 => 30.0,
            2 => 15.0,
            default => 15.0,
        };

        // e) Drekkana Bala — decanate strength
        // Males (Su,Ma,Ju): strong in 1st drekkana (0–10°)
        // Females (Mo,Ve):  strong in 2nd drekkana (10–20°)
        // Neutrals (Me,Sa): strong in 3rd drekkana (20–30°)
        $drekkana = (int)floor($degInSign / 10.0); // 0,1,2
        $drekkBala = 0.0;
        if (in_array($pid, ['sun', 'mars', 'jupiter']) && $drekkana === 0) $drekkBala = 15.0;
        if (in_array($pid, ['moon', 'venus'])           && $drekkana === 1) $drekkBala = 15.0;
        if (in_array($pid, ['mercury', 'saturn'])       && $drekkana === 2) $drekkBala = 15.0;

        $total = $uchcha + $saptaBala + $ojaBala + $kendraBala + $drekkBala;

        return [
            'ucchaBala'      => round($uchcha, 2),
            'saptavargaBala' => round($saptaBala, 2),
            'ojayugmaBala'   => round($ojaBala, 2),
            'kendraBala'     => round($kendraBala, 2),
            'drekkanaBala'   => round($drekkBala, 2),
            'total'          => round($total, 2),
        ];
    }

    /**
     * Saptavargaja Bala — dignity points across 7 vargas.
     * Vargas: D1, D2, D3, D7, D9, D12, D30
     */
    private static function calcSaptavargajaBala(string $pid, float $sider): float
    {
        $signIdx   = (int)floor($sider / 30.0);
        $degInSign = fmod($sider, 30.0);
        $total     = 0.0;

        // Varga divisors and weights
        $vargas = [
            ['d' => 1,  'w' => 1.0],
            ['d' => 2,  'w' => 0.5],
            ['d' => 3,  'w' => 0.5],
            ['d' => 7,  'w' => 0.5],
            ['d' => 9,  'w' => 1.0],
            ['d' => 12, 'w' => 0.5],
            ['d' => 30, 'w' => 0.5],
        ];

        $dignityPoints = [
            'exalted'      => 45.0,
            'moolatrikona' => 22.5,
            'own'          => 15.0,
            'friendly'     => 7.5,
            'neutral'      => 3.75,
            'enemy'        => 1.875,
            'debilitated'  => 0.0,
        ];

        foreach ($vargas as $v) {
            $vSignIdx = self::getVargaSign($signIdx, $degInSign, $v['d']);
            $dignity  = self::getPlanetDignityStr($pid, $vSignIdx, $sider, $v['d'] === 1);
            $pts      = $dignityPoints[$dignity] ?? 3.75;
            $total   += $pts * $v['w'];
        }

        return $total;
    }

    // ════════════════════════════════════════════════════════════
    //  2. DIG BALA — Directional Strength (BPHS Ch.29)
    //
    //  Each planet is strongest at a specific angle of the chart.
    //  Dig Bala = 60 × (1 – angular_distance / 180)
    //  Angular distance from the planet to its strongest point.
    // ════════════════════════════════════════════════════════════

    private static function calcDigBala(
        string   $pid,
        float    $sider,
        float    $ascLon,
        float    $mcLon,
        float    $icLon,
        float    $dscLon,
        callable $n360
    ): float {
        $strongLon = match(self::DIG_BALA_STRONG_LON[$pid] ?? 'asc') {
            'mc'  => $mcLon,
            'ic'  => $icLon,
            'dsc' => $dscLon,
            default => $ascLon,
        };

        $diff = abs($sider - $strongLon);
        if ($diff > 180.0) $diff = 360.0 - $diff;
        // Max 60 when diff=0, 0 when diff=180
        return max(0.0, 60.0 * (1.0 - $diff / 180.0));
    }

    // ════════════════════════════════════════════════════════════
    //  3. KAALA BALA — Temporal Strength (BPHS Ch.30–32)
    //
    //  Components:
    //    a) Nathonnatha Bala  — Day/Night strength
    //    b) Paksha Bala       — Lunar phase strength
    //    c) Tribhaga Bala     — 3-part day strength
    //    d) Abda Bala         — Year lord strength
    //    e) Masa Bala         — Month lord strength
    //    f) Vara Bala         — Weekday lord strength
    //    g) Hora Bala         — Hora lord strength
    //    h) Ayana Bala        — Solstice strength
    //    i) Yuddha Bala       — Planetary war strength
    // ════════════════════════════════════════════════════════════

    private static function calcKaalaBala(
        string   $pid,
        float    $sider,
        bool     $isDayBirth,
        int      $birthDow,
        int      $birthHour,
        float    $jd,
        float    $lat,
        callable $n360
    ): array {
        // a) Nathonnatha Bala — day/night polarity
        // Diurnal planets (Su,Ve,Ju) stronger by day; nocturnal (Mo,Ma,Sa) stronger at night
        // Mercury is always moderate (neutral)
        $natho = 0.0;
        if ($isDayBirth) {
            $natho = in_array($pid, ['sun', 'venus', 'jupiter']) ? 60.0 : 30.0;
        } else {
            $natho = in_array($pid, ['moon', 'mars', 'saturn'])  ? 60.0 : 30.0;
        }
        if ($pid === 'mercury') $natho = 60.0; // Mercury always benefits

        // b) Paksha Bala — lunar phase (0–60)
        // Benefics (Mo,Me,Ve,Ju) stronger at full moon, malefics (Su,Ma,Sa,Ra,Ke) stronger at new moon
        $paksha = 0.0;
        // Approximate moon elongation from JD — simplified
        $T       = ($jd - 2451545.0) / 36525.0;
        $moonLon = fmod(218.3164477 + 481267.88123421 * $T, 360.0);
        $sunLon  = fmod(280.46646   + 36000.76983     * $T, 360.0);
        $elong   = $n360($moonLon - $sunLon);
        // Phase 0=new, 180=full
        $phaseAngle = $elong; // 0–360
        $pakshaFrac = $phaseAngle <= 180.0 ? $phaseAngle / 180.0 : (360.0 - $phaseAngle) / 180.0;

        if (in_array($pid, ['moon', 'mercury', 'venus', 'jupiter'])) {
            $paksha = $pakshaFrac * 60.0;  // strongest at full moon
        } else {
            $paksha = (1.0 - $pakshaFrac) * 60.0; // strongest at new moon
        }

        // c) Tribhaga Bala — 3-part of day (BPHS Ch.31)
        // Day divided into 3 parts; each part has a lord
        // Part 1 (sunrise–2/3 day): Mercury (daytime), Moon (nighttime)
        // Part 2 (mid-day): Sun (day), Venus (night)
        // Part 3 (2/3–sunset): Saturn (day), Mars (night)
        $tribhaga = 0.0;
        if ($isDayBirth) {
            if ($birthHour >= 6  && $birthHour < 10) { if ($pid === 'mercury') $tribhaga = 60.0; }
            if ($birthHour >= 10 && $birthHour < 14) { if ($pid === 'sun')     $tribhaga = 60.0; }
            if ($birthHour >= 14 && $birthHour < 18) { if ($pid === 'saturn')  $tribhaga = 60.0; }
        } else {
            if ($birthHour >= 18 && $birthHour < 22) { if ($pid === 'moon')    $tribhaga = 60.0; }
            if ($birthHour >= 22 || $birthHour < 2)  { if ($pid === 'venus')   $tribhaga = 60.0; }
            if ($birthHour >= 2  && $birthHour < 6)  { if ($pid === 'mars')    $tribhaga = 60.0; }
        }

        // d) Vara Bala — weekday lord (BPHS Ch.30)
        // 0=Sun,1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat
        $varaLords = ['sun','moon','mars','mercury','jupiter','venus','saturn'];
        $vara = ($varaLords[$birthDow % 7] === $pid) ? 45.0 : 0.0;

        // e) Hora Bala — planetary hour lord
        // Hora sequence: Su,Ve,Me,Mo,Sa,Ju,Ma (repeating)
        $horaSeq  = ['sun','venus','mercury','moon','saturn','jupiter','mars'];
        $horaIdx  = ($birthDow * 24 + $birthHour) % 7;
        $hora     = ($horaSeq[$horaIdx] === $pid) ? 60.0 : 0.0;

        // f) Ayana Bala — solstice/equinox strength
        // Based on Sun's declination and planet's position
        // Simplified: planets in northern signs (Mesha–Kanya) get boost when Sun is in N hemisphere
        $T2       = ($jd - 2451545.0) / 36525.0;
        $sunSiderApprox = $n360($sunLon - 23.85);  // approximate sidereal
        $sunNorth = $sunSiderApprox < 180.0;        // Sun in Mesha–Kanya
        $planNorth = $sider < 180.0;
        $ayana = ($sunNorth === $planNorth) ? 30.0 : 15.0;
        // Sun and Moon always benefit from their preferred hemisphere
        if ($pid === 'sun'  && $sunNorth)  $ayana = 60.0;
        if ($pid === 'moon' && !$sunNorth) $ayana = 60.0;

        // g) Masa Bala — approximate (month lord = Sun's sign lord)
        $sunSignLords = ['mars','venus','mercury','moon','sun','mercury','venus','mars','jupiter','saturn','saturn','jupiter'];
        $sunSignIdx   = (int)floor($sunSiderApprox / 30.0) % 12;
        $masaLord     = $sunSignLords[$sunSignIdx];
        $masa         = ($masaLord === $pid) ? 30.0 : 0.0;

        $total = $natho + $paksha + $tribhaga + $vara + $hora + $ayana + $masa;

        return [
            'nathoBala'    => round($natho, 2),
            'pakshaBala'   => round($paksha, 2),
            'tribhagaBala' => round($tribhaga, 2),
            'varaBala'     => round($vara, 2),
            'horaBala'     => round($hora, 2),
            'ayanaBala'    => round($ayana, 2),
            'masaBala'     => round($masa, 2),
            'total'        => round($total, 2),
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  4. CHESTA BALA — Motional Strength (BPHS Ch.33)
    //
    //  Based on planet's speed relative to mean speed.
    //  Retrograde planets get full (60) Chesta Bala (BPHS rule).
    //  Direct motion: proportional to speed deviation.
    //
    //  For Sun and Moon: uses Ayana / Paksha Bala instead
    //  (they don't retrograde — Chesta Bala = Paksha/Ayana)
    // ════════════════════════════════════════════════════════════

    private static function calcChestaBala(
        string   $pid,
        bool     $retro,
        float    $sider,
        float    $jd,
        callable $n360
    ): float {
        // Sun and Moon use special rules — return moderate value
        if ($pid === 'sun' || $pid === 'moon') {
            return 30.0; // Replaced by Ayana/Paksha Bala in practice
        }

        // Retrograde = full Chesta Bala (60)
        if ($retro) return 60.0;

        // For direct planets: estimate speed by comparing positions at JD ± 1
        // Mean daily motions (degrees/day)
        $meanMotion = [
            'mars'    => 0.524,
            'mercury' => 1.383,
            'jupiter' => 0.083,
            'venus'   => 1.202,
            'saturn'  => 0.034,
        ];

        // Get actual daily motion via finite difference
        $prevSider = self::approxPlanetLon($pid, $jd - 1.0, $n360);
        $nextSider = self::approxPlanetLon($pid, $jd + 1.0, $n360);
        $actualSpeed = $n360($nextSider - $prevSider);
        if ($actualSpeed > 180.0) $actualSpeed -= 360.0;

        $mean  = $meanMotion[$pid] ?? 1.0;
        $ratio = abs($actualSpeed) / $mean;

        // Chesta Bala: 0 when stationary, 60 when at max speed
        // Max speed ≈ 2× mean for most planets
        return min(60.0, $ratio * 30.0);
    }

    // ════════════════════════════════════════════════════════════
    //  6. DRIG BALA — Aspectual Strength (BPHS Ch.35)
    //
    //  Benefic aspects add strength, malefic aspects subtract.
    //  Natural benefics: Mo (waxing), Me, Ve, Ju
    //  Natural malefics: Su, Ma, Sa, Ra, Ke, Mo (waning)
    //
    //  Aspect fractions (BPHS Ch.26):
    //    All planets: 7th = full (1.0)
    //    Mars: 4th=0.75, 8th=0.75
    //    Jupiter: 5th=1.0, 9th=1.0
    //    Saturn: 3rd=0.75, 10th=0.75
    //    Rahu/Ketu: 5th=0.5, 9th=0.5 (some texts: 5th,7th,9th)
    // ════════════════════════════════════════════════════════════

    private static function calcDrigBala(
        string   $pid,
        float    $sider,
        array    $planets,
        callable $n360
    ): float {
        $drig       = 0.0;
        $isBenefic  = in_array($pid, ['moon', 'mercury', 'venus', 'jupiter']);
        $isMalefic  = in_array($pid, ['sun', 'mars', 'saturn']);

        // Get aspect fractions on this planet FROM each other planet
        foreach ($planets as $aspPid => $aspP) {
            if ($aspPid === $pid) continue;
            if (!isset($aspP['sider'])) continue;

            $aspIsNatBenefic = in_array($aspPid, ['moon', 'mercury', 'venus', 'jupiter']);
            $aspSider        = $aspP['sider'];

            // Distance from aspecting planet to this planet
            $dist = $n360($sider - $aspSider);
            $house = (int)floor($dist / 30.0) + 1; // 1–12

            // Aspect fraction
            $frac = self::getAspectFraction($aspPid, $house);
            if ($frac <= 0) continue;

            // Benefic aspect adds, malefic subtracts
            if ($aspIsNatBenefic) {
                $drig += $frac * 60.0 / 4.0; // normalised contribution
            } else {
                $drig -= $frac * 60.0 / 4.0;
            }
        }

        // Clamp between -60 and +60 per BPHS convention
        return max(-60.0, min(60.0, $drig));
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Aspect fractions (BPHS)
    // ════════════════════════════════════════════════════════════

    private static function getAspectFraction(string $pid, int $house): float
    {
        // All planets aspect the 7th house fully
        if ($house === 7) return 1.0;

        return match($pid) {
            'mars'    => in_array($house, [4, 8])     ? 0.75 : 0.0,
            'jupiter' => in_array($house, [5, 9])     ? 1.0  : 0.0,
            'saturn'  => in_array($house, [3, 10])    ? 0.75 : 0.0,
            'rahu',
            'ketu'    => in_array($house, [5, 9])     ? 0.5  : 0.0,
            default   => 0.0,
        };
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Varga sign calculator
    // ════════════════════════════════════════════════════════════

    private static function getVargaSign(int $signIdx, float $deg, int $d): int
    {
        return match($d) {
            1  => $signIdx,
            2  => self::d2($signIdx, $deg),
            3  => ($signIdx + (int)floor($deg / 10.0) * 4) % 12,
            7  => (($signIdx % 2 === 0 ? $signIdx : ($signIdx + 6) % 12) + (int)floor($deg / (30.0/7.0))) % 12,
            9  => (self::navamshaStart($signIdx) + (int)floor($deg / (30.0/9.0))) % 12,
            12 => ($signIdx + (int)floor($deg / 2.5)) % 12,
            30 => self::d30Sign($signIdx, $deg),
            default => $signIdx,
        };
    }

    private static function d2(int $si, float $deg): int
    {
        $isOdd = ($si % 2 === 0);
        $first = ($deg < 15.0);
        return ($isOdd ? $first : !$first) ? 4 : 3;
    }

    private static function navamshaStart(int $si): int
    {
        static $starts = [0,9,6,3,0,9,6,3,0,9,6,3];
        return $starts[$si];
    }

    private static function d30Sign(int $si, float $deg): int
    {
        static $oddMap  = ['Mars'=>0,'Saturn'=>10,'Jupiter'=>8,'Mercury'=>2,'Venus'=>6];
        static $evenMap = ['Venus'=>1,'Mercury'=>5,'Jupiter'=>10,'Moon'=>7,'Mars'=>0];
        $lord = self::trimshaLord($si, $deg);
        return ($si % 2 === 0) ? ($oddMap[$lord] ?? 0) : ($evenMap[$lord] ?? 0);
    }

    private static function trimshaLord(int $si, float $deg): string
    {
        if ($si % 2 === 0) {
            if ($deg <  5) return 'Mars';
            if ($deg < 10) return 'Saturn';
            if ($deg < 18) return 'Jupiter';
            if ($deg < 25) return 'Mercury';
            return 'Venus';
        } else {
            if ($deg <  5) return 'Venus';
            if ($deg < 12) return 'Mercury';
            if ($deg < 20) return 'Jupiter';
            if ($deg < 25) return 'Moon';
            return 'Mars';
        }
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Planet dignity string
    // ════════════════════════════════════════════════════════════

    private static function getPlanetDignityStr(
        string $pid,
        int    $vargaSign,
        float  $sider,
        bool   $checkExact
    ): string {
        // Exaltation
        $exDeg = self::EXALT_DEG[$pid] ?? -1;
        if ($exDeg >= 0) {
            $exSign = (int)floor($exDeg / 30.0);
            if ($vargaSign === $exSign) return 'exalted';
            // Debilitation
            $debSign = ($exSign + 6) % 12;
            if ($vargaSign === $debSign) return 'debilitated';
        }

        // Moolatrikona
        $mt = self::MOOLATRIKONA[$pid] ?? null;
        if ($mt && $vargaSign === $mt[0]) {
            if (!$checkExact) return 'moolatrikona';
            $deg = fmod($sider, 30.0);
            if ($deg >= $mt[1] && $deg <= $mt[2]) return 'moolatrikona';
        }

        // Own sign
        if (in_array($vargaSign, self::OWN_SIGNS[$pid] ?? [])) return 'own';

        // Friendship (simplified permanent)
        static $FRIENDS = [
            'sun'     => [0,3,4,8],
            'moon'    => [2,4],
            'mars'    => [0,3,4,8],
            'mercury' => [1,4,6],
            'jupiter' => [0,3,4],
            'venus'   => [2,5,9,10],
            'saturn'  => [2,5,6,9,10],
        ];
        static $ENEMIES = [
            'sun'     => [1,6,9,10],
            'moon'    => [],
            'mars'    => [2,5],
            'mercury' => [3],
            'jupiter' => [2,5,6],
            'venus'   => [0,3,4],
            'saturn'  => [0,3,4],
        ];

        if (in_array($vargaSign, $FRIENDS[$pid] ?? [])) return 'friendly';
        if (in_array($vargaSign, $ENEMIES[$pid] ?? [])) return 'enemy';
        return 'neutral';
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Approximate planet longitude for Chesta Bala
    // ════════════════════════════════════════════════════════════

    private static function approxPlanetLon(string $pid, float $jd, callable $n360): float
    {
        $T = ($jd - 2451545.0) / 36525.0;
        return match($pid) {
            'mercury' => $n360(252.25 + 149474.07  * $T),
            'venus'   => $n360(181.98 + 58519.21   * $T),
            'mars'    => $n360(355.43 + 19141.70   * $T),
            'jupiter' => $n360(34.35  + 3036.30    * $T),
            'saturn'  => $n360(50.08  + 1223.51    * $T),
            default   => 0.0,
        };
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Grade strength
    // ════════════════════════════════════════════════════════════

    private static function gradeStrength(float $ratio): string
    {
        if ($ratio >= 2.0)  return 'Exceptional';
        if ($ratio >= 1.5)  return 'Very Strong';
        if ($ratio >= 1.0)  return 'Strong';
        if ($ratio >= 0.75) return 'Moderate';
        if ($ratio >= 0.5)  return 'Weak';
        return 'Very Weak';
    }

    // ════════════════════════════════════════════════════════════
    //  HTML RENDERER  — soft light palette matching planet tiles
    // ════════════════════════════════════════════════════════════

   public static function renderHtml(array $shadbala): string
{
    // ── Soft palette helper ──────────────────────────────────────
    $sc = fn(string $pid): array => self::SOFT[$pid]
        ?? ['bg'=>'#f4f4f4','border'=>'#d0d0d0','text'=>'#444','accent'=>'#666','abbr'=>'?'];
 
    $html = '<div style="font-family:\'DM Sans\',sans-serif">';
 
    // ════════════════════════════════════════════════════════════
    //  VERTICAL BAR CHART
    // ════════════════════════════════════════════════════════════
    $chartH   = 200;   // chart area height (px)
    $barW     = 36;    // bar width (px)
    $barGap   = 18;    // gap between bars
    $padLeft  = 44;    // y-axis gutter
    $padRight = 12;
    $padTop   = 14;
    $padBot   = 58;    // room for labels
 
    // Determine y-axis ceiling
    $maxRupas = 0.0;
    foreach ($shadbala as $data) {
        if ($data['rupas'] > $maxRupas) $maxRupas = $data['rupas'];
    }
    $maxRupas = max($maxRupas * 1.20, 10.0);
 
    // Visual green/red split at 6 Rupas (visual midpoint — typical minimum)
    $splitRupas = 6.0;
 
    $nPlanets = count($shadbala);
    $totalW   = $padLeft + ($barW + $barGap) * $nPlanets - $barGap + $padRight;
    $svgH     = $chartH + $padTop + $padBot;
 
    // y-pixel for a Rupas value (measured from top of chart area)
    $toY = fn(float $v): float => $chartH - ($v / $maxRupas) * $chartH;
 
    $splitYabs = $padTop + $toY($splitRupas);   // absolute y of threshold line
 
    // ── SVG open ────────────────────────────────────────────────
    $svg  = '<svg xmlns="http://www.w3.org/2000/svg" ';
    $svg .= 'viewBox="0 0 '.$totalW.' '.$svgH.'" ';
    $svg .= 'style="width:100%;max-width:'.min($totalW * 1.6, 680).'px;';
    $svg .= 'display:block;overflow:visible;margin:0 auto">';
 
    // Drop-shadow filter
    $svg .= '<defs>
      <filter id="sb_shadow" x="-15%" y="-10%" width="130%" height="130%">
        <feDropShadow dx="0" dy="1.5" stdDeviation="2" flood-color="rgba(0,0,0,.15)"/>
      </filter>
    </defs>';
 
    $chartX2 = $totalW - $padRight;
    $chartW  = $chartX2 - $padLeft;
 
    // ── Green zone (above split) ─────────────────────────────────
    $greenH = $splitYabs - $padTop;
    if ($greenH > 0) {
        $svg .= '<rect x="'.$padLeft.'" y="'.$padTop.'" ';
        $svg .= 'width="'.$chartW.'" height="'.round($greenH,1).'" ';
        $svg .= 'fill="#c8eecb" rx="4 4 0 0"/>';
    }
 
    // ── Red zone (below split) ───────────────────────────────────
    $redTop = $splitYabs;
    $redH   = ($padTop + $chartH) - $redTop;
    if ($redH > 0) {
        $svg .= '<rect x="'.$padLeft.'" y="'.round($redTop,1).'" ';
        $svg .= 'width="'.$chartW.'" height="'.round($redH,1).'" ';
        $svg .= 'fill="#f5c6c6" rx="0 0 4 4"/>';
    }
 
    // ── Horizontal grid lines ────────────────────────────────────
    for ($g = 0; $g <= ceil($maxRupas); $g += 2) {
        $gy = round($padTop + $toY((float)$g), 1);
        if ($gy < $padTop - 1 || $gy > $padTop + $chartH + 1) continue;
        $svg .= '<line x1="'.$padLeft.'" y1="'.$gy.'" x2="'.$chartX2.'" y2="'.$gy.'" ';
        $svg .= 'stroke="rgba(0,0,0,.12)" stroke-width="1" stroke-dasharray="3,3"/>';
        $svg .= '<text x="'.($padLeft - 4).'" y="'.($gy + 3.5).'" ';
        $svg .= 'text-anchor="end" font-size="9" fill="#777" ';
        $svg .= 'font-family="DM Sans,sans-serif">'.$g.'</text>';
    }
 
    // ── Threshold split line ─────────────────────────────────────
    $svg .= '<line x1="'.$padLeft.'" y1="'.round($splitYabs,1).'" ';
    $svg .= 'x2="'.$chartX2.'" y2="'.round($splitYabs,1).'" ';
    $svg .= 'stroke="#333" stroke-width="2"/>';
 
    // ── Bars ─────────────────────────────────────────────────────
    $i = 0;
    foreach ($shadbala as $pid => $data) {
        $s    = $sc($pid);
        $barX = $padLeft + $i * ($barW + $barGap);
        $barH = max(3, ($data['rupas'] / $maxRupas) * $chartH);
        $barY = round($padTop + $chartH - $barH, 1);
 
        // ── Bar body (cream) ────────────────────────────────────
        $svg .= '<rect x="'.$barX.'" y="'.$barY.'" ';
        $svg .= 'width="'.$barW.'" height="'.round($barH,1).'" ';
        $svg .= 'fill="#f7f2e0" stroke="'.$s['accent'].'" stroke-width="1.2" ';
        $svg .= 'rx="2" filter="url(#sb_shadow)"/>';
 
        // ── Coloured top cap ─────────────────────────────────────
        $capH = min(6, $barH);
        $svg .= '<rect x="'.$barX.'" y="'.$barY.'" ';
        $svg .= 'width="'.$barW.'" height="'.round($capH,1).'" ';
        $svg .= 'fill="'.$s['accent'].'" rx="2"/>';
 
        // ── Per-planet min threshold tick ────────────────────────
        $minY = round($padTop + $toY($data['minRupas']), 1);
        if ($minY >= $padTop && $minY <= $padTop + $chartH) {
            $svg .= '<line x1="'.($barX - 3).'" y1="'.$minY.'" ';
            $svg .= 'x2="'.($barX + $barW + 3).'" y2="'.$minY.'" ';
            $svg .= 'stroke="'.$s['accent'].'" stroke-width="1.5" ';
            $svg .= 'stroke-dasharray="3,2" opacity=".65"/>';
        }
 
        // ── Rupas value above bar ─────────────────────────────────
        $valY = max($padTop + 9, $barY - 4);
        $svg .= '<text x="'.($barX + $barW / 2).'" y="'.$valY.'" ';
        $svg .= 'text-anchor="middle" font-size="9.5" font-weight="800" ';
        $svg .= 'fill="'.$s['accent'].'" font-family="DM Sans,sans-serif">';
        $svg .= $data['rupas'].'</text>';
 
        // ── Planet abbreviation (below chart) ────────────────────
        $lblY = $padTop + $chartH + 15;
        $svg .= '<text x="'.($barX + $barW / 2).'" y="'.$lblY.'" ';
        $svg .= 'text-anchor="middle" font-size="11.5" font-weight="900" ';
        $svg .= 'fill="'.$s['accent'].'" font-family="DM Sans,sans-serif">';
        $svg .= $s['abbr'].'</text>';
 
        // ── Rupas value below abbreviation ───────────────────────
        $svg .= '<text x="'.($barX + $barW / 2).'" y="'.($lblY + 13).'" ';
        $svg .= 'text-anchor="middle" font-size="8.5" fill="#555" ';
        $svg .= 'font-family="DM Sans,sans-serif">'.$data['rupas'].'</text>';
 
        // ── Strong / weak dot ────────────────────────────────────
        $dotClr = $data['isStrong'] ? '#1e7a3e' : '#c03030';
        $svg .= '<circle cx="'.($barX + $barW / 2).'" cy="'.($lblY + 27).'" ';
        $svg .= 'r="4" fill="'.$dotClr.'"/>';
 
        $i++;
    }
 
    // ── Chart border ─────────────────────────────────────────────
    $svg .= '<rect x="'.$padLeft.'" y="'.$padTop.'" ';
    $svg .= 'width="'.$chartW.'" height="'.$chartH.'" ';
    $svg .= 'fill="none" stroke="rgba(0,0,0,.2)" stroke-width="1" rx="4"/>';
 
    // ── Y-axis label ─────────────────────────────────────────────
    $midY = $padTop + $chartH / 2;
    $svg .= '<text transform="rotate(-90)" ';
    $svg .= 'x="'.(-$midY).'" y="11" ';
    $svg .= 'text-anchor="middle" font-size="9" fill="#999" ';
    $svg .= 'font-family="DM Sans,sans-serif">Rupas</text>';
 
    $svg .= '</svg>';
 
    // ── Chart wrapper ─────────────────────────────────────────────
    $html .= '<div style="background:#fff;border-radius:14px;padding:14px 10px 10px;';
    $html .= 'border:1.5px solid #e0e8f0;box-shadow:0 2px 12px rgba(13,40,70,.07);';
    $html .= 'margin-bottom:18px;overflow-x:auto">';
 
    $html .= '<div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;flex-wrap:wrap;padding:0 4px">';
    $html .= '<span style="font-size:.7rem;font-weight:800;color:#3a5a78;text-transform:uppercase;letter-spacing:.8px">Shadbala — Rupas</span>';
    $html .= '<span style="display:flex;align-items:center;gap:4px;font-size:.7rem;color:#1e7a3e">';
    $html .= '<span style="display:inline-block;width:11px;height:11px;background:#c8eecb;border:1px solid #1e7a3e;border-radius:2px"></span>Strong zone</span>';
    $html .= '<span style="display:flex;align-items:center;gap:4px;font-size:.7rem;color:#c03030">';
    $html .= '<span style="display:inline-block;width:11px;height:11px;background:#f5c6c6;border:1px solid #c03030;border-radius:2px"></span>Below minimum</span>';
    $html .= '<span style="font-size:.65rem;color:#999;margin-left:auto">Dashed line = planet\'s minimum Rupas</span>';
    $html .= '</div>';
 
    $html .= $svg;
    $html .= '</div>';
 
    // ════════════════════════════════════════════════════════════
    //  DETAIL TABLE (unchanged from original)
    // ════════════════════════════════════════════════════════════
    $html .= '<div style="border-radius:14px;overflow:hidden;border:1.5px solid #e0e8f0;';
    $html .= 'box-shadow:0 2px 12px rgba(13,40,70,.07)">';
    $html .= '<table style="width:100%;border-collapse:collapse;font-size:.78rem">';
 
    $html .= '<thead><tr style="background:linear-gradient(120deg,#f0f4f8,#e4ecf4)">';
    foreach (['Planet','Sthana','Dig','Kaala','Chesta','Naisargika','Drig','Total','Rupas','Grade'] as $c) {
        $html .= '<th style="padding:10px 8px;text-align:center;color:#3a5a78;font-size:.63rem;';
        $html .= 'text-transform:uppercase;letter-spacing:.8px;font-weight:800;';
        $html .= 'border-bottom:2px solid #d0dce8;white-space:nowrap">'.$c.'</th>';
    }
    $html .= '</tr></thead><tbody>';
 
    $ri = 0;
    foreach ($shadbala as $pid => $data) {
        $s     = $sc($pid);
        $isAlt = ($ri++ % 2 === 1);
        $isSt  = $data['isStrong'];
        $st    = $data['sthanaBala'];
        $ka    = $data['kaalaBala'];
 
        $html .= '<tr style="background:'.($isAlt ? '#f8fafb' : '#ffffff').';border-bottom:1px solid #eaeff4">';
 
        // Planet
        $html .= '<td style="padding:10px 8px;text-align:center">'
               . '<div style="display:inline-flex;align-items:center;justify-content:center;'
               . 'width:30px;height:30px;border-radius:8px;background:'.$s['bg'].';'
               . 'border:1.5px solid '.$s['border'].';margin-bottom:2px">'
               . '<span style="color:'.$s['accent'].';font-weight:900;font-size:.88rem">'.$s['abbr'].'</span>'
               . '</div>'
               . '<div style="color:'.$s['text'].';font-size:.6rem;text-transform:capitalize;opacity:.8">'.$pid.'</div>'
               . '</td>';
 
        // Sthana
        $html .= '<td style="padding:10px 6px;text-align:center" '
               . 'title="Uchcha:'.$st['ucchaBala'].' · Sapta:'.$st['saptavargaBala']
               . ' · Oja:'.$st['ojayugmaBala'].' · Kendra:'.$st['kendraBala']
               . ' · Drekkana:'.$st['drekkanaBala'].'">'
               . '<strong style="color:#2d4a62">'.$st['total'].'</strong></td>';
 
        // Dig
        $html .= '<td style="padding:10px 6px;text-align:center"><strong style="color:#2d4a62">'.$data['digBala'].'</strong></td>';
 
        // Kaala
        $html .= '<td style="padding:10px 6px;text-align:center" '
               . 'title="Natho:'.$ka['nathoBala'].' · Paksha:'.$ka['pakshaBala']
               . ' · Tribhaga:'.$ka['tribhagaBala'].' · Vara:'.$ka['varaBala']
               . ' · Hora:'.$ka['horaBala'].' · Ayana:'.$ka['ayanaBala'].'">'
               . '<strong style="color:#2d4a62">'.$ka['total'].'</strong></td>';
 
        // Chesta / Naisargika
        $html .= '<td style="padding:10px 6px;text-align:center"><strong style="color:#2d4a62">'.$data['chestaBala'].'</strong></td>';
        $html .= '<td style="padding:10px 6px;text-align:center"><strong style="color:#2d4a62">'.$data['naisargikaBala'].'</strong></td>';
 
        // Drig
        $dPos = $data['drigBala'] >= 0;
        $html .= '<td style="padding:10px 6px;text-align:center">'
               . '<strong style="color:'.($dPos ? '#2e7a4e' : '#c04040').'">'.$data['drigBala'].'</strong></td>';
 
        // Total
        $html .= '<td style="padding:10px 6px;text-align:center"><strong style="color:'.$s['accent'].';font-size:.92rem">'.$data['total'].'</strong></td>';
 
        // Rupas
        $html .= '<td style="padding:10px 6px;text-align:center">'
               . '<strong style="color:'.($isSt ? $s['accent'] : '#d04040').';font-size:.88rem">'.$data['rupas'].'</strong>'
               . '<div style="color:#9aabbf;font-size:.6rem">/ '.$data['minRupas'].'</div></td>';
 
        // Grade
        [$gradeC, $gradeBg] = match($data['grade']) {
            'Exceptional', 'Very Strong' => ['#2e7a4e', '#e6f4ec'],
            'Strong'                     => ['#1a7ab5', '#eaf4fb'],
            'Moderate'                   => ['#b36000', '#fdf6e3'],
            'Weak'                       => ['#c0311f', '#fce8e6'],
            default                      => ['#9c2d8a', '#f9edf7'],
        };
        $html .= '<td style="padding:10px 6px;text-align:center">'
               . '<span style="background:'.$gradeBg.';color:'.$gradeC.';'
               . 'border:1px solid '.$gradeC.'40;border-radius:20px;'
               . 'padding:3px 10px;font-weight:700;font-size:.65rem;white-space:nowrap">'
               . $data['grade'].'</span></td>';
 
        $html .= '</tr>';
    }
 
    $html .= '</tbody></table></div>';
 
    // ── Legend footer ─────────────────────────────────────────────
    $html .= '<div style="margin-top:12px;font-size:.72rem;color:#5a7a93;line-height:1.9;'
           . 'background:#f4f8fc;border-radius:10px;padding:10px 16px;border:1px solid #dde8f0">'
           . '<strong style="color:#2d4a62">Shadbala Components:</strong> '
           . 'Sthana = Positional · Dig = Directional · Kaala = Temporal · '
           . 'Chesta = Motional · Naisargika = Natural · Drig = Aspectual. '
           . 'All values in <em>Shashtiamshas (Virupas)</em>. '
           . 'Divide by 60 to get <em>Rupas</em>. '
           . 'Hover Sthana / Kaala columns for sub-component breakdown.'
           . '</div>';
 
    $html .= '</div>';
    return $html;
}
 
}