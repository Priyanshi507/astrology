{{--
  _panel_festival.blade.php — v10 PANCHANG REDESIGN
  Aesthetic: Warm saffron-parchment panchang style
  • Rich ochre/saffron/vermillion palette — NOT dark, NOT cold blue
  • Devanagari-first typography with Sanskrit feel
  • Ornate card borders with tilak-style accent marks
  • Hero with mandala-inspired background pattern
  • Navigation that feels like sacred scripture sections
  • Warm gold modal overlay
--}}

<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit:ital@0;1&family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Crimson+Pro:ital,wght@0,400;0,600;0,700;1,400;1,600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>

<style>
/* ════════════════════════════════════════════════════════════
   FESTIVAL PANEL v10 — वैदिक पञ्चाङ्ग PANCHANG AESTHETIC
   Warm · Sacred · Saffron · Gold · Parchment
   ════════════════════════════════════════════════════════════ */

#festivalPanel {
  /* ── Core palette — saffron parchment ── */
  --fp-bg       : #fdf6ec;
  --fp-bg2      : #f9edd8;
  --fp-bg3      : #f0dfc0;
  --fp-surface  : #fffbf5;
  --fp-border   : rgba(168,112,40,.18);
  --fp-border2  : rgba(168,112,40,.32);

  /* ── Gold accent ── */
  --fp-gold     : #9a6b0a;
  --fp-gold-lt  : #f5e6c0;
  --fp-gold-mid : #c89020;
  --fp-gold-dk  : #6b4800;

  /* ── Saffron / vermillion ── */
  --fp-saffron  : #c8521a;
  --fp-saffron-lt: #fde8dc;
  --fp-vermil   : #a02010;
  --fp-vermil-lt: #fdeae8;

  /* ── Deep teal accent ── */
  --fp-teal     : #1a5a50;
  --fp-teal-lt  : #d4eeea;
  --fp-teal-mid : #2a7a6a;

  /* ── Ink / text ── */
  --fp-ink      : #1c1008;
  --fp-ink2     : #3a2410;
  --fp-ink3     : #7a5830;
  --fp-ink4     : #b09070;

  /* ── Hero ── */
  --fp-hero-bg  : #5a2800;
  --fp-hero-bg2 : #3a1500;

  /* ── Shadow ── */
  --fp-shadow   : rgba(90,40,0,.12);

  font-family   : 'Crimson Pro', Georgia, serif;
  color         : var(--fp-ink);
  background    : var(--fp-bg);
  border-radius : 20px;
  overflow      : hidden;
  border        : 2px solid var(--fp-border2);
  box-shadow    : 0 6px 32px var(--fp-shadow);
}

#festivalPanel * { box-sizing: border-box; }

/* ════════════════ HERO ════════════════ */
.fp-hero {
  background    : linear-gradient(150deg, #6b2800 0%, #4a1800 45%, #2a0c00 100%);
  padding       : 40px 48px 34px;
  position      : relative;
  overflow      : hidden;
}

/* Mandala / yantra grid pattern */
.fp-hero::before {
  content       : '';
  position      : absolute; inset: 0;
  background-image:
    radial-gradient(circle at 20% 50%, rgba(200,144,32,.12) 0%, transparent 60%),
    radial-gradient(circle at 80% 20%, rgba(200,80,32,.10) 0%, transparent 50%),
    repeating-linear-gradient(0deg, rgba(200,144,32,.05) 0, rgba(200,144,32,.05) 1px, transparent 1px, transparent 40px),
    repeating-linear-gradient(90deg, rgba(200,144,32,.05) 0, rgba(200,144,32,.05) 1px, transparent 1px, transparent 40px);
  pointer-events: none;
}

/* Large faint Om symbol */
.fp-hero::after {
  content       : 'ॐ';
  position      : absolute;
  right         : 44px; top: 50%;
  transform     : translateY(-50%);
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 9rem; font-weight: 400;
  color         : rgba(255,255,255,.04);
  pointer-events: none;
  user-select   : none;
  line-height   : 1;
}

.fp-hero-inner { position: relative; z-index: 1; }

/* ── eyebrow ── */
.fp-eyebrow {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .78rem; letter-spacing: 3px;
  color         : rgba(255,220,140,.6);
  margin-bottom : 16px;
  display       : flex; align-items: center; gap: 10px;
}
.fp-eyebrow::before,
.fp-eyebrow::after {
  content       : '';
  height        : 1px; flex: 1 1 32px;
  background    : rgba(200,144,32,.3);
}

/* ── main title ── */
.fp-title-row {
  display       : flex; align-items: center;
  gap           : 16px; flex-wrap: wrap;
  margin-bottom : 10px;
}
.fp-title-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 2.8rem; font-weight: 400;
  color         : #fff9f0; line-height: 1.1;
  text-shadow   : 0 2px 12px rgba(0,0,0,.4);
}
.fp-samvat {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .68rem; font-weight: 600;
  color         : rgba(255,220,140,.8);
  background    : rgba(200,144,32,.18);
  border        : 1px solid rgba(200,144,32,.35);
  padding       : 5px 14px; border-radius: 6px;
  letter-spacing: 1.5px;
}
.fp-title-sub {
  font-family   : 'Crimson Pro', serif;
  font-size     : 1.05rem; font-style: italic;
  color         : rgba(255,210,140,.6);
  margin-bottom : 20px;
}

