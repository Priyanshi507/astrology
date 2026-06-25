<?php

namespace App\Services\Planetary;

use Illuminate\Support\Facades\DB;

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
    // ── Mathematical constants ─────────────────────────────────
    private const DEG     = M_PI / 180.0;
    private const PLANETS = ['sun','moon','mars','mercury','jupiter','venus','saturn'];

    // Dig Bala reference longitudes (code concept, not data)
    private const DIG_BALA_STRONG_LON = [
        'sun'     => 'mc',
        'moon'    => 'ic',
        'mars'    => 'mc',
        'mercury' => 'asc',
        'jupiter' => 'asc',
        'venus'   => 'ic',
        'saturn'  => 'dsc',
    ];

    // Aspect strengths (Graha Drishti) in fractions — BPHS Ch.26
    private const ASPECT_FRACTION = [
        'full'         => 1.0,
        'threequarter' => 0.75,
        'half'         => 0.5,
        'quarter'      => 0.25,
    ];

    // ── Soft palette matching existing planet tiles (UI-only) ──
    private const SOFT = [
        'sun'     => ['bg'=>'#fff8ee','border'=>'#f5c870','text'=>'#7a3a02','accent'=>'#d4760a','abbr'=>'Su'],
        'moon'    => ['bg'=>'#eaf4fb','border'=>'#90c8e8','text'=>'#0e3050','accent'=>'#1a7ab5','abbr'=>'Mo'],
        'mercury' => ['bg'=>'#e8f7f5','border'=>'#a0d8d0','text'=>'#1e5a52','accent'=>'#0a8c5a','abbr'=>'Me'],
        'venus'   => ['bg'=>'#f9edf7','border'=>'#d8a0d0','text'=>'#6a1858','accent'=>'#9c2d8a','abbr'=>'Ve'],
        'mars'    => ['bg'=>'#fce8e6','border'=>'#e8a0a0','text'=>'#8a1810','accent'=>'#c0311f','abbr'=>'Ma'],
        'jupiter' => ['bg'=>'#fdf6e3','border'=>'#d8c080','text'=>'#5a4000','accent'=>'#b36000','abbr'=>'Ju'],
        'saturn'  => ['bg'=>'#eeecf5','border'=>'#b0a8d0','text'=>'#30284a','accent'=>'#5a4a8a','abbr'=>'Sa'],
    ];

    // ── DB-backed static caches ────────────────────────────────
    public  static ?array $minRupas    = null; // [key => float]  — public: used by views
    private static ?array $naisargika  = null; // [key => float]
    private static ?array $exaltDeg    = null; // [key => float]
    private static ?array $moolatrikona= null; // [key => [signIdx, from, to]]
    private static ?array $ownSigns    = null; // [key => [signIdx, ...]]
    private static ?array $friends     = null; // [key => [signIdx, ...]]
    private static ?array $enemies     = null; // [key => [signIdx, ...]]

    private static function loadFromDB(): void
    {
        if (self::$naisargika !== null) return;

        $planets = DB::table('planets')
            ->get(['name', 'naisargika_bala', 'min_shadbala_rupas',
                   'exaltation_degree', 'moolatrikona_sign_idx',
                   'moolatrikona_from', 'moolatrikona_to',
                   'friendly_sign_indices', 'enemy_sign_indices']);

        self::$naisargika   = [];
        self::$minRupas     = [];
        self::$exaltDeg     = [];
        self::$moolatrikona = [];
        self::$friends      = [];
        self::$enemies      = [];

        foreach ($planets as $p) {
            $key = strtolower($p->name);
            if ($p->naisargika_bala !== null)  self::$naisargika[$key]  = (float)$p->naisargika_bala;
            if ($p->min_shadbala_rupas !== null) self::$minRupas[$key]  = (float)$p->min_shadbala_rupas;
            if ($p->exaltation_degree !== null)  self::$exaltDeg[$key]  = (float)$p->exaltation_degree;
            if ($p->moolatrikona_sign_idx !== null) {
                self::$moolatrikona[$key] = [
                    (int)$p->moolatrikona_sign_idx,
                    (float)$p->moolatrikona_from,
                    (float)$p->moolatrikona_to,
                ];
            }
            self::$friends[$key] = $p->friendly_sign_indices
                ? json_decode($p->friendly_sign_indices, true) : [];
            self::$enemies[$key] = $p->enemy_sign_indices
                ? json_decode($p->enemy_sign_indices, true) : [];
        }

        // Own signs derived from zodiac_signs.lord_planet_id
        self::$ownSigns = [];
        $signLords = DB::table('zodiac_signs')
            ->join('planets', 'zodiac_signs.lord_planet_id', '=', 'planets.id')
            ->orderBy('zodiac_signs.sort_order')
            ->get(['zodiac_signs.sort_order', 'planets.name as planet_name']);
        foreach ($signLords as $s) {
            $key = strtolower($s->planet_name);
            self::$ownSigns[$key][] = (int)$s->sort_order;
        }
    }

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
        self::loadFromDB();

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
            $signIdx   = (int)floor($sider / 30.0);
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
            $naisargikaBala = self::$naisargika[$pid] ?? 0.0;

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
            $minReq  = self::$minRupas[$pid] ?? 5.0;
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
        $exaltDeg = self::$exaltDeg[$pid] ?? 0.0;
        $diff = abs($sider - $exaltDeg);
        if ($diff > 180.0) $diff = 360.0 - $diff;
        $uchcha = (180.0 - $diff) / 3.0;

        // b) Saptavargaja Bala
        $saptaBala = self::calcSaptavargajaBala($pid, $sider);

        // c) Ojayugma Bala
        $isOdd = ($signIdx % 2 === 0);
        $ojaBala = 0.0;
        if (in_array($pid, ['sun', 'mars', 'jupiter', 'saturn', 'mercury'])) {
            $ojaBala = $isOdd ? 15.0 : 0.0;
        } elseif (in_array($pid, ['moon', 'venus'])) {
            $ojaBala = $isOdd ? 0.0 : 15.0;
        }

        // d) Kendra Bala
        $kendraSign = (int)floor($sider / 30.0);
        $kendraDist = $kendraSign % 3;
        $kendraBala = match($kendraDist) {
            0 => 60.0,
            1 => 30.0,
            2 => 15.0,
            default => 15.0,
        };

        // e) Drekkana Bala
        $drekkana = (int)floor($degInSign / 10.0);
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
        return max(0.0, 60.0 * (1.0 - $diff / 180.0));
    }

    // ════════════════════════════════════════════════════════════
    //  3. KAALA BALA — Temporal Strength (BPHS Ch.30–32)
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
        // a) Nathonnatha Bala
        $natho = 0.0;
        if ($isDayBirth) {
            $natho = in_array($pid, ['sun', 'venus', 'jupiter']) ? 60.0 : 30.0;
        } else {
            $natho = in_array($pid, ['moon', 'mars', 'saturn'])  ? 60.0 : 30.0;
        }
        if ($pid === 'mercury') $natho = 60.0;

        // b) Paksha Bala
        $T       = ($jd - 2451545.0) / 36525.0;
        $moonLon = fmod(218.3164477 + 481267.88123421 * $T, 360.0);
        $sunLon  = fmod(280.46646   + 36000.76983     * $T, 360.0);
        $elong   = $n360($moonLon - $sunLon);
        $pakshaFrac = $elong <= 180.0 ? $elong / 180.0 : (360.0 - $elong) / 180.0;

        $paksha = in_array($pid, ['moon', 'mercury', 'venus', 'jupiter'])
            ? $pakshaFrac * 60.0
            : (1.0 - $pakshaFrac) * 60.0;

        // c) Tribhaga Bala
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

        // d) Vara Bala — weekday lord sequence (mathematical constant)
        $varaLords = ['sun','moon','mars','mercury','jupiter','venus','saturn'];
        $vara = ($varaLords[$birthDow % 7] === $pid) ? 45.0 : 0.0;

        // e) Hora Bala — hora lord sequence (mathematical constant)
        $horaSeq = ['sun','venus','mercury','moon','saturn','jupiter','mars'];
        $horaIdx = ($birthDow * 24 + $birthHour) % 7;
        $hora    = ($horaSeq[$horaIdx] === $pid) ? 60.0 : 0.0;

        // f) Ayana Bala
        $sunSiderApprox = $n360($sunLon - 23.85);
        $sunNorth  = $sunSiderApprox < 180.0;
        $planNorth = $sider < 180.0;
        $ayana = ($sunNorth === $planNorth) ? 30.0 : 15.0;
        if ($pid === 'sun'  && $sunNorth)  $ayana = 60.0;
        if ($pid === 'moon' && !$sunNorth) $ayana = 60.0;

        // g) Masa Bala — sign lord sequence (mathematical constant)
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
    // ════════════════════════════════════════════════════════════

    private static function calcChestaBala(
        string   $pid,
        bool     $retro,
        float    $sider,
        float    $jd,
        callable $n360
    ): float {
        if ($pid === 'sun' || $pid === 'moon') return 30.0;
        if ($retro) return 60.0;

        $meanMotion = [
            'mars'    => 0.524,
            'mercury' => 1.383,
            'jupiter' => 0.083,
            'venus'   => 1.202,
            'saturn'  => 0.034,
        ];

        $prevSider = self::approxPlanetLon($pid, $jd - 1.0, $n360);
        $nextSider = self::approxPlanetLon($pid, $jd + 1.0, $n360);
        $actualSpeed = $n360($nextSider - $prevSider);
        if ($actualSpeed > 180.0) $actualSpeed -= 360.0;

        $mean  = $meanMotion[$pid] ?? 1.0;
        $ratio = abs($actualSpeed) / $mean;
        return min(60.0, $ratio * 30.0);
    }

    // ════════════════════════════════════════════════════════════
    //  6. DRIG BALA — Aspectual Strength (BPHS Ch.35)
    // ════════════════════════════════════════════════════════════

    private static function calcDrigBala(
        string   $pid,
        float    $sider,
        array    $planets,
        callable $n360
    ): float {
        $drig = 0.0;

        foreach ($planets as $aspPid => $aspP) {
            if ($aspPid === $pid) continue;
            if (!isset($aspP['sider'])) continue;

            $aspIsNatBenefic = in_array($aspPid, ['moon', 'mercury', 'venus', 'jupiter']);
            $aspSider        = $aspP['sider'];
            $dist  = $n360($sider - $aspSider);
            $house = (int)floor($dist / 30.0) + 1;
            $frac  = self::getAspectFraction($aspPid, $house);
            if ($frac <= 0) continue;

            if ($aspIsNatBenefic) {
                $drig += $frac * 60.0 / 4.0;
            } else {
                $drig -= $frac * 60.0 / 4.0;
            }
        }

        return max(-60.0, min(60.0, $drig));
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Aspect fractions (BPHS)
    // ════════════════════════════════════════════════════════════

    private static function getAspectFraction(string $pid, int $house): float
    {
        if ($house === 7) return 1.0;

        return match($pid) {
            'mars'    => in_array($house, [4, 8])  ? 0.75 : 0.0,
            'jupiter' => in_array($house, [5, 9])  ? 1.0  : 0.0,
            'saturn'  => in_array($house, [3, 10]) ? 0.75 : 0.0,
            'rahu',
            'ketu'    => in_array($house, [5, 9])  ? 0.5  : 0.0,
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
        $exDeg = self::$exaltDeg[$pid] ?? -1.0;
        if ($exDeg >= 0) {
            $exSign = (int)floor($exDeg / 30.0);
            if ($vargaSign === $exSign) return 'exalted';
            $debSign = ($exSign + 6) % 12;
            if ($vargaSign === $debSign) return 'debilitated';
        }

        $mt = self::$moolatrikona[$pid] ?? null;
        if ($mt && $vargaSign === $mt[0]) {
            if (!$checkExact) return 'moolatrikona';
            $deg = fmod($sider, 30.0);
            if ($deg >= $mt[1] && $deg <= $mt[2]) return 'moolatrikona';
        }

        if (in_array($vargaSign, self::$ownSigns[$pid] ?? [])) return 'own';
        if (in_array($vargaSign, self::$friends[$pid]  ?? [])) return 'friendly';
        if (in_array($vargaSign, self::$enemies[$pid]  ?? [])) return 'enemy';
        return 'neutral';
    }

    // ════════════════════════════════════════════════════════════
    //  HELPER — Approximate planet longitude for Chesta Bala
    // ════════════════════════════════════════════════════════════

    private static function approxPlanetLon(string $pid, float $jd, callable $n360): float
    {
        $T = ($jd - 2451545.0) / 36525.0;
        return match($pid) {
            'mercury' => $n360(252.25 + 149474.07 * $T),
            'venus'   => $n360(181.98 + 58519.21  * $T),
            'mars'    => $n360(355.43 + 19141.70  * $T),
            'jupiter' => $n360(34.35  + 3036.30   * $T),
            'saturn'  => $n360(50.08  + 1223.51   * $T),
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
    //  VIEW DATA PREPARATION
    // ════════════════════════════════════════════════════════════

    public static function prepareForView(array $shadbala): array
    {
        $rows = [];
        $ri   = 0;
        foreach ($shadbala as $pid => $data) {
            $s  = self::SOFT[$pid] ?? ['bg'=>'#f4f4f4','border'=>'#d0d0d0','text'=>'#444','accent'=>'#666','abbr'=>'?'];
            $st = $data['sthanaBala'];
            $ka = $data['kaalaBala'];

            [$gradeColor, $gradeBg] = match($data['grade']) {
                'Exceptional', 'Very Strong' => ['#2e7a4e', '#e6f4ec'],
                'Strong'                     => ['#1a7ab5', '#eaf4fb'],
                'Moderate'                   => ['#b36000', '#fdf6e3'],
                'Weak'                       => ['#c0311f', '#fce8e6'],
                default                      => ['#9c2d8a', '#f9edf7'],
            };

            $rows[] = array_merge($data, [
                'pid'         => $pid,
                'style'       => $s,
                'isAlt'       => ($ri++ % 2 === 1),
                'drigPositive'=> $data['drigBala'] >= 0,
                'gradeColor'  => $gradeColor,
                'gradeBg'     => $gradeBg,
                'sthanaTip'   => 'Uchcha:'.$st['ucchaBala'].' · Sapta:'.$st['saptavargaBala']
                               . ' · Oja:'.$st['ojayugmaBala'].' · Kendra:'.$st['kendraBala']
                               . ' · Drekkana:'.$st['drekkanaBala'],
                'kaalaTip'    => 'Natho:'.$ka['nathoBala'].' · Paksha:'.$ka['pakshaBala']
                               . ' · Tribhaga:'.$ka['tribhagaBala'].' · Vara:'.$ka['varaBala']
                               . ' · Hora:'.$ka['horaBala'].' · Ayana:'.$ka['ayanaBala'],
            ]);
        }

        return ['svg' => self::buildSvgChart($shadbala), 'rows' => $rows];
    }

    private static function buildSvgChart(array $shadbala): string
    {
        $sc = fn(string $pid): array => self::SOFT[$pid]
            ?? ['bg'=>'#f4f4f4','border'=>'#d0d0d0','text'=>'#444','accent'=>'#666','abbr'=>'?'];

        $chartH   = 200;
        $barW     = 36;
        $barGap   = 18;
        $padLeft  = 44;
        $padRight = 12;
        $padTop   = 14;
        $padBot   = 58;

        $maxRupas = 0.0;
        foreach ($shadbala as $data) {
            if ($data['rupas'] > $maxRupas) $maxRupas = $data['rupas'];
        }
        $maxRupas = max($maxRupas * 1.20, 10.0);

        $splitRupas = 6.0;

        $nPlanets = count($shadbala);
        $totalW   = $padLeft + ($barW + $barGap) * $nPlanets - $barGap + $padRight;
        $svgH     = $chartH + $padTop + $padBot;

        $toY = fn(float $v): float => $chartH - ($v / $maxRupas) * $chartH;

        $splitYabs = $padTop + $toY($splitRupas);

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" ';
        $svg .= 'viewBox="0 0 '.$totalW.' '.$svgH.'" ';
        $svg .= 'style="width:100%;max-width:'.min($totalW * 1.6, 680).'px;';
        $svg .= 'display:block;overflow:visible;margin:0 auto">';

        $svg .= '<defs>
          <filter id="sb_shadow" x="-15%" y="-10%" width="130%" height="130%">
            <feDropShadow dx="0" dy="1.5" stdDeviation="2" flood-color="rgba(0,0,0,.15)"/>
          </filter>
        </defs>';

        $chartX2 = $totalW - $padRight;
        $chartW  = $chartX2 - $padLeft;

        $greenH = $splitYabs - $padTop;
        if ($greenH > 0) {
            $svg .= '<rect x="'.$padLeft.'" y="'.$padTop.'" ';
            $svg .= 'width="'.$chartW.'" height="'.round($greenH,1).'" ';
            $svg .= 'fill="#c8eecb" rx="4 4 0 0"/>';
        }

        $redTop = $splitYabs;
        $redH   = ($padTop + $chartH) - $redTop;
        if ($redH > 0) {
            $svg .= '<rect x="'.$padLeft.'" y="'.round($redTop,1).'" ';
            $svg .= 'width="'.$chartW.'" height="'.round($redH,1).'" ';
            $svg .= 'fill="#f5c6c6" rx="0 0 4 4"/>';
        }

        for ($g = 0; $g <= ceil($maxRupas); $g += 2) {
            $gy = round($padTop + $toY((float)$g), 1);
            if ($gy < $padTop - 1 || $gy > $padTop + $chartH + 1) continue;
            $svg .= '<line x1="'.$padLeft.'" y1="'.$gy.'" x2="'.$chartX2.'" y2="'.$gy.'" ';
            $svg .= 'stroke="rgba(0,0,0,.12)" stroke-width="1" stroke-dasharray="3,3"/>';
            $svg .= '<text x="'.($padLeft - 4).'" y="'.($gy + 3.5).'" ';
            $svg .= 'text-anchor="end" font-size="9" fill="#777" ';
            $svg .= 'font-family="DM Sans,sans-serif">'.$g.'</text>';
        }

        $svg .= '<line x1="'.$padLeft.'" y1="'.round($splitYabs,1).'" ';
        $svg .= 'x2="'.$chartX2.'" y2="'.round($splitYabs,1).'" ';
        $svg .= 'stroke="#333" stroke-width="2"/>';

        $i = 0;
        foreach ($shadbala as $pid => $data) {
            $s    = $sc($pid);
            $barX = $padLeft + $i * ($barW + $barGap);
            $barH = max(3, ($data['rupas'] / $maxRupas) * $chartH);
            $barY = round($padTop + $chartH - $barH, 1);

            $svg .= '<rect x="'.$barX.'" y="'.$barY.'" ';
            $svg .= 'width="'.$barW.'" height="'.round($barH,1).'" ';
            $svg .= 'fill="#f7f2e0" stroke="'.$s['accent'].'" stroke-width="1.2" ';
            $svg .= 'rx="2" filter="url(#sb_shadow)"/>';

            $capH = min(6, $barH);
            $svg .= '<rect x="'.$barX.'" y="'.$barY.'" ';
            $svg .= 'width="'.$barW.'" height="'.round($capH,1).'" ';
            $svg .= 'fill="'.$s['accent'].'" rx="2"/>';

            $minY = round($padTop + $toY($data['minRupas']), 1);
            if ($minY >= $padTop && $minY <= $padTop + $chartH) {
                $svg .= '<line x1="'.($barX - 3).'" y1="'.$minY.'" ';
                $svg .= 'x2="'.($barX + $barW + 3).'" y2="'.$minY.'" ';
                $svg .= 'stroke="'.$s['accent'].'" stroke-width="1.5" ';
                $svg .= 'stroke-dasharray="3,2" opacity=".65"/>';
            }

            $valY = max($padTop + 9, $barY - 4);
            $svg .= '<text x="'.($barX + $barW / 2).'" y="'.$valY.'" ';
            $svg .= 'text-anchor="middle" font-size="9.5" font-weight="800" ';
            $svg .= 'fill="'.$s['accent'].'" font-family="DM Sans,sans-serif">';
            $svg .= $data['rupas'].'</text>';

            $lblY = $padTop + $chartH + 15;
            $svg .= '<text x="'.($barX + $barW / 2).'" y="'.$lblY.'" ';
            $svg .= 'text-anchor="middle" font-size="11.5" font-weight="900" ';
            $svg .= 'fill="'.$s['accent'].'" font-family="DM Sans,sans-serif">';
            $svg .= $s['abbr'].'</text>';

            $svg .= '<text x="'.($barX + $barW / 2).'" y="'.($lblY + 13).'" ';
            $svg .= 'text-anchor="middle" font-size="8.5" fill="#555" ';
            $svg .= 'font-family="DM Sans,sans-serif">'.$data['rupas'].'</text>';

            $dotClr = $data['isStrong'] ? '#1e7a3e' : '#c03030';
            $svg .= '<circle cx="'.($barX + $barW / 2).'" cy="'.($lblY + 27).'" ';
            $svg .= 'r="4" fill="'.$dotClr.'"/>';

            $i++;
        }

        $svg .= '<rect x="'.$padLeft.'" y="'.$padTop.'" ';
        $svg .= 'width="'.$chartW.'" height="'.$chartH.'" ';
        $svg .= 'fill="none" stroke="rgba(0,0,0,.2)" stroke-width="1" rx="4"/>';

        $midY = $padTop + $chartH / 2;
        $svg .= '<text transform="rotate(-90)" ';
        $svg .= 'x="'.(-$midY).'" y="11" ';
        $svg .= 'text-anchor="middle" font-size="9" fill="#999" ';
        $svg .= 'font-family="DM Sans,sans-serif">Rupas</text>';

        $svg .= '</svg>';
        return $svg;
    }
}
