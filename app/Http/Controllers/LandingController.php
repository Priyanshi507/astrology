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

    // ── Main landing page ────────────────────────────────────────────
    public function index()
    {
        $now  = new \DateTime('now', new \DateTimeZone(self::TZ));
        $data = $this->buildForDate($now);

        // Upcoming festivals (next 9)
        $yr       = (int)$now->format('Y');
        $today    = $now->format('Y-m-d');
        $festData = HinduFestivalCalculator::calculateYear($yr, self::LAT, self::LON, self::UTC);
        $upcoming = [];
        foreach (($festData['festivals'] ?? []) as $f) {
            if (($f['date'] ?? '') >= $today && count($upcoming) < 9) {
                $upcoming[] = $f;
            }
        }

        return view('landing', array_merge($data, ['upcoming' => $upcoming]));
    }

    // ── AJAX: panchanga + planets for any date ────────────────────────
    public function panchangaData(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $dateStr = $request->input('date', (new \DateTime('now', new \DateTimeZone(self::TZ)))->format('Y-m-d'));
        $date    = \DateTime::createFromFormat('Y-m-d', $dateStr, new \DateTimeZone(self::TZ));
        return response()->json($this->buildForDate($date));
    }

    // ── Private builder ───────────────────────────────────────────────
    private function buildForDate(\DateTime $date): array
    {
        $yr = (int)$date->format('Y');
        $mo = (int)$date->format('m');
        $dy = (int)$date->format('d');

        // Use sunrise as the panchanga reference time
        $ss     = AstroCalculator::sunriseSunset($yr, $mo, $dy, self::LAT, self::LON, self::UTC);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $hr     = (int)floor($riseHr);
        $mn     = (int)round(($riseHr - $hr) * 60);

        $result  = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, self::UTC, self::LAT, self::LON);
        $ayan    = $result['ayan'];
        $planets = $result['planets'];
        $pancha  = $result['pancha'];
        $tk      = $result['tk'];

        $vSigns = AstroCalculator::getVedicSigns();
        $naks   = AstroCalculator::getNakshatras();

        $syms   = ['sun'=>'☀','moon'=>'☽','mercury'=>'☿','venus'=>'♀','mars'=>'♂',
                   'jupiter'=>'♃','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋'];
        $colors = ['sun'=>'#d4921e','moon'=>'#7aafce','mercury'=>'#28a870',
                   'venus'=>'#b84ca0','mars'=>'#d83820','jupiter'=>'#c8901a',
                   'saturn'=>'#7060a8','rahu'=>'#208048','ketu'=>'#a03818'];
        $labels = ['sun'=>'Surya','moon'=>'Chandra','mercury'=>'Budha',
                   'venus'=>'Shukra','mars'=>'Mangala','jupiter'=>'Guru',
                   'saturn'=>'Shani','rahu'=>'Rahu','ketu'=>'Ketu'];

        $planetDisplay = [];
        foreach ($planets as $pid => $p) {
            $sIdx = (int)floor($p['sider'] / 30);
            $nIdx = (int)floor($p['sider'] / (360 / 27));
            $planetDisplay[$pid] = [
                'lon'   => round($p['sider'], 2),
                'sign'  => $vSigns[$sIdx],
                'nak'   => $naks[$nIdx]['n'],
                'lord'  => $naks[$nIdx]['l'],
                'retro' => $p['retro'],
                'sym'   => $syms[$pid] ?? '◈',
                'color' => $colors[$pid] ?? '#888',
                'label' => $labels[$pid] ?? ucfirst($pid),
                'deg'   => AstroCalculator::dms(fmod($p['sider'], 30)),
            ];
        }

        $sunrise = (!$ss['polar'] && $ss['rise'] !== null)
            ? substr(AstroCalculator::decToHMS($ss['rise']), 0, 5) : '—';
        $sunset  = (!$ss['polar'] && $ss['set'] !== null)
            ? substr(AstroCalculator::decToHMS($ss['set']),  0, 5) : '—';

        return [
            'date'        => $date->format('Y-m-d'),
            'dateDisplay' => $date->format('d F Y'),
            'dayName'     => $pancha['vara']['en'],
            'ayan'        => round($ayan, 4),
            'sunrise'     => $sunrise,
            'sunset'      => $sunset,
            'planets'     => $planetDisplay,
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
