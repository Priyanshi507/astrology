<?php

namespace App\Features\Festival;

use App\Features\Planetary\AstroCalculator;
use App\Features\Festival\MuhratCalculator;

/**
 * TarabalMurtiService — तारबल एवं मूर्ति निर्णय
 *
 * आधार: बृहत् पाराशर होरा शास्त्र (BPHS), मुहूर्त चिन्तामणि (दैवज्ञ राम),
 *        मुहूर्त गणपति, ज्योतिर्निबन्ध
 *
 * तारबल (Tarabala):
 *   BPHS: जन्म नक्षत्र से मुहूर्त नक्षत्र की दूरी नौ वर्गों में बाँटी जाती है।
 *   प्रत्येक वर्ग = एक "तारा" — शुभ/अशुभ फल निर्दिष्ट।
 *   तारा 1=जन्म, 2=सम्पत्, 3=विपत्, 4=क्षेम, 5=प्रत्यरि,
 *         6=साधक, 7=नैधन, 8=मित्र, 9=अतिमित्र
 *   सम्पत्, क्षेम, साधक, मित्र, अतिमित्र = शुभ (5)
 *   जन्म = सामान्य (neutral under BPHS strictest reading, अशुभ for muhurta)
 *   विपत्, प्रत्यरि, नैधन = अशुभ (3)
 *
 * मूर्ति निर्णय (Murti Nirnaya):
 *   BPHS Ch.87 + Muhurta Chintamani: वार + चन्द्र नक्षत्र + जन्म नक्षत्र की
 *   संयुक्त गणना से जातक की "मूर्ति" (रूप/अवतार) निर्धारित होती है।
 *   मूर्तियाँ: स्वर्ण, रजत, ताम्र, लौह — फल क्रमशः उत्तम→अशुभ।
 *   गणना: (वार + जन्म नक्षत्र + चन्द्र नक्षत्र) mod 4
 *          0→स्वर्ण, 1→रजत, 2→ताम्र, 3→लौह
 */
class TarabalMurtiService
{
    // ══════════════════════════════════════════════════════════════
    //  स्थैतिक तालिकाएँ — BPHS + MC
    // ══════════════════════════════════════════════════════════════

    public const NAK_HI = [
        'अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशिरा','आर्द्रा','पुनर्वसु',
        'पुष्य','आश्लेषा','मघा','पूर्वाफाल्गुनी','उत्तराफाल्गुनी','हस्त',
        'चित्रा','स्वाति','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढ़ा',
        'उत्तराषाढ़ा','श्रवण','धनिष्ठा','शतभिषा','पूर्वाभाद्रपदा',
        'उत्तराभाद्रपदा','रेवती',
    ];

    public const NAK_EN = [
        'Ashwini','Bharani','Krittika','Rohini','Mrigashira','Ardra','Punarvasu',
        'Pushya','Ashlesha','Magha','PurvaPhalguni','UttaraPhalguni','Hasta',
        'Chitra','Swati','Vishakha','Anuradha','Jyeshtha','Moola','PurvaAshadha',
        'UttaraAshadha','Shravana','Dhanishtha','Shatabhisha','PurvaBhadrapada',
        'UttaraBhadrapada','Revati',
    ];

    // नक्षत्र स्वामी (ग्रह) — 0=केतु,1=शुक्र,2=सूर्य,3=चन्द्र,4=मंगल,5=राहु,6=गुरु,7=शनि,8=बुध
    public const NAK_LORD = [0,1,2,3,4,5,6,7,8, 0,1,2,3,4,5,6,7,8, 0,1,2,3,4,5,6,7,8];

    public const NAK_LORD_HI = ['केतु','शुक्र','सूर्य','चन्द्र','मंगल','राहु','गुरु','शनि','बुध'];

