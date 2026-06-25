<?php

namespace App\Services\Planetary;

use Illuminate\Support\Facades\DB;

/**
 * AstroCalculator — PHP port of astro.js
 *
 * Engine : Jean Meeus "Astronomical Algorithms" (2nd Ed.)
 * Planets: Sun, Moon, Mercury, Venus, Mars, Jupiter, Saturn,
 *          Rahu (North Node), Ketu (South Node)
 * Added  : Ascendant (Lagna), Descendant, MC, IC
 *          Tithi, Karana, Vara, Nakshatra, Yoga (Panchanga)
 *          Vimshottari Dasha balance
 *          Sunrise / Sunset
 *          Monthly Masa Panchanga with Samapti Kaal
 *          Chandra Rashi Pravesh (Moon sign changes)
 */
class AstroCalculator
{
    // ── Constants ──────────────────────────────────────────────────
    private const DEG = M_PI / 180.0;

    // ── Panchanga data tables — DB-backed, lazy-loaded ────────────
    // Colors are UI-only; kept here since weekdays.color_hex was dropped.
    private const VARA_COLORS = ['#d4760a','#1d4e6f','#b83020','#2e7a6e','#7a5a10','#8e3a7a','#4a4060'];

    private static ?array $TITHIS        = null;
    private static ?array $KARANA_CYCLE  = null;
    private static ?array $KARANA_FIXED  = null;
    private static ?array $VARAS         = null;
    private static ?array $YOGAS         = null;
    private static ?array $NAKSHATRAS    = null;
    private static ?array $EKADASHI_NAMES = null;

    private static function ensurePanchangaData(): void
    {
        if (self::$TITHIS !== null) {
            return;
        }

        self::$TITHIS = DB::table('tithis')
            ->orderBy('id')
            ->get(['name as n', 'paksha', 'tithi_number as num',
                   'ruling_lord as lord', 'nature', 'presiding_deity as deity'])
            ->map(fn($r) => (array) $r)
            ->values()->toArray();

        self::$NAKSHATRAS = DB::table('nakshatras as nak')
            ->join('planets as p', 'nak.lord_planet_id', '=', 'p.id')
            ->orderBy('nak.id')
            ->get(['nak.name as n', 'nak.deity as d', 'p.name as l',
                   'nak.gana', 'nak.yoni', 'nak.nadi', 'nak.tattva',
                   'nak.muhurta_quality as quality'])
            ->map(fn($r) => (array) $r)
            ->values()->toArray();

        self::$YOGAS = DB::table('yogas')
            ->orderBy('id')
            ->get(['name as n', 'nature', 'ruling_lord as lord',
                   'presiding_deity as deity', 'classification as cls', 'description as desc'])
            ->map(fn($r) => (array) $r)
            ->values()->toArray();

        $karanas = DB::table('karanas')
            ->orderBy('id')
            ->get(['name as n', 'ruling_lord as lord', 'nature',
                   'karana_type as type', 'presiding_deity as deity',
                   'favourable_activities as favour'])
            ->map(function ($r) {
                $a        = (array) $r;
                $a['cls'] = $r->type === 'Chara' ? 'Movable (Chara)' : 'Fixed (Sthira)';
                return $a;
            })
            ->values()->toArray();
        self::$KARANA_CYCLE = array_slice($karanas, 0, 7);
        self::$KARANA_FIXED = array_slice($karanas, 7, 4);

        self::$VARAS = DB::table('weekdays as w')
            ->join('planets as p', 'w.lord_planet_id', '=', 'p.id')
            ->orderBy('w.dow_index')
            ->get(['w.dow_index', 'w.name as n', 'w.english_name as en',
                   'p.name as lord', 'w.symbol as sym', 'w.nature',
                   'w.presiding_deity as deity', 'w.deity_note as deityNote',
                   'w.classification', 'w.classification_note as classNote',
                   'w.auspicious_activities as auspicious', 'w.info_text as info'])
            ->map(function ($r) {
                $a             = (array) $r;
                $a['color']    = self::VARA_COLORS[$r->dow_index];
                $a['horaLord'] = $r->lord;
                unset($a['dow_index']);
                return $a;
            })
            ->values()->toArray();

        $zodiacRows = DB::table('zodiac_signs as z')
            ->join('planets as p', 'z.lord_planet_id', '=', 'p.id')
            ->orderBy('z.id')
            ->get(['z.name', 'z.english_name', 'z.symbol', 'p.name as lord']);
        self::$VEDIC_SIGNS   = $zodiacRows->pluck('name')->toArray();
        self::$WESTERN_SIGNS = $zodiacRows->map(fn($r) => ['n' => $r->english_name, 's' => $r->symbol])->toArray();
        self::$SIGN_LORDS    = $zodiacRows->pluck('lord')->toArray();

        $planetRows = DB::table('planets')
            ->orderBy('vimshottari_order')
            ->get(['name', 'vimshottari_dasha_years']);
        self::$DASHA_LORDS = $planetRows->pluck('name')->toArray();
        self::$DASHA_YEARS = $planetRows->pluck('vimshottari_dasha_years')->map(fn($v) => (int) $v)->toArray();
    }

    private static ?array $WESTERN_SIGNS = null;
    private static ?array $VEDIC_SIGNS   = null;
    private static ?array $SIGN_LORDS    = null;

    // Vimshottari Dasha
    private static ?array $DASHA_LORDS = null;
    private static ?array $DASHA_YEARS = null;

    // Keplerian orbital elements (Meeus Ch.32/33)
    private static array $ELEMENTS = [
        'mercury' => ['L0'=>252.250906,'L1'=>149474.0722491,'a'=>0.38709831,'e0'=>0.20563175,'e1'=> 0.000020406,'w0'=> 77.456119,'w1'=> 0.1588643,'Om0'=> 48.330893,'Om1'=>-0.1254229,'i0'=> 7.004986,'i1'=>-0.0059516],
        'venus'   => ['L0'=>181.979801,'L1'=> 58519.2130302,'a'=>0.72332982,'e0'=>0.00677188,'e1'=>-0.000047766,'w0'=>131.563707,'w1'=> 0.1212060,'Om0'=> 76.679920,'Om1'=>-0.2780080,'i0'=> 3.394662,'i1'=>-0.0008568],
        'mars'    => ['L0'=>355.433275,'L1'=> 19141.6964746,'a'=>1.52371243,'e0'=>0.09340062,'e1'=> 0.000090483,'w0'=>336.060234,'w1'=> 0.4438898,'Om0'=> 49.558093,'Om1'=>-0.2949846,'i0'=> 1.849726,'i1'=>-0.0006011],
        'jupiter' => ['L0'=> 34.351519,'L1'=>  3036.3027748,'a'=>5.20248019,'e0'=>0.04853590,'e1'=> 0.000016322,'w0'=> 14.331964,'w1'=> 0.2155209,'Om0'=>100.464441,'Om1'=> 0.1766828,'i0'=> 1.303270,'i1'=>-0.0054966],
        'saturn'  => ['L0'=> 50.077444,'L1'=>  1223.5110686,'a'=>9.54149883,'e0'=>0.05550825,'e1'=>-0.000346641,'w0'=> 93.056787,'w1'=> 0.5665496,'Om0'=>113.665524,'Om1'=>-0.2566649,'i0'=> 2.488878,'i1'=>-0.0037363],
    ];
    
