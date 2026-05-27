<?php

namespace App\Services;

use App\Services\AstroCalculator;
use App\Services\HinduFestivalCalculator;

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
        'Ashwini'           => ['quality' => 'क्षिप्र (शीघ्र)',  'good_for' => 'चिकित्सा, शीघ्र यात्रा, नई विद्या',   'avoid' => 'दीर्घकालिक प्रतिबद्धता',       'color' => '#1a5a1a'],
        'Bharani'           => ['quality' => 'उग्र (तीव्र)',    'good_for' => 'साहसी कार्य, अग्नि कर्म',        'avoid' => 'शुभ अनुष्ठान',       'color' => '#8a2010'],
        'Krittika'          => ['quality' => 'मिश्र',   'good_for' => 'अग्नि अनुष्ठान, पाक, शुद्धि',         'avoid' => 'संवेदनशील वार्ता',      'color' => '#c56408'],
        'Rohini'            => ['quality' => 'ध्रुव (स्थिर)',   'good_for' => 'राज्याभिषेक, बीज वपन, दीर्घकालीन कार्य',    'avoid' => 'कुछ नहीं — सर्वश्रेष्ठ', 'color' => '#2e7a2e'],
        'Mrigashira'        => ['quality' => 'मृदु (कोमल)',     'good_for' => 'प्रेम, कला, संगीत',      'avoid' => 'कठोर संघर्ष',        'color' => '#1d6090'],
        'Ardra'             => ['quality' => 'तीक्ष्ण (तीव्र)',  'good_for' => 'विनाश, शल्य, साहसी कार्य',             'avoid' => 'शुभ आरम्भ',   'color' => '#6a2090'],
        'Punarvasu'         => ['quality' => 'चर (चल)',  'good_for' => 'वापसी यात्रा, उपचार',       'avoid' => 'स्थायी निर्णय',         'color' => '#1d8090'],
        'Pushya'            => ['quality' => 'मृदु (कोमल)',     'good_for' => 'सभी शुभ कार्य — सर्वश्रेष्ठ नक्षत्र',      'avoid' => 'विवाह (परम्परा)',    'color' => '#2e7a6e'],
        'Ashlesha'          => ['quality' => 'तीक्ष्ण (तीव्र)',  'good_for' => 'गुप्त विद्या, शत्रु निवारण',             'avoid' => 'नए सम्बन्ध, यात्रा', 'color' => '#4a1a6a'],
        'Magha'             => ['quality' => 'उग्र (तीव्र)',    'good_for' => 'पितृ पूजा, राजकीय कार्य',   'avoid' => 'नए उद्यम',               'color' => '#6a3800'],
        'Purva Phalguni'    => ['quality' => 'उग्र (तीव्र)',    'good_for' => 'आनंद, विश्राम, कला',                     'avoid' => 'गंभीर कार्य',    'color' => '#c56408'],
        'Uttara Phalguni'   => ['quality' => 'ध्रुव (स्थिर)',   'good_for' => 'विवाह, दीर्घकालिक कार्य',               'avoid' => 'कुछ नहीं — शुभ',   'color' => '#2e8060'],
        'Hasta'             => ['quality' => 'क्षिप्र (शीघ्र)',  'good_for' => 'व्यापार, शिल्प, उपचार',          'avoid' => 'विनाशकारी कार्य',         'color' => '#1d6aaa'],
        'Chitra'            => ['quality' => 'मृदु (कोमल)',     'good_for' => 'कला, आभूषण, सजावट',              'avoid' => 'संघर्ष',              'color' => '#8e3a7a'],
        'Swati'             => ['quality' => 'चर (चल)',  'good_for' => 'व्यापार, यात्रा, नई विद्या',          'avoid' => 'स्थायी निर्माण',     'color' => '#1d4e8f'],
        'Vishakha'          => ['quality' => 'मिश्र',   'good_for' => 'प्रतिस्पर्धा, लक्ष्य निर्धारण',           'avoid' => 'साझेदारी अनुबंध',      'color' => '#b83020'],
        'Anuradha'          => ['quality' => 'मृदु (कोमल)',     'good_for' => 'मित्रता, समूह, दक्षिण यात्रा',           'avoid' => 'एकांत कार्य',        'color' => '#2e6090'],
        'Jyeshtha'          => ['quality' => 'तीक्ष्ण (तीव्र)',  'good_for' => 'नेतृत्व, मंत्र, रक्षा अनुष्ठान',   'avoid' => 'नए सम्बन्ध',           'color' => '#7a3a10'],
        'Moola'             => ['quality' => 'तीक्ष्ण (तीव्र)',  'good_for' => 'चिकित्सा, गुप्त विषय',         'avoid' => 'नए शुभ आरम्भ',     'color' => '#5a1a1a'],
        'Purva Ashadha'     => ['quality' => 'उग्र (तीव्र)',    'good_for' => 'जल कार्य, साहसी निर्णय',     'avoid' => 'शांतिपूर्ण वार्ता',       'color' => '#1a5a8a'],
        'Uttara Ashadha'    => ['quality' => 'ध्रुव (स्थिर)',   'good_for' => 'स्थायी कार्य, प्रतियोगिता में विजय',        'avoid' => 'कुछ नहीं — शुभ', 'color' => '#2e7a40'],
        'Shravana'          => ['quality' => 'चर (चल)',  'good_for' => 'श्रवण, विद्या, आध्यात्मिक अध्ययन',           'avoid' => 'आक्रामक कार्य',       'color' => '#1d4e6f'],
        'Dhanishta'         => ['quality' => 'चर (चल)',  'good_for' => 'संगीत, साहस, वित्त',           'avoid' => 'विवाह (परम्परा)',    'color' => '#b83020'],
        'Shatabhisha'       => ['quality' => 'चर (चल)',  'good_for' => 'उपचार, गुप्त विद्या, वैदिक अध्ययन',               'avoid' => 'सार्वजनिक कार्य',             'color' => '#1a3a8a'],
        'Purva Bhadrapada'  => ['quality' => 'उग्र (तीव्र)',    'good_for' => 'तीव्र कार्य, तपस्या',               'avoid' => 'सामाजिक आयोजन',               'color' => '#4a2a8a'],
        'Uttara Bhadrapada' => ['quality' => 'ध्रुव (स्थिर)',   'good_for' => 'विवाह, दान, वर्षा प्रार्थना',       'avoid' => 'कुछ नहीं — शुभ',        'color' => '#1d6070'],
        'Revati'            => ['quality' => 'मृदु (कोमल)',     'good_for' => 'यात्रा, समापन, पोषण कार्य',       'avoid' => 'नए आरम्भ',       'color' => '#6a3a8a'],
    ];

    private const YOGA_ADVICE = [
        'Mahavisha' => ['सभी नए कार्य, अनुबंध, यात्रा से बचें — विश्राम करें', 'विराम',       '#8a1a1a'],
        'Ashubha'   => ['नए कार्यों में सावधानी रखें','सावधानी',    '#7a5800'],
        'Subha'     => ['नए आरम्भ एवं शुभ कार्यों के लिए उत्तम समय','शुभ','#1a6a30'],
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
            'quality' => 'मिश्र', 'good_for' => 'सामान्य कार्य',
            'avoid' => 'कुछ नहीं विशेष', 'color' => '#1d4e6f',
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

        $dashaSummary = "दशा स्वामी: {$dasha['lord']} · {$dasha['yrs']} वर्ष {$dasha['mos']} माह शेष";

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

        $rahuKaal  = self::computeRahuKaal($dow, $ss);
        $yamaganda = self::computeYamaganda($dow, $ss);
        $gulika    = self::computeGulika($dow, $ss);
        $abhijit   = self::computeAbhijitMuhurta($ss);
        $horaTable = self::computeHoraTable($dow, $ss);

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
            'rahuKaal'          => $rahuKaal,
            'yamaganda'         => $yamaganda,
            'gulika'            => $gulika,
            'abhijit'           => $abhijit,
            'horaTable'         => $horaTable,
            'nakHints'          => $nakHints,
            'yogaAdvice'        => $yogaAdvice,
        ];
    }

    public static function renderHtml(array $d): string
    {
        $mq        = $d['muhurtaQuality']   ?? [];
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
        $vrats     = $d['vrats']             ?? [];
        $rahuKaal  = $d['rahuKaal']          ?? null;
        $yamaganda = $d['yamaganda']         ?? null;
        $gulika    = $d['gulika']            ?? null;
        $abhijit   = $d['abhijit']           ?? null;
        $horaTable = $d['horaTable']         ?? [];
        $nakHints  = $d['nakHints']          ?? [];
        $yogaAdv   = $d['yogaAdvice']        ?? [];
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

        // ── Translation maps (all Hindi) ─────────────────────────
        $signHiMap = [
            'Mesha'=>'मेष','Vrishabha'=>'वृषभ','Mithuna'=>'मिथुन','Karka'=>'कर्क',
            'Simha'=>'सिंह','Kanya'=>'कन्या','Tula'=>'तुला','Vrishchika'=>'वृश्चिक',
            'Dhanu'=>'धनु','Makara'=>'मकर','Kumbha'=>'कुंभ','Meena'=>'मीन',
            'Vrischika'=>'वृश्चिक',
        ];
        $nakHiMap = [
            'Ashwini'=>'अश्विनी','Bharani'=>'भरणी','Krittika'=>'कृत्तिका',
            'Rohini'=>'रोहिणी','Mrigashira'=>'मृगशिरा','Ardra'=>'आर्द्रा',
            'Punarvasu'=>'पुनर्वसु','Pushya'=>'पुष्य','Ashlesha'=>'आश्लेषा',
            'Magha'=>'मघा','Purva Phalguni'=>'पूर्व फाल्गुनी','Uttara Phalguni'=>'उत्तर फाल्गुनी',
            'Hasta'=>'हस्त','Chitra'=>'चित्रा','Swati'=>'स्वाती','Vishakha'=>'विशाखा',
            'Anuradha'=>'अनुराधा','Jyeshtha'=>'ज्येष्ठा','Moola'=>'मूल',
            'Purva Ashadha'=>'पूर्वाषाढ़','Uttara Ashadha'=>'उत्तराषाढ़','Shravana'=>'श्रवण',
            'Dhanishta'=>'धनिष्ठा','Shatabhisha'=>'शतभिषा','Purva Bhadrapada'=>'पूर्व भाद्रपद',
            'Uttara Bhadrapada'=>'उत्तर भाद्रपद','Revati'=>'रेवती',
        ];
        $lordHiMap = [
            'Sun'=>'सूर्य','Moon'=>'चन्द्र','Mercury'=>'बुध','Venus'=>'शुक्र',
            'Mars'=>'मंगल','Jupiter'=>'गुरु','Saturn'=>'शनि','Rahu'=>'राहु','Ketu'=>'केतु',
            'Brahma'=>'ब्रह्मा','Vishnu'=>'विष्णु','Shiva'=>'शिव','Agni'=>'अग्नि',
            'Varuna'=>'वरुण','Indra'=>'इन्द्र','Yama'=>'यम','Rudra'=>'रुद्र',
            'Kubera'=>'कुबेर','Durga'=>'दुर्गा','Lakshmi'=>'लक्ष्मी','Ganesha'=>'गणेश',
            'Kartikeya'=>'कार्तिकेय','Pitrs'=>'पितृगण','Nagas'=>'नागदेव',
            'Naga'=>'नागदेव','Brihaspati'=>'बृहस्पति','Chandra'=>'चन्द्र','Surya'=>'सूर्य',
            'Parvati'=>'पार्वती','Saraswati'=>'सरस्वती','Bhaga'=>'भग',
            'Aryaman'=>'अर्यमन','Savitar'=>'सवितार','Vishvakarma'=>'विश्वकर्मा',
            'Mitra'=>'मित्र','Tvashtr'=>'त्वष्टा','Soma'=>'सोम','Vayu'=>'वायु',
            'Indr-Agni'=>'इन्द्र-अग्नि','Indra-Agni'=>'इन्द्र-अग्नि',
            'Aja Ekapada'=>'अज एकपाद','Ahir Budhyana'=>'अहिर्बुध्न्य',
            'Pushan'=>'पूषा','Vishvedevas'=>'विश्वदेव','Nirrti'=>'निऋति',
            'Apas'=>'आपः','Ashwini Kumaras'=>'अश्विनी कुमार',
            'Ashta Vasus'=>'अष्ट वसु',
            // Yoga deities
            'Jaya'=>'जया','Vishkambha'=>'विष्कम्भ','Priti'=>'प्रीति',
            'Ayushman'=>'आयुष्मान','Saubhagya'=>'सौभाग्य',
        ];
        $tithiHiMap = [
            'Pratipada'=>'प्रतिपदा','Dwitiya'=>'द्वितीया','Tritiya'=>'तृतीया',
            'Chaturthi'=>'चतुर्थी','Panchami'=>'पञ्चमी','Shashthi'=>'षष्ठी',
            'Saptami'=>'सप्तमी','Ashtami'=>'अष्टमी','Navami'=>'नवमी',
            'Dashami'=>'दशमी','Ekadashi'=>'एकादशी','Dwadashi'=>'द्वादशी',
            'Trayodashi'=>'त्रयोदशी','Chaturdashi'=>'चतुर्दशी',
            'Purnima'=>'पूर्णिमा','Amavasya'=>'अमावस्या',
        ];
        $pakshaHiMap = ['Shukla'=>'शुक्ल पक्ष','Krishna'=>'कृष्ण पक्ष'];
        $natureHiMap = [
            'Auspicious'=>'शुभ','Inauspicious'=>'अशुभ','Mixed'=>'मिश्रित',
            'Nanda'=>'नन्दा','Bhadra'=>'भद्रा','Jaya'=>'जया','Rikta'=>'रिक्ता',
            'Purna'=>'पूर्णा','Ugra (Fierce)'=>'उग्र','Saumya (Gentle)'=>'सौम्य',
            'Guru (Auspicious)'=>'गुरु (शुभ)','Sthira (Stable)'=>'स्थिर',
            'Ugra'=>'उग्र','Saumya'=>'सौम्य','Guru'=>'गुरु','Sthira'=>'स्थिर',
            'Deva'=>'देव','Manushya'=>'मानुष्य','Rakshasa'=>'राक्षस',
            'Movable'=>'चर','Fixed'=>'स्थिर','Soft'=>'मृदु','Sharp'=>'तीक्ष्ण',
            'Swift'=>'क्षिप्र','Fierce'=>'उग्र','Mixed (Mishra)'=>'मिश्र',
            'Subha'=>'शुभ','Mahavisha'=>'महाविष','Ashubha'=>'अशुभ',
            'Kshipra (Quick)'=>'क्षिप्र','Dhruva (Fixed)'=>'ध्रुव',
            'Mridu (Soft)'=>'मृदु','Chara (Movable)'=>'चर','Tikshna (Sharp)'=>'तीक्ष्ण',
            'Mishra (Mixed)'=>'मिश्र','Ugra (Fierce)'=>'उग्र',
            'Nanda (Auspicious)'=>'नन्दा (शुभ)','Bhadra (Prosperous)'=>'भद्रा (समृद्ध)',
            'Purna (Full)'=>'पूर्णा (सम्पूर्ण)',
        ];
        $varaHiMap = [
            'Ravivara'=>'रविवार','Somavara'=>'सोमवार','Mangalavara'=>'मंगलवार',
            'Budhavara'=>'बुधवार','Guruvara'=>'गुरुवार','Shukravara'=>'शुक्रवार',
            'Shanivara'=>'शनिवार',
        ];
        $yogaHiMap = [
            'Vishkambha'=>'विष्कम्भ','Priti'=>'प्रीति','Ayushman'=>'आयुष्मान',
            'Saubhagya'=>'सौभाग्य','Shobhana'=>'शोभन','Atiganda'=>'अतिगण्ड',
            'Sukarma'=>'सुकर्मा','Dhriti'=>'धृति','Shoola'=>'शूल','Ganda'=>'गण्ड',
            'Vriddhi'=>'वृद्धि','Dhruva'=>'ध्रुव','Vyaghata'=>'व्याघात',
            'Harshana'=>'हर्षण','Vajra'=>'वज्र','Siddhi'=>'सिद्धि',
            'Vyatipata'=>'व्यतीपात','Variyan'=>'वरियान','Parigha'=>'परिघ',
            'Shiva'=>'शिव','Siddha'=>'सिद्ध','Sadhya'=>'साध्य',
            'Shubha'=>'शुभ','Shukla'=>'शुक्ल','Brahma'=>'ब्रह्म',
            'Indra'=>'इन्द्र','Vaidhriti'=>'वैधृति','Variyana'=>'वरियान',
        ];
        $karanaHiMap = [
            'Bava'=>'बव','Balava'=>'बालव','Kaulava'=>'कौलव','Taitila'=>'तैतिल',
            'Garija'=>'गरज','Vanija'=>'वणिज','Vishti'=>'विष्टि (भद्रा)',
            'Shakuni'=>'शकुनि','Chatushpada'=>'चतुष्पाद','Naga'=>'नाग',
            'Kimstughna'=>'किंस्तुघ्न','Kintughna'=>'किंतुघ्न','Bhadra'=>'भद्रा',
        ];
        // HINDI translations for karana favour (English → Hindi)
        $favourHiMap = [
            'Creative work, rituals'  => 'सृजन कार्य, अनुष्ठान',
            'Travel, business'        => 'यात्रा, व्यापार',
            'Trade, commerce'         => 'व्यापार, वाणिज्य',
            'Auspicious acts'         => 'शुभ कार्य',
            'Religious acts'          => 'धार्मिक कार्य',
            'All good works'          => 'सभी शुभ कार्य',
            'Agriculture, planting'   => 'कृषि, बीजारोपण',
            'Destruction of enemies'  => 'शत्रु नाश',
            'Water works'             => 'जल कार्य',
            'Mechanical work'         => 'यांत्रिक कार्य',
            'Nothing (inauspicious)'  => 'कुछ नहीं (अशुभ)',
            'Fire rituals'            => 'अग्नि अनुष्ठान',
            'Healing, medicine'       => 'उपचार, चिकित्सा',
            'Music, arts'             => 'संगीत, कला',
            'Government work'         => 'राजकीय कार्य',
            'Charity, giving'         => 'दान, परोपकार',
            'Spiritual practice'      => 'साधना, आध्यात्मिक अभ्यास',
        ];
        $yoniHiMap = [
            'Horse'=>'अश्व','Elephant'=>'गज','Sheep'=>'मेष','Serpent'=>'सर्प',
            'Dog'=>'श्वान','Cat'=>'मार्जार','Rat'=>'मूषक','Cow'=>'गौ',
            'Buffalo'=>'महिष','Tiger'=>'व्याघ्र','Deer'=>'मृग','Monkey'=>'वानर',
            'Lion'=>'सिंह','Mongoose'=>'नेवला',
        ];
        $nadiHiMap = ['Vata'=>'वात','Pitta'=>'पित्त','Kapha'=>'कफ'];
        $tattvaHiMap = ['Fire'=>'अग्नि','Earth'=>'पृथ्वी','Air'=>'वायु','Water'=>'जल','Ether'=>'आकाश'];
        $ganaHiMap = ['Deva'=>'देव गण','Manushya'=>'मानुष्य गण','Rakshasa'=>'राक्षस गण'];
        $karanaTypeHiMap = ['Chara'=>'चर','Sthira'=>'स्थिर','Fixed'=>'स्थिर','Movable'=>'चर'];

        $tr = function(string $en, array $map): string {
            if (isset($map[$en])) return $map[$en];
            foreach ($map as $k => $v) {
                if (stripos($en, $k) !== false) return $v;
            }
            return $en;
        };

        $PLANET_ORDER = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
        $PLANET_HI    = ['sun'=>'सूर्य','moon'=>'चन्द्र','mercury'=>'बुध','venus'=>'शुक्र',
                         'mars'=>'मंगल','jupiter'=>'गुरु','saturn'=>'शनि','rahu'=>'राहु','ketu'=>'केतु'];
        $PLANET_CLR   = ['sun'=>'#c87010','moon'=>'#1a4a8a','mercury'=>'#1a6a58','venus'=>'#7a1a6a',
                         'mars'=>'#a01810','jupiter'=>'#6a5010','saturn'=>'#382048','rahu'=>'#1a4830','ketu'=>'#5a2818'];
        $PLANET_BG    = ['sun'=>'#fff4e4','moon'=>'#eef2fc','mercury'=>'#eef8f5','venus'=>'#fceef8',
                         'mars'=>'#fceee8','jupiter'=>'#fdf8e8','saturn'=>'#f2eff8','rahu'=>'#eef6f0','ketu'=>'#faf2ee'];
        $PLANET_BD    = ['sun'=>'#f0c070','moon'=>'#9ab0d8','mercury'=>'#80c8b8','venus'=>'#d080b8',
                         'mars'=>'#d89080','jupiter'=>'#d0b870','saturn'=>'#a898c0','rahu'=>'#88c098','ketu'=>'#c09888'];

        // Derived display values
        $tithiNameHi  = $tr($tithi['name']   ?? '', $tithiHiMap);
        $pakshaNameHi = $tr($tithi['paksha'] ?? '', $pakshaHiMap);
        $varaNameHi   = $tr($vara['name']    ?? '', $varaHiMap);
        $varaLordHi   = $tr($vara['lord']    ?? '', $lordHiMap);
        $varaNatureHi = $tr($vara['nature']  ?? '', $natureHiMap);
        $varaDeityHi  = $tr($vara['deity']   ?? '', $lordHiMap);
        $varaHoraHi   = $tr($vara['horaLord']?? '', $lordHiMap);
        $nakNameHi    = $tr($nak['name']     ?? '', $nakHiMap);
        $nakLordHi    = $tr($nak['lord']     ?? '', $lordHiMap);
        $nakDeityHi   = $tr($nak['deity']    ?? '', $lordHiMap);
        $nakGanaHi    = $tr($nak['gana']     ?? '', $ganaHiMap);
        $nakQualHi    = $nak['quality'] ?? '—';
        $nakYoniHi    = $tr($nak['yoni']     ?? '', $yoniHiMap);
        $nakNadiHi    = $tr($nak['nadi']     ?? '', $nadiHiMap);
        $nakTattvaHi  = $tr($nak['tattva']   ?? '', $tattvaHiMap);
        $yogaNameHi   = $tr($yoga['name']    ?? '', $yogaHiMap);
        $yogaLordHi   = $tr($yoga['lord']    ?? '', $lordHiMap);
        $yogaDeityHi  = $tr($yoga['deity']   ?? '', $lordHiMap);
        $yogaNatHi    = $tr($yoga['nature']  ?? '', $natureHiMap);
        $karanaNameHi = $tr($karana['name']  ?? '', $karanaHiMap);
        $karanaLordHi = $tr($karana['lord']  ?? '', $lordHiMap);
        $karanaTypeHi = $tr($karana['type']  ?? '', $karanaTypeHiMap);
        // Translate favour field
        $karanaFavourRaw = $karana['favour'] ?? '—';
        $karanaFavourHi  = $favourHiMap[$karanaFavourRaw] ?? $tr($karanaFavourRaw, $lordHiMap);

        $sunrise = htmlspecialchars($d['sunrise'] ?? '—');
        $sunset  = htmlspecialchars($d['sunset']  ?? '—');
        $dayLen  = htmlspecialchars($d['dayLength']?? '—');

        $mqMeta = [
            'Excellent'   => ['#5a6400','श्रेष्ठ','#d8e890'],
            'Good'        => ['#6b3d00','शुभ','#f5dba0'],
            'Mixed'       => ['#7a4800','मिश्रित','#f0c878'],
            'Challenging' => ['#7a1a1a','साधारण','#f0b0a0'],
        ];
        [$mqColor, $mqHi, $mqBg] = $mqMeta[$mq['label'] ?? 'Good'] ?? $mqMeta['Good'];
        $mqPct = (int)($mq['pct'] ?? 50);

        $moonSignHi  = $tr($moon['sign'] ?? '', $signHiMap);
        $moonNakHi   = $tr($moon['nakshatra'] ?? '', $nakHiMap);
        $lagnaSignHi = $tr(($d['lagna'] ?? [])['sign'] ?? '', $signHiMap);

        $mqBarColor = $mq['label'] === 'Excellent' ? '#5a8a00'
                    : ($mq['label'] === 'Good'        ? '#c8520a'
                    : ($mq['label'] === 'Mixed'        ? '#b87800' : '#a02020'));

        $formatDateHi = function(string $ds) use ($MONTHS_HI): string {
            if (!$ds || strlen($ds) < 10) return $ds;
            [$y, $m, $da] = explode('-', substr($ds, 0, 10));
            return (int)$da . ' ' . ($MONTHS_HI[(int)$m] ?? '') . ' ' . $y;
        };

        $h = '';

        // ── CSS ────────────────────────────────────────────────────
        $h .= '<style>
@import url(\'https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit:ital@0;1&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap\');

.tp-root{
  --saffron:#c8521a;--saffron-lt:#fde8dc;--saffron-dk:#8a2e00;
  --gold:#9a6b0a;--gold-lt:#f5e6c0;--gold-mid:#c89020;--gold-dk:#6b4800;
  --cream:#fdf8f2;--cream2:#f9edd8;--cream3:#f0dfc0;--cream4:#e8d0a8;
  --ink:#1c1008;--ink2:#3a2410;--ink3:#5a3c18;--ink4:#8a6840;
  --teal:#1a5a50;--teal-lt:#d4eeea;
  --rule:rgba(168,112,40,.18);--rule2:rgba(168,112,40,.32);
  --shadow:rgba(90,40,0,.10);
  font-family:\'Crimson Pro\',Georgia,serif;
  color:var(--ink);
  background:var(--cream);
  padding:0;
  width:100%;
}
.tp-root *{box-sizing:border-box}

/* Ornamental rule */
.tp-rule{display:flex;align-items:center;gap:12px;margin:32px 0 20px;padding:0 4px}
.tp-rule-line{flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--gold-mid),transparent)}
.tp-rule-om{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.5rem;color:var(--gold-mid);line-height:1;flex-shrink:0}
.tp-rule-hi{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.6rem;color:var(--gold-dk);white-space:nowrap;flex-shrink:0}
.tp-rule-sub{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.82rem;letter-spacing:.5px;color:var(--ink3);white-space:nowrap;flex-shrink:0;font-weight:500}