    // नक्षत्र तत्त्व — 0=अग्नि,1=पृथ्वी,2=आकाश,3=वायु,4=जल
    public const NAK_TATVA = [0,1,2,3,4, 0,1,2,3,4, 0,1,2,3,4, 0,1,2,3,4, 0,1,2,3,4, 0,1];
    public const TATVA_HI  = ['अग्नि','पृथ्वी','आकाश','वायु','जल'];
    public const TATVA_COLOR= ['#c8521a','#7a5530','#4a4a7a','#2a7a5a','#1a5a8a'];

    // नक्षत्र गण — 0=देव, 1=मनुष्य, 2=राक्षस
    public const NAK_GANA  = [0,1,2,1,0,1,0,0,2, 2,1,1,0,2,0,2,0,2, 2,1,1,0,2,2,1,1,0];
    public const GANA_HI   = ['देव','मनुष्य','राक्षस'];

    // नक्षत्र नाड़ी — 0=आदि, 1=मध्य, 2=अन्त्य
    public const NAK_NADI  = [0,1,2,2,1,0,0,1,2, 2,1,0,0,1,2,2,1,0, 0,1,2,2,1,0,0,1,2];
    public const NADI_HI   = ['आदि','मध्य','अन्त्य'];

    // नक्षत्र गुण — 0=सात्विक,1=राजस,2=तामस
    public const NAK_GUNA  = [0,1,2,0,1,2,0,1,2, 0,1,2,0,1,2,0,1,2, 0,1,2,0,1,2,0,1,2];
    public const GUNA_HI   = ['सात्विक','राजस','तामस'];

    // नक्षत्र चरण स्वामी base: अश्विनी चरण 1 → मेष
    // प्रत्येक नक्षत्र के 4 चरण, क्रमशः मेष से मीन

    // वार हिन्दी नाम
    public const VARA_HI = ['रविवार','सोमवार','मंगलवार','बुधवार','गुरुवार','शुक्रवार','शनिवार'];

