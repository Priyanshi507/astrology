<?php

namespace App\Services;

/**
 * VimshottariDashaCalculator
 *
 * Implements the complete Vimshottari Dasha system per:
 *   - Brihat Parashara Hora Shastra (BPHS), Ch. 46–48
 *   - Komilla Sutton, "The Essentials of Vedic Astrology"
 *
 * System overview:
 *   Total cycle  : 120 years
 *   Base         : Moon's sidereal Nakshatra at birth
 *   Levels       : Mahadasha → Antardasha → Pratyantar → Sookshma → Prana
 *
 * Nakshatra → Dasha lord mapping (BPHS Ch.46):
 *   Each of the 27 Nakshatras is ruled by one of 9 lords in order,
 *   repeating cyclically: Ke Ve Su Mo Ma Ra Ju Sa Me
 *
 * Balance calculation:
 *   The fraction of the Nakshatra already elapsed at birth
 *   determines how much of the first Mahadasha has been "used up".
 */
class VimshottariDashaCalculator
{
    // ── Dasha sequence (BPHS order) ──────────────────────────────
    public const LORDS = [
        'ketu', 'venus', 'sun', 'moon', 'mars',
        'rahu', 'jupiter', 'saturn', 'mercury',
    ];

    // Mahadasha years per lord (BPHS Ch.46, total = 120 yrs)
    public const YEARS = [
        'ketu'    => 7,
        'venus'   => 20,
        'sun'     => 6,
        'moon'    => 10,
        'mars'    => 7,
        'rahu'    => 18,
        'jupiter' => 16,
        'saturn'  => 19,
        'mercury' => 17,
    ];

    // Total cycle in days (120 × 365.25)
    private const CYCLE_DAYS = 43830.0;

    // Days per lord
    private const LORD_DAYS = [
        'ketu'    =>  2556.75,
        'venus'   =>  7305.0,
        'sun'     =>  2191.5,
        'moon'    =>  3652.5,
        'mars'    =>  2556.75,
        'rahu'    =>  6574.5,
        'jupiter' =>  5844.0,
        'saturn'  =>  6939.75,
        'mercury' =>  6213.75,
    ];

    private const NAK_LORD_IDX = [
        0,1,2,3,4,5,6,7,8,
        0,1,2,3,4,5,6,7,8,
        0,1,2,3,4,5,6,7,8,
    ];

    // Abbreviated display names
    public const ABBR = [
        'ketu'    => 'Ke',
        'venus'   => 'Ve',
        'sun'     => 'Su',
        'moon'    => 'Mo',
        'mars'    => 'Ma',
        'rahu'    => 'Ra',
        'jupiter' => 'Ju',
        'saturn'  => 'Sa',
        'mercury' => 'Me',
    ];

    // Planet colours (matches AstroChartRenderer)
    public const COLORS = [
        'sun'     => '#c47000',
        'moon'    => '#1a7ab5',
        'mercury' => '#0a8c5a',
        'venus'   => '#9c2d8a',
        'mars'    => '#c0311f',
        'jupiter' => '#b36000',
        'saturn'  => '#5a4a8a',
        'rahu'    => '#1a7a3a',
        'ketu'    => '#a0440e',
    ];

    // Nakshatra names (for display)
    private const NAK_NAMES = [
        'Ashwini','Bharani','Krittika','Rohini','Mrigashira','Ardra',
        'Punarvasu','Pushya','Ashlesha','Magha','Purva Phalguni',
        'Uttara Phalguni','Hasta','Chitra','Swati','Vishakha','Anuradha',
        'Jyeshtha','Moola','Purva Ashadha','Uttara Ashadha','Shravana',
        'Dhanishta','Shatabhisha','Purva Bhadrapada','Uttara Bhadrapada','Revati',
    ];

    // ── Soft palette matching existing planet tiles ───────────────
    private const SOFT = [
        'sun'     => ['bg'=>'#fff8ee','border'=>'#f5c870','text'=>'#5a2a00','accent'=>'#b35a00','light'=>'#fff3e0'],
        'moon'    => ['bg'=>'#eaf4fb','border'=>'#90c8e8','text'=>'#0a2540','accent'=>'#1565a0','light'=>'#e0f0fa'],
        'mercury' => ['bg'=>'#e8f7f5','border'=>'#a0d8d0','text'=>'#163d35','accent'=>'#0a7a50','light'=>'#dff4f0'],
        'venus'   => ['bg'=>'#f9edf7','border'=>'#d8a0d0','text'=>'#4a0e3a','accent'=>'#8a2070','light'=>'#f5e4f5'],
        'mars'    => ['bg'=>'#fce8e6','border'=>'#e8a0a0','text'=>'#6a0c08','accent'=>'#b02010','light'=>'#fae0de'],
        'jupiter' => ['bg'=>'#fdf6e3','border'=>'#d8c080','text'=>'#3a2800','accent'=>'#9a5000','light'=>'#faf0d8'],
        'saturn'  => ['bg'=>'#eeecf5','border'=>'#b0a8d0','text'=>'#201830','accent'=>'#4a3a7a','light'=>'#e8e4f0'],
        'rahu'    => ['bg'=>'#e6f0e6','border'=>'#90b890','text'=>'#081808','accent'=>'#146030','light'=>'#dceadc'],
        'ketu'    => ['bg'=>'#f5e8e4','border'=>'#d8a898','text'=>'#2a0800','accent'=>'#8a3008','light'=>'#f0e0d8'],
    ];

    // ── Planet symbols ────────────────────────────────────────────
    private const SYMS = [
        'sun'     => '☀',
        'moon'    => '☽',
        'mercury' => '☿',
        'venus'   => '♀',
        'mars'    => '♂',
        'jupiter' => '♃',
        'saturn'  => '♄',
        'rahu'    => '☊',
        'ketu'    => '☋',
    ];

    // ── Full planet names ─────────────────────────────────────────
    private const FULL_NAMES = [
        'sun'     => 'Sun (Surya)',
        'moon'    => 'Moon (Chandra)',
        'mercury' => 'Mercury (Budha)',
        'venus'   => 'Venus (Shukra)',
        'mars'    => 'Mars (Mangala)',
        'jupiter' => 'Jupiter (Guru)',
        'saturn'  => 'Saturn (Shani)',
        'rahu'    => 'Rahu (N. Node)',
        'ketu'    => 'Ketu (S. Node)',
    ];

