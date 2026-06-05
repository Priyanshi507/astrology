<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Features\Planetary\AstroCalculator;
use App\Features\ChartRendering\AstroChartRenderer;
use App\Features\ChartRendering\ShodashvargaCalculator;
use App\Features\ChartRendering\VargaChartRenderer;
use App\Features\Planetary\ShadBalaCalculator;
use App\Features\Planetary\GocharCalculator;
use App\Features\Planetary\TransitCalculator;
use App\Features\Dasha\VimshottariDashaCalculator;
use App\Features\Festival\HinduFestivalCalculator;
use App\Features\Festival\TodayPanelService;
use App\Features\Festival\MuhratCalculator;
use App\Features\Festival\TarabalMurtiService;

class AstroController extends Controller
{
    // ── GET /astro — show the main page ───────────────────────────
    public function index()
    {
        return view('index');
    }

    // ── POST /astro/calculate — full chart calculation ─────────────
    public function calculate(Request $request)
    {
        $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'time'      => 'required|date_format:H:i',
            'utcOffset' => 'required|numeric|min:-12|max:14',
            'lat'       => 'required|numeric|min:-90|max:90',
            'lon'       => 'required|numeric|min:-180|max:180',
        ]);

        [$yr, $mo, $dy] = array_map('intval', explode('-', $request->date));
        [$hr, $mn]      = array_map('intval', explode(':', $request->time));
        $utcOff = (float)$request->utcOffset;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;

        // ── All astronomical calculations ──────────────────────────
        $result  = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);

        $ayan    = $result['ayan'];
        $planets = $result['planets'];
        $ascTrop = $result['ascTrop'];
        $ascSider= $result['ascSider'];
        $dasha   = $result['dasha'];

        // ── Build D1 Kundali SVG ───────────────────────────────────
        $chartHtml = AstroChartRenderer::render($ascTrop, $planets, $ayan, $dasha);
        

        // ── Build house signs list for legend ─────────────────────
        $signToHouse     = array_fill(0, 12, 0);
        foreach ($result['houseSign'] as $h => $sign) {
            $signToHouse[$sign] = $h + 1;
        }
        $housePlanetsArr = array_fill(0, 12, []);
        $n360 = fn(float $x) => fmod(fmod($x, 360) + 360, 360);
        foreach ($planets as $pid => $pdata) {
            $pSider   = $n360($pdata['trop'] - $ayan);
            $pSignIdx = (int)floor($pSider / 30);
            $house    = $signToHouse[$pSignIdx];
            if ($house >= 1) {
                $housePlanetsArr[$house - 1][] = [
                    'pid'   => $pid,
                    'retro' => $pdata['retro'],
                    'abbr'  => AstroChartRenderer::PLANET_ABBR[$pid] ?? strtoupper(substr($pid, 0, 2)),
                ];
            }
        }
        $houseSignsHtml    = AstroChartRenderer::buildHouseSignsList($result['ascSignIdx'], $housePlanetsArr);
        $planetSummaryHtml = AstroChartRenderer::buildPlanetSummary($planets, $ayan, $signToHouse);

        // ── Shodashvarga — all 20 divisional charts ────────────────
        $allVargas         = ShodashvargaCalculator::calculateAll($planets, $ascSider);
        $vargaGridHtml     = VargaChartRenderer::renderVargaGrid($allVargas, $ascSider);
        $vargaSummary      = ShodashvargaCalculator::buildSummary($allVargas);
        $planetVargaBadges = [];
        foreach (array_keys($planets) as $pid) {
            $planetVargaBadges[$pid] = VargaChartRenderer::buildPlanetVargaSummary($allVargas, $pid);
        }

        // ── Formatted display strings ──────────────────────────────
        $fmt    = $this->formatResult($result);
        $angles = $result['angles'];
        $ss     = $result['ss'];
        $tk     = $result['tk'];
        $tkRise = $result['tkRise'];
        $tkSet  = $result['tkSet'];
        $pancha = $result['pancha'];
        $sunEq  = $result['sunEq'];

        // Lagna info
        $lagnaVS   = AstroCalculator::getVedicSigns()[$result['ascSignIdx']];
        $ws        = AstroCalculator::getWesternSigns();
        $lagnaWS   = $ws[(int)floor($ascTrop / 30)];
        $naks      = AstroCalculator::getNakshatras();
        $lagnaHnak = $naks[(int)floor($result['ascSider'] / (360/27))];

       $jd = $result['jd'];
       $shadbala = ShadBalaCalculator::calculate(
    $planets, $ascSider, $jd, $lat, $angles,
    $hr, (int)(new \DateTime("{$yr}-{$mo}-{$dy}"))->format('w'),
    ($angles['asc'] > $planets['sun']['trop'] ?? true)
);
$shadBalaHtml = ShadBalaCalculator::renderHtml($shadbala);

