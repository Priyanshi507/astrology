<?php

namespace App\Services\Planetary;

use Illuminate\Support\Facades\DB;

/**
 * TransitCalculator — Dynamic Gochar (planetary transit) engine
 *
 * Pure mathematics (no API). Built on AstroCalculator's Jean Meeus
 * longitude routines + Lahiri ayanamsa.
 *
 *   • Sign ingress (entry) and egress (exit) dates of every graha in
 *     its current sign — found by sidereal-longitude bisection.
 *   • Transit-to-natal graha drishti (aspects) with effect descriptions
 *     per Brihat Parashara Hora Shastra aspect rules.
 *   • Month / Year ingress calendars (every sign change in the period).
 */
class TransitCalculator
{
    // ── Display/iteration order (algorithmic constant) ────────────
    private const ORDER = ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu'];

    // ── Scan step and search window per planet (mathematical constants)
    private const STEP = ['moon'=>0.1,'sun'=>0.5,'mercury'=>0.4,'venus'=>0.5,'mars'=>0.7,'jupiter'=>2.0,'saturn'=>3.0,'rahu'=>2.0,'ketu'=>2.0];
    private const WIN  = ['moon'=>6.0,'sun'=>45.0,'mercury'=>90.0,'venus'=>90.0,'mars'=>140.0,'jupiter'=>440.0,'saturn'=>1250.0,'rahu'=>680.0,'ketu'=>680.0];

    public const MONTHS = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    // ── DB-backed static caches ───────────────────────────────────
    private static ?array $pmeta  = null; // [key => ['en','sym','col','nat']]
    private static ?array $karaka = null; // [key => karaka string]

