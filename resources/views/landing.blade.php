<!DOCTYPE html>
<html lang="en" data-theme="dark" data-lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Vedic Astro Calculator — Jyotish Engine</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web@2.0.3/src/index.js"></script>
<style>
/* ═══════════════════ DESIGN TOKENS ═══════════════════ */
:root {
  /* dark (default) */
  --bg:      #060b14;
  --bg2:     #0a1020;
  --bg3:     #101828;
  --card:    rgba(255,255,255,.045);
  --card-b:  rgba(255,255,255,.09);
  --card-h:  rgba(255,255,255,.07);
  --gold:    #c8a84b;
  --gold-l:  #f0d470;
  --gold-d:  #8a6820;
  --sky:     #3a80b8;
  --sky-l:   #68aad8;
  --sky-d:   #1a4870;
  --text:    #e4dac8;
  --text-m:  #a8997a;
  --text-d:  #6a5f4a;
  --accent:  #e87828;
  --green:   #28a870;
  --red:     #d83820;
  --purple:  #6040a8;
  --nav-bg:  rgba(6,11,20,.75);
  --rx:      18px;
  --shadow:  0 8px 32px rgba(0,0,0,.45);
}
[data-theme="light"] {
  --bg:      #f5f0e8;
  --bg2:     #ede7d8;
  --bg3:     #e4dcc8;
  --card:    rgba(255,255,255,.82);
  --card-b:  rgba(0,0,0,.08);
  --card-h:  rgba(255,255,255,.95);
  --gold:    #8a6010;
  --gold-l:  #c8900a;
  --gold-d:  #5a4008;
  --sky:     #1a5e90;
  --sky-l:   #2a7ab0;
  --sky-d:   #0e3a60;
  --text:    #1a1208;
  --text-m:  #5a4830;
  --text-d:  #8a7458;
  --accent:  #c05800;
  --green:   #1a7a50;
  --red:     #b02808;
  --purple:  #4828a0;
  --nav-bg:  rgba(245,240,232,.9);
  --shadow:  0 4px 20px rgba(0,0,0,.12);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',system-ui,sans-serif;overflow-x:hidden;line-height:1.6;transition:background .35s,color .35s}

/* ═══════════════════ NAV ═══════════════════ */
nav{position:fixed;top:0;inset-inline:0;z-index:200;height:60px;display:flex;align-items:center;padding:0 32px;gap:24px;backdrop-filter:blur(20px);background:var(--nav-bg);border-bottom:1px solid var(--card-b);transition:background .35s}
.nav-logo{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--gold);text-decoration:none;flex-shrink:0}
.nav-logo small{font-weight:400;font-size:.72rem;color:var(--text-d);margin-left:6px;font-family:'DM Sans',sans-serif}
.nav-links{display:flex;align-items:center;gap:20px;margin-left:auto}
.nav-links a{font-size:.83rem;font-weight:500;color:var(--text-m);text-decoration:none;transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:var(--gold)}
.nav-sep{width:1px;height:16px;background:var(--card-b)}
.nav-icon-btn{width:34px;height:34px;border-radius:50%;border:1px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:border-color .2s,background .2s;font-size:1rem;color:var(--text-m)}
.nav-icon-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--card-h)}
.lang-btn{min-width:34px;padding:0 10px;font-size:.7rem;font-weight:800;letter-spacing:.5px;border-radius:20px}
.nav-cta{padding:7px 20px;border-radius:40px;background:linear-gradient(135deg,var(--gold),var(--gold-d));color:#fff;font-size:.8rem;font-weight:700;text-decoration:none;transition:opacity .2s;white-space:nowrap}
.nav-cta:hover{opacity:.85}
@media(max-width:768px){.nav-links a:not(.nav-cta){display:none}.nav-sep{display:none}}

/* ═══════════════════ HERO ═══════════════════ */
.hero{position:relative;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:40px;padding:80px 80px 60px;overflow:hidden}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:80px 24px 48px;text-align:center}}
.hero-bg{position:absolute;inset:0;z-index:0}
.hero-bg::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 65% 50%,rgba(58,128,184,.1),transparent),radial-gradient(ellipse 50% 70% at 20% 90%,rgba(96,64,168,.08),transparent)}
[data-theme="light"] .hero-bg::before{background:radial-gradient(ellipse 70% 60% at 65% 50%,rgba(26,94,144,.07),transparent),radial-gradient(ellipse 50% 70% at 20% 90%,rgba(72,40,160,.05),transparent)}
#starsCanvas{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
[data-theme="light"] #starsCanvas{opacity:.3}
.hero-content{position:relative;z-index:2}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(200,168,75,.1);border:1px solid rgba(200,168,75,.22);border-radius:40px;padding:5px 16px;font-size:.68rem;font-weight:800;color:var(--gold);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:22px}
.hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.4rem,4.5vw,3.6rem);font-weight:700;line-height:1.1;margin-bottom:16px;color:var(--text)}
.hero-title em{color:var(--gold);font-style:italic}
.hero-sub{font-size:1rem;color:var(--text-m);margin-bottom:30px;line-height:1.75;max-width:460px}
@media(max-width:900px){.hero-sub{margin:0 auto 28px}}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
@media(max-width:900px){.hero-btns{justify-content:center}}
.btn-p{background:linear-gradient(135deg,#c8901a,#7a4800);color:#fff;padding:13px 30px;border-radius:50px;font-weight:700;font-size:.9rem;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:transform .15s,box-shadow .15s;box-shadow:0 6px 20px rgba(200,120,26,.35)}
.btn-p:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,120,26,.55)}
.btn-g{color:var(--text-m);border:1.5px solid var(--card-b);padding:12px 24px;border-radius:50px;font-size:.88rem;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:border-color .2s,color .2s}
.btn-g:hover{border-color:var(--gold);color:var(--gold)}
/* Orrery */
.hero-visual{position:relative;z-index:2;display:flex;align-items:center;justify-content:center}
.orrery-wrap{position:relative;width:470px;height:470px;max-width:100%}
@media(max-width:900px){.orrery-wrap{width:300px;height:300px}}
#zodiacRing{position:absolute;inset:0;width:100%;height:100%;animation:zspin 120s linear infinite}
#orreryCanvas{position:absolute;inset:0;width:100%;height:100%}
@keyframes zspin{to{transform:rotate(360deg)}}

/* ═══════════════════ SECTION BASE ═══════════════════ */
.section{padding:72px 80px}
@media(max-width:700px){.section{padding:52px 20px}}
.s-eyebrow{font-size:.6rem;font-weight:900;text-transform:uppercase;letter-spacing:2.5px;color:var(--gold);margin-bottom:10px;display:flex;align-items:center;gap:10px}
.s-eyebrow::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,var(--gold-d),transparent)}
.s-title{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.3rem);font-weight:700;color:var(--text);margin-bottom:10px;line-height:1.25}
.s-sub{color:var(--text-m);font-size:.92rem;max-width:520px;line-height:1.75;margin-bottom:40px}
.divider{height:1px;background:linear-gradient(90deg,transparent,var(--card-b),transparent);margin:0 80px}
@media(max-width:700px){.divider{margin:0 20px}}

/* ═══════════════════ PANCHANGA ═══════════════════ */
.pancha-section{background:var(--bg2)}
.day-nav{display:flex;align-items:center;gap:14px;margin-bottom:32px;flex-wrap:wrap}
.day-nav-btn{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-m);font-size:1.1rem;transition:all .2s;flex-shrink:0}
.day-nav-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--card-h)}
.day-nav-btn:disabled{opacity:.35;cursor:not-allowed}
.day-display{display:flex;flex-direction:column}
.day-display .dd-date{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--text);line-height:1}
.day-display .dd-sub{font-size:.72rem;color:var(--text-d);margin-top:3px;font-family:'DM Mono',monospace}
.day-today-btn{margin-left:auto;font-size:.72rem;font-weight:700;color:var(--gold);border:1px solid rgba(200,168,75,.3);background:rgba(200,168,75,.08);padding:5px 14px;border-radius:20px;cursor:pointer;transition:all .2s}
.day-today-btn:hover{background:rgba(200,168,75,.18)}
.loading-overlay{position:absolute;inset:0;background:var(--bg2);opacity:0;pointer-events:none;z-index:10;display:flex;align-items:center;justify-content:center;border-radius:var(--rx);transition:opacity .2s}
.loading-overlay.show{opacity:.75;pointer-events:all}