/* ── meta tags ── */
.fp-meta-row {
  display       : flex; align-items: center;
  gap           : 8px; flex-wrap: wrap;
}
.fp-meta-tag {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .82rem; font-weight: 400;
  color         : rgba(255,220,140,.75);
  background    : rgba(200,144,32,.12);
  border        : 1px solid rgba(200,144,32,.25);
  padding       : 5px 14px; border-radius: 20px;
}
.fp-ayanamsa {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .58rem; letter-spacing: 1px;
  color         : rgba(255,200,100,.4);
  background    : rgba(255,255,255,.05);
  border        : 1px solid rgba(255,255,255,.1);
  padding       : 4px 10px; border-radius: 6px;
}

/* ════════════════ YEAR BAR ════════════════ */
.fp-year-bar {
  display       : flex; align-items: center; gap: 10px;
  padding       : 14px 48px;
  background    : var(--fp-surface);
  border-bottom : 2px solid var(--fp-border);
  flex-wrap     : wrap;
}
.fp-yr-btn {
  width         : 36px; height: 36px; border-radius: 8px;
  border        : 1.5px solid var(--fp-border2);
  background    : var(--fp-bg2);
  color         : var(--fp-gold); font-size: 1.2rem; font-weight: 700;
  cursor        : pointer; display: flex; align-items: center; justify-content: center;
  transition    : all .15s; font-family: 'Crimson Pro', serif;
}
.fp-yr-btn:hover {
  background    : var(--fp-gold-lt); border-color: var(--fp-gold);
  color         : var(--fp-gold-dk);
}
.fp-yr-input {
  width         : 86px; padding: 8px 12px;
  border-radius : 8px; border: 1.5px solid var(--fp-border2);
  background    : var(--fp-bg2); color: var(--fp-ink2);
  font-size     : 1rem; font-weight: 700;
  font-family   : 'IBM Plex Mono', monospace;
  text-align    : center; outline: none; transition: all .18s;
}
.fp-yr-input:focus {
  border-color  : var(--fp-gold-mid);
  box-shadow    : 0 0 0 3px rgba(200,144,32,.15);
}
.fp-yr-label {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.1rem; color: var(--fp-ink2);
}
.fp-load-btn {
  display       : flex; align-items: center; gap: 8px;
  padding       : 10px 24px; border-radius: 8px;
  background    : linear-gradient(135deg, var(--fp-saffron) 0%, var(--fp-vermil) 100%);
  color         : #fff; border: none; cursor: pointer;
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .95rem; font-weight: 400;
  letter-spacing: .3px;
  box-shadow    : 0 3px 12px rgba(160,40,16,.3);
  transition    : all .18s;
}
.fp-load-btn:hover {
  transform     : translateY(-2px);
  box-shadow    : 0 6px 18px rgba(160,40,16,.38);
  background    : linear-gradient(135deg, #d95e22 0%, #b02212 100%);
}
.fp-yr-meta {
  margin-left   : auto;
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .58rem; color: var(--fp-ink4);
  letter-spacing: 1.5px; text-transform: uppercase;
}

/* ════════════════ NAV TABS ════════════════ */
.fp-nav {
  background    : var(--fp-bg2);
  border-bottom : 2px solid var(--fp-border2);
  padding       : 0 48px;
  display       : flex;
  overflow-x    : auto; scrollbar-width: none;
  gap           : 4px;
}
.fp-nav::-webkit-scrollbar { display: none; }

.fp-tab {
  flex-shrink   : 0;
  display       : flex; flex-direction: column;
  align-items   : center; justify-content: center;
  gap           : 3px;
  padding       : 14px 22px;
  border        : none; background: transparent;
  cursor        : pointer; white-space: nowrap;
  border-bottom : 3px solid transparent;
  margin-bottom : -2px;
  transition    : all .18s; outline: none;
  position      : relative;
}

.fp-tab-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.1rem; font-weight: 400;
  color         : var(--fp-ink3);
  transition    : color .15s; line-height: 1;
}
.fp-tab-en {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .52rem; letter-spacing: 1.5px;
  text-transform: uppercase;
  color         : var(--fp-ink4);
  transition    : color .15s;
}
.fp-tab:hover { background: var(--fp-gold-lt); }
.fp-tab:hover .fp-tab-hi { color: var(--fp-gold-dk); }
.fp-tab:hover .fp-tab-en { color: var(--fp-gold); }

