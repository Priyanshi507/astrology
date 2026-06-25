<?php

namespace App\Services\ChartRendering;

use Illuminate\Support\Facades\DB;

/**
 * VargaChartRenderer — Redesigned with light/warm theme, house-based layout,
 * improved dignity matrix, Vimshopaka Bala removed.
 */
class VargaChartRenderer
{
    private static ?array $planetAbbr  = null;
    private static ?array $planetColor = null;

    private static function loadChartData(): void
    {
        if (self::$planetAbbr !== null) return;
        self::$planetAbbr  = ['ascendant' => 'As'];
        self::$planetColor = ['ascendant' => '#1a3a8e'];
        foreach (DB::table('planets')->get(['name', 'abbreviation', 'color_hex']) as $p) {
            $key = strtolower($p->name);
            self::$planetAbbr[$key]  = $p->abbreviation;
            self::$planetColor[$key] = $p->color_hex ?? '#444';
        }
    }

    // Dignity fill colors — warm palette
    private const DIGNITY_BG = [
        'Exalted'      => '#d4edda',
        'Own Sign'     => '#d0e8fb',
        'Moolatrikona' => '#dce8f8',
        'Friendly'     => '#e8f5e9',
        'Neutral'      => '#f0f0f0',
        'Inimical'     => '#fde8d0',
        'Debilitated'  => '#fde0e0',
    ];

    private const DIGNITY_TEXT = [
        'Exalted'      => '#1a6b34',
        'Own Sign'     => '#1a5a8e',
        'Moolatrikona' => '#2a5a9e',
        'Friendly'     => '#2a6a2a',
        'Neutral'      => '#555555',
        'Inimical'     => '#b05010',
        'Debilitated'  => '#c02020',
    ];

    // Short dignity labels
    private const DIGNITY_SHORT = [
        'Exalted'      => 'Exl',
        'Own Sign'     => 'Own',
        'Moolatrikona' => 'Mlt',
        'Friendly'     => 'Fri',
        'Neutral'      => '—',
        'Inimical'     => 'Ene',
        'Debilitated'  => 'Deb',
    ];

    // South Indian grid: sign index → [row, col]
    private const SI_GRID = [
        11 => [0,0], 0 => [0,1], 1 => [0,2], 2  => [0,3],
        10 => [1,0],                           3  => [1,3],
        9  => [2,0],                           4  => [2,3],
        8  => [3,0], 7 => [3,1], 6 => [3,2],  5  => [3,3],
    ];

    // ════════════════════════════════════════════════════════════
    //  PRIVATE: Render one chart cell (South Indian, light theme)
    // ════════════════════════════════════════════════════════════

    private static function renderChartCell(
        array $vargaData, float $ox, float $oy, float $size, float $ascSider
    ): string {
        self::loadChartData();
        $svg  = '';
        $pad  = 3;
        $titleH = $size * 0.11;
        $chartY = $oy + $pad + $titleH;
        $chartS = $size - $pad * 2 - $titleH;
        $cw     = $chartS / 4;
        $ch     = $chartS / 4;

        // Card background
        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#fffdf9" rx="8" stroke="#e0d8cc" stroke-width="1"/>',
            $ox + $pad, $oy + $pad, $size - $pad*2, $size - $pad*2
        );

        // Title bar
        $d     = $vargaData['division'];
        $name  = self::vargaShortName($d);
        $titleFg = '#5a3a10';

        $svg .= sprintf(
            '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#e8ddd0" rx="6"/>',
            $ox + $pad, $oy + $pad, $size - $pad*2, $titleH
        );
        $svg .= sprintf(
            '<text x="%.1f" y="%.1f" text-anchor="middle" fill="%s" font-size="%.1f" font-weight="800" letter-spacing=".3">D%d · %s</text>',
            $ox + $size/2, $oy + $pad + $titleH * 0.67,
            $titleFg, $size * 0.052, $d, $name
        );

        // Ascendant sign for THIS varga
        $ascVargaSign = null;
        if (isset($vargaData['planets']['ascendant'])) {
            $ascVargaSign = $vargaData['planets']['ascendant']['vargaSignIdx'];
        }

