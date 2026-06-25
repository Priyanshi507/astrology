<?php

namespace App\Services\Muhurta;

use App\Services\Planetary\AstroCalculator;
use Illuminate\Support\Facades\DB;

/**
 * MuhratCalculator -- Complete Vedic Muhurta Shastra
 *
 * Source: Muhurta Chintamani, Daivagya Rama
 * Computation: Jean Meeus via AstroCalculator . Lahiri Ayanamsa
 *
 * Categories:
 *   1. Choghadiya        6. Lagna Table      11. Griha Pravesh
 *   2. Shubha Hora       7. Panchaka Rahita  12. Vahana
 *   3. Abhijit Muhurta   8. Do Ghati         13. Mundan
 *   4. Rahu Kala         9. Gowri Panchangam 14. Sampatti
 *   5. Auspicious Yoga  10. Vivah Muhurta    15. Shubha Dates
 */
class MuhratCalculator
{
    // -- Mathematical/calculation constants (Jean Meeus / BPHS) --
    // Planet friendship matrix: 0=Sun,1=Moon,2=Mars,3=Mercury,4=Jupiter,5=Venus,6=Saturn
    private const GRAHA_MITRA = [
        0 => [1, 2, 4],
        1 => [0, 2, 4],
        2 => [0, 1, 4],
        3 => [0, 5, 6],
        4 => [0, 1, 2],
        5 => [2, 3, 6],
        6 => [3, 5]
    ];
    private const GRAHA_SHATRU = [
        0 => [5, 6],
        1 => [3],
        2 => [3, 5, 6],
        3 => [1, 2],
        4 => [3, 5, 6],
        5 => [0, 1],
        6 => [0, 1, 2, 4]
    ];
    private const BHAKOOT_DOSHA = [2, 12, 5, 9, 6, 8];
    private const TARA_SHUBHA   = [2, 4, 6, 8, 9];
    private const YONI_SHATRU   = [[0,4],[1,13],[2,9],[3,12],[5,11],[6,14],[7,8],[10,14]];

    // -- UI color palette (display only) --
    private const CHOGHADIYA_COLOR = ['#c0302a','#7a6010','#2d7a3a','#1a5a8a','#8a2010','#2a6030','#c86a14'];

    // -- Display name arrays (presentation labels only) --
    // Paksha has only 2 values — not worth a DB lookup
    private const PAKSHA_HI = ['Shukla' => 'Shukla', 'Krishna' => 'Krishna'];
    // Classical Vedic graha order (Sun=0…Saturn=6) — tied to GRAHA_MITRA/GRAHA_SHATRU calculation constants
    private const GRAHA_HI  = ['Sun','Moon','Mars','Mercury','Jupiter','Venus','Saturn'];
    // Enum display values used as integer-indexed labels in ashtakoot calculations
    private const GANA_HI   = ['Deva','Manushya','Rakshasa'];
    private const NADI_HI   = ['Adi','Madhya','Antya'];
    private const VARNA_HI  = ['Brahmin','Kshatriya','Vaishya','Shudra'];
    private const YONI_HI   = ['Ashva','Gaja','Mesha','Sarpa','Shvana','Marjara','Mushaka','Go','Mahisha','Vyaghra','Mruga','Vanara','Nakula','Simha'];

    // -- DB-backed name caches (loaded once in loadMuhurtaData) --
    private static ?array $varaNames   = null; // [0..6  => weekday name]
    private static ?array $rashiNames  = null; // [0..11 => zodiac sign name]
    private static ?array $nakNames    = null; // [0..26 => nakshatra name]
    private static ?array $tithiNames  = null; // [0..14 => tithi name]
    private static ?array $karanaNames = null; // [name  => display name]

    // -- Choghadiya (already DB-backed) --
    private static ?array $chogData = null;

    // -- DB-backed static caches --
    private static ?array $nakRashi        = null;
    private static ?array $nakGana         = null;
    private static ?array $nakNadi         = null;
    private static ?array $nakYoni         = null;
    private static ?array $rashiLord       = null;
    private static ?array $rashiVarna      = null;
    private static ?array $rashiVasya      = null;
    private static ?array $rahuPart        = null;
    private static ?array $yamaPart        = null;
    private static ?array $gulikaPart      = null;
    private static ?array $durmuhurtaPart  = null;
    private static ?array $gowriDay        = null;
    private static ?array $vivahNakUttam   = null;
    private static ?array $vivahNakMadhyam = null;
    private static ?array $vivahNakVarjit  = null;
    private static ?array $vivahTithiUttam = null;
    private static ?array $vivahTithiVarjit = null;
    private static ?array $vivahVaraUttam  = null;
    private static ?array $vivahVaraVarjit = null;
    private static ?array $grihaGoodNak    = null;
    private static ?array $grihaBadNak     = null;
    private static ?array $vahanGoodNak    = null;
    private static ?array $mundanGoodNak   = null;
    private static ?array $sampattiGoodNak = null;
    private static ?array $panchakNak      = null;
    private static ?array $yogaShubha      = null;
    private static ?array $yogaAshubha     = null;
    private static ?array $lattaOffsets    = null;
    private static ?array $horaQuality     = null;

    // =========================================================================
    //  DB Loaders
    // =========================================================================

    private static function loadChogData(): void
    {
        if (self::$chogData !== null) return;

        $rows = DB::table('choghadiya_sequences as cs')
            ->join('weekdays as w', 'cs.weekday_id', '=', 'w.id')
            ->join('choghadiya_types as ct', 'cs.choghadiya_type_id', '=', 'ct.id')
            ->orderBy('cs.id')
            ->get(['w.dow_index', 'cs.is_night', 'ct.sequence_index', 'ct.name', 'ct.nature', 'ct.ruling_planet']);

        $seqs  = [];
        $types = [];
        foreach ($rows as $row) {
            $night = $row->is_night ? 1 : 0;
            $seqs[$row->dow_index][$night][] = [
                'idx'    => $row->sequence_index,
                'name'   => $row->name,
                'nature' => $row->nature,
                'planet' => $row->ruling_planet,
            ];
            $types[$row->sequence_index] ??= [
                'name'   => $row->name,
                'nature' => $row->nature,
                'planet' => $row->ruling_planet,
            ];
        }
        self::$chogData = ['seqs' => $seqs, 'types' => $types];
    }

    private static function loadMuhurtaData(): void
    {
        if (self::$nakRashi !== null) return;

        $ganaMap = ['Deva' => 0, 'Manushya' => 1, 'Rakshasa' => 2];
        $nadiMap = ['Adi'  => 0, 'Madhya'   => 1, 'Antya'    => 2];
        $yoniMap = [
            // Sanskrit
            'Ashva' => 0, 'Gaja' => 1, 'Mesha' => 2, 'Sarpa' => 3, 'Shvana' => 4,
            'Marjara' => 5, 'Mushaka' => 6, 'Go' => 7, 'Mahisha' => 8,
            'Vyaghra' => 9, 'Mruga' => 10, 'Vanara' => 11, 'Nakula' => 12, 'Simha' => 13,
            // English equivalents
            'Horse' => 0, 'Elephant' => 1, 'Ram' => 2, 'Sheep' => 2, 'Serpent' => 3, 'Snake' => 3,
            'Dog' => 4, 'Cat' => 5, 'Rat' => 6, 'Mouse' => 6, 'Cow' => 7, 'Buffalo' => 8,
            'Tiger' => 9, 'Deer' => 10, 'Monkey' => 11, 'Mongoose' => 12, 'Lion' => 13,
        ];

        self::$nakRashi        = [];
        self::$nakGana         = [];
        self::$nakNadi         = [];
        self::$nakYoni         = [];
        self::$vivahNakUttam   = [];
        self::$vivahNakMadhyam = [];
        self::$vivahNakVarjit  = [];
        self::$grihaGoodNak    = [];
        self::$grihaBadNak     = [];
        self::$vahanGoodNak    = [];
        self::$mundanGoodNak   = [];
        self::$sampattiGoodNak = [];
        self::$panchakNak      = [];
        self::$nakNames        = [];

        // Nakshatras (sort_order 1-27, 1-based => index = sort_order - 1)
        $naks = DB::table('nakshatras')
            ->join('zodiac_signs', 'nakshatras.zodiac_sign_id', '=', 'zodiac_signs.id')
            ->orderBy('nakshatras.sort_order')
            ->get([
                'nakshatras.sort_order', 'nakshatras.name', 'nakshatras.gana', 'nakshatras.nadi', 'nakshatras.yoni',
                'nakshatras.vivah_suitability', 'nakshatras.griha_pravesh_suitability',
                'nakshatras.vahana_suitability', 'nakshatras.mundan_suitability',
                'nakshatras.sampatti_suitability', 'nakshatras.is_panchak',
                'zodiac_signs.sort_order as rashi_idx',
            ]);

        foreach ($naks as $n) {
            $i = (int)$n->sort_order - 1;
            self::$nakNames[$i] = $n->name;
            self::$nakRashi[$i] = (int)$n->rashi_idx;
            self::$nakGana[$i]  = $ganaMap[$n->gana]  ?? 0;
            self::$nakNadi[$i]  = $nadiMap[$n->nadi]  ?? 0;
            self::$nakYoni[$i]  = $yoniMap[$n->yoni]  ?? 0;

            if ($n->vivah_suitability === 'Uttam')            self::$vivahNakUttam[]   = $i;
            if ($n->vivah_suitability === 'Madhyam')          self::$vivahNakMadhyam[] = $i;
            if ($n->vivah_suitability === 'Varjit')           self::$vivahNakVarjit[]  = $i;
            if ($n->griha_pravesh_suitability === 'Good')     self::$grihaGoodNak[]    = $i;
            if ($n->griha_pravesh_suitability === 'Bad')      self::$grihaBadNak[]     = $i;
            if ($n->vahana_suitability   === 'Good')          self::$vahanGoodNak[]    = $i;
            if ($n->mundan_suitability   === 'Good')          self::$mundanGoodNak[]   = $i;
            if ($n->sampatti_suitability === 'Good')          self::$sampattiGoodNak[] = $i;
            if ($n->is_panchak)                               self::$panchakNak[]      = $i;
        }

        // Zodiac signs (sort_order 0-11)
        $varnaMap = array_flip(self::VARNA_HI);
        self::$rashiLord  = [];
        self::$rashiVarna = [];
        self::$rashiVasya = [];
        self::$rashiNames = [];

        $signs = DB::table('zodiac_signs')
            ->join('planets', 'zodiac_signs.lord_planet_id', '=', 'planets.id')
            ->orderBy('zodiac_signs.sort_order')
            ->get(['zodiac_signs.sort_order as rashi', 'zodiac_signs.name',
                   'zodiac_signs.varna', 'zodiac_signs.vasya_signs', 'planets.name as lord_name']);

        foreach ($signs as $s) {
            $r = (int)$s->rashi;
            self::$rashiNames[$r] = $s->name;
            $lordIdx = array_search($s->lord_name, self::GRAHA_HI);
            self::$rashiLord[$r]  = ($lordIdx !== false) ? (int)$lordIdx : 0;
            self::$rashiVarna[$r] = isset($varnaMap[$s->varna]) ? (int)$varnaMap[$s->varna] : 0;
            self::$rashiVasya[$r] = json_decode($s->vasya_signs, true) ?? [];
        }

        // Weekdays (sort_order 0-6, 0=Sunday)
        self::$rahuPart        = [];
        self::$yamaPart        = [];
        self::$gulikaPart      = [];
        self::$durmuhurtaPart  = [];
        self::$gowriDay        = [];
        self::$vivahVaraUttam  = [];
        self::$vivahVaraVarjit = [];
        self::$varaNames       = [];

        $wdays = DB::table('weekdays')
            ->orderBy('dow_index')
            ->get(['dow_index', 'english_name', 'rahu_kala_part', 'yamaganda_part', 'gulika_part',
                   'durmuhurta_parts', 'gowri_sequence', 'vivah_suitability']);

        foreach ($wdays as $w) {
            $d = (int)$w->dow_index;
            self::$varaNames[$d]      = $w->english_name;
            self::$rahuPart[$d]       = (int)$w->rahu_kala_part;
            self::$yamaPart[$d]       = (int)$w->yamaganda_part;
            self::$gulikaPart[$d]     = (int)$w->gulika_part;
            self::$durmuhurtaPart[$d] = json_decode($w->durmuhurta_parts, true) ?? [];
            self::$gowriDay[$d]       = json_decode($w->gowri_sequence, true) ?? [];
            if ($w->vivah_suitability === 'Uttam')  self::$vivahVaraUttam[]  = $d;
            if ($w->vivah_suitability === 'Varjit') self::$vivahVaraVarjit[] = $d;
        }

        // Tithis (sort_order 0-29)
        self::$vivahTithiUttam  = [];
        self::$vivahTithiVarjit = [];

        foreach (DB::table('tithis')->orderBy('sort_order')->get(['sort_order', 'vivah_suitability']) as $t) {
            if ($t->vivah_suitability === 'Uttam')  self::$vivahTithiUttam[]  = (int)$t->sort_order;
            if ($t->vivah_suitability === 'Varjit') self::$vivahTithiVarjit[] = (int)$t->sort_order;
        }

        // Tithi names: 15 names indexed 0–14 (tithi_number 1–15, same for both pakshas)
        self::$tithiNames = [];
        foreach (DB::table('tithis')->where('paksha', 'Shukla')->orderBy('tithi_number')
                    ->get(['tithi_number', 'name']) as $t) {
            self::$tithiNames[(int)$t->tithi_number - 1] = $t->name;
        }

        // Karana display names — fallback to canonical name if display_name is not set
        self::$karanaNames = [];
        foreach (DB::table('karanas')->get(['name', 'display_name']) as $k) {
            self::$karanaNames[$k->name] = $k->display_name ?: $k->name;
        }

        // Planets: latta offsets and hora quality (keyed by lowercase name)
        self::$lattaOffsets = [];
        self::$horaQuality  = [];

        foreach (DB::table('planets')->get(['name', 'latta_offset', 'hora_quality', 'hora_metal']) as $p) {
            $key = strtolower($p->name);
            if ($p->latta_offset !== null) {
                self::$lattaOffsets[$key] = (int)$p->latta_offset;
            }
            $idx = array_search($p->name, self::GRAHA_HI);
            if ($idx !== false && $p->hora_quality !== null) {
                self::$horaQuality[(int)$idx] = [$p->hora_quality, $p->hora_metal ?? ''];
            }
        }

        // Yogas classification
        self::$yogaShubha  = DB::table('yogas')->where('classification', 'Subha')->pluck('name')->toArray();
        self::$yogaAshubha = DB::table('yogas')->whereIn('classification', ['Ashubha', 'Mahavisha'])->pluck('name')->toArray();
    }

