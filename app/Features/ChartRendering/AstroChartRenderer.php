<?php

namespace App\Features\ChartRendering;

/**
 * AstroChartRenderer — North Indian Chart (BPHS) v12
 *
 * Fix summary (v12):
 *  - Removed the faint/light secondary house numbers that overlapped planets
 *  - Each cell has ONE visible house number, placed at a fixed "number anchor"
 *    that never overlaps the planet area
 *  - Planet block anchored to the "body centre" of each cell (well away from edges)
 *  - H4 (right kendra) planets placed left of cell centre so they don't bleed off-edge
 *  - Sign numbers (large gold) placed near outer corners/edges as before
 *  - "As" + lagna degrees kept in H1, pushed toward top edge
 */
class AstroChartRenderer
{
    public const PLANET_ABBR = [
        'sun'=>'Su','moon'=>'Mo','mercury'=>'Me','venus'=>'Ve',
        'mars'=>'Ma','jupiter'=>'Ju','saturn'=>'Sa','rahu'=>'Ra','ketu'=>'Ke',
    ];

    private const PLANET_COLOR = [
        'sun'=>'#c47000','moon'=>'#1a7ab5','mercury'=>'#0a8c5a','venus'=>'#9c2d8a',
        'mars'=>'#c0311f','jupiter'=>'#b36000','saturn'=>'#5a4a8a',
        'rahu'=>'#1a7a3a','ketu'=>'#a0440e',
    ];

    private const SIGN_SHORT = ['Ar','Ta','Ge','Cn','Le','Vi','Li','Sc','Sg','Cp','Aq','Pi'];
    private const SIGN_FULL  = [
        'Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya',
        'Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena',
    ];
    private const SIGN_LORD = ['Ma','Ve','Me','Mo','Su','Me','Ve','Ma','Ju','Sa','Sa','Ju'];

    // ═══════════════════════════════════════════════════════════════
    //  PRIMARY ENTRY POINT
    // ═══════════════════════════════════════════════════════════════
    public static function render(float $ascTrop, array $planets, float $ayan, array $dasha): string
    {
        $n360     = fn(float $x) => fmod(fmod($x,360)+360,360);
        $ascSider = $n360($ascTrop - $ayan);
        $ascSign  = (int)floor($ascSider / 30);

        $siderPos = [];
        foreach ($planets as $pid => $pd) {
            $siderPos[$pid] = ['lon' => $n360($pd['trop'] - $ayan), 'retro' => $pd['retro']];
        }

        $signToHouse = [];
        for ($h = 1; $h <= 12; $h++) {
            $signToHouse[($ascSign+$h-1)%12] = $h;
        }

        $housePlanets = array_fill(1, 12, []);
foreach ($siderPos as $pid => $pd) {
    $pSign = (int)floor($pd['lon'] / 30);
    $hNum  = (($pSign - $ascSign + 12) % 12) + 1;
    $housePlanets[$hNum][] = ['pid'=>$pid,'retro'=>$pd['retro'],'deg'=>fmod($pd['lon'],30)];
}

        $d1Svg  = self::buildChartSVG($ascSign, $ascSider, $siderPos, true,  560);
        $panel  = self::buildDetailPanel($ascSign, $housePlanets, $planets, $ayan, $signToHouse);
        $d9Svg  = self::buildChartSVG(self::navamshaSign($ascSign,fmod($ascSider,30)),-1,self::vargaPositions($siderPos,9),false,340);
        $d10Svg = self::buildChartSVG(self::dashamsha($ascSign,fmod($ascSider,30)),   -1,self::vargaPositions($siderPos,10),false,340);

        $lbl = fn($t) => '<div style="font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7a5010;margin-bottom:8px">'.$t.'</div>';
        $box = fn($s,$p='8px') => '<div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:'.$p.';box-shadow:0 2px 10px rgba(0,0,0,.09)">'.$s.'</div>';

        $html  = '<div style="font-family:Georgia,\'Times New Roman\',serif;background:#ede8dc;padding:20px;border-radius:8px">';
        $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:stretch">';
        $html .= '<div>'.$lbl('D1 &mdash; R&#x101;shi &middot; Birth Chart').$box($d1Svg).'</div>';
        $html .= '<div style="display:flex;flex-direction:column">'
               . $lbl('Houses &middot; Planets')
               . '<div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:14px 16px;flex:1">'
               . $panel.'</div></div>';
        $html .= '</div>';
        $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">';
        $html .= '<div>'.$lbl('D9 &mdash; Navamsha').$box($d9Svg,'6px').'</div>';
        $html .= '<div>'.$lbl('D10 &mdash; Dashamsha').$box($d10Svg,'6px').'</div>';
        $html .= '</div>';

        if (!empty($dasha)) {
            $html .= '<div style="margin-top:14px;background:#fdf5e0;border-radius:6px;padding:12px 20px;'
                   . 'border-left:4px solid #b8900a;color:#5a3e00;font-size:.84rem;line-height:1.85">'
                   . '<strong style="color:#7a4f00;letter-spacing:.06em">◈ VIMSHOTTARI DASHA BALANCE AT BIRTH</strong><br>'
                   . '<strong style="color:#3a2000;font-size:1.02rem">'.htmlspecialchars($dasha['lord']).' Mahadasha</strong>'
                   . ' &mdash; '.$dasha['yrs'].'y &nbsp;'.$dasha['mos'].'m &nbsp;'.$dasha['days'].'d remaining'
                   . ' <span style="opacity:.45;font-size:.76rem">(total '.$dasha['lordYrs'].' yrs)</span></div>';
        }

        return $html.'</div>';
    }

