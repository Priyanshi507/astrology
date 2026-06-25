<?php

namespace App\Services\Festival;

use App\Services\Planetary\AstroCalculator;
use Illuminate\Support\Facades\DB;

/**
 * HinduFestivalCalculator — Comprehensive Hindu Festival & Vrat Calendar
 *
 * Mathematical Basis:
 *   • Jean Meeus — "Astronomical Algorithms" (2nd Ed.) — Sun/Moon positions
 *   • B.V. Raman   — "Hindu Predictive Astrology", "Muhurtha"
 *   • Brihat Parashara Hora Shastra (BPHS) — Tithi/Karana/Nakshatra rules
 *   • Lahiri Ayanamsa (Rashtriya Panchang standard)
 *   • Surya Siddhanta — Solar Sankranti calculations
 *
 * Festival rules are stored in the festival_rules DB table — no hardcoded
 * if-statement chains. Astronomical trigger logic (tithi matching, solar
 * ingress, nakshatra+DOW) remains in code; only the content lives in DB.
 */
class HinduFestivalCalculator
{
    private static ?array $tithiRules        = null;
    private static ?array $gregorianRules    = null;
    private static ?array $solarIngressRules = null;
    private static ?array $nakshatraDowRules = null;
    private static ?array $ekadashiPuranaRefs = null;
    private static ?array $shraddhaNames     = null; // [tithi_number => name]
    private static ?array $chipLabels        = null; // [category => chip_label]

    // ── Load festival rules once per process ──────────────────────────────────
    private static function ensureRules(): void
    {
        if (self::$tithiRules !== null) return;

        self::$tithiRules = DB::table('festival_rules')
            ->whereIn('trigger_type', ['tithi', 'dow_masa'])
            ->orderBy('priority')
            ->get()
            ->toArray();

        $gr = DB::table('festival_rules')
            ->where('trigger_type', 'gregorian')
            ->get();

        self::$gregorianRules = [];
        foreach ($gr as $r) {
            self::$gregorianRules[$r->gregorian_date] = $r;
        }

        self::$solarIngressRules = DB::table('festival_rules')
            ->where('trigger_type', 'solar_ingress')
            ->orderBy('sun_sign_index')
            ->orderBy('day_offset')
            ->get()
            ->toArray();

        self::$nakshatraDowRules = DB::table('festival_rules')
            ->where('trigger_type', 'nakshatra_dow')
            ->get()
            ->toArray();

        $ekRefs = DB::table('ekadashis')->select('name', 'purana_reference')->get();
        self::$ekadashiPuranaRefs = [];
        foreach ($ekRefs as $ek) {
            self::$ekadashiPuranaRefs[$ek->name] = $ek->purana_reference ?? '';
        }

        // Shraddha names from tithis table (Krishna paksha)
        self::$shraddhaNames = [];
        foreach (DB::table('tithis')->where('paksha', 'Krishna')->whereNotNull('shraddha_name')
                    ->get(['tithi_number', 'shraddha_name']) as $s) {
            self::$shraddhaNames[(int)$s->tithi_number] = $s->shraddha_name;
        }

        // Chip labels from festival_rules table
        self::$chipLabels = [];
        foreach (DB::table('festival_rules')->select('category', 'chip_label')
                    ->whereNotNull('chip_label')->distinct()->get() as $c) {
            self::$chipLabels[strtolower($c->category)] = $c->chip_label;
        }
    }