    // ── Planet details for the Details tab ───────────────────────
    private const LORD_DETAILS = [
        'sun' => [
            'nature'   => 'Natural Malefic (Krura)',
            'rules'    => 'Leo (Simha)',
            'exalt'    => 'Aries 10°',
            'debil'    => 'Libra 10°',
            'signif'   => 'Soul, Father, Authority, Government, Health, Vitality, Bones, Eyes',
            'themes'   => 'Leadership, self-confidence, career advancement, recognition, father/authority figures, health of the heart and spine. A strong Sun Dasha brings fame and authority; afflicted Sun brings ego conflicts or health setbacks.',
            'gemstone' => 'Ruby',
            'metal'    => 'Gold',
            'day'      => 'Sunday',
            'num'      => '1',
        ],
        'moon' => [
            'nature'   => 'Natural Benefic (when waxing)',
            'rules'    => 'Cancer (Karka)',
            'exalt'    => 'Taurus 3°',
            'debil'    => 'Scorpio 3°',
            'signif'   => 'Mind, Mother, Emotions, Fluids, Public, Home, Fertility, Nourishment',
            'themes'   => 'Emotional well-being, family matters, public popularity, travel, mother or maternal figures, home and property. Moon Dasha heightens sensitivity and intuition; afflicted Moon can bring mood swings or domestic disturbances.',
            'gemstone' => 'Pearl / Moonstone',
            'metal'    => 'Silver',
            'day'      => 'Monday',
            'num'      => '2',
        ],
        'mercury' => [
            'nature'   => 'Natural Benefic (when alone)',
            'rules'    => 'Gemini & Virgo',
            'exalt'    => 'Virgo 15°',
            'debil'    => 'Pisces 15°',
            'signif'   => 'Intellect, Communication, Trade, Siblings, Skin, Nervous System, Education',
            'themes'   => 'Business, writing, learning, analytical work, communication skills, siblings, short travels. Mercury Dasha is excellent for education and commerce; afflicted Mercury can cause confusion or disputes in agreements.',
            'gemstone' => 'Emerald',
            'metal'    => 'Bronze / Brass',
            'day'      => 'Wednesday',
            'num'      => '5',
        ],
        'venus' => [
            'nature'   => 'Natural Benefic (Saumya)',
            'rules'    => 'Taurus & Libra',
            'exalt'    => 'Pisces 27°',
            'debil'    => 'Virgo 27°',
            'signif'   => 'Love, Relationships, Luxury, Arts, Vehicles, Comforts, Wife/Partner, Semen',
            'themes'   => 'Romance, marriage, creativity, beauty, material comforts, artistic expression. Venus Dasha generally brings pleasures and partnerships; afflicted Venus may bring relationship complications or excessive indulgence.',
            'gemstone' => 'Diamond / White Sapphire',
            'metal'    => 'Silver / Copper',
            'day'      => 'Friday',
            'num'      => '6',
        ],
        'mars' => [
            'nature'   => 'Natural Malefic (Krura)',
            'rules'    => 'Aries & Scorpio',
            'exalt'    => 'Capricorn 28°',
            'debil'    => 'Cancer 28°',
            'signif'   => 'Energy, Courage, Brothers, Land, Accidents, Surgery, Engineering, Military',
            'themes'   => 'Physical strength, courage, competition, real estate, siblings, technical or engineering work. Mars Dasha builds drive and ambition; afflicted Mars can bring accidents, conflict, or impulsive decisions.',
            'gemstone' => 'Red Coral',
            'metal'    => 'Copper / Iron',
            'day'      => 'Tuesday',
            'num'      => '9',
        ],
        'jupiter' => [
            'nature'   => 'Natural Benefic — Greatest Benefic',
            'rules'    => 'Sagittarius & Pisces',
            'exalt'    => 'Cancer 5°',
            'debil'    => 'Capricorn 5°',
            'signif'   => 'Wisdom, Dharma, Children, Guru, Wealth, Religion, Law, Higher Knowledge',
            'themes'   => 'Spiritual growth, wisdom, children, prosperity, legal matters, higher education, teachers. Jupiter Dasha is typically auspicious and expansive; even an afflicted Jupiter Dasha carries some protective grace.',
            'gemstone' => 'Yellow Sapphire',
            'metal'    => 'Gold',
            'day'      => 'Thursday',
            'num'      => '3',
        ],
        'saturn' => [
            'nature'   => 'Natural Malefic (Krura) — Slow, Karmic',
            'rules'    => 'Capricorn & Aquarius',
            'exalt'    => 'Libra 20°',
            'debil'    => 'Aries 20°',
            'signif'   => 'Karma, Discipline, Longevity, Servants, Delays, Hardship, Renunciation',
            'themes'   => 'Hard work, discipline, karmic lessons, service, chronic conditions, old age matters, losses followed by eventual gains. Saturn Dasha (Sade Sati in transit too) is demanding but ultimately purifying for the sincere.',
            'gemstone' => 'Blue Sapphire / Amethyst',
            'metal'    => 'Iron / Steel',
            'day'      => 'Saturday',
            'num'      => '8',
        ],
        'rahu' => [
            'nature'   => 'Shadow Planet — Malefic by nature',
            'rules'    => 'Co-rules Aquarius (some traditions)',
            'exalt'    => 'Gemini / Taurus (traditions vary)',
            'debil'    => 'Sagittarius / Scorpio (traditions vary)',
            'signif'   => 'Obsession, Foreign, Technology, Ambition, Illusion, Politics, Sudden Gains',
            'themes'   => 'Worldly ambition, foreign connections, unconventional paths, technology, sudden events, materialism. Rahu Dasha can bring rapid rise in material life; it amplifies whatever planet or house it influences.',
            'gemstone' => 'Hessonite Garnet (Gomed)',
            'metal'    => 'Lead / Mixed metals',
            'day'      => 'Saturday (shared)',
            'num'      => '4',
        ],
        'ketu' => [
            'nature'   => 'Shadow Planet — Malefic / Moksha Karaka',
            'rules'    => 'Co-rules Scorpio (some traditions)',
            'exalt'    => 'Sagittarius / Scorpio (traditions vary)',
            'debil'    => 'Gemini / Taurus (traditions vary)',
            'signif'   => 'Spirituality, Detachment, Past Lives, Liberation, Mysticism, Accidents, Isolation',
            'themes'   => 'Spiritual seeking, detachment from material life, psychic sensitivity, past-life karma surfacing, sudden losses that lead to liberation. Ketu Dasha is often mysterious; blessings come through letting go.',
            'gemstone' => "Cat's Eye (Lehsunia)",
            'metal'    => 'Iron / Mixed metals',
            'day'      => 'Tuesday (shared)',
            'num'      => '7',
        ],
    ];