.fp-tab.fp-active {
  border-bottom-color: var(--fp-saffron);
  background    : rgba(200,82,26,.05);
}
.fp-tab.fp-active .fp-tab-hi { color: var(--fp-saffron); font-weight: 400; }
.fp-tab.fp-active .fp-tab-en { color: var(--fp-saffron); opacity: .7; }

/* ── Tab dividers (tilak marks) ── */
.fp-tab + .fp-tab { border-left: 1px solid var(--fp-border); }

/* ════════════════ SUB PILLS ════════════════ */
#festSubPills {
  padding       : 14px 48px;
  background    : var(--fp-bg3);
  border-bottom : 1.5px solid var(--fp-border2);
  display       : flex; flex-wrap: wrap; gap: 8px; align-items: center;
}
.fp-sub-pill {
  display       : inline-flex; align-items: center; gap: 6px;
  padding       : 7px 18px;
  border        : 1.5px solid var(--fp-border2);
  background    : var(--fp-surface);
  border-radius : 50px;
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .95rem; font-weight: 400;
  color         : var(--fp-ink2); cursor: pointer; transition: all .15s;
}
.fp-sub-pill:hover {
  border-color  : var(--fp-saffron);
  background    : var(--fp-saffron-lt);
  color         : var(--fp-vermil);
  transform     : translateY(-1px);
}
.fp-sub-pill.fp-sub-on {
  background    : var(--fp-saffron-lt) !important;
  border-color  : var(--fp-saffron) !important;
  color         : var(--fp-vermil) !important;
  box-shadow    : 0 2px 8px rgba(160,32,16,.15);
}

/* ════════════════ CONTENT AREA ════════════════ */
#festivalContent {
  padding       : 28px 48px 56px;
  background    : var(--fp-bg);
  min-height    : 360px;
}

/* info strip */
.fp-info-strip {
  display       : flex; align-items: center; gap: 14px;
  padding       : 14px 20px;
  background    : var(--fp-surface);
  border        : 1.5px solid var(--fp-border);
  border-left   : 4px solid var(--fp-gold);
  border-radius : 10px; margin-bottom: 24px;
  box-shadow    : 0 2px 8px var(--fp-shadow);
}
.fp-strip-title {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.1rem; color: var(--fp-ink);
}
.fp-strip-sub {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .58rem; color: var(--fp-ink4);
  letter-spacing: .5px; margin-top: 2px;
}
.fp-strip-count {
  margin-left   : auto;
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .9rem;
  color         : var(--fp-gold-dk);
  background    : var(--fp-gold-lt);
  border        : 1px solid rgba(154,107,10,.25);
  padding       : 5px 18px; border-radius: 20px;
  white-space   : nowrap; flex-shrink: 0;
}

/* month header */
.fp-month-hdr {
  display       : flex; align-items: center; gap: 14px;
  margin        : 28px 0 14px;
  padding       : 12px 20px;
  background    : var(--fp-bg2);
  border        : 1.5px solid var(--fp-border2);
  border-left   : 5px solid var(--fp-saffron);
  border-radius : 10px;
}
.fp-month-en {
  font-family   : 'Crimson Pro', serif;
  font-size     : 1.3rem; font-weight: 700; color: var(--fp-ink);
  letter-spacing: -.2px;
}
.fp-month-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.05rem; color: var(--fp-ink3);
}
.fp-month-ct {
  margin-left   : auto;
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .65rem; font-weight: 700;
  padding       : 4px 12px; border-radius: 20px;
  background    : var(--fp-saffron-lt);
  border        : 1px solid rgba(200,82,26,.2);
  color         : var(--fp-saffron);
}

