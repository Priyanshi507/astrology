<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\Planetary\AstroCalculator;
use App\Services\Planetary\ShadBalaCalculator;
use App\Services\Dasha\VimshottariDashaCalculator;
use App\Services\Kundali\KundaliService;
 
class KundaliController extends Controller
{
    // ── POST /astro/kundali ───────────────────────────────────────
    public function kundali(Request $request)
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

        // Serve cached HTML instantly if available (same inputs as Phase 2)
        $rawKey    = 'astro_' . md5("{$yr}{$mo}{$dy}{$hr}{$mn}{$utcOff}{$lat}{$lon}");
        $cachedHtml = Cache::get($rawKey . '_kundali_html');
        if ($cachedHtml) {
            return response()->json(['html' => $cachedHtml]);
        }

        // Reuse Phase 1 raw calculation cache
        $result   = Cache::remember($rawKey . '_raw', 300,
            fn() => AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon)
        );
        $ayan     = $result['ayan'];
        $planets  = $result['planets'];
        $ascSider = $result['ascSider'];
        $angles   = $result['angles'];
        $pancha   = $result['pancha'];
        $tk       = $result['tk'];
        $ss       = $result['ss'];
        $jd       = $result['jd'];

        // Shadbala
        $shadbala = ShadBalaCalculator::calculate(
            $planets, $ascSider, $jd, $lat, $angles, $hr,
            (int)(new \DateTime("{$yr}-{$mo}-{$dy}"))->format('w'),
            ($angles['asc'] > ($planets['sun']['trop'] ?? 0))
        );
        $shadBalaHtml = view('partials.planetary._shadbala',
            ['data' => ShadBalaCalculator::prepareForView($shadbala)])->render();

        // Vimshottari Dasha
        $moonSider   = fmod(fmod($planets['moon']['trop'] - $ayan, 360) + 360, 360);
        $vimshottari = VimshottariDashaCalculator::calculate($moonSider, $yr, $mo, $dy, $hr + $mn / 60.0);
        $dashaHtml   = view('partials.dasha._vimshottari',
            ['data' => VimshottariDashaCalculator::prepareForView($vimshottari)])->render();

        // KundaliService — Graha, Bhava, Upagraha, Yoga, AV, Bhava Bala
        $kundaliData = KundaliService::calculate(
            $planets, $ascSider, $jd,
            $yr, $mo, $dy, $hr, $mn,
            $utcOff, $lat, $lon,
            $shadbala, $angles, $pancha, $vimshottari
        );

        // Moon sign for Panchanga display
        $vedicSigns   = AstroCalculator::getVedicSigns();
        $moonSignName = $vedicSigns[(int)floor($moonSider / 30)] ?? '—';

        $panchaDisplay = [
            'tithi'     => ($tk['tithi']['paksha'] ?? '') . ' ' . ($tk['tithi']['n'] ?? '—'),
            'vara'      => $pancha['vara']['n']      ?? '—',
            'nakshatra' => $pancha['moonNak']['n']   ?? '—',
            'yoga'      => $pancha['yoga']['n']      ?? '—',
            'karana'    => $tk['karana']['n']        ?? '—',
            'paksha'    => $tk['tithi']['paksha']    ?? '—',
            'moonSign'  => $moonSignName,
            'sunRise'   => ($ss && !($ss['polar'] ?? false) && isset($ss['rise']))
                            ? AstroCalculator::decToHMS($ss['rise']) : '—',
            'sunSet'    => ($ss && !($ss['polar'] ?? false) && isset($ss['set']))
                            ? AstroCalculator::decToHMS($ss['set'])  : '—',
        ];

        $html = view('partials.kundali._panel_kundali', [
            'k'           => $kundaliData,
            'shadBalaHtml'=> $shadBalaHtml,
            'dashaHtml'   => $dashaHtml,
            'pancha'      => $panchaDisplay,
        ])->render();

        Cache::put($rawKey . '_kundali_html', $html, 300);
        return response()->json(['html' => $html]);
    }
}
