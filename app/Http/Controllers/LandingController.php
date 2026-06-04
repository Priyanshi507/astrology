<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Features\Planetary\AstroCalculator;
use App\Features\Festival\HinduFestivalCalculator;

class LandingController extends Controller
{
    private const LAT = 28.6139;
    private const LON = 77.2090;
    private const UTC = 5.5;
    private const TZ  = 'Asia/Kolkata';

    private const CHOGHADIYA_DAY = [
        0 => ['Udveg','Chal','Labh','Amrit','Kaal','Shubh','Rog','Udveg'],
        1 => ['Amrit','Kaal','Shubh','Rog','Udveg','Chal','Labh','Amrit'],
        2 => ['Rog','Udveg','Chal','Labh','Amrit','Kaal','Shubh','Rog'],
        3 => ['Labh','Amrit','Kaal','Shubh','Rog','Udveg','Chal','Labh'],
        4 => ['Shubh','Rog','Udveg','Chal','Labh','Amrit','Kaal','Shubh'],
        5 => ['Chal','Labh','Amrit','Kaal','Shubh','Rog','Udveg','Chal'],
        6 => ['Kaal','Shubh','Rog','Udveg','Chal','Labh','Amrit','Kaal'],
    ];

    public function index()
    {
        $now  = new \DateTime('now', new \DateTimeZone(self::TZ));
        $data = $this->buildForDate($now);

        $yr       = (int)$now->format('Y');
        $today    = $now->format('Y-m-d');
        $festData = HinduFestivalCalculator::calculateYear($yr, self::LAT, self::LON, self::UTC);
        $upcoming = [];
        foreach (($festData['festivals'] ?? []) as $f) {
            if (($f['date'] ?? '') >= $today && count($upcoming) < 10) {
                $upcoming[] = $f;
            }
        }

        return view('landing', array_merge($data, ['upcoming' => $upcoming]));
    }

    public function panchangaData(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $dateStr = $request->input('date', (new \DateTime('now', new \DateTimeZone(self::TZ)))->format('Y-m-d'));
        $date    = \DateTime::createFromFormat('Y-m-d', $dateStr, new \DateTimeZone(self::TZ));
        return response()->json($this->buildForDate($date));
    }

    private function buildForDate(\DateTime $date): array
    {
        $yr = (int)$date->format('Y');
        $mo = (int)$date->format('m');
        $dy = (int)$date->format('d');

        $ss     = AstroCalculator::sunriseSunset($yr, $mo, $dy, self::LAT, self::LON, self::UTC);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $setHr  = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;
        $hr     = (int)floor($riseHr);
        $mn     = (int)round(($riseHr - $hr) * 60);

        $result     = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, self::UTC, self::LAT, self::LON);
        $ayan       = $result['ayan'];
        $planets    = $result['planets'];
        $pancha     = $result['pancha'];
        $tk         = $result['tk'];
        $ascSignIdx = $result['ascSignIdx'];
        $ascSider   = $result['ascSider'];

        $vSigns = AstroCalculator::getVedicSigns();
        $naks   = AstroCalculator::getNakshatras();

        $syms   = ['sun'=>'☀','moon'=>'☽','mercury'=>'☿','venus'=>'♀','mars'=>'♂',
                   'jupiter'=>'♃','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋'];
        $colors = ['sun'=>'#d4921e','moon'=>'#5a9fd4','mercury'=>'#28a870',
                   'venus'=>'#b84ca0','mars'=>'#d83820','jupiter'=>'#c8901a',
                   'saturn'=>'#7060a8','rahu'=>'#208048','ketu'=>'#a03818'];
        $abbrs  = ['sun'=>'Su','moon'=>'Mo','mercury'=>'Me','venus'=>'Ve','mars'=>'Ma',
                   'jupiter'=>'Ju','saturn'=>'Sa','rahu'=>'Ra','ketu'=>'Ke'];
        $labels = ['sun'=>'Surya','moon'=>'Chandra','mercury'=>'Budha','venus'=>'Shukra',
                   'mars'=>'Mangala','jupiter'=>'Guru','saturn'=>'Shani','rahu'=>'Rahu','ketu'=>'Ketu'];

        $planetDisplay = [];
        foreach ($planets as $pid => $p) {
            $sIdx = (int)floor($p['sider'] / 30);
            $nIdx = (int)floor($p['sider'] / (360 / 27));
            $houseNum = (($sIdx - $ascSignIdx + 12) % 12) + 1;
            $planetDisplay[$pid] = [
                'lon'      => round($p['sider'], 2),
                'signIdx'  => $sIdx,
                'sign'     => $vSigns[$sIdx],
                'nak'      => $naks[$nIdx]['n'],
                'lord'     => $naks[$nIdx]['l'],
                'retro'    => $p['retro'],
                'sym'      => $syms[$pid] ?? '◈',
                'color'    => $colors[$pid] ?? '#888',
                'abbr'     => $abbrs[$pid] ?? strtoupper(substr($pid, 0, 2)),
                'label'    => $labels[$pid] ?? ucfirst($pid),
                'deg'      => AstroCalculator::dms(fmod($p['sider'], 30)),
                'house'    => $houseNum,
            ];
        }