    /**
     * Main entry point — full chart calculation.
     */  
    public static function calculate(
        int $yr, int $mo, int $dy,
        int $hr, int $mn,
        float $utcOff, float $lat, float $lon
    ): array {
        // Local → UTC with date rollover
        $utHr = ($hr + $mn / 60.0) - $utcOff;
        $baseTs = gmmktime(0, 0, 0, $mo, $dy, $yr);
        $utcTs  = $baseTs + (int)round($utHr * 3600);
        $adjYr  = (int)gmdate('Y', $utcTs);
        $adjMo  = (int)gmdate('n', $utcTs);
        $adjDy  = (int)gmdate('j', $utcTs);
        $adjUtHr= (int)gmdate('G', $utcTs)
                + (int)gmdate('i', $utcTs) / 60.0
                + (int)gmdate('s', $utcTs) / 3600.0;

        $jd   = self::julianDay($adjYr, $adjMo, $adjDy, $adjUtHr);
        $ayan = self::lahiriAyanamsa($jd);

        // Angles
        $angles   = self::computeAngles($jd, $lat, $lon);
        $ascTrop  = $angles['asc'];
        $ascSider = self::n360($ascTrop - $ayan);
        $ascSignIdx = (int)floor($ascSider / 30.0);

        // Whole-sign house map
        $houseSign = [];
        for ($h = 0; $h < 12; $h++) {
            $houseSign[$h] = ($ascSignIdx + $h) % 12;
        }

        // Planets
        $planets = [
            'sun'     => self::computePlanet($jd, $ayan, 'sun',     false),
            'moon'    => self::computePlanet($jd, $ayan, 'moon',    false),
            'mercury' => self::computePlanet($jd, $ayan, 'mercury', null),
            'venus'   => self::computePlanet($jd, $ayan, 'venus',   null),
            'mars'    => self::computePlanet($jd, $ayan, 'mars',    null),
            'jupiter' => self::computePlanet($jd, $ayan, 'jupiter', null),
            'saturn'  => self::computePlanet($jd, $ayan, 'saturn',  null),
            'rahu'    => self::computePlanet($jd, $ayan, 'rahu',    true),
            'ketu'    => self::computePlanet($jd, $ayan, 'ketu',    true),
        ];

        // Sunrise / Sunset
        $ss = self::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);

        // Tithi / Karana at input time, sunrise, sunset
        $tk     = self::computeTithiKarana($jd);
        $tkRise = null;
        $tkSet  = null;
        if (!$ss['polar'] && $ss['rise'] !== null) {
            $jdRise = self::julianDay($yr, $mo, $dy, $ss['rise'] - $utcOff);
            $tkRise = self::computeTithiKarana($jdRise);
        }
        if (!$ss['polar'] && $ss['set'] !== null) {
            $jdSet  = self::julianDay($yr, $mo, $dy, $ss['set'] - $utcOff);
            $tkSet  = self::computeTithiKarana($jdSet);
        }

        // Panchanga
        $pancha = self::computePanchanga($jd, $ayan, $yr, $mo, $dy, $utcOff);

        // Sun equatorial
        $sunEq = self::sunEquatorial($jd);

        // Dasha
        $moonSider = self::n360($planets['moon']['trop'] - $ayan);
        $dasha = self::getDashaMd($moonSider);

