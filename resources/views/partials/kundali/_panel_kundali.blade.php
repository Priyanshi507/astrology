{{-- Kundali Panel — comprehensive astrologer view
     $k  = KundaliService::calculate(...)
     $shadBalaHtml, $dashaHtml, $pancha — from AstroController
--}}
@php
  $gold    = '#b8860b';
  $goldLt  = '#e6c96e';
  $bg      = '#fffdf6';
  $bgRow   = '#faf6ec';
  $bgHd    = '#f3ead6';
  $bd      = '#e0d0a8';
  $txt     = '#2a1a00';
  $txtM    = '#6a4a20';
  $txtL    = '#9a7a4a';
  $red     = '#b83232';
  $grn     = '#1e6e3e';
  $blue    = '#1a3a7e';

  $th = "background:{$bgHd};color:{$gold};font-size:.72rem;text-transform:uppercase;letter-spacing:1.3px;font-weight:800;padding:9px 11px;border:1px solid {$bd};white-space:nowrap";
  $td = "color:{$txt};font-size:.9rem;padding:7px 11px;border:1px solid {$bd};vertical-align:middle;line-height:1.45";
  $tdr = "color:{$txt};font-size:.9rem;padding:7px 11px;border:1px solid {$bd};vertical-align:middle;text-align:right";
  $tdc = "color:{$txt};font-size:.9rem;padding:7px 11px;border:1px solid {$bd};vertical-align:middle;text-align:center";

  $dignityColor = fn($d) => match(true) {
    str_contains($d,'Exalted') => $grn,
    str_contains($d,'Moola')   => '#1a6a4a',
    str_contains($d,'Own')     => '#2a5a8e',
    str_contains($d,'Debilitated') => $red,
    default => $txtM,
  };

  $clsColor = fn($c) => match($c) {
    'excellent'   => $grn,
    'very_good'   => '#1a5a3a',
    'good'        => '#2a5a8e',
    'challenging' => $red,
    default       => $txtM,
  };
@endphp

<div style="font-family:'DM Sans',sans-serif;color:{{$txt}}">