/* Panchanga grid */
.pancha-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px}
.pa-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:20px;position:relative;overflow:hidden;transition:transform .2s,border-color .2s,box-shadow .2s}
.pa-card:hover{transform:translateY(-3px);border-color:var(--card-b);box-shadow:var(--shadow)}
.pa-card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px}
.pa-anga-label{font-size:.58rem;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-d)}
.pa-anga-num{width:22px;height:22px;border-radius:50%;background:var(--card-b);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:var(--text-d);flex-shrink:0}
/* diagram canvas */
.pa-diagram{width:100%;display:flex;justify-content:center;margin-bottom:14px}
.pa-diagram canvas{display:block}
/* values */
.pa-val-name{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--text);line-height:1.2;margin-bottom:3px}
.pa-val-sub{font-size:.75rem;color:var(--text-m);line-height:1.5}
.pa-val-meta{font-size:.68rem;color:var(--text-d);margin-top:6px;display:flex;gap:10px;flex-wrap:wrap}
.pa-meta-pill{background:var(--card-b);border-radius:20px;padding:2px 8px}
/* color bars per anga */
.pa-card[data-anga="tithi"]  {border-top:2px solid #6040a8}
.pa-card[data-anga="vara"]   {border-top:2px solid #d4921e}
.pa-card[data-anga="nak"]    {border-top:2px solid #3a80b8}
.pa-card[data-anga="yoga"]   {border-top:2px solid #28a870}
.pa-card[data-anga="karana"] {border-top:2px solid #c8901a}
.pa-card[data-anga="sun"]    {border-top:2px solid #e87828}

/* ═══════════════════ PLANETS ═══════════════════ */
.planets-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:12px}
.pcard{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:16px 18px;display:flex;gap:12px;transition:transform .2s,box-shadow .2s}
.pcard:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.pc-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.pc-body{flex:1;min-width:0}
.pc-label{font-size:.56rem;font-weight:900;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:2px}
.pc-name{font-weight:700;font-size:.82rem;color:var(--text-m)}
.pc-sign{font-family:'Playfair Display',serif;font-size:1.02rem;font-weight:700;color:var(--text);margin-top:4px}
.pc-nak{font-size:.7rem;color:var(--text-d);margin-top:2px}
.pc-deg{font-family:'DM Mono',monospace;font-size:.68rem;color:var(--text-d)}
.pc-retro{font-size:.58rem;font-weight:800;background:rgba(232,120,40,.12);color:var(--accent);border:1px solid rgba(232,120,40,.25);border-radius:20px;padding:1px 7px;margin-top:4px;display:inline-block}

/* ═══════════════════ FEATURES ═══════════════════ */
.feat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.feat-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:26px;transition:transform .2s,box-shadow .2s,border-color .2s;position:relative;overflow:hidden}
.feat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow);border-color:rgba(200,168,75,.3)}
.feat-card::before{content:'';position:absolute;top:0;inset-inline:0;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:0;transition:opacity .3s}
.feat-card:hover::before{opacity:1}
.feat-icon-wrap{width:46px;height:46px;border-radius:14px;background:var(--card-b);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--gold);margin-bottom:16px;border:1px solid var(--card-b)}
.feat-title{font-family:'Playfair Display',serif;font-size:1.08rem;font-weight:700;color:var(--text);margin-bottom:8px}
.feat-desc{font-size:.82rem;color:var(--text-m);line-height:1.7}
.feat-link{display:inline-flex;align-items:center;gap:5px;margin-top:14px;font-size:.78rem;font-weight:700;color:var(--gold);text-decoration:none;opacity:.75;transition:opacity .2s}
.feat-link:hover{opacity:1}

/* ═══════════════════ FESTIVALS ═══════════════════ */
.fest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px}
.fest-card{background:var(--card);border:1px solid var(--card-b);border-radius:16px;padding:16px 18px;display:flex;gap:14px;align-items:flex-start;transition:transform .2s}
.fest-card:hover{transform:translateY(-2px)}
.fest-icon-wrap{width:42px;height:42px;border-radius:12px;background:var(--card-b);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem}
.fest-body{flex:1}
.fest-date{font-family:'DM Mono',monospace;font-size:.66rem;font-weight:600;color:var(--gold);margin-bottom:3px}
.fest-name{font-weight:700;font-size:.86rem;color:var(--text);line-height:1.3;margin-bottom:2px}
.fest-masa{font-size:.7rem;color:var(--text-d)}
.fest-badge{display:inline-block;font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;padding:2px 8px;border-radius:20px;margin-top:6px;border:1px solid transparent}
.fb-festival{background:rgba(200,168,75,.1);color:var(--gold);border-color:rgba(200,168,75,.2)}
.fb-vrat{background:rgba(58,128,184,.1);color:var(--sky-l);border-color:rgba(58,128,184,.2)}

/* ═══════════════════ DASHA ═══════════════════ */
.dasha-layout{display:grid;grid-template-columns:280px 1fr;gap:60px;align-items:center}
@media(max-width:800px){.dasha-layout{grid-template-columns:1fr;text-align:center}.dasha-wheel-wrap{margin:0 auto}}
.dasha-wheel-wrap{width:280px;height:280px;flex-shrink:0}
.dl-list{display:flex;flex-direction:column;gap:9px}
.dl-item{display:grid;grid-template-columns:22px 90px 44px 1fr;gap:8px;align-items:center}
.dl-dot{width:10px;height:10px;border-radius:50%}
.dl-name{font-size:.82rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:5px}
.dl-yrs{font-size:.72rem;color:var(--text-d);font-family:'DM Mono',monospace;text-align:right}
.dl-bar-bg{height:4px;background:var(--card-b);border-radius:4px;overflow:hidden}
.dl-bar-fill{height:100%;border-radius:4px;transition:width .6s}

