  {{-- ═══════════ PANCHANGA PANEL ═══════════ --}}
  <div id="tithiPanel" style="display:none">
    <div class="sec-lbl">🌙 Panchanga — Five Angas of the Vedic Day</div>

    {{-- 3-Mode Buttons --}}
    <div class="tithi-mode-bar">
      <button class="tmb active" id="tmb_sunrise" onclick="switchMode('sunrise')">
        <span class="tmb-icon">🌅</span>
        <span class="tmb-lbl">Sunrise</span>
        <span class="tmb-time" id="t_riseTime">—</span>
        <span class="tmb-sub" id="t_riseSub">—</span>
      </button>
      <button class="tmb" id="tmb_now" onclick="switchMode('now')">
        <span class="tmb-icon">🕐</span>
        <span class="tmb-lbl">Input Time</span>
        <span class="tmb-time" id="t_nowTime">—</span>
        <span class="tmb-sub" id="t_nowSub">—</span>
      </button>
      <button class="tmb" id="tmb_sunset" onclick="switchMode('sunset')">
        <span class="tmb-icon">🌇</span>
        <span class="tmb-lbl">Sunset</span>
        <span class="tmb-time" id="t_setTime">—</span>
        <span class="tmb-sub" id="t_setSub">—</span>
      </button>
    </div>

    {{-- Tithi Strip --}}
    <div class="strip tithi-strip">
      <div class="strip-sym">🌙</div>
      <div><h2 id="t_strip">—</h2><p id="t_stripSub">—</p></div>
      <div class="strip-lon"><div class="big" id="t_elong">—</div><div class="sm">Moon–Sun Elongation</div></div>
    </div>

    {{-- Lunar arc SVG --}}
    <div class="lunar-arc">
      <svg id="lunarSvg" viewBox="0 0 320 80" style="width:100%;max-width:420px;display:block;margin:0 auto">
        <path d="M 20 60 Q 160 -20 300 60" fill="none" stroke="#dac8f0" stroke-width="6" stroke-linecap="round"/>
        <path id="lunarFill" d="M 20 60 Q 160 -20 300 60" fill="none" stroke="#8040c0" stroke-width="6" stroke-linecap="round" stroke-dasharray="320" stroke-dashoffset="320"/>
        <circle id="lunarDot" cx="20" cy="60" r="7" fill="#5a3080"/>
        <text x="20" y="76" text-anchor="middle" font-size="9" fill="#9070b8" font-family="DM Sans,sans-serif">New</text>
        <text x="300" y="76" text-anchor="middle" font-size="9" fill="#9070b8" font-family="DM Sans,sans-serif">Full</text>
        <text id="lunarLbl" x="160" y="76" text-anchor="middle" font-size="9" fill="#9070b8" font-family="DM Sans,sans-serif">—</text>
      </svg>
    </div>

    {{-- ANGA 1: TITHI --}}
    <div class="anga-badge tithi"><div class="ab-num">1</div><div class="ab-body"><div class="ab-name">Tithi — Lunar Day</div><div class="ab-sub">Anga 1 · 12° Moon–Sun elongation each</div></div><div class="ab-sym">🌙</div></div>
    <div class="rg">
      <div class="tile tithi-tile"><div class="tl">Tithi Name</div><div class="tv" id="t_name">—</div><div class="ts" id="t_paksha">—</div></div>
      <div class="tile tithi-tile"><div class="tl">Tithi No. · Paksha</div><div class="tv" id="t_num">—</div><div class="ts">of 15 in this Paksha</div></div>
      <div class="tile gold"><div class="tl">Tithi Lord · Deity</div><div class="tv" id="t_lord">—</div><div class="ts" id="t_deity">—</div></div>
      <div class="tile tithi-tile"><div class="tl">Tithi Nature</div><div class="tv" id="t_nature">—</div><div class="ts" id="t_prog">—</div></div>
    </div>

    <div class="prog-wrap tithi-prog">
      <div class="tl">Tithi Progress</div>
      <div class="prog-track"><div class="prog-fill" id="t_bar" style="width:0%"></div></div>
      <div class="prog-padas"><span>☽ Start (0°)</span><span>6° midpoint</span><span>End (12°) ☀</span></div>
    </div> 

    {{-- ANGA 2: VARA --}}
    <div class="anga-badge vara"><div class="ab-num">2</div><div class="ab-body"><div class="ab-name">Vara — Vedic Weekday</div><div class="ab-sub">Anga 2 · Planetary ruler of the day</div></div><div class="ab-sym">☀</div></div>
    <div class="strip vara-strip">
      <div class="strip-sym" id="v_sym">☀</div>
      <div><h2 id="v_strip">—</h2><p id="v_stripSub">—</p></div>
      <div class="strip-lon"><div class="big" id="v_day">—</div><div class="sm">Vedic Weekday</div></div>
    </div>
    <div class="arc-wrap">
      <svg id="varaArc" viewBox="0 0 340 100" style="max-width:340px">
        <path d="M 20 90 Q 170 -10 320 90" fill="none" stroke="#f0d8b0" stroke-width="8" stroke-linecap="round"/>
        <path id="varaFill" d="M 20 90 Q 170 -10 320 90" fill="none" stroke="#d4760a" stroke-width="8" stroke-linecap="round" stroke-dasharray="340" stroke-dashoffset="340"/>
        <circle id="varaDot" cx="20" cy="90" r="8" fill="#c56408"/>
        <text x="20" y="98" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Sun</text>
        <text x="67" y="70" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Mon</text>
        <text x="122" y="50" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Tue</text>
        <text x="170" y="42" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Wed</text>
        <text x="218" y="50" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Thu</text>
        <text x="273" y="70" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Fri</text>
        <text x="320" y="98" text-anchor="middle" font-size="7" fill="#b08040" font-family="DM Sans,sans-serif">Sat</text>
        <text id="varaLbl" x="170" y="95" text-anchor="middle" font-size="10" fill="#d4760a" font-weight="700" font-family="DM Sans,sans-serif">—</text>
      </svg>
    </div>
    <div class="rg">
      <div class="tile vara-tile"><div class="tl">Vara Name · Day</div><div class="tv" id="v_name">—</div><div class="ts" id="v_en">—</div></div>
      <div class="tile vara-tile"><div class="tl">Weekday Lord</div><div class="tv" id="v_lord">—</div><div class="ts" id="v_nature">—</div></div>
      <div class="tile vara-tile"><div class="tl">Hora Lord (1st Hour)</div><div class="tv" id="v_hora">—</div><div class="ts">Planetary hour at sunrise</div></div>
      <div class="tile vara-tile"><div class="tl">Classification</div><div class="tv" id="v_class">—</div><div class="ts" id="v_cNote">—</div></div>
      <div class="tile gold"><div class="tl">Ruling Deity</div><div class="tv" id="v_deity">—</div><div class="ts" id="v_dNote">—</div></div>
      <div class="tile vara-tile"><div class="tl">Auspiciousness</div><div class="tv" id="v_ausp">—</div><div class="ts" id="v_act">—</div></div>
    </div>
    <div class="info-vara"><strong id="v_infoTitle">☀ Vara</strong> — <span id="v_info">—</span></div>

    {{-- ANGA 3: NAKSHATRA --}}
    <div class="anga-badge naksh"><div class="ab-num">3</div><div class="ab-body"><div class="ab-name">Nakshatra — Lunar Mansion</div><div class="ab-sub">Anga 3 · 27 mansions, 13°20′ each</div></div><div class="ab-sym">✦</div></div>
    <div class="arc-wrap">
      <svg id="nakArc" viewBox="0 0 340 105" style="max-width:340px">
        <path d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#c8e0f0" stroke-width="8" stroke-linecap="round"/>
        <path id="nakFill" d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#1d6aa0" stroke-width="8" stroke-linecap="round" stroke-dasharray="340" stroke-dashoffset="340"/>
        <circle id="nakDot" cx="20" cy="95" r="7" fill="#1d4e6f"/>
        <text x="20" y="102" text-anchor="middle" font-size="7" fill="#4a80a0" font-family="DM Sans,sans-serif">Ash</text>
        <text x="170" y="20" text-anchor="middle" font-size="7" fill="#4a80a0" font-family="DM Sans,sans-serif">Chitra</text>
        <text x="320" y="102" text-anchor="middle" font-size="7" fill="#4a80a0" font-family="DM Sans,sans-serif">Rev</text>
        <text id="nakLbl" x="170" y="99" text-anchor="middle" font-size="10" fill="#1d6aa0" font-weight="700" font-family="DM Sans,sans-serif">—</text>
      </svg>
    </div>
    <div class="strip moon-strip"><div class="strip-sym">✦</div><div><h2 id="n_strip">—</h2><p id="n_stripSub">—</p></div><div class="strip-lon"><div class="big" id="n_num">—</div><div class="sm">of 27 Nakshatras</div></div></div>
    <div class="rg">
      <div class="tile nak-tile"><div class="tl">Nakshatra Name</div><div class="tv" id="n_name">—</div><div class="ts" id="n_num2">—</div></div>
      <div class="tile nak-tile"><div class="tl">Nakshatra Lord</div><div class="tv" id="n_lord">—</div><div class="ts" id="n_deity">—</div></div>
      <div class="tile gold"><div class="tl">Pada · Progress</div><div class="tv" id="n_pada">—</div><div class="ts" id="n_prog">—</div></div>
      <div class="tile nak-tile"><div class="tl">Gana · Nature</div><div class="tv" id="n_gana">—</div><div class="ts" id="n_ganaSub">—</div></div>
      <div class="tile nak-tile"><div class="tl">Yoni · Symbol</div><div class="tv" id="n_yoni">—</div><div class="ts">Symbolic animal</div></div>
      <div class="tile gold"><div class="tl">Nadi · Dosha</div><div class="tv" id="n_nadi">—</div><div class="ts" id="n_nadiSub">—</div></div>
      <div class="tile nak-tile"><div class="tl">Tattva · Element</div><div class="tv" id="n_tattva">—</div><div class="ts">Elemental quality</div></div>
      <div class="tile nak-tile"><div class="tl">Quality</div><div class="tv" id="n_quality">—</div><div class="ts">Muhurta classification</div></div>
    </div>
    <div class="prog-wrap nak-prog">
      <div class="tl">Nakshatra Progress (13°20′)</div>
      <div class="prog-track"><div class="prog-fill" id="n_bar" style="width:0%"></div></div>
      <div class="prog-padas"><span>Pada 1</span><span>Pada 2</span><span>Pada 3</span><span>Pada 4</span></div>
    </div>
    <div class="info-nak"><strong id="n_infoTitle">✦ Nakshatra</strong> — <span id="n_info">—</span></div>

    {{-- ANGA 4: YOGA --}}
    <div class="anga-badge yoga"><div class="ab-num">4</div><div class="ab-body"><div class="ab-name">Yoga — Luni-Solar Combination</div><div class="ab-sub">Anga 4 · Sun + Moon sum, 27 divisions</div></div><div class="ab-sym">✧</div></div>
    <div class="arc-wrap">
      <svg id="yogaArc" viewBox="0 0 340 105" style="max-width:340px">
        <path d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#d8c8f0" stroke-width="8" stroke-linecap="round"/>
        <path id="yogaFill" d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#6040b0" stroke-width="8" stroke-linecap="round" stroke-dasharray="340" stroke-dashoffset="340"/>
        <circle id="yogaDot" cx="20" cy="95" r="7" fill="#4a2080"/>
        <text x="20" y="102" text-anchor="middle" font-size="7" fill="#8060c0" font-family="DM Sans,sans-serif">Vishk</text>
        <text x="170" y="20" text-anchor="middle" font-size="7" fill="#8060c0" font-family="DM Sans,sans-serif">Siddhi</text>
        <text x="320" y="102" text-anchor="middle" font-size="7" fill="#8060c0" font-family="DM Sans,sans-serif">Vaidh</text>
        <text id="yogaLbl" x="170" y="99" text-anchor="middle" font-size="10" fill="#6040b0" font-weight="700" font-family="DM Sans,sans-serif">—</text>
      </svg>
    </div>
    <div class="strip" style="background:linear-gradient(120deg,#5a3080,#2a0e50)"><div class="strip-sym">✧</div><div><h2 id="y_strip">—</h2><p id="y_stripSub">—</p></div><div class="strip-lon"><div class="big" id="y_num">—</div><div class="sm">of 27 Yogas</div></div></div>
    <div class="rg">
      <div class="tile yoga-tile"><div class="tl">Yoga Name</div><div class="tv" id="y_name">—</div><div class="ts" id="y_num2">—</div></div>
      <div class="tile yoga-tile"><div class="tl">Yoga Nature</div><div class="tv" id="y_nature">—</div><div class="ts" id="y_lord">—</div></div>
      <div class="tile gold"><div class="tl">Yoga Deity</div><div class="tv" id="y_deity">—</div><div class="ts">Presiding deity</div></div>
      <div class="tile yoga-tile"><div class="tl">Classification</div><div class="tv" id="y_class">—</div><div class="ts" id="y_cSub">—</div></div>
      <div class="tile yoga-tile"><div class="tl">Sun + Moon Sum</div><div class="tv" id="y_sum">—</div><div class="ts">Sidereal longitudes</div></div>
      <div class="tile gold"><div class="tl">Yoga Progress</div><div class="tv" id="y_prog">—</div></div>
    </div>
    <div class="prog-wrap yoga-prog">
      <div class="tl">Yoga Progress (13°20′)</div>
      <div class="prog-track"><div class="prog-fill" id="y_bar" style="width:0%"></div></div>
      <div class="prog-padas"><span>▸ Start</span><span id="y_pct">0%</span><span>End ◂</span></div>
    </div>
    <div class="info-yoga"><strong id="y_infoTitle">✧ Yoga</strong> — <span id="y_info">—</span></div>

    {{-- ANGA 5: KARANA --}}
    <div class="anga-badge karana"><div class="ab-num">5</div><div class="ab-body"><div class="ab-name">Karana — Half Tithi</div><div class="ab-sub">Anga 5 · 6° per Karana, 60 per lunar month</div></div><div class="ab-sym">⬡</div></div>
    <div class="arc-wrap">
      <svg id="karanaArc" viewBox="0 0 340 105" style="max-width:340px">
        <path d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#f0e0b0" stroke-width="8" stroke-linecap="round"/>
        <path id="karanaFill" d="M 20 95 Q 170 -5 320 95" fill="none" stroke="#c07020" stroke-width="8" stroke-linecap="round" stroke-dasharray="340" stroke-dashoffset="340"/>
        <circle id="karanaDot" cx="20" cy="95" r="7" fill="#9a5010"/>
        <text x="20" y="102" text-anchor="middle" font-size="7" fill="#a07030" font-family="DM Sans,sans-serif">K1</text>
        <text x="170" y="20" text-anchor="middle" font-size="7" fill="#a07030" font-family="DM Sans,sans-serif">K30</text>
        <text x="320" y="102" text-anchor="middle" font-size="7" fill="#a07030" font-family="DM Sans,sans-serif">K60</text>
        <text id="karanaLbl" x="170" y="99" text-anchor="middle" font-size="10" fill="#c07020" font-weight="700" font-family="DM Sans,sans-serif">—</text>
      </svg>
    </div>
    <div class="strip" style="background:linear-gradient(120deg,#7a5a10,#3e2800)"><div class="strip-sym">⬡</div><div><h2 id="k_strip">—</h2><p id="k_stripSub">—</p></div><div class="strip-lon"><div class="big" id="k_slot">—</div><div class="sm">of 60 Karanas</div></div></div>
    <div class="rg">
      <div class="tile karana-tile"><div class="tl">Karana Name · Type</div><div class="tv" id="k_name">—</div><div class="ts" id="k_type">—</div></div>
      <div class="tile karana-tile"><div class="tl">Slot · Half</div><div class="tv" id="k_slotEl">—</div><div class="ts" id="k_prog">—</div></div>
      <div class="tile gold"><div class="tl">Karana Lord · Nature</div><div class="tv" id="k_lord">—</div><div class="ts" id="k_nature">—</div></div>
      <div class="tile karana-tile"><div class="tl">Ruling Deity</div><div class="tv" id="k_deity">—</div></div>
      <div class="tile karana-tile"><div class="tl">Favourable For</div><div class="tv" id="k_favour">—</div></div>
      <div class="tile gold"><div class="tl">Classification</div><div class="tv" id="k_class">—</div></div>
    </div>
    <div class="prog-wrap karana-prog">
      <div class="tl">Karana Progress (6° per Karana)</div>
      <div class="prog-track"><div class="prog-fill" id="k_bar" style="width:0%"></div></div>
      <div class="prog-padas"><span>▸ Start</span><span>50%</span><span>End ◂</span></div>
    </div>

    {{-- Panchanga Summary --}}
    <div class="sep">◈ Panchanga — Five Limbs Summary</div>
    <div class="pancha-sum">
      <div class="pa"><div class="pa-num">① Tithi</div><div style="font-size:1.4rem;line-height:1;margin:2px 0">🌙</div><div class="pa-lbl">Lunar Day</div><div class="pa-val" id="ps_tithi">—</div></div>
      <div class="pa"><div class="pa-num">② Vara</div><div style="font-size:1.4rem;line-height:1;margin:2px 0">☀</div><div class="pa-lbl">Weekday</div><div class="pa-val" id="ps_vara">—</div></div>
      <div class="pa"><div class="pa-num">③ Nakshatra</div><div style="font-size:1.4rem;line-height:1;margin:2px 0">✦</div><div class="pa-lbl">Moon Mansion</div><div class="pa-val" id="ps_nak">—</div></div>
      <div class="pa"><div class="pa-num">④ Yoga</div><div style="font-size:1.4rem;line-height:1;margin:2px 0">✧</div><div class="pa-lbl">Luni-Solar</div><div class="pa-val" id="ps_yoga">—</div></div>
      <div class="pa"><div class="pa-num">⑤ Karana</div><div style="font-size:1.4rem;line-height:1;margin:2px 0">⬡</div><div class="pa-lbl">Half-Tithi</div><div class="pa-val" id="ps_karana">—</div></div>
    </div>
    <div class="sun-row">
      <span class="sri rise"><span class="sri-icon">🌅</span><span class="sri-lbl">Sunrise</span><span class="sri-val" id="ps_rise">—</span></span>
      <span class="sri set"><span class="sri-icon">🌇</span><span class="sri-lbl">Sunset</span><span class="sri-val" id="ps_set">—</span></span>
      <span class="sri day"><span class="sri-icon">⏱</span><span class="sri-lbl">Day Length</span><span class="sri-val" id="ps_day">—</span></span>
    </div>
    <div class="info-dark">
      <strong>🌙 Tithi</strong> — 30 lunar days, 12° Moon–Sun elongation each. Tithi at sunrise governs the Vedic calendar day.<br><br>
      <strong>☀ Vara</strong> — the weekday lord influences the energy of the day.<br><br>
      <strong>✦ Nakshatra</strong> — the Moon's 27-fold division (13°20′ each). Gana, Yoni, Nadi and Tattva shape its quality.<br><br>
      <strong>✧ Yoga</strong> — computed from Sun + Moon sidereal longitudes ÷ 27 equal parts.<br><br>
      <strong>⬡ Karana</strong> = half a tithi = 6°. <strong>Vishti (Bhadra)</strong> karana is inauspicious for new beginnings.
    </div>
  </div>