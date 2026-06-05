<?php

namespace App\Features\Planetary;

/**
 * GocharCalculator — गोचर में राशियों का फल (Planetary Transit Results)
 *
 * Mathematical basis:
 *   • Brihat Parashara Hora Shastra (BPHS) — Gochara Adhyaya
 *   • Transit (Gochar) of each graha is judged by counting its house
 *     FROM the Janma Rashi (natal Moon sign). Each planet has a fixed
 *     set of houses in which its transit gives auspicious results.
 *   • Saturn transiting the 12th, 1st or 2nd from Moon = Sade Sati.
 *     Saturn in the 4th or 8th from Moon = Dhaiya (Kantaka / Ashtama).
 *
 * No external API — every value is derived arithmetically from the
 * sidereal (Lahiri) planetary longitudes computed by AstroCalculator
 * (Jean Meeus algorithms).
 */
class GocharCalculator
{
    private const ORDER = ['sun','moon','mars','mercury','jupiter','venus','saturn','rahu','ketu'];

    // BPHS — houses (counted from Moon) where each planet's transit is auspicious
    private const AUSP = [
        'sun'     => [3, 6, 10, 11],
        'moon'    => [1, 3, 6, 7, 10, 11],
        'mars'    => [3, 6, 11],
        'mercury' => [2, 4, 6, 8, 10, 11],
        'jupiter' => [2, 5, 7, 9, 11],
        'venus'   => [1, 2, 3, 4, 5, 8, 9, 11, 12],
        'saturn'  => [3, 6, 11],
        'rahu'    => [3, 6, 10, 11],
        'ketu'    => [3, 6, 11],
    ];

    private const PLANET = [
        'sun'     => ['en' => 'Sun',     'hi' => 'सूर्य',  'sym' => '☀', 'col' => '#d4760a'],
        'moon'    => ['en' => 'Moon',    'hi' => 'चंद्र',  'sym' => '☽', 'col' => '#1d4e6f'],
        'mars'    => ['en' => 'Mars',    'hi' => 'मंगल',   'sym' => '♂', 'col' => '#b83020'],
        'mercury' => ['en' => 'Mercury', 'hi' => 'बुध',    'sym' => '☿', 'col' => '#2e7a6e'],
        'jupiter' => ['en' => 'Jupiter', 'hi' => 'गुरु',   'sym' => '♃', 'col' => '#7a5a10'],
        'venus'   => ['en' => 'Venus',   'hi' => 'शुक्र',  'sym' => '♀', 'col' => '#8e3a7a'],
        'saturn'  => ['en' => 'Saturn',  'hi' => 'शनि',    'sym' => '♄', 'col' => '#4a4060'],
        'rahu'    => ['en' => 'Rahu',    'hi' => 'राहु',   'sym' => '☊', 'col' => '#1a3a1a'],
        'ketu'    => ['en' => 'Ketu',    'hi' => 'केतु',   'sym' => '☋', 'col' => '#5a1a0a'],
    ];

    private const HOUSE = [
        1  => ['en' => 'self, health, mind & vitality',          'hi' => 'स्वयं, स्वास्थ्य, मन एवं जीवनशक्ति'],
        2  => ['en' => 'wealth, family & speech',                'hi' => 'धन, परिवार एवं वाणी'],
        3  => ['en' => 'courage, siblings & efforts',            'hi' => 'पराक्रम, भाई-बहन एवं प्रयास'],
        4  => ['en' => 'home, mother, property & peace',         'hi' => 'घर, माता, संपत्ति एवं शांति'],
        5  => ['en' => 'children, intellect, romance & studies', 'hi' => 'संतान, बुद्धि, प्रेम एवं विद्या'],
        6  => ['en' => 'enemies, debts, competition & health',   'hi' => 'शत्रु, ऋण, प्रतिस्पर्धा एवं रोग'],
        7  => ['en' => 'marriage, partnership & travel',         'hi' => 'विवाह, साझेदारी एवं यात्रा'],
        8  => ['en' => 'obstacles, sudden change & longevity',   'hi' => 'बाधा, आकस्मिक परिवर्तन एवं आयु'],
        9  => ['en' => 'fortune, dharma, father & wisdom',       'hi' => 'भाग्य, धर्म, पिता एवं ज्ञान'],
        10 => ['en' => 'career, status & authority',             'hi' => 'करियर, पद एवं अधिकार'],
        11 => ['en' => 'gains, income & fulfilment of desires',  'hi' => 'लाभ, आय एवं इच्छापूर्ति'],
        12 => ['en' => 'expenses, losses, foreign & moksha',     'hi' => 'व्यय, हानि, विदेश एवं मोक्ष'],
    ];