/* ════════════════════════════════════════════════
   FESTIVAL CARDS — Panchang style
   ════════════════════════════════════════════════ */
.fp-card-grid, .fest-card-grid {
  display       : grid;
  grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
  gap           : 16px; margin-bottom: 6px;
}

.fp-card, .fest-card {
  background    : var(--fp-surface);
  border-radius : 14px; overflow: hidden;
  border        : 1.5px solid var(--fp-border2);
  cursor        : pointer;
  transition    : transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s;
  display       : flex; flex-direction: column;
  box-shadow    : 0 2px 8px var(--fp-shadow);
  position      : relative;
}
.fp-card:hover, .fest-card:hover {
  transform     : translateY(-5px);
  box-shadow    : 0 16px 40px rgba(90,40,0,.15);
  border-color  : var(--fp-gold-mid);
}

/* ── Category color bar — ornate top stripe ── */
.fpc-bar, .fc-stripe {
  height        : 6px; width: 100%; flex-shrink: 0;
  position      : relative;
}
.fpc-bar::after, .fc-stripe::after {
  content       : '';
  position      : absolute; bottom: -1px; left: 50%; transform: translateX(-50%);
  width         : 32px; height: 3px;
  border-radius : 0 0 4px 4px;
  background    : inherit; opacity: .5;
  filter        : blur(2px);
}