/* ═══════════════════ CTA / FOOTER ═══════════════════ */
.cta-section{text-align:center;padding:100px 24px;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 70% at 50% 50%,rgba(200,168,75,.07),transparent)}
[data-theme="light"] .cta-section::before{background:radial-gradient(ellipse 60% 70% at 50% 50%,rgba(138,96,16,.06),transparent)}
footer{background:var(--bg2);border-top:1px solid var(--card-b);padding:28px 80px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
@media(max-width:700px){footer{padding:28px 20px;flex-direction:column;text-align:center}}
.footer-note{font-size:.76rem;color:var(--text-d);line-height:1.7}
.footer-links{display:flex;gap:18px}
.footer-links a{font-size:.76rem;color:var(--text-d);text-decoration:none;transition:color .2s}
.footer-links a:hover{color:var(--gold)}

/* ═══════════════════ SCROLL REVEAL ═══════════════════ */
.reveal{opacity:0;transform:translateY(24px);transition:opacity .65s cubic-bezier(.4,0,.2,1),transform .65s cubic-bezier(.4,0,.2,1)}
.reveal.in{opacity:1;transform:none}
</style>
</head>
<body>

{{-- ════════ NAV ════════ --}}
<nav>
  <a href="/" class="nav-logo">Vedic Astro <small>Calculator</small></a>
  <div class="nav-links">
    <a href="#panchanga" class="t" data-en="Panchanga" data-hi="पंचांग">Panchanga</a>
    <a href="#planets"   class="t" data-en="Planets"   data-hi="ग्रह">Planets</a>
    <a href="#features"  class="t" data-en="Features"  data-hi="विशेषताएं">Features</a>
    <a href="#festivals" class="t" data-en="Festivals" data-hi="उत्सव">Festivals</a>
    <div class="nav-sep"></div>
    {{-- Language toggle --}}
    <button class="nav-icon-btn lang-btn" id="langToggle" title="Toggle language" onclick="toggleLang()">EN</button>
    {{-- Theme toggle --}}
    <button class="nav-icon-btn" id="themeToggle" title="Toggle theme" onclick="toggleTheme()">
      <i class="ph ph-moon-stars" id="themeIcon"></i>
    </button>
    <div class="nav-sep"></div>
    <a href="/astro" class="nav-cta t" data-en="Open Calculator" data-hi="कैलकुलेटर खोलें">Open Calculator</a>
  </div>
</nav>

{{-- ════════ HERO ════════ --}}
<section class="hero">
  <div class="hero-bg"></div>
  <canvas id="starsCanvas"></canvas>

  <div class="hero-content">
    <div class="hero-badge">
      <i class="ph ph-star-four" style="font-size:.85rem"></i>
      <span class="t" data-en="Vedic Jyotish · Jean Meeus Algorithms" data-hi="वैदिक ज्योतिष · जीन मेउस एल्गोरिदम">Vedic Jyotish · Jean Meeus Algorithms</span>
    </div>
    <h1 class="hero-title">
      Vedic <em>Astro</em><br>
      <span class="t" data-en="Calculator" data-hi="कैलकुलेटर">Calculator</span>
    </h1>
    <p class="hero-sub t" data-en="Precise planetary calculations, live Panchanga, Vimshottari Dasha, Muhurat &amp; 20 divisional charts — computed in real time." data-hi="सटीक ग्रह गणना, जीवंत पंचांग, विंशोत्तरी दशा, मुहूर्त और 20 विभागीय कुंडली — रियल टाइम में।">
      Precise planetary calculations, live Panchanga, Vimshottari Dasha, Muhurat &amp; 20 divisional charts — computed in real time.
    </p>
    <div class="hero-btns">
      <a href="/astro" class="btn-p">
        <i class="ph ph-star-four"></i>
        <span class="t" data-en="Calculate Your Chart" data-hi="कुंडली बनाएं">Calculate Your Chart</span>
      </a>
      <a href="#panchanga" class="btn-g">
        <span class="t" data-en="Today's Panchanga" data-hi="आज का पंचांग">Today's Panchanga</span>
        <i class="ph ph-arrow-down"></i>
      </a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="orrery-wrap" id="orreryWrap">
      <svg id="zodiacRing" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg"></svg>
      <canvas id="orreryCanvas" width="500" height="500"></canvas>
    </div>
  </div>
</section>

{{-- ════════ PANCHANGA SECTION ════════ --}}
<section class="section pancha-section" id="panchanga">
  <div class="s-eyebrow"><i class="ph ph-calendar-dots"></i> <span class="t" data-en="Live Panchanga · New Delhi, IST" data-hi="लाइव पंचांग · नई दिल्ली, IST">Live Panchanga · New Delhi, IST</span></div>

  {{-- Day navigation --}}
  <div class="day-nav">
    <button class="day-nav-btn" id="btnPrev" onclick="shiftDay(-1)" title="Previous day"><i class="ph ph-caret-left"></i></button>
    <button class="day-nav-btn" id="btnNext" onclick="shiftDay(1)"  title="Next day"><i class="ph ph-caret-right"></i></button>
    <div class="day-display">
      <div class="dd-date" id="ddDate">{{ $dateDisplay }}</div>
      <div class="dd-sub" id="ddSub">{{ $dayName }} · Ayanamsa {{ $ayan }}°</div>
    </div>
    <button class="day-today-btn" onclick="gotoToday()">
      <span class="t" data-en="Today" data-hi="आज">Today</span>
    </button>
  </div>

  {{-- The 5 angas + sunrise --}}
  <div class="pancha-grid reveal" id="panchaGrid">

    {{-- 1. TITHI --}}
    <div class="pa-card" data-anga="tithi">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-moon" style="margin-right:4px"></i><span class="t" data-en="Tithi — Lunar Day" data-hi="तिथि — चंद्र दिवस">Tithi — Lunar Day</span></span>
        <span class="pa-anga-num">①</span>
      </div>
      <div class="pa-diagram"><canvas id="cvTithi" width="160" height="160"></canvas></div>
      <div class="pa-val-name" id="pvTithiName">{{ $tithi['name'] }}</div>
      <div class="pa-val-sub" id="pvTithiSub">{{ $tithi['paksha'] }} Paksha · {{ $tithi['num'] }}/15</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill" id="pvTithiLord"><span class="t" data-en="Lord:" data-hi="स्वामी:">Lord:</span> {{ $tithi['lord'] }}</span>
        <span class="pa-meta-pill" id="pvTithiElong">{{ $tithi['elong'] }}°</span>
      </div>
    </div>

    {{-- 2. VARA --}}
    <div class="pa-card" data-anga="vara">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-sun" style="margin-right:4px"></i><span class="t" data-en="Vara — Weekday" data-hi="वार — सप्ताह का दिन">Vara — Weekday</span></span>
        <span class="pa-anga-num">②</span>
      </div>
      <div class="pa-diagram"><canvas id="cvVara" width="160" height="160"></canvas></div>
      <div class="pa-val-name" id="pvVaraName">{{ $vara['name'] }}</div>
      <div class="pa-val-sub" id="pvVaraSub">{{ $vara['en'] }} · <span class="t" data-en="Lord:" data-hi="स्वामी:">Lord:</span> {{ $vara['lord'] }}</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill" id="pvVaraNature">{{ $vara['nature'] }}</span>
      </div>
    </div>

    {{-- 3. NAKSHATRA --}}
    <div class="pa-card" data-anga="nak">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-star" style="margin-right:4px"></i><span class="t" data-en="Nakshatra — Moon Mansion" data-hi="नक्षत्र — चंद्र भवन">Nakshatra — Moon Mansion</span></span>
        <span class="pa-anga-num">③</span>
      </div>
      <div class="pa-diagram"><canvas id="cvNak" width="160" height="160"></canvas></div>
      <div class="pa-val-name" id="pvNakName">{{ $nakshatra['name'] }}</div>
      <div class="pa-val-sub" id="pvNakSub"><span class="t" data-en="Pada" data-hi="पाद">Pada</span> {{ $nakshatra['pada'] }} · {{ $nakshatra['lord'] }}</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill" id="pvNakGana">{{ $nakshatra['gana'] }}</span>
        <span class="pa-meta-pill" id="pvNakDeity">{{ $nakshatra['deity'] }}</span>
      </div>
    </div>

    {{-- 4. YOGA --}}
    <div class="pa-card" data-anga="yoga">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-compass" style="margin-right:4px"></i><span class="t" data-en="Yoga — Luni-Solar" data-hi="योग — चंद्र-सौर">Yoga — Luni-Solar</span></span>
        <span class="pa-anga-num">④</span>
      </div>
      <div class="pa-diagram"><canvas id="cvYoga" width="160" height="160"></canvas></div>
      <div class="pa-val-name" id="pvYogaName">{{ $yoga['name'] }}</div>
      <div class="pa-val-sub" id="pvYogaSub">{{ $yoga['nature'] }} · <span class="t" data-en="Lord:" data-hi="स्वामी:">Lord:</span> {{ $yoga['lord'] }}</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill" id="pvYogaCls">{{ $yoga['cls'] }}</span>
        <span class="pa-meta-pill" id="pvYogaNum">{{ $yoga['idx'] + 1 }}/27</span>
      </div>
    </div>

    {{-- 5. KARANA --}}
    <div class="pa-card" data-anga="karana">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-circle-half" style="margin-right:4px"></i><span class="t" data-en="Karana — Half Tithi" data-hi="करण — अर्ध तिथि">Karana — Half Tithi</span></span>
        <span class="pa-anga-num">⑤</span>
      </div>
      <div class="pa-diagram"><canvas id="cvKarana" width="160" height="160"></canvas></div>
      <div class="pa-val-name" id="pvKaranaName">{{ $karana['name'] }}</div>
      <div class="pa-val-sub" id="pvKaranaSub">{{ $karana['type'] }} · Slot {{ $karana['slot'] }}/60</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill" id="pvKaranaLord"><span class="t" data-en="Lord:" data-hi="स्वामी:">Lord:</span> {{ $karana['lord'] }}</span>
        <span class="pa-meta-pill" id="pvKaranaNature">{{ $karana['nature'] }}</span>
      </div>
    </div>

    {{-- 6. SUNRISE / SUNSET --}}
    <div class="pa-card" data-anga="sun">
      <div class="pa-card-top">
        <span class="pa-anga-label"><i class="ph ph-sunrise" style="margin-right:4px"></i><span class="t" data-en="Sunrise &amp; Sunset" data-hi="सूर्योदय और सूर्यास्त">Sunrise &amp; Sunset</span></span>
        <span class="pa-anga-num">☀</span>
      </div>
      <div class="pa-diagram"><canvas id="cvSun" width="160" height="120"></canvas></div>
      <div class="pa-val-name" style="font-family:'DM Mono',monospace;font-size:1rem" id="pvSunTimes">{{ $sunrise }} — {{ $sunset }}</div>
      <div class="pa-val-sub t" data-en="New Delhi · IST +5:30" data-hi="नई दिल्ली · IST +5:30">New Delhi · IST +5:30</div>
      <div class="pa-val-meta">
        <span class="pa-meta-pill"><span class="t" data-en="Rise:" data-hi="उदय:">Rise:</span> <span id="pvSunrise">{{ $sunrise }}</span></span>
        <span class="pa-meta-pill"><span class="t" data-en="Set:" data-hi="अस्त:">Set:</span> <span id="pvSunset">{{ $sunset }}</span></span>
      </div>
    </div>

  </div>{{-- /pancha-grid --}}
</section>

<div class="divider"></div>

{{-- ════════ PLANETS ════════ --}}
<section class="section" id="planets">
  <div class="s-eyebrow"><i class="ph ph-planet"></i> <span class="t" data-en="Nava Graha · Sidereal Positions (Lahiri)" data-hi="नव ग्रह · सायन स्थितियाँ (लाहिरी)">Nava Graha · Sidereal Positions (Lahiri)</span></div>
  <h2 class="s-title t" data-en="Nine Planets — Live Positions" data-hi="नव ग्रह — जीवंत स्थितियाँ">Nine Planets — Live Positions</h2>
  <p class="s-sub t" data-en="Geocentric sidereal longitudes for New Delhi, India. Rendered at page load time." data-hi="नई दिल्ली, भारत के लिए भू-केंद्रीय सायन देशांतर। पृष्ठ लोड समय पर गणना की गई।">Geocentric sidereal longitudes for New Delhi, India. Rendered at page load time.</p>

  <div class="planets-grid reveal" id="planetsGrid">
    @foreach($planets as $pid => $p)
    <div class="pcard" style="border-left:3px solid {{ $p['color'] }}50">
      <div class="pc-icon" style="background:{{ $p['color'] }}18;color:{{ $p['color'] }}">{{ $p['sym'] }}</div>
      <div class="pc-body">
        <div class="pc-label" style="color:{{ $p['color'] }}">{{ $p['label'] }}</div>
        <div class="pc-name">{{ ucfirst($pid) }}</div>
        <div class="pc-sign">{{ $p['sign'] }}</div>
        <div class="pc-deg">{{ $p['deg'] }} in sign</div>
        <div class="pc-nak">{{ $p['nak'] }} · {{ $p['lord'] }}</div>
        @if($p['retro'])<div class="pc-retro"><i class="ph ph-arrow-counter-clockwise" style="font-size:.75rem"></i> Retrograde</div>@endif
      </div>
    </div>
    @endforeach
  </div>
</section>

<div class="divider"></div>

{{-- ════════ FEATURES ════════ --}}
<section class="section" id="features">
  <div class="s-eyebrow"><i class="ph ph-squares-four"></i> <span class="t" data-en="Calculation Engine" data-hi="गणना इंजन">Calculation Engine</span></div>
  <h2 class="s-title t" data-en="Full Jyotish Suite" data-hi="सम्पूर्ण ज्योतिष सूट">Full Jyotish Suite</h2>
  <p class="s-sub t" data-en="Every module uses the same precision planetary engine — Jean Meeus algorithms with Lahiri Ayanamsa." data-hi="प्रत्येक मॉड्यूल एक ही सटीक ग्रहीय इंजन का उपयोग करता है — लाहिरी अयनांश के साथ जीन मेउस एल्गोरिदम।">Every module uses the same precision planetary engine — Jean Meeus algorithms with Lahiri Ayanamsa.</p>

  <div class="feat-grid reveal">
    @foreach([
      ['ph-chart-donut-slice', 'D1 Birth Chart',       'North-Indian Kundali with 9 grahas, Lagna, Ascendant, Descendant, MC and IC. Whole-sign house system.',        'D1 राशि कुंडली — 9 ग्रह, लग्न, आरोही, अवरोही।'],
      ['ph-grid-four',        'Shodashvarga',           '20 divisional charts (D1–D60): Navamsha, Dashamsha, Drekkana and more — all rendered as North-Indian charts.',   'सोलह वर्ग — 20 विभागीय कुंडली।'],
      ['ph-scales',           'Shadbala',               'Complete six-fold planetary strength: Sthana, Dig, Kaala, Chesta, Naisargika, Drig Bala per BPHS.',             'षड्बल — छः प्रकार की ग्रह शक्ति।'],
      ['ph-hourglass',        'Vimshottari Dasha',      '120-year cycle with Mahadasha → Antardasha → Pratyantar → Sookshma levels and exact start/end dates.',          'विंशोत्तरी दशा — महादशा से सूक्ष्म दशा तक।'],
      ['ph-calendar-check',   'Muhurat Calculator',     'Vivah, Griha Pravesh, Vahana, Mundan and Sampatti muhurtas with day/month/year scan.',                         'मुहूर्त कैलकुलेटर — विवाह, गृह प्रवेश, वाहन।'],
      ['ph-candle',           'Festival Calendar',      '228+ Hindu festivals, Ekadashis and vratas with significance, rituals and mantras for any year.',               'त्योहार कैलेंडर — 228+ हिंदू त्योहार।'],
    ] as [$icon, $title, $desc, $descHi])
    <div class="feat-card">
      <div class="feat-icon-wrap"><i class="ph-bold {{ $icon }}"></i></div>
      <div class="feat-title t" data-en="{{ $title }}" data-hi="{{ $title }}">{{ $title }}</div>
      <div class="feat-desc t" data-en="{{ $desc }}" data-hi="{{ $descHi }}">{{ $desc }}</div>
      <a href="/astro" class="feat-link t" data-en="Open Calculator →" data-hi="कैलकुलेटर खोलें →">Open Calculator →</a>
    </div>
    @endforeach
  </div>
</section>

<div class="divider"></div>

{{-- ════════ FESTIVALS ════════ --}}
<section class="section" id="festivals">
  <div class="s-eyebrow"><i class="ph ph-candle"></i> <span class="t" data-en="Upcoming Festivals &amp; Vratas" data-hi="आगामी त्योहार और व्रत">Upcoming Festivals &amp; Vratas</span></div>
  <h2 class="s-title t" data-en="Festival Calendar" data-hi="त्योहार कैलेंडर">Festival Calendar</h2>
  <p class="s-sub t" data-en="Computed from tithi positions for New Delhi, India." data-hi="नई दिल्ली, भारत के लिए तिथि स्थितियों से गणना।">Computed from tithi positions for New Delhi, India.</p>

  <div class="fest-grid reveal">
    @foreach($upcoming as $f)
    @php
      $fd = \DateTime::createFromFormat('Y-m-d', $f['date']);
    @endphp
    <div class="fest-card">
      <div class="fest-icon-wrap">{{ $f['icon'] ?? '✦' }}</div>
      <div class="fest-body">
        <div class="fest-date">{{ $fd ? $fd->format('d M Y') : $f['date'] }}</div>
        <div class="fest-name">{{ $f['name'] }}</div>
        @if(!empty($f['name_hi']))<div class="fest-masa" style="font-size:.72rem;color:var(--text-m)">{{ $f['name_hi'] }}</div>@endif
        <div class="fest-masa">{{ $f['masa'] ?? '' }}</div>
        <span class="fest-badge {{ $f['type'] === 'festival' ? 'fb-festival' : 'fb-vrat' }}">{{ $f['type'] }}</span>
      </div>
    </div>
    @endforeach
  </div>
  <div style="text-align:center;margin-top:28px">
    <a href="/astro" class="btn-g" style="font-size:.82rem">
      <span class="t" data-en="View Full Calendar →" data-hi="पूरा कैलेंडर देखें →">View Full Calendar →</span>
    </a>
  </div>
</section>

<div class="divider"></div>

{{-- ════════ DASHA WHEEL ════════ --}}
<section class="section" id="dasha">
  <div class="s-eyebrow"><i class="ph ph-hourglass"></i> <span class="t" data-en="Vimshottari Dasha Chakra" data-hi="विंशोत्तरी दशा चक्र">Vimshottari Dasha Chakra</span></div>
  <h2 class="s-title t" data-en="120-Year Planetary Cycle" data-hi="120-वर्षीय ग्रहीय चक्र">120-Year Planetary Cycle</h2>
  <p class="s-sub t" data-en="Each lord rules for a fixed period based on the Moon's nakshatra at birth. The full cycle totals 120 years." data-hi="प्रत्येक ग्रह जन्म के समय चंद्र नक्षत्र के आधार पर एक निश्चित अवधि के लिए शासन करता है।">Each lord rules for a fixed period based on the Moon's nakshatra at birth. The full cycle totals 120 years.</p>

  <div class="dasha-layout reveal">
    <div class="dasha-wheel-wrap"><svg id="dashaWheel" viewBox="0 0 280 280" width="280" height="280"></svg></div>
    <div class="dl-list" id="dashaList"></div>
  </div>
</section>

{{-- ════════ CTA ════════ --}}
<section class="cta-section">
  <div style="position:relative;z-index:1">
    <div class="s-eyebrow" style="justify-content:center;margin-bottom:16px"><i class="ph ph-star-four"></i> <span class="t" data-en="Get Started" data-hi="शुरू करें">Get Started</span></div>
    <h2 class="s-title" style="font-size:clamp(2rem,4vw,2.7rem);margin-bottom:12px" class="t" data-en="Calculate Your Birth Chart">
      <span class="t" data-en="Calculate Your Birth Chart" data-hi="अपनी जन्म कुंडली बनाएं">Calculate Your Birth Chart</span>
    </h2>
    <p class="s-sub" style="margin:0 auto 32px;text-align:center">
      <span class="t" data-en="Enter your date, time and location to generate a complete Jyotish reading." data-hi="पूर्ण ज्योतिष पठन के लिए अपनी जन्म तिथि, समय और स्थान दर्ज करें।">Enter your date, time and location to generate a complete Jyotish reading.</span>
    </p>
    <div style="display:flex;justify-content:center">
      <a href="/astro" class="btn-p" style="font-size:.97rem;padding:15px 40px">
        <i class="ph-bold ph-star-four"></i>
        <span class="t" data-en="Open Calculator" data-hi="कैलकुलेटर खोलें">Open Calculator</span>
      </a>
    </div>
  </div>
</section>

{{-- ════════ FOOTER ════════ --}}
<footer>
  <div class="footer-note">
    <strong>Vedic Astro Calculator</strong> · Jean Meeus Astronomical Algorithms (2nd Ed.)<br>
    <span class="t" data-en="Lahiri Ayanamsa · BPHS Rules · Brihat Parashara Hora Shastra" data-hi="लाहिरी अयनांश · BPHS नियम · बृहत् पाराशर होरा शास्त्र">Lahiri Ayanamsa · BPHS Rules · Brihat Parashara Hora Shastra</span>
  </div>
  <div class="footer-links">
    <a href="/astro" class="t" data-en="Calculator" data-hi="कैलकुलेटर">Calculator</a>
    <a href="/astro" class="t" data-en="Festivals"  data-hi="त्योहार">Festivals</a>
    <a href="/astro" class="t" data-en="Muhurat"    data-hi="मुहूर्त">Muhurat</a>
  </div>
</footer>

{{-- ════════ DATA BRIDGE ════════ --}}
@php
$_bridge = ['date'=>$date,'sunrise'=>$sunrise,'sunset'=>$sunset,'tithi'=>$tithi,'vara'=>$vara,'nakshatra'=>$nakshatra,'yoga'=>$yoga,'karana'=>$karana];
@endphp
<script>
const PANCHANGA = @json($_bridge);
const PLANETS   = @json($planets);
const AYAN      = {{ $ayan }};
const TODAY     = '{{ $date }}';
</script>

{{-- ════════ SCRIPTS ════════ --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
'use strict';

// ── TRANSLATIONS ──────────────────────────────────────────────────────────
const LANG = {
  tithi:    {'Pratipada':'प्रतिपदा','Dwitiya':'द्वितीया','Tritiya':'तृतीया','Chaturthi':'चतुर्थी','Panchami':'पंचमी','Shashthi':'षष्ठी','Saptami':'सप्तमी','Ashtami':'अष्टमी','Navami':'नवमी','Dashami':'दशमी','Ekadashi':'एकादशी','Dwadashi':'द्वादशी','Trayodashi':'त्रयोदशी','Chaturdashi':'चतुर्दशी','Purnima':'पूर्णिमा','Amavasya':'अमावस्या'},
  paksha:   {'Shukla':'शुक्ल','Krishna':'कृष्ण'},
  vara:     {'Sunday':'रविवार','Monday':'सोमवार','Tuesday':'मंगलवार','Wednesday':'बुधवार','Thursday':'गुरुवार','Friday':'शुक्रवार','Saturday':'शनिवार'},
  varaName: {'Ravivara':'रविवार','Somavara':'सोमवार','Mangalavara':'मंगलवार','Budhavara':'बुधवार','Guruvara':'गुरुवार','Shukravara':'शुक्रवार','Shanivara':'शनिवार'},
  nakshatra:{'Ashwini':'अश्विनी','Bharani':'भरणी','Krittika':'कृत्तिका','Rohini':'रोहिणी','Mrigashira':'मृगशिरा','Ardra':'आर्द्रा','Punarvasu':'पुनर्वसु','Pushya':'पुष्य','Ashlesha':'आश्लेषा','Magha':'मघा','Purva Phalguni':'पूर्व फाल्गुनी','Uttara Phalguni':'उत्तर फाल्गुनी','Hasta':'हस्त','Chitra':'चित्रा','Swati':'स्वाती','Vishakha':'विशाखा','Anuradha':'अनुराधा','Jyeshtha':'ज्येष्ठा','Moola':'मूल','Purva Ashadha':'पूर्व आषाढ़','Uttara Ashadha':'उत्तर आषाढ़','Shravana':'श्रवण','Dhanishta':'धनिष्ठा','Shatabhisha':'शतभिषा','Purva Bhadrapada':'पूर्व भाद्रपदा','Uttara Bhadrapada':'उत्तर भाद्रपदा','Revati':'रेवती'},
  yoga:     {'Vishkambha':'विष्कम्भ','Priti':'प्रीति','Ayushman':'आयुष्मान','Saubhagya':'सौभाग्य','Shobhana':'शोभन','Atiganda':'अतिगण्ड','Sukarma':'सुकर्म','Dhriti':'धृति','Shoola':'शूल','Ganda':'गण्ड','Vriddhi':'वृद्धि','Dhruva':'ध्रुव','Vyaghata':'व्याघात','Harshana':'हर्षण','Vajra':'वज्र','Siddhi':'सिद्धि','Vyatipata':'व्यतीपात','Variyana':'वरीयान','Parigha':'परिघ','Shiva':'शिव','Siddha':'सिद्ध','Sadhya':'साध्य','Shubha':'शुभ','Shukla':'शुक्ल','Brahma':'ब्रह्म','Indra':'इन्द्र','Vaidhriti':'वैधृति'},
  sign:     {'Mesha':'मेष','Vrishabha':'वृषभ','Mithuna':'मिथुन','Karka':'कर्क','Simha':'सिंह','Kanya':'कन्या','Tula':'तुला','Vrishchika':'वृश्चिक','Dhanu':'धनु','Makara':'मकर','Kumbha':'कुंभ','Meena':'मीन'},
  planet:   {'sun':'सूर्य','moon':'चंद्र','mercury':'बुध','venus':'शुक्र','mars':'मंगल','jupiter':'गुरु','saturn':'शनि','rahu':'राहु','ketu':'केतु'},
  gana:     {'Deva':'देव','Manushya':'मनुष्य','Rakshasa':'राक्षस'},
};
function tr(key, category) {
  if (document.documentElement.getAttribute('data-lang') !== 'hi') return key;
  return (LANG[category] && LANG[category][key]) || key;
}

// ── THEME ─────────────────────────────────────────────────────────────────
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.getAttribute('data-theme') === 'dark';
  html.setAttribute('data-theme', isDark ? 'light' : 'dark');
  document.getElementById('themeIcon').className = isDark ? 'ph ph-moon-stars' : 'ph ph-sun';
  localStorage.setItem('theme', isDark ? 'light' : 'dark');
  redrawAll(); // diagrams use CSS vars
}
(function initTheme() {
  const saved = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  const icon = document.getElementById('themeIcon');
  if (icon) icon.className = saved === 'light' ? 'ph ph-sun' : 'ph ph-moon-stars';
})();

// ── LANGUAGE ──────────────────────────────────────────────────────────────
function toggleLang() {
  const html = document.documentElement;
  const isEn = html.getAttribute('data-lang') === 'en';
  const lang = isEn ? 'hi' : 'en';
  html.setAttribute('data-lang', lang);
  document.getElementById('langToggle').textContent = isEn ? 'हि' : 'EN';
  applyLang(lang);
  localStorage.setItem('lang', lang);
}
function applyLang(lang) {
  document.querySelectorAll('.t[data-en]').forEach(el => {
    el.textContent = el.dataset[lang] || el.dataset.en;
  });
  // Also translate dynamic values
  updatePanchangaText(PANCHANGA, lang);
}
function updatePanchangaText(p, lang) {
  const isHi = lang === 'hi';
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  const tithi = p.tithi;
  set('pvTithiName',  isHi ? (tr(tithi.name,'tithi')) : tithi.name);
  set('pvTithiSub',   (isHi ? tr(tithi.paksha,'paksha') : tithi.paksha) + ' Paksha · ' + tithi.num + '/15');
  set('pvVaraName',   isHi ? tr(p.vara.name,'varaName') : p.vara.name);
  set('pvVaraSub',    (isHi ? tr(p.vara.en,'vara') : p.vara.en) + ' · ' + (isHi?'स्वामी:':'Lord:') + ' ' + p.vara.lord);
  set('pvNakName',    isHi ? tr(p.nakshatra.name,'nakshatra') : p.nakshatra.name);
  set('pvYogaName',   isHi ? tr(p.yoga.name,'yoga') : p.yoga.name);
}
(function initLang() {
  const saved = localStorage.getItem('lang') || 'en';
  document.documentElement.setAttribute('data-lang', saved);
  const btn = document.getElementById('langToggle');
  if (btn) btn.textContent = saved === 'hi' ? 'हि' : 'EN';
  if (saved === 'hi') applyLang('hi');
})();

// ── STAR CANVAS ───────────────────────────────────────────────────────────
(function() {
  const c = document.getElementById('starsCanvas');
  const resize = () => { c.width = window.innerWidth; c.height = window.innerHeight; };
  resize(); window.addEventListener('resize', resize);
  const ctx = c.getContext('2d');
  const S = Array.from({length:200}, () => ({
    x: Math.random(), y: Math.random(),
    r: Math.random() * 1.1 + .2,
    a: Math.random(), s: .3 + Math.random() * .6, p: Math.random() * Math.PI * 2,
  }));
  let t = 0;
  (function frame() {
    ctx.clearRect(0, 0, c.width, c.height);
    S.forEach(s => {
      const alpha = s.a * .65 * (.5 + .5 * Math.sin(t * s.s + s.p));
      ctx.fillStyle = `rgba(220,212,190,${alpha})`;
      ctx.beginPath(); ctx.arc(s.x * c.width, s.y * c.height, s.r, 0, Math.PI * 2); ctx.fill();
    });
    t += .007; requestAnimationFrame(frame);
  })();
})();

// ── ZODIAC SVG RING ───────────────────────────────────────────────────────
(function() {
  const svg = document.getElementById('zodiacRing');
  const ns = 'http://www.w3.org/2000/svg';
  const cx=250,cy=250,ro=238,ri=200;
  const glyphs = ['♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓'];
  for (let i = 0; i < 12; i++) {
    const a1 = (i*30-90)*Math.PI/180, a2 = ((i+1)*30-90)*Math.PI/180;
    const am = ((i+.5)*30-90)*Math.PI/180, rm = (ro+ri)/2;
    const p = document.createElementNS(ns,'path');
    p.setAttribute('d',`M${cx+ro*Math.cos(a1)},${cy+ro*Math.sin(a1)} A${ro},${ro} 0 0,1 ${cx+ro*Math.cos(a2)},${cy+ro*Math.sin(a2)} L${cx+ri*Math.cos(a2)},${cy+ri*Math.sin(a2)} A${ri},${ri} 0 0,0 ${cx+ri*Math.cos(a1)},${cy+ri*Math.sin(a1)} Z`);
    const h = i*30;
    p.setAttribute('fill',`hsla(${h},35%,18%,.65)`);
    p.setAttribute('stroke',`hsla(${h},45%,45%,.2)`);
    p.setAttribute('stroke-width','0.8');
    svg.appendChild(p);
    const tx = document.createElementNS(ns,'text');
    tx.setAttribute('x',cx+rm*Math.cos(am)); tx.setAttribute('y',cy+rm*Math.sin(am));
    tx.setAttribute('text-anchor','middle'); tx.setAttribute('dominant-baseline','central');
    tx.setAttribute('fill',`hsla(${h},55%,72%,.55)`); tx.setAttribute('font-size','13');
    tx.textContent = glyphs[i]; svg.appendChild(tx);
  }
  const c1 = document.createElementNS(ns,'circle');
  c1.setAttribute('cx',cx); c1.setAttribute('cy',cy); c1.setAttribute('r',ro+6);
  c1.setAttribute('fill','none'); c1.setAttribute('stroke','rgba(200,168,75,.12)'); c1.setAttribute('stroke-width','1.5');
  svg.appendChild(c1);
})();

// ── ORRERY CANVAS ─────────────────────────────────────────────────────────
const orrCtx = document.getElementById('orreryCanvas').getContext('2d');
const OrrORBITS = {moon:58,mercury:88,venus:116,sun:144,mars:166,jupiter:188,saturn:210,rahu:232};
const OrrCOLORS = {sun:'#d4921e',moon:'#7aafce',mercury:'#28a870',venus:'#b84ca0',mars:'#d83820',jupiter:'#c8901a',saturn:'#7060a8',rahu:'#208048',ketu:'#a03818'};
const OrrLABELS = {sun:'Su',moon:'Mo',mercury:'Me',venus:'Ve',mars:'Ma',jupiter:'Ju',saturn:'Sa',rahu:'Ra',ketu:'Ke'};
let orrDrift = 0, orrTooltip = null, orrPlanets = PLANETS;

function drawOrrery(pdata) {
  const ctx = orrCtx, cw = 500, cx = 250, cy = 250;
  ctx.clearRect(0, 0, cw, cw);
  // Earth glow
  const eg = ctx.createRadialGradient(cx,cy,0,cx,cy,24);
  eg.addColorStop(0,'rgba(60,120,200,.9)'); eg.addColorStop(.5,'rgba(30,60,120,.5)'); eg.addColorStop(1,'rgba(10,20,50,0)');
  ctx.fillStyle=eg; ctx.beginPath(); ctx.arc(cx,cy,24,0,Math.PI*2); ctx.fill();
  ctx.fillStyle='#3a78c8'; ctx.beginPath(); ctx.arc(cx,cy,7,0,Math.PI*2); ctx.fill();
  // Orbits
  Object.values(OrrORBITS).forEach(r => {
    ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2);
    ctx.strokeStyle='rgba(150,180,220,.09)'; ctx.lineWidth=1; ctx.stroke();
  });
  // Planets
  Object.keys(OrrORBITS).forEach(pid => {
    if (!pdata[pid]) return;
    const r = OrrORBITS[pid], lon = pdata[pid].lon + orrDrift;
    const ang = (lon-90)*Math.PI/180, x = cx+r*Math.cos(ang), y = cy+r*Math.sin(ang);
    const col = OrrCOLORS[pid]||'#888', sz = pid==='sun'?9:pid==='moon'?6:5;
    const grd = ctx.createRadialGradient(x,y,0,x,y,sz*2.8);
    grd.addColorStop(0,col+'cc'); grd.addColorStop(1,'transparent');
    ctx.fillStyle=grd; ctx.beginPath(); ctx.arc(x,y,sz*2.8,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=col; ctx.beginPath(); ctx.arc(x,y,sz,0,Math.PI*2); ctx.fill();
    const lr=r+15, lx=cx+lr*Math.cos(ang), ly=cy+lr*Math.sin(ang);
    ctx.fillStyle=col; ctx.font='bold 9px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText(OrrLABELS[pid]||pid.slice(0,2).toUpperCase(),lx,ly);
  });
  // Ketu
  if (pdata.rahu) {
    const r=OrrORBITS.rahu, lon=pdata.rahu.lon+180+orrDrift;
    const ang=(lon-90)*Math.PI/180, x=cx+r*Math.cos(ang), y=cy+r*Math.sin(ang);
    const col=OrrCOLORS.ketu;
    const grd=ctx.createRadialGradient(x,y,0,x,y,14); grd.addColorStop(0,col+'cc'); grd.addColorStop(1,'transparent');
    ctx.fillStyle=grd; ctx.beginPath(); ctx.arc(x,y,14,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=col; ctx.beginPath(); ctx.arc(x,y,5,0,Math.PI*2); ctx.fill();
    const lr=r+15, lx=cx+lr*Math.cos(ang), ly=cy+lr*Math.sin(ang);
    ctx.fillStyle=col; ctx.font='bold 9px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText('Ke',lx,ly);
  }
  // Tooltip
  if (orrTooltip) {
    ctx.fillStyle='rgba(10,15,28,.9)'; ctx.strokeStyle='rgba(200,168,75,.5)'; ctx.lineWidth=1;
    const tw=130,th=46,tx=Math.min(orrTooltip.x+12,cw-tw-4),ty=Math.max(orrTooltip.y-th-4,4);
    ctx.beginPath(); ctx.roundRect(tx,ty,tw,th,8); ctx.fill(); ctx.stroke();
    ctx.fillStyle='#f0e8d4'; ctx.font='bold 11px DM Sans,sans-serif'; ctx.textAlign='left'; ctx.textBaseline='top';
    ctx.fillText(orrTooltip.name,tx+8,ty+6);
    ctx.fillStyle='rgba(200,168,75,.85)'; ctx.font='10px DM Mono,monospace';
    ctx.fillText(orrTooltip.info,tx+8,ty+24);
  }
  orrDrift += .004;
  requestAnimationFrame(() => drawOrrery(orrPlanets));
}
drawOrrery(PLANETS);
(function() {
  const cv = document.getElementById('orreryCanvas');
  cv.addEventListener('mousemove', e => {
    const r = cv.getBoundingClientRect(), sx=500/r.width, sy=500/r.height;
    const mx=(e.clientX-r.left)*sx, my=(e.clientY-r.top)*sy;
    orrTooltip = null;
    for (const pid of [...Object.keys(OrrORBITS),'ketu']) {
      const src = pid==='ketu' ? PLANETS.rahu : PLANETS[pid]; if (!src) continue;
      const rr = OrrORBITS[pid==='ketu'?'rahu':pid];
      const lon = src.lon + (pid==='ketu'?180:0);
      const ang=(lon-90)*Math.PI/180, x=250+rr*Math.cos(ang), y=250+rr*Math.sin(ang);
      if (Math.hypot(mx-x,my-y)<14) {
        const d = pid==='ketu'?(PLANETS.ketu||src):src;
        orrTooltip = {x,y,name:pid.charAt(0).toUpperCase()+pid.slice(1),info:d.sign+' · '+d.deg};
        break;
      }
    }
  });
  cv.addEventListener('mouseleave',()=>{orrTooltip=null;});
})();

// ── DIAGRAM HELPERS ───────────────────────────────────────────────────────
function cssVar(name) {
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}
function drawArc(ctx, cx, cy, r, startA, endA, col, lw) {
  ctx.beginPath(); ctx.arc(cx,cy,r,startA,endA);
  ctx.strokeStyle=col; ctx.lineWidth=lw; ctx.stroke();
}
function drawDot(ctx,x,y,r,col,glow) {
  if (glow) {
    const g=ctx.createRadialGradient(x,y,0,x,y,glow);
    g.addColorStop(0,col); g.addColorStop(1,'transparent');
    ctx.fillStyle=g; ctx.beginPath(); ctx.arc(x,y,glow,0,Math.PI*2); ctx.fill();
  }
  ctx.fillStyle=col; ctx.beginPath(); ctx.arc(x,y,r,0,Math.PI*2); ctx.fill();
}
function canvasClear(cv) {
  cv.getContext('2d').clearRect(0,0,cv.width,cv.height);
}
function hexToRGBA(hex,a) {
  const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}

// ── DIAGRAM: TITHI ────────────────────────────────────────────────────────
// Circular moon-phase arc: full ring = 360° elongation (one lunar month)
function drawTithi(prog, elong) {
  const cv = document.getElementById('cvTithi');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const cx=80,cy=80,ro=64,ri=46;
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  const trackCol = isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.06)';
  // Track ring (30 tithis, alternating Shukla/Krishna)
  for (let i=0;i<30;i++) {
    const a1=(i*12-90)*Math.PI/180, a2=((i+1)*12-90)*Math.PI/180;
    const isShukla = i<15;
    ctx.beginPath(); ctx.moveTo(cx+(ri+1)*Math.cos(a1),cy+(ri+1)*Math.sin(a1));
    ctx.arc(cx,cy,ro,a1,a2); ctx.arc(cx,cy,ri,a2,a1,true); ctx.closePath();
    ctx.fillStyle = isShukla ? `rgba(200,168,75,${0.07+i*.005})` : `rgba(100,140,200,${0.04+(29-i)*.003})`;
    ctx.fill();
  }
  // Tick marks
  for (let i=0;i<30;i++) {
    const a=(i*12-90)*Math.PI/180;
    const x1=cx+(ri-2)*Math.cos(a),y1=cy+(ri-2)*Math.sin(a);
    const x2=cx+(ro+2)*Math.cos(a),y2=cy+(ro+2)*Math.sin(a);
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2);
    ctx.strokeStyle= i===0||i===15 ? 'rgba(200,168,75,.6)' : 'rgba(255,255,255,.12)';
    ctx.lineWidth=i===0||i===15?2:1; ctx.stroke();
  }
  // Progress fill (animated via GSAP on _animProg)
  const progAngle = (elong-90)*Math.PI/180;
  const startA = -90*Math.PI/180;
  if (elong > 0) {
    const midRing = (ri+ro)/2;
    const grd = ctx.createLinearGradient(-ro,0,ro,0);
    grd.addColorStop(0,'rgba(200,168,75,.6)'); grd.addColorStop(1,'rgba(200,168,75,.2)');
    ctx.beginPath(); ctx.arc(cx,cy,(ri+ro)/2,startA,progAngle);
    ctx.strokeStyle=grd; ctx.lineWidth=ro-ri-4; ctx.stroke();
  }
  // Current position dot
  const dotA = (elong-90)*Math.PI/180, dotR=(ri+ro)/2;
  drawDot(ctx, cx+dotR*Math.cos(dotA), cy+dotR*Math.sin(dotA), 5, '#c8a84b', 10);
  // Center text
  ctx.fillStyle = isDark?'#e4dac8':'#1a1208';
  ctx.font='bold 14px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(Math.round(elong)+'°', cx, cy-6);
  ctx.font='10px DM Sans,sans-serif';
  ctx.fillStyle = isDark?'rgba(200,168,75,.7)':'rgba(138,96,16,.7)';
  ctx.fillText(elong<=180?'Shukla':'Krishna', cx, cy+8);
}

// ── DIAGRAM: VARA (week wheel) ────────────────────────────────────────────
function drawVara(varaIdx, varaColor) {
  const cv = document.getElementById('cvVara');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const cx=80,cy=80,ro=68,ri=36;
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  const syms = ['☀','☽','♂','☿','♃','♀','♄'];
  const cols = ['#d4921e','#7aafce','#d83820','#28a870','#c8901a','#b84ca0','#7060a8'];
  const names = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  for (let i=0;i<7;i++) {
    const a1 = (i*360/7-90)*Math.PI/180, a2 = ((i+1)*360/7-90)*Math.PI/180;
    const am = ((i+.5)*360/7-90)*Math.PI/180;
    const isActive = i===varaIdx;
    ctx.beginPath(); ctx.moveTo(cx+ri*Math.cos(a1),cy+ri*Math.sin(a1));
    ctx.arc(cx,cy,ro,a1,a2); ctx.arc(cx,cy,ri,a2,a1,true); ctx.closePath();
    ctx.fillStyle = isActive ? hexToRGBA(cols[i],.35) : isDark?'rgba(255,255,255,.03)':'rgba(0,0,0,.03)';
    ctx.fill();
    ctx.strokeStyle = isActive ? cols[i] : isDark?'rgba(255,255,255,.08)':'rgba(0,0,0,.08)';
    ctx.lineWidth=isActive?2:1; ctx.stroke();
    const rm=(ri+ro)/2, tx=cx+rm*Math.cos(am), ty=cy+rm*Math.sin(am);
    ctx.fillStyle = isActive ? cols[i] : isDark?'rgba(255,255,255,.3)':'rgba(0,0,0,.3)';
    ctx.font = (isActive?'bold ':'')+' 14px DM Sans,sans-serif';
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText(syms[i],tx,ty);
  }
  // Center
  ctx.fillStyle = isDark?'rgba(255,255,255,.05)':'rgba(0,0,0,.04)';
  ctx.beginPath(); ctx.arc(cx,cy,ri-2,0,Math.PI*2); ctx.fill();
  ctx.fillStyle=varaColor||cols[varaIdx];
  ctx.font='bold 12px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(names[varaIdx],cx,cy);
}

// ── DIAGRAM: NAKSHATRA (27-arc ring) ─────────────────────────────────────
function drawNakshatra(nakIdx, nakProg) {
  const cv = document.getElementById('cvNak');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const cx=80,cy=80,ro=68,ri=48;
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  for (let i=0;i<27;i++) {
    const a1=(i*360/27-90)*Math.PI/180, a2=((i+1)*360/27-90)*Math.PI/180;
    const isActive=i===nakIdx, near=Math.abs(i-nakIdx)<=1||Math.abs(i-nakIdx)>=26;
    ctx.beginPath(); ctx.moveTo(cx+ri*Math.cos(a1),cy+ri*Math.sin(a1));
    ctx.arc(cx,cy,ro,a1,a2); ctx.arc(cx,cy,ri,a2,a1,true); ctx.closePath();
    ctx.fillStyle = isActive?'rgba(58,128,184,.4)':near?'rgba(58,128,184,.1)':isDark?'rgba(255,255,255,.025)':'rgba(0,0,0,.03)';
    ctx.fill();
    ctx.strokeStyle = isActive?'rgba(106,170,216,.7)':isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.07)';
    ctx.lineWidth=isActive?1.5:.5; ctx.stroke();
  }
  // Pada progress within active nakshatra
  const padaA = ((nakIdx*360/27)-90)*Math.PI/180;
  const padaSpan = (360/27)*Math.PI/180;
  const fillEnd = padaA + padaSpan*(nakProg/100);
  ctx.beginPath(); ctx.arc(cx,cy,(ri+ro)/2,padaA,fillEnd);
  ctx.strokeStyle='rgba(58,128,184,.8)'; ctx.lineWidth=ro-ri-4; ctx.stroke();
  // Dot
  const dotA = padaA + padaSpan*(nakProg/100);
  drawDot(ctx, cx+(ri+ro)/2*Math.cos(dotA), cy+(ri+ro)/2*Math.sin(dotA), 4, '#6aaad8', 9);
  // Center
  ctx.fillStyle = isDark?'#e4dac8':'#1a1208';
  ctx.font='bold 11px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(nakIdx+1+'/27',cx,cy-6);
  ctx.font='9px DM Sans,sans-serif'; ctx.fillStyle=isDark?'rgba(106,170,216,.7)':'rgba(26,94,144,.7)';
  ctx.fillText('Pada '+Math.ceil(nakProg/25||1),cx,cy+7);
}