    // =========================================================================
    //  Public helpers used by views
    // =========================================================================

    public static function yogaHi(string $en): string   { return $en; }
    public static function karanaHi(string $en): string
    {
        self::loadMuhurtaData();
        return self::$karanaNames[$en] ?? $en;
    }

    // =========================================================================
    //  Main calculation -- full day muhurta data
    // =========================================================================

    public static function computeFullDay(
        int $yr, int $mo, int $dy,
        float $lat, float $lon, float $utcOff,
        string $type = 'vivah',
        array $options = []
    ): array {
        self::loadMuhurtaData();

        $ss     = AstroCalculator::sunriseSunset($yr, $mo, $dy, $lat, $lon, $utcOff);
        $riseHr = (!$ss['polar'] && $ss['rise'] !== null) ? $ss['rise'] : 6.0;
        $setHr  = (!$ss['polar'] && $ss['set']  !== null) ? $ss['set']  : 18.0;

        $jdRise = AstroCalculator::julianDay($yr, $mo, $dy, $riseHr - $utcOff);
        $ayan   = AstroCalculator::lahiriAyanamsa($jdRise);
        $tk     = AstroCalculator::computeTithiKarana($jdRise);
        $pancha = AstroCalculator::computePanchanga($jdRise, $ayan, $yr, $mo, $dy, $utcOff);

        $planets = self::getPlanetPositions($jdRise, $ayan);

        $angles     = AstroCalculator::computeAngles($jdRise, $lat, $lon);
        $lagnaSider = fmod(fmod($angles['asc'] - $ayan, 360) + 360, 360);
        $lagnaRashi = (int)floor($lagnaSider / 30);

        $moonRashiIdx = $planets['moon']['rashi'];
        $sunRashiIdx  = $planets['sun']['rashi'];
        $moonNakIdx   = $planets['moon']['nak'];

        $varaIdx  = $pancha['varaIdx'];
        $nakIdx   = $moonNakIdx;
        $tithiIdx = $tk['tithiIndex'];
        $paksha   = $tk['tithi']['paksha'];

        $dayLen  = $setHr - $riseHr;
        $partLen = $dayLen / 8.0;

        $rahuS = $riseHr + (self::$rahuPart[$varaIdx]   - 1) * $partLen;
        $rahuE = $rahuS  + $partLen;
        $yamaS = $riseHr + (self::$yamaPart[$varaIdx]   - 1) * $partLen;
        $yamaE = $yamaS  + $partLen;
        $guliS = $riseHr + (self::$gulikaPart[$varaIdx] - 1) * $partLen;
        $guliE = $guliS  + $partLen;

        $durmuhurta = [];
        foreach (self::$durmuhurtaPart[$varaIdx] ?? [] as $dSlot) {
            $s = $riseHr + ($dSlot - 1) * $partLen;
            $durmuhurta[] = [
                's'   => $s,
                'e'   => $s + $partLen,
                'str' => self::hm($s) . ' -- ' . self::hm($s + $partLen),
            ];
        }

        $abhi = ($riseHr + $setHr) / 2.0;
        $abhijitSlot = $varaIdx === 3
            ? null
            : ['s' => $abhi - 0.4, 'e' => $abhi + 0.4, 'str' => self::hm($abhi - 0.4) . ' -- ' . self::hm($abhi + 0.4)];

        $brahmaS = $riseHr - 0.8;
        $brahmaM = $riseHr - 0.4;
        $brahmaMuhurat = [
            ['s' => $brahmaS, 'e' => $brahmaM, 'str' => self::hm($brahmaS) . ' -- ' . self::hm($brahmaM)],
            ['s' => $brahmaM, 'e' => $riseHr,  'str' => self::hm($brahmaM) . ' -- ' . self::hm($riseHr)],
        ];

        $amritNaks = [0, 4, 6, 7, 12, 13, 14, 16, 21, 22, 23, 26];
        $amritKaal = null;
        if (in_array($nakIdx, $amritNaks)) {
            $prevNakEnd = AstroCalculator::findNakshatraEnd($jdRise - 1.5, $utcOff);
            $amritKaal = ['str' => $prevNakEnd, 'duration' => '1 hr 36 mins (approx)', 'active' => true];
        }

        return [
            'date'          => sprintf('%04d-%02d-%02d', $yr, $mo, $dy),
            'dateHi'        => self::dateHi($dy, $mo, $yr),
            'sunrise'       => AstroCalculator::decToHMS($riseHr),
            'sunset'        => AstroCalculator::decToHMS($setHr),
            'riseHr'        => $riseHr,
            'setHr'         => $setHr,
            'dayLen'        => $dayLen,
            'varaIdx'       => $varaIdx,
            'varaHi'        => self::$varaNames[$varaIdx],
            'panchanga'     => self::buildPanchaData($pancha, $tk),
            'planets'       => $planets,
            'ayan'          => $ayan,
            'jdRise'        => $jdRise,
            'lat'           => $lat,
            'lon'           => $lon,
            'lagnaRashi'    => $lagnaRashi,
            'lagnaRashiHi'  => self::$rashiNames[$lagnaRashi],
            'moonRashiIdx'  => $moonRashiIdx,
            'moonRashiHi'   => self::$rashiNames[$moonRashiIdx],
            'sunRashiIdx'   => $sunRashiIdx,
            'sunRashiHi'    => self::$rashiNames[$sunRashiIdx],
            'nakIdx'        => $moonNakIdx,
            'nakHi'         => self::$nakNames[$moonNakIdx],
            'rahuKaal'      => ['s' => $rahuS, 'e' => $rahuE, 'str' => self::hm($rahuS) . ' -- ' . self::hm($rahuE)],
            'yamaghanta'    => ['s' => $yamaS, 'e' => $yamaE, 'str' => self::hm($yamaS) . ' -- ' . self::hm($yamaE)],
            'gulikaKaal'    => ['s' => $guliS, 'e' => $guliE, 'str' => self::hm($guliS) . ' -- ' . self::hm($guliE)],
            'abhijit'       => $abhijitSlot,
            'brahmaMuhurat' => $brahmaMuhurat,
            'durmuhurta'    => $durmuhurta,
            'amritKaal'     => $amritKaal,
            'choghadiya'    => self::computeChoghadiya($riseHr, $setHr, $varaIdx),
            'hora'          => self::computeHora($riseHr, $setHr, $varaIdx),
            'yoga'          => self::computeAuspiciousYogaList($pancha, $tk, $riseHr, $setHr),
            'panchak'       => self::computePanchak($nakIdx, $riseHr, $setHr),
            'gowri'         => self::computeGowriPanchangam($riseHr, $setHr, $varaIdx),
            'doGhati'       => self::computeDoGhati($abhi),
            'lagna'         => self::computeLagnaTable($yr, $mo, $dy, $riseHr, $setHr, $lat, $lon, $ayan, $utcOff),
            'vivah'         => self::computeVivahMC($pancha, $tk, $planets, $riseHr, $setHr, $varaIdx, $nakIdx, $tithiIdx, $paksha, $ayan, $jdRise, $lat, $lon, $options),
            'griha'         => self::computeGriha($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'vahana'        => self::computeVahana($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'mundan'        => self::computeMundan($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
            'sampatti'      => self::computeSampatti($varaIdx, $nakIdx, $tithiIdx, $paksha, $pancha, $tk),
        ];
    }

    // =========================================================================
    //  1. Choghadiya
    // =========================================================================

    public static function computeChoghadiya(float $rise, float $set, int $vara): array
    {
        self::loadChogData();
        $dayLen   = $set - $rise;
        $partLen  = $dayLen / 8.0;
        $nightLen = 24.0 - $dayLen;
        $nightPart = $nightLen / 8.0;

        $result = ['day' => [], 'night' => []];

        foreach (self::$chogData['seqs'][$vara][0] as $i => $slot) {
            $cIdx = $slot['idx'];
            $s    = $rise + $i * $partLen;
            $result['day'][] = [
                'name'    => $slot['name'],
                'nature'  => $slot['nature'],
                'planet'  => $slot['planet'],
                'color'   => self::CHOGHADIYA_COLOR[$cIdx],
                'start'   => self::hm($s),
                'end'     => self::hm($s + $partLen),
                'startHr' => $s,
                'endHr'   => $s + $partLen,
                'shubh'   => in_array($cIdx, [2, 3, 5]),
                'idx'     => $cIdx,
            ];
        }
        foreach (self::$chogData['seqs'][$vara][1] as $i => $slot) {
            $cIdx = $slot['idx'];
            $s    = $set + $i * $nightPart;
            $result['night'][] = [
                'name'    => $slot['name'],
                'nature'  => $slot['nature'],
                'planet'  => $slot['planet'],
                'color'   => self::CHOGHADIYA_COLOR[$cIdx],
                'start'   => self::hm($s),
                'end'     => self::hm($s + $nightPart),
                'startHr' => $s,
                'endHr'   => $s + $nightPart,
                'shubh'   => in_array($cIdx, [2, 3, 5]),
                'idx'     => $cIdx,
            ];
        }
        return $result;
    }

    // =========================================================================
    //  2. Shubha Hora
    // =========================================================================

    public static function computeHora(float $rise, float $set, int $vara): array
    {
        self::loadMuhurtaData();

        // Chaldean order is a fixed astronomical sequence, not DB data
        $chaldean  = [0, 5, 2, 1, 6, 4, 3]; // Sun, Venus, Mercury, Moon, Saturn, Jupiter, Mars
        $startPos  = array_search($vara, [0, 1, 2, 3, 4, 5, 6]);
        $firstLord = $vara; // day lord index matches GRAHA_HI order for Sun-through-Saturn
        $startPos  = array_search($firstLord, $chaldean);

        $horas = [];
        for ($i = 0; $i < 24; $i++) {
            $lord  = $chaldean[($startPos + $i) % 7];
            $s     = $rise + $i * 1.0;
            $isDay = ($s >= $rise && $s < $set);
            [$qual, $desc] = self::$horaQuality[$lord] ?? ['Unknown', ''];
            $horas[] = [
                'hora'    => $i + 1,
                'lord'    => $lord,
                'lordHi'  => self::GRAHA_HI[$lord] ?? '',
                'start'   => self::hm($s),
                'end'     => self::hm($s + 1.0),
                'startHr' => $s,
                'isDay'   => $isDay,
                'quality' => $qual,
                'desc'    => $desc,
                'shubh'   => in_array($lord, [1, 3, 4, 5]),
            ];
        }
        return $horas;
    }

    // =========================================================================
    //  3. Do Ghati (Abhijit)
    // =========================================================================

    public static function computeDoGhati(float $abhi): array
    {
        return [
            'start'    => self::hm($abhi - 0.4),
            'end'      => self::hm($abhi + 0.4),
            'startHr'  => $abhi - 0.4,
            'endHr'    => $abhi + 0.4,
            'duration' => '48 minutes (2 Ghati)',
            'note'     => 'MC: Abhijit Muhurta destroys all doshas. Avoided on Wednesday.',
        ];
    }

    // =========================================================================
    //  4. Auspicious Yoga List
    // =========================================================================

    public static function computeAuspiciousYogaList(array $pancha, array $tk, float $rise, float $set): array
    {
        self::loadMuhurtaData();

        $yogaName = $pancha['yoga']['n'];
        $isShubh  = in_array($yogaName, self::$yogaShubha);
        $isAshubh = in_array($yogaName, self::$yogaAshubha);

        $specialYogas = [];

        $shubhVara = in_array($pancha['varaIdx'], [1, 3, 4, 5]);
        $shubhNak  = in_array($pancha['nakIdx'], [3, 4, 7, 11, 12, 14, 16, 20, 21, 26]);
        if ($shubhVara && $shubhNak) {
            $specialYogas[] = ['name' => 'Siddhi Yoga', 'nature' => 'Highly Auspicious',
                'desc' => 'Auspicious Vara + Auspicious Nakshatra -- success in all endeavours.'];
        }

        $amritCombos = [[1,3],[2,9],[3,7],[4,25],[5,11],[6,26],[0,0]];
        foreach ($amritCombos as [$v, $n]) {
            if ($pancha['varaIdx'] === $v && $pancha['nakIdx'] === $n) {
                $specialYogas[] = ['name' => 'Amrit Siddhi Yoga', 'nature' => 'Supremely Auspicious',
                    'desc' => 'MC: Best for all auspicious activities.'];
                break;
            }
        }

        $sarvarth = [[1,3],[1,7],[3,7],[3,25],[4,7],[4,25],[5,7],[5,11],[5,26],[6,26]];
        foreach ($sarvarth as [$v, $n]) {
            if ($pancha['varaIdx'] === $v && $pancha['nakIdx'] === $n) {
                $specialYogas[] = ['name' => 'Sarvartha Siddhi Yoga', 'nature' => 'Highly Auspicious',
                    'desc' => 'All attainments -- suitable for any auspicious activity.'];
                break;
            }
        }

        if ($pancha['varaIdx'] === 4 && in_array($tk['tithi']['num'], [5, 10, 15]) && $tk['tithi']['paksha'] === 'Shukla') {
            $specialYogas[] = ['name' => 'Raja Yoga', 'nature' => 'Highly Auspicious',
                'desc' => 'Thursday + Shukla Panchami/Dashami/Purnima.'];
        }

        $allGood = $shubhVara && $shubhNak
            && in_array($tk['tithi']['num'], [2, 3, 5, 7, 10, 11, 12, 13])
            && $isShubh
            && !($tk['karana']['n'] === 'Vishti');
        if ($allGood) {
            $specialYogas[] = ['name' => 'Panchanga Shuddhi', 'nature' => 'Best',
                'desc' => 'All five Panchanga limbs auspicious -- a rare combination.'];
        }

        return [
            'yogaName'  => $yogaName,
            'yogaHi'    => $pancha['yoga']['n'],
            'nature'    => $pancha['yoga']['nature'],
            'isShubh'   => $isShubh,
            'isAshubh'  => $isAshubh,
            'special'   => $specialYogas,
            'isBhadra'  => ($tk['karana']['n'] === 'Vishti'),
            'bhadraNote' => 'MC: All auspicious activities are prohibited during Bhadra (Vishti Karana).',
        ];
    }

    // =========================================================================
    //  5. Panchaka
    // =========================================================================

    public static function computePanchak(int $nakIdx, float $rise, float $set): array
    {
        self::loadMuhurtaData();
        $isPanchak = in_array($nakIdx, self::$panchakNak);
        $nakHi     = self::$nakNames[$nakIdx];
        return [
            'active'      => $isPanchak,
            'nakName'     => $nakHi,
            'nakIdx'      => $nakIdx,
            'note'        => $isPanchak
                ? "MC: $nakHi -- Panchak active. Griha Pravesh, marriage, travel, storing fuel, wood-work, last rites are prohibited."
                : "No Panchak -- {$nakHi} nakshatra is free from Panchak.",
            'panchakNaks' => array_map(fn($i) => self::$nakNames[$i], self::$panchakNak),
        ];
    }

    // =========================================================================
    //  6. Lagna Table
    // =========================================================================

    public static function computeLagnaTable(
        int $yr, int $mo, int $dy,
        float $rise, float $set,
        float $lat, float $lon, float $ayan, float $utcOff
    ): array {
        $lagnas = [];
        for ($h = 0; $h <= 12; $h++) {
            $t = $rise + $h * (($set - $rise) / 12.0);
            if ($t > $set + 6) break;
            $jd     = AstroCalculator::julianDay($yr, $mo, $dy, $t - $utcOff);
            $angles = AstroCalculator::computeAngles($jd, $lat, $lon);
            $sider  = fmod(fmod($angles['asc'] - $ayan, 360) + 360, 360);
            $signIdx = (int)floor($sider / 30);
            $deg    = fmod($sider, 30);
            $isFixed   = in_array($signIdx, [1, 4, 7, 10]);
            $isDual    = in_array($signIdx, [2, 5, 8, 11]);
            $lagnas[] = [
                'time'    => self::hm($t),
                'timeHr'  => $t,
                'signIdx' => $signIdx,
                'signHi'  => self::$rashiNames[$signIdx],
                'deg'     => number_format($deg, 1) . 'deg',
                'type'    => $isFixed ? 'Sthira (Fixed)' : ($isDual ? 'Dwisvabhava (Dual)' : 'Chara (Movable)'),
                'shubh'   => $isFixed || $isDual,
                'note'    => $isFixed
                    ? 'MC: Sthira Lagna -- excellent for Griha Pravesh and marriage.'
                    : ($isDual ? 'Moderate for marriage.' : 'Chara Lagna -- auspicious for travel.'),
            ];
        }
        return $lagnas;
    }

    // =========================================================================
    //  7. Gowri Panchangam
    // =========================================================================

    public static function computeGowriPanchangam(float $rise, float $set, int $vara): array
    {
        self::loadChogData();
        self::loadMuhurtaData();
        $dayLen  = $set - $rise;
        $partLen = $dayLen / 8.0;
        $periods = [];
        foreach (self::$gowriDay[$vara] ?? [] as $i => $cIdx) {
            $s    = $rise + $i * $partLen;
            $type = self::$chogData['types'][$cIdx] ?? ['name' => '', 'nature' => '', 'planet' => ''];
            $periods[] = [
                'name'   => $type['name'],
                'nature' => $type['nature'],
                'start'  => self::hm($s),
                'end'    => self::hm($s + $partLen),
                'shubh'  => in_array($cIdx, [2, 3, 5]),
                'color'  => self::CHOGHADIYA_COLOR[$cIdx] ?? '#888',
            ];
        }
        return $periods;
    }

    // =========================================================================
    //  8. Vivah Muhurta -- MC Vivah Prakarana
    // =========================================================================

    public static function computeVivahMC(
        array $pancha, array $tk, array $planets,
        float $rise, float $set, int $vara, int $nak, int $tithiIdx,
        string $paksha, float $ayan, float $jdRise,
        float $lat, float $lon, array $options = []
    ): array {
        self::loadMuhurtaData();

        $doshas = [];
        $shubh  = [];
        $score  = 50;

        // Vara
        $varaHi = self::$varaNames[$vara];
        if (in_array($vara, self::$vivahVaraUttam)) {
            $score += 15;
            $shubh[] = "$varaHi -- MC: Excellent Vivah Vara.";
        } elseif (in_array($vara, self::$vivahVaraVarjit)) {
            $score -= 20;
            $doshas[] = "$varaHi -- MC: Strictly prohibited Vara for marriage.";
        } elseif ($vara === 0) {
            $score += 3;
            $shubh[] = "$varaHi -- MC: Moderate -- acceptable for Kshatriya.";
        }

        // Paksha + Tithi
        if ($paksha === 'Krishna') {
            $score -= 50;
            $doshas[] = 'Krishna Paksha -- MC: Entire Krishna Paksha prohibited for marriage.';
        } elseif (in_array($tithiIdx, self::$vivahTithiUttam)) {
            $score += 12;
            $shubh[] = 'Shukla ' . self::$tithiNames[$tk['tithi']['num'] - 1] . ' -- MC: Excellent Vivah Tithi.';
        } elseif (in_array($tithiIdx, self::$vivahTithiVarjit)) {
            $score -= 15;
            $doshas[] = self::$tithiNames[$tk['tithi']['num'] - 1] . ' -- MC: Prohibited Tithi for marriage (Rikta/Shashthi).';
        }

        // Nakshatra
        $nakHi = self::$nakNames[$nak];
        if (in_array($nak, self::$vivahNakUttam)) {
            $score += 20;
            $shubh[] = "$nakHi -- MC: Excellent Nakshatra for marriage.";
        } elseif (in_array($nak, self::$vivahNakMadhyam)) {
            $score += 8;
            $shubh[] = "$nakHi -- MC: Moderate Nakshatra -- acceptable with other auspicious combinations.";
        } elseif (in_array($nak, self::$vivahNakVarjit)) {
            $score -= 20;
            $doshas[] = "$nakHi -- MC: Prohibited Nakshatra for marriage.";
        }

        // Yoga
        $yogaName = $pancha['yoga']['n'];
        if (in_array($yogaName, self::$yogaAshubha)) {
            $score -= 10;
            $doshas[] = "$yogaName Yoga -- inauspicious yoga, avoid new undertakings.";
        } elseif (in_array($yogaName, self::$yogaShubha)) {
            $score += 10;
            $shubh[] = "$yogaName Yoga -- auspicious and auspicious.";
        }

        if ($tk['karana']['n'] === 'Vishti') {
            $score -= 10;
            $doshas[] = 'Bhadra (Vishti Karana) -- MC: Marriage prohibited during Bhadra.';
        }

        $guruAsta = self::checkGuruAsta($planets);
        if ($guruAsta['asta']) {
            $score -= 25;
            $doshas[] = "Jupiter Combust -- MC: {$guruAsta['diff']}deg from Sun -- less than {$guruAsta['limit']}deg. Marriage prohibited.";
        } else {
            $shubh[] = "Jupiter not combust -- {$guruAsta['diff']}deg from Sun. Favourable for marriage.";
        }

        $shukraAsta = self::checkShukraAsta($planets);
        if ($shukraAsta['asta']) {
            $score -= 25;
            $doshas[] = "Venus Combust -- MC: {$shukraAsta['diff']}deg from Sun -- less than {$shukraAsta['limit']}deg. Marriage prohibited.";
        } else {
            $shubh[] = "Venus not combust -- {$shukraAsta['diff']}deg from Sun.";
        }

        $lattaDoshas = self::checkLattaDosha($planets, $ayan, $nak);
        foreach ($lattaDoshas as $ld) {
            $score -= 8;
            $doshas[] = "Latta Dosha -- {$ld['graha']} Latta Nakshatra {$ld['latNak']} -- {$ld['note']}";
        }

        if (in_array($nak, self::$panchakNak)) {
            $score -= 8;
            $doshas[] = self::$nakNames[$nak] . ' -- Panchak active. MC: Marriage prohibited.';
        }

        $milan    = null;
        $girlRashi = $options['girlRashiIdx'] ?? null;
        $boyRashi  = $options['boyRashiIdx']  ?? null;
        $girlNak   = $options['girlNakIdx']   ?? null;
        $boyNak    = $options['boyNakIdx']     ?? null;

        if ($girlRashi !== null && $boyRashi !== null) {
            $milan = self::ashtkootMilan((int)$girlRashi, (int)$boyRashi, $girlNak, $boyNak);
            $moonRashiAcc = $planets['moon']['rashi'];
            $gTri = [(int)$girlRashi, ((int)$girlRashi + 4) % 12, ((int)$girlRashi + 8) % 12];
            $bTri = [(int)$boyRashi,  ((int)$boyRashi  + 4) % 12, ((int)$boyRashi  + 8) % 12];
            if (in_array($moonRashiAcc, $gTri) || in_array($moonRashiAcc, $bTri)) {
                $score += 8;
                $shubh[] = 'Moon in trine Rashi of bride/groom -- especially auspicious.';
            }
        }

        $chandrabala = null;
        if ($girlRashi !== null) {
            $cb = self::getChandrabala((int)$girlRashi, $planets['moon']['rashi']);
            $chandrabala = $cb;
            $score += $cb['bonus'];
            if (!$cb['shubh']) {
                $doshas[] = 'Chandrabala inauspicious -- ' . $cb['label'] . '. MC: Moon unfavourable for bride on this date.';
            } else {
                $shubh[]  = 'Chandrabala auspicious -- ' . $cb['label'] . '. MC: Moon favourable for bride.';
            }
        }

        $tarabala = null;
        if ($girlNak !== null) {
            $tb = self::getTarabala((int)$girlNak, $nak);
            $tarabala = $tb;
            $score   += $tb['bonus'];
            if (!$tb['shubh']) {
                $doshas[] = 'Tarabala inauspicious -- ' . $tb['name'] . '. MC: Distance from birth Nakshatra unfavourable.';
            } else {
                $shubh[]  = 'Tarabala auspicious -- ' . $tb['name'] . '.';
            }
        }

        $dayLen  = $set - $rise;
        $partLen = $dayLen / 8.0;
        $badPeriods = [
            [$rise + (self::$rahuPart[$vara] - 1) * $partLen, $rise + self::$rahuPart[$vara] * $partLen],
            [$rise + (self::$yamaPart[$vara] - 1) * $partLen, $rise + self::$yamaPart[$vara] * $partLen],
        ];
        $windows = self::getAuspiciousWindows($rise, $set, $badPeriods);
        $grade   = self::grade(max(0, min(100, $score)));

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
                'MC: Uttaraphalguni -- best Vivah Nakshatra, deity Aryama.',
                'MC: Shukla Paksha mandatory. Entire Krishna Paksha prohibited for marriage.',
                'MC: Jupiter (>11deg) and Venus (>10deg) must not be combust -- otherwise marriage prohibited.',
                'MC: No marriage Muhurta during Bhadra (Vishti Karana).',
                'MC: Ashtakoot minimum 18 Gunas required; Nadi Dosha must be absent.',
                'MC: To avoid Latta Dosha -- select a different auspicious Nakshatra.',
                'MC: Tuesday and Saturday are strictly prohibited for marriage.',
                'MC: Siddhi Yoga + Amrit Siddhi Yoga = best marriage Muhurta.',
            ],
        ];
    }

    private static function checkGuruAsta(array $planets): array
    {
        $diff  = self::angDiff($planets['sun']['lon'], $planets['jupiter']['lon']);
        $limit = 11.0;
        return ['asta' => $diff < $limit, 'diff' => round($diff, 2), 'limit' => $limit];
    }

    private static function checkShukraAsta(array $planets): array
    {
        $diff  = self::angDiff($planets['sun']['lon'], $planets['venus']['lon']);
        $limit = 10.0;
        return ['asta' => $diff < $limit, 'diff' => round($diff, 2), 'limit' => $limit];
    }

    private static function checkLattaDosha(array $planets, float $ayan, int $muhurtaNak): array
    {
        $doshas = [];
        $pids   = ['sun', 'moon', 'mars', 'mercury', 'jupiter', 'venus', 'saturn'];
        foreach ($pids as $idx => $pid) {
            if (!isset($planets[$pid])) continue;
            $off    = self::$lattaOffsets[$pid] ?? 0;
            $sider  = fmod(fmod($planets[$pid]['lon'] - $ayan, 360) + 360, 360);
            $pNak   = (int)floor($sider / (360 / 27));
            $latNak = ($pNak + $off) % 27;
            if ($latNak === $muhurtaNak) {
                $doshas[] = [
                    'graha'  => self::GRAHA_HI[$idx],
                    'latNak' => self::$nakNames[$latNak],
                    'note'   => "MC: {$pid} Latta ({$off} Nakshatras ahead) = " . self::$nakNames[$latNak] . " = Muhurta Nakshatra.",
                ];
            }
        }
        return $doshas;
    }

    private static function angDiff(float $a, float $b): float
    {
        $d = abs($a - $b);
        return min($d, 360.0 - $d);
    }

    // =========================================================================
    //  9. Ashtakoot Milan (MC Vivah Prakarana)
    // =========================================================================

    public static function ashtkootMilan(int $gR, int $bR, ?int $gN = null, ?int $bN = null): array
    {
        self::loadMuhurtaData();

        if ($gN === null) $gN = max(0, min(26, (int)round($gR * 27 / 12)));
        if ($bN === null) $bN = max(0, min(26, (int)round($bR * 27 / 12)));

        $total = 0;
        $koot  = [];

        // 1. Varna
        $gV   = self::$rashiVarna[$gR] ?? 0;
        $bV   = self::$rashiVarna[$bR] ?? 0;
        $got  = ($bV <= $gV) ? 1 : 0;
        $total += $got;
        $koot['varna'] = ['name' => 'Varna', 'max' => 1, 'got' => $got,
            'girl' => self::VARNA_HI[$gV], 'boy' => self::VARNA_HI[$bV],
            'note' => $got ? 'Favourable -- Varna compatibility.' : 'Varna Dosha -- groom Varna lower than bride.',
            'dosha' => !$got];

        // 2. Vasya
        $gVasya = self::$rashiVasya[$gR] ?? [];
        $bVasya = self::$rashiVasya[$bR] ?? [];
        $vasya  = (in_array($bR, $gVasya) && in_array($gR, $bVasya)) ? 2 : (in_array($bR, $gVasya) || in_array($gR, $bVasya) ? 1 : 0);
        $total += $vasya;
        $koot['vasya'] = ['name' => 'Vasya', 'max' => 2, 'got' => $vasya,
            'girl' => self::$rashiNames[$gR], 'boy' => self::$rashiNames[$bR],
            'note' => $vasya == 2 ? 'Mutual Vasya.' : ($vasya == 1 ? 'One-sided Vasya.' : 'Vasya Dosha.'),
            'dosha' => $vasya === 0];

        // 3. Tara
        $tDist  = (($gN - $bN) + 27) % 27;
        $tNum   = ($tDist % 9) + 1;
        $tNames = ['', 'Janma', 'Sampat', 'Vipat', 'Kshema', 'Pratyari', 'Sadhaka', 'Naidhana', 'Mitra', 'Atimitra'];
        $tGot   = in_array($tNum, self::TARA_SHUBHA) ? 3 : 0;
        $total += $tGot;
        $koot['tara'] = ['name' => 'Tara', 'max' => 3, 'got' => $tGot,
            'girl' => self::$nakNames[$gN], 'boy' => self::$nakNames[$bN],
            'taraNam' => $tNames[$tNum],
            'note' => $tGot ? "Auspicious Tara -- {$tNames[$tNum]}." : "Inauspicious Tara -- {$tNames[$tNum]}.",
            'dosha' => !$tGot];

        // 4. Yoni
        $gY   = self::$nakYoni[$gN] ?? 0;
        $bY   = self::$nakYoni[$bN] ?? 0;
        $yGot = 4;
        $yNote = 'Same Yoni.';
        if ($gY !== $bY) {
            $shatru = false;
            foreach (self::YONI_SHATRU as [$a, $b]) {
                if (($gY === $a && $bY === $b) || ($gY === $b && $bY === $a)) { $shatru = true; break; }
            }
            $yGot  = $shatru ? 0 : 2;
            $yNote = $shatru ? 'Yoni Vaira Dosha.' : 'Yoni neutral.';
        }
        $total += $yGot;
        $koot['yoni'] = ['name' => 'Yoni', 'max' => 4, 'got' => $yGot,
            'girl' => self::YONI_HI[$gY], 'boy' => self::YONI_HI[$bY],
            'note' => $yNote, 'dosha' => $yGot === 0];

        // 5. Graha Maitri
        $gL = self::$rashiLord[$gR] ?? 0;
        $bL = self::$rashiLord[$bR] ?? 0;
        $gM = self::GRAHA_MITRA[$gL]  ?? [];
        $bM = self::GRAHA_MITRA[$bL]  ?? [];
        $gS = self::GRAHA_SHATRU[$gL] ?? [];
        $bS = self::GRAHA_SHATRU[$bL] ?? [];
        $gB = in_array($bL, $gM) ? 'Mitra' : (in_array($bL, $gS) ? 'Shatru' : 'Sama');
        $bB = in_array($gL, $bM) ? 'Mitra' : (in_array($gL, $bS) ? 'Shatru' : 'Sama');
        $mGot = match ([$gB, $bB]) {
            ['Mitra', 'Mitra']           => 5,
            ['Mitra', 'Sama'], ['Sama', 'Mitra'], ['Sama', 'Sama'] => 3,
            ['Mitra', 'Shatru'], ['Shatru', 'Mitra'] => 1,
            default => 0,
        };
        $total += $mGot;
        $koot['maitri'] = ['name' => 'Graha Maitri', 'max' => 5, 'got' => $mGot,
            'girl' => self::GRAHA_HI[$gL] . ' (' . self::$rashiNames[$gR] . ')',
            'boy'  => self::GRAHA_HI[$bL] . ' (' . self::$rashiNames[$bR] . ')',
            'gBuddhi' => $gB, 'bBuddhi' => $bB,
            'note' => $mGot >= 4 ? 'Mutual friendship.' : ($mGot >= 2 ? 'Neutral.' : 'Planetary enmity.'),
            'dosha' => $mGot === 0];

        // 6. Gana
        $gG  = self::$nakGana[$gN] ?? 0;
        $bG  = self::$nakGana[$bN] ?? 0;
        $gGot = match (true) {
            $gG === $bG      => 6,
            $bG === 0 && $gG === 1 => 5,
            default => 0,
        };
        $total += $gGot;
        $koot['gana'] = ['name' => 'Gana', 'max' => 6, 'got' => $gGot,
            'girl' => self::GANA_HI[$gG], 'boy' => self::GANA_HI[$bG],
            'note' => $gGot === 6 ? 'Same Gana.' : ($gGot >= 4 ? 'Compatible.' : 'Gana Dosha.'),
            'dosha' => $gGot === 0];

        // 7. Bhakoot
        $bDist  = (($gR - $bR) + 12) % 12;
        $bDistR = (($bR - $gR) + 12) % 12;
        $bDosha = in_array($bDist, self::BHAKOOT_DOSHA) || in_array($bDistR, self::BHAKOOT_DOSHA);
        $bhGot  = $bDosha ? 0 : 7;
        $total += $bhGot;
        $dType = '';
        if ($bDosha) {
            if (in_array($bDist, [6, 8]) || in_array($bDistR, [6, 8]))      $dType = 'Shadashtak';
            elseif (in_array($bDist, [5, 9]) || in_array($bDistR, [5, 9])) $dType = 'Panchanavama';
            else                                                              $dType = 'Dwidwadasha';
        }
        $koot['bhakoot'] = ['name' => 'Bhakoot', 'max' => 7, 'got' => $bhGot,
            'girl' => self::$rashiNames[$gR], 'boy' => self::$rashiNames[$bR],
            'note' => $bhGot ? 'Bhakoot auspicious.' : "Bhakoot Dosha ($dType).",
            'dosha' => $bDosha, 'doshaType' => $dType];

        // 8. Nadi
        $gN2  = self::$nakNadi[$gN] ?? 0;
        $bN2  = self::$nakNadi[$bN] ?? 0;
        $nGot = ($gN2 !== $bN2) ? 8 : 0;
        $total += $nGot;
        $koot['nadi'] = ['name' => 'Nadi', 'max' => 8, 'got' => $nGot,
            'girl' => self::NADI_HI[$gN2], 'boy' => self::NADI_HI[$bN2],
            'note' => $nGot ? 'Different Nadi -- excellent.' : 'Nadi Dosha -- MC: extremely serious.',
            'dosha' => !$nGot];

        $rating = match (true) {
            $total >= 32 => ['hi' => 'Excellent',         'color' => '#1a6a2a'],
            $total >= 27 => ['hi' => 'Very Good',         'color' => '#2d7a3a'],
            $total >= 21 => ['hi' => 'Moderate',          'color' => '#9a6b0a'],
            $total >= 18 => ['hi' => 'Acceptable',        'color' => '#b87a20'],
            default      => ['hi' => 'Not Compatible',    'color' => '#c0302a'],
        };

        $mahadosha = [];
        if (!$koot['nadi']['got'])    $mahadosha[] = 'Nadi Dosha (extremely serious)';
        if (!$koot['bhakoot']['got']) $mahadosha[] = "Bhakoot Dosha ($dType)";
        if (!$koot['gana']['got'])    $mahadosha[] = 'Gana Dosha';
        if (!$koot['yoni']['got'])    $mahadosha[] = 'Yoni Vaira';

        return [
            'koot'     => $koot,
            'total'    => $total,
            'max'      => 36,
            'rating'   => $rating,
            'mahadosha' => $mahadosha,
            'girRashi' => self::$rashiNames[$gR],
            'boyRashi' => self::$rashiNames[$bR],
            'girNak'   => self::$nakNames[$gN],
            'boyNak'   => self::$nakNames[$bN],
        ];
    }

    // =========================================================================
    //  10-14. Griha Pravesh / Vahana / Mundan / Sampatti
    // =========================================================================

    private static function computeSamskar(
        string $type, int $vara, int $nak, int $tithiIdx,
        string $paksha, array $pancha, array $tk
    ): array {
        self::loadMuhurtaData();

        $goodVara = [1, 3, 4, 5];
        $badVara  = ($type === 'vahana' || $type === 'sampatti') ? [2, 6] : [0, 2, 6];
        $goodNak  = match ($type) {
            'griha'    => self::$grihaGoodNak,
            'mundan'   => self::$mundanGoodNak,
            'sampatti' => self::$sampattiGoodNak,
            default    => self::$vahanGoodNak,
        };
        $badNak    = [1, 5, 8, 9, 17, 18];
        $goodTithi = [1, 2, 4, 5, 6, 9, 10, 11, 12, 16, 17, 19, 21, 24, 25, 27];
        $badTithi  = [3, 7, 13, 14, 28, 29];

        $score  = 50;
        $doshas = [];
        $shubh  = [];

        if (in_array($vara, $goodVara))       { $score += 15; $shubh[] = self::$varaNames[$vara] . ' -- auspicious Vara.'; }
        elseif (in_array($vara, $badVara))    { $score -= 15; $doshas[] = self::$varaNames[$vara] . ' -- Vara Dosha.'; }

        if (in_array($nak, $goodNak))         { $score += 20; $shubh[] = self::$nakNames[$nak] . ' -- auspicious Nakshatra.'; }
        elseif (in_array($nak, $badNak))      { $score -= 20; $doshas[] = self::$nakNames[$nak] . ' -- Nakshatra Dosha.'; }

        if (in_array($tithiIdx, $goodTithi))  { $score += 15; $shubh[] = self::PAKSHA_HI[$paksha] . ' ' . self::$tithiNames[min($tk['tithi']['num'] - 1, 14)] . ' -- auspicious Tithi.'; }
        elseif (in_array($tithiIdx, $badTithi)) { $score -= 15; $doshas[] = self::$tithiNames[min($tk['tithi']['num'] - 1, 14)] . ' -- Tithi Dosha.'; }

        if ($paksha === 'Shukla')             { $score += 5; $shubh[] = 'Shukla Paksha -- waxing Moon auspicious.'; }
        else                                  { $score -= 5; }

        $yogaName = $pancha['yoga']['n'];
        if (in_array($yogaName, self::$yogaAshubha))  { $score -= 10; $doshas[] = "$yogaName Yoga Dosha."; }
        elseif (in_array($yogaName, self::$yogaShubha)) { $score += 10; $shubh[] = "$yogaName Yoga -- auspicious."; }

        if ($tk['karana']['n'] === 'Vishti')  { $score -= 8; $doshas[] = 'Bhadra Dosha.'; }

        return [
            'score'  => max(0, min(100, $score)),
            'grade'  => self::grade(max(0, min(100, $score))),
            'doshas' => $doshas,
            'shubh'  => $shubh,
        ];
    }

    public static function computeGriha(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('griha', $vara, $nak, $tithiIdx, $paksha, $pancha, $tk);
        $r['shastra'] = [
            'MC: Rohini, Punarvasu, Pushya, Uttaraphalguni, Hasta, Shravana, Revati -- excellent.',
            'MC: Shukla Paksha and Sthira Lagna mandatory.',
            'MC: Griha Pravesh prohibited during Adhika Maasa.',
            'Enter from north or east, right foot first.',
        ];
        return $r;
    }

    public static function computeVahana(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('vahana', $vara, $nak, $tithiIdx, $paksha, $pancha, $tk);
        $r['shastra'] = [
            'MC: Ashwini, Rohini, Punarvasu, Hasta -- best Nakshatras.',
            'Dashami Tithi + Vijaya Yoga -- especially auspicious.',
            'Vehicle purchase prohibited during Rahukala.',
        ];
        return $r;
    }

    public static function computeMundan(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('mundan', $vara, $nak, $tithiIdx, $paksha, $pancha, $tk);
        $r['shastra'] = [
            'MC: Perform in 1st, 3rd, or 5th year.',
            'Jyeshtha, Mula, Ashlesha -- prohibited.',
            'Do not perform Mundan in birth month.',
            'Abhijit Muhurta -- always auspicious.',
        ];
        return $r;
    }

    public static function computeSampatti(int $vara, int $nak, int $tithiIdx, string $paksha, array $pancha, array $tk): array
    {
        $r = self::computeSamskar('sampatti', $vara, $nak, $tithiIdx, $paksha, $pancha, $tk);
        $r['shastra'] = [
            'MC: Rohini, Punarvasu, Pushya, Anuradha, Shravana -- excellent.',
            'Purchase on Thursday or Friday -- auspicious.',
            'Shukla Pratipada through Shashthi -- not advisable.',
            'Sign documents during Mercury, Jupiter, or Venus Hora.',
        ];
        return $r;
    }

    // =========================================================================
    //  Chandrabala & Tarabala
    // =========================================================================

    public static function getChandrabala(int $birthRashi, int $moonRashi): array
    {
        $dist  = (($moonRashi - $birthRashi) + 12) % 12 + 1;
        $shubh = in_array($dist, [2, 4, 6, 8, 9, 10, 11]);
        $label = match ($dist) {
            1  => 'Janma (Neutral)',          2  => 'Sampat (Auspicious)',
            3  => 'Vipat (Inauspicious)',     4  => 'Kshema (Auspicious)',
            5  => 'Pratyari (Inauspicious)',  6  => 'Sadhaka (Auspicious)',
            7  => 'Naidhana (Inauspicious)',  8  => 'Mitra (Auspicious)',
            9  => 'Atimitra (Auspicious)',    10 => 'Sampat+9 (Auspicious)',
            11 => 'Kshema+9 (Auspicious)',   12 => 'Vipat+9 (Inauspicious)',
            default => '?',
        };
        $bonus = match ($dist) {
            1 => 0, 2 => 8, 3 => -8, 4 => 6, 5 => -10, 6 => 6,
            7 => -8, 8 => 8, 9 => 8, 10 => 6, 11 => 4, 12 => -6, default => 0,
        };
        return ['dist' => $dist, 'shubh' => $shubh, 'label' => $label, 'bonus' => $bonus];
    }

    public static function getTarabala(int $birthNak, int $muhurtaNak): array
    {
        $dist    = (($muhurtaNak - $birthNak) + 27) % 27;
        $taraNum = ($dist % 9) + 1;
        $names   = ['', 'Janma', 'Sampat', 'Vipat', 'Kshema', 'Pratyari', 'Sadhaka', 'Naidhana', 'Mitra', 'Atimitra'];
        $shubh   = in_array($taraNum, [2, 4, 6, 8, 9]);
        $bonus   = match ($taraNum) {
            1 => -2, 2 => 8, 3 => -8, 4 => 6, 5 => -10,
            6 => 6, 7 => -8, 8 => 8, 9 => 10, default => 0,
        };
        return ['taraNum' => $taraNum, 'name' => $names[$taraNum], 'shubh' => $shubh, 'bonus' => $bonus];
    }

    // =========================================================================
    //  Month Scan
    // =========================================================================

    public static function scanMonth(
        int $yr, int $mo, float $lat, float $lon, float $utcOff,
        string $type = 'vivah', array $options = []
    ): array {
        self::loadMuhurtaData();

        $days      = cal_days_in_month(CAL_GREGORIAN, $mo, $yr);
        $girlRashi = isset($options['girlRashiIdx']) ? (int)$options['girlRashiIdx'] : null;
        $boyRashi  = isset($options['boyRashiIdx'])  ? (int)$options['boyRashiIdx']  : null;
        $girlNak   = isset($options['girlNakIdx'])   ? (int)$options['girlNakIdx']   : null;
        $boyNak    = isset($options['boyNakIdx'])     ? (int)$options['boyNakIdx']    : null;
        $minScore  = (int)($options['minScore'] ?? 40);
        $results   = [];

        for ($d = 1; $d <= $days; $d++) {
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

            $sunLon   = AstroCalculator::sunLongitude($jdRise);
            $monLon   = AstroCalculator::moonLongitude($jdRise);
            $sunSider = fmod(fmod($sunLon - $ayan, 360) + 360, 360);
            $monSider = fmod(fmod($monLon - $ayan, 360) + 360, 360);
            $sunRashi  = (int)floor($sunSider / 30);
            $moonRashi = (int)floor($monSider / 30);
            $nakIdx    = (int)floor($monSider / (360.0 / 27.0));

            $jupLon = AstroCalculator::planetLongitude($jdRise, 'jupiter');
            $venLon = AstroCalculator::planetLongitude($jdRise, 'venus');
            $marLon = AstroCalculator::planetLongitude($jdRise, 'mars');
            $merLon = AstroCalculator::planetLongitude($jdRise, 'mercury');
            $satLon = AstroCalculator::planetLongitude($jdRise, 'saturn');

            $score  = 50;
            $doshas = [];
            $shubh  = [];
            $flags  = [];

            if ($type === 'vivah') {
                if (in_array($varaIdx, self::$vivahVaraUttam))      { $score += 15; $shubh[] = self::$varaNames[$varaIdx] . ' auspicious.'; }
                elseif (in_array($varaIdx, self::$vivahVaraVarjit)) { $score -= 20; $doshas[] = self::$varaNames[$varaIdx] . ' prohibited.'; }
            } else {
                if (in_array($varaIdx, [1, 3, 4, 5]))    { $score += 15; $shubh[] = self::$varaNames[$varaIdx] . ' auspicious.'; }
                elseif (in_array($varaIdx, [0, 2, 6]))   { $score -= 15; $doshas[] = self::$varaNames[$varaIdx] . ' Dosha.'; }
            }

            if ($type === 'vivah' && $paksha === 'Krishna') {
                $score -= 50;
                $doshas[] = 'Krishna Paksha -- MC: Marriage entirely prohibited.';
                $flags['krishna_paksha'] = true;
            }

            if ($type === 'vivah' && in_array($varaIdx, self::$vivahVaraVarjit)) {
                $score -= 30;
            }

            if ($paksha === 'Shukla')      { $score += 5; if ($type === 'vivah') $shubh[] = 'Shukla Paksha.'; }
            elseif ($paksha === 'Krishna') { $score -= 3; }

            if ($type === 'vivah') {
                if ($paksha === 'Shukla' && in_array($tithiIdx, self::$vivahTithiUttam))  { $score += 12; $shubh[] = self::$nakNames[$nakIdx] . ' Tithi excellent.'; }
                elseif (in_array($tithiIdx, self::$vivahTithiVarjit))                     { $score -= 15; $doshas[] = 'Rikta/inauspicious Tithi.'; }
            } else {
                if (in_array($tithiIdx, [1,2,4,5,6,9,10,11,12,16,17,19,21,24,25,27]))   { $score += 12; }
                elseif (in_array($tithiIdx, [3,7,13,14,28,29]))                           { $score -= 12; $doshas[] = 'Inauspicious Tithi.'; }
            }

            if ($type === 'vivah') {
                if (in_array($nakIdx, self::$vivahNakUttam))      { $score += 20; $flags['nak_uttam']  = true; }
                elseif (in_array($nakIdx, self::$vivahNakMadhyam)){ $score += 8;  $flags['nak_madhyam'] = true; }
                elseif (in_array($nakIdx, self::$vivahNakVarjit)) { $score -= 20; $doshas[] = self::$nakNames[$nakIdx] . ' Nakshatra prohibited.'; $flags['nak_varjit'] = true; }
            } else {
                $goodNak = match ($type) {
                    'griha_pravesh' => self::$grihaGoodNak,
                    'mundan'        => self::$mundanGoodNak,
                    'sampatti'      => self::$sampattiGoodNak,
                    default         => self::$vahanGoodNak,
                };
                if (in_array($nakIdx, $goodNak))             { $score += 18; }
                elseif (in_array($nakIdx, [1,5,8,9,17,18])) { $score -= 15; $doshas[] = self::$nakNames[$nakIdx] . ' Dosha.'; }
            }

            $yogaName = $pancha['yoga']['n'];
            if (in_array($yogaName, self::$yogaAshubha))  { $score -= 10; $doshas[] = $yogaName . ' Yoga Dosha.'; }
            elseif (in_array($yogaName, self::$yogaShubha)) { $score += 8; }

            $isBhadra = ($tk['karana']['n'] === 'Vishti');
            if ($isBhadra) { $score -= 8; $doshas[] = 'Bhadra Dosha.'; $flags['bhadra'] = true; }

            $isPanchak = in_array($nakIdx, self::$panchakNak);
            if ($isPanchak && in_array($type, ['vivah', 'griha_pravesh'])) { $score -= 6; $doshas[] = 'Panchak.'; $flags['panchak'] = true; }

            $guruDiff   = self::angDiff($sunLon, $jupLon);
            $shukraDiff = self::angDiff($sunLon, $venLon);
            $guruAsta   = ($guruDiff < 11.0);
            $shukraAsta = ($shukraDiff < 10.0);
            if ($guruAsta   && $type === 'vivah') { $score -= 25; $doshas[] = 'Jupiter combust (' . round($guruDiff, 1) . 'deg).'; $flags['guru_asta']  = true; }
            if ($shukraAsta && $type === 'vivah') { $score -= 25; $doshas[] = 'Venus combust (' . round($shukraDiff, 1) . 'deg).'  ; $flags['shukra_asta'] = true; }

            // Latta Dosha using offsets from DB
            $allLons     = ['sun' => $sunLon, 'moon' => $monLon, 'mars' => $marLon, 'mercury' => $merLon, 'jupiter' => $jupLon, 'venus' => $venLon, 'saturn' => $satLon];
            $lattaDoshas = [];
            $pids        = ['sun', 'moon', 'mars', 'mercury', 'jupiter', 'venus', 'saturn'];
            foreach (self::$lattaOffsets as $pid => $off) {
                if (!isset($allLons[$pid])) continue;
                $sider  = fmod(fmod($allLons[$pid] - $ayan, 360) + 360, 360);
                $pNak   = (int)floor($sider / (360 / 27));
                $latNak = ($pNak + $off) % 27;
                if ($latNak === $nakIdx) {
                    $pidIdx      = array_search($pid, $pids);
                    $lattaDoshas[] = self::GRAHA_HI[$pidIdx !== false ? $pidIdx : 0] . ' Latta';
                    $score -= 8;
                }
            }
            if ($lattaDoshas) { $doshas[] = implode(', ', $lattaDoshas) . ' Dosha.'; $flags['latta'] = true; }

            // Chandrabala
            $chandrabala = null;
            if ($girlRashi !== null) {
                $cb = self::getChandrabala($girlRashi, $moonRashi);
                $chandrabala = $cb;
                $score += $cb['bonus'];
                if (!$cb['shubh']) { $doshas[] = 'Chandrabala inauspicious (' . $cb['label'] . ').'; $flags['chandrabala_bad']  = true; }
                else               { $shubh[]  = 'Chandrabala auspicious ('  . $cb['label'] . ').'; $flags['chandrabala_good'] = true; }
            }

            // Tarabala
            $tarabala = null;
            if ($girlNak !== null) {
                $tb = self::getTarabala($girlNak, $nakIdx);
                $tarabala = $tb;
                $score += $tb['bonus'];
                if (!$tb['shubh']) { $doshas[] = 'Tarabala inauspicious (' . $tb['name'] . ').'; $flags['tara_bad'] = true; }
                else               { $shubh[]  = 'Tarabala auspicious ('  . $tb['name'] . ').'; }
            }

            if ($boyRashi !== null) {
                $bcb = self::getChandrabala($boyRashi, $moonRashi);
                $score += (int)($bcb['bonus'] * 0.5);
                if (!$bcb['shubh']) $doshas[] = 'Groom Chandrabala inauspicious.';
            }

            if ($boyNak !== null) {
                $btb = self::getTarabala($boyNak, $nakIdx);
                $score += (int)($btb['bonus'] * 0.5);
            }

            $milan = null;
            if ($girlRashi !== null && $boyRashi !== null && $type === 'vivah') {
                $milan    = self::ashtkootMilan($girlRashi, $boyRashi, $girlNak, $boyNak);
                $milanPct = $milan['total'] / 36.0;
                $score   += (int)(($milanPct - 0.5) * 20);
                if (!empty($milan['mahadosha'])) { $doshas[] = implode(' | ', $milan['mahadosha']) . '.'; }
            }

            $dayLen  = $setHr - $riseHr;
            $partLen = $dayLen / 8.0;
            $rahuS   = $riseHr + (self::$rahuPart[$varaIdx] - 1) * $partLen;
            $rahuE   = $rahuS + $partLen;
            $abhi    = ($riseHr + $setHr) / 2.0;

            $choData  = self::computeChoghadiya($riseHr, $setHr, $varaIdx);
            $shubhCho = array_filter($choData['day'], fn($c) => $c['shubh']);

            $finalScore = max(0, min(100, $score));
            $grade      = self::grade($finalScore);

            $results[] = [
                'day'          => $d,
                'dateStr'      => sprintf('%02d/%02d/%04d', $d, $mo, $yr),
                'isoDate'      => sprintf('%04d-%02d-%02d', $yr, $mo, $d),
                'score'        => $finalScore,
                'grade'        => $grade,
                'varaIdx'      => $varaIdx,
                'varaHi'       => self::$varaNames[$varaIdx],
                'paksha'       => $paksha,
                'pakshaHi'     => self::PAKSHA_HI[$paksha],
                'tithiNum'     => $tithiNum,
                'tithiHi'      => self::$tithiNames[min($tithiNum - 1, 14)],
                'tithiIdx'     => $tithiIdx,
                'nakIdx'       => $nakIdx,
                'nakHi'        => self::$nakNames[$nakIdx],
                'moonRashi'    => $moonRashi,
                'moonRashiHi'  => self::$rashiNames[$moonRashi],
                'sunRashi'     => $sunRashi,
                'sunRashiHi'   => self::$rashiNames[$sunRashi],
                'yogaHi'       => self::yogaHi($yogaName),
                'karanaHi'     => self::karanaHi($tk['karana']['n']),
                'sunrise'      => AstroCalculator::decToHMS($riseHr),
                'sunset'       => AstroCalculator::decToHMS($setHr),
                'doshas'       => $doshas,
                'shubh'        => $shubh,
                'flags'        => $flags,
                'chandrabala'  => $chandrabala,
                'tarabala'     => $tarabala,
                'milan'        => $milan,
                'guruAsta'     => $guruAsta,
                'shukraAsta'   => $shukraAsta,
                'lattaDoshas'  => $lattaDoshas,
                'isBhadra'     => $isBhadra,
                'isPanchak'    => $isPanchak,
                'shubhCho'     => array_values($shubhCho),
                'abhi'         => self::hm($abhi - 0.4) . '--' . self::hm($abhi + 0.4),
                'rahuStr'      => self::hm($rahuS) . '--' . self::hm($rahuE),
            ];
        }

        usort($results, fn($a, $b) => $a['day'] - $b['day']);
        return array_values(array_filter($results, fn($r) => $r['score'] >= $minScore));
    }

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

    // =========================================================================
    //  Controller compatibility wrappers
    // =========================================================================

    public static function calculate(int $yr, int $mo, int $dy, float $lat, float $lon, float $utcOff, string $type, array $options = []): array
    {
        return self::computeFullDay($yr, $mo, $dy, $lat, $lon, $utcOff, $type, $options);
    }

    public static function prepareDayView(array $data, string $type): array
    {
        self::loadMuhurtaData();

        $p = $data['panchanga'];
        $mainData = match ($type) {
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

        $yogaClr = in_array($p['yogaHi'], self::$yogaAshubha) ? '#c0302a'
                 : (in_array($p['yogaHi'], self::$yogaShubha) ? '#1a6a2a' : '#5a5030');

        $typeLabel = match ($type) {
            'vivah'         => 'Vivah Muhurta',
            'griha_pravesh' => 'Griha Pravesh Muhurta',
            'vahana'        => 'Vahana Puja',
            'mundan'        => 'Mundan Sanskar',
            'sampatti'      => 'Property Purchase',
            default         => 'Muhurta Analysis',
        };

        $choRows = [];
        foreach ($data['choghadiya']['day'] as $ch) {
            $choRows[] = array_merge($ch, [
                'borderColor' => $ch['shubh'] ? '#2d7a3a' : ($ch['idx'] === 4 ? '#c0302a' : '#9a6b0a'),
                'bgColor'     => $ch['shubh'] ? 'rgba(26,106,42,.12)' : ($ch['idx'] === 4 ? 'rgba(192,48,42,.12)' : 'rgba(120,96,16,.08)'),
            ]);
        }

        $horaRows = [];
        foreach (array_slice($data['hora'], 0, 8) as $h) {
            if (!$h['isDay']) continue;
            $horaRows[] = array_merge($h, [
                'bgColor'   => $h['shubh'] ? 'rgba(26,90,66,.1)' : 'rgba(120,80,10,.07)',
                'textColor' => $h['shubh'] ? '#1a5a42' : '#7a5010',
            ]);
        }

        $doshas    = $mainData['doshas'] ?? [];
        $shubhList = $mainData['shubh']  ?? [];

        $lagnaRows = [];
        foreach ($data['lagna'] as $l) {
            $lagnaRows[] = array_merge($l, [
                'color' => $l['shubh'] ? '#1a5a28' : '#7a5010',
                'bg'    => $l['shubh'] ? 'rgba(26,90,42,.06)' : 'rgba(120,80,10,.04)',
            ]);
        }

        $vivahData = $data['vivah'] ?? [];
        $windows   = $vivahData['windows'] ?? self::getAuspiciousWindows(
            $data['riseHr'], $data['setHr'],
            [[$data['rahuKaal']['s'], $data['rahuKaal']['e']], [$data['yamaghanta']['s'], $data['yamaghanta']['e']]]
        );
        $windowSlots = [];
        foreach (array_slice($windows, 0, 5) as $w) {
            $windowSlots[] = ['start' => self::hm($w['s']), 'end' => self::hm($w['e'])];
        }

        $shastraRules = $mainData['shastra'] ?? [];
        $lattaDoshas  = ($type === 'vivah') ? ($vivahData['lattaDoshas'] ?? []) : [];

        $astaData = null;
        if ($type === 'vivah') {
            $gA = $vivahData['guruAsta']   ?? null;
            $sA = $vivahData['shukraAsta'] ?? null;
            if ($gA && $sA) {
                $astaData = [
                    'guru'   => array_merge($gA, ['color' => $gA['asta']   ? '#c0302a' : '#1a6a2a']),
                    'shukra' => array_merge($sA, ['color' => $sA['asta']   ? '#c0302a' : '#1a6a2a']),
                ];
            }
        }

        $chandrabalaData = null;
        if ($type === 'vivah') {
            $cb = $vivahData['chandrabala'] ?? null;
            $tb = $vivahData['tarabala']    ?? null;
            if ($cb) {
                $chandrabalaData = [
                    'cb' => array_merge($cb, [
                        'color' => $cb['shubh'] ? '#1a5a28' : '#841808',
                        'bg'    => $cb['shubh'] ? '#e8f8ec' : '#fff0ee',
                        'icon'  => $cb['shubh'] ? 'ok' : 'x',
                    ]),
                    'tb' => $tb ? array_merge($tb, [
                        'color' => $tb['shubh'] ? '#1a5a28' : '#841808',
                        'bg'    => $tb['shubh'] ? '#e8f8ec' : '#fff0ee',
                        'icon'  => $tb['shubh'] ? 'ok' : 'x',
                    ]) : null,
                    'girlRashi' => $vivahData['milan']['girRashi'] ?? null,
                    'boyRashi'  => $vivahData['milan']['boyRashi'] ?? null,
                    'girlNak'   => $vivahData['milan']['girNak']   ?? null,
                    'boyNak'    => $vivahData['milan']['boyNak']   ?? null,
                ];
            }
        }

        $milanData = null;
        if ($type === 'vivah' && !empty($vivahData['milan'])) {
            $km = $vivahData['milan'];
            $kootDesc = [
                'varna'   => ['Varna',        '1', 'Varna compatibility -- social harmony.'],
                'vasya'   => ['Vasya',         '2', 'Vasya compatibility -- authority and love.'],
                'tara'    => ['Tara',          '3', 'Tara compatibility -- fortune and health.'],
                'yoni'    => ['Yoni',          '4', 'Yoni compatibility -- physical and mental harmony.'],
                'maitri'  => ['Graha Maitri',  '5', 'Planetary friendship -- mental compatibility.'],
                'gana'    => ['Gana',           '6', 'Gana compatibility -- nature compatibility.'],
                'bhakoot' => ['Bhakoot',        '7', 'Bhakoot -- prosperity and progeny.'],
                'nadi'    => ['Nadi',           '8', 'Nadi -- health and progeny. MC: most important.'],
            ];
            $mt    = $km['total'];
            $mc    = $km['rating']['color'];
            $interp = match (true) {
                $mt >= 32 => 'Excellent -- ideal for marriage.',
                $mt >= 27 => 'Very Good -- auspicious for marriage.',
                $mt >= 21 => 'Moderate -- generally acceptable.',
                $mt >= 18 => 'Acceptable -- ensure no Mahadosha.',
                default   => 'Not compatible -- reconsider.',
            };
            $kootRows = [];
            foreach ($km['koot'] as $key => $kv) {
                $cl    = $kv['got'] === $kv['max'] ? '#1a6a2a' : ($kv['got'] > 0 ? '#9a6b0a' : '#c0302a');
                $rowBg = ($kv['dosha'] ?? false) ? '#fff5f0' : ($kv['got'] === $kv['max'] ? '#f0faf2' : '#fdf8ee');
                $kootRows[] = array_merge($kv, [
                    'kootName'     => $kootDesc[$key][0] ?? ($kv['name'] ?? $key),
                    'maxDisplay'   => $kootDesc[$key][1] ?? (string)$kv['max'],
                    'color'        => $cl,
                    'rowBg'        => $rowBg,
                    'isAuspicious' => !($kv['dosha'] ?? false),
                ]);
            }
            $milanData = array_merge($km, [
                'color'    => $mc,
                'interp'   => $interp,
                'kootRows' => $kootRows,
            ]);
        }

        $months = ['January','February','March','April','May','June',
                   'July','August','September','October','November','December'];
        $scanYears = [];
        for ($y = (int)date('Y'); $y <= (int)date('Y') + 3; $y++) {
            $scanYears[] = $y;
        }

        return [
            'typeLabel'    => $typeLabel,
            'type'         => $type,
            'panchanga'    => $p,
            'score'        => $score,
            'grade'        => $grade,
            'color'        => $clr,
            'yogaColor'    => $yogaClr,
            'isPanchak'    => $p['isPanchak'],
            'isBhadra'     => $p['isBhadra'],
            'sunrise'      => $data['sunrise'],
            'sunset'       => $data['sunset'],
            'dateHi'       => $data['dateHi'],
            'lagnaRashiHi' => $data['lagnaRashiHi'],
            'moonRashiHi'  => $data['moonRashiHi'],
            'sunRashiHi'   => $data['sunRashiHi'],
            'rahuKaal'     => $data['rahuKaal'],
            'yamaghanta'   => $data['yamaghanta'],
            'gulikaKaal'   => $data['gulikaKaal'],
            'abhijit'      => $data['abhijit'],
            'panchak'      => $data['panchak'],
            'choRows'      => $choRows,
            'horaRows'     => $horaRows,
            'doshas'       => $doshas,
            'shubhList'    => $shubhList,
            'lagnaRows'    => $lagnaRows,
            'windowSlots'  => $windowSlots,
            'shastraRules' => $shastraRules,
            'milanData'    => $milanData,
            'chandrabala'  => $chandrabalaData,
            'astaData'     => $astaData,
            'lattaDoshas'  => $lattaDoshas,
            'months'       => $months,
            'currentMo'    => (int)date('n'),
            'currentYr'    => (int)date('Y'),
            'scanYears'    => $scanYears,
        ];
    }

    public static function prepareMonthView(array $dates, string $type, int $mo = 0, int $yr = 0): array
    {
        self::loadMuhurtaData();

        $moNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
        $head = $mo > 0 ? "{$moNames[$mo]} {$yr} -- " . match ($type) {
            'vivah'         => 'Auspicious Vivah Muhurta',
            'griha_pravesh' => 'Griha Pravesh Muhurta',
            'vahana'        => 'Vahana Purchase',
            'mundan'        => 'Mundan',
            'sampatti'      => 'Property Purchase',
            default         => 'Auspicious Muhurta',
        } : '';

        if (empty($dates)) {
            return ['isEmpty' => true, 'head' => $head, 'rows' => []];
        }

        $gradeColor = fn(int $s) => $s >= 85 ? '#1a6a2a' : ($s >= 70 ? '#2d7a3a' : ($s >= 55 ? '#9a6b0a' : ($s >= 40 ? '#8a5010' : '#c0302a')));
        $gradeLabel = fn(int $s) => $s >= 85 ? 'Excellent' : ($s >= 70 ? 'Very Good' : ($s >= 55 ? 'Auspicious' : ($s >= 40 ? 'Ordinary' : 'Inauspicious')));
        $gradeBg    = fn(int $s) => $s >= 85 ? '#f0faf2' : ($s >= 70 ? '#f5faf0' : ($s >= 55 ? '#fffdf0' : ($s >= 40 ? '#fffbf5' : '#fff5f0')));

        $rows = [];
        foreach ($dates as $d) {
            $s = $d['score'];

            $cbCell = null;
            if (isset($d['chandrabala'])) {
                $cb = $d['chandrabala'];
                $cbCell = [
                    'shubh' => $cb['shubh'],
                    'icon'  => $cb['shubh'] ? 'ok' : 'x',
                    'dist'  => $cb['dist'],
                    'color' => $cb['shubh'] ? '#1a5a28' : '#841808',
                    'bg'    => $cb['shubh'] ? '#e8f8ec' : '#fff0ee',
                    'tb'    => null,
                ];
            }
            if ($cbCell && isset($d['tarabala'])) {
                $tb = $d['tarabala'];
                $cbCell['tb'] = [
                    'shubh' => $tb['shubh'],
                    'icon'  => $tb['shubh'] ? 'ok' : 'x',
                    'name'  => $tb['name'],
                    'color' => $tb['shubh'] ? '#1a5a28' : '#841808',
                    'bg'    => $tb['shubh'] ? '#e8f8ec' : '#fff0ee',
                ];
            }

            $flags = $d['flags'] ?? [];
            $doshaItems = [];
            if ($flags['guru_asta']      ?? false) $doshaItems[] = ['text' => 'Jupiter Combust', 'color' => '#841808'];
            if ($flags['shukra_asta']    ?? false) $doshaItems[] = ['text' => 'Venus Combust',   'color' => '#841808'];
            if ($flags['bhadra']         ?? false) $doshaItems[] = ['text' => 'Bhadra',           'color' => '#841808'];
            if ($flags['panchak']        ?? false) $doshaItems[] = ['text' => 'Panchak',          'color' => '#841808'];
            if ($flags['latta']          ?? false) $doshaItems[] = ['text' => 'Latta',            'color' => '#c86a14'];
            if ($flags['nak_varjit']     ?? false) $doshaItems[] = ['text' => 'Prohibited Nak',   'color' => '#841808'];
            if ($flags['krishna_paksha'] ?? false) $doshaItems[] = ['text' => 'Krishna Paksha',   'color' => '#841808'];

            $yogaColor = in_array($d['yogaHi'], self::$yogaAshubha) ? '#841808'
                       : (in_array($d['yogaHi'], self::$yogaShubha) ? '#1a5a28' : '#7a5830');

            $choParts = [];
            foreach (($d['shubhCho'] ?? []) as $ch) {
                $choParts[] = $ch['name'] . ' ' . $ch['start'];
            }

            $milanBadge = null;
            if (isset($d['milan']['total'])) {
                $milanBadge = ['total' => $d['milan']['total'], 'color' => $d['milan']['rating']['color']];
            }

            $rows[] = array_merge($d, [
                'gradeColor' => $gradeColor($s),
                'gradeLabel' => $gradeLabel($s),
                'gradeBg'    => $gradeBg($s),
                'cbCell'     => $cbCell,
                'doshaItems' => $doshaItems,
                'yogaColor'  => $yogaColor,
                'choStr'     => $choParts ? implode(', ', $choParts) : '',
                'milanBadge' => $milanBadge,
            ]);
        }

        return ['isEmpty' => false, 'head' => $head, 'rows' => $rows];
    }

    // =========================================================================
    //  Helpers
    // =========================================================================

    private static function getPlanetPositions(float $jd, float $ayan): array
    {
        $sunLon = AstroCalculator::sunLongitude($jd);
        $monLon = AstroCalculator::moonLongitude($jd);
        $jupLon = AstroCalculator::planetLongitude($jd, 'jupiter');
        $venLon = AstroCalculator::planetLongitude($jd, 'venus');
        $marLon = AstroCalculator::planetLongitude($jd, 'mars');
        $merLon = AstroCalculator::planetLongitude($jd, 'mercury');
        $satLon = AstroCalculator::planetLongitude($jd, 'saturn');

        $n = fn($x) => fmod(fmod($x - $ayan, 360) + 360, 360);

        return [
            'sun'     => ['lon' => $sunLon, 'sider' => $n($sunLon), 'rashi' => (int)floor($n($sunLon) / 30)],
            'moon'    => ['lon' => $monLon, 'sider' => $n($monLon), 'rashi' => (int)floor($n($monLon) / 30),
                          'nak' => (int)floor($n($monLon) / (360 / 27))],
            'jupiter' => ['lon' => $jupLon, 'sider' => $n($jupLon)],
            'venus'   => ['lon' => $venLon, 'sider' => $n($venLon)],
            'mars'    => ['lon' => $marLon, 'sider' => $n($marLon)],
            'mercury' => ['lon' => $merLon, 'sider' => $n($merLon)],
            'saturn'  => ['lon' => $satLon, 'sider' => $n($satLon)],
        ];
    }

    private static function buildPanchaData(array $pancha, array $tk): array
    {
        self::loadMuhurtaData();
        $nakIdx = $pancha['nakIdx'];
        return [
            'varaIdx'    => $pancha['varaIdx'],
            'varaHi'     => self::$varaNames[$pancha['varaIdx']],
            'varaEn'     => $pancha['vara']['en'],
            'varaLord'   => $pancha['vara']['lord'],
            'pakshaHi'   => self::PAKSHA_HI[$tk['tithi']['paksha']],
            'paksha'     => $tk['tithi']['paksha'],
            'tithiNum'   => $tk['tithi']['num'],
            'tithiHi'    => self::$tithiNames[min($tk['tithi']['num'] - 1, 14)],
            'tithiIdx'   => $tk['tithiIndex'],
            'nakIdx'     => $nakIdx,
            'nakHi'      => self::$nakNames[$nakIdx],
            'nakEn'      => $pancha['moonNak']['n'],
            'nakLord'    => $pancha['moonNak']['l'],
            'nakPada'    => $pancha['nakPada'],
            'nakGana'    => self::GANA_HI[self::$nakGana[$nakIdx] ?? 0],
            'nakNadi'    => self::NADI_HI[self::$nakNadi[$nakIdx] ?? 0],
            'yogaHi'     => self::yogaHi($pancha['yoga']['n']),
            'yogaEn'     => $pancha['yoga']['n'],
            'yogaNature' => $pancha['yoga']['nature'],
            'karanaHi'   => self::karanaHi($tk['karana']['n']),
            'karanaEn'   => $tk['karana']['n'],
            'elong'      => round($tk['elong'], 2),
            'isPanchak'  => in_array($nakIdx, self::$panchakNak ?? []),
            'isBhadra'   => $tk['karana']['n'] === 'Vishti',
        ];
    }

    private static function getAuspiciousWindows(float $rise, float $set, array $bad): array
    {
        $dayLen = $set - $rise;
        $muLen  = $dayLen / 30.0;
        $good   = [];
        for ($i = 0; $i < 30; $i++) {
            $s  = $rise + $i * $muLen;
            $e  = $s + $muLen;
            $ok = true;
            foreach ($bad as [$bs, $be]) {
                if ($s < $be && $e > $bs) { $ok = false; break; }
            }
            if ($ok) $good[] = ['s' => $s, 'e' => $e];
        }
        $merged = [];
        foreach ($good as $w) {
            if ($merged && ($w['s'] - $merged[count($merged) - 1]['e']) < 0.02) {
                $merged[count($merged) - 1]['e'] = $w['e'];
            } else {
                $merged[] = $w;
            }
        }
        return $merged;
    }

    private static function grade(int $s): array
    {
        return match (true) {
            $s >= 85 => ['hi' => 'Excellent',    'color' => '#1a6a2a'],
            $s >= 70 => ['hi' => 'Very Good',    'color' => '#2d7a3a'],
            $s >= 55 => ['hi' => 'Auspicious',   'color' => '#9a6b0a'],
            $s >= 40 => ['hi' => 'Ordinary',     'color' => '#8a5010'],
            default  => ['hi' => 'Inauspicious', 'color' => '#c0302a'],
        };
    }

    public static function hm(float $h): string
    {
        $h  = fmod($h + 48, 24);
        $hh = (int)$h;
        $mm = (int)(($h - $hh) * 60 + 0.5);
        if ($mm === 60) { $hh++; $mm = 0; }
        return sprintf('%02d:%02d', $hh, $mm);
    }

    private static function dateHi(int $dy, int $mo, int $yr): string
    {
        $moHi = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];
        return "{$dy} {$moHi[$mo]} {$yr}";
    }
}
