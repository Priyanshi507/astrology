<?php

namespace App\Services\Kundali;

/**
 * KundaliService — Complete Kundali Table Calculations
 *
 * Implements:
 *   1. Graha Table  — planetary details (longitude, nakshatra, sub-lord, ruler, dignity)
 *   2. Bhava Table  — house details (residents, owner, qualities, aspects)
 *   3. Upgraha      — Dhuma, Vyatipata, Parivesha, Indrachapa, Upaketu, Gulika
 *   4. Yoga         — Pancha Mahapurusha, Gajakesari, Raj, Dhana, Viparita, etc.
 *   5. Ashtaka Varga — 7-planet Bhinnashtakavarga + Sarvashtakavarga (BPHS tables)
 *   6. Bhava Bala   — Bhavadhipati + Dig + Drishti bala per house
 *
 * References: BPHS, B.V. Raman "Ashtakavarga System", K.S. Krishnamurti (sub-lords)
 */
class KundaliService
{
    // ── Vimshottari dasha data ────────────────────────────────────────────────
    private const VIMSH_YEARS = [
        'ketu'=>7,'venus'=>20,'sun'=>6,'moon'=>10,
        'mars'=>7,'rahu'=>18,'jupiter'=>16,'saturn'=>19,'mercury'=>17,
    ];
    private const VIMSH_ORDER = ['ketu','venus','sun','moon','mars','rahu','jupiter','saturn','mercury'];

    // ── Sign lords (whole-sign: 0=Aries/Mars … 11=Pisces/Jupiter) ────────────
    public const SIGN_LORDS = [
        0=>'mars',1=>'venus',2=>'mercury',3=>'moon',4=>'sun',5=>'mercury',
        6=>'venus',7=>'mars',8=>'jupiter',9=>'saturn',10=>'saturn',11=>'jupiter',
    ];

    // ── Planet display ────────────────────────────────────────────────────────
    public const SYMS = [
        'sun'=>'☉','moon'=>'☾','mars'=>'♂','mercury'=>'☿',
        'jupiter'=>'♃','venus'=>'♀','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋',
    ];
    public const VEDIC_NAMES = [
        'sun'=>'Surya','moon'=>'Chandra','mars'=>'Mangal','mercury'=>'Budha',
        'jupiter'=>'Guru','venus'=>'Shukra','saturn'=>'Shani','rahu'=>'Rahu','ketu'=>'Ketu',
    ];

    // ── Sign data ─────────────────────────────────────────────────────────────
    public const SIGN_NAMES  = [
        'Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya',
        'Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena',
    ];
    private const SIGN_ABBR = [
        'Mesh','Vrsb','Mith','Kark','Simh','Kany',
        'Tula','Vris','Dhnu','Makr','Kumb','Meen',
    ];
    // gender (M/F), modality, element
    private const SIGN_QUALITIES = [
        0=>['M','Movable','Fire'],  1=>['F','Fixed','Earth'],
        2=>['M','Common','Air'],   3=>['F','Movable','Water'],
        4=>['M','Fixed','Fire'],   5=>['F','Common','Earth'],
        6=>['M','Movable','Air'],  7=>['F','Fixed','Water'],
        8=>['M','Common','Fire'],  9=>['F','Movable','Earth'],
        10=>['M','Fixed','Air'],   11=>['F','Common','Water'],
    ];

    // ── Nakshatra lords (index 0-26) ──────────────────────────────────────────
    public const NAK_LORDS = [
        'ketu','venus','sun','moon','mars','rahu','jupiter','saturn','mercury',
        'ketu','venus','sun','moon','mars','rahu','jupiter','saturn','mercury',
        'ketu','venus','sun','moon','mars','rahu','jupiter','saturn','mercury',
    ];
    public const NAK_NAMES = [
        'Ashwini','Bharani','Krittika','Rohini','Mrigashira','Ardra',
        'Punarvasu','Pushya','Ashlesha','Magha','Purva Phalguni','Uttara Phalguni',
        'Hasta','Chitra','Swati','Vishakha','Anuradha','Jyeshtha',
        'Moola','Purva Ashadha','Uttara Ashadha','Shravana','Dhanishtha',
        'Shatabhisha','Purva Bhadrapada','Uttara Bhadrapada','Revati',
    ];

    private const NAK_SPAN = 13.333333333333334; // 360/27

    // ── Dignity data ──────────────────────────────────────────────────────────
    // Exact sidereal exaltation degrees
    private const EXALT_DEG = [
        'sun'=>10.0,'moon'=>33.0,'mars'=>298.0,'mercury'=>165.0,
        'jupiter'=>95.0,'venus'=>357.0,'saturn'=>200.0,'rahu'=>50.0,'ketu'=>230.0,
    ];
    private const EXALT_SIGN = [
        'sun'=>0,'moon'=>1,'mars'=>9,'mercury'=>5,
        'jupiter'=>3,'venus'=>11,'saturn'=>6,'rahu'=>1,'ketu'=>7,
    ];
    private const DEBIL_SIGN = [
        'sun'=>6,'moon'=>7,'mars'=>3,'mercury'=>11,
        'jupiter'=>9,'venus'=>5,'saturn'=>0,'rahu'=>7,'ketu'=>1,
    ];
    private const OWN_SIGNS = [
        'sun'=>[4],'moon'=>[3],'mars'=>[0,7],'mercury'=>[2,5],
        'jupiter'=>[8,11],'venus'=>[1,6],'saturn'=>[9,10],'rahu'=>[],'ketu'=>[],
    ];
    private const MOOLATRIKONA = [
        'sun'=>4,'moon'=>1,'mars'=>0,'mercury'=>2,
        'jupiter'=>8,'venus'=>6,'saturn'=>10,
    ];