/* ── HERO ── */
.tp-hero{
  background:linear-gradient(145deg,#6b2800 0%,#4a1800 50%,#2a0c00 100%);
  padding:34px 36px 28px;border-radius:16px;margin-bottom:20px;
  position:relative;overflow:hidden;
}
.tp-hero::before{
  content:\'\';position:absolute;inset:0;pointer-events:none;
  background-image:
    radial-gradient(circle at 15% 50%,rgba(200,144,32,.14) 0%,transparent 55%),
    radial-gradient(circle at 85% 20%,rgba(200,80,32,.10) 0%,transparent 45%),
    repeating-linear-gradient(0deg,rgba(200,144,32,.04) 0,rgba(200,144,32,.04) 1px,transparent 1px,transparent 36px),
    repeating-linear-gradient(90deg,rgba(200,144,32,.04) 0,rgba(200,144,32,.04) 1px,transparent 1px,transparent 36px);
}
.tp-hero::after{
  content:\'ॐ\';position:absolute;right:28px;top:50%;transform:translateY(-50%);
  font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:8rem;
  color:rgba(255,255,255,.04);pointer-events:none;user-select:none;line-height:1;
}
.tp-hero-inner{position:relative;z-index:1;display:grid;grid-template-columns:auto 1fr auto;gap:28px;align-items:start}
@media(max-width:640px){.tp-hero-inner{grid-template-columns:1fr;gap:18px}.tp-hero::after{display:none}}

.tp-date-col{text-align:center}
.tp-date-num{font-family:\'Crimson Pro\',serif;font-size:5rem;font-weight:700;line-height:1;color:#fff9f0;letter-spacing:-2px;text-shadow:0 2px 12px rgba(0,0,0,.4)}
.tp-date-month{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.4rem;color:rgba(255,220,140,.8);margin-top:-4px}
.tp-date-year{font-family:\'IBM Plex Mono\',monospace;font-size:.82rem;letter-spacing:1.5px;color:rgba(255,200,100,.6);margin-top:4px}

.tp-mid-col{}
.tp-vara-eyebrow{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.9rem;letter-spacing:.5px;color:rgba(255,220,140,.75);margin-bottom:6px;font-weight:500}
.tp-vara-name{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:2.8rem;color:#fff9f0;line-height:1.1;margin-bottom:4px}
.tp-vara-sub{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.1rem;color:rgba(255,200,130,.75);margin-bottom:16px}
.tp-sun-row{display:flex;gap:8px;flex-wrap:wrap}
.tp-sun-chip{display:flex;align-items:center;gap:8px;background:rgba(200,144,32,.14);border:1px solid rgba(200,144,32,.28);border-radius:10px;padding:7px 14px}
.tp-sun-lbl{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:rgba(255,220,140,.75)}
.tp-sun-val{font-family:\'IBM Plex Mono\',monospace;font-size:1.05rem;font-weight:500;color:rgba(255,240,200,.95)}
.tp-fest-pills{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
.tp-fest-pill{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;background:rgba(200,82,26,.2);border:1px solid rgba(200,144,32,.3);border-radius:50px;padding:5px 16px;color:rgba(255,220,140,.9)}

.tp-mq-col{text-align:right;flex-shrink:0;min-width:120px}
.tp-mq-eyebrow{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.85rem;letter-spacing:.5px;color:rgba(255,220,140,.65);margin-bottom:6px;font-weight:500}
.tp-mq-hi{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:2.2rem;font-weight:400;margin-bottom:4px}
.tp-mq-bar-row{display:flex;align-items:center;gap:8px;justify-content:flex-end}
.tp-mq-track{background:rgba(255,255,255,.1);border-radius:4px;height:6px;flex:1;max-width:100px;overflow:hidden}
.tp-mq-fill{height:100%;border-radius:4px}
.tp-mq-pct{font-family:\'IBM Plex Mono\',monospace;font-size:.88rem;font-weight:500}

/* ── PANCHANGA 5 ANGA GRID ── */
.tp-anga-wrap{border:1.5px solid var(--rule2);border-radius:14px;overflow:hidden;margin-bottom:6px;background:var(--cream2)}
.tp-anga-grid{display:grid;grid-template-columns:repeat(5,1fr);background:var(--cream3)}
@media(max-width:760px){.tp-anga-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:440px){.tp-anga-grid{grid-template-columns:repeat(2,1fr)}}
.tp-anga{background:var(--cream2);padding:22px 18px;border-right:1px solid var(--rule);border-bottom:1px solid var(--rule);transition:background .2s}
.tp-anga:nth-child(5),.tp-anga:nth-child(3n):last-child{border-right:none}
.tp-anga:hover{background:var(--cream)}
.tp-anga-num{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.82rem;color:var(--ink3);margin-bottom:7px;font-weight:600}
.tp-anga-lbl{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:var(--gold);margin-bottom:9px;font-weight:600}
.tp-anga-main{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.55rem;color:var(--ink);line-height:1.2;margin-bottom:6px}
.tp-anga-sub{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink3);line-height:1.6}

/* ── INFO CARDS ── */
.tp-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px;margin-bottom:4px}
.tp-card{background:var(--cream2);border:1.5px solid var(--rule2);border-radius:12px;padding:20px 20px 16px;border-top:4px solid var(--rule2)}
.tp-card-title{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.15rem;margin-bottom:14px;font-weight:600}
.tp-card-row{display:flex;justify-content:space-between;align-items:baseline;gap:8px;padding:9px 0;border-bottom:1px solid var(--rule)}
.tp-card-row:last-child{border-bottom:none}
.tp-card-key{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1rem;color:var(--ink3);flex-shrink:0}
.tp-card-val{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:var(--ink2);text-align:right;font-weight:600}

/* ── TIMING GRID (Rahu Kaal etc.) ── */
.tp-timing-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin-bottom:4px}
.tp-timing-card{border-radius:12px;padding:18px 20px;border:1.5px solid var(--rule2)}
.tp-timing-head{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.15rem;margin-bottom:4px;font-weight:600}
.tp-timing-sub{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;color:var(--ink3);margin-bottom:12px;line-height:1.5}
.tp-timing-time{font-family:\'IBM Plex Mono\',monospace;font-size:1.15rem;font-weight:600;letter-spacing:.5px}
.tp-timing-note{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.9rem;margin-top:6px;line-height:1.5}

.tp-timing-bad{background:#fdf0ec;border-color:rgba(180,60,20,.25)}
.tp-timing-bad .tp-timing-head{color:#8a2000}
.tp-timing-bad .tp-timing-time{color:#a02800}
.tp-timing-good{background:#eef8f0;border-color:rgba(30,100,70,.22)}
.tp-timing-good .tp-timing-head{color:#1a6040}
.tp-timing-good .tp-timing-time{color:#1a7048}

/* ── HORA TABLE — card grid layout ── */
.tp-hora-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}
.tp-hora-card{border-radius:10px;padding:14px 16px;border:1.5px solid var(--rule2);background:var(--cream2);transition:transform .15s,box-shadow .15s}
.tp-hora-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px var(--shadow)}
.tp-hora-card-active{border-width:2.5px;background:rgba(200,144,32,.10)}
.tp-hora-card-time{font-family:\'IBM Plex Mono\',monospace;font-size:.88rem;color:var(--ink3);margin-bottom:6px;font-weight:500}
.tp-hora-card-planet{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.35rem;line-height:1.2;font-weight:600}
.tp-hora-card-badge{display:inline-block;font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-top:5px;background:rgba(200,144,32,.15);color:var(--gold-dk)}
.tp-hora-card-active .tp-hora-card-badge{background:rgba(200,82,26,.2);color:var(--saffron-dk)}
.tp-hora-current-label{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.72rem;font-weight:700;color:var(--saffron-dk)}

/* ── VRAT CARDS ── */
.tp-vrat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:14px}
.tp-vrat-card{border-radius:12px;padding:22px 22px 18px;border-left:5px solid;background:var(--cream2);border-top:1.5px solid var(--rule);border-right:1.5px solid var(--rule);border-bottom:1.5px solid var(--rule)}
.tp-vrat-name{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.3rem;margin-bottom:4px;font-weight:600}
.tp-vrat-deity{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink3);margin-bottom:14px}
.tp-vrat-section{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.75rem;letter-spacing:.5px;color:var(--ink3);margin:12px 0 6px;font-weight:700;text-transform:uppercase}
.tp-vrat-text{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink2);line-height:1.7}
.tp-vrat-mantra{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.1rem;padding:10px 16px;border-radius:8px;background:var(--gold-lt);border:1px solid rgba(154,107,10,.25);color:var(--gold-dk);margin-top:12px;text-align:center}

/* ── TODAY FESTIVALS ── */
.tp-fest-cat{border-radius:12px;overflow:hidden;border:1.5px solid var(--rule2);margin-bottom:12px}
.tp-fest-cat-head{display:flex;align-items:center;padding:14px 20px;border-bottom:1px solid var(--rule);gap:12px}
.tp-fest-cat-hi{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.4rem;flex:1;font-weight:600}
.tp-fest-cat-cnt{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.85rem;font-weight:700;padding:4px 14px;border-radius:50px}
.tp-fest-item{padding:16px 20px;border-bottom:1px solid var(--rule);background:var(--cream)}
.tp-fest-item:last-child{border-bottom:none}
.tp-fest-item-name{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.15rem;color:var(--ink);margin-bottom:5px;font-weight:600}
.tp-fest-item-sig{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink3);line-height:1.65;margin-bottom:7px}
.tp-fest-item-mantra{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1rem;color:var(--gold-dk);font-style:italic}
.tp-fest-empty{padding:16px 20px;font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1rem;color:var(--ink4);font-style:italic}

/* ── PLANETS ── */
.tp-planet-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px}
.tp-pc{border-radius:12px;padding:16px 16px;border-top:4px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid;transition:transform .2s,box-shadow .2s}
.tp-pc:hover{transform:translateY(-3px);box-shadow:0 8px 24px var(--shadow)}
.tp-pc-hi{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.95rem;line-height:1;margin-bottom:3px}
.tp-pc-sign{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.2rem;color:var(--ink2);line-height:1.3;margin-bottom:4px}
.tp-pc-nak{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.92rem;color:var(--ink3);line-height:1.7}
.tp-pc-retro{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.82rem;font-weight:700;border:1.5px solid;border-radius:5px;padding:2px 8px;margin-top:6px;display:inline-block}
.tp-pc-deg{font-family:\'IBM Plex Mono\',monospace;font-size:.82rem;color:var(--ink4);margin-top:4px}

/* ── SIGN CHANGES ── */
.tp-sc-row{display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid var(--rule)}
.tp-sc-row:last-child{border-bottom:none}
.tp-sc-planet{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.25rem;min-width:65px;font-weight:600}
.tp-sc-from{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:var(--ink3)}
.tp-sc-arr{color:var(--ink4);font-size:1.1rem}
.tp-sc-to{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.15rem;font-weight:700;flex:1}
.tp-sc-date{text-align:right}
.tp-sc-dt{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink2);font-weight:600}
.tp-sc-days{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.88rem;color:var(--saffron);font-weight:700}

/* ── CALENDAR ── */
.tp-cal-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
@media(max-width:640px){.tp-cal-grid{grid-template-columns:1fr}}
.tp-cal-sect-head{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.3rem;color:var(--ink2);padding-bottom:10px;border-bottom:2px solid var(--rule2);margin-bottom:14px;font-weight:600}
.tp-cal-row{display:flex;align-items:flex-start;gap:10px;padding:12px 0;border-bottom:1px solid var(--rule)}
.tp-cal-row:last-child{border-bottom:none}
.tp-cal-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:6px}
.tp-cal-name{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;color:var(--ink);flex:1;line-height:1.4}
.tp-cal-date{text-align:right;flex-shrink:0}
.tp-cal-dt{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;color:var(--ink2);font-weight:600;white-space:nowrap}
.tp-cal-days{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.85rem;font-weight:700;color:var(--saffron)}

/* ── FOOTER ── */
.tp-footer{display:flex;border:1.5px solid var(--rule2);border-radius:14px;overflow:hidden;background:var(--cream2);margin-top:36px}
.tp-fi{flex:1;padding:22px 26px}
.tp-fi-lbl{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.82rem;letter-spacing:.5px;color:var(--ink3);margin-bottom:6px;font-weight:700}
.tp-fi-hi{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.55rem;color:var(--ink2);line-height:1.2}
.tp-fi-sub{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.98rem;color:var(--ink3);margin-top:4px}
.tp-fi-div{width:1.5px;background:var(--rule2);flex-shrink:0;align-self:stretch}

/* Info box for nakshatra / yoga */
.tp-info-box{border-radius:10px;padding:16px 20px;border-left:4px solid;margin-top:4px;margin-bottom:4px}
.tp-info-box-good{background:#eef8f0;border-color:#1a8050}
.tp-info-box-caution{background:#fdf4e4;border-color:#b87800}
.tp-info-box-avoid{background:#fdf0ec;border-color:#a02800}
.tp-info-text{font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1.05rem;line-height:1.8;color:var(--ink2)}
.tp-info-label{font-family:\'Noto Sans Devanagari\',sans-serif;font-size:.82rem;letter-spacing:.5px;color:var(--ink3);margin-bottom:6px;font-weight:700}

@media(max-width:700px){
  .tp-hero{padding:20px 16px}
  .tp-date-num{font-size:3.5rem}
  .tp-vara-name{font-size:2rem}
  .tp-planet-grid{grid-template-columns:repeat(3,1fr)}
  .tp-timing-grid{grid-template-columns:repeat(2,1fr)}
  .tp-anga-grid{grid-template-columns:repeat(2,1fr)}
  .tp-card-grid{grid-template-columns:1fr}
  .tp-footer{flex-direction:column}
  .tp-fi-div{width:100%;height:1px}
  .tp-hora-grid{grid-template-columns:repeat(3,1fr)}
}
</style>';

        $h .= '<div class="tp-root">';

        // ══════════════════════════════════════════════════════════
        // 1. HERO
        // ══════════════════════════════════════════════════════════
        $h .= '<div class="tp-hero"><div class="tp-hero-inner">';

        $h .= '<div class="tp-date-col">'
            . '<div class="tp-date-num">' . $dy . '</div>'
            . '<div class="tp-date-month">' . ($MONTHS_HI[$mo] ?? '') . '</div>'
            . '<div class="tp-date-year">' . $yr . '</div>'
            . '</div>';

        $h .= '<div class="tp-mid-col">'
            . '<div class="tp-vara-eyebrow">आज का वार</div>'
            . '<div class="tp-vara-name">' . htmlspecialchars($varaNameHi) . '</div>'
            . '<div class="tp-vara-sub">' . htmlspecialchars($varaLordHi) . ' · ' . htmlspecialchars($varaNatureHi) . '</div>'
            . '<div class="tp-sun-row">';
        $sunChips = ['सूर्योदय' => $sunrise, 'सूर्यास्त' => $sunset, 'दिन की अवधि' => $dayLen];
        foreach ($sunChips as $lbl => $val) {
            $h .= '<div class="tp-sun-chip">'
                . '<span class="tp-sun-lbl">' . $lbl . '</span>'
                . '<span class="tp-sun-val">' . $val . '</span>'
                . '</div>';
        }
        $h .= '</div>';
        if (!empty($allTodayFests)) {
            $h .= '<div class="tp-fest-pills">';
            foreach (array_slice($allTodayFests, 0, 4) as $f) {
                $fHi = htmlspecialchars($f['name_hi'] ?? $f['name'] ?? '');
                $h .= '<span class="tp-fest-pill">' . $fHi . '</span>';
            }
            $h .= '</div>';
        }
        $h .= '</div>';

        $h .= '<div class="tp-mq-col">'
            . '<div class="tp-mq-eyebrow">मुहूर्त गुणवत्ता</div>'
            . '<div class="tp-mq-hi" style="color:' . htmlspecialchars($mqBarColor) . ';text-shadow:0 0 20px rgba(255,255,255,.3)">' . htmlspecialchars($mqHi) . '</div>'
            . '<div class="tp-mq-bar-row">'
            . '<div class="tp-mq-track"><div class="tp-mq-fill" style="width:' . $mqPct . '%;background:' . htmlspecialchars($mqBarColor) . '"></div></div>'
            . '<span class="tp-mq-pct" style="color:' . htmlspecialchars($mqBarColor) . '">' . $mqPct . '%</span>'
            . '</div>'
            . '</div>';

        $h .= '</div></div>';

        // ══════════════════════════════════════════════════════════
        // 2. PANCHANGA — 5 ANGA
        // ══════════════════════════════════════════════════════════
        $h .= self::sectionRule('पञ्चाङ्ग', 'पाँच अंग');

        $angas = [
            ['①','तिथि','चन्द्र दिन',    $pakshaNameHi . ' ' . $tithiNameHi,   $tr($tithi['lord'] ?? '', $lordHiMap) . ' · ' . $tr($tithi['nature'] ?? '', $natureHiMap)],
            ['②','वार', 'सप्ताह दिन',   $varaNameHi,                           $varaLordHi . ' · ' . $varaNatureHi],
            ['③','नक्षत्र','चन्द्र मंडल',$nakNameHi,                            'पाद ' . ($nak['pada'] ?? '?') . ' · ' . $nakLordHi],
            ['④','योग', 'लूनी-सौर',      $yogaNameHi,                           $yogaNatHi . ' · ' . $yogaLordHi],
            ['⑤','करण','अर्ध तिथि',      $karanaNameHi,                         $karanaTypeHi . ' · ' . $karanaLordHi],
        ];

        $h .= '<div class="tp-anga-wrap"><div class="tp-anga-grid">';
        foreach ($angas as [$num, $hi, $sub, $main, $detail]) {
            $h .= '<div class="tp-anga">'
                . '<div class="tp-anga-num">' . $num . ' · ' . htmlspecialchars($sub) . '</div>'
                . '<div class="tp-anga-lbl">' . htmlspecialchars($hi) . '</div>'
                . '<div class="tp-anga-main">' . htmlspecialchars($main) . '</div>'
                . '<div class="tp-anga-sub">' . htmlspecialchars($detail) . '</div>'
                . '</div>';
        }
        $h .= '</div></div>';

        // ── Detail cards for panchanga ───────────────────────────
        $detailCards = [
            ['#a03818', 'तिथि विवरण', [
                ['पक्ष',                 $pakshaNameHi],
                ['क्रम संख्या',          ($tithi['num'] ?? '') . ' / १५'],
                ['तिथि स्वामी',          $tr($tithi['lord'] ?? '', $lordHiMap)],
                ['अधिष्ठात्र देवता',     $tr($tithi['deity'] ?? '', $lordHiMap)],
                ['स्वभाव',               $tr($tithi['nature'] ?? '', $natureHiMap)],
                ['चन्द्र-सूर्य अन्तर',  ($tithi['elong'] ?? '') . '°'],
            ]],
            ['#1a5a50', 'नक्षत्र विवरण', [
                ['क्रम संख्या',           ($nak['num'] ?? '') . ' / २७'],
                ['नक्षत्र स्वामी',        $nakLordHi],
                ['अधिष्ठात्र देवता',      $nakDeityHi],
                ['गण',                    $nakGanaHi],
                ['योनि',                  $nakYoniHi],
                ['नाड़ी',                 $nakNadiHi],
                ['तत्व',                  $nakTattvaHi],
                ['गुण',                   $nakQualHi],
            ]],
            ['#5a2888', 'योग विवरण', [
                ['क्रम संख्या',           ($yoga['num'] ?? '') . ' / २७'],
                ['देवता',                 $yogaDeityHi],
                ['वर्गीकरण',             $tr($yoga['cls'] ?? '', $natureHiMap)],
                ['स्वभाव',               $yogaNatHi],
                ['परामर्श',              $yoga['advice'] ?? '—'],
            ]],
            ['#7a4800', 'करण विवरण', [
                ['स्लॉट',                ($karana['slot'] ?? '') . ' / ६०'],
                ['प्रकार',               $karanaTypeHi],
                ['करण देवता',            $tr($karana['deity'] ?? '', $lordHiMap)],
                ['अनुकूल कार्य',         $karanaFavourHi],
                ['अर्ध',                 $tr($karana['half'] ?? '', ['First Half' => 'प्रथम अर्ध', 'Second Half' => 'द्वितीय अर्ध'])],
            ]],
        ];

        $h .= '<div class="tp-card-grid" style="margin-top:14px">';
        foreach ($detailCards as [$borderColor, $titleHi, $rows]) {
            $h .= '<div class="tp-card" style="border-top-color:' . $borderColor . '">'
                . '<div class="tp-card-title" style="color:' . $borderColor . '">' . htmlspecialchars($titleHi) . '</div>';
            foreach ($rows as [$k, $v]) {
                if (!$v || trim((string)$v) === '—') continue;
                $h .= '<div class="tp-card-row">'
                    . '<span class="tp-card-key">' . htmlspecialchars($k) . '</span>'
                    . '<span class="tp-card-val">' . htmlspecialchars((string)$v) . '</span>'
                    . '</div>';
            }
            $h .= '</div>';
        }
        $h .= '</div>';

        // ── Nakshatra advice box ──────────────────────────────────
        if (!empty($nakHints)) {
            $h .= '<div class="tp-info-box tp-info-box-good" style="margin-top:16px">'
                . '<div class="tp-info-label">नक्षत्र गुण — ' . htmlspecialchars($nakQualHi) . '</div>'
                . '<div class="tp-info-text">'
                . '<strong style="color:var(--teal)">शुभ कार्य:</strong> ' . htmlspecialchars($nakHints['good_for'] ?? '') . '<br>'
                . '<strong style="color:var(--saffron)">परहेज़:</strong> ' . htmlspecialchars($nakHints['avoid'] ?? '')
                . '</div></div>';
        }

        // ── Yoga advice box ───────────────────────────────────────
        if (!empty($yogaAdv)) {
            $yClass = ($yoga['cls'] ?? '') === 'Mahavisha' ? 'tp-info-box-avoid'
                    : (($yoga['cls'] ?? '') === 'Ashubha'  ? 'tp-info-box-caution' : 'tp-info-box-good');
            $h .= '<div class="tp-info-box ' . $yClass . '" style="margin-top:10px">'
                . '<div class="tp-info-label">योग परामर्श — ' . htmlspecialchars($yogaNameHi) . ' योग</div>'
                . '<div class="tp-info-text">' . htmlspecialchars($yogaAdv[0] ?? '') . '</div>'
                . '</div>';
        }

        // ══════════════════════════════════════════════════════════
        // 3. TODAY'S VRATS & OBSERVANCES  ← MOVED UP
        // ══════════════════════════════════════════════════════════
        if (!empty($vrats) || !empty($allTodayFests)) {
            $h .= self::sectionRule('आज के व्रत एवं अनुष्ठान', 'पूजन विधि एवं मंत्र');

            if (!empty($vrats)) {
                $h .= '<div class="tp-vrat-grid">';
                foreach ($vrats as $v) {
                    $clr = htmlspecialchars($v['color'] ?? '#9a6b0a');
                    $deityHi = $lordHiMap[$v['deity'] ?? ''] ?? ($v['deity'] ?? '');
                    $h .= '<div class="tp-vrat-card" style="border-left-color:' . $clr . '">'
                        . '<div class="tp-vrat-name" style="color:' . $clr . '">' . htmlspecialchars($v['name'] ?? '') . '</div>'
                        . '<div class="tp-vrat-deity">देवता: ' . htmlspecialchars($deityHi) . '</div>'
                        . '<div class="tp-vrat-section">शुभ फल</div>'
                        . '<div class="tp-vrat-text">' . htmlspecialchars($v['benefit'] ?? '') . '</div>'
                        . '<div class="tp-vrat-section">पूजन विधि</div>'
                        . '<div class="tp-vrat-text">' . htmlspecialchars($v['ritual'] ?? '') . '</div>'
                        . '<div class="tp-vrat-mantra">' . htmlspecialchars($v['mantra'] ?? '') . '</div>'
                        . '</div>';
                }
                $h .= '</div>';
            }

            $catDefs = [
                'vrat'    => ['व्रत', '#6a1a5a', 'rgba(106,26,90,.1)'],
                'parv'    => ['पर्व', '#8a3000', 'rgba(138,48,0,.1)'],
                'jayanti' => ['जयन्ती', '#1a3a6a', 'rgba(26,58,106,.1)'],
                'other'   => ['अन्य', '#1a5030', 'rgba(26,80,48,.1)'],
            ];
            foreach ($catDefs as $key => [$catHi, $accent, $headBg]) {
                $items = $todayFests[$key] ?? [];
                if (empty($items)) continue;
                $cnt = count($items);
                $h .= '<div class="tp-fest-cat" style="margin-top:14px;border-left:4px solid ' . $accent . '">'
                    . '<div class="tp-fest-cat-head" style="background:' . $headBg . '">'
                    . '<span class="tp-fest-cat-hi" style="color:' . $accent . '">' . $catHi . '</span>'
                    . '<span class="tp-fest-cat-cnt" style="background:' . $accent . ';color:#fff">' . $cnt . '</span>'
                    . '</div>';
                foreach ($items as $f) {
                    $fHi  = htmlspecialchars($f['name_hi'] ?? $f['name'] ?? '');
                    $fSig = htmlspecialchars(substr($f['significance'] ?? '', 0, 120));
                    $fMan = htmlspecialchars($f['mantra'] ?? '');
                    $h .= '<div class="tp-fest-item">'
                        . '<div class="tp-fest-item-name">' . $fHi . '</div>'
                        . ($fSig ? '<div class="tp-fest-item-sig">' . $fSig . (strlen($f['significance'] ?? '') > 120 ? '…' : '') . '</div>' : '')
                        . ($fMan ? '<div class="tp-fest-item-mantra">' . $fMan . '</div>' : '')
                        . '</div>';
                }
                $h .= '</div>';
            }
        }

        // ══════════════════════════════════════════════════════════
        // 4. MUHURTA — Rahu Kaal, Yamaganda, Gulika, Abhijit
        // ══════════════════════════════════════════════════════════
        $h .= self::sectionRule('मुहूर्त काल', 'शुभ एवं अशुभ समय');

        $h .= '<div class="tp-timing-grid">';

        if ($rahuKaal) {
            $h .= '<div class="tp-timing-card tp-timing-bad">'
                . '<div class="tp-timing-head">राहु काल</div>'
                . '<div class="tp-timing-sub">इस समय में शुभ कार्य न करें<br>राहु की अशुभ अवधि</div>'
                . '<div class="tp-timing-time">' . htmlspecialchars($rahuKaal['start']) . ' – ' . htmlspecialchars($rahuKaal['end']) . '</div>'
                . '<div class="tp-timing-note" style="color:#8a2000">किसी भी नए कार्य का आरम्भ वर्जित</div>'
                . '</div>';
        }
        if ($yamaganda) {
            $h .= '<div class="tp-timing-card tp-timing-bad">'
                . '<div class="tp-timing-head">यमगण्ड</div>'
                . '<div class="tp-timing-sub">यम का अशुभ काल<br>प्रवास एवं व्यापार से बचें</div>'
                . '<div class="tp-timing-time">' . htmlspecialchars($yamaganda['start']) . ' – ' . htmlspecialchars($yamaganda['end']) . '</div>'
                . '<div class="tp-timing-note" style="color:#8a2000">यात्रा एवं नए अनुबंध वर्जित</div>'
                . '</div>';
        }
        if ($gulika) {
            $h .= '<div class="tp-timing-card tp-timing-bad">'
                . '<div class="tp-timing-head">गुलिका काल</div>'
                . '<div class="tp-timing-sub">शनि पुत्र गुलिका का काल<br>मांगलिक कार्य न करें</div>'
                . '<div class="tp-timing-time">' . htmlspecialchars($gulika['start']) . ' – ' . htmlspecialchars($gulika['end']) . '</div>'
                . '<div class="tp-timing-note" style="color:#8a2000">मंगल कार्यों में बाधा संभव</div>'
                . '</div>';
        }
        if ($abhijit) {
            $h .= '<div class="tp-timing-card tp-timing-good">'
                . '<div class="tp-timing-head">अभिजित मुहूर्त</div>'
                . '<div class="tp-timing-sub">सर्वश्रेष्ठ मुहूर्त<br>विष्णु का आशीर्वाद प्राप्त काल</div>'
                . '<div class="tp-timing-time">' . htmlspecialchars($abhijit['start']) . ' – ' . htmlspecialchars($abhijit['end']) . '</div>'
                . '<div class="tp-timing-note" style="color:#1a6040">सभी शुभ कार्य सिद्ध होते हैं</div>'
                . '</div>';
        }

        $h .= '</div>';

        // ══════════════════════════════════════════════════════════
        // 5. HORA — Planetary Hour Card Grid (redesigned)
        // ══════════════════════════════════════════════════════════
        if (!empty($horaTable)) {
            $h .= self::sectionRule('होरा चक्र', 'ग्रह होरा — प्रत्येक घंटे का स्वामी');
            $currentHour = (int)date('G');
            $h .= '<div class="tp-hora-grid">';
            foreach ($horaTable as $hora) {
                $isActive = ($hora['hour'] === $currentHour);
                $cardCls  = 'tp-hora-card' . ($isActive ? ' tp-hora-card-active' : '');
                $borderStyle = $isActive
                    ? 'border-color:' . htmlspecialchars($hora['color']) . ';'
                    : '';
                $h .= '<div class="' . $cardCls . '" style="' . $borderStyle . '">'
                    . '<div class="tp-hora-card-time">' . htmlspecialchars($hora['timeStr']) . '</div>'
                    . '<div class="tp-hora-card-planet" style="color:' . htmlspecialchars($hora['color']) . '">'
                    . htmlspecialchars($hora['hi'])
                    . '</div>'
                    . ($isActive
                        ? '<div class="tp-hora-current-label">▶ वर्तमान होरा</div>'
                        : '<span class="tp-hora-card-badge">होरा</span>')
                    . '</div>';
            }
            $h .= '</div>';
        }

        // ══════════════════════════════════════════════════════════
        // 6. PLANETARY POSITIONS
        // ══════════════════════════════════════════════════════════
        $h .= self::sectionRule('नवग्रह स्थिति', 'नौ ग्रहों की वर्तमान स्थिति');

        $h .= '<div class="tp-planet-grid">';
        foreach ($PLANET_ORDER as $pid) {
            $p = $planets[$pid] ?? null;
            if (!$p) continue;
            $hi  = $PLANET_HI[$pid]  ?? $pid;
            $clr = $PLANET_CLR[$pid] ?? '#3a2418';
            $bg  = $PLANET_BG[$pid]  ?? '#f9edd8';
            $bd  = $PLANET_BD[$pid]  ?? '#c8a848';
            $signHi  = $tr($p['sign'] ?? '', $signHiMap);
            $nakHiP  = $tr($p['nak']  ?? '', $nakHiMap);
            $retro   = !empty($p['retro']);
            $h .= '<div class="tp-pc" style="background:' . $bg . ';border-top-color:' . $clr . ';border-left-color:' . $bd . ';border-right-color:' . $bd . ';border-bottom-color:' . $bd . '">'
                . '<div class="tp-pc-hi" style="color:' . $clr . '">' . htmlspecialchars($hi) . '</div>'
                . '<div class="tp-pc-sign">' . htmlspecialchars($signHi) . '</div>'
                . '<div class="tp-pc-nak">' . htmlspecialchars($nakHiP) . '<br>पाद ' . (int)($p['pada'] ?? 0) . '</div>'
                . '<div class="tp-pc-deg">' . number_format((float)($p['deg'] ?? 0), 2) . '°</div>'
                . ($retro ? '<div class="tp-pc-retro" style="color:' . $clr . ';border-color:' . $clr . '">वक्री</div>' : '')
                . '</div>';
        }
        $h .= '</div>';

        // ══════════════════════════════════════════════════════════
        // 7. UPCOMING SIGN CHANGES
        // ══════════════════════════════════════════════════════════
        if (!empty($upcomingP)) {
            $h .= self::sectionRule('ग्रह राशि परिवर्तन', 'आगामी राशि परिवर्तन');
            $h .= '<div style="background:var(--cream2);border:1.5px solid var(--rule2);border-radius:12px;padding:6px 20px">';
            foreach ($upcomingP as $ch) {
                $clr     = $PLANET_CLR[$ch['pid']] ?? '#3a2418';
                $hiNm    = $PLANET_HI[$ch['pid']] ?? $ch['pid'];
                $fromHi  = $tr($ch['fromSign'] ?? '', $signHiMap);
                $toHi    = $tr($ch['toSign']   ?? '', $signHiMap);
                $dtHi    = $formatDateHi($ch['date'] ?? '');
                $days    = (int)($ch['daysAway'] ?? 0);
                $dLbl    = $days === 0 ? 'आज' : ($days === 1 ? 'कल' : $days . ' दिन बाद');
                $h .= '<div class="tp-sc-row">'
                    . '<div class="tp-sc-planet" style="color:' . $clr . '">' . htmlspecialchars($hiNm) . '</div>'
                    . '<span class="tp-sc-from">' . htmlspecialchars($fromHi) . '</span>'
                    . '<span class="tp-sc-arr"> → </span>'
                    . '<span class="tp-sc-to" style="color:' . $clr . '">' . htmlspecialchars($toHi) . '</span>'
                    . '<div class="tp-sc-date">'
                    . '<div class="tp-sc-dt">' . htmlspecialchars($dtHi) . '</div>'
                    . '<div class="tp-sc-days">' . htmlspecialchars($dLbl) . '</div>'
                    . '</div></div>';
            }
            $h .= '</div>';
        }

        // ══════════════════════════════════════════════════════════
        // 8. FESTIVAL CALENDAR — Past & Upcoming
        // ══════════════════════════════════════════════════════════
        $h .= self::sectionRule('पर्व कैलेंडर', 'विगत एवं आगामी पर्व');

        $h .= '<div class="tp-cal-grid">';

        $h .= '<div><div class="tp-cal-sect-head">विगत पर्व <span style="font-size:.9rem;color:var(--ink4);font-weight:400"> — गत १५ दिन</span></div>';
        if (empty($pastFests)) {
            $h .= '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1rem;color:var(--ink4);font-style:italic">कोई विगत पर्व नहीं</div>';
        } else {
            foreach (array_slice($pastFests, 0, 12) as $f) {
                $fHi  = htmlspecialchars($f['name_hi'] ?? $f['name'] ?? '—');
                $dtHi = htmlspecialchars($formatDateHi($f['date'] ?? ''));
                $fType = strtolower($f['type'] ?? 'festival');
                $dotClr = str_contains($fType, 'vrat') ? '#6a1a5a' : (str_contains($fType, 'jayanti') ? '#1a3a6a' : '#8a3000');
                $h .= '<div class="tp-cal-row" style="opacity:.75">'
                    . '<div class="tp-cal-dot" style="background:' . $dotClr . '"></div>'
                    . '<div class="tp-cal-name">' . $fHi . '</div>'
                    . '<div class="tp-cal-date"><div class="tp-cal-dt">' . $dtHi . '</div></div>'
                    . '</div>';
            }
        }
        $h .= '</div>';

        $h .= '<div><div class="tp-cal-sect-head" style="border-bottom-color:var(--saffron);color:var(--saffron-dk)">आगामी पर्व <span style="font-size:.9rem;color:var(--ink4);font-weight:400"> — अगले १५ दिन</span></div>';
        if (empty($upcoming)) {
            $h .= '<div style="font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:1rem;color:var(--ink4);font-style:italic">कोई आगामी पर्व नहीं</div>';
        } else {
            foreach (array_slice($upcoming, 0, 12) as $f) {
                $fHi  = htmlspecialchars($f['name_hi'] ?? $f['name'] ?? '—');
                $dtHi = htmlspecialchars($formatDateHi($f['date'] ?? ''));
                $fType = strtolower($f['type'] ?? 'festival');
                $dotClr = str_contains($fType, 'vrat') ? '#6a1a5a' : (str_contains($fType, 'jayanti') ? '#1a3a6a' : '#8a3000');
                $daysA  = ($f['date'] && $dateISO) ? (int)round((strtotime($f['date']) - strtotime($dateISO)) / 86400) : 0;
                $dLbl   = $daysA === 0 ? 'आज' : ($daysA === 1 ? 'कल' : $daysA . ' दिन');
                $h .= '<div class="tp-cal-row">'
                    . '<div class="tp-cal-dot" style="background:' . $dotClr . '"></div>'
                    . '<div class="tp-cal-name">' . $fHi . '</div>'
                    . '<div class="tp-cal-date">'
                    . '<div class="tp-cal-dt">' . $dtHi . '</div>'
                    . '<div class="tp-cal-days">' . htmlspecialchars($dLbl) . '</div>'
                    . '</div></div>';
            }
        }
        $h .= '</div>';
        $h .= '</div>';

        // ══════════════════════════════════════════════════════════
        // 9. FOOTER
        // ══════════════════════════════════════════════════════════
        $moonPakshaHi = $pakshaHiMap[$moon['paksha'] ?? ''] ?? '';
        $footerItems = [
            ['चन्द्र राशि', $moonSignHi, $moonNakHi . ' · ' . $moonPakshaHi . ' तिथि ' . (int)($moon['tithiNum'] ?? 0)],
            ['लग्न', $lagnaSignHi, 'लाहिरी अयनांश · सायन'],
            ['दशा', $d['dasha'] ?? '—', 'विंशोत्तरी'],
        ];

        $h .= '<div class="tp-footer">';
        foreach ($footerItems as $i => [$lbl, $hi, $sub]) {
            if ($i > 0) $h .= '<div class="tp-fi-div"></div>';
            $h .= '<div class="tp-fi">'
                . '<div class="tp-fi-lbl">' . htmlspecialchars($lbl) . '</div>'
                . '<div class="tp-fi-hi">' . htmlspecialchars($hi) . '</div>'
                . '<div class="tp-fi-sub">' . htmlspecialchars($sub) . '</div>'
                . '</div>';
        }
        $h .= '</div>';

        $h .= '</div>';
        return $h;
    }

    private static function sectionRule(string $hi, string $sub): string
    {
        return '<div class="tp-rule">'
            . '<div class="tp-rule-line"></div>'
            . '<span class="tp-rule-om">॥</span>'
            . '<span class="tp-rule-hi">' . htmlspecialchars($hi) . '</span>'
            . '<span class="tp-rule-sub">' . htmlspecialchars($sub) . '</span>'
            . '<div class="tp-rule-line"></div>'
            . '</div>';
    }

    private static function computeRahuKaal(int $dow, ?array $ss): ?array
    {
        if (!$ss || $ss['polar'] || $ss['rise'] === null || $ss['set'] === null) return null;
        $rahuSlot = [8, 2, 7, 5, 6, 4, 3];
        $slot  = $rahuSlot[$dow];
        $dayDur = ($ss['set'] - $ss['rise']);
        $unit  = $dayDur / 8.0;
        $start = $ss['rise'] + ($slot - 1) * $unit;
        $end   = $start + $unit;
        return ['start' => self::hhmm($start), 'end' => self::hhmm($end)];
    }

    private static function computeYamaganda(int $dow, ?array $ss): ?array
    {
        if (!$ss || $ss['polar'] || $ss['rise'] === null || $ss['set'] === null) return null;
        $yamSlot = [4, 3, 2, 1, 7, 6, 5];
        $slot  = $yamSlot[$dow];
        $dayDur= ($ss['set'] - $ss['rise']);
        $unit  = $dayDur / 8.0;
        $start = $ss['rise'] + ($slot - 1) * $unit;
        return ['start' => self::hhmm($start), 'end' => self::hhmm($start + $unit)];
    }

    private static function computeGulika(int $dow, ?array $ss): ?array
    {
        if (!$ss || $ss['polar'] || $ss['rise'] === null || $ss['set'] === null) return null;
        $gulikaSlot = [7, 6, 5, 4, 3, 2, 1];
        $slot  = $gulikaSlot[$dow];
        $dayDur= ($ss['set'] - $ss['rise']);
        $unit  = $dayDur / 8.0;
        $start = $ss['rise'] + ($slot - 1) * $unit;
        return ['start' => self::hhmm($start), 'end' => self::hhmm($start + $unit)];
    }

    private static function computeAbhijitMuhurta(?array $ss): ?array
    {
        if (!$ss || $ss['polar'] || $ss['rise'] === null || $ss['set'] === null) return null;
        $midday = ($ss['rise'] + $ss['set']) / 2.0;
        $start  = $midday - (24.0 / 60.0);
        $end    = $midday + (24.0 / 60.0);
        return ['start' => self::hhmm($start), 'end' => self::hhmm($end)];
    }

    private static function computeHoraTable(int $dow, ?array $ss): array
    {
        if (!$ss || $ss['polar'] || $ss['rise'] === null || $ss['set'] === null) return [];
        $chaldean = ['Saturn','Jupiter','Mars','Sun','Venus','Mercury','Moon'];
        $dayLordIdx = [3, 6, 2, 5, 1, 4, 0];
        $startIdx   = $dayLordIdx[$dow];
        $horaDur    = ($ss['set'] - $ss['rise']) / 12.0;
        $PLANET_HI  = ['Sun'=>'सूर्य','Moon'=>'चन्द्र','Mercury'=>'बुध','Venus'=>'शुक्र',
                       'Mars'=>'मंगल','Jupiter'=>'गुरु','Saturn'=>'शनि'];
        $PLANET_CLR = ['Sun'=>'#c87010','Moon'=>'#1a4a8a','Mercury'=>'#1a6a58',
                       'Venus'=>'#7a1a6a','Mars'=>'#a01810','Jupiter'=>'#6a5010','Saturn'=>'#382048'];
        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $planet = $chaldean[($startIdx + $i) % 7];
            $start  = $ss['rise'] + $i * $horaDur;
            $end    = $start + $horaDur;
            $rows[] = [
                'hour'    => (int)floor($start) % 24,
                'timeStr' => self::hhmm($start) . '–' . self::hhmm($end),
                'planet'  => $planet,
                'hi'      => $PLANET_HI[$planet] ?? $planet,
                'color'   => $PLANET_CLR[$planet] ?? '#3a2418',
            ];
        }
        return $rows;
    }

    private static function hhmm(float $h): string
    {
        $h  = fmod($h + 24.0, 24.0);
        $hh = (int)$h;
        $mm = (int)round(($h - $hh) * 60.0);
        if ($mm === 60) { $hh++; $mm = 0; }
        $hh = $hh % 24;
        return sprintf('%02d:%02d', $hh, $mm);
    }

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
        if ($vara['classification'] === 'Guru')       $hints[] = ['type' => 'श्रेष्ठ',  'text' => 'गुरुवार — आध्यात्मिक अभ्यास, विद्या, विवाह के लिए श्रेष्ठ', 'color' => '#2e7a40'];
        elseif ($vara['classification'] === 'Saumya') $hints[] = ['type' => 'शुभ',      'text' => 'सौम्य वार — कोमल एवं रचनात्मक कार्यों के लिए अनुकूल',         'color' => '#1d5a8a'];
        else                                           $hints[] = ['type' => 'सावधानी',  'text' => 'उग्र वार — साहसी एवं निर्णायक कार्यों के लिए ऊर्जा लगाएं',     'color' => '#b83020'];
        $nature = $tithi['nature'] ?? '';
        if (str_contains($nature, 'Purna'))     $hints[] = ['type' => 'पूर्णा',   'text' => 'पूर्णा तिथि — सभी शुभ कार्यों के लिए अनुकूल', 'color' => '#2e7a40'];
        elseif (str_contains($nature, 'Rikta')) $hints[] = ['type' => 'सावधानी', 'text' => 'रिक्ता तिथि — आज नए कार्यों से बचें',            'color' => '#c47a20'];
        elseif (str_contains($nature, 'Jaya'))  $hints[] = ['type' => 'विजया',   'text' => 'जया तिथि — प्रतिस्पर्धी कार्यों के लिए श्रेष्ठ',  'color' => '#1d4e6f'];
        if ($yoga['cls'] === 'Mahavisha')       $hints[] = ['type' => 'अशुभ',   'text' => ($yoga['n'] ?? '') . ' योग — अत्यंत अशुभ; विश्राम करें', 'color' => '#b83020'];
        elseif ($yoga['cls'] === 'Ashubha')     $hints[] = ['type' => 'सावधानी','text' => ($yoga['n'] ?? '') . ' योग — सावधानी से आगे बढ़ें',      'color' => '#c47a20'];
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