/* Category bars — warm, sacred colors */
.fpc-ekadashi        .fpc-bar, .fpc-ekadashi        .fc-stripe { background: linear-gradient(90deg, #4c1d95, #7c3aed); }
.fpc-satyanarayan    .fpc-bar, .fpc-satyanarayan    .fc-stripe { background: linear-gradient(90deg, #1d4ed8, #3b82f6); }
.fpc-pradosh         .fpc-bar, .fpc-pradosh         .fc-stripe { background: linear-gradient(90deg, #1a5a50, #2a9a80); }
.fpc-masik_shivratri .fpc-bar, .fpc-masik_shivratri .fc-stripe { background: linear-gradient(90deg, #3b0764, #7e22ce); }
.fpc-chaturthi       .fpc-bar, .fpc-chaturthi       .fc-stripe { background: linear-gradient(90deg, #9a5800, #d48020); }
.fpc-kalashtami      .fpc-bar, .fpc-kalashtami      .fc-stripe { background: linear-gradient(90deg, #1e293b, #475569); }
.fpc-durgaashtami    .fpc-bar, .fpc-durgaashtami    .fc-stripe { background: linear-gradient(90deg, #881337, #e11d48); }
.fpc-festival        .fpc-bar, .fpc-festival        .fc-stripe { background: linear-gradient(90deg, #c8521a, #f0820a); }
.fpc-purnima         .fpc-bar, .fpc-purnima         .fc-stripe { background: linear-gradient(90deg, #9a6b0a, #c8a020); }
.fpc-amavasya        .fpc-bar, .fpc-amavasya        .fc-stripe { background: linear-gradient(90deg, #1e293b, #475569); }
.fpc-navratri        .fpc-bar, .fpc-navratri        .fc-stripe { background: linear-gradient(90deg, #86198f, #d946ef); }
.fpc-sankranti       .fpc-bar, .fpc-sankranti       .fc-stripe { background: linear-gradient(90deg, #a03818, #e85820); }
.fpc-jayanti         .fpc-bar, .fpc-jayanti         .fc-stripe { background: linear-gradient(90deg, #1a3a6a, #2a5aaa); }
.fpc-shraddha        .fpc-bar, .fpc-shraddha        .fc-stripe { background: linear-gradient(90deg, #334155, #64748b); }
.fpc-national        .fpc-bar, .fpc-national        .fc-stripe { background: linear-gradient(90deg, #14532d, #16a34a); }
.fpc-default         .fpc-bar, .fpc-default         .fc-stripe { background: linear-gradient(90deg, #9a6b0a, #c8a020); }

/* card header */
.fc-head {
  padding       : 18px 20px 12px;
  display       : flex; align-items: flex-start; gap: 14px;
}

/* date badge — parchment scroll style */
.fc-date-box {
  flex-shrink   : 0; width: 60px; padding: 10px 6px;
  border-radius : 10px; text-align: center;
  background    : var(--fp-bg2);
  border        : 1.5px solid var(--fp-border2);
  box-shadow    : inset 0 1px 0 rgba(255,255,255,.8);
}
.fc-date-num {
  font-family   : 'Crimson Pro', serif;
  font-size     : 2rem; font-weight: 700; line-height: 1;
  color         : var(--fp-ink);
}
.fc-date-day {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .52rem; letter-spacing: 1.5px;
  text-transform: uppercase;
  color         : var(--fp-ink3); font-weight: 600; margin-top: 4px;
}
.fc-date-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .72rem; color: var(--fp-ink4); margin-top: 2px;
}

/* name area */
.fc-icon-name { flex: 1; min-width: 0; }
.fc-badges    { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; flex-wrap: wrap; }

/* category chip */
.fc-cat-chip {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .72rem; font-weight: 400;
  padding       : 4px 12px; border-radius: 20px;
  white-space   : nowrap; border: 1px solid;
}

/* per-category chip colors */
.fpc-ekadashi        .fc-cat-chip { background:#ede9fe; color:#5b21b6; border-color:#c4b5fd; }
.fpc-satyanarayan    .fc-cat-chip { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
.fpc-pradosh         .fc-cat-chip { background:#d4eeea; color:#1a5a50; border-color:#86cfc4; }
.fpc-masik_shivratri .fc-cat-chip { background:#f3e8ff; color:#6b21a8; border-color:#d8b4fe; }
.fpc-chaturthi       .fc-cat-chip { background:#fef3c7; color:#92400e; border-color:#fcd34d; }
.fpc-kalashtami      .fc-cat-chip { background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
.fpc-durgaashtami    .fc-cat-chip { background:#ffe4e6; color:#9f1239; border-color:#fca5a5; }
.fpc-festival        .fc-cat-chip { background:#fde8dc; color:#c8521a; border-color:#f4a97e; }
.fpc-purnima         .fc-cat-chip { background:#fef9e0; color:#9a6b0a; border-color:#e8d080; }
.fpc-amavasya        .fc-cat-chip { background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
.fpc-navratri        .fc-cat-chip { background:#fdf4ff; color:#86198f; border-color:#f0abfc; }
.fpc-sankranti       .fc-cat-chip { background:#fde8dc; color:#a03818; border-color:#f4a97e; }
.fpc-jayanti         .fc-cat-chip { background:#dbeafe; color:#1e3a8a; border-color:#93c5fd; }
.fpc-shraddha        .fc-cat-chip { background:#f8fafc; color:#64748b; border-color:#e2e8f0; }
.fpc-national        .fc-cat-chip { background:#dcfce7; color:#14532d; border-color:#86efac; }
.fpc-default         .fc-cat-chip { background:#fef9e0; color:#9a6b0a; border-color:#e8d080; }

.fc-tithi-chip, .fc-tithi-pill {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .72rem;
  padding       : 4px 10px; border-radius: 20px;
  background    : var(--fp-bg2);
  border        : 1px solid var(--fp-border2);
  color         : var(--fp-ink3);
  max-width     : 130px; overflow: hidden;
  text-overflow : ellipsis; white-space: nowrap;
}

.fc-name-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.2rem; font-weight: 400; line-height: 1.35;
  color         : var(--fp-ink); margin-bottom: 3px;
}
.fc-name-en {
  font-family   : 'Crimson Pro', serif;
  font-size     : .85rem; font-style: italic;
  color         : var(--fp-ink3);
  white-space   : nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* card body */
.fc-body {
  padding       : 0 20px 0; flex: 1;
  display       : flex; flex-direction: column; gap: 10px;
}
.fc-sig {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .9rem; line-height: 1.75; color: var(--fp-ink2);
  padding       : 10px 14px;
  background    : var(--fp-bg2);
  border-left   : 3px solid var(--fp-border2);
  border-radius : 0 8px 8px 0;
  display       : -webkit-box;
  -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}
.fc-timing {
  display       : flex; gap: 8px; flex-wrap: wrap;
}
.fc-time-chip {
  display       : inline-flex; align-items: center; gap: 5px;
  padding       : 5px 12px; border-radius: 8px;
  background    : var(--fp-bg2); border: 1px solid var(--fp-border);
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .68rem; font-weight: 600; color: var(--fp-ink3);
}

/* card footer */
.fc-footer {
  padding       : 11px 20px 15px;
  display       : flex; align-items: center; justify-content: space-between;
  border-top    : 1px solid var(--fp-border);
  margin-top    : 12px;
}
.fc-masa {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .78rem;
  padding       : 4px 12px; border-radius: 20px;
  background    : var(--fp-bg3); border: 1px solid var(--fp-border);
  color         : var(--fp-ink3);
}
.fc-detail-hint {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .82rem;
  color         : var(--fp-saffron); opacity: .5; transition: opacity .15s;
}
.fp-card:hover .fc-detail-hint,
.fest-card:hover .fc-detail-hint { opacity: 1; }

/* ════════════════ MODAL — Panchang scroll style ════════════════ */
#festDetailOverlay {
  position      : fixed; inset: 0; z-index: 9999;
  background    : rgba(60,20,0,.55);
  backdrop-filter: blur(6px);
  display       : flex; align-items: center; justify-content: center;
  padding       : 20px;
  animation     : fp-fadein .2s ease;
}
@keyframes fp-fadein { from { opacity: 0; } to { opacity: 1; } }

.fp-modal {
  background    : #fffbf5 !important;
  border-radius : 20px; width: 100%;
  max-width     : 640px; max-height: 88vh;
  overflow-y    : auto;
  box-shadow    : 0 40px 100px rgba(60,20,0,.35), 0 0 0 2px var(--fp-border2);
  animation     : fp-slide .24s cubic-bezier(.4,0,.2,1);
  position      : relative;
  border        : 1.5px solid var(--fp-border2);
}
@keyframes fp-slide {
  from { transform: translateY(24px) scale(.96); opacity: 0; }
  to   { transform: none; opacity: 1; }
}
.fp-modal::-webkit-scrollbar { width: 5px; }
.fp-modal::-webkit-scrollbar-thumb { background: var(--fp-border2); border-radius: 3px; }

/* Modal banner — sacred scroll header */
.fp-modal-banner {
  padding       : 30px 30px 24px;
  border-radius : 18px 18px 0 0;
  border-bottom : 2px solid var(--fp-border2);
  position      : relative; overflow: hidden;
}

/* Grid pattern on modal banner */
.fp-modal-banner::before {
  content       : '';
  position      : absolute; inset: 0;
  background-image:
    radial-gradient(circle at 15% 50%, rgba(255,200,100,.15) 0%, transparent 55%),
    repeating-linear-gradient(0deg, rgba(255,200,100,.06) 0, rgba(255,200,100,.06) 1px, transparent 1px, transparent 32px),
    repeating-linear-gradient(90deg, rgba(255,200,100,.06) 0, rgba(255,200,100,.06) 1px, transparent 1px, transparent 32px);
  pointer-events: none;
}
/* Large Om in corner */
.fp-modal-banner::after {
  content       : 'ॐ';
  position      : absolute; right: 20px; top: 50%;
  transform     : translateY(-50%);
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 6rem;
  color         : rgba(255,255,255,.06);
  pointer-events: none; user-select: none;
}

.fp-modal-close {
  position      : absolute; top: 14px; right: 16px;
  width         : 36px; height: 36px; border-radius: 8px;
  background    : rgba(255,255,255,.15);
  border        : 1px solid rgba(255,255,255,.3);
  cursor        : pointer; display: flex; align-items: center; justify-content: center;
  font-size     : 1rem; color: rgba(255,255,255,.85);
  transition    : all .15s; z-index: 2;
  font-family   : sans-serif;
}
.fp-modal-close:hover { background: rgba(255,255,255,.28); color: #fff; }

.fp-modal-hero {
  position      : relative; z-index: 1;
  display       : flex; align-items: flex-start; gap: 20px;
}
.fp-modal-date {
  flex-shrink   : 0; width: 80px; padding: 14px 8px;
  border-radius : 14px; text-align: center;
  background    : rgba(255,255,255,.18);
  border        : 1.5px solid rgba(255,255,255,.3);
}
.fp-modal-date-num {
  font-family   : 'Crimson Pro', serif;
  font-size     : 2.8rem; font-weight: 700; line-height: 1;
  color         : #fff;
}
.fp-modal-date-dow-en {
  font-family   : 'IBM Plex Mono', monospace;
  font-size     : .58rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1.5px;
  margin-top    : 5px; color: rgba(255,255,255,.65);
}
.fp-modal-date-dow-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .9rem; margin-top: 3px;
  color         : rgba(255,255,255,.55);
}
.fp-modal-names { flex: 1; position: relative; z-index: 1; }
.fp-modal-name-hi {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 2rem; font-weight: 400; line-height: 1.25;
  color         : #fff; margin-bottom: 5px;
}
.fp-modal-name-en {
  font-family   : 'Crimson Pro', serif;
  font-size     : 1rem; font-style: italic;
  color         : rgba(255,255,255,.6); margin-bottom: 16px;
}
.fp-modal-meta { display: flex; gap: 8px; flex-wrap: wrap; }
.fp-modal-badge {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .75rem;
  padding       : 5px 14px; border-radius: 20px;
  background    : rgba(255,255,255,.15);
  border        : 1px solid rgba(255,255,255,.28);
  color         : rgba(255,255,255,.88); white-space: nowrap;
}

/* modal body — parchment */
.fp-modal-body {
  padding       : 26px 30px 36px;
  background    : #fdf6ec !important;
  /* subtle texture */
  background-image   : radial-gradient(circle at 80% 10%, rgba(200,144,32,.04) 0%, transparent 50%) !important;
}

.fp-modal-section {  background: transparent; }
.fp-modal-sec-title {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .8rem; color: #9a6b0a !important;
  margin-bottom : 14px;
  display       : flex; align-items: center; gap: 12px;
}
.fp-modal-sec-title::before {
  content       : '॥';
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1rem; color: var(--fp-gold-mid);
}
.fp-modal-sec-title::after {
  content       : '';
  flex          : 1; height: 1px;
  background    : linear-gradient(90deg, var(--fp-border2), transparent);
}

.fp-modal-sig {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1rem; line-height: 1.9; color: var(--fp-ink2);
  padding       : 16px 20px; border-radius: 10px;
  background    : #fffbf5 !important;
  border        : 1.5px solid var(--fp-border);
  border-left-color : #c89020;
}

.fp-modal-rituals { display: flex; flex-direction: column; gap: 8px; }
.fp-modal-ritual {
  display       : flex; align-items: flex-start; gap: 14px;
  padding       : 12px 16px; border-radius: 10px;
  background: #fffbf5 !important; border-color: rgba(168,112,40,.18);
  transition    : background .12s;
}
.fp-modal-ritual:hover { background: #f9edd8 !important; border-color: var(--fp-border2); }
.fp-ritual-num {
  flex-shrink   : 0; width: 28px; height: 28px;
  border-radius : 6px; display: flex; align-items: center; justify-content: center;
  font-family   : 'Crimson Pro', serif;
  font-size     : .85rem; font-weight: 700; color : #c8521a !important;
  background    : #fde8dc !important; border-color  : rgba(200,82,26,.2) !important;
}
.fp-ritual-text {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .95rem; line-height: 1.7; color: var(--fp-ink2); flex: 1;
}

.fp-modal-mantra {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.15rem; font-weight: 400; text-align: center;
  padding       : 18px 24px; border-radius: 12px; line-height: 2.2;
  background    : linear-gradient(135deg, #f9edd8, #f5e6c0) !important;
  border-color  : rgba(168,112,40,.32) !important;
  color         : #6b4800 !important;
  box-shadow    : inset 0 1px 0 rgba(255,255,255,.8);
}
.fp-modal-details {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .92rem; line-height: 1.9; color: #3a2410 !important; 
  background: #fffbf5 !important; border-color: rgba(168,112,40,.18);
  padding       : 14px 18px; border: 1px solid var(--fp-border);
}
.fp-modal-timing { display: flex; gap: 12px; flex-wrap: wrap; }
.fp-modal-time-chip {
  display       : flex; align-items: center; gap: 10px;
  padding       : 12px 18px; border-radius: 10px;
  background    : #f9edd8 !important; border-color: rgba(168,112,40,.18);
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : .95rem; color: #3a2410 !important;
}

/* ── Loading & empty ── */
.fp-loading {
  display       : flex; flex-direction: column; align-items: center;
  padding       : 80px 24px; gap: 22px;
}
.fp-loader {
  width         : 46px; height: 46px;
  border        : 3px solid var(--fp-border2);
  border-top-color: var(--fp-saffron); border-radius: 50%;
  animation     : fp-spin .9s linear infinite;
}
@keyframes fp-spin { to { transform: rotate(360deg); } }
.fp-loading-text {
  font-family   : 'Tiro Devanagari Sanskrit', serif;
  font-size     : 1.05rem; color: var(--fp-ink3);
  text-align    : center; line-height: 2;
}
.fp-empty {
  text-align    : center; padding: 72px 24px;
  border        : 1.5px dashed var(--fp-border2);
  border-radius : 14px; background: var(--fp-surface);
  color         : var(--fp-ink4);
  font-family   : 'Tiro Devanagari Sanskrit', serif; font-size: 1.05rem;
}

/* Responsive */
@media (max-width: 720px) {
  .fp-hero, .fp-year-bar, .fp-nav, #festSubPills, #festivalContent { padding-left: 18px; padding-right: 18px; }
  .fp-title-hi { font-size: 2.1rem; }
  .fp-modal { max-height: 94vh; border-radius: 18px 18px 0 0; }
  #festDetailOverlay { align-items: flex-end; padding: 0; }
  .fp-modal-name-hi { font-size: 1.55rem; }
  .fp-card-grid, .fest-card-grid { grid-template-columns: 1fr; }
  .fp-hero::after { display: none; }
}
</style>

<div id="festivalPanel" style="display:none">

  {{-- HERO --}}
  <div class="fp-hero">
    <div class="fp-hero-inner">
      <div class="fp-eyebrow">वैदिक पञ्चाङ्ग · लाहिरी अयनांश</div>
      <div class="fp-title-row">
        <div class="fp-title-hi">पंचांग कैलेंडर</div>
        <span class="fp-samvat">विक्रम संवत् २०८२</span>
      </div>
      <div class="fp-title-sub">Hindu Festival, Vrat &amp; Observance Calendar</div>
      <div class="fp-meta-row">
        <span class="fp-meta-tag">सभी व्रत</span>
        <span class="fp-meta-tag">त्योहार</span>
        <span class="fp-meta-tag">जयंती</span>
        <span class="fp-meta-tag">संक्रांति</span>
        <span class="fp-meta-tag">पूर्णिमा</span>
        <span class="fp-meta-tag">एकादशी</span>
        <span class="fp-ayanamsa">Jean Meeus Algorithms</span>
      </div>
    </div>
  </div>

  {{-- YEAR BAR --}}
  <div class="fp-year-bar">
    <button class="fp-yr-btn" onclick="festNav(-1)">‹</button>
    <input type="number" id="festYear" class="fp-yr-input" min="1900" max="2100" value="2026" onchange="festYearChanged()"/>
    <button class="fp-yr-btn" onclick="festNav(1)">›</button>
    <span class="fp-yr-label" id="festYearLabel">२०२६</span>
    <button class="fp-load-btn" onclick="festLoad()">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="14" height="13" rx="2"/>
        <path d="M3 8h14M8 4V2M12 4V2"/>
      </svg>
      वर्ष लोड करें
    </button>
    <span class="fp-yr-meta">Jean Meeus Algorithm</span>
  </div>

  {{-- NAV TABS --}}
  <div class="fp-nav">
    <button class="fp-tab fp-active" id="fmain_all" onclick="fmainSwitch('all',this)">
      <span class="fp-tab-hi">सभी</span>
      <span class="fp-tab-en">All</span>
    </button>
    <button class="fp-tab" id="fmain_vrat" onclick="fmainSwitch('vrat',this)">
      <span class="fp-tab-hi">व्रत</span>
      <span class="fp-tab-en">Vrat</span>
    </button>
    <button class="fp-tab" id="fmain_parv" onclick="fmainSwitch('parv',this)">
      <span class="fp-tab-hi">पर्व</span>
      <span class="fp-tab-en">Festival</span>
    </button>
    <button class="fp-tab" id="fmain_jayanti" onclick="fmainSwitch('jayanti',this)">
      <span class="fp-tab-hi">जयंती</span>
      <span class="fp-tab-en">Jayanti</span>
    </button>
    <button class="fp-tab" id="fmain_other" onclick="fmainSwitch('other',this)">
      <span class="fp-tab-hi">अन्य</span>
      <span class="fp-tab-en">Other</span>
    </button>
  </div>

  {{-- SUB PILLS --}}
  <div id="festSubPills" style="display:none"></div>

  {{-- CONTENT --}}
  <div id="festivalContent">
    <div class="fp-loading">
      <div class="fp-loader"></div>
      <div class="fp-loading-text">
        वर्ष चुनें और<br>
        <strong style="color:var(--fp-saffron)">वर्ष लोड करें</strong> दबाएं
      </div>
    </div>
  </div>

</div>