<?php

namespace App\Features\Festival;

    use App\Features\Planetary\AstroCalculator;
    use App\Features\Festival\HinduFestivalCalculator;

    class TodayPanelService
    {
        private const WEEKDAY_VRATS = [
            0 => [
                ['name' => 'Surya Vrat', 'deity' => 'Surya (Sun)', 'benefit' => 'Health, vitality, success in work', 'ritual' => 'Offer red flowers to Sun at sunrise. Chant Aditya Hridayam.', 'mantra' => 'ॐ सूर्याय नमः', 'color' => '#d4760a'],
            ],
            1 => [
                ['name' => 'Somvar Vrat', 'deity' => 'Shiva', 'benefit' => 'Fulfilment of desires, marital happiness', 'ritual' => 'Fast from sunrise to sunset. Offer milk and bel leaves to Shivalinga.', 'mantra' => 'ॐ नमः शिवाय', 'color' => '#1d4e6f'],
                ['name' => 'Pradosh Vrat', 'deity' => 'Shiva-Parvati', 'benefit' => 'Removal of sins, peace and prosperity', 'ritual' => 'If Trayodashi tithi falls today — full Pradosh fast.', 'mantra' => 'ॐ नमः शिवाय', 'color' => '#2a6d9c', 'conditional' => 'trayodashi'],
            ],
            2 => [
                ['name' => 'Mangalvar Vrat', 'deity' => 'Hanuman / Mangal', 'benefit' => 'Courage, protection from enemies, strength', 'ritual' => 'Offer sindoor and red flowers to Hanuman. Eat once.', 'mantra' => 'ॐ हं हनुमते नमः', 'color' => '#b83020'],
                ['name' => 'Kaal Bhairav Vrat', 'deity' => 'Kaal Bhairav', 'benefit' => 'Protection, removal of fear', 'ritual' => 'If Ashtami falls today — special Bhairav puja.', 'mantra' => 'ॐ कालभैरवाय नमः', 'color' => '#8a1810', 'conditional' => 'ashtami'],
            ],
            3 => [
                ['name' => 'Budhvar Vrat', 'deity' => 'Ganesha / Budha', 'benefit' => 'Intelligence, education, business success', 'ritual' => 'Worship Ganesha with green durva grass. Observe fast till noon.', 'mantra' => 'ॐ गं गणपतये नमः', 'color' => '#2e7a6e'],
            ],
            4 => [
                ['name' => 'Guruvar Vrat', 'deity' => 'Vishnu / Brihaspati', 'benefit' => 'Knowledge, wealth, spiritual growth, good children', 'ritual' => 'Wear yellow. Offer yellow flowers to Vishnu. Eat once.', 'mantra' => 'ॐ नमो भगवते वासुदेवाय', 'color' => '#7a5a10'],
            ],
            5 => [
                ['name' => 'Shukravar Vrat', 'deity' => 'Lakshmi / Shukra', 'benefit' => 'Prosperity, love, beauty, happiness in marriage', 'ritual' => 'Worship Lakshmi with lotus. Offer white flowers. Eat once.', 'mantra' => 'ॐ श्रीं महालक्ष्म्यै नमः', 'color' => '#8e3a7a'],
                ['name' => 'Santoshi Mata Vrat', 'deity' => 'Santoshi Mata', 'benefit' => 'Family harmony, fulfilment of all desires', 'ritual' => 'On every Friday — fast and listen to Santoshi Mata Katha.', 'mantra' => 'ॐ सन्तोषी माता नमः', 'color' => '#c47a20'],
            ],
            6 => [
                ['name' => 'Shanivar Vrat', 'deity' => 'Shani (Saturn)', 'benefit' => 'Relief from Sade-Sati, karmic protection', 'ritual' => 'Offer sesame oil to Shani idol. Feed crows. Wear black.', 'mantra' => 'ॐ शं शनैश्चराय नमः', 'color' => '#4a4060'],
                ['name' => 'Hanuman Puja', 'deity' => 'Hanuman', 'benefit' => 'Strength, protection, removal of Shani dosha', 'ritual' => 'Read Hanuman Chalisa 11 times. Offer sindoor and oil.', 'mantra' => 'ॐ हं हनुमते नमः', 'color' => '#b83020'],
            ],
        ];

        private const TITHI_VRATS = [
            ['Shukla',  1,  'Pratipada Vrat',               'Brahma',          'New beginnings, success in new ventures',          'Worship Brahma at sunrise. Light a lamp.',                          'ॐ ब्रह्मणे नमः',          '#c48a2f'],
            ['Shukla',  4,  'Vinayak Chaturthi',            'Ganesha',         'Removal of obstacles, wisdom, prosperity',          'Offer modak to Ganesha. Recite Ganapati Atharvasheersham.',        'ॐ गं गणपतये नमः',         '#2e7a6e'],
            ['Shukla',  5,  'Naga Panchami',                'Nagas',           'Protection from snake bite, family welfare',         'Offer milk to Naga idol. Draw snake images with turmeric.',        'ॐ नागेभ्यो नमः',          '#1a5a1a'],
            ['Shukla',  6,  'Skanda Shashthi',              'Kartikeya',       'Health of children, victory over enemies',           'Observe fast. Worship Kartikeya with red flowers.',                'ॐ षण्मुखाय नमः',          '#b83020'],
            ['Shukla',  8,  'Durgashtami',                  'Durga',           'Courage, destruction of evil, protection',           'Special Durga puja. Recite Durga Saptashati.',                     'ॐ दुर्गायै नमः',          '#8e3a7a'],
            ['Shukla',  11, 'Ekadashi Vrat',                'Vishnu',          'Cleansing of all sins, liberation (Moksha)',          'Complete fast — no grains. Worship Vishnu. Night vigil.',          'ॐ नमो भगवते वासुदेवाय',   '#1d4e6f'],
            ['Shukla',  13, 'Trayodashi / Pradosh',         'Shiva',           'Relief from diseases, sins, great spiritual merit',  'Pradosh fast. Visit Shiva temple at dusk.',                        'ॐ नमः शिवाय',             '#4a4060'],
            ['Shukla',  14, 'Chaturdashi Vrat',             'Shiva',           'Destroys sins accumulated over lifetimes',           'Worship Shiva with bel leaves. Fast till sunset.',                 'ॐ नमः शिवाय',             '#4a4060'],
            ['Shukla',  15, 'Purnima Vrat',                 'Vishnu / Moon',   'Wealth, fullness, blessings from ancestors',          'Take sacred bath. Offer arghya to Moon. Charity.',                'ॐ नमो भगवते वासुदेवाय',   '#1d4e6f'],
            ['Krishna', 4,  'Sankashti Chaturthi',          'Ganesha',         'Removal of obstacles and hardships',                 'Fast till moonrise. Offer durva to Ganesha. See moon.',            'ॐ गं गणपतये नमः',         '#2e7a6e'],
            ['Krishna', 8,  'Kalashtami / Bhairav Ashtami', 'Kaal Bhairav',    'Protection from enemies, fear, evil',                'Worship Bhairav with sindoor. Night vigil.',                       'ॐ कालभैरवाय नमः',        '#4a4060'],
            ['Krishna', 11, 'Ekadashi Vrat',                'Vishnu',          'Liberation from cycle of birth and death',           'Complete fast — no grains. Night-long kirtan.',                    'ॐ नमो भगवते वासुदेवाय',   '#1d4e6f'],
            ['Krishna', 13, 'Masik Shivratri',              'Shiva',           'Blessings of Shiva, release from Pasa (bondage)',    'Fast and Shiva puja. Abhisheka with milk and honey.',              'ॐ नमः शिवाय',             '#4a4060'],
            ['Krishna', 14, 'Maha Shivratri / Shivratri',   'Shiva',           'Liberation, union with Shiva, destruction of ego',   'All-night Shiva vigil. Abhisheka in 4 praharas.',                  'ॐ नमः शिवाय',             '#4a4060'],
            ['Krishna', 15, 'Amavasya Vrat',                'Pitrs / Ancestors','Peace for ancestors, relief from Pitru dosha',      'Tarpana for ancestors. Light lamp at dusk. Charity.',              'ॐ पितृभ्यः नमः',          '#1a3a1a'],
        ];

        private const NAKSHATRA_MUHURTA = [
            'Ashwini'           => ['quality' => 'Kshipra (Swift)',  'good_for' => 'Medical treatments, quick journeys, learning',   'avoid' => 'Long-term commitments',       'color' => '#1a5a1a'],
            'Bharani'           => ['quality' => 'Ugra (Fierce)',    'good_for' => 'Bold actions, fire rituals, destruction',        'avoid' => 'Auspicious ceremonies',       'color' => '#8a2010'],
            'Krittika'          => ['quality' => 'Mishra (Mixed)',   'good_for' => 'Fire ceremonies, cooking, purification',         'avoid' => 'Sensitive negotiations',      'color' => '#c56408'],
            'Rohini'            => ['quality' => 'Dhruva (Fixed)',   'good_for' => 'Coronations, sowing seeds, long-term starts',    'avoid' => 'Nothing — highly auspicious', 'color' => '#2e7a2e'],
            'Mrigashira'        => ['quality' => 'Mridu (Soft)',     'good_for' => 'Romantic activities, arts, learning music',      'avoid' => 'Harsh confrontations',        'color' => '#1d6090'],
            'Ardra'             => ['quality' => 'Tikshna (Sharp)',  'good_for' => 'Destruction, surgery, bold actions',             'avoid' => 'Auspicious new beginnings',   'color' => '#6a2090'],
            'Punarvasu'         => ['quality' => 'Chara (Movable)',  'good_for' => 'Return journeys, second chances, healing',       'avoid' => 'Permanent decisions',         'color' => '#1d8090'],
            'Pushya'            => ['quality' => 'Mridu (Soft)',     'good_for' => 'All auspicious works — the best nakshatra',      'avoid' => 'Marriage (traditionally)',    'color' => '#2e7a6e'],
            'Ashlesha'          => ['quality' => 'Tikshna (Sharp)',  'good_for' => 'Occult practices, trapping enemies',             'avoid' => 'New relationships, journeys', 'color' => '#4a1a6a'],
            'Magha'             => ['quality' => 'Ugra (Fierce)',    'good_for' => 'Ancestor worship, authority, government work',   'avoid' => 'New ventures',               'color' => '#6a3800'],
            'Purva Phalguni'    => ['quality' => 'Ugra (Fierce)',    'good_for' => 'Pleasure, relaxation, arts',                     'avoid' => 'Serious disciplined work',    'color' => '#c56408'],
            'Uttara Phalguni'   => ['quality' => 'Dhruva (Fixed)',   'good_for' => 'Marriage, long-term undertakings',               'avoid' => 'Nothing — very auspicious',   'color' => '#2e8060'],
            'Hasta'             => ['quality' => 'Kshipra (Swift)',  'good_for' => 'Trade, craft, healing, dexterous work',          'avoid' => 'Destructive actions',         'color' => '#1d6aaa'],
            'Chitra'            => ['quality' => 'Mridu (Soft)',     'good_for' => 'Art, jewellery-making, decoration',              'avoid' => 'Confrontations',              'color' => '#8e3a7a'],
            'Swati'             => ['quality' => 'Chara (Movable)',  'good_for' => 'Commerce, travel, learning new skills',          'avoid' => 'Permanent constructions',     'color' => '#1d4e8f'],
            'Vishakha'          => ['quality' => 'Mishra (Mixed)',   'good_for' => 'Competitive activities, goal-setting',           'avoid' => 'Partnership agreements',      'color' => '#b83020'],
            'Anuradha'          => ['quality' => 'Mridu (Soft)',     'good_for' => 'Friendship, groups, travel southward',           'avoid' => 'Solitary activities',         'color' => '#2e6090'],
            'Jyeshtha'          => ['quality' => 'Tikshna (Sharp)',  'good_for' => 'Bold leadership, mantras, protection rituals',   'avoid' => 'New relationships',           'color' => '#7a3a10'],
            'Moola'             => ['quality' => 'Tikshna (Sharp)',  'good_for' => 'Medicine, investigating hidden matters',         'avoid' => 'New positive beginnings',     'color' => '#5a1a1a'],
            'Purva Ashadha'     => ['quality' => 'Ugra (Fierce)',    'good_for' => 'Invigoration, water activities, bold moves',     'avoid' => 'Peaceful negotiations',       'color' => '#1a5a8a'],
            'Uttara Ashadha'    => ['quality' => 'Dhruva (Fixed)',   'good_for' => 'Permanent works, victory in competition',        'avoid' => 'Nothing — highly auspicious', 'color' => '#2e7a40'],
            'Shravana'          => ['quality' => 'Chara (Movable)',  'good_for' => 'Learning, listening, spiritual study',           'avoid' => 'Aggressive activities',       'color' => '#1d4e6f'],
            'Dhanishta'         => ['quality' => 'Chara (Movable)',  'good_for' => 'Music, courage, financial activities',           'avoid' => 'Marriage (traditionally)',    'color' => '#b83020'],
            'Shatabhisha'       => ['quality' => 'Chara (Movable)',  'good_for' => 'Healing, occult, Vedic learning',               'avoid' => 'Public dealings',             'color' => '#1a3a8a'],
            'Purva Bhadrapada'  => ['quality' => 'Ugra (Fierce)',    'good_for' => 'Fierce tasks, ascetic practices',               'avoid' => 'Social events',               'color' => '#4a2a8a'],
            'Uttara Bhadrapada' => ['quality' => 'Dhruva (Fixed)',   'good_for' => 'Marriage, charitable works, rain prayers',       'avoid' => 'Nothing — auspicious',        'color' => '#1d6070'],
            'Revati'            => ['quality' => 'Mridu (Soft)',     'good_for' => 'Travel, completion, nurturing activities',       'avoid' => 'Starting from scratch',       'color' => '#6a3a8a'],
        ];

        private const YOGA_ADVICE = [
            'Mahavisha' => ['Avoid all new starts, contracts, travel', 'rest',       '#b83020'],
            'Ashubha'   => ['Proceed with caution in new undertakings','caution',    '#c47a20'],
            'Subha'     => ['Excellent time for new beginnings and auspicious works','auspicious','#2e7a40'],
        ];

        private const FEST_CATEGORY_MAP = [
            'ekadashi'        => 'vrat',
            'trayodashi'      => 'vrat',
            'satyanarayan'    => 'vrat',
            'pradosh'         => 'vrat',
            'shivratri'       => 'vrat',
            'masik_shivratri' => 'vrat',
            'chaturthi'       => 'vrat',
            'kalashtami'      => 'vrat',
            'durgaashtami'    => 'vrat',
            'ashtami'         => 'vrat',
            'purnima'         => 'parv',
            'amavasya'        => 'parv',
            'festival'        => 'parv',
            'navratri'        => 'parv',
            'sankranti'       => 'parv',
            'diwali'          => 'parv',
            'holi'            => 'parv',
            'shraddha'        => 'parv',
            'jayanti'         => 'jayanti',
            'dashavatar'      => 'jayanti',
            'mahavidya'       => 'jayanti',
            'dham'            => 'jayanti',
            'mahapurush'      => 'jayanti',
            'muslim'          => 'other',
            'christian'       => 'other',
            'sikh'            => 'other',
            'jain'            => 'other',
            'national'        => 'other',
        ];

        // ══════════════════════════════════════════════════════════════
        //  build()
        // ══════════════════════════════════════════════════════════════
        public static function build(
            int $yr, int $mo, int $dy,
            int $hr, int $mn,
            float $utcOff, float $lat, float $lon
        ): array {
            $result  = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);
            $ayan    = $result['ayan'];
            $planets = $result['planets'];
            $pancha  = $result['pancha'];
            $ss      = $result['ss'];
            $tk      = $result['tk'];
            $tkRise  = $result['tkRise'];
            $dasha   = $result['dasha'];
            $jd      = $result['jd'];

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
            $nakHints = self::NAKSHATRA_MUHURTA[$nakName] ?? [
                'quality' => 'Mixed', 'good_for' => 'General activities',
                'avoid' => 'Nothing specific', 'color' => '#1d4e6f',
            ];
            $yogaAdvice = self::YOGA_ADVICE[$yoga['cls']] ?? self::YOGA_ADVICE['Subha'];

            $vrats = [];
            foreach ((self::WEEKDAY_VRATS[$dow] ?? []) as $v) {
                if (isset($v['conditional'])) {
                    $cond = $v['conditional'];
                    $include = false;
                    if ($cond === 'trayodashi' && $tithiNum === 13) $include = true;
                    if ($cond === 'ashtami'    && $tithiNum === 8)  $include = true;
                    if (!$include) continue;
                }
                $vrats[] = $v;
            }
            foreach (self::TITHI_VRATS as $tv) {
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
            $signLords    = ['Mars','Venus','Mercury','Moon','Sun','Mercury','Venus','Mars','Jupiter','Saturn','Saturn','Jupiter'];
            $moonSignLord = $signLords[$moonSignIdx];

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
        //  renderHtml() — Light parchment theme, large Devanagari fonts
        // ══════════════════════════════════════════════════════════════
    public static function renderHtml(array $d): string
    {
        $mq        = $d['muhurtaQuality']   ?? [];
        $mqClass   = strtolower($mq['label'] ?? 'good');
        $panchanga = $d['panchanga']         ?? [];
        $tithi     = $panchanga['tithi']     ?? [];
        $vara      = $panchanga['vara']      ?? [];
        $nak       = $panchanga['nakshatra'] ?? [];
        $yoga      = $panchanga['yoga']      ?? [];
        $karana    = $panchanga['karana']    ?? [];
        $moon      = $d['moon']              ?? [];
        $todayFests= $d['todayFestivals']    ?? [];
        $upcoming  = $d['upcomingFestivals'] ?? [];
        $pastFests = $d['pastFestivals']     ?? [];
        $planets   = $d['planetPositions']   ?? [];
        $upcomingP = $d['upcomingPlanets']   ?? [];
        $dateISO   = $d['dateISO']           ?? date('Y-m-d');
    
        [$yr, $mo, $dy] = array_map('intval', explode('-', $dateISO));
    
        $MONTHS_HI = ['','जनवरी','फरवरी','मार्च','अप्रैल','मई','जून',
                    'जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
    
        $allTodayFests = array_merge(
            $todayFests['vrat']    ?? [],
            $todayFests['parv']    ?? [],
            $todayFests['jayanti'] ?? [],
            $todayFests['other']   ?? []
        );
    
        // Muhurta palette
        $mqMeta = [
            'excellent'   => ['#5a6e00', 'श्रेष्ठ'],
            'good'        => ['#1a4a7a', 'शुभ'],
            'mixed'       => ['#7a5800', 'मिश्रित'],
            'challenging' => ['#7a1a1a', 'साधारण'],
        ];
        [$mqColor, $mqHi] = $mqMeta[$mqClass] ?? $mqMeta['good'];
        $mqPct = (int)($mq['pct'] ?? 50);
    
        $tithiHiMap = [
            'Pratipada'  => 'प्रतिपदा',  'Dwitiya'    => 'द्वितीया',
            'Tritiya'    => 'तृतीया',    'Chaturthi'  => 'चतुर्थी',
            'Panchami'   => 'पञ्चमी',    'Shashthi'   => 'षष्ठी',
            'Saptami'    => 'सप्तमी',    'Ashtami'    => 'अष्टमी',
            'Navami'     => 'नवमी',      'Dashami'    => 'दशमी',
            'Ekadashi'   => 'एकादशी',    'Dwadashi'   => 'द्वादशी',
            'Trayodashi' => 'त्रयोदशी', 'Chaturdashi'=> 'चतुर्दशी',
            'Purnima'    => 'पूर्णिमा',  'Amavasya'   => 'अमावस्या',
        ];
        $pakshaHiMap = ['Shukla' => 'शुक्ल पक्ष', 'Krishna' => 'कृष्ण पक्ष'];
    
        $tithiNameHi  = $tithiHiMap[$tithi['name']   ?? ''] ?? ($tithi['name']   ?? '—');
        $pakshaNameHi = $pakshaHiMap[$tithi['paksha'] ?? ''] ?? ($tithi['paksha'] ?? '');
        $tithiFull    = $pakshaNameHi . ' ' . $tithiNameHi;
    
        // ── Planet Hindi names & metadata ──────────────────────────────
        $PLANET_ORDER = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
        $PLANET_META  = [
            'sun'     => ['hi' => 'सूर्य',  'clr' => '#a04010', 'bg' => '#fff4e8', 'bd' => '#e8b880'],
            'moon'    => ['hi' => 'चन्द्र', 'clr' => '#1a3a6a', 'bg' => '#eef2fc', 'bd' => '#9ab0e0'],
            'mercury' => ['hi' => 'बुध',    'clr' => '#1a5a50', 'bg' => '#eef8f5', 'bd' => '#80c8b8'],
            'venus'   => ['hi' => 'शुक्र',  'clr' => '#6a1a5a', 'bg' => '#fceef8', 'bd' => '#d080b8'],
            'mars'    => ['hi' => 'मंगल',   'clr' => '#8a1a10', 'bg' => '#fceee8', 'bd' => '#e09080'],
            'jupiter' => ['hi' => 'गुरु',   'clr' => '#5a4000', 'bg' => '#fdf8e8', 'bd' => '#d0b878'],
            'saturn'  => ['hi' => 'शनि',    'clr' => '#302848', 'bg' => '#f2eff8', 'bd' => '#a898c0'],
            'rahu'    => ['hi' => 'राहु',   'clr' => '#1a4828', 'bg' => '#eef6f0', 'bd' => '#88c098'],
            'ketu'    => ['hi' => 'केतु',   'clr' => '#502818', 'bg' => '#faf2ee', 'bd' => '#c09888'],
        ];
    
        // ── Sign Hindi names ───────────────────────────────────────────
        $signHiMap = [
            'Mesha'     => 'मेष',      'Vrishabha' => 'वृषभ',
            'Mithuna'   => 'मिथुन',   'Karka'     => 'कर्क',
            'Simha'     => 'सिंह',    'Kanya'     => 'कन्या',
            'Tula'      => 'तुला',    'Vrischika' => 'वृश्चिक',
            'Dhanu'     => 'धनु',     'Makara'    => 'मकर',
            'Kumbha'    => 'कुंभ',    'Meena'     => 'मीन',
        ];
    
        // ── Nakshatra Hindi names ──────────────────────────────────────
        $nakHiMap = [
            'Ashwini'           => 'अश्विनी',       'Bharani'           => 'भरणी',
            'Krittika'          => 'कृत्तिका',       'Rohini'            => 'रोहिणी',
            'Mrigashira'        => 'मृगशिरा',        'Ardra'             => 'आर्द्रा',
            'Punarvasu'         => 'पुनर्वसु',        'Pushya'            => 'पुष्य',
            'Ashlesha'          => 'आश्लेषा',         'Magha'             => 'मघा',
            'Purva Phalguni'    => 'पूर्व फाल्गुनी', 'Uttara Phalguni'   => 'उत्तर फाल्गुनी',
            'Hasta'             => 'हस्त',           'Chitra'            => 'चित्रा',
            'Swati'             => 'स्वाती',          'Vishakha'          => 'विशाखा',
            'Anuradha'          => 'अनुराधा',         'Jyeshtha'          => 'ज्येष्ठा',
            'Moola'             => 'मूल',            'Purva Ashadha'     => 'पूर्वाषाढ़',
            'Uttara Ashadha'    => 'उत्तराषाढ़',      'Shravana'          => 'श्रवण',
            'Dhanishta'         => 'धनिष्ठा',         'Shatabhisha'       => 'शतभिषा',
            'Purva Bhadrapada'  => 'पूर्व भाद्रपद',  'Uttara Bhadrapada' => 'उत्तर भाद्रपद',
            'Revati'            => 'रेवती',
        ];
    
        // ── Planet lord Hindi names ────────────────────────────────────
        $lordHiMap = [
            'Sun'     => 'सूर्य',   'Moon'    => 'चन्द्र',  'Mercury' => 'बुध',
            'Venus'   => 'शुक्र',   'Mars'    => 'मंगल',    'Jupiter' => 'गुरु',
            'Saturn'  => 'शनि',    'Rahu'    => 'राहु',    'Ketu'    => 'केतु',
            'Brahma'  => 'ब्रह्मा', 'Vishnu'  => 'विष्णु',  'Shiva'   => 'शिव',
            'Agni'    => 'अग्नि',   'Varuna'  => 'वरुण',    'Indra'   => 'इन्द्र',
            'Yama'    => 'यम',     'Rudra'   => 'रुद्र',   'Kubera'  => 'कुबेर',
        ];
    
        // ── Nature / classification Hindi ─────────────────────────────
        $natureHiMap = [
            'Auspicious'         => 'शुभ',          'Inauspicious'  => 'अशुभ',
            'Mixed'              => 'मिश्रित',       'Nanda'         => 'नन्दा',
            'Bhadra'             => 'भद्रा',         'Jaya'          => 'जया',
            'Rikta'              => 'रिक्ता',         'Purna'         => 'पूर्णा',
            'Nanda (Auspicious)' => 'नन्दा (शुभ)',   'Fixed'         => 'स्थिर',
            'Ugra (Fierce)'      => 'उग्र',          'Saumya'        => 'सौम्य',
            'Guru'               => 'गुरु',          'Manushya'      => 'मानुष्य',
            'Deva'               => 'दैव',           'Rakshasa'      => 'राक्षस',
            'Kshipra (Swift)'    => 'क्षिप्र',        'Dhruva (Fixed)' => 'ध्रुव',
            'Mridu (Soft)'       => 'मृदु',           'Chara (Movable)' => 'चर',
            'Tikshna (Sharp)'    => 'तीक्ष्ण',        'Ugra (Fierce)'  => 'उग्र',
            'Mishra (Mixed)'     => 'मिश्र',          'Sthira'        => 'स्थिर',
            'Subha'              => 'शुभ',            'Mahavisha'     => 'महाविष',
            'Ashubha'            => 'अशुभ',
        ];
    
        // ── Vara Hindi names ───────────────────────────────────────────
        $varaHiMap = [
            'Ravivara'  => 'रविवार',  'Somavara'  => 'सोमवार',
            'Mangalvara'=> 'मंगलवार', 'Budhavara' => 'बुधवार',
            'Guruvara'  => 'गुरुवार', 'Shukravara'=> 'शुक्रवार',
            'Shanivara' => 'शनिवार',
        ];
    
        // ── Helper: translate or fallback ─────────────────────────────
        $hi = fn(string $en, array $map) => $map[$en] ?? $en;
    
        // ── Vara display ───────────────────────────────────────────────
        $varaNameEn  = $vara['name'] ?? '—';
        $varaNameHi  = $varaHiMap[$varaNameEn] ?? $varaNameEn;
        $varaLordHi  = $lordHiMap[$vara['lord'] ?? ''] ?? ($vara['lord'] ?? '');
        $varaNatureHi= $natureHiMap[$vara['nature'] ?? ''] ?? ($vara['nature'] ?? '');
    
        $sunrise  = htmlspecialchars($d['sunrise']   ?? '—');
        $sunset   = htmlspecialchars($d['sunset']    ?? '—');
        $dayLen   = htmlspecialchars($d['dayLength'] ?? '—');
    
        // ── Nakshatra display ──────────────────────────────────────────
        $nakNameEn   = $nak['name'] ?? '—';
        $nakNameHi   = $nakHiMap[$nakNameEn] ?? $nakNameEn;
        $nakLordHi   = $lordHiMap[$nak['lord'] ?? ''] ?? ($nak['lord'] ?? '');
        $nakGanaHi   = $natureHiMap[$nak['gana'] ?? ''] ?? ($nak['gana'] ?? '—');
        $nakQualHi   = $natureHiMap[$nak['quality'] ?? ''] ?? ($nak['quality'] ?? '—');
    
        // ── Yoga display ───────────────────────────────────────────────
        $yogaNameEn  = $yoga['name'] ?? '—';
        $yogaLordHi  = $lordHiMap[$yoga['lord'] ?? ''] ?? ($yoga['lord'] ?? '');
        $yogaNatHi   = $natureHiMap[$yoga['nature'] ?? ''] ?? ($yoga['nature'] ?? '');
    
        // ── Karana display ─────────────────────────────────────────────
        $karanaNameEn = $karana['name'] ?? '—';
        $karanaLordHi = $lordHiMap[$karana['lord'] ?? ''] ?? ($karana['lord'] ?? '');
        $karanaNatHi  = $natureHiMap[$karana['nature'] ?? ''] ?? ($karana['nature'] ?? '');
        $karanaTypeHi = $natureHiMap[$karana['type'] ?? ''] ?? ($karana['type'] ?? '—');
    
        // ── Tithi display ──────────────────────────────────────────────
        $tithiLordHi  = $lordHiMap[$tithi['lord'] ?? ''] ?? ($tithi['lord'] ?? '—');
        $tithiDeityHi = $lordHiMap[$tithi['deity'] ?? ''] ?? ($tithi['deity'] ?? '—');
        $tithiNatHi   = $natureHiMap[$tithi['nature'] ?? ''] ?? ($tithi['nature'] ?? '—');
    
        // ── Date formatting for festival calendar ─────────────────────
        // Convert YYYY-MM-DD → DD Month YYYY in Hindi
        $formatDateHi = function(string $dateStr) use ($MONTHS_HI): string {
            if (!$dateStr || strlen($dateStr) < 10) return $dateStr;
            [$y, $m, $da] = explode('-', substr($dateStr, 0, 10));
            return (int)$da . ' ' . ($MONTHS_HI[(int)$m] ?? $m) . ' ' . $y;
        };
    
        $h = '';
    
        // ── CSS ────────────────────────────────────────────────────────
        $h .= '<style>';
        $h .= "@import url('https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit:ital@0;1&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap');";
        $h .= '
    .tp{
    --sky:#1d4e6f;--sky2:#2a6d9c;--sky-lt:#5d8bb8;--sky-pale:#dbe9f5;--sky-wash:#f1f7fc;
    --gold:#bf871f;--gold-d:#8a6010;--gold-pale:#fbf2dc;
    --ink:#0e2d3f;--ink2:#234a62;--ink3:#4a6a82;--ink4:#86a0b3;
    --card:#ffffff;--rule:#e4edf4;--soft:#f5f9fc;
    --terra:#c0490f;--green:#2e7d52;
    font-family:"DM Sans",system-ui,sans-serif;color:var(--ink);width:100%;
    }
    .tp *{box-sizing:border-box}

    /* Section dividers — modern */
    .tp-div{display:flex;align-items:center;gap:14px;margin:36px 0 20px}
    .tp-div-line{flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--sky-pale),transparent)}
    .tp-div-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.5rem;font-weight:400;color:var(--sky);white-space:nowrap}
    .tp-div-en{font-size:.78rem;letter-spacing:1.2px;text-transform:uppercase;font-weight:800;color:var(--ink4);white-space:nowrap}

    .tp-card{background:var(--card);border:1px solid var(--rule);border-radius:16px;padding:22px 24px;box-shadow:0 2px 16px rgba(20,60,100,.05)}

    /* HERO — gradient banner */
    .tp-hero{background:linear-gradient(135deg,#173f60 0%,#235f8c 52%,#2e79a8 100%);border:none;border-radius:22px;padding:32px 38px;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 44px -14px rgba(16,55,95,.5)}
    .tp-hero::after{content:"";position:absolute;top:-45%;right:-8%;width:360px;height:360px;background:radial-gradient(circle,rgba(255,205,110,.2),transparent 70%);pointer-events:none}
    .tp-hero-inner{display:grid;grid-template-columns:auto 1fr auto;gap:36px;align-items:center;position:relative;z-index:1}
    @media(max-width:760px){.tp-hero-inner{grid-template-columns:1fr;gap:22px}.tp-hero-right{text-align:left!important}}

    .tp-date-num{font-family:"Playfair Display",serif;font-size:5.6rem;font-weight:700;line-height:.88;color:#ffd47a;letter-spacing:-2px;text-shadow:0 3px 22px rgba(0,0,0,.28)}
    .tp-date-month{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.35rem;color:#d2e7f8;margin-top:3px}
    .tp-date-year{font-family:"DM Mono",monospace;font-size:.88rem;color:rgba(255,255,255,.55);letter-spacing:3px;margin-top:2px}

    .tp-vara-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:2.2px;font-weight:800;color:rgba(255,255,255,.58);margin-bottom:7px}
    .tp-vara-name{font-family:"Tiro Devanagari Sanskrit",serif;font-size:3rem;color:#fff;line-height:1;margin-bottom:7px}
    .tp-vara-sub{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.12rem;color:#bcdcf0;margin-bottom:18px}

    .tp-sun-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:4px}
    .tp-sun-chip{display:flex;flex-direction:column;gap:1px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:13px;padding:9px 17px}
    .tp-sun-chip-lbl{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.85rem;color:rgba(255,255,255,.62)}
    .tp-sun-chip-val{font-family:"DM Mono",monospace;font-size:1.05rem;font-weight:600;color:#fff}

    .tp-fest-pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
    .tp-fest-pill{display:inline-flex;align-items:center;background:rgba(255,210,120,.16);border:1px solid rgba(255,210,120,.42);border-radius:50px;padding:6px 17px;font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.02rem;color:#ffe1a4}

    .tp-mq-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:2.2px;font-weight:800;color:rgba(255,255,255,.58);margin-bottom:10px}
    .tp-mq-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:2.5rem;line-height:1;margin-bottom:10px;color:#fff!important}
    .tp-mq-bar{display:flex;align-items:center;gap:10px;justify-content:flex-end}
    .tp-mq-track{background:rgba(255,255,255,.22);border-radius:5px;height:8px;flex:1;max-width:130px;overflow:hidden}
    .tp-mq-fill{height:100%;border-radius:5px;background:#ffd47a!important}
    .tp-mq-pct{font-family:"DM Mono",monospace;font-size:.92rem;font-weight:700;color:#ffd47a!important}

    /* PANCHANGA 5-anga grid — modern cards */
    .tp-anga-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:22px}
    @media(max-width:760px){.tp-anga-grid{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:440px){.tp-anga-grid{grid-template-columns:repeat(2,1fr)}}
    .tp-anga{background:var(--card);border:1px solid var(--rule);border-top:3px solid var(--sky-lt);border-radius:14px;padding:18px 16px;box-shadow:0 2px 12px rgba(20,60,100,.05);transition:transform .2s,box-shadow .2s}
    .tp-anga:hover{transform:translateY(-3px);box-shadow:0 10px 26px rgba(20,60,100,.12)}
    .tp-anga.feat{border-top-color:var(--gold)}
    .tp-anga-label{font-size:.66rem;text-transform:uppercase;letter-spacing:1.2px;font-weight:800;color:var(--ink4);margin-bottom:9px}
    .tp-anga-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.85rem;color:var(--ink);line-height:1.2;margin-bottom:6px}
    .tp-anga-sub{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.92rem;color:var(--ink3);line-height:1.5}

    /* Detail cards */
    .tp-detail-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:8px}
    .tp-detail-card{background:var(--soft);border:1px solid var(--rule);border-top:3px solid var(--sky-lt);border-radius:14px;padding:16px 18px}
    .tp-detail-head{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.15rem;color:var(--sky);margin-bottom:10px;font-weight:600}
    .tp-detail-row{display:flex;justify-content:space-between;gap:8px;padding:7px 0;border-bottom:1px solid var(--rule)}
    .tp-detail-row:last-child{border-bottom:none}
    .tp-detail-key{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.92rem;color:var(--ink3);flex-shrink:0}
    .tp-detail-val{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.95rem;color:var(--ink);text-align:right;font-weight:700}

    /* OBSERVANCES */
    .tp-obs-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media(max-width:640px){.tp-obs-grid{grid-template-columns:1fr}}
    .tp-obs{background:var(--card);border-radius:14px;overflow:hidden;border:1px solid var(--rule);box-shadow:0 2px 12px rgba(20,60,100,.05)}
    .tp-obs-head{padding:15px 19px;display:flex;align-items:center;justify-content:space-between}
    .tp-obs-head-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.55rem;font-weight:400}
    .tp-obs-head-en{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;color:var(--ink4);margin-top:3px}
    .tp-obs-cnt{font-family:"DM Mono",monospace;font-size:.92rem;font-weight:700;padding:4px 13px;border-radius:50px;flex-shrink:0;background:#fff}
    .tp-obs-body{padding:4px 19px}
    .tp-obs-empty{padding:18px 19px;font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.02rem;font-style:italic;color:var(--ink4)}
    .tp-fest-row{display:flex;align-items:flex-start;gap:11px;padding:13px 0;border-bottom:1px solid var(--rule)}
    .tp-fest-row:last-child{border-bottom:none}
    .tp-fest-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:7px}
    .tp-fest-name{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.12rem;color:var(--ink);line-height:1.35;font-weight:600}
    .tp-fest-desc{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.9rem;color:var(--ink3);line-height:1.5;margin-top:4px}
    .tp-fest-mantra{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.92rem;margin-top:5px;font-style:italic}

    /* PLANETS */
    .tp-planet-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px}
    .tp-pc{background:var(--card)!important;border-radius:14px;padding:16px 18px;border:1px solid var(--rule);border-top:3px solid transparent;box-shadow:0 2px 12px rgba(20,60,100,.05);transition:transform .2s,box-shadow .2s}
    .tp-pc:hover{transform:translateY(-3px);box-shadow:0 10px 26px rgba(20,60,100,.12)}
    .tp-pc-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:2rem;line-height:1;margin-bottom:3px}
    .tp-pc-sign{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.3rem;color:var(--ink);line-height:1.25;margin-bottom:4px;font-weight:600}
    .tp-pc-nak{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.9rem;color:var(--ink3);line-height:1.6}
    .tp-retro{display:inline-block;font-family:"DM Sans",sans-serif;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;border:1.5px solid;border-radius:20px;padding:2px 10px;margin-top:8px}

    /* SIGN CHANGES */
    .tp-sc-row{display:flex;align-items:center;gap:14px;padding:13px 2px;border-bottom:1px solid var(--rule)}
    .tp-sc-row:last-child{border-bottom:none}
    .tp-sc-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.3rem;min-width:84px;font-weight:600}
    .tp-sc-arrow{color:var(--ink4);font-size:1.1rem}
    .tp-sc-to{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.22rem;font-weight:700;flex:1;color:var(--sky)}
    .tp-sc-date{font-family:"DM Mono",monospace;font-size:.92rem;color:var(--ink2);font-weight:600;white-space:nowrap}
    .tp-sc-days{font-family:"DM Sans",sans-serif;font-size:.82rem;font-weight:700;color:var(--terra);display:block;margin-top:2px;text-align:right}

    /* FESTIVAL CALENDAR */
    .tp-fest-cal-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    @media(max-width:640px){.tp-fest-cal-grid{grid-template-columns:1fr}}
    .tp-cal-head{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.4rem;color:var(--sky);border-bottom:2px solid var(--sky-pale);padding-bottom:11px;margin-bottom:14px;font-weight:600}
    .tp-cal-row{display:flex;align-items:flex-start;gap:11px;padding:12px 0;border-bottom:1px solid var(--rule)}
    .tp-cal-row:last-child{border-bottom:none}
    .tp-cal-name{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.08rem;color:var(--ink);flex:1;line-height:1.4}
    .tp-cal-date{font-family:"DM Mono",monospace;font-size:.92rem;color:var(--ink2);white-space:nowrap;text-align:right;font-weight:700}
    .tp-cal-days{font-family:"DM Sans",sans-serif;font-size:.8rem;font-weight:700;color:var(--terra);display:block;margin-top:2px}

    /* FOOTER */
    .tp-footer{display:flex;gap:0;background:linear-gradient(135deg,#173f60,#2e79a8);border:none;border-radius:18px;margin-top:38px;overflow:hidden;box-shadow:0 12px 34px -14px rgba(16,55,95,.5)}
    .tp-fi{flex:1;min-width:170px;padding:22px 26px;border-right:1px solid rgba(255,255,255,.12)}
    .tp-fi:last-child{border-right:none}
    .tp-fi-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:rgba(255,255,255,.58);margin-bottom:7px}
    .tp-fi-hi{font-family:"Tiro Devanagari Sanskrit",serif;font-size:1.55rem;color:#fff;font-weight:400}
    .tp-fi-sub{font-family:"Tiro Devanagari Sanskrit",serif;font-size:.95rem;color:#bcdcf0;margin-top:3px}
    .tp-fi-div{display:none}

    /* Animations */
    @keyframes tpFade{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    .ta{animation:tpFade .5s ease both}
    .ta1{animation-delay:.07s}.ta2{animation-delay:.14s}
    .ta3{animation-delay:.21s}.ta4{animation-delay:.28s}.ta5{animation-delay:.35s}

    @media(max-width:700px){
    .tp-date-num{font-size:4.2rem}
    .tp-vara-name{font-size:2.4rem}
    .tp-planet-grid{grid-template-columns:repeat(2,1fr)}
    }
    ';
        $h .= '</style>';
    
        $h .= '<div class="tp">';
    
        // ── Rule line ──────────────────────────────────────────────────
        $h .= '<div class="ta" style="display:flex;align-items:center;gap:18px;margin-bottom:28px">'
            . '<div style="flex:1;height:1.5px;background:linear-gradient(90deg,transparent,var(--gold-lt),transparent)"></div>'
            . '<span style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.9rem;letter-spacing:2px;color:var(--ink4)">वैदिक पञ्चाङ्ग</span>'
            . '<div style="flex:1;height:1.5px;background:linear-gradient(90deg,var(--gold-lt),transparent)"></div>'
            . '</div>';
    
        // ══════════════════════════════════════════════
        // 1. HERO
        // ══════════════════════════════════════════════
        $h .= '<div class="tp-hero ta">';
        $h .= '<div class="tp-hero-inner">';
    
        // Left: date
        $h .= '<div style="flex-shrink:0">'
            . '<div class="tp-date-num">' . $dy . '</div>'
            . '<div class="tp-date-month">' . ($MONTHS_HI[$mo] ?? '') . '</div>'
            . '<div class="tp-date-year">' . $yr . '</div>'
            . '</div>';
    
        // Centre: vara + sun + fest pills
        $h .= '<div>'
            . '<div class="tp-vara-lbl">आज का वार</div>'
            . '<div class="tp-vara-name">' . htmlspecialchars($varaNameHi) . '</div>'
            . '<div class="tp-vara-sub">' . htmlspecialchars($varaLordHi) . ' · ' . htmlspecialchars($varaNatureHi) . '</div>'
            . '<div class="tp-sun-row">';
    
        foreach (['उदय' => $sunrise, 'अस्त' => $sunset, 'दिन की लम्बाई' => $dayLen] as $lbl => $val) {
            $h .= '<div class="tp-sun-chip">'
                . '<span class="tp-sun-chip-lbl">' . $lbl . '</span>'
                . '<span class="tp-sun-chip-val">' . $val . '</span>'
                . '</div>';
        }
        $h .= '</div>';
    
        if (!empty($allTodayFests)) {
            $h .= '<div class="tp-fest-pills">';
            foreach (array_slice($allTodayFests, 0, 5) as $f) {
                $h .= '<span class="tp-fest-pill">' . htmlspecialchars($f['name'] ?? '—') . '</span>';
            }
            $h .= '</div>';
        }
        $h .= '</div>'; // centre
    
        // Right: muhurta quality
        $h .= '<div class="tp-hero-right" style="text-align:right;flex-shrink:0;min-width:140px">'
            . '<div class="tp-mq-lbl">मुहूर्त गुणवत्ता</div>'
            . '<div class="tp-mq-hi" style="color:' . $mqColor . '">' . htmlspecialchars($mqHi) . '</div>'
            . '<div class="tp-mq-bar">'
            . '<div class="tp-mq-track"><div class="tp-mq-fill" style="width:' . $mqPct . '%;background:' . $mqColor . '"></div></div>'
            . '<span class="tp-mq-pct" style="color:' . $mqColor . '">' . $mqPct . '%</span>'
            . '</div>'
            . '</div>';
    
        $h .= '</div></div>'; // hero-inner + hero

        // ══════════════════════════════════════════════
        // 1b. PANCHANGA-AT-A-GLANCE DIAGRAM (SVG)
        // ══════════════════════════════════════════════
        $sr = substr($sunrise, 0, 5);
        $st = substr($sunset, 0, 5);

        $ring = function (float $prog, string $col, string $labelHi, string $val): string {
            $prog = max(0.0, min(100.0, $prog));
            $r = 34.0; $c = 2 * M_PI * $r; $off = $c * (1 - $prog / 100);
            return '<div style="display:flex;flex-direction:column;align-items:center;gap:7px;flex:1;min-width:96px">'
                . '<svg width="88" height="88" viewBox="0 0 84 84">'
                . '<circle cx="42" cy="42" r="34" fill="none" stroke="rgba(120,90,30,.12)" stroke-width="7"/>'
                . '<circle cx="42" cy="42" r="34" fill="none" stroke="' . $col . '" stroke-width="7" stroke-linecap="round"'
                . ' stroke-dasharray="' . round($c, 2) . '" stroke-dashoffset="' . round($off, 2) . '" transform="rotate(-90 42 42)"/>'
                . '<text x="42" y="40" text-anchor="middle" font-family="Playfair Display,serif" font-size="17" font-weight="700" fill="' . $col . '">' . round($prog) . '%</text>'
                . '<text x="42" y="56" text-anchor="middle" font-size="8.5" fill="#9a8a6a">' . htmlspecialchars($val) . '</text>'
                . '</svg>'
                . '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:#5a4a30;font-weight:600">' . $labelHi . '</div>'
                . '</div>';
        };

        $h .= '<div class="ta ta1" style="display:flex;gap:16px;flex-wrap:wrap;align-items:stretch;margin-bottom:28px">';

        // Sun arc card
        $h .= '<div style="flex:2;min-width:240px;background:#fff;border:1px solid var(--rule);border-radius:16px;box-shadow:0 2px 14px rgba(20,60,100,.05);padding:14px 20px 10px;display:flex;flex-direction:column;align-items:center;justify-content:center">'
            . '<svg width="100%" height="96" viewBox="0 0 260 96" preserveAspectRatio="xMidYMid meet">'
            . '<defs><linearGradient id="tpSun" x1="0" y1="0" x2="1" y2="0">'
            . '<stop offset="0" stop-color="#e8902a"/><stop offset="0.5" stop-color="#f5c130"/><stop offset="1" stop-color="#e8902a"/>'
            . '</linearGradient></defs>'
            . '<line x1="20" y1="74" x2="240" y2="74" stroke="rgba(120,90,30,.18)" stroke-width="1.5"/>'
            . '<path d="M24 74 Q130 -6 236 74" fill="none" stroke="url(#tpSun)" stroke-width="3.5" stroke-linecap="round"/>'
            . '<circle cx="130" cy="18" r="16" fill="#f5c130" opacity="0.25"/>'
            . '<circle cx="130" cy="18" r="8" fill="#f3b12a"/>'
            . '<circle cx="24" cy="74" r="4" fill="#e8902a"/><circle cx="236" cy="74" r="4" fill="#c06028"/>'
            . '<text x="24" y="90" text-anchor="middle" font-family="DM Mono,monospace" font-size="11" font-weight="600" fill="#a05818">' . $sr . '</text>'
            . '<text x="236" y="90" text-anchor="middle" font-family="DM Mono,monospace" font-size="11" font-weight="600" fill="#7a4080">' . $st . '</text>'
            . '<text x="24" y="62" text-anchor="middle" font-family="Tiro Devanagari Sanskrit,serif" font-size="10" fill="#b07840">उदय</text>'
            . '<text x="236" y="62" text-anchor="middle" font-family="Tiro Devanagari Sanskrit,serif" font-size="10" fill="#8060a0">अस्त</text>'
            . '</svg>'
            . '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;color:#8a6020;margin-top:2px">दिन की लम्बाई · ' . $dayLen . '</div>'
            . '</div>';

        // Progress rings
        $h .= '<div style="flex:3;min-width:300px;background:#fff;border:1px solid var(--rule);border-radius:16px;box-shadow:0 2px 14px rgba(20,60,100,.05);padding:16px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-around">'
            . $ring((float)($tithi['prog'] ?? 0), '#c48a2f', 'तिथि', ($tithi['num'] ?? '') . '/15')
            . $ring((float)($nak['prog']   ?? 0), '#1d6aaa', 'नक्षत्र', ($nak['num'] ?? '') . '/27')
            . $ring((float)($yoga['prog']  ?? 0), '#2d7a3a', 'योग', ($yoga['num'] ?? '') . '/27')
            . '</div>';

        $h .= '</div>';

        // ══════════════════════════════════════════════
        // 2. PANCHANGA
        // ══════════════════════════════════════════════
        $h .= '<div class="ta ta1">';
        $h .= '<div class="tp-div">'
            . '<div class="tp-div-line"></div>'
            . '<span class="tp-div-hi">पञ्चाङ्ग</span>'
            . '<span class="tp-div-en">दिन के पाँच अंग</span>'
            . '<div class="tp-div-line"></div>'
            . '</div>';
    
        $angas = [
            ['तिथि',    'तिथि · चन्द्र दिन',     $tithiFull,              $tithiLordHi . ' · ' . $tithiNatHi,    true],
            ['वार',     'वार · सप्ताह दिन',       $varaNameHi,             $varaNatureHi . ' · ' . $varaLordHi,   false],
            ['नक्षत्र','नक्षत्र · चन्द्र मण्डल', $nakNameHi,              'पाद ' . ($nak['pada']??'?') . ' · ' . $nakLordHi, false],
            ['योग',     'योग · लूनी-सौर योग',      htmlspecialchars($yogaNameEn), $yogaNatHi . ' · ' . $yogaLordHi, false],
            ['करण',     'करण · अर्ध तिथि',         htmlspecialchars($karanaNameEn), $karanaTypeHi . ' · ' . $karanaLordHi, false],
        ];
    
        $h .= '<div class="tp-anga-grid">';
        foreach ($angas as [$hi_lbl, $en, $name, $sub, $feat]) {
            $cls = 'tp-anga' . ($feat ? ' feat' : '');
            $h .= '<div class="' . $cls . '">'
                . '<div class="tp-anga-label">' . htmlspecialchars($en) . '</div>'
                . '<div class="tp-anga-hi">' . htmlspecialchars($name) . '</div>'
                . '<div class="tp-anga-sub">' . htmlspecialchars($sub) . '</div>'
                . '</div>';
        }
        $h .= '</div>';
    
        // Detail cards — all Hindi
        $detailCards = [
            ['#a03818', 'तिथि विवरण', [
                ['पक्ष',              ($pakshaHiMap[$tithi['paksha'] ?? ''] ?? '') . ' पक्ष'],
                ['क्रम',              ($tithi['num']??'') . ' / १५'],
                ['अधिष्ठात्र देवता', $tithiDeityHi],
                ['तिथि स्वामी',      $tithiLordHi],
                ['स्वभाव',           $tithiNatHi],
                ['प्रगति',           ($tithi['prog']??'0') . '%'],
            ]],
            ['#1a3a6a', 'वार विवरण', [
                ['देवता',       $lordHiMap[$vara['deity'] ?? ''] ?? ($vara['deity'] ?? '—')],
                ['होरा स्वामी', $lordHiMap[$vara['horaLord'] ?? ''] ?? ($vara['horaLord'] ?? '—')],
                ['वर्गीकरण',   $natureHiMap[$vara['ausp'] ?? ''] ?? ($vara['ausp'] ?? '—')],
                ['शुभ कार्य',   $vara['auspNote'] ?? '—'],
            ]],
            ['#1a5a50', 'नक्षत्र विवरण', [
                ['गण',    $nakGanaHi],
                ['योनि',  $nak['yoni']  ?? '—'],
                ['नाड़ी', $nak['nadi']  ?? '—'],
                ['तत्व',  $nak['tattva']?? '—'],
                ['गुण',   $nakQualHi],
            ]],
            ['#5a4000', 'योग विवरण', [
                ['अधिष्ठात्र देवता', $lordHiMap[$yoga['deity'] ?? ''] ?? ($yoga['deity'] ?? '—')],
                ['वर्ग',    $natureHiMap[$yoga['cls'] ?? ''] ?? ($yoga['cls'] ?? '—')],
                ['परामर्श', $yoga['advice'] ?? '—'],
            ]],
            ['#302848', 'करण विवरण', [
                ['प्रकार',  $karanaTypeHi],
                ['देवता',   $lordHiMap[$karana['deity'] ?? ''] ?? ($karana['deity'] ?? '—')],
                ['अनुकूल', $karana['favour'] ?? '—'],
            ]],
        ];
    
        $h .= '<div class="tp-detail-grid">';
        foreach ($detailCards as [$accentColor, $titleHi, $rows]) {
            $h .= '<div class="tp-detail-card" style="border-top-color:' . $accentColor . '">'
                . '<div class="tp-detail-head" style="color:' . $accentColor . '">' . htmlspecialchars($titleHi) . '</div>';
            foreach ($rows as [$k, $v]) {
                if (!$v || trim((string)$v) === '—') continue;
                $h .= '<div class="tp-detail-row">'
                    . '<span class="tp-detail-key">' . htmlspecialchars($k) . '</span>'
                    . '<span class="tp-detail-val">' . htmlspecialchars((string)$v) . '</span>'
                    . '</div>';
            }
            $h .= '</div>';
        }
        $h .= '</div></div>';
    
        // ══════════════════════════════════════════════
        // 3. TODAY OBSERVANCES
        // ══════════════════════════════════════════════
        $h .= '<div class="ta ta2">';
        $h .= '<div class="tp-div">'
            . '<div class="tp-div-line"></div>'
            . '<span class="tp-div-hi">आज के पर्व एवं व्रत</span>'
            . '<span class="tp-div-en">आज के अनुष्ठान</span>'
            . '<div class="tp-div-line"></div>'
            . '</div>';
    
        $obsCats = [
            ['vrat',    'व्रत',    'उपवास एवं व्रत',      '#6a1a5a', 'rgba(106,26,90,.07)', 'rgba(106,26,90,.12)'],
            ['parv',    'पर्व',    'उत्सव एवं पर्व',       '#8a3000', 'rgba(138,48,0,.07)',  'rgba(138,48,0,.12)'],
            ['jayanti', 'जयन्ती', 'जयन्ती एवं वर्षगांठ', '#1a3a6a', 'rgba(26,58,106,.07)', 'rgba(26,58,106,.12)'],
            ['other',   'अन्य',   'अन्य अनुष्ठान',         '#1a5030', 'rgba(26,80,48,.07)',  'rgba(26,80,48,.12)'],
        ];
    
        $h .= '<div class="tp-obs-grid">';
        foreach ($obsCats as [$key, $hi_lbl, $en, $accent, $bg, $badgeBg]) {
            $items = $todayFests[$key] ?? [];
            $cnt   = count($items);
            $h .= '<div class="tp-obs" style="border-left:4px solid ' . $accent . '">';
            $h .= '<div class="tp-obs-head" style="background:' . $bg . '">'
                . '<div>'
                . '<div class="tp-obs-head-hi" style="color:' . $accent . '">' . htmlspecialchars($hi_lbl) . '</div>'
                . '<div class="tp-obs-head-en">' . htmlspecialchars($en) . '</div>'
                . '</div>'
                . '<div class="tp-obs-cnt" style="background:' . ($cnt ? $accent : 'var(--cream3)') . ';color:' . ($cnt ? '#fff' : 'var(--ink4)') . '">' . ($cnt ?: '—') . '</div>'
                . '</div>';
            if (!$cnt) {
                $h .= '<div class="tp-obs-empty">आज कोई ' . htmlspecialchars($hi_lbl) . ' नहीं</div>';
            } else {
                $h .= '<div class="tp-obs-body">';
                foreach ($items as $f) {
                    $fName   = $f['name']   ?? '—';
                    $fSig    = substr($f['significance'] ?? $f['desc'] ?? '', 0, 100);
                    $fMantra = $f['mantra'] ?? '';
                    $h .= '<div class="tp-fest-row">'
                        . '<div class="tp-fest-dot" style="background:' . $accent . '"></div>'
                        . '<div style="flex:1">'
                        . '<div class="tp-fest-name">' . htmlspecialchars($fName) . '</div>'
                        . ($fSig ? '<div class="tp-fest-desc">' . htmlspecialchars($fSig) . (strlen($f['significance'] ?? $f['desc'] ?? '') > 100 ? '…' : '') . '</div>' : '')
                        . ($fMantra ? '<div class="tp-fest-mantra" style="color:' . $accent . '">' . htmlspecialchars($fMantra) . '</div>' : '')
                        . '</div></div>';
                }
                $h .= '</div>';
            }
            $h .= '</div>';
        }
        $h .= '</div></div>';
    
        // ══════════════════════════════════════════════
        // 4. PLANETARY POSITIONS — all Hindi
        // ══════════════════════════════════════════════
        $h .= '<div class="ta ta3">';
        $h .= '<div class="tp-div">'
            . '<div class="tp-div-line"></div>'
            . '<span class="tp-div-hi">नवग्रह स्थिति</span>'
            . '<span class="tp-div-en">नौ ग्रहों की वर्तमान स्थिति</span>'
            . '<div class="tp-div-line"></div>'
            . '</div>';
    
        $h .= '<div class="tp-planet-grid">';
        foreach ($PLANET_ORDER as $pid) {
            $p  = $planets[$pid] ?? null;
            $pm = $PLANET_META[$pid] ?? null;
            if (!$p || !$pm) continue;
            $retro      = !empty($p['retro']);
            $signHi     = $signHiMap[$p['sign'] ?? ''] ?? ($p['sign'] ?? '—');
            $nakHi      = $nakHiMap[$p['nak'] ?? ''] ?? ($p['nak'] ?? '—');
    
            $h .= '<div class="tp-pc" style="background:' . $pm['bg'] . ';border-color:' . $pm['bd'] . ';border-top-color:' . $pm['clr'] . '">'
                . '<div class="tp-pc-hi" style="color:' . $pm['clr'] . '">' . htmlspecialchars($pm['hi']) . '</div>'
                . '<div class="tp-pc-sign">' . htmlspecialchars($signHi) . '</div>'
                . '<div class="tp-pc-nak">' . htmlspecialchars($nakHi) . '<br>पाद ' . (int)($p['pada'] ?? 0) . ' · ' . number_format((float)($p['deg'] ?? 0), 1) . '°</div>'
                . ($retro ? '<div class="tp-retro" style="color:' . $pm['clr'] . ';border-color:' . $pm['clr'] . '">वक्री</div>' : '')
                . '</div>';
        }
        $h .= '</div></div>';
    
        // ══════════════════════════════════════════════
        // 5. UPCOMING SIGN CHANGES — all Hindi
        // ══════════════════════════════════════════════
        if (!empty($upcomingP)) {
            $h .= '<div class="ta ta4">';
            $h .= '<div class="tp-div">'
                . '<div class="tp-div-line"></div>'
                . '<span class="tp-div-hi">राशि परिवर्तन</span>'
                . '<span class="tp-div-en">आगामी ग्रह राशि परिवर्तन</span>'
                . '<div class="tp-div-line"></div>'
                . '</div>';
    
            $h .= '<div class="tp-card">';
            foreach ($upcomingP as $ch) {
                $pm     = $PLANET_META[$ch['pid']] ?? null;
                $clr    = $pm['clr']  ?? '#3a2a18';
                $hiName = $pm['hi']   ?? ucfirst($ch['pid']);
                $from   = htmlspecialchars($signHiMap[$ch['fromSign'] ?? ''] ?? ($ch['fromSign'] ?? '—'));
                $to     = htmlspecialchars($signHiMap[$ch['toSign']   ?? ''] ?? ($ch['toSign']   ?? '—'));
                $dt     = $formatDateHi($ch['date'] ?? '');
                $days   = (int)($ch['daysAway'] ?? 0);
                $dLbl   = $days === 0 ? 'आज' : ($days === 1 ? 'कल' : $days . ' दिन');
    
                $h .= '<div class="tp-sc-row">'
                    . '<div style="min-width:90px">'
                    . '<div class="tp-sc-hi" style="color:' . $clr . '">' . htmlspecialchars($hiName) . '</div>'
                    . '</div>'
                    . '<div style="flex:1;display:flex;align-items:center;gap:10px">'
                    . '<span class="tp-sc-hi" style="color:var(--ink3)">' . $from . '</span>'
                    . '<span class="tp-sc-arrow">&rarr;</span>'
                    . '<span class="tp-sc-to" style="color:' . $clr . '">' . $to . '</span>'
                    . '</div>'
                    . '<div style="text-align:right">'
                    . '<div class="tp-sc-date">' . htmlspecialchars($dt) . '</div>'
                    . '<div class="tp-sc-days">' . htmlspecialchars($dLbl) . '</div>'
                    . '</div>'
                    . '</div>';
            }
            $h .= '</div></div>';
        }
    
        // ══════════════════════════════════════════════
        // 6. FESTIVAL CALENDAR — larger dates
        // ══════════════════════════════════════════════
        $h .= '<div class="ta ta5">';
        $h .= '<div class="tp-div">'
            . '<div class="tp-div-line"></div>'
            . '<span class="tp-div-hi">पर्व कैलेंडर</span>'
            . '<span class="tp-div-en">विगत एवं आगामी १५ दिन</span>'
            . '<div class="tp-div-line"></div>'
            . '</div>';
    
        $h .= '<div class="tp-fest-cal-grid">';
    
        // Past 15
        $h .= '<div>';
        $h .= '<div class="tp-cal-head">विगत पर्व <span style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.8rem;color:var(--ink4);font-weight:400"> · गत १५ दिन</span></div>';
        if (empty($pastFests)) {
            $h .= '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;font-style:italic;color:var(--ink4)">कोई विगत पर्व नहीं</div>';
        } else {
            foreach (array_slice($pastFests, 0, 15) as $f) {
                $fName  = htmlspecialchars($f['name'] ?? '—');
                $fDate  = htmlspecialchars($formatDateHi($f['date'] ?? ''));
                $fType  = strtolower($f['type'] ?? 'festival');
                $dotClr = str_contains($fType, 'vrat')    ? '#6a1a5a'
                        : (str_contains($fType, 'jayanti') ? '#1a3a6a' : '#8a3000');
                $h .= '<div class="tp-cal-row" style="opacity:.72">'
                    . '<div class="tp-fest-dot" style="background:' . $dotClr . ';margin-top:7px;flex-shrink:0"></div>'
                    . '<div class="tp-cal-name">' . $fName . '</div>'
                    . '<div class="tp-cal-date">' . $fDate . '</div>'
                    . '</div>';
            }
        }
        $h .= '</div>';
    
        // Next 15
        $h .= '<div>';
        $h .= '<div class="tp-cal-head" style="border-bottom-color:var(--terra);color:var(--terra)">आगामी पर्व <span style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.8rem;font-weight:400"> · अगले १५ दिन</span></div>';
        if (empty($upcoming)) {
            $h .= '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;font-style:italic;color:var(--ink4)">कोई आगामी पर्व नहीं</div>';
        } else {
            foreach (array_slice($upcoming, 0, 15) as $f) {
                $fName  = htmlspecialchars($f['name'] ?? '—');
                $fDate  = htmlspecialchars($formatDateHi($f['date'] ?? ''));
                $fSig   = substr($f['significance'] ?? $f['desc'] ?? '', 0, 75);
                $fType  = strtolower($f['type'] ?? 'festival');
                $dotClr = str_contains($fType, 'vrat')    ? '#6a1a5a'
                        : (str_contains($fType, 'jayanti') ? '#1a3a6a' : '#8a3000');
                $daysA  = ($f['date'] && $dateISO)
                        ? (int)round((strtotime($f['date']) - strtotime($dateISO)) / 86400)
                        : 0;
                $dLbl   = $daysA === 0 ? 'आज' : ($daysA === 1 ? 'कल' : $daysA . ' दिन');
                $h .= '<div class="tp-cal-row">'
                    . '<div class="tp-fest-dot" style="background:' . $dotClr . ';margin-top:7px;flex-shrink:0"></div>'
                    . '<div style="flex:1">'
                    . '<div class="tp-cal-name">' . $fName . '</div>'
                    . ($fSig ? '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.9rem;color:var(--ink3);line-height:1.5">' . htmlspecialchars($fSig) . (strlen($f['significance'] ?? $f['desc'] ?? '') > 75 ? '…' : '') . '</div>' : '')
                    . '</div>'
                    . '<div style="text-align:right;flex-shrink:0;padding-left:8px">'
                    . '<div class="tp-cal-date">' . $fDate . '</div>'
                    . '<div class="tp-cal-days">' . htmlspecialchars($dLbl) . '</div>'
                    . '</div>'
                    . '</div>';
            }
        }
        $h .= '</div>';
        $h .= '</div></div>';
    
        // ══════════════════════════════════════════════
        // 7. FOOTER — Hindi
        // ══════════════════════════════════════════════
        $lagnaSign   = htmlspecialchars(($d['lagna'] ?? [])['sign'] ?? '—');
        $moonSignKey = $moon['sign'] ?? '—';
        $moonNakN    = htmlspecialchars($nakHiMap[$moon['nakshatra'] ?? ''] ?? ($moon['nakshatra'] ?? '—'));
        $moonPaksha  = $pakshaHiMap[$moon['paksha'] ?? ''] ?? htmlspecialchars($moon['paksha'] ?? '');
        $tithiNumD   = (int)($moon['tithiNum'] ?? 0);
        $dashaSum    = htmlspecialchars($d['dasha'] ?? '—');
    
        // Translate dasha lord in summary string (e.g. "Dasha Lord: Moon · 9y 10m remaining")
        $dashaHi = preg_replace_callback(
            '/Dasha Lord:\s*(\w+)\s*·\s*(\d+)y\s*(\d+)m\s*remaining/',
            function($m) use ($lordHiMap) {
                $lord = $lordHiMap[$m[1]] ?? $m[1];
                return 'दशा स्वामी: ' . $lord . ' · ' . $m[2] . ' वर्ष ' . $m[3] . ' माह शेष';
            },
            $dashaSum
        );
    
        $moonSignHi  = $signHiMap[$moonSignKey] ?? $moonSignKey;
        $lagnaSignHi = $signHiMap[$lagnaSign]   ?? $lagnaSign;
    
        $h .= '<div class="tp-footer">'
            . '<div class="tp-fi">'
            . '<div class="tp-fi-lbl">चन्द्र राशि</div>'
            . '<div class="tp-fi-hi">' . htmlspecialchars($moonSignHi) . '</div>'
            . '<div class="tp-fi-sub">' . $moonNakN . ' · ' . $moonPaksha . ' तिथि ' . $tithiNumD . '</div>'
            . '</div>'
            . '<div class="tp-fi-div"></div>'
            . '<div class="tp-fi">'
            . '<div class="tp-fi-lbl">लग्न</div>'
            . '<div class="tp-fi-hi">' . htmlspecialchars($lagnaSignHi) . '</div>'
            . '<div class="tp-fi-sub">लाहिरी अयनांश · सायन</div>'
            . '</div>'
            . '<div class="tp-fi-div"></div>'
            . '<div class="tp-fi">'
            . '<div class="tp-fi-lbl">दशा</div>'
            . '<div class="tp-fi-hi" style="font-size:1.2rem">' . $dashaHi . '</div>'
            . '</div>'
            . '</div>';
    
        $h .= '</div>'; // .tp
        return $h;
    }
        // ══════════════════════════════════════════════════════════════
        //  Private helpers (unchanged)
        // ══════════════════════════════════════════════════════════════

        private static function fetchTodayFestivalsCategorized(
            int $yr, int $mo, int $dy,
            float $lat, float $lon, float $utcOff
        ): array {
            $result = ['vrat' => [], 'parv' => [], 'jayanti' => [], 'other' => []];
            try {
                $calData   = HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff);
                $festivals = $calData['festivals'] ?? [];
                $today     = sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
                foreach ($festivals as $f) {
                    if (($f['date'] ?? '') !== $today) continue;
                    $fType = strtolower($f['type'] ?? 'festival');
                    $cat   = self::FEST_CATEGORY_MAP[$fType] ?? 'parv';
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
                $calData   = HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff);
                $festivals = $calData['festivals'] ?? [];
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
                $calData   = HinduFestivalCalculator::calculateYear($yr, $lat, $lon, $utcOff);
                $festivals = $calData['festivals'] ?? [];
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

        private static function monthName(int $m): string
        {
            return ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$m] ?? '';
        }
    }