// Vimshottari (full, not just balance)
$moonSider = fmod(fmod($planets['moon']['trop'] - $ayan, 360) + 360, 360);
$vimshottari = VimshottariDashaCalculator::calculate(
    $moonSider, $yr, $mo, $dy, $hr + $mn/60.0
);
$dashaHtml = VimshottariDashaCalculator::renderHtml($vimshottari);

        return response()->json([
            // ── D1 Chart (Rashi / birth chart) ──────────────────
            'chartHtml'         => $chartHtml,
            'houseSignsHtml'    => $houseSignsHtml,
            'planetSummaryHtml' => $planetSummaryHtml,

            // ── Shodashvarga (D2–D60 divisional charts) ─────────
            'vargaGridHtml'     => $vargaGridHtml,
            'vargaSummary'      => $vargaSummary,
            'planetVargaBadges' => $planetVargaBadges,

            // ── Lagna/Angles panel ───────────────────────────────
            'lagna' => [
                'ascTrop'    => AstroCalculator::dms($angles['asc']),
                'ascSider'   => AstroCalculator::dms($result['ascSider']),
                'ascWSign'   => $lagnaWS['s'] . ' ' . $lagnaWS['n'],
                'ascWDeg'    => AstroCalculator::dms(fmod($angles['asc'], 30)) . ' in sign',
                'ascVSign'   => $lagnaVS,
                'ascVDeg'    => AstroCalculator::dms(fmod($result['ascSider'], 30)) . ' in sign',
                'ascNak'     => $lagnaHnak['n'],
                'ascNakLord' => $lagnaHnak['l'],
                'ascStrip'   => "Lagna · {$lagnaWS['s']} {$lagnaWS['n']} / {$lagnaVS}",
                'ascStripSub'=> AstroCalculator::dms($angles['asc']) . ' Tropical · ' . AstroCalculator::dms($result['ascSider']) . ' Sidereal',
                'desc'       => $this->angleData($angles['desc'], $ayan, $naks, $ws),
                'mc'         => $this->angleData($angles['mc'],   $ayan, $naks, $ws),
                'ic'         => $this->angleData($angles['ic'],   $ayan, $naks, $ws),
                'ayanNote'   => number_format($ayan, 4) . '° · LST ' . number_format($angles['lst'], 4) . '° · ε ' . number_format($angles['eps'], 4) . '°',
            ],

            // ── Planet panels ────────────────────────────────────
            'planets' => $this->buildPlanetPanels($planets, $ayan, $ascTrop),

            // ── Sun extras ───────────────────────────────────────
            'sunDec' => AstroCalculator::dms(abs($sunEq['dec'])) . ($sunEq['dec'] >= 0 ? ' N' : ' S'),
            'sunRA'  => AstroCalculator::decToHMS($sunEq['ra'] / 15),

            // ── Sunrise/sunset ───────────────────────────────────
            'sunrise'   => $fmt['sunrise'],
            'sunset'    => $fmt['sunset'],
            'dayLength' => $fmt['dayLength'],

            // ── Tithi/Karana ─────────────────────────────────────
            'tk'    => $this->formatTK($tk),
            'tkRise'=> $tkRise ? $this->formatTK($tkRise) : null,
            'tkSet' => $tkSet  ? $this->formatTK($tkSet)  : null,
            'ssRise'=> ($ss && !$ss['polar'] && $ss['rise'] !== null) ? AstroCalculator::decToHMS($ss['rise']) : '—',
            'ssSet' => ($ss && !$ss['polar'] && $ss['set']  !== null) ? AstroCalculator::decToHMS($ss['set'])  : '—',

            // ── Panchanga ────────────────────────────────────────
            'pancha' => $this->formatPancha($pancha, $ss),

            // ── Dasha ────────────────────────────────────────────
            'dasha' => $dasha,

            // ── Raw values for client-side interactions ──────────
            'ayan'    => $ayan,
            'ascTrop' => $ascTrop,
            'shadBalaHtml' => $shadBalaHtml,
            'dashaHtml'    => $dashaHtml,
        ]);
    }

    // ── POST /astro/gochar — dynamic transit (date / month / year) ──────────
    public function gochar(Request $request)
    {
        $request->validate([
            'date'      => 'required|date_format:Y-m-d',   // natal birth date
            'time'      => 'required|date_format:H:i',
            'utcOffset' => 'required|numeric|min:-12|max:14',
            'lat'       => 'required|numeric|min:-90|max:90',
            'lon'       => 'required|numeric|min:-180|max:180',
            'mode'      => 'required|string|in:date,month,year',
            'target'    => 'required|date_format:Y-m-d',    // target/anchor date
        ]);

        [$nyr, $nmo, $ndy] = array_map('intval', explode('-', $request->date));
        [$nhr, $nmn]       = array_map('intval', explode(':', $request->time));
        $utcOff = (float)$request->utcOffset;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;
        $mode   = $request->mode;
        [$tyr, $tmo, $tdy] = array_map('intval', explode('-', $request->target));

        // ── Natal chart → Moon sign + natal planet signs (for aspects) ──
        $natal     = AstroCalculator::calculate($nyr, $nmo, $ndy, $nhr, $nmn, $utcOff, $lat, $lon);
        $nAyan     = $natal['ayan'];
        $natalMoon = (int)floor(fmod(fmod($natal['planets']['moon']['trop'] - $nAyan, 360) + 360, 360) / 30.0);
        $natalSigns = [];
        foreach ($natal['planets'] as $pid => $pd) {
            $sd = fmod(fmod($pd['trop'] - $nAyan, 360) + 360, 360);
            $natalSigns[$pid] = (int)floor($sd / 30.0);
        }

        if ($mode === 'date') {
            $jd      = TransitCalculator::localJd($tyr, $tmo, $tdy, 12.0, $utcOff);
            $details = TransitCalculator::planetDetails($jd, $utcOff);
            $aspects = TransitCalculator::natalAspects($details, $natalSigns);

            $tSigns  = AstroCalculator::getVedicSigns();
            $transit = [];
            foreach ($details as $pid => $d) {
                $transit[$pid] = ['sign' => $d['signIdx'], 'signName' => $tSigns[$d['signIdx']], 'retro' => $d['retro']];
            }
            $gocharData = GocharCalculator::calculate($natalMoon, $transit);

            $label = sprintf('%02d %s %d', $tdy, ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$tmo], $tyr);
            $html  = TransitCalculator::renderDate($details, $aspects, $label, $natalMoon, $utcOff)
                   . '<div style="margin-top:8px">' . GocharCalculator::renderHtml($gocharData) . '</div>';

            return response()->json(['html' => $html, 'label' => $label]);
        }

        if ($mode === 'month') {
            $lastDay = (int)(new \DateTime("$tyr-$tmo-01"))->format('t');
            $events  = TransitCalculator::rangeEvents(
                $tyr, $tmo, 1, $tyr, $tmo, $lastDay, $utcOff,
                ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu']
            );
            $label = ['','January','February','March','April','May','June','July','August','September','October','November','December'][$tmo] . ' ' . $tyr;
            $html  = TransitCalculator::renderCalendar($events, 'Transit Calendar — ' . $label, $utcOff, false);
            return response()->json(['html' => $html, 'label' => $label]);
        }

        // year
        $events = TransitCalculator::rangeEvents(
            $tyr, 1, 1, $tyr, 12, 31, $utcOff,
            ['sun','mars','mercury','venus','jupiter','saturn','rahu','ketu'] // Moon excluded (changes every ~2.25 days)
        );
        $html = TransitCalculator::renderCalendar($events, 'Yearly Transit Calendar — ' . $tyr, $utcOff, true);
        return response()->json(['html' => $html, 'label' => (string)$tyr]);
    }

    public function tarabalMurti(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = $request->validate([
            'yr'         => 'required|integer|min:1900|max:2100',
            'mo'         => 'required|integer|min:1|max:12',
            'dy'         => 'required|integer|min:1|max:31',
            'lat'        => 'required|numeric',
            'lon'        => 'required|numeric',
            'utcOff'     => 'required|numeric',
            'birthNak'   => 'nullable|integer|min:-1|max:26',
            'birthRashi' => 'nullable|integer|min:-1|max:11',
        ]);
 
        $yr         = (int)$v['yr'];
        $mo         = (int)$v['mo'];
        $dy         = (int)$v['dy'];
        $lat        = (float)$v['lat'];
        $lon        = (float)$v['lon'];
        $utcOff     = (float)$v['utcOff'];
        $birthNak   = (int)($v['birthNak']   ?? -1);
        $birthRashi = (int)($v['birthRashi'] ?? -1);
 
        // Compute Tarabal (includes full day data + tara result)
        $taraData = TarabalMurtiService::computeTarabal(
            $yr, $mo, $dy, $lat, $lon, $utcOff, $birthNak
        );
 
        // Merge Murti Nirnaya data
        $murtiData = TarabalMurtiService::computeMurtiNirnaya(
            $yr, $mo, $dy, $lat, $lon, $utcOff, $birthNak, $birthRashi
        );
 
        // Combined response
        $response = array_merge($taraData, [
            'murtiFormula'    => $murtiData['murtiFormula'],
            'murtiForAllVara' => $murtiData['murtiForAllVara'],
            'nakMuhurtaType'  => $murtiData['nakMuhurtaIdx'],
            'nakMuhurtaInfo'  => $murtiData['nakMuhurtaType'],
            'chandrabala'     => $murtiData['chandrabala'],
        ]);
 
        return response()->json($response);
    }

 

   public function muhrat(Request $request)
    {
        $request->validate([
            'year'         => 'required|integer|min:1900|max:2100',
            'month'        => 'required|integer|min:1|max:12',
            'day'          => 'required|integer|min:1|max:31',
            'lat'          => 'required|numeric|min:-90|max:90',
            'lon'          => 'required|numeric|min:-180|max:180',
            'utcOff'       => 'required|numeric|min:-12|max:14',
            'type'         => 'required|string|in:vivah,griha_pravesh,vahana,mundan,sampatti',
            'subtype'      => 'nullable|string|in:new,old',
            'girlRashiIdx' => 'nullable|integer|min:0|max:11',
            'boyRashiIdx'  => 'nullable|integer|min:0|max:11',
            'girlNakIdx'   => 'nullable|integer|min:0|max:26',
            'boyNakIdx'    => 'nullable|integer|min:0|max:26',
            'displayCat'   => 'nullable|string|max:40',
        ]);
 
        $data = MuhratCalculator::computeFullDay(
            (int)$request->year, (int)$request->month, (int)$request->day,
            (float)$request->lat, (float)$request->lon, (float)$request->utcOff,
            $request->type,
            [
                'subtype'      => $request->input('subtype', 'new'),
                'girlRashiIdx' => $request->input('girlRashiIdx'),
                'boyRashiIdx'  => $request->input('boyRashiIdx'),
                'girlNakIdx'   => $request->input('girlNakIdx'),
                'boyNakIdx'    => $request->input('boyNakIdx'),
            ]
        );
        $html = MuhratCalculator::buildResultHtml($data, $request->type);
        return response()->json(['html' => $html]);
    }
 
    // ── माह स्कैन ────────────────────────────────────────────
    public function muhratMonth(Request $request)
    {
        $request->validate([
            'year'         => 'required|integer|min:1900|max:2100',
            'month'        => 'required|integer|min:1|max:12',
            'lat'          => 'required|numeric|min:-90|max:90',
            'lon'          => 'required|numeric|min:-180|max:180',
            'utcOff'       => 'required|numeric|min:-12|max:14',
            'type'         => 'required|string|in:vivah,griha_pravesh,vahana,mundan,sampatti',
            'subtype'      => 'nullable|string|in:new,old',
            'girlRashiIdx' => 'nullable|integer|min:0|max:11',
            'boyRashiIdx'  => 'nullable|integer|min:0|max:11',
            'girlNakIdx'   => 'nullable|integer|min:0|max:26',
            'boyNakIdx'    => 'nullable|integer|min:0|max:26',
            'minScore'     => 'nullable|integer|min:0|max:100',
        ]);
 
        $mo    = (int)$request->month;
        $yr    = (int)$request->year;
        $dates = MuhratCalculator::scanMonth(
            $yr, $mo,
            (float)$request->lat, (float)$request->lon, (float)$request->utcOff,
            $request->type,
            [
                'subtype'      => $request->input('subtype', 'new'),
                'girlRashiIdx' => $request->input('girlRashiIdx'),
                'boyRashiIdx'  => $request->input('boyRashiIdx'),
                'girlNakIdx'   => $request->input('girlNakIdx'),
                'boyNakIdx'    => $request->input('boyNakIdx'),
                'minScore'     => $request->input('minScore', 40),
            ]
        );
        $html = MuhratCalculator::buildMonthHtml($dates, $request->type, $mo, $yr);
        return response()->json(['html' => $html, 'dates' => $dates, 'count' => count($dates)]);
    }
 
    // ── वर्ष स्कैन (सम्पूर्ण वर्ष एक साथ) ───────────────────
    public function muhratYear(Request $request)
    {
        $request->validate([
            'year'         => 'required|integer|min:1900|max:2100',
            'lat'          => 'required|numeric|min:-90|max:90',
            'lon'          => 'required|numeric|min:-180|max:180',
            'utcOff'       => 'required|numeric|min:-12|max:14',
            'type'         => 'required|string|in:vivah,griha_pravesh,vahana,mundan,sampatti',
            'girlRashiIdx' => 'nullable|integer|min:0|max:11',
            'boyRashiIdx'  => 'nullable|integer|min:0|max:11',
            'girlNakIdx'   => 'nullable|integer|min:0|max:26',
            'boyNakIdx'    => 'nullable|integer|min:0|max:26',
            'minScore'     => 'nullable|integer|min:0|max:100',
        ]);
 
        $yr      = (int)$request->year;
        $options = [
            'girlRashiIdx' => $request->input('girlRashiIdx'),
            'boyRashiIdx'  => $request->input('boyRashiIdx'),
            'girlNakIdx'   => $request->input('girlNakIdx'),
            'boyNakIdx'    => $request->input('boyNakIdx'),
            'minScore'     => $request->input('minScore', 40),
        ];
 
        // Build HTML for all 12 months
        $allDates = [];
        $allHtml  = '';
        $total    = 0;
        $moHi     = ['','जनवरी','फरवरी','मार्च','अप्रैल','मई','जून','जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
 
        for ($m = 1; $m <= 12; $m++) {
            $dates = MuhratCalculator::scanMonth(
                $yr, $m,
                (float)$request->lat, (float)$request->lon, (float)$request->utcOff,
                $request->type, $options
            );
            $allDates[$m] = $dates;
            $total += count($dates);
            $allHtml .= MuhratCalculator::buildMonthHtml($dates, $request->type, $m, $yr);
        }
 
        return response()->json([
            'html'       => $allHtml,
            'allDates'   => $allDates,
            'total'      => $total,
            'year'       => $yr,
        ]);
    }
 
  public function today(Request $request)
    {
        $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'time'      => 'required|date_format:H:i',
            'utcOffset' => 'required|numeric|min:-12|max:14',
            'lat'       => 'required|numeric|min:-90|max:90',
            'lon'       => 'required|numeric|min:-180|max:180',
        ]);
 
        [$yr, $mo, $dy] = array_map('intval', explode('-', $request->date));
        [$hr, $mn]      = array_map('intval', explode(':', $request->time));
 
        $data = TodayPanelService::build(
            $yr, $mo, $dy, $hr, $mn,
            (float)$request->utcOffset,
            (float)$request->lat,
            (float)$request->lon
        );
 
        $html = TodayPanelService::renderHtml($data);
        return response()->json(array_merge($data, ['html' => $html]));
    }

    // ── POST /astro/masa — monthly panchanga ───────────────────────
    public function masa(Request $request)
    {
        $request->validate([
            'year'   => 'required|integer|min:1900|max:2100',
            'vedMon' => 'required|integer|min:1|max:12',
            'lat'    => 'required|numeric',
            'lon'    => 'required|numeric',
            'utcOff' => 'required|numeric',
        ]);

        $data = AstroCalculator::buildMasaData(
            (int)$request->year,
            (int)$request->vedMon,
            (float)$request->lat,
            (float)$request->lon,
            (float)$request->utcOff
        );

        return response()->json($data);
    }

    // ── POST /astro/varga — single divisional chart (optional) ─────
    public function varga(Request $request)
    {
        $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'time'      => 'required|date_format:H:i',
            'utcOffset' => 'required|numeric|min:-12|max:14',
            'lat'       => 'required|numeric|min:-90|max:90',
            'lon'       => 'required|numeric|min:-180|max:180',
            'division'  => 'nullable|integer|in:1,2,3,4,5,6,7,8,9,10,11,12,16,20,24,27,30,40,45,60',
        ]);

        [$yr, $mo, $dy] = array_map('intval', explode('-', $request->date));
        [$hr, $mn]      = array_map('intval', explode(':', $request->time));
        $utcOff = (float)$request->utcOffset;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;

        $result   = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);
        $planets  = $result['planets'];
        $ascSider = $result['ascSider'];

        if ($request->filled('division')) {
            $d      = (int)$request->division;
            $points = ['ascendant' => $ascSider];
            foreach ($planets as $pid => $pdata) {
                $points[$pid] = $pdata['sider'];
            }
            $vargaData = ShodashvargaCalculator::calculateVarga($d, $points, $planets);
            $chartHtml = VargaChartRenderer::renderSingleVarga($vargaData);
            return response()->json([
                'division'  => $d,
                'name'      => $vargaData['name'],
                'chartHtml' => $chartHtml,
                'planets'   => $vargaData['planets'],
            ]);
        }

        $allVargas    = ShodashvargaCalculator::calculateAll($planets, $ascSider);
        $vargaSummary = ShodashvargaCalculator::buildSummary($allVargas);
        return response()->json([
            'vargaGridHtml' => VargaChartRenderer::renderVargaGrid($allVargas, $ascSider),
            'vargaSummary'  => $vargaSummary,
        ]);
    }

 
