<?php

namespace App\Http\Controllers;

use App\Features\Planetary\AstroCalculator;
use App\Features\Festival\HinduFestivalCalculator;

class LandingController extends Controller
{
    private const LAT = 28.6139;  // New Delhi
    private const LON = 77.2090;
    private const UTC = 5.5;      // IST
    private const TZ  = 'Asia/Kolkata';

    public function index()
    {
        $now = new \DateTime('now', new \DateTimeZone(self::TZ));
        $yr  = (int)$now->format('Y');
        $mo  = (int)$now->format('m');
        $dy  = (int)$now->format('d');
        $hr  = (int)$now->format('H');
        $mn  = (int)$now->format('i');

        $result  = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, self::UTC, self::LAT, self::LON);
        $ayan    = $result['ayan'];
        $planets = $result['planets'];
        $pancha  = $result['pancha'];
        $tk      = $result['tk'];
        $ss      = $result['ss'];

        $vSigns = AstroCalculator::getVedicSigns();
        $naks   = AstroCalculator::getNakshatras();

        $syms   = ['sun'=>'☀','moon'=>'☽','mercury'=>'☿','venus'=>'♀','mars'=>'♂',
                   'jupiter'=>'♃','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋'];
        $colors = ['sun'=>'#d4921e','moon'=>'#7aafce','mercury'=>'#28a870','venus'=>'#b84ca0',
                   'mars'=>'#d83820','jupiter'=>'#c8901a','saturn'=>'#7060a8',
                   'rahu'=>'#208048','ketu'=>'#a03818'];
        $labels = ['sun'=>'Surya','moon'=>'Chandra','mercury'=>'Budha','venus'=>'Shukra',
                   'mars'=>'Mangala','jupiter'=>'Guru','saturn'=>'Shani','rahu'=>'Rahu','ketu'=>'Ketu'];

        $planetDisplay = [];
        foreach ($planets as $pid => $p) {
            $signIdx = (int)floor($p['sider'] / 30);
            $nakIdx  = (int)floor($p['sider'] / (360 / 27));
            $planetDisplay[$pid] = [
                'lon'   => round($p['sider'], 2),
                'sign'  => $vSigns[$signIdx],
                'nak'   => $naks[$nakIdx]['n'],
                'lord'  => $naks[$nakIdx]['l'],
                'retro' => $p['retro'],
                'sym'   => $syms[$pid] ?? '◈',
                'color' => $colors[$pid] ?? '#888',
                'label' => $labels[$pid] ?? ucfirst($pid),
                'deg'   => AstroCalculator::dms(fmod($p['sider'], 30)),
            ];
        }

        // Upcoming festivals — next 9
        $today    = $now->format('Y-m-d');
        $festData = HinduFestivalCalculator::calculateYear($yr, self::LAT, self::LON, self::UTC);
        $upcoming = [];
        foreach (($festData['festivals'] ?? []) as $f) {
            if (($f['date'] ?? '') >= $today && count($upcoming) < 9) {
                $upcoming[] = $f;
            }
        }

        $sunrise = (!$ss['polar'] && $ss['rise'] !== null)
            ? substr(AstroCalculator::decToHMS($ss['rise']), 0, 5) : '—';
        $sunset  = (!$ss['polar'] && $ss['set']  !== null)
            ? substr(AstroCalculator::decToHMS($ss['set']),  0, 5) : '—';

        $tithiProg  = round($tk['tithiProg']  * 100, 1);
        $elong      = round($tk['elong'], 2);

        return view('landing', [
            'planetDisplay' => $planetDisplay,
            'ayan'          => round($ayan, 4),
            'pancha'        => $pancha,
            'tithi'         => $tk['tithi'],
            'karana'        => $tk['karana'],
            'elong'         => $elong,
            'tithiProg'     => $tithiProg,
            'sunrise'       => $sunrise,
            'sunset'        => $sunset,
            'upcoming'      => $upcoming,
            'dateStr'       => $now->format('d F Y'),
            'dayName'       => $pancha['vara']['en'],
        ]);
    }
}
