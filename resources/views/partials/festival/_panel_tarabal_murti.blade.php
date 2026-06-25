{{--
  _panel_tarabal_murti.blade.php — Tarabala & Murti Nirnaya
  Light parchment-saffron theme matching existing app aesthetic
  Tiro Devanagari Sanskrit + Crimson Pro fonts
  BPHS + Muhurta Chintamani mathematical basis
  v2 — increased font sizes for readability
--}}

<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit:ital@0;1&family=Crimson+Pro:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<style>
/* ═══════════════════════════════════════════════════════════
   TARABAL + MURTI PANEL — Vedic Parashara Style  v2 (larger fonts)
   ═══════════════════════════════════════════════════════════ */
#tarabalPanel {
  --bg0    : #fdf6ec;
  --bg1    : #f9edd8;
  --bg2    : #f0dfc0;
  --bg3    : #e8d0a8;
  --surf   : #fffbf5;
  --bdr    : rgba(168,112,40,.18);
  --bdr2   : rgba(168,112,40,.32);
  --gold   : #9a6b0a;
  --goldlt : #f5e6c0;
  --golddk : #6b4800;
  --saffron: #c8521a;
  --saflt  : #fde8dc;
  --vermil : #a02010;
  --teal   : #1a5a50;
  --teallt : #d4eeea;
  --ink    : #1c1008;
  --ink2   : #3a2410;
  --ink3   : #7a5830;
  --ink4   : #b09070;
  --shad   : rgba(90,40,0,.10);
  --shubh  : #1a5a30;
  --ashubh : #8a1010;
  --madh   : #7a6010;

  font-family   : 'Crimson Pro', Georgia, serif;
  font-size     : 17px; /* base size bump */
  color         : var(--ink);
  background    : var(--bg0);
  border-radius : 20px;
  overflow      : hidden;
  border        : 2px solid var(--bdr2);
  box-shadow    : 0 6px 32px var(--shad);
}
#tarabalPanel * { box-sizing: border-box; }

/* ── HERO ─────────────────────────────────────────────────── */
.tm-hero {
  background : linear-gradient(150deg,#6b2800 0%,#3a1500 50%,#1a0800 100%);
  padding    : 36px 48px 28px;
  position   : relative; overflow: hidden;
}
.tm-hero::before {
  content:'';position:absolute;inset:0;
  background-image:
    radial-gradient(circle at 20% 50%,rgba(200,144,32,.12) 0%,transparent 60%),
    radial-gradient(circle at 80% 20%,rgba(200,82,26,.10) 0%,transparent 50%),
    repeating-linear-gradient(0deg,rgba(200,144,32,.05) 0,rgba(200,144,32,.05) 1px,transparent 1px,transparent 40px),
    repeating-linear-gradient(90deg,rgba(200,144,32,.05) 0,rgba(200,144,32,.05) 1px,transparent 1px,transparent 40px);
  pointer-events:none;
}
.tm-hero::after {
  content:'✦';position:absolute;right:44px;top:50%;transform:translateY(-50%);
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:9rem;
  color:rgba(255,255,255,.04);pointer-events:none;user-select:none;
}
.tm-hero-inner { position:relative;z-index:1; }
.tm-eyebrow {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;letter-spacing:2px;
  color:rgba(255,220,140,.6);margin-bottom:14px;display:flex;align-items:center;gap:10px;
}
.tm-eyebrow::before,.tm-eyebrow::after{content:'';height:1px;flex:1 1 28px;background:rgba(200,144,32,.3);}
.tm-title-row { display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:8px; }
.tm-title-hi {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:2.6rem;font-weight:400;
  color:#fff9f0;line-height:1.1;text-shadow:0 2px 12px rgba(0,0,0,.4);
}
.tm-badge {
  font-family:'IBM Plex Mono',monospace;font-size:.75rem;font-weight:600;
  color:rgba(255,220,140,.8);background:rgba(200,144,32,.18);
  border:1px solid rgba(200,144,32,.35);padding:5px 12px;border-radius:6px;letter-spacing:1.5px;
}
.tm-title-sub {
  font-family:'Crimson Pro',serif;font-size:1.15rem;font-style:italic;
  color:rgba(255,210,140,.55);margin-bottom:18px;
}
.tm-hero-tags { display:flex;gap:8px;flex-wrap:wrap; }
.tm-htag {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;
  color:rgba(255,220,140,.75);background:rgba(200,144,32,.12);
  border:1px solid rgba(200,144,32,.22);padding:6px 16px;border-radius:20px;
}

/* ── INPUT BAR ─────────────────────────────────────────────── */
.tm-input-bar {
  background:var(--surf);border-bottom:2px solid var(--bdr);
  padding:16px 48px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;
}
.tm-label {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:var(--ink3);
  white-space:nowrap;
}
.tm-select, .tm-date-inp {
  padding:10px 16px;border-radius:8px;border:1.5px solid var(--bdr2);
  background:var(--bg1);color:var(--ink2);font-size:1.05rem;
  font-family:'Crimson Pro',serif;outline:none;transition:all .18s;min-width:150px;
}
.tm-select:focus,.tm-date-inp:focus {
  border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,144,32,.15);
}
.tm-calc-btn {
  display:flex;align-items:center;gap:8px;
  padding:11px 28px;border-radius:8px;
  background:linear-gradient(135deg,var(--saffron) 0%,var(--vermil) 100%);
  color:#fff;border:none;cursor:pointer;
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;
  box-shadow:0 3px 12px rgba(160,40,16,.28);transition:all .18s;
}
.tm-calc-btn:hover { transform:translateY(-2px);box-shadow:0 6px 18px rgba(160,40,16,.36); }

