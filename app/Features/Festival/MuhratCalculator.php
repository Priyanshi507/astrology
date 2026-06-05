<?php

namespace App\Features\Festival;

use App\Features\Planetary\AstroCalculator;

/**
 * MuhratCalculator — पूर्ण वैदिक मुहूर्त शास्त्र
 *
 * आधार: मुहूर्त चिन्तामणि, दैवज्ञ राम
 * गणना: Jean Meeus via AstroCalculator · लाहिरी अयनांश
 *
 * ─── सम्पूर्ण मुहूर्त श्रेणियाँ ────────────────────────
 *   १. चौघड़िया          (Choghadiya)
 *   २. शुभ होरा          (Shubha Hora)
 *   ३. अभिजित् मुहूर्त  (Abhijit Muhurta)
 *   ४. राहुकाल           (Rahu Kala)
 *   ५. शुभ योग           (Auspicious Yoga)
 *   ६. लग्न तालिका       (Lagna Table)
 *   ७. पञ्चक रहित        (Panchaka Rahita)
 *   ८. दो घटी मुहूर्त    (Do Ghati Muhurta)
 *   ९. गौरी पञ्चाङ्ग     (Gowri Panchangam)
 *  १०. विवाह मुहूर्त     (Vivah Muhurta) — MC Vivah Prakarana complete
 *  ११. गृहप्रवेश         (Griha Pravesh)
 *  १२. वाहन क्रय         (Vahana)
 *  १३. मुण्डन            (Mundan)
 *  १४. सम्पत्ति क्रय     (Property Purchase)
 *  १५. शुभ तिथियाँ       (Shubha Dates — month scan)
 * ─────────────────────────────────────────────────────────
 */
class MuhratCalculator
{
    // ═══════════════════════════════════════════════════════════
    //  स्थैतिक तालिकाएँ — Muhurta Chintamani (Daivajna Rama)
    // ═══════════════════════════════════════════════════════════

    // ── नक्षत्र तालिका ──────────────────────────────────────
    private const NAK_RASHI = [0,0,0,1,1,2,2,3,3,4,4,4,5,5,6,6,7,7,8,8,8,9,9,10,10,11,11];
    private const NAK_GANA  = [0,1,2,1,0,1,0,0,2,2,1,1,0,2,0,2,0,2,2,1,1,0,2,2,1,1,0];
    // 0=देव 1=मनुष्य 2=राक्षस
    private const NAK_NADI  = [0,1,2,2,1,0,0,1,2,2,1,0,0,1,2,2,1,0,0,1,2,2,1,0,0,1,2];
    // 0=आदि 1=मध्य 2=अन्त्य
    private const NAK_YONI  = [0,1,2,3,3,4,5,2,5,6,6,7,8,9,8,9,10,10,4,11,12,11,13,0,13,7,1];
    // 0=अश्व 1=गज 2=मेष 3=सर्प 4=श्वान 5=मार्जार 6=मूषक 7=गो 8=महिष 9=व्याघ्र 10=मृग 11=वानर 12=नकुल 13=सिंह

    private const RASHI_LORD  = [0,1,2,3,4,2,1,0,5,6,6,5];
    // 0=Mars 1=Venus 2=Mercury 3=Moon 4=Sun 5=Jupiter 6=Saturn
    private const RASHI_VARNA = [1,2,3,0,1,2,3,0,1,2,3,0];
    // 0=Brahmin 1=Kshatriya 2=Vaishya 3=Shudra
    private const RASHI_VASYA = [[1,4],[0,3],[5,11],[2,10],[0,3],[2,9],[5,9],[6,3],[11,3],[6,5],[8,11],[10,8]];
    private const GRAHA_MITRA = [
        0=>[1,2,4],1=>[0,2,4],2=>[0,1,4],3=>[0,5,6],4=>[0,1,2],5=>[2,3,6],6=>[3,5]
    ];
    private const GRAHA_SHATRU = [
        0=>[5,6],1=>[3],2=>[3,5,6],3=>[1,2],4=>[3,5,6],5=>[0,1],6=>[0,1,2,4]
    ];
    private const BHAKOOT_DOSHA = [2,12,5,9,6,8];
    private const TARA_SHUBHA   = [2,4,6,8,9]; // संपत्, क्षेम, साधक, मित्र, अतिमित्र
    private const YONI_SHATRU   = [[0,4],[1,13],[2,9],[3,12],[5,11],[6,14],[7,8],[10,14]];

    // ── वार अनुक्रमांक 0=रवि…6=शनि ─────────────────────────
    // Day part (1–8 of sunrise→sunset) per weekday (0=Sun … 6=Sat).
    // Standard Panchang values: Sun → Rahu 8th, Yamaganda 5th, Gulika 7th.
    private const RAHU_PART   = [8,2,7,5,6,4,3];
    private const YAMA_PART   = [5,4,3,2,1,7,6];
    private const GULIKA_PART = [7,6,5,4,3,2,1];
    private const DURMUHURTA_PART = [
        0 => [3, 5], 1 => [7], 2 => [3, 8], 3 => [6], 4 => [6, 7], 5 => [1, 8], 6 => [1, 2]
    ];

    // ── चौघड़िया तालिका (MC + traditional) ───────────────────
    // दिन का चौघड़िया — वार से प्रारम्भ (0=रवि, क्रम: उद्वेग, चर, लाभ, अमृत, काल, शुभ, रोग, उद्वेग)
    private const CHOGHADIYA_DAY = [
        0 => [6,1,2,3,4,5,0,6], // रवि: उद्वेग,चर,लाभ,अमृत,काल,शुभ,रोग,उद्वेग
        1 => [3,4,5,0,6,1,2,3], // सोम
        2 => [0,6,1,2,3,4,5,0], // मंगल
        3 => [2,3,4,5,0,6,1,2], // बुध
        4 => [5,0,6,1,2,3,4,5], // गुरु
        5 => [1,2,3,4,5,0,6,1], // शुक्र
        6 => [4,5,0,6,1,2,3,4], // शनि
    ];
    private const CHOGHADIYA_NIGHT = [
        0 => [5,3,1,0,4,2,6,5],
        1 => [1,0,4,2,6,5,3,1],
        2 => [4,2,6,5,3,1,0,4],
        3 => [6,5,3,1,0,4,2,6],
        4 => [3,1,0,4,2,6,5,3],
        5 => [0,4,2,6,5,3,1,0],
        6 => [2,6,5,3,1,0,4,2],
    ];
    // 0=रोग 1=चर 2=लाभ 3=अमृत 4=काल 5=शुभ 6=उद्वेग
    private const CHOGHADIYA_NAMES  = ['रोग','चर','लाभ','अमृत','काल','शुभ','उद्वेग'];
    private const CHOGHADIYA_NATURE = ['अशुभ','सामान्य','शुभ','अति शुभ','अशुभ','शुभ','अशुभ'];
    private const CHOGHADIYA_PLANET = ['मंगल','शनि','गुरु','चन्द्र','सूर्य','शुक्र','बुध'];
    private const CHOGHADIYA_COLOR  = ['#c0302a','#7a6010','#2d7a3a','#1a5a8a','#8a2010','#2a6030','#c86a14'];

    // ── होरा (Hora) — ग्रह क्रम ─────────────────────────────
    // 0=Sun,1=Moon,2=Mars,3=Mercury,4=Jupiter,5=Venus,6=Saturn
    // Day lord → first hora: Sun→Sun, Mon→Moon, Tue→Mars, Wed→Mercury, Thu→Jupiter, Fri→Venus, Sat→Saturn
    private const HORA_ORDER     = [0,5,2,6,4,1,3]; // Chaldean order going backwards for hora
    private const HORA_DAY_FIRST = [0,1,2,3,4,5,6]; // vara index → first hora lord
    private const HORA_PLANET_HI = ['सूर्य','चन्द्र','मंगल','बुध','गुरु','शुक्र','शनि'];
    private const HORA_QUALITY   = [
        0 => ['शुभ','Gold/Health'], // Sun hora — auspicious for government, authority
        1 => ['शुभ','Silver/Travel'], // Moon hora — good for travel, liquids
        2 => ['अशुभ','Iron/War'],    // Mars hora — avoid new starts
        3 => ['शुभ','Quicksilver/Trade'], // Mercury — good for business
        4 => ['अति शुभ','Tin/Religion'],  // Jupiter hora — best for auspicious work
        5 => ['शुभ','Copper/Arts'],   // Venus hora — arts, marriage, luxury
        6 => ['अशुभ','Lead/Hardship'], // Saturn hora — delays, obstacles
    ];

    // ── विवाह नक्षत्र (MC Vivah Prakarana) ──────────────────
    // उत्तम: रोहिणी, मृगशिरा, मघा, उत्तराफाल्गुनी, हस्त, स्वाति, अनुराधा, उत्तराषाढ़ा, उत्तराभाद्रपदा, रेवती
    private const VIVAH_NAK_UTTAM    = [3,4,9,11,12,14,16,20,25,26];
    // मध्यम: अश्विनी, पुनर्वसु, पुष्य, श्रवण, धनिष्ठा
    private const VIVAH_NAK_MADHYAM  = [0,6,7,21,22];
    // वर्जित: भरणी, कृत्तिका, आर्द्रा, आश्लेषा, पूर्वाफाल्गुनी, चित्रा, विशाखा, ज्येष्ठा,
    //         मूल (गण्डान्त), पूर्वाषाढ़ा, शतभिषा, पूर्वाभाद्रपदा
    private const VIVAH_NAK_VARJIT   = [1,2,5,8,10,13,15,17,18,19,23,24];

    // ── विवाह तिथि (MC) ──────────────────────────────────────
    // शुभ: शुक्ल 2,3,5,7,10,11,12 (0-based: 1,2,4,6,9,10,11)
    // त्रयोदशी rikta — varjit में है अतः uttam से हटाई
    private const VIVAH_TITHI_UTTAM  = [1,2,4,6,9,10,11];
    // वर्जित: प्रतिपदा(0),चतुर्थी(3),षष्ठी(5),अष्टमी(7),नवमी(8),त्रयोदशी(12),चतुर्दशी(13),पूर्णिमा/अमावास्या(14)
    private const VIVAH_TITHI_VARJIT = [0,3,5,7,8,12,13,14];
    // सम्पूर्ण कृष्ण पक्ष विवाह के लिए निषिद्ध (tithiIdx 15-29)

    // ── विवाह वार (MC) ───────────────────────────────────────
    private const VIVAH_VARA_UTTAM  = [1,3,4,5]; // Mon, Wed, Thu, Fri
    private const VIVAH_VARA_VARJIT = [2,6];     // Tue, Sat strictly forbidden

    // ── लट्टा दोष — नक्षत्र दूरी (MC) ─────────────────────
    // ग्रह के नक्षत्र से इतने नक्षत्र आगे = लट्टा नक्षत्र
    private const LATTA_OFFSET = [12, 22, 3, 4, 3, 7, 8];
    // Sun=12, Moon=22, Mars=3, Mercury=4, Jupiter=3, Venus=7, Saturn=8

    // ── गृहप्रवेश नक्षत्र (MC) ──────────────────────────────
    private const GRIHA_NAK_GOOD = [0,2,3,4,6,7,11,12,14,16,20,21,22,26];
    private const GRIHA_NAK_BAD  = [1,5,8,9,15,17,18,23,24];

    // ── वाहन/मुण्डन/सम्पत्ति नक्षत्र ───────────────────────
    private const VAHAN_NAK_GOOD  = [0,2,3,4,6,7,11,12,13,14,16,20,21,22,26];
    private const MUNDAN_NAK_GOOD = [0,3,4,6,7,11,12,14,16,20,21,26];
    private const SAMPATTI_NAK_GOOD = [3,6,7,11,12,14,16,20,21,25,26]; // property purchase

    // ── पञ्चक नक्षत्र (22-26) ────────────────────────────────
    private const PANCHAK_NAK = [22,23,24,25,26]; // धनिष्ठा..रेवती

    // ── योग शुभाशुभ ──────────────────────────────────────────
    private const YOGA_SHUBHA  = ['Priti','Ayushman','Saubhagya','Shobhana','Sukarma','Dhriti','Vriddhi','Dhruva','Harshana','Siddhi','Variyana','Shiva','Siddha','Sadhya','Shubha','Shukla','Brahma','Indra'];
    private const YOGA_ASHUBHA = ['Vishkambha','Atiganda','Shoola','Ganda','Vyaghata','Vajra','Vyatipata','Parigha','Vaidhriti'];

    // ── गौरी पञ्चाङ्ग (Tamil tradition) ─────────────────────
    // वार से — 8 periods: Amrit, Kaal, Shubh, Rog, Udveg, Char, Labh, (repeating)
    private const GOWRI_DAY = [
        0=>[3,4,5,0,6,1,2,3], 1=>[2,3,4,5,0,6,1,2],
        2=>[1,2,3,4,5,0,6,1], 3=>[0,1,2,3,4,5,0,6],
        4=>[5,0,6,1,2,3,4,5], 5=>[4,5,0,6,1,2,3,4],
        6=>[6,1,2,3,4,5,0,6],
    ];

