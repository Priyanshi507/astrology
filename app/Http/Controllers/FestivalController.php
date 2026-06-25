<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\Planetary\AstroCalculator;
use App\Services\Festival\HinduFestivalCalculator;
use App\Services\Festival\TodayPanelService;

class FestivalController extends Controller
{
    // ── POST /astro/festivals ─────────────────────────────────────
    public function festivals(Request $request)
    {
        $request->validate([
            'year'     => 'required|integer|min:1900|max:2100',
            'lat'      => 'required|numeric|min:-90|max:90',
            'lon'      => 'required|numeric|min:-180|max:180',
            'utcOff'   => 'required|numeric|min:-12|max:14',
            'category' => 'nullable|string|max:50',
        ]);

        $yr     = (int)$request->year;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;
        $utcOff = (float)$request->utcOff;

        // Share the same cache key as TodayPanelService::yearFests() so a warm
        // Today panel makes the Festival tab instant too (and vice-versa).
        $cacheKey = "festivals_year_{$yr}_" . md5("{$lat}{$lon}{$utcOff}");
        $festivals = Cache::remember($cacheKey, 86400, function () use ($yr, $lat, $lon, $utcOff) {
            return HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff)['festivals'] ?? [];
        });

        $calData = ['festivals' => $festivals, 'count' => count($festivals)];
        $category  = $request->input('category', 'all');

        $viewData = HinduFestivalCalculator::prepareForView($festivals, $category);
        $html = view('partials.festival._festivals', ['view' => $viewData])->render();

        return response()->json([
            'html'      => $html,
            'festivals' => $festivals,
            'count'     => $calData['count'] ?? count($festivals),
            'year'      => (int)$request->year,
            'category'  => $category,
        ]);
    }

    // ── POST /astro/today ─────────────────────────────────────────
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
        $utcOff = (float)$request->utcOffset;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;

        $cacheKey = "today_panel_{$yr}{$mo}{$dy}_{$hr}{$mn}_" . md5("{$lat}{$lon}{$utcOff}");
        $data = Cache::remember($cacheKey, 86400, function () use ($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon) {
            return TodayPanelService::prepareForView(TodayPanelService::build(
                $yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon
            ));
        });

        $html = view('partials.festival._today', ['d' => $data])->render();
        return response()->json(array_merge($data, ['html' => $html]));
    }

    // ── POST /astro/ekadashi ──────────────────────────────────────
    public function ekadashiYear(Request $request)
    {
        $request->validate([
            'year'   => 'required|integer|min:1900|max:2100',
            'lat'    => 'required|numeric|min:-90|max:90',
            'lon'    => 'required|numeric|min:-180|max:180',
            'utcOff' => 'required|numeric|min:-12|max:14',
        ]);

        $yr  = (int)$request->year;
        $lat = (float)$request->lat;
        $lon = (float)$request->lon;
        $utc = (float)$request->utcOff;

        $ekadashis = Cache::remember(
            "ekadashi_{$yr}_" . md5("{$lat}{$lon}{$utc}"),
            86400,
            fn() => AstroCalculator::getEkadashiYear($yr, $lat, $lon, $utc)
        );

        return response()->json([
            'ekadashis' => $ekadashis,
            'count'     => count($ekadashis),
            'year'      => (int)$request->year,
        ]);
    }
}