// ── DIAGRAM: YOGA (gauge/speedometer) ────────────────────────────────────
function drawYoga(yogaIdx, yogaProg, yogaCls) {
  const cv = document.getElementById('cvYoga');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const cx=80,cy=88,r=62;
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  const startA = Math.PI, sweepA = Math.PI; // semicircle bottom-up
  // Background track (27 segments in semicircle)
  for (let i=0;i<27;i++) {
    const a1 = Math.PI + (i/27)*Math.PI, a2 = Math.PI + ((i+1)/27)*Math.PI;
    const isMaha = (i===0||i===16||i===26), isAsh=(i===5||i===8||i===9||i===12||i===13||i===14||i===17);
    ctx.beginPath(); ctx.arc(cx,cy,r,a1,a2);
    ctx.strokeStyle = isMaha?'rgba(216,56,32,.5)':isAsh?'rgba(232,120,40,.35)':'rgba(40,168,112,.25)';
    ctx.lineWidth=14; ctx.stroke();
  }
  // Active segment
  const yA1 = Math.PI+(yogaIdx/27)*Math.PI, yA2=Math.PI+((yogaIdx+1)/27)*Math.PI;
  const yCol = yogaCls==='Mahavisha'?'#d83820':yogaCls==='Ashubha'?'#e87828':'#28a870';
  ctx.beginPath(); ctx.arc(cx,cy,r,yA1,yA2);
  ctx.strokeStyle=yCol; ctx.lineWidth=14; ctx.stroke();
  // Needle
  const needleA = Math.PI + ((yogaIdx + yogaProg/100)/27)*Math.PI;
  const nx=cx+r*Math.cos(needleA), ny=cy+r*Math.sin(needleA);
  ctx.beginPath(); ctx.moveTo(cx,cy); ctx.lineTo(nx,ny);
  ctx.strokeStyle=yCol; ctx.lineWidth=2.5; ctx.stroke();
  drawDot(ctx,nx,ny,5,yCol,10);
  // Center pivot
  ctx.fillStyle=isDark?'#e4dac8':'#1a1208';
  ctx.beginPath(); ctx.arc(cx,cy,7,0,Math.PI*2);
  ctx.fillStyle=isDark?'rgba(255,255,255,.1)':'rgba(0,0,0,.08)'; ctx.fill();
  ctx.fillStyle=isDark?'#e4dac8':'#1a1208';
  ctx.font='bold 11px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(yogaIdx+1+'/27',cx,cy-22);
  ctx.font='9px DM Sans,sans-serif';
  ctx.fillStyle=yCol;
  ctx.fillText(yogaCls==='Subha'?'Auspicious':yogaCls==='Mahavisha'?'Inauspicious':yogaCls,cx,cy-10);
}