    private const RASHI_EN = [
        'Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya',
        'Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena',
    ];
    private const RASHI_HI = [
        'मेष','वृषभ','मिथुन','कर्क','सिंह','कन्या',
        'तुला','वृश्चिक','धनु','मकर','कुंभ','मीन',
    ];
    private const RASHI_LORD = [
        'Mars','Venus','Mercury','Moon','Sun','Mercury',
        'Venus','Mars','Jupiter','Saturn','Saturn','Jupiter',
    ];

    /**
     * @param int   $natalMoonSign  0..11 (Janma Rashi)
     * @param array $transit        [pid => ['sign'=>0..11,'signName'=>string,'retro'=>bool]]
     */
    public static function calculate(int $natalMoonSign, array $transit): array
    {
        $rashis = [];
        for ($r = 0; $r < 12; $r++) {
            $rows = [];
            $good = 0;
            foreach (self::ORDER as $pid) {
                $t     = $transit[$pid];
                $house = (($t['sign'] - $r + 12) % 12) + 1;
                $ausp  = in_array($house, self::AUSP[$pid], true);
                if ($ausp) $good++;

                $hEn = self::HOUSE[$house]['en'];
                $hHi = self::HOUSE[$house]['hi'];
                $pEn = self::PLANET[$pid]['en'];
                $pHi = self::PLANET[$pid]['hi'];
                $ord = self::ordinal($house);

                $rows[] = [
                    'pid'      => $pid,
                    'en'       => $pEn,
                    'hi'       => $pHi,
                    'sym'      => self::PLANET[$pid]['sym'],
                    'col'      => self::PLANET[$pid]['col'],
                    'signName' => $t['signName'],
                    'retro'    => $t['retro'],
                    'house'    => $house,
                    'ausp'     => $ausp,
                    'phalEn'   => $ausp
                        ? "Favourable transit through your {$ord} house of {$hEn}."
                        : "Testing transit through your {$ord} house of {$hEn} — stay patient.",
                    'phalHi'   => $ausp
                        ? "{$pHi} आपके {$house}वें भाव ({$hHi}) में शुभ फल देगा।"
                        : "{$pHi} आपके {$house}वें भाव ({$hHi}) में सावधानी का संकेत।",
                ];
            }

            $satHouse = (($transit['saturn']['sign'] - $r + 12) % 12) + 1;
            $sade     = in_array($satHouse, [12, 1, 2], true);
            $dhaiya   = in_array($satHouse, [4, 8], true);

            $sadePhase = '';
            if ($sade) {
                $sadePhase = $satHouse === 12 ? 'प्रथम चरण · Rising (12th from Moon)'
                          : ($satHouse === 1  ? 'द्वितीय चरण · Peak (1st from Moon)'
                                              : 'तृतीय चरण · Setting (2nd from Moon)');
            }
            $dhaiyaType = '';
            if ($dhaiya) {
                $dhaiyaType = $satHouse === 4 ? 'कंटक शनि · Kantaka Shani (4th)'
                                             : 'अष्टम शनि · Ashtama Shani (8th)';
            }

            $rashis[] = [
                'idx'        => $r,
                'en'         => self::RASHI_EN[$r],
                'hi'         => self::RASHI_HI[$r],
                'lord'       => self::RASHI_LORD[$r],
                'good'       => $good,
                'rows'       => $rows,
                'satHouse'   => $satHouse,
                'sade'       => $sade,
                'sadePhase'  => $sadePhase,
                'dhaiya'     => $dhaiya,
                'dhaiyaType' => $dhaiyaType,
            ];
        }

        return [
            'natalMoonSign' => $natalMoonSign,
            'natalName'     => self::RASHI_EN[$natalMoonSign],
            'natalHi'       => self::RASHI_HI[$natalMoonSign],
            'rashis'        => $rashis,
        ];
    }

