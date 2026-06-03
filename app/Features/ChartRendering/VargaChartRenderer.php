<?php

namespace App\Features\ChartRendering;

/**
 * VargaChartRenderer — Redesigned with light/warm theme, house-based layout,
 * improved dignity matrix, Vimshopaka Bala removed.
 */
class VargaChartRenderer
{
    public const PLANET_ABBR = [
        'sun'      => 'Su',
        'moon'     => 'Mo',
        'mercury'  => 'Me',
        'venus'    => 'Ve',
        'mars'     => 'Ma',
        'jupiter'  => 'Ju',
        'saturn'   => 'Sa',
        'rahu'     => 'Ra',
        'ketu'     => 'Ke',
        'ascendant'=> 'As',
    ];

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

    // Planet display colors (warm, readable on light bg)
    private const PLANET_COLORS = [
        'sun'      => '#b85c00',
        'moon'     => '#1a5f9e',
        'mercury'  => '#2a7a5a',
        'venus'    => '#8e2a7a',
        'mars'     => '#c02020',
        'jupiter'  => '#7a5000',
        'saturn'   => '#4a3a80',
        'rahu'     => '#2a6a2a',
        'ketu'     => '#7a2a10',
        'ascendant'=> '#1a3a8e',
    ];

    // Vedic sign abbreviations (3-char)
    private const SIGN_ABBR = [
        'Mee', 'Vri', 'Mit', 'Kan', 'Sim', 'Kan',
        'Tul', 'Vri', 'Dha', 'Mak', 'Kum', 'Mee',
    ];

    // Full sign names (Vedic)
    private const SIGN_NAMES = [
        'Mesha', 'Vrishabha', 'Mithuna', 'Karka',
        'Simha', 'Kanya',     'Tula',    'Vrishchika',
        'Dhanu', 'Makara',    'Kumbha',  'Meena',
    ];

    // South Indian grid: sign index → [row, col]
    private const SI_GRID = [
        11 => [0,0], 0 => [0,1], 1 => [0,2], 2  => [0,3],
        10 => [1,0],                           3  => [1,3],
        9  => [2,0],                           4  => [2,3],
        8  => [3,0], 7 => [3,1], 6 => [3,2],  5  => [3,3],
    ];

    // ════════════════════════════════════════════════════════════
    //  PUBLIC: Render all varga charts as HTML grid
    // ════════════════════════════════════════════════════════════

    public static function renderVargaGrid(array $allVargas, float $ascSider): string
    {
        $VARGA_KEYS = ['D1','D2','D3','D4','D7','D9','D10','D12','D16','D20','D24','D27','D30','D40','D45','D60'];
        $extra      = ['D5','D6','D8','D11'];
        $allKeys    = array_merge($VARGA_KEYS, $extra);

        $chartSize  = 210;
        $cols       = 4;
        $rows       = (int)ceil(count($allKeys) / $cols);
        $totalW     = $chartSize * $cols;
        $totalH     = $chartSize * $rows;

        // Outer wrapper
        $html  = '<div style="overflow-x:auto;width:100%;background:#f5f0eb;border-radius:16px;padding:16px;box-sizing:border-box">';

        // Chart grid SVG
        $html .= sprintf(
            '<svg viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" style="width:100%%;display:block;font-family:\'DM Sans\',sans-serif">',
            $totalW, $totalH
        );
        $html .= sprintf('<rect width="%d" height="%d" fill="#f5f0eb"/>', $totalW, $totalH);

        foreach ($allKeys as $i => $vk) {
            if (!isset($allVargas[$vk])) continue;
            $col = $i % $cols;
            $row = (int)floor($i / $cols);
            $html .= self::renderChartCell($allVargas[$vk], $col * $chartSize, $row * $chartSize, $chartSize, $ascSider);
        }

        $html .= '</svg>';

        // Dignity Matrix (redesigned)
        $html .= self::renderDignityMatrix($allVargas, $allKeys);
        $html .= '</div>';

        return $html;
    }

    // ════════════════════════════════════════════════════════════
    //  PRIVATE: Render one chart cell (South Indian, light theme)
    // ════════════════════════════════════════════════════════════

