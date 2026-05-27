<?php

namespace App\Services;

use App\Services\AstroCalculator;

/**
 * HinduFestivalCalculator — Comprehensive Hindu Festival & Vrat Calendar
 *
 * Mathematical Basis:
 *   • Jean Meeus — "Astronomical Algorithms" (2nd Ed.) — Sun/Moon positions
 *   • B.V. Raman   — "Hindu Predictive Astrology", "Muhurtha"
 *   • Lahiri Ayanamsa (Rashtriya Panchang standard)
 *   • Surya Siddhanta — Solar Sankranti calculations
 *
 * KEY FIX (v2): Masa name is determined by sun sign at PREVIOUS NEW MOON + 1
 *   Old (WRONG): MASA_FROM_SUN_SIGN = [1,2,3,4,5,6,7,8,9,10,11,0]  — off by month
 *   New (CORRECT): getMasaFromNewMoon() — bisects to find new moon, reads sun sign there
 */
class HinduFestivalCalculator
{
    // ══════════════════════════════════════════════════════════
    //  स्थैतिक डेटा
    // ══════════════════════════════════════════════════════════
    private const DOW_NAMES_HI = ['रवि','सोम','मंगल','बुध','गुरु','शुक्र','शनि'];

    public const MASA_NAMES = [
        'Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
        'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna',
    ];

    // ══════════════════════════════════════════════════════════
    //  चन्द्र मास निर्धारण — CORRECT ALGORITHM
    //  masa = MASA_NAMES[(sun_sign_at_previous_new_moon + 1) % 12]
    // ══════════════════════════════════════════════════════════
    private static function getMasaFromNewMoon(float $jdSunrise): string
    {
        // Step back to find the previous new moon (elongation ≈ 0)
        $jd = $jdSunrise;
        $e0 = fmod(AstroCalculator::moonLongitude($jd) - AstroCalculator::sunLongitude($jd) + 360, 360);

        // If not at new moon, walk back until elongation crosses from >300 to <60
        if ($e0 > 12.0) {
            for ($steps = 0; $steps < 35; $steps++) {
                $jdPrev = $jd - 1.0;
                $e1 = fmod(AstroCalculator::moonLongitude($jdPrev) - AstroCalculator::sunLongitude($jdPrev) + 360, 360);
                if ($e1 > 300.0 && $e0 < 60.0) {
                    // Crossed new moon — bisect for precision
                    $lo = $jdPrev; $hi = $jd;
                    for ($i = 0; $i < 40; $i++) {
                        $mid  = ($lo + $hi) / 2.0;
                        $eMid = fmod(AstroCalculator::moonLongitude($mid) - AstroCalculator::sunLongitude($mid) + 360, 360);
                        if ($eMid > 180.0) $lo = $mid;
                        else               $hi = $mid;
                        if ($hi - $lo < 0.0005) break;
                    }
                    $jd = ($lo + $hi) / 2.0;
                    break;
                }
                $jd = $jdPrev;
                $e0 = $e1;
            }
        }

        $sunLon   = AstroCalculator::sunLongitude($jd);
        $ayan     = AstroCalculator::lahiriAyanamsa($jd);
        $sunSider = fmod(fmod($sunLon - $ayan, 360) + 360, 360);
        $sunSign  = (int)floor($sunSider / 30);
        // Lunar masa = one sign ahead of sun at new moon
        return self::MASA_NAMES[($sunSign + 1) % 12];
    }

    /**
     * Adhik / Purushottam Maas detection
     * Two consecutive new moons with same sun sign → leap month
     */
    private static function getAdhikMaasFlag(float $jdSunrise, string $currentMasa): bool
    {
        // Check masa ~30 days later
        $jdNext   = $jdSunrise + 30.0;
        $nextMasa = self::getMasaFromNewMoon($jdNext);
        return ($currentMasa === $nextMasa);
    }