    // ═══════════════════════════════════════════════════════════════
    //  CORE SVG BUILDER  (v12 — clean, no collision)
    // ═══════════════════════════════════════════════════════════════
    public static function buildChartSVG(
        int $ascSign, float $ascSider, array $siderPos, bool $showAs, int $size=500
    ): string {
        $S   = (float)$size;
        $PAD = $S * 0.045;
        $m   = $S / 2.0;

        // ── Nine key points ──────────────────────────────────────────
        $TL = [$PAD,    $PAD   ];  $TR = [$S-$PAD, $PAD   ];
        $BR = [$S-$PAD, $S-$PAD];  $BL = [$PAD,    $S-$PAD];
        $MT = [$m,      $PAD   ];  $MR = [$S-$PAD, $m     ];
        $MB = [$m,      $S-$PAD];  $ML = [$PAD,    $m     ];
        $C  = [$m,      $m     ];

        // ── 12 triangles (house cells) ───────────────────────────────
        $cells = [
     1 => [$TL,$TR,$C ],
     2 => [$MT,$ML,$C ],
     3 => [$MT,$TL,$ML],
     4 => [$TL,$BL,$C ],
     5 => [$ML,$MB,$C ],
     6 => [$ML,$BL,$MB],
     7 => [$BR,$BL,$C ],
     8 => [$MB,$MR,$C ],
     9 => [$MB,$BR,$MR],
    10 => [$BR,$TR,$C ],
    11 => [$MR,$MT,$C ],
    12 => [$MR,$TR,$MT],
];

        // ── Planet → house ───────────────────────────────────────────
        $housePlanets = array_fill(1,12,[]);
        foreach ($siderPos as $pid => $pd) {
            $pSign = (int)floor($pd['lon']/30);
            $hNum  = (($pSign-$ascSign+12)%12)+1;
            $housePlanets[$hNum][] = ['pid'=>$pid,'retro'=>$pd['retro']];
        }

        // ── Lagna string ─────────────────────────────────────────────
        $lagnaStr='';
        if ($showAs && $ascSider>=0) {
            $di = $ascSider - floor($ascSider/30)*30;
            $lagnaStr = self::SIGN_SHORT[$ascSign].' '.(int)$di.'°'.sprintf('%02d',(int)(($di-(int)$di)*60))."'";
        }

        $sf = $S / 600.0;

        // ── Font sizes ───────────────────────────────────────────────
        $fSign   = round(20*$sf, 1);
        $fHouse  = round(14*$sf, 1);
        $fPlanet = round(19*$sf, 1);
        $fAs     = round(20*$sf, 1);
        $fLagna  = round(10.5*$sf, 1);
        $fRetro  = round(10*$sf, 1);

        $LC = '#9a8060';
        $LW = max(0.8, $sf*1.2);

        // ── Centroid helper ──────────────────────────────────────────
        $cen = fn($p) => [($p[0][0]+$p[1][0]+$p[2][0])/3, ($p[0][1]+$p[1][1]+$p[2][1])/3];

        // ────────────────────────────────────────────────────────────
        // PRE-COMPUTE per-cell layout anchors
        //
        // For each house we define three non-overlapping zones:
        //   [sign#]   — near outer boundary  (large gold number)
        //   [house#]  — near inner boundary / corner opposite outer  (medium brown)
        //   [planets] — body centre of the cell
        //
        // The key insight: sign# and house# go to OPPOSITE ends of the cell.
        // Planets fill the middle body zone.
        // ────────────────────────────────────────────────────────────

        // Helper: push point P away from reference point REF by fraction t
        // result = REF + t*(P-REF)   — fraction 0=REF, 1=P
        $push = fn(array $from, array $to, float $t) => [
            $from[0] + ($to[0]-$from[0])*$t,
            $from[1] + ($to[1]-$from[1])*$t,
        ];

        // ── SIGN number anchors (near outer edge) ────────────────────
        // H1(top kendra)→near MT,  H4(right)→near MR,  H7(bottom)→near MB,  H10(left)→near ML
        // H3(corner TR), H6(corner BR), H9(corner BL), H12(corner TL)
        // H2(inner UR),  H5(inner LR),  H8(inner LL),  H11(inner UL)
        $signAnchors = [];
        foreach ($cells as $h => $poly) {
            [$cx,$cy] = $cen($poly);
            if ($h===1)  { $signAnchors[$h] = $push($C, $MT, 0.70); }
            elseif ($h===4)  { $signAnchors[$h] = $push($C, $ML, 0.70); }
            elseif ($h===7)  { $signAnchors[$h] = $push($C, $MB, 0.70); }
            elseif ($h===10) { $signAnchors[$h] = $push($C, $MR, 0.70); }
            elseif ($h===3)  {  $signAnchors[$h] = [
        ($MR[0] + $TR[0] + $MT[0]) / 3 - $S * 0.70,
        ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * 0.02,  
    ]; }
            elseif ($h===6)  { $signAnchors[$h] = [
        ($MR[0] + $TR[0] + $MT[0]) / 3 - $S * 0.53,
        ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * 0.62,  
    ]; }
            elseif ($h===9) {
    $signAnchors[$h] = [
        ($MB[0] + $BR[0] + $MR[0]) / 3 - $S * -0.1,
        ($MB[1] + $BR[1] + $MR[1]) / 3 - $S * 0.13,
    ];
}
            elseif ($h===12) { 
    $signAnchors[$h] = [
        ($MR[0] + $TR[0] + $MT[0]) / 3 + $S * -0.07,
        ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * -0.01,
    ];
}
            elseif ($h===2)  {
                $signAnchors[$h] = [
        ($MR[0] + $TR[0] + $MT[0]) / 3 + $S * -0.53,
        ($MR[1] + $TR[1] + $MT[1]) / 3 - $S * 0.01, 
    ];
            }
        elseif ($h===5)  {
            $signAnchors[$h] = [
                ($ML[0] + $MB[0] + $C[0]) / 3 + $S * -0.24,
                ($ML[1] + $MB[1] + $C[1]) / 3 - $S * 0.00,
            ];
        }
            elseif ($h===8)  {
               $signAnchors[$h] = [
                ($ML[0] + $MB[0] + $C[0]) / 3 + $S * 0.38,
                ($ML[1] + $MB[1] + $C[1]) / 3 - $S * -0.17,
            ];
            }
            elseif ($h===11) {
    $signAnchors[$h] = [
        ($ML[0] + $MB[0] + $C[0]) / 3 + $S * 0.55,
                ($ML[1] + $MB[1] + $C[1]) / 3 - $S * 0.45,
            ];
    
}
        }

       

        // ── PLANET body anchors ──────────────────────────────────────
        // The planet sits between house# and sign#.
        // For kendra: body = 50% from C toward outer midpoint
        // For inner:  body = centroid + small push away from C
        // For corner: body = 35% toward corner from centroid
        // Special: H1 with "As" label — planets pushed slightly down from As
        $planetAnchors = [];
        foreach ($cells as $h => $poly) {
            [$cx,$cy] = $cen($poly);
            if (in_array($h,[1,4,7,10])) {
                $outerPt = match($h) {
                    1  => $MT,
                    4  => $ML,
                    7  => $MB,
                    10 => $MR,
                };
                $planetAnchors[$h] = $push($C, $outerPt, 0.48);
            } elseif (in_array($h,[2,5,8,11])) {
                // Push away from C along centroid direction
                $dx = $cx-$C[0]; $dy = $cy-$C[1];
                $len = max(0.01, sqrt($dx*$dx+$dy*$dy));
                $planetAnchors[$h] = [
                    $cx + ($dx/$len)*$S*0.045,
                    $cy + ($dy/$len)*$S*0.045,
                ];
            } else {
                // Corner cells: between centroid and corner vertex
                $cornerPt = match($h) {
                    3  => $TL,
                    6  => $BL,
                    9  => $BR,
                    12 => $TR,
                };
                $planetAnchors[$h] = $push([$cx,$cy], $cornerPt, 0.30);
            }
            // ADD ALL THESE after the $planetAnchors foreach loop ends

// H11 (inner upper-left) 
$planetAnchors[11] = [
    ($ML[0] + $MB[0] + $C[0]) / 3 - $S * -0.50,
    ($ML[1] + $MB[1] + $C[1]) / 3 + $S * -0.38,
];

// H5 (inner lower-right) 
$planetAnchors[5] = [
    ($ML[0] + $MB[0] + $C[0]) / 3 + $S * -0.21,
    ($ML[1] + $MB[1] + $C[1]) / 3 + $S * 0.07,
];

// H9 (bottom-left corner) 
$planetAnchors[9] = [
    ($MB[0] + $BR[0] + $MR[0]) / 3 - $S * -0.05,
    ($MB[1] + $BR[1] + $MR[1]) / 3 - $S * 0.07,
];

// H6 (top-left corner) — move same direction as sign 12
$planetAnchors[6] = [
    ($MR[0] + $TR[0] + $MT[0]) / 3 - $S * 0.53,
    ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * 0.69,
];

// H3 (top-right corner) — move same direction as sign 9
$planetAnchors[3] = [
    ($ML[0] + $TL[0] + $MT[0]) / 3 - $S * 0.05,
    ($ML[1] + $TL[1] + $MT[1]) / 3 + $S * 0.09,
];

// H2 (inner upper-right) — move same direction as sign 2
$planetAnchors[2] = [
    ($ML[0] + $TL[0] + $MT[0]) / 3 + $S * 0.10,
    ($ML[1] + $TL[1] + $MT[1]) / 3 - $S * 0.09,
];

// H12 (bottom-left corner) — move same direction as sign 3
$planetAnchors[12] = [
    ($MR[0] + $TR[0] + $MT[0]) / 3 + $S * -0.02,
    ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * -0.09,
];

// H8 (inner upper-left) — move same direction as sign 8
$planetAnchors[8] = [
    ($ML[0] + $MB[0] + $C[0]) / 3 - $S * -0.38,
    ($ML[1] + $MB[1] + $C[1]) / 3 + $S * 0.22,
];
        }

        // ────────────────────────────────────────────────────────────
        // SVG start
        // ────────────────────────────────────────────────────────────
        $svg  = '<svg viewBox="0 0 '.$S.' '.$S.'" xmlns="http://www.w3.org/2000/svg"'
              . ' style="width:100%;display:block;border-radius:4px">';
        $svg .= '<rect width="'.$S.'" height="'.$S.'" fill="#fdf7e8"/>';

        // ── Cell fills ───────────────────────────────────────────────
        foreach ($cells as $h => $poly) {
            $fill = in_array($h,[1,4,7,10]) ? '#fdf5e0'
                  : (in_array($h,[2,5,8,11]) ? '#f8efd4' : '#f2e8c8');
            $svg .= '<polygon points="'.self::pts($poly).'" fill="'.$fill.'" stroke="none"/>';
        }

        // ── Chart lines ──────────────────────────────────────────────
        $svg .= '<polygon points="'.self::pts([$TL,$TR,$BR,$BL]).'" fill="none" stroke="'.$LC
              . '" stroke-width="'.round($LW*1.5,2).'" stroke-linejoin="miter"/>';
        $svg .= '<polygon points="'.self::pts([$MT,$MR,$MB,$ML]).'" fill="none" stroke="'.$LC
              . '" stroke-width="'.round($LW,2).'" stroke-linejoin="miter"/>';
        $svg .= '<line x1="'.$TL[0].'" y1="'.$TL[1].'" x2="'.$BR[0].'" y2="'.$BR[1]
              . '" stroke="'.$LC.'" stroke-width="'.round($LW*.85,2).'"/>';
        $svg .= '<line x1="'.$TR[0].'" y1="'.$TR[1].'" x2="'.$BL[0].'" y2="'.$BL[1]
              . '" stroke="'.$LC.'" stroke-width="'.round($LW*.85,2).'"/>';

        // ════════════════════════════════════════════════════════════
        //  SIGN NUMBERS  (large gold, near outer boundary)
        // ════════════════════════════════════════════════════════════
        foreach ($cells as $h => $poly) {
            $rashiIdx = ($ascSign+$h-1)%12;
            $signNum  = (string)($rashiIdx+1);  
            [$sx,$sy] = $signAnchors[$h];
            $svg .= self::txt(round($sx,1), round($sy,1), $signNum, $fSign, '#7a5a10', 700);
            
        }


        // ════════════════════════════════════════════════════════════
        //  "As" LABEL + LAGNA DEGREES  (H1 only)
        //  Place "As" between house# cluster and sign# (at ~55% toward MT)
        //  Then lagna degrees just below "As"
        // ════════════════════════════════════════════════════════════
        if ($showAs) {
    [$ax,$ay] = $push($C, $MT, 0.55);
    $svg .= self::txt(round($ax,1), round($ay,1), 'As', $fAs, '#8b4010', 700);
    if ($lagnaStr !== '') {
        $ly = $ay + $fAs * 1.10;
        $svg .= self::txt(round($ax,1), round($ly,1), htmlspecialchars($lagnaStr), $fLagna, '#7a5030', 400);
    }
    $planetAnchors[1] = $push($C, $MT, 0.36);
} else {
    // D9/D10 — show small "As" marker without degrees
    [$ax,$ay] = $push($C, $MT, 0.55);
    $svg .= self::txt(round($ax,1), round($ay,1), 'As', $fAs, '#8b4010', 700);
    $planetAnchors[1] = $push($C, $MT, 0.36);
}

        // ════════════════════════════════════════════════════════════
        //  PLANET BLOCKS
        // ════════════════════════════════════════════════════════════
        $pSpc = $fPlanet * 1.65;

        foreach ($cells as $h => $poly) {
            $planetsH = $housePlanets[$h];
            if (empty($planetsH)) continue;

            [$bx,$by] = $planetAnchors[$h];

            $rows   = array_chunk($planetsH, 3);
            $nRows  = count($rows);
            $rowH   = $fPlanet * 1.42;
            $startY = $by - ($nRows-1)*$rowH/2;

            foreach ($rows as $ri => $row) {
                $nc     = count($row);
                $rowW   = ($nc-1)*$pSpc;
                $startX = $bx - $rowW/2;
                $ry     = $startY + $ri*$rowH;

                foreach ($row as $pi => $p) {
                    $px  = $startX + $pi*$pSpc;
                    $col = self::PLANET_COLOR[$p['pid']] ?? '#444';
                    $abb = self::PLANET_ABBR[$p['pid']]  ?? strtoupper(substr($p['pid'],0,2));
                    $svg .= self::txt(round($px,1), round($ry,1), htmlspecialchars($abb), $fPlanet, $col, 700);
                    if ($p['retro']) {
                        $svg .= self::txt(
                            round($px + $fPlanet*0.58, 1),
                            round($ry  - $fPlanet*0.50, 1),
                            'R', $fRetro, $col, 400, true
                        );
                    }
                }
            }
        }

        return $svg.'</svg>';
    }