        return compact(
            'jd','ayan','angles','ascTrop','ascSider','ascSignIdx',
            'houseSign','planets','ss','tk','tkRise','tkSet',
            'pancha','sunEq','dasha'
        );
    }

    // ── Public accessors for static data ─────────────────────────

    public static function getNakshatras():  array { self::ensurePanchangaData(); return self::$NAKSHATRAS; }
    public static function getVedicSigns():  array { self::ensurePanchangaData(); return self::$VEDIC_SIGNS; }
    public static function getWesternSigns(): array { self::ensurePanchangaData(); return self::$WESTERN_SIGNS; }

    // ══════════════════════════════════════════════════════════════
    //  JULIAN DAY  (Meeus Ch.7)
    // ══════════════════════════════════════════════════════════════
    public static function julianDay(int $yr, int $mo, int $dy, float $utHr): float
    {
        if ($mo <= 2) { $yr--; $mo += 12; }
        $A = (int)floor($yr / 100);
        $B = 2 - $A + (int)floor($A / 4);
        return (int)floor(365.25 * ($yr + 4716))
             + (int)floor(30.6001 * ($mo + 1))
             + $dy + $utHr / 24.0 + $B - 1524.5;
    }

    // ══════════════════════════════════════════════════════════════
    //  LAHIRI AYANAMSA  (IAU 1976 precession, Rashtriya Panchang seed)
    // ══════════════════════════════════════════════════════════════
    public static function lahiriAyanamsa(float $jd): float
    {
        $T     = ($jd - 2451545.0) / 36525.0;
        $years = $T * 100.0;
        $precArcSec = 50.2910 * $years
                    + 0.022   * $T * $T
                    - 0.0003  * $T * $T * $T;
        return 23.853722 + $precArcSec / 3600.0;
    }

    // ══════════════════════════════════════════════════════════════
    //  MOON LONGITUDE  (Meeus Ch.47 full series, ±0.3°)
    // ══════════════════════════════════════════════════════════════
    public static function moonLongitude(float $jd): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $Lp = self::n360(218.3164477 + 481267.88123421*$T - 0.0015786*$T*$T + $T*$T*$T/538841.0);
        $D  = self::n360(297.8501921 + 445267.1114034 *$T - 0.0018819*$T*$T + $T*$T*$T/545868.0);
        $M  = self::n360(357.5291092 + 35999.0502909  *$T - 0.0001536*$T*$T);
        $Mp = self::n360(134.9633964 + 477198.8675055 *$T + 0.0087414*$T*$T + $T*$T*$T/69699.0);
        $F  = self::n360(93.2720950  + 483202.0175233 *$T - 0.0036539*$T*$T);
        $A1 = self::n360(119.75 + 131.849*$T);
        $A2 = self::n360(53.09 + 479264.290*$T);
        $E  = 1.0 - 0.002516*$T - 0.0000074*$T*$T;
        $E2 = $E * $E;

        static $terms = [
            [0,0,1,0,6288774],[2,0,-1,0,1274027],[2,0,0,0,658314],[0,0,2,0,213618],
            [0,1,0,0,-185116],[0,0,0,2,-114332],[2,0,-2,0,58793],[2,-1,-1,0,57066],
            [2,0,1,0,53322],[2,-1,0,0,45758],[0,1,-1,0,-40923],[1,0,0,0,-34720],
            [0,1,1,0,-30383],[2,0,0,-2,15327],[0,0,1,-2,10980],[4,0,-1,0,10675],
            [0,0,3,0,10034],[4,0,-2,0,8548],[2,1,-1,0,-7888],[2,1,0,0,-6766],
            [1,0,-1,0,-5163],[1,1,0,0,4987],[2,-1,1,0,4036],[2,0,2,0,3994],
            [4,0,0,0,3861],[2,0,-3,0,3665],[0,1,-2,0,-2689],[2,-1,-2,0,2390],
            [1,0,1,0,-2348],[2,-2,0,0,2236],[0,1,2,0,-2120],[0,2,0,0,-2069],
            [2,-2,-1,0,2048],[2,0,1,-2,-1773],[2,0,0,2,-1595],[4,-1,-1,0,1215],
            [0,0,2,2,-1110],[3,0,-1,0,-892],[2,1,1,0,-810],[4,-1,-2,0,759],
            [0,2,-1,0,-713],[2,2,-1,0,-700],[2,1,-2,0,691],[4,0,1,0,549],
            [0,0,4,0,537],[4,-1,0,0,520],[1,0,-2,0,-487],[0,0,2,-2,-381],
            [1,1,1,0,351],[3,0,-2,0,-340],[4,0,-3,0,330],[2,-1,2,0,327],
            [0,2,1,0,-323],[1,1,-1,0,299],[2,0,3,0,294],
        ];

        $Sl = 0.0;
        foreach ($terms as [$d,$m,$mp,$f,$c]) {
            $cf = (float)$c;
            if (abs($m) === 1) $cf *= $E;
            if (abs($m) === 2) $cf *= $E2;
            $Sl += $cf * sin(self::r($d*$D + $m*$M + $mp*$Mp + $f*$F));
        }
        $Sl += 3958.0*sin(self::r($A1))
             + 1962.0*sin(self::r($Lp - $F))
             + 318.0 *sin(self::r($A2));
        return self::n360($Lp + $Sl / 1e6);
    }

    // ══════════════════════════════════════════════════════════════
    //  RAHU (Mean ascending lunar node, always retrograde)
    // ══════════════════════════════════════════════════════════════
    public static function rahuLongitude(float $jd): float
{
    $T  = ($jd - 2451545.0) / 36525.0;
    $T2 = $T * $T;
    $T3 = $T2 * $T;

    // Mean node (Meeus Ch.47)
    $Om = self::n360(125.0445479 - 1934.1362608*$T + 0.0020754*$T2 + $T3/467441.0);

    // True node corrections (Meeus Ch.47, principal periodic terms)
    $D  = self::n360(297.8501921 + 445267.1114034*$T - 0.0018819*$T2 + $T3/545868.0);
    $M  = self::n360(357.5291092 + 35999.0502909 *$T - 0.0001536*$T2);
    $Mp = self::n360(134.9633964 + 477198.8675055*$T + 0.0087414*$T2 + $T3/69699.0);
    $F  = self::n360(93.2720950  + 483202.0175233*$T - 0.0036539*$T2);

    $corr =
        -1.4979 * sin(self::r(2.0*($D - $F)))
        -0.1500 * sin(self::r($M))
        -0.1226 * sin(self::r(2.0*$D))
        +0.1176 * sin(self::r(2.0*$F))
        -0.0801 * sin(self::r(2.0*($Mp - $F)));

    return self::n360($Om + $corr);
}

    // ══════════════════════════════════════════════════════════════
    //  SUN LONGITUDE  (Meeus Ch.27 apparent, ~0.01° accuracy)
    // ══════════════════════════════════════════════════════════════
    public static function sunLongitude(float $jd): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $T2 = $T * $T;
        $T3 = $T2 * $T;
        $L0 = self::n360(280.46646 + 36000.76983*$T + 0.0003032*$T2);
        $M  = self::n360(357.52911 + 35999.05029*$T - 0.0001537*$T2 - 0.00000048*$T3);
        $Mr = self::r($M);
        $C  = (1.9146 - 0.004817*$T - 0.000014*$T2) * sin($Mr)
            + (0.019993 - 0.000101*$T) * sin(2.0*$Mr)
            + 0.000290 * sin(3.0*$Mr)
            + 0.0000075 * sin(4.0*$Mr);
        $sunTrue = $L0 + $C;
        $omega   = self::n360(125.04452 - 1934.136261*$T + 0.0020708*$T2 + $T3/450000.0);
        $appLon  = $sunTrue - 0.00569 - 0.00478 * sin(self::r($omega));
        return self::n360($appLon);
    }

    public static function sunEquatorial(float $jd): array
    {
        $T   = ($jd - 2451545.0) / 36525.0;
        $eps = 23.439291111
             - 0.013004167  * $T
             - 0.0000001639 * $T * $T
             + 0.0000005036 * $T * $T * $T;
        $lon  = self::sunLongitude($jd);
        $lonR = self::r($lon);
        $epsR = self::r($eps);
        $ra   = atan2(cos($epsR)*sin($lonR), cos($lonR)) / self::DEG;
        $dec  = asin(sin($epsR)*sin($lonR)) / self::DEG;
        return ['ra' => self::n360($ra), 'dec' => $dec, 'lon' => $lon];
    }

    // ══════════════════════════════════════════════════════════════
    //  PLANETARY LONGITUDES  (Meeus Ch.32/33 Keplerian + perturbations)
    // ══════════════════════════════════════════════════════════════
    public static function planetLongitude(float $jd, string $planet): float
    {
        $T  = ($jd - 2451545.0) / 36525.0;
        $el = self::$ELEMENTS[$planet];

        $L   = self::n360($el['L0'] + $el['L1']*$T);
        $ww  = $el['w0']  + $el['w1']  * $T;
        $Om  = $el['Om0'] + $el['Om1'] * $T;
        $inc = self::r($el['i0'] + $el['i1'] * $T);
        $e   = $el['e0']  + $el['e1']  * $T;
        $a   = $el['a'];

        $M_deg = self::n360($L - $ww);
        $E     = self::solveKepler($M_deg, $e);
        $nu    = 2.0 * atan2(sqrt(1.0+$e)*sin($E/2.0), sqrt(1.0-$e)*cos($E/2.0));
        $rv    = $a * (1.0 - $e*cos($E));

        $omR = self::r($Om);
        $wR  = self::r($ww - $Om);
        $u   = $nu + $wR;
        $xh  = $rv * (cos($omR)*cos($u) - sin($omR)*sin($u)*cos($inc));
        $yh  = $rv * (sin($omR)*cos($u) + cos($omR)*sin($u)*cos($inc));

        // Earth heliocentric position
        $Me   = self::n360(357.52911 + 35999.05029*$T - 0.0001537*$T*$T);
        $MeR  = self::r($Me);
        $r_e  = 1.000001018 * (1.0
              - 0.01671123*cos($MeR)
              - 0.000139  *cos(2.0*$MeR)
              - 0.000014  *cos(3.0*$MeR)
              + 0.0000003 *cos(4.0*$MeR));
        $sLon = self::r(self::sunLongitude($jd));
        $xe   = $r_e * cos($sLon + M_PI);
        $ye   = $r_e * sin($sLon + M_PI);

        $lon = atan2($yh - $ye, $xh - $xe) / self::DEG;
        $lon = self::n360($lon);

        // Jupiter / Saturn mutual perturbations (Meeus Ch.33)
        if ($planet === 'jupiter' || $planet === 'saturn') {
            $Mj  = self::n360(20.9  + 0.071023 * ($jd - 2451545.0));
            $Ms  = self::n360(317.0 + 0.028441 * ($jd - 2451545.0));
            $MjR = self::r($Mj);
            $MsR = self::r($Ms);
            if ($planet === 'jupiter') {
                $lon += (  0.3314*sin(2.0*$MsR - 5.0*$MjR - self::r(67.6))
                         - 0.0390*sin($MsR - 2.0*$MjR + self::r(76.0))
                         + 0.0318*sin($MsR - 3.0*$MjR + self::r(13.0))
                         - 0.0185*sin($MsR  + self::r(100.0))
                         - 0.0143*sin(2.0*$MjR)) / 3600.0;
            } else {
                $lon += (- 0.8138*sin(2.0*$MsR - 4.0*$MjR - self::r(68.0))
                         + 0.2073*sin(2.0*$MsR - 5.0*$MjR - self::r(67.6))
                         - 0.0924*sin(2.0*$MsR - 3.0*$MjR)
                         + 0.0462*sin($MsR - self::r(56.0))
                         - 0.0402*sin($MsR + $MjR - self::r(120.0))) / 3600.0;
            }
            $lon = self::n360($lon);
        }
        return $lon;
    }

    // ══════════════════════════════════════════════════════════════
    //  ASCENDANT & ANGLES  (Meeus Ch.25 RAMC method)
    // ══════════════════════════════════════════════════════════════
    public static function computeAngles(float $jd, float $lat, float $lon): array
    {
        $T   = ($jd - 2451545.0) / 36525.0;
        $eps = 23.439291111
             - 0.013004167  * $T
             - 0.0000001639 * $T * $T
             + 0.0000005036 * $T * $T * $T;

        $gmst = self::n360(
            280.46061837
            + 360.98564736629 * ($jd - 2451545.0)
            + 0.000387933 * $T * $T
            - $T * $T * $T / 38710000.0
        );
        $lst   = self::n360($gmst + $lon);
        $ramc  = $lst;
        $raMC_r = self::r($ramc);
        $epsR   = self::r($eps);

        // MC
        $mc = atan2(sin($raMC_r), cos($raMC_r) * cos($epsR)) / self::DEG;
        $mc = self::n360($mc);

        // Ascendant
        $latR = self::r($lat);
        $num  = -cos($raMC_r);
        $den  = sin($epsR)*tan($latR) + cos($epsR)*sin($raMC_r);
        $asc  = atan2($num, $den) / self::DEG;
        $asc  = self::n360($asc);

        // Quadrant correction
        $expected = self::n360($ramc + 90.0);
        $diff = $asc - $expected;
        if ($diff >  180.0) $diff -= 360.0;
        if ($diff < -180.0) $diff += 360.0;
        if (abs($diff) > 90.0) $asc = self::n360($asc + 180.0);

        $desc = self::n360($asc + 180.0);
        $ic   = self::n360($mc  + 180.0);

        return compact('asc','desc','mc','ic','lst','eps');
    }

    // ══════════════════════════════════════════════════════════════
    //  TITHI & KARANA
    // ══════════════════════════════════════════════════════════════
    public static function computeTithiKarana(float $jd): array
    {
        self::ensurePanchangaData();
        $moonLon = self::moonLongitude($jd);
        $sunLon  = self::sunLongitude($jd);
        $elong   = self::n360($moonLon - $sunLon);

        $tithiIndex = (int)floor($elong / 12.0);
        $tithiProg  = fmod($elong, 12.0) / 12.0;
        $tithi      = self::$TITHIS[$tithiIndex];

        $karanaSlotRaw = (int)floor($elong / 6.0);  // 0..59
        $karanaProg    = fmod($elong, 6.0) / 6.0;

        if ($karanaSlotRaw === 0) {
            $karana = self::$KARANA_FIXED[0];           // Kimstughna
        } elseif ($karanaSlotRaw <= 56) {
            $karana = self::$KARANA_CYCLE[($karanaSlotRaw - 1) % 7];
        } elseif ($karanaSlotRaw === 57) {
            $karana = self::$KARANA_FIXED[1];           // Shakuni
        } elseif ($karanaSlotRaw === 58) {
            $karana = self::$KARANA_FIXED[2];           // Chatushpada
        } else {
            $karana = self::$KARANA_FIXED[3];           // Naga
        }

        $karanaSlot = $karanaSlotRaw + 1;   // 1-based display
        $tithiHalf  = ($karanaSlotRaw % 2 === 0) ? 'First Half' : 'Second Half';

        return compact(
            'elong','tithi','tithiIndex','tithiProg',
            'karana','karanaSlot','karanaProg','tithiHalf',
            'moonLon','sunLon'
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  PANCHANGA — Vara, Nakshatra, Yoga
    // ══════════════════════════════════════════════════════════════
    public static function computePanchanga(
        float $jd, float $ayan,
        int $yr, int $mo, int $dy, float $utcOff
    ): array {
        self::ensurePanchangaData();
        // Vara: weekday at local noon
        $varaIdx = ((int)floor($jd + $utcOff/24.0 + 1.5)) % 7;
        $vara    = self::$VARAS[$varaIdx];

        // Moon nakshatra (sidereal)
        $moonSider = self::n360(self::moonLongitude($jd) - $ayan);
        $nakSz     = 360.0 / 27.0;
        $nakIdx    = (int)floor($moonSider / $nakSz);
        $nakProg   = fmod($moonSider, $nakSz) / $nakSz;
        $nakPada   = (int)floor($nakProg * 4.0) + 1;
        $moonNak   = self::$NAKSHATRAS[$nakIdx];

        // Yoga
        $sunSider = self::n360(self::sunLongitude($jd) - $ayan);
        $yogaSum  = self::n360($sunSider + $moonSider);
        $yogaIdx  = (int)floor($yogaSum / $nakSz) % 27;
        $yogaProg = fmod($yogaSum, $nakSz) / $nakSz;
        $yoga     = self::$YOGAS[$yogaIdx];

        return compact(
            'vara','varaIdx',
            'moonNak','nakIdx','nakProg','nakPada',
            'yoga','yogaIdx','yogaProg',
            'moonSider','sunSider','yogaSum'
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  VIMSHOTTARI DASHA BALANCE
    // ══════════════════════════════════════════════════════════════
    public static function getDashaMd(float $moonSiderLon): array
    {
        self::ensurePanchangaData();
        $nakSz    = 360.0 / 27.0;
        $nakIdx   = (int)floor($moonSiderLon / $nakSz);
        $nakProg  = fmod($moonSiderLon, $nakSz) / $nakSz;
        $lords    = self::$DASHA_LORDS;
        $years    = self::$DASHA_YEARS;
        // Each nakshatra maps to one of 9 lords cyclically
        $lordIdx  = $nakIdx % 9;
        $lord     = $lords[$lordIdx];
        $lordYrs  = $years[$lordIdx];
        $elapsed  = $nakProg * $lordYrs;
        $remaining= $lordYrs - $elapsed;
        $yrs      = (int)floor($remaining);
        $mosFrac  = ($remaining - $yrs) * 12.0;
        $mos      = (int)floor($mosFrac);
        $days     = (int)round(($mosFrac - $mos) * 30.0);
        return compact('lord','remaining','yrs','mos','days','lordYrs');
    }

    // ══════════════════════════════════════════════════════════════
    //  SUNRISE / SUNSET  (Meeus Ch.15)
    // ══════════════════════════════════════════════════════════════
    public static function sunriseSunset(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff
    ): array {
        $jd  = self::julianDay($yr, $mo, $dy, 12.0 - $utcOff);
        $eq  = self::sunEquatorial($jd);
        $dec = $eq['dec'];

        $latR = self::r($lat);
        $decR = self::r($dec);
        $cosH = (cos(self::r(90.833)) - sin($latR)*sin($decR))
              / (cos($latR)*cos($decR));

        if ($cosH >  1.0) return ['rise'=>null,'set'=>null,'polar'=>'no_rise','dayLength'=>0.0];
        if ($cosH < -1.0) return ['rise'=>null,'set'=>null,'polar'=>'no_set', 'dayLength'=>24.0];

        $H  = acos($cosH) / self::DEG;
        $T  = ($jd - 2451545.0) / 36525.0;
        $L0 = self::n360(280.46646 + 36000.76983*$T);
        $M  = self::n360(357.52911 + 35999.05029*$T);
        $eps = 23.439291111 - 0.013004167*$T;
        $y   = tan(self::r($eps/2.0)) ** 2;
        $eot = 4.0 * ($y*sin(self::r(2.0*$L0))
                    - 2.0*0.016708634*sin(self::r($M))
                    + 4.0*0.016708634*$y*sin(self::r($M))*cos(self::r(2.0*$L0))
                    - 0.5*$y*$y*sin(self::r(4.0*$L0))
                    - 1.25*0.016708634*0.016708634*sin(self::r(2.0*$M)));

        $lngHour = $lon / 15.0;
        return [
            'rise'      => self::normalHour(12.0 - $H/15.0 - $lngHour - $eot/60.0 + $utcOff),
            'set'       => self::normalHour(12.0 + $H/15.0 - $lngHour - $eot/60.0 + $utcOff),
            'polar'     => null,
            'dayLength' => $H * 2.0 / 15.0,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  MONTHLY MASA PANCHANGA
    // ══════════════════════════════════════════════════════════════
    public static function buildMasaData(
        int $year, int $vedMon, float $lat, float $lon, float $utcOff
    ): array {
        self::ensurePanchangaData();
        // Vedic month → civil month map
        $civilMonthMap = [3,4,5,6,7,8,9,10,11,12,1,2];
        $civilMon = $civilMonthMap[$vedMon - 1];
        $civYear  = ($civilMon <= 2) ? $year + 1 : $year;
        $daysInMonth = (int)(new \DateTime("$civYear-$civilMon-01"))->format('t');

        $VARA_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        $MASA_NAMES = ['Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
                       'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna'];

        // Moon sign changes for the month
        $moonChanges = self::findMoonSignChanges($civYear, $civilMon, $lat, $lon, $utcOff);
        $moonChangesByDay = [];
        foreach ($moonChanges as $ch) {
            $moonChangesByDay[$ch['day']][] = $ch;
        }

        $rows       = [];
        $prevTithi  = null;
        $prevPaksha = null;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ss    = self::sunriseSunset($civYear, $civilMon, $d, $lat, $lon, $utcOff);
            $riseHr= (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : null;
            $setHr = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : null;

            $jdRef = ($riseHr !== null)
                   ? self::julianDay($civYear, $civilMon, $d, $riseHr - $utcOff)
                   : self::julianDay($civYear, $civilMon, $d, 12.0    - $utcOff);

            $ayan   = self::lahiriAyanamsa($jdRef);
            $tk     = self::computeTithiKarana($jdRef);
            $pancha = self::computePanchanga($jdRef, $ayan, $civYear, $civilMon, $d, $utcOff);

            // Samapti Kaal
            $tithiEnd  = self::findNextCrossing($jdRef, $utcOff, 12.0,        [$civYear,$civilMon,$d,'elong'],   2.0);
            $karanaEnd = self::findNextCrossing($jdRef, $utcOff, 6.0,         [$civYear,$civilMon,$d,'elong'],   2.0);
            $nakEnd    = self::findNextCrossing($jdRef, $utcOff, 360.0/27.0,  [$civYear,$civilMon,$d,'nakSider'],2.0);
            $yogaEnd   = self::findNextCrossing($jdRef, $utcOff, 360.0/27.0,  [$civYear,$civilMon,$d,'yogaSum'], 2.0);

            $dow = (int)(new \DateTime("$civYear-$civilMon-$d"))->format('w');

            $isAmavasya = ($tk['tithi']['paksha'] === 'Krishna' && $tk['tithi']['num'] === 15);
            $isPurnima  = ($tk['tithi']['paksha'] === 'Shukla'  && $tk['tithi']['num'] === 15);
            $isEkadashi = ($tk['tithi']['num'] === 11);
            // Kshaya Ekadashi: tithi 11 perished between yesterday's and today's sunrise.
            // Detected when yesterday was Dashami (10) and today is Dvadashi (12), same paksha.
            if (!$isEkadashi
                && $tk['tithi']['num'] === 12
                && $prevTithi  === 10
                && $prevPaksha === $tk['tithi']['paksha']
            ) {
                $isEkadashi = true;
            }

            $riseStr = $riseHr !== null ? substr(self::decToHMS($riseHr), 0, 5) : '—';
            $setStr  = $setHr  !== null ? substr(self::decToHMS($setHr),  0, 5) : '—';

            // Rashi Pravesh
            $rashiPravesh = '';
            if (!empty($moonChangesByDay[$d])) {
                $parts = [];
                foreach ($moonChangesByDay[$d] as $ch) {
                    $parts[] = self::$VEDIC_SIGNS[$ch['sign']] . ' ' . $ch['time'];
                }
                $rashiPravesh = implode(', ', $parts);
            }

            $rows[] = [
                'day'           => $d,
                'dow'           => $dow,
                'varaShort'     => $VARA_SHORT[$dow],
                'paksha'        => $tk['tithi']['paksha'],
                'tithiName'     => $tk['tithi']['n'],
                'tithiNum'      => $tk['tithi']['num'],
                'tithiSamapti'  => self::fmtSamaptiLocal($tithiEnd,  $riseHr),
                'karanaName'    => $tk['karana']['n'],
                'karanaSamapti' => self::fmtSamaptiLocal($karanaEnd, $riseHr),
                'nakName'       => $pancha['moonNak']['n'],
                'nakSamapti'    => self::fmtSamaptiLocal($nakEnd,    $riseHr),
                'yogaName'      => $pancha['yoga']['n'],
                'yogaNature'    => $pancha['yoga']['nature'],
                'yogaSamapti'   => self::fmtSamaptiLocal($yogaEnd,   $riseHr),
                'riseStr'       => $riseStr,
                'setStr'        => $setStr,
                'rashiPravesh'  => $rashiPravesh,
                'isAmavasya'    => $isAmavasya,
                'isPurnima'     => $isPurnima,
                'isEkadashi'    => $isEkadashi,
            ];

            $prevTithi  = $tk['tithi']['num'];
            $prevPaksha = $tk['tithi']['paksha'];
        }

        $shuklaCount  = count(array_filter($rows, fn($r) => $r['paksha'] === 'Shukla'));
        $krishnaCount = count($rows) - $shuklaCount;

        return [
            'rows'          => $rows,
            'monthName'     => $MASA_NAMES[$vedMon - 1] . ' ' . $year,
            'shuklaCount'   => $shuklaCount,
            'krishnaCount'  => $krishnaCount,
        ];
    }

    /**
     * Precise JD of the new moon (Sun–Moon conjunction) that began the lunation
     * containing $jdRef, whose Moon–Sun elongation is $elong (0–360°).
     * Newton refinement on the true elongation (≈12.19°/day relative motion)
     * removes the ~1-day error of a linear estimate near a Sankranti boundary.
     */
    public static function newMoonBefore(float $jdRef, float $elong): float
    {
        $nm = $jdRef - $elong / 12.19;
        for ($k = 0; $k < 5; $k++) {
            $e = self::n360(self::moonLongitude($nm) - self::sunLongitude($nm));
            if ($e > 180.0) $e -= 360.0;
            $nm -= $e / 12.19;
        }
        return $nm;
    }

    /** Precise JD of the next new moon AFTER $nm. */
    public static function newMoonAfter(float $nm): float
    {
        $t = $nm + 29.53;
        for ($k = 0; $k < 6; $k++) {
            $e = self::n360(self::moonLongitude($t) - self::sunLongitude($t));
            if ($e > 180.0) $e -= 360.0;
            $t -= $e / 12.19;
        }
        return $t;
    }

    /**
     * Purnimanta (North-Indian) lunar-month index (0 = Chaitra) for a date.
     * Month is named from the Sun's sidereal sign at the lunation's new moon;
     * Krishna paksha takes the next month's name (Purnimanta convention).
     */
    public static function purnimantaMasaIdx(float $jdRef, float $elong, string $paksha): int
    {
        return self::lunarMonthInfo($jdRef, $elong, $paksha)['masaIdx'];
    }

    /**
     * Full lunar-month info: Purnimanta month index + Adhik Maas flag.
     * A lunation (new moon → new moon) with NO solar Sankranti inside it is an
     * Adhika (leap) month — the Sun stays in the same sidereal sign across both
     * bounding new moons.
     */
    public static function lunarMonthInfo(float $jdRef, float $elong, string $paksha): array
    {
        $nm      = self::newMoonBefore($jdRef, $elong);
        $next    = self::newMoonAfter($nm);
        $signNm  = (int)floor(self::n360(self::sunLongitude($nm)   - self::lahiriAyanamsa($nm))   / 30.0);
        $signNxt = (int)floor(self::n360(self::sunLongitude($next) - self::lahiriAyanamsa($next)) / 30.0);
        $adhik   = ($signNm === $signNxt);                    // no Sankranti in this lunation
        $amanta  = ($signNm + 1) % 12;                        // Meena → Chaitra
        // Purnimanta convention: Krishna paksha takes the next month's name.
        // Exception: inside Adhik Maas both fortnights share the same (Amanta) name.
        $purnim  = (!$adhik && $paksha === 'Krishna') ? ($amanta + 1) % 12 : $amanta;
        return ['masaIdx' => $purnim, 'amantaIdx' => $amanta, 'adhik' => $adhik];
    }

     public static function getEkadashiYear(
    int $year, float $lat, float $lon, float $utcOff
): array {
    $ekadashis = [];
    $ekNames   = self::getEkadashiNames();

    // Scan every civil day of the year. An Ekadashi vrat day is one where the
    // Ekadashi tithi (11) prevails at sunrise. The lunar month is named in the
    // Purnimanta (North-Indian) convention from the Sun's sidereal sign at the
    // NEW MOON that began the lunation — so names never drift across a Sankranti.
    $start    = new \DateTime("$year-01-01");
    $lastKey  = '';
    $lastJd   = -1e9;

    for ($i = 0; $i < 366; $i++) {
        $dt = clone $start; $dt->modify("+{$i} days");
        if ((int)$dt->format('Y') !== $year) break;
        $y = (int)$dt->format('Y'); $m = (int)$dt->format('n'); $d = (int)$dt->format('j');

        $ss     = self::sunriseSunset($y, $m, $d, $lat, $lon, $utcOff);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $jdRef  = self::julianDay($y, $m, $d, $riseHr - $utcOff);

        $tk = self::computeTithiKarana($jdRef);
        if ($tk['tithi']['num'] !== 11) {
            // Kshaya Ekadashi: tithi 11 is never the prevailing tithi at sunrise —
            // it starts after today's sunrise and expires before tomorrow's sunrise.
            // Detected when today is Dashami (10) and tomorrow is Dvadashi (12).
            // Tradition: observe the vrat on the Dvadashi day (next sunrise).
            if ($tk['tithi']['num'] === 10) {
                $tkNext = self::computeTithiKarana($jdRef + 1.0);
                if ($tkNext['tithi']['num'] === 12
                    && $tkNext['tithi']['paksha'] === $tk['tithi']['paksha']
                ) {
                    $dt->modify('+1 day');
                    if ((int)$dt->format('Y') !== $year) continue;
                    $y   = (int)$dt->format('Y');
                    $m   = (int)$dt->format('n');
                    $d   = (int)$dt->format('j');
                    $ss  = self::sunriseSunset($y, $m, $d, $lat, $lon, $utcOff);
                    $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
                    $jdRef  = self::julianDay($y, $m, $d, $riseHr - $utcOff);
                    $tk     = self::computeTithiKarana($jdRef);
                    // $tk is now Dvadashi; paksha and elong are correct for month lookup
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }
        $paksha = $tk['tithi']['paksha'];

        // Purnimanta month + Adhik Maas (leap month) detection
        $minfo     = self::lunarMonthInfo($jdRef, $tk['elong'], $paksha);
        $purnimIdx = $minfo['masaIdx'];
        $isAdhik   = $minfo['adhik'];
        $vedMonIdx = $purnimIdx + 1;
        $key       = ($isAdhik ? 'A_' : '') . $paksha . '_' . $vedMonIdx;

        // Skip the second sunrise of a tithi-Vriddhi (Ekadashi on two sunrises)
        if ($key === $lastKey && ($jdRef - $lastJd) < 3.0) continue;
        $lastKey = $key; $lastJd = $jdRef;

        $ayan     = self::lahiriAyanamsa($jdRef);
        $pancha   = self::computePanchanga($jdRef, $ayan, $y, $m, $d, $utcOff);
        $tithiEnd = self::findNextCrossing($jdRef, $utcOff, 12.0, [$y, $m, $d, 'elong'], 2.0);

        if ($isAdhik) {
            // Adhik Maas (Purushottam Maas) Ekadashis
            $ekInfo = ($paksha === 'Shukla')
                ? ['name'=>'Padmini Ekadashi', 'nameHi'=>'Padmini Ekadashi',
                   'significance'=>'Shukla Ekadashi of Adhik Maas (Purushottam Maas) — exceptionally meritorious.',
                   'rituals'=>['Observe full fast','Worship Purushottam (Vishnu)','Night vigil & kirtan'],
                   'mantra'=>'Om Namo Bhagavate Vasudevaya','auspTime'=>'Sunrise to Dvadashi sunrise']
                : ['name'=>'Parama Ekadashi', 'nameHi'=>'Parama Ekadashi',
                   'significance'=>'Krishna Ekadashi of Adhik Maas (Purushottam Maas) — grants prosperity and liberation.',
                   'rituals'=>['Observe full fast','Worship Vishnu','Donate generously'],
                   'mantra'=>'Om Namo Bhagavate Vasudevaya','auspTime'=>'Sunrise to Dvadashi sunrise'];
        } else {
            $ekInfo = $ekNames[$paksha . '_' . $vedMonIdx] ?? ['name'=>'Ekadashi','nameHi'=>'Ekadashi'];
        }

        $ekadashis[] = [
            'name'        => $ekInfo['name'],
            'nameHi'      => $ekInfo['nameHi'],
            'paksha'      => $paksha,
            'vedMonth'    => ($isAdhik ? 'Adhika ' : '') . self::MASA_NAMES[$purnimIdx],
            'vedMonthNum' => $vedMonIdx,
            'date'        => sprintf('%04d-%02d-%02d', $y, $m, $d),
            'startTime'   => $ss['rise'] !== null ? self::decToHMS($ss['rise']) : '06:00',
            'endTime'     => $tithiEnd ? self::fmtSamaptiLocal($tithiEnd, $ss['rise']) : '—',
            'tithi'       => 11,
            'tithiLord'   => 'Vishnu',
            'yoga'        => $pancha['yoga']['n'],
            'nakshatra'   => $pancha['moonNak']['n'],
            'nakLord'     => $pancha['moonNak']['l'],
            'significance'=> $ekInfo['significance'] ?? '',
            'rituals'     => $ekInfo['rituals']       ?? [],
            'mantra'      => $ekInfo['mantra']        ?? 'Om Namo Bhagavate Vasudevaya',
            'auspTime'    => $ekInfo['auspTime']      ?? 'Sunrise to Dvadashi sunrise',
        ];
    }

    return $ekadashis;
}

public const MASA_NAMES = [
    'Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
    'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna',
];



private static function getEkadashiNames(): array {
    if (self::$EKADASHI_NAMES !== null) return self::$EKADASHI_NAMES;

    self::$EKADASHI_NAMES = [];
    $rows = DB::table('ekadashis')
        ->select('lookup_key', 'name', 'mantra', 'significance_text', 'rituals_list', 'auspicious_time_note')
        ->get();

    foreach ($rows as $row) {
        self::$EKADASHI_NAMES[$row->lookup_key] = [
            'name'        => $row->name,
            'nameHi'      => $row->name,
            'significance' => $row->significance_text ?? '',
            'rituals'     => is_array($row->rituals_list)
                               ? $row->rituals_list
                               : (json_decode($row->rituals_list ?? '[]', true) ?? []),
            'mantra'      => $row->mantra ?? 'Om Namo Bhagavate Vasudevaya',
            'auspTime'    => $row->auspicious_time_note ?? 'Sunrise to Dvadashi sunrise',
        ];
    }

    return self::$EKADASHI_NAMES;
}

    // ══════════════════════════════════════════════════════════════
    //  MOON SIGN CHANGE FINDER (Chandra Rashi Pravesh)
    // ══════════════════════════════════════════════════════════════
    private static function findMoonSignChanges(
        int $yr, int $mo, float $lat, float $lon, float $utcOff
    ): array {
        $daysInMonth = (int)(new \DateTime("$yr-$mo-01"))->format('t');
        $changes     = [];
        $prevSign    = -1;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            for ($stepH = 0; $stepH < 24; $stepH += 2) {
                $jd   = self::julianDay($yr, $mo, $d, $stepH - $utcOff);
                $ayan = self::lahiriAyanamsa($jd);
                $moonS= self::n360(self::moonLongitude($jd) - $ayan);
                $sign = (int)floor($moonS / 30.0);

                if ($prevSign !== -1 && $sign !== $prevSign) {
                    // Bisect to 1-minute precision
                    $lo = self::julianDay($yr, $mo, $d, $stepH - 2 - $utcOff);
                    $hi = $jd;
                    for ($i = 0; $i < 40; $i++) {
                        $mid     = ($lo + $hi) / 2.0;
                        $ayanMid = self::lahiriAyanamsa($mid);
                        $ms      = self::n360(self::moonLongitude($mid) - $ayanMid);
                        if ((int)floor($ms / 30.0) === $prevSign) $lo = $mid;
                        else                                        $hi = $mid;
                        if ($hi - $lo < 1.0/1440.0) break;
                    }
                    $jdPravesh = ($lo + $hi) / 2.0;
                    $localHr   = fmod(($jdPravesh + $utcOff/24.0 + 0.5), 1.0) * 24.0;
                    if ($localHr < 0) $localHr += 24.0;
                    $hh = str_pad((int)$localHr, 2, '0', STR_PAD_LEFT);
                    $mm = str_pad((int)(fmod($localHr, 1.0) * 60), 2, '0', STR_PAD_LEFT);
                    // Civil date of pravesh
                    $dateMs = ($jdPravesh - 2440587.5 + $utcOff/24.0) * 86400.0;
                    $dt     = new \DateTime('@' . (int)$dateMs);
                    $changes[] = [
                        'day'     => (int)$dt->format('j'),
                        'sign'    => $sign,
                        'time'    => "$hh:$mm",
                        'localHr' => $localHr,
                    ];
                }
                $prevSign = $sign;
            }
        }
        return $changes;
    }

    // ══════════════════════════════════════════════════════════════
    //  SAMAPTI KAAL — find next boundary crossing via bisection
    // ══════════════════════════════════════════════════════════════
    /**
     * @param float  $jd0     Reference JD (usually sunrise)
     * @param float  $utcOff  UTC offset in hours
     * @param float  $step    Boundary step in degrees (12, 6, or 360/27)
     * @param array  $fnSpec  [$yr,$mo,$dy,$type] — type: 'elong'|'nakSider'|'yogaSum'
     * @param float  $maxDays Search window
     */
    private static function findNextCrossing(
        float $jd0, float $utcOff, float $step, array $fnSpec, float $maxDays
    ): ?array {
        $v0     = self::evalValue($jd0, $fnSpec);
        $curIdx = (int)floor($v0 / $step);
        $target = ($curIdx + 1) * $step;

        // Unwrapped value accumulator
        $unwrap = function(float $jd) use ($jd0, $v0, $fnSpec): float {
            $numSteps = max(2, (int)round(($jd - $jd0) * 24.0));
            $prev  = $v0;
            $accum = 0.0;
            for ($i = 1; $i <= $numSteps; $i++) {
                $jdStep = $jd0 + ($jd - $jd0) * $i / $numSteps;
                $cur    = self::evalValue($jdStep, $fnSpec);
                $diff   = $cur - $prev;
                if ($diff < -180.0) $diff += 360.0;
                if ($diff >  180.0) $diff -= 360.0;
                $accum += $diff;
                $prev   = $cur;
            }
            return $v0 + $accum;
        };

        $hi    = $jd0 + $maxDays;
        $uvHi  = $unwrap($hi);
        if ($uvHi < $target) return null;

        $lo = $jd0;
        for ($i = 0; $i < 60; $i++) {
            $mid = ($lo + $hi) / 2.0;
            if ($unwrap($mid) < $target) $lo = $mid;
            else                         $hi = $mid;
            if ($hi - $lo < 1.0 / (24.0 * 3600.0)) break;
        }

        $jdCross = ($lo + $hi) / 2.0;
        $localHr = fmod($jdCross + $utcOff/24.0 + 0.5, 1.0) * 24.0;
        if ($localHr < 0) $localHr += 24.0;
        return ['jd' => $jdCross, 'localHr' => $localHr];
    }

    /** Evaluate the degree value for Samapti bisection. */
    private static function evalValue(float $jd, array $fnSpec): float
    {
        [,,,, $type] = array_pad($fnSpec, 5, null);
        $ayan = self::lahiriAyanamsa($jd);
        return match ($type) {
            'elong'    => self::n360(self::moonLongitude($jd) - self::sunLongitude($jd)),
            'nakSider' => self::n360(self::moonLongitude($jd) - $ayan),
            'yogaSum'  => self::n360(
                               self::n360(self::moonLongitude($jd) - $ayan) +
                               self::n360(self::sunLongitude($jd)  - $ayan)
                           ),
            default    => 0.0,
        };
    }

    // ══════════════════════════════════════════════════════════════
    //  COMPUTE ONE PLANET
    // ══════════════════════════════════════════════════════════════
    private static function computePlanet(
        float $jd, float $ayan, string $pid, ?bool $forceRetro
    ): array {
        self::ensurePanchangaData();
        $calcFn = fn($j) => match ($pid) {
            'sun'  => self::sunLongitude($j),
            'moon' => self::moonLongitude($j),
            'rahu' => self::rahuLongitude($j),
            'ketu' => self::n360(self::rahuLongitude($j) + 180.0),
            default=> self::planetLongitude($j, $pid),
        };

        $trop  = $calcFn($jd);

        // Speed (deg/day) — also used for retrograde detection
        $d1   = $calcFn($jd - 0.5);
        $d2   = $calcFn($jd + 0.5);
        $spd  = $d2 - $d1;
        if ($spd >  180.0) $spd -= 360.0;
        if ($spd < -180.0) $spd += 360.0;

        $sider = self::n360($trop - $ayan);
        $ws    = self::$WESTERN_SIGNS[(int)floor($trop / 30.0)];
        $vi    = (int)floor($sider / 30.0);
        $nakSz = 360.0 / 27.0;
        $nak   = self::$NAKSHATRAS[(int)floor($sider / $nakSz)];
        $np    = fmod($sider, $nakSz) / $nakSz;
        $pada  = (int)floor($np * 4.0) + 1;
        $retro = $forceRetro ?? ($spd < 0.0);

        return compact('trop','sider','ws','vi','nak','np','pada','retro','spd');
    }

    // ══════════════════════════════════════════════════════════════
    //  KEPLER SOLVER  (Newton–Raphson, converges to 1e-10 radians)
    // ══════════════════════════════════════════════════════════════
    private static function solveKepler(float $M_deg, float $e): float
    {
        $E = self::r($M_deg);
        for ($i = 0; $i < 50; $i++) {
            $dE = (self::r($M_deg) - $E + $e * sin($E)) / (1.0 - $e * cos($E));
            $E += $dE;
            if (abs($dE) < 1e-10) break;
        }
        return $E;
    }

    // ══════════════════════════════════════════════════════════════
    //  UTILITIES
    // ══════════════════════════════════════════════════════════════

    public static function findTithiEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 12.0, [0,0,0,0,'elong'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findNakshatraEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 360.0/27.0, [0,0,0,0,'nakSider'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findYogaEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 360.0/27.0, [0,0,0,0,'yogaSum'], 2.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function findKaranaEnd(float $jdStart, float $utcOff): string
    {
        $res = self::findNextCrossing($jdStart, $utcOff, 6.0, [0,0,0,0,'elong'], 1.0);
        if (!$res) return '—';
        $dt = new \DateTime('@' . (int)round(($res['jd'] - 2440587.5) * 86400));
        $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
        return $dt->format('Y-m-d H:i:s');
    }

    public static function calculateSankrantis(int $year, float $utcOff): array
    {
        $sankrantis = [];
        $names = [
            0   => 'Mesh', 30  => 'Vrishabh', 60  => 'Mithun', 90  => 'Kark',
            120 => 'Simha', 150 => 'Kanya', 180 => 'Tula', 210 => 'Vrischik',
            240 => 'Dhanu', 270 => 'Makar', 300 => 'Kumbh', 330 => 'Meen'
        ];

        $jd = self::julianDay($year - 1, 12, 15, 0.0);
        $endJd = self::julianDay($year + 1, 2, 1, 0.0);

        while ($jd < $endJd) {
            $s1 = self::n360(self::sunLongitude($jd) - self::lahiriAyanamsa($jd));
            $s2 = self::n360(self::sunLongitude($jd + 16.0) - self::lahiriAyanamsa($jd + 16.0));
            $idx1 = (int)floor($s1 / 30.0);
            $idx2 = (int)floor($s2 / 30.0);
            
            if ($idx1 !== $idx2) {
                $deg = $idx2 * 30;
                $lo = $jd;
                $hi = $jd + 16.0;
                for ($i = 0; $i < 50; $i++) {
                    $mid = ($lo + $hi) / 2.0;
                    $sMid = self::n360(self::sunLongitude($mid) - self::lahiriAyanamsa($mid));
                    $diff = self::n360($sMid - $deg);
                    if ($diff > 180) {
                        $lo = $mid;
                    } else {
                        $hi = $mid;
                    }
                    if ($hi - $lo < 1.0 / 86400.0) break;
                }
                $jdCross = ($lo + $hi) / 2.0;
                $dt = new \DateTime('@' . (int)round(($jdCross - 2440587.5) * 86400));
                $dt->modify(sprintf('%+d minutes', (int)round($utcOff * 60)));
                
                if ((int)$dt->format('Y') === $year) {
                    $sankrantis[$deg] = [
                        'name' => $names[$deg] . ' Sankranti',
                        'time' => $dt->format('Y-m-d H:i:s'),
                        'jd'   => $jdCross
                    ];
                }
            }
            $jd += 15.0; // Advance carefully
        }
        return $sankrantis;
    }

    /** Normalise to [0, 360) */
    public static function n360(float $x): float
    {
        return fmod(fmod($x, 360.0) + 360.0, 360.0);
    }

    /** Degrees → radians */
    private static function r(float $d): float
    {
        return $d * self::DEG;
    }

    /** Normalise hour to [0, 24) */
    private static function normalHour(float $h): float
    {
        return fmod(fmod($h, 24.0) + 24.0, 24.0);
    }

    /** Format decimal hours as HH:MM:SS */
    public static function decToHMS(float $h): string
    {
        $sign = $h < 0 ? '-' : '';
        $h    = abs($h);
        $hh   = (int)$h;
        $mm   = (int)(($h - $hh) * 60.0);
        $ss   = (int)round((($h - $hh) * 60.0 - $mm) * 60.0);
        return sprintf('%s%02d:%02d:%02d', $sign, $hh, $mm, $ss);
    }

    /** Format degrees as D° M′ S″ */
    public static function dms(float $deg): string
    {
        $d  = (int)floor($deg);
        $ms = ($deg - $d) * 60.0;
        $m  = (int)floor($ms);
        $s  = (int)round(($ms - $m) * 60.0);
        return "{$d}° {$m}′ {$s}″";
    }

    /** Ordinal suffix */
    public static function ordinal(int $n): string
    {
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n . ($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
    }

    /** Format Samapti local time result */
    private static function fmtSamaptiLocal(?array $result, ?float $riseHr): string
    {
        if ($result === null) return '—';
        $hr = $result['localHr'];
        if ($hr < 0 || $hr > 47) return '—';
        $hh = str_pad((int)($hr % 24), 2, '0', STR_PAD_LEFT);
        $mm = str_pad((int)(fmod($hr, 1.0) * 60.0), 2, '0', STR_PAD_LEFT);
        if ($riseHr !== null && $hr < $riseHr - 0.1) {
            return "▶{$hh}:{$mm}";   // ends before next sunrise → next day
        }
        return "{$hh}:{$mm}";
    }
}