    // ════════════════════════════════════════════════════════════
    //  PRIMARY ENTRY POINT
    // ════════════════════════════════════════════════════════════

    public static function calculate(
        float $moonSiderLon,
        int   $birthYear,
        int   $birthMonth,
        int   $birthDay,
        float $birthHour = 0.0
    ): array {
        $nakSz    = 360.0 / 27.0;
        $nakIdx   = (int)floor($moonSiderLon / $nakSz);
        $nakProg  = fmod($moonSiderLon, $nakSz) / $nakSz;

        $lordSeqIdx = self::NAK_LORD_IDX[$nakIdx];
        $birthLord  = self::LORDS[$lordSeqIdx];

        $lordDays      = self::LORD_DAYS[$birthLord];
        $elapsedDays   = $nakProg * $lordDays;
        $remainingDays = $lordDays - $elapsedDays;

        $birthJD = self::toJD($birthYear, $birthMonth, $birthDay, $birthHour);

        $mahadashas = self::buildMahadashas($birthJD, $birthLord, $remainingDays);

        $currentJD = self::toJD(
            (int)date('Y'), (int)date('n'), (int)date('j'),
            (float)date('G') + (float)date('i') / 60.0
        );
        $current = self::findCurrent($mahadashas, $currentJD);

        return [
            'moonSiderLon'  => $moonSiderLon,
            'nakIdx'        => $nakIdx,
            'nakName'       => self::NAK_NAMES[$nakIdx],
            'nakProg'       => round($nakProg * 100, 2),
            'birthLord'     => $birthLord,
            'birthLordYrs'  => self::YEARS[$birthLord],
            'elapsedDays'   => round($elapsedDays, 2),
            'remainingDays' => round($remainingDays, 2),
            'remainingStr'  => self::daysToYMD($remainingDays),
            'lordSeqIdx'    => $lordSeqIdx,
            'mahadashas'    => $mahadashas,
            'current'       => $current,
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD MAHADASHA SEQUENCE
    // ════════════════════════════════════════════════════════════

    private static function buildMahadashas(
        float  $birthJD,
        string $startLord,
        float  $firstLordRemainingDays
    ): array {
        $mahadashas = [];
        $startIdx   = array_search($startLord, self::LORDS);
        $currentJD  = $birthJD;

        for ($i = 0; $i < 9; $i++) {
            $idx     = ($startIdx + $i) % 9;
            $lord    = self::LORDS[$idx];
            $days    = ($i === 0) ? $firstLordRemainingDays : self::LORD_DAYS[$lord];
            $endJD   = $currentJD + $days;

            $antars  = self::buildAntardashas($lord, $currentJD, $days);

            $mahadashas[] = [
                'lord'       => $lord,
                'abbr'       => self::ABBR[$lord],
                'years'      => self::YEARS[$lord],
                'days'       => round($days, 2),
                'durationStr'=> self::daysToYMD($days),
                'startJD'    => $currentJD,
                'endJD'      => $endJD,
                'startDate'  => self::jdToDate($currentJD),
                'endDate'    => self::jdToDate($endJD),
                'color'      => self::COLORS[$lord],
                'antars'     => $antars,
            ];

            $currentJD = $endJD;
        }

        return $mahadashas;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD ANTARDASHA (Bhukti) SEQUENCE
    // ════════════════════════════════════════════════════════════

    private static function buildAntardashas(
        string $mahaLord,
        float  $mahaStartJD,
        float  $mahaDays
    ): array {
        $antars   = [];
        $startIdx = array_search($mahaLord, self::LORDS);
        $currentJD = $mahaStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx       = ($startIdx + $i) % 9;
            $antarLord = self::LORDS[$idx];
            $antarDays = (self::YEARS[$mahaLord] * self::YEARS[$antarLord] / 120.0) * 365.25;
            $endJD     = $currentJD + $antarDays;

            $pratyantars = self::buildPratyantardashas($mahaLord, $antarLord, $currentJD, $antarDays);

            $antars[] = [
                'lord'       => $antarLord,
                'abbr'       => self::ABBR[$antarLord],
                'days'       => round($antarDays, 2),
                'durationStr'=> self::daysToYMD($antarDays),
                'startJD'    => $currentJD,
                'endJD'      => $endJD,
                'startDate'  => self::jdToDate($currentJD),
                'endDate'    => self::jdToDate($endJD),
                'color'      => self::COLORS[$antarLord],
                'pratyantars'=> $pratyantars,
            ];

            $currentJD = $endJD;
        }

        return $antars;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD PRATYANTAR DASHA
    // ════════════════════════════════════════════════════════════

    private static function buildPratyantardashas(
        string $mahaLord,
        string $antarLord,
        float  $antarStartJD,
        float  $antarDays
    ): array {
        $pratyantars = [];
        $startIdx    = array_search($antarLord, self::LORDS);
        $currentJD   = $antarStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx      = ($startIdx + $i) % 9;
            $pratLord = self::LORDS[$idx];
            $pratDays = ($antarDays * self::YEARS[$pratLord]) / 120.0;
            $endJD    = $currentJD + $pratDays;

            $sookshmas = self::buildSookshmaDashas($antarLord, $pratLord, $currentJD, $pratDays);

            $pratyantars[] = [
                'lord'       => $pratLord,
                'abbr'       => self::ABBR[$pratLord],
                'days'       => round($pratDays, 2),
                'durationStr'=> self::daysToYMD($pratDays),
                'startJD'    => $currentJD,
                'endJD'      => $endJD,
                'startDate'  => self::jdToDate($currentJD),
                'endDate'    => self::jdToDate($endJD),
                'color'      => self::COLORS[$pratLord],
                'sookshmas'  => $sookshmas,
            ];

            $currentJD = $endJD;
        }

        return $pratyantars;
    }

    // ════════════════════════════════════════════════════════════
    //  BUILD SOOKSHMA DASHA (4th level)
    // ════════════════════════════════════════════════════════════

    private static function buildSookshmaDashas(
        string $antarLord,
        string $pratLord,
        float  $pratStartJD,
        float  $pratDays
    ): array {
        $sookshmas = [];
        $startIdx  = array_search($pratLord, self::LORDS);
        $currentJD = $pratStartJD;

        for ($i = 0; $i < 9; $i++) {
            $idx        = ($startIdx + $i) % 9;
            $sookLord   = self::LORDS[$idx];
            $sookDays   = ($pratDays * self::YEARS[$sookLord]) / 120.0;
            $endJD      = $currentJD + $sookDays;

            $sookshmas[] = [
                'lord'       => $sookLord,
                'abbr'       => self::ABBR[$sookLord],
                'days'       => round($sookDays, 4),
                'durationStr'=> self::daysToYMD($sookDays),
                'startJD'    => $currentJD,
                'endJD'      => $endJD,
                'startDate'  => self::jdToDate($currentJD),
                'endDate'    => self::jdToDate($endJD),
                'color'      => self::COLORS[$sookLord],
            ];

            $currentJD = $endJD;
        }

        return $sookshmas;
    }

    // ════════════════════════════════════════════════════════════
    //  FIND CURRENT DASHA (up to Sookshma level)
    // ════════════════════════════════════════════════════════════

    public static function findCurrent(array $mahadashas, float $currentJD): array
    {
        $result = [
            'maha'       => null,
            'antar'      => null,
            'pratyantar' => null,
            'sookshma'   => null,
            'elapsed'    => null,
        ];

        foreach ($mahadashas as $maha) {
            if ($currentJD < $maha['startJD'] || $currentJD >= $maha['endJD']) continue;
            $result['maha'] = $maha;
            $elapsed = ($currentJD - $maha['startJD']) / ($maha['endJD'] - $maha['startJD']) * 100;
            $result['elapsed'] = round($elapsed, 2);

            foreach ($maha['antars'] as $antar) {
                if ($currentJD < $antar['startJD'] || $currentJD >= $antar['endJD']) continue;
                $result['antar'] = $antar;

                foreach ($antar['pratyantars'] as $prat) {
                    if ($currentJD < $prat['startJD'] || $currentJD >= $prat['endJD']) continue;
                    $result['pratyantar'] = $prat;

                    foreach ($prat['sookshmas'] as $sook) {
                        if ($currentJD < $sook['startJD'] || $currentJD >= $sook['endJD']) continue;
                        $result['sookshma'] = $sook;
                        break;
                    }
                    break;
                }
                break;
            }
            break;
        }

        return $result;
    }

    // ════════════════════════════════════════════════════════════
    //  PLANET DETAILS TAB HTML
    // ════════════════════════════════════════════════════════════

    private static function renderDetailsTab(string $lord, array $c, string $sym): string
    {
        $d = self::LORD_DETAILS[$lord] ?? null;
        if (!$d) return '';

        $html  = '<div style="padding:14px 18px 16px;background:#fafbfc;border-top:1px solid '.$c['border'].'">';
        $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 20px;margin-bottom:12px">';

        $chips = [
            ['Nature',    $d['nature']],
            ['Rules',     $d['rules']],
            ['Exaltation',$d['exalt']],
            ['Debilitation',$d['debil']],
            ['Gemstone',  $d['gemstone']],
            ['Metal',     $d['metal']],
            ['Best Day',  $d['day']],
            ['Number',    $d['num']],
        ];
        foreach ($chips as [$label, $val]) {
            $html .= '<div style="display:flex;flex-direction:column;gap:2px">'
                   . '<span style="font-size:.58rem;text-transform:uppercase;letter-spacing:.9px;'
                   . 'font-weight:800;color:'.$c['accent'].'">'.$label.'</span>'
                   . '<span style="font-size:.75rem;font-weight:600;color:#1a2535">'
                   . htmlspecialchars($val).'</span>'
                   . '</div>';
        }

        $html .= '</div>';

        // Significations
        $html .= '<div style="background:'.$c['light'].';border-radius:8px;padding:10px 12px;margin-bottom:10px;border:1px solid '.$c['border'].'">'
               . '<div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;color:'.$c['accent'].';margin-bottom:5px">Significations (Karakatwa)</div>'
               . '<div style="font-size:.75rem;font-weight:600;color:#1a2535;line-height:1.5">'
               . htmlspecialchars($d['signif']).'</div>'
               . '</div>';

        // Themes
        $html .= '<div style="background:#fff;border-radius:8px;padding:10px 12px;border:1px solid '.$c['border'].'">'
               . '<div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;color:'.$c['accent'].';margin-bottom:5px">Dasha Themes &amp; Effects</div>'
               . '<div style="font-size:.75rem;color:#283040;line-height:1.65">'
               . htmlspecialchars($d['themes']).'</div>'
               . '</div>';

        $html .= '</div>';
        return $html;
    }

    // ════════════════════════════════════════════════════════════
    //  HTML RENDERER — full nested Mahadasha → Antar → Pratyantar → Sookshma
    // ════════════════════════════════════════════════════════════

    public static function renderHtml(array $data, bool $showAll = false): string
    {
        $today = self::toJD((int)date('Y'), (int)date('n'), (int)date('j'), 12.0);

        $sc    = fn(string $lord): array => self::SOFT[$lord]
            ?? ['bg'=>'#f4f4f4','border'=>'#d0d0d0','text'=>'#1a2535','accent'=>'#444','light'=>'#f0f0f0'];
        $sym   = fn(string $lord): string => self::SYMS[$lord] ?? '◈';
        $fname = fn(string $lord): string => self::FULL_NAMES[$lord] ?? ucfirst($lord);

        // Unique prefix to avoid ID collisions if rendered multiple times
        $uid = substr(md5(uniqid('', true)), 0, 6);

        $html = '';

        // ── 1. Header birth balance card ─────────────────────────
        $bl  = $data['remainingStr'];
        $bc  = $sc($data['birthLord']);

        $html .= '<div style="background:'.$bc['bg'].';border-radius:14px;padding:18px 22px;'
               . 'margin-bottom:16px;border:1.5px solid '.$bc['border'].';border-left:4px solid '.$bc['accent'].'">'
               . '<div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;'
               . 'color:'.$bc['accent'].';margin-bottom:12px">◈ Vimshottari Dasha — 120-Year Cycle</div>'
               . '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px">'
               . self::_infoChip('Moon Nakshatra',    $sym($data['birthLord']).' '.htmlspecialchars($data['nakName']), $bc)
               . self::_infoChip('Nakshatra elapsed', $data['nakProg'].'%', $bc)
               . self::_infoChip('Birth Dasha lord',  strtoupper($data['birthLord']).' ('.self::YEARS[$data['birthLord']].' yrs)', $bc)
               . self::_infoChip('Balance at birth',  $bl['y'].'y '.$bl['m'].'m '.$bl['d'].'d', $bc)
               . '</div></div>';

        // ── 2. Current dasha summary card ─────────────────────────
        $cur = $data['current'];
        if ($cur['maha']) {
            $mc = $sc($cur['maha']['lord']);

            $html .= '<div style="background:'.$mc['bg'].';border-radius:14px;padding:16px 20px;'
                   . 'margin-bottom:20px;border:1.5px solid '.$mc['border'].';border-left:4px solid '.$mc['accent'].'">'
                   . '<div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;'
                   . 'color:'.$mc['accent'].';margin-bottom:12px">▶ Currently Active Dasha — All Levels</div>'
                   . '<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:stretch;margin-bottom:14px">';

            foreach ([
                ['Mahadasha',           $cur['maha'],       'Major period (~'.self::YEARS[$cur['maha']['lord']].' yrs)'],
                ['Antardasha',          $cur['antar'],      'Sub-period (Bhukti)'],
                ['Pratyantar Dasha',    $cur['pratyantar'], 'Sub-sub period'],
                ['Sookshma Dasha',      $cur['sookshma'],   '4th level period'],
            ] as [$lbl, $period, $hint]) {
                if (!$period) continue;
                $pc = $sc($period['lord']);
                $html .= '<div style="background:#fff;border-radius:10px;padding:10px 14px;'
                       . 'min-width:130px;border:1.5px solid '.$pc['border'].';flex:1">'
                       . '<div style="font-size:.58rem;color:'.$pc['accent'].';font-weight:800;'
                       . 'text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px">'.$lbl.'</div>'
                       . '<div style="display:flex;align-items:center;gap:6px;margin-bottom:5px">'
                       . '<span style="font-size:1.2rem;line-height:1">'.$sym($period['lord']).'</span>'
                       . '<div>'
                       . '<div style="color:'.$pc['accent'].';font-size:1rem;font-weight:900;line-height:1">'
                       . strtoupper($period['lord']).'</div>'
                       . '<div style="font-size:.65rem;color:'.$pc['text'].';font-weight:600">'
                       . htmlspecialchars($fname($period['lord'])).'</div>'
                       . '</div></div>'
                       . '<div style="font-size:.65rem;color:#3a4a5a;line-height:1.6;font-weight:500;border-top:1px solid '.$pc['border'].';padding-top:5px">'
                       . $period['startDate'].' → '.$period['endDate'].'<br>'
                       . '<span style="color:'.$pc['accent'].';font-weight:700">'
                       . $period['durationStr']['y'].'y '.$period['durationStr']['m'].'m total</span>'
                       . '</div>'
                       . '</div>';
            }

            $html .= '</div>';

            // Mahadasha progress bar
            if ($cur['elapsed'] !== null) {
                $remaining = number_format(100 - $cur['elapsed'], 1);
                $html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">'
                       . '<span style="font-size:.68rem;color:'.$mc['accent'].';font-weight:700">'
                       . strtoupper($cur['maha']['lord']).' Mahadasha — '.$cur['elapsed'].'% elapsed</span>'
                       . '<span style="font-size:.68rem;color:#3a4a5a;font-weight:600">'.$remaining.'% remaining · ends '.$cur['maha']['endDate'].'</span>'
                       . '</div>'
                       . '<div style="height:7px;background:rgba(0,0,0,.1);border-radius:4px;overflow:hidden">'
                       . '<div style="width:'.$cur['elapsed'].'%;height:100%;background:'.$mc['accent'].';border-radius:4px"></div>'
                       . '</div>';

                // Antardasha progress within current maha
                if ($cur['antar']) {
                    $antarElapsed = round(($today - $cur['antar']['startJD']) / ($cur['antar']['endJD'] - $cur['antar']['startJD']) * 100, 1);
                    $ac2 = $sc($cur['antar']['lord']);
                    $html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;margin-bottom:4px">'
                           . '<span style="font-size:.66rem;color:'.$ac2['accent'].';font-weight:700">'
                           . strtoupper($cur['antar']['lord']).' Antardasha — '.$antarElapsed.'% elapsed</span>'
                           . '<span style="font-size:.66rem;color:#3a4a5a;font-weight:600">ends '.$cur['antar']['endDate'].'</span>'
                           . '</div>'
                           . '<div style="height:5px;background:rgba(0,0,0,.08);border-radius:3px;overflow:hidden">'
                           . '<div style="width:'.$antarElapsed.'%;height:100%;background:'.$ac2['accent'].';border-radius:3px;opacity:.85"></div>'
                           . '</div>';
                }
            }

            $html .= '</div>';
        }

        // ── 3. Mahadasha sequence ─────────────────────────────────
        $html .= '<div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;'
               . 'color:#2a3a4a;margin-bottom:12px">◈ All 9 Mahadashas — Complete 120-Year Sequence</div>'
               . '<div style="font-size:.72rem;color:#3a4a5a;font-weight:500;margin-bottom:14px">'
               . 'Click any Mahadasha row to expand Antardasha sub-periods. '
               . 'Active periods are pre-expanded and highlighted.</div>';

        foreach ($data['mahadashas'] as $mIdx => $maha) {
            $isPast    = $maha['endJD']   < $today;
            $isCurrent = $maha['startJD'] <= $today && $maha['endJD'] > $today;
            $rc        = $sc($maha['lord']);
            $op        = $isPast ? '.60' : '1';

            $htmlId = $uid.'_maha_'.$mIdx;

            // Mahadasha block
            $html .= '<div style="border-radius:14px;margin-bottom:8px;opacity:'.$op.';overflow:hidden;'
                   . 'border:1.5px solid '.($isCurrent ? $rc['border'] : ($isPast ? '#dde0e6' : '#e0e4ec')).';'
                   . 'border-left:4px solid '.($isCurrent ? $rc['accent'] : ($isPast ? '#b8c0cc' : '#c8d0de')).'">';

            // Clickable header — tabs: Antardashas | Details
            $html .= '<div id="'.$htmlId.'_hdr" style="background:'.($isCurrent ? $rc['bg'] : ($isPast ? '#f5f6f8' : '#f8f9fb')).';'
                   . 'padding:14px 18px;cursor:pointer;display:flex;align-items:center;gap:14px;'
                   . 'user-select:none">';

            // Lord badge
            $html .= '<div style="background:'.$rc['bg'].';border:1.5px solid '.$rc['border'].';'
                   . 'border-radius:10px;padding:8px 14px;text-align:center;flex-shrink:0;min-width:54px">'
                   . '<div style="font-size:1.2rem;line-height:1;margin-bottom:3px">'.$sym($maha['lord']).'</div>'
                   . '<div style="color:'.$rc['accent'].';font-size:.75rem;font-weight:900;line-height:1">'.$maha['abbr'].'</div>'
                   . '</div>';

            // Title
            $html .= '<div style="flex:1;min-width:0">'
                   . '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">'
                   . '<span style="font-size:.95rem;font-weight:800;color:#0f1c2d">'
                   . htmlspecialchars($fname($maha['lord'])).' Mahadasha</span>';

            if ($isCurrent) {
                $html .= '<span style="background:'.$rc['accent'].';color:#fff;font-size:.58rem;'
                       . 'padding:2px 9px;border-radius:20px;font-weight:800">▶ ACTIVE NOW</span>';
            } elseif ($isPast) {
                $html .= '<span style="background:#dde2e8;color:#3a4a5a;font-size:.58rem;'
                       . 'padding:2px 8px;border-radius:20px;font-weight:700">PAST</span>';
            } else {
                $html .= '<span style="background:'.$rc['light'].';color:'.$rc['accent'].';font-size:.58rem;'
                       . 'padding:2px 8px;border-radius:20px;font-weight:700">UPCOMING</span>';
            }

            $html .= '</div>'
                   . '<div style="font-size:.75rem;color:#3a4a5a;font-weight:600">'
                   . $maha['startDate'].' → '.$maha['endDate']
                   . ' &nbsp;·&nbsp; '.self::YEARS[$maha['lord']].' yrs total'
                   . ' &nbsp;·&nbsp; '.$maha['durationStr']['y'].'y '.$maha['durationStr']['m'].'m in this cycle'
                   . '</div></div>';

            // Tab buttons (Antardashas + Details)
            $html .= '<div style="display:flex;gap:6px;flex-shrink:0">'
                   // Antardashas button
                   . '<button id="'.$htmlId.'_btn_antar" onclick="(function(e){e.stopPropagation();'
                   . 'var b=document.getElementById(\''.$htmlId.'_body\');'
                   . 'var d=document.getElementById(\''.$htmlId.'_det\');'
                   . 'var open=b.style.display!==\'none\';'
                   . 'b.style.display=open?\'none\':\'block\';'
                   . 'd.style.display=\'none\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_antar\').style.background=open?\''.$rc['light'].'\':\''.addslashes($rc['accent']).'\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_antar\').style.color=open?\''.$rc['accent'].'\':\'#fff\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_det\').style.background=\''.$rc['light'].'\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_det\').style.color=\''.$rc['accent'].'\';'
                   . '})(event)" '
                   . 'style="color:'.($isCurrent ? '#fff' : $rc['accent']).';font-size:.7rem;font-weight:800;'
                   . 'flex-shrink:0;padding:5px 12px;background:'.($isCurrent ? $rc['accent'] : $rc['light']).';'
                   . 'border-radius:20px;border:1.5px solid '.$rc['border'].';cursor:pointer;white-space:nowrap">'
                   . ($isCurrent ? '▼ Antardashas' : '▼ Antardashas')
                   . '</button>'
                   // Details button
                   . '<button id="'.$htmlId.'_btn_det" onclick="(function(e){e.stopPropagation();'
                   . 'var b=document.getElementById(\''.$htmlId.'_body\');'
                   . 'var d=document.getElementById(\''.$htmlId.'_det\');'
                   . 'var open=d.style.display!==\'none\';'
                   . 'd.style.display=open?\'none\':\'block\';'
                   . 'b.style.display=\'none\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_det\').style.background=open?\''.$rc['light'].'\':\''.addslashes($rc['accent']).'\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_det\').style.color=open?\''.$rc['accent'].'\':\'#fff\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_antar\').style.background=\''.$rc['light'].'\';'
                   . 'document.getElementById(\''.$htmlId.'_btn_antar\').style.color=\''.$rc['accent'].'\';'
                   . '})(event)" '
                   . 'style="color:'.$rc['accent'].';font-size:.7rem;font-weight:800;'
                   . 'flex-shrink:0;padding:5px 12px;background:'.$rc['light'].';'
                   . 'border-radius:20px;border:1.5px solid '.$rc['border'].';cursor:pointer;white-space:nowrap">'
                   . '◈ Details'
                   . '</button>'
                   . '</div>';

            $html .= '</div>'; // end header

            // ── Details panel ─────────────────────────────────────
            $html .= '<div id="'.$htmlId.'_det" style="display:none">'
                   . self::renderDetailsTab($maha['lord'], $rc, $sym($maha['lord']))
                   . '</div>';

            // ── Antardasha body ───────────────────────────────────
            $html .= '<div id="'.$htmlId.'_body" style="display:'.($isCurrent ? 'block' : 'none').';'
                   . 'background:#f8f9fb;border-top:1px solid '.($isCurrent ? $rc['border'] : '#e0e4ec').'">';

            // Column headers
            $html .= '<div style="display:grid;grid-template-columns:48px 1fr 100px 100px 100px 90px;'
                   . 'padding:10px 18px 8px;gap:0;font-size:.6rem;text-transform:uppercase;letter-spacing:1px;'
                   . 'font-weight:800;color:#3a4a5a;border-bottom:1px solid #dde2ea">'
                   . '<div></div>'
                   . '<div>Antardasha (Bhukti)</div>'
                   . '<div style="text-align:center">Duration</div>'
                   . '<div style="text-align:center">Start date</div>'
                   . '<div style="text-align:center">End date</div>'
                   . '<div style="text-align:center">Info</div>'
                   . '</div>';

            foreach ($maha['antars'] as $aIdx => $antar) {
                $isCA    = $antar['startJD'] <= $today && $antar['endJD'] > $today;
                $isPastA = $antar['endJD'] < $today;
                $ac      = $sc($antar['lord']);
                $antarId = $uid.'_antar_'.$mIdx.'_'.$aIdx;

                $html .= '<div style="overflow:hidden;border-bottom:1px solid #e8ecf0">'

                // Antardasha row
                       . '<div id="'.$antarId.'_hdr" style="display:grid;grid-template-columns:48px 1fr 100px 100px 100px 90px;'
                       . 'align-items:center;padding:10px 18px;gap:0;'
                       . 'background:'.($isCA ? $ac['bg'] : 'transparent').';'
                       . 'opacity:'.($isPastA ? '.60' : '1').'">'

                       // Symbol
                       . '<div style="font-size:1.1rem;line-height:1;text-align:center">'.$sym($antar['lord']).'</div>'

                       // Name
                       . '<div>'
                       . '<span style="font-size:.82rem;font-weight:800;color:'.$ac['accent'].'">'
                       . strtoupper($antar['lord']).'</span>'
                       . ' <span style="font-size:.72rem;font-weight:600;color:#283040">'
                       . htmlspecialchars($fname($antar['lord'])).'</span>'
                       . ($isCA ? ' <span style="font-size:.55rem;background:'.$ac['accent'].';color:#fff;'
                           . 'padding:1px 6px;border-radius:10px;font-weight:800;margin-left:3px">▶ ACTIVE</span>' : '')
                       . '</div>'

                       // Duration
                       . '<div style="text-align:center;font-size:.72rem;color:#283040;font-weight:600">'
                       . $antar['durationStr']['y'].'y '.$antar['durationStr']['m'].'m</div>'

                       // Start
                       . '<div style="text-align:center;font-size:.7rem;color:#283040;font-weight:600;font-family:monospace">'
                       . $antar['startDate'].'</div>'

                       // End
                       . '<div style="text-align:center;font-size:.7rem;color:#283040;font-weight:600;font-family:monospace">'
                       . $antar['endDate'].'</div>'

                       // Expand + Details buttons
                       . '<div style="text-align:center;display:flex;gap:4px;justify-content:center">'

                       // Sub-periods toggle
                       . '<button id="'.$antarId.'_btn_sub" onclick="(function(e){e.stopPropagation();'
                       . 'var b=document.getElementById(\''.$antarId.'_body\');'
                       . 'var d=document.getElementById(\''.$antarId.'_det\');'
                       . 'var open=b.style.display!==\'none\';'
                       . 'b.style.display=open?\'none\':\'block\';'
                       . 'd.style.display=\'none\';'
                       . '})(event)" '
                       . 'style="font-size:.6rem;font-weight:800;padding:3px 7px;'
                       . 'background:'.($isCA ? $ac['accent'] : $ac['light']).';'
                       . 'color:'.($isCA ? '#fff' : $ac['accent']).';'
                       . 'border-radius:12px;border:1px solid '.$ac['border'].';cursor:pointer;white-space:nowrap">'
                       . '▼ Sub</button>'

                       // Details toggle
                       . '<button id="'.$antarId.'_btn_det" onclick="(function(e){e.stopPropagation();'
                       . 'var b=document.getElementById(\''.$antarId.'_body\');'
                       . 'var d=document.getElementById(\''.$antarId.'_det\');'
                       . 'var open=d.style.display!==\'none\';'
                       . 'd.style.display=open?\'none\':\'block\';'
                       . 'b.style.display=\'none\';'
                       . '})(event)" '
                       . 'style="font-size:.6rem;font-weight:800;padding:3px 7px;'
                       . 'background:'.$ac['light'].';color:'.$ac['accent'].';'
                       . 'border-radius:12px;border:1px solid '.$ac['border'].';cursor:pointer">'
                       . '◈</button>'
                       . '</div>'

                       . '</div>'; // end antar row

                // ── Antardasha Details panel ──────────────────────
                $html .= '<div id="'.$antarId.'_det" style="display:none">'
                       . self::renderDetailsTab($antar['lord'], $ac, $sym($antar['lord']))
                       . '</div>';

                // ── Pratyantar body ───────────────────────────────
                $html .= '<div id="'.$antarId.'_body" style="display:'.($isCA ? 'block' : 'none').';'
                       . 'background:'.($isCA ? '#fff' : '#f8f9fb').';'
                       . 'border-top:1px solid '.$ac['border'].';padding:12px 18px 14px 66px">';

                $html .= '<div style="font-size:.6rem;text-transform:uppercase;letter-spacing:1px;font-weight:800;'
                       . 'color:'.$ac['accent'].';margin-bottom:10px">Pratyantar Dasha — Sub-Sub Periods</div>';

                $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:6px">';

                foreach ($antar['pratyantars'] as $pIdx => $prat) {
                    $isCP    = $prat['startJD'] <= $today && $prat['endJD'] > $today;
                    $isPastP = $prat['endJD'] < $today;
                    $pc      = $sc($prat['lord']);
                    $pratId  = $uid.'_prat_'.$mIdx.'_'.$aIdx.'_'.$pIdx;

                    $html .= '<div style="border-radius:8px;overflow:hidden;'
                           . 'background:'.($isCP ? $pc['bg'] : ($isPastP ? '#f2f4f6' : '#f7f8fa')).';'
                           . 'border:1.5px solid '.($isCP ? $pc['border'] : '#dde2e8').';'
                           . 'opacity:'.($isPastP ? '.60' : '1').'">';

                    // Card header row
                    $html .= '<div style="padding:9px 12px 0">'
                           . '<div style="display:flex;align-items:center;gap:6px;margin-bottom:5px">'
                           . '<span style="font-size:1rem;line-height:1">'.$sym($prat['lord']).'</span>'
                           . '<span style="font-size:.82rem;font-weight:800;color:'.$pc['accent'].'">'
                           . strtoupper($prat['lord']).'</span>'
                           . '<span style="font-size:.7rem;font-weight:600;color:#283040">'
                           . htmlspecialchars($fname($prat['lord'])).'</span>'
                           . ($isCP ? '<span style="font-size:.55rem;background:'.$pc['accent'].';color:#fff;'
                               . 'padding:1px 6px;border-radius:10px;font-weight:800;margin-left:auto">▶</span>' : '')
                           . '</div>'

                           . '<div style="font-size:.66rem;color:#283040;font-weight:600;line-height:1.6;'
                           . 'border-top:1px solid '.($isCP ? $pc['border'] : '#e4e8ec').';padding-top:5px">'
                           . $prat['startDate'].' → '.$prat['endDate'].'<br>'
                           . '<span style="color:'.$pc['accent'].';font-weight:700">'
                           . $prat['durationStr']['m'].'m '.$prat['durationStr']['d'].'d</span>'
                           . '</div>';

                    // Details button for pratyantar
                    $html .= '<div style="padding:5px 0 6px">'
                           . '<button id="'.$pratId.'_btn" onclick="(function(e){e.stopPropagation();'
                           . 'var d=document.getElementById(\''.$pratId.'_det\');'
                           . 'var open=d.style.display!==\'none\';'
                           . 'd.style.display=open?\'none\':\'block\';'
                           . 'this.textContent=open?\'◈ Details\':\'▲ Close\';'
                           . '})(event)" '
                           . 'style="font-size:.6rem;font-weight:800;padding:2px 8px;'
                           . 'background:'.$pc['light'].';color:'.$pc['accent'].';'
                           . 'border-radius:10px;border:1px solid '.$pc['border'].';cursor:pointer">'
                           . '◈ Details</button>'
                           . '</div>'
                           . '</div>'; // end card header padding

                    // Pratyantar details panel (hidden by default)
                    $html .= '<div id="'.$pratId.'_det" style="display:none">'
                           . self::renderDetailsTab($prat['lord'], $pc, $sym($prat['lord']))
                           . '</div>';

                    // Sookshma pills — only for current pratyantar
                    if ($isCP) {
                        $html .= '<div style="padding:0 12px 10px;border-top:1px solid '.$pc['border'].';margin-top:2px">'
                               . '<div style="font-size:.56rem;text-transform:uppercase;letter-spacing:.8px;'
                               . 'font-weight:800;color:'.$pc['accent'].';margin:8px 0 5px">Sookshma Dasha</div>'
                               . '<div style="display:flex;flex-wrap:wrap;gap:3px">';

                        foreach ($prat['sookshmas'] as $sook) {
                            $isCS    = $sook['startJD'] <= $today && $sook['endJD'] > $today;
                            $isPastS = $sook['endJD'] < $today;
                            $sc2     = $sc($sook['lord']);

                            $html .= '<span style="font-size:.63rem;padding:2px 8px;border-radius:12px;'
                                   . 'display:inline-flex;align-items:center;gap:3px;'
                                   . 'background:'.($isCS ? $sc2['bg'] : ($isPastS ? '#eaedf0' : '#f0f3f6')).';'
                                   . 'color:'.($isCS ? $sc2['accent'] : '#3a4a5a').';'
                                   . 'border:1px solid '.($isCS ? $sc2['border'] : '#d8dde4').';'
                                   . 'font-weight:'.($isCS ? '800' : '600').';'
                                   . 'opacity:'.($isPastS ? '.55' : '1').'">'
                                   . $sym($sook['lord']).' '.$sook['abbr']
                                   . ($isCS ? ' ▶' : '')
                                   . '<span style="font-size:.55rem;opacity:.7;font-weight:500">·'.$sook['durationStr']['d'].'d</span>'
                                   . '</span>';
                        }

                        $html .= '</div></div>';
                    }

                    $html .= '</div>'; // end pratyantar card
                }

                $html .= '</div>'; // end pratyantar grid
                $html .= '</div>'; // end antardasha body
                $html .= '</div>'; // end antardasha wrapper
            }

            $html .= '</div>'; // end mahadasha body
            $html .= '</div>'; // end mahadasha block
        }

        // ── 4. Legend / reference ─────────────────────────────────
        $html .= '<div style="background:#eef1f5;border-radius:12px;padding:16px 20px;margin-top:8px;'
               . 'border:1px solid #cdd4de">'
               . '<div style="font-size:.6rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;'
               . 'color:#3a4a5a;margin-bottom:12px">◈ Dasha Sequence &amp; Durations (BPHS Ch. 46)</div>'
               . '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">';

        foreach (self::LORDS as $lord) {
            $lc = $sc($lord);
            $html .= '<div style="display:flex;align-items:center;gap:5px;background:'.$lc['bg'].';'
                   . 'border:1px solid '.$lc['border'].';border-radius:20px;padding:4px 12px">'
                   . '<span style="font-size:.9rem">'.$sym($lord).'</span>'
                   . '<span style="font-size:.72rem;font-weight:800;color:'.$lc['accent'].'">'
                   . strtoupper($lord).'</span>'
                   . '<span style="font-size:.72rem;font-weight:600;color:'.$lc['text'].'">'
                   . self::YEARS[$lord].' yrs</span>'
                   . '</div>';
        }

        

        return $html;
    }

    // ── Helper: info chip card ────────────────────────────────────
    private static function _infoChip(string $label, string $value, array $c): string
    {
        return '<div style="background:#fff;border-radius:10px;padding:10px 14px;'
             . 'border:1px solid '.$c['border'].'">'
             . '<div style="font-size:.6rem;text-transform:uppercase;letter-spacing:1px;'
             . 'font-weight:700;color:'.$c['accent'].';margin-bottom:4px">'.$label.'</div>'
             . '<div style="font-size:.9rem;font-weight:800;color:'.$c['text'].'">'.$value.'</div>'
             . '</div>';
    }

    // ════════════════════════════════════════════════════════════
    //  UTILITIES
    // ════════════════════════════════════════════════════════════

    public static function toJD(int $y, int $m, int $d, float $h = 12.0): float
    {
        if ($m <= 2) { $y--; $m += 12; }
        $A = (int)floor($y / 100);
        $B = 2 - $A + (int)floor($A / 4);
        return floor(365.25 * ($y + 4716))
             + floor(30.6001 * ($m + 1))
             + $d + $h / 24.0 + $B - 1524.5;
    }

    public static function jdToDate(float $jd): string
    {
        $jd   += 0.5;
        $Z     = (int)$jd;
        $F     = $jd - $Z;
        $A     = ($Z < 2299161) ? $Z : (function() use ($Z) {
            $alpha = (int)(($Z - 1867216.25) / 36524.25);
            return $Z + 1 + $alpha - (int)($alpha / 4);
        })();
        $B  = $A + 1524;
        $C  = (int)(($B - 122.1) / 365.25);
        $D  = (int)(365.25 * $C);
        $E  = (int)(($B - $D) / 30.6001);
        $dy = $B - $D - (int)(30.6001 * $E);
        $mo = $E < 14 ? $E - 1 : $E - 13;
        $yr = $mo > 2  ? $C - 4716 : $C - 4715;
        return sprintf('%04d-%02d-%02d', $yr, $mo, $dy);
    }

    public static function daysToYMD(float $days): array
    {
        $y = (int)floor($days / 365.25);
        $rem = $days - $y * 365.25;
        $m = (int)floor($rem / 30.4375);
        $d = (int)round($rem - $m * 30.4375);
        if ($d >= 30) { $m++; $d = 0; }
        if ($m >= 12) { $y++; $m = 0; }
        return ['y' => $y, 'm' => $m, 'd' => $d];
    }
}