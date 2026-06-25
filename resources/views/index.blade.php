@extends('layouts.app')

@section('content')

{{-- ── HERO ── --}}
<div class="hero">
  <div style="display:flex;gap:6px;flex-shrink:0">
    <div class="hero-moon">☽</div>
    <div class="hero-sun">☀</div>
  </div>
  <div>
    <h1>Akashology<em>  Calculator</em></h1>
    <p>9 Planets · Lagna · Ascendant · Descendant </p>
  </div>
</div>

<!-- INPUT CARD -->
<div class="card">
  <div class="sec-lbl">Location &amp; Time</div>
  <div class="input-panel">

    <!-- State -->
    <div class="ig">
      <label>State</label>
      <select id="stateSelect" onchange="onStateChange()">
        <option value="">— Select State —</option>
        <option value="AN">Andaman &amp; Nicobar</option>
        <option value="AP">Andhra Pradesh</option>
        <option value="AR">Arunachal Pradesh</option>
        <option value="AS">Assam</option>
        <option value="BR">Bihar</option>
        <option value="CH">Chandigarh</option>
        <option value="CG">Chhattisgarh</option>
        <option value="DD">Dadra &amp; Nagar Haveli / Daman &amp; Diu</option>
        <option value="DL">Delhi</option>
        <option value="GA">Goa</option>
        <option value="GJ">Gujarat</option>
        <option value="HR">Haryana</option>
        <option value="HP">Himachal Pradesh</option>
        <option value="JK">Jammu &amp; Kashmir</option>
        <option value="JH">Jharkhand</option>
        <option value="KA">Karnataka</option>
        <option value="KL">Kerala</option>
        <option value="LA">Ladakh</option>
        <option value="LD">Lakshadweep</option>
        <option value="MP">Madhya Pradesh</option>
        <option value="MH">Maharashtra</option>
        <option value="MN">Manipur</option>
        <option value="ML">Meghalaya</option>
        <option value="MZ">Mizoram</option>
        <option value="NL">Nagaland</option>
        <option value="OD">Odisha</option>
        <option value="PY">Puducherry</option>
        <option value="PB">Punjab</option>
        <option value="RJ">Rajasthan</option>
        <option value="SK">Sikkim</option>
        <option value="TN">Tamil Nadu</option>
        <option value="TG">Telangana</option>
        <option value="TR">Tripura</option>
        <option value="UP">Uttar Pradesh</option>
        <option value="UK">Uttarakhand</option>
        <option value="WB">West Bengal</option>
      </select>
    </div>

    <!-- City -->
    <div class="ig">
      <label>City</label>
      <select id="citySelect" disabled onchange="onCitySelect()">
        <option value="">— Select State first —</option>
      </select>
    </div>

    <!-- Date -->
    <div class="ig">
      <label>Date (DD/MM/YYYY)</label>
      <input type="text" id="dateDisplay" placeholder="12/05/2026" maxlength="10"
     oninput="syncDateFromDisplay()" style="font-family:'DM Sans',sans-serif;font-size:.92rem;color:var(--text);letter-spacing:0"/>
      <input type="hidden" id="dateInput"/>
    </div>

    <!-- Time -->
    <div class="ig">
      <label>Local Time</label>
      <input type="time" id="timeInput" step="60"/>
    </div>

    <!-- UTC Offset -->
    <div class="ig">
      <label>UTC Offset (hrs)</label>
      <input type="number" id="utcOffset" step="0.5" placeholder="+5.5"/>
    </div>

    <!-- Hidden lat/lon (required by JS) -->
    <input type="hidden" id="lat"/>
    <input type="hidden" id="lon"/>
    <input type="hidden" id="cityInput"/>
    <input type="hidden" id="cityStatus"/>

    <!-- Coordinates display row -->
    <div class="coords-display" id="coordsRow" style="display:none">
      <div class="coord-pill">
        <span>Lat</span>
        <code id="latDisplay">—</code>
      </div>
      <div class="coord-pill">
        <span>Lon</span>
        <code id="lonDisplay">—</code>
      </div>
      <div class="coord-pill">
        <span>TZ</span>
        <code id="tzDisplay">IST +5:30</code>
      </div>
      <div class="geo-status ok" id="geoStatusMsg"></div>
    </div>

    <!-- Calculate button -->
    <button class="btn-calc" id="calcBtn" onclick="doCalculate()">
      ✦ &nbsp;Calculate All Planet Positions
    </button>
  </div>
  <div class="err-pill" id="errPill"></div>
