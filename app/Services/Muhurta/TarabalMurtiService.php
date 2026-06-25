<?php

namespace App\Services\Muhurta;

use App\Services\Planetary\AstroCalculator;
use App\Services\Muhurta\MuhratCalculator;
use Illuminate\Support\Facades\DB;

/**
 * TarabalMurtiService â€” Tarabala and Murti Nirnaya
 *
 * Source: Brihat Parashara Hora Shastra (BPHS), Muhurta Chintamani (Daivagya Rama),
 *         Muhurta Ganapati, Jyotir-nibandha
 *
 * Tarabala:
 *   BPHS: The distance from birth nakshatra to muhurta nakshatra is divided into nine groups.
 *   Each group = one "Tara" â€” fixed auspicious/inauspicious result.
 *   Tara 1=Janma, 2=Sampat, 3=Vipat, 4=Kshema, 5=Pratyari,
 *         6=Sadhaka, 7=Naidhana, 8=Mitra, 9=Atimitra
 *   Sampat, Kshema, Sadhaka, Mitra, Atimitra = Auspicious (5)
 *   Janma = Neutral (inauspicious for muhurta under BPHS strictest reading)
 *   Vipat, Pratyari, Naidhana = Inauspicious (3)
 *
 * Murti Nirnaya:
 *   BPHS Ch.87 + Muhurta Chintamani: The "Murti" (form/avatar) of the native is
 *   determined by the combined calculation of Vara + Moon Nakshatra + Birth Nakshatra.
 *   Murtis: Swarna, Rajata, Tamra, Loha â€” results range from most auspicious to inauspicious.
 *   Formula: (Vara + Birth Nak + Moon Nak) mod 4
 *            0=Swarna, 1=Rajata, 2=Tamra, 3=Loha
 */
class TarabalMurtiService
{
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    //  UI-only constants (no DB table for these)
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    // Colors are UI-only; tara/murti color_hex was dropped from DB.
    private const TARA_COLORS  = [1=>'#c8a020',2=>'#2d7a3a',3=>'#a02010',4=>'#1a5a8a',
                                   5=>'#7a1a3a',6=>'#2a5a8a',7=>'#8a1a10',8=>'#5a3a8a',9=>'#1a6a2a'];
    private const MURTI_COLORS = ['#c8a020','#6a7a8a','#b05010','#3a3a4a'];
    private const MURTI_BG     = ['#fef9e0','#f0f4f8','#fdf0e8','#f0f0f4'];

    // Tattva element â†’ color (string-keyed; tattva comes as string from DB)
    private const TATVA_COLOR = [
        'Fire'  => '#c8521a', 'Earth' => '#7a5530', 'Ether' => '#4a4a7a',
        'Air'   => '#2a7a5a', 'Water' => '#1a5a8a',
    ];

    // DB-backed: taras[1-9], murtis[0-3], nakCache[0-26], varaNames[0-6], rashiNames[0-11]
    private static ?array $taras     = null;
    private static ?array $murtis    = null;
    private static ?array $nakCache  = null;
    private static ?array $varaNames  = null;
    private static ?array $rashiNames = null;