public function festivals(Request $request)
{
    $request->validate([
        'year'     => 'required|integer|min:1900|max:2100',
        'lat'      => 'required|numeric|min:-90|max:90',
        'lon'      => 'required|numeric|min:-180|max:180',
        'utcOff'   => 'required|numeric|min:-12|max:14',
        'category' => 'nullable|string|max:50',
    ]);
 
    $calData = HinduFestivalCalculator::calculateYear(
        (int)$request->year,
        (float)$request->lat,
        (float)$request->lon,
        (float)$request->utcOff
    );
 
    $festivals = $calData['festivals'] ?? [];
    $category  = $request->input('category', 'all');
 
    // ── HinduFestivalCalculator::renderHtml() renders ALL card HTML ──
    // The JS in index.blade.php just injects data.html into #festivalContent
    $html = HinduFestivalCalculator::renderHtml($festivals, $category);
 
    return response()->json([
        'html'      => $html,          // PHP-rendered card grid
        'festivals' => $festivals,     // raw data for JS state cache
        'count'     => $calData['count'] ?? count($festivals),
        'year'      => (int)$request->year,
        'category'  => $category,
    ]);
}   

    // ── GET /astro/city — geocode city name ────────────────────────
    public function city(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);

        $query = trim($request->name);
        $url   = 'https://geocoding-api.open-meteo.com/v1/search?' . http_build_query([
            'name'     => $query,
            'count'    => 5,
            'language' => 'en',
            'format'   => 'json',
        ]);

        $json = @file_get_contents($url);
        if (!$json) return response()->json(['error' => 'Geocoding failed'], 502);

        $data = json_decode($json, true);
        if (empty($data['results'])) return response()->json(['error' => 'City not found'], 404);

        $results = array_slice($data['results'], 0, 5);
        $primary = $results[0];

        $formatted = array_map(fn($g) => [
            'name'     => $g['name'],
            'state'    => $g['admin1'] ?? '',
            'country'  => $g['country'] ?? '',
            'lat'      => $g['latitude'],
            'lon'      => $g['longitude'],
            'timezone' => $g['timezone'] ?? 'UTC',
            'display'  => trim(implode(', ', array_filter([
                              $g['name'], $g['admin1'] ?? '', $g['country'] ?? '',
                          ]))),
        ], $results);

        return response()->json([
            'lat'      => $primary['latitude'],
            'lon'      => $primary['longitude'],
            'timezone' => $primary['timezone'] ?? 'UTC',
            'state'    => $primary['admin1'] ?? '',
            'country'  => $primary['country'] ?? '',
            'results'  => $formatted,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function angleData(float $trop, float $ayan, array $naks, array $ws): array
    {
        $n360  = fn(float $x) => fmod(fmod($x, 360) + 360, 360);
        $sider = $n360($trop - $ayan);
        $wSign = $ws[(int)floor($trop / 30)];
        $vSigns= AstroCalculator::getVedicSigns();
        $vSign = $vSigns[(int)floor($sider / 30)];
        $nak   = $naks[(int)floor($sider / (360/27))];
        return [
            'trop'    => AstroCalculator::dms($trop),
            'sider'   => AstroCalculator::dms($sider),
            'wSign'   => $wSign['s'] . ' ' . $wSign['n'],
            'wDeg'    => AstroCalculator::dms(fmod($trop, 30)) . ' in sign',
            'vSign'   => $vSign,
            'vDeg'    => AstroCalculator::dms(fmod($sider, 30)) . ' in sign',
            'nakName' => $nak['n'],
            'nakLord' => $nak['l'],
        ];
    }

    private function buildPlanetPanels(array $planets, float $ayan, float $ascTrop): array
    {
        $n360   = fn(float $x) => fmod(fmod($x, 360) + 360, 360);
        $naks   = AstroCalculator::getNakshatras();
        $vSigns = AstroCalculator::getVedicSigns();
        $ws     = AstroCalculator::getWesternSigns();
        $ascSider = $n360($ascTrop - $ayan);

        $HOUSE_NAMES = [
            '','Self & Body','Wealth & Family','Courage & Siblings',
            'Home & Mother','Children & Intellect','Enemies & Health',
            'Partnerships','Transformation','Dharma & Fortune',
            'Career & Status','Gains & Desires','Losses & Liberation',
        ];

        $out = [];
        foreach ($planets as $pid => $p) {
            $trop  = $p['trop'];
            $sider = $p['sider'];
            $nak   = $naks[(int)floor($sider / (360/27))];
            $np    = fmod($sider, 360/27) / (360/27);
            $pada  = (int)floor($np * 4) + 1;
            $vi    = (int)floor($sider / 30);
            $wSign = $ws[(int)floor($trop / 30)];
            $house = (int)floor($n360($sider - $ascSider) / 30) + 1;

            $out[$pid] = [
                'stripTitle' => $nak['n'] . ($p['retro'] ? '  *' : ''),
                'stripSub'   => $vSigns[$vi] . ' (Vedic) · Pada ' . $pada . ($p['retro'] ? ' *' : ''),
                'stripLon'   => AstroCalculator::dms($trop),
                'vSign'      => $vSigns[$vi],
                'vDeg'       => AstroCalculator::dms(fmod($sider, 30)) . ' in sign',
                'wSign'      => $wSign['s'] . ' ' . $wSign['n'],
                'wDeg'       => AstroCalculator::dms(fmod($trop, 30)) . ' in sign',
                'nakName'    => $nak['n'],
                'nakPada'    => 'Pada ' . $pada . ' of 4  ·  ' . number_format($np * 100, 1) . '% through',
                'nakLord'    => $nak['l'],
                'nakDeity'   => $nak['d'],
                'nakProg'    => round($np * 100, 1),
                'retro'      => $p['retro'] ? '* Retrograde' : 'Direct ↻',
                'house'      => AstroCalculator::ordinal($house) . ' House',
                'houseSig'   => $HOUSE_NAMES[$house] ?? '',
                'ayanNote'   => number_format($ayan, 4) . '° · Tropical ' . number_format($trop, 4) . '° · Sidereal ' . number_format($sider, 4) . '°',
            ];
        }
        return $out;
    }

    private function formatTK(array $tk): array
    {
        return [
            'stripTitle'   => $tk['tithi']['paksha'] . ' ' . $tk['tithi']['n'] . ' · ' . $tk['karana']['n'],
            'stripSub'     => $tk['tithi']['paksha'] . ' Paksha · Tithi ' . $tk['tithi']['num'] . ' of 15 · Karana ' . $tk['karanaSlot'] . ' of 60',
            'stripElong'   => number_format($tk['elong'], 2) . '°',
            'tithiName'    => $tk['tithi']['n'],
            'tithiPaksha'  => $tk['tithi']['paksha'] . ' Paksha',
            'tithiNum'     => $tk['tithi']['num'] . ' / 15',
            'tithiLord'    => $tk['tithi']['lord'],
            'tithiDeity'   => $tk['tithi']['deity'],
            'tithiNature'  => $tk['tithi']['nature'],
            'tithiProg'    => round($tk['tithiProg'] * 100, 1),
            'karanaName'   => $tk['karana']['n'],
            'karanaType'   => $tk['karana']['type'] . ' · ' . $tk['tithiHalf'],
            'karanaLord'   => $tk['karana']['lord'],
            'karanaNature' => $tk['karana']['nature'],
            'karanaSlot'   => $tk['karanaSlot'] . ' / 60',
            'karanaFavour' => $tk['karana']['favour'] ?? '—',
            'karanaClass'  => $tk['karana']['cls'] ?? $tk['karana']['type'],
            'karanaProg'   => round($tk['karanaProg'] * 100, 1),
            'karanaDeity'  => $tk['karana']['deity'] ?? $tk['karana']['lord'],
            'tithiHalf'    => $tk['tithiHalf'],
            'elong'        => $tk['elong'],
            'moonLon'      => $tk['moonLon'],
            'sunLon'       => $tk['sunLon'],
            'karanaSlotNum'=> $tk['karanaSlot'],
            'pakshaShort'  => substr($tk['tithi']['paksha'], 0, 3),
        ];
    }

    private function formatPancha(array $pancha, ?array $ss): array
    {
        $vara    = $pancha['vara'];
        $moonNak = $pancha['moonNak'];
        $yoga    = $pancha['yoga'];

        $ganaSub = match($moonNak['gana']) {
            'Deva'     => 'Divine · Spiritual temperament',
            'Manushya' => 'Human · Worldly temperament',
            default    => 'Demonic · Fierce temperament',
        };
        $nadiSub = match($moonNak['nadi']) {
            'Vata'  => 'Air — quick, changeable',
            'Pitta' => 'Fire — intense, sharp',
            default => 'Water — slow, steady',
        };
        $yogaClassSub = match($yoga['cls']) {
            'Mahavisha' => 'Highly inauspicious — avoid new starts',
            'Ashubha'   => 'Inauspicious — proceed carefully',
            default     => 'Auspicious — favourable for activity',
        };
        $varaAusp = match($vara['classification']) {
            'Guru'   => 'Very Auspicious ✦✦✦',
            'Saumya' => 'Auspicious ✦✦',
            default  => 'Use with Intention ✦',
        };

        $rise = '—'; $set = '—'; $dayLen = '—';
        if ($ss && !$ss['polar']) {
            $rise   = $ss['rise'] !== null ? AstroCalculator::decToHMS($ss['rise']) : '—';
            $set    = $ss['set']  !== null ? AstroCalculator::decToHMS($ss['set'])  : '—';
            $dlH    = (int)$ss['dayLength'];
            $dlM    = (int)round(($ss['dayLength'] - $dlH) * 60);
            $dayLen = "{$dlH}h {$dlM}m";
        } elseif ($ss) {
            $rise   = $ss['polar'] === 'no_rise' ? 'Polar Night' : 'Midnight Sun';
            $dayLen = $ss['polar'] === 'no_rise' ? '0h' : '24h';
        }

        return [
            'varaName'      => $vara['n'],
            'varaEn'        => $vara['en'],
            'varaLord'      => $vara['lord'],
            'varaNature'    => $vara['nature'],
            'varaSym'       => $vara['sym'],
            'varaColor'     => $vara['color'],
            'varaHora'      => $vara['horaLord'],
            'varaClass'     => $vara['classification'],
            'varaClassNote' => $vara['classNote'],
            'varaDeity'     => $vara['deity'],
            'varaDNote'     => $vara['deityNote'],
            'varaAusp'      => $varaAusp,
            'varaAct'       => $vara['auspicious'],
            'varaInfo'      => $vara['info'],
            'varaStripTitle'=> $vara['n'] . ' · ' . $vara['lord'],
            'varaStripSub'  => $vara['classification'] . ' Vara · ' . $vara['nature'],
            'nakName'       => $moonNak['n'],
            'nakLord'       => $moonNak['l'],
            'nakDeity'      => $moonNak['d'],
            'nakPada'       => 'Pada ' . $pancha['nakPada'] . ' of 4',
            'nakNum'        => 'Nakshatra ' . ($pancha['nakIdx'] + 1) . ' of 27',
            'nakProg'       => round($pancha['nakProg'] * 100, 1),
            'nakGana'       => $moonNak['gana'],
            'nakGanaSub'    => $ganaSub,
            'nakYoni'       => $moonNak['yoni'],
            'nakNadi'       => $moonNak['nadi'],
            'nakNadiSub'    => $nadiSub,
            'nakTattva'     => $moonNak['tattva'],
            'nakQuality'    => $moonNak['quality'],
            'nakInfo'       => 'Deity: ' . $moonNak['d'] . ' · Lord: ' . $moonNak['l']
                             . ' · Gana: ' . $moonNak['gana'] . ' · Yoni: ' . $moonNak['yoni']
                             . ' · Nadi: ' . $moonNak['nadi'] . ' Dosha · Element: ' . $moonNak['tattva']
                             . '. Quality: ' . $moonNak['quality'] . '.',
            'nakStripTitle' => $moonNak['n'] . ' Nakshatra',
            'nakStripSub'   => 'Lord: ' . $moonNak['l'] . ' · Deity: ' . $moonNak['d'] . ' · Pada ' . $pancha['nakPada'],
            'nakStripNum'   => ($pancha['nakIdx'] + 1) . ' / 27',
            'yogaName'      => $yoga['n'],
            'yogaNature'    => $yoga['nature'],
            'yogaLord'      => $yoga['lord'],
            'yogaNum'       => 'Yoga ' . ($pancha['yogaIdx'] + 1) . ' of 27',
            'yogaDeity'     => $yoga['deity'],
            'yogaClass'     => $yoga['cls'],
            'yogaClassSub'  => $yogaClassSub,
            'yogaSum'       => number_format($pancha['yogaSum'], 2) . '°',
            'yogaProg'      => round($pancha['yogaProg'] * 100, 1),
            'yogaInfo'      => $yoga['desc'] . ' Deity: ' . $yoga['deity'] . '. Lord: ' . $yoga['lord'] . '.',
            'yogaStripTitle'=> $yoga['n'] . ' Yoga',
            'yogaStripSub'  => $yoga['nature'] . ' · Lord: ' . $yoga['lord'] . ' · ' . $yoga['cls'],
            'yogaStripNum'  => ($pancha['yogaIdx'] + 1) . ' / 27',
            'varaIdx'       => $pancha['varaIdx'],
            'nakIdx'        => $pancha['nakIdx'],
            'yogaIdx'       => $pancha['yogaIdx'],
            'sumVara'       => preg_replace('/vara$/i', '', $vara['n']),
            'sumNak'        => $moonNak['n'],
            'sumYoga'       => $yoga['n'],
            'rise'          => $rise,
            'set'           => $set,
            'dayLen'        => $dayLen,
        ];
    }

    private function formatResult(array $result): array
    {
        $ss = $result['ss'];
        if (!$ss['polar']) {
            $rise   = $ss['rise'] !== null ? AstroCalculator::decToHMS($ss['rise']) : '—';
            $set    = $ss['set']  !== null ? AstroCalculator::decToHMS($ss['set'])  : '—';
            $dlH    = (int)$ss['dayLength'];
            $dlM    = (int)round(($ss['dayLength'] - $dlH) * 60);
            $dayLen = "{$dlH}h {$dlM}m";
        } else {
            $rise   = $ss['polar'] === 'no_rise' ? 'Polar Night' : 'Midnight Sun';
            $set    = '—';
            $dayLen = $ss['polar'] === 'no_rise' ? '0h' : '24h';
        }
        return ['sunrise' => $rise, 'sunset' => $set, 'dayLength' => $dayLen];
    }

    // In AstroController.php — add this method
public function ekadashiYear(Request $request)
{
    $request->validate([
        'year'   => 'required|integer|min:1900|max:2100',
        'lat'    => 'required|numeric|min:-90|max:90',
        'lon'    => 'required|numeric|min:-180|max:180',
        'utcOff' => 'required|numeric|min:-12|max:14',
    ]);

    $ekadashis = AstroCalculator::getEkadashiYear(
        (int)$request->year,
        (float)$request->lat,
        (float)$request->lon,
        (float)$request->utcOff
    );

    return response()->json([
        'ekadashis' => $ekadashis,
        'count'     => count($ekadashis),
        'year'      => (int)$request->year,
    ]);
}
}