    private static function renderChartCell(
        array $vargaData, float $ox, float $oy, float $size, float $ascSider
    ): string {
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
                'abbr'    => self::PLANET_ABBR[$pid] ?? strtoupper(substr($pid,0,2)),
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
                $color = self::PLANET_COLORS[$p['pid']] ?? '#444';
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
    //  PUBLIC: Render single varga (standalone)
    // ════════════════════════════════════════════════════════════

    public static function renderSingleVarga(array $vargaData, int $size = 320): string
    {
        $html  = '<div style="display:inline-block;background:#f5f0eb;border-radius:12px;padding:8px">';
        $html .= sprintf(
            '<svg viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" style="width:100%%;max-width:%dpx;font-family:\'DM Sans\',sans-serif">',
            $size, $size, $size
        );
        $html .= self::renderChartCell($vargaData, 0, 0, $size, 0);
        $html .= '</svg></div>';
        return $html;
    }

    // ════════════════════════════════════════════════════════════
    //  DIGNITY MATRIX — redesigned, clean, light theme
    // ════════════════════════════════════════════════════════════

    public static function renderDignityMatrix(array $allVargas, array $vargaKeys): string
    {
        $PLANETS = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
        $SYMS    = ['sun'=>'☀','moon'=>'☽','mercury'=>'☿','venus'=>'♀','mars'=>'♂','jupiter'=>'♃','saturn'=>'♄','rahu'=>'☊','ketu'=>'☋'];
        $P_LABELS= ['sun'=>'Sun','moon'=>'Moon','mercury'=>'Mercury','venus'=>'Venus','mars'=>'Mars','jupiter'=>'Jupiter','saturn'=>'Saturn','rahu'=>'Rahu','ketu'=>'Ketu'];

        // Summary stats per planet
        $stats = [];
        foreach ($PLANETS as $pid) {
            $stats[$pid] = ['Exalted'=>0,'Own Sign'=>0,'Moolatrikona'=>0,'Friendly'=>0,'Neutral'=>0,'Inimical'=>0,'Debilitated'=>0,'total'=>0];
        }
        foreach ($vargaKeys as $vk) {
            if (!isset($allVargas[$vk]['planets'])) continue;
            foreach ($PLANETS as $pid) {
                $dig = $allVargas[$vk]['planets'][$pid]['dignity'] ?? 'Neutral';
                if (isset($stats[$pid][$dig])) {
                    $stats[$pid][$dig]++;
                    $stats[$pid]['total']++;
                }
            }
        }

        $html  = '<div style="margin-top:28px;overflow-x:auto">';

        // Section header
        $html .= '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">';
        $html .= '<div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:#7a5a30">◈ Varga Dignity Matrix</div>';
        $html .= '<div style="flex:1;height:1px;background:linear-gradient(90deg,#d0c0a8,transparent)"></div>';
        $html .= '<div style="font-size:.68rem;color:#a08060;font-style:italic">Planet positions across all divisional charts</div>';
        $html .= '</div>';

        // Legend
        $html .= '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;padding:10px 14px;background:#faf6f0;border-radius:10px;border:1px solid #e8ddd0">';
        $html .= '<span style="font-size:.65rem;font-weight:800;color:#9a7a50;text-transform:uppercase;letter-spacing:1px;margin-right:4px;align-self:center">Dignity</span>';
        foreach (self::DIGNITY_BG as $dig => $bg) {
            $tc = self::DIGNITY_TEXT[$dig];
            $sh = self::DIGNITY_SHORT[$dig];
            $html .= sprintf(
                '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;background:%s;color:%s;font-size:.68rem;font-weight:700;border:1px solid %s22">%s</span>',
                $bg, $tc, $tc, $sh === '—' ? 'Neutral' : $dig
            );
        }
        $html .= '</div>';

        // Planet summary row (donut-like stat bars)
        $html .= '<div style="display:grid;grid-template-columns:repeat(9,1fr);gap:6px;margin-bottom:16px">';
        foreach ($PLANETS as $pid) {
            $s   = $stats[$pid];
            $tot = $s['total'] ?: 1;
            $exlPct = round($s['Exalted']/$tot*100);
            $ownPct = round(($s['Own Sign']+$s['Moolatrikona'])/$tot*100);
            $friPct = round($s['Friendly']/$tot*100);
            $neuPct = round($s['Neutral']/$tot*100);
            $badPct = round(($s['Inimical']+$s['Debilitated'])/$tot*100);
            $sym    = $SYMS[$pid];

            $html .= '<div style="background:#fffdf9;border:1px solid #e8ddd0;border-radius:10px;padding:8px 6px;text-align:center">';
            $html .= sprintf('<div style="font-size:1.1rem;line-height:1;margin-bottom:3px">%s</div>', $sym);
            $html .= sprintf('<div style="font-size:.6rem;font-weight:800;color:#7a5a30;text-transform:uppercase;letter-spacing:.5px">%s</div>', $P_LABELS[$pid]);
            // Mini stacked bar
            $html .= '<div style="margin:6px 0;height:5px;border-radius:4px;overflow:hidden;display:flex;background:#eee">';
            if ($exlPct) $html .= sprintf('<div style="width:%d%%;background:#2a9a50;min-width:2px"></div>', $exlPct);
            if ($ownPct) $html .= sprintf('<div style="width:%d%%;background:#2a6aae;min-width:2px"></div>', $ownPct);
            if ($friPct) $html .= sprintf('<div style="width:%d%%;background:#5a9a5a;min-width:2px"></div>', $friPct);
            if ($neuPct) $html .= sprintf('<div style="width:%d%%;background:#c0b0a0;min-width:2px"></div>', $neuPct);
            if ($badPct) $html .= sprintf('<div style="width:%d%%;background:#c85030;min-width:2px"></div>', $badPct);
            $html .= '</div>';
            $html .= sprintf('<div style="font-size:.6rem;color:#9a8060">%d%%<span style="color:#2a9a50">↑</span> %d%%<span style="color:#c85030">↓</span></div>', $exlPct+$ownPct, $badPct);
            $html .= '</div>';
        }
        $html .= '</div>';

        // Main matrix table
        $html .= '<div style="overflow-x:auto;border-radius:12px;border:1.5px solid #ddd0c0;box-shadow:0 2px 12px rgba(120,80,20,.06)">';
        $html .= '<table style="width:100%;border-collapse:collapse;font-family:\'DM Mono\',monospace;font-size:.73rem;min-width:780px">';

        // Header
        $html .= '<thead>';
        $html .= '<tr style="background:#e8ddd0">';
        $html .= '<th style="padding:10px 14px;text-align:left;font-size:.65rem;text-transform:uppercase;letter-spacing:1px;font-weight:800;color:#7a5a30;white-space:nowrap;min-width:110px">Varga</th>';
        foreach ($PLANETS as $pid) {
            $sym = $SYMS[$pid];
            $lbl = $P_LABELS[$pid];
            $html .= sprintf(
                '<th style="padding:8px 6px;text-align:center;font-size:.65rem;color:#5a3a10;font-weight:700;white-space:nowrap">%s<br><span style="font-size:.55rem;opacity:.7;font-family:\'DM Sans\',sans-serif">%s</span></th>',
                $sym, $lbl
            );
        }
        $html .= '</tr>';
        $html .= '</thead><tbody>';

        foreach ($vargaKeys as $vi => $vk) {
            if (!isset($allVargas[$vk]['planets'])) continue;
            $vd  = $allVargas[$vk];
            $d   = $vd['division'];
            $sig = self::vargaSignif($d);
            $isAlt = ($vi % 2 === 1);

            $html .= sprintf(
                '<tr style="border-bottom:1px solid #ede0cc;background:%s" onmouseover="this.style.background=\'#fff8ee\'" onmouseout="this.style.background=\'%s\'">',
                $isAlt ? '#faf5ee' : '#fffdf9',
                $isAlt ? '#faf5ee' : '#fffdf9'
            );

            // Varga label cell
            $html .= sprintf(
                '<td style="padding:7px 14px;vertical-align:middle">'.
                '<div style="font-weight:800;color:#4a2800;font-size:.76rem;font-family:\'DM Sans\',sans-serif">D%d</div>'.
                '<div style="font-size:.6rem;color:#9a7a50;font-family:\'DM Sans\',sans-serif;margin-top:1px">%s</div>'.
                '</td>',
                $d, htmlspecialchars(substr($sig, 0, 22))
            );

            foreach ($PLANETS as $pid) {
                $pdata  = $vd['planets'][$pid] ?? null;
                $dig    = $pdata['dignity'] ?? 'Neutral';
                $sign   = $pdata ? $pdata['vargaSign'] : '—';
                $retro  = ($pdata['retro'] ?? false) ? '℞' : '';
                $bg     = self::DIGNITY_BG[$dig] ?? '#f0f0f0';
                $tc     = self::DIGNITY_TEXT[$dig] ?? '#555';
                $short  = self::DIGNITY_SHORT[$dig] ?? '—';
                // 3-char sign abbreviation
                $signAbbr = $pdata ? substr($sign, 0, 3) : '—';

                $html .= sprintf(
                    '<td style="padding:5px 4px;text-align:center;vertical-align:middle" title="%s in %s (%s)">'.
                    '<div style="display:inline-flex;flex-direction:column;align-items:center;background:%s;border-radius:6px;padding:4px 6px;min-width:36px">'.
                    '<span style="color:%s;font-weight:700;font-size:.7rem;line-height:1">%s%s</span>'.
                    '<span style="color:%s;font-size:.58rem;opacity:.8;margin-top:1px">%s</span>'.
                    '</div></td>',
                    ucfirst($pid), htmlspecialchars($pdata['vargaSign'] ?? '—'), $dig,
                    $bg,
                    $tc, $signAbbr, $retro,
                    $tc, $short
                );
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';
        $html .= '</div>';
        return $html;
    }

    // ════════════════════════════════════════════════════════════
    //  AstroChartRenderer compatibility
    // ════════════════════════════════════════════════════════════

    public static function buildPlanetVargaSummary(array $allVargas, string $pid): string
    {
        $VARGA_KEYS = ['D1','D2','D3','D9','D10','D12','D30','D60'];
        $rows = [];

        foreach ($VARGA_KEYS as $vk) {
            if (!isset($allVargas[$vk]['planets'][$pid])) continue;
            $p    = $allVargas[$vk]['planets'][$pid];
            $dig  = $p['dignity'];
            $bg   = self::DIGNITY_BG[$dig]  ?? '#f0f0f0';
            $tc   = self::DIGNITY_TEXT[$dig] ?? '#555';
            $d    = $allVargas[$vk]['division'];
            $rows[] = sprintf(
                '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:16px;background:%s;border:1px solid %s44;font-size:.72rem;font-weight:700;color:%s;white-space:nowrap">D%d <span style="opacity:.7">%s</span> %s</span>',
                $bg, $tc, $tc, $d, substr($p['vargaSign'], 0, 3), self::DIGNITY_SHORT[$dig]
            );
        }

        return '<div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:8px">'.implode('', $rows).'</div>';
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