{{-- ══ SUB-TAB BAR ══ --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;border-bottom:2px solid {{$bd}};padding-bottom:10px;margin-bottom:18px">
  @php
    $stabs = [
      ['graha',   'Graha',    '☿'],
      ['bhava',   'Bhava',    '⬆'],
      ['upgraha', 'Upagraha', '⊕'],
      ['yoga',    'Yoga',     '✦'],
      ['av',      'Ashtaka Varga','⊞'],
      ['bb',      'Bhava Bala','⚖'],
      ['dasha',   'Dasha',    '⏳'],
      ['shadbala','Shadbala', '⚡'],
      ['pancha',  'Panchanga','🌙'],
    ];
  @endphp
  @foreach($stabs as [$sid,$slbl,$ssym])
  <button id="kst_{{$sid}}" onclick="kSwitchTab('{{$sid}}')"
    style="padding:6px 14px;border-radius:20px;border:1.5px solid {{$bd}};background:{{$bgRow}};
           cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.78rem;font-weight:700;
           color:{{$txtM}};transition:all .18s;white-space:nowrap">
    {{$ssym}} {{$slbl}}
  </button>
  @endforeach
</div>

{{-- ══ LAGNA BADGE ══ --}}
<div style="display:flex;align-items:center;gap:16px;background:{{$bgHd}};border:1.5px solid {{$bd}};
            border-radius:12px;padding:10px 18px;margin-bottom:16px">
  <span style="font-size:1.6rem">⬆</span>
  <div>
    <span style="font-size:1.1rem;font-weight:800;color:{{$gold}}">Lagna: {{$k['lagnaSign']}}</span>
    <span style="color:{{$txtM}};font-size:.85rem;margin-left:8px">House 1 · {{$k['lagnaSign']}} ascendant</span>
  </div>
</div>

{{-- ══════════════════════════════════════ GRAHA TAB ══════════════════════════════════════ --}}
<div id="ks_graha" class="k-sub">
  <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:{{$gold}};margin-bottom:12px">Graha — Planetary Details</div>
  <div style="overflow-x:auto">
  <table style="border-collapse:collapse;width:100%;min-width:1080px">
    <thead>
      <tr>
        <th style="{{$th}}">Graha</th>
        <th style="{{$th}}">R</th>
        <th style="{{$th}}">C</th>
        <th style="{{$th}}">Longitude</th>
        <th style="{{$th}}">Nakshatra / Swami</th>
        <th style="{{$th}}">Raw L.</th>
        <th style="{{$th}}">Latitude / Shara</th>
        <th style="{{$th}}">Right Ascension</th>
        <th style="{{$th}}">Declination / Kranti</th>
        <th style="{{$th}}">Speed °/day</th>
        <th style="{{$th}}">House</th>
        <th style="{{$th}}">Sub Lord</th>
        <th style="{{$th}}">Dignity</th>
      </tr>
    </thead>
    <tbody>
      @foreach($k['graha'] as $g)
      @php
        $isRetro = !$g['isLagna'] && ($g['retro'] ?? false);
        $isCombust = !$g['isLagna'] && ($g['combust'] ?? false);
        $rowBg = $g['isLagna'] ? "background:#f0ead8" : (($loop->index % 2===0) ? "background:{$bg}" : "background:{$bgRow}");
      @endphp
      <tr style="{{$rowBg}}">
        <td style="{{$td}};font-weight:700;white-space:nowrap">
          <span style="font-size:1.15rem;margin-right:5px">{{$g['sym']}}</span>{{$g['vedicName']}}
        </td>
        <td style="{{$tdc}};font-weight:800;color:{{$red}}">{{$isRetro ? '℞' : '—'}}</td>
        <td style="{{$tdc}};font-weight:700;color:{{$isCombust ? $red : $txtL}}">{{$isCombust ? '☌' : '—'}}</td>
        <td style="{{$td}};font-family:monospace;font-size:.85rem;color:{{$blue}};white-space:nowrap">{{$g['lonFmt']}}</td>
        <td style="{{$td}}">
          {{$g['nakName']}}
          @if(!$g['isLagna'])<span style="font-size:.8rem;color:{{$txtL}}"> p{{$g['nakPada']}} · {{$g['nakLord']}}</span>@endif
        </td>
        <td style="{{$tdc}};font-family:monospace;font-size:.82rem;color:{{$txtM}}">{{$g['lonFull']}}°</td>
        <td style="{{$tdc}};font-family:monospace;font-size:.82rem;color:{{$txtM}}">{{$g['lat'] ?? '—'}}</td>
        <td style="{{$tdc}};font-family:monospace;font-size:.82rem;color:{{$txtM}}">{{$g['ra'] ?? '—'}}</td>
        <td style="{{$tdc}};font-family:monospace;font-size:.82rem;color:{{$txtM}}">{{$g['dec'] ?? '—'}}</td>
        <td style="{{$tdc}};font-family:monospace;font-size:.82rem;color:{{$isRetro ? $red : $txtM}}">{{$g['spd'] ?? '—'}}</td>
        <td style="{{$tdc}};font-weight:800;color:{{$blue}};font-size:.95rem">{{$g['isIn']}}</td>
        <td style="{{$tdc}};color:{{$gold}};font-weight:700">{{$g['subLord']}}</td>
        <td style="{{$td}};font-weight:700;color:{{$dignityColor($g['dignity'])}}">{{$g['dignity'] ?: '—'}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="font-size:.78rem;color:{{$txtL}};margin-top:10px;line-height:1.7">
    <b>R</b> = Retrograde &nbsp;·&nbsp; <b>C</b> = Combust (within Sun's orb) &nbsp;·&nbsp;
    <b>Longitude</b> = Sidereal DMS in sign &nbsp;·&nbsp; <b>Raw L.</b> = full sidereal degrees &nbsp;·&nbsp;
    <b>RA</b> = Right Ascension (HH:MM:SS) &nbsp;·&nbsp; <b>Dec</b> = Declination (ecliptic lat = 0 assumed for non-specialised nodes).
    Sub-lord by KP (Krishnamurti Paddhati). Dignity = Vedic classical (Exaltation / Debilitation / Own / Moolatrikona).
  </div>
</div>

{{-- ══════════════════════════════════════ BHAVA TAB ══════════════════════════════════════ --}}
<div id="ks_bhava" class="k-sub" style="display:none">
  <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:{{$gold}};margin-bottom:12px">Bhava — House Details</div>
  <div style="overflow-x:auto">
  <table style="border-collapse:collapse;width:100%;min-width:860px">
    <thead>
      <tr>
        <th style="{{$th}}">Bhava</th>
        <th style="{{$th}}">Sign / Rashi</th>
        <th style="{{$th}}">Lord</th>
        <th style="{{$th}}">Residents</th>
        <th style="{{$th}}">Mod.</th>
        <th style="{{$th}}">Element</th>
        <th style="{{$th}}">Nature</th>
        <th style="{{$th}}">Aspected By</th>
      </tr>
    </thead>
    <tbody>
      @foreach($k['bhava'] as $bv)
      @php
        // House type markers
        $typeMarkers = [];
        if ($bv['isKendra'])   $typeMarkers[] = '<span style="color:'.$blue.';font-weight:800;font-size:.8rem" title="Kendra — angular house">(Q)</span>';
        if ($bv['isTrikona'])  $typeMarkers[] = '<span style="color:'.$grn.';font-weight:800;font-size:.8rem" title="Trikona — trine house">(T)</span>';
        if ($bv['isDusthana']) $typeMarkers[] = '<span style="color:'.$red.';font-weight:800;font-size:.8rem" title="Dusthana — difficult house">(D)</span>';
        $typeStr = implode(' ', $typeMarkers);
        if (!$typeMarkers) $typeStr = '<span style="color:'.$txtL.'">—</span>';

        $typeLabel = implode(' · ', array_filter([
          $bv['isKendra']   ? 'Kendra'   : null,
          $bv['isTrikona']  ? 'Trikona'  : null,
          $bv['isDusthana'] ? 'Dusthana' : null,
        ])) ?: 'Upachaya/Neutral';

        $houseColor = $bv['isKendra'] ? $blue : ($bv['isTrikona'] ? $grn : ($bv['isDusthana'] ? $red : $txtM));
        $rowBg = ($loop->index % 2 === 0) ? "background:{$bg}" : "background:{$bgRow}";
      @endphp
      <tr style="{{$rowBg}}">
        <td style="{{$tdc}};font-weight:900;color:{{$houseColor}};font-size:1.05rem">
          {{$bv['house']}}
          <div style="font-size:.75rem;font-weight:500;color:{{$txtL}};margin-top:1px">{!!$typeStr!!}</div>
        </td>
        <td style="{{$td}};font-weight:600">{{$bv['signName']}}</td>
        <td style="{{$td}};font-weight:700;white-space:nowrap">
          <span style="font-size:1.1rem;margin-right:4px">{{$bv['ownerSym']}}</span>{{$bv['owner']}}
        </td>
        <td style="{{$td}}">
          @if(empty($bv['residents']))
            <span style="color:{{$txtL}};font-style:italic">Empty</span>
          @else
            @foreach($bv['residents'] as $r)
              <span title="{{ucfirst($r['pid'])}}" style="font-size:1.05rem;margin-right:3px">{{$r['sym']}}@if($r['retro'])<sup style="color:{{$red}};font-size:.65rem">℞</sup>@endif</span>
            @endforeach
          @endif
        </td>
        <td style="{{$tdc}};font-size:.85rem">{{$bv['modality']}}</td>
        <td style="{{$tdc}};font-size:.85rem">{{$bv['element']}}</td>
        <td style="{{$td}};font-size:.85rem;color:{{$houseColor}};font-weight:600">{{$typeLabel}}</td>
        <td style="{{$td}}">
          @if(empty($bv['aspectedBy']))
            <span style="color:{{$txtL}}">—</span>
          @else
            @foreach($bv['aspectedBy'] as $a)
              <span title="{{ucfirst($a['pid'])}} ({{($a['fraction']==1.0)?'Full':round($a['fraction']*100).'%'}} aspect)"
                    style="font-size:1.05rem;margin-right:4px;color:{{$txtM}}">{{$a['sym']}}</span>
            @endforeach
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="font-size:.78rem;color:{{$txtL}};margin-top:10px;line-height:1.7">
    <b>(Q)</b> = Kendra (angular: 1,4,7,10) &nbsp;·&nbsp; <b>(T)</b> = Trikona (trine: 1,5,9) &nbsp;·&nbsp;
    <b>(D)</b> = Dusthana (difficult: 6,8,12) &nbsp;·&nbsp;
    Whole-sign house system &nbsp;·&nbsp; Vedic aspects: Mars 4/7/8H, Jupiter 5/7/9H, Saturn 3/7/10H, all others 7H only.
  </div>
</div>

{{-- ══════════════════════════════════════ UPAGRAHA TAB ══════════════════════════════════════ --}}
<div id="ks_upgraha" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Upagraha — Shadow Planets</div>
  <div style="overflow-x:auto">
  <table style="border-collapse:collapse;width:100%;min-width:640px">
    <thead>
      <tr>
        <th style="{{$th}}">Upagraha</th>
        <th style="{{$th}}">Longitude</th>
        <th style="{{$th}}">Sign</th>
        <th style="{{$th}}">House</th>
        <th style="{{$th}}">Nakshatra</th>
        <th style="{{$th}}">Pada</th>
        <th style="{{$th}}">Nak Lord</th>
        <th style="{{$th}}">Significance</th>
      </tr>
    </thead>
    <tbody>
      @foreach($k['upgraha'] as $u)
      <tr style="background:{{$loop->index%2===0?$bg:$bgRow}}">
        <td style="{{$td}};font-weight:700">
          <span style="font-size:1.1rem;margin-right:5px">{{$u['sym']}}</span>{{$u['name']}}
        </td>
        <td style="{{$td}};font-family:monospace;font-size:.78rem;color:{{$blue}}">{{$u['lonFmt']}}</td>
        <td style="{{$td}}">{{$u['signName']}}</td>
        <td style="{{$tdc}};font-weight:800;color:{{$blue}}">{{$u['house']}}</td>
        <td style="{{$td}}">{{$u['nakName']}}</td>
        <td style="{{$tdc}}">{{$u['nakPada']}}</td>
        <td style="{{$tdc}}">{{$u['nakLord']}}</td>
        <td style="{{$td}};font-size:.77rem;color:{{$txtM}}">{{$u['desc']}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="font-size:.72rem;color:{{$txtL}};margin-top:8px;line-height:1.6">
    Upagrahas derived from Sun's sidereal longitude · Gulika = Son of Saturn, most significant shadow planet
  </div>
</div>

{{-- ══════════════════════════════════════ YOGA TAB ══════════════════════════════════════ --}}
<div id="ks_yoga" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Yoga — Planetary Combinations</div>

  @if(empty($k['yogas']))
    <div style="padding:24px;text-align:center;color:{{$txtL}};font-style:italic">No notable yogas detected for this chart.</div>
  @else

  {{-- Auspicious yogas --}}
  @php $auspYogas = array_filter($k['yogas'], fn($y) => $y['ausp']); @endphp
  @if(!empty($auspYogas))
  <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:{{$grn}};margin-bottom:8px">✦ Auspicious Yogas</div>
  <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px">
    @foreach($auspYogas as $y)
    <div style="border:1.5px solid {{$bd}};border-radius:12px;padding:14px 16px;background:{{$bg}};
                border-left:4px solid {{$clsColor($y['cls'])}}">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
        <span style="font-weight:800;color:{{$clsColor($y['cls'])}};font-size:.95rem">{{$y['name']}}</span>
        <span style="font-size:.68rem;background:{{$bgHd}};color:{{$txtM}};padding:2px 8px;border-radius:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase">{{$y['type']}}</span>
        @if($y['house'])
        <span style="font-size:.72rem;color:{{$blue}};font-weight:700;margin-left:auto">H{{$y['house']}}</span>
        @endif
      </div>
      <div style="font-size:.82rem;color:{{$txtM}};line-height:1.55">{{$y['desc']}}</div>
    </div>
    @endforeach
  </div>
  @endif

  {{-- Challenging yogas --}}
  @php $challYogas = array_filter($k['yogas'], fn($y) => !$y['ausp']); @endphp
  @if(!empty($challYogas))
  <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:{{$red}};margin-bottom:8px">⚠ Challenging Yogas</div>
  <div style="display:flex;flex-direction:column;gap:10px">
    @foreach($challYogas as $y)
    <div style="border:1.5px solid {{$bd}};border-radius:12px;padding:14px 16px;background:{{$bg}};
                border-left:4px solid {{$red}}">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
        <span style="font-weight:800;color:{{$red}};font-size:.95rem">{{$y['name']}}</span>
        <span style="font-size:.68rem;background:{{$bgHd}};color:{{$txtM}};padding:2px 8px;border-radius:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase">{{$y['type']}}</span>
        @if($y['house'])
        <span style="font-size:.72rem;color:{{$blue}};font-weight:700;margin-left:auto">H{{$y['house']}}</span>
        @endif
      </div>
      <div style="font-size:.82rem;color:{{$txtM}};line-height:1.55">{{$y['desc']}}</div>
    </div>
    @endforeach
  </div>
  @endif
  @endif
</div>

{{-- ══════════════════════════════════════ ASHTAKA VARGA TAB ══════════════════════════════════════ --}}
<div id="ks_av" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Ashtaka Varga — BPHS Bhinnashtakavarga</div>

  @php
    $av = $k['ashtakaVarga'];
    $signs = $av['signAbbr'];
    $pids  = $av['planets'];
    $bin   = $av['bhinnaAV'];
    $sarva = $av['sarva'];

    $avBarColor = fn($v) => match(true) {
      $v >= 6  => '#1e6e3e',
      $v >= 4  => '#2a5a8e',
      $v >= 2  => '#7a5a30',
      default  => '#b83232',
    };
  @endphp

  {{-- Planet-wise tables --}}
  @foreach($pids as $pid)
  @php $pts = $bin[$pid]; $total = array_sum($pts); @endphp
  <div style="margin-bottom:22px">
    <div style="font-size:.75rem;font-weight:800;color:{{$gold}};margin-bottom:6px">
      {{ ucfirst($pid) }} Bhinnashtakavarga (Total: {{$total}} pts)
    </div>
    <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:3px">
      @foreach($signs as $si => $sn)
      @php $v=$pts[$si]; @endphp
      <div style="text-align:center;border-radius:6px;padding:4px 2px;background:{{$bgHd}};border:1px solid {{$bd}}">
        <div style="font-size:.6rem;color:{{$txtL}};font-weight:700">{{$sn}}</div>
        <div style="font-size:1.05rem;font-weight:800;color:{{$avBarColor($v)}}">{{$v}}</div>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach

  {{-- Sarvashtakavarga --}}
  <div style="margin-bottom:12px">
    <div style="font-size:.75rem;font-weight:800;color:{{$gold}};margin-bottom:6px">
      Sarvashtakavarga (Total: {{array_sum($sarva)}} pts)
    </div>
    <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:3px">
      @foreach($signs as $si => $sn)
      @php $v=$sarva[$si]; @endphp
      <div style="text-align:center;border-radius:6px;padding:5px 2px;background:{{$bgHd}};border:2px solid {{$avBarColor($v)}}">
        <div style="font-size:.6rem;color:{{$txtL}};font-weight:700">{{$sn}}</div>
        <div style="font-size:1.15rem;font-weight:900;color:{{$avBarColor($v)}}">{{$v}}</div>
      </div>
      @endforeach
    </div>
    <div style="font-size:.72rem;color:{{$txtL}};margin-top:6px">28+ = excellent · 25–27 = good · 22–24 = average · below 22 = weak</div>
  </div>
</div>

{{-- ══════════════════════════════════════ BHAVA BALA TAB ══════════════════════════════════════ --}}
<div id="ks_bb" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Bhava Bala — House Strength</div>
  <div style="overflow-x:auto">
  <table style="border-collapse:collapse;width:100%;min-width:700px">
    <thead>
      <tr>
        <th style="{{$th}}">House</th>
        <th style="{{$th}}">Sign</th>
        <th style="{{$th}}">Lord</th>
        <th style="{{$th}}">Bhavadhipati Bala</th>
        <th style="{{$th}}">Dig Bala</th>
        <th style="{{$th}}">Drishti Bala</th>
        <th style="{{$th}}">Total (Shashtiam.)</th>
        <th style="{{$th}}">Rupas</th>
        <th style="{{$th}}">Grade</th>
      </tr>
    </thead>
    <tbody>
      @foreach($k['bhavaBala'] as $bb)
      @php
        $gradeColor = match($bb['grade']) {
          'Exceptional' => '#0a6a2a',
          'Strong'      => '#1e6e3e',
          'Average'     => '#7a5a30',
          'Weak'        => '#b86030',
          default       => $red,
        };
        $rowBg = $loop->index%2===0 ? "background:{$bg}" : "background:{$bgRow}";
        $ddCol = $bb['drishBala'] >= 0 ? $grn : $red;
      @endphp
      <tr style="{{$rowBg}}">
        <td style="{{$tdc}};font-weight:800;color:{{$gold}};font-size:.95rem">{{$bb['house']}}</td>
        <td style="{{$td}}">{{$bb['signName']}}</td>
        <td style="{{$td}}"><span style="margin-right:3px">{{$bb['lordsym']}}</span>{{$bb['lord']}}</td>
        <td style="{{$tdr}}">{{$bb['bdBala']}}</td>
        <td style="{{$tdr}}">{{$bb['digBala']}}</td>
        <td style="{{$tdr}};color:{{$ddCol}};font-weight:700">{{$bb['drishBala'] >= 0 ? '+'.$bb['drishBala'] : $bb['drishBala']}}</td>
        <td style="{{$tdr}};font-weight:800">{{$bb['total']}}</td>
        <td style="{{$tdr}};color:{{$blue}};font-weight:700">{{$bb['rupas']}}</td>
        <td style="{{$td}};color:{{$gradeColor}};font-weight:800">{{$bb['grade']}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="font-size:.72rem;color:{{$txtL}};margin-top:8px;line-height:1.6">
    Bhavadhipati Bala = lord's Shadbala × 10 · Dig Bala: Kendra=60, Panaphar=30, Apoklima=15 (Shashtiyamshas)
    · Drishti Bala: benefic aspects +15, malefic −15 (proportional to aspect fraction)
  </div>
</div>

{{-- ══════════════════════════════════════ DASHA TAB ══════════════════════════════════════ --}}
<div id="ks_dasha" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Vimshottari Dasha</div>
  {!! $dashaHtml !!}
</div>

{{-- ══════════════════════════════════════ SHADBALA TAB ══════════════════════════════════════ --}}
<div id="ks_shadbala" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Shadbala — Six-fold Planetary Strength</div>
  {!! $shadBalaHtml !!}
</div>

{{-- ══════════════════════════════════════ PANCHANGA TAB ══════════════════════════════════════ --}}
<div id="ks_pancha" class="k-sub" style="display:none">
  <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.6px;font-weight:800;color:{{$gold}};margin-bottom:10px">Panchangam — Five Limbs of Time</div>
  @php
    $items = [
      ['Tithi',    $pancha['tithi']??'—',    'Lunar day · emotional rhythms, deity worship timing'],
      ['Vara',     $pancha['vara']??'—',     'Weekday · planetary ruler of the day'],
      ['Nakshatra',$pancha['nakshatra']??'—',"Moon's asterism · activity suitability"],
      ['Yoga',     $pancha['yoga']??'—',     'Sun–Moon sum nakshatra · auspiciousness quality'],
      ['Karana',   $pancha['karana']??'—',   'Half-tithi · muhurta refinement'],
    ];
  @endphp
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
    @foreach($items as [$lbl,$val,$desc])
    <div style="border:1.5px solid {{$bd}};border-radius:12px;padding:14px 16px;background:{{$bgRow}}">
      <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.2px;font-weight:800;color:{{$gold}};margin-bottom:4px">{{$lbl}}</div>
      <div style="font-size:1.1rem;font-weight:800;color:{{$txt}};margin-bottom:3px">{{$val}}</div>
      <div style="font-size:.72rem;color:{{$txtL}};line-height:1.5">{{$desc}}</div>
    </div>
    @endforeach
  </div>

  @if(!empty($pancha['sunRise']) || !empty($pancha['sunSet']))
  <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
    @foreach([['☀ Sunrise',$pancha['sunRise']??'—'],['🌅 Sunset',$pancha['sunSet']??'—'],['Paksha',$pancha['paksha']??'—'],['Moon in',$pancha['moonSign']??'—']] as [$l,$v])
    <div style="border:1px solid {{$bd}};border-radius:8px;padding:8px 14px;background:{{$bg}};font-size:.84rem">
      <span style="color:{{$txtL}};margin-right:6px">{{$l}}</span>
      <strong style="color:{{$txt}}">{{$v}}</strong>
    </div>
    @endforeach
  </div>
  @endif
</div>

</div>{{-- end kundali panel --}}