    // ── SVG text helper ──────────────────────────────────────────────
    private static function txt(
        float $x, float $y, string $t, float $fs,
        string $fill, int $fw=400, bool $italic=false
    ): string {
        $style = $italic ? ' font-style="italic"' : '';
        return '<text x="'.$x.'" y="'.$y.'"'
             . ' text-anchor="middle" dominant-baseline="middle"'
             . ' font-family="Georgia,serif"'
             . ' font-size="'.$fs.'" font-weight="'.$fw.'"'
             . $style.' fill="'.$fill.'">'.$t.'</text>';
    }

    // ═══════════════════════════════════════════════════════════════
    //  DETAIL PANEL
    // ═══════════════════════════════════════════════════════════════
    private static function buildDetailPanel(
        int $ascSignIdx, array $housePlanetsArr,
        array $planets, float $ayan, array $signToHouse
    ): string {
        $n360 = fn($x) => fmod(fmod($x,360)+360,360);
        $sec  = fn($t) => '<div style="font-size:11px;font-weight:700;letter-spacing:.15em;'
                        . 'text-transform:uppercase;color:#7a5010;border-bottom:1px solid #d0b880;'
                        . 'padding-bottom:5px;margin-bottom:8px">'.$t.'</div>';

        $html = $sec('Houses &middot; Signs');

        for ($h = 1; $h <= 12; $h++) {
    $ri       = ($ascSignIdx+$h-1)%12;
    $planetsH = $housePlanetsArr[$h] ?? [];
    $displayH = $ri + 1;
            $isK      = in_array($h,[1,4,7,10]);
            $bg       = $isK ? 'rgba(180,150,60,.09)' : 'transparent';

            $html .= '<div style="display:flex;align-items:center;gap:6px;padding:4px 2px;'
                   . 'border-bottom:1px solid rgba(180,150,80,.14);background:'.$bg.'">';
            $html .= '<span style="font-size:11px;font-weight:700;color:#7a5010;'
                   . 'background:rgba(180,140,40,.18);border-radius:3px;padding:1px 7px;'
                   . 'min-width:24px;text-align:center;flex-shrink:0">'.$displayH.'</span>';
            $html .= '<span style="font-size:12.5px;font-weight:600;color:#3a2800;flex:1;min-width:0">'
                   . self::SIGN_FULL[$ri]
                   . '<span style="opacity:.45;font-weight:400;font-size:10.5px"> &middot; '
                   . self::SIGN_LORD[$ri].'</span></span>';

            foreach ($planetsH as $p) {
                $col  = self::PLANET_COLOR[$p['pid']] ?? '#444';
                $abbr = self::PLANET_ABBR[$p['pid']]  ?? '??';
                $rx   = $p['retro'] ? '<sup style="font-size:7px;font-style:italic">R</sup>' : '';
                $html .= '<span style="font-size:12.5px;font-weight:700;color:'.$col
                       . ';flex-shrink:0;margin-left:2px">'.$abbr.$rx.'</span>';
            }
            $html .= '</div>';
        }

        $html .= '<div style="margin-top:16px">'.$sec('Planet Positions');
        $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:5px 10px">';

        foreach ($planets as $pid => $pdata) {
            $sider = $n360($pdata['trop']-$ayan);
            $vIdx  = (int)floor($sider/30);
            $col   = self::PLANET_COLOR[$pid] ?? '#444';
            $abbr  = self::PLANET_ABBR[$pid]  ?? '??';
            $rx    = $pdata['retro'] ? '<sup style="font-size:7px;font-style:italic">R</sup>' : '';
            $house = $vIdx + 1;
            $di    = fmod($sider,30);
            $d=(int)$di; $mm=(int)(($di-$d)*60);

            $html .= '<div style="display:flex;align-items:center;gap:5px;padding:2px 0;'
                   . 'border-bottom:1px solid rgba(180,150,80,.12)">'
                   . '<span style="color:'.$col.';font-size:13px;font-weight:700;'
                   . 'min-width:26px;flex-shrink:0">'.$abbr.$rx.'</span>'
                   . '<span style="color:#5a3800;font-size:12px;font-weight:600">'
                   . self::SIGN_SHORT[$vIdx].' H'.$house.'</span>'
                   . '<span style="color:#9a7840;font-size:10.5px;margin-left:auto">'
                   . $d.'°'.sprintf('%02d',$mm)."'".'</span>'
                   . '</div>';
        }

        return $html.'</div></div>';
    }