    public static function renderHtml(array $data): string
    {
        $natal = $data['natalMoonSign'];

        // ── Intro ────────────────────────────────────────────────────
        $html  = '<div style="font-family:\'DM Sans\',sans-serif">';
        $html .= '<div style="background:var(--panel);border-radius:18px;padding:18px 22px;margin-bottom:18px;border-left:4px solid var(--gold)">'
               . '<div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:var(--gold);margin-bottom:6px">◈ गोचर फल · Transit Results (BPHS)</div>'
               . '<div style="color:var(--text-mid);font-size:.9rem;line-height:1.7">Transit (Gochar) effects are judged by counting the house of each transiting planet <strong>from your Janma Rashi (Moon sign)</strong>. Houses that are auspicious per Brihat Parashara Hora Shastra are shown in green. गोचर का फल जन्म राशि (चंद्र राशि) से ग्रह की स्थिति गिनकर निकाला जाता है।</div>'
               . '<div style="margin-top:10px;font-size:.92rem;color:var(--text)">Your Janma Rashi · आपकी जन्म राशि: '
               . '<strong style="color:var(--sky)">' . self::RASHI_EN[$natal] . ' (' . self::RASHI_HI[$natal] . ')</strong></div>'
               . '</div>';

        // ── Rashi selector ───────────────────────────────────────────
        $html .= '<div style="display:flex;flex-wrap:wrap;gap:7px;margin-bottom:18px">';
        foreach ($data['rashis'] as $r) {
            $i      = $r['idx'];
            $on     = $i === $natal;
            $isNatal = $i === $natal ? ' ★' : '';
            $bg     = $on ? 'var(--sky)' : 'var(--card)';
            $fg     = $on ? '#fff' : 'var(--text-mid)';
            $bd     = $on ? 'var(--sky)' : 'var(--sky-pale)';
            $toggle = '(function(sel){'
                    . 'document.querySelectorAll(\'.gochar-detail\').forEach(function(e){e.style.display=\'none\'});'
                    . 'var t=document.getElementById(\'gochar-rashi-\'+sel);if(t)t.style.display=\'block\';'
                    . 'document.querySelectorAll(\'.gobtn\').forEach(function(b){b.style.background=\'var(--card)\';b.style.color=\'var(--text-mid)\';b.style.borderColor=\'var(--sky-pale)\'});'
                    . 'var btn=document.getElementById(\'gobtn\'+sel);if(btn){btn.style.background=\'var(--sky)\';btn.style.color=\'#fff\';btn.style.borderColor=\'var(--sky)\'}'
                    . '})(' . $i . ')';
            $html .= '<button class="gobtn" id="gobtn' . $i . '" onclick="' . htmlspecialchars($toggle, ENT_QUOTES) . '" '
                   . 'style="background:' . $bg . ';color:' . $fg . ';border:1.5px solid ' . $bd . ';'
                   . 'border-radius:14px;padding:7px 13px;cursor:pointer;font-family:\'DM Sans\',sans-serif;'
                   . 'font-size:.8rem;font-weight:700;line-height:1.25;text-align:center;min-width:74px">'
                   . self::RASHI_EN[$i] . $isNatal . '<br><span style="font-size:.72rem;font-weight:500;opacity:.85">' . self::RASHI_HI[$i] . '</span></button>';
        }
        $html .= '</div>';

        // ── 12 detail blocks ─────────────────────────────────────────
        foreach ($data['rashis'] as $r) {
            $html .= self::renderRashiDetail($r, $r['idx'] === $natal);
        }

        $html .= '</div>';
        return $html;
    }

