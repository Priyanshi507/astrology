<?php

namespace App\Services\Dasha;

use Illuminate\Support\Facades\DB;

/**
 * VimshottariDashaCalculator
 *
 * Implements the complete Vimshottari Dasha system per:
 *   - Brihat Parashara Hora Shastra (BPHS), Ch. 46–48
 *   - Komilla Sutton, "The Essentials of Vedic Astrology"
 *
 * System overview:
 *   Total cycle  : 120 years
 *   Base         : Moon's sidereal Nakshatra at birth
 *   Levels       : Mahadasha → Antardasha → Pratyantar → Sookshma → Prana
 *
 * Nakshatra → Dasha lord mapping (BPHS Ch.46):
 *   Each of the 27 Nakshatras is ruled by one of 9 lords in order,
 *   repeating cyclically: Ke Ve Su Mo Ma Ra Ju Sa Me
 *
 * Balance calculation:
 *   The fraction of the Nakshatra already elapsed at birth
 *   determines how much of the first Mahadasha has been "used up".
 */
class VimshottariDashaCalculator
{
    // ── Total cycle (mathematical constant) ─────────────────────
    private const CYCLE_DAYS = 43830.0;

    // ── DB-backed static caches (loaded once per process) ────────
    private static ?array $lords       = null; // ordered sequence ['ketu','venus',...]
    private static ?array $years       = null; // [name => int years]
    private static ?array $lordDays    = null; // [name => float days]
    private static ?array $abbr        = null; // [name => abbreviation]
    private static ?array $colors      = null; // [name => color_hex]
    private static ?array $nakNames    = null; // [0..26 => nakshatra name]
    private static ?array $syms        = null; // [name => symbol]
    private static ?array $fullNames   = null; // [name => 'Name (VedicName)']
    private static ?array $lordDetails = null; // [name => detail array]

    private static function loadFromDB(): void
    {
        if (self::$lords !== null) return;

        $rows = DB::table('planets')
            ->orderBy('vimshottari_order')
            ->get(['name','abbreviation','symbol','color_hex','vedic_name',
                   'vimshottari_dasha_years','nature','rules_signs',
                   'exaltation_text','debilitation_text','gemstone','metal',
                   'lucky_day','numerology_number','significations','themes']);

        self::$lords       = [];
        self::$years       = [];
        self::$lordDays    = [];
        self::$abbr        = [];
        self::$colors      = [];
        self::$syms        = [];
        self::$fullNames   = [];
        self::$lordDetails = [];

        foreach ($rows as $p) {
            $key = strtolower($p->name);
            self::$lords[]         = $key;
            self::$years[$key]     = (int)$p->vimshottari_dasha_years;
            self::$lordDays[$key]  = (int)$p->vimshottari_dasha_years * 365.25;
            self::$abbr[$key]      = $p->abbreviation;
            self::$colors[$key]    = $p->color_hex ?? '#888888';
            self::$syms[$key]      = $p->symbol ?? '◈';
            self::$fullNames[$key] = $p->name . ' (' . $p->vedic_name . ')';
            if ($p->significations) {
                self::$lordDetails[$key] = [
                    'nature'   => $p->nature ?? '',
                    'rules'    => $p->rules_signs ?? '',
                    'exalt'    => $p->exaltation_text ?? '',
                    'debil'    => $p->debilitation_text ?? '',
                    'signif'   => $p->significations ?? '',
                    'themes'   => $p->themes ?? '',
                    'gemstone' => $p->gemstone ?? '',
                    'metal'    => $p->metal ?? '',
                    'day'      => $p->lucky_day ?? '',
                    'num'      => (string)($p->numerology_number ?? ''),
                ];
            }
        }

        self::$nakNames = DB::table('nakshatras')
            ->orderBy('starting_degree')
            ->pluck('name')
            ->values()
            ->toArray();
    }

