<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\Planetary\AstroCalculator;
use App\Services\Planetary\GocharCalculator;
use App\Services\Planetary\TransitCalculator;

class GocharController extends Controller
{
    // ── POST /astro/gochar ────────────────────────────────────────
    public function gochar(Request $request)
    {
        $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'time'      => 'required|date_format:H:i',
            'utcOffset' => 'required|numeric|min:-12|max:14',
            'lat'       => 'required|numeric|min:-90|max:90',
            'lon'       => 'required|numeric|min:-180|max:180',
            'mode'      => 'required|string|in:date,month,year',
            'target'    => 'required|date_format:Y-m-d',
        ]);

        [$nyr, $nmo, $ndy] = array_map('intval', explode('-', $request->date));
        [$nhr, $nmn]       = array_map('intval', explode(':', $request->time));
        $utcOff = (float)$request->utcOffset;
        $lat    = (float)$request->lat;
        $lon    = (float)$request->lon;
        $mode   = $request->mode;
        [$tyr, $tmo, $tdy] = array_map('intval', explode('-', $request->target));

        // Reuse Phase 1 natal cache; cache the rendered output per natal+target+mode
        $natalKey = 'astro_' . md5("{$nyr}{$nmo}{$ndy}{$nhr}{$nmn}{$utcOff}{$lat}{$lon}");
        $natal    = Cache::remember($natalKey . '_raw', 300,
            fn() => AstroCalculator::calculate($nyr, $nmo, $ndy, $nhr, $nmn, $utcOff, $lat, $lon)
        );

        $gocharHtmlKey = 'gochar_' . md5("{$natalKey}{$request->target}{$mode}");
        if ($cached = Cache::get($gocharHtmlKey)) {
            return response()->json($cached);
        }
        $nAyan      = $natal['ayan'];
        $natalMoon  = (int)floor(fmod(fmod($natal['planets']['moon']['trop'] - $nAyan, 360) + 360, 360) / 30.0);
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

            $label       = sprintf('%02d %s %d', $tdy, ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$tmo], $tyr);
            $detailData  = TransitCalculator::prepareDetailView($details, $label);
            $aspectsData = TransitCalculator::prepareAspectsView($aspects);
            $gocharHtml  = view('partials.planetary._gochar', ['data' => $gocharData])->render();
            $html = view('partials.planetary._transit_detail', ['data' => $detailData])->render()
                . '<div style="margin-top:8px">' . $gocharHtml . '</div>'
                . view('partials.planetary._transit_aspects', ['data' => $aspectsData])->render();

            $res = ['html' => $html, 'label' => $label];
            Cache::put($gocharHtmlKey, $res, 3600);
            return response()->json($res);
        }

        if ($mode === 'month') {
            $lastDay = (int)(new \DateTime("$tyr-$tmo-01"))->format('t');
            $events  = TransitCalculator::rangeEvents(
                $tyr, $tmo, 1, $tyr, $tmo, $lastDay, $utcOff,
                ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu']
            );
            $label        = ['','January','February','March','April','May','June','July','August','September','October','November','December'][$tmo] . ' ' . $tyr;
            $calendarData = TransitCalculator::prepareCalendarView($events, 'Transit Calendar — ' . $label, $utcOff, false);
            $html         = view('partials.planetary._transit_calendar', ['data' => $calendarData])->render();
            $res = ['html' => $html, 'label' => $label];
            Cache::put($gocharHtmlKey, $res, 3600);
            return response()->json($res);
        }

        // year
        $events = TransitCalculator::rangeEvents(
            $tyr, 1, 1, $tyr, 12, 31, $utcOff,
            ['sun','mars','mercury','venus','jupiter','saturn','rahu','ketu']
        );
        $calendarData = TransitCalculator::prepareCalendarView($events, 'Yearly Transit Calendar — ' . $tyr, $utcOff, true);
        $html         = view('partials.planetary._transit_calendar', ['data' => $calendarData])->render();
        $res = ['html' => $html, 'label' => (string)$tyr];
        Cache::put($gocharHtmlKey, $res, 3600);
        return response()->json($res);
    }
}