/* ── SUB TABS ──────────────────────────────────────────────── */
.tm-tabs {
  background:var(--bg1);border-bottom:2px solid var(--bdr2);
  padding:0 48px;display:flex;gap:4px;overflow-x:auto;scrollbar-width:none;
}
.tm-tabs::-webkit-scrollbar{display:none;}
.tm-tab {
  flex-shrink:0;padding:16px 26px;border:none;background:transparent;
  cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;
  transition:all .18s;outline:none;
  display:flex;flex-direction:column;align-items:center;gap:4px;
}
.tm-tab-hi {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.2rem;
  color:var(--ink3);transition:color .15s;line-height:1;
}
.tm-tab-en {
  font-family:'IBM Plex Mono',monospace;font-size:.65rem;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--ink4);transition:color .15s;
}
.tm-tab:hover { background:var(--goldlt); }
.tm-tab:hover .tm-tab-hi { color:var(--golddk); }
.tm-tab.tm-active { border-bottom-color:var(--saffron);background:rgba(200,82,26,.05); }
.tm-tab.tm-active .tm-tab-hi { color:var(--saffron); }
.tm-tab.tm-active .tm-tab-en { color:var(--saffron);opacity:.7; }
.tm-tab+.tm-tab { border-left:1px solid var(--bdr); }

/* ── CONTENT AREA ──────────────────────────────────────────── */
#tmContent {
  padding:28px 48px 56px;background:var(--bg0);min-height:380px;
}

/* ── SECTION CARDS ─────────────────────────────────────────── */
.tm-section {
  background:var(--surf);border:1.5px solid var(--bdr);border-radius:14px;
  padding:24px 28px;margin-bottom:20px;
  box-shadow:0 2px 8px var(--shad);
}
.tm-section-title {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.5rem;color:var(--ink);
  margin-bottom:16px;display:flex;align-items:center;gap:12px;padding-bottom:12px;
  border-bottom:1.5px solid var(--bdr);
}
.tm-section-title::before {
  content:'◆';font-family:'Tiro Devanagari Sanskrit',serif;
  font-size:1.5rem;color:var(--gold);
}
.tm-section-title::after {
  content:'';flex:1;height:1px;
  background:linear-gradient(90deg,var(--bdr2),transparent);
}

/* ── PANCHANGA ROW ─────────────────────────────────────────── */
.tm-panch-row {
  display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;
}
.tm-panch-chip {
  padding:12px 20px;border-radius:10px;
  background:var(--bg1);border:1.5px solid var(--bdr2);
  flex-direction:column;align-items:flex-start;min-width:140px;
}
.tm-chip-label {
  font-family:'IBM Plex Mono',monospace;font-size:.7rem;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--ink4);margin-bottom:4px;
}
.tm-chip-val {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.2rem;color:var(--ink);
  line-height:1.3;
}
.tm-chip-sub {
  font-family:'Crimson Pro',serif;font-size:.9rem;font-style:italic;color:var(--ink3);
  margin-top:2px;
}

/* ── TARA RESULT CARD ──────────────────────────────────────── */
.tm-tara-result {
  border-radius:12px;padding:24px 28px;margin-bottom:16px;
  border:2px solid;position:relative;overflow:hidden;
}
.tm-tara-result::before {
  content:'';position:absolute;top:0;left:0;right:0;height:4px;background:currentColor;opacity:.5;
}
.tm-tara-hero-row { display:flex;align-items:center;gap:20px;margin-bottom:16px; }
.tm-tara-icon { font-size:3rem;line-height:1; }
.tm-tara-main .tm-tara-name-hi {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:2.4rem;font-weight:400;line-height:1;
}
.tm-tara-main .tm-tara-num {
  font-family:'IBM Plex Mono',monospace;font-size:.85rem;letter-spacing:2px;
  text-transform:uppercase;margin-top:6px;opacity:.7;
}
.tm-tara-type-badge {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;
  padding:6px 18px;border-radius:20px;border:1.5px solid;margin-top:8px;
  display:inline-block;
}
.tm-tara-body {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;line-height:2;color:var(--ink2);
  background:rgba(255,255,255,.6);border-radius:8px;padding:16px 20px;
  border-left:3px solid;border-color:inherit;
}
.tm-tara-dist {
  font-family:'IBM Plex Mono',monospace;font-size:.85rem;color:var(--ink3);
  margin-top:12px;letter-spacing:.5px;line-height:1.7;
}

/* ── 9-TARA GRID ───────────────────────────────────────────── */
.tm-tara-grid {
  display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;
  margin-top:12px;
}
.tm-tara-cell {
  padding:12px 16px;border-radius:8px;border:1.5px solid var(--bdr);
  background:var(--bg1);display:flex;align-items:center;gap:10px;
  transition:all .15s;cursor:default;position:relative;
}
.tm-tara-cell:hover { transform:translateY(-2px);border-color:var(--gold);box-shadow:0 4px 12px var(--shad); }
.tm-tara-cell.tc-active { border-width:2px; }
.tm-tara-cell .tc-num {
  font-family:'IBM Plex Mono',monospace;font-size:.8rem;font-weight:700;
  min-width:26px;height:26px;border-radius:5px;display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.8);color:var(--ink3);
}

/* ── MURTI RESULT CARD ─────────────────────────────────────── */
.tm-murti-card {
  border-radius:14px;padding:28px 32px;margin-bottom:16px;
  border:2px solid;box-shadow:0 4px 16px var(--shad);
}
.tm-murti-hero {
  display:flex;align-items:flex-start;gap:24px;margin-bottom:20px;
}
.tm-murti-symbol {
  font-size:4.5rem;line-height:1;flex-shrink:0;
  width:88px;height:88px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.4);border:2px solid rgba(255,255,255,.5);
}
.tm-murti-info .tm-murti-name {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:2.6rem;font-weight:400;line-height:1.1;
  margin-bottom:6px;
}
.tm-murti-info .tm-murti-en {
  font-family:'Crimson Pro',serif;font-size:1.1rem;font-style:italic;opacity:.6;margin-bottom:10px;
}
.tm-murti-info .tm-murti-quality {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;
  padding:6px 18px;border-radius:20px;background:rgba(255,255,255,.3);
  border:1.5px solid rgba(255,255,255,.5);display:inline-block;
}
.tm-murti-phala {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;line-height:2;
  background:rgba(255,255,255,.5);border-radius:10px;padding:18px 22px;
  border-left:4px solid rgba(255,255,255,.7);margin-bottom:16px;
}
.tm-murti-upay {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;
  background:rgba(255,255,255,.3);border-radius:8px;padding:14px 18px;
  display:flex;align-items:center;gap:10px;
}
.tm-upay-label {
  font-family:'IBM Plex Mono',monospace;font-size:.7rem;letter-spacing:1px;
  text-transform:uppercase;opacity:.6;white-space:nowrap;
}
.tm-formula-box {
  font-family:'IBM Plex Mono',monospace;font-size:.9rem;letter-spacing:.3px;
  background:var(--bg2);border:1px solid var(--bdr2);border-radius:8px;
  padding:14px 18px;color:var(--ink2);margin-top:14px;line-height:1.8;
}
.tm-formula-box strong { color:var(--golddk);font-weight:600; }