    // ── Soft palette matching existing planet tiles (UI-only) ────
    private const SOFT = [
        'sun'     => ['bg'=>'#fff8ee','border'=>'#f5c870','text'=>'#5a2a00','accent'=>'#b35a00','light'=>'#fff3e0'],
        'moon'    => ['bg'=>'#eaf4fb','border'=>'#90c8e8','text'=>'#0a2540','accent'=>'#1565a0','light'=>'#e0f0fa'],
        'mercury' => ['bg'=>'#e8f7f5','border'=>'#a0d8d0','text'=>'#163d35','accent'=>'#0a7a50','light'=>'#dff4f0'],
        'venus'   => ['bg'=>'#f9edf7','border'=>'#d8a0d0','text'=>'#4a0e3a','accent'=>'#8a2070','light'=>'#f5e4f5'],
        'mars'    => ['bg'=>'#fce8e6','border'=>'#e8a0a0','text'=>'#6a0c08','accent'=>'#b02010','light'=>'#fae0de'],
        'jupiter' => ['bg'=>'#fdf6e3','border'=>'#d8c080','text'=>'#3a2800','accent'=>'#9a5000','light'=>'#faf0d8'],
        'saturn'  => ['bg'=>'#eeecf5','border'=>'#b0a8d0','text'=>'#201830','accent'=>'#4a3a7a','light'=>'#e8e4f0'],
        'rahu'    => ['bg'=>'#e6f0e6','border'=>'#90b890','text'=>'#081808','accent'=>'#146030','light'=>'#dceadc'],
        'ketu'    => ['bg'=>'#f5e8e4','border'=>'#d8a898','text'=>'#2a0800','accent'=>'#8a3008','light'=>'#f0e0d8'],
    ];

    // ════════════════════════════════════════════════════════════
    //  PRIMARY ENTRY POINT
    // ════════════════════════════════════════════════════════════

