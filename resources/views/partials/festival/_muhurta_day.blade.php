{{-- Muhurta day result panel — AJAX partial
     Expects: $data = MuhratCalculator::prepareDayView($dayData, $type)
--}}
<style>
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
.mhres-hero{background:linear-gradient(180deg,var(--sur) 0%,var(--bg1) 55%,var(--bg2) 100%);border-bottom:2px solid var(--bdr2);padding:22px 28px 18px;position:relative;}
.mhres-hero::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--gmd),transparent);}
.mhres-type{font-family:var(--font);font-size:2.2rem;color:var(--gdk);margin:0 0 3px;}
.mhres-sub{font-family:var(--serif);font-size:.82rem;font-style:italic;color:var(--ink4);letter-spacing:.12em;margin-bottom:14px;}
.mhres-score-row{display:flex;align-items:center;gap:14px;margin-bottom:10px;flex-wrap:wrap;}
.mhres-num{font-family:var(--serif);font-size:3.8rem;font-weight:700;line-height:1;color:var(--gdk);}
.mhres-grade{font-family:var(--font);font-size:1.7rem;padding:4px 18px;border-radius:6px;border:2px solid;}
.mhres-bar{background:#e8d0aa;border-radius:3px;height:7px;margin-bottom:14px;overflow:hidden;}
.mhres-bar-fill{height:7px;border-radius:3px;transition:width .8s cubic-bezier(.4,0,.2,1);}
.mhres-pancha{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--bdr2);border-radius:9px;overflow:hidden;border:1.5px solid var(--bdr2);margin-bottom:13px;}
.mhres-pc{background:var(--sur);padding:10px 6px;text-align:center;}
.mhres-pc span:first-child{display:block;font-family:var(--font);font-size:.7rem;color:var(--ink4);margin-bottom:4px;}
.mhres-pc span:last-child{font-family:var(--font);font-size:1rem;color:var(--ink);font-weight:600;}
.mhres-chips{display:flex;gap:7px;flex-wrap:wrap;}
.mhres-chip{background:var(--glt);border:1.5px solid var(--bdr2);border-radius:18px;padding:5px 14px;font-size:.88rem;color:var(--ink2);font-family:var(--serif);}
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

<div class="mhres">
<div class="mhres-hero">
    <div class="mhres-type">{{ $data['typeLabel'] }}</div>
    <div class="mhres-sub">Muhurta Chintamani · Daivajna Rama · {{ $data['dateHi'] }}</div>
    <div class="mhres-score-row">
        <span class="mhres-num" style="color:{{ $data['color'] }}">{{ $data['score'] }}</span>
        <span class="mhres-grade" style="color:{{ $data['color'] }};border-color:{{ $data['color'] }};background:rgba(0,0,0,.04)">{{ $data['grade']['hi'] }}</span>
        <span style="font-family:'Crimson Pro',serif;font-size:1rem;color:var(--ink4)">/ 100 Points</span>
    </div>
    <div class="mhres-bar"><div class="mhres-bar-fill" style="width:{{ $data['score'] }}%;background:{{ $data['color'] }}"></div></div>
    <div class="mhres-pancha">
        <div class="mhres-pc"><span>Vara</span><span>{{ $data['panchanga']['varaHi'] }}</span></div>
        <div class="mhres-pc"><span>Tithi</span><span>{{ $data['panchanga']['pakshaHi'] }} {{ $data['panchanga']['tithiHi'] }}</span></div>
        <div class="mhres-pc"><span>Nakshatra</span><span>{{ $data['panchanga']['nakHi'] }}</span></div>
        <div class="mhres-pc"><span>Yoga</span><span style="color:{{ $data['yogaColor'] }}">{{ $data['panchanga']['yogaHi'] }}</span></div>
        <div class="mhres-pc"><span>Karana</span><span>{{ $data['panchanga']['karanaHi'] }}</span></div>
    </div>
    <div class="mhres-chips">
        <span class="mhres-chip">☀ Sunrise {{ $data['sunrise'] }}</span>
        <span class="mhres-chip">🌅 Sunset {{ $data['sunset'] }}</span>
        <span class="mhres-chip">{{ $data['panchanga']['nakGana'] }} Gana · {{ $data['panchanga']['nakNadi'] }} Nadi</span>
        <span class="mhres-chip">Rahukala {{ $data['rahuKaal']['str'] }}</span>
        <span class="mhres-chip">Lagna: {{ $data['lagnaRashiHi'] }}</span>
        <span class="mhres-chip">Moon: {{ $data['moonRashiHi'] }}</span>
        <span class="mhres-chip">Sun: {{ $data['sunRashiHi'] }}</span>
        @if($data['isPanchak'])<span class="mhres-chip" style="border-color:#a02010;color:#841808;background:#fff0ee">⚠ Panchak</span>@endif
        @if($data['isBhadra'])<span class="mhres-chip" style="border-color:#c0302a;color:#f0a080">⚠ Bhadra</span>@endif
    </div>
