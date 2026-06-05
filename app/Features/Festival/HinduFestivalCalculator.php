<?php

namespace App\Features\Festival;

use App\Features\Planetary\AstroCalculator;

/**
 * HinduFestivalCalculator — Comprehensive Hindu Festival & Vrat Calendar
 *
 * Mathematical Basis:
 *   • Jean Meeus — "Astronomical Algorithms" (2nd Ed.) — Sun/Moon positions
 *   • B.V. Raman   — "Hindu Predictive Astrology", "Muhurtha"
 *   • Brihat Parashara Hora Shastra (BPHS) — Tithi/Karana/Nakshatra rules
 *   • Lahiri Ayanamsa (Rashtriya Panchang standard)
 *   • Surya Siddhanta — Solar Sankranti calculations
 */
class HinduFestivalCalculator
{
    private const MASA_FROM_SUN_SIGN = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
    private const DOW_NAMES_HI = ['रवि', 'सोम', 'मंगल', 'बुध', 'गुरु', 'शुक्र', 'शनि'];
    private const DOW_NAMES_EN = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    public const MASA_NAMES = [
        'Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
        'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna',
    ];

    public static function calculateYear(
        int $year, float $lat, float $lon, float $utcOff
    ): array {
        $festivals = [];

        $ekadashis = AstroCalculator::getEkadashiYear($year, $lat, $lon, $utcOff);
        foreach ($ekadashis as $ek) {
            $festivals[] = [
                'date'         => $ek['date'],
                'name'         => $ek['name'],
                'name_hi'      => $ek['nameHi'] ?? '',
                'type'         => 'vrat',
                'category'     => 'ekadashi',
                'tithi'        => ($ek['paksha'] ?? '') . ' एकादशी',
                'paksha'       => $ek['paksha'] ?? '',
                'tithi_num'    => 11,
                'masa'         => $ek['vedMonth'] ?? '',
                'significance' => $ek['significance'] ?? '',
                'rituals'      => $ek['rituals'] ?? [],
                'mantra'       => $ek['mantra'] ?? 'ॐ नमो भगवते वासुदेवाय',
                'auspTime'     => $ek['auspTime'] ?? '',
                'details'      => self::getEkadashiDetails($ek['name'] ?? ''),
                'icon'         => '🌸',
            ];
        }

        $startDate   = new \DateTime("{$year}-01-01");
        $prevSunSign = -1;
        $prevDateStr = null;
        $prevNum     = 0;
        $prevPaksha  = '';
        $prevMasaName= 'Unknown';

        for ($i = 0; $i < 366; $i++) {
            $dt = clone $startDate;
            $dt->modify("+{$i} days");
            if ((int)$dt->format('Y') !== $year) break;

            $dateStr = $dt->format('Y-m-d');
            $yr  = (int)$dt->format('Y');
            $mo  = (int)$dt->format('m');
            $dy  = (int)$dt->format('d');
            $dow = (int)$dt->format('w');

            $approx    = AstroCalculator::calculate($yr, $mo, $dy, 6, 0, $utcOff, $lat, $lon);
            $sunriseHr = (!$approx['ss']['polar'] && $approx['ss']['rise'] !== null)
                       ? $approx['ss']['rise'] : 6.0;
            $hr = (int)floor($sunriseHr);
            $mn = (int)round(($sunriseHr - $hr) * 60);
            $result = AstroCalculator::calculate($yr, $mo, $dy, $hr, $mn, $utcOff, $lat, $lon);

            $tk      = $result['tk'] ?? [];
            $tithi   = $tk['tithi'] ?? [];
            $paksha  = $tithi['paksha'] ?? '';
            $num     = (int)($tithi['num'] ?? 0);

            $sunSider    = $result['planets']['sun']['sider'] ?? 0;
            $curSunSign  = (int)floor($sunSider / 30);

            // ── Lunar month (Purnimanta · North-Indian) ──────────────────────
            // Named from the Sun's sidereal sign at the NEW MOON that began this
            // lunation (invariant for the whole month) — not the sun-sign on the
            // day, which drifts across the Sankranti and mis-named festivals by
            // a whole month. Shukla paksha keeps the amanta month; Krishna paksha
            // takes the next month's name (Purnimanta convention).
            $elong     = $result['tk']['elong'] ?? 0.0;
            $jdRef     = $result['jd'] ?? AstroCalculator::julianDay($yr, $mo, $dy, 6.0 - $utcOff);
            $masaIdx   = AstroCalculator::purnimantaMasaIdx($jdRef, $elong, $paksha);
            $masaName  = AstroCalculator::MASA_NAMES[$masaIdx] ?? 'Unknown';

            $moonSider = $result['planets']['moon']['sider'] ?? 0;
            $nakIdx    = (int)floor($moonSider / (360/27));

            $ssRise = (!$result['ss']['polar'] && $result['ss']['rise'] !== null)
                    ? AstroCalculator::decToHMS($result['ss']['rise']) : '';
            $ssSet  = (!$result['ss']['polar'] && $result['ss']['set']  !== null)
                    ? AstroCalculator::decToHMS($result['ss']['set'])  : '';

            $skippedTithis = [];
            if ($prevNum > 0 && $prevPaksha === $paksha) {
                $diff = $num - $prevNum;
                if ($diff > 1) {
                    for ($k = 1; $k < $diff; $k++) $skippedTithis[] = $prevNum + $k;
                }
            }

            if ($num === 11) goto updatePrev;

            if (($num === 15 && $paksha === 'Shukla')
                || (in_array(15, $skippedTithis) && $prevPaksha === 'Shukla')) {

                $useDate = in_array(15, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa = in_array(15, $skippedTithis) ? $prevMasaName : $masaName;
                $base = ['date'=>$useDate,'tithi'=>'शुक्ल पूर्णिमा','paksha'=>'Shukla',
                         'tithi_num'=>15,'masa'=>$useMasa,'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌕'];

                $festivals[] = array_merge($base, [
                    'name'         => 'Purnima',
                    'name_hi'      => 'पूर्णिमा',
                    'type'         => 'vrat',
                    'category'     => 'purnima',
                    'significance' => 'पूर्णिमा — पूर्ण चंद्रमा तिथि। पितृ तर्पण, विष्णु पूजा, चंद्र अर्घ्य के लिए श्रेष्ठ। B.V. Raman के अनुसार यह चंद्र ऊर्जा की पराकाष्ठा है।',
                    'rituals'      => ['प्रातःकाल स्नान (सप्त नदी स्मरण)','विष्णु-लक्ष्मी पूजा','चंद्र अर्घ्य','दान-दक्षिणा','पितृ तर्पण'],
                    'mantra'       => 'ॐ सोमाय नमः · ॐ नमो भगवते वासुदेवाय',
                    'details'      => 'पूर्णिमा तब होती है जब चंद्रमा सूर्य से ठीक 180° दूर हो (Meeus Ch.47 elongation = 180°)। सभी 12 पूर्णिमाओं के अलग नाम हैं।',
                ]);

                $festivals[] = array_merge($base, [
                    'name'         => 'Satyanarayan Vrat',
                    'name_hi'      => 'सत्यनारायण व्रत',
                    'type'         => 'vrat',
                    'category'     => 'satyanarayan',
                    'icon'         => '🪷',
                    'significance' => 'भगवान विष्णु का सर्वप्रिय पूर्णिमा व्रत। स्कंद पुराण (रेवा खंड) में वर्णित — धर्म, अर्थ, काम, मोक्ष चारों पुरुषार्थ देता है।',
                    'rituals'      => ['संध्या तक उपवास','सत्यनारायण कथा','पंचामृत अभिषेक','तुलसी, पीले पुष्प, केला अर्पण','शीरा-पंचामृत प्रसाद वितरण'],
                    'mantra'       => 'ॐ नमो भगवते वासुदेवाय · ॐ श्री सत्यनारायणाय नमः',
                    'details'      => 'स्कंद पुराण में वर्णित — नारद मुनि ने मनुष्यों के दुःख देखकर विष्णु से उपाय पूछा, विष्णु ने यह व्रत बताया।',
                ]);

                switch ($useMasa) {
                    case 'Phalguna':
                        $prevDt = (new \DateTime($useDate))->modify('-1 day');
                        $festivals[] = ['date'=>$prevDt->format('Y-m-d'),'name'=>'Holika Dahan',
                            'name_hi'=>'होलिका दहन','type'=>'festival','category'=>'festival',
                            'tithi'=>'फाल्गुन पूर्णिमा संध्या','masa'=>$useMasa,
                            'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🔥',
                            'significance'=>'होलिका दहन — प्रह्लाद की भक्ति की विजय। होलिका (दानव कन्या) का अग्नि में भस्म होना। B.V. Raman: उत्तरायण के वसंत अग्नि उत्सव का प्रतीक।',
                            'rituals'=>['होलिका का अलाव','3/7 बार परिक्रमा','नारियल और नई फसल अर्पण','नृसिंह कवच','भक्ति गीत'],
                            'mantra'=>'ॐ नमो भगवते नरसिंहाय नमः',
                            'details'=>'होलिका दहन: हिरण्यकशिपु की बहन होलिका को अग्नि वरदान था, परंतु प्रह्लाद की भक्ति ने उसे बचाया। Phalguna Purnima की पूर्व संध्या।'];
                        $festivals[] = array_merge($base, ['name'=>'Holi','name_hi'=>'होली',
                            'type'=>'festival','category'=>'festival','icon'=>'🎨',
                            'significance'=>'रंगों का त्योहार — बुराई पर अच्छाई की विजय। वृंदावन में श्रीकृष्ण-गोपियों की रासलीला का उत्सव।',
                            'rituals'=>['प्राकृतिक गुलाल से खेलें','अबीर-गुलाल','मंदिर दर्शन','गुजिया-ठंडाई','क्षमा और मेलमिलाप'],
                            'mantra'=>'राधे कृष्ण राधे कृष्ण · होली खेले रघुवीरा अवध में',
                            'details'=>'फाल्गुन पूर्णिमा पर होली। रंग वसंत के फूलों का प्रतीक। मथुरा-वृंदावन में लट्ठमार और फूलवाली होली।']);
                        break;
                    case 'Chaitra':
                        $festivals[] = array_merge($base, ['name'=>'Hanuman Jayanti',
                            'name_hi'=>'हनुमान जयंती','type'=>'festival','category'=>'jayanti',
                            'tithi'=>'चैत्र पूर्णिमा','icon'=>'🐒',
                            'significance'=>'चैत्र पूर्णिमा, मूल नक्षत्र में भगवान हनुमान का जन्म — अंजना और केसरी के पुत्र, वायुपुत्र।',
                            'rituals'=>['दिनभर उपवास','हनुमान चालीसा 108 बार','सिंदूर-तेल अर्पण','सुंदरकांड पाठ','जासमीन गजरा'],
                            'mantra'=>'ॐ हं हनुमते नमः · मनोजवं मारुततुल्यवेगम्',
                            'details'=>'हनुमान जन्म: चैत्र पूर्णिमा, सूर्योदय काल, मूल नक्षत्र।']);
                        break;
                    case 'Vaishakha':
                        $festivals[] = array_merge($base, ['name'=>'Buddha Purnima',
                            'name_hi'=>'बुद्ध पूर्णिमा','type'=>'festival','category'=>'festival',
                            'tithi'=>'वैशाख पूर्णिमा','icon'=>'☸',
                            'significance'=>'त्रिपुण्य दिवस — गौतम बुद्ध का जन्म, बोधि प्राप्ति, महापरिनिर्वाण।',
                            'rituals'=>['बौद्ध मंदिर दर्शन','ध्यान','मौन','निर्धनों को भोजन','श्वेत पुष्प'],
                            'mantra'=>'बुद्धं शरणं गच्छामि · धर्मं शरणं गच्छामि',
                            'details'=>'वैशाख पूर्णिमा बौद्ध धर्म का पवित्रतम दिन।']);
                        break;
                    case 'Ashadha':
                        $festivals[] = array_merge($base, ['name'=>'Guru Purnima',
                            'name_hi'=>'गुरु पूर्णिमा','type'=>'festival','category'=>'festival',
                            'tithi'=>'आषाढ़ पूर्णिमा','icon'=>'📿',
                            'significance'=>'महर्षि वेद व्यास जयंती — वेद, महाभारत, पुराणों के संकलनकर्ता।',
                            'rituals'=>['व्यास पूजा','गुरु गीता पाठ','पीले पुष्प-मिठाई','नई साधना का आरंभ'],
                            'mantra'=>'गुरुर्ब्रह्मा गुरुर्विष्णु गुरुर्देवो महेश्वरः',
                            'details'=>'आषाढ़ पूर्णिमा = व्यास पूर्णिमा।']);
                        break;
                    case 'Shravana':
                        $festivals[] = array_merge($base, ['name'=>'Rakshabandhan',
                            'name_hi'=>'रक्षाबंधन','type'=>'festival','category'=>'festival',
                            'tithi'=>'श्रावण पूर्णिमा','icon'=>'🧵',
                            'significance'=>'भाई-बहन के पवित्र बंधन का महापर्व।',
                            'rituals'=>['राखी बांधना (शुभ मुहूर्त में)','भाई का तिलक-आरती','बहन को उपहार'],
                            'mantra'=>'येन बद्धो बली राजा दानवेन्द्रो महाबलः',
                            'details'=>'रक्षा मंत्र इंद्राणी द्वारा वृत्रासुर युद्ध से पहले इंद्र को सूत्र बांधने की कथा से।']);
                        break;
                    case 'Ashwin':
                        $festivals[] = array_merge($base, ['name'=>'Sharad Purnima (Kojagari)',
                            'name_hi'=>'शरद पूर्णिमा — कोजागरी','type'=>'festival','category'=>'festival',
                            'tithi'=>'आश्विन पूर्णिमा','icon'=>'🌕',
                            'significance'=>'वर्ष की सर्वाधिक प्रकाशमान पूर्णिमा — श्रीकृष्ण ने इसी रात महारास रचा।',
                            'rituals'=>['रात्रि जागरण (कोजागरी)','खीर बनाकर चंद्रमा को अर्पण','लक्ष्मी पूजा'],
                            'mantra'=>'ॐ श्रीं महालक्ष्म्यै नमः',
                            'details'=>'आश्विन पूर्णिमा पर चंद्रमा वृषभ में (उच्च राशि) और पृथ्वी के सबसे निकट।']);
                        break;
                    case 'Kartik':
                        $festivals[] = array_merge($base, ['name'=>'Kartik Purnima (Dev Diwali)',
                            'name_hi'=>'कार्तिक पूर्णिमा — देव दीपावली','type'=>'festival',
                            'category'=>'festival','tithi'=>'कार्तिक पूर्णिमा','icon'=>'🪔',
                            'significance'=>'देवता दीपावली मनाते हैं — त्रिपुरी पूर्णिमा। शिव ने त्रिपुरासुर का वध किया।',
                            'rituals'=>['वाराणसी में 1000 दीप दान','शिव पूजा','कार्तिक दीप दान'],
                            'mantra'=>'ॐ नमः शिवाय · ॐ श्रीं महालक्ष्म्यै नमः',
                            'details'=>'कार्तिक पूर्णिमा पर कार्तिक व्रत का समापन।']);
                        break;
                    case 'Margashirsha':
                        $festivals[] = array_merge($base, ['name'=>'Dattatreya Jayanti',
                            'name_hi'=>'दत्तात्रेय जयंती','type'=>'festival','category'=>'jayanti',
                            'tithi'=>'मार्गशीर्ष पूर्णिमा','icon'=>'✨',
                            'significance'=>'भगवान दत्तात्रेय — ब्रह्मा-विष्णु-महेश के त्रिमूर्ति अवतार।',
                            'rituals'=>['गंगा स्नान','दत्त पूजा','गुरु चरित्र पाठ'],
                            'mantra'=>'ॐ द्रां दत्तात्रेयाय नमः',
                            'details'=>'मार्गशीर्ष पूर्णिमा = दत्त जयंती।']);
                        break;
                    case 'Pausha':
                        $festivals[] = array_merge($base, ['name'=>'Pausha Purnima',
                            'name_hi'=>'पौष पूर्णिमा','type'=>'vrat','category'=>'purnima','icon'=>'🌕',
                            'significance'=>'पवित्र शीतकालीन पूर्णिमा — प्रयागराज कुंभ मेला का कल्पवास आरंभ।',
                            'rituals'=>['त्रिवेणी संगम स्नान','विष्णु पूजा','तिल दान'],
                            'mantra'=>'ॐ नमो भगवते वासुदेवाय',
                            'details'=>'स्कंद पुराण: पौष पूर्णिमा पर प्रयाग स्नान सभी तीर्थों के बराबर।']);
                        break;
                    case 'Magha':
                        $festivals[] = array_merge($base, ['name'=>'Maghi Purnima',
                            'name_hi'=>'माघी पूर्णिमा','type'=>'festival','category'=>'purnima','icon'=>'🌕',
                            'significance'=>'माघ मेला / कुंभ मेला का सर्वाधिक पवित्र स्नान दिवस।',
                            'rituals'=>['त्रिवेणी संगम स्नान','तिल दान','विष्णु पूजा'],
                            'mantra'=>'ॐ माघस्नानं करिष्यामि सर्वपापहरं शुभम्',
                            'details'=>'माघ पूर्णिमा पर चंद्रमा कर्क राशि में उच्च का।']);
                        break;
                    case 'Bhadrapada':
                        $festivals[] = array_merge($base, ['name'=>'Bhadra Purnima',
                            'name_hi'=>'भाद्रपद पूर्णिमा','type'=>'vrat','category'=>'purnima','icon'=>'🌕',
                            'significance'=>'भाद्रपद पूर्णिमा — मत्स्यावतार पूजा और पितृ तर्पण।',
                            'rituals'=>['पवित्र स्नान','विष्णु पूजा','पितृ तर्पण'],
                            'mantra'=>'ॐ नमो भगवते वासुदेवाय',
                            'details'=>'']);
                        break;
                }
            }

            if (($num === 15 && $paksha === 'Krishna')
                || (in_array(15, $skippedTithis) && $prevPaksha === 'Krishna')) {

                $useDate = in_array(15, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa = in_array(15, $skippedTithis) ? $prevMasaName : $masaName;
                $base = ['date'=>$useDate,'tithi'=>'कृष्ण अमावस्या','paksha'=>'Krishna',
                         'tithi_num'=>15,'masa'=>$useMasa,'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌑'];

                $festivals[] = array_merge($base, [
                    'name'         => 'Amavasya',
                    'name_hi'      => 'अमावस्या',
                    'type'         => 'vrat',
                    'category'     => 'amavasya',
                    'significance' => 'अमावस्या — सूर्य-चंद्र का युति दिन। पितृ तर्पण का सर्वोत्तम दिन।',
                    'rituals'      => ['पितृ तर्पण (काले तिल और जल से)','श्राद्ध','उपवास','कौवे-गाय-ब्राह्मण भोज'],
                    'mantra'       => 'ॐ पितृ देवतायै नमः · तिलोदकं स्वधा नमः पितृभ्यः',
                    'details'      => 'अमावस्या: Meeus Ch.47 के अनुसार Moon–Sun elongation = 0°।',
                ]);

                if ($useMasa === 'Kartik') {
                    $festivals[] = array_merge($base, ['name'=>'Diwali (Deepawali)',
                        'name_hi'=>'दीपावली','type'=>'festival','category'=>'festival','icon'=>'🪔',
                        'significance'=>'दीपों का महोत्सव — देवी लक्ष्मी का पृथ्वी आगमन। भगवान राम का अयोध्या प्रत्यागमन।',
                        'rituals'=>['108 दीपक जलाना','लक्ष्मी-गणेश पूजा','रंगोली','चोपड़ा पूजन','पटाखे'],
                        'mantra'=>'ॐ श्रीं ह्रीं श्रीं महालक्ष्म्यै नमः',
                        'details'=>'दीपावली = कार्तिक अमावस्या। पांच दिवसीय पर्व।']);
                }
                if ($useMasa === 'Ashwin') {
                    $festivals[] = array_merge($base, ['name'=>'Mahalaya Amavasya (Sarva Pitru)',
                        'name_hi'=>'महालया अमावस्या — सर्व पितृ','type'=>'festival',
                        'category'=>'amavasya','icon'=>'🙏',
                        'significance'=>'पितृ पक्ष का अंतिम और सर्वाधिक महत्वपूर्ण दिन। सभी पूर्वजों को तर्पण।',
                        'rituals'=>['सर्व पितृ तर्पण','पिंड दान','ब्राह्मण भोज','दुर्गा आह्वान'],
                        'mantra'=>'ॐ पितृ तर्पयामि स्वाहा',
                        'details'=>'महालया अमावस्या 16-दिवसीय पितृ पक्ष का समापन करती है।']);
                }
                if ($useMasa === 'Vaishakha') {
                    $festivals[] = array_merge($base, ['name'=>'Vaishakha Amavasya (Shani Jayanti)',
                        'name_hi'=>'वैशाख अमावस्या — शनि जयंती','type'=>'festival',
                        'category'=>'amavasya','icon'=>'♄',
                        'significance'=>'शनि देव का जन्मदिन। शनि पूजा से शनि दोष और साढ़ेसाती का शमन।',
                        'rituals'=>['शनि मंदिर दर्शन','सरसों तेल का दीपक','काले तिल दान'],
                        'mantra'=>'ॐ शं शनैश्चराय नमः',
                        'details'=>'वैशाख अमावस्या = शनि जयंती।']);
                }
            }

            if ($num === 13 || in_array(13, $skippedTithis)) {
                $useDate   = in_array(13, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa   = in_array(13, $skippedTithis) ? $prevMasaName : $masaName;
                $usePaksha = in_array(13, $skippedTithis) ? $prevPaksha : $paksha;
                $useDow    = (int)(new \DateTime($useDate))->format('w');
                $isSomavar = ($useDow === 1);
                $isShani   = ($useDow === 6);
                $prefix    = $isSomavar ? 'Soma ' : ($isShani ? 'Shani ' : '');
                $prefixHi  = $isSomavar ? 'सोम ' : ($isShani ? 'शनि ' : '');
                $pakNameHi = $usePaksha === 'Shukla' ? 'शुक्ल' : 'कृष्ण';

                $festivals[] = [
                    'date'         => $useDate,
                    'name'         => "{$prefix}" . ($usePaksha === 'Shukla' ? 'Shukla' : 'Krishna') . " Trayodashi (Pradosh Vrat)",
                    'name_hi'      => "{$prefixHi}{$pakNameHi} त्रयोदशी — प्रदोष व्रत",
                    'type'         => 'vrat',
                    'category'     => 'pradosh',
                    'tithi'        => "{$pakNameHi} त्रयोदशी",
                    'paksha'       => $usePaksha,
                    'tithi_num'    => 13,
                    'masa'         => $useMasa,
                    'sunrise'      => $ssRise,
                    'sunset'       => $ssSet,
                    'icon'         => '🔱',
                    'significance' => 'प्रदोष व्रत — सूर्यास्त के डेढ़ घंटे (प्रदोष काल) में शिव-पार्वती पूजा।'
                                    . ($isSomavar ? ' सोम प्रदोष अत्यंत शक्तिशाली।' : '')
                                    . ($isShani  ? ' शनि प्रदोष — शनि दोष निवारण।' : ''),
                    'rituals'      => [
                        'सूर्योदय से प्रदोष पूजा तक उपवास',
                        'शिव अभिषेक — दूध, शहद, दही, घृत',
                        'प्रदोष काल में तिल का दीपक',
                        'बिल्वपत्र, धतूरा, सफेद पुष्प',
                        'महामृत्युंजय जप',
                        $isSomavar ? 'सोमवार प्रदोष पर रुद्राभिषेक' : ($isShani ? 'शनि को सरसों तेल, काले तिल' : 'शिव सहस्रनाम'),
                    ],
                    'mantra'       => 'ॐ नमः शिवाय · ॐ त्र्यम्बकं यजामहे',
                    'details'      => 'प्रदोष = 13वीं तिथि। B.V. Raman: प्रदोष काल में शिव नटराज तांडव करते हैं।<br><br><strong>प्रदोष पूजन विधि:</strong><br>1. सूर्यास्त से 1.5 घंटे पहले स्नान करें।<br>2. शिवलिंग पर दूध, दही, शहद, घृत, गंगाजल से अभिषेक।<br>3. बिल्वपत्र (तीन पत्ती एक साथ) अर्पण।<br>4. धतूरा, आकड़े के पुष्प, सफेद चंदन।<br>5. ॐ नमः शिवाय का 108 बार जप।<br>6. प्रदोष काल में दीपक जलाएं।<br>7. महामृत्युंजय मंत्र का पाठ।',
                    'vidhiTitle'   => 'प्रदोष पूजन विधि',
                ];
            }

            if (($num === 14 && $paksha === 'Krishna')
                || (in_array(14, $skippedTithis) && ($prevPaksha === 'Krishna'))) {

                $useDate = in_array(14, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa = in_array(14, $skippedTithis) ? $prevMasaName : $masaName;
                // Maha Shivratri = Phalguna Krishna Chaturdashi (Purnimanta).
                // Magha Krishna 14 is the monthly Masik Shivratri, not Maha.
                $isMaha  = ($useMasa === 'Phalguna');

                $festivals[] = [
                    'date'         => $useDate,
                    'name'         => $isMaha ? 'Maha Shivratri' : 'Masik Shivratri',
                    'name_hi'      => $isMaha ? 'महा शिवरात्रि' : 'मासिक शिवरात्रि',
                    'type'         => $isMaha ? 'festival' : 'vrat',
                    'category'     => $isMaha ? 'festival' : 'masik_shivratri',
                    'tithi'        => 'कृष्ण चतुर्दशी',
                    'paksha'       => 'Krishna',
                    'tithi_num'    => 14,
                    'masa'         => $useMasa,
                    'sunrise'      => $ssRise,
                    'sunset'       => $ssSet,
                    'icon'         => '🌙',
                    'significance' => $isMaha
                        ? 'महा शिवरात्रि — शिव का ब्रह्मांडीय ज्योतिर्लिंग प्रकट हुआ। चार प्रहर में रुद्राभिषेक से मोक्ष।'
                        : "मासिक शिवरात्रि — {$useMasa} मास में कृष्ण चतुर्दशी।",
                    'rituals'      => [
                        'निर्जला व्रत (बिना जल, अगले सूर्योदय तक)',
                        'चार प्रहर रुद्राभिषेक (मध्यरात्रि उपासना)',
                        'बिल्वपत्र, दूध, शहद, दही, घृत, गुलाब जल',
                        'ॐ नमः शिवाय 108 बार प्रति प्रहर',
                        'अखंड रात्रि जागरण',
                        $isMaha ? 'ज्योतिर्लिंग दर्शन (यदि संभव)' : 'तिल का दीपक',
                    ],
                    'mantra'       => 'ॐ नमः शिवाय · ॐ त्र्यम्बकं यजामहे',
                    'details'      => $isMaha
                        ? 'महा शिवरात्रि: चार प्रहर — 1. सूर्यास्त-9pm: दुग्ध अभिषेक। 2. 9pm-12am: दधि अभिषेक। 3. 12am-3am: घृत अभिषेक। 4. 3am-सूर्योदय: मधु अभिषेक।<br><br><strong>शिवरात्रि पूजन विधि:</strong><br>• प्रत्येक प्रहर में शिवलिंग को पंचामृत (दूध, दही, शहद, घृत, शक्कर) से स्नान कराएं।<br>• बिल्वपत्र की माला अर्पण करें।<br>• ॐ नमः शिवाय का निरंतर जप।<br>• रात्रि में न सोएं — जागरण अनिवार्य।'
                        : "मासिक शिवरात्रि: कृष्ण 14 पर चंद्र-शिव संयोग।",
                    'vidhiTitle'   => $isMaha ? 'महा शिवरात्रि पूजन विधि' : 'मासिक शिवरात्रि विधि',
                ];
            }

            if (($num === 4 && $paksha === 'Krishna')
                || (in_array(4, $skippedTithis) && $prevPaksha === 'Krishna')) {

                $useDate   = in_array(4, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa   = in_array(4, $skippedTithis) ? $prevMasaName : $masaName;
                $useDow    = (int)(new \DateTime($useDate))->format('w');
                $isAngarki = ($useDow === 2);

                if ($useMasa === 'Kartik') {
                    $festivals[] = ['date'=>$useDate,'name'=>'Karwa Chauth','name_hi'=>'करवा चौथ',
                        'type'=>'festival','category'=>'festival','tithi'=>'कृष्ण चतुर्थी',
                        'paksha'=>'Krishna','tithi_num'=>4,'masa'=>$useMasa,
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌙',
                        'significance'=>'करवा चौथ — पति की दीर्घायु के लिए निर्जला व्रत।',
                        'rituals'=>['सर्गी (सूर्योदय से पहले)','निर्जला व्रत','चंद्रमा को चलनी से देखना'],
                        'mantra'=>'ॐ नमः शिवायै शर्वाण्यै सौभाग्यं संतति शुभाम्',
                        'details'=>'करवा चौथ: कार्तिक कृष्ण चतुर्थी। महिलाएं दुल्हन की पोशाक में चंद्रोदय तक उपवास।'];
                } else {
                    $name   = $isAngarki ? 'Angarki Sankashti Chaturthi' : 'Sankashti Chaturthi';
                    $nameHi = $isAngarki ? 'अंगारकी संकष्टी चतुर्थी' : 'संकष्टी चतुर्थी';
                    $festivals[] = ['date'=>$useDate,'name'=>$name,'name_hi'=>$nameHi,
                        'type'=>'vrat','category'=>'chaturthi','tithi'=>'कृष्ण चतुर्थी',
                        'paksha'=>'Krishna','tithi_num'=>4,'masa'=>$useMasa,
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🐘',
                        'significance'=>'संकष्टी चतुर्थी — विघ्नहर्ता गणेश को समर्पित।'
                                    . ($isAngarki ? ' अंगारकी: दस गुना शक्तिशाली।' : ''),
                        'rituals'=>['सूर्योदय से चंद्रोदय तक उपवास','21 दूर्वा गणेश पूजा','मोदक-नारियल','चंद्र दर्शन'],
                        'mantra'=>'ॐ गं गणपतये नमः',
                        'details'=>'संकष्टी = "खतरों से मुक्ति"।'];
                }
            }

            if (($num === 4 && $paksha === 'Shukla')
                || (in_array(4, $skippedTithis) && $prevPaksha === 'Shukla')) {

                $useDate = in_array(4, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa = in_array(4, $skippedTithis) ? $prevMasaName : $masaName;
                $isMain  = ($useMasa === 'Bhadrapada');

                $festivals[] = ['date'=>$useDate,
                    'name'=>$isMain ? 'Ganesh Chaturthi (Siddhivinayak)' : 'Vinayak Chaturthi',
                    'name_hi'=>$isMain ? 'श्री गणेश चतुर्थी — सिद्धिविनायक' : 'विनायक चतुर्थी',
                    'type'=>$isMain ? 'festival' : 'vrat','category'=>'chaturthi',
                    'tithi'=>'शुक्ल चतुर्थी','paksha'=>'Shukla','tithi_num'=>4,'masa'=>$useMasa,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🐘',
                    'significance'=>$isMain
                        ? 'गणेश चतुर्थी — 10-दिवसीय महोत्सव आरंभ।'
                        : "विनायक चतुर्थी — शुक्ल चतुर्थी: सिद्धिविनायक पूजा।",
                    'rituals'=>['गणेश स्थापना','षोडशोपचार पूजा','21 मोदक','दूर्वा और लाल पुष्प','चंद्र मत देखें'],
                    'mantra'=>'ॐ गं गणपतये नमः',
                    'details'=>$isMain
                        ? 'गणेश चतुर्थी: भाद्रपद शुक्ल 4।'
                        : 'शिव पुराण: 12 विनायक चतुर्थी व्रत से गणेश का आशीर्वाद।'];
            }

            if (($num === 8 && $paksha === 'Krishna')
                || (in_array(8, $skippedTithis) && $prevPaksha === 'Krishna')) {

                $useDate = in_array(8, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa = in_array(8, $skippedTithis) ? $prevMasaName : $masaName;

                if ($useMasa === 'Bhadrapada') {
                    $festivals[] = ['date'=>$useDate,'name'=>'Krishna Janmashtami',
                        'name_hi'=>'श्रीकृष्ण जन्माष्टमी','type'=>'festival','category'=>'festival',
                        'tithi'=>'भाद्रपद कृष्ण अष्टमी','paksha'=>'Krishna','tithi_num'=>8,'masa'=>$useMasa,
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🦚',
                        'significance'=>'भगवान श्रीकृष्ण का जन्मोत्सव — विष्णु के अष्टम अवतार। मध्यरात्रि, मथुरा कारागार में।',
                        'rituals'=>['दिनभर उपवास','रात 12 बजे जन्मोत्सव','पंचामृत अभिषेक','झूला सजाना','हरे कृष्ण कीर्तन'],
                        'mantra'=>'ॐ नमो भगवते वासुदेवाय · हरे कृष्ण हरे कृष्ण',
                        'details'=>'श्रीकृष्ण जन्म: भाद्रपद कृष्ण 8, रोहिणी नक्षत्र, मध्यरात्रि।'];
                } else {
                    $isBhairav = ($useMasa === 'Margashirsha');
                    $festivals[] = ['date'=>$useDate,
                        'name'=>$isBhairav ? 'Kaal Bhairav Jayanti' : 'Masik Kalashtami',
                        'name_hi'=>$isBhairav ? 'काल भैरव जयंती' : 'मासिक कालाष्टमी',
                        'type'=>$isBhairav ? 'festival' : 'vrat','category'=>'kalashtami',
                        'tithi'=>'कृष्ण अष्टमी','paksha'=>'Krishna','tithi_num'=>8,'masa'=>$useMasa,
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🖤',
                        'significance'=>$isBhairav
                            ? 'काल भैरव जयंती — काशी के रक्षक।'
                            : "मासिक कालाष्टमी — काल भैरव उपासना।",
                        'rituals'=>['काल भैरव पूजा (मध्यरात्रि)','सरसों तेल का दीपक','काले तिल','कुत्तों को भोजन'],
                        'mantra'=>'ॐ कालभैरवाय नमः',
                        'details'=>$isBhairav ? 'काल भैरव: काशी में पापमुक्त हुए।' : "कालाष्टमी: प्रतिमास कृष्ण अष्टमी।"];
                }
            }

            if (($num === 8 && $paksha === 'Shukla')
                || (in_array(8, $skippedTithis) && $prevPaksha === 'Shukla')) {

                $useDate    = in_array(8, $skippedTithis) ? $prevDateStr : $dateStr;
                $useMasa    = in_array(8, $skippedTithis) ? $prevMasaName : $masaName;
                $isNavratri = in_array($useMasa, ['Chaitra','Ashwin']);
                $isRadha    = ($useMasa === 'Bhadrapada');

                if ($isRadha) {
                    $festivals[] = ['date'=>$useDate,'name'=>'Radha Ashtami',
                        'name_hi'=>'राधा अष्टमी','type'=>'festival','category'=>'festival',
                        'tithi'=>'शुक्ल अष्टमी','paksha'=>'Shukla','tithi_num'=>8,'masa'=>$useMasa,
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌸',
                        'significance'=>'देवी राधा का जन्मदिन — जन्माष्टमी के 15 दिन बाद।',
                        'rituals'=>['राधा-कृष्ण पूजा','कमल पुष्प','राधा अष्टोत्तर'],
                        'mantra'=>'ॐ राधिकायै नमः · राधे राधे',
                        'details'=>'राधा अष्टमी: जन्माष्टमी के ठीक 15 दिन बाद।'];
                }

                $nameDurga = $isNavratri
                    ? ($useMasa === 'Ashwin' ? 'Maha Ashtami (Sharad Navratri)' : 'Chaitra Ashtami')
                    : "Masik Durga Ashtami ({$useMasa})";
                $festivals[] = ['date'=>$useDate,'name'=>$nameDurga,
                    'name_hi'=>$isNavratri ? 'महा अष्टमी — शारद नवरात्रि' : 'मासिक दुर्गा अष्टमी',
                    'type'=>$isNavratri?'festival':'vrat','category'=>'durgaashtami',
                    'tithi'=>'शुक्ल अष्टमी','paksha'=>'Shukla','tithi_num'=>8,'masa'=>$useMasa,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'⚔️',
                    'significance'=>$isNavratri
                        ? 'महा अष्टमी — नवरात्रि का सबसे शक्तिशाली दिन।'
                        : "मासिक दुर्गा अष्टमी।",
                    'rituals'=>['दुर्गा सप्तशती पाठ','कन्या पूजन','लाल पुष्प','अष्टमी हवन','रात्रि जागरण'],
                    'mantra'=>'ॐ दुं दुर्गायै नमः',
                    'details'=>$isNavratri
                        ? 'महा अष्टमी: देवी दुर्गा ने महिषासुर का वध किया।'
                        : "मासिक दुर्गा अष्टमी।"];
            }

            if ($num === 9 && $paksha === 'Shukla' && $masaName === 'Chaitra') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Rama Navami','name_hi'=>'राम नवमी',
                    'type'=>'festival','category'=>'jayanti','tithi'=>'चैत्र शुक्ल नवमी',
                    'paksha'=>'Shukla','tithi_num'=>9,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🏹',
                    'significance'=>'भगवान राम का जन्मोत्सव — विष्णु के सप्तम अवतार।',
                    'rituals'=>['राम नवमी व्रत','मध्याह्न राम अभिषेक','रामायण पाठ','राम रक्षा स्तोत्र'],
                    'mantra'=>'श्री राम जय राम जय जय राम',
                    'details'=>'राम नवमी: चैत्र शुक्ल 9, अभिजित मुहूर्त, दोपहर।'];
            }

            if ($num === 1 && $paksha === 'Shukla' && $masaName === 'Ashwin') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Sharad Navratri (Ghatasthapana)',
                    'name_hi'=>'शारद नवरात्रि — घटस्थापना','type'=>'festival','category'=>'navratri',
                    'tithi'=>'आश्विन शुक्ल प्रतिपदा','paksha'=>'Shukla','tithi_num'=>1,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌺',
                    'significance'=>'नौ पवित्र रात्रियों का आरंभ — वर्ष की सर्वाधिक महत्वपूर्ण नवरात्रि।',
                    'rituals'=>['शुभ मुहूर्त में घटस्थापना','जौ बीज रोपण','अखंड ज्योति प्रज्वलन','9 दिन उपवास'],
                    'mantra'=>'ॐ दुर्गायै नमः',
                    'details'=>'शारद नवरात्रि: आश्विन शुक्ल 1-9।'];
            }

            if ($num === 1 && $paksha === 'Shukla' && $masaName === 'Chaitra') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Chaitra Navratri (Hindu New Year)',
                    'name_hi'=>'चैत्र नवरात्रि — हिन्दू नव वर्ष','type'=>'festival','category'=>'navratri',
                    'tithi'=>'चैत्र शुक्ल प्रतिपदा','paksha'=>'Shukla','tithi_num'=>1,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌸',
                    'significance'=>'चैत्र शुक्ल प्रतिपदा = हिन्दू नव वर्ष।',
                    'rituals'=>['गुड़ी पड़वा','युगादि','पंचांग श्रवण','नवरात्रि घटस्थापना'],
                    'mantra'=>'ॐ शक्त्यै नमः',
                    'details'=>'चैत्र शुक्ल प्रतिपदा = हिन्दू पंचांग वर्ष का पहला दिन।'];
            }

            if ($num === 10 && $paksha === 'Shukla' && $masaName === 'Ashwin') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Vijayadashami — Dussehra',
                    'name_hi'=>'विजयदशमी — दशहरा','type'=>'festival','category'=>'festival',
                    'tithi'=>'आश्विन शुक्ल दशमी','paksha'=>'Shukla','tithi_num'=>10,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🏹',
                    'significance'=>'विजयदशमी — भगवान राम ने रावण का वध किया।',
                    'rituals'=>['रावण दहन','शमी पूजन','आयुध पूजा','नए वस्त्र-नया कार्य आरंभ'],
                    'mantra'=>'जय श्री राम',
                    'details'=>'विजयदशमी = आश्विन शुक्ल 10।'];
            }

            if ($num === 9 && $paksha === 'Shukla' && $masaName === 'Ashwin') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Maha Navami (Ayudha Puja)',
                    'name_hi'=>'महा नवमी — आयुध पूजा','type'=>'festival','category'=>'navratri',
                    'tithi'=>'आश्विन शुक्ल नवमी','paksha'=>'Shukla','tithi_num'=>9,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'⚔️',
                    'significance'=>'नवरात्रि का 9वां दिन — माँ सिद्धिदात्री।',
                    'rituals'=>['माँ सिद्धिदात्री पूजा','आयुध पूजा','कन्या पूजन','हवन'],
                    'mantra'=>'ॐ सिद्धगन्धर्वयक्षाद्यैरसुरैरमरैरपि',
                    'details'=>'महा नवमी: नवरात्रि का अंतिम दिन।'];
            }

            if ($num === 13 && $paksha === 'Krishna' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Dhanteras (Dhanvantari Trayodashi)',
                    'name_hi'=>'धनतेरस — धन्वंतरि त्रयोदशी','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक कृष्ण त्रयोदशी','paksha'=>'Krishna','tithi_num'=>13,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'💰',
                    'significance'=>'धन्वंतरि त्रयोदशी — सोना-चांदी खरीदने का श्रेष्ठ दिन।',
                    'rituals'=>['सोना-चांदी-बर्तन खरीदना','धन्वंतरि पूजा','दक्षिण दिशा में 13 दीपक'],
                    'mantra'=>'ॐ धन्वन्तरये नमः',
                    'details'=>'धनतेरस: कार्तिक कृष्ण 13। दीपावली का प्रथम दिन।'];
            }

            if ($num === 14 && $paksha === 'Krishna' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Naraka Chaturdashi (Choti Diwali)',
                    'name_hi'=>'नरक चतुर्दशी — छोटी दीवाली','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक कृष्ण चतुर्दशी','paksha'=>'Krishna','tithi_num'=>14,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🪔',
                    'significance'=>'श्रीकृष्ण ने नरकासुर का वध किया।',
                    'rituals'=>['सूर्योदय से पहले अभ्यंग स्नान','14 दीपक','आतिशबाजी'],
                    'mantra'=>'ॐ कृष्णाय नमः',
                    'details'=>'नरक चतुर्दशी: कार्तिक कृष्ण 14।'];
            }

            if ($num === 1 && $paksha === 'Shukla' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Govardhan Puja (Annakut)',
                    'name_hi'=>'गोवर्धन पूजा — अन्नकूट','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक शुक्ल प्रतिपदा','paksha'=>'Shukla','tithi_num'=>1,'masa'=>'Kartik',
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'⛰',
                    'significance'=>'श्रीकृष्ण ने गोवर्धन पर्वत उठाया। 56 व्यंजनों का भोग।',
                    'rituals'=>['गोवर्धन पर्वत पूजा','56 व्यंजन (छप्पन भोग)','गाय पूजा'],
                    'mantra'=>'गोवर्धनधराय नमः',
                    'details'=>'गोवर्धन पूजा: कार्तिक शुक्ल 1।'];
            }

            if ($num === 2 && $paksha === 'Shukla' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Bhai Dooj (Yama Dwitiya)',
                    'name_hi'=>'भाई दूज — यम द्वितीया','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक शुक्ल द्वितीया','paksha'=>'Shukla','tithi_num'=>2,'masa'=>'Kartik',
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🤝',
                    'significance'=>'यमराज अपनी बहन यमुना से मिलने आए। भाई-बहन का पर्व।',
                    'rituals'=>['बहन भाई के माथे पर तिलक','साथ भोजन','भाई का उपहार'],
                    'mantra'=>'यमाय नमः','details'=>''];
            }

            if ($num === 6 && $paksha === 'Shukla' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Chhath Puja (Sandhya Arghya)',
                    'name_hi'=>'छठ पूजा — संध्या अर्घ्य','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक शुक्ल षष्ठी','paksha'=>'Shukla','tithi_num'=>6,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'☀',
                    'significance'=>'सूर्य षष्ठी — 36 घंटे निर्जला व्रत।',
                    'rituals'=>['36 घंटे निर्जला व्रत','संध्या अर्घ्य — डूबते सूर्य को','ठेकुआ-नारियल प्रसाद'],
                    'mantra'=>'ॐ सूर्याय नमः · छठी मैया की जय',
                    'details'=>'छठ पूजा: कार्तिक शुक्ल चतुर्थी से सप्तमी तक 4 दिन।'];
            }

            if ($num === 11 && $paksha === 'Shukla' && $masaName === 'Kartik') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Prabodhini Ekadashi (Tulasi Vivaha)',
                    'name_hi'=>'प्रबोधिनी एकादशी — तुलसी विवाह','type'=>'festival','category'=>'festival',
                    'tithi'=>'कार्तिक शुक्ल एकादशी','paksha'=>'Shukla','tithi_num'=>11,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌿',
                    'significance'=>'विष्णु की 4 माह की योग निद्रा का समापन। तुलसी-शालिग्राम विवाह।',
                    'rituals'=>['तुलसी को लाल चुनरी','शालिग्राम','विवाह संस्कार विधि'],
                    'mantra'=>'तुलस्यै नमः',
                    'details'=>'प्रबोधिनी एकादशी: विष्णु 4 माह बाद जागते हैं।'];
            }

            if ($num === 3 && $paksha === 'Shukla' && $masaName === 'Vaishakha') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Akshaya Tritiya','name_hi'=>'अक्षय तृतीया',
                    'type'=>'festival','category'=>'festival','tithi'=>'वैशाख शुक्ल तृतीया',
                    'paksha'=>'Shukla','tithi_num'=>3,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'✨',
                    'significance'=>'अक्षय तृतीया — "अक्षय" = कभी न घटने वाला। 3.5 स्वयंसिद्ध मुहूर्तों में से एक।',
                    'rituals'=>['सोना-चांदी खरीदना','दान','नया व्यापार आरंभ','तुलसी सहित विष्णु पूजा'],
                    'mantra'=>'ॐ नमो भगवते वासुदेवाय · ॐ श्रीं महालक्ष्म्यै नमः',
                    'details'=>'अक्षय तृतीया: वैशाख शुक्ल 3 — सूर्य-चंद्र दोनों उच्च राशि में।'];
            }

            if ($num === 5 && $paksha === 'Shukla' && $masaName === 'Magha') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Vasant Panchami (Saraswati Puja)',
                    'name_hi'=>'वसंत पंचमी — सरस्वती पूजा','type'=>'festival','category'=>'festival',
                    'tithi'=>'माघ शुक्ल पंचमी','paksha'=>'Shukla','tithi_num'=>5,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'📚',
                    'significance'=>'माँ सरस्वती जन्मदिन — विद्या, कला, संगीत, ज्ञान की देवी।',
                    'rituals'=>['पीले वस्त्र धारण','सरस्वती पूजा','पीले फूल-पीली मिठाई','विद्यारंभ संस्कार'],
                    'mantra'=>'ॐ ऐं सरस्वत्यै नमः',
                    'details'=>'वसंत पंचमी: माघ शुक्ल 5 — ब्रह्मा ने सरस्वती देवी की रचना की।'];
            }

            if ($num === 10 && $paksha === 'Shukla' && $masaName === 'Jyeshtha') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Ganga Dussehra','name_hi'=>'गंगा दशहरा',
                    'type'=>'festival','category'=>'festival','tithi'=>'ज्येष्ठ शुक्ल दशमी',
                    'paksha'=>'Shukla','tithi_num'=>10,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌊',
                    'significance'=>'माँ गंगा का पृथ्वी पर अवतरण दिवस।',
                    'rituals'=>['गंगा स्नान','दीप दान','10 वस्तुओं का दान'],
                    'mantra'=>'ॐ गं गंगायै नमः',
                    'details'=>'गंगा दशहरा: ज्येष्ठ शुक्ल 10।'];
            }

            if ($num === 5 && $paksha === 'Shukla' && $masaName === 'Shravana') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Naga Panchami','name_hi'=>'नाग पंचमी',
                    'type'=>'festival','category'=>'festival','tithi'=>'श्रावण शुक्ल पंचमी',
                    'paksha'=>'Shukla','tithi_num'=>5,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🐍',
                    'significance'=>'नागों की पूजा। सर्पदंश भय से मुक्ति।',
                    'rituals'=>['नागों को दूध चढ़ाना','नाग मंदिर दर्शन','अष्टनाग स्तोत्र'],
                    'mantra'=>'ॐ नागेभ्यो नमः',
                    'details'=>'नाग पंचमी: श्रावण शुक्ल 5।'];
            }

            if ($num === 3 && $paksha === 'Shukla'
                && in_array($masaName, ['Shravana','Bhadrapada'])) {
                $teejName = $masaName === 'Shravana' ? 'Hariyali Teej' : 'Hartalika Teej';
                $teejHi   = $masaName === 'Shravana' ? 'हरियाली तीज' : 'हरतालिका तीज';
                $festivals[] = ['date'=>$dateStr,'name'=>$teejName,'name_hi'=>$teejHi,
                    'type'=>'festival','category'=>'festival',
                    'tithi'=>($masaName==='Shravana'?'श्रावण':'भाद्रपद').' शुक्ल तृतीया',
                    'paksha'=>'Shukla','tithi_num'=>3,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'💚',
                    'significance'=>'माँ पार्वती ने शिव को पाने के लिए यह व्रत किया।',
                    'rituals'=>['निर्जला व्रत','झूला झूलना-तीज गीत','पार्वती-शिव पूजा'],
                    'mantra'=>'ॐ नमः शिवाय',
                    'details'=>'तीज: श्रावण-भाद्रपद।'];
            }

            if ($num === 14 && $paksha === 'Shukla' && $masaName === 'Bhadrapada') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Anant Chaturdashi (Ganesh Visarjan)',
                    'name_hi'=>'अनंत चतुर्दशी — गणेश विसर्जन','type'=>'festival','category'=>'festival',
                    'tithi'=>'भाद्रपद शुक्ल चतुर्दशी','paksha'=>'Shukla','tithi_num'=>14,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🐘',
                    'significance'=>'10-दिवसीय गणेश उत्सव का समापन।',
                    'rituals'=>['गणपति की भव्य विदाई','अनंत भगवान पूजा','जल विसर्जन'],
                    'mantra'=>'गणपति बप्पा मोरया',
                    'details'=>'अनंत चतुर्दशी: गणेश उत्सव का अंतिम दिन।'];
            }

            if ($prevSunSign !== -1 && $curSunSign !== $prevSunSign) {
                $sankranti_names = [
                    0=>'Mesha', 1=>'Vrishabha', 2=>'Mithuna', 3=>'Karka',
                    4=>'Simha', 5=>'Kanya',    6=>'Tula',    7=>'Vrishchika',
                    8=>'Dhanu', 9=>'Makar',    10=>'Kumbha', 11=>'Meena'
                ];
                $sName = $sankranti_names[$curSunSign] ?? '';

                if ($curSunSign === 9) {
                    $lohriDt = (new \DateTime($dateStr))->modify('-1 day');
                    $festivals[] = ['date'=>$lohriDt->format('Y-m-d'),'name'=>'Lohri','name_hi'=>'लोहड़ी',
                        'type'=>'festival','category'=>'festival','tithi'=>'पौष — मकर पूर्व',
                        'paksha'=>'','masa'=>'Solar','sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🔥',
                        'significance'=>'पंजाब और उत्तर भारत का शीतकालीन पर्व। अलाव जलाकर सूर्य-अग्नि पूजा।',
                        'rituals'=>['अलाव जलाना','तिल-गुड़-मूंगफली','भांगड़ा-गिद्दा नृत्य'],
                        'mantra'=>'सुंदर मुंदरिए होए',
                        'details'=>'लोहड़ी: मकर संक्रांति की पूर्व संध्या।'];

                    $festivals[] = ['date'=>$dateStr,'name'=>'Thai Pongal','name_hi'=>'पोंगल — थाई पोंगल',
                        'type'=>'festival','category'=>'festival','tithi'=>'सौर मकर संक्रांति',
                        'paksha'=>'','masa'=>'Solar','sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🍚',
                        'significance'=>'तमिलनाडु का सबसे बड़ा फसल उत्सव।',
                        'rituals'=>['चावल-दूध उबालना (पोंगल)','सूर्य पूजा','गाय-बैल सजाना'],
                        'mantra'=>'ॐ सूर्याय नमः · पोंगलो पोंगल',
                        'details'=>'थाई पोंगल: 4 दिन।'];

                    $festivals[] = ['date'=>$dateStr,'name'=>'Makar Sankranti','name_hi'=>'मकर संक्रांति',
                        'type'=>'festival','category'=>'sankranti','tithi'=>'सौर संक्रांति',
                        'paksha'=>'','masa'=>'Solar','sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'☀',
                        'significance'=>'सूर्य मकर राशि में प्रवेश। उत्तरायण आरंभ।',
                        'rituals'=>['गंगा स्नान','तिल-गुड़ विनिमय','पतंग उड़ाना','खिचड़ी दान'],
                        'mantra'=>'ॐ सूर्याय नमः',
                        'details'=>'मकर संक्रांति: पोंगल (तमिलनाडु), लोहड़ी (पंजाब), बिहू (असम)।'];

                } elseif ($curSunSign === 0) {
                    $festivals[] = ['date'=>$dateStr,'name'=>'Baisakhi / Vishu / Rongali Bihu',
                        'name_hi'=>'बैसाखी — विशु — रंगाली बिहू','type'=>'festival','category'=>'festival',
                        'tithi'=>'मेष संक्रांति','paksha'=>'','masa'=>'Solar',
                        'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌾',
                        'significance'=>'मेष संक्रांति — पंजाब में बैसाखी (खालसा पंथ स्थापना 1699)।',
                        'rituals'=>['नए वस्त्र','मंदिर दर्शन','बिहू नृत्य','खालसा दीवान'],
                        'mantra'=>'ॐ सूर्याय नमः',
                        'details'=>'मेष संक्रांति: 1699 में गुरु गोबिंद सिंह जी ने खालसा पंथ की स्थापना की।'];
                } else {
                    if ($sName) {
                        $festivals[] = ['date'=>$dateStr,'name'=>"{$sName} Sankranti",
                            'name_hi'=>"{$sName} संक्रांति",'type'=>'vrat','category'=>'sankranti',
                            'tithi'=>"सौर {$sName} संक्रांति",'paksha'=>'','masa'=>'Solar',
                            'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'☀',
                            'significance'=>"सूर्य {$sName} राशि में प्रवेश।",
                            'rituals'=>['सूर्य अर्घ्य','पवित्र स्नान','ब्राह्मण दान'],
                            'mantra'=>'ॐ सूर्याय नमः','details'=>''];
                    }
                }
            }
            $prevSunSign = $curSunSign;

            if ($paksha === 'Krishna' && $masaName === 'Ashwin' && $num >= 1 && $num <= 15) {
                $shraddhaNames = [
                    1=>'Pratipada Shraddha',2=>'Dwitiya Shraddha',3=>'Tritiya Shraddha',
                    4=>'Chaturthi Shraddha',5=>'Panchami Shraddha',6=>'Shashthi Shraddha',
                    7=>'Saptami Shraddha',8=>'Ashtami Shraddha',9=>'Navami Shraddha',
                    10=>'Dashami Shraddha',11=>'Ekadashi Shraddha',12=>'Dwadashi Shraddha',
                    13=>'Trayodashi Shraddha',14=>'Chaturdashi Shraddha',
                    15=>'Sarva Pitru Amavasya (Mahalaya)',
                ];
                $festivals[] = ['date'=>$dateStr,
                    'name'    => $shraddhaNames[$num] ?? 'Pitru Paksha Shraddha',
                    'name_hi' => 'पितृ पक्ष श्राद्ध','type'=>'vrat','category'=>'shraddha',
                    'tithi'   => 'आश्विन कृष्ण '.$num,'paksha'=>'Krishna','tithi_num'=>$num,'masa'=>'Ashwin',
                    'sunrise' => $ssRise,'sunset'=>$ssSet,'icon'=>'🙏',
                    'significance' => 'पितृ पक्ष श्राद्ध — पूर्वजों को तर्पण।',
                    'rituals'  => ['काले तिल और जल से तर्पण','पिंड दान','कौवे-गाय को भोजन','ब्राह्मण भोज'],
                    'mantra'   => 'ॐ पितृ देवतायै नमः',
                    'details'  => 'पितृ पक्ष: आश्विन कृष्ण पक्ष (16 दिन)।'];
            }

            if ($nakIdx === 3 && $dow === 1) {
                $festivals[] = ['date'=>$dateStr,'name'=>'Rohini Vrat','name_hi'=>'रोहिणी व्रत',
                    'type'=>'vrat','category'=>'jayanti','tithi'=>'रोहिणी नक्षत्र',
                    'paksha'=>$paksha,'tithi_num'=>$num,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌙',
                    'significance'=>'जैन परंपरा: चंद्रमा रोहिणी नक्षत्र में + सोमवार = रोहिणी व्रत।',
                    'rituals'=>['जैन मंदिर दर्शन','रोहिणी व्रत कथा','उपवास'],
                    'mantra'=>'णमोकार महामंत्र','details'=>''];
            }

            if ($nakIdx === 21 && $curSunSign === 4) {
                $festivals[] = ['date'=>$dateStr,'name'=>'Thiruvonam (Onam)',
                    'name_hi'=>'थिरुवोणम — ओणम','type'=>'festival','category'=>'festival',
                    'tithi'=>'श्रवण नक्षत्र — चिंगम','paksha'=>$paksha,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🌺',
                    'significance'=>'केरल का सबसे बड़ा त्योहार। राजा महाबलि के पृथ्वी आगमन का उत्सव।',
                    'rituals'=>['पूकलम — फूलों की रंगोली','ओणसद्या — 26 व्यंजन','वल्लमकाली'],
                    'mantra'=>'ॐ नमो भगवते वामनाय',
                    'details'=>'ओणम: चंद्रमा श्रावण नक्षत्र + सूर्य सिंह राशि।'];
            }

            if ($masaName === 'Shravana' && $dow === 1) {
                $festivals[] = ['date'=>$dateStr,'name'=>'Shravana Somavar','name_hi'=>'श्रावण सोमवार',
                    'type'=>'vrat','category'=>'masik_shivratri',
                    'tithi'=>$paksha.' '.($num),'paksha'=>$paksha,'tithi_num'=>$num,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'🔱',
                    'significance'=>'श्रावण मास का सोमवार — शिव पूजा के लिए सर्वश्रेष्ठ।',
                    'rituals'=>['शिव अभिषेक','बेलपत्र, धतूरा','उपवास','कांवड़ यात्रा'],
                    'mantra'=>'ॐ नमः शिवाय',
                    'details'=>'श्रावण सोमवार: शिव पुराण — श्रावण शिव का प्रिय मास।'];
            }

            $monthDay = sprintf('%02d-%02d', $mo, $dy);
            $nationalDays = [
                '01-26' => ['Republic Day','गणतंत्र दिवस','🇮🇳','national'],
                '03-08' => ['International Women\'s Day','अंतरराष्ट्रीय महिला दिवस','👩','national'],
                '04-14' => ['Ambedkar Jayanti','डॉ. अंबेडकर जयंती','📜','national'],
                '04-22' => ['Earth Day','पृथ्वी दिवस','🌍','national'],
                '08-15' => ['Independence Day','स्वतंत्रता दिवस','🇮🇳','national'],
                '10-02' => ['Gandhi Jayanti','गांधी जयंती','🕊','national'],
                '11-14' => ['Children\'s Day','बाल दिवस','👦','national'],
                '12-25' => ['Christmas','क्रिसमस','✝','christian'],
            ];
            if (isset($nationalDays[$monthDay])) {
                [$nName, $nNameHi, $nIcon, $nCat] = $nationalDays[$monthDay];
                $festivals[] = ['date'=>$dateStr,'name'=>$nName,'name_hi'=>$nNameHi,
                    'type'=>'festival','category'=>$nCat,'tithi'=>'Gregorian Fixed',
                    'paksha'=>'','tithi_num'=>0,'masa'=>'',
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>$nIcon,
                    'significance'=>"{$nName}.",
                    'rituals'=>[],'mantra'=>'','details'=>''];
            }

            if ($num === 7 && $paksha === 'Shukla' && $masaName === 'Magha') {
                $festivals[] = ['date'=>$dateStr,'name'=>'Ratha Saptami','name_hi'=>'रथ सप्तमी',
                    'type'=>'festival','category'=>'festival','tithi'=>'माघ शुक्ल सप्तमी',
                    'paksha'=>'Shukla','tithi_num'=>7,'masa'=>$masaName,
                    'sunrise'=>$ssRise,'sunset'=>$ssSet,'icon'=>'☀',
                    'significance'=>'भगवान सूर्य का जन्मदिन।',
                    'rituals'=>['प्रातःकाल सूर्य स्नान','सूर्य अर्घ्य','सूर्य नमस्कार 108'],
                    'mantra'=>'ॐ सूर्याय नमः',
                    'details'=>'रथ सप्तमी: माघ शुक्ल 7।'];
            }

            updatePrev:
            $prevDateStr  = $dateStr;
            $prevNum      = $num;
            $prevPaksha   = $paksha;
            $prevMasaName = $masaName;
        }

        usort($festivals, fn($a, $b) => $a['date'] <=> $b['date']);

        return ['festivals'=>$festivals, 'count'=>count($festivals), 'year'=>$year];
    }

    private static function getEkadashiDetails(string $name): string
    {
        $details = [
            'Papmochani Ekadashi'   => 'पापमोचनी एकादशी: चैत्र कृष्ण 11। ब्रह्म वैवर्त पुराण: सभी पापों से मुक्ति।',
            'Kamada Ekadashi'       => 'कामदा एकादशी: चैत्र शुक्ल 11। वराह पुराण: सभी मनोकामनाएं पूर्ण।',
            'Varuthini Ekadashi'    => 'वरूथिनी एकादशी: वैशाख कृष्ण 11। शत्रु, रोग और दुर्भाग्य से रक्षा।',
            'Mohini Ekadashi'       => 'मोहिनी एकादशी: वैशाख शुक्ल 11। माया के भ्रम से मुक्ति।',
            'Apara Ekadashi'        => 'अपरा एकादशी: ज्येष्ठ कृष्ण 11।',
            'Nirjala Ekadashi'      => 'निर्जला एकादशी: ज्येष्ठ शुक्ल 11 — सर्वाधिक कठोर। जल भी नहीं। सभी 24 एकादशियों का राजा।',
            'Yogini Ekadashi'       => 'योगिनी एकादशी: आषाढ़ कृष्ण 11। शारीरिक और आत्मिक रोगों का निवारण।',
            'Devshayani Ekadashi'   => 'देवशयनी एकादशी: आषाढ़ शुक्ल 11 — विष्णु की योग निद्रा आरंभ। चातुर्मास प्रारंभ।',
            'Kamika Ekadashi'       => 'कामिका एकादशी: श्रावण कृष्ण 11। तुलसी पूजा विशेष फलदायी।',
            'Putrada Ekadashi'      => 'पुत्रदा एकादशी: श्रावण शुक्ल 11। दंपती को पुत्र प्राप्ति।',
            'Aja Ekadashi'          => 'अजा एकादशी: भाद्रपद कृष्ण 11। जन्म-मृत्यु से मुक्ति।',
            'Parsva Ekadashi'       => 'पार्श्व एकादशी: भाद्रपद शुक्ल 11। विष्णु योग निद्रा में करवट बदलते हैं।',
            'Indira Ekadashi'       => 'इंदिरा एकादशी: आश्विन कृष्ण 11 — पितृ पक्ष में। पितृ दोष निवारण की श्रेष्ठ एकादशी।',
            'Papankusha Ekadashi'   => 'पापांकुशा एकादशी: आश्विन शुक्ल 11। पापों को लौह अंकुश से हटाती है।',
            'Rama Ekadashi'         => 'रमा एकादशी: कार्तिक कृष्ण 11 — दीपावली से पहले।',
            'Prabodhini Ekadashi'   => 'प्रबोधिनी एकादशी: कार्तिक शुक्ल 11 — विष्णु जागते हैं। चातुर्मास समाप्त।',
            'Utpanna Ekadashi'      => 'उत्पन्ना एकादशी: मार्गशीर्ष कृष्ण 11 — एकादशी देवी का जन्म।',
            'Mokshada Ekadashi'     => 'मोक्षदा एकादशी: मार्गशीर्ष शुक्ल 11 — श्रीकृष्ण ने गीता उपदेश दिया (गीता जयंती)।',
            'Saphala Ekadashi'      => 'सफला एकादशी: पौष कृष्ण 11। सभी कार्यों में सफलता।',
            'Putrada Ekadashi (Pausha)' => 'पौष पुत्रदा एकादशी: पौष शुक्ल 11।',
            'Shattila Ekadashi'     => 'षट्तिला एकादशी: माघ कृष्ण 11। छह तिल कर्म।',
            'Jaya Ekadashi'         => 'जया एकादशी: माघ शुक्ल 11। सभी शत्रुओं पर विजय।',
            'Vijaya Ekadashi'       => 'विजया एकादशी: फाल्गुन कृष्ण 11। राम ने लंका प्रस्थान से पहले यह व्रत किया।',
            'Amalaki Ekadashi'      => 'आमलकी एकादशी: फाल्गुन शुक्ल 11। आंवला वृक्ष पूजा।',
        ];
        return $details[$name] ?? '';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  renderHtml — PASTEL palette, visible name+timing, onclick detail
    // ══════════════════════════════════════════════════════════════════════
       public static function renderHtml(array $festivals, string $activeCategory = 'all'): string
    {
        if (empty($festivals)) {
            return '<div style="padding:48px;text-align:center;color:#64748b;font-family:Inter,sans-serif;
                background:#f8fafc;border-radius:14px;border:1.5px dashed #e2e8f0">
                कोई उत्सव नहीं मिला।</div>';
        }
 
        $aliasMap = [
            'trayodashi'  => 'pradosh',
            'shivratri'   => 'masik_shivratri',
            'ashtami'     => 'durgaashtami',
        ];
 
        $filtered = ($activeCategory === 'all') ? $festivals : array_values(array_filter(
            $festivals,
            fn($f) => ($f['category'] ?? '') === $activeCategory
                   || ($f['category'] ?? '') === ($aliasMap[$activeCategory] ?? $activeCategory)
        ));
 
        if (empty($filtered)) {
            return '<div style="padding:52px;text-align:center;background:#f8fafc;
                border-radius:14px;border:1.5px dashed #e2e8f0">
                <div style="font-size:2rem;margin-bottom:10px">🪷</div>
                <div style="color:#64748b;font-size:1rem;font-weight:600">इस श्रेणी में कोई उत्सव नहीं।</div>
                </div>';
        }
 
        $count = count($filtered);
        $year  = substr($filtered[0]['date'], 0, 4);
 
        // Category chip label map
        $chipLabels = [
            'ekadashi'=>'Ekadashi', 'satyanarayan'=>'Satyanarayan',
            'pradosh'=>'Pradosh', 'masik_shivratri'=>'Shivratri',
            'chaturthi'=>'Chaturthi', 'kalashtami'=>'Kalashtami',
            'durgaashtami'=>'Ashtami', 'festival'=>'Festival',
            'purnima'=>'Purnima', 'amavasya'=>'Amavasya',
            'navratri'=>'Navratri', 'sankranti'=>'Sankranti',
            'jayanti'=>'Jayanti', 'shraddha'=>'Shraddha',
            'national'=>'National', 'christian'=>'Christian',
            'sikh'=>'Sikh', 'jain'=>'Jain', 'muslim'=>'Muslim',
        ];
 
        // CSS class + banner gradient per category
        $catCss = [
            'ekadashi'        => ['fpc-ekadashi',        'linear-gradient(135deg,#1e1b4b,#2e1065)'],
            'satyanarayan'    => ['fpc-satyanarayan',    'linear-gradient(135deg,#1e3a8a,#1e40af)'],
            'pradosh'         => ['fpc-pradosh',         'linear-gradient(135deg,#0c4a6e,#075985)'],
            'masik_shivratri' => ['fpc-masik_shivratri', 'linear-gradient(135deg,#2e1065,#4c1d95)'],
            'chaturthi'       => ['fpc-chaturthi',       'linear-gradient(135deg,#451a03,#78350f)'],
            'kalashtami'      => ['fpc-kalashtami',      'linear-gradient(135deg,#111827,#1f2937)'],
            'durgaashtami'    => ['fpc-durgaashtami',    'linear-gradient(135deg,#4c0519,#881337)'],
            'festival'        => ['fpc-festival',        'linear-gradient(135deg,#042f2e,#134e4a)'],
            'purnima'         => ['fpc-purnima',         'linear-gradient(135deg,#451a03,#78350f)'],
            'amavasya'        => ['fpc-amavasya',        'linear-gradient(135deg,#0f172a,#1e293b)'],
            'navratri'        => ['fpc-navratri',        'linear-gradient(135deg,#4a044e,#701a75)'],
            'sankranti'       => ['fpc-sankranti',       'linear-gradient(135deg,#431407,#7c2d12)'],
            'jayanti'         => ['fpc-jayanti',         'linear-gradient(135deg,#1e3a5f,#1e3a8a)'],
            'shraddha'        => ['fpc-shraddha',        'linear-gradient(135deg,#1e293b,#334155)'],
            'national'        => ['fpc-national',        'linear-gradient(135deg,#022c22,#064e3b)'],
            'christian'       => ['fpc-national',        'linear-gradient(135deg,#1e3a5f,#1e3a8a)'],
            'sikh'            => ['fpc-sankranti',       'linear-gradient(135deg,#431407,#7c2d12)'],
            'jain'            => ['fpc-festival',        'linear-gradient(135deg,#1a2e05,#3f6212)'],
            'muslim'          => ['fpc-satyanarayan',    'linear-gradient(135deg,#0c4a6e,#075985)'],
        ];
        $catDefault = ['fpc-default', 'linear-gradient(135deg,#042f2e,#134e4a)'];
 
        $MONTHS_EN = ['','January','February','March','April','May','June',
                      'July','August','September','October','November','December'];
        $MONTHS_HI = ['','जनवरी','फ़रवरी','मार्च','अप्रैल','मई','जून',
                      'जुलाई','अगस्त','सितम्बर','अक्टूबर','नवम्बर','दिसम्बर'];
        $DAYS_HI   = ['रवि','सोम','मंगल','बुध','गुरु','शुक्र','शनि'];
        $DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
 
        $byMonth = [];
        foreach ($filtered as $f) {
            $mo = (int)substr($f['date'], 5, 2);
            $byMonth[$mo][] = $f;
        }
        ksort($byMonth);
 
        // ── Build HTML ────────────────────────────────────────────
        $html = "<div id=\"festRenderRoot\">";
 
        // Info strip
        $html .= "<div class=\"fp-info-strip\">
          <div>
            <div class=\"fp-strip-title\">{$year} — सभी उत्सव व व्रत</div>
            <div class=\"fp-strip-sub\">Lahiri Ayanamsa · Meeus Precision · {$count} events</div>
          </div>
          <div class=\"fp-strip-count\">{$count} Events</div>
        </div>";
 
        foreach ($byMonth as $mo => $items) {
            $moCount = count($items);
            $html .= "<div class=\"fp-month-hdr\">
              <span class=\"fp-month-en\">{$MONTHS_EN[$mo]}</span>
              <span class=\"fp-month-hi\">{$MONTHS_HI[$mo]}</span>
              <span class=\"fp-month-ct\">{$moCount} event" . ($moCount > 1 ? 's' : '') . "</span>
            </div>";
 
            $html .= "<div class=\"fest-card-grid\">";
 
            foreach ($items as $f) {
                [$yr2, $mo2, $dy2] = explode('-', $f['date']);
                $dayNum   = (int)$dy2;
                $dow      = (int)(new \DateTime($f['date']))->format('w');
                $dayEn    = $DAYS_EN[$dow];
                $dayHi    = $DAYS_HI[$dow];
                $nameHi   = htmlspecialchars($f['name_hi'] ?? '');
                $nameEn   = htmlspecialchars($f['name']    ?? '');
                $sigHi    = htmlspecialchars($f['significance'] ?? '');
                $sunrise  = $f['sunrise'] ?? '';
                $sunset   = $f['sunset']  ?? '';
                $tithi    = htmlspecialchars($f['tithi']   ?? '');
                $masa     = htmlspecialchars($f['masa']    ?? '');
                $cardCat  = $f['category'] ?? 'festival';
 
                [$cssClass, $bannerBg] = $catCss[$cardCat] ?? $catDefault;
                $chipLabel = $chipLabels[$cardCat] ?? ucfirst($cardCat);
 
                // Build JSON for modal
                $festJson = htmlspecialchars(json_encode([
                    'name'         => $f['name']         ?? '',
                    'name_hi'      => $f['name_hi']      ?? '',
                    'date'         => $f['date']          ?? '',
                    'tithi'        => $f['tithi']         ?? '',
                    'masa'         => $f['masa']          ?? '',
                    'significance' => $f['significance']  ?? '',
                    'details'      => $f['details']       ?? '',
                    'rituals'      => $f['rituals']       ?? [],
                    'mantra'       => $f['mantra']        ?? '',
                    'sunrise'      => $f['sunrise']       ?? '',
                    'sunset'       => $f['sunset']        ?? '',
                    'type'         => $f['type']          ?? '',
                    'vidhiTitle'   => $f['vidhiTitle']    ?? '',
                    'cat'          => $cardCat,
                    'bannerBg'     => explode(',', str_replace(['linear-gradient(135deg,',')',' '], ['','',''], $bannerBg))[0] ?? '#1e3a5f',
                    'accent'       => '#0f766e',
                ]), ENT_QUOTES, 'UTF-8');
 
                $html .= "<div class=\"fest-card {$cssClass}\" onclick=\"openFestDetail({$festJson})\">";
 
                // Color bar
                $html .= "<div class=\"fc-stripe fpc-bar\"></div>";
 
                // Card head
                $html .= "<div class=\"fc-head\">
                  <div class=\"fc-date-box\">
                    <div class=\"fc-date-num\">{$dayNum}</div>
                    <div class=\"fc-date-day\">{$dayEn}</div>
                    <div class=\"fc-date-hi\">{$dayHi}</div>
                  </div>
                  <div class=\"fc-icon-name\">
                    <div class=\"fc-badges\">
                      <span class=\"fc-cat-chip\">{$chipLabel}</span>";
 
                if ($tithi) {
                    $html .= "<span class=\"fc-tithi-pill\">{$tithi}</span>";
                }
 
                $html .= "</div>
                    <div class=\"fc-name-hi\">{$nameHi}</div>
                    <div class=\"fc-name-en\">{$nameEn}</div>
                  </div>
                </div>";
 
                // Card body
                $html .= "<div class=\"fc-body\">";
                if ($sigHi) {
                    $html .= "<div class=\"fc-sig\">{$sigHi}</div>";
                }
                if ($sunrise || $sunset) {
                    $html .= "<div class=\"fc-timing\">";
                    if ($sunrise) $html .= "<span class=\"fc-time-chip\">🌅 {$sunrise}</span>";
                    if ($sunset)  $html .= "<span class=\"fc-time-chip\">🌇 {$sunset}</span>";
                    $html .= "</div>";
                }
                $html .= "</div>";
 
                // Footer
                $hasDetails = !empty($f['details']) || !empty($f['rituals']) || !empty($f['mantra']);
                $html .= "<div class=\"fc-footer\">
                  <span class=\"fc-masa\">{$masa}</span>
                  <span class=\"fc-detail-hint\">" . ($hasDetails ? "विवरण देखें →" : "जानकारी →") . "</span>
                </div>";
 
                $html .= "</div>"; // end card
            }
 
            $html .= "</div>"; // end grid
        }
 
        $html .= "</div>";
        return $html;
    }
}