    // ── Natural friendship tables ─────────────────────────────────────────────
    private const NAT_FRIENDS = [
        'sun'=>['moon','mars','jupiter'],'moon'=>['sun','mercury'],
        'mars'=>['sun','moon','jupiter'],'mercury'=>['sun','venus'],
        'jupiter'=>['sun','moon','mars'],'venus'=>['mercury','saturn'],
        'saturn'=>['mercury','venus'],'rahu'=>['venus','saturn'],'ketu'=>['mars','jupiter'],
    ];
    private const NAT_ENEMIES = [
        'sun'=>['venus','saturn'],'moon'=>[],'mars'=>['mercury','venus','saturn'],
        'mercury'=>['moon'],'jupiter'=>['mercury','venus'],'venus'=>['sun','moon'],
        'saturn'=>['sun','moon','mars'],'rahu'=>['sun','moon'],'ketu'=>['venus','saturn'],
    ];

    // ── Natural malefics ──────────────────────────────────────────────────────
    private const MALEFICS = ['sun','mars','saturn','rahu','ketu'];

    // ── Vedic aspects per planet (house offsets → fraction strength) ──────────
    private const VEDIC_ASPECTS = [
        'sun'    =>  [7=>1.0],
        'moon'   =>  [7=>1.0],
        'mercury'=>  [7=>1.0],
        'venus'  =>  [7=>1.0],
        'mars'   =>  [4=>0.75,7=>1.0,8=>0.75],
        'jupiter'=>  [5=>1.0,7=>1.0,9=>1.0],
        'saturn' =>  [3=>0.75,7=>1.0,10=>0.75],
        'rahu'   =>  [5=>0.5,7=>1.0,9=>0.5],
        'ketu'   =>  [5=>0.5,7=>1.0,9=>0.5],
    ];

    // ── BPHS Bhinnashtakavarga tables ────────────────────────────────────────
    // Rows = contributor order: [Sun, Moon, Mars, Mercury, Jupiter, Venus, Saturn, Ascendant]
    // Values = 1-based house offsets from contributor's sign that receive a benefic point
    private const AV_TABLES = [
        'sun' => [
            [1,2,4,7,8,9,10,11],[3,6,10,11],[1,2,4,7,8,9,10,11],
            [3,5,6,9,10,11,12],[5,6,9,11],[6,7,12],
            [1,2,4,7,8,9,10,11],[3,4,6,10,11,12],
        ],
        'moon' => [
            [3,6,7,8,10,11],[1,3,6,7,10,11],[2,3,5,6,9,10,11],
            [1,3,4,5,7,8,10,11],[1,4,7,8,10,11,12],[3,4,5,7,9,10,11],
            [3,5,6,11],[3,6,10,11],
        ],
        'mars' => [
            [3,5,6,10,11],[3,6,11],[1,2,4,7,8,10,11],
            [3,5,6,11],[6,10,11,12],[6,8,11,12],
            [1,4,7,8,9,10,11],[1,2,4,8,10,11],
        ],
        'mercury' => [
            [5,6,9,11,12],[2,4,6,8,10,11],[1,2,4,7,8,9,10,11],
            [1,3,5,6,9,10,11,12],[6,8,11,12],[1,2,3,4,5,8,9,11],
            [1,2,4,7,8,9,10,11],[1,2,4,6,8,10,11],
        ],
        'jupiter' => [
            [1,2,3,4,7,8,9,10,11],[2,5,7,9,11],[1,2,4,7,8,10,11],
            [1,2,4,5,6,9,10,11],[1,2,3,4,7,8,10,11],[2,5,6,9,10,11],
            [3,5,6,12],[1,2,4,5,6,7,9,10,11],
        ],
        'venus' => [
            [8,11,12],[1,2,3,4,5,8,9,11,12],[3,4,6,9,11,12],
            [3,5,6,9,11],[5,8,9,10,11],[1,2,3,4,5,8,9,10,11],
            [3,4,5,8,9,10,11],[1,2,3,4,5,8,9,11],
        ],
        'saturn' => [
            [1,2,4,7,8,10,11],[3,6,11],[3,5,6,10,11,12],
            [6,8,9,10,11,12],[5,6,11,12],[6,11,12],
            [3,5,6,11],[1,3,4,6,10,11],
        ],
    ];

    // ════════════════════════════════════════════════════════════════════════
    //  MAIN ENTRY POINT
    // ════════════════════════════════════════════════════════════════════════