    // राशि हिन्दी नाम
    public const RASHI_HI = ['मेष','वृषभ','मिथुन','कर्क','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];

    // ── तारबल फल विवरण (BPHS + MC) ───────────────────────────────
    public const TARA_DATA = [
        1 => [
            'name'   => 'जन्म',
            'en'     => 'Janma',
            'shubh'  => false,
            'type'   => 'सामान्य',
            'color'  => '#c8a020',
            'icon'   => '⚠',
            'bphs'   => 'BPHS Ch.26: जन्म तारा — जातक को कष्ट, शारीरिक पीड़ा और धन हानि की संभावना। मुहूर्त में वर्जित।',
            'phala'  => 'भय, रोग, शरीर को कष्ट। यात्रा, विवाह, व्यापार आरम्भ में त्याज्य।',
            'bonus'  => -5,
        ],
        2 => [
            'name'   => 'सम्पत्',
            'en'     => 'Sampat',
            'shubh'  => true,
            'type'   => 'शुभ',
            'color'  => '#2d7a3a',
            'icon'   => '✦',
            'bphs'   => 'BPHS Ch.26: सम्पत् तारा — धन, ऐश्वर्य और सुख की प्राप्ति। अत्यन्त शुभ।',
            'phala'  => 'धन-धान्य वृद्धि, सुख-समृद्धि, कार्य सिद्धि। सभी मुहूर्त में उत्तम।',
            'bonus'  => 10,
        ],
        3 => [
            'name'   => 'विपत्',
            'en'     => 'Vipat',
            'shubh'  => false,
            'type'   => 'अशुभ',
            'color'  => '#a02010',
            'icon'   => '✗',
            'bphs'   => 'BPHS Ch.26: विपत् तारा — विपत्ति, शत्रु वृद्धि, धन हानि। मुहूर्त में वर्जित।',
            'phala'  => 'शत्रु-भय, आकस्मिक विपत्ति, कार्य में बाधा। सभी शुभ कार्यों में त्याज्य।',
            'bonus'  => -10,
        ],
        4 => [
            'name'   => 'क्षेम',
            'en'     => 'Kshema',
            'shubh'  => true,
            'type'   => 'शुभ',
            'color'  => '#1a5a8a',
            'icon'   => '✦',
            'bphs'   => 'BPHS Ch.26: क्षेम तारा — कल्याण, स्वास्थ्य, परिवार में सुख। शुभ।',
            'phala'  => 'आरोग्य, परिवार कल्याण, स्थिरता। यात्रा और व्यापार के लिए विशेष शुभ।',
            'bonus'  => 8,
        ],
        5 => [
            'name'   => 'प्रत्यरि',
            'en'     => 'Pratyari',
            'shubh'  => false,
            'type'   => 'अशुभ',
            'color'  => '#7a1a3a',
            'icon'   => '✗',
            'bphs'   => 'BPHS Ch.26: प्रत्यरि तारा — शत्रुता, कलह, हानि। अत्यन्त अशुभ।',
            'phala'  => 'शत्रु वृद्धि, कलह, मुकदमा। विवाह में विशेष वर्जित (नैधन तारा से भी अधिक हानिकर)।',
            'bonus'  => -12,
        ],
        6 => [
            'name'   => 'साधक',
            'en'     => 'Sadhaka',
            'shubh'  => true,
            'type'   => 'शुभ',
            'color'  => '#2a5a8a',
            'icon'   => '✦',
            'bphs'   => 'BPHS Ch.26: साधक तारा — साधना सिद्धि, उद्देश्य प्राप्ति। शुभ।',
            'phala'  => 'मनोरथ पूर्ति, सिद्धि, लक्ष्य प्राप्ति। दीक्षा, अध्ययन, व्यापार आरम्भ में उत्तम।',
            'bonus'  => 8,
        ],
        7 => [
            'name'   => 'नैधन',
            'en'     => 'Naidhana',
            'shubh'  => false,
            'type'   => 'अशुभ',
            'color'  => '#8a1a10',
            'icon'   => '✗',
            'bphs'   => 'BPHS Ch.26: नैधन तारा — मृत्यु-तुल्य कष्ट, भारी हानि। मुहूर्त में वर्जित।',
            'phala'  => 'जीवन-संकट, भारी धन-हानि, प्रिय-जन का वियोग। सभी मुहूर्त में त्याज्य।',
            'bonus'  => -12,
        ],
        8 => [
            'name'   => 'मित्र',
            'en'     => 'Mitra',
            'shubh'  => true,
            'type'   => 'शुभ',
            'color'  => '#5a3a8a',
            'icon'   => '✦',
            'bphs'   => 'BPHS Ch.26: मित्र तारा — मित्र लाभ, सहयोग, समृद्धि। शुभ।',
            'phala'  => 'मित्र-वृद्धि, सामाजिक प्रतिष्ठा, सहयोग। साझेदारी और संधि के लिए श्रेष्ठ।',
            'bonus'  => 8,
        ],
        9 => [
            'name'   => 'अतिमित्र',
            'en'     => 'Atimitra',
            'shubh'  => true,
            'type'   => 'अति शुभ',
            'color'  => '#1a6a2a',
            'icon'   => '★',
            'bphs'   => 'BPHS Ch.26: अतिमित्र तारा — महान सफलता, सर्वोच्च शुभ। MC: सभी मुहूर्त में श्रेष्ठतम।',
            'phala'  => 'सर्वोच्च सिद्धि, विजय, महान समृद्धि। किसी भी कार्य के लिए सर्वोत्तम।',
            'bonus'  => 12,
        ],
    ];

    // ── मूर्ति निर्णय — BPHS Ch.87 + MC Vivah Prakarana ──────────
    // मूर्ति = (वारांक + जन्म नक्षत्रांक + मुहूर्त नक्षत्रांक) mod 4
    // परन्तु कुछ ग्रन्थों में केवल (वार + नक्षत्र) का योग
    // BPHS strictest: vara_index (0=रवि) + muhurta_nak_index (0-26) → sum % 4
    // (जन्म नक्षत्र input होने पर: vara + birth_nak + muhurta_nak → sum % 4)
    public const MURTI_DATA = [
        0 => [
            'name'    => 'स्वर्ण मूर्ति',
            'en'      => 'Swarna (Gold)',
            'symbol'  => '◈',
            'color'   => '#c8a020',
            'bg'      => '#fef9e0',
            'quality' => 'अति उत्तम',
            'rank'    => 1,
            'bphs'    => 'BPHS Ch.87: स्वर्ण मूर्ति — सर्वोत्तम स्थिति। जातक पर देव कृपा, राजयोग, अपार सम्पत्ति की प्राप्ति।',
            'phala'   => 'विवाह: अत्यन्त शुभ, सम्पन्न दाम्पत्य। यात्रा: पूर्ण सफलता। व्यापार: महा लाभ। दीर्घायु और यश।',
            'upay'    => 'स्वर्ण का दान, सूर्य अर्घ्य, श्रीसूक्त पाठ',
        ],
        1 => [
            'name'    => 'रजत मूर्ति',
            'en'      => 'Rajata (Silver)',
            'symbol'  => '◇',
            'color'   => '#6a7a8a',
            'bg'      => '#f0f4f8',
            'quality' => 'शुभ',
            'rank'    => 2,
            'bphs'    => 'BPHS Ch.87: रजत मूर्ति — शुभ स्थिति। मध्यम से उत्तम फल। संतोषजनक परिणाम।',
            'phala'   => 'विवाह: शुभ, सुखी जीवन। यात्रा: सफल। व्यापार: लाभ। स्वास्थ्य अच्छा।',
            'upay'    => 'चाँदी का दान, चन्द्र अर्घ्य, चन्द्रनामाष्टक पाठ',
        ],
        2 => [
            'name'    => 'ताम्र मूर्ति',
            'en'      => 'Tamra (Copper)',
            'symbol'  => '◉',
            'color'   => '#b05010',
            'bg'      => '#fdf0e8',
            'quality' => 'मध्यम',
            'rank'    => 3,
            'bphs'    => 'BPHS Ch.87: ताम्र मूर्ति — मध्यम स्थिति। कार्य में कुछ बाधा सम्भव, उपाय से शुभ।',
            'phala'   => 'विवाह: मध्यम, कलह सम्भव। यात्रा: बाधा सम्भव। व्यापार: मध्यम लाभ। उपाय आवश्यक।',
            'upay'    => 'मंगल पूजन, लाल वस्त्र दान, हनुमान चालीसा',
        ],
        3 => [
            'name'    => 'लौह मूर्ति',
            'en'      => 'Loha (Iron)',
            'symbol'  => '◆',
            'color'   => '#3a3a4a',
            'bg'      => '#f0f0f4',
            'quality' => 'अशुभ',
            'rank'    => 4,
            'bphs'    => 'BPHS Ch.87: लौह मूर्ति — अशुभ स्थिति। पीड़ा, हानि, बाधा की प्रबल सम्भावना।',
            'phala'   => 'विवाह: अशुभ, वर्जित। यात्रा: खतरनाक। व्यापार: हानि। कार्य टालना श्रेयस्कर।',
            'upay'    => 'शनि पूजन, काले तिल दान, शनि स्तोत्र, महामृत्युंजय जप',
        ],
    ];

    // वार-नक्षत्र आधारित मूर्ति (बिना जन्म नक्षत्र के — सामान्य उपयोग)
    // Muhurta Chintamani तालिका: vara_idx (0-6) + muhurta_nak (0-26) → % 4
    // यह "मुहूर्त मूर्ति" है — जन्म नक्षत्र मिलाने पर "व्यक्तिगत मूर्ति" मिलती है

    // ── नक्षत्र शुभता वर्गीकरण (BPHS + MC) ────────────────────────
    // 0=अति शुभ(देव), 1=शुभ, 2=मध्यम, 3=अशुभ(क्रूर), 4=अति अशुभ(तीव्र)
    public const NAK_MUHURTA_TYPE = [
        0,  // अश्विनी — देव, शुभ (laghu/kshipra)
        4,  // भरणी — क्रूर, अशुभ (tikshna)
        2,  // कृत्तिका — मध्यम (mixed)
        0,  // रोहिणी — देव, अति शुभ (sthira)
        1,  // मृगशिरा — मृदु/कोमल, शुभ
        4,  // आर्द्रा — तीव्र, अशुभ (tikshna)
        1,  // पुनर्वसु — शुभ (chara)
        0,  // पुष्य — अति शुभ (laghu)
        3,  // आश्लेषा — अशुभ (tikshna)
        3,  // मघा — तीव्र, पितर (tikshna)
        1,  // पूर्वाफाल्गुनी — शुभ (ugra)
        0,  // उत्तराफाल्गुनी — अति शुभ (sthira)
        0,  // हस्त — शुभ (laghu)
        2,  // चित्रा — मध्यम (mridu)
        1,  // स्वाति — शुभ (chara)
        2,  // विशाखा — मध्यम (mixed)
        1,  // अनुराधा — शुभ (mridu)
        3,  // ज्येष्ठा — अशुभ (tikshna)
        4,  // मूल — अति अशुभ (tikshna)
        1,  // पूर्वाषाढ़ा — शुभ (ugra)
        0,  // उत्तराषाढ़ा — अति शुभ (sthira)
        0,  // श्रवण — शुभ (chara)
        1,  // धनिष्ठा — शुभ (chara)
        3,  // शतभिषा — मध्यम (chara/sthira mixed)
        1,  // पूर्वाभाद्रपदा — मध्यम (ugra)
        0,  // उत्तराभाद्रपदा — अति शुभ (sthira)
        0,  // रेवती — शुभ (mridu)
    ];

    // ══════════════════════════════════════════════════════════════
    //  MAIN: तारबल गणना — एक दिन के लिए
    // ══════════════════════════════════════════════════════════════
    /**
     * Compute Tarabala for a given date and birth nakshatra
     *
     * @param int    $yr          वर्ष
     * @param int    $mo          माह
     * @param int    $dy          दिन
     * @param float  $lat         अक्षांश
     * @param float  $lon         देशांतर
     * @param float  $utcOff      UTC+
     * @param int    $birthNak    जन्म नक्षत्र (0-26), -1 = अज्ञात
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

        // 27 तारा चक्र (BPHS exactness)
        // Each cycle of 9 gives: 1-9. Three cycles cover all 27 naks.
        // 1st cycle (naks 1-9): Primary tara
        // 2nd cycle (naks 10-18): same names, 2nd repetition
        // 3rd cycle (naks 19-27): same names, 3rd repetition
        $taraResult = null;
        $personalMurti = null;

        if ($birthNak >= 0) {
            $dist    = (($moonNak - $birthNak) + 27) % 27;
            $taraNum = ($dist % 9) + 1; // 1..9
            $cycle   = (int)floor($dist / 9) + 1; // 1st, 2nd, 3rd cycle
            $taraInfo = self::TARA_DATA[$taraNum];

            // Extended BPHS: 2nd and 3rd cycle reduce effect by 50%/25%
            $cycleModifier = match($cycle) { 2 => 0.5, 3 => 0.25, default => 1.0 };
            $effectiveBonus = (int)round($taraInfo['bonus'] * $cycleModifier);

            $taraResult = [
                'birthNak'       => $birthNak,
                'birthNakHi'     => self::NAK_HI[$birthNak],
                'moonNak'        => $moonNak,
                'moonNakHi'      => self::NAK_HI[$moonNak],
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
                'cycleNote'      => $cycle > 1 ? "({$cycle}वाँ आवर्त — प्रभाव " . ($cycle === 2 ? '50%' : '25%') . " कम)" : '',
            ];

            // Personal Murti: vara + birthNak + moonNak
            $murtiIdx = ($varaIdx + $birthNak + $moonNak) % 4;
            $personalMurti = array_merge(['idx' => $murtiIdx, 'type' => 'व्यक्तिगत (जन्म नक्षत्र सहित)'], self::MURTI_DATA[$murtiIdx]);
        }

        // General Murti: vara + moonNak (without birth nak)
        $generalMurtiIdx = ($varaIdx + $moonNak) % 4;
        $generalMurti = array_merge(['idx' => $generalMurtiIdx, 'type' => 'सामान्य (वार + चन्द्र नक्षत्र)'], self::MURTI_DATA[$generalMurtiIdx]);

        // All 9 taras from moonNak (informational table)
        $taraTable = [];
        for ($i = 0; $i < 27; $i++) {
            $tNum = ($i % 9) + 1;
            $nakIdx = ($moonNak + $i) % 27; // naks that are tara-i from today's moon
            $taraTable[$i] = [
                'nak'    => $nakIdx,
                'nakHi'  => self::NAK_HI[$nakIdx],
                'tara'   => $tNum,
                'taraHi' => self::TARA_DATA[$tNum]['name'],
                'shubh'  => self::TARA_DATA[$tNum]['shubh'],
                'color'  => self::TARA_DATA[$tNum]['color'],
                'cycle'  => (int)floor($i / 9) + 1,
            ];
        }

        // Nakshatra details
        $nakDetails = self::getNakDetails($moonNak, $moonPada);

        // Chandrabala (if birth rashi provided — MuhratCalculator does not expose a public getter here)
        // Use empty placeholder — detailed Chandra Bala is computed within MuhratCalculator during vivah checks.
        $chandrabala = [];

        return [
            'date'          => "{$yr}-{$mo}-{$dy}",
            'varaIdx'       => $varaIdx,
            'varaHi'        => self::VARA_HI[$varaIdx],
            'sunrise'       => AstroCalculator::decToHMS($riseHr),
            'sunset'        => AstroCalculator::decToHMS($setHr),
            'moonNak'       => $moonNak,
            'moonNakHi'     => self::NAK_HI[$moonNak],
            'moonNakEn'     => self::NAK_EN[$moonNak],
            'moonPada'      => $moonPada,
            'moonRashi'     => $moonRashi,
            'moonRashiHi'   => self::RASHI_HI[$moonRashi],
            'moonLord'      => self::NAK_LORD[$moonNak],
            'moonLordHi'    => self::NAK_LORD_HI[self::NAK_LORD[$moonNak]],
            'moonGana'      => self::GANA_HI[self::NAK_GANA[$moonNak]],
            'moonNadi'      => self::NADI_HI[self::NAK_NADI[$moonNak]],
            'moonTatva'     => self::TATVA_HI[self::NAK_TATVA[$moonNak]],
            'moonTatvaColor'=> self::TATVA_COLOR[self::NAK_TATVA[$moonNak]],
            'nakMuhurtaType'=> self::NAK_MUHURTA_TYPE[$moonNak],
            'sunNak'        => $sunNak,
            'sunNakHi'      => self::NAK_HI[$sunNak],
            'sunRashiHi'    => self::RASHI_HI[$sunRashi],
            'tithiHi'       => ($tk['tithi']['paksha'] ?? '') . ' ' . ($tk['tithi']['num'] ?? ''),
            'nakDetails'    => $nakDetails,
            'taraResult'    => $taraResult,
            'personalMurti' => $personalMurti,
            'generalMurti'  => $generalMurti,
            'taraTable'     => $taraTable,
            'allTaraData'   => self::TARA_DATA,
            'allMurtiData'  => self::MURTI_DATA,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  MAIN: मूर्ति निर्णय — विस्तृत (for dedicated panel)
    // ══════════════════════════════════════════════════════════════
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

        // Computations
        $generalMurtiIdx  = ($varaIdx + $moonNak) % 4;
        $generalMurti     = array_merge(['idx'=>$generalMurtiIdx,'type'=>'सामान्य'], self::MURTI_DATA[$generalMurtiIdx]);

        $personalMurti    = null;
        $chandrabala      = null;
        $tarabal          = null;

        if ($birthNak >= 0) {
            $pMurtiIdx    = ($varaIdx + $birthNak + $moonNak) % 4;
            $personalMurti= array_merge(['idx'=>$pMurtiIdx,'type'=>'व्यक्तिगत'], self::MURTI_DATA[$pMurtiIdx]);

            // Tarabala for murti context
            $dist  = (($moonNak - $birthNak) + 27) % 27;
            $tNum  = ($dist % 9) + 1;
            $tarabal = ['taraNum'=>$tNum, 'name'=>self::TARA_DATA[$tNum]['name'],
                        'shubh'=>self::TARA_DATA[$tNum]['shubh'], 'color'=>self::TARA_DATA[$tNum]['color']];
        }
        if ($birthRashi >= 0) {
            $chandrabala = [];
        }

        // All 4 murti calculations for reference
        $murtiForAllVara = [];
        for ($v = 0; $v < 7; $v++) {
            $idx = ($v + $moonNak) % 4;
            $murtiForAllVara[] = [
                'vara'   => self::VARA_HI[$v],
                'idx'    => $idx,
                'murti'  => self::MURTI_DATA[$idx]['name'],
                'quality'=> self::MURTI_DATA[$idx]['quality'],
                'color'  => self::MURTI_DATA[$idx]['color'],
                'shubh'  => $idx <= 1,
            ];
        }

        // Nakshatra muhurta quality
        $nakMuhurtaTypes = [
            0 => ['name'=>'अति शुभ (लघु/क्षिप्र)','desc'=>'कला, शिल्प, भ्रमण, क्रय-विक्रय के लिए उत्तम'],
            1 => ['name'=>'शुभ (मृदु)','desc'=>'विवाह, मित्रता, गृहारम्भ के लिए शुभ'],
            2 => ['name'=>'मध्यम (चर)','desc'=>'यात्रा और नए कार्य के लिए ठीक'],
            3 => ['name'=>'अशुभ (उग्र/क्रूर)','desc'=>'बाधाकारक कार्य, शत्रु नाश, जादू-टोने के लिए'],
            4 => ['name'=>'अति अशुभ (तीव्र/तीक्ष्ण)','desc'=>'सभी शुभ कार्यों में वर्जित'],
        ];

        return [
            'date'           => "{$yr}-{$mo}-{$dy}",
            'varaIdx'        => $varaIdx,
            'varaHi'         => self::VARA_HI[$varaIdx],
            'sunrise'        => AstroCalculator::decToHMS($riseHr),
            'sunset'         => AstroCalculator::decToHMS($setHr),
            'moonNak'        => $moonNak,
            'moonNakHi'      => self::NAK_HI[$moonNak],
            'moonPada'       => $moonPada,
            'moonRashi'      => $moonRashi,
            'moonRashiHi'    => self::RASHI_HI[$moonRashi],
            'moonLordHi'     => self::NAK_LORD_HI[self::NAK_LORD[$moonNak]],
            'moonGana'       => self::GANA_HI[self::NAK_GANA[$moonNak]],
            'moonNadi'       => self::NADI_HI[self::NAK_NADI[$moonNak]],
            'sunNakHi'       => self::NAK_HI[$sunNak],
            'sunRashiHi'     => self::RASHI_HI[$sunRashi],
            'generalMurti'   => $generalMurti,
            'personalMurti'  => $personalMurti,
            'tarabal'        => $tarabal,
            'chandrabala'    => $chandrabala,
            'murtiFormula'   => [
                'general'  => "वार ({$varaIdx}) + चन्द्र नक्षत्र ({$moonNak}) = " . ($varaIdx + $moonNak) . " → " . ($generalMurtiIdx) . " (mod 4)",
                'personal' => $birthNak >= 0 ? "वार ({$varaIdx}) + जन्म नक्षत्र ({$birthNak}) + चन्द्र नक्षत्र ({$moonNak}) = " . ($varaIdx + $birthNak + $moonNak) . " → " . (($varaIdx + $birthNak + $moonNak) % 4) . " (mod 4)" : null,
            ],
            'murtiForAllVara'=> $murtiForAllVara,
            'nakMuhurtaType' => $nakMuhurtaTypes[self::NAK_MUHURTA_TYPE[$moonNak]],
            'nakMuhurtaIdx'  => self::NAK_MUHURTA_TYPE[$moonNak],
            'allMurtiData'   => self::MURTI_DATA,
            'nakDetails'     => self::getNakDetails($moonNak, $moonPada),
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  HELPER: नक्षत्र विवरण
    // ══════════════════════════════════════════════════════════════
    public static function getNakDetails(int $nak, int $pada = 1): array
    {
        $charanaRashi = ($nak * 4 + ($pada - 1)) % 12; // चरण राशि
        $navamshaLord = self::RASHI_HI[$charanaRashi];

        $nakDesc = [
            0  => 'अश्व, अश्विनी कुमार (देव-वैद्य), आरम्भ, शीघ्रता',
            1  => 'यम, भरण-पोषण, जन्म-मृत्यु का चक्र',
            2  => 'अग्नि, कार्तिकेय, ऊर्जा, संक्रमण',
            3  => 'ब्रह्मा, पालन-पोषण, उर्वरता, स्थिरता',
            4  => 'चन्द्र, सौम्यता, जिज्ञासा, द्वैत',
            5  => 'रुद्र, वर्षा, तीव्रता, परिवर्तन',
            6  => 'अदिति (माँ), विस्तार, पुनरागमन, उदारता',
            7  => 'बृहस्पति (देवगुरु), ज्ञान, पोषण, शान्ति',
            8  => 'सर्प (नाग), गुप्त ज्ञान, भय, सूक्ष्मता',
            9  => 'पितर, मघा, पूर्वज, राज्य, परम्परा',
            10 => 'भग (ऐश्वर्य), विश्राम, प्रेम, सौन्दर्य',
            11 => 'अर्यमन (सूर्य), कर्तव्य, व्यवस्था, नेतृत्व',
            12 => 'सूर्य, कौशल, स्पष्टता, शिल्प',
            13 => 'त्वष्टा (विश्वकर्मा), सौन्दर्य, कला, जीवन-साथी',
            14 => 'वायु, स्वतन्त्रता, सूचना, व्यापार',
            15 => 'इन्द्र-अग्नि (युगल), शक्ति, विजय, विस्तार',
            16 => 'मित्र, मित्रता, भक्ति, भावना',
            17 => 'इन्द्र (राजा), सम्पन्नता, शक्ति, ईर्ष्या',
            18 => 'निऋति, मूल, उखाड़ना, ज्ञान की जड़',
            19 => 'अप-जल, शुद्धि, आध्यात्म, शक्ति',
            20 => 'विश्वेदेव, विजय, सार्वभौमिकता',
            21 => 'विष्णु, श्रवण, परम्परा, संचार',
            22 => 'अष्टवसु, गति, धन, यश',
            23 => 'वरुण, उपचार, रहस्य, विशालता',
            24 => 'अजैकपाद, रहस्य, गहराई, परिवर्तन',
            25 => 'अहिर्बुध्न्य, ज्ञान, साधना, आधार',
            26 => 'पूषन, पोषण, मार्गदर्शन, अन्त',
        ];

        return [
            'nak'          => $nak,
            'nakHi'        => self::NAK_HI[$nak],
            'nakEn'        => self::NAK_EN[$nak],
            'lord'         => self::NAK_LORD_HI[self::NAK_LORD[$nak]],
            'lordIdx'      => self::NAK_LORD[$nak],
            'gana'         => self::GANA_HI[self::NAK_GANA[$nak]],
            'nadi'         => self::NADI_HI[self::NAK_NADI[$nak]],
            'tatva'        => self::TATVA_HI[self::NAK_TATVA[$nak]],
            'tatvaColor'   => self::TATVA_COLOR[self::NAK_TATVA[$nak]],
            'guna'         => self::GUNA_HI[self::NAK_GUNA[$nak]],
            'pada'         => $pada,
            'charanaRashi' => $navamshaLord,
            'desc'         => $nakDesc[$nak] ?? '',
            'muhurtaType'  => self::NAK_MUHURTA_TYPE[$nak],
        ];
    }
}