<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Planetary\AstroCalculator;
use App\Services\ChartRendering\AstroChartRenderer;
use App\Services\ChartRendering\ShodashvargaCalculator;
use App\Services\ChartRendering\VargaChartRenderer;
use App\Services\Planetary\ShadBalaCalculator;
use App\Services\Dasha\VimshottariDashaCalculator;
use App\Services\Festival\TodayPanelService;
use App\Services\Kundali\KundaliService;

class AstroController extends Controller
{
    // ── GET /astro — main page ────────────────────────────────────
    public function index()
    {
        $rashis     = DB::table('zodiac_signs')->orderBy('id')->pluck('name')->toArray();
        $nakshatras = DB::table('nakshatras')->orderBy('id')->pluck('name')->toArray();

        return view('index', compact('rashis', 'nakshatras'));
    }

    // ── POST /astro/calculate — Phase 1: core chart (fast) ───────
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

        // Cache key — same inputs always reuse the raw calculation
        $cacheKey = 'astro_' . md5("{$yr}{$mo}{$dy}{$hr}{$mn}{$utcOff}{$lat}{$lon}");

        $result = Cache::remember($cacheKey . '_raw', 86400, function () use ($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon) {
            return AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);
        });

        // Store input params so panels() can use them without re-parsing
        Cache::put($cacheKey . '_meta', compact('yr', 'mo', 'dy', 'hr', 'mn', 'utcOff', 'lat', 'lon'), 86400);

        $ayan     = $result['ayan'];
        $planets  = $result['planets'];
        $ascTrop  = $result['ascTrop'];
        $ascSider = $result['ascSider'];
        $dasha    = $result['dasha'];
        $angles   = $result['angles'];
        $ss       = $result['ss'];
        $tk       = $result['tk'];
        $tkRise   = $result['tkRise'];
        $tkSet    = $result['tkSet'];
        $pancha   = $result['pancha'];
        $sunEq    = $result['sunEq'];
        $jd       = $result['jd'];

        // ── D1 Rashi chart ────────────────────────────────────────
        $chartHtml = view('partials.chart._main_chart', [
            'data' => AstroChartRenderer::prepareForView($ascTrop, $planets, $ayan, $dasha),
        ])->render();

        // ── House signs & planet summary ──────────────────────────
        $signToHouse = array_fill(0, 12, 0);
        foreach ($result['houseSign'] as $h => $sign) {
            $signToHouse[$sign] = $h + 1;
        }
        $n360 = fn(float $x) => fmod(fmod($x, 360) + 360, 360);
        $housePlanetsArr = array_fill(0, 12, []);
        foreach ($planets as $pid => $pdata) {
            $pSignIdx = (int)floor($n360($pdata['trop'] - $ayan) / 30);
            $house    = $signToHouse[$pSignIdx];
            if ($house >= 1) {
                $housePlanetsArr[$house - 1][] = ['pid' => $pid, 'retro' => $pdata['retro']];
            }
        }
        $houseSignsHtml    = view('partials.chart._house_signs', [
            'data' => AstroChartRenderer::buildHouseSignsData($result['ascSignIdx'], $housePlanetsArr),
        ])->render();
        $planetSummaryHtml = view('partials.chart._planet_summary', [
            'data' => AstroChartRenderer::buildPlanetSummaryData($planets, $ayan, $signToHouse),
        ])->render();

        // ── Lagna info ────────────────────────────────────────────
        $ws        = AstroCalculator::getWesternSigns();
        $lagnaWS   = $ws[(int)floor($ascTrop / 30)];
        $lagnaVS   = AstroCalculator::getVedicSigns()[$result['ascSignIdx']];
        $naks      = AstroCalculator::getNakshatras();
        $lagnaHnak = $naks[(int)floor($result['ascSider'] / (360 / 27))];

        $fmt = $this->formatResult($ss);

        return response()->json([
            // Phase 1 flag — frontend uses this to fetch panels in Phase 2
            'panelsKey' => $cacheKey,

            // D1 chart
            'chartHtml'         => $chartHtml,
            'houseSignsHtml'    => $houseSignsHtml,
            'planetSummaryHtml' => $planetSummaryHtml,

            // Lagna / angles
            'lagna' => [
                'ascTrop'     => AstroCalculator::dms($angles['asc']),
                'ascSider'    => AstroCalculator::dms($result['ascSider']),
                'ascWSign'    => $lagnaWS['s'] . ' ' . $lagnaWS['n'],
                'ascWDeg'     => AstroCalculator::dms(fmod($angles['asc'], 30)) . ' in sign',
                'ascVSign'    => $lagnaVS,
                'ascVDeg'     => AstroCalculator::dms(fmod($result['ascSider'], 30)) . ' in sign',
                'ascNak'      => $lagnaHnak['n'],
                'ascNakLord'  => $lagnaHnak['l'],
                'ascStrip'    => "Lagna · {$lagnaWS['s']} {$lagnaWS['n']} / {$lagnaVS}",
                'ascStripSub' => AstroCalculator::dms($angles['asc']) . ' Tropical · ' . AstroCalculator::dms($result['ascSider']) . ' Sidereal',
                'desc'        => $this->angleData($angles['desc'], $ayan, $naks, $ws),
                'mc'          => $this->angleData($angles['mc'],   $ayan, $naks, $ws),
                'ic'          => $this->angleData($angles['ic'],   $ayan, $naks, $ws),
                'ayanNote'    => number_format($ayan, 4) . '° · LST ' . number_format($angles['lst'], 4) . '° · ε ' . number_format($angles['eps'], 4) . '°',
            ],

            // Planet panels
            'planets' => $this->buildPlanetPanels($planets, $ayan, $ascTrop),

            // Sun extras
            'sunDec' => AstroCalculator::dms(abs($sunEq['dec'])) . ($sunEq['dec'] >= 0 ? ' N' : ' S'),
            'sunRA'  => AstroCalculator::decToHMS($sunEq['ra'] / 15),

            // Sunrise / sunset
            'sunrise'   => $fmt['sunrise'],
            'sunset'    => $fmt['sunset'],
            'dayLength' => $fmt['dayLength'],

            // Tithi / Karana
            'tk'     => $this->formatTK($tk),
            'tkRise' => $tkRise ? $this->formatTK($tkRise) : null,
            'tkSet'  => $tkSet  ? $this->formatTK($tkSet)  : null,
            'ssRise' => ($ss && !$ss['polar'] && $ss['rise'] !== null) ? AstroCalculator::decToHMS($ss['rise']) : '—',
            'ssSet'  => ($ss && !$ss['polar'] && $ss['set']  !== null) ? AstroCalculator::decToHMS($ss['set'])  : '—',

            // Panchanga
            'pancha' => $this->formatPancha($pancha, $ss),

            // Dasha balance
            'dasha' => $dasha,

            // Raw values
            'ayan'    => $ayan,
            'ascTrop' => $ascTrop,
        ]);
    }

    // ── POST /astro/panels — Phase 2: heavy panels (deferred) ────
    public function panels(Request $request)
    {
        $request->validate(['key' => 'required|string|size:38']);

        $cacheKey = $request->input('key');
        $result   = Cache::get($cacheKey . '_raw');

        if (!$result) {
            return response()->json(['error' => 'Session expired — please recalculate.'], 410);
        }

        // Return cached panels immediately if available (same inputs = same output)
        $cached = Cache::get($cacheKey . '_panels');
        if ($cached) {
            return response()->json($cached);
        }

        try {
            $ayan     = $result['ayan'];
            $planets  = $result['planets'];
            $ascSider = $result['ascSider'];
            $angles   = $result['angles'];
            $ss       = $result['ss'];
            $tk       = $result['tk'];
            $pancha   = $result['pancha'];
            $jd       = $result['jd'];

            $meta   = Cache::get($cacheKey . '_meta', []);
            $yr     = $meta['yr']     ?? (int)date('Y');
            $mo     = $meta['mo']     ?? (int)date('m');
            $dy     = $meta['dy']     ?? (int)date('d');
            $hr     = $meta['hr']     ?? 12;
            $mn     = $meta['mn']     ?? 0;
            $lat    = $meta['lat']    ?? 28.6;
            $lon    = $meta['lon']    ?? 77.2;
            $utcOff = $meta['utcOff'] ?? 5.5;

            // ── Shodashvarga ──────────────────────────────────────
            $allVargas     = ShodashvargaCalculator::calculateAll($planets, $ascSider);
            $vargaGridData = VargaChartRenderer::prepareVargaGridData($allVargas, $ascSider);
            $vargaGridHtml = view('partials.chart._varga_grid', [
                'chartSvg' => $vargaGridData['chartSvg'],
                'dm'       => $vargaGridData['dignityMatrix'],
            ])->render();
            $vargaSummary      = ShodashvargaCalculator::buildSummary($allVargas);
            $planetVargaBadges = [];
            foreach (array_keys($planets) as $pid) {
                $planetVargaBadges[$pid] = view('partials.chart._planet_varga_badges', [
                    'data' => VargaChartRenderer::buildPlanetVargaSummaryData($allVargas, $pid),
                ])->render();
            }

            // ── Shadbala ──────────────────────────────────────────
            $moonSider = fmod(fmod($planets['moon']['trop'] - $ayan, 360) + 360, 360);
            $shadbala  = ShadBalaCalculator::calculate(
                $planets, $ascSider, $jd, $lat, $angles, $hr,
                (int)(new \DateTime("{$yr}-{$mo}-{$dy}"))->format('w'),
                ($angles['asc'] > ($planets['sun']['trop'] ?? 0))
            );
            $shadBalaHtml = view('partials.planetary._shadbala',
                ['data' => ShadBalaCalculator::prepareForView($shadbala)])->render();

            // ── Vimshottari Dasha ─────────────────────────────────
            $vimshottari = VimshottariDashaCalculator::calculate($moonSider, $yr, $mo, $dy, $hr + $mn / 60.0);
            $dashaHtml   = view('partials.dasha._vimshottari',
                ['data' => VimshottariDashaCalculator::prepareForView($vimshottari)])->render();

            // ── Today panel ───────────────────────────────────────
            $todayHtml = '';
            try {
                $todayData = TodayPanelService::prepareForView(
                    TodayPanelService::buildFromResult($result, $yr, $mo, $dy, $hr, $mn, $lat, $lon, $utcOff)
                );
                $todayHtml = view('partials.festival._today', ['d' => $todayData])->render();
            } catch (\Throwable $e) {
                $todayHtml = '<p style="padding:16px;color:red">Today error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }

            // ── Kundali ───────────────────────────────────────────
            $kundaliHtml = '';
            try {
                $kundaliData  = KundaliService::calculate(
                    $planets, $ascSider, $jd,
                    $yr, $mo, $dy, $hr, $mn,
                    $utcOff, $lat, $lon,
                    $shadbala, $angles, $pancha, $vimshottari
                );
                $vedicSigns   = AstroCalculator::getVedicSigns();
                $moonSignName = $vedicSigns[(int)floor($moonSider / 30)] ?? '—';
                $panchaK = [
                    'tithi'     => ($tk['tithi']['paksha'] ?? '') . ' ' . ($tk['tithi']['n'] ?? '—'),
                    'vara'      => $pancha['vara']['n']    ?? '—',
                    'nakshatra' => $pancha['moonNak']['n'] ?? '—',
                    'yoga'      => $pancha['yoga']['n']    ?? '—',
                    'karana'    => $tk['karana']['n']      ?? '—',
                    'paksha'    => $tk['tithi']['paksha']  ?? '—',
                    'moonSign'  => $moonSignName,
                    'sunRise'   => ($ss && !($ss['polar'] ?? false) && isset($ss['rise']))
                                    ? AstroCalculator::decToHMS($ss['rise']) : '—',
                    'sunSet'    => ($ss && !($ss['polar'] ?? false) && isset($ss['set']))
                                    ? AstroCalculator::decToHMS($ss['set'])  : '—',
                ];
                $kundaliHtml = view('partials.kundali._panel_kundali', [
                    'k'            => $kundaliData,
                    'shadBalaHtml' => $shadBalaHtml,
                    'dashaHtml'    => $dashaHtml,
                    'pancha'       => $panchaK,
                ])->render();
            } catch (\Throwable $e) {
                $kundaliHtml = '<p style="padding:16px;color:red">Kundali error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }

            $payload = [
                'vargaGridHtml'     => $vargaGridHtml,
                'vargaSummary'      => $vargaSummary,
                'planetVargaBadges' => $planetVargaBadges,
                'shadBalaHtml'      => $shadBalaHtml,
                'dashaHtml'         => $dashaHtml,
                'todayHtml'         => $todayHtml,
                'kundaliHtml'       => $kundaliHtml,
            ];

            Cache::put($cacheKey . '_panels', $payload, 86400);

            return response()->json($payload);

        } catch (\Throwable $e) {
            \Log::error('panels() fatal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── POST /astro/masa — monthly panchanga ──────────────────────
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

    // ── POST /astro/varga — divisional chart ──────────────────────
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

        // Reuse Phase 1 cache so clicking individual varga charts is instant
        $rawKey  = 'astro_' . md5("{$yr}{$mo}{$dy}{$hr}{$mn}{$utcOff}{$lat}{$lon}");
        $result  = Cache::remember($rawKey . '_raw', 300,
            fn() => AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon)
        );
        $planets  = $result['planets'];
        $ascSider = $result['ascSider'];

        if ($request->filled('division')) {
            $d      = (int)$request->division;
            $points = ['ascendant' => $ascSider];
            foreach ($planets as $pid => $pdata) {
                $points[$pid] = $pdata['sider'];
            }
            $vargaData = ShodashvargaCalculator::calculateVarga($d, $points, $planets);
            $chartHtml = view('partials.chart._single_varga', [
                'data' => VargaChartRenderer::prepareSingleVargaData($vargaData),
            ])->render();
            return response()->json([
                'division'  => $d,
                'name'      => $vargaData['name'],
                'chartHtml' => $chartHtml,
                'planets'   => $vargaData['planets'],
            ]);
        }

        $allVargas     = ShodashvargaCalculator::calculateAll($planets, $ascSider);
        $vargaGridData = VargaChartRenderer::prepareVargaGridData($allVargas, $ascSider);
        $vargaSummary  = ShodashvargaCalculator::buildSummary($allVargas);
        return response()->json([
            'vargaGridHtml' => view('partials.chart._varga_grid', [
                'chartSvg' => $vargaGridData['chartSvg'],
                'dm'       => $vargaGridData['dignityMatrix'],
            ])->render(),
            'vargaSummary'  => $vargaSummary,
        ]);
    }

    // ── GET /astro/city — geocoding ───────────────────────────────
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
        $vSign = AstroCalculator::getVedicSigns()[(int)floor($sider / 30)];
        $nak   = $naks[(int)floor($sider / (360 / 27))];
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
            '', 'Self & Body', 'Wealth & Family', 'Courage & Siblings',
            'Home & Mother', 'Children & Intellect', 'Enemies & Health',
            'Partnerships', 'Transformation', 'Dharma & Fortune',
            'Career & Status', 'Gains & Desires', 'Losses & Liberation',
        ];

        $out = [];
        foreach ($planets as $pid => $p) {
            $trop  = $p['trop'];
            $sider = $p['sider'];
            $nak   = $naks[(int)floor($sider / (360 / 27))];
            $np    = fmod($sider, 360 / 27) / (360 / 27);
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
            'stripTitle'    => $tk['tithi']['paksha'] . ' ' . $tk['tithi']['n'] . ' · ' . $tk['karana']['n'],
            'stripSub'      => $tk['tithi']['paksha'] . ' Paksha · Tithi ' . $tk['tithi']['num'] . ' of 15 · Karana ' . $tk['karanaSlot'] . ' of 60',
            'stripElong'    => number_format($tk['elong'], 2) . '°',
            'tithiName'     => $tk['tithi']['n'],
            'tithiPaksha'   => $tk['tithi']['paksha'] . ' Paksha',
            'tithiNum'      => $tk['tithi']['num'] . ' / 15',
            'tithiLord'     => $tk['tithi']['lord'],
            'tithiDeity'    => $tk['tithi']['deity'],
            'tithiNature'   => $tk['tithi']['nature'],
            'tithiProg'     => round($tk['tithiProg'] * 100, 1),
            'karanaName'    => $tk['karana']['n'],
            'karanaType'    => $tk['karana']['type'] . ' · ' . $tk['tithiHalf'],
            'karanaLord'    => $tk['karana']['lord'],
            'karanaNature'  => $tk['karana']['nature'],
            'karanaSlot'    => $tk['karanaSlot'] . ' / 60',
            'karanaFavour'  => $tk['karana']['favour'] ?? '—',
            'karanaClass'   => $tk['karana']['cls'] ?? $tk['karana']['type'],
            'karanaProg'    => round($tk['karanaProg'] * 100, 1),
            'karanaDeity'   => $tk['karana']['deity'] ?? $tk['karana']['lord'],
            'tithiHalf'     => $tk['tithiHalf'],
            'elong'         => $tk['elong'],
            'moonLon'       => $tk['moonLon'],
            'sunLon'        => $tk['sunLon'],
            'karanaSlotNum' => $tk['karanaSlot'],
            'pakshaShort'   => substr($tk['tithi']['paksha'], 0, 3),
        ];
    }

    private function formatPancha(array $pancha, ?array $ss): array
    {
        $vara    = $pancha['vara'];
        $moonNak = $pancha['moonNak'];
        $yoga    = $pancha['yoga'];

        $ganaSub = match ($moonNak['gana']) {
            'Deva'     => 'Divine · Spiritual temperament',
            'Manushya' => 'Human · Worldly temperament',
            default    => 'Demonic · Fierce temperament',
        };
        $nadiSub = match ($moonNak['nadi']) {
            'Vata'  => 'Air — quick, changeable',
            'Pitta' => 'Fire — intense, sharp',
            default => 'Water — slow, steady',
        };
        $yogaClassSub = match ($yoga['cls']) {
            'Mahavisha' => 'Highly inauspicious — avoid new starts',
            'Ashubha'   => 'Inauspicious — proceed carefully',
            default     => 'Auspicious — favourable for activity',
        };
        $varaAusp = match ($vara['classification']) {
            'Guru'   => 'Very Auspicious ✦✦✦',
            'Saumya' => 'Auspicious ✦✦',
            default  => 'Use with Intention ✦',
        };

        $fmt = $this->formatResult($ss);

        return [
            'varaName'       => $vara['n'],
            'varaEn'         => $vara['en'],
            'varaLord'       => $vara['lord'],
            'varaNature'     => $vara['nature'],
            'varaSym'        => $vara['sym'],
            'varaColor'      => $vara['color'],
            'varaHora'       => $vara['horaLord'],
            'varaClass'      => $vara['classification'],
            'varaClassNote'  => $vara['classNote'],
            'varaDeity'      => $vara['deity'],
            'varaDNote'      => $vara['deityNote'],
            'varaAusp'       => $varaAusp,
            'varaAct'        => $vara['auspicious'],
            'varaInfo'       => $vara['info'],
            'varaStripTitle' => $vara['n'] . ' · ' . $vara['lord'],
            'varaStripSub'   => $vara['classification'] . ' Vara · ' . $vara['nature'],
            'nakName'        => $moonNak['n'],
            'nakLord'        => $moonNak['l'],
            'nakDeity'       => $moonNak['d'],
            'nakPada'        => 'Pada ' . $pancha['nakPada'] . ' of 4',
            'nakNum'         => 'Nakshatra ' . ($pancha['nakIdx'] + 1) . ' of 27',
            'nakProg'        => round($pancha['nakProg'] * 100, 1),
            'nakGana'        => $moonNak['gana'],
            'nakGanaSub'     => $ganaSub,
            'nakYoni'        => $moonNak['yoni'],
            'nakNadi'        => $moonNak['nadi'],
            'nakNadiSub'     => $nadiSub,
            'nakTattva'      => $moonNak['tattva'],
            'nakQuality'     => $moonNak['quality'],
            'nakInfo'        => 'Deity: ' . $moonNak['d'] . ' · Lord: ' . $moonNak['l']
                . ' · Gana: ' . $moonNak['gana'] . ' · Yoni: ' . $moonNak['yoni']
                . ' · Nadi: ' . $moonNak['nadi'] . ' Dosha · Element: ' . $moonNak['tattva']
                . '. Quality: ' . $moonNak['quality'] . '.',
            'nakStripTitle'  => $moonNak['n'] . ' Nakshatra',
            'nakStripSub'    => 'Lord: ' . $moonNak['l'] . ' · Deity: ' . $moonNak['d'] . ' · Pada ' . $pancha['nakPada'],
            'nakStripNum'    => ($pancha['nakIdx'] + 1) . ' / 27',
            'yogaName'       => $yoga['n'],
            'yogaNature'     => $yoga['nature'],
            'yogaLord'       => $yoga['lord'],
            'yogaNum'        => 'Yoga ' . ($pancha['yogaIdx'] + 1) . ' of 27',
            'yogaDeity'      => $yoga['deity'],
            'yogaClass'      => $yoga['cls'],
            'yogaClassSub'   => $yogaClassSub,
            'yogaSum'        => number_format($pancha['yogaSum'], 2) . '°',
            'yogaProg'       => round($pancha['yogaProg'] * 100, 1),
            'yogaInfo'       => $yoga['desc'] . ' Deity: ' . $yoga['deity'] . '. Lord: ' . $yoga['lord'] . '.',
            'yogaStripTitle' => $yoga['n'] . ' Yoga',
            'yogaStripSub'   => $yoga['nature'] . ' · Lord: ' . $yoga['lord'] . ' · ' . $yoga['cls'],
            'yogaStripNum'   => ($pancha['yogaIdx'] + 1) . ' / 27',
            'varaIdx'        => $pancha['varaIdx'],
            'nakIdx'         => $pancha['nakIdx'],
            'yogaIdx'        => $pancha['yogaIdx'],
            'sumVara'        => preg_replace('/vara$/i', '', $vara['n']),
            'sumNak'         => $moonNak['n'],
            'sumYoga'        => $yoga['n'],
            'rise'           => $fmt['sunrise'],
            'set'            => $fmt['sunset'],
            'dayLen'         => $fmt['dayLength'],
        ];
    }

    private function formatResult(?array $ss): array
    {
        if (!$ss || $ss['polar']) {
            $rise   = ($ss && $ss['polar'] === 'no_rise') ? 'Polar Night' : 'Midnight Sun';
            $set    = '—';
            $dayLen = ($ss && $ss['polar'] === 'no_rise') ? '0h' : '24h';
        } else {
            $rise   = $ss['rise'] !== null ? AstroCalculator::decToHMS($ss['rise']) : '—';
            $set    = $ss['set']  !== null ? AstroCalculator::decToHMS($ss['set'])  : '—';
            $dlH    = (int)$ss['dayLength'];
            $dlM    = (int)round(($ss['dayLength'] - $dlH) * 60);
            $dayLen = "{$dlH}h {$dlM}m";
        }
        return ['sunrise' => $rise, 'sunset' => $set, 'dayLength' => $dayLen];
    }
}
