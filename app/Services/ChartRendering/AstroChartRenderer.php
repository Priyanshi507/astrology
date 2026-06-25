<?php

namespace App\Services\ChartRendering;

use Illuminate\Support\Facades\DB;

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
    private static ?array $planetAbbr  = null;
    private static ?array $planetColor = null;
    private static ?array $signShort   = null;
    private static ?array $signFull    = null;
    private static ?array $signLord    = null;

    private static function loadChartData(): void
    {
        if (self::$planetAbbr !== null) return;
        self::$planetAbbr  = [];
        self::$planetColor = [];
        foreach (DB::table('planets')->get(['name', 'abbreviation', 'color_hex']) as $p) {
            $key = strtolower($p->name);
            self::$planetAbbr[$key]  = $p->abbreviation;
            self::$planetColor[$key] = $p->color_hex ?? '#444';
        }
        self::$signShort = [];
        self::$signFull  = [];
        self::$signLord  = [];
        $i = 0;
        foreach (
            DB::table('zodiac_signs as z')
                ->join('planets as p', 'z.lord_planet_id', '=', 'p.id')
                ->orderBy('z.id')
                ->get(['z.name', 'z.abbreviation', 'p.abbreviation as lord_abbr'])
            as $s
        ) {
            self::$signFull[$i]  = $s->name;
            self::$signShort[$i] = $s->abbreviation ?? '';
            self::$signLord[$i]  = $s->lord_abbr;
            $i++;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  CORE SVG BUILDER  (v12 — clean, no collision)
    // ═══════════════════════════════════════════════════════════════
    public static function buildChartSVG(
        int $ascSign,
        float $ascSider,
        array $siderPos,
        bool $showAs,
        int $size = 500
    ): string {
        self::loadChartData();
        $S   = (float)$size;
        $PAD = $S * 0.045;
        $m   = $S / 2.0;

        // ── Nine key points ──────────────────────────────────────────
        $TL = [$PAD,    $PAD];
        $TR = [$S - $PAD, $PAD];
        $BR = [$S - $PAD, $S - $PAD];
        $BL = [$PAD,    $S - $PAD];
        $MT = [$m,      $PAD];
        $MR = [$S - $PAD, $m];
        $MB = [$m,      $S - $PAD];
        $ML = [$PAD,    $m];
        $C  = [$m,      $m];

        // ── 12 triangles (house cells) ───────────────────────────────
        $cells = [
            1 => [$TL, $TR, $C],
            2 => [$MT, $ML, $C],
            3 => [$MT, $TL, $ML],
            4 => [$TL, $BL, $C],
            5 => [$ML, $MB, $C],
            6 => [$ML, $BL, $MB],
            7 => [$BR, $BL, $C],
            8 => [$MB, $MR, $C],
            9 => [$MB, $BR, $MR],
            10 => [$BR, $TR, $C],
            11 => [$MR, $MT, $C],
            12 => [$MR, $TR, $MT],
        ];

        // ── Planet → house ───────────────────────────────────────────
        $housePlanets = array_fill(1, 12, []);
        foreach ($siderPos as $pid => $pd) {
            $pSign = (int)floor($pd['lon'] / 30);
            $hNum  = (($pSign - $ascSign + 12) % 12) + 1;
            $housePlanets[$hNum][] = ['pid' => $pid, 'retro' => $pd['retro']];
        }

        // ── Lagna string ─────────────────────────────────────────────
        $lagnaStr = '';
        if ($showAs && $ascSider >= 0) {
            $di = $ascSider - floor($ascSider / 30) * 30;
            $lagnaStr = self::$signShort[$ascSign] . ' ' . (int)$di . '°' . sprintf('%02d', (int)(($di - (int)$di) * 60)) . "'";
        }

        $sf = $S / 600.0;

        // ── Font sizes ───────────────────────────────────────────────
        $fSign   = round(20 * $sf, 1);
        $fHouse  = round(14 * $sf, 1);
        $fPlanet = round(19 * $sf, 1);
        $fAs     = round(20 * $sf, 1);
        $fLagna  = round(10.5 * $sf, 1);
        $fRetro  = round(10 * $sf, 1);

        $LC = '#9a8060';
        $LW = max(0.8, $sf * 1.2);

        // ── Centroid helper ──────────────────────────────────────────
        $cen = fn($p) => [($p[0][0] + $p[1][0] + $p[2][0]) / 3, ($p[0][1] + $p[1][1] + $p[2][1]) / 3];

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
            $from[0] + ($to[0] - $from[0]) * $t,
            $from[1] + ($to[1] - $from[1]) * $t,
        ];

        // ── SIGN number anchors (near outer edge) ────────────────────
        // H1(top kendra)→near MT,  H4(right)→near MR,  H7(bottom)→near MB,  H10(left)→near ML
        // H3(corner TR), H6(corner BR), H9(corner BL), H12(corner TL)
        // H2(inner UR),  H5(inner LR),  H8(inner LL),  H11(inner UL)
        $signAnchors = [];
        foreach ($cells as $h => $poly) {
            [$cx, $cy] = $cen($poly);
            if ($h === 1) {
                $signAnchors[$h] = $push($C, $MT, 0.70);
            } elseif ($h === 4) {
                $signAnchors[$h] = $push($C, $ML, 0.70);
            } elseif ($h === 7) {
                $signAnchors[$h] = $push($C, $MB, 0.70);
            } elseif ($h === 10) {
                $signAnchors[$h] = $push($C, $MR, 0.70);
            } elseif ($h === 3) {
                $signAnchors[$h] = [
                    ($MR[0] + $TR[0] + $MT[0]) / 3 - $S * 0.70,
                    ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * 0.02,
                ];
            } elseif ($h === 6) {
                $signAnchors[$h] = [
                    ($MR[0] + $TR[0] + $MT[0]) / 3 - $S * 0.53,
                    ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * 0.62,
                ];
            } elseif ($h === 9) {
                $signAnchors[$h] = [
                    ($MB[0] + $BR[0] + $MR[0]) / 3 - $S * -0.1,
                    ($MB[1] + $BR[1] + $MR[1]) / 3 - $S * 0.13,
                ];
            } elseif ($h === 12) {
                $signAnchors[$h] = [
                    ($MR[0] + $TR[0] + $MT[0]) / 3 + $S * -0.07,
                    ($MR[1] + $TR[1] + $MT[1]) / 3 + $S * -0.01,
                ];
            } elseif ($h === 2) {
                $signAnchors[$h] = [
                    ($MR[0] + $TR[0] + $MT[0]) / 3 + $S * -0.53,
                    ($MR[1] + $TR[1] + $MT[1]) / 3 - $S * 0.01,
                ];
            } elseif ($h === 5) {
                $signAnchors[$h] = [
                    ($ML[0] + $MB[0] + $C[0]) / 3 + $S * -0.24,
                    ($ML[1] + $MB[1] + $C[1]) / 3 - $S * 0.00,
                ];
            } elseif ($h === 8) {
                $signAnchors[$h] = [
                    ($ML[0] + $MB[0] + $C[0]) / 3 + $S * 0.38,
                    ($ML[1] + $MB[1] + $C[1]) / 3 - $S * -0.17,
                ];
            } elseif ($h === 11) {
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
            [$cx, $cy] = $cen($poly);
            if (in_array($h, [1, 4, 7, 10])) {
                $outerPt = match ($h) {
                    1  => $MT,
                    4  => $ML,
                    7  => $MB,
                    10 => $MR,
                };
                $planetAnchors[$h] = $push($C, $outerPt, 0.48);
            } elseif (in_array($h, [2, 5, 8, 11])) {
                // Push away from C along centroid direction
                $dx = $cx - $C[0];
                $dy = $cy - $C[1];
                $len = max(0.01, sqrt($dx * $dx + $dy * $dy));
                $planetAnchors[$h] = [
                    $cx + ($dx / $len) * $S * 0.045,
                    $cy + ($dy / $len) * $S * 0.045,
                ];
            } else {
                // Corner cells: between centroid and corner vertex
                $cornerPt = match ($h) {
                    3  => $TL,
                    6  => $BL,
                    9  => $BR,
                    12 => $TR,
                };
                $planetAnchors[$h] = $push([$cx, $cy], $cornerPt, 0.30);
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
        $svg  = '<svg viewBox="0 0 ' . $S . ' ' . $S . '" xmlns="http://www.w3.org/2000/svg"'
            . ' style="width:100%;display:block;border-radius:4px">';
        $svg .= '<rect width="' . $S . '" height="' . $S . '" fill="#fdf7e8"/>';

        // ── Cell fills ───────────────────────────────────────────────
        foreach ($cells as $h => $poly) {
            $fill = in_array($h, [1, 4, 7, 10]) ? '#fdf5e0'
                : (in_array($h, [2, 5, 8, 11]) ? '#f8efd4' : '#f2e8c8');
            $svg .= '<polygon points="' . self::pts($poly) . '" fill="' . $fill . '" stroke="none"/>';
        }

        // ── Chart lines ──────────────────────────────────────────────
        $svg .= '<polygon points="' . self::pts([$TL, $TR, $BR, $BL]) . '" fill="none" stroke="' . $LC
            . '" stroke-width="' . round($LW * 1.5, 2) . '" stroke-linejoin="miter"/>';
        $svg .= '<polygon points="' . self::pts([$MT, $MR, $MB, $ML]) . '" fill="none" stroke="' . $LC
            . '" stroke-width="' . round($LW, 2) . '" stroke-linejoin="miter"/>';
        $svg .= '<line x1="' . $TL[0] . '" y1="' . $TL[1] . '" x2="' . $BR[0] . '" y2="' . $BR[1]
            . '" stroke="' . $LC . '" stroke-width="' . round($LW * .85, 2) . '"/>';
        $svg .= '<line x1="' . $TR[0] . '" y1="' . $TR[1] . '" x2="' . $BL[0] . '" y2="' . $BL[1]
            . '" stroke="' . $LC . '" stroke-width="' . round($LW * .85, 2) . '"/>';

        // ════════════════════════════════════════════════════════════
        //  SIGN NUMBERS  (large gold, near outer boundary)
        // ════════════════════════════════════════════════════════════
        foreach ($cells as $h => $poly) {
            $rashiIdx = ($ascSign + $h - 1) % 12;
            $signNum  = (string)($rashiIdx + 1);
            [$sx, $sy] = $signAnchors[$h];
            $svg .= self::txt(round($sx, 1), round($sy, 1), $signNum, $fSign, '#7a5a10', 700);
        }


        // ════════════════════════════════════════════════════════════
        //  "As" LABEL + LAGNA DEGREES  (H1 only)
        //  Place "As" between house# cluster and sign# (at ~55% toward MT)
        //  Then lagna degrees just below "As"
        // ════════════════════════════════════════════════════════════
        if ($showAs) {
            [$ax, $ay] = $push($C, $MT, 0.55);
            $svg .= self::txt(round($ax, 1), round($ay, 1), 'As', $fAs, '#8b4010', 700);
            if ($lagnaStr !== '') {
                $ly = $ay + $fAs * 1.10;
                $svg .= self::txt(round($ax, 1), round($ly, 1), htmlspecialchars($lagnaStr), $fLagna, '#7a5030', 400);
            }
            $planetAnchors[1] = $push($C, $MT, 0.36);
        } else {
            // D9/D10 — show small "As" marker without degrees
            [$ax, $ay] = $push($C, $MT, 0.55);
            $svg .= self::txt(round($ax, 1), round($ay, 1), 'As', $fAs, '#8b4010', 700);
            $planetAnchors[1] = $push($C, $MT, 0.36);
        }

        // ════════════════════════════════════════════════════════════
        //  PLANET BLOCKS
        // ════════════════════════════════════════════════════════════
        $pSpc = $fPlanet * 1.65;

        foreach ($cells as $h => $poly) {
            $planetsH = $housePlanets[$h];
            if (empty($planetsH)) continue;

            [$bx, $by] = $planetAnchors[$h];

            $rows   = array_chunk($planetsH, 3);
            $nRows  = count($rows);
            $rowH   = $fPlanet * 1.42;
            $startY = $by - ($nRows - 1) * $rowH / 2;

            foreach ($rows as $ri => $row) {
                $nc     = count($row);
                $rowW   = ($nc - 1) * $pSpc;
                $startX = $bx - $rowW / 2;
                $ry     = $startY + $ri * $rowH;

                foreach ($row as $pi => $p) {
                    $px  = $startX + $pi * $pSpc;
                    $col = self::$planetColor[$p['pid']] ?? '#444';
                    $abb = self::$planetAbbr[$p['pid']]  ?? strtoupper(substr($p['pid'], 0, 2));
                    $svg .= self::txt(round($px, 1), round($ry, 1), htmlspecialchars($abb), $fPlanet, $col, 700);
                    if ($p['retro']) {
                        $svg .= self::txt(
                            round($px + $fPlanet * 0.58, 1),
                            round($ry  - $fPlanet * 0.50, 1),
                            'R',
                            $fRetro,
                            $col,
                            400,
                            true
                        );
                    }
                }
            }
        }

        return $svg . '</svg>';
    }

    // ── SVG text helper ──────────────────────────────────────────────
    private static function txt(
        float $x,
        float $y,
        string $t,
        float $fs,
        string $fill,
        int $fw = 400,
        bool $italic = false
    ): string {
        $style = $italic ? ' font-style="italic"' : '';
        return '<text x="' . $x . '" y="' . $y . '"'
            . ' text-anchor="middle" dominant-baseline="middle"'
            . ' font-family="Georgia,serif"'
            . ' font-size="' . $fs . '" font-weight="' . $fw . '"'
            . $style . ' fill="' . $fill . '">' . $t . '</text>';
    }

    // ═══════════════════════════════════════════════════════════════
    //  DIVISIONAL CHART ALGORITHMS
    // ═══════════════════════════════════════════════════════════════
    public static function navamshaSign(int $si, float $deg): int
    {
        static $ns = [0, 9, 6, 3, 0, 9, 6, 3, 0, 9, 6, 3];
        return ($ns[$si] + (int)floor($deg / (30 / 9))) % 12;
    }

    public static function dashamsha(int $si, float $deg): int
    {
        $p = (int)floor($deg / 3);
        return (($si % 2 === 0 ? $si : ($si + 8) % 12) + $p) % 12;
    }

    private static function vargaPositions(array $sp, int $d): array
    {
        $out = [];
        foreach ($sp as $pid => $pd) {
            $si = (int)floor($pd['lon'] / 30);
            $dig = fmod($pd['lon'], 30);
            $vs = match ($d) {
                9  => self::navamshaSign($si, $dig),
                10 => self::dashamsha($si, $dig),
                default => $si
            };
            $out[$pid] = ['lon' => $vs * 30 + ($dig / (30 / $d)), 'retro' => $pd['retro']];
        }
        return $out;
    }

    // ═══════════════════════════════════════════════════════════════
    //  VIEW DATA METHODS — return structured arrays for Blade partials
    // ═══════════════════════════════════════════════════════════════

    public static function prepareForView(float $ascTrop, array $planets, float $ayan, array $dasha): array
    {
        $n360     = fn(float $x) => fmod(fmod($x, 360) + 360, 360);
        $ascSider = $n360($ascTrop - $ayan);
        $ascSign  = (int)floor($ascSider / 30);

        $siderPos = [];
        foreach ($planets as $pid => $pd) {
            $siderPos[$pid] = ['lon' => $n360($pd['trop'] - $ayan), 'retro' => $pd['retro']];
        }

        $signToHouse = [];
        for ($h = 1; $h <= 12; $h++) {
            $signToHouse[($ascSign + $h - 1) % 12] = $h;
        }

        $housePlanets = array_fill(1, 12, []);
        foreach ($siderPos as $pid => $pd) {
            $pSign = (int)floor($pd['lon'] / 30);
            $hNum  = (($pSign - $ascSign + 12) % 12) + 1;
            $housePlanets[$hNum][] = ['pid' => $pid, 'retro' => $pd['retro'], 'deg' => fmod($pd['lon'], 30)];
        }

        return [
            'd1Svg'  => self::buildChartSVG($ascSign, $ascSider, $siderPos, true, 560),
            'd9Svg'  => self::buildChartSVG(self::navamshaSign($ascSign, fmod($ascSider, 30)), -1, self::vargaPositions($siderPos, 9),  false, 340),
            'd10Svg' => self::buildChartSVG(self::dashamsha($ascSign, fmod($ascSider, 30)),    -1, self::vargaPositions($siderPos, 10), false, 340),
            'panel'  => self::buildDetailPanelData($ascSign, $housePlanets, $planets, $ayan, $signToHouse),
            'dasha'  => !empty($dasha) ? $dasha : null,
        ];
    }

    private static function buildDetailPanelData(
        int $ascSignIdx,
        array $housePlanetsArr,
        array $planets,
        float $ayan,
        array $signToHouse
    ): array {
        self::loadChartData();
        $n360 = fn($x) => fmod(fmod($x, 360) + 360, 360);

        $houses = [];
        for ($h = 1; $h <= 12; $h++) {
            $ri       = ($ascSignIdx + $h - 1) % 12;
            $planetsH = $housePlanetsArr[$h] ?? [];
            $items    = [];
            foreach ($planetsH as $p) {
                $items[] = [
                    'abbr'  => self::$planetAbbr[$p['pid']]  ?? '??',
                    'color' => self::$planetColor[$p['pid']] ?? '#444',
                    'retro' => $p['retro'],
                ];
            }
            $houses[] = [
                'h'        => $h,
                'signFull' => self::$signFull[$ri],
                'signLord' => self::$signLord[$ri],
                'isKendra' => in_array($h, [1, 4, 7, 10]),
                'planets'  => $items,
            ];
        }

        $planetPositions = [];
        foreach ($planets as $pid => $pdata) {
            $sider = $n360($pdata['trop'] - $ayan);
            $vIdx  = (int)floor($sider / 30);
            $di    = fmod($sider, 30);
            $d     = (int)$di;
            $mm    = (int)(($di - $d) * 60);
            $planetPositions[] = [
                'abbr'       => self::$planetAbbr[$pid]  ?? '??',
                'color'      => self::$planetColor[$pid] ?? '#444',
                'retro'      => $pdata['retro'],
                'signShort'  => self::$signShort[$vIdx],
                'house'      => $signToHouse[$vIdx] ?? '?',
                'degDisplay' => $d . '°' . sprintf('%02d', $mm) . "'",
            ];
        }

        return ['houses' => $houses, 'planetPositions' => $planetPositions];
    }

    public static function buildHouseSignsData(int $ascSignIdx, array $housePlanetsArr): array
    {
        self::loadChartData();
        $houses = [];
        for ($h = 1; $h <= 12; $h++) {
            $ri   = ($ascSignIdx + $h - 1) % 12;
            $ps   = $housePlanetsArr[$h - 1] ?? [];
            $items = [];
            foreach ($ps as $p) {
                $items[] = [
                    'abbr'  => self::$planetAbbr[$p['pid']]  ?? '??',
                    'color' => self::$planetColor[$p['pid']] ?? '#444',
                ];
            }
            $houses[] = [
                'h'        => $h,
                'signFull' => self::$signFull[$ri],
                'planets'  => $items,
            ];
        }
        return ['houses' => $houses];
    }

    public static function buildPlanetSummaryData(array $planets, float $ayan, array $signToHouse): array
    {
        self::loadChartData();
        $n360  = fn($x) => fmod(fmod($x, 360) + 360, 360);
        $items = [];
        foreach ($planets as $pid => $pd) {
            $s  = $n360($pd['trop'] - $ayan);
            $vi = (int)floor($s / 30);
            $di = fmod($s, 30);
            $d  = (int)$di;
            $mm = (int)(($di - $d) * 60);
            $items[] = [
                'abbr'  => self::$planetAbbr[$pid]  ?? '??',
                'color' => self::$planetColor[$pid] ?? '#444',
                'retro' => $pd['retro'],
                'sign'  => self::$signShort[$vi],
                'house' => $signToHouse[$vi] ?? '?',
                'deg'   => $d . '°' . sprintf('%02d', $mm) . "'",
            ];
        }
        return ['items' => $items];
    }

    // ═══════════════════════════════════════════════════════════════
    //  UTILITY
    // ═══════════════════════════════════════════════════════════════

    public static function buildChart(int $ascSign, float $ascSider, array $siderPos, string $title, bool $showAs): string
    {
        return '<div style="background:#fff;border-radius:10px;padding:8px;border:1px solid #c8b896">'
            . self::buildChartSVG($ascSign, $ascSider, $siderPos, $showAs, 400) . '</div>';
    }

    private static function pts(array $poly): string
    {
        return implode(' ', array_map(fn($p) => round($p[0], 1) . ',' . round($p[1], 1), $poly));
    }
}