        $sunrise = (!$ss['polar'] && $ss['rise'] !== null)
            ? substr(AstroCalculator::decToHMS($ss['rise']), 0, 5) : '—';
        $sunset  = (!$ss['polar'] && $ss['set']  !== null)
            ? substr(AstroCalculator::decToHMS($ss['set']),  0, 5) : '—';

        // Choghadiya (8 day periods sunrise→sunset)
        $varaIdx  = $pancha['varaIdx'];
        $periods  = self::CHOGHADIYA_DAY[$varaIdx] ?? self::CHOGHADIYA_DAY[0];
        $periodLen = ($setHr - $riseHr) / 8;
        $choQual  = ['Amrit'=>'best','Shubh'=>'good','Labh'=>'good',
                     'Chal'=>'neutral','Udveg'=>'bad','Kaal'=>'bad','Rog'=>'bad'];
        $choHi    = ['Amrit'=>'अमृत','Shubh'=>'शुभ','Labh'=>'लाभ',
                     'Chal'=>'चल','Udveg'=>'उद्वेग','Kaal'=>'काल','Rog'=>'रोग'];
        $choghadiya = [];
        for ($i = 0; $i < 8; $i++) {
            $sH = $riseHr + $i * $periodLen;
            $eH = $riseHr + ($i + 1) * $periodLen;
            $nm = $periods[$i];
            $choghadiya[] = [
                'name'    => $nm,
                'nameHi'  => $choHi[$nm] ?? $nm,
                'quality' => $choQual[$nm] ?? 'neutral',
                'start'   => substr(AstroCalculator::decToHMS($sH), 0, 5),
                'end'     => substr(AstroCalculator::decToHMS($eH), 0, 5),
                'startHr' => round($sH, 3),
                'endHr'   => round($eH, 3),
            ];
        }

        return [
            'date'        => $date->format('Y-m-d'),
            'dateDisplay' => $date->format('d F Y'),
            'dayName'     => $pancha['vara']['en'],
            'ayan'        => round($ayan, 4),
            'sunrise'     => $sunrise,
            'sunset'      => $sunset,
            'ascSignIdx'  => $ascSignIdx,
            'ascSider'    => round($ascSider, 2),
            'planets'     => $planetDisplay,
            'choghadiya'  => $choghadiya,
            'tithi' => [
                'name'   => $tk['tithi']['n'],
                'paksha' => $tk['tithi']['paksha'],
                'num'    => $tk['tithi']['num'],
                'lord'   => $tk['tithi']['lord'],
                'deity'  => $tk['tithi']['deity'],
                'nature' => $tk['tithi']['nature'],
                'elong'  => round($tk['elong'], 2),
                'prog'   => round($tk['tithiProg'] * 100, 1),
            ],
            'vara' => [
                'name'   => $pancha['vara']['n'],
                'en'     => $pancha['vara']['en'],
                'lord'   => $pancha['vara']['lord'],
                'nature' => $pancha['vara']['nature'],
                'color'  => $pancha['vara']['color'],
                'sym'    => $pancha['vara']['sym'],
                'idx'    => $pancha['varaIdx'],
            ],
            'nakshatra' => [
                'name'  => $pancha['moonNak']['n'],
                'lord'  => $pancha['moonNak']['l'],
                'deity' => $pancha['moonNak']['d'],
                'gana'  => $pancha['moonNak']['gana'],
                'pada'  => $pancha['nakPada'],
                'idx'   => $pancha['nakIdx'],
                'prog'  => round($pancha['nakProg'] * 100, 1),
            ],
            'yoga' => [
                'name'   => $pancha['yoga']['n'],
                'nature' => $pancha['yoga']['nature'],
                'lord'   => $pancha['yoga']['lord'],
                'cls'    => $pancha['yoga']['cls'],
                'idx'    => $pancha['yogaIdx'],
                'prog'   => round($pancha['yogaProg'] * 100, 1),
            ],
            'karana' => [
                'name'   => $tk['karana']['n'],
                'lord'   => $tk['karana']['lord'],
                'nature' => $tk['karana']['nature'],
                'type'   => $tk['karana']['type'],
                'prog'   => round($tk['karanaProg'] * 100, 1),
                'slot'   => $tk['karanaSlot'],
            ],
        ];
    }
}