    // ── हिन्दी नाम ────────────────────────────────────────────
    private const VARA_HI   = ['रविवार','सोमवार','मंगलवार','बुधवार','गुरुवार','शुक्रवार','शनिवार'];
    private const PAKSHA_HI = ['Shukla'=>'शुक्ल','Krishna'=>'कृष्ण'];
    private const RASHI_HI  = ['मेष','वृषभ','मिथुन','कर्क','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];
    private const GRAHA_HI  = ['सूर्य','चन्द्र','मंगल','बुध','गुरु','शुक्र','शनि'];
    private const GANA_HI   = ['देव','मनुष्य','राक्षस'];
    private const NADI_HI   = ['आदि','मध्य','अन्त्य'];
    private const VARNA_HI  = ['ब्राह्मण','क्षत्रिय','वैश्य','शूद्र'];
    private const YONI_HI   = ['अश्व','गज','मेष','सर्प','श्वान','मार्जार','मूषक','गो','महिष','व्याघ्र','मृग','वानर','नकुल','सिंह'];
    private const NAK_HI = [
        'अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशिरा','आर्द्रा','पुनर्वसु',
        'पुष्य','आश्लेषा','मघा','पूर्वाफाल्गुनी','उत्तराफाल्गुनी','हस्त',
        'चित्रा','स्वाति','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढ़ा',
        'उत्तराषाढ़ा','श्रवण','धनिष्ठा','शतभिषा','पूर्वाभाद्रपदा',
        'उत्तराभाद्रपदा','रेवती',
    ];
    private const TITHI_HI = [
        'प्रतिपदा','द्वितीया','तृतीया','चतुर्थी','पञ्चमी','षष्ठी','सप्तमी',
        'अष्टमी','नवमी','दशमी','एकादशी','द्वादशी','त्रयोदशी','चतुर्दशी',
        'पूर्णिमा/अमावास्या',
    ];
    private const SIGN_HI = ['मेष','वृषभ','मिथुन','कर्क','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];

    // ── २७ योगों के हिन्दी नाम ────────────────────────────────
    private const YOGA_HI = [
        'Vishkambha'=>'विष्कम्भ','Priti'=>'प्रीति','Ayushman'=>'आयुष्मान',
        'Saubhagya'=>'सौभाग्य','Shobhana'=>'शोभन','Atiganda'=>'अतिगण्ड',
        'Sukarma'=>'सुकर्मा','Dhriti'=>'धृति','Shoola'=>'शूल',
        'Ganda'=>'गण्ड','Vriddhi'=>'वृद्धि','Dhruva'=>'ध्रुव',
        'Vyaghata'=>'व्याघात','Harshana'=>'हर्षण','Vajra'=>'वज्र',
        'Siddhi'=>'सिद्धि','Vyatipata'=>'व्यतीपात','Variyana'=>'वरीयान',
        'Parigha'=>'परिघ','Shiva'=>'शिव','Siddha'=>'सिद्ध',
        'Sadhya'=>'साध्य','Shubha'=>'शुभ','Shukla'=>'शुक्ल',
        'Brahma'=>'ब्रह्म','Indra'=>'इन्द्र','Vaidhriti'=>'वैधृति',
    ];

    // ── ११ करणों के हिन्दी नाम ────────────────────────────────
    private const KARANA_HI = [
        'Bava'=>'बव','Balava'=>'बालव','Kaulava'=>'कौलव','Taitila'=>'तैतिल',
        'Garija'=>'गरज','Vanija'=>'वणिज','Vishti'=>'विष्टि (भद्रा)',
        'Shakuni'=>'शकुनि','Chatushpada'=>'चतुष्पाद','Naga'=>'नाग',
        'Kimstughna'=>'किंस्तुघ्न','Kinstughna'=>'किंस्तुघ्न',
    ];

    /** अंग्रेज़ी योग/करण → हिन्दी */
    public static function yogaHi(string $en): string  { return self::YOGA_HI[$en]   ?? $en; }
    public static function karanaHi(string $en): string{ return self::KARANA_HI[$en] ?? $en; }

    // ══════════════════════════════════════════════════════════
    //  मुख्य गणना — सम्पूर्ण दिन का मुहूर्त डेटा
    // ══════════════════════════════════════════════════════════

    /**
     * सम्पूर्ण मुहूर्त विश्लेषण — एक दिन के लिए सभी डेटा
     */
    public static function computeFullDay(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff,
        string $type = 'vivah',
        array $options = []
    ): array {
        // ── AstroCalculator से पञ्चाङ्ग ──────────────────────
        $ss     = AstroCalculator::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $setHr  = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;
        $midnightHr = $setHr + 6.0; // approximate next midnight ~18+6=24

        $jdRise = AstroCalculator::julianDay($yr, $mo, $dy, $riseHr - $utcOff);
        $ayan   = AstroCalculator::lahiriAyanamsa($jdRise);
        $tk     = AstroCalculator::computeTithiKarana($jdRise);
        $pancha = AstroCalculator::computePanchanga($jdRise, $ayan, $yr, $mo, $dy, $utcOff);

        // ── ग्रह स्थिति (विवाह के लिए ग्रह-अस्त जाँच) ───────
        $planets = self::getPlanetPositions($jdRise, $ayan);

        // ── लग्न, चन्द्र राशि, सूर्य राशि (AstroCalculator से) ─
        $angles     = AstroCalculator::computeAngles($jdRise, $lat, $lon);
        $lagnaSider = fmod(fmod($angles['asc'] - $ayan, 360) + 360, 360);
        $lagnaRashi = (int)floor($lagnaSider / 30);
        // चन्द्र और सूर्य राशि — planets array से (already computed)
        $moonRashiIdx = $planets['moon']['rashi'];  // floor(moonSider/30)
        $sunRashiIdx  = $planets['sun']['rashi'];   // floor(sunSider/30)
        $moonNakIdx   = $planets['moon']['nak'];    // floor(moonSider/(360/27))

        $varaIdx  = $pancha['varaIdx'];
        $nakIdx   = $moonNakIdx; // use exact nak from actual moon longitude
        $tithiIdx = $tk['tithiIndex'];
        $paksha   = $tk['tithi']['paksha'];

        // ── काल खण्ड ─────────────────────────────────────────
        $dayLen  = $setHr - $riseHr;
        $partLen = $dayLen / 8.0;
        $nightLen= 24.0 - $dayLen;
        $nightPartLen = $nightLen / 8.0;

        // राहुकाल / यमगण्ड / गुलिक
        $rahuS = $riseHr + (self::RAHU_PART[$varaIdx]   - 1) * $partLen;
        $rahuE = $rahuS  + $partLen;
        $yamaS = $riseHr + (self::YAMA_PART[$varaIdx]   - 1) * $partLen;
        $yamaE = $yamaS  + $partLen;
        $guliS = $riseHr + (self::GULIKA_PART[$varaIdx] - 1) * $partLen;
        $guliE = $guliS  + $partLen;

        $durmuhurta = [];
        foreach (self::DURMUHURTA_PART[$varaIdx] as $dSlot) {
            $s = $riseHr + ($dSlot - 1) * $partLen;
            $durmuhurta[] = ['s' => $s, 'e' => $s + $partLen, 'str' => self::hm($s).' – '.self::hm($s + $partLen)];
        }

        // अभिजित् — दिन का ठीक मध्य, ±24 मिनट
        $abhi  = ($riseHr + $setHr) / 2.0;
        $abhijitSlot = $varaIdx === 3 ? null : ['s'=>$abhi-0.4,'e'=>$abhi+0.4,'str'=>self::hm($abhi-0.4).' – '.self::hm($abhi+0.4)];

        // ब्रह्म मुहूर्त (48 mins before sunrise)
        $brahmaS = $riseHr - 0.8;
        $brahmaM = $riseHr - 0.4;
        $brahmaMuhurat = [
            ['s' => $brahmaS, 'e' => $brahmaM, 'str' => self::hm($brahmaS).' – '.self::hm($brahmaM)],
            ['s' => $brahmaM, 'e' => $riseHr, 'str' => self::hm($brahmaM).' – '.self::hm($riseHr)]
        ];

        // अमृत काल
        $amritNaks = [0,4,6,7,12,13,14,16,21,22,23,26];
        $amritKaal = null;
        if (in_array($nakIdx, $amritNaks)) {
            // Find start of current nakshatra (approx by using findNakshatraEnd on prev day)
            $prevNakEnd = AstroCalculator::findNakshatraEnd($jdRise - 1.5, $utcOff);
            $amritKaal = ['str' => $prevNakEnd, 'duration' => '1 hr 36 mins (approx)', 'active' => true];
        }

        // ── सम्पूर्ण डेटा बनाएं ──────────────────────────────
        return [
            'date'       => sprintf('%04d-%02d-%02d', $yr, $mo, $dy),
            'dateHi'     => self::dateHi($dy, $mo, $yr),
            'sunrise'    => AstroCalculator::decToHMS($riseHr),
            'sunset'     => AstroCalculator::decToHMS($setHr),
            'riseHr'     => $riseHr,
            'setHr'      => $setHr,
            'dayLen'     => $dayLen,
            'varaIdx'    => $varaIdx,
            'varaHi'     => self::VARA_HI[$varaIdx],
            'panchanga'  => self::buildPanchaData($pancha, $tk),
            'planets'    => $planets,
            'ayan'       => $ayan,
            'jdRise'     => $jdRise,
            'lat'        => $lat,
            'lon'        => $lon,
            // ── AstroCalculator से सटीक लग्न/राशि/नक्षत्र ──────
            'lagnaRashi'   => $lagnaRashi,
            'lagnaRashiHi' => self::RASHI_HI[$lagnaRashi],
            'moonRashiIdx' => $moonRashiIdx,
            'moonRashiHi'  => self::RASHI_HI[$moonRashiIdx],
            'sunRashiIdx'  => $sunRashiIdx,
            'sunRashiHi'   => self::RASHI_HI[$sunRashiIdx],
            'nakIdx'       => $moonNakIdx,
            'nakHi'        => self::NAK_HI[$moonNakIdx],

            // काल खण्ड
            'rahuKaal'   => ['s'=>$rahuS,'e'=>$rahuE,'str'=>self::hm($rahuS).' – '.self::hm($rahuE)],
            'yamaghanta' => ['s'=>$yamaS,'e'=>$yamaE,'str'=>self::hm($yamaS).' – '.self::hm($yamaE)],
            'gulikaKaal' => ['s'=>$guliS,'e'=>$guliE,'str'=>self::hm($guliS).' – '.self::hm($guliE)],
            'abhijit'    => $abhijitSlot,
            'brahmaMuhurat' => $brahmaMuhurat,
            'durmuhurta'    => $durmuhurta,
            'amritKaal'     => $amritKaal,

            // सभी श्रेणियों का डेटा
            'choghadiya' => self::computeChoghadiya($riseHr, $setHr, $varaIdx),
            'hora'       => self::computeHora($riseHr, $setHr, $varaIdx),
            'yoga'       => self::computeAuspiciousYogaList($pancha, $tk, $riseHr, $setHr),
            'panchak'    => self::computePanchak($nakIdx, $riseHr, $setHr),
            'gowri'      => self::computeGowriPanchangam($riseHr, $setHr, $varaIdx),
            'doGhati'    => self::computeDoGhati($abhi),
            'lagna'      => self::computeLagnaTable($yr, $mo, $dy, $riseHr, $setHr, $lat, $lon, $ayan, $utcOff),
            'vivah'      => self::computeVivahMC($pancha, $tk, $planets, $riseHr, $setHr, $varaIdx, $nakIdx, $tithiIdx, $paksha, $ayan, $jdRise, $lat, $lon, $options),
            'griha'      => self::computeGriha($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'vahana'     => self::computeVahana($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'mundan'     => self::computeMundan($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'sampatti'   => self::computeSampatti($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  १. चौघड़िया
    // ══════════════════════════════════════════════════════════

    public static function computeChoghadiya(float $rise, float $set, int $vara): array
    {
        $dayLen   = $set - $rise;
        $partLen  = $dayLen / 8.0;
        $nightLen = 24.0 - $dayLen;
        $nightPart= $nightLen / 8.0;

        $result = ['day'=>[], 'night'=>[]];

        foreach (self::CHOGHADIYA_DAY[$vara] as $i => $cIdx) {
            $s = $rise + $i * $partLen;
            $result['day'][] = [
                'name'   => self::CHOGHADIYA_NAMES[$cIdx],
                'nature' => self::CHOGHADIYA_NATURE[$cIdx],
                'planet' => self::CHOGHADIYA_PLANET[$cIdx],
                'color'  => self::CHOGHADIYA_COLOR[$cIdx],
                'start'  => self::hm($s),
                'end'    => self::hm($s + $partLen),
                'startHr'=> $s,
                'endHr'  => $s + $partLen,
                'shubh'  => in_array($cIdx, [2,3,5]), // लाभ, अमृत, शुभ
                'idx'    => $cIdx,
            ];
        }
        foreach (self::CHOGHADIYA_NIGHT[$vara] as $i => $cIdx) {
            $s = $set + $i * $nightPart;
            $result['night'][] = [
                'name'   => self::CHOGHADIYA_NAMES[$cIdx],
                'nature' => self::CHOGHADIYA_NATURE[$cIdx],
                'planet' => self::CHOGHADIYA_PLANET[$cIdx],
                'color'  => self::CHOGHADIYA_COLOR[$cIdx],
                'start'  => self::hm($s),
                'end'    => self::hm($s + $nightPart),
                'startHr'=> $s,
                'endHr'  => $s + $nightPart,
                'shubh'  => in_array($cIdx, [2,3,5]),
                'idx'    => $cIdx,
            ];
        }
        return $result;
    }

    // ══════════════════════════════════════════════════════════
    //  २. शुभ होरा
    // ══════════════════════════════════════════════════════════

    public static function computeHora(float $rise, float $set, int $vara): array
    {
        $totalDay = 24.0;
        $horaLen  = $totalDay / 24.0; // 1 hora = 1 hour exactly

        // Chaldean order: Sun, Venus, Mercury, Moon, Saturn, Jupiter, Mars
        // First hora of the day = lord of the day
        // Day lords: Sun(0)=Sun, Mon(1)=Moon, Tue(2)=Mars, Wed(3)=Mercury, Thu(4)=Jupiter, Fri(5)=Venus, Sat(6)=Saturn
        $firstLord = [0,1,2,3,4,5,6][$vara]; // index into Chaldean
        $chaldean  = [0,5,2,1,6,4,3]; // Sun, Venus, Mercury, Moon, Saturn, Jupiter, Mars

        // Find position of day lord in Chaldean
        $startPos = array_search($firstLord, $chaldean);

        $horas = [];
        for ($i = 0; $i < 24; $i++) {
            $lord    = $chaldean[($startPos + $i) % 7];
            $s       = $rise + $i * $horaLen;
            $isDay   = ($s >= $rise && $s < $set);
            [$qual, $desc] = self::HORA_QUALITY[$lord];
            $horas[] = [
                'hora'    => $i + 1,
                'lord'    => $lord,
                'lordHi'  => self::HORA_PLANET_HI[$lord],
                'start'   => self::hm($s),
                'end'     => self::hm($s + $horaLen),
                'startHr' => $s,
                'isDay'   => $isDay,
                'quality' => $qual,
                'desc'    => $desc,
                'shubh'   => in_array($lord, [1,3,4,5]), // Moon, Mercury, Jupiter, Venus
            ];
        }
        return $horas;
    }

    // ══════════════════════════════════════════════════════════
    //  ३. अभिजित् मुहूर्त
    // ══════════════════════════════════════════════════════════

    public static function computeDoGhati(float $abhi): array
    {
        // 1 Ghati = 24 minutes = 0.4 hours; Do Ghati = 2 Ghati = 48 min = 0.8 hrs
        return [
            'start'   => self::hm($abhi - 0.4),
            'end'     => self::hm($abhi + 0.4),
            'startHr' => $abhi - 0.4,
            'endHr'   => $abhi + 0.4,
            'duration'=> '४८ मिनट (२ घटी)',
            'note'    => 'MC: अभिजित् मुहूर्त सभी दोषों को नष्ट करता है। बुधवार को वर्जित।',
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  ४. शुभ योग सूची
    // ══════════════════════════════════════════════════════════

    public static function computeAuspiciousYogaList(array $pancha, array $tk, float $rise, float $set): array
    {
        $yogaName = $pancha['yoga']['n'];
        $isShubh  = in_array($yogaName, self::YOGA_SHUBHA);
        $isAshubh = in_array($yogaName, self::YOGA_ASHUBHA);

        $specialYogas = [];

        // सिद्धि योग = शुभ वार + शुभ नक्षत्र (MC specific)
        $shubhVara = in_array($pancha['varaIdx'], [1,3,4,5]);
        $shubhNak  = in_array($pancha['nakIdx'], [3,4,7,11,12,14,16,20,21,26]);
        if ($shubhVara && $shubhNak) {
            $specialYogas[] = ['name'=>'सिद्धि योग','nature'=>'अति शुभ','desc'=>'शुभ वार + शुभ नक्षत्र — सभी कार्य सफल।'];
        }

        // अमृत सिद्धि योग
        $amritCombos = [[1,3],[2,9],[3,7],[4,25],[5,11],[6,26],[0,0]]; // [vara, nak]
        foreach ($amritCombos as [$v,$n]) {
            if ($pancha['varaIdx']===$v && $pancha['nakIdx']===$n) {
                $specialYogas[] = ['name'=>'अमृत सिद्धि योग','nature'=>'अत्यन्त शुभ','desc'=>'MC: सभी शुभ कार्यों के लिए श्रेष्ठ।'];
                break;
            }
        }

        // सर्वार्थ सिद्धि योग — वार + नक्षत्र विशेष
        $sarvarth = [[1,3],[1,7],[3,7],[3,25],[4,7],[4,25],[5,7],[5,11],[5,26],[6,26]];
        foreach ($sarvarth as [$v,$n]) {
            if ($pancha['varaIdx']===$v && $pancha['nakIdx']===$n) {
                $specialYogas[] = ['name'=>'सर्वार्थ सिद्धि योग','nature'=>'अति शुभ','desc'=>'सभी सिद्धियाँ — किसी भी शुभ कार्य के लिए।'];
                break;
            }
        }

        // राज योग — शुक्ल पञ्चमी/दशमी/पूर्णिमा + गुरुवार
        if ($pancha['varaIdx']===4 && in_array($tk['tithi']['num'],[5,10,15]) && $tk['tithi']['paksha']==='Shukla') {
            $specialYogas[] = ['name'=>'राज योग','nature'=>'अति शुभ','desc'=>'गुरुवार + शुक्ल पञ्चमी/दशमी/पूर्णिमा।'];
        }

        // पञ्चाङ्ग शुद्धि — सभी 5 अंग शुभ हों
        $allGood = $shubhVara && $shubhNak && in_array($tk['tithi']['num'],[2,3,5,7,10,11,12,13]) && $isShubh && !($tk['karana']['n']==='Vishti');
        if ($allGood) {
            $specialYogas[] = ['name'=>'पञ्चाङ्ग शुद्धि','nature'=>'श्रेष्ठ','desc'=>'सभी पञ्च अंग शुभ — दुर्लभ संयोग।'];
        }

        return [
            'yogaName'   => $yogaName,
            'yogaHi'     => $pancha['yoga']['n'],
            'nature'     => $pancha['yoga']['nature'],
            'isShubh'    => $isShubh,
            'isAshubh'   => $isAshubh,
            'special'    => $specialYogas,
            'isBhadra'   => ($tk['karana']['n'] === 'Vishti'),
            'bhadraNote' => 'MC: भद्रा (विष्टि करण) में समस्त शुभ कार्य वर्जित।',
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  ५. पञ्चक
    // ══════════════════════════════════════════════════════════

    public static function computePanchak(int $nakIdx, float $rise, float $set): array
    {
        $isPanchak = in_array($nakIdx, self::PANCHAK_NAK);
        $nakHi     = self::NAK_HI[$nakIdx];
        return [
            'active'  => $isPanchak,
            'nakName' => $nakHi,
            'nakIdx'  => $nakIdx,
            'note'    => $isPanchak
                ? "MC: $nakHi — पञ्चक में। गृहप्रवेश, विवाह, यात्रा, ईंधन संग्रह, काष्ठ-कार्य, मृत-संस्कार वर्जित।"
                : "पञ्चक रहित — $nakHi नक्षत्र में पञ्चक नहीं।",
            'panchakNaks' => array_map(fn($i) => self::NAK_HI[$i], self::PANCHAK_NAK),
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  ६. लग्न तालिका
    // ══════════════════════════════════════════════════════════

    public static function computeLagnaTable(
        int $yr, int $mo, int $dy,
        float $rise, float $set,
        float $lat, float $lon, float $ayan, float $utcOff
    ): array {
        $lagnas = [];
        $step   = ($set - $rise) / 12.0; // approximate — each lagna ~2 hours
        // More accurate: compute ascendant at each hour
        for ($h = 0; $h <= 12; $h++) {
            $t = $rise + $h * (($set - $rise) / 12.0);
            if ($t > $set + 6) break;
            $jd = AstroCalculator::julianDay($yr, $mo, $dy, $t - $utcOff);
            $angles = AstroCalculator::computeAngles($jd, $lat, $lon);
            $sider  = fmod(fmod($angles['asc'] - $ayan, 360) + 360, 360);
            $signIdx= (int)floor($sider / 30);
            $deg    = fmod($sider, 30);
            $isFixed= in_array($signIdx, [1,4,7,10]); // Taurus,Leo,Scorpio,Aquarius
            $isDual = in_array($signIdx, [2,5,8,11]);
            $isMovable = !$isFixed && !$isDual;
            $lagnas[] = [
                'time'    => self::hm($t),
                'timeHr'  => $t,
                'signIdx' => $signIdx,
                'signHi'  => self::SIGN_HI[$signIdx],
                'deg'     => number_format($deg, 1).'°',
                'type'    => $isFixed ? 'स्थिर' : ($isDual ? 'द्विस्वभाव' : 'चर'),
                'shubh'   => $isFixed || $isDual, // Sthira/Dwisvabhava good for muhurta
                'note'    => $isFixed ? 'MC: स्थिर लग्न — गृहप्रवेश, विवाह हेतु उत्तम।' : ($isDual ? 'विवाह के लिए मध्यम।' : 'चर लग्न — यात्रा हेतु शुभ।'),
            ];
        }
        return $lagnas;
    }

    // ══════════════════════════════════════════════════════════
    //  ७. गौरी पञ्चाङ्ग
    // ══════════════════════════════════════════════════════════

    public static function computeGowriPanchangam(float $rise, float $set, int $vara): array
    {
        $dayLen  = $set - $rise;
        $partLen = $dayLen / 8.0;
        $periods = [];
        foreach (self::GOWRI_DAY[$vara] as $i => $cIdx) {
            $s = $rise + $i * $partLen;
            $periods[] = [
                'name'   => self::CHOGHADIYA_NAMES[$cIdx],
                'nature' => self::CHOGHADIYA_NATURE[$cIdx],
                'start'  => self::hm($s),
                'end'    => self::hm($s + $partLen),
                'shubh'  => in_array($cIdx, [2,3,5]),
                'color'  => self::CHOGHADIYA_COLOR[$cIdx],
            ];
        }
        return $periods;
    }

    // ══════════════════════════════════════════════════════════
    //  ८. विवाह मुहूर्त — MC Vivah Prakarana (सम्पूर्ण)
    // ══════════════════════════════════════════════════════════

    public static function computeVivahMC(
        array $pancha, array $tk, array $planets,
        float $rise, float $set, int $vara, int $nak, int $tithiIdx,
        string $paksha, float $ayan, float $jdRise,
        float $lat, float $lon, array $options = []
    ): array {
        $doshas  = [];
        $shubh   = [];
        $score   = 50;

        // ── १. वार (MC Vivah Prakarana) ──────────────────────
        $varaHi = self::VARA_HI[$vara];
        if (in_array($vara, self::VIVAH_VARA_UTTAM)) {
            $score += 15; $shubh[] = "$varaHi — MC: उत्तम विवाह वार।";
        } elseif (in_array($vara, self::VIVAH_VARA_VARJIT)) {
            $score -= 20; $doshas[] = "$varaHi — MC: विवाह के लिए कठोर वर्जित वार।";
        } elseif ($vara === 0) { // Sun — Madhyama, acceptable for Kshatriya
            $score += 3; $shubh[] = "$varaHi — MC: क्षत्रिय के लिए मध्यम।";
        }

        // ── २. तिथि (MC — शुक्ल पक्ष केवल) ──────────────────
        if ($paksha === 'Krishna') {
            $score -= 50; $doshas[] = 'कृष्ण पक्ष — MC: विवाह में सम्पूर्ण कृष्ण पक्ष वर्जित।';
        } elseif (in_array($tithiIdx, self::VIVAH_TITHI_UTTAM)) {
            $score += 12; $shubh[] = 'शुक्ल '.self::TITHI_HI[$tk['tithi']['num']-1].' — MC: उत्तम विवाह तिथि।';
        } elseif (in_array($tithiIdx, self::VIVAH_TITHI_VARJIT)) {
            $score -= 15; $doshas[] = self::TITHI_HI[$tk['tithi']['num']-1].' — MC: विवाह में वर्जित तिथि (रिक्ता/षष्ठी)।';
        }

        // ── ३. नक्षत्र (MC) ───────────────────────────────────
        $nakHi = self::NAK_HI[$nak];
        if (in_array($nak, self::VIVAH_NAK_UTTAM)) {
            $score += 20; $shubh[] = "$nakHi — MC: विवाह हेतु उत्तम नक्षत्र।";
        } elseif (in_array($nak, self::VIVAH_NAK_MADHYAM)) {
            $score += 8; $shubh[] = "$nakHi — MC: मध्यम नक्षत्र, अन्य शुभ योग हों तो ग्राह्य।";
        } elseif (in_array($nak, self::VIVAH_NAK_VARJIT)) {
            $score -= 20; $doshas[] = "$nakHi — MC: विवाह में वर्जित नक्षत्र।";
        }

        // ── ४. योग ────────────────────────────────────────────
        $yogaName = $pancha['yoga']['n'];
        if (in_array($yogaName, self::YOGA_ASHUBHA)) {
            $score -= 10; $doshas[] = "$yogaName योग — अशुभ योग, नया कार्य वर्जित।";
        } elseif (in_array($yogaName, self::YOGA_SHUBHA)) {
            $score += 10; $shubh[] = "$yogaName योग — शुभ एवं मंगलकारक।";
        }

        // ── ५. भद्रा (विष्टि करण) ────────────────────────────
        if ($tk['karana']['n'] === 'Vishti') {
            $score -= 10; $doshas[] = 'भद्रा (विष्टि करण) — MC: भद्रा में विवाह वर्जित।';
        }

        // ── ६. गुरु-अस्त (MC mathematical: < 11°) ────────────
        $guruAsta = self::checkGuruAsta($planets);
        if ($guruAsta['asta']) {
            $score -= 25; $doshas[] = "गुरु अस्त — MC: सूर्य से {$guruAsta['diff']}° — {$guruAsta['limit']}° से कम। विवाह वर्जित।";
        } else {
            $shubh[] = "गुरु अनस्त — सूर्य से {$guruAsta['diff']}° दूर। विवाह के लिए अनुकूल।";
        }

        // ── ७. शुक्र-अस्त (MC: < 10°) ────────────────────────
        $shukraAsta = self::checkShukraAsta($planets);
        if ($shukraAsta['asta']) {
            $score -= 25; $doshas[] = "शुक्र अस्त — MC: सूर्य से {$shukraAsta['diff']}° — {$shukraAsta['limit']}° से कम। विवाह वर्जित।";
        } else {
            $shubh[] = "शुक्र अनस्त — सूर्य से {$shukraAsta['diff']}° दूर।";
        }

        // ── ८. लट्टा दोष (MC — नक्षत्र-दूरी) ────────────────
        $lattaDoshas = self::checkLattaDosha($planets, $ayan, $nak);
        foreach ($lattaDoshas as $ld) {
            $score -= 8; $doshas[] = "लट्टा दोष — {$ld['graha']} का लट्टा नक्षत्र {$ld['latNak']} — {$ld['note']}";
        }

        // ── ९. पञ्चक ──────────────────────────────────────────
        if (in_array($nak, self::PANCHAK_NAK)) {
            $score -= 8; $doshas[] = self::NAK_HI[$nak].' — पञ्चक में। MC: विवाह में वर्जित।';
        }

        // ── १०. राशि-आधारित अष्टकूट मिलान ───────────────────
        $milan = null;
        $girlRashi = $options['girlRashiIdx'] ?? null;
        $boyRashi  = $options['boyRashiIdx']  ?? null;
        $girlNak   = $options['girlNakIdx']   ?? null;
        $boyNak    = $options['boyNakIdx']     ?? null;

        if ($girlRashi !== null && $boyRashi !== null) {
            $milan = self::ashtkootMilan((int)$girlRashi, (int)$boyRashi, $girlNak, $boyNak);
            // चन्द्रमा की सटीक राशि — planets array से (AstroCalculator)
            $moonNakRashi = isset($planets['moon']['rashi'])
                ? $planets['moon']['rashi']
                : (int)floor(fmod(fmod($planets['moon']['lon'] - $ayan, 360)+360, 360) / 30);
            $gTri = [(int)$girlRashi, ((int)$girlRashi+4)%12, ((int)$girlRashi+8)%12];
            $bTri = [(int)$boyRashi,  ((int)$boyRashi +4)%12, ((int)$boyRashi +8)%12];
            if (in_array($moonNakRashi, $gTri) || in_array($moonNakRashi, $bTri)) {
                $score += 8;
                $shubh[] = 'चन्द्रमा वर/कन्या की त्रिकोण राशि में — विशेष शुभ।';
            }
        }

        // ── ११. चन्द्रबल + तारबल (MC: सर्वाधिक महत्त्वपूर्ण) ─
        $chandrabala = null;
        if ($girlRashi !== null) {
            // चन्द्र राशि: AstroCalculator fetched — planets['moon']['rashi']
            $moonSignAcc = isset($planets['moon']['rashi'])
                ? $planets['moon']['rashi']
                : (int)floor(fmod(fmod($planets['moon']['lon'] - $ayan, 360)+360, 360) / 30);
            $chandrabala = self::getChandrabala((int)$girlRashi, $moonSignAcc);
            // Full bonus applied (not just -5 for ashubh)
            $score += $chandrabala['bonus']; // -10 to +8
            if (!$chandrabala['shubh']) {
                $doshas[] = 'चन्द्रबल अशुभ — '.$chandrabala['label'].'। MC: इस तिथि पर कन्या को चन्द्रबल प्रतिकूल।';
            } else {
                $shubh[]  = 'चन्द्रबल शुभ — '.$chandrabala['label'].'। MC: चन्द्रमा की स्थिति कन्या के लिए अनुकूल।';
            }
        }

        // ── १२. तारबल (कन्या के जन्म नक्षत्र से) ─────────────
        $tarabala = null;
        if ($girlNak !== null) {
            $tarabala = self::getTarabala((int)$girlNak, $nak);
            $score   += $tarabala['bonus']; // -10 to +10
            if (!$tarabala['shubh']) {
                $doshas[] = 'तारबल अशुभ — '.$tarabala['name'].'। MC: जन्म नक्षत्र से दूरी अशुभ।';
            } else {
                $shubh[]  = 'तारबल शुभ — '.$tarabala['name'].'।';
            }
        }

        // ── शुभ काल खण्ड (राहुकाल रहित) ─────────────────────
        $badPeriods = [
            [$rise + (self::RAHU_PART[$vara]-1)*($set-$rise)/8, $rise + self::RAHU_PART[$vara]*($set-$rise)/8],
            [$rise + (self::YAMA_PART[$vara]-1)*($set-$rise)/8, $rise + self::YAMA_PART[$vara]*($set-$rise)/8],
        ];
        $windows = self::getAuspiciousWindows($rise, $set, $badPeriods);

        $grade = self::grade(max(0, min(100, $score)));

        return [
            'score'       => max(0, min(100, $score)),
            'grade'       => $grade,
            'doshas'      => $doshas,
            'shubh'       => $shubh,
            'milan'       => $milan,
            'chandrabala' => $chandrabala,
            'tarabala'    => $tarabala ?? null,
            'windows'     => $windows,
            'guruAsta'    => $guruAsta,
            'shukraAsta'  => $shukraAsta,
            'lattaDoshas' => $lattaDoshas,
            'shastra'     => [
                'MC: उत्तराफाल्गुनी — सर्वश्रेष्ठ विवाह नक्षत्र, देवता अर्यमा।',
                'MC: शुक्ल पक्ष अनिवार्य। कृष्ण पक्ष में विवाह सर्वथा वर्जित।',
                'MC: गुरु (>११°) और शुक्र (>१०°) सूर्य से दूर हों — अन्यथा विवाह निषिद्ध।',
                'MC: भद्रा (विष्टि करण) में विवाह मुहूर्त नहीं।',
                'MC: अष्टकूट में न्यूनतम १८ गुण और नाड़ी-दोष रहित होना चाहिए।',
                'MC: लट्टा दोष का परिहार — किसी अन्य शुभ नक्षत्र का चयन करें।',
                'MC: मंगलवार और शनिवार विवाह के लिए कठोर वर्जित।',
                'MC: सिद्धि योग + अमृत सिद्धि योग = श्रेष्ठतम विवाह मुहूर्त।',
            ],
        ];
    }

    // ── MC गुरु-अस्त जाँच ──────────────────────────────────
    private static function checkGuruAsta(array $planets): array
    {
        $diff  = self::angDiff($planets['sun']['lon'], $planets['jupiter']['lon']);
        $limit = 11.0; // MC: 11° each side
        return ['asta'=>$diff < $limit, 'diff'=>round($diff,2), 'limit'=>$limit];
    }

    // ── MC शुक्र-अस्त जाँच ─────────────────────────────────
    private static function checkShukraAsta(array $planets): array
    {
        $diff  = self::angDiff($planets['sun']['lon'], $planets['venus']['lon']);
        $limit = 10.0; // MC: 10°
        return ['asta'=>$diff < $limit, 'diff'=>round($diff,2), 'limit'=>$limit];
    }

    // ── लट्टा दोष (MC mathematical) ────────────────────────
    private static function checkLattaDosha(array $planets, float $ayan, int $muhurtaNak): array
    {
        $offsets = ['sun'=>12,'moon'=>22,'mars'=>3,'mercury'=>4,'jupiter'=>3,'venus'=>7,'saturn'=>8];
        $doshas  = [];
        $naks    = AstroCalculator::getNakshatras();
        foreach ($offsets as $pid => $off) {
            if (!isset($planets[$pid])) continue;
            $sider  = fmod(fmod($planets[$pid]['lon'] - $ayan, 360) + 360, 360);
            $pNak   = (int)floor($sider / (360/27));
            $latNak = ($pNak + $off) % 27;
            if ($latNak === $muhurtaNak) {
                $doshas[] = [
                    'graha'  => self::GRAHA_HI[array_search($pid, ['sun','moon','mars','mercury','jupiter','venus','saturn'])],
                    'latNak' => self::NAK_HI[$latNak],
                    'note'   => "MC: {$pid} का लट्टा ({$off} नक्षत्र आगे) = ".self::NAK_HI[$latNak]." = मुहूर्त नक्षत्र।",
                ];
            }
        }
        return $doshas;
    }

    // ── कोणीय दूरी ──────────────────────────────────────────
    private static function angDiff(float $a, float $b): float
    {
        $d = abs($a - $b);
        return min($d, 360.0 - $d);
    }

    // ══════════════════════════════════════════════════════════
    //  ९. अष्टकूट मिलान (MC विवाह प्रकरण)
    // ══════════════════════════════════════════════════════════

    public static function ashtkootMilan(int $gR, int $bR, ?int $gN = null, ?int $bN = null): array
    {
        if ($gN === null) $gN = max(0, min(26, (int)round($gR * 27 / 12)));
        if ($bN === null) $bN = max(0, min(26, (int)round($bR * 27 / 12)));

        $total = 0; $koot = [];

        // १. वर्ण
        $gV = self::RASHI_VARNA[$gR]; $bV = self::RASHI_VARNA[$bR];
        $got = ($bV <= $gV) ? 1 : 0; $total += $got;
        $koot['varna'] = ['name'=>'वर्ण','max'=>1,'got'=>$got,'girl'=>self::VARNA_HI[$gV],'boy'=>self::VARNA_HI[$bV],
            'note'=>$got?'अनुकूल — वर्ण सामञ्जस्य।':'वर्ण दोष — वर का वर्ण कन्या से निम्न।','dosha'=>!$got];

        // २. वश्य
        $gVasya = self::RASHI_VASYA[$gR]; $bVasya = self::RASHI_VASYA[$bR];
        $vasya  = (in_array($bR,$gVasya)&&in_array($gR,$bVasya))?2:(in_array($bR,$gVasya)||in_array($gR,$bVasya)?1:0);
        $total += $vasya;
        $koot['vasya'] = ['name'=>'वश्य','max'=>2,'got'=>$vasya,'girl'=>self::RASHI_HI[$gR],'boy'=>self::RASHI_HI[$bR],
            'note'=>$vasya==2?'परस्पर वश्य।':($vasya==1?'एकपक्षीय।':'वश्य दोष।'),'dosha'=>$vasya===0];

        // ३. तारा
        $tDist = (($gN - $bN) + 27) % 27;
        $tNum  = ($tDist % 9) + 1;
        $tNames= ['','जन्म','संपत्','विपत्','क्षेम','प्रत्यरि','साधक','नैधन','मित्र','अतिमित्र'];
        $tGot  = in_array($tNum, self::TARA_SHUBHA) ? 3 : 0; $total += $tGot;
        $koot['tara'] = ['name'=>'तारा','max'=>3,'got'=>$tGot,'girl'=>self::NAK_HI[$gN],'boy'=>self::NAK_HI[$bN],
            'taraNam'=>$tNames[$tNum],'note'=>$tGot?"शुभ तारा — {$tNames[$tNum]}।":"अशुभ तारा — {$tNames[$tNum]}।",'dosha'=>!$tGot];

        // ४. योनि
        $gY = self::NAK_YONI[$gN]; $bY = self::NAK_YONI[$bN];
        $yGot = 4; $yNote = 'समान योनि।';
        if ($gY !== $bY) {
            $shatru = false;
            foreach (self::YONI_SHATRU as [$a,$b]) { if (($gY===$a&&$bY===$b)||($gY===$b&&$bY===$a)){$shatru=true;break;} }
            $yGot = $shatru ? 0 : 2; $yNote = $shatru ? 'योनि वैर दोष।' : 'योनि सम।';
        }
        $total += $yGot;
        $koot['yoni'] = ['name'=>'योनि','max'=>4,'got'=>$yGot,'girl'=>self::YONI_HI[$gY],'boy'=>self::YONI_HI[$bY],
            'note'=>$yNote,'dosha'=>$yGot===0];

        // ५. ग्रहमैत्री
        $gL = self::RASHI_LORD[$gR]; $bL = self::RASHI_LORD[$bR];
        $gM = self::GRAHA_MITRA[$gL]??[]; $bM = self::GRAHA_MITRA[$bL]??[];
        $gS = self::GRAHA_SHATRU[$gL]??[]; $bS = self::GRAHA_SHATRU[$bL]??[];
        $gB = in_array($bL,$gM)?'मित्र':(in_array($bL,$gS)?'शत्रु':'सम');
        $bB = in_array($gL,$bM)?'मित्र':(in_array($gL,$bS)?'शत्रु':'सम');
        $mGot = match([$gB,$bB]){['मित्र','मित्र']=>5,['मित्र','सम'],['सम','मित्र'],['सम','सम']=>3,['मित्र','शत्रु'],['शत्रु','मित्र']=>1,default=>0};
        $total += $mGot;
        $koot['maitri'] = ['name'=>'ग्रहमैत्री','max'=>5,'got'=>$mGot,'girl'=>self::GRAHA_HI[$gL].' ('.self::RASHI_HI[$gR].')','boy'=>self::GRAHA_HI[$bL].' ('.self::RASHI_HI[$bR].')',
            'gBuddhi'=>$gB,'bBuddhi'=>$bB,'note'=>$mGot>=4?'परस्पर मित्रता।':($mGot>=2?'सम-भाव।':'ग्रह-शत्रुता।'),'dosha'=>$mGot===0];

        // ६. गण
        $gG = self::NAK_GANA[$gN]; $bG = self::NAK_GANA[$bN];
        $gGot = match(true){$gG===$bG=>6,$bG===0&&$gG===1=>5,default=>0};
        $total += $gGot;
        $koot['gana'] = ['name'=>'गण','max'=>6,'got'=>$gGot,'girl'=>self::GANA_HI[$gG],'boy'=>self::GANA_HI[$bG],
            'note'=>$gGot===6?'समान गण।':($gGot>=4?'अनुकूल।':'गण दोष।'),'dosha'=>$gGot===0];

        // ७. भकूट
        $bDist = (($gR-$bR)+12)%12; $bDistR = (($bR-$gR)+12)%12;
        $bDosha= in_array($bDist,self::BHAKOOT_DOSHA)||in_array($bDistR,self::BHAKOOT_DOSHA);
        $bhGot = $bDosha?0:7; $total += $bhGot;
        $dType = '';
        if($bDosha){if(in_array($bDist,[6,8])||in_array($bDistR,[6,8]))$dType='षडष्टक';elseif(in_array($bDist,[5,9])||in_array($bDistR,[5,9]))$dType='पञ्चनवम';else$dType='द्विद्वादश';}
        $koot['bhakoot'] = ['name'=>'भकूट','max'=>7,'got'=>$bhGot,'girl'=>self::RASHI_HI[$gR],'boy'=>self::RASHI_HI[$bR],
            'note'=>$bhGot?'भकूट शुभ।':"भकूट दोष ($dType)।",'dosha'=>$bDosha,'doshaType'=>$dType];

        // ८. नाड़ी
        $gN2 = self::NAK_NADI[$gN]; $bN2 = self::NAK_NADI[$bN];
        $nGot = ($gN2!==$bN2)?8:0; $total += $nGot;
        $koot['nadi'] = ['name'=>'नाड़ी','max'=>8,'got'=>$nGot,'girl'=>self::NADI_HI[$gN2],'boy'=>self::NADI_HI[$bN2],
            'note'=>$nGot?'भिन्न नाड़ी — उत्तम।':'नाड़ी दोष — MC: अत्यन्त गम्भीर।','dosha'=>!$nGot];

        $rating = match(true){$total>=32=>['hi'=>'अति उत्तम','color'=>'#1a6a2a'],$total>=27=>['hi'=>'उत्तम','color'=>'#2d7a3a'],$total>=21=>['hi'=>'मध्यम','color'=>'#9a6b0a'],$total>=18=>['hi'=>'स्वीकार्य','color'=>'#b87a20'],default=>['hi'=>'अनुकूल नहीं','color'=>'#c0302a']};
        $mahadosha = [];
        if(!$koot['nadi']['got'])$mahadosha[]='नाड़ी दोष (अत्यन्त गम्भीर)';
        if(!$koot['bhakoot']['got'])$mahadosha[]="भकूट दोष ($dType)";
        if(!$koot['gana']['got'])$mahadosha[]='गण दोष';
        if(!$koot['yoni']['got'])$mahadosha[]='योनि वैर';

        return ['koot'=>$koot,'total'=>$total,'max'=>36,'rating'=>$rating,'mahadosha'=>$mahadosha,
            'girRashi'=>self::RASHI_HI[$gR],'boyRashi'=>self::RASHI_HI[$bR],'girNak'=>self::NAK_HI[$gN],'boyNak'=>self::NAK_HI[$bN]];
    }

    // ══════════════════════════════════════════════════════════
    //  १०-१४. गृहप्रवेश / वाहन / मुण्डन / सम्पत्ति
    // ══════════════════════════════════════════════════════════

    private static function computeSamskar(string $type, int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $goodVara = [1,3,4,5]; $badVara = ($type==='vahana'||$type==='sampatti') ? [2,6] : [0,2,6];
        $goodNak  = match($type){'griha'=>self::GRIHA_NAK_GOOD,'mundan'=>self::MUNDAN_NAK_GOOD,'sampatti'=>self::SAMPATTI_NAK_GOOD,default=>self::VAHAN_NAK_GOOD};
        $badNak   = [1,5,8,9,17,18];
        $goodTithi= [1,2,4,5,6,9,10,11,12,16,17,19,21,24,25,27];
        $badTithi = [3,7,13,14,28,29];

        $score = 50; $doshas = []; $shubh = [];

        if(in_array($vara,$goodVara)){$score+=15;$shubh[]=self::VARA_HI[$vara].' — शुभ वार।';}
        elseif(in_array($vara,$badVara)){$score-=15;$doshas[]=self::VARA_HI[$vara].' — वार दोष।';}

        if(in_array($nak,$goodNak)){$score+=20;$shubh[]=self::NAK_HI[$nak].' — शुभ नक्षत्र।';}
        elseif(in_array($nak,$badNak)){$score-=20;$doshas[]=self::NAK_HI[$nak].' — नक्षत्र दोष।';}

        if(in_array($tithiIdx,$goodTithi)){$score+=15;$shubh[]=self::PAKSHA_HI[$paksha].' '.self::TITHI_HI[min($tk['tithi']['num']-1,14)].' — शुभ तिथि।';}
        elseif(in_array($tithiIdx,$badTithi)){$score-=15;$doshas[]=self::TITHI_HI[min($tk['tithi']['num']-1,14)].' — तिथि दोष।';}

        if($paksha==='Shukla'){$score+=5;$shubh[]='शुक्ल पक्ष — चन्द्रवृद्धि शुभ।';}
        else{$score-=5;}

        $yogaName=$pancha['yoga']['n'];
        if(in_array($yogaName,self::YOGA_ASHUBHA)){$score-=10;$doshas[]="$yogaName योग दोष।";}
        elseif(in_array($yogaName,self::YOGA_SHUBHA)){$score+=10;$shubh[]="$yogaName योग — शुभ।";}

        if($tk['karana']['n']==='Vishti'){$score-=8;$doshas[]='भद्रा दोष।';}

        return ['score'=>max(0,min(100,$score)),'grade'=>self::grade(max(0,min(100,$score))),'doshas'=>$doshas,'shubh'=>$shubh];
    }

    public static function computeGriha(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('griha',$vara,$nak,$tithiIdx,$paksha,$pancha,$tk);
        $r['shastra'] = ['MC: रोहिणी, पुनर्वसु, पुष्य, उत्तराफाल्गुनी, हस्त, श्रवण, रेवती — उत्तम।','MC: शुक्ल पक्ष, स्थिर लग्न अनिवार्य।','MC: अधिकमास में गृहप्रवेश वर्जित।','उत्तर या पूर्व से प्रवेश, दाहिना पैर पहले।'];
        return $r;
    }
    public static function computeVahana(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('vahana',$vara,$nak,$tithiIdx,$paksha,$pancha,$tk);
        $r['shastra'] = ['MC: अश्विनी, रोहिणी, पुनर्वसु, हस्त — सर्वश्रेष्ठ।','दशमी तिथि + विजय योग — विशेष शुभ।','राहुकाल में वाहन-क्रय वर्जित।'];
        return $r;
    }
    public static function computeMundan(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('mundan',$vara,$nak,$tithiIdx,$paksha,$pancha,$tk);
        $r['shastra'] = ['MC: प्रथम, तृतीय या पञ्चम वर्ष में करें।','ज्येष्ठा, मूल, आश्लेषा — वर्जित।','जन्ममास में मुण्डन न करें।','अभिजित् मुहूर्त — सदैव शुभ।'];
        return $r;
    }
    public static function computeSampatti(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('sampatti',$vara,$nak,$tithiIdx,$paksha,$pancha,$tk);
        $r['shastra'] = ['MC: रोहिणी, पुनर्वसु, पुष्य, अनुराधा, श्रवण — उत्तम।','गुरुवार या शुक्रवार को क्रय — शुभ।','शुक्ल प्रतिपदा से षष्ठी तक — ग्राह्य नहीं।','बुध, गुरु, शुक्र होरा में हस्ताक्षर।'];
        return $r;
    }

    // ══════════════════════════════════════════════════════════
    //  माह स्कैन (Shubha Dates)
    // ══════════════════════════════════════════════════════════

    // ══════════════════════════════════════════════════════════
    //  चन्द्रबल — रात्रि/दिन के अनुसार (राशि से चन्द्र की स्थिति)
    //  MC: 1,2,3,4,6,7,10,11 = शुभ; 5,8,9,12 = अशुभ (8वाँ = अत्यन्त अशुभ)
    // ══════════════════════════════════════════════════════════
    public static function getChandrabala(int $birthRashi, int $moonRashi): array
    {
        $dist = (($moonRashi - $birthRashi) + 12) % 12 + 1; // 1..12
        // MC Chandrabala: 2,4,6,8,9,10,11 = शुभ; 1=सामान्य(neutral); 3,5,7,12=अशुभ
        $shubh = in_array($dist, [2,4,6,8,9,10,11]);
        $label = match($dist) {
            1  => 'जन्म (सामान्य)',       2  => 'सम्पत् (शुभ)',
            3  => 'विपत् (अशुभ)',          4  => 'क्षेम (शुभ)',
            5  => 'प्रत्यरि (अशुभ)',       6  => 'साधक (शुभ)',
            7  => 'नैधन (अशुभ)',            8  => 'मित्र (शुभ)',
            9  => 'अतिमित्र (शुभ)',        10 => 'सम्पत्+९ (शुभ)',
            11 => 'क्षेम+९ (शुभ)',          12 => 'विपत्+९ (अशुभ)',
            default => '?',
        };
        $bonus = match($dist) {
            1=>0, 2=>8, 3=>-8, 4=>6, 5=>-10, 6=>6, 7=>-8, 8=>8, 9=>8, 10=>6, 11=>4, 12=>-6, default=>0
        };
        return ['dist'=>$dist,'shubh'=>$shubh,'label'=>$label,'bonus'=>$bonus];
    }

    // ══════════════════════════════════════════════════════════
    //  तारबल — जन्म नक्षत्र से मुहूर्त नक्षत्र की दूरी
    //  तारा 1=जन्म(अशुभ) 2=सम्पत् 3=विपत् 4=क्षेम 5=प्रत्यरि 6=साधक 7=नैधन 8=मित्र 9=अतिमित्र
    // ══════════════════════════════════════════════════════════
    public static function getTarabala(int $birthNak, int $muhurtaNak): array
    {
        $dist  = (($muhurtaNak - $birthNak) + 27) % 27;
        $taraNum = ($dist % 9) + 1; // 1..9
        $names = ['','जन्म','सम्पत्','विपत्','क्षेम','प्रत्यरि','साधक','नैधन','मित्र','अतिमित्र'];
        $shubh = in_array($taraNum, [2,4,6,8,9]);
        $bonus = match($taraNum){ 1=>-2, 2=>8, 3=>-8, 4=>6, 5=>-10, 6=>6, 7=>-8, 8=>8, 9=>10, default=>0 };
        return ['taraNum'=>$taraNum,'name'=>$names[$taraNum],'shubh'=>$shubh,'bonus'=>$bonus];
    }

    // ══════════════════════════════════════════════════════════
    //  माह स्कैन — पूर्णतः राशि-विशिष्ट गणना
    //  प्रत्येक दिन के लिए: चन्द्रबल + तारबल + लट्टा + गुरु/शुक्र अस्त
    //  सभी गणनाएँ Jean Meeus algorithms पर आधारित (AstroCalculator से)
    // ══════════════════════════════════════════════════════════
    public static function scanMonth(
        int $yr, int $mo, float $lat, float $lon, float $utcOff,
        string $type = 'vivah', array $options = []
    ): array {
        $days       = cal_days_in_month(CAL_GREGORIAN, $mo, $yr);
        $girlRashi  = isset($options['girlRashiIdx']) ? (int)$options['girlRashiIdx'] : null;
        $boyRashi   = isset($options['boyRashiIdx'])  ? (int)$options['boyRashiIdx']  : null;
        $girlNak    = isset($options['girlNakIdx'])   ? (int)$options['girlNakIdx']   : null;
        $boyNak     = isset($options['boyNakIdx'])    ? (int)$options['boyNakIdx']    : null;
        $minScore   = (int)($options['minScore'] ?? 40);
        $results    = [];

        for ($d = 1; $d <= $days; $d++) {
            // ── पञ्चाङ्ग गणना (AstroCalculator — Jean Meeus) ──
            $ss     = AstroCalculator::sunriseSunset($yr, $mo, $d, $lat, $lon, $utcOff);
            $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
            $setHr  = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;
            $jdRise = AstroCalculator::julianDay($yr, $mo, $d, $riseHr - $utcOff);
            $ayan   = AstroCalculator::lahiriAyanamsa($jdRise);
            $tk     = AstroCalculator::computeTithiKarana($jdRise);
            $pancha = AstroCalculator::computePanchanga($jdRise, $ayan, $yr, $mo, $d, $utcOff);

            $varaIdx  = $pancha['varaIdx'];
            $tithiIdx = $tk['tithiIndex'];
            $paksha   = $tk['tithi']['paksha'];
            $tithiNum = $tk['tithi']['num'];

            // ── सूर्य + चन्द्र राशि — AstroCalculator से (Jean Meeus) ─
            $sunLon   = AstroCalculator::sunLongitude($jdRise);
            $monLon   = AstroCalculator::moonLongitude($jdRise);
            $sunSider = fmod(fmod($sunLon - $ayan, 360) + 360, 360);
            $monSider = fmod(fmod($monLon - $ayan, 360) + 360, 360);
            // Actual rashi from longitude (NOT from nakshatra lookup — more accurate)
            $sunRashi  = (int)floor($sunSider / 30);
            $moonRashi = (int)floor($monSider / 30);
            // Nakshatra from actual moon sidereal longitude
            $nakIdx    = (int)floor($monSider / (360.0 / 27.0));

            // ग्रह स्थिति (Guru/Shukra asta + Latta दोष के लिए)
            $jupLon = AstroCalculator::planetLongitude($jdRise, 'jupiter');
            $venLon = AstroCalculator::planetLongitude($jdRise, 'venus');
            $marLon = AstroCalculator::planetLongitude($jdRise, 'mars');
            $merLon = AstroCalculator::planetLongitude($jdRise, 'mercury');
            $satLon = AstroCalculator::planetLongitude($jdRise, 'saturn');
            // $monLon already computed above for moon rashi

            // ── मूल स्कोर (सभी के लिए समान) ────────────────
            $score  = 50;
            $doshas = [];
            $shubh  = [];
            $flags  = []; // quick flags for display

            // वार
            if ($type === 'vivah') {
                if (in_array($varaIdx, self::VIVAH_VARA_UTTAM))      { $score += 15; $shubh[] = self::VARA_HI[$varaIdx].' शुभ।'; }
                elseif (in_array($varaIdx, self::VIVAH_VARA_VARJIT)) { $score -= 20; $doshas[] = self::VARA_HI[$varaIdx].' वर्जित।'; }
            } else {
                if (in_array($varaIdx, [1,3,4,5]))  { $score += 15; $shubh[] = self::VARA_HI[$varaIdx].' शुभ।'; }
                elseif (in_array($varaIdx, [0,2,6])){ $score -= 15; $doshas[] = self::VARA_HI[$varaIdx].' दोष।'; }
            }

            // ══ MC विवाह: कृष्ण पक्ष सम्पूर्णतः वर्जित ═══════
            if ($type === 'vivah' && $paksha === 'Krishna') {
                $score -= 50; // Score कभी भी 40+ नहीं जा सकता
                $doshas[] = 'कृष्ण पक्ष — MC: विवाह सम्पूर्णतः वर्जित।';
                $flags['krishna_paksha'] = true;
            }

            // ══ MC विवाह: मंगल/शनिवार सम्पूर्णतः वर्जित ══════
            if ($type === 'vivah' && in_array($varaIdx, self::VIVAH_VARA_VARJIT)) {
                $score -= 30; // Additional penalty to hard-exclude
            }

            // पक्ष बोनस
            if ($paksha === 'Shukla')    { $score += 5; if($type==='vivah') $shubh[] = 'शुक्ल पक्ष।'; }
            elseif ($paksha === 'Krishna'){ $score -= 3; }

            // तिथि
            if ($type === 'vivah') {
                if ($paksha === 'Shukla' && in_array($tithiIdx, self::VIVAH_TITHI_UTTAM))  { $score += 12; $shubh[] = self::NAK_HI[$nakIdx].' तिथि उत्तम।'; }
                elseif (in_array($tithiIdx, self::VIVAH_TITHI_VARJIT))                      { $score -= 15; $doshas[] = 'रिक्ता/अशुभ तिथि।'; }
            } else {
                if (in_array($tithiIdx, [1,2,4,5,6,9,10,11,12,16,17,19,21,24,25,27]))      { $score += 12; }
                elseif (in_array($tithiIdx, [3,7,13,14,28,29]))                             { $score -= 12; $doshas[] = 'अशुभ तिथि।'; }
            }

            // नक्षत्र
            if ($type === 'vivah') {
                if (in_array($nakIdx, self::VIVAH_NAK_UTTAM))    { $score += 20; $flags['nak_uttam'] = true; }
                elseif (in_array($nakIdx, self::VIVAH_NAK_MADHYAM)){ $score += 8; $flags['nak_madhyam'] = true; }
                elseif (in_array($nakIdx, self::VIVAH_NAK_VARJIT)){ $score -= 20; $doshas[] = self::NAK_HI[$nakIdx].' नक्षत्र वर्जित।'; $flags['nak_varjit'] = true; }
            } else {
                $goodNak = match($type){ 'griha_pravesh'=>self::GRIHA_NAK_GOOD, 'mundan'=>self::MUNDAN_NAK_GOOD, 'sampatti'=>self::SAMPATTI_NAK_GOOD, default=>self::VAHAN_NAK_GOOD };
                if (in_array($nakIdx, $goodNak))             { $score += 18; }
                elseif (in_array($nakIdx, [1,5,8,9,17,18])) { $score -= 15; $doshas[] = self::NAK_HI[$nakIdx].' दोष।'; }
            }

            // योग
            $yogaName = $pancha['yoga']['n'];
            if (in_array($yogaName, self::YOGA_ASHUBHA)) { $score -= 10; $doshas[] = $yogaName.' योग दोष।'; }
            elseif (in_array($yogaName, self::YOGA_SHUBHA)) { $score += 8; }

            // भद्रा (विष्टि करण)
            $isBhadra = ($tk['karana']['n'] === 'Vishti');
            if ($isBhadra) { $score -= 8; $doshas[] = 'भद्रा दोष।'; $flags['bhadra'] = true; }

            // पञ्चक
            $isPanchak = in_array($nakIdx, self::PANCHAK_NAK);
            if ($isPanchak && in_array($type, ['vivah','griha_pravesh'])) { $score -= 6; $doshas[] = 'पञ्चक।'; $flags['panchak'] = true; }

            // ── गुरु/शुक्र अस्त (MC mathematical) ────────────
            $guruDiff  = self::angDiff($sunLon, $jupLon);
            $shukraDiff= self::angDiff($sunLon, $venLon);
            $guruAsta  = ($guruDiff < 11.0);
            $shukraAsta= ($shukraDiff < 10.0);
            if ($guruAsta && $type === 'vivah')   { $score -= 25; $doshas[] = 'गुरु अस्त ('.round($guruDiff,1).'°)।'; $flags['guru_asta'] = true; }
            if ($shukraAsta && $type === 'vivah') { $score -= 25; $doshas[] = 'शुक्र अस्त ('.round($shukraDiff,1).'°)।'; $flags['shukra_asta'] = true; }

            // ── लट्टा दोष ─────────────────────────────────────
            $lattaOffsets = ['sun'=>12,'moon'=>22,'mars'=>3,'mercury'=>4,'jupiter'=>3,'venus'=>7,'saturn'=>8];
            $allLons = ['sun'=>$sunLon,'moon'=>$monLon,'mars'=>$marLon,'mercury'=>$merLon,'jupiter'=>$jupLon,'venus'=>$venLon,'saturn'=>$satLon];
            $lattaDoshas = [];
            foreach ($lattaOffsets as $pid => $off) {
                $sider = fmod(fmod($allLons[$pid] - $ayan, 360) + 360, 360);
                $pNak  = (int)floor($sider / (360/27));
                $latNak= ($pNak + $off) % 27;
                if ($latNak === $nakIdx) {
                    $lattaDoshas[] = self::GRAHA_HI[array_search($pid, ['sun','moon','mars','mercury','jupiter','venus','saturn'])].' लट्टा';
                    $score -= 8;
                }
            }
            if ($lattaDoshas) { $doshas[] = implode(', ', $lattaDoshas).' दोष।'; $flags['latta'] = true; }

            // ══════════════════════════════════════════════════
            //  राशि-विशिष्ट गणना (RASHI-SPECIFIC — DYNAMIC)
            //  यही मुख्य अन्तर है — प्रत्येक राशि के लिए अलग
            // ══════════════════════════════════════════════════

            // चन्द्रबल (कन्या की राशि से) — MC: सर्वाधिक महत्त्वपूर्ण
            $chandrabala = null;
            if ($girlRashi !== null) {
                $cb = self::getChandrabala($girlRashi, $moonRashi);
                $chandrabala = $cb;
                $score += $cb['bonus'];
                if (!$cb['shubh']) { $doshas[] = 'चन्द्रबल अशुभ ('.$cb['label'].')।'; $flags['chandrabala_bad'] = true; }
                else               { $shubh[]  = 'चन्द्रबल शुभ ('.$cb['label'].')।'; $flags['chandrabala_good'] = true; }
            }

            // तारबल (कन्या के जन्म नक्षत्र से)
            $tarabala = null;
            if ($girlNak !== null) {
                $tb = self::getTarabala($girlNak, $nakIdx);
                $tarabala = $tb;
                $score += $tb['bonus'];
                if (!$tb['shubh']) { $doshas[] = 'तारबल अशुभ ('.$tb['name'].')।'; $flags['tara_bad'] = true; }
                else               { $shubh[]  = 'तारबल शुभ ('.$tb['name'].')।'; }
            }

            // वर चन्द्रबल (वर की राशि से)
            if ($boyRashi !== null) {
                $bcb = self::getChandrabala($boyRashi, $moonRashi);
                $score += (int)($bcb['bonus'] * 0.5); // वर का वजन कम
                if (!$bcb['shubh']) { $doshas[] = 'वर चन्द्रबल अशुभ।'; }
            }

            // वर तारबल
            if ($boyNak !== null) {
                $btb = self::getTarabala($boyNak, $nakIdx);
                $score += (int)($btb['bonus'] * 0.5);
            }

            // अष्टकूट मिलान (यदि दोनों राशियाँ उपलब्ध)
            $milan = null;
            if ($girlRashi !== null && $boyRashi !== null && $type === 'vivah') {
                $milan = self::ashtkootMilan($girlRashi, $boyRashi, $girlNak, $boyNak);
                // मिलान स्कोर बोनस
                $milanPct = $milan['total'] / 36.0;
                $score += (int)(($milanPct - 0.5) * 20); // -10..+10
                if (!empty($milan['mahadosha'])) { $doshas[] = implode(' | ', $milan['mahadosha']).'।'; }
            }

            // ── राहुकाल में अभिजित् भी नहीं ─────────────────
            $dayLen  = $setHr - $riseHr;
            $partLen = $dayLen / 8.0;
            $rahuS   = $riseHr + (self::RAHU_PART[$varaIdx]-1)*$partLen;
            $rahuE   = $rahuS + $partLen;
            $abhi    = ($riseHr + $setHr) / 2.0;
            $abhiInRahu = ($abhi > $rahuS && $abhi < $rahuE);

            // शुभ चौघड़िया (display के लिए)
            $choData = self::computeChoghadiya($riseHr, $setHr, $varaIdx);
            $shubhCho = array_filter($choData['day'], fn($c) => $c['shubh']);

            $finalScore = max(0, min(100, $score));
            $grade      = self::grade($finalScore);

            $results[] = [
                'day'         => $d,
                'dateStr'     => sprintf('%02d/%02d/%04d', $d, $mo, $yr),
                'isoDate'     => sprintf('%04d-%02d-%02d', $yr, $mo, $d),
                'score'       => $finalScore,
                'grade'       => $grade,
                'varaIdx'     => $varaIdx,
                'varaHi'      => self::VARA_HI[$varaIdx],
                'paksha'      => $paksha,
                'pakshaHi'    => self::PAKSHA_HI[$paksha],
                'tithiNum'    => $tithiNum,
                'tithiHi'     => self::TITHI_HI[min($tithiNum-1,14)],
                'tithiIdx'    => $tithiIdx,
                'nakIdx'      => $nakIdx,
                'nakHi'       => self::NAK_HI[$nakIdx],
                'moonRashi'   => $moonRashi,
                'moonRashiHi' => self::RASHI_HI[$moonRashi],
                'sunRashi'    => $sunRashi,
                'sunRashiHi'  => self::RASHI_HI[$sunRashi],
                'yogaHi'      => self::yogaHi($yogaName),
                'karanaHi'    => self::karanaHi($tk['karana']['n']),
                'sunrise'     => AstroCalculator::decToHMS($riseHr),
                'sunset'      => AstroCalculator::decToHMS($setHr),
                'doshas'      => $doshas,
                'shubh'       => $shubh,
                'flags'       => $flags,
                'chandrabala' => $chandrabala,
                'tarabala'    => $tarabala,
                'milan'       => $milan,
                'guruAsta'    => $guruAsta,
                'shukraAsta'  => $shukraAsta,
                'lattaDoshas' => $lattaDoshas,
                'isBhadra'    => $isBhadra,
                'isPanchak'   => $isPanchak,
                'shubhCho'    => array_values($shubhCho),
                'abhi'        => self::hm($abhi-0.4).'–'.self::hm($abhi+0.4),
                'rahuStr'     => self::hm($rahuS).'–'.self::hm($rahuE),
            ];
        }

        // दिनाँक क्रम (NOT score order — पुस्तक की तरह कालक्रम में)
        usort($results, fn($a,$b) => $a['day'] - $b['day']);

        // minScore filter (all data returned — JS filters)
        return array_values(array_filter($results, fn($r) => $r['score'] >= $minScore));
    }

    // ══════════════════════════════════════════════════════════
    //  वर्ष स्कैन — सम्पूर्ण वर्ष (all 12 months at once, server-side)
    // ══════════════════════════════════════════════════════════
    public static function scanYear(
        int $yr, float $lat, float $lon, float $utcOff,
        string $type = 'vivah', array $options = []
    ): array {
        $allMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            $allMonths[$m] = self::scanMonth($yr, $m, $lat, $lon, $utcOff, $type, $options);
        }
        return $allMonths;
    }

    // ══════════════════════════════════════════════════════════
    //  Controller compatibility wrappers
    // ══════════════════════════════════════════════════════════

    public static function calculate(int $yr, int $mo, int $dy, float $lat, float $lon, float $utcOff, string $type, array $options=[]): array
    {
        return self::computeFullDay($yr, $mo, $dy, $lat, $lon, $utcOff, $type, $options);
    }

    public static function renderHtml(array $data, string $type): string
    {
        return self::buildResultHtml($data, $type);
    }

    public static function renderMonthHtml(array $dates, string $type): string
    {
        return self::buildMonthHtml($dates, $type);
    }

    // ══════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════

    private static function getPlanetPositions(float $jd, float $ayan): array
    {
        // All longitudes from AstroCalculator (Jean Meeus) — tropical
        $sunLon  = AstroCalculator::sunLongitude($jd);
        $monLon  = AstroCalculator::moonLongitude($jd);
        $jupLon  = AstroCalculator::planetLongitude($jd, 'jupiter');
        $venLon  = AstroCalculator::planetLongitude($jd, 'venus');
        $marLon  = AstroCalculator::planetLongitude($jd, 'mars');
        $merLon  = AstroCalculator::planetLongitude($jd, 'mercury');
        $satLon  = AstroCalculator::planetLongitude($jd, 'saturn');

        // Sidereal = tropical − ayanamsa (Lahiri)
        $n = fn($x) => fmod(fmod($x - $ayan, 360) + 360, 360);

        return [
            'sun'     => ['lon' => $sunLon, 'sider' => $n($sunLon), 'rashi' => (int)floor($n($sunLon)/30)],
            'moon'    => ['lon' => $monLon, 'sider' => $n($monLon), 'rashi' => (int)floor($n($monLon)/30),
                          'nak' => (int)floor($n($monLon)/(360/27))],
            'jupiter' => ['lon' => $jupLon, 'sider' => $n($jupLon)],
            'venus'   => ['lon' => $venLon, 'sider' => $n($venLon)],
            'mars'    => ['lon' => $marLon, 'sider' => $n($marLon)],
            'mercury' => ['lon' => $merLon, 'sider' => $n($merLon)],
            'saturn'  => ['lon' => $satLon, 'sider' => $n($satLon)],
        ];
    }

    private static function buildPanchaData(array $pancha, array $tk): array
    {
        $nakIdx = $pancha['nakIdx'];
        return [
            'varaIdx'  => $pancha['varaIdx'],
            'varaHi'   => self::VARA_HI[$pancha['varaIdx']],
            'varaEn'   => $pancha['vara']['en'],
            'varaLord' => $pancha['vara']['lord'],
            'pakshaHi' => self::PAKSHA_HI[$tk['tithi']['paksha']],
            'paksha'   => $tk['tithi']['paksha'],
            'tithiNum' => $tk['tithi']['num'],
            'tithiHi'  => self::TITHI_HI[min($tk['tithi']['num']-1,14)],
            'tithiIdx' => $tk['tithiIndex'],
            'nakIdx'   => $nakIdx,
            'nakHi'    => self::NAK_HI[$nakIdx],
            'nakEn'    => $pancha['moonNak']['n'],
            'nakLord'  => $pancha['moonNak']['l'],
            'nakPada'  => $pancha['nakPada'],
            'nakGana'  => self::GANA_HI[self::NAK_GANA[$nakIdx]],
            'nakNadi'  => self::NADI_HI[self::NAK_NADI[$nakIdx]],
            'yogaHi'   => self::yogaHi($pancha['yoga']['n']),
            'yogaEn'   => $pancha['yoga']['n'],
            'yogaNature'=> $pancha['yoga']['nature'],
            'karanaHi' => self::karanaHi($tk['karana']['n']),
            'karanaEn' => $tk['karana']['n'],
            'elong'    => round($tk['elong'],2),
            'isPanchak'=> in_array($nakIdx, self::PANCHAK_NAK),
            'isBhadra' => $tk['karana']['n'] === 'Vishti',
        ];
    }

    private static function getAuspiciousWindows(float $rise, float $set, array $bad): array
    {
        $dayLen = $set - $rise;
        $muLen  = $dayLen / 30.0;
        $good   = [];
        for ($i=0; $i<30; $i++) {
            $s = $rise + $i * $muLen;
            $e = $s + $muLen;
            $ok = true;
            foreach ($bad as [$bs,$be]) { if($s<$be&&$e>$bs){$ok=false;break;} }
            if ($ok) $good[] = ['s'=>$s,'e'=>$e];
        }
        // Merge adjacent
        $merged = [];
        foreach ($good as $w) {
            if ($merged && ($w['s']-$merged[count($merged)-1]['e']) < 0.02)
                $merged[count($merged)-1]['e'] = $w['e'];
            else $merged[] = $w;
        }
        return $merged;
    }

    private static function grade(int $s): array
    {
        return match(true){
            $s>=85=>['hi'=>'अति उत्तम','color'=>'#1a6a2a'],
            $s>=70=>['hi'=>'उत्तम',    'color'=>'#2d7a3a'],
            $s>=55=>['hi'=>'शुभ',      'color'=>'#9a6b0a'],
            $s>=40=>['hi'=>'सामान्य',  'color'=>'#8a5010'],
            default=>['hi'=>'अशुभ',    'color'=>'#c0302a'],
        };
    }

    public static function hm(float $h): string
    {
        $h  = fmod($h + 48, 24);
        $hh = (int)$h;
        $mm = (int)(($h - $hh) * 60 + 0.5);
        if ($mm===60){$hh++;$mm=0;}
        return sprintf('%02d:%02d', $hh, $mm);
    }

    private static function dateHi(int $dy, int $mo, int $yr): string
    {
        $mo_hi = ['','जनवरी','फरवरी','मार्च','अप्रैल','मई','जून','जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
        return "{$dy} {$mo_hi[$mo]} {$yr}";
    }

    // ══════════════════════════════════════════════════════════
    //  RENDER HTML — results
    // ══════════════════════════════════════════════════════════

    public static function buildResultHtml(array $data, string $type): string
    {
        $p = $data['panchanga'];

        // Determine which section to show prominently
        $mainData = match($type) {
            'vivah'         => $data['vivah'],
            'griha_pravesh' => $data['griha'],
            'vahana'        => $data['vahana'],
            'mundan'        => $data['mundan'],
            'sampatti'      => $data['sampatti'],
            default         => $data['vivah'],
        };

        $score = $mainData['score'];
        $grade = $mainData['grade'];
        $clr   = $grade['color'];

        // ── पञ्चाङ्ग पट्टी ────────────────────────────────
        $yogaClr    = in_array($p['yogaHi'], self::YOGA_ASHUBHA) ? '#c0302a' : (in_array($p['yogaHi'], self::YOGA_SHUBHA) ? '#1a6a2a' : '#5a5030');
        $panchakChip= $p['isPanchak'] ? "<span class='mhres-chip' style='border-color:#a02010;color:#841808;background:#fff0ee;'>⚠ पञ्चक</span>" : '';
        $bhadraChip = $p['isBhadra']  ? "<span class='mhres-chip' style='border-color:#c0302a;color:#f0a080;'>⚠ भद्रा</span>"  : '';

        // ── चौघड़िया HTML ─────────────────────────────────
        $choHtml = '';
        foreach ($data['choghadiya']['day'] as $ch) {
            $bg  = $ch['shubh'] ? 'rgba(26,106,42,.12)' : ($ch['idx']===4?'rgba(192,48,42,.12)':'rgba(120,96,16,.08)');
            $brd = $ch['shubh'] ? '#2d7a3a' : ($ch['idx']===4?'#c0302a':'#9a6b0a');
            $choHtml .= "<div style='border-left:3px solid {$brd};padding:7px 10px;background:{$bg};border-radius:0 6px 6px 0;margin-bottom:5px;'>
  <div style='display:flex;justify-content:space-between;align-items:center;'>
    <span style='font-family:\"Tiro Devanagari Sanskrit\",serif;font-size:1.05rem;font-weight:600;color:#1c0e04;'>{$ch['name']}</span>
    <span style='font-family:\"Crimson Pro\",serif;font-size:.9rem;color:#7a5830;font-size:.95rem;'>{$ch['start']} – {$ch['end']}</span>
  </div>
  <div style='font-size:.78rem;color:{$brd};font-family:\"Crimson Pro\",serif;'>{$ch['nature']} · {$ch['planet']}</div>
</div>";
        }

        // ── होरा HTML ─────────────────────────────────────
        $horaHtml = '';
        foreach (array_slice($data['hora'], 0, 8) as $h) { // Show day horas only
            if (!$h['isDay']) continue;
            $hbg = $h['shubh'] ? 'rgba(26,90,66,.1)' : 'rgba(120,80,10,.07)';
            $hCl = $h['shubh'] ? '#1a5a42' : '#7a5010';
            $horaHtml .= "<div style='border:1px solid rgba(168,112,40,.2);border-radius:6px;padding:7px 10px;background:{$hbg};margin-bottom:5px;display:flex;justify-content:space-between;align-items:center;'>
  <div><span style='font-family:\"Tiro Devanagari Sanskrit\",serif;font-size:1rem;color:{$hCl};'>{$h['lordHi']}</span>
  <span style='font-size:.75rem;color:#7a5830;margin-left:8px;font-family:\"Crimson Pro\",serif;font-size:1rem;'>{$h['quality']}</span></div>
  <span style='font-family:\"Crimson Pro\",serif;font-size:.88rem;color:#5a3010;'>{$h['start']}</span>
</div>";
        }

        // ── दोष + शुभ ────────────────────────────────────
        $doshaHtml = '';
        foreach (($mainData['doshas'] ?? []) as $d) {
            $doshaHtml .= "<div style='border-left:4px solid #a02010;padding:11px 16px;background:#fff0ee;border-radius:8px;margin-bottom:8px;font-size:1rem;color:#841808;font-family:var(--font);'>⚠ {$d}</div>";
        }
        $shubhHtml = '';
        foreach (($mainData['shubh'] ?? []) as $s) {
            $shubhHtml .= "<div style='border-left:4px solid #1a5a2a;padding:11px 16px;background:#f0faf2;border-radius:8px;margin-bottom:8px;font-size:1rem;color:#1a5a2a;font-family:var(--font);'>✓ {$s}</div>";
        }

        // ── मिलान HTML (विवाह) ───────────────────────────
        $milanHtml = '';
        if ($type === 'vivah') {
            // ── वर-कन्या चन्द्रबल/तारबल display ──────────────
            $cbTbHtml = '';
            $cb = $data['vivah']['chandrabala'] ?? null;
            $tb = $data['vivah']['tarabala'] ?? null;
            if ($cb) {
                $cbCl = $cb['shubh'] ? '#1a5a28' : '#841808';
                $cbBg = $cb['shubh'] ? '#e8f8ec' : '#fff0ee';
                $cbGlyph = $cb['shubh'] ? '✓' : '✗';
                $cbDist = $cb['dist'];
                $cbLabel = $cb['label'];
                $cbTbHtml .= "<div style='background:{$cbBg};border:1.5px solid {$cbCl};border-radius:8px;padding:12px 16px;margin-bottom:10px;'>"
                    . "<div style='font-size:1.1rem;font-weight:600;color:{$cbCl};'>{$cbGlyph} चन्द्रबल: {$cbLabel}</div>"
                    . "<div style='font-size:.95rem;color:#7a5830;margin-top:4px;'>कन्या की राशि से चन्द्रमा {$cbDist}वें स्थान पर।"
                    . " ".($cb['shubh']?'इस तिथि का चन्द्रबल कन्या के लिए <strong>शुभ</strong> है।':'इस तिथि का चन्द्रबल कन्या के लिए <strong>अशुभ</strong> है।')."</div>"
                    . "</div>";
            }
            if ($tb) {
                $tbCl = $tb['shubh'] ? '#1a5a28' : '#841808';
                $tbBg = $tb['shubh'] ? '#e8f8ec' : '#fff0ee';
                $tbGlyph = $tb['shubh'] ? '✓' : '✗';
                $cbTbHtml .= "<div style='background:{$tbBg};border:1.5px solid {$tbCl};border-radius:8px;padding:12px 16px;margin-bottom:10px;'>"
                    . "<div style='font-size:1.1rem;font-weight:600;color:{$tbCl};'>{$tbGlyph} तारबल: {$tb['name']}</div>"
                    . "<div style='font-size:.95rem;color:#7a5830;margin-top:4px;'>"
                    . ($tb['shubh']?'जन्म नक्षत्र से तारबल <strong>शुभ</strong> है।':'जन्म नक्षत्र से तारबल <strong>अशुभ</strong> है।')."</div>"
                    . "</div>";
            }
            if ($cbTbHtml) {
                $cbTbHtml = "<div style='margin-bottom:18px;'>"
                    . "<div style='font-size:1.2rem;font-weight:600;color:#5a2080;border-bottom:2px solid rgba(160,80,200,.2);padding-bottom:8px;margin-bottom:12px;'>॥ चन्द्रबल एवं तारबल — राशि-विशिष्ट ॥"
                    . "<span style='font-size:.82rem;color:#9a7050;font-weight:400;margin-left:8px;'>प्रत्येक राशि के लिए अलग परिणाम</span></div>"
                    . $cbTbHtml . "</div>";
            }

            // ── वर-कन्या मिलन सारणी (Vivah Milap Sarini) ──────
            if (isset($data['vivah']['milan']) && $data['vivah']['milan']) {
                $km = $data['vivah']['milan'];
                $mc = $km['rating']['color'];
                $mt = $km['total'];

                // Koot names with max points for reference
                $kootDesc = [
                    'varna'  => ['वर्ण', '१', 'वर्ण-साम्य — सामाजिक सम्बन्ध।'],
                    'vasya'  => ['वश्य', '२', 'वश्य-साम्य — अधिकार एवं प्रेम।'],
                    'tara'   => ['तारा', '३', 'तारा-साम्य — भाग्य एवं स्वास्थ्य।'],
                    'yoni'   => ['योनि', '४', 'योनि-साम्य — शारीरिक-मानसिक सामञ्जस्य।'],
                    'maitri' => ['ग्रहमैत्री', '५', 'ग्रह-मित्रता — मानसिक अनुकूलता।'],
                    'gana'   => ['गण', '६', 'गण-साम्य — स्वभाव-साम्य।'],
                    'bhakoot'=> ['भकूट', '७', 'भकूट — आर्थिक समृद्धि एवं सन्तान।'],
                    'nadi'   => ['नाड़ी', '८', 'नाड़ी — स्वास्थ्य एवं सन्तान-विज्ञान। MC: सर्वाधिक महत्त्वपूर्ण।'],
                ];

                $tableRows = '';
                foreach ($km['koot'] as $key => $kv) {
                    $cl = $kv['got']===$kv['max']?'#1a6a2a':($kv['got']>0?'#9a6b0a':'#c0302a');
                    $rowBg = $kv['dosha'] ? '#fff5f0' : ($kv['got']===$kv['max']?'#f0faf2':'#fdf8ee');
                    $dBadge = $kv['dosha'] ? "<span style='background:#f5b8a8;color:#8a1010;padding:2px 8px;border-radius:6px;font-size:.82rem;margin-left:6px;'>⚠ दोष</span>" : "<span style='background:#d4eedc;color:#1a6a2a;padding:2px 8px;border-radius:6px;font-size:.82rem;margin-left:6px;'>✓ शुभ</span>";
                    $maxPts = $kootDesc[$key][1] ?? $kv['max'];
                    $desc = $kootDesc[$key][2] ?? '';
                    $tableRows .= "<tr style='background:{$rowBg};border-bottom:1px solid rgba(168,112,40,.12);'>
  <td style='padding:11px 14px;font-size:1.05rem;font-weight:600;color:#5a2800;white-space:nowrap;'>{$kv['name']}{$dBadge}</td>
  <td style='padding:11px 14px;font-size:.95rem;color:#3a2010;text-align:center;'>{$kv['girl']}</td>
  <td style='padding:11px 14px;font-size:.95rem;color:#3a2010;text-align:center;'>{$kv['boy']}</td>
  <td style='padding:11px 14px;text-align:center;'>
    <span style='font-family:\"Crimson Pro\",serif;font-size:1.3rem;font-weight:700;color:{$cl};'>{$kv['got']}</span>
    <span style='font-size:.9rem;color:#b09070;'>/{$kv['max']}</span>
  </td>
  <td style='padding:11px 14px;font-size:.88rem;color:#7a5030;'>{$kv['note']}</td>
</tr>";
                }

                $mdBadges = '';
                foreach ($km['mahadosha'] as $md) {
                    $mdBadges .= "<span style='background:rgba(192,48,42,.15);color:#8a1010;padding:4px 12px;border-radius:20px;font-size:.9rem;margin-right:6px;margin-bottom:4px;display:inline-block;'>⚠ {$md}</span>";
                }

                // Score interpretation
                $interp = match(true) {
                    $mt >= 32 => 'अत्यन्त उत्तम — विवाह के लिए श्रेष्ठ।',
                    $mt >= 27 => 'उत्तम — विवाह के लिए शुभ।',
                    $mt >= 21 => 'मध्यम — सामान्यतः स्वीकार्य।',
                    $mt >= 18 => 'स्वीकार्य — परन्तु महादोष न हो।',
                    default   => 'अनुकूल नहीं — पुनर्विचार करें।',
                };

                $milanHtml = "
<div style='margin-bottom:24px;'>
  <div style='font-size:1.3rem;font-weight:600;color:#6b4800;border-bottom:2px solid rgba(168,112,40,.25);padding-bottom:10px;margin-bottom:16px;'>
    ॥ वर-कन्या मिलन सारणी — अष्टकूट कुण्डली मिलान ॥
  </div>
  <!-- मिलान हेडर -->
  <div style='background:linear-gradient(135deg,#1a0e2e,#2a1848);border-radius:12px;padding:20px;margin-bottom:14px;'>
    <div style='display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:center;'>
      <div style='text-align:left;'>
        <div style='color:#e890d0;font-size:1.8rem;margin-bottom:4px;'>♀</div>
        <div style='font-size:1.2rem;font-weight:600;color:#f0d8ff;'>{$km['girRashi']}</div>
        <div style='font-size:.95rem;color:rgba(200,180,255,.7);'>{$km['girNak']} नक्षत्र</div>
      </div>
      <div style='text-align:center;padding:0 16px;'>
        <div style='font-family:\"Crimson Pro\",serif;font-size:4rem;font-weight:700;color:{$mc};line-height:1;'>{$mt}</div>
        <div style='font-size:1rem;color:rgba(200,180,255,.5);margin-bottom:4px;'>/ ३६ गुण</div>
        <div style='font-size:1.1rem;font-weight:600;color:{$mc};'>{$km['rating']['hi']}</div>
        <div style='font-size:.88rem;color:rgba(200,180,255,.6);margin-top:4px;'>{$interp}</div>
      </div>
      <div style='text-align:right;'>
        <div style='color:#70b8e8;font-size:1.8rem;margin-bottom:4px;'>♂</div>
        <div style='font-size:1.2rem;font-weight:600;color:#d8eaff;'>{$km['boyRashi']}</div>
        <div style='font-size:.95rem;color:rgba(160,200,255,.7);'>{$km['boyNak']} नक्षत्र</div>
      </div>
    </div>
    ".($mdBadges?"<div style='margin-top:12px;padding-top:10px;border-top:1px solid rgba(255,255,255,.1);'>{$mdBadges}</div>":'')."
  </div>

  <!-- अष्टकूट तालिका -->
  <div style='border:1.5px solid rgba(168,112,40,.25);border-radius:10px;overflow:hidden;'>
    <table style='width:100%;border-collapse:collapse;font-family:\"Tiro Devanagari Sanskrit\",serif;'>
      <thead>
        <tr style='background:#f2e0c4;'>
          <th style='padding:11px 14px;text-align:left;font-size:1.05rem;color:#6b4800;font-weight:600;'>कूट / विषय</th>
          <th style='padding:11px 14px;text-align:center;font-size:1rem;color:#a02060;font-weight:600;'>♀ कन्या</th>
          <th style='padding:11px 14px;text-align:center;font-size:1rem;color:#204090;font-weight:600;'>♂ वर</th>
          <th style='padding:11px 14px;text-align:center;font-size:1.05rem;color:#6b4800;font-weight:600;'>अंक</th>
          <th style='padding:11px 14px;text-align:left;font-size:1.05rem;color:#6b4800;font-weight:600;'>परिणाम</th>
        </tr>
      </thead>
      <tbody>{$tableRows}</tbody>
      <tfoot>
        <tr style='background:#f9edd8;border-top:2px solid rgba(168,112,40,.3);'>
          <td colspan='3' style='padding:12px 14px;font-size:1.05rem;font-weight:600;color:#6b4800;'>कुल योग</td>
          <td style='padding:12px 14px;text-align:center;'>
            <span style='font-family:\"Crimson Pro\",serif;font-size:1.5rem;font-weight:700;color:{$mc};'>{$mt}</span>
            <span style='font-size:.9rem;color:#b09070;'>/३६</span>
          </td>
          <td style='padding:12px 14px;font-size:.95rem;color:{$mc};font-weight:600;'>{$km['rating']['hi']} — {$interp}</td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div style='margin-top:10px;padding:10px 14px;background:#fdf8ee;border-radius:7px;font-size:.95rem;color:#3a2010;line-height:1.7;'>
    <strong>MC:</strong> न्यूनतम १८ गुण, नाड़ी-भकूट-गण दोष रहित होना चाहिए। नाड़ी दोष सर्वाधिक गम्भीर — सन्तान-हानि की सम्भावना। भकूट दोष — आर्थिक हानि। गण दोष — स्वभाव-भेद।
  </div>
</div>" . $cbTbHtml;
            } elseif ($cbTbHtml) {
                $milanHtml = $cbTbHtml;
            }
        }

        // ── ग्रह-अस्त (विवाह) ─────────────────────────────
        $astaHtml = '';
        if ($type === 'vivah') {
            $gA = $data['vivah']['guruAsta'];
            $sA = $data['vivah']['shukraAsta'];
            $gClr = $gA['asta']?'#c0302a':'#1a6a2a';
            $sClr = $sA['asta']?'#c0302a':'#1a6a2a';
            $astaHtml = "
<div style='display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;'>
  <div style='border:1.5px solid {$gClr};border-radius:9px;padding:12px;background:rgba(0,0,0,.02);'>
    <div style='font-family:\"Tiro Devanagari Sanskrit\",serif;font-size:1rem;font-weight:600;color:{$gClr};'>गुरु ".($gA['asta']?'अस्त ✗':'अनस्त ✓')."</div>
    <div style='font-size:.88rem;color:#7a5830;margin-top:4px;'>सूर्य से {$gA['diff']}° · सीमा: {$gA['limit']}°</div>
    <div style='font-size:.82rem;color:#9a6b0a;margin-top:3px;'>".($gA['asta']?'MC: विवाह वर्जित।':'MC: विवाह के लिए अनुकूल।')."</div>
  </div>
  <div style='border:1.5px solid {$sClr};border-radius:9px;padding:12px;background:rgba(0,0,0,.02);'>
    <div style='font-family:\"Tiro Devanagari Sanskrit\",serif;font-size:1rem;font-weight:600;color:{$sClr};'>शुक्र ".($sA['asta']?'अस्त ✗':'अनस्त ✓')."</div>
    <div style='font-size:.82rem;color:#5a3010;margin-top:4px;'>सूर्य से {$sA['diff']}° · सीमा: {$sA['limit']}°</div>
    <div style='font-size:.78rem;color:#7a5030;margin-top:3px;'>".($sA['asta']?'MC: विवाह वर्जित।':'MC: अनुकूल।')."</div>
  </div>
</div>";
        }

        // ── शुभ काल ──────────────────────────────────────
        $winHtml = '';
        $windows = $data['vivah']['windows'] ?? self::getAuspiciousWindows($data['riseHr'], $data['setHr'], [
            [$data['rahuKaal']['s'],$data['rahuKaal']['e']],[$data['yamaghanta']['s'],$data['yamaghanta']['e']]
        ]);
        foreach (array_slice($windows, 0, 5) as $w) {
            $winHtml .= "<span style='background:#d4eeea;color:#1a5a50;border:1.5px solid rgba(26,90,80,.25);border-radius:20px;padding:7px 16px;font-family:\"Crimson Pro\",serif;font-size:.95rem;letter-spacing:.03em;'>".self::hm($w['s']).' – '.self::hm($w['e'])."</span> ";
        }

        // ── शास्त्र निर्देश ───────────────────────────────
        $shaHtml = '';
        foreach (($mainData['shastra'] ?? []) as $i => $r) {
            $n = $i+1;
            $shaHtml .= "<div style='display:flex;gap:12px;align-items:flex-start;margin-bottom:9px;'>
  <span style='min-width:24px;height:24px;border-radius:50%;background:#f5e8d0;border:1px solid rgba(168,112,40,.3);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#9a6b0a;flex-shrink:0;font-family:\"Crimson Pro\",serif;font-weight:700;'>{$n}</span>
  <span style='font-size:.95rem;color:#2a1408;line-height:1.65;'>{$r}</span>
</div>";
        }

        // ── लग्न तालिका ──────────────────────────────────
        $lagnaHtml = '';
        foreach ($data['lagna'] as $l) {
            $lCl    = $l['shubh'] ? '#1a5a28' : '#7a5010';
            $lBg    = $l['shubh'] ? 'rgba(26,90,42,.06)' : 'rgba(120,80,10,.04)';
            $lSign  = $l['signHi'];
            $lType  = $l['type'];
            $lTime  = $l['time'];
            $lagnaHtml .= "<div style='display:flex;justify-content:space-between;align-items:center;padding:7px 12px;border-bottom:1px solid rgba(168,112,40,.08);background:{$lBg};'>"
                . "<span style='font-size:.95rem;color:{$lCl};font-weight:600;'>{$lSign}</span>"
                . "<span style='font-size:.88rem;color:#9a6b0a;'>{$lType}</span>"
                . "<span style='font-size:.88rem;color:#6b4800;'>{$lTime}</span>"
                . "</div>";
        }

        $typeHi = match($type){'vivah'=>'विवाह मुहूर्त','griha_pravesh'=>'गृहप्रवेश मुहूर्त','vahana'=>'वाहन पूजा','mundan'=>'मुण्डन संस्कार','sampatti'=>'सम्पत्ति क्रय',default=>'मुहूर्त विश्लेषण'};

        // लट्टा दोष HTML
        $lattaHtml = '';
        if ($type === 'vivah' && !empty($data['vivah']['lattaDoshas'])) {
            foreach ($data['vivah']['lattaDoshas'] as $ld) {
                $lattaHtml .= "<div style='border-left:3px solid #c86a14;padding:8px 12px;background:#fff8ee;border-radius:0 6px 6px 0;margin-bottom:6px;font-size:.88rem;color:#5a2800;'>⚡ {$ld['note']}</div>";
            }
        }

        $panchakNote = $data['panchak']['active'] ? "<div style='background:#fff0ee;border:1.5px solid #f0a090;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:.9rem;color:#8a1010;'><strong>पञ्चक सक्रिय:</strong> {$data['panchak']['note']}</div>" : '';

        // ── राशि-विशिष्ट कार्ड (chandrabala से, milan के बिना भी) ──
        $rashiCardHtml = '';
        if ($type === 'vivah') {
            $cb         = $data['vivah']['chandrabala'] ?? null;
            // Get rashi names — from milan if available, else from options
            $girlRashiStr = isset($data['vivah']['milan']['girRashi']) ? $data['vivah']['milan']['girRashi'] : null;
            $boyRashiStr  = isset($data['vivah']['milan']['boyRashi'])  ? $data['vivah']['milan']['boyRashi']  : null;
            $girlNakStr   = isset($data['vivah']['milan']['girNak'])    ? $data['vivah']['milan']['girNak']    : null;
            $boyNakStr    = isset($data['vivah']['milan']['boyNak'])    ? $data['vivah']['milan']['boyNak']    : null;

            // Chandrabala is set whenever girlRashi was provided
            if ($cb) {
                $cbCl   = $cb['shubh'] ? '#1a5a28' : '#841808';
                $cbBg   = $cb['shubh'] ? '#e8f8ec' : '#fff0ee';
                $cbIcon = $cb['shubh'] ? '✓' : '✗';
                $cbNote = $cb['shubh'] ? 'इस तिथि का चन्द्रबल कन्या के लिए शुभ है।' : 'इस तिथि का चन्द्रबल कन्या के लिए प्रतिकूल है।';
                $cbDist = $cb['dist'];
                $cbLabel= $cb['label'];

                $girlBox = $girlRashiStr ? "<div style='background:rgba(200,80,160,.08);border:1px solid rgba(200,80,160,.25);border-radius:8px;padding:10px 14px;'>"
                    . "<div style='font-size:.8rem;color:#a02060;margin-bottom:3px;'>♀ कन्या</div>"
                    . "<div style='font-size:1.1rem;font-weight:600;color:#7a1050;'>{$girlRashiStr}</div>"
                    . ($girlNakStr?"<div style='font-size:.82rem;color:#7a5030;'>{$girlNakStr} नक्षत्र</div>":'')
                    . "</div>" : '';

                $boyBox = $boyRashiStr ? "<div style='background:rgba(80,120,200,.08);border:1px solid rgba(80,120,200,.25);border-radius:8px;padding:10px 14px;'>"
                    . "<div style='font-size:.8rem;color:#204080;margin-bottom:3px;'>♂ वर</div>"
                    . "<div style='font-size:1.1rem;font-weight:600;color:#1a3080;'>{$boyRashiStr}</div>"
                    . ($boyNakStr?"<div style='font-size:.82rem;color:#7a5030;'>{$boyNakStr} नक्षत्र</div>":'')
                    . "</div>" : '';

                $rashiCardHtml = "<div style='background:linear-gradient(135deg,#fdf0f8,#f0f0ff);border:1.5px solid rgba(160,80,200,.2);border-radius:12px;padding:16px 20px;margin-bottom:18px;'>"
                    . "<div style='font-size:1.05rem;color:#5a2080;font-weight:600;margin-bottom:10px;'>॥ आपकी राशि के अनुसार परिणाम ॥ "
                    . "<span style='font-size:.82rem;color:#9a7050;font-weight:400;'>— यह परिणाम हर राशि के लिए अलग होता है</span></div>"
                    . (($girlBox||$boyBox)?"<div style='display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;'>{$girlBox}{$boyBox}</div>":'')
                    . "<div style='background:{$cbBg};border:1.5px solid {$cbCl};border-radius:8px;padding:10px 14px;'>"
                    . "<div style='font-size:1rem;font-weight:600;color:{$cbCl};'>{$cbIcon} चन्द्रबल: {$cbLabel}</div>"
                    . "<div style='font-size:.88rem;color:#7a5830;margin-top:3px;'>कन्या की राशि से चन्द्रमा {$cbDist}वें स्थान पर। {$cbNote}</div>"
                    . "</div></div>";
            }
        }
        // माह-स्कैन selects
        $months = ['जनवरी','फरवरी','मार्च','अप्रैल','मई','जून','जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
        $mOpts  = ''; foreach ($months as $mi => $mn) { $sel=(($mi+1)==(int)date('n'))?'selected':''; $mOpts.="<option value='".($mi+1)."' {$sel}>{$mn}</option>"; }
        $yOpts  = ''; for ($y=(int)date('Y'); $y<=(int)date('Y')+3; $y++) { $sel=($y==(int)date('Y'))?'selected':''; $yOpts.="<option value='{$y}' {$sel}>{$y}</option>"; }

        return <<<HTML
<style>
/* ── मुहूर्त परिणाम — Light Festival Theme ──────────── */
.mhres{
  --bg0:#fdf6ec;--bg1:#f9edd8;--bg2:#f2e0c4;--sur:#fffbf5;
  --bdr:rgba(168,112,40,.18);--bdr2:rgba(168,112,40,.35);
  --gold:#9a6b0a;--glt:#f5e6c0;--gmd:#c89020;--gdk:#6b4800;
  --saffron:#c8521a;--slt:#fde8dc;--terra:#a02010;
  --teal:#1a5a50;--tlt:#d4eeea;
  --ink:#1c1008;--ink2:#3a2410;--ink3:#7a5830;--ink4:#b09070;
  --font:'Tiro Devanagari Sanskrit',serif;
  --serif:'Crimson Pro',Georgia,serif;
  font-family:var(--font);background:var(--bg0);color:var(--ink);font-size:17px;
}
.mhres *{box-sizing:border-box;}

/* ── हीरो — light manuscript header ─────────────────── */
.mhres-hero{
  background:linear-gradient(180deg,var(--sur) 0%,var(--bg1) 55%,var(--bg2) 100%);
  border-bottom:2px solid var(--bdr2);padding:22px 28px 18px;
  position:relative;
}
.mhres-hero::after{
  content:'';position:absolute;bottom:-1px;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,var(--gmd),transparent);
}
.mhres-type{font-family:var(--font);font-size:2.2rem;color:var(--gdk);margin:0 0 3px;}
.mhres-sub{font-family:var(--serif);font-size:.82rem;font-style:italic;color:var(--ink4);letter-spacing:.12em;margin-bottom:14px;}
.mhres-score-row{display:flex;align-items:center;gap:14px;margin-bottom:10px;flex-wrap:wrap;}
.mhres-num{font-family:var(--serif);font-size:3.8rem;font-weight:700;line-height:1;color:var(--gdk);}
.mhres-grade{font-family:var(--font);font-size:1.7rem;padding:4px 18px;border-radius:6px;border:2px solid;}
.mhres-bar{background:var(--bg3,#e8d0aa);border-radius:3px;height:7px;margin-bottom:14px;overflow:hidden;}
.mhres-bar-fill{height:7px;border-radius:3px;transition:width .8s cubic-bezier(.4,0,.2,1);}

/* पञ्चाङ्ग पट्टी */
.mhres-pancha{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--bdr2);border-radius:9px;overflow:hidden;border:1.5px solid var(--bdr2);margin-bottom:13px;}
.mhres-pc{background:var(--sur);padding:10px 6px;text-align:center;}
.mhres-pc span:first-child{display:block;font-family:var(--font);font-size:.7rem;color:var(--ink4);margin-bottom:4px;}
.mhres-pc span:last-child{font-family:var(--font);font-size:1rem;color:var(--ink);font-weight:600;}
.mhres-chips{display:flex;gap:7px;flex-wrap:wrap;}
.mhres-chip{background:var(--glt);border:1.5px solid var(--bdr2);border-radius:18px;padding:5px 14px;font-size:.88rem;color:var(--ink2);font-family:var(--serif);}

/* बॉडी */
.mhres-body{padding:22px 26px 30px;background:var(--bg0);}
.mhres-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.mhres-sec{margin-bottom:22px;}
.mhres-sh{font-family:var(--font);font-size:1.2rem;color:var(--gdk);border-bottom:1.5px solid var(--bdr2);padding-bottom:7px;margin-bottom:13px;}
.mhres-rahu{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:10px;}
.mhres-inaup{background:#fff0ee;border:1.5px solid rgba(160,32,20,.2);color:var(--terra);border-radius:18px;padding:6px 16px;font-size:.9rem;font-family:var(--serif);}
.mhres-win{background:var(--tlt);border:1.5px solid rgba(26,90,80,.25);color:var(--teal);border-radius:20px;padding:7px 16px;font-size:.92rem;font-family:var(--serif);font-weight:600;}
.mhres-abhijit{background:linear-gradient(135deg,var(--glt),#fdecc0);border:1.5px solid var(--gmd);border-radius:9px;padding:13px 18px;}
.mhres-scan-bar{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin-bottom:12px;}
.mhres-sel{padding:9px 13px;border-radius:7px;border:1.5px solid var(--bdr2);background:var(--sur);font-family:var(--font);font-size:.95rem;color:var(--ink);outline:none;}
.mhres-sbtn{padding:10px 22px;border-radius:7px;border:none;cursor:pointer;background:linear-gradient(160deg,var(--saffron),#7a3202);color:#fff;font-family:var(--font);font-size:.95rem;transition:filter .2s;}
.mhres-sbtn:hover{filter:brightness(1.1);}
.mhres-scanres{border:1.5px solid var(--bdr);border-radius:9px;overflow:hidden;display:none;margin-top:10px;}
@media(max-width:640px){.mhres-grid{grid-template-columns:1fr;}.mhres-pancha{grid-template-columns:repeat(3,1fr);}.mhres-num{font-size:2.8rem;}.mhres-type{font-size:1.7rem;}.mhres-body{padding:16px;}}
</style>

<div class='mhres'>
<div class='mhres-hero'>
  <div class='mhres-type'>{$typeHi}</div>
  <div class='mhres-sub'>मुहूर्त चिन्तामणि · दैवज्ञ राम · {$data['dateHi']}</div>
  <div class='mhres-score-row'>
    <span class='mhres-num' style='color:{$clr};'>{$score}</span>
    <span class='mhres-grade' style='color:{$clr};border-color:{$clr};background:rgba(0,0,0,.04);'>{$grade['hi']}</span>
    <span style='font-family:"Crimson Pro",serif;font-size:1rem;color:var(--ink4);'>/१०० अंक</span>
  </div>
  <div class='mhres-bar'><div class='mhres-bar-fill' style='width:{$score}%;background:{$clr};'></div></div>
  <div class='mhres-pancha'>
    <div class='mhres-pc'><span>वार</span><span>{$p['varaHi']}</span></div>
    <div class='mhres-pc'><span>तिथि</span><span>{$p['pakshaHi']} {$p['tithiHi']}</span></div>
    <div class='mhres-pc'><span>नक्षत्र</span><span>{$p['nakHi']}</span></div>
    <div class='mhres-pc'><span>योग</span><span style='color:{$yogaClr};'>{$p['yogaHi']}</span></div>
    <div class='mhres-pc'><span>करण</span><span>{$p['karanaHi']}</span></div>
  </div>
  <div class='mhres-chips'>
    <span class='mhres-chip'>☀ सूर्योदय {$data['sunrise']}</span>
    <span class='mhres-chip'>🌅 सूर्यास्त {$data['sunset']}</span>
    <span class='mhres-chip'>{$p['nakGana']} गण · {$p['nakNadi']} नाड़ी</span>
    <span class='mhres-chip'>राहुकाल {$data['rahuKaal']['str']}</span>
    <span class='mhres-chip'>लग्न: {$data['lagnaRashiHi']}</span>
    <span class='mhres-chip'>चन्द्र: {$data['moonRashiHi']}</span>
    <span class='mhres-chip'>सूर्य: {$data['sunRashiHi']}</span>
    {$panchakChip}
    {$bhadraChip}
  </div>
</div>

<div class='mhres-body'>

{$rashiCardHtml}
{$panchakNote}
{$doshaHtml}
{$shubhHtml}

{$astaHtml}
{$lattaHtml}

<div class='mhres-grid'>

<!-- बाईं तरफ -->
<div>

<!-- कारक विश्लेषण सारांश already shown above -->

<!-- चौघड़िया -->
<div class='mhres-sec'>
  <div class='mhres-sh'>॥ चौघड़िया ॥</div>
  {$choHtml}
</div>

<!-- लग्न तालिका -->
<div class='mhres-sec'>
  <div class='mhres-sh'>॥ लग्न तालिका ॥</div>
  <div style='border:1px solid rgba(168,112,40,.18);border-radius:8px;overflow:hidden;'>{$lagnaHtml}</div>
</div>

</div><!-- left -->

<!-- दाईं तरफ -->
<div>

<!-- मिलान (विवाह) -->
{$milanHtml}

<!-- शुभ होरा -->
<div class='mhres-sec'>
  <div class='mhres-sh'>॥ शुभ होरा ॥</div>
  {$horaHtml}
</div>

<!-- काल खण्ड -->
<div class='mhres-sec'>
  <div class='mhres-sh'>॥ शुभ मुहूर्त काल खण्ड ॥</div>
  <div style='display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;'>{$winHtml}</div>
  <div class='mhres-rahu'>
    <div class='mhres-inaup'>⛔ राहुकाल {$data['rahuKaal']['str']}</div>
    <div class='mhres-inaup'>⛔ यमगण्ड {$data['yamaghanta']['str']}</div>
    <div class='mhres-inaup'>⛔ गुलिक {$data['gulikaKaal']['str']}</div>
  </div>
  <div class='mhres-abhijit'>
    <div style='font-family:"Tiro Devanagari Sanskrit",serif;font-size:.88rem;color:#6b4800;margin-bottom:3px;'>अभिजित् मुहूर्त — MC: सर्वदा शुभ</div>
    <div style='font-family:"Crimson Pro",serif;font-size:1.3rem;font-weight:700;color:#c8521a;'>{$data['abhijit']['str']}</div>
  </div>
</div>

</div><!-- right -->
</div><!-- grid -->

<!-- शास्त्र निर्देश -->
<div class='mhres-sec'>
  <div class='mhres-sh'>॥ MC शास्त्र निर्देश — मुहूर्त चिन्तामणि (दैवज्ञ राम) ॥</div>
  {$shaHtml}
</div>

</div></div>

<script>
function mhDoScan(type){
  var m=document.getElementById('mhs-mo').value,y=document.getElementById('mhs-yr').value;
  var res=document.getElementById('mhs-res');
  res.style.display='block';
  res.innerHTML='<div style="padding:14px;font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;color:#7a5030;">॥ खोज जारी है… ॥</div>';
  var csrf=document.querySelector('meta[name=csrf-token]');
  fetch('/astro/muhrat/month',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf?csrf.content:'','Accept':'application/json'},
    body:JSON.stringify({year:parseInt(y),month:parseInt(m),type:type,
      lat:typeof _masaLat!=='undefined'?_masaLat:28.61,
      lon:typeof _masaLon!=='undefined'?_masaLon:77.21,
      utcOff:typeof _masaOff!=='undefined'?_masaOff:5.5,
      girlRashiIdx:typeof _mhGirlRashi!=='undefined'?_mhGirlRashi:null,
      boyRashiIdx:typeof _mhBoyRashi!=='undefined'?_mhBoyRashi:null})
  }).then(r=>r.json()).then(d=>{res.innerHTML=d.html||'<div style="padding:14px;color:#c0302a;">त्रुटि।</div>';}).catch(()=>{res.innerHTML='<div style="padding:14px;color:#c0302a;">नेटवर्क त्रुटि।</div>';});
}
</script>
HTML;
    }

    // ══════════════════════════════════════════════════════════
    //  MONTH RESULTS HTML
    // ══════════════════════════════════════════════════════════

    // ══════════════════════════════════════════════════════════
    //  माह HTML — पुस्तक शैली तालिका (Book-style table)
    //  जैसा छवियों में दिखाया गया: पक्ष|तिथि|वार|दिनांक|नक्षत्र|
    //  सूर्य राशि|चन्द्र राशि|चन्द्रबल|तारबल|दोष|श्रेणी
    // ══════════════════════════════════════════════════════════
    public static function buildMonthHtml(array $dates, string $type, int $mo = 0, int $yr = 0): string
    {
        $moHi = ['','जनवरी','फरवरी','मार्च','अप्रैल','मई','जून','जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
        $head = $mo > 0 ? "॥ {$moHi[$mo]} {$yr} — " . match($type){'vivah'=>'शुभ विवाह मुहूर्त','griha_pravesh'=>'गृहप्रवेश मुहूर्त','vahana'=>'वाहन क्रय','mundan'=>'मुण्डन','sampatti'=>'सम्पत्ति क्रय',default=>'शुभ मुहूर्त'} . " ॥" : '';

        if (empty($dates)) {
            return "<div style='padding:18px 22px;font-family:\"Tiro Devanagari Sanskrit\",serif;font-size:1rem;
              color:#7a5830;background:#fff8ee;border-radius:8px;border:1px solid rgba(168,112,40,.2);'>
              ".($head?"<strong>{$head}</strong><br><br>":'')."
              इस माह कोई शुभ तिथि नहीं मिली। राशि/श्रेणी बदलकर पुनः प्रयास करें।
            </div>";
        }

        // Grade color helper
        $gc = fn(int $s) => $s>=85?'#1a6a2a':($s>=70?'#2d7a3a':($s>=55?'#9a6b0a':($s>=40?'#8a5010':'#c0302a')));
        $gh = fn(int $s) => $s>=85?'अति उत्तम':($s>=70?'उत्तम':($s>=55?'शुभ':($s>=40?'सामान्य':'अशुभ')));
        $rowbg = fn(int $s) => $s>=85?'#f0faf2':($s>=70?'#f5faf0':($s>=55?'#fffdf0':($s>=40?'#fffbf5':'#fff5f0')));

        $html  = "<div style='font-family:\"Tiro Devanagari Sanskrit\",serif;overflow-x:auto;'>";
        if ($head) {
            $html .= "<div style='padding:10px 16px;background:linear-gradient(135deg,#f9edd8,#f2e0c4);
              border-bottom:2px solid rgba(168,112,40,.3);font-size:1.1rem;font-weight:600;color:#6b4800;
              letter-spacing:.04em;'>{$head}</div>";
        }

        // ── तालिका शीर्ष ─────────────────────────────────────
        $html .= "<table style='width:100%;border-collapse:collapse;font-size:1.08rem;'>
<thead>
<tr style='background:#f2e0c4;border-bottom:2px solid rgba(168,112,40,.4);'>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.9rem;border-right:1px solid rgba(168,112,40,.2);'>पक्ष<br>तिथि</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>वार<br>दिनांक</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>नक्षत्र</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>सूर्य<br>राशि</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>चन्द्र<br>राशि</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>चन्द्रबल<br>तारबल</th>
  <th style='padding:9px 8px;text-align:left;color:#6b4800;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2);'>योग · करण · दोष</th>
  <th style='padding:9px 8px;text-align:center;color:#6b4800;font-size:.95rem;'>श्रेणी</th>
</tr>
</thead>
<tbody>";

        foreach ($dates as $d) {
            $s   = $d['score'];
            $clr = $gc($s);
            $bg  = $rowbg($s);
            $grd = $gh($s);

            // चन्द्रबल cell
            $cbCell = '—';
            if (isset($d['chandrabala'])) {
                $cb  = $d['chandrabala'];
                $cbCl= $cb['shubh'] ? '#1a5a28' : '#841808';
                $cbBg= $cb['shubh'] ? '#e8f8ec' : '#fff0ee';
                $cbCell = "<span style='background:{$cbBg};color:{$cbCl};padding:2px 6px;border-radius:4px;font-size:1rem;display:inline-block;margin-bottom:2px;'>"
                    .($cb['shubh']?'✓':'✗')." {$cb['dist']}वाँ</span>";
            }
            if (isset($d['tarabala'])) {
                $tb  = $d['tarabala'];
                $tbCl= $tb['shubh'] ? '#1a5a28' : '#841808';
                $tbBg= $tb['shubh'] ? '#e8f8ec' : '#fff0ee';
                $cbCell .= "<br><span style='background:{$tbBg};color:{$tbCl};padding:2px 6px;border-radius:4px;font-size:.88rem;display:inline-block;'>"
                    .($tb['shubh']?'✓':'✗')." {$tb['name']}</span>";
            }

            // दोष list — compact
            $doshaList = [];
            $flags = $d['flags'] ?? [];
            if ($flags['guru_asta']   ?? false) $doshaList[] = "<span style='color:#841808;font-size:.95rem;'>गुरु अस्त</span>";
            if ($flags['shukra_asta'] ?? false) $doshaList[] = "<span style='color:#841808;font-size:.95rem;'>शुक्र अस्त</span>";
            if ($flags['bhadra']      ?? false) $doshaList[] = "<span style='color:#841808;font-size:.95rem;'>भद्रा</span>";
            if ($flags['panchak']     ?? false) $doshaList[] = "<span style='color:#841808;font-size:.95rem;'>पञ्चक</span>";
            if ($flags['latta']       ?? false) $doshaList[] = "<span style='color:#c86a14;font-size:.95rem;'>लट्टा</span>";
            if ($flags['nak_varjit']  ?? false) $doshaList[] = "<span style='color:#841808;font-size:.95rem;'>वर्जित नक्षत्र</span>";
            if ($flags['krishna_paksha']??false)$doshaList[] = "<span style='color:#841808;font-size:.95rem;'>कृष्ण पक्ष</span>";
            $doshaHtml = $doshaList ? implode(' ', $doshaList) : "<span style='color:#1a5a28;font-size:.95rem;'>दोष रहित</span>";

            // योग
            $yogaClr = in_array($d['yogaHi'], self::YOGA_ASHUBHA) ? '#841808' : (in_array($d['yogaHi'], self::YOGA_SHUBHA) ? '#1a5a28' : '#7a5830');

            // शुभ चौघड़िया
            $choParts = [];
            foreach (($d['shubhCho'] ?? []) as $ch) {
                $choParts[] = $ch['name'].' '.$ch['start'];
            }
            $choStr = $choParts ? implode(', ', $choParts) : '';

            // मिलान badge
            $milanBadge = '';
            if (isset($d['milan']['total'])) {
                $mt = $d['milan']['total'];
                $mc = $d['milan']['rating']['color'];
                $milanBadge = "<br><span style='background:{$mc};color:#fff;padding:1px 7px;border-radius:8px;font-size:.95rem;'>मिलान {$mt}/३६</span>";
            }

            $html .= "<tr style='background:{$bg};border-bottom:1px solid rgba(168,112,40,.12);'>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);'>
    <div style='font-size:.95rem;color:#6b4800;font-weight:600;'>{$d['pakshaHi']}</div>
    <div style='font-size:.9rem;color:#3a2410;'>{$d['tithiHi']}</div>
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);'>
    <div style='font-size:1.05rem;font-weight:600;color:#1c0e04;'>{$d['varaHi']}</div>
    <div style='font-family:\"Crimson Pro\",serif;font-size:1.15rem;font-weight:700;color:#6b4800;'>{$d['day']}</div>
    <div style='font-size:.82rem;color:#9a6b0a;'>{$d['sunrise']}</div>
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);'>
    <div style='font-size:1.05rem;color:#1c0e04;font-weight:600;'>{$d['nakHi']}</div>
    ".($choStr?"<div style='font-size:.92rem;color:#1a5a28;margin-top:3px;'>{$choStr}</div>":'')."
    <div style='font-size:.92rem;color:#7a5830;'>अभिजित् {$d['abhi']}</div>
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);font-size:1.05rem;color:#3a2410;'>
    {$d['sunRashiHi']}
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);font-size:1.05rem;color:#3a2410;'>
    {$d['moonRashiHi']}
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);'>
    {$cbCell}
  </td>
  <td style='padding:12px 10px;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);'>
    <div style='color:{$yogaClr};font-size:.95rem;'>{$d['yogaHi']} · {$d['karanaHi']}</div>
    <div style='margin-top:3px;'>{$doshaHtml}</div>
    {$milanBadge}
    <div style='font-size:.82rem;color:#c0302a;margin-top:2px;'>राहुकाल {$d['rahuStr']}</div>
  </td>
  <td style='padding:12px 10px;text-align:center;vertical-align:top;'>
    <div style='font-family:\"Crimson Pro\",serif;font-size:2rem;font-weight:700;color:{$clr};line-height:1;'>{$s}</div>
    <div style='font-size:.9rem;color:{$clr};font-weight:600;margin-top:2px;'>{$grd}</div>
  </td>
</tr>";
        }

        $html .= "</tbody></table></div>";
        return $html;
    }
}