    // ═══════════════════════════════════════════════════════════════
    //  DIVISIONAL CHART ALGORITHMS
    // ═══════════════════════════════════════════════════════════════
    public static function navamshaSign(int $si, float $deg): int
    {
        static $ns=[0,9,6,3,0,9,6,3,0,9,6,3];
        return ($ns[$si]+(int)floor($deg/(30/9)))%12;
    }

    public static function dashamsha(int $si, float $deg): int
    {
        $p=(int)floor($deg/3);
        return (($si%2===0?$si:($si+8)%12)+$p)%12;
    }

    private static function vargaPositions(array $sp, int $d): array
    {
        $out=[];
        foreach ($sp as $pid=>$pd) {
            $si=(int)floor($pd['lon']/30); $dig=fmod($pd['lon'],30);
            $vs=match($d){
                9  => self::navamshaSign($si,$dig),
                10 => self::dashamsha($si,$dig),
                default => $si
            };
            $out[$pid]=['lon'=>$vs*30+($dig/(30/$d)),'retro'=>$pd['retro']];
        }
        return $out;
    }

    // ═══════════════════════════════════════════════════════════════
    //  LEGACY PUBLIC METHODS (unchanged)
    // ═══════════════════════════════════════════════════════════════
    public static function buildChart(int $ascSign, float $ascSider, array $siderPos, string $title, bool $showAs): string
    {
        return '<div style="background:#fff;border-radius:10px;padding:8px;border:1px solid #c8b896">'
             . self::buildChartSVG($ascSign,$ascSider,$siderPos,$showAs,400).'</div>';
    }