</div>

{{-- ── RESULT CARD ── --}}
<div class="card" id="resultCard" style="display:none">

  {{-- Tab Navigation --}}
  <div class="tabs">

    <div class="tab-row tab-row-views" style="margin-bottom:6px">
      <span class="tab-lbl">Quick</span>
      <div class="tab-btns">
        <button class="tab-btn" id="tab_today" onclick="showTab('today')" style="padding:7px 22px;font-size:.85rem;font-weight:700"><span class="tab-sym">🌅</span> Today<span class="tab-k">T</span></button>
        <button class="tab-btn" id="tab_kundali" onclick="showTab('kundali')"><span class="tab-sym">🔯</span> Kundali<span class="tab-k">N</span></button>
        <button class="tab-btn" id="tab_festival" onclick="showTab('festival')"><span class="tab-sym">🪔</span> Panchanga Calendar<span class="tab-k">F</span></button>
        <button class="tab-btn" id="tab_muhrat" onclick="showTab('muhrat')"><span class="tab-sym">✦</span> Muhrat<span class="tab-k">M</span></button>
      </div>
    </div>
    <div class="tab-row tab-row-views">
      <span class="tab-lbl">Views</span>
      <div class="tab-btns">
        <button class="tab-btn" id="tab_chart"    onclick="showTab('chart')"    ><span class="tab-sym">◈</span> Chart<span class="tab-k">C</span></button>
        <button class="tab-btn" id="tab_varga"    onclick="showTab('varga')"    ><span class="tab-sym">◈</span> Vargas<span class="tab-k">G</span></button>
        <button class="tab-btn" id="tab_lagna"    onclick="showTab('lagna')"    ><span class="tab-sym">⬆</span> Lagna<span class="tab-k">L</span></button>
        <button class="tab-btn" id="tab_tithi"    onclick="showTab('tithi')"    ><span class="tab-sym">🌙</span> Panchanga<span class="tab-k">T</span></button>
        <button class="tab-btn" id="tab_masa"     onclick="showTab('masa')"     ><span class="tab-sym">📅</span> Masa<span class="tab-k">P</span></button>
        <button class="tab-btn" id="tab_dasha"    onclick="showTab('dasha')"    ><span class="tab-sym">⏳</span> Dasha<span class="tab-k">D</span></button>
        <button class="tab-btn" id="tab_shadbala" onclick="showTab('shadbala')" ><span class="tab-sym">⚖</span> Shadbala<span class="tab-k">B</span></button>
        <button class="tab-btn" id="tab_tarabal"  onclick="showTab('tarabal')"  ><span class="tab-sym">⭐</span> Tarabal<span class="tab-k">T</span></button>
      </div>
    </div>
    <div class="tab-row tab-row-planets">
      <span class="tab-lbl">Planets</span>
      <div class="tab-btns">
        <button class="tab-btn" id="tab_sun"     onclick="showTab('sun')"     ><span class="tab-sym">☀</span> Sun<span class="tab-k">S</span></button>
        <button class="tab-btn" id="tab_moon"    onclick="showTab('moon')"    ><span class="tab-sym">☽</span> Moon<span class="tab-k">M</span></button>
        <button class="tab-btn" id="tab_mercury" onclick="showTab('mercury')" ><span class="tab-sym">☿</span> Mercury<span class="tab-k">E</span></button>
        <button class="tab-btn" id="tab_venus"   onclick="showTab('venus')"   ><span class="tab-sym">♀</span> Venus<span class="tab-k">V</span></button>
        <button class="tab-btn" id="tab_mars"    onclick="showTab('mars')"    ><span class="tab-sym">♂</span> Mars<span class="tab-k">A</span></button>
        <button class="tab-btn" id="tab_jupiter" onclick="showTab('jupiter')" ><span class="tab-sym">♃</span> Jupiter<span class="tab-k">J</span></button>
        <button class="tab-btn" id="tab_saturn"  onclick="showTab('saturn')"  ><span class="tab-sym">♄</span> Saturn<span class="tab-k">K</span></button>
        <button class="tab-btn" id="tab_rahu"    onclick="showTab('rahu')"    ><span class="tab-sym">☊</span> Rahu<span class="tab-k">R</span></button>
        <button class="tab-btn" id="tab_ketu"    onclick="showTab('ketu')"    ><span class="tab-sym">☋</span> Ketu<span class="tab-k">U</span></button>
        <button class="tab-btn" id="tab_gochar"  onclick="showTab('gochar')"  ><span class="tab-sym">♆</span> Gochar fal<span class="tab-k">G</span></button>
      </div>
    </div>
  </div>

  {{-- Planet quick-info bar --}}
  <div class="pib" id="pib" style="display:none">
    <span id="pib_sym" style="font-size:1.1rem;line-height:1"></span>
    <span class="pib-name" id="pib_name"></span>
    <span class="pib-sep">·</span>
    <span class="pib-sign" id="pib_sign"></span>
    <span class="pib-sep">·</span>
    <span class="pib-nak" id="pib_nak"></span>
    <span class="pib-retro" id="pib_retro"></span>
    <span class="pib-deg" id="pib_deg"></span>
  </div>

  {{-- ═══════════ KUNDALI PANEL ═══════════ --}}
  <div id="kundaliPanel" style="display:none">
    <div class="sec-lbl">🔯 Kundali — Complete Chart Analysis</div>
    <div id="kundaliContent">
      <div style="text-align:center;padding:48px;color:#9a7a4a;font-style:italic;font-size:.9rem">
        Calculate your chart to load full Kundali analysis
      </div>
    </div>
  </div>

  {{-- ═══════════ TODAY PANEL ═══════════ --}}
  <div id="todayPanel" style="display:none">
    <div class="sec-lbl">🌅 Today at a Glance</div>
    <div id="todayContent">
      <div class="td-loading" style="justify-content:center;min-height:120px;align-items:center;flex-direction:column;gap:12px">
        <span class="td-spinner" style="font-size:2.2rem">🪷</span>
        <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:var(--text-mid)">Today's Panchang Calculation loading…</span>
      </div>
    </div>
  </div>

  {{-- ═══════════ CHART PANEL ═══════════ --}}
  <div id="chartPanel" style="display:none">
    <div class="sec-lbl">◈ Kundali — Birth Chart · Navamsha · Dashamsha</div>
    <div id="chartSvgWrap"></div>
  </div>

  <div id="vargaPanel" style="display:none">
    <div class="sec-lbl">◈ Shodashvarga — 20 Divisional Charts</div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px;background:#f5f0e8;
               border-radius:14px;padding:10px 14px;border:1.5px solid #ddd0c0;align-items:center">
      <span style="font-size:.6rem;text-transform:uppercase;letter-spacing:1px;font-weight:800;
                   color:#7a5a30;margin-right:4px">Charts</span>
      <button class="varga-btn active" onclick="filterVarga('all',this)">All 20</button>
      <button class="varga-btn" onclick="filterVarga('D1',this)">D1 Rashi</button>
      <button class="varga-btn" onclick="filterVarga('D9',this)">D9 Navamsha</button>
      <button class="varga-btn" onclick="filterVarga('D10',this)">D10 Dashamsha</button>
      <button class="varga-btn" onclick="filterVarga('D3',this)">D3 Drekkana</button>
      <button class="varga-btn" onclick="filterVarga('D7',this)">D7 Saptamamsha</button>
      <button class="varga-btn" onclick="filterVarga('D12',this)">D12 Dwadashamsha</button>
      <button class="varga-btn" onclick="filterVarga('D30',this)">D30 Trimsha</button>
      <button class="varga-btn" onclick="filterVarga('D60',this)">D60 Shashtiamsha</button>
    </div>
    <div class="varga-grid-wrap" id="vargaGridWrap">
      <div style="text-align:center;padding:48px;color:#5a6a8a;font-style:italic;font-size:.9rem">
        ✦ Calculate your chart above to generate all divisional charts
      </div>
    </div>
  </div>

  {{-- ═══════════ LAGNA PANEL ═══════════ --}}
  <div id="lagnaPanel" style="display:none">
    <div class="sec-lbl">⬆ Ascendant (Lagna) &amp; Angles</div>
    <div class="strip lagna-strip">
      <div class="strip-sym">⬆</div>
      <div><h2 id="l_ascStrip">—</h2><p id="l_ascStripSub">—</p></div>
      <div class="strip-lon"><div class="big" id="l_ascTrop">—</div><div class="sm">Tropical Ascendant</div></div>
    </div>
    <div class="angles-banner">
      <div class="angle-block asc-block">
        <div class="angle-icon">⬆</div>
        <div class="angle-head">Ascendant (Lagna)</div>
        <div class="angle-sub">Eastern Horizon · 1st House cusp</div>
        <div class="rg" style="margin-top:12px">
          <div class="tile"><div class="tl">Western Sign</div><div class="tv" id="l_ascWSign">—</div><div class="ts" id="l_ascWDeg">—</div></div>
          <div class="tile"><div class="tl">Vedic Sign</div><div class="tv" id="l_ascVSign">—</div><div class="ts" id="l_ascVDeg">—</div></div>
          <div class="tile gold"><div class="tl">Nakshatra</div><div class="tv" id="l_ascNak">—</div><div class="ts" id="l_ascNakLord">—</div></div>
          <div class="tile lagna-tile"><div class="tl">Sidereal Longitude</div><div class="tv" id="l_ascSider">—</div><div class="ts">Vedic Lagna degree</div></div>
        </div>
      </div>
      <div class="angle-div">⟺</div>
      <div class="angle-block desc-block">
        <div class="angle-icon">⬇</div>
        <div class="angle-head">Descendant</div>
        <div class="angle-sub">Western Horizon · 7th House · ASC + 180°</div>
        <div class="rg" style="margin-top:12px">
          <div class="tile"><div class="tl">Western Sign</div><div class="tv" id="l_descWSign">—</div><div class="ts" id="l_descWDeg">—</div></div>
          <div class="tile"><div class="tl">Vedic Sign</div><div class="tv" id="l_descVSign">—</div><div class="ts" id="l_descVDeg">—</div></div>
          <div class="tile gold"><div class="tl">Nakshatra</div><div class="tv" id="l_descNak">—</div><div class="ts" id="l_descNakLord">—</div></div>
          <div class="tile desc-tile"><div class="tl">Sidereal Longitude</div><div class="tv" id="l_descSider">—</div><div class="ts">Vedic 7th cusp</div></div>
        </div>
      </div>
    </div>
    <div class="sep">☀ Midheaven (MC) &amp; IC</div>
    <div class="rg">
      <div class="tile"><div class="tl">MC Western</div><div class="tv" id="l_mcWSign">—</div></div>
      <div class="tile"><div class="tl">MC Vedic</div><div class="tv" id="l_mcVSign">—</div></div>
      <div class="tile gold"><div class="tl">MC Nakshatra</div><div class="tv" id="l_mcNak">—</div></div>
      <div class="tile lagna-tile"><div class="tl">MC Longitude</div><div class="tv" id="l_mcTrop">—</div></div>
      <div class="tile"><div class="tl">IC Western</div><div class="tv" id="l_icWSign">—</div></div>
      <div class="tile"><div class="tl">IC Vedic</div><div class="tv" id="l_icVSign">—</div></div>
      <div class="tile gold"><div class="tl">IC Nakshatra</div><div class="tv" id="l_icNak">—</div></div>
      <div class="tile lagna-tile"><div class="tl">IC Longitude</div><div class="tv" id="l_icTrop">—</div></div>
    </div>
    <div class="info-box lagna-box"><strong>⬆ Ascendant (Lagna)</strong> is the ecliptic degree rising on the eastern horizon at birth. It defines the 1st House and entire house wheel. Calculated using Local Sidereal Time via Meeus RAMC formula.</div>
    <div class="fn" id="l_ayanNote">—</div>
  </div>

  {{-- ── Large panels kept as partials (100+ lines each) ── --}}
  @include('partials.planetary._panel_panchanga')
  @include('partials.planetary._panel_masa')
  @include('partials.festival._panel_muhrat')
  @include('partials.festival._panel_tarabal_murti')

  {{-- ═══════════ PLANET PANELS ═══════════ --}}
  @foreach([
    ['sun',     '☀','Sun',    'sun-strip',  'sun-tile',   'sun-progress',  ''],
    ['moon',    '☽','Moon',   'moon-strip', '',           '',              ''],
    ['mercury', '☿','Mercury','merc-strip', 'merc-tile',  'merc-progress', ''],
    ['venus',   '♀','Venus',  'venus-strip','venus-tile', 'venus-progress',''],
    ['mars',    '♂','Mars',   'mars-strip', 'mars-tile',  'mars-progress', ''],
    ['jupiter', '♃','Jupiter','jup-strip',  'jup-tile',   'jup-progress',  ''],
    ['saturn',  '♄','Saturn', 'sat-strip',  'sat-tile',   'sat-progress',  ''],
    ['rahu',    '☊','Rahu',   'rahu-strip', 'rahu-tile',  'rahu-progress', 'rahu'],
    ['ketu',    '☋','Ketu',   'ketu-strip', 'ketu-tile',  'ketu-progress', 'ketu'],
  ] as [$pid,$sym,$label,$strip,$tile,$prog,$info])
  <div id="{{ $pid }}Panel" style="display:none">
    <div class="sec-lbl">{{ $sym }} {{ $label }} Position</div>
    <div class="strip {{ $strip }}">
      <div class="strip-sym">{{ $sym }}</div>
      <div><h2 id="{{ $pid }}_stripTitle">—</h2><p id="{{ $pid }}_stripSub">—</p></div>
      <div class="strip-lon"><div class="big" id="{{ $pid }}_stripLon">—</div><div class="sm">Tropical ecliptic longitude</div></div>
    </div>
    <div class="rg">
      <div class="tile"><div class="tl">Vedic Sign (Sidereal)</div><div class="tv" id="{{ $pid }}_vSign">—</div><div class="ts" id="{{ $pid }}_vDeg">—</div></div>
      <div class="tile gold"><div class="tl">Nakshatra</div><div class="tv" id="{{ $pid }}_nakName">—</div><div class="ts" id="{{ $pid }}_nakPada">—</div></div>
      <div class="tile gold"><div class="tl">Nakshatra Lord · Deity</div><div class="tv" id="{{ $pid }}_nakLord">—</div><div class="ts" id="{{ $pid }}_nakDeity">—</div></div>
      <div class="tile {{ $tile }}"><div class="tl">Motion</div><div class="tv" id="{{ $pid }}_retro">—</div></div>
      <div class="tile house-tile"><div class="tl">House Position</div><div class="tv" id="{{ $pid }}_house">—</div><div class="ts" id="{{ $pid }}_houseSig">—</div></div>
      @if($pid === 'sun')
      <div class="tile sun-tile"><div class="tl">Declination</div><div class="tv" id="sunDec">—</div><div class="ts">N / S of equator</div></div>
      <div class="tile sun-tile"><div class="tl">Right Ascension</div><div class="tv" id="sunRA">—</div><div class="ts">Equatorial coord.</div></div>
      @endif
    </div>
    <div class="prog-wrap {{ $prog }}">
      <div class="tl">Progress through Nakshatra (Pada)</div>
      <div class="prog-track"><div class="prog-fill" id="{{ $pid }}_nakProg" style="width:0%"></div></div>
      <div class="prog-padas"><span>▸ Pada 1</span><span>Pada 2</span><span>Pada 3</span><span>Pada 4 ◂</span></div>
    </div>
    @if($pid === 'sun')
    <div class="sep">🌅 Sunrise &amp; Sunset</div>
    <div class="sun-banner">
      <div class="se-col rise"><div class="se-lbl">🌅 Sunrise</div><div class="se-time" id="sunriseTime">—</div><div class="se-sub">Local time</div></div>
      <div class="se-icon">☀</div>
      <div class="se-col set"><div class="se-lbl">🌇 Sunset</div><div class="se-time" id="sunsetTime">—</div><div class="se-sub">Local time</div></div>
    </div>
    <div class="rg" style="margin-top:0"><div class="tile daylength-tile"><div class="tl">Day Length</div><div class="tv" id="dayLength">—</div></div></div>
    @endif
    @if($info === 'rahu')
    <div class="info-box rahu-box"><strong>☊ Rahu</strong> is the Ascending Lunar Node — a shadow planet (Chaya Graha). It represents desire, karma, and worldly ambition. Always retrograde, completing one zodiac cycle in ~18.6 years.</div>
    @elseif($info === 'ketu')
    <div class="info-box ketu-box"><strong>☋ Ketu</strong> is the Descending Lunar Node — always exactly 180° opposite Rahu. It represents liberation (Moksha), past karma, and spiritual detachment. Always retrograde.</div>
    @endif
    <div class="fn" id="{{ $pid }}_ayanNote">—</div>
  </div>
  @endforeach

  @include('partials.festival._panel_festival')

</div>{{-- /resultCard --}}

@endsection

@section('scripts')
@include('partials.js._scripts')
@endsection