    private static function loadFromDB(): void
    {
        if (self::$pmeta !== null) return;

        $rows = DB::table('planets')
            ->get(['name', 'vedic_name', 'symbol', 'color_hex', 'nature', 'karaka_en']);

        self::$pmeta  = [];
        self::$karaka = [];

        foreach ($rows as $p) {
            $key = strtolower($p->name);
            $nature = strtolower($p->nature ?? '');
            self::$pmeta[$key] = [
                'en'  => $p->name,
                'hi'  => $p->vedic_name ?? $p->name,
                'sym' => $p->symbol ?? '',
                'col' => $p->color_hex ?? '#888888',
                'nat' => str_contains($nature, 'malefic') ? 'malefic' : 'benefic',
            ];
            self::$karaka[$key] = $p->karaka_en ?? '';
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  LOW-LEVEL: longitude / sign / retro
    // ══════════════════════════════════════════════════════════════
    private static function tropLon(float $jd, string $pid): float
    {
        return match ($pid) {
            'sun'   => AstroCalculator::sunLongitude($jd),
            'moon'  => AstroCalculator::moonLongitude($jd),
            'rahu'  => AstroCalculator::rahuLongitude($jd),
            'ketu'  => AstroCalculator::n360(AstroCalculator::rahuLongitude($jd) + 180.0),
            default => AstroCalculator::planetLongitude($jd, $pid),
        };
    }

    private static function siderLon(float $jd, string $pid): float
    {
        return AstroCalculator::n360(self::tropLon($jd, $pid) - AstroCalculator::lahiriAyanamsa($jd));
    }

    private static function signOf(float $jd, string $pid): int
    {
        return (int)floor(self::siderLon($jd, $pid) / 30.0);
    }

    private static function isRetro(float $jd, string $pid): bool
    {
        if ($pid === 'sun' || $pid === 'moon')  return false;
        if ($pid === 'rahu' || $pid === 'ketu') return true;
        $l1 = self::siderLon($jd, $pid);
        $l2 = self::siderLon($jd + 0.5, $pid);
        $d  = $l2 - $l1;
        if ($d >  180) $d -= 360;
        if ($d < -180) $d += 360;
        return $d < 0;
    }

    // JD where the sign changes between $lo and $hi (exactly one side == $sign0)
    private static function bisectBoundary(float $lo, float $hi, int $sign0, string $pid): float
    {
        $loIsS = self::signOf($lo, $pid) === $sign0;
        for ($i = 0; $i < 42; $i++) {
            $m    = ($lo + $hi) / 2.0;
            $mIsS = self::signOf($m, $pid) === $sign0;
            if ($mIsS === $loIsS) $lo = $m; else $hi = $m;
            if ($hi - $lo < 6.94e-4) break; // ~1 minute
        }
        return ($lo + $hi) / 2.0;
    }

    /** Entry/exit JD of the planet's CURRENT sign at $jd0. */
    private static function findIngress(float $jd0, string $pid): array
    {
        $sign0 = self::signOf($jd0, $pid);
        $step  = self::STEP[$pid];
        $max   = (int)ceil(self::WIN[$pid] / $step);

        // exit — scan forward
        $exit = null; $t = $jd0;
        for ($i = 0; $i < $max; $i++) {
            $t2 = $t + $step;
            if (self::signOf($t2, $pid) !== $sign0) { $exit = self::bisectBoundary($t, $t2, $sign0, $pid); break; }
            $t = $t2;
        }
        // entry — scan backward
        $entry = null; $t = $jd0;
        for ($i = 0; $i < $max; $i++) {
            $t2 = $t - $step;
            if (self::signOf($t2, $pid) !== $sign0) { $entry = self::bisectBoundary($t2, $t, $sign0, $pid); break; }
            $t = $t2;
        }
        return ['entry' => $entry, 'exit' => $exit, 'sign' => $sign0];
    }

    // ══════════════════════════════════════════════════════════════
    //  JD ↔ civil date helpers
    // ══════════════════════════════════════════════════════════════
    public static function localJd(int $yr, int $mo, int $dy, float $localHr, float $utcOff): float
    {
        return AstroCalculator::julianDay($yr, $mo, $dy, $localHr - $utcOff);
    }

    private static function jdToLocalParts(float $jd, float $utcOff): array
    {
        $j = $jd + $utcOff / 24.0 + 0.5;
        $Z = (int)floor($j);
        $F = $j - $Z;
        if ($Z < 2299161) { $A = $Z; }
        else { $al = (int)floor(($Z - 1867216.25) / 36524.25); $A = $Z + 1 + $al - (int)floor($al / 4); }
        $B = $A + 1524;
        $C = (int)floor(($B - 122.1) / 365.25);
        $D = (int)floor(365.25 * $C);
        $E = (int)floor(($B - $D) / 30.6001);
        $dayF = $B - $D - (int)floor(30.6001 * $E) + $F;
        $day  = (int)floor($dayF);
        $mon  = $E < 14 ? $E - 1 : $E - 13;
        $yr   = $mon > 2 ? $C - 4716 : $C - 4715;
        $hrs  = ($dayF - $day) * 24.0;
        $h    = (int)floor($hrs);
        $m    = (int)round(($hrs - $h) * 60.0);
        if ($m === 60) { $m = 0; $h++; }
        if ($h === 24) { $h = 0; $day++; }
        return ['y'=>$yr, 'm'=>$mon, 'd'=>$day, 'h'=>$h, 'min'=>$m];
    }

    private static function fmtDate(?float $jd, float $utcOff): string
    {
        if ($jd === null) return '—';
        $p = self::jdToLocalParts($jd, $utcOff);
        return sprintf('%02d %s %d', $p['d'], self::MONTHS[$p['m']], $p['y']);
    }

    private static function fmtDateTime(?float $jd, float $utcOff): string
    {
        if ($jd === null) return '—';
        $p = self::jdToLocalParts($jd, $utcOff);
        return sprintf('%02d %s %d · %02d:%02d', $p['d'], self::MONTHS[$p['m']], $p['y'], $p['h'], $p['min']);
    }

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC: per-planet transit detail for a JD
    // ══════════════════════════════════════════════════════════════
    public static function planetDetails(float $jd, float $utcOff): array
    {
        self::loadFromDB();

        $signs = AstroCalculator::getVedicSigns();
        $out   = [];
        foreach (self::ORDER as $pid) {
            $sider = self::siderLon($jd, $pid);
            $sIdx  = (int)floor($sider / 30.0);
            $ing   = self::findIngress($jd, $pid);
            $retro = self::isRetro($jd, $pid);
            $remDays = ($ing['exit'] !== null) ? ($ing['exit'] - $jd) : null;
            $totDays = ($ing['exit'] !== null && $ing['entry'] !== null) ? ($ing['exit'] - $ing['entry']) : null;
            $out[$pid] = [
                'pid'     => $pid,
                'en'      => self::$pmeta[$pid]['en']  ?? ucfirst($pid),
                'hi'      => self::$pmeta[$pid]['hi']  ?? ucfirst($pid),
                'sym'     => self::$pmeta[$pid]['sym'] ?? '',
                'col'     => self::$pmeta[$pid]['col'] ?? '#888888',
                'signIdx' => $sIdx,
                'sign'    => $signs[$sIdx],
                'deg'     => AstroCalculator::dms(fmod($sider, 30.0)),
                'retro'   => $retro,
                'entryJd' => $ing['entry'],
                'exitJd'  => $ing['exit'],
                'entry'   => self::fmtDateTime($ing['entry'], $utcOff),
                'exit'    => self::fmtDateTime($ing['exit'], $utcOff),
                'nextSign'=> $ing['exit'] !== null ? $signs[(self::signOf($ing['exit'] + 0.5, $pid))] : '—',
                'remDays' => $remDays !== null ? round($remDays, 1) : null,
                'totDays' => $totDays !== null ? round($totDays, 1) : null,
            ];
        }
        return $out;
    }

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC: transit → natal aspects (graha drishti)
    // ══════════════════════════════════════════════════════════════
    private static function aspectSigns(string $pid, int $s): array
    {
        $a = [[($s + 6) % 12, '7th']]; // all planets aspect the 7th
        if ($pid === 'mars')              { $a[] = [($s + 3) % 12, '4th']; $a[] = [($s + 7) % 12, '8th']; }
        elseif ($pid === 'jupiter')       { $a[] = [($s + 4) % 12, '5th']; $a[] = [($s + 8) % 12, '9th']; }
        elseif ($pid === 'saturn')        { $a[] = [($s + 2) % 12, '3rd']; $a[] = [($s + 9) % 12, '10th']; }
        elseif ($pid === 'rahu' || $pid === 'ketu') { $a[] = [($s + 4) % 12, '5th']; $a[] = [($s + 8) % 12, '9th']; }
        return $a;
    }

    /** @param array $natalSigns [pid => signIdx] */
    public static function natalAspects(array $transitDetails, array $natalSigns): array
    {
        self::loadFromDB();

        $signs = AstroCalculator::getVedicSigns();
        $list  = [];
        foreach ($transitDetails as $tpid => $t) {
            $s       = $t['signIdx'];
            $benefic = (self::$pmeta[$tpid]['nat'] ?? 'malefic') === 'benefic';
            $tone    = $benefic ? 'a supportive and growth-giving' : 'a testing and transformative';
            foreach ($natalSigns as $npid => $nSign) {
                $type = null;
                if ($nSign === $s) {
                    $type = 'conjunction';
                } else {
                    foreach (self::aspectSigns($tpid, $s) as [$asp, $lbl]) {
                        if ($asp === $nSign) { $type = $lbl; break; }
                    }
                }
                if ($type === null) continue;

                $nMeta = self::$pmeta[$npid] ?? ['en' => ucfirst($npid), 'sym' => '', 'col' => '#888'];
                $kar   = self::$karaka[$npid] ?? '';
                if ($type === 'conjunction') {
                    $en = (self::$pmeta[$tpid]['en'] ?? ucfirst($tpid)) . " conjoins your natal " . $nMeta['en']
                        . " in " . $signs[$nSign] . " — intensified focus on " . $kar . ".";
                } else {
                    $en = (self::$pmeta[$tpid]['en'] ?? ucfirst($tpid)) . "'s " . $type . " aspect falls on your natal "
                        . $nMeta['en'] . " (" . $signs[$nSign] . ") — " . $tone . " influence on " . $kar . ".";
                }
                $list[] = [
                    'transit' => $tpid,
                    'tSym'    => self::$pmeta[$tpid]['sym'] ?? '',
                    'tCol'    => self::$pmeta[$tpid]['col'] ?? '#888888',
                    'natal'   => $npid,
                    'nSym'    => $nMeta['sym'],
                    'type'    => $type,
                    'benefic' => $benefic,
                    'text'    => $en,
                ];
            }
        }
        return $list;
    }

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC: ingress calendar for a date range
    // ══════════════════════════════════════════════════════════════
    public static function rangeEvents(int $y1, int $m1, int $d1, int $y2, int $m2, int $d2, float $utcOff, array $planets): array
    {
        self::loadFromDB();

        $signs   = AstroCalculator::getVedicSigns();
        $startJd = self::localJd($y1, $m1, $d1, 0.0, $utcOff);
        $endJd   = self::localJd($y2, $m2, $d2, 24.0, $utcOff);
        $events  = [];

        foreach ($planets as $pid) {
            $prevSign = self::signOf($startJd, $pid);
            $t = $startJd + 1.0;
            while ($t <= $endJd) {
                $sign = self::signOf($t, $pid);
                if ($sign !== $prevSign) {
                    $b = self::bisectBoundary($t - 1.0, $t, $prevSign, $pid);
                    $events[] = [
                        'jd'    => $b,
                        'pid'   => $pid,
                        'sym'   => self::$pmeta[$pid]['sym'] ?? '',
                        'col'   => self::$pmeta[$pid]['col'] ?? '#888888',
                        'en'    => self::$pmeta[$pid]['en']  ?? ucfirst($pid),
                        'from'  => $signs[$prevSign],
                        'to'    => $signs[$sign],
                        'retro' => self::isRetro($b, $pid),
                        'when'  => self::fmtDateTime($b, $utcOff),
                    ];
                    $prevSign = $sign;
                }
                $t += 1.0;
            }
        }
        usort($events, fn($a, $b) => $a['jd'] <=> $b['jd']);
        return $events;
    }

    // ══════════════════════════════════════════════════════════════
    //  VIEW DATA PREPARATION
    // ══════════════════════════════════════════════════════════════

    /** Prepare planet-detail cards for the transit detail Blade partial. */
    public static function prepareDetailView(array $details, string $dateLabel): array
    {
        $cards = [];
        foreach ($details as $d) {
            $cards[] = array_merge($d, [
                'remLabel' => $d['remDays'] !== null ? $d['remDays'] . ' days left' : '—',
            ]);
        }
        return ['dateLabel' => $dateLabel, 'cards' => $cards];
    }

    /** Prepare transit-aspect rows for the transit aspects Blade partial. */
    public static function prepareAspectsView(array $aspects): array
    {
        $rows = [];
        foreach ($aspects as $a) {
            $rows[] = array_merge($a, [
                'bgColor'     => $a['benefic'] ? '#eaf6ef'   : '#fbecec',
                'borderColor' => $a['benefic'] ? '#2e7a6e33' : '#b8302033',
            ]);
        }
        return ['aspects' => $rows];
    }

    /** Prepare sign-change event rows for the transit calendar Blade partial. */
    public static function prepareCalendarView(array $events, string $title, float $utcOff, bool $groupByMonth): array
    {
        $rows      = [];
        $lastMonth = '';
        foreach ($events as $e) {
            $monthHeading = null;
            if ($groupByMonth) {
                $p  = self::jdToLocalParts($e['jd'], $utcOff);
                $mk = self::MONTHS[$p['m']] . ' ' . $p['y'];
                if ($mk !== $lastMonth) { $lastMonth = $mk; $monthHeading = $mk; }
            }
            $rows[] = array_merge($e, ['monthHeading' => $monthHeading]);
        }
        return ['title' => $title, 'count' => count($events), 'rows' => $rows];
    }
}