</div>

<div class="mhres-body">

{{-- Rashi-specific card (vivah chandrabala) --}}
@if($data['chandrabala'])
<div style="background:linear-gradient(135deg,#fdf0f8,#f0f0ff);border:1.5px solid rgba(160,80,200,.2);border-radius:12px;padding:16px 20px;margin-bottom:18px">
    <div style="font-size:1.05rem;color:#5a2080;font-weight:600;margin-bottom:10px">
        Result Based on Your Rashi
        <span style="font-size:.82rem;color:#9a7050;font-weight:400"> — results differ for each Rashi</span>
    </div>
    @if($data['chandrabala']['girlRashi'] || $data['chandrabala']['boyRashi'])
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
        @if($data['chandrabala']['girlRashi'])
        <div style="background:rgba(200,80,160,.08);border:1px solid rgba(200,80,160,.25);border-radius:8px;padding:10px 14px">
            <div style="font-size:.8rem;color:#a02060;margin-bottom:3px">♀ Bride</div>
            <div style="font-size:1.1rem;font-weight:600;color:#7a1050">{{ $data['chandrabala']['girlRashi'] }}</div>
            @if($data['chandrabala']['girlNak'])<div style="font-size:.82rem;color:#7a5030">{{ $data['chandrabala']['girlNak'] }} Nakshatra</div>@endif
        </div>
        @endif
        @if($data['chandrabala']['boyRashi'])
        <div style="background:rgba(80,120,200,.08);border:1px solid rgba(80,120,200,.25);border-radius:8px;padding:10px 14px">
            <div style="font-size:.8rem;color:#204080;margin-bottom:3px">♂ Groom</div>
            <div style="font-size:1.1rem;font-weight:600;color:#1a3080">{{ $data['chandrabala']['boyRashi'] }}</div>
            @if($data['chandrabala']['boyNak'])<div style="font-size:.82rem;color:#7a5030">{{ $data['chandrabala']['boyNak'] }} Nakshatra</div>@endif
        </div>
        @endif
    </div>
    @endif
    <div style="background:{{ $data['chandrabala']['cb']['bg'] }};border:1.5px solid {{ $data['chandrabala']['cb']['color'] }};border-radius:8px;padding:10px 14px">
        <div style="font-size:1rem;font-weight:600;color:{{ $data['chandrabala']['cb']['color'] }}">{{ $data['chandrabala']['cb']['icon'] }} Chandrabala: {{ $data['chandrabala']['cb']['label'] }}</div>
        <div style="font-size:.88rem;color:#7a5830;margin-top:3px">Moon in position {{ $data['chandrabala']['cb']['dist'] }} from bride's Rashi.
            {{ $data['chandrabala']['cb']['shubh'] ? 'Chandrabala is auspicious for the bride on this date.' : 'Chandrabala is unfavourable for the bride on this date.' }}
        </div>
    </div>
</div>
@endif

{{-- Panchak note --}}
@if($data['panchak']['active'])
<div style="background:#fff0ee;border:1.5px solid #f0a090;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:.9rem;color:#8a1010">
    <strong>Panchak Active:</strong> {{ $data['panchak']['note'] }}
</div>
@endif

{{-- Doshas --}}
@foreach($data['doshas'] as $d)
<div style="border-left:4px solid #a02010;padding:11px 16px;background:#fff0ee;border-radius:8px;margin-bottom:8px;font-size:1rem;color:#841808;font-family:var(--font)">⚠ {{ $d }}</div>
@endforeach

