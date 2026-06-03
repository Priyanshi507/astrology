{{-- ═══════════ CHART PANEL ═══════════ --}}
<div id="chartPanel" style="display:none">
    <div class="sec-lbl">◈ Kundali — Birth Chart · Navamsha · Dashamsha</div>
    <div id="chartSvgWrap"></div>
  </div>

<div id="vargaPanel" style="display:none">
  <div class="sec-lbl">◈ Shodashvarga — 20 Divisional Charts</div>

  {{-- Quick-jump buttons --}}
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
 
  {{-- Chart grid (server-rendered HTML injected here) --}}
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