  {{-- Tab Navigation --}}
  <div class="tabs">

<div class="tab-row tab-row-views" style="margin-bottom:6px">
  <span class="tab-lbl">Quick</span>
  <div class="tab-btns">
    <button class="tab-btn" id="tab_today" onclick="showTab('today')" style="padding:7px 22px;font-size:.85rem;font-weight:700"><span class="tab-sym">🌅</span> Today<span class="tab-k">T</span></button>
    <button class="tab-btn" id="tab_festival" onclick="showTab('festival')"><span class="tab-sym">🪔</span> पंचांग Calendar<span class="tab-k">F</span></button>
    <button class="tab-btn" id="tab_muhrat" onclick="showTab('muhrat')"><span class="tab-sym">✦</span> Muhrat<span class="tab-k">M</span></button>
  </div>
</div>
    <div class="tab-row tab-row-views">
      <span class="tab-lbl">Views</span>
      <div class="tab-btns">
        <button class="tab-btn" id="tab_chart"   onclick="showTab('chart')"  ><span class="tab-sym">◈</span> Chart<span class="tab-k">C</span></button>
        <button class="tab-btn" id="tab_varga" onclick="showTab('varga')"><span class="tab-sym">◈</span> Vargas<span class="tab-k">G</span></button>
        <button class="tab-btn" id="tab_lagna"   onclick="showTab('lagna')"  ><span class="tab-sym">⬆</span> Lagna<span class="tab-k">L</span></button>
        <button class="tab-btn" id="tab_tithi"   onclick="showTab('tithi')"  ><span class="tab-sym">🌙</span> Panchanga<span class="tab-k">T</span></button>
        <button class="tab-btn" id="tab_masa"    onclick="showTab('masa')"   ><span class="tab-sym">📅</span> Masa<span class="tab-k">P</span></button>
        <button class="tab-btn" id="tab_dasha"   onclick="showTab('dasha')"  ><span class="tab-sym">⏳</span> Dasha<span class="tab-k">D</span></button>
        <button class="tab-btn" id="tab_shadbala" onclick="showTab('shadbala')"><span class="tab-sym">⚖</span> Shadbala<span class="tab-k">B</span></button>
        <button class="tab-btn" id="tab_tarabal" onclick="showTab('tarabal')"><span class="tab-sym">⭐</span> तारबल<span class="tab-k">Y</span></button>
        
      </div>
    </div>
    <div class="tab-row tab-row-planets">
      <span class="tab-lbl">Planets</span>
      <div class="tab-btns">
        <button class="tab-btn" id="tab_sun"     onclick="showTab('sun')"    ><span class="tab-sym">☀</span> Sun<span class="tab-k">S</span></button>
        <button class="tab-btn" id="tab_moon"    onclick="showTab('moon')"   ><span class="tab-sym">☽</span> Moon<span class="tab-k">M</span></button>
        <button class="tab-btn" id="tab_mercury" onclick="showTab('mercury')"><span class="tab-sym">☿</span> Mercury<span class="tab-k">E</span></button>
        <button class="tab-btn" id="tab_venus"   onclick="showTab('venus')"  ><span class="tab-sym">♀</span> Venus<span class="tab-k">V</span></button>
        <button class="tab-btn" id="tab_mars"    onclick="showTab('mars')"   ><span class="tab-sym">♂</span> Mars<span class="tab-k">A</span></button>
        <button class="tab-btn" id="tab_jupiter" onclick="showTab('jupiter')"><span class="tab-sym">♃</span> Jupiter<span class="tab-k">J</span></button>
        <button class="tab-btn" id="tab_saturn"  onclick="showTab('saturn')" ><span class="tab-sym">♄</span> Saturn<span class="tab-k">K</span></button>
        <button class="tab-btn" id="tab_rahu"    onclick="showTab('rahu')"   ><span class="tab-sym">☊</span> Rahu<span class="tab-k">R</span></button>
        <button class="tab-btn" id="tab_ketu"    onclick="showTab('ketu')"   ><span class="tab-sym">☋</span> Ketu<span class="tab-k">U</span></button>
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