/* ── MURTI FOR ALL VARA TABLE ──────────────────────────────── */
.tm-vara-table {
  width:100%;border-collapse:separate;border-spacing:0 5px;margin-top:10px;
}
.tm-vara-table th {
  font-family:'IBM Plex Mono',monospace;font-size:.72rem;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--ink4);text-align:left;padding:8px 16px;
}
.tm-vara-table td {
  padding:14px 16px;background:var(--bg1);border:1px solid var(--bdr);
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:var(--ink);
}
.tm-vara-table tr td:first-child { border-radius:8px 0 0 8px; }
.tm-vara-table tr td:last-child  { border-radius:0 8px 8px 0; }
.tm-vara-table tr.vtrow-active td { background:var(--goldlt);border-color:var(--gold); }
.tm-murti-dot {
  display:inline-flex;align-items:center;gap:8px;
  padding:5px 14px;border-radius:12px;border:1.5px solid;font-size:1rem;
}

/* ── NAK DETAIL BLOCK ──────────────────────────────────────── */
.tm-nak-detail {
  display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin-top:14px;
}
.tm-nak-item {
  background:var(--bg1);border:1.5px solid var(--bdr);border-radius:10px;
  padding:14px 18px;
}
.tm-nak-item-label {
  font-family:'IBM Plex Mono',monospace;font-size:.68rem;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--ink4);margin-bottom:6px;
}
.tm-nak-item-val {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.2rem;color:var(--ink);
}
.tm-nak-item-sub {
  font-family:'Crimson Pro',serif;font-size:.9rem;font-style:italic;color:var(--ink3);margin-top:3px;
}

/* ── BPHS REFERENCE BOX ────────────────────────────────────── */
.tm-bphs-box {
  background:linear-gradient(135deg,var(--bg1),var(--goldlt));
  border:1.5px solid var(--bdr2);border-left:4px solid var(--gold);
  border-radius:0 10px 10px 0;padding:18px 22px;
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;
  line-height:2;color:var(--ink2);margin-top:14px;
}
.tm-bphs-title {
  font-family:'IBM Plex Mono',monospace;font-size:.68rem;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--golddk);margin-bottom:8px;
}

/* ── INFO STRIP ────────────────────────────────────────────── */
.tm-info-strip {
  display:flex;align-items:center;gap:12px;padding:14px 20px;
  background:var(--surf);border:1.5px solid var(--bdr);border-left:4px solid var(--gold);
  border-radius:10px;margin-bottom:20px;
}
.tm-strip-title { font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.15rem;color:var(--ink); }
.tm-strip-sub   { font-family:'IBM Plex Mono',monospace;font-size:.7rem;color:var(--ink4);letter-spacing:.5px;margin-top:3px; }
.tm-strip-badge {
  margin-left:auto;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;
  color:var(--golddk);background:var(--goldlt);border:1px solid rgba(154,107,10,.25);
  padding:5px 18px;border-radius:16px;white-space:nowrap;flex-shrink:0;
}

/* ── LOADING / EMPTY ───────────────────────────────────────── */
.tm-loading {
  display:flex;flex-direction:column;align-items:center;padding:72px 24px;gap:20px;
}
.tm-loader {
  width:48px;height:48px;border:3px solid var(--bdr2);border-top-color:var(--saffron);
  border-radius:50%;animation:tm-spin .9s linear infinite;
}
@keyframes tm-spin{to{transform:rotate(360deg);}}
.tm-load-text {
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:var(--ink3);text-align:center;
}
.tm-empty {
  text-align:center;padding:64px 24px;border:1.5px dashed var(--bdr2);
  border-radius:14px;color:var(--ink4);background:var(--surf);
  font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;
}