    public static function calculate(
        array $planets,
        float $ascSider,
        float $jd,
        int   $yr, int $mo, int $dy,
        int   $hr, int $mn,
        float $utcOff,
        float $lat,  float $lon,
        array $shadbala,
        array $angles,
        array $pancha,
        array $vimshottari
    ): array {
        $lagnaSignIdx = (int)floor($ascSider / 30.0);

        return [
            'graha'        => self::buildGrahaTable($planets, $ascSider, $lagnaSignIdx, $angles),
            'bhava'        => self::buildBhavaTable($planets, $lagnaSignIdx),
            'upgraha'      => self::buildUpagrahadata($planets, $ascSider, $jd, $yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon, $angles),
            'yogas'        => self::detectYogas($planets, $lagnaSignIdx),
            'ashtakaVarga' => self::buildAshtakaVarga($planets, $ascSider),
            'bhavaBala'    => self::buildBhavaBala($planets, $lagnaSignIdx, $shadbala),
            'lagnaSignIdx' => $lagnaSignIdx,
            'lagnaSign'    => self::SIGN_NAMES[$lagnaSignIdx],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  1. GRAHA TABLE
    // ════════════════════════════════════════════════════════════════════════

    // Combust thresholds (angular degrees from Sun)
    private const COMBUST_DEG = [
        'moon'    => 12.0,
        'mars'    => 17.0,
        'mercury' => 14.0,
        'jupiter' => 11.0,
        'venus'   => 10.0,
        'saturn'  => 15.0,
    ];

    public static function buildGrahaTable(array $planets, float $ascSider, int $lagnaSignIdx, array $angles = []): array
    {
        $rows = [];
        $eps  = $angles['eps'] ?? 23.4367; // ecliptic obliquity
        $sunTrop = $planets['sun']['trop'] ?? 0.0;

        // RA/Dec from tropical longitude (ecliptic → equatorial, zero latitude)
        $toEq = function(float $trop) use ($eps): array {
            $lr  = deg2rad($trop);
            $er  = deg2rad($eps);
            $ra  = fmod(rad2deg(atan2(sin($lr) * cos($er), cos($lr))) + 360.0, 360.0);
            $dec = rad2deg(asin(sin($er) * sin($lr)));
            return ['ra' => $ra, 'dec' => $dec];
        };
        // Format RA as HH:MM:SS
        $fmtRA = function(float $deg): string {
            $h  = (int)($deg / 15.0);
            $rm = ($deg - $h * 15.0) / 15.0 * 60.0;
            $m  = (int)$rm;
            $s  = round(($rm - $m) * 60.0);
            if ($s === 60) { $s = 0; $m++; }
            if ($m === 60) { $m = 0; $h++; }
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        };
        // Format Dec as ±DD°MM'SS"
        $fmtDec = function(float $deg): string {
            $sign = $deg < 0 ? '−' : '+';
            $d = abs($deg);
            $dd = (int)$d;
            $mm = (int)(($d - $dd) * 60);
            $ss = round((($d - $dd) * 60 - $mm) * 60);
            return sprintf('%s%02d°%02d\'%02d"', $sign, $dd, $mm, $ss);
        };
        // Angular distance accounting for wrap-around
        $angDist = function(float $a, float $b): float {
            $d = abs(fmod($a - $b + 360.0, 360.0));
            return $d > 180.0 ? 360.0 - $d : $d;
        };

        // ── Lagna row ──
        $lsn   = (int)floor($ascSider / 30.0);
        $lnIdx = (int)floor($ascSider / self::NAK_SPAN);
        $lnIn  = fmod($ascSider, self::NAK_SPAN);
        $lEq   = $toEq($planets['sun']['trop'] ?? $ascSider); // Lagna uses tropical asc; approx with sun trop for RA
        $lEq   = $toEq($ascSider + ($angles['eps'] ?? 0) * 0); // use sidereal as proxy — Lagna RA not meaningful; skip
        $rows[] = [
            'pid'         => 'lagna',
            'sym'         => '⬆',
            'name'        => 'Lagna',
            'vedicName'   => 'Lagna',
            'isLagna'     => true,
            'lonFmt'      => self::dmsInSign(fmod($ascSider, 30.0), $lsn),
            'lonFull'     => round($ascSider, 4),
            'signIdx'     => $lsn,
            'signName'    => self::SIGN_NAMES[$lsn],
            'nakName'     => self::NAK_NAMES[$lnIdx],
            'nakPada'     => self::nakPada($ascSider, $lnIdx),
            'nakLord'     => self::cap(self::NAK_LORDS[$lnIdx]),
            'subLord'     => self::cap(self::subLord($lnIdx, $lnIn)),
            'rulerOf'     => [1],
            'isIn'        => 1,
            'bOwner'      => self::cap(self::SIGN_LORDS[$lagnaSignIdx]),
            'relationship'=> 'Lagna',
            'dignity'     => '—',
            'retro'       => false,
            'combust'     => false,
            'lat'         => '—',
            'ra'          => '—',
            'dec'         => '—',
            'spd'         => '—',
        ];

        $ORDER = ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu'];
        foreach ($ORDER as $pid) {
            if (!isset($planets[$pid])) continue;
            $p      = $planets[$pid];
            $sider  = $p['sider'];
            $trop   = $p['trop'];
            $sgnIdx = (int)floor($sider / 30.0);
            $nakIdx = (int)floor($sider / self::NAK_SPAN);
            $inNak  = fmod($sider, self::NAK_SPAN);
            $house  = (($sgnIdx - $lagnaSignIdx + 12) % 12) + 1;
            $hSgnIdx= ($lagnaSignIdx + $house - 1) % 12;
            $bOwner = self::SIGN_LORDS[$hSgnIdx];

            $rulerOf = [];
            for ($h = 1; $h <= 12; $h++) {
                if (self::SIGN_LORDS[($lagnaSignIdx + $h - 1) % 12] === $pid) $rulerOf[] = $h;
            }

            // Combust check (Sun excluded)
            $combust = false;
            if ($pid !== 'sun' && isset(self::COMBUST_DEG[$pid])) {
                $combust = $angDist($trop, $sunTrop) <= self::COMBUST_DEG[$pid];
            }

            // RA & Dec from tropical longitude
            $eq  = $toEq($trop);
            $raFmt  = $fmtRA($eq['ra']);
            $decFmt = $fmtDec($eq['dec']);

            // Speed
            $spd = isset($p['spd']) ? sprintf('%+.4f°', $p['spd']) : '—';

            // Ecliptic latitude: 0 for Sun/Rahu/Ketu (in ecliptic plane), "—" for others
            $lat = in_array($pid, ['sun','rahu','ketu']) ? '0°00\'00"' : '—';

            $rows[] = [
                'pid'         => $pid,
                'sym'         => self::SYMS[$pid],
                'name'        => ucfirst($pid),
                'vedicName'   => self::VEDIC_NAMES[$pid],
                'isLagna'     => false,
                'lonFmt'      => self::dmsInSign(fmod($sider, 30.0), $sgnIdx),
                'lonFull'     => round($sider, 4),
                'signIdx'     => $sgnIdx,
                'signName'    => self::SIGN_NAMES[$sgnIdx],
                'nakName'     => self::NAK_NAMES[$nakIdx],
                'nakPada'     => self::nakPada($sider, $nakIdx),
                'nakLord'     => self::cap(self::NAK_LORDS[$nakIdx]),
                'subLord'     => self::cap(self::subLord($nakIdx, $inNak)),
                'rulerOf'     => $rulerOf,
                'isIn'        => $house,
                'bOwner'      => self::cap($bOwner),
                'relationship'=> self::relationship($pid, $sgnIdx),
                'dignity'     => self::dignity($pid, $sider, $sgnIdx),
                'retro'       => $p['retro'] ?? false,
                'combust'     => $combust,
                'lat'         => $lat,
                'ra'          => $raFmt,
                'dec'         => $decFmt,
                'spd'         => $spd,
            ];
        }
        return $rows;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  2. BHAVA TABLE
    // ════════════════════════════════════════════════════════════════════════

    public static function buildBhavaTable(array $planets, int $lagnaSignIdx): array
    {
        $houseRes  = array_fill(1, 12, []);
        foreach ($planets as $pid => $p) {
            $si = (int)floor($p['sider'] / 30.0);
            $h  = (($si - $lagnaSignIdx + 12) % 12) + 1;
            $houseRes[$h][] = ['pid'=>$pid,'sym'=>self::SYMS[$pid],'retro'=>$p['retro']??false];
        }

        $houseAsp = self::houseAspects($planets, $lagnaSignIdx);
        $rows = [];

        for ($h = 1; $h <= 12; $h++) {
            $si  = ($lagnaSignIdx + $h - 1) % 12;
            $q   = self::SIGN_QUALITIES[$si];
            $lord= self::SIGN_LORDS[$si];
            $rows[] = [
                'house'     => $h,
                'signIdx'   => $si,
                'signName'  => self::SIGN_NAMES[$si],
                'signAbbr'  => self::SIGN_ABBR[$si],
                'owner'     => self::cap($lord),
                'ownerSym'  => self::SYMS[$lord] ?? '',
                'residents' => $houseRes[$h],
                'gender'    => $q[0] === 'M' ? 'Mas' : 'Fem',
                'modality'  => $q[1],
                'element'   => $q[2],
                'aspectedBy'=> $houseAsp[$h] ?? [],
                'isKendra'  => in_array($h,[1,4,7,10]),
                'isTrikona' => in_array($h,[1,5,9]),
                'isDusthana'=> in_array($h,[6,8,12]),
            ];
        }
        return $rows;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  3. UPGRAHA (Upagrahas)
    // ════════════════════════════════════════════════════════════════════════

    public static function buildUpagrahadata(
        array $planets, float $ascSider, float $jd,
        int $yr, int $mo, int $dy, int $hr, int $mn,
        float $utcOff, float $lat, float $lon, array $angles
    ): array {
        $n = fn($x) => fmod(fmod($x,360)+360,360);
        $si = $planets['sun']['sider'] ?? 0.0;

        $dhuma     = $n($si + 133.333333);
        $vyatipata = $n(360.0 - $dhuma);
        $parivesha = $n($dhuma + 180.0);
        $indrachapa= $n(360.0 - $parivesha);
        $upaketu   = $n($si - 30.0);
        $gulika    = self::gulikaLon($jd, $hr, $mn, $ascSider);

        $lsi = (int)floor($ascSider / 30.0);
        $list = [
            ['Dhuma',      '⊕', $dhuma,     'Smoke planet — Sun + 133°20′'],
            ['Vyatipata',  '⊗', $vyatipata, '360° - Dhuma — calamity indicator'],
            ['Parivesha',  '⊙', $parivesha, 'Dhuma + 180°'],
            ['Indrachapa', '⊘', $indrachapa,'360° - Parivesha'],
            ['Upaketu',    '⊛', $upaketu,   'Sun - 30°'],
            ['Gulika',     '⬤', $gulika,    'Son of Saturn — most significant Upagraha'],
            ['Mandi',      '◉', $gulika,    'Same as Gulika (traditional equivalence)'],
        ];

        return array_map(function($u) use ($lsi) {
            [$name,$sym,$lon,$desc] = $u;
            $si2 = (int)floor($lon / 30.0);
            $ni  = (int)floor($lon / self::NAK_SPAN);
            $h   = (($si2 - $lsi + 12) % 12) + 1;
            return [
                'name'     => $name,
                'sym'      => $sym,
                'lon'      => round($lon, 4),
                'lonFmt'   => self::dmsInSign(fmod($lon, 30.0), $si2),
                'signIdx'  => $si2,
                'signName' => self::SIGN_NAMES[$si2],
                'nakName'  => self::NAK_NAMES[$ni],
                'nakLord'  => self::cap(self::NAK_LORDS[$ni]),
                'nakPada'  => self::nakPada($lon, $ni),
                'house'    => $h,
                'desc'     => $desc,
            ];
        }, $list);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  4. YOGA DETECTION
    // ════════════════════════════════════════════════════════════════════════

    public static function detectYogas(array $planets, int $lagnaSignIdx): array
    {
        $yogas = [];
        $houseOf = []; $signOf = [];
        foreach ($planets as $pid => $p) {
            $si = (int)floor($p['sider'] / 30.0);
            $signOf[$pid]  = $si;
            $houseOf[$pid] = (($si - $lagnaSignIdx + 12) % 12) + 1;
        }
        $K = [1,4,7,10]; $TK = [1,5,9]; $DU = [6,8,12];

        // ── Pancha Mahapurusha ─────────────────────────────────────────────
        $maha = [
            'mars'    =>['Ruchaka','Mars','valour, power, military success',[0,7,9]],
            'mercury' =>['Bhadra', 'Mercury','intellect, eloquence, business acumen',[2,5]],
            'jupiter' =>['Hamsa',  'Jupiter','wisdom, spirituality, wealth',[8,11,3]],
            'venus'   =>['Malavya','Venus','beauty, luxury, artistic talent, marital bliss',[1,6,11]],
            'saturn'  =>['Shasha', 'Saturn','authority over masses, administrative power',[9,10,6]],
        ];
        foreach ($maha as $pid => [$yName,$pName,$desc,$ownSigns]) {
            if (isset($houseOf[$pid], $signOf[$pid])
                && in_array($houseOf[$pid], $K)
                && in_array($signOf[$pid], $ownSigns)) {
                $yogas[] = self::yoga($yName.' Yoga','Pancha Mahapurusha',true,
                    "$pName in own/exalted sign in kendra (house {$houseOf[$pid]}). Bestows $desc.",
                    [$pid], $houseOf[$pid], 'excellent');
            }
        }

        // ── Gajakesari ────────────────────────────────────────────────────
        if (isset($houseOf['moon'],$houseOf['jupiter'])) {
            $diff = abs($houseOf['jupiter'] - $houseOf['moon']);
            if ($diff === 0 || $diff === 3 || $diff === 6 || $diff === 9) {
                $yogas[] = self::yoga('Gajakesari Yoga','Dhana-Raja',true,
                    'Jupiter in kendra from Moon. Bestows intelligence, fame, wealth and good fortune.',
                    ['jupiter','moon'],$houseOf['jupiter'],'very_good');
            }
        }

        // ── Budhaditya ────────────────────────────────────────────────────
        if (isset($houseOf['sun'],$houseOf['mercury'])
            && $houseOf['sun'] === $houseOf['mercury']) {
            $yogas[] = self::yoga('Budhaditya Yoga','Intelligence',true,
                'Sun and Mercury conjunct. Grants sharp intellect, clarity of thought and success in communication.',
                ['sun','mercury'],$houseOf['sun'],'good');
        }

        // ── Saraswati Yoga ─────────────────────────────────────────────────
        if (isset($houseOf['jupiter'],$houseOf['venus'],$houseOf['mercury'])) {
            $kkt = [1,2,4,5,7,9,10,11];
            if (in_array($houseOf['jupiter'],$kkt) && in_array($houseOf['venus'],$kkt)
                && in_array($houseOf['mercury'],$kkt) && in_array($signOf['jupiter'],[8,11,3])) {
                $yogas[] = self::yoga('Saraswati Yoga','Knowledge',true,
                    'Jupiter, Venus and Mercury all in kendra/trikona with Jupiter exalted/own. Grants mastery of arts and sciences.',
                    ['jupiter','venus','mercury'],null,'excellent');
            }
        }

        // ── Chandra-Mangala ──────────────────────────────────────────────
        if (isset($houseOf['moon'],$houseOf['mars'])
            && $houseOf['moon'] === $houseOf['mars']) {
            $yogas[] = self::yoga('Chandra-Mangala Yoga','Wealth',true,
                'Moon and Mars conjunct. Bestows financial enterprise, determination and materialistic drive.',
                ['moon','mars'],$houseOf['moon'],'good');
        }

        // ── Adhi Yoga ─────────────────────────────────────────────────────
        if (isset($houseOf['moon'])) {
            $mH = $houseOf['moon'];
            $benInPos = 0;
            foreach (['mercury','jupiter','venus'] as $b) {
                if (!isset($houseOf[$b])) continue;
                $rel = (($houseOf[$b] - $mH + 12) % 12) + 1;
                if (in_array($rel,[6,7,8])) $benInPos++;
            }
            if ($benInPos >= 2) {
                $yogas[] = self::yoga('Adhi Yoga','Leadership',true,
                    '2+ benefics in 6th–8th from Moon. Bestows leadership, authority and political power.',
                    ['mercury','jupiter','venus'],null,'good');
            }
        }

        // ── Raj Yoga (kendra-trikona lord conjunction) ────────────────────
        $kLords = []; $tLords = [];
        for ($h = 1; $h <= 12; $h++) {
            $l = self::SIGN_LORDS[($lagnaSignIdx + $h - 1) % 12];
            if (in_array($h,[4,7,10])) $kLords[$l] = $h;
            if (in_array($h,[5,9]))    $tLords[$l] = $h;
        }
        $rajFound = false;
        foreach ($kLords as $kl => $kh) {
            foreach ($tLords as $tl => $th) {
                if ($kl === $tl || $rajFound) continue;
                if (isset($houseOf[$kl],$houseOf[$tl]) && $houseOf[$kl] === $houseOf[$tl]) {
                    $yogas[] = self::yoga('Raj Yoga','Power-Authority',true,
                        ucfirst($kl).' (lord of '.$kh.'H kendra) conjunct '.ucfirst($tl).' (lord of '.$th.'H trikona). Bestows authority and prosperity.',
                        [$kl,$tl],$houseOf[$kl],'excellent');
                    $rajFound = true;
                }
            }
        }

        // ── Viparita Raja Yoga ────────────────────────────────────────────
        $dl = [6=>null,8=>null,12=>null];
        foreach ([5,7,11] as $offset) {
            $h = ($offset === 5)?6:($offset===7?8:12);
            $dl[$h] = self::SIGN_LORDS[($lagnaSignIdx + $h - 1) % 12];
        }
        $inDust = 0;
        foreach ($dl as $ownH => $lrd) {
            if ($lrd && isset($houseOf[$lrd]) && in_array($houseOf[$lrd],$DU) && $houseOf[$lrd] !== $ownH) {
                $inDust++;
            }
        }
        if ($inDust >= 2) {
            $yogas[] = self::yoga('Viparita Raja Yoga','Hidden-Strength',true,
                'Lords of dusthana houses (6,8,12) placed in other dusthanas. Adversity transforms into extraordinary strength.',
                array_values(array_filter(array_values($dl))),null,'very_good');
        }

        // ── Neecha Bhanga Raj Yoga ────────────────────────────────────────
        foreach (['sun','moon','mars','mercury','jupiter','venus','saturn'] as $pid) {
            if (!isset($signOf[$pid])) continue;
            if ($signOf[$pid] !== (self::DEBIL_SIGN[$pid] ?? -1)) continue;
            $dispLord = self::SIGN_LORDS[$signOf[$pid]];
            $cancel = false;
            if (isset($houseOf[$dispLord]) && in_array($houseOf[$dispLord],$K)) $cancel = true;
            if (!$cancel && isset($houseOf['moon'],$houseOf[$dispLord])) {
                $rel = (($houseOf[$dispLord]-$houseOf['moon']+12)%12)+1;
                if (in_array($rel,$K)) $cancel = true;
            }
            // Planet that exalts in same sign in kendra
            foreach (self::EXALT_SIGN as $ep => $es) {
                if ($es === $signOf[$pid] && $ep !== $pid && isset($houseOf[$ep]) && in_array($houseOf[$ep],$K)) {
                    $cancel = true; break;
                }
            }
            if ($cancel) {
                $yogas[] = self::yoga('Neecha Bhanga Raj Yoga','Redemption',true,
                    ucfirst($pid).' debilitated in '.self::SIGN_NAMES[$signOf[$pid]].' but cancellation converts weakness to power.',
                    [$pid],$houseOf[$pid]??null,'very_good');
            }
        }

        // ── Dhana Yoga ────────────────────────────────────────────────────
        $l2  = self::SIGN_LORDS[($lagnaSignIdx+1)%12];
        $l11 = self::SIGN_LORDS[($lagnaSignIdx+10)%12];
        if ($l2 !== $l11 && isset($houseOf[$l2],$houseOf[$l11]) && $houseOf[$l2]===$houseOf[$l11]) {
            $yogas[] = self::yoga('Dhana Yoga','Wealth',true,
                'Lords of 2nd and 11th conjunct. Bestows financial prosperity and fulfilment of desires.',
                [$l2,$l11],$houseOf[$l2],'very_good');
        }

        // ── Kemadruma Yoga (negative) ─────────────────────────────────────
        if (isset($houseOf['moon'])) {
            $mH = $houseOf['moon'];
            $adj = false;
            foreach ($planets as $pid => $p) {
                if (in_array($pid,['moon','rahu','ketu'])) continue;
                $ph = $houseOf[$pid] ?? 0;
                if ($ph === (($mH-2+12)%12)+1 || $ph === ($mH%12)+1) { $adj = true; break; }
            }
            if (!$adj && !in_array($mH,$K)) {
                $yogas[] = self::yoga('Kemadruma Yoga','Challenge',false,
                    'Moon isolated — no planets in 2nd/12th from it and not in kendra. Periods of struggle; mitigated by strong Jupiter.',
                    ['moon'],$mH,'challenging');
            }
        }

        return $yogas;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  5. ASHTAKA VARGA (BPHS Ch. 66–71)
    // ════════════════════════════════════════════════════════════════════════

    public static function buildAshtakaVarga(array $planets, float $ascSider): array
    {
        $lsi  = (int)floor($ascSider / 30.0);
        $PIDS = ['sun','moon','mars','mercury','jupiter','venus','saturn'];
        $CONTRIB_KEYS = ['sun','moon','mars','mercury','jupiter','venus','saturn','asc'];

        // Sign index of each contributor
        $contrib = [];
        foreach ($PIDS as $pid) {
            $contrib[$pid] = isset($planets[$pid]) ? (int)floor($planets[$pid]['sider']/30) : null;
        }
        $contrib['asc'] = $lsi;

        $bhinnaAV = [];
        $sarva    = array_fill(0, 12, 0);

        foreach (self::AV_TABLES as $pid => $tbl) {
            $pts = array_fill(0, 12, 0);
            foreach ($CONTRIB_KEYS as $ci => $ck) {
                $cs = $contrib[$ck];
                if ($cs === null) continue;
                foreach ($tbl[$ci] as $pos) {
                    $pts[($cs + $pos - 1) % 12]++;
                }
            }
            $bhinnaAV[$pid] = $pts;
            for ($s = 0; $s < 12; $s++) $sarva[$s] += $pts[$s];
        }

        return [
            'bhinnaAV'  => $bhinnaAV,
            'sarva'     => $sarva,
            'signNames' => self::SIGN_NAMES,
            'signAbbr'  => self::SIGN_ABBR,
            'planets'   => $PIDS,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  6. BHAVA BALA
    // ════════════════════════════════════════════════════════════════════════

    public static function buildBhavaBala(array $planets, int $lagnaSignIdx, array $shadbala): array
    {
        $shadRupas = [];
        foreach ($shadbala as $pid => $s) $shadRupas[$pid] = $s['rupas'] ?? 4.0;

        $houseAsp = self::houseAspects($planets, $lagnaSignIdx);
        $rows = [];

        for ($h = 1; $h <= 12; $h++) {
            $si   = ($lagnaSignIdx + $h - 1) % 12;
            $lord = self::SIGN_LORDS[$si];

            // Bhavadhipati Bala = lord's Shadbala rupas × 10 (scaled to Shashtiamshas)
            $bdBala = ($shadRupas[$lord] ?? 4.0) * 10.0;

            // Dig Bala — kendra strongest, apoklima weakest
            $digBala = match(true) {
                in_array($h,[1,4,7,10]) => 60.0,
                in_array($h,[2,5,8,11]) => 30.0,
                default                 => 15.0,
            };

            // Drishti Bala — benefic aspects add, malefic subtract
            $drishti = 0.0;
            foreach ($houseAsp[$h] ?? [] as $asp) {
                $mal = in_array($asp['pid'], self::MALEFICS);
                $drishti += $asp['fraction'] * ($mal ? -15.0 : 15.0);
            }

            $total = $bdBala + $digBala + $drishti;
            $rupas = $total / 60.0;

            $rows[] = [
                'house'     => $h,
                'signName'  => self::SIGN_NAMES[$si],
                'lord'      => self::cap($lord),
                'lordsym'   => self::SYMS[$lord] ?? '',
                'bdBala'    => round($bdBala, 2),
                'digBala'   => round($digBala, 2),
                'drishBala' => round($drishti, 2),
                'total'     => round($total, 2),
                'rupas'     => round($rupas, 3),
                'grade'     => self::gradeBB($rupas),
                'isKendra'  => in_array($h,[1,4,7,10]),
                'aspectedBy'=> $houseAsp[$h] ?? [],
            ];
        }
        return $rows;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /** KP Sub-lord: nakshatra span divided proportionally by Vimshottari years */
    private static function subLord(int $nakIdx, float $degInNak): string
    {
        $nakLord  = self::NAK_LORDS[$nakIdx];
        $startPos = (int)array_search($nakLord, self::VIMSH_ORDER);
        $total    = array_sum(self::VIMSH_YEARS); // 120
        $accum    = 0.0;
        for ($i = 0; $i < 9; $i++) {
            $pid  = self::VIMSH_ORDER[($startPos + $i) % 9];
            $span = (self::VIMSH_YEARS[$pid] / $total) * self::NAK_SPAN;
            $accum += $span;
            if ($degInNak <= $accum || $i === 8) return $pid;
        }
        return 'unknown';
    }

    /** Format degrees within a sign as DD° MM′ SS″ SignAbbr */
    private static function dmsInSign(float $deg, int $signIdx): string
    {
        $d = (int)$deg;
        $mf= ($deg - $d) * 60; $m = (int)$mf;
        $s = (int)(($mf - $m) * 60);
        return sprintf('%02d° %s %02d′ %02d″', $d, self::SIGN_ABBR[$signIdx], $m, $s);
    }

    /** Nakshatra pada (1-4) for a given sidereal longitude */
    public static function nakPada(float $sider, int $nakIdx): int
    {
        return (int)floor(fmod($sider, self::NAK_SPAN) / (self::NAK_SPAN / 4)) + 1;
    }

    /** Planetary relationship with the sign it occupies */
    private static function relationship(string $pid, int $signIdx): string
    {
        if (in_array($signIdx, self::OWN_SIGNS[$pid] ?? []))        return 'Own House';
        if ($signIdx === (self::EXALT_SIGN[$pid] ?? -1))             return 'Exalted';
        if ($signIdx === (self::DEBIL_SIGN[$pid] ?? -1))             return 'Debilitated';
        $sl = self::SIGN_LORDS[$signIdx];
        if ($sl === $pid) return 'Own House';
        if (in_array($sl, self::NAT_FRIENDS[$pid] ?? []))  return "Friend's House";
        if (in_array($sl, self::NAT_ENEMIES[$pid] ?? []))  return "Enemy's House";
        return 'Neutral';
    }

    /** Dignity label for a planet at a given position */
    private static function dignity(string $pid, float $sider, int $signIdx): string
    {
        if (in_array($pid,['rahu','ketu'])) {
            if ($signIdx === (self::EXALT_SIGN[$pid]??-1)) return 'Exalted';
            if ($signIdx === (self::DEBIL_SIGN[$pid]??-1)) return 'Debilitated';
            return '—';
        }
        $ed = self::EXALT_DEG[$pid] ?? -1;
        if ($ed >= 0) {
            $diff = abs($sider - $ed); if ($diff > 180) $diff = 360 - $diff;
            if ($diff <= 1.0) return 'Deep Exalted';
            if ($signIdx === (self::EXALT_SIGN[$pid]??-1)) return 'Exalted';
        }
        if ($signIdx === (self::DEBIL_SIGN[$pid]??-1)) {
            $dd = fmod($ed + 180, 360);
            $diff = abs($sider - $dd); if ($diff > 180) $diff = 360 - $diff;
            return $diff <= 1.0 ? 'Deep Debilitated' : 'Debilitated';
        }
        if ($signIdx === (self::MOOLATRIKONA[$pid]??-1)) return 'Moolatrikona';
        if (in_array($signIdx, self::OWN_SIGNS[$pid]??[]))  return 'Own Sign';
        return '—';
    }

    /** House aspects map: house → [{pid, sym, fraction}] */
    public static function houseAspects(array $planets, int $lagnaSignIdx): array
    {
        $map = array_fill(1, 12, []);
        foreach ($planets as $pid => $p) {
            $h  = ((int)floor($p['sider']/30) - $lagnaSignIdx + 12) % 12 + 1;
            foreach (self::VEDIC_ASPECTS[$pid] ?? [7=>1.0] as $dist => $frac) {
                $target = (($h + $dist - 2) % 12) + 1;
                $map[$target][] = ['pid'=>$pid,'sym'=>self::SYMS[$pid],'fraction'=>$frac,'retro'=>$p['retro']??false];
            }
        }
        return $map;
    }

    /** Gulika longitude via weekday-based hora slot */
    private static function gulikaLon(float $jd, int $hr, int $mn, float $ascSider): float
    {
        $n   = fn($x) => fmod(fmod($x,360)+360,360);
        $dow = (int)(($jd + 1.5) % 7); // 0=Sun … 6=Sat
        $daySlots = [6,5,4,3,2,1,0];   // Gulika slot for each weekday (from sunrise)
        $slot = $daySlots[$dow];
        // Day approximated 6 AM–6 PM; each of 8 slots = 1.5 hours
        $gulikaHr = 6.0 + $slot * 1.5;
        // ASC shifts ~15°/hr on average
        $ascShift = ($gulikaHr - ($hr + $mn / 60.0)) * 15.0;
        return $n($ascSider + $ascShift);
    }

    private static function yoga(string $name,string $type,bool $ausp,string $desc,array $pids,?int $house,string $cls): array
    {
        return compact('name','type','ausp','desc','pids','house','cls');
    }

    private static function gradeBB(float $rupas): string
    {
        return match(true) {
            $rupas >= 8.0 => 'Exceptional',
            $rupas >= 6.0 => 'Strong',
            $rupas >= 4.0 => 'Average',
            $rupas >= 2.0 => 'Weak',
            default       => 'Very Weak',
        };
    }

    private static function cap(string $s): string
    {
        return ucfirst($s);
    }
}
