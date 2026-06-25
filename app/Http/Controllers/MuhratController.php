<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Muhurta\MuhratCalculator;

class MuhratController extends Controller
{
    // ── POST /astro/muhrat — single day ───────────────────────────
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
            (int)$request->year,
            (int)$request->month,
            (int)$request->day,
            (float)$request->lat,
            (float)$request->lon,
            (float)$request->utcOff,
            $request->type,
            [
                'subtype'      => $request->input('subtype', 'new'),
                'girlRashiIdx' => $request->input('girlRashiIdx'),
                'boyRashiIdx'  => $request->input('boyRashiIdx'),
                'girlNakIdx'   => $request->input('girlNakIdx'),
                'boyNakIdx'    => $request->input('boyNakIdx'),
            ]
        );
        $viewData = MuhratCalculator::prepareDayView($data, $request->type);
        $html = view('partials.festival._muhurta_day', ['data' => $viewData])->render();
        return response()->json(['html' => $html]);
    }

    // ── POST /astro/muhrat/month ──────────────────────────────────
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
            (float)$request->lat,
            (float)$request->lon,
            (float)$request->utcOff,
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
        $viewData = MuhratCalculator::prepareMonthView($dates, $request->type, $mo, $yr);
        $html = view('partials.festival._muhurta_month', ['data' => $viewData])->render();
        return response()->json(['html' => $html, 'dates' => $dates, 'count' => count($dates)]);
    }

    // ── POST /astro/muhrat/year ───────────────────────────────────
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

        $allDates = [];
        $allHtml  = '';
        $total    = 0;

        for ($m = 1; $m <= 12; $m++) {
            $dates = MuhratCalculator::scanMonth(
                $yr, $m,
                (float)$request->lat,
                (float)$request->lon,
                (float)$request->utcOff,
                $request->type,
                $options
            );
            $allDates[$m] = $dates;
            $total += count($dates);
            $allHtml .= view('partials.festival._muhurta_month', [
                'data' => MuhratCalculator::prepareMonthView($dates, $request->type, $m, $yr),
            ])->render();
        }

        return response()->json([
            'html'     => $allHtml,
            'allDates' => $allDates,
            'total'    => $total,
            'year'     => $yr,
        ]);
    }
}