    public static function calculate(
        float $moonSiderLon,
        int   $birthYear,
        int   $birthMonth,
        int   $birthDay,
        float $birthHour = 0.0
    ): array {
        self::loadFromDB();

        $nakSz    = 360.0 / 27.0;
        $nakIdx   = (int)floor($moonSiderLon / $nakSz);
        $nakProg  = fmod($moonSiderLon, $nakSz) / $nakSz;

        $lordSeqIdx = $nakIdx % 9;
        $birthLord  = self::$lords[$lordSeqIdx];

        $lordDays      = self::$lordDays[$birthLord];
        $elapsedDays   = $nakProg * $lordDays;
        $remainingDays = $lordDays - $elapsedDays;

        $birthJD = self::toJD($birthYear, $birthMonth, $birthDay, $birthHour);

        $mahadashas = self::buildMahadashas($birthJD, $birthLord, $remainingDays);

        $currentJD = self::toJD(
            (int)date('Y'), (int)date('n'), (int)date('j'),
            (float)date('G') + (float)date('i') / 60.0
        );
        $current = self::findCurrent($mahadashas, $currentJD);

        return [
            'moonSiderLon'  => $moonSiderLon,
            'nakIdx'        => $nakIdx,
            'nakName'       => self::$nakNames[$nakIdx],
            'nakProg'       => round($nakProg * 100, 2),
            'birthLord'     => $birthLord,
            'birthLordYrs'  => self::$years[$birthLord],
            'elapsedDays'   => round($elapsedDays, 2),
            'remainingDays' => round($remainingDays, 2),
            'remainingStr'  => self::daysToYMD($remainingDays),
            'lordSeqIdx'    => $lordSeqIdx,
            'mahadashas'    => $mahadashas,
            'current'       => $current,
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD MAHADASHA SEQUENCE
    // ════════════════════════════════════════════════════════════

    private static function buildMahadashas(
        float  $birthJD,
        string $startLord,
        float  $firstLordRemainingDays
    ): array {
        $mahadashas = [];
        $startIdx   = array_search($startLord, self::$lords);
        $currentJD  = $birthJD;

        for ($i = 0; $i < 9; $i++) {
            $idx     = ($startIdx + $i) % 9;
            $lord    = self::$lords[$idx];
            $days    = ($i === 0) ? $firstLordRemainingDays : self::$lordDays[$lord];
            $endJD   = $currentJD + $days;

            $antars  = self::buildAntardashas($lord, $currentJD, $days);

            $mahadashas[] = [
                'lord'        => $lord,
                'abbr'        => self::$abbr[$lord],
                'years'       => self::$years[$lord],
                'days'        => round($days, 2),
                'durationStr' => self::daysToYMD($days),
                'startJD'     => $currentJD,
                'endJD'       => $endJD,
                'startDate'   => self::jdToDate($currentJD),
                'endDate'     => self::jdToDate($endJD),
                'color'       => self::$colors[$lord],
                'antars'      => $antars,
            ];

            $currentJD = $endJD;
        }

        return $mahadashas;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD ANTARDASHA (Bhukti) SEQUENCE
    // ════════════════════════════════════════════════════════════

    private static function buildAntardashas(
        string $mahaLord,
        float  $mahaStartJD,
        float  $mahaDays
    ): array {
        $antars    = [];
        $startIdx  = array_search($mahaLord, self::$lords);
        $currentJD = $mahaStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx       = ($startIdx + $i) % 9;
            $antarLord = self::$lords[$idx];
            $antarDays = (self::$years[$mahaLord] * self::$years[$antarLord] / 120.0) * 365.25;
            $endJD     = $currentJD + $antarDays;

            $pratyantars = self::buildPratyantardashas($mahaLord, $antarLord, $currentJD, $antarDays);

            $antars[] = [
                'lord'        => $antarLord,
                'abbr'        => self::$abbr[$antarLord],
                'days'        => round($antarDays, 2),
                'durationStr' => self::daysToYMD($antarDays),
                'startJD'     => $currentJD,
                'endJD'       => $endJD,
                'startDate'   => self::jdToDate($currentJD),
                'endDate'     => self::jdToDate($endJD),
                'color'       => self::$colors[$antarLord],
                'pratyantars' => $pratyantars,
            ];

            $currentJD = $endJD;
        }

        return $antars;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD PRATYANTAR DASHA
    // ════════════════════════════════════════════════════════════

    private static function buildPratyantardashas(
        string $mahaLord,
        string $antarLord,
        float  $antarStartJD,
        float  $antarDays
    ): array {
        $pratyantars = [];
        $startIdx    = array_search($antarLord, self::$lords);
        $currentJD   = $antarStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx      = ($startIdx + $i) % 9;
            $pratLord = self::$lords[$idx];
            $pratDays = ($antarDays * self::$years[$pratLord]) / 120.0;
            $endJD    = $currentJD + $pratDays;

            $sookshmas = self::buildSookshmaDashas($antarLord, $pratLord, $currentJD, $pratDays);

            $pratyantars[] = [
                'lord'        => $pratLord,
                'abbr'        => self::$abbr[$pratLord],
                'days'        => round($pratDays, 2),
                'durationStr' => self::daysToYMD($pratDays),
                'startJD'     => $currentJD,
                'endJD'       => $endJD,
                'startDate'   => self::jdToDate($currentJD),
                'endDate'     => self::jdToDate($endJD),
                'color'       => self::$colors[$pratLord],
                'sookshmas'   => $sookshmas,
            ];

            $currentJD = $endJD;
        }

        return $pratyantars;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD SOOKSHMA DASHA (4th level)
    // ════════════════════════════════════════════════════════════

    private static function buildSookshmaDashas(
        string $antarLord,
        string $pratLord,
        float  $pratStartJD,
        float  $pratDays
    ): array {
        $sookshmas = [];
        $startIdx  = array_search($pratLord, self::$lords);
        $currentJD = $pratStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx      = ($startIdx + $i) % 9;
            $sookLord = self::$lords[$idx];
            $sookDays = ($pratDays * self::$years[$sookLord]) / 120.0;
            $endJD    = $currentJD + $sookDays;

            $sookshmas[] = [
                'lord'        => $sookLord,
                'abbr'        => self::$abbr[$sookLord],
                'days'        => round($sookDays, 4),
                'durationStr' => self::daysToYMD($sookDays),
                'startJD'     => $currentJD,
                'endJD'       => $endJD,
                'startDate'   => self::jdToDate($currentJD),
                'endDate'     => self::jdToDate($endJD),
                'color'       => self::$colors[$sookLord],
            ];

            $currentJD = $endJD;
        }

        return $sookshmas;
    }

    // ════════════════════════════════════════════════════════════
    //  FIND CURRENT DASHA (up to Sookshma level)
    // ════════════════════════════════════════════════════════════

    public static function findCurrent(array $mahadashas, float $currentJD): array
    {
        $result = [
            'maha'       => null,
            'antar'      => null,
            'pratyantar' => null,
            'sookshma'   => null,
            'elapsed'    => null,
        ];

        foreach ($mahadashas as $maha) {
            if ($currentJD < $maha['startJD'] || $currentJD >= $maha['endJD']) continue;
            $result['maha'] = $maha;
            $elapsed = ($currentJD - $maha['startJD']) / ($maha['endJD'] - $maha['startJD']) * 100;
            $result['elapsed'] = round($elapsed, 2);

            foreach ($maha['antars'] as $antar) {
                if ($currentJD < $antar['startJD'] || $currentJD >= $antar['endJD']) continue;
                $result['antar'] = $antar;

                foreach ($antar['pratyantars'] as $prat) {
                    if ($currentJD < $prat['startJD'] || $currentJD >= $prat['endJD']) continue;
                    $result['pratyantar'] = $prat;

                    foreach ($prat['sookshmas'] as $sook) {
                        if ($currentJD < $sook['startJD'] || $currentJD >= $sook['endJD']) continue;
                        $result['sookshma'] = $sook;
                        break;
                    }
                    break;
                }
                break;
            }
            break;
        }

        return $result;
    }

    // ════════════════════════════════════════════════════════════
    //  PLANET DETAILS TAB DATA
    // ════════════════════════════════════════════════════════════

    private static function prepareDetailsData(string $lord, array $c): array
    {
        self::loadFromDB();

        $d = self::$lordDetails[$lord] ?? null;
        if (!$d) return ['empty' => true, 'style' => $c];
        return [
            'empty'  => false,
            'style'  => $c,
            'chips'  => [
                ['label' => 'Nature',       'value' => $d['nature']],
                ['label' => 'Rules',        'value' => $d['rules']],
                ['label' => 'Exaltation',   'value' => $d['exalt']],
                ['label' => 'Debilitation', 'value' => $d['debil']],
                ['label' => 'Gemstone',     'value' => $d['gemstone']],
                ['label' => 'Metal',        'value' => $d['metal']],
                ['label' => 'Best Day',     'value' => $d['day']],
                ['label' => 'Number',       'value' => (string)$d['num']],
            ],
            'signif' => $d['signif'],
            'themes' => $d['themes'],
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  VIEW DATA PREPARATION
    // ════════════════════════════════════════════════════════════

    public static function prepareForView(array $data, bool $showAll = false): array
    {
        self::loadFromDB();

        $today = self::toJD((int)date('Y'), (int)date('n'), (int)date('j'), 12.0);
        $uid   = substr(md5(uniqid('', true)), 0, 6);

        $sc    = fn(string $lord): array  => self::SOFT[$lord] ?? ['bg'=>'#f4f4f4','border'=>'#d0d0d0','text'=>'#1a2535','accent'=>'#444','light'=>'#f0f0f0'];
        $sym   = fn(string $lord): string => self::$syms[$lord]      ?? '◈';
        $fname = fn(string $lord): string => self::$fullNames[$lord]  ?? ucfirst($lord);

        // Birth balance card
        $bl = $data['remainingStr'];
        $bc = $sc($data['birthLord']);
        $birthCard = [
            'style'      => $bc,
            'nakSym'     => $sym($data['birthLord']),
            'nakName'    => $data['nakName'],
            'nakProg'    => $data['nakProg'],
            'birthLord'  => $data['birthLord'],
            'birthYears' => self::$years[$data['birthLord']],
            'balance'    => $bl['y'].'y '.$bl['m'].'m '.$bl['d'].'d',
        ];

        // Currently active dasha summary
        $cur = $data['current'];
        $currentCard = null;
        if ($cur['maha']) {
            $mc = $sc($cur['maha']['lord']);
            $periods = [];
            foreach ([
                ['Mahadasha',        $cur['maha'],       'Major period (~'.self::$years[$cur['maha']['lord']].' yrs)'],
                ['Antardasha',       $cur['antar'],       'Sub-period (Bhukti)'],
                ['Pratyantar Dasha', $cur['pratyantar'],  'Sub-sub period'],
                ['Sookshma Dasha',   $cur['sookshma'],    '4th level period'],
            ] as [$lbl, $period, $hint]) {
                if (!$period) continue;
                $pc = $sc($period['lord']);
                $periods[] = [
                    'label'       => $lbl,
                    'hint'        => $hint,
                    'style'       => $pc,
                    'lord'        => $period['lord'],
                    'lordUC'      => strtoupper($period['lord']),
                    'lordFull'    => $fname($period['lord']),
                    'sym'         => $sym($period['lord']),
                    'startDate'   => $period['startDate'],
                    'endDate'     => $period['endDate'],
                    'durationStr' => $period['durationStr'],
                ];
            }

            $antarElapsed = null;
            $antarStyle   = null;
            $antarLordUC  = null;
            $antarEndDate = null;
            if ($cur['antar'] && $cur['elapsed'] !== null) {
                $antarElapsed = round(($today - $cur['antar']['startJD']) / ($cur['antar']['endJD'] - $cur['antar']['startJD']) * 100, 1);
                $antarStyle   = $sc($cur['antar']['lord']);
                $antarLordUC  = strtoupper($cur['antar']['lord']);
                $antarEndDate = $cur['antar']['endDate'];
            }

            $currentCard = [
                'mahaStyle'    => $mc,
                'mahaLordUC'   => strtoupper($cur['maha']['lord']),
                'periods'      => $periods,
                'elapsed'      => $cur['elapsed'],
                'remaining'    => $cur['elapsed'] !== null ? number_format(100 - $cur['elapsed'], 1) : null,
                'mahaEndDate'  => $cur['maha']['endDate'],
                'antarElapsed' => $antarElapsed,
                'antarStyle'   => $antarStyle,
                'antarLordUC'  => $antarLordUC,
                'antarEndDate' => $antarEndDate,
            ];
        }

        // All 9 Mahadashas with nested antars / pratyantars / sookshmas
        $mahaRows = [];
        foreach ($data['mahadashas'] as $mIdx => $maha) {
            $isPast    = $maha['endJD']   < $today;
            $isCurrent = $maha['startJD'] <= $today && $maha['endJD'] > $today;
            $rc        = $sc($maha['lord']);
            $htmlId    = $uid.'_maha_'.$mIdx;

            $antarRows = [];
            foreach ($maha['antars'] as $aIdx => $antar) {
                $isCA    = $antar['startJD'] <= $today && $antar['endJD'] > $today;
                $isPastA = $antar['endJD'] < $today;
                $ac      = $sc($antar['lord']);
                $antarId = $uid.'_antar_'.$mIdx.'_'.$aIdx;

                $prRows = [];
                foreach ($antar['pratyantars'] as $pIdx => $prat) {
                    $isCP    = $prat['startJD'] <= $today && $prat['endJD'] > $today;
                    $isPastP = $prat['endJD'] < $today;
                    $pc      = $sc($prat['lord']);
                    $pratId  = $uid.'_prat_'.$mIdx.'_'.$aIdx.'_'.$pIdx;

                    $sookRows = [];
                    if ($isCP) {
                        foreach ($prat['sookshmas'] as $sook) {
                            $isCS    = $sook['startJD'] <= $today && $sook['endJD'] > $today;
                            $isPastS = $sook['endJD'] < $today;
                            $sc2     = $sc($sook['lord']);
                            $sookRows[] = [
                                'lord'        => $sook['lord'],
                                'abbr'        => $sook['abbr'],
                                'sym'         => $sym($sook['lord']),
                                'style'       => $sc2,
                                'durationStr' => $sook['durationStr'],
                                'isCurrent'   => $isCS,
                                'isPast'      => $isPastS,
                            ];
                        }
                    }

                    $prRows[] = [
                        'lord'        => $prat['lord'],
                        'lordUC'      => strtoupper($prat['lord']),
                        'lordFull'    => $fname($prat['lord']),
                        'sym'         => $sym($prat['lord']),
                        'style'       => $pc,
                        'startDate'   => $prat['startDate'],
                        'endDate'     => $prat['endDate'],
                        'durationStr' => $prat['durationStr'],
                        'isCurrent'   => $isCP,
                        'isPast'      => $isPastP,
                        'pratId'      => $pratId,
                        'detailData'  => self::prepareDetailsData($prat['lord'], $pc),
                        'sookRows'    => $sookRows,
                    ];
                }

                $antarRows[] = [
                    'lord'        => $antar['lord'],
                    'lordUC'      => strtoupper($antar['lord']),
                    'lordFull'    => $fname($antar['lord']),
                    'sym'         => $sym($antar['lord']),
                    'style'       => $ac,
                    'startDate'   => $antar['startDate'],
                    'endDate'     => $antar['endDate'],
                    'durationStr' => $antar['durationStr'],
                    'isCurrent'   => $isCA,
                    'isPast'      => $isPastA,
                    'antarId'     => $antarId,
                    'detailData'  => self::prepareDetailsData($antar['lord'], $ac),
                    'prRows'      => $prRows,
                ];
            }

            $mahaRows[] = [
                'lord'        => $maha['lord'],
                'lordUC'      => strtoupper($maha['lord']),
                'lordFull'    => $fname($maha['lord']),
                'abbr'        => $maha['abbr'],
                'sym'         => $sym($maha['lord']),
                'style'       => $rc,
                'years'       => self::$years[$maha['lord']],
                'startDate'   => $maha['startDate'],
                'endDate'     => $maha['endDate'],
                'durationStr' => $maha['durationStr'],
                'isCurrent'   => $isCurrent,
                'isPast'      => $isPast,
                'opacity'     => $isPast ? '.60' : '1',
                'htmlId'      => $htmlId,
                'detailData'  => self::prepareDetailsData($maha['lord'], $rc),
                'antarRows'   => $antarRows,
            ];
        }

        // Legend reference cards
        $legendCards = [];
        foreach (self::$lords as $lord) {
            $lc = $sc($lord);
            $legendCards[] = [
                'lord'   => $lord,
                'lordUC' => strtoupper($lord),
                'sym'    => $sym($lord),
                'style'  => $lc,
                'years'  => self::$years[$lord],
            ];
        }

        return [
            'uid'         => $uid,
            'birthCard'   => $birthCard,
            'currentCard' => $currentCard,
            'mahaRows'    => $mahaRows,
            'legendCards' => $legendCards,
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  UTILITIES
    // ════════════════════════════════════════════════════════════

    public static function toJD(int $y, int $m, int $d, float $h = 12.0): float
    {
        if ($m <= 2) { $y--; $m += 12; }
        $A = (int)floor($y / 100);
        $B = 2 - $A + (int)floor($A / 4);
        return floor(365.25 * ($y + 4716))
             + floor(30.6001 * ($m + 1))
             + $d + $h / 24.0 + $B - 1524.5;
    }

    public static function jdToDate(float $jd): string
    {
        $jd   += 0.5;
        $Z     = (int)$jd;
        $F     = $jd - $Z;
        $A     = ($Z < 2299161) ? $Z : (function() use ($Z) {
            $alpha = (int)(($Z - 1867216.25) / 36524.25);
            return $Z + 1 + $alpha - (int)($alpha / 4);
        })();
        $B  = $A + 1524;
        $C  = (int)(($B - 122.1) / 365.25);
        $D  = (int)(365.25 * $C);
        $E  = (int)(($B - $D) / 30.6001);
        $dy = $B - $D - (int)(30.6001 * $E);
        $mo = $E < 14 ? $E - 1 : $E - 13;
        $yr = $mo > 2  ? $C - 4716 : $C - 4715;
        return sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
    }

    public static function daysToYMD(float $days): array
    {
        $y   = (int)floor($days / 365.25);
        $rem = $days - $y * 365.25;
        $m   = (int)floor($rem / 30.4375);
        $d   = (int)round($rem - $m * 30.4375);
        if ($d >= 30) { $m++; $d = 0; }
        if ($m >= 12) { $y++; $m = 0; }
        return ['y' => $y, 'm' => $m, 'd' => $d];
    }
}