{{-- Shubh --}}
@foreach($data['shubhList'] as $s)
<div style="border-left:4px solid #1a5a2a;padding:11px 16px;background:#f0faf2;border-radius:8px;margin-bottom:8px;font-size:1rem;color:#1a5a2a;font-family:var(--font)">✓ {{ $s }}</div>
@endforeach

{{-- Asta (vivah) --}}
@if($data['astaData'])
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
    <div style="border:1.5px solid {{ $data['astaData']['guru']['color'] }};border-radius:9px;padding:12px;background:rgba(0,0,0,.02)">
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;font-weight:600;color:{{ $data['astaData']['guru']['color'] }}">Jupiter {{ $data['astaData']['guru']['asta'] ? 'Combust ✗' : 'Not Combust ✓' }}</div>
        <div style="font-size:.88rem;color:#7a5830;margin-top:4px">{{ $data['astaData']['guru']['diff'] }}° from Sun · Limit: {{ $data['astaData']['guru']['limit'] }}°</div>
        <div style="font-size:.82rem;color:#9a6b0a;margin-top:3px">{{ $data['astaData']['guru']['asta'] ? 'MC: Marriage prohibited.' : 'MC: Favourable for marriage.' }}</div>
    </div>
    <div style="border:1.5px solid {{ $data['astaData']['shukra']['color'] }};border-radius:9px;padding:12px;background:rgba(0,0,0,.02)">
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;font-weight:600;color:{{ $data['astaData']['shukra']['color'] }}">Venus {{ $data['astaData']['shukra']['asta'] ? 'Combust ✗' : 'Not Combust ✓' }}</div>
        <div style="font-size:.82rem;color:#5a3010;margin-top:4px">{{ $data['astaData']['shukra']['diff'] }}° from Sun · Limit: {{ $data['astaData']['shukra']['limit'] }}°</div>
        <div style="font-size:.78rem;color:#7a5030;margin-top:3px">{{ $data['astaData']['shukra']['asta'] ? 'MC: Marriage prohibited.' : 'MC: Favourable.' }}</div>
    </div>
</div>
@endif

{{-- Latta doshas (vivah) --}}
@foreach($data['lattaDoshas'] as $ld)
<div style="border-left:3px solid #c86a14;padding:8px 12px;background:#fff8ee;border-radius:0 6px 6px 0;margin-bottom:6px;font-size:.88rem;color:#5a2800">⚡ {{ $ld['note'] }}</div>
@endforeach

<div class="mhres-grid">

{{-- Left column --}}
<div>

{{-- Choghadiya --}}
<div class="mhres-sec">
    <div class="mhres-sh">Choghadiya</div>
    @foreach($data['choRows'] as $ch)
    <div style="border-left:3px solid {{ $ch['borderColor'] }};padding:7px 10px;background:{{ $ch['bgColor'] }};border-radius:0 6px 6px 0;margin-bottom:5px">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;font-weight:600;color:#1c0e04">{{ $ch['name'] }}</span>
            <span style="font-family:'Crimson Pro',serif;font-size:.95rem;color:#7a5830">{{ $ch['start'] }} – {{ $ch['end'] }}</span>
        </div>
        <div style="font-size:.78rem;color:{{ $ch['borderColor'] }};font-family:'Crimson Pro',serif">{{ $ch['nature'] }} · {{ $ch['planet'] }}</div>
    </div>
    @endforeach
</div>

{{-- Lagna table --}}
<div class="mhres-sec">
    <div class="mhres-sh">Lagna Table</div>
    <div style="border:1px solid rgba(168,112,40,.18);border-radius:8px;overflow:hidden">
        @foreach($data['lagnaRows'] as $l)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;border-bottom:1px solid rgba(168,112,40,.08);background:{{ $l['bg'] }}">
            <span style="font-size:.95rem;color:{{ $l['color'] }};font-weight:600">{{ $l['signHi'] }}</span>
            <span style="font-size:.88rem;color:#9a6b0a">{{ $l['type'] }}</span>
            <span style="font-size:.88rem;color:#6b4800">{{ $l['time'] }}</span>
        </div>
        @endforeach
    </div>
</div>

</div>{{-- /left --}}