// ── DIAGRAM: KARANA (arc progress) ───────────────────────────────────────
function drawKarana(karanaProg, karanaSlot) {
  const cv = document.getElementById('cvKarana');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const cx=80,cy=80,r=60;
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  const startA = (220)*Math.PI/180, endA = (320)*Math.PI/180; // nearly full circle except bottom gap
  const fullSweep = (320-220+360)*Math.PI/180; // going from 220 clockwise to 320 = 260 degrees... no
  // Actually draw a 300-degree arc (from -150 to 150 degrees = 300° sweep)
  const sa = (210)*Math.PI/180; // start = -150°
  const fullAng = (300)*Math.PI/180; // 300° sweep
  // Track
  ctx.beginPath(); ctx.arc(cx,cy,r,sa,sa+fullAng);
  ctx.strokeStyle=isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.06)'; ctx.lineWidth=14; ctx.stroke();
  // Fill
  const fillAng = fullAng * (karanaProg/100);
  ctx.beginPath(); ctx.arc(cx,cy,r,sa,sa+fillAng);
  ctx.strokeStyle='#c8901a'; ctx.lineWidth=14; ctx.stroke();
  // Dot at tip
  const ta = sa+fillAng;
  drawDot(ctx, cx+r*Math.cos(ta), cy+r*Math.sin(ta), 6, '#c8901a', 12);
  // 60 tick marks (karanas)
  for (let i=0;i<=60;i++) {
    const a = sa+(fullAng*i/60);
    const x1=cx+(r-9)*Math.cos(a), y1=cy+(r-9)*Math.sin(a);
    const x2=cx+(r+9)*Math.cos(a), y2=cy+(r+9)*Math.sin(a);
    if (i%10===0) { ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.strokeStyle='rgba(200,168,75,.4)'; ctx.lineWidth=1.5; ctx.stroke(); }
  }
  // Center
  ctx.fillStyle=isDark?'#e4dac8':'#1a1208';
  ctx.font='bold 13px DM Sans,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(karanaSlot+'/60',cx,cy-4);
  ctx.font='9px DM Sans,sans-serif'; ctx.fillStyle=isDark?'rgba(200,168,75,.7)':'rgba(138,96,16,.7)';
  ctx.fillText('Karana',cx,cy+9);
}