    // =========================================================================
    public static function calculateYear(
        int $year, float $lat, float $lon, float $utcOff
    ): array {
        self::ensureRules();

        $festivals = [];

        // ── 1. Ekadashis from astronomical calculator ─────────────────────────
        $ekadashis = AstroCalculator::getEkadashiYear($year, $lat, $lon, $utcOff);
        foreach ($ekadashis as $ek) {
            $festivals[] = [
                'date'         => $ek['date'],
                'name'         => $ek['name'],
                'name_hi'      => $ek['nameHi'] ?? '',
                'type'         => 'vrat',
                'category'     => 'ekadashi',
                'tithi'        => ($ek['paksha'] ?? '') . ' Ekadashi',
                'paksha'       => $ek['paksha'] ?? '',
                'tithi_num'    => 11,
                'masa'         => $ek['vedMonth'] ?? '',
                'significance' => $ek['significance'] ?? '',
                'rituals'      => $ek['rituals'] ?? [],
                'mantra'       => $ek['mantra'] ?? 'Om Namo Bhagavate Vasudevaya',
                'auspTime'     => $ek['auspTime'] ?? '',
                'details'      => self::$ekadashiPuranaRefs[$ek['name'] ?? ''] ?? '',
                'icon'         => '🌸',
            ];
        }

        // ── 2. Day loop ───────────────────────────────────────────────────────
        $startDate    = new \DateTime("{$year}-01-01");
        $prevSunSign  = -1;
        $prevDateStr  = null;
        $prevNum      = 0;
        $prevPaksha   = '';
        $prevMasaName = 'Unknown';

        for ($i = 0; $i < 366; $i++) {
            $dt = clone $startDate;
            $dt->modify("+{$i} days");
            if ((int)$dt->format('Y') !== $year) break;

            $dateStr = $dt->format('Y-m-d');
            $yr  = (int)$dt->format('Y');
            $mo  = (int)$dt->format('m');
            $dy  = (int)$dt->format('d');
            $dow = (int)$dt->format('w');

            // Astronomical calculations
            $approx    = AstroCalculator::calculate($yr, $mo, $dy, 6, 0, $utcOff, $lat, $lon);
            $sunriseHr = (!$approx['ss']['polar'] && $approx['ss']['rise'] !== null)
                       ? $approx['ss']['rise'] : 6.0;
            $hr     = (int)floor($sunriseHr);
            $mn     = (int)round(($sunriseHr - $hr) * 60);
            $result = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);

            $tk     = $result['tk'] ?? [];
            $tithi  = $tk['tithi'] ?? [];
            $paksha = $tithi['paksha'] ?? '';
            $num    = (int)($tithi['num'] ?? 0);

            $sunSider   = $result['planets']['sun']['sider'] ?? 0;
            $curSunSign = (int)floor($sunSider / 30);

            $elong    = $result['tk']['elong'] ?? 0.0;
            $jdRef    = $result['jd'] ?? AstroCalculator::julianDay($yr, $mo, $dy, 6.0 - $utcOff);
            $masaIdx  = AstroCalculator::purnimantaMasaIdx($jdRef, $elong, $paksha);
            $masaName = AstroCalculator::MASA_NAMES[$masaIdx] ?? 'Unknown';

            $moonSider = $result['planets']['moon']['sider'] ?? 0;
            $nakIdx    = (int)floor($moonSider / (360 / 27));

            $ssRise = (!$result['ss']['polar'] && $result['ss']['rise'] !== null)
                    ? AstroCalculator::decToHMS($result['ss']['rise']) : '';
            $ssSet  = (!$result['ss']['polar'] && $result['ss']['set']  !== null)
                    ? AstroCalculator::decToHMS($result['ss']['set'])  : '';

            // Detect skipped tithis and vriddhi (double) tithis
            $skippedTithis = [];
            $isVriddhiTithi = false;
            if ($prevNum > 0 && $prevPaksha === $paksha) {
                $diff = $num - $prevNum;
                if ($diff === 0) {
                    // Same tithi at today's sunrise as yesterday — vriddhi tithi
                    $isVriddhiTithi = true;
                } elseif ($diff > 1) {
                    for ($k = 1; $k < $diff; $k++) $skippedTithis[] = $prevNum + $k;
                }
            } elseif ($prevNum > 0 && $prevPaksha !== '' && $prevPaksha !== $paksha && $prevNum < 15) {
                // Cross-paksha transition with prevNum < 15 means the boundary tithi was skipped:
                // Shukla 14 → Krishna 1 = Purnima (Shukla 15) skipped
                // Krishna 14 → Shukla 1 = Amavasya (Krishna 15) skipped
                $skippedTithis[] = 15;
            }

            // Ekadashi handled by getEkadashiYear
            if ($num === 11) goto updatePrev;

            // ── 3. Process tithi-based & dow_masa rules from DB ───────────────
            // First pass: detect whether Maha Shivratri (Phalguna Krishna 14) fires
            // today — if so, suppress the generic Masik Shivratri recurring entry
            // that would otherwise also match the same tithi+paksha.
            $mahaShivratriToday = false;
            foreach (self::$tithiRules as $rule) {
                if ($rule->slug !== 'maha-shivratri') continue;
                $t     = (int)$rule->tithi_number;
                $rpksh = $rule->paksha;
                $mf    = json_decode($rule->masa_filter, true) ?? [];
                $dm    = ($num === $t) && ($rpksh === null || $rpksh === $paksha);
                $sm    = in_array($t, $skippedTithis) && ($rpksh === null || $rpksh === $prevPaksha);
                if ($dm || $sm) {
                    $useMasa2 = ($sm && !$dm) ? $prevMasaName : $masaName;
                    if (in_array($useMasa2, $mf)) { $mahaShivratriToday = true; break; }
                }
            }

            // Second pass: emit all matched rules
            foreach (self::$tithiRules as $rule) {
                $useDate   = $dateStr;
                $useMasa   = $masaName;
                $usePaksha = $paksha;
                $matched   = false;

                if ($rule->trigger_type === 'dow_masa') {
                    // DOW + Masa rule (e.g. Shravana Somavar)
                    $masaFilter = $rule->masa_filter ? json_decode($rule->masa_filter, true) : null;
                    $dowMatch   = ($rule->day_of_week === null || (int)$rule->day_of_week === $dow);
                    $masaMatch  = ($masaFilter === null || in_array($masaName, $masaFilter));
                    if ($dowMatch && $masaMatch) $matched = true;

                } elseif ($rule->tithi_range_start !== null) {
                    // Range rule (Pitru Paksha Shraddha days 1–14)
                    $masaFilter = $rule->masa_filter ? json_decode($rule->masa_filter, true) : null;
                    $pkshaOk    = ($rule->paksha === null || $rule->paksha === $paksha);
                    $masaOk     = ($masaFilter === null || in_array($masaName, $masaFilter));
                    $rangeOk    = ($num >= (int)$rule->tithi_range_start && $num <= (int)$rule->tithi_range_end);
                    if ($pkshaOk && $masaOk && $rangeOk) {
                        $matched   = true;
                        $usePaksha = $paksha;
                    }

                } elseif ($rule->tithi_number !== null) {
                    $t    = (int)$rule->tithi_number;
                    $rpksh = $rule->paksha;

                    $directMatch  = ($num === $t) && ($rpksh === null || $rpksh === $paksha);
                    $skippedMatch = in_array($t, $skippedTithis) && ($rpksh === null || $rpksh === $prevPaksha);

                    if ($directMatch || $skippedMatch) {
                        if ($skippedMatch && !$directMatch) {
                            $useDate   = $prevDateStr;
                            $useMasa   = $prevMasaName;
                            $usePaksha = $prevPaksha;
                        }
                        $masaFilter = $rule->masa_filter ? json_decode($rule->masa_filter, true) : null;
                        if ($masaFilter === null || in_array($useMasa, $masaFilter)) {
                            $matched = true;
                        }
                    }

                    // On a vriddhi (double) tithi, suppress recurring rules on the
                    // second sunrise — only non-recurring specific festivals fire again.
                    if ($matched && $rule->is_recurring && $isVriddhiTithi) {
                        $matched = false;
                    }
                    // Suppress Masik Shivratri when Maha Shivratri fires today.
                    if ($matched && $rule->category === 'masik_shivratri' && $mahaShivratriToday) {
                        $matched = false;
                    }
                }

                if (!$matched) continue;

                // Determine actual festival date (with day_offset)
                $entryDate = $useDate;
                if ((int)$rule->day_offset !== 0) {
                    $entryDate = (new \DateTime($useDate))
                        ->modify(((int)$rule->day_offset > 0 ? '+' : '') . $rule->day_offset . ' day')
                        ->format('Y-m-d');
                }
                $entryDow = (int)(new \DateTime($entryDate))->format('w');

                // Build festival name (special cases handled in code)
                $festName     = $rule->name;
                $festSignif   = $rule->significance;

                if ($rule->category === 'pradosh') {
                    $pakLabel  = $usePaksha === 'Shukla' ? 'Shukla' : 'Krishna';
                    $dowPrefix = '';
                    if ($entryDow === 1) {
                        $dowPrefix  = 'Soma ';
                        $festSignif .= ' Soma Pradosh is extremely powerful.';
                    } elseif ($entryDow === 6) {
                        $dowPrefix  = 'Shani ';
                        $festSignif .= ' Shani Pradosh — alleviates Shani Dosha.';
                    }
                    $festName = "{$dowPrefix}{$pakLabel} Trayodashi (Pradosh Vrat)";
                }

                if ($rule->category === 'chaturthi' && $rule->paksha === 'Krishna'
                    && $rule->masa_filter === null && $entryDow === 2) {
                    $festName = 'Angarki Sankashti Chaturthi';
                    $festSignif = $rule->significance . ' Angarki: ten times more powerful.';
                }

                if ($rule->category === 'shraddha' && $rule->tithi_range_start !== null) {
                    $festName = self::$shraddhaNames[$num] ?? 'Pitru Paksha Shraddha';
                }

                $festivals[] = [
                    'date'       => $entryDate,
                    'name'       => $festName,
                    'name_hi'    => $festName,
                    'type'       => $rule->type,
                    'category'   => $rule->category,
                    'tithi'      => $usePaksha . ' ' . $num,
                    'paksha'     => $usePaksha,
                    'tithi_num'  => $rule->tithi_number ?? $num,
                    'masa'       => $useMasa,
                    'sunrise'    => $ssRise,
                    'sunset'     => $ssSet,
                    'icon'       => $rule->icon,
                    'significance'=> $festSignif,
                    'rituals'    => json_decode($rule->rituals, true) ?? [],
                    'mantra'     => $rule->mantra ?? '',
                    'details'    => $rule->details ?? '',
                    'vidhiTitle' => $rule->vidhi_title ?? '',
                ];
            }

            // ── 4. Solar Sankranti (content from DB, detection is mathematical) ──
            if ($prevSunSign !== -1 && $curSunSign !== $prevSunSign) {
                foreach (self::$solarIngressRules as $rule) {
                    if ((int)$rule->sun_sign_index !== $curSunSign) continue;

                    $entryDate = $dateStr;
                    if ((int)$rule->day_offset !== 0) {
                        $entryDate = (new \DateTime($dateStr))
                            ->modify(((int)$rule->day_offset > 0 ? '+' : '') . $rule->day_offset . ' day')
                            ->format('Y-m-d');
                    }

                    $festivals[] = [
                        'date'       => $entryDate,
                        'name'       => $rule->name,
                        'name_hi'    => $rule->name,
                        'type'       => $rule->type,
                        'category'   => $rule->category,
                        'tithi'      => 'Solar Sankranti',
                        'paksha'     => '',
                        'masa'       => 'Solar',
                        'sunrise'    => $ssRise,
                        'sunset'     => $ssSet,
                        'icon'       => $rule->icon,
                        'significance' => $rule->significance,
                        'rituals'    => json_decode($rule->rituals, true) ?? [],
                        'mantra'     => $rule->mantra ?? '',
                        'details'    => $rule->details ?? '',
                        'vidhiTitle' => $rule->vidhi_title ?? '',
                    ];
                }
            }
            $prevSunSign = $curSunSign;

            // ── 5. Nakshatra + DOW special festivals (content from DB) ──────────
            foreach (self::$nakshatraDowRules as $rule) {
                $nakMatch = ($rule->nakshatra_index === null || (int)$rule->nakshatra_index === $nakIdx);
                $dowMatch = ($rule->day_of_week    === null || (int)$rule->day_of_week    === $dow);
                $sunMatch = ($rule->sun_sign_index  === null || (int)$rule->sun_sign_index  === $curSunSign);
                if (!$nakMatch || !$dowMatch || !$sunMatch) continue;

                $festivals[] = [
                    'date'       => $dateStr,
                    'name'       => $rule->name,
                    'name_hi'    => $rule->name,
                    'type'       => $rule->type,
                    'category'   => $rule->category,
                    'tithi'      => $paksha . ' ' . $num,
                    'paksha'     => $paksha,
                    'tithi_num'  => $num,
                    'masa'       => $masaName,
                    'sunrise'    => $ssRise,
                    'sunset'     => $ssSet,
                    'icon'       => $rule->icon,
                    'significance' => $rule->significance,
                    'rituals'    => json_decode($rule->rituals, true) ?? [],
                    'mantra'     => $rule->mantra ?? '',
                    'details'    => $rule->details ?? '',
                    'vidhiTitle' => $rule->vidhi_title ?? '',
                ];
            }

            // ── 6. Gregorian fixed-date festivals from DB ─────────────────────
            $monthDay = sprintf('%02d-%02d', $mo, $dy);
            if (isset(self::$gregorianRules[$monthDay])) {
                $r = self::$gregorianRules[$monthDay];
                $festivals[] = [
                    'date'       => $dateStr,
                    'name'       => $r->name, 'name_hi' => $r->name,
                    'type'       => $r->type, 'category' => $r->category,
                    'tithi'      => 'Gregorian Fixed', 'paksha' => '', 'tithi_num' => 0, 'masa' => '',
                    'sunrise'    => $ssRise, 'sunset' => $ssSet, 'icon' => $r->icon,
                    'significance' => $r->significance,
                    'rituals'    => json_decode($r->rituals, true) ?? [],
                    'mantra'     => $r->mantra ?? '', 'details' => $r->details ?? '',
                ];
            }

            updatePrev:
            $prevDateStr  = $dateStr;
            $prevNum      = $num;
            $prevPaksha   = $paksha;
            $prevMasaName = $masaName;
        }