        // Build sign→planets map
        $signPlanets = array_fill(0, 12, []);
        foreach ($vargaData['planets'] as $pid => $pdata) {
            if ($pid === 'ascendant') continue;
            $s = $pdata['vargaSignIdx'];
            $signPlanets[$s][] = [
                'abbr'    => self::$planetAbbr[$pid] ?? strtoupper(substr($pid,0,2)),
                'dignity' => $pdata['dignity'],
                'retro'   => $pdata['retro'] ?? false,
                'pid'     => $pid,
            ];
        }

        // Short sign abbreviations (2-char for tiny charts)
        $SIGN_2 = ['Me','Vr','Mi','Ka','Si','Kn','Tu','Vs','Dh','Mk','Ku','Mn'];

        foreach (self::SI_GRID as $signIdx => [$row, $col]) {
            $cx = $ox + $pad + $col * $cw;
            $cy = $chartY + $row * $ch;

            // Skip inner cells (they're covered by the 4 corner inner cells)
            if ($row >= 1 && $row <= 2 && $col >= 1 && $col <= 2) continue;

            $isLagna = ($ascVargaSign === $signIdx);
            $cellFill = $isLagna ? '#fff8e8' : '#fffdf9';
            $stroke   = $isLagna ? '#d4a020' : '#e0d4c0';

            $svg .= sprintf(
                '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" stroke="%s" stroke-width="%.1f"/>',
                $cx, $cy, $cw, $ch, $cellFill, $stroke, $isLagna ? 1.2 : 0.7
            );

            // Sign label (top-left, very small)
            $svg .= sprintf(
                '<text x="%.1f" y="%.1f" fill="%s" font-size="%.1f" font-weight="600">%s</text>',
                $cx + 2.5, $cy + $ch * 0.22,
                $isLagna ? '#9a6000' : '#b0a090',
                $size * 0.036,
                $SIGN_2[$signIdx]
            );

            // Lagna triangle indicator
            if ($isLagna) {
                $ts = $cw * 0.35;
                $svg .= sprintf(
                    '<polygon points="%.1f,%.1f %.1f,%.1f %.1f,%.1f" fill="#d4a020" opacity="0.3"/>',
                    $cx + $cw - $ts, $cy + 1,
                    $cx + $cw - 1,   $cy + 1,
                    $cx + $cw - 1,   $cy + $ts
                );
            }

            // Planets
            $pList  = $signPlanets[$signIdx];
            $pCount = count($pList);
            $fsize  = $size * 0.044;
            $lineH  = min($ch * 0.26, ($ch * 0.72) / max($pCount, 1));

            foreach ($pList as $pi => $p) {
                $color = self::$planetColor[$p['pid']] ?? '#444';
                $label = $p['abbr'] . ($p['retro'] ? '℞' : '');
                $py    = $cy + $ch * 0.42 + ($pi - ($pCount-1)/2.0) * $lineH;

                $svg .= sprintf(
                    '<text x="%.1f" y="%.1f" text-anchor="middle" fill="%s" font-size="%.1f" font-weight="700">%s</text>',
                    $cx + $cw/2, $py,
                    $color, $fsize, htmlspecialchars($label)
                );
            }
        }

        // Draw inner 4 blank cells
        for ($r = 1; $r <= 2; $r++) {
            for ($c = 1; $c <= 2; $c++) {
                $cx = $ox + $pad + $c * $cw;
                $cy = $chartY + $r * $ch;
                $svg .= sprintf(
                    '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#f2ece4" stroke="#e0d4c0" stroke-width="0.5"/>',
                    $cx, $cy, $cw, $ch
                );
            }
        }