// ── DIAGRAM: SUNRISE ARC ──────────────────────────────────────────────────
function drawSunArc(sunrise, sunset) {
  const cv = document.getElementById('cvSun');
  const ctx = cv.getContext('2d');
  canvasClear(cv);
  const isDark = document.documentElement.getAttribute('data-theme')==='dark';
  const w=160,h=120,pad=18;
  // Horizon
  ctx.beginPath(); ctx.moveTo(pad,h-24); ctx.lineTo(w-pad,h-24);
  ctx.strokeStyle=isDark?'rgba(255,255,255,.1)':'rgba(0,0,0,.1)'; ctx.lineWidth=1; ctx.stroke();
  // Arc
  const cx=w/2, cy=h-24, r=50;
  const grd = ctx.createLinearGradient(cx-r,0,cx+r,0);
  grd.addColorStop(0,'rgba(232,120,40,.25)'); grd.addColorStop(.5,'rgba(255,220,50,.7)'); grd.addColorStop(1,'rgba(232,120,40,.25)');
  ctx.beginPath(); ctx.arc(cx,cy,r,Math.PI,0);
  ctx.strokeStyle=grd; ctx.lineWidth=3; ctx.stroke();
  // Sunrise label
  const toHrs = t => { const [h,m]=(t||'06:00').split(':').map(Number); return h+m/60; };
  const riseHr = toHrs(sunrise), setHr = toHrs(sunset);
  const now = new Date(); const nowHr = now.getHours()+now.getMinutes()/60;
  // Current sun position on arc
  const dayLen = setHr - riseHr;
  const progress = dayLen>0 ? Math.max(0,Math.min(1,(nowHr-riseHr)/dayLen)) : 0.5;
  const sunA = Math.PI + progress*Math.PI;
  const sx = cx+r*Math.cos(sunA), sy = cy+r*Math.sin(sunA);
  // Sun glow
  const sg = ctx.createRadialGradient(sx,sy,0,sx,sy,14);
  sg.addColorStop(0,'rgba(255,220,50,.8)'); sg.addColorStop(1,'transparent');
  ctx.fillStyle=sg; ctx.beginPath(); ctx.arc(sx,sy,14,0,Math.PI*2); ctx.fill();
  ctx.fillStyle='#ffd830'; ctx.beginPath(); ctx.arc(sx,sy,5,0,Math.PI*2); ctx.fill();
  // Labels
  ctx.fillStyle=isDark?'rgba(200,168,75,.7)':'rgba(138,96,16,.7)';
  ctx.font='9px DM Mono,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='top';
  ctx.fillText(sunrise,pad+8,h-20);
  ctx.fillText(sunset,w-pad-8,h-20);
  // Ground gradient
  const gg=ctx.createLinearGradient(0,h-24,0,h);
  gg.addColorStop(0,isDark?'rgba(40,100,200,.15)':'rgba(100,150,220,.2)'); gg.addColorStop(1,'transparent');
  ctx.fillStyle=gg; ctx.fillRect(pad,h-24,w-pad*2,24);
}