    private static function ensureData(): void
    {
        if (self::$taras !== null) {
            return;
        }
        self::$taras = DB::table('taras')
            ->orderBy('tara_number')
            ->get()
            ->mapWithKeys(fn($r) => [$r->tara_number => [
                'name'  => $r->name,
                'en'    => $r->name,
                'shubh' => (bool) $r->is_auspicious,
                'type'  => $r->auspiciousness_type,
                'color' => self::TARA_COLORS[$r->tara_number],
                'icon'  => $r->icon_symbol,
                'bphs'  => $r->bphs_reference,
                'phala' => $r->phala_description,
                'bonus' => $r->scoring_bonus,
            ]])
            ->toArray();

        self::$murtis = DB::table('murtis')
            ->orderBy('murti_index')
            ->get()
            ->mapWithKeys(fn($r) => [$r->murti_index => [
                'name'    => $r->name,
                'en'      => $r->name,
                'symbol'  => $r->symbol,
                'color'   => self::MURTI_COLORS[$r->murti_index],
                'bg'      => self::MURTI_BG[$r->murti_index],
                'quality' => $r->quality_description,
                'rank'    => $r->rank_order,
                'bphs'    => $r->bphs_reference,
                'phala'   => $r->phala_description,
                'upay'    => $r->upaya_remedy,
            ]])
            ->toArray();

        self::$nakCache = DB::table('nakshatras as nak')
            ->join('planets as p', 'nak.lord_planet_id', '=', 'p.id')
            ->orderBy('nak.id')
            ->get(['nak.name', 'p.name as lord', 'p.vimshottari_order as lord_idx',
                   'nak.tattva', 'nak.gana', 'nak.nadi', 'nak.guna',
                   'nak.muhurta_auspiciousness_score as muhurta_idx',
                   'nak.description',
                   'nak.muhurta_type_label',
                   'nak.muhurta_type_desc'])
            ->map(fn($r) => (array) $r)
            ->values()->toArray();

        self::$varaNames  = DB::table('weekdays')->orderBy('dow_index')->pluck('english_name')->toArray();
        self::$rashiNames = DB::table('zodiac_signs')->orderBy('id')->pluck('name')->toArray();
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    //  MAIN: à¤¤à¤¾à¤°à¤¬à¤² à¤—à¤£à¤¨à¤¾ â€” à¤à¤• à¤¦à¤¿à¤¨ à¤•à¥‡ à¤²à¤¿à¤
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    /**
     * Compute Tarabala for a given date and birth nakshatra
     *
     * @param int    $yr          à¤µà¤°à¥à¤·
     * @param int    $mo          à¤®à¤¾à¤¹
     * @param int    $dy          à¤¦à¤¿à¤¨
     * @param float  $lat         à¤…à¤•à¥à¤·à¤¾à¤‚à¤¶
     * @param float  $lon         à¤¦à¥‡à¤¶à¤¾à¤‚à¤¤à¤°
     * @param float  $utcOff      UTC+
     * @param int    $birthNak    à¤œà¤¨à¥à¤® à¤¨à¤•à¥à¤·à¤¤à¥à¤° (0-26), -1 = à¤…à¤œà¥à¤žà¤¾à¤¤
     * @return array
     */
    public static function computeTarabal(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff,
        int $birthNak = -1
    ): array {
        $ss      = AstroCalculator::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);
        $riseHr  = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $setHr   = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;
        $jdRise  = AstroCalculator::julianDay($yr, $mo, $dy, $riseHr - $utcOff);
        $ayan    = AstroCalculator::lahiriAyanamsa($jdRise);

        // Current moon nakshatra
        $moonLon  = AstroCalculator::moonLongitude($jdRise);
        $moonSid  = fmod(fmod($moonLon - $ayan, 360) + 360, 360);
        $moonNak  = (int)floor($moonSid / (360.0 / 27.0));
        $moonPada = (int)(($moonSid - $moonNak * (360/27)) / ((360/27)/4)) + 1;
        $moonRashi= (int)floor($moonSid / 30);

        // Sun nakshatra
        $sunLon   = AstroCalculator::sunLongitude($jdRise);
        $sunSid   = fmod(fmod($sunLon - $ayan, 360) + 360, 360);
        $sunNak   = (int)floor($sunSid / (360.0 / 27.0));
        $sunRashi = (int)floor($sunSid / 30);

        // Panchanga
        $result = AstroCalculator::calculate($yr, $mo, $dy,
            (int)floor($riseHr), (int)round(($riseHr - floor($riseHr))*60),
            $utcOff, $lat, $lon);
        $tk    = $result['tk']     ?? [];
        $panch = $result['pancha'] ?? [];
        $varaIdx = (int)(new \DateTime("{$yr}-{$mo}-{$dy}"))->format('w'); // 0=Sun

        // 27 à¤¤à¤¾à¤°à¤¾ à¤šà¤•à¥à¤° (BPHS exactness)
        // Each cycle of 9 gives: 1-9. Three cycles cover all 27 naks.
        // 1st cycle (naks 1-9): Primary tara
        // 2nd cycle (naks 10-18): same names, 2nd repetition
        // 3rd cycle (naks 19-27): same names, 3rd repetition
        self::ensureData();
        $taraResult = null;
        $personalMurti = null;

        if ($birthNak >= 0) {
            $dist    = (($moonNak - $birthNak) + 27) % 27;
            $taraNum = ($dist % 9) + 1; // 1..9
            $cycle   = (int)floor($dist / 9) + 1; // 1st, 2nd, 3rd cycle
            $taraInfo = self::$taras[$taraNum];

            // Extended BPHS: 2nd and 3rd cycle reduce effect by 50%/25%
            $cycleModifier = match($cycle) { 2 => 0.5, 3 => 0.25, default => 1.0 };
            $effectiveBonus = (int)round($taraInfo['bonus'] * $cycleModifier);

            $taraResult = [
                'birthNak'       => $birthNak,
                'birthNakHi'     => self::$nakCache[$birthNak]['name'],
                'moonNak'        => $moonNak,
                'moonNakHi'      => self::$nakCache[$moonNak]['name'],
                'dist'           => $dist,
                'cycle'          => $cycle,
                'taraNum'        => $taraNum,
                'name'           => $taraInfo['name'],
                'en'             => $taraInfo['en'],
                'shubh'          => $taraInfo['shubh'],
                'type'           => $taraInfo['type'],
                'color'          => $taraInfo['color'],
                'icon'           => $taraInfo['icon'],
                'bphs'           => $taraInfo['bphs'],
                'phala'          => $taraInfo['phala'],
                'bonus'          => $effectiveBonus,
                'cycleModifier'  => $cycleModifier,
                'cycleNote'      => $cycle > 1 ? "(Cycle {$cycle} â€” effect reduced by " . ($cycle === 2 ? '50%' : '25%') . ")" : '',
            ];

            // Personal Murti: vara + birthNak + moonNak
            $murtiIdx = ($varaIdx + $birthNak + $moonNak) % 4;
            $personalMurti = array_merge(['idx' => $murtiIdx, 'type' => 'Personal (with Birth Nakshatra)'], self::$murtis[$murtiIdx]);
        }

        // General Murti: vara + moonNak (without birth nak)
        $generalMurtiIdx = ($varaIdx + $moonNak) % 4;
        $generalMurti = array_merge(['idx' => $generalMurtiIdx, 'type' => 'General (Vara + Moon Nakshatra)'], self::$murtis[$generalMurtiIdx]);

        // All 9 taras from moonNak (informational table)
        $taraTable = [];
        for ($i = 0; $i < 27; $i++) {
            $tNum = ($i % 9) + 1;
            $nakIdx = ($moonNak + $i) % 27; // naks that are tara-i from today's moon
            $taraTable[$i] = [
                'nak'    => $nakIdx,
                'nakHi'  => self::$nakCache[$nakIdx]['name'],
                'tara'   => $tNum,
                'taraHi' => self::$taras[$tNum]['name'],
                'shubh'  => self::$taras[$tNum]['shubh'],
                'color'  => self::$taras[$tNum]['color'],
                'cycle'  => (int)floor($i / 9) + 1,
            ];
        }

        // Nakshatra details
        $nakDetails = self::getNakDetails($moonNak, $moonPada);

        // Chandrabala (if birth rashi provided â€” MuhratCalculator does not expose a public getter here)
        // Use empty placeholder â€” detailed Chandra Bala is computed within MuhratCalculator during vivah checks.
        $chandrabala = [];

        return [
            'date'          => "{$yr}-{$mo}-{$dy}",
            'varaIdx'       => $varaIdx,
            'varaHi'        => self::$varaNames[$varaIdx],
            'sunrise'       => AstroCalculator::decToHMS($riseHr),
            'sunset'        => AstroCalculator::decToHMS($setHr),
            'moonNak'       => $moonNak,
            'moonNakHi'     => self::$nakCache[$moonNak]['name'],
            'moonNakEn'     => self::$nakCache[$moonNak]['name'],
            'moonPada'      => $moonPada,
            'moonRashi'     => $moonRashi,
            'moonRashiHi'   => self::$rashiNames[$moonRashi],
            'moonLord'      => self::$nakCache[$moonNak]['lord_idx'],
            'moonLordHi'    => self::$nakCache[$moonNak]['lord'],
            'moonGana'      => self::$nakCache[$moonNak]['gana'],
            'moonNadi'      => self::$nakCache[$moonNak]['nadi'],
            'moonTatva'     => self::$nakCache[$moonNak]['tattva'],
            'moonTatvaColor'=> self::TATVA_COLOR[self::$nakCache[$moonNak]['tattva']],
            'nakMuhurtaType'=> self::$nakCache[$moonNak]['muhurta_idx'],
            'sunNak'        => $sunNak,
            'sunNakHi'      => self::$nakCache[$sunNak]['name'],
            'sunRashiHi'    => self::$rashiNames[$sunRashi],
            'tithiHi'       => ($tk['tithi']['paksha'] ?? '') . ' ' . ($tk['tithi']['num'] ?? ''),
            'nakDetails'    => $nakDetails,
            'taraResult'    => $taraResult,
            'personalMurti' => $personalMurti,
            'generalMurti'  => $generalMurti,
            'taraTable'     => $taraTable,
            'allTaraData'   => self::$taras,
            'allMurtiData'  => self::$murtis,
        ];
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    //  MAIN: à¤®à¥‚à¤°à¥à¤¤à¤¿ à¤¨à¤¿à¤°à¥à¤£à¤¯ â€” à¤µà¤¿à¤¸à¥à¤¤à¥ƒà¤¤ (for dedicated panel)
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public static function computeMurtiNirnaya(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff,
        int $birthNak = -1,
        int $birthRashi = -1
    ): array {
        $ss      = AstroCalculator::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);
        $riseHr  = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $setHr   = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;
        $jdRise  = AstroCalculator::julianDay($yr, $mo, $dy, $riseHr - $utcOff);
        $ayan    = AstroCalculator::lahiriAyanamsa($jdRise);

        $moonLon  = AstroCalculator::moonLongitude($jdRise);
        $moonSid  = fmod(fmod($moonLon - $ayan, 360) + 360, 360);
        $moonNak  = (int)floor($moonSid / (360.0 / 27.0));
        $moonPada = (int)(($moonSid - $moonNak * (360/27)) / ((360/27)/4)) + 1;
        $moonRashi= (int)floor($moonSid / 30);

        $sunLon   = AstroCalculator::sunLongitude($jdRise);
        $sunSid   = fmod(fmod($sunLon - $ayan, 360) + 360, 360);
        $sunNak   = (int)floor($sunSid / (360.0 / 27.0));
        $sunRashi = (int)floor($sunSid / 30);

        $varaIdx  = (int)(new \DateTime("{$yr}-{$mo}-{$dy}"))->format('w');
        self::ensureData();

        // Computations
        $generalMurtiIdx  = ($varaIdx + $moonNak) % 4;
        $generalMurti     = array_merge(['idx'=>$generalMurtiIdx,'type'=>'General'], self::$murtis[$generalMurtiIdx]);

        $personalMurti    = null;
        $chandrabala      = null;
        $tarabal          = null;

        if ($birthNak >= 0) {
            $pMurtiIdx    = ($varaIdx + $birthNak + $moonNak) % 4;
            $personalMurti= array_merge(['idx'=>$pMurtiIdx,'type'=>'Personal'], self::$murtis[$pMurtiIdx]);

            // Tarabala for murti context
            $dist  = (($moonNak - $birthNak) + 27) % 27;
            $tNum  = ($dist % 9) + 1;
            $tarabal = ['taraNum'=>$tNum, 'name'=>self::$taras[$tNum]['name'],
                        'shubh'=>self::$taras[$tNum]['shubh'], 'color'=>self::$taras[$tNum]['color']];
        }
        if ($birthRashi >= 0) {
            $chandrabala = [];
        }

        // All 4 murti calculations for reference
        $murtiForAllVara = [];
        for ($v = 0; $v < 7; $v++) {
            $idx = ($v + $moonNak) % 4;
            $murtiForAllVara[] = [
                'vara'   => self::$varaNames[$v],
                'idx'    => $idx,
                'murti'  => self::$murtis[$idx]['name'],
                'quality'=> self::$murtis[$idx]['quality'],
                'color'  => self::$murtis[$idx]['color'],
                'shubh'  => $idx <= 1,
            ];
        }

        return [
            'date'           => "{$yr}-{$mo}-{$dy}",
            'varaIdx'        => $varaIdx,
            'varaHi'         => self::$varaNames[$varaIdx],
            'sunrise'        => AstroCalculator::decToHMS($riseHr),
            'sunset'         => AstroCalculator::decToHMS($setHr),
            'moonNak'        => $moonNak,
            'moonNakHi'      => self::$nakCache[$moonNak]['name'],
            'moonPada'       => $moonPada,
            'moonRashi'      => $moonRashi,
            'moonRashiHi'    => self::$rashiNames[$moonRashi],
            'moonLordHi'     => self::$nakCache[$moonNak]['lord'],
            'moonGana'       => self::$nakCache[$moonNak]['gana'],
            'moonNadi'       => self::$nakCache[$moonNak]['nadi'],
            'sunNakHi'       => self::$nakCache[$sunNak]['name'],
            'sunRashiHi'     => self::$rashiNames[$sunRashi],
            'generalMurti'   => $generalMurti,
            'personalMurti'  => $personalMurti,
            'tarabal'        => $tarabal,
            'chandrabala'    => $chandrabala,
            'murtiFormula'   => [
                'general'  => "Vara ({$varaIdx}) + Moon Nakshatra ({$moonNak}) = " . ($varaIdx + $moonNak) . " â†’ " . ($generalMurtiIdx) . " (mod 4)",
                'personal' => $birthNak >= 0 ? "Vara ({$varaIdx}) + Birth Nakshatra ({$birthNak}) + Moon Nakshatra ({$moonNak}) = " . ($varaIdx + $birthNak + $moonNak) . " â†’ " . (($varaIdx + $birthNak + $moonNak) % 4) . " (mod 4)" : null,
            ],
            'murtiForAllVara'=> $murtiForAllVara,
            'nakMuhurtaType' => [
                'name' => self::$nakCache[$moonNak]['muhurta_type_label'] ?? '',
                'desc' => self::$nakCache[$moonNak]['muhurta_type_desc']  ?? '',
            ],
            'nakMuhurtaIdx'  => self::$nakCache[$moonNak]['muhurta_idx'],
            'allMurtiData'   => self::$murtis,
            'nakDetails'     => self::getNakDetails($moonNak, $moonPada),
        ];
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    //  HELPER: à¤¨à¤•à¥à¤·à¤¤à¥à¤° à¤µà¤¿à¤µà¤°à¤£
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public static function getNakDetails(int $nak, int $pada = 1): array
    {
        self::ensureData();
        $charanaRashi = ($nak * 4 + ($pada - 1)) % 12; // Charana Rashi
        $navamshaLord = self::$rashiNames[$charanaRashi];

        return [
            'nak'          => $nak,
            'nakHi'        => self::$nakCache[$nak]['name'],
            'nakEn'        => self::$nakCache[$nak]['name'],
            'lord'         => self::$nakCache[$nak]['lord'],
            'lordIdx'      => self::$nakCache[$nak]['lord_idx'],
            'gana'         => self::$nakCache[$nak]['gana'],
            'nadi'         => self::$nakCache[$nak]['nadi'],
            'tatva'        => self::$nakCache[$nak]['tattva'],
            'tatvaColor'   => self::TATVA_COLOR[self::$nakCache[$nak]['tattva']],
            'guna'         => self::$nakCache[$nak]['guna'],
            'pada'         => $pada,
            'charanaRashi' => $navamshaLord,
            'desc'         => self::$nakCache[$nak]['description'] ?? '',
            'muhurtaType'  => self::$nakCache[$nak]['muhurta_idx'],
        ];
    }
}