    private static function renderRashiDetail(array $r, bool $visible): string
    {
        $good = $r['good'];
        [$vLabel, $vCol] = $good >= 6 ? ['Excellent · उत्तम', '#2e7d52']
                          : ($good >= 4 ? ['Favourable · शुभ', '#1d6aa0']
                          : ($good >= 2 ? ['Mixed · मिश्र', '#c48a2f']
                                        : ['Challenging · सावधानी', '#b13e3e']));

        $h  = '<div class="gochar-detail" id="gochar-rashi-' . $r['idx'] . '" style="display:' . ($visible ? 'block' : 'none') . '">';

        // Header
        $h .= '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:var(--sky-wash);'
            . 'border:1.5px solid var(--sky-pale);border-radius:18px;padding:16px 20px;margin-bottom:14px">'
            . '<div style="flex:1;min-width:180px">'
            . '<div style="font-family:\'Playfair Display\',serif;font-size:1.4rem;font-weight:700;color:var(--sky)">'
            . $r['en'] . ' <span style="font-size:1rem;color:var(--text-lt)">(' . $r['hi'] . ')</span></div>'
            . '<div style="font-size:.8rem;color:var(--text-mid);margin-top:2px">Rashi Lord · राशि स्वामी: ' . $r['lord'] . '</div>'
            . '</div>'
            . '<div style="text-align:right">'
            . '<div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;font-weight:800;color:var(--text-lt);margin-bottom:4px">Overall Transit</div>'
            . '<div style="display:inline-block;background:' . $vCol . '1a;color:' . $vCol . ';border:1.5px solid ' . $vCol . '55;'
            . 'border-radius:20px;padding:5px 14px;font-weight:800;font-size:.85rem">' . $vLabel . '</div>'
            . '<div style="font-size:.75rem;color:var(--text-lt);margin-top:5px">' . $good . ' / 9 grahas auspicious</div>'
            . '</div>'
            . '</div>';

        // Sade Sati / Dhaiya banner
        if ($r['sade']) {
            $h .= '<div style="background:#fbeae6;border-left:4px solid #b83020;border-radius:12px;padding:13px 18px;margin-bottom:14px">'
                . '<strong style="color:#8a1810">♄ शनि साढ़े साती · Saturn Sade Sati</strong>'
                . '<span style="color:#6a3020;font-size:.85rem"> — ' . $r['sadePhase'] . '. Saturn is in the ' . self::ordinal($r['satHouse']) . ' house from your Moon. यह काल धैर्य, अनुशासन एवं परिश्रम का है।</span>'
                . '</div>';
        } elseif ($r['dhaiya']) {
            $h .= '<div style="background:#fdf3e0;border-left:4px solid #c48a2f;border-radius:12px;padding:13px 18px;margin-bottom:14px">'
                . '<strong style="color:#8a5a10">♄ शनि ढैय्या · Saturn Dhaiya</strong>'
                . '<span style="color:#6a4a10;font-size:.85rem"> — ' . $r['dhaiyaType'] . '. A 2.5-year Saturn phase. सावधानी एवं स्वास्थ्य का ध्यान रखें।</span>'
                . '</div>';
        }

        // Planet grid
        $h .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">';
        foreach ($r['rows'] as $row) {
            $ac  = $row['ausp'] ? '#2e7d52' : '#b13e3e';
            $aBg = $row['ausp'] ? '#eaf6ef' : '#fbecec';
            $aLbl = $row['ausp'] ? 'शुभ · Auspicious' : 'अशुभ · Inauspicious';
            $retro = $row['retro'] ? ' <span style="color:#b83020;font-weight:700">℞</span>' : '';

            $h .= '<div style="background:var(--card);border:1.5px solid ' . $ac . '40;border-left:4px solid ' . $row['col'] . ';'
                . 'border-radius:14px;padding:14px 16px">'
                . '<div style="display:flex;align-items:center;gap:9px;margin-bottom:7px">'
                . '<span style="font-size:1.4rem;line-height:1;color:' . $row['col'] . '">' . $row['sym'] . '</span>'
                . '<div style="flex:1">'
                . '<div style="font-weight:800;font-size:.92rem;color:var(--text)">' . $row['en']
                . ' <span style="font-weight:500;color:var(--text-lt);font-size:.82rem">' . $row['hi'] . '</span></div>'
                . '<div style="font-size:.76rem;color:var(--text-mid)">in ' . $row['signName'] . $retro
                . ' · <strong>' . self::ordinal($row['house']) . ' house</strong></div>'
                . '</div>'
                . '<span style="background:' . $aBg . ';color:' . $ac . ';border:1px solid ' . $ac . '40;'
                . 'border-radius:20px;padding:3px 9px;font-size:.62rem;font-weight:800;white-space:nowrap">' . $aLbl . '</span>'
                . '</div>'
                . '<div style="font-size:.8rem;color:var(--text-mid);line-height:1.55">' . $row['phalEn'] . '</div>'
                . '<div style="font-size:.8rem;color:var(--text-lt);line-height:1.6;margin-top:3px">' . $row['phalHi'] . '</div>'
                . '</div>';
        }
        $h .= '</div>';

        $h .= '</div>';
        return $h;
    }

    private static function ordinal(int $n): string
    {
        $s = ['th','st','nd','rd'];
        $v = $n % 100;
        return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
    }
}