// ── DRAW ALL DIAGRAMS ─────────────────────────────────────────────────────
function redrawAll() {
  const p = window.__panchangaData || PANCHANGA;
  drawTithi(p.tithi.prog, p.tithi.elong);
  drawVara(p.vara.idx, p.vara.color);
  drawNakshatra(p.nakshatra.idx, p.nakshatra.prog);
  drawYoga(p.yoga.idx, p.yoga.prog, p.yoga.cls);
  drawKarana(p.karana.prog, p.karana.slot);
  drawSunArc(p.sunrise, p.sunset);
}
window.__panchangaData = PANCHANGA;
window.addEventListener('load', () => { setTimeout(redrawAll, 100); });

// ── DAY NAVIGATION ────────────────────────────────────────────────────────
let _currentDate = TODAY;

async function shiftDay(dir) {
  const d = new Date(_currentDate+'T00:00:00');
  d.setDate(d.getDate()+dir);
  await loadDay(d.toISOString().slice(0,10));
}
async function gotoToday() {
  await loadDay(TODAY);
}
async function loadDay(dateStr) {
  _currentDate = dateStr;
  document.getElementById('btnPrev').disabled = true;
  document.getElementById('btnNext').disabled = true;
  try {
    const res  = await fetch('/panchanga-data?date='+dateStr);
    const data = await res.json();
    window.__panchangaData = data;
    // Update header
    document.getElementById('ddDate').textContent = data.dateDisplay;
    document.getElementById('ddSub').textContent  = data.dayName + ' · Ayanamsa ' + data.ayan + '°';
    // Update text values
    const lang = document.documentElement.getAttribute('data-lang');
    const isHi = lang==='hi';
    const set = (id,val) => { const el=document.getElementById(id); if(el) el.textContent=val; };
    set('pvTithiName',  isHi?tr(data.tithi.name,'tithi')   :data.tithi.name);
    set('pvTithiSub',   (isHi?tr(data.tithi.paksha,'paksha'):data.tithi.paksha)+' Paksha · '+data.tithi.num+'/15');
    set('pvTithiLord',  (isHi?'स्वामी:':'Lord: ')+data.tithi.lord);
    set('pvTithiElong', data.tithi.elong+'°');
    set('pvVaraName',   isHi?tr(data.vara.name,'varaName')  :data.vara.name);
    set('pvVaraSub',    (isHi?tr(data.vara.en,'vara'):data.vara.en)+' · '+(isHi?'स्वामी:':'Lord:')+' '+data.vara.lord);
    set('pvVaraNature', data.vara.nature);
    set('pvNakName',    isHi?tr(data.nakshatra.name,'nakshatra'):data.nakshatra.name);
    set('pvNakSub',     (isHi?'पाद':'Pada')+' '+data.nakshatra.pada+' · '+data.nakshatra.lord);
    set('pvNakGana',    isHi?tr(data.nakshatra.gana,'gana'):data.nakshatra.gana);
    set('pvNakDeity',   data.nakshatra.deity);
    set('pvYogaName',   isHi?tr(data.yoga.name,'yoga'):data.yoga.name);
    set('pvYogaSub',    data.yoga.nature+' · '+(isHi?'स्वामी:':'Lord:')+' '+data.yoga.lord);
    set('pvYogaCls',    data.yoga.cls);
    set('pvYogaNum',    (data.yoga.idx+1)+'/27');
    set('pvKaranaName', data.karana.name);
    set('pvKaranaSub',  data.karana.type+' · Slot '+data.karana.slot+'/60');
    set('pvKaranaLord', (isHi?'स्वामी:':'Lord:')+' '+data.karana.lord);
    set('pvKaranaNature',data.karana.nature);
    set('pvSunTimes',   data.sunrise+' — '+data.sunset);
    set('pvSunrise',    data.sunrise); set('pvSunset',data.sunset);
    // Animate the diagrams (fade out → redraw → fade in)
    const grid = document.getElementById('panchaGrid');
    grid.style.opacity='0.4'; grid.style.transition='opacity .2s';
    setTimeout(()=>{ redrawAll(); grid.style.opacity='1'; }, 220);
  } catch(e) { console.error(e); }
  finally {
    document.getElementById('btnPrev').disabled = false;
    document.getElementById('btnNext').disabled = false;
  }
}