{{-- Right column --}}
<div>

{{-- Milan (vivah only) --}}
@if($data['milanData'])
<div style="margin-bottom:24px">
    <div style="font-size:1.3rem;font-weight:600;color:#6b4800;border-bottom:2px solid rgba(168,112,40,.25);padding-bottom:10px;margin-bottom:16px">
        Bride-Groom Compatibility — Ashtakoot Kundali Milan
    </div>
    <div style="background:linear-gradient(135deg,#1a0e2e,#2a1848);border-radius:12px;padding:20px;margin-bottom:14px">
        <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:center">
            <div style="text-align:left">
                <div style="color:#e890d0;font-size:1.8rem;margin-bottom:4px">♀</div>
                <div style="font-size:1.2rem;font-weight:600;color:#f0d8ff">{{ $data['milanData']['girRashi'] }}</div>
                <div style="font-size:.95rem;color:rgba(200,180,255,.7)">{{ $data['milanData']['girNak'] }} Nakshatra</div>
            </div>
            <div style="text-align:center;padding:0 16px">
                <div style="font-family:'Crimson Pro',serif;font-size:4rem;font-weight:700;color:{{ $data['milanData']['color'] }};line-height:1">{{ $data['milanData']['total'] }}</div>
                <div style="font-size:1rem;color:rgba(200,180,255,.5);margin-bottom:4px">/ 36 Points</div>
                <div style="font-size:1.1rem;font-weight:600;color:{{ $data['milanData']['color'] }}">{{ $data['milanData']['rating']['hi'] }}</div>
                <div style="font-size:.88rem;color:rgba(200,180,255,.6);margin-top:4px">{{ $data['milanData']['interp'] }}</div>
            </div>
            <div style="text-align:right">
                <div style="color:#70b8e8;font-size:1.8rem;margin-bottom:4px">♂</div>
                <div style="font-size:1.2rem;font-weight:600;color:#d8eaff">{{ $data['milanData']['boyRashi'] }}</div>
                <div style="font-size:.95rem;color:rgba(160,200,255,.7)">{{ $data['milanData']['boyNak'] }} Nakshatra</div>
            </div>
        </div>
        @if(!empty($data['milanData']['mahadosha']))
        <div style="margin-top:12px;padding-top:10px;border-top:1px solid rgba(255,255,255,.1)">
            @foreach($data['milanData']['mahadosha'] as $md)
            <span style="background:rgba(192,48,42,.15);color:#8a1010;padding:4px 12px;border-radius:20px;font-size:.9rem;margin-right:6px;margin-bottom:4px;display:inline-block">⚠ {{ $md }}</span>
            @endforeach
        </div>
        @endif
    </div>
    <div style="border:1.5px solid rgba(168,112,40,.25);border-radius:10px;overflow:hidden">
        <table style="width:100%;border-collapse:collapse;font-family:'Tiro Devanagari Sanskrit',serif">
            <thead>
                <tr style="background:#f2e0c4">
                    <th style="padding:11px 14px;text-align:left;font-size:1.05rem;color:#6b4800;font-weight:600">Koot / Factor</th>
                    <th style="padding:11px 14px;text-align:center;font-size:1rem;color:#a02060;font-weight:600">♀ Bride</th>
                    <th style="padding:11px 14px;text-align:center;font-size:1rem;color:#204090;font-weight:600">♂ Groom</th>
                    <th style="padding:11px 14px;text-align:center;font-size:1.05rem;color:#6b4800;font-weight:600">Score</th>
                    <th style="padding:11px 14px;text-align:left;font-size:1.05rem;color:#6b4800;font-weight:600">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['milanData']['kootRows'] as $koot)
                <tr style="background:{{ $koot['rowBg'] }};border-bottom:1px solid rgba(168,112,40,.12)">
                    <td style="padding:11px 14px;font-size:1.05rem;font-weight:600;color:#5a2800;white-space:nowrap">
                        {{ $koot['kootName'] }}
                        @if(!$koot['isAuspicious'])
                        <span style="background:#f5b8a8;color:#8a1010;padding:2px 8px;border-radius:6px;font-size:.82rem;margin-left:6px">⚠ Dosha</span>
                        @else
                        <span style="background:#d4eedc;color:#1a6a2a;padding:2px 8px;border-radius:6px;font-size:.82rem;margin-left:6px">✓ Auspicious</span>
                        @endif
                    </td>
                    <td style="padding:11px 14px;font-size:.95rem;color:#3a2010;text-align:center">{{ $koot['girl'] }}</td>
                    <td style="padding:11px 14px;font-size:.95rem;color:#3a2010;text-align:center">{{ $koot['boy'] }}</td>
                    <td style="padding:11px 14px;text-align:center">
                        <span style="font-family:'Crimson Pro',serif;font-size:1.3rem;font-weight:700;color:{{ $koot['color'] }}">{{ $koot['got'] }}</span>
                        <span style="font-size:.9rem;color:#b09070">/{{ $koot['max'] }}</span>
                    </td>
                    <td style="padding:11px 14px;font-size:.88rem;color:#7a5030">{{ $koot['note'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9edd8;border-top:2px solid rgba(168,112,40,.3)">
                    <td colspan="3" style="padding:12px 14px;font-size:1.05rem;font-weight:600;color:#6b4800">Total Score</td>
                    <td style="padding:12px 14px;text-align:center">
                        <span style="font-family:'Crimson Pro',serif;font-size:1.5rem;font-weight:700;color:{{ $data['milanData']['color'] }}">{{ $data['milanData']['total'] }}</span>
                        <span style="font-size:.9rem;color:#b09070">/36</span>
                    </td>
                    <td style="padding:12px 14px;font-size:.95rem;color:{{ $data['milanData']['color'] }};font-weight:600">{{ $data['milanData']['rating']['hi'] }} — {{ $data['milanData']['interp'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div style="margin-top:10px;padding:10px 14px;background:#fdf8ee;border-radius:7px;font-size:.95rem;color:#3a2010;line-height:1.7">
        <strong>MC:</strong> Minimum 18 Gunas required; Nadi, Bhakoot, and Gana Doshas must be absent.
        Nadi Dosha is most serious — risk of progeny issues. Bhakoot Dosha — financial loss. Gana Dosha — incompatibility of nature.
    </div>
</div>
@elseif($data['chandrabala'])
{{-- Chandrabala + tarabala without milan --}}
<div style="margin-bottom:18px">
    <div style="font-size:1.2rem;font-weight:600;color:#5a2080;border-bottom:2px solid rgba(160,80,200,.2);padding-bottom:8px;margin-bottom:12px">
        Chandrabala &amp; Tarabala — Rashi-specific
        <span style="font-size:.82rem;color:#9a7050;font-weight:400;margin-left:8px">— results differ for each Rashi</span>
    </div>
    <div style="background:{{ $data['chandrabala']['cb']['bg'] }};border:1.5px solid {{ $data['chandrabala']['cb']['color'] }};border-radius:8px;padding:12px 16px;margin-bottom:10px">
        <div style="font-size:1.1rem;font-weight:600;color:{{ $data['chandrabala']['cb']['color'] }}">{{ $data['chandrabala']['cb']['icon'] }} Chandrabala: {{ $data['chandrabala']['cb']['label'] }}</div>
        <div style="font-size:.95rem;color:#7a5830;margin-top:4px">Moon in position {{ $data['chandrabala']['cb']['dist'] }} from bride's Rashi.
            {{ $data['chandrabala']['cb']['shubh'] ? 'Chandrabala is auspicious for the bride on this date.' : 'Chandrabala is inauspicious for the bride on this date.' }}
        </div>
    </div>
    @if($data['chandrabala']['tb'])
    <div style="background:{{ $data['chandrabala']['tb']['bg'] }};border:1.5px solid {{ $data['chandrabala']['tb']['color'] }};border-radius:8px;padding:12px 16px;margin-bottom:10px">
        <div style="font-size:1.1rem;font-weight:600;color:{{ $data['chandrabala']['tb']['color'] }}">{{ $data['chandrabala']['tb']['icon'] }} Tarabala: {{ $data['chandrabala']['tb']['name'] }}</div>
        <div style="font-size:.95rem;color:#7a5830;margin-top:4px">{{ $data['chandrabala']['tb']['shubh'] ? 'Tarabala from birth Nakshatra is auspicious.' : 'Tarabala from birth Nakshatra is inauspicious.' }}</div>
    </div>
    @endif
</div>
@endif

{{-- Hora --}}
<div class="mhres-sec">
    <div class="mhres-sh">Auspicious Hora</div>
    @foreach($data['horaRows'] as $h)
    <div style="border:1px solid rgba(168,112,40,.2);border-radius:6px;padding:7px 10px;background:{{ $h['bgColor'] }};margin-bottom:5px;display:flex;justify-content:space-between;align-items:center">
        <div>
            <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:{{ $h['textColor'] }}">{{ $h['lordHi'] }}</span>
            <span style="font-size:1rem;color:#7a5830;margin-left:8px;font-family:'Crimson Pro',serif">{{ $h['quality'] }}</span>
        </div>
        <span style="font-family:'Crimson Pro',serif;font-size:.88rem;color:#5a3010">{{ $h['start'] }}</span>
    </div>
    @endforeach
</div>

{{-- Auspicious windows --}}
<div class="mhres-sec">
    <div class="mhres-sh">Auspicious Muhurta Windows</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
        @foreach($data['windowSlots'] as $w)
        <span class="mhres-win">{{ $w['start'] }} – {{ $w['end'] }}</span>
        @endforeach
    </div>
    <div class="mhres-rahu">
        <div class="mhres-inaup">⛔ Rahukala {{ $data['rahuKaal']['str'] }}</div>
        <div class="mhres-inaup">⛔ Yamaghanta {{ $data['yamaghanta']['str'] }}</div>
        <div class="mhres-inaup">⛔ Gulika {{ $data['gulikaKaal']['str'] }}</div>
    </div>
    <div class="mhres-abhijit">
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.88rem;color:#6b4800;margin-bottom:3px">Abhijit Muhurta — MC: Always Auspicious</div>
        <div style="font-family:'Crimson Pro',serif;font-size:1.3rem;font-weight:700;color:#c8521a">{{ $data['abhijit']['str'] }}</div>
    </div>
</div>

</div>{{-- /right --}}
</div>{{-- /grid --}}

{{-- Shastra guidelines --}}
<div class="mhres-sec">
    <div class="mhres-sh">MC Shastra Guidelines — Muhurta Chintamani (Daivajna Rama)</div>
    @foreach($data['shastraRules'] as $i => $rule)
    <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:9px">
        <span style="min-width:24px;height:24px;border-radius:50%;background:#f5e8d0;border:1px solid rgba(168,112,40,.3);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#9a6b0a;flex-shrink:0;font-family:'Crimson Pro',serif;font-weight:700">{{ $i + 1 }}</span>
        <span style="font-size:.95rem;color:#2a1408;line-height:1.65">{{ $rule }}</span>
    </div>
    @endforeach
</div>

</div>{{-- /body --}}
</div>{{-- /mhres --}}

<script>
function mhDoScan(type){
    var m = document.getElementById('mhs-mo').value, y = document.getElementById('mhs-yr').value;
    var res = document.getElementById('mhs-res');
    res.style.display = 'block';
    res.innerHTML = '<div style="padding:14px;font-family:\'Tiro Devanagari Sanskrit\',serif;font-size:.95rem;color:#7a5030">Searching...</div>';
    var csrf = document.querySelector('meta[name=csrf-token]');
    fetch('/astro/muhrat/month', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '', 'Accept': 'application/json'},
        body: JSON.stringify({year: parseInt(y), month: parseInt(m), type: type,
            lat: typeof _masaLat !== 'undefined' ? _masaLat : 28.61,
            lon: typeof _masaLon !== 'undefined' ? _masaLon : 77.21,
            utcOff: typeof _masaOff !== 'undefined' ? _masaOff : 5.5,
            girlRashiIdx: typeof _mhGirlRashi !== 'undefined' ? _mhGirlRashi : null,
            boyRashiIdx: typeof _mhBoyRashi !== 'undefined' ? _mhBoyRashi : null})
    }).then(r => r.json()).then(d => { res.innerHTML = d.html || '<div style="padding:14px;color:#c0302a">Error.</div>'; })
      .catch(() => { res.innerHTML = '<div style="padding:14px;color:#c0302a">Network error.</div>'; });
}
</script>