    public static function buildHouseSignsList(int $ascSignIdx, array $housePlanetsArr): string
    {
        $html='<div style="font-size:.8rem;font-weight:700;color:#6b4c1a;margin-bottom:8px">Houses &middot; Signs &middot; Planets</div>';
        for ($h=1;$h<=12;$h++) {
            $ri=($ascSignIdx+$h-1)%12; $ps=$housePlanetsArr[$h-1]??[];
            $html.='<div style="font-size:.75rem;padding:3px 0;border-bottom:1px solid rgba(160,130,80,.15)">'
                  .'<strong style="color:#3a2a00">'.$h.'</strong> <span style="color:#5a3a00">'.self::SIGN_FULL[$ri].'</span>';
            foreach ($ps as $p) {
                $html.=' <span style="color:'.(self::PLANET_COLOR[$p['pid']]??'#444').';font-weight:700">'.(self::PLANET_ABBR[$p['pid']]??'??').'</span>';
            }
            $html.='</div>';
        }
        return $html;
    }

    public static function buildPlanetSummary(array $planets, float $ayan, array $signToHouse): string
    {
        $n360=fn($x)=>fmod(fmod($x,360)+360,360);
        $html='<div style="font-size:.8rem;font-weight:700;color:#6b4c1a;margin:8px 0 6px">Planet Positions</div>';
        foreach ($planets as $pid=>$pd) {
            $s=$n360($pd['trop']-$ayan); $vi=(int)floor($s/30);
            $di=fmod($s,30); $d=(int)$di; $mm=(int)(($di-$d)*60);
            $html.='<div style="font-size:.75rem;margin-bottom:3px">'
                  .'<span style="color:'.(self::PLANET_COLOR[$pid]??'#444').';font-weight:700">'
                  .(self::PLANET_ABBR[$pid]??'??').($pd['retro']?'R':'').'</span> '
                  .'<span style="color:#4a3520">'.self::SIGN_SHORT[$vi].' H'.($signToHouse[$vi]??'?').'</span> '
                  .'<span style="color:#8a7050">'.$d.'°'.sprintf('%02d',$mm)."'".'</span></div>';
        }
        return $html;
    }

    private static function pts(array $poly): string
    {
        return implode(' ', array_map(fn($p) => round($p[0],1).','.round($p[1],1), $poly));
    }
}