// ── DASHA WHEEL SVG ───────────────────────────────────────────────────────
(function() {
  const lords=[
    {n:'Ketu',y:7,c:'#a03818',s:'☋'},{n:'Venus',y:20,c:'#b84ca0',s:'♀'},{n:'Sun',y:6,c:'#d4921e',s:'☀'},
    {n:'Moon',y:10,c:'#7aafce',s:'☽'},{n:'Mars',y:7,c:'#d83820',s:'♂'},{n:'Rahu',y:18,c:'#208048',s:'☊'},
    {n:'Jupiter',y:16,c:'#c8901a',s:'♃'},{n:'Saturn',y:19,c:'#7060a8',s:'♄'},{n:'Mercury',y:17,c:'#28a870',s:'☿'},
  ];
  const svg=document.getElementById('dashaWheel'), ns='http://www.w3.org/2000/svg';
  const dl=document.getElementById('dashaList');
  const cx=140,cy=140,ro=122,ri=58; let angle=-90;
  lords.forEach(l=>{
    const sw=l.y/120*360, a1=angle*Math.PI/180, a2=(angle+sw)*Math.PI/180;
    const am=(angle+sw/2)*Math.PI/180, lg=sw>180?1:0;
    const x1=cx+ro*Math.cos(a1),y1=cy+ro*Math.sin(a1),x2=cx+ro*Math.cos(a2),y2=cy+ro*Math.sin(a2);
    const x3=cx+ri*Math.cos(a2),y3=cy+ri*Math.sin(a2),x4=cx+ri*Math.cos(a1),y4=cy+ri*Math.sin(a1);
    const p=document.createElementNS(ns,'path');
    p.setAttribute('d',`M${x1},${y1} A${ro},${ro} 0 ${lg},1 ${x2},${y2} L${x3},${y3} A${ri},${ri} 0 ${lg},0 ${x4},${y4} Z`);
    p.setAttribute('fill',l.c+'28'); p.setAttribute('stroke',l.c+'70'); p.setAttribute('stroke-width','1');
    svg.appendChild(p);
    if (sw>15) {
      const rm=(ri+ro)/2;
      const tx=document.createElementNS(ns,'text');
      tx.setAttribute('x',cx+rm*Math.cos(am)); tx.setAttribute('y',cy+rm*Math.sin(am));
      tx.setAttribute('text-anchor','middle'); tx.setAttribute('dominant-baseline','central');
      tx.setAttribute('fill',l.c); tx.setAttribute('font-size',sw>38?'14':'10');
      tx.textContent=l.s; svg.appendChild(tx);
    }
    dl.innerHTML+=`<div class="dl-item"><div class="dl-dot" style="background:${l.c}"></div><div class="dl-name" style="color:${l.c}">${l.s} ${l.n}</div><div class="dl-yrs">${l.y} yrs</div><div class="dl-bar-bg"><div class="dl-bar-fill" style="width:${l.y/20*100}%;background:${l.c}60"></div></div></div>`;
    angle+=sw;
  });
  // Center
  const cc=document.createElementNS(ns,'circle'); cc.setAttribute('cx',cx); cc.setAttribute('cy',cy); cc.setAttribute('r',ri-2);
  cc.setAttribute('fill','var(--bg)'); cc.setAttribute('stroke','rgba(200,168,75,.2)'); cc.setAttribute('stroke-width','1');
  svg.appendChild(cc);
  const t1=document.createElementNS(ns,'text'); t1.setAttribute('x',cx); t1.setAttribute('y',cy-7);
  t1.setAttribute('text-anchor','middle'); t1.setAttribute('fill','rgba(200,168,75,.75)'); t1.setAttribute('font-size','13'); t1.setAttribute('font-family','Playfair Display,serif'); t1.textContent='120'; svg.appendChild(t1);
  const t2=document.createElementNS(ns,'text'); t2.setAttribute('x',cx); t2.setAttribute('y',cy+9);
  t2.setAttribute('text-anchor','middle'); t2.setAttribute('fill','rgba(200,168,75,.4)'); t2.setAttribute('font-size','8'); t2.textContent='years'; svg.appendChild(t2);
})();

// ── GSAP SCROLL ANIMATIONS ────────────────────────────────────────────────
window.addEventListener('load',()=>{
  if (typeof gsap==='undefined') return;
  gsap.registerPlugin(ScrollTrigger);
  // Hero entrance
  gsap.from('#heroTitle',    {opacity:0,y:44,duration:1.1,ease:'power3.out'});
  gsap.from('.hero-badge',   {opacity:0,y:18,duration:.8,delay:.15});
  gsap.from('.hero-sub',     {opacity:0,y:28,duration:.9,delay:.3});
  gsap.from('.hero-btns',    {opacity:0,y:16,duration:.7,delay:.5});
  gsap.from('.orrery-wrap',  {opacity:0,scale:.88,duration:1.2,delay:.2,ease:'power2.out'});
  gsap.from('nav',           {opacity:0,y:-16,duration:.6});
  // Scroll reveals
  document.querySelectorAll('.reveal').forEach(el=>{
    ScrollTrigger.create({
      trigger:el, start:'top 86%',
      onEnter:()=>{
        el.classList.add('in');
        const kids=el.querySelectorAll('.pa-card,.pcard,.feat-card,.fest-card,.dl-item');
        if (kids.length) gsap.from(kids,{opacity:0,y:20,stagger:.055,duration:.55,ease:'power2.out',clearProps:'all'});
      },
      once:true,
    });
  });
});
</script>
</body>
</html>