        return $svg;
    }

    // ════════════════════════════════════════════════════════════
    //  VIEW DATA METHODS — return structured arrays for Blade partials
    // ════════════════════════════════════════════════════════════

    public static function prepareVargaGridData(array $allVargas, float $ascSider): array
    {
        $VARGA_KEYS = ['D1','D2','D3','D4','D7','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];
        $extra      = ['D5','D6','D8','D11'];
        $allKeys    = array_merge($VARGA_KEYS, $extra);

        $chartSize = 210;
        $cols      = 4;
        $rows      = (int)ceil(count($allKeys) / $cols);
        $totalW    = $chartSize * $cols;
        $totalH    = $chartSize * $rows;

        $svg = sprintf(
            '<svg viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" style="width:100%%;display:block;font-family:\'DM Sans\',sans-serif">',
            $totalW, $totalH
        );
        $svg .= sprintf('<rect width="%d" height="%d" fill="#f5f0eb"/>', $totalW, $totalH);

        foreach ($allKeys as $i => $vk) {
            if (!isset($allVargas[$vk])) continue;
            $col  = $i % $cols;
            $row  = (int)floor($i / $cols);
            $svg .= self::renderChartCell($allVargas[$vk], $col * $chartSize, $row * $chartSize, $chartSize, $ascSider);
        }

        $svg .= '</svg>';

        return [
            'chartSvg'      => $svg,
            'dignityMatrix' => self::buildDignityMatrixData($allVargas, $allKeys),
        ];
    }

    public static function prepareSingleVargaData(array $vargaData, int $size = 320): array
    {
        return [
            'svg'  => self::renderChartCell($vargaData, 0, 0, $size, 0),
            'size' => $size,
        ];
    }

    public static function buildPlanetVargaSummaryData(array $allVargas, string $pid): array
    {
        $VARGA_KEYS = ['D1','D2','D3','D9','D10','D12','D30','D60'];
        $badges = [];
        foreach ($VARGA_KEYS as $vk) {
            if (!isset($allVargas[$vk]['planets'][$pid])) continue;
            $p   = $allVargas[$vk]['planets'][$pid];
            $dig = $p['dignity'];
            $badges[] = [
                'd'        => $allVargas[$vk]['division'],
                'signShort'=> substr($p['vargaSign'], 0, 3),
                'digShort' => self::DIGNITY_SHORT[$dig] ?? '—',
                'bg'       => self::DIGNITY_BG[$dig]    ?? '#f0f0f0',
                'tc'       => self::DIGNITY_TEXT[$dig]   ?? '#555',
            ];
        }
        return ['badges' => $badges];
    }

    private static function buildDignityMatrixData(array $allVargas, array $vargaKeys): array
    {
        $PLANETS  = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
        $SYMS     = ['sun'=>'☀','moon'=>'☽','mercury'=>'☿','venus'=>'♀','mars'=>'♂','jupiter'=>'♃','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋'];
        $P_LABELS = ['sun'=>'Sun','moon'=>'Moon','mercury'=>'Mercury','venus'=>'Venus','mars'=>'Mars','jupiter'=>'Jupiter','saturn'=>'Saturn','rahu'=>'Rahu','ketu'=>'Ketu'];

        // Stats per planet
        $stats = [];
        foreach ($PLANETS as $pid) {
            $stats[$pid] = ['Exalted'=>0,'Own Sign'=>0,'Moolatrikona'=>0,'Friendly'=>0,'Neutral'=>0,'Inimical'=>0,'Debilitated'=>0,'total'=>0];
        }
        foreach ($vargaKeys as $vk) {
            if (!isset($allVargas[$vk]['planets'])) continue;
            foreach ($PLANETS as $pid) {
                $dig = $allVargas[$vk]['planets'][$pid]['dignity'] ?? 'Neutral';
                if (isset($stats[$pid][$dig])) { $stats[$pid][$dig]++; $stats[$pid]['total']++; }
            }
        }

        // Planet summary cards
        $planetCards = [];
        foreach ($PLANETS as $pid) {
            $s      = $stats[$pid];
            $tot    = $s['total'] ?: 1;
            $exlPct = round($s['Exalted'] / $tot * 100);
            $ownPct = round(($s['Own Sign'] + $s['Moolatrikona']) / $tot * 100);
            $friPct = round($s['Friendly'] / $tot * 100);
            $neuPct = round($s['Neutral'] / $tot * 100);
            $badPct = round(($s['Inimical'] + $s['Debilitated']) / $tot * 100);
            $planetCards[] = [
                'pid'    => $pid,
                'sym'    => $SYMS[$pid],
                'label'  => $P_LABELS[$pid],
                'exlPct' => $exlPct,
                'ownPct' => $ownPct,
                'friPct' => $friPct,
                'neuPct' => $neuPct,
                'badPct' => $badPct,
                'goodPct'=> $exlPct + $ownPct,
            ];
        }

        // Legend items
        $legend = [];
        foreach (self::DIGNITY_BG as $dig => $bg) {
            $legend[] = [
                'dig'   => $dig,
                'short' => self::DIGNITY_SHORT[$dig],
                'bg'    => $bg,
                'tc'    => self::DIGNITY_TEXT[$dig],
            ];
        }

        // Matrix rows
        $matrixRows = [];
        foreach ($vargaKeys as $vi => $vk) {
            if (!isset($allVargas[$vk]['planets'])) continue;
            $vd    = $allVargas[$vk];
            $d     = $vd['division'];
            $cells = [];
            foreach ($PLANETS as $pid) {
                $pdata    = $vd['planets'][$pid] ?? null;
                $dig      = $pdata['dignity'] ?? 'Neutral';
                $cells[] = [
                    'tooltip'   => ucfirst($pid) . ' in ' . htmlspecialchars($pdata['vargaSign'] ?? '—') . ' (' . $dig . ')',
                    'bg'        => self::DIGNITY_BG[$dig]    ?? '#f0f0f0',
                    'tc'        => self::DIGNITY_TEXT[$dig]   ?? '#555',
                    'signShort' => $pdata ? substr($pdata['vargaSign'], 0, 3) : '—',
                    'retro'     => $pdata['retro'] ?? false,
                    'digShort'  => self::DIGNITY_SHORT[$dig]  ?? '—',
                ];
            }
            $matrixRows[] = [
                'division' => $d,
                'name'     => self::vargaShortName($d),
                'signif'   => substr(self::vargaSignif($d), 0, 22),
                'altBg'    => ($vi % 2 === 1) ? '#faf5ee' : '#fffdf9',
                'cells'    => $cells,
                'planets'  => $PLANETS,
            ];
        }

        return [
            'planets'    => $planetCards,
            'legend'     => $legend,
            'matrixRows' => $matrixRows,
            'planetHeaders' => array_map(fn($pid) => ['sym' => $SYMS[$pid], 'label' => $P_LABELS[$pid]], $PLANETS),
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  LEGACY (kept for internal usage; controller now uses Blade)
    // ════════════════════════════════════════════════════════════

    public static function buildPlanetVargaSummary(array $allVargas, string $pid): string
    {
        $data = self::buildPlanetVargaSummaryData($allVargas, $pid);
        $rows = [];
        foreach ($data['badges'] as $b) {
            $rows[] = sprintf(
                '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:16px;background:%s;border:1px solid %s44;font-size:.72rem;font-weight:700;color:%s;white-space:nowrap">D%d <span style="opacity:.7">%s</span> %s</span>',
                $b['bg'], $b['tc'], $b['tc'], $b['d'], $b['signShort'], $b['digShort']
            );
        }
        return '<div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:8px">' . implode('', $rows) . '</div>';
    }

    // ════════════════════════════════════════════════════════════
    //  UTILITY
    // ════════════════════════════════════════════════════════════

    private static function vargaShortName(int $d): string
    {
        return match($d) {
            1  => 'Rashi',
            2  => 'Hora',
            3  => 'Drekkana',
            4  => 'Chaturth.',
            5  => 'Pancha.',
            6  => 'Shashth.',
            7  => 'Saptam.',
            8  => 'Ashta.',
            9  => 'Navamsha',
            10 => 'Dashamsha',
            11 => 'Ekadasha',
            12 => 'Dwadasha.',
            16 => 'Shodasha',
            20 => 'Vimsha',
            24 => 'Chaturv.',
            27 => 'Nakshatra.',
            30 => 'Trimsha',
            40 => 'Khaved.',
            45 => 'Aksha V.',
            60 => 'Shashti.',
            default => "D{$d}",
        };
    }

    private static function vargaSignif(int $d): string
    {
        return match($d) {
            1  => 'Physical body, overall life',
            2  => 'Wealth, family, finances',
            3  => 'Siblings, courage, travels',
            4  => 'Property, assets, mother',
            5  => 'Fame, authority, power',
            6  => 'Enemies, diseases, debts',
            7  => 'Children, progeny',
            8  => 'Longevity, sudden events',
            9  => 'Spouse, dharma, soul',
            10 => 'Career, profession, status',
            11 => 'Gains, profits, income',
            12 => 'Parents, ancestry',
            16 => 'Vehicles, comforts, luxury',
            20 => 'Spiritual practice, worship',
            24 => 'Education, learning',
            27 => 'Strength, vitality',
            30 => 'Misfortunes, afflictions',
            40 => 'Maternal legacy',
            45 => 'Paternal legacy',
            60 => 'Past karma — most potent',
            default => "Division by {$d}",
        };
    }
}