/* ── NAK MUHURTA TYPE BADGE ────────────────────────────────── */
.tm-mtype-0 { background:#e8f8e8;color:#1a5a20;border-color:#86c886; }
.tm-mtype-1 { background:#e8f4e8;color:#2a6a30;border-color:#6ab86a; }
.tm-mtype-2 { background:#f8f4e0;color:#7a6010;border-color:#d4b050; }
.tm-mtype-3 { background:#f8e8e0;color:#8a3010;border-color:#d09070; }
.tm-mtype-4 { background:#f8e0e0;color:#8a1010;border-color:#d08080; }

/* ── RESPONSIVE ─────────────────────────────────────────────── */
@media (max-width: 720px) {
  .tm-hero,.tm-input-bar,.tm-tabs,#tmContent { padding-left:16px;padding-right:16px; }
  .tm-title-hi { font-size:1.9rem; }
  .tm-tara-grid { grid-template-columns:1fr 1fr; }
  .tm-nak-detail { grid-template-columns:1fr 1fr; }
}
</style>

<div id="tarabalPanel" style="display:none">

  {{-- HERO --}}
  <div class="tm-hero">
    <div class="tm-hero-inner">
      <div class="tm-eyebrow">Brihat Parashara Hora Shastra · Muhurta Chintamani</div>
      <div class="tm-title-row">
        <div class="tm-title-hi">Tarabala &amp; Murti Nirnaya</div>
      </div>
      <div class="tm-title-sub">Tarabala &amp; Murti Nirnaya — Vedic Auspiciousness Analysis</div>
      <div class="tm-hero-tags">
        <span class="tm-htag">Nakshatra Bala</span>
        <span class="tm-htag">Janma Tara</span>
        <span class="tm-htag">Sampat Tara</span>
        <span class="tm-htag">Swarna Murti</span>
        <span class="tm-htag">Lahiri Ayanamsa</span>
      </div>
    </div>
  </div>

  {{-- INPUT BAR --}}
  <div class="tm-input-bar">
    <span class="tm-label">Date :</span>
    <input type="date" id="tmDate" class="tm-date-inp" value="{{ date('Y-m-d') }}"/>
    <span class="tm-label">Birth Nakshatra :</span>
    <select id="tmBirthNak" class="tm-select">
      <option value="-1">— Unknown / General —</option>
      <option value="0">Ashwini</option><option value="1">Bharani</option>
      <option value="2">Krittika</option><option value="3">Rohini</option>
      <option value="4">Mrigashira</option><option value="5">Ardra</option>
      <option value="6">Punarvasu</option><option value="7">Pushya</option>
      <option value="8">Ashlesha</option><option value="9">Magha</option>
      <option value="10">PurvaPhalguni</option><option value="11">UttaraPhalguni</option>
      <option value="12">Hasta</option><option value="13">Chitra</option>
      <option value="14">Swati</option><option value="15">Vishakha</option>
      <option value="16">Anuradha</option><option value="17">Jyeshtha</option>
      <option value="18">Moola</option><option value="19">PurvaAshadha</option>
      <option value="20">UttaraAshadha</option><option value="21">Shravana</option>
      <option value="22">Dhanishtha</option><option value="23">Shatabhisha</option>
      <option value="24">PurvaBhadrapada</option><option value="25">UttaraBhadrapada</option>
      <option value="26">Revati</option>
    </select>
    <select id="tmBirthRashi" class="tm-select" style="min-width:130px">
      <option value="-1">— Birth Rashi —</option>
      <option value="0">Mesha</option><option value="1">Vrishabha</option>
      <option value="2">Mithuna</option><option value="3">Karka</option>
      <option value="4">Simha</option><option value="5">Kanya</option>
      <option value="6">Tula</option><option value="7">Vrishchika</option>
      <option value="8">Dhanu</option><option value="9">Makara</option>
      <option value="10">Kumbha</option><option value="11">Meena</option>
    </select>
    <button class="tm-calc-btn" onclick="tmCalculate()">
      <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="10" cy="10" r="8"/><path d="M10 6v4l3 2"/>
      </svg>
      Calculate
    </button>
  </div>

  {{-- SUB TABS --}}
  <div class="tm-tabs">
    <button class="tm-tab tm-active" id="tmTab_tarabal" onclick="tmSwitchTab('tarabal',this)">
      <span class="tm-tab-hi">Tarabala</span>
      <span class="tm-tab-en">Tarabala</span>
    </button>
    <button class="tm-tab" id="tmTab_murti" onclick="tmSwitchTab('murti',this)">
      <span class="tm-tab-hi">Murti Nirnaya</span>
      <span class="tm-tab-en">Murti Nirnaya</span>
    </button>
    <button class="tm-tab" id="tmTab_nak" onclick="tmSwitchTab('nak',this)">
      <span class="tm-tab-hi">Nakshatra</span>
      <span class="tm-tab-en">Nakshatra</span>
    </button>
    <button class="tm-tab" id="tmTab_ref" onclick="tmSwitchTab('ref',this)">
      <span class="tm-tab-hi">Shastra Ref</span>
      <span class="tm-tab-en">Shastra Ref</span>
    </button>
  </div>

  {{-- CONTENT --}}
  <div id="tmContent">
    <div class="tm-empty">
      <div style="font-size:2.8rem;margin-bottom:14px;opacity:.4">🪐</div>
      Select date and birth nakshatra, then press <strong>"Calculate"</strong>.<br>
      <span style="font-size:1rem;opacity:.7">Murti Nirnaya also works without a birth nakshatra.</span>
    </div>
  </div>

</div>

<script>
// ══════════════════════════════════════════════════════
//  TARABAL + MURTI PANEL — STATE
// ══════════════════════════════════════════════════════
let _tmActiveTab  = 'tarabal';
let _tmData       = null;
let _tmLoading    = false;

function tmSwitchTab(tab, el) {
  _tmActiveTab = tab;
  document.querySelectorAll('.tm-tab').forEach(b => b.classList.remove('tm-active'));
  if (el) el.classList.add('tm-active');
  if (_tmData) _tmRender(_tmData);
}

async function tmCalculate() {
  if (_tmLoading || typeof _masaLat === 'undefined' || _masaLat === null) return;
  const dateVal = document.getElementById('tmDate')?.value;
  if (!dateVal) return;
  const [yr, mo, dy] = dateVal.split('-').map(Number);
  const birthNak   = parseInt(document.getElementById('tmBirthNak')?.value  ?? '-1');
  const birthRashi = parseInt(document.getElementById('tmBirthRashi')?.value ?? '-1');

  _tmLoading = true;
  const cont = document.getElementById('tmContent');
  cont.innerHTML = `<div class="tm-loading">
    <div class="tm-loader"></div>
    <div class="tm-load-text">Computing BPHS…<br>
    <span style="font-family:'IBM Plex Mono',monospace;font-size:.75rem;color:var(--ink4)">
    Jean Meeus · Lahiri Ayanamsa</span></div></div>`;

  try {
    const res = await fetch('{{ route("astro.tarabal-murti") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ yr, mo, dy, lat: _masaLat, lon: _masaLon, utcOff: _masaOff, birthNak, birthRashi })
    });
    if (!res.ok) throw new Error('Server ' + res.status);
    _tmData = await res.json();
    _tmRender(_tmData);
  } catch(e) {
    cont.innerHTML = `<div class="tm-empty">⚠ Error: ${e.message}</div>`;
  } finally { _tmLoading = false; }
}

function tmAutoLoad() {
  if (_masaLat !== null && !_tmData && !_tmLoading) tmCalculate();
}

// ══════════════════════════════════════════════════════
//  RENDER ROUTER
// ══════════════════════════════════════════════════════
function _tmRender(d) {
  const cont = document.getElementById('tmContent');
  if (!cont || !d) return;
  switch (_tmActiveTab) {
    case 'tarabal': cont.innerHTML = _tmRenderTarabal(d); break;
    case 'murti':   cont.innerHTML = _tmRenderMurti(d);   break;
    case 'nak':     cont.innerHTML = _tmRenderNak(d);     break;
    case 'ref':     cont.innerHTML = _tmRenderRef(d);     break;
    default:        cont.innerHTML = _tmRenderTarabal(d);
  }
}

// ══════════════════════════════════════════════════════
//  TARABAL TAB
// ══════════════════════════════════════════════════════
function _tmRenderTarabal(d) {
  const tb = d.taraResult;
  const mtypeLabels = ['Highly Auspicious (Laghu/Kshipra)','Auspicious (Mrudu)','Moderate (Chara)','Inauspicious (Ugra)','Highly Inauspicious (Tikshna)'];

  let html = `
  <div class="tm-info-strip">
    <div>
      <div class="tm-strip-title">${d.varaHi} · Moon: ${d.moonNakHi} (${d.moonNakEn ?? ''}) · ${d.tithiHi ?? ''}</div>
      <div class="tm-strip-sub">Sunrise ${d.sunrise} · Sunset ${d.sunset} · Rashi: ${d.moonRashiHi}</div>
    </div>
    <span class="tm-strip-badge tm-mtype-${d.nakMuhurtaType ?? 0}" style="border:1.5px solid">${mtypeLabels[d.nakMuhurtaType??0]}</span>
  </div>`;

  html += `<div class="tm-section">
    <div class="tm-section-title">Today's Panchanga — Nakshatra Details</div>
    <div class="tm-panch-row">
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Moon Nakshatra</div>
        <div class="tm-chip-val">${d.moonNakHi}</div>
        <div class="tm-chip-sub">Pada ${d.moonPada}</div>
      </div>
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Nak Lord</div>
        <div class="tm-chip-val">${d.moonLordHi}</div>
      </div>
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Gana</div>
        <div class="tm-chip-val">${d.moonGana}</div>
      </div>
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Nadi</div>
        <div class="tm-chip-val">${d.moonNadi}</div>
      </div>
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Tattva</div>
        <div class="tm-chip-val" style="color:${d.moonTatvaColor ?? '#000'}">${d.moonTatva}</div>
      </div>
      <div class="tm-panch-chip">
        <div class="tm-chip-label">Sun Nakshatra</div>
        <div class="tm-chip-val">${d.sunNakHi}</div>
        <div class="tm-chip-sub">Rashi: ${d.sunRashiHi}</div>
      </div>
    </div>
  </div>`;

  if (tb) {
    const c = tb.color;
    const isShubh = tb.shubh;
    const bg = isShubh ? '#f0f8f0' : '#f8f0f0';
    html += `
    <div class="tm-section">
      <div class="tm-section-title">Tarabala — Personal Result</div>
      <div class="tm-tara-result" style="background:${bg};border-color:${c};color:${c}">
        <div class="tm-tara-hero-row">
          <div class="tm-tara-icon">${isShubh ? '✦' : '⚠'}</div>
          <div class="tm-tara-main">
            <div class="tm-tara-name-hi" style="color:${c}">${tb.name} Tara</div>
            <div class="tm-tara-num">Tara ${tb.taraNum}/9 · ${tb.en}</div>
            <span class="tm-tara-type-badge" style="color:${c};border-color:${c};background:${bg}">${tb.type}</span>
            ${tb.cycleNote ? `<span style="font-family:'IBM Plex Mono',monospace;font-size:.75rem;margin-left:10px;opacity:.65">${tb.cycleNote}</span>` : ''}
          </div>
        </div>
        <div class="tm-tara-body" style="border-color:${c}">
          <strong>Phala:</strong> ${tb.phala}
        </div>
        <div class="tm-tara-dist">
          Birth Nak: <strong>${tb.birthNakHi}</strong> (${tb.birthNak}) →
          Moon Nak: <strong>${tb.moonNakHi}</strong> (${tb.moonNak}) →
          Distance: ${tb.dist} Nak → Tara ${tb.taraNum}
          · Effect: <strong style="color:${c}">${tb.bonus > 0 ? '+' : ''}${tb.bonus}</strong>
        </div>
        <div class="tm-bphs-box" style="margin-top:14px">
          <div class="tm-bphs-title">Brihat Parashara Hora Shastra</div>
          ${tb.bphs}
        </div>
      </div>
    </div>`;
  } else {
    html += `
    <div class="tm-section">
      <div class="tm-section-title">Tarabala — Result</div>
      <div class="tm-empty" style="margin:0;padding:28px">
        <div style="font-size:1.6rem;margin-bottom:10px">ℹ</div>
        Select birth nakshatra for personal Tarabala result.<br>
        <span style="font-size:1rem;opacity:.7">See the 27-nakshatra Tara table below.</span>
      </div>
    </div>`;
  }

  // 9 Taras grid
  html += `
  <div class="tm-section">
    <div class="tm-section-title">Nava Tara Details — BPHS Ch.26</div>
    <div class="tm-tara-grid">`;

  const allTara = d.allTaraData ?? {};
  for (let t = 1; t <= 9; t++) {
    const td2 = allTara[t] ?? {};
    const isActive = tb && tb.taraNum === t;
    html += `
      <div class="tm-tara-cell${isActive ? ' tc-active' : ''}"
           style="${isActive ? `border-color:${td2.color};background:${tb.shubh ? '#f0f8f0' : '#f8f0f0'}` : ''}">
        <div style="display:flex;align-items:center;gap:10px;width:100%">
          <div class="tc-num" style="background:${td2.color}15;color:${td2.color};font-weight:700">${t}</div>
          <div style="flex:1">
            <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:${td2.color};font-weight:${isActive?'700':'400'}">
              ${td2.name ?? ''}</div>
            <div style="font-family:'IBM Plex Mono',monospace;font-size:.72rem;opacity:.6;margin-top:2px">${td2.en ?? ''}</div>
          </div>
          <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.88rem;
            padding:4px 12px;border-radius:10px;border:1.5px solid ${td2.color};color:${td2.color};
            background:${td2.color}15">${td2.type ?? ''}</span>
        </div>
      </div>`;
  }
  html += `</div></div>`;

  // 27-Nakshatra tara table
  html += `
  <div class="tm-section">
    <div class="tm-section-title">Tara of all 27 Nakshatras from today's Moon position</div>
    <p style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:var(--ink3);margin:0 0 16px">
      ${d.moonNakHi} counting from current Moon: 27 nakshatras give 9 Taras each in three cycles:
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:8px">`;

  (d.taraTable ?? []).forEach((item) => {
    const t = d.allTaraData?.[item.tara] ?? {};
    const isBirth = tb && item.nak === tb.birthNak;
    const isMoon  = item.nak === d.moonNak;
    html += `
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;
        background:${isBirth ? '#fef9e0' : (isMoon ? '#f0f8f0' : 'var(--bg1)')};
        border:1.5px solid ${isBirth ? 'var(--gold)' : (isMoon ? '#6ab86a' : 'var(--bdr)')};
        position:relative">
        <div style="font-family:'IBM Plex Mono',monospace;font-size:.72rem;font-weight:700;
          min-width:22px;height:22px;border-radius:4px;display:flex;align-items:center;justify-content:center;
          background:${t.color}20;color:${t.color}">${item.tara}</div>
        <div style="flex:1">
          <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:var(--ink)">
            ${item.nakHi}</div>
          <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.85rem;color:${t.color}">
            ${item.taraHi}</div>
        </div>
        ${isBirth ? '<span style="font-size:.7rem;position:absolute;top:4px;right:6px;color:var(--gold);font-weight:700">Birth</span>' : ''}
        ${isMoon  ? '<span style="font-size:.7rem;position:absolute;top:4px;right:6px;color:#1a6a30;font-weight:700">Today</span>' : ''}
        <span style="font-family:'IBM Plex Mono',monospace;font-size:.6rem;position:absolute;bottom:3px;right:6px;opacity:.35">
          C${item.cycle}</span>
      </div>`;
  });
  html += `</div></div>`;
  return html;
}

// ══════════════════════════════════════════════════════
//  MURTI NIRNAYA TAB
// ══════════════════════════════════════════════════════
function _tmRenderMurti(d) {
  const gm = d.generalMurti;
  const pm = d.personalMurti;
  const allM = d.allMurtiData ?? {};
  const murtiColors = {0:'#c8a020',1:'#6a7a8a',2:'#b05010',3:'#3a3a4a'};
  const murtiBg     = {0:'#fef9e0',1:'#f0f4f8',2:'#fdf0e8',3:'#f0f0f4'};

  let html = `
  <div class="tm-info-strip">
    <div>
      <div class="tm-strip-title">${d.varaHi} · Moon: ${d.moonNakHi} (Pada ${d.moonPada}) · Rashi: ${d.moonRashiHi}</div>
      <div class="tm-strip-sub">Murti = (Vara + Nakshatra) mod 4 — BPHS Ch.87</div>
    </div>
    <span class="tm-strip-badge">Murti Nirnaya</span>
  </div>`;

  const gColor = murtiColors[gm.idx];
  const gBg    = murtiBg[gm.idx];
  html += `
  <div class="tm-section">
    <div class="tm-section-title">General Murti (Vara + Moon Nakshatra)</div>
    <div class="tm-murti-card" style="background:${gBg};border-color:${gColor}">
      <div class="tm-murti-hero">
        <div class="tm-murti-symbol" style="color:${gColor};border-color:${gColor}50">${gm.symbol}</div>
        <div class="tm-murti-info">
          <div class="tm-murti-name" style="color:${gColor}">${gm.name}</div>
          <div class="tm-murti-en">${gm.en}</div>
          <div class="tm-murti-quality" style="color:${gColor}">${gm.quality}</div>
        </div>
      </div>
      <div class="tm-murti-phala">${gm.phala}</div>
      <div class="tm-murti-upay">
        <span class="tm-upay-label">Upaya</span>
        <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:var(--ink2)">${gm.upay}</span>
      </div>
      <div class="tm-formula-box">
        <strong>Calculation:</strong> ${d.murtiFormula?.general ?? ''}
      </div>
      <div class="tm-bphs-box">
        <div class="tm-bphs-title">Brihat Parashara Hora Shastra — Chapter 87</div>
        ${gm.bphs}
      </div>
    </div>
  </div>`;

  if (pm) {
    const pColor = murtiColors[pm.idx];
    const pBg    = murtiBg[pm.idx];
    html += `
    <div class="tm-section">
      <div class="tm-section-title">Personal Murti (Vara + Birth Nak + Moon Nak)</div>
      <div class="tm-murti-card" style="background:${pBg};border-color:${pColor}">
        <div class="tm-murti-hero">
          <div class="tm-murti-symbol" style="color:${pColor};border-color:${pColor}50">${pm.symbol}</div>
          <div class="tm-murti-info">
            <div class="tm-murti-name" style="color:${pColor}">${pm.name}</div>
            <div class="tm-murti-en">${pm.en}</div>
            <div class="tm-murti-quality" style="color:${pColor}">${pm.quality}</div>
          </div>
        </div>
        <div class="tm-murti-phala">${pm.phala}</div>
        <div class="tm-murti-upay">
          <span class="tm-upay-label">Upaya</span>
          <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:var(--ink2)">${pm.upay}</span>
        </div>
        <div class="tm-formula-box">
          <strong>Calculation:</strong> ${d.murtiFormula?.personal ?? ''}
        </div>
      </div>
    </div>`;
  }

  // All 4 murti types
  html += `
  <div class="tm-section">
    <div class="tm-section-title">All 4 Murtis — Complete Details</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">`;

  for (let i = 0; i <= 3; i++) {
    const m = allM[i] ?? {};
    const c = murtiColors[i]; const bg = murtiBg[i];
    const isActive = (gm.idx === i || (pm && pm.idx === i));
    html += `
      <div style="background:${bg};border:${isActive ? '2.5px' : '1.5px'} solid ${isActive ? c : 'var(--bdr)'};
        border-radius:12px;padding:20px 22px;transition:all .2s">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
          <span style="font-size:2rem;color:${c}">${m.symbol}</span>
          <div>
            <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.25rem;color:${c}">${m.name}</div>
            <div style="font-family:'IBM Plex Mono',monospace;font-size:.7rem;opacity:.6;margin-top:2px">${m.en}</div>
          </div>
          <span style="margin-left:auto;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;
            padding:4px 12px;border-radius:10px;background:${c}15;color:${c};border:1px solid ${c}">${m.quality}</span>
        </div>
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;line-height:1.85;color:var(--ink2)">
          ${m.phala}</div>
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;color:var(--ink3);
          margin-top:12px;padding:10px 14px;background:rgba(255,255,255,.5);border-radius:6px">
          <strong>Upaya:</strong> ${m.upay}</div>
        ${isActive ? `<div style="font-family:'IBM Plex Mono',monospace;font-size:.75rem;color:${c};margin-top:10px;font-weight:700">
          <- Today's Murti ${gm.idx===i?'(General)':''}${pm&&pm.idx===i?' (Personal)':''}</div>` : ''}
      </div>`;
  }
  html += `</div></div>`;

  // Vara table
  html += `
  <div class="tm-section">
    <div class="tm-section-title">Sapta Vara — Murti Chart</div>
    <table class="tm-vara-table">
      <thead><tr><th>Vara</th><th>Murti</th><th>Phala</th></tr></thead>
      <tbody>`;

  (d.murtiForAllVara ?? []).forEach((row, i) => {
    const c = murtiColors[row.idx];
    const isToday = (i === d.varaIdx);
    html += `
      <tr class="${isToday ? 'vtrow-active' : ''}">
        <td>${isToday ? '▶ ' : ''}${row.vara}</td>
        <td><span class="tm-murti-dot" style="color:${c};border-color:${c};background:${c}10">
          ${allM[row.idx]?.symbol ?? ''} ${row.murti}</span></td>
        <td style="font-size:1rem;color:var(--ink3)">${row.quality}</td>
      </tr>`;
  });
  html += `</tbody></table></div>`;
  return html;
}

// ══════════════════════════════════════════════════════
//  NAKSHATRA TAB
// ══════════════════════════════════════════════════════
function _tmRenderNak(d) {
  const nd = d.nakDetails ?? {};
  const mtypeLabels = ['Highly Auspicious (Laghu/Kshipra)','Auspicious (Mrudu)','Moderate (Chara)','Inauspicious (Ugra)','Highly Inauspicious (Tikshna)'];
  const mtypeColors = ['#1a5a20','#2a6a30','#7a6010','#8a3010','#8a1010'];

  let html = `
  <div class="tm-info-strip">
    <div>
      <div class="tm-strip-title">Moon Nakshatra: ${nd.nakHi ?? ''} — ${nd.nakEn ?? ''}</div>
      <div class="tm-strip-sub">BPHS + Muhurta Chintamani Nakshatra Details</div>
    </div>
    <span class="tm-strip-badge" style="color:${mtypeColors[nd.muhurtaType ?? 0]}">
      ${mtypeLabels[nd.muhurtaType ?? 0]}</span>
  </div>

  <div class="tm-section">
    <div class="tm-section-title">${nd.nakHi ?? ''} Nakshatra — Complete Details</div>
    <div class="tm-nak-detail">
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Nak Number</div>
        <div class="tm-nak-item-val">${(nd.nak ?? 0) + 1}/27</div>
        <div class="tm-nak-item-sub">${nd.nakEn ?? ''}</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Pada</div>
        <div class="tm-nak-item-val">${nd.pada ?? ''}</div>
        <div class="tm-nak-item-sub">Navamsha: ${nd.charanaRashi ?? ''}</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Nak Lord</div>
        <div class="tm-nak-item-val">${nd.lord ?? ''}</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Gana</div>
        <div class="tm-nak-item-val">${nd.gana ?? ''}</div>
        <div class="tm-nak-item-sub">Deva / Manushya / Rakshasa</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Nadi</div>
        <div class="tm-nak-item-val">${nd.nadi ?? ''}</div>
        <div class="tm-nak-item-sub">Adi / Madhya / Antya</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Tattva</div>
        <div class="tm-nak-item-val" style="color:${nd.tatvaColor ?? '#000'}">${nd.tatva ?? ''}</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Guna</div>
        <div class="tm-nak-item-val">${nd.guna ?? ''}</div>
        <div class="tm-nak-item-sub">Sattvic / Rajasic / Tamasic</div>
      </div>
      <div class="tm-nak-item">
        <div class="tm-nak-item-label">Muhurta Class</div>
        <div class="tm-nak-item-val" style="color:${mtypeColors[nd.muhurtaType ?? 0]};font-size:1.05rem">
          ${mtypeLabels[nd.muhurtaType ?? 0]}</div>
      </div>
    </div>
    <div class="tm-bphs-box" style="margin-top:16px">
      <div class="tm-bphs-title">Nakshatra Deity &amp; Qualities</div>
      ${nd.desc ?? ''}
    </div>
  </div>

  <div class="tm-section">
    <div class="tm-section-title">All 27 Nakshatras — Muhurta Classification</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:8px">`;

  const nakHiArr = ['Ashwini','Bharani','Krittika','Rohini','Mrigashira','Ardra','Punarvasu','Pushya','Ashlesha','Magha','PurvaPhalguni','UttaraPhalguni','Hasta','Chitra','Swati','Vishakha','Anuradha','Jyeshtha','Moola','PurvaAshadha','UttaraAshadha','Shravana','Dhanishtha','Shatabhisha','PurvaBhadrapada','UttaraBhadrapada','Revati'];
  const mtypes   = [0,4,2,0,1,4,1,0,3, 3,1,0,0,2,1,2,1,3, 4,1,0,0,1,3,1,0,0];

  nakHiArr.forEach((n, i) => {
    const mt = mtypes[i]; const c = mtypeColors[mt];
    const isActive = (i === d.moonNak);
    html += `
      <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:8px;
        background:${isActive ? '#fef9e0' : 'var(--bg1)'};
        border:${isActive ? '2px' : '1.5px'} solid ${isActive ? 'var(--gold)' : 'var(--bdr)'};
        transition:all .15s">
        <span style="font-family:'IBM Plex Mono',monospace;font-size:.68rem;
          min-width:20px;text-align:center;opacity:.5">${i+1}</span>
        <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:var(--ink);flex:1">${n}</span>
        <span style="font-family:'IBM Plex Mono',monospace;font-size:.65rem;
          padding:3px 9px;border-radius:8px;border:1px solid ${c};color:${c};background:${c}15">
          ${['HA','Au','Mo','In','◆'][mt]}</span>
        ${isActive ? '<span style="font-size:.75rem;color:var(--gold);font-weight:700">◀</span>' : ''}
      </div>`;
  });
  html += `</div></div>`;
  return html;
}

// ══════════════════════════════════════════════════════
//  SHASTRA REFERENCE TAB
// ══════════════════════════════════════════════════════
function _tmRenderRef(d) {
  const taraData  = d.allTaraData  ?? {};
  const murtiData = d.allMurtiData ?? {};
  const murtiColors = {0:'#c8a020',1:'#6a7a8a',2:'#b05010',3:'#3a3a4a'};

  return `
  <div class="tm-info-strip">
    <div>
      <div class="tm-strip-title">Shastra Reference — Tarabala &amp; Murti Nirnaya</div>
      <div class="tm-strip-sub">Brihat Parashara Hora Shastra · Muhurta Chintamani · Jyotir-nibandha</div>
    </div>
    <span class="tm-strip-badge">BPHS Reference</span>
  </div>

  <div class="tm-section">
    <div class="tm-section-title">Tarabala Calculation Method — BPHS Ch.26</div>
    <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;line-height:2.1;color:var(--ink2)">
      <p style="margin-bottom:12px"><strong>Formula:</strong> Count from birth nakshatra to current Moon nakshatra.
        This distance gives 9 Taras across three cycles.</p>
      <p style="margin-bottom:12px"><strong>Calculation:</strong> (Moon Nak − Birth Nak + 27) mod 27 = Distance<br>
         Distance mod 9 + 1 = Tara number (1-9)</p>
      <p style="margin-bottom:12px"><strong>Three Cycles:</strong> 1st cycle (0-8) = full effect · 2nd (9-17) = 50% · 3rd (18-26) = 25%</p>
      <p><strong>BPHS Shloka (26.3):</strong></p>
      <div class="tm-bphs-box" style="font-style:italic;color:var(--golddk);font-size:1.05rem">
        "Janmadi Taraganana Vilomya, Sampat-Vipat-Kshema-Pratyari-Sadhaka-Naidhana-Mitra-Atimitra Tara Phalam Jneyam."
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px">
      ${Object.entries(taraData).map(([num, t]) => `
        <div style="display:flex;align-items:flex-start;gap:16px;padding:16px 20px;
          border-radius:10px;background:${t.shubh ? '#f0f8f0' : '#f8f0f0'};
          border:1.5px solid ${t.color}40">
          <div style="font-family:'IBM Plex Mono',monospace;font-size:.9rem;font-weight:700;
            min-width:28px;text-align:center;color:${t.color};padding-top:2px">${num}</div>
          <div style="flex:1">
            <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:${t.color};margin-bottom:6px;font-weight:600">
              ${t.name} (${t.en}) — ${t.type}</div>
            <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:var(--ink2);line-height:1.9">
              ${t.bphs}</div>
          </div>
        </div>`).join('')}
    </div>
  </div>

  <div class="tm-section">
    <div class="tm-section-title">Murti Nirnaya Method — BPHS Ch.87</div>
    <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;line-height:2.1;color:var(--ink2)">
      <p style="margin-bottom:12px"><strong>Formula (General):</strong> (Vara index + Moon Nakshatra) mod 4
        → 0=Swarna · 1=Rajata · 2=Tamra · 3=Loha</p>
      <p style="margin-bottom:12px"><strong>Formula (Personal):</strong> (Vara + Birth Nak + Moon Nak) mod 4</p>
      <p><strong>BPHS Shloka (87.4):</strong></p>
      <div class="tm-bphs-box" style="font-style:italic;color:var(--golddk);font-size:1.05rem">
        "Swarna-Rajata-Tamra-Loha Murtayah Kramena Shreshttha-Madhyama-Adhama-Ashubha Phaladah."
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px">
      ${[0,1,2,3].map(i => {
        const m = murtiData[i] ?? {}; const c = murtiColors[i];
        return `
        <div style="padding:18px 22px;border-radius:10px;background:var(--bg1);border:1.5px solid ${c}40">
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px">
            <span style="font-size:1.6rem;color:${c}">${m.symbol}</span>
            <strong style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.15rem;color:${c}">
              ${m.name} — ${m.quality}</strong>
          </div>
          <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;line-height:1.9;color:var(--ink2)">
            ${m.bphs}</div>
          <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:var(--ink3);margin-top:8px">
            <strong>Upaya:</strong> ${m.upay}</div>
        </div>`; }).join('')}
    </div>
  </div>`;
}

// ══════════════════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════════════════
function initTarabalPanel() {
  const d = new Date();
  const dateStr = d.getFullYear() + '-'
    + String(d.getMonth()+1).padStart(2,'0') + '-'
    + String(d.getDate()).padStart(2,'0');
  const dateEl = document.getElementById('tmDate');
  if (dateEl && !dateEl.value) dateEl.value = dateStr;
  tmAutoLoad();
}
</script>
<div id="murtiPanel" style="display:none"></div>