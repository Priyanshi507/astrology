<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Planetary\AstroCalculator;
use App\Services\Festival\HinduFestivalCalculator;

class LandingController extends Controller
{
    private const LAT = 28.6139;
    private const LON = 77.2090;
    private const UTC = 5.5;
    private const TZ  = 'Asia/Kolkata';

    public function index()
    {
        $now     = new \DateTime('now', new \DateTimeZone(self::TZ));
        $dateStr = $now->format('Y-m-d');

        // Reuse the same 30-day cache as panchangaData() so the initial page
        // load is instant on repeated visits and shares cache with AJAX calls.
        $data = Cache::remember("panchanga_day_{$dateStr}", 86400 * 30, function () use ($now, $dateStr) {
            $built    = $this->buildForDate($now);
            $allFests = $this->yearFestivals((int)$now->format('Y'));
            $upcoming = [];
            foreach ($allFests as $f) {
                if (($f['date'] ?? '') >= $dateStr && count($upcoming) < 40) {
                    $upcoming[] = $f;
                }
            }
            $built['upcoming'] = $upcoming;
            return $built;
        });

        return view('landing', $data);
    }

    public function panchangaData(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $dateStr = $request->input('date', (new \DateTime('now', new \DateTimeZone(self::TZ)))->format('Y-m-d'));

        $data = Cache::remember("panchanga_day_{$dateStr}", 86400 * 30, function () use ($dateStr) {
            $date     = \DateTime::createFromFormat('Y-m-d', $dateStr, new \DateTimeZone(self::TZ));
            $built    = $this->buildForDate($date);
            $allFests = $this->yearFestivals((int)$date->format('Y'));
            $upcoming = [];
            foreach ($allFests as $f) {
                if (($f['date'] ?? '') >= $dateStr && count($upcoming) < 40) {
                    $upcoming[] = $f;
                }
            }
            $built['upcoming'] = $upcoming;
            return $built;
        });

        return response()->json($data);
    }

    private function yearFestivals(int $yr): array
    {
        return Cache::remember("landing_festivals_{$yr}", 86400, function () use ($yr) {
            $festData = HinduFestivalCalculator::calculateYear($yr, self::LAT, self::LON, self::UTC);
            return $festData['festivals'] ?? [];
        });
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

        // Panchanga (tithi / nakshatra / yoga …) uses the conventional sunrise reference.
        $result     = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, self::UTC, self::LAT, self::LON);
        $pancha     = $result['pancha'];
        $tk         = $result['tk'];

        // Planet positions, ascendant & charts use the date's ACTUAL clock time, so
        // the degrees shown here exactly match the calculator page when the same
        // date/time/location is entered there. (date-only → noon).
        $clkHr = (int)$date->format('G');
        $clkMn = (int)$date->format('i');
        if ($clkHr === 0 && $clkMn === 0) { $clkHr = 12; }
        $pResult    = AstroCalculator::calculate($yr, $mo, $dy, $clkHr, $clkMn, self::UTC, self::LAT, self::LON);
        $ayan       = $pResult['ayan'];
        $planets    = $pResult['planets'];
        $ascSignIdx = $pResult['ascSignIdx'];
        $ascSider   = $pResult['ascSider'];

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
        $varaIdx   = $pancha['varaIdx'];
        $periodLen = ($setHr - $riseHr) / 8;
        $periods   = DB::table('choghadiya_sequences as cs')
            ->join('weekdays as w', 'cs.weekday_id', '=', 'w.id')
            ->join('choghadiya_types as ct', 'cs.choghadiya_type_id', '=', 'ct.id')
            ->where('w.dow_index', $varaIdx)
            ->where('cs.is_night', false)
            ->orderBy('cs.id')
            ->pluck('ct.name')
            ->toArray();
        $choQual = ['Amrit'=>'best', 'Shubha'=>'good', 'Labha'=>'good',
                    'Char'=>'neutral', 'Udveg'=>'bad', 'Kaal'=>'bad', 'Rog'=>'bad'];
        $choghadiya = [];
        for ($i = 0; $i < 8; $i++) {
            $sH = $riseHr + $i * $periodLen;
            $eH = $riseHr + ($i + 1) * $periodLen;
            $nm = $periods[$i] ?? '';
            $choghadiya[] = [
                'name'    => $nm,
                'nameHi'  => $nm,
                'quality' => $choQual[$nm] ?? 'neutral',
                'start'   => substr(AstroCalculator::decToHMS($sH), 0, 5),
                'end'     => substr(AstroCalculator::decToHMS($eH), 0, 5),
                'startHr' => round($sH, 3),
                'endHr'   => round($eH, 3),
            ];
        }

        // ── Rahu Kaal — standard part (1–8) of the day per weekday ──────
        $rKParts = [0 => 8, 1 => 2, 2 => 7, 3 => 5, 4 => 6, 5 => 4, 6 => 3];
        $rKPart  = $rKParts[$varaIdx] ?? 1;
        $rKStart = $riseHr + ($rKPart - 1) * ($setHr - $riseHr) / 8;
        $rKEnd   = $riseHr + $rKPart       * ($setHr - $riseHr) / 8;

        // ── Abhijit Muhurat (solar noon ± 24 min, always auspicious) ───
        $solarNoon = ($riseHr + $setHr) / 2;
        $abStart   = $solarNoon - 24 / 60;
        $abEnd     = $solarNoon + 24 / 60;

        // ── Yamaganda Kaal — standard part per weekday ─────────────────
        $ygParts = [0 => 5, 1 => 4, 2 => 3, 3 => 2, 4 => 1, 5 => 7, 6 => 6];
        $ygPart  = $ygParts[$varaIdx] ?? 1;
        $ygStart = $riseHr + ($ygPart - 1) * ($setHr - $riseHr) / 8;
        $ygEnd   = $riseHr + $ygPart       * ($setHr - $riseHr) / 8;

        return [
            'date'        => $date->format('Y-m-d'),
            'dateDisplay' => $date->format('d F Y'),
            'dayName'     => $pancha['vara']['en'],
            'ayan'        => round($ayan, 4),
            'rahuKaal' => [
                'start'   => substr(AstroCalculator::decToHMS($rKStart), 0, 5),
                'end'     => substr(AstroCalculator::decToHMS($rKEnd),   0, 5),
                'startHr' => round($rKStart, 3),
                'endHr'   => round($rKEnd,   3),
            ],
            'abhijit' => [
                'start'   => substr(AstroCalculator::decToHMS($abStart), 0, 5),
                'end'     => substr(AstroCalculator::decToHMS($abEnd),   0, 5),
                'startHr' => round($abStart, 3),
                'endHr'   => round($abEnd,   3),
            ],
            'yamghantam' => [
                'start'   => substr(AstroCalculator::decToHMS($ygStart), 0, 5),
                'end'     => substr(AstroCalculator::decToHMS($ygEnd),   0, 5),
                'startHr' => round($ygStart, 3),
                'endHr'   => round($ygEnd,   3),
            ],
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
