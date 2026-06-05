<?php

namespace App\Features\Planetary;

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
    private const ORDER = ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu'];

    // Coarse scan step (days) and max search window (days) per planet
    private const STEP = ['moon'=>0.1,'sun'=>0.5,'mercury'=>0.4,'venus'=>0.5,'mars'=>0.7,'jupiter'=>2.0,'saturn'=>3.0,'rahu'=>2.0,'ketu'=>2.0];
    private const WIN  = ['moon'=>6.0,'sun'=>45.0,'mercury'=>90.0,'venus'=>90.0,'mars'=>140.0,'jupiter'=>440.0,'saturn'=>1250.0,'rahu'=>680.0,'ketu'=>680.0];

    private const PMETA = [
        'sun'     => ['en'=>'Sun',     'hi'=>'सूर्य', 'sym'=>'☀', 'col'=>'#d4760a', 'nat'=>'malefic'],
        'moon'    => ['en'=>'Moon',    'hi'=>'चंद्र', 'sym'=>'☽', 'col'=>'#1d4e6f', 'nat'=>'benefic'],
        'mars'    => ['en'=>'Mars',    'hi'=>'मंगल',  'sym'=>'♂', 'col'=>'#b83020', 'nat'=>'malefic'],
        'mercury' => ['en'=>'Mercury', 'hi'=>'बुध',   'sym'=>'☿', 'col'=>'#2e7a6e', 'nat'=>'benefic'],
        'jupiter' => ['en'=>'Jupiter', 'hi'=>'गुरु',  'sym'=>'♃', 'col'=>'#7a5a10', 'nat'=>'benefic'],
        'venus'   => ['en'=>'Venus',   'hi'=>'शुक्र', 'sym'=>'♀', 'col'=>'#8e3a7a', 'nat'=>'benefic'],
        'saturn'  => ['en'=>'Saturn',  'hi'=>'शनि',   'sym'=>'♄', 'col'=>'#4a4060', 'nat'=>'malefic'],
        'rahu'    => ['en'=>'Rahu',    'hi'=>'राहु',  'sym'=>'☊', 'col'=>'#1a3a1a', 'nat'=>'malefic'],
        'ketu'    => ['en'=>'Ketu',    'hi'=>'केतु',  'sym'=>'☋', 'col'=>'#5a1a0a', 'nat'=>'malefic'],
    ];

    private const KARAKA = [
        'sun'=>'soul, authority, father & vitality',
        'moon'=>'mind, emotions, mother & comfort',
        'mars'=>'energy, courage, land & siblings',
        'mercury'=>'intellect, speech, business & learning',
        'jupiter'=>'wisdom, wealth, children & fortune',
        'venus'=>'love, relationships, comforts & arts',
        'saturn'=>'discipline, career, karma & longevity',
        'rahu'=>'ambition, foreign matters & sudden gains',
        'ketu'=>'spirituality, detachment & past karma',
    ];

    private const MONTHS = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

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
                'pid'       => $pid,
                'en'        => self::PMETA[$pid]['en'],
                'hi'        => self::PMETA[$pid]['hi'],
                'sym'       => self::PMETA[$pid]['sym'],
                'col'       => self::PMETA[$pid]['col'],
                'signIdx'   => $sIdx,
                'sign'      => $signs[$sIdx],
                'deg'       => AstroCalculator::dms(fmod($sider, 30.0)),
                'retro'     => $retro,
                'entryJd'   => $ing['entry'],
                'exitJd'    => $ing['exit'],
                'entry'     => self::fmtDateTime($ing['entry'], $utcOff),
                'exit'      => self::fmtDateTime($ing['exit'], $utcOff),
                'nextSign'  => $ing['exit'] !== null ? $signs[(self::signOf($ing['exit'] + 0.5, $pid))] : '—',
                'remDays'   => $remDays !== null ? round($remDays, 1) : null,
                'totDays'   => $totDays !== null ? round($totDays, 1) : null,
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
        $signs = AstroCalculator::getVedicSigns();
        $list  = [];
        foreach ($transitDetails as $tpid => $t) {
            $s = $t['signIdx'];
            $benefic = self::PMETA[$tpid]['nat'] === 'benefic';
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

                $nMeta = self::PMETA[$npid];
                $kar   = self::KARAKA[$npid];
                if ($type === 'conjunction') {
                    $en = self::PMETA[$tpid]['en'] . " conjoins your natal " . $nMeta['en']
                        . " in " . $signs[$nSign] . " — intensified focus on " . $kar . ".";
                } else {
                    $en = self::PMETA[$tpid]['en'] . "'s " . $type . " aspect falls on your natal "
                        . $nMeta['en'] . " (" . $signs[$nSign] . ") — " . $tone . " influence on " . $kar . ".";
                }
                $list[] = [
                    'transit' => $tpid,
                    'tSym'    => self::PMETA[$tpid]['sym'],
                    'tCol'    => self::PMETA[$tpid]['col'],
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
                        'sym'   => self::PMETA[$pid]['sym'],
                        'col'   => self::PMETA[$pid]['col'],
                        'en'    => self::PMETA[$pid]['en'],
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
    //  RENDERERS
    // ══════════════════════════════════════════════════════════════
    public static function renderDate(array $details, array $aspects, string $dateLabel, int $natalMoonSign, float $utcOff): string
    {
        $signs = AstroCalculator::getVedicSigns();

        $h  = '<div style="font-family:\'DM Sans\',sans-serif">';

        // Transit detail table
        $h .= '<div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.4px;font-weight:800;color:var(--gold);margin-bottom:12px">◈ Planetary Transits · ' . $dateLabel . '</div>';
        $h .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-bottom:26px">';
        foreach ($details as $d) {
            $retro = $d['retro'] ? ' <span style="color:#b83020;font-weight:800">℞</span>' : '';
            $rem   = $d['remDays'] !== null ? $d['remDays'] . ' days left' : '—';
            $tot   = $d['totDays'] !== null ? $d['totDays'] . 'd total' : '';
            $h .= '<div style="background:var(--card);border:1.5px solid var(--sky-pale);border-left:4px solid ' . $d['col'] . ';border-radius:14px;padding:14px 16px">'
                . '<div style="display:flex;align-items:center;gap:9px;margin-bottom:8px">'
                . '<span style="font-size:1.5rem;color:' . $d['col'] . '">' . $d['sym'] . '</span>'
                . '<div style="flex:1"><div style="font-weight:800;color:var(--text)">' . $d['en']
                . ' <span style="font-weight:500;color:var(--text-lt);font-size:.82rem">' . $d['hi'] . '</span>' . $retro . '</div>'
                . '<div style="font-size:.78rem;color:var(--text-mid)">in <strong>' . $d['sign'] . '</strong> · ' . $d['deg'] . '</div></div>'
                . '</div>'
                . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;font-size:.74rem;border-top:1px solid var(--sky-pale);padding-top:8px">'
                . '<div><span style="color:var(--text-lt)">Entered</span><br><strong style="color:var(--text)">' . $d['entry'] . '</strong></div>'
                . '<div><span style="color:var(--text-lt)">Exits</span><br><strong style="color:var(--text)">' . $d['exit'] . '</strong></div>'
                . '<div><span style="color:var(--text-lt)">Remaining</span><br><strong style="color:#2e7a6e">' . $rem . '</strong></div>'
                . '<div><span style="color:var(--text-lt)">Next sign</span><br><strong style="color:' . $d['col'] . '">' . $d['nextSign'] . '</strong></div>'
                . '</div></div>';
        }
        $h .= '</div>';

        // Aspect effects
        $h .= '<div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.4px;font-weight:800;color:var(--gold);margin-bottom:6px">◈ Transit Aspects on Your Natal Planets (Graha Drishti)</div>';
        $h .= '<div style="font-size:.82rem;color:var(--text-mid);margin-bottom:14px">How each transiting planet currently influences your birth planets through conjunction and Vedic aspects.</div>';
        if (empty($aspects)) {
            $h .= '<div style="color:var(--text-lt);font-style:italic;padding:14px">No major transit aspects on natal planets at this date.</div>';
        } else {
            $h .= '<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:10px">';
            foreach ($aspects as $a) {
                $bcol = $a['benefic'] ? '#2e7a6e' : '#b83020';
                $bg   = $a['benefic'] ? '#eaf6ef' : '#fbecec';
                $h .= '<div style="display:flex;align-items:center;gap:12px;background:' . $bg . ';border:1px solid ' . $bcol . '33;border-radius:12px;padding:11px 15px">'
                    . '<span style="font-size:1.2rem;color:' . $a['tCol'] . ';flex-shrink:0">' . $a['tSym'] . '<span style="color:var(--text-lt);font-size:.9rem">→</span> ' . $a['nSym'] . '</span>'
                    . '<div style="font-size:.84rem;color:var(--text);line-height:1.5">' . $a['text'] . '</div>'
                    . '</div>';
            }
            $h .= '</div>';
        }

        $h .= '</div>';
        return $h;
    }

    public static function renderCalendar(array $events, string $title, float $utcOff, bool $groupByMonth): string
    {
        $h  = '<div style="font-family:\'DM Sans\',sans-serif">';
        $h .= '<div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.4px;font-weight:800;color:var(--gold);margin-bottom:4px">◈ ' . $title . '</div>';
        $h .= '<div style="font-size:.82rem;color:var(--text-mid);margin-bottom:16px">' . count($events) . ' planetary sign-changes (Sankranti / ingress) in this period. Each row marks when a graha leaves one rashi and enters the next.</div>';

        if (empty($events)) {
            $h .= '<div style="color:var(--text-lt);font-style:italic;padding:14px">No sign-changes in this period.</div></div>';
            return $h;
        }

        $lastMonth = '';
        $h .= '<div style="display:flex;flex-direction:column;gap:7px">';
        foreach ($events as $e) {
            if ($groupByMonth) {
                $p  = self::jdToLocalParts($e['jd'], $utcOff);
                $mk = self::MONTHS[$p['m']] . ' ' . $p['y'];
                if ($mk !== $lastMonth) {
                    $lastMonth = $mk;
                    $h .= '<div style="font-family:\'Playfair Display\',serif;font-size:1rem;font-weight:700;color:var(--sky);margin:12px 0 4px">' . $mk . '</div>';
                }
            }
            $retro = $e['retro'] ? ' <span style="color:#b83020;font-weight:800;font-size:.72rem">℞</span>' : '';
            $h .= '<div style="display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--sky-pale);border-left:4px solid ' . $e['col'] . ';border-radius:12px;padding:10px 15px">'
                . '<span style="font-size:1.35rem;color:' . $e['col'] . ';flex-shrink:0;width:26px;text-align:center">' . $e['sym'] . '</span>'
                . '<div style="flex:1;min-width:0"><div style="font-weight:700;color:var(--text);font-size:.88rem">' . $e['en'] . $retro
                . ' <span style="font-weight:500;color:var(--text-mid)">enters ' . $e['to'] . '</span></div>'
                . '<div style="font-size:.74rem;color:var(--text-lt)">leaves ' . $e['from'] . ' → ' . $e['to'] . '</div></div>'
                . '<div style="font-family:\'DM Mono\',monospace;font-size:.76rem;color:var(--text-mid);text-align:right;flex-shrink:0">' . $e['when'] . '</div>'
                . '</div>';
        }
        $h .= '</div></div>';
        return $h;
    }
}
