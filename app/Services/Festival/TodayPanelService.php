<?php

namespace App\Services\Festival;

    use App\Services\Planetary\AstroCalculator;
    use App\Services\Festival\HinduFestivalCalculator;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Cache;

    class TodayPanelService
    {
        private static ?array $weekdayVrats     = null;
        private static ?array $tithiVrats       = null;
        private static ?array $nakshatraMuhurta = null;
        private static ?array $yogaAdvice       = null;
        private static ?array $festCategoryMap  = null;
        private static ?array $signLordsCache   = null;
        private static array  $yearFestsCache   = [];

        private static function loadLookupData(): void
        {
            if (self::$weekdayVrats !== null) return;

            $cached = Cache::get('today_panel_lookup');
            if ($cached) {
                self::$weekdayVrats     = $cached['weekdayVrats'];
                self::$tithiVrats       = $cached['tithiVrats'];
                self::$nakshatraMuhurta = $cached['nakshatraMuhurta'];
                self::$yogaAdvice       = $cached['yogaAdvice'];
                self::$festCategoryMap  = $cached['festCategoryMap'];
                self::$signLordsCache   = $cached['signLordsCache'];
                return;
            }

            self::$weekdayVrats = [];
            foreach (DB::table('weekdays')->select('dow_index', 'vrats')->get() as $w) {
                self::$weekdayVrats[(int)$w->dow_index] = json_decode($w->vrats ?? '[]', true) ?? [];
            }

            self::$tithiVrats = [];
            foreach (DB::table('tithis')
                ->whereNotNull('vrat_name')
                ->select('paksha', 'tithi_number', 'vrat_name', 'vrat_deity', 'vrat_benefit', 'vrat_ritual', 'vrat_mantra', 'vrat_color')
                ->get() as $t) {
                self::$tithiVrats[] = [
                    $t->paksha, (int)$t->tithi_number,
                    $t->vrat_name, $t->vrat_deity, $t->vrat_benefit,
                    $t->vrat_ritual, $t->vrat_mantra, $t->vrat_color,
                ];
            }

            self::$nakshatraMuhurta = [];
            foreach (DB::table('nakshatras')
                ->select('name', 'muhurta_quality', 'good_for', 'avoid', 'display_color')
                ->get() as $n) {
                self::$nakshatraMuhurta[$n->name] = [
                    'quality'  => $n->muhurta_quality ?? 'Mixed',
                    'good_for' => $n->good_for ?? 'General activities',
                    'avoid'    => $n->avoid ?? 'Nothing specific',
                    'color'    => $n->display_color ?? '#1d4e6f',
                ];
            }

            self::$yogaAdvice = [];
            foreach (DB::table('yogas')->select('classification', 'advice_text', 'mood_label', 'color_hex')
                        ->whereNotNull('advice_text')->distinct()->get() as $y) {
                self::$yogaAdvice[$y->classification] = [$y->advice_text, $y->mood_label, $y->color_hex];
            }
            if (empty(self::$yogaAdvice)) {
                self::$yogaAdvice = ['Subha' => ['Excellent time for new beginnings and auspicious works', 'auspicious', '#2e7a40']];
            }

            self::$festCategoryMap = [];
            foreach (DB::table('festival_rules')->select('category', 'display_group')
                        ->whereNotNull('display_group')->distinct()->get() as $f) {
                self::$festCategoryMap[strtolower($f->category)] = $f->display_group;
            }

            self::$signLordsCache = DB::table('zodiac_signs')
                ->join('planets', 'zodiac_signs.lord_planet_id', '=', 'planets.id')
                ->orderBy('zodiac_signs.sort_order')
                ->pluck('planets.name')
                ->toArray();

            Cache::put('today_panel_lookup', [
                'weekdayVrats'     => self::$weekdayVrats,
                'tithiVrats'       => self::$tithiVrats,
                'nakshatraMuhurta' => self::$nakshatraMuhurta,
                'yogaAdvice'       => self::$yogaAdvice,
                'festCategoryMap'  => self::$festCategoryMap,
                'signLordsCache'   => self::$signLordsCache,
            ], 3600);
        }

        private static function yearFests(int $yr, float $lat, float $lon, float $utcOff): array
        {
            $key = "{$yr}_" . md5("{$lat}{$lon}{$utcOff}");
            if (isset(self::$yearFestsCache[$key])) {
                return self::$yearFestsCache[$key];
            }
            $cacheKey = "festivals_year_{$key}";

            // 1. Location-specific cache (exact match)
            $fests = Cache::get($cacheKey);

            // 2. Landing-page pre-warmed cache (Delhi) — festival dates are the same
            //    across India ±1 day at most, so this is a safe instant fallback
            if ($fests === null) {
                $fests = Cache::get("landing_festivals_{$yr}");
            }

            // 3. Cold calculation (~500ms); store for 24h so next request is instant
            if ($fests === null) {
                $fests = HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff)['festivals'] ?? [];
                Cache::put($cacheKey, $fests, 86400);
            }

            self::$yearFestsCache[$key] = $fests;
            return $fests;
        }

        // ══════════════════════════════════════════════════════════════
        //  build()
        // ══════════════════════════════════════════════════════════════
        // Build from a pre-computed AstroCalculator::calculate() result (avoids double computation)
        // Pass date/time/location params so buildCore() can fetch festivals & planetary positions.
        public static function buildFromResult(
            array $result,
            int $yr = 0, int $mo = 0, int $dy = 0,
            int $hr = 0, int $mn = 0,
            float $lat = 0.0, float $lon = 0.0, float $utcOff = 0.0
        ): array {
            self::loadLookupData();
            $result['yr']     = $yr;
            $result['mo']     = $mo;
            $result['dy']     = $dy;
            $result['hr']     = $hr;
            $result['mn']     = $mn;
            $result['lat']    = $lat;
            $result['lon']    = $lon;
            $result['utcOff'] = $utcOff;
            return self::buildCore($result);
        }

        public static function build(
            int $yr, int $mo, int $dy,
            int $hr, int $mn,
            float $utcOff, float $lat, float $lon
        ): array {
            self::loadLookupData();
            $result = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);
            $result['yr']     = $yr;
            $result['mo']     = $mo;
            $result['dy']     = $dy;
            $result['hr']     = $hr;
            $result['mn']     = $mn;
            $result['lat']    = $lat;
            $result['lon']    = $lon;
            $result['utcOff'] = $utcOff;
            return self::buildCore($result);
        }

        private static function buildCore(array $result): array
        {
            $ayan    = $result['ayan'];
            $planets = $result['planets'];
            $pancha  = $result['pancha'];
            $ss      = $result['ss'];
            $tk      = $result['tk'];
            $tkRise  = $result['tkRise'];
            $dasha   = $result['dasha'];
            $jd      = $result['jd'];
            $yr      = $result['yr']     ?? 0;
            $mo      = $result['mo']     ?? 0;
            $dy      = $result['dy']     ?? 0;
            $hr      = $result['hr']     ?? 0;
            $mn      = $result['mn']     ?? 0;
            $lat     = $result['lat']    ?? 0.0;
            $lon     = $result['lon']    ?? 0.0;
            $utcOff  = $result['utcOff'] ?? 0.0;

            $activeTK = $tkRise ?? $tk;

            $sunrise = '—'; $sunset = '—'; $dayLen = '—';
            if ($ss && !$ss['polar']) {
                if ($ss['rise'] !== null) $sunrise = AstroCalculator::decToHMS($ss['rise']);
                if ($ss['set']  !== null) $sunset  = AstroCalculator::decToHMS($ss['set']);
                $dlH    = (int)$ss['dayLength'];
                $dlM    = (int)round(($ss['dayLength'] - $dlH) * 60);
                $dayLen = "{$dlH}h {$dlM}m";
            }

            $tithi       = $activeTK['tithi'];
            $karana      = $activeTK['karana'];
            $vara        = $pancha['vara'];
            $moonNak     = $pancha['moonNak'];
            $yoga        = $pancha['yoga'];
            $tithiPaksha = $tithi['paksha'];
            $tithiNum    = $tithi['num'];
            $dow         = $pancha['varaIdx'];

            $nakName  = $moonNak['n'];
            $nakHints = self::$nakshatraMuhurta[$nakName] ?? [
                'quality' => 'Mixed', 'good_for' => 'General activities',
                'avoid' => 'Nothing specific', 'color' => '#1d4e6f',
            ];
            $yogaAdvice = self::$yogaAdvice[$yoga['cls']] ?? self::$yogaAdvice['Subha'] ?? ['—', 'neutral', '#888'];

            $vrats = [];
            foreach ((self::$weekdayVrats[$dow] ?? []) as $v) {
                if (isset($v['conditional'])) {
                    $cond = $v['conditional'];
                    $include = false;
                    if ($cond === 'trayodashi' && $tithiNum === 13) $include = true;
                    if ($cond === 'ashtami'    && $tithiNum === 8)  $include = true;
                    if (!$include) continue;
                }
                $vrats[] = $v;
            }
            foreach (self::$tithiVrats as $tv) {
                [$pak, $num, $name, $deity, $benefit, $ritual, $mantra, $color] = $tv;
                if ($pak === $tithiPaksha && $num === $tithiNum) {
                    $vrats[] = compact('name', 'deity', 'benefit', 'ritual', 'mantra', 'color');
                }
            }
            $seen = []; $uniqueVrats = [];
            foreach ($vrats as $v) {
                if (!in_array($v['name'], $seen, true)) {
                    $seen[] = $v['name']; $uniqueVrats[] = $v;
                }
            }

            $todayFests    = self::fetchTodayFestivalsCategorized($yr, $mo, $dy, $lat, $lon, $utcOff);
            $upcomingFests = self::fetchUpcomingFestivals($yr, $mo, $dy, $lat, $lon, $utcOff, 15);
            $pastFests     = self::fetchPastFestivals($yr, $mo, $dy, $lat, $lon, $utcOff, 15);

            $ekadashiInfo = null;
            if ($tithiNum === 11) {
                $allEk   = AstroCalculator::getEkadashiYear($yr, $lat, $lon, $utcOff);
                $dateStr = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
                foreach ($allEk as $ek) {
                    if ($ek['date'] === $dateStr) { $ekadashiInfo = $ek; break; }
                }
            }

            $auspiciousHints = self::buildAuspiciousHints($vara, $yoga, $moonNak, $tithi);

            $moonSider    = $planets['moon']['sider'];
            $moonSignIdx  = (int)floor($moonSider / 30);
            $vedicSigns   = AstroCalculator::getVedicSigns();
            $moonSign     = $vedicSigns[$moonSignIdx];
            $moonSignLord = self::$signLordsCache[$moonSignIdx] ?? '—';

            $ascSider     = $result['ascSider'];
            $lagnaSignIdx = (int)floor($ascSider / 30);
            $lagnaSign    = $vedicSigns[$lagnaSignIdx];

            $dashaSummary = "Dasha Lord: {$dasha['lord']} · {$dasha['yrs']}y {$dasha['mos']}m remaining";

            $panchaDetails = [
                'tithi' => [
                    'name'   => $tithi['n'],
                    'paksha' => $tithiPaksha,
                    'num'    => $tithiNum,
                    'lord'   => $tithi['lord'],
                    'deity'  => $tithi['deity'],
                    'nature' => $tithi['nature'],
                    'elong'  => round($activeTK['elong'], 2),
                    'prog'   => round($activeTK['tithiProg'] * 100, 1),
                    'half'   => $activeTK['tithiHalf'],
                ],
                'vara' => [
                    'name'      => $vara['n'],
                    'en'        => $vara['en'],
                    'lord'      => $vara['lord'],
                    'sym'       => $vara['sym'],
                    'color'     => $vara['color'],
                    'nature'    => $vara['nature'],
                    'ausp'      => $vara['classification'],
                    'classNote' => $vara['classNote'],
                    'deity'     => $vara['deity'],
                    'deityNote' => $vara['deityNote'],
                    'auspNote'  => $vara['auspicious'],
                    'horaLord'  => $vara['horaLord'],
                ],
                'nakshatra' => [
                    'name'    => $nakName,
                    'lord'    => $moonNak['l'],
                    'deity'   => $moonNak['d'],
                    'pada'    => $pancha['nakPada'],
                    'gana'    => $moonNak['gana'],
                    'yoni'    => $moonNak['yoni'],
                    'nadi'    => $moonNak['nadi'],
                    'tattva'  => $moonNak['tattva'],
                    'quality' => $nakHints['quality'],
                    'goodFor' => $nakHints['good_for'],
                    'avoid'   => $nakHints['avoid'],
                    'color'   => $nakHints['color'],
                    'num'     => $pancha['nakIdx'] + 1,
                    'prog'    => round(($pancha['nakProg'] ?? 0) * 100, 1),
                ],
                'yoga' => [
                    'name'   => $yoga['n'],
                    'nature' => $yoga['nature'],
                    'lord'   => $yoga['lord'],
                    'cls'    => $yoga['cls'],
                    'deity'  => $yoga['deity'],
                    'advice' => $yogaAdvice[0],
                    'mood'   => $yogaAdvice[1],
                    'color'  => $yogaAdvice[2],
                    'desc'   => $yoga['desc'],
                    'num'    => $pancha['yogaIdx'] + 1,
                    'prog'   => round(($pancha['yogaProg'] ?? 0) * 100, 1),
                ],
                'karana' => [
                    'name'   => $karana['n'],
                    'type'   => $karana['type'],
                    'lord'   => $karana['lord'],
                    'nature' => $karana['nature'],
                    'favour' => $karana['favour'] ?? '—',
                    'deity'  => $karana['deity'] ?? $karana['lord'],
                    'slot'   => $activeTK['karanaSlot'],
                    'half'   => $activeTK['tithiHalf'],
                ],
            ];

            $upcomingPlanets = self::buildUpcomingPlanetaryPositions(
                $yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon, $planets, $ayan, $jd
            );

            return [
                'date'              => sprintf('%02d %s %04d', $dy, self::monthName($mo), $yr),
                'dateISO'           => sprintf('%04d-%02d-%02d', $yr, $mo, $dy),
                'sunrise'           => $sunrise,
                'sunset'            => $sunset,
                'dayLength'         => $dayLen,
                'panchanga'         => $panchaDetails,
                'panchaDetails'     => $panchaDetails,
                'moon'              => [
                    'sign'      => $moonSign,
                    'signLord'  => $moonSignLord,
                    'nakshatra' => $nakName,
                    'pada'      => $pancha['nakPada'],
                    'paksha'    => $tithiPaksha,
                    'tithiNum'  => $tithiNum,
                    'sider'     => round($moonSider, 2),
                ],
                'lagna'             => ['sign' => $lagnaSign, 'sider' => round($ascSider, 2)],
                'planetPositions'   => self::buildPlanetPositionsSummary($planets, $ayan, $vedicSigns),
                'dasha'             => $dashaSummary,
                'vrats'             => $uniqueVrats,
                'todayFestivals'    => $todayFests,
                'festivals'         => array_merge(
                    $todayFests['vrat'] ?? [], $todayFests['parv'] ?? [],
                    $todayFests['jayanti'] ?? [], $todayFests['other'] ?? []
                ),
                'upcomingFestivals' => $upcomingFests,
                'pastFestivals'     => $pastFests,
                'ekadashi'          => $ekadashiInfo,
                'auspicious'        => $auspiciousHints,
                'muhurtaQuality'    => self::computeMuhurtaQuality($yoga, $moonNak, $tithi, $vara),
                'upcomingPlanets'   => $upcomingPlanets,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        //  Private helpers
        // ══════════════════════════════════════════════════════════════

        private static function fetchTodayFestivalsCategorized(
            int $yr, int $mo, int $dy,
            float $lat, float $lon, float $utcOff
        ): array {
            $result = ['vrat' => [], 'parv' => [], 'jayanti' => [], 'other' => []];
            try {
                $festivals = self::yearFests($yr, $lat, $lon, $utcOff);
                $today     = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
                foreach ($festivals as $f) {
                    if (($f['date'] ?? '') !== $today) continue;
                    $fType = strtolower($f['type'] ?? 'festival');
                    $cat   = self::$festCategoryMap[$fType] ?? 'parv';
                    $result[$cat][] = $f;
                }
            } catch (\Throwable $e) {}
            return $result;
        }

        private static function fetchUpcomingFestivals(
            int $yr, int $mo, int $dy,
            float $lat, float $lon, float $utcOff,
            int $days = 15
        ): array {
            $upcoming = [];
            try {
                $festivals = self::yearFests($yr, $lat, $lon, $utcOff);
                $today     = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
                $limit     = date('Y-m-d', strtotime($today . " +{$days} days"));
                foreach ($festivals as $f) {
                    $fDate = $f['date'] ?? '';
                    if ($fDate > $today && $fDate <= $limit) $upcoming[] = $f;
                }
                usort($upcoming, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
                $upcoming = array_slice($upcoming, 0, $days);
            } catch (\Throwable $e) {}
            return $upcoming;
        }

        private static function fetchPastFestivals(
            int $yr, int $mo, int $dy,
            float $lat, float $lon, float $utcOff,
            int $days = 15
        ): array {
            $past = [];
            try {
                $festivals = self::yearFests($yr, $lat, $lon, $utcOff);
                $today     = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
                $limit     = date('Y-m-d', strtotime($today . " -{$days} days"));
                foreach ($festivals as $f) {
                    $fDate = $f['date'] ?? '';
                    if ($fDate >= $limit && $fDate < $today) $past[] = $f;
                }
                usort($past, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
                $past = array_slice($past, 0, $days);
            } catch (\Throwable $e) {}
            return $past;
        }

        private static function buildPlanetPositionsSummary(
            array $planets, float $ayan, array $vedicSigns
        ): array {
            $naks = AstroCalculator::getNakshatras();
            $out  = [];
            foreach ($planets as $pid => $p) {
                $sider     = $p['sider'];
                $signIdx   = (int)floor($sider / 30);
                $nakIdx    = (int)floor($sider / (360.0 / 27));
                $nak       = $naks[$nakIdx];
                $degInSign = fmod($sider, 30);
                $out[$pid] = [
                    'name'    => ucfirst($pid),
                    'sign'    => $vedicSigns[$signIdx] ?? '—',
                    'signIdx' => $signIdx,
                    'nak'     => $nak['n'],
                    'nakLord' => $nak['l'],
                    'pada'    => $p['pada'],
                    'deg'     => round($degInSign, 2),
                    'sider'   => round($sider, 2),
                    'retro'   => $p['retro'],
                    'mantra'  => '',
                ];
            }
            return $out;
        }

        private static function buildUpcomingPlanetaryPositions(
            int $yr, int $mo, int $dy, int $hr, int $mn,
            float $utcOff, float $lat, float $lon,
            array $currentPlanets, float $ayan, float $jd0
        ): array {
            $changes    = [];
            $n360       = fn(float $x) => fmod(fmod($x, 360.0) + 360.0, 360.0);
            $vedicSigns = AstroCalculator::getVedicSigns();

            foreach (['sun', 'moon', 'mercury', 'venus', 'mars'] as $pid) {
                $pNow    = $currentPlanets[$pid] ?? null;
                if (!$pNow) continue;
                $signNow = (int)floor($pNow['sider'] / 30);
                for ($dOffset = 1; $dOffset <= 30; $dOffset++) {
                    $futureJd = $jd0 + $dOffset;
                    try {
                        $futTrop  = match ($pid) {
                            'sun'   => AstroCalculator::sunLongitude($futureJd),
                            'moon'  => AstroCalculator::moonLongitude($futureJd),
                            default => AstroCalculator::planetLongitude($futureJd, $pid),
                        };
                        $futAyan  = AstroCalculator::lahiriAyanamsa($futureJd);
                        $futSider = $n360($futTrop - $futAyan);
                        $futSign  = (int)floor($futSider / 30);
                        if ($futSign !== $signNow) {
                            $ts = (int)(($futureJd - 2440587.5) * 86400);
                            $changes[] = [
                                'pid'      => $pid,
                                'name'     => ucfirst($pid),
                                'fromSign' => $vedicSigns[$signNow] ?? '—',
                                'toSign'   => $vedicSigns[$futSign] ?? '—',
                                'date'     => gmdate('Y-m-d', $ts),
                                'daysAway' => $dOffset,
                            ];
                            break;
                        }
                        $signNow = $futSign;
                    } catch (\Throwable $e) { break; }
                }
            }
            usort($changes, fn($a, $b) => $a['daysAway'] <=> $b['daysAway']);
            return $changes;
        }

        private static function buildAuspiciousHints(
            array $vara, array $yoga, array $moonNak, array $tithi
        ): array {
            $hints = [];
            if ($vara['classification'] === 'Guru')       $hints[] = ['type' => 'Best',    'text' => 'Guruvara — ideal for spiritual practices, education, marriage & sacred rituals', 'color' => '#2e7a40'];
            elseif ($vara['classification'] === 'Saumya') $hints[] = ['type' => 'Good',    'text' => 'Saumya Vara — favourable for gentle and creative activities',                    'color' => '#1d5a8a'];
            else                                           $hints[] = ['type' => 'Caution', 'text' => 'Ugra Vara — channel energy toward courageous, decisive tasks',                  'color' => '#b83020'];
            $nature = $tithi['nature'] ?? '';
            if (str_contains($nature, 'Purna'))     $hints[] = ['type' => 'Purna',   'text' => 'Purna Tithi — fullness; excellent for all auspicious acts', 'color' => '#2e7a40'];
            elseif (str_contains($nature, 'Rikta')) $hints[] = ['type' => 'Caution', 'text' => 'Rikta Tithi — avoid major new beginnings today',            'color' => '#c47a20'];
            elseif (str_contains($nature, 'Jaya'))  $hints[] = ['type' => 'Victory', 'text' => 'Jaya Tithi — excellent for competitive activities',          'color' => '#1d4e6f'];
            if ($yoga['cls'] === 'Mahavisha')       $hints[] = ['type' => 'Inauspicious', 'text' => ($yoga['n'] ?? '') . ' Yoga — highly inauspicious; rest recommended', 'color' => '#b83020'];
            elseif ($yoga['cls'] === 'Ashubha')     $hints[] = ['type' => 'Caution',      'text' => ($yoga['n'] ?? '') . ' Yoga — proceed carefully',                     'color' => '#c47a20'];
            return $hints;
        }

        private static function computeMuhurtaQuality(
            array $yoga, array $moonNak, array $tithi, array $vara
        ): array {
            $score = 0; $max = 4;
            if ($yoga['cls'] === 'Subha')         $score++;
            if ($yoga['nature'] === 'Auspicious') $score++;
            $qual = $moonNak['quality'] ?? '';
            foreach (['Kshipra', 'Dhruva', 'Mridu', 'Chara'] as $gq) {
                if (str_contains($qual, $gq)) { $score++; break; }
            }
            if ($vara['classification'] === 'Guru')       $score++;
            elseif ($vara['classification'] === 'Saumya') $score += 0.5;
            $nature = $tithi['nature'] ?? '';
            if (str_contains($nature, 'Purna'))     $score++;
            elseif (str_contains($nature, 'Jaya'))  $score += 0.5;
            elseif (str_contains($nature, 'Rikta')) $score -= 0.5;
            $score = max(0, min($max, $score));
            $pct   = round($score / $max * 100);
            if ($pct >= 75) return ['label' => 'Excellent',   'pct' => $pct, 'color' => '#5a6e00', 'stars' => '***'];
            if ($pct >= 50) return ['label' => 'Good',        'pct' => $pct, 'color' => '#1a4a7a', 'stars' => '**'];
            if ($pct >= 25) return ['label' => 'Mixed',       'pct' => $pct, 'color' => '#7a5800', 'stars' => '*'];
            return                 ['label' => 'Challenging', 'pct' => $pct, 'color' => '#7a1a1a', 'stars' => '-'];
        }

        // ══════════════════════════════════════════════════════════════
        //  prepareForView()  — augments build() output with display-ready
        //  fields so Blade views need zero @php blocks.
        // ══════════════════════════════════════════════════════════════
        public static function prepareForView(array $d): array
        {
            static $MONTHS = ['','January','February','March','April','May','June',
                               'July','August','September','October','November','December'];
            static $PLANET_META = [
                'sun'     => ['label' => 'Sun',     'clr' => '#a04010', 'bg' => '#fff4e8', 'bd' => '#e8b880'],
                'moon'    => ['label' => 'Moon',    'clr' => '#1a3a6a', 'bg' => '#eef2fc', 'bd' => '#9ab0e0'],
                'mercury' => ['label' => 'Mercury', 'clr' => '#1a5a50', 'bg' => '#eef8f5', 'bd' => '#80c8b8'],
                'venus'   => ['label' => 'Venus',   'clr' => '#6a1a5a', 'bg' => '#fceef8', 'bd' => '#d080b8'],
                'mars'    => ['label' => 'Mars',    'clr' => '#8a1a10', 'bg' => '#fceee8', 'bd' => '#e09080'],
                'jupiter' => ['label' => 'Jupiter', 'clr' => '#5a4000', 'bg' => '#fdf8e8', 'bd' => '#d0b878'],
                'saturn'  => ['label' => 'Saturn',  'clr' => '#302848', 'bg' => '#f2eff8', 'bd' => '#a898c0'],
                'rahu'    => ['label' => 'Rahu',    'clr' => '#1a4828', 'bg' => '#eef6f0', 'bd' => '#88c098'],
                'ketu'    => ['label' => 'Ketu',    'clr' => '#502818', 'bg' => '#faf2ee', 'bd' => '#c09888'],
            ];
            static $PLANET_ORDER = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
            static $PAKSHA_MAP   = ['Shukla' => 'Shukla Paksha', 'Krishna' => 'Krishna Paksha'];

            // ── Date parts ───────────────────────────────────────────────
            $dateISO  = $d['dateISO'] ?? date('Y-m-d');
            [$yr, $mo, $dy] = array_map('intval', explode('-', $dateISO));

            // ── Muhurta quality ──────────────────────────────────────────
            $mq       = $d['muhurtaQuality'] ?? [];
            $mqLabel  = $mq['label'] ?? 'Good';
            $mqClass  = strtolower($mqLabel);
            $mqHiMap  = ['excellent' => 'Excellent', 'good' => 'Auspicious', 'mixed' => 'Mixed', 'challenging' => 'Challenging'];

            // ── Panchanga helpers ────────────────────────────────────────
            $panchanga = $d['panchanga'] ?? [];
            $tithi     = $panchanga['tithi']     ?? [];
            $vara      = $panchanga['vara']      ?? [];
            $nak       = $panchanga['nakshatra'] ?? [];
            $yoga      = $panchanga['yoga']      ?? [];
            $karana    = $panchanga['karana']    ?? [];

            $pakshaLabel = $PAKSHA_MAP[$tithi['paksha'] ?? ''] ?? ($tithi['paksha'] ?? '');
            $tithiFull   = $pakshaLabel . ' ' . ($tithi['name'] ?? '—');

            $sunrise = $d['sunrise'] ?? '—';
            $sunset  = $d['sunset']  ?? '—';

            // ── Panchanga "angas" display list ───────────────────────────
            $angas = [
                ['Tithi',     'Tithi · Lunar Day',        $tithiFull,
                    ($tithi['lord'] ?? '—') . ' · ' . ($tithi['nature'] ?? '—'), true],
                ['Vara',      'Vara · Day of Week',        $vara['name']    ?? '—',
                    ($vara['nature'] ?? '—') . ' · ' . ($vara['lord'] ?? ''),    false],
                ['Nakshatra', 'Nakshatra · Lunar Mansion', $nak['name']     ?? '—',
                    'Pada ' . ($nak['pada'] ?? '?') . ' · ' . ($nak['lord'] ?? ''), false],
                ['Yoga',      'Yoga · Luni-Solar Yoga',    $yoga['name']    ?? '—',
                    ($yoga['nature'] ?? '—') . ' · ' . ($yoga['lord'] ?? ''),    false],
                ['Karana',    'Karana · Half Tithi',        $karana['name']  ?? '—',
                    ($karana['type'] ?? '—') . ' · ' . ($karana['lord'] ?? ''), false],
            ];

            // ── Panchanga detail cards ───────────────────────────────────
            $detailCards = [
                ['#a03818', 'Tithi Details', [
                    ['Paksha',   $PAKSHA_MAP[$tithi['paksha'] ?? ''] ?? ($tithi['paksha'] ?? '')],
                    ['Number',   ($tithi['num'] ?? '') . '/15'],
                    ['Deity',    $tithi['deity']   ?? '—'],
                    ['Lord',     $tithi['lord']    ?? '—'],
                    ['Nature',   $tithi['nature']  ?? '—'],
                    ['Progress', ($tithi['prog']   ?? '0') . '%'],
                ]],
                ['#1a3a6a', 'Vara Details', [
                    ['Deity',      $vara['deity']    ?? '—'],
                    ['Hora Lord',  $vara['horaLord'] ?? '—'],
                    ['Class',      $vara['ausp']     ?? '—'],
                    ['Auspicious', $vara['auspNote'] ?? '—'],
                ]],
                ['#1a5a50', 'Nakshatra Details', [
                    ['Gana',    $nak['gana']    ?? '—'],
                    ['Yoni',    $nak['yoni']    ?? '—'],
                    ['Nadi',    $nak['nadi']    ?? '—'],
                    ['Tattva',  $nak['tattva']  ?? '—'],
                    ['Quality', $nak['quality'] ?? '—'],
                ]],
                ['#5a4000', 'Yoga Details', [
                    ['Deity',  $yoga['deity']  ?? '—'],
                    ['Class',  $yoga['cls']    ?? '—'],
                    ['Advice', $yoga['advice'] ?? '—'],
                ]],
                ['#302848', 'Karana Details', [
                    ['Type',   $karana['type']   ?? '—'],
                    ['Deity',  $karana['deity']  ?? '—'],
                    ['Favour', $karana['favour'] ?? '—'],
                ]],
            ];

            // ── Observance categories with enriched items ────────────────
            $todayFests = $d['todayFestivals'] ?? [];
            $obsCats    = [];
            foreach ([
                ['vrat',    'Vrat',    'Fasting & Vrats',      '#6a1a5a', 'rgba(106,26,90,.07)'],
                ['parv',    'Parva',   'Festivals & Parvs',    '#8a3000', 'rgba(138,48,0,.07)'],
                ['jayanti', 'Jayanti', 'Jayantis & Birthdays', '#1a3a6a', 'rgba(26,58,106,.07)'],
                ['other',   'Other',   'Other Observances',    '#1a5030', 'rgba(26,80,48,.07)'],
            ] as [$key, $label, $enLabel, $accent, $bg]) {
                $items = $todayFests[$key] ?? [];
                $cnt   = count($items);
                $enriched = [];
                foreach ($items as $f) {
                    $sig = $f['significance'] ?? $f['desc'] ?? '';
                    $enriched[] = [
                        'name'     => $f['name']   ?? '—',
                        'sigTrunc' => substr($sig, 0, 100),
                        'sigMore'  => strlen($sig) > 100,
                        'mantra'   => $f['mantra'] ?? '',
                    ];
                }
                $obsCats[] = [
                    'label'      => $label,
                    'enLabel'    => $enLabel,
                    'accent'     => $accent,
                    'bg'         => $bg,
                    'items'      => $enriched,
                    'cnt'        => $cnt,
                    'cntBg'      => $cnt ? $accent : 'var(--soft)',
                    'cntColor'   => $cnt ? '#fff' : 'var(--ink4)',
                    'cntDisplay' => $cnt ?: '—',
                ];
            }

            // ── All today's festivals merged (for hero pills) ─────────────
            $allTodayFests = array_merge(
                $todayFests['vrat']    ?? [], $todayFests['parv'] ?? [],
                $todayFests['jayanti'] ?? [], $todayFests['other'] ?? []
            );

            // ── Planet display — ordered with meta merged in ──────────────
            $planets = $d['planetPositions'] ?? [];
            $planetDisplay = [];
            foreach ($PLANET_ORDER as $pid) {
                $p  = $planets[$pid] ?? null;
                $pm = $PLANET_META[$pid] ?? null;
                if ($p && $pm) {
                    $planetDisplay[] = array_merge($p, $pm, ['pid' => $pid]);
                }
            }

            // ── Dot-colour helper (by festival type) ──────────────────────
            $dotColor = static function (string $type): string {
                $t = strtolower($type);
                if (str_contains($t, 'vrat'))    return '#6a1a5a';
                if (str_contains($t, 'jayanti')) return '#1a3a6a';
                return '#8a3000';
            };

            // ── Upcoming planetary sign changes ───────────────────────────
            $upcomingPlanetsDisplay = [];
            foreach ($d['upcomingPlanets'] ?? [] as $ch) {
                $pm2   = $PLANET_META[$ch['pid']] ?? null;
                $days2 = (int)($ch['daysAway'] ?? 0);
                $upcomingPlanetsDisplay[] = array_merge($ch, [
                    'clr'         => $pm2['clr'] ?? '#3a2a18',
                    'pmLabel'     => $pm2['label'] ?? ucfirst($ch['pid']),
                    'daysLabel'   => $days2 === 0 ? 'Today' : ($days2 === 1 ? 'Tomorrow' : $days2 . ' days'),
                    'dateDisplay' => self::formatDateDisplay($ch['date'] ?? '', $MONTHS),
                ]);
            }

            // ── Past festivals ────────────────────────────────────────────
            $pastFestsDisplay = array_map(
                fn($f) => array_merge($f, [
                    'dotColor'    => $dotColor($f['type'] ?? ''),
                    'dateDisplay' => self::formatDateDisplay($f['date'] ?? '', $MONTHS),
                ]),
                array_slice($d['pastFestivals'] ?? [], 0, 15)
            );

            // ── Upcoming festivals ────────────────────────────────────────
            $upcomingFestsDisplay = [];
            foreach (array_slice($d['upcomingFestivals'] ?? [], 0, 15) as $f) {
                $sig   = $f['significance'] ?? $f['desc'] ?? '';
                $fDate = $f['date'] ?? '';
                $daysA = ($fDate && $dateISO)
                    ? (int)round((strtotime($fDate) - strtotime($dateISO)) / 86400) : 0;
                $upcomingFestsDisplay[] = array_merge($f, [
                    'dotColor'    => $dotColor($f['type'] ?? ''),
                    'dateDisplay' => self::formatDateDisplay($fDate, $MONTHS),
                    'sigTrunc'    => substr($sig, 0, 75),
                    'sigMore'     => strlen($sig) > 75,
                    'daysLabel'   => $daysA === 0 ? 'Today' : ($daysA === 1 ? 'Tomorrow' : $daysA . ' days'),
                ]);
            }

            // ── Progress rings (SVG maths pre-computed) ───────────────────
            $ringC = 2 * M_PI * 34.0;
            $rings = [];
            foreach ([
                ['prog' => (float)($tithi['prog'] ?? 0), 'col' => '#c48a2f', 'label' => 'Tithi',    'val' => ($tithi['num'] ?? '') . '/15'],
                ['prog' => (float)($nak['prog']   ?? 0), 'col' => '#1d6aaa', 'label' => 'Nakshatra','val' => ($nak['num']   ?? '') . '/27'],
                ['prog' => (float)($yoga['prog']  ?? 0), 'col' => '#2d7a3a', 'label' => 'Yoga',     'val' => ($yoga['num']  ?? '') . '/27'],
            ] as $rd) {
                $prog  = max(0.0, min(100.0, $rd['prog']));
                $rings[] = [
                    'prog'       => $prog,
                    'pct'        => (int)round($prog),
                    'col'        => $rd['col'],
                    'label'      => $rd['label'],
                    'val'        => $rd['val'],
                    'dasharray'  => round($ringC, 2),
                    'dashoffset' => round($ringC * (1 - $prog / 100), 2),
                ];
            }

            // ── Moon & footer ─────────────────────────────────────────────
            $moon = $d['moon'] ?? [];

            return array_merge($d, [
                'yr'                     => $yr,
                'mo'                     => $mo,
                'dy'                     => $dy,
                'monthName'              => $MONTHS[$mo] ?? '',
                'mqColor'                => $mq['color'] ?? '#1a4a7a',
                'mqHi'                   => $mqHiMap[$mqClass] ?? $mqLabel,
                'mqPct'                  => (int)($mq['pct'] ?? 50),
                'sr'                     => strlen($sunrise) >= 5 ? substr($sunrise, 0, 5) : $sunrise,
                'st'                     => strlen($sunset)  >= 5 ? substr($sunset, 0, 5) : $sunset,
                'tithiFull'              => $tithiFull,
                'varaName'               => $vara['name']    ?? '—',
                'varaLord'               => $vara['lord']    ?? '',
                'varaNature'             => $vara['nature']  ?? '',
                'allTodayFests'          => $allTodayFests,
                'angas'                  => $angas,
                'detailCards'            => $detailCards,
                'obsCats'                => $obsCats,
                'planetDisplay'          => $planetDisplay,
                'upcomingPlanetsDisplay' => $upcomingPlanetsDisplay,
                'pastFestsDisplay'       => $pastFestsDisplay,
                'upcomingFestsDisplay'   => $upcomingFestsDisplay,
                'moonPaksha'             => $PAKSHA_MAP[$moon['paksha'] ?? ''] ?? ($moon['paksha'] ?? ''),
                'tithiNumD'              => (int)($moon['tithiNum'] ?? 0),
                'moonSignKey'            => $moon['sign']       ?? '—',
                'moonNakDisplay'         => $moon['nakshatra']  ?? '—',
                'dashaSum'               => $d['dasha']         ?? '—',
                'lagnaSign'              => ($d['lagna']        ?? [])['sign'] ?? '—',
                'rings'                  => $rings,
            ]);
        }

        private static function monthName(int $m): string
        {
            return ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$m] ?? '';
        }

        private static function formatDateDisplay(string $ds, array $months): string
        {
            if (!$ds || strlen($ds) < 10) return $ds;
            return ((int)substr($ds, 8, 2)) . ' ' . ($months[(int)substr($ds, 5, 2)] ?? '') . ' ' . substr($ds, 0, 4);
        }
    }