        usort($festivals, fn($a, $b) => $a['date'] <=> $b['date']);

        // ── De-duplicate annual festivals across an Adhik Maas ───────────────
        $recurring = [
            'ekadashi','pradosh','chaturthi','kalashtami','durgaashtami',
            'masik_shivratri','sankranti','purnima','amavasya','shraddha','satyanarayan',
        ];
        $seen      = [];
        $festivals = array_values(array_filter($festivals, function ($f) use (&$seen, $recurring) {
            if (in_array($f['category'] ?? '', $recurring, true)) return true;
            $name = $f['name'] ?? '';
            if (isset($seen[$name])) return false;
            $seen[$name] = true;
            return true;
        }));

        return ['festivals' => $festivals, 'count' => count($festivals), 'year' => $year];
    }

    // ── Display helpers ───────────────────────────────────────────────────────


    private const CAT_CSS = [
        'ekadashi'        => 'fpc-ekadashi',
        'satyanarayan'    => 'fpc-satyanarayan',
        'pradosh'         => 'fpc-pradosh',
        'masik_shivratri' => 'fpc-masik_shivratri',
        'chaturthi'       => 'fpc-chaturthi',
        'kalashtami'      => 'fpc-kalashtami',
        'durgaashtami'    => 'fpc-durgaashtami',
        'festival'        => 'fpc-festival',
        'purnima'         => 'fpc-purnima',
        'amavasya'        => 'fpc-amavasya',
        'navratri'        => 'fpc-navratri',
        'sankranti'       => 'fpc-sankranti',
        'jayanti'         => 'fpc-jayanti',
        'shraddha'        => 'fpc-shraddha',
        'national'        => 'fpc-national',
        'christian'       => 'fpc-national',
    ];

    private const MONTH_NAMES = [
        '','January','February','March','April','May','June',
        'July','August','September','October','November','December',
    ];

    private const DAY_ABBR = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    private const ALIAS_MAP = [
        'trayodashi' => 'pradosh',
        'shivratri'  => 'masik_shivratri',
        'ashtami'    => 'durgaashtami',
    ];

    /**
     * Prepare a display-ready data structure for the festival view.
     * All grouping, CSS-class assignment, and JSON encoding happens here.
     */
    public static function prepareForView(array $festivals, string $activeCategory = 'all'): array
    {
        $filtered = ($activeCategory === 'all') ? $festivals : array_values(array_filter(
            $festivals,
            fn($f) => ($f['category'] ?? '') === $activeCategory
                   || ($f['category'] ?? '') === (self::ALIAS_MAP[$activeCategory] ?? $activeCategory)
        ));

        if (empty($filtered)) {
            return ['empty' => true, 'message' => empty($festivals) ? 'No festivals found.' : 'No festivals in this category.'];
        }

        $count = count($filtered);
        $year  = substr($filtered[0]['date'], 0, 4);

        // Group by month number, then build display items per month
        $grouped = [];
        foreach ($filtered as $f) {
            $moNum = (int)substr($f['date'], 5, 2);
            $grouped[$moNum][] = $f;
        }
        ksort($grouped);

        $months = [];
        foreach ($grouped as $moNum => $items) {
            $cards = [];
            foreach ($items as $f) {
                $dt       = new \DateTime($f['date']);
                $cat      = $f['category'] ?? 'festival';
                $hasDetail = !empty($f['details']) || !empty($f['rituals']) || !empty($f['mantra']);
                $cards[] = [
                    'dayNum'     => (int)$dt->format('d'),
                    'dayAbbr'    => self::DAY_ABBR[(int)$dt->format('w')],
                    'cssClass'   => self::CAT_CSS[$cat] ?? 'fpc-default',
                    'chipLabel'  => self::$chipLabels[$cat] ?? ucfirst($cat),
                    'name'       => $f['name']        ?? '',
                    'tithi'      => $f['tithi']        ?? '',
                    'masa'       => $f['masa']         ?? '',
                    'significance'=> $f['significance'] ?? '',
                    'sunrise'    => $f['sunrise']      ?? '',
                    'sunset'     => $f['sunset']       ?? '',
                    'hasDetail'  => $hasDetail,
                    'detailJson' => htmlspecialchars(json_encode([
                        'name'         => $f['name']        ?? '',
                        'name_hi'      => $f['name_hi']     ?? '',
                        'date'         => $f['date']         ?? '',
                        'tithi'        => $f['tithi']        ?? '',
                        'masa'         => $f['masa']         ?? '',
                        'significance' => $f['significance'] ?? '',
                        'details'      => $f['details']      ?? '',
                        'rituals'      => $f['rituals']      ?? [],
                        'mantra'       => $f['mantra']       ?? '',
                        'sunrise'      => $f['sunrise']      ?? '',
                        'sunset'       => $f['sunset']       ?? '',
                        'type'         => $f['type']         ?? '',
                        'vidhiTitle'   => $f['vidhiTitle']   ?? '',
                        'cat'          => $cat,
                        'accent'       => '#0f766e',
                    ]), ENT_QUOTES, 'UTF-8'),
                ];
            }
            $months[] = [
                'name'  => self::MONTH_NAMES[$moNum] ?? '',
                'count' => count($cards),
                'cards' => $cards,
            ];
        }

        return [
            'empty'  => false,
            'year'   => $year,
            'count'  => $count,
            'months' => $months,
        ];
    }
}