    // ══════════════════════════════════════════════════════════
    //  मुख्य गणना
    // ══════════════════════════════════════════════════════════
    public static function calculateYear(
        int $year, float $lat, float $lon, float $utcOff
    ): array {
        $festivals = [];
        
        // 1. Ekadashis
        $ekadashis = AstroCalculator::getEkadashiYear($year, $lat, $lon, $utcOff);
        foreach ($ekadashis as $ek) {
            $festivals[] = [
                'date'         => $ek['date'],
                'name'         => $ek['name'],
                'name_hi'      => $ek['nameHi'] ?? '',
                'type'         => 'vrat',
                'category'     => 'ekadashi',
                'tithi'        => ($ek['paksha'] ?? '') . ' एकादशी',
                'icon'         => '🌸',
                'details'      => self::getEkadashiDetails($ek['name'] ?? ''),
            ];
        }

        // 2. Makar Sankranti (Solar)
        $sankrantis = AstroCalculator::calculateSankrantis($year, $utcOff);
        foreach ($sankrantis as $deg => $sk) {
            if ($deg === 270) {
                $dt = new \DateTime($sk['time']);
                $festivals[] = [
                    'date' => $dt->format('Y-m-d'), 'name' => 'Makar Sankranti', 'name_hi' => 'मकर संक्रांति',
                    'type' => 'festival', 'category' => 'festival', 'tithi' => 'Makar Sankranti', 'icon' => '🪁',
                    'details' => 'Exact time: ' . $sk['time']
                ];
            }
        }

        $startDate = new \DateTime("{$year}-01-01");
        $masaCache = [];

        for ($i = 0; $i < 366; $i++) {
            $dt = clone $startDate;
            $dt->modify("+{$i} days");
            if ((int)$dt->format('Y') !== $year) break;

            $dateStr = $dt->format('Y-m-d');
            $yr = (int)$dt->format('Y'); $mo = (int)$dt->format('m'); $dy = (int)$dt->format('d');

            $approx = AstroCalculator::calculate($yr, $mo, $dy, 6, 0, $utcOff, $lat, $lon);
            $sunriseHr = (!$approx['ss']['polar'] && $approx['ss']['rise'] !== null) ? $approx['ss']['rise'] : 6.0;
            $sunsetHr  = (!$approx['ss']['polar'] && $approx['ss']['set'] !== null) ? $approx['ss']['set'] : 18.0;

            // Get Sunrise Tithi
            $hr = (int)floor($sunriseHr); $mn = (int)round(($sunriseHr - $hr) * 60);
            $resultSR = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);
            $srNum = (int)($resultSR['tk']['tithi']['num'] ?? 0);
            $srPaksha = $resultSR['tk']['tithi']['paksha'] ?? '';
            
            // Get Sunset Tithi
            $hrSS = (int)floor($sunsetHr); $mnSS = (int)round(($sunsetHr - $hrSS) * 60);
            $resultSS = AstroCalculator::calculate($yr, $mo, $dy, $hrSS, $mnSS, $utcOff, $lat, $lon);
            $ssNum = (int)($resultSS['tk']['tithi']['num'] ?? 0);
            $ssPaksha = $resultSS['tk']['tithi']['paksha'] ?? '';

            // Get Midnight Tithi (using approx midnight)
            $midnightHr = $sunsetHr + 6.0;
            if ($midnightHr >= 24.0) $midnightHr -= 24.0; // Wait, actually it's next day's 00:00 local
            // Better: use JD for midnight local time
            $jdMidnight = AstroCalculator::julianDay($yr, $mo, $dy, 24.0 - $utcOff);
            $tkMid = AstroCalculator::computeTithiKarana($jdMidnight);
            $midNum = $tkMid['tithi']['num'];
            $midPaksha = $tkMid['tithi']['paksha'];

            // Lunar Month
            $jdRise = AstroCalculator::julianDay($yr, $mo, $dy, $sunriseHr - $utcOff);
            $cacheKey = "$yr-$mo-$dy";
            if (!isset($masaCache[$cacheKey])) {
                $masaCache[$cacheKey] = self::getMasaFromNewMoon($jdRise);
            }
            $masa = $masaCache[$cacheKey];

            // ── Festival Rules ──
            
            // Janmashtami: Bhadrapada Krishna 8 at Midnight
            if ($masa === 'Bhadrapada' && $midPaksha === 'Krishna' && $midNum === 8) {
                // Also check Rohini at midnight (Nakshatra 3)
                $ayan = AstroCalculator::lahiriAyanamsa($jdMidnight);
                $moonLon = AstroCalculator::moonLongitude($jdMidnight);
                $sider = AstroCalculator::n360($moonLon - $ayan);
                $nakIdx = (int)floor($sider / (360/27));
                $name = ($nakIdx === 3) ? 'Krishna Janmashtami (Rohini)' : 'Krishna Janmashtami';
                $festivals[] = ['date' => $dateStr, 'name' => $name, 'name_hi' => 'कृष्ण जन्माष्टमी', 'type' => 'festival', 'category' => 'jayanti', 'tithi' => 'भाद्रपद कृष्ण अष्टमी', 'icon' => '🦚', 'details' => 'Midnight Ashtami present.'];
            }

            // Pradosh Vrat: Trayodashi (13) at Sunset
            if ($ssNum === 13) {
                $festivals[] = ['date' => $dateStr, 'name' => 'Pradosh Vrat', 'name_hi' => 'प्रदोष व्रत', 'type' => 'vrat', 'category' => 'vrat', 'tithi' => $ssPaksha.' त्रयोदशी', 'icon' => '🙏', 'details' => 'Trayodashi present at sunset.'];
            }

            // Shivratri / Mahashivratri: Krishna 14 at Midnight
            if ($midPaksha === 'Krishna' && $midNum === 14) {
                $name = ($masa === 'Phalguna') ? 'Maha Shivratri' : 'Masik Shivratri';
                $nameHi = ($masa === 'Phalguna') ? 'महाशिवरात्रि' : 'मासिक शिवरात्रि';
                $icon = ($masa === 'Phalguna') ? '🔱' : '🙏';
                $festivals[] = ['date' => $dateStr, 'name' => $name, 'name_hi' => $nameHi, 'type' => 'festival', 'category' => 'vrat', 'tithi' => $masa.' कृष्ण चतुर्दशी', 'icon' => $icon, 'details' => 'Chaturdashi present at midnight.'];
            }

            // Tithi-based festivals (checking Sunrise tithi for standard ones)
            if ($srNum === 1 && $srPaksha === 'Shukla' && $masa === 'Chaitra') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Navratri Begins / Ugadi / Gudi Padwa', 'name_hi' => 'नवरात्रि आरंभ / उगादि / गुड़ी पड़वा', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'चैत्र शुक्ल प्रतिपदा', 'icon' => '🚩', 'details' => ''];
            }
            if ($srNum === 9 && $srPaksha === 'Shukla' && $masa === 'Chaitra') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Rama Navami', 'name_hi' => 'राम नवमी', 'type' => 'festival', 'category' => 'jayanti', 'tithi' => 'चैत्र शुक्ल नवमी', 'icon' => '🏹', 'details' => ''];
            }
            if ($srNum === 1 && $srPaksha === 'Shukla' && $masa === 'Ashwin') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Sharad Navratri Begins', 'name_hi' => 'शारदीय नवरात्रि आरंभ', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'आश्विन शुक्ल प्रतिपदा', 'icon' => '🌺', 'details' => ''];
            }
            if ($srNum === 9 && $srPaksha === 'Shukla' && $masa === 'Ashwin') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Maha Navami', 'name_hi' => 'महानवमी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'आश्विन शुक्ल नवमी', 'icon' => '🔱', 'details' => ''];
            }
            if ($srNum === 10 && $srPaksha === 'Shukla' && $masa === 'Ashwin') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Vijaya Dashami (Dussehra)', 'name_hi' => 'विजयादशमी (दशहरा)', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'आश्विन शुक्ल दशमी', 'icon' => '🏹', 'details' => ''];
            }
            if ($srNum === 15 && $srPaksha === 'Krishna' && $masa === 'Kartik') {
                $amavasyaEnd = AstroCalculator::findTithiEnd($jdRise, $utcOff);
                $festivals[] = ['date' => $dateStr, 'name' => 'Diwali', 'name_hi' => 'दीपावली', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक अमावस्या', 'icon' => '🪔', 'details' => 'Amavasya End: '.$amavasyaEnd];
            }
            if ($srNum === 15 && $srPaksha === 'Shukla' && $masa === 'Phalguna') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Holi (Holika Dahan)', 'name_hi' => 'होली (होलिका दहन)', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'फाल्गुन पूर्णिमा', 'icon' => '🔥', 'details' => 'Next day is Dhuleti'];
            }
            if ($srNum === 3 && $srPaksha === 'Shukla' && $masa === 'Vaishakha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Akshaya Tritiya', 'name_hi' => 'अक्षय तृतीया', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'वैशाख शुक्ल तृतीया', 'icon' => '🪙', 'details' => ''];
            }
            if ($srNum === 15 && $srPaksha === 'Shukla' && $masa === 'Ashadha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Guru Purnima', 'name_hi' => 'गुरु पूर्णिमा', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'आषाढ़ पूर्णिमा', 'icon' => '📿', 'details' => ''];
            }
            if ($srNum === 15 && $srPaksha === 'Shukla' && $masa === 'Shravana') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Raksha Bandhan', 'name_hi' => 'रक्षाबंधन', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'श्रावण पूर्णिमा', 'icon' => '🧵', 'details' => ''];
            }
            if ($srNum === 4 && $srPaksha === 'Shukla' && $masa === 'Bhadrapada') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Ganesh Chaturthi', 'name_hi' => 'गणेश चतुर्थी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'भाद्रपद शुक्ल चतुर्थी', 'icon' => '🐘', 'details' => ''];
            }
            if ($srNum === 14 && $srPaksha === 'Shukla' && $masa === 'Bhadrapada') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Anant Chaturdashi', 'name_hi' => 'अनंत चतुर्दशी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'भाद्रपद शुक्ल चतुर्दशी', 'icon' => '🕉', 'details' => ''];
            }
            if ($srNum === 4 && $srPaksha === 'Krishna' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Karva Chauth', 'name_hi' => 'करवा चौथ', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक कृष्ण चतुर्थी', 'icon' => '🌙', 'details' => ''];
            }
            if ($srNum === 8 && $srPaksha === 'Krishna' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Ahoi Ashtami', 'name_hi' => 'अहोई अष्टमी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक कृष्ण अष्टमी', 'icon' => '🌟', 'details' => ''];
            }
            if ($srNum === 13 && $srPaksha === 'Krishna' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Dhanteras', 'name_hi' => 'धनतेरस', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक कृष्ण त्रयोदशी', 'icon' => '💰', 'details' => ''];
            }
            if ($srNum === 14 && $srPaksha === 'Krishna' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Naraka Chaturdashi', 'name_hi' => 'नरक चतुर्दशी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक कृष्ण चतुर्दशी', 'icon' => '🪔', 'details' => ''];
            }
            if ($srNum === 2 && $srPaksha === 'Shukla' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Bhai Dooj', 'name_hi' => 'भाई दूज', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक शुक्ल द्वितीया', 'icon' => '👫', 'details' => ''];
            }
            if ($srNum === 6 && $srPaksha === 'Shukla' && $masa === 'Kartik') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Chhath Puja', 'name_hi' => 'छठ पूजा', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'कार्तिक शुक्ल षष्ठी', 'icon' => '🌅', 'details' => ''];
            }
            if ($srNum === 15 && $srPaksha === 'Shukla' && $masa === 'Jyeshtha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Vat Purnima', 'name_hi' => 'वट पूर्णिमा', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'ज्येष्ठ पूर्णिमा', 'icon' => '🌳', 'details' => ''];
            }
            if ($srNum === 3 && $srPaksha === 'Shukla' && $masa === 'Shravana') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Hariyali Teej', 'name_hi' => 'हरियाली तीज', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'श्रावण शुक्ल तृतीया', 'icon' => '🌿', 'details' => ''];
            }
            if ($srNum === 3 && $srPaksha === 'Shukla' && $masa === 'Bhadrapada') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Hartalika Teej', 'name_hi' => 'हरतालिका तीज', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'भाद्रपद शुक्ल तृतीया', 'icon' => '🌸', 'details' => ''];
            }
            if ($srNum === 4 && $srPaksha === 'Krishna' && $masa === 'Magha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Sakat Chauth', 'name_hi' => 'सकट चौथ', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'माघ कृष्ण चतुर्थी', 'icon' => '🐘', 'details' => ''];
            }
            if ($srNum === 15 && $srPaksha === 'Krishna' && $masa === 'Magha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Mauni Amavasya', 'name_hi' => 'मौनी अमावस्या', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'माघ अमावस्या', 'icon' => '🤫', 'details' => ''];
            }
            if ($srNum === 5 && $srPaksha === 'Shukla' && $masa === 'Magha') {
                $festivals[] = ['date' => $dateStr, 'name' => 'Basant Panchami', 'name_hi' => 'बसंत पंचमी', 'type' => 'festival', 'category' => 'festival', 'tithi' => 'माघ शुक्ल पंचमी', 'icon' => '🌼', 'details' => ''];
            }
        }
        
        usort($festivals, fn($a, $b) => $a['date'] <=> $b['date']);
        return ['festivals'=>$festivals, 'count'=>count($festivals), 'year'=>$year];
    }
    private static function getEkadashiDetails(string $name): string
    {
        $details = [
            'Papmochani Ekadashi'   => 'पापमोचनी एकादशी: चैत्र कृष्ण 11।',
            'Kamada Ekadashi'       => 'कामदा एकादशी: चैत्र शुक्ल 11। सभी मनोकामनाएं पूर्ण।',
            'Varuthini Ekadashi'    => 'वरूथिनी एकादशी: वैशाख कृष्ण 11।',
            'Mohini Ekadashi'       => 'मोहिनी एकादशी: वैशाख शुक्ल 11।',
            'Apara Ekadashi'        => 'अपरा एकादशी: ज्येष्ठ कृष्ण 11।',
            'Nirjala Ekadashi'      => 'निर्जला एकादशी: ज्येष्ठ शुक्ल 11 — सर्वाधिक कठोर। जल भी नहीं।',
            'Yogini Ekadashi'       => 'योगिनी एकादशी: आषाढ़ कृष्ण 11।',
            'Devshayani Ekadashi'   => 'देवशयनी एकादशी: आषाढ़ शुक्ल 11 — विष्णु योग निद्रा। चातुर्मास आरंभ।',
            'Kamika Ekadashi'       => 'कामिका एकादशी: श्रावण कृष्ण 11।',
            'Putrada Ekadashi'      => 'पुत्रदा एकादशी: श्रावण शुक्ल 11।',
            'Aja Ekadashi'          => 'अजा एकादशी: भाद्रपद कृष्ण 11।',
            'Parsva Ekadashi'       => 'पार्श्व एकादशी: भाद्रपद शुक्ल 11।',
            'Indira Ekadashi'       => 'इंदिरा एकादशी: आश्विन कृष्ण 11 — पितृ पक्ष में।',
            'Papankusha Ekadashi'   => 'पापांकुशा एकादशी: आश्विन शुक्ल 11।',
            'Rama Ekadashi'         => 'रमा एकादशी: कार्तिक कृष्ण 11।',
            'Prabodhini Ekadashi'   => 'प्रबोधिनी एकादशी: कार्तिक शुक्ल 11 — विष्णु जागते हैं।',
            'Utpanna Ekadashi'      => 'उत्पन्ना एकादशी: मार्गशीर्ष कृष्ण 11।',
            'Mokshada Ekadashi'     => 'मोक्षदा एकादशी: मार्गशीर्ष शुक्ल 11 — गीता जयंती।',
            'Saphala Ekadashi'      => 'सफला एकादशी: पौष कृष्ण 11।',
            'Shattila Ekadashi'     => 'षट्तिला एकादशी: माघ कृष्ण 11।',
            'Jaya Ekadashi'         => 'जया एकादशी: माघ शुक्ल 11।',
            'Vijaya Ekadashi'       => 'विजया एकादशी: फाल्गुन कृष्ण 11।',
            'Amalaki Ekadashi'      => 'आमलकी एकादशी: फाल्गुन शुक्ल 11।',
        ];
        return $details[$name] ?? '';
    }

    // ══════════════════════════════════════════════════════════
    //  renderHtml (unchanged from v1 — works with existing blade)
    // ══════════════════════════════════════════════════════════
    public static function renderHtml(array $festivals, string $activeCategory = 'all'): string
    {
        if (empty($festivals)) {
            return '<div style="padding:48px;text-align:center;color:#64748b;">कोई उत्सव नहीं मिला।</div>';
        }

        $filtered = ($activeCategory === 'all') ? $festivals : array_values(array_filter(
            $festivals,
            fn($f) => ($f['category'] ?? '') === $activeCategory
        ));

        if (empty($filtered)) {
            return '<div style="padding:52px;text-align:center;">इस श्रेणी में कोई उत्सव नहीं।</div>';
        }

        // Delegate to JS card renderer — just return raw JSON for the blade to use
        // The blade/JS does the actual rendering via _renderCards()
        $count = count($filtered);
        return "<!-- {$count} festivals loaded -->";
    }
}