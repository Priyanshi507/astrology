<!DOCTYPE html>
<html lang="en" data-theme="dark" data-lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Vedic Astro Calculator — Jyotish Engine</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
/* ── TOKENS ── */
:root{
  --bg:#070c16;--bg2:#0c1220;--bg3:#121a2e;
  --card:rgba(255,255,255,.055);--card-b:rgba(255,255,255,.1);--card-h:rgba(255,255,255,.09);
  --gold:#c8a84b;--gold-l:#f0d470;--gold-d:#8a6820;
  --sky:#4a90c4;--sky-l:#78b8e0;--sky-d:#1a507a;
  --text:#ede4d0;--text-m:#a09070;--text-d:#60503a;
  --accent:#e87828;--green:#28b870;--red:#d83820;--purple:#7050b8;
  --nav-bg:rgba(7,12,22,.8);--rx:16px;
  --shadow:0 8px 36px rgba(0,0,0,.5);
}
[data-theme="light"]{
  --bg:#f5f0e6;--bg2:#ede8d8;--bg3:#e4dcc8;
  --card:rgba(255,255,255,.88);--card-b:rgba(0,0,0,.1);--card-h:rgba(255,255,255,.96);
  --gold:#7a5a08;--gold-l:#b07c10;--gold-d:#4a3800;
  --sky:#1a5a90;--sky-l:#2878b8;--sky-d:#0e3860;
  --text:#14100a;--text-m:#5a4828;--text-d:#8a7450;
  --accent:#b84808;--green:#1a8850;--red:#c01808;--purple:#4828a0;
  --nav-bg:rgba(245,240,230,.92);--shadow:0 4px 20px rgba(0,0,0,.14);
}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:18px;scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',system-ui,sans-serif;overflow-x:hidden;line-height:1.65;transition:background .3s,color .3s}

/* ── NAV ── */
nav{position:fixed;top:0;inset-inline:0;z-index:300;height:64px;display:flex;align-items:center;padding:0 36px;gap:20px;backdrop-filter:blur(20px);background:var(--nav-bg);border-bottom:1px solid var(--card-b)}
.nav-logo{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--gold);text-decoration:none;flex-shrink:0;white-space:nowrap}
.nav-logo small{font-family:'DM Sans',sans-serif;font-weight:400;font-size:.72rem;color:var(--text-m);margin-left:6px}
.nav-links{display:flex;align-items:center;gap:18px;margin-left:auto}
.nav-links a{font-size:.85rem;font-weight:500;color:var(--text-m);text-decoration:none;transition:color .2s;white-space:nowrap}
.nav-links a:hover{color:var(--gold)}
.nav-sep{width:1px;height:18px;background:var(--card-b);flex-shrink:0}
.nav-ib{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;font-size:.75rem;font-weight:800;color:var(--text-m);letter-spacing:.3px}
.nav-ib:hover{border-color:var(--gold);color:var(--gold)}
.nav-ib i{font-size:1rem}
.nav-cta{padding:8px 22px;border-radius:40px;background:var(--gold);color:#fff;font-size:.85rem;font-weight:700;text-decoration:none;transition:opacity .2s,transform .15s;white-space:nowrap;border:none;cursor:pointer}
.nav-cta:hover{opacity:.85;transform:translateY(-1px)}
@media(max-width:800px){.nav-links a:not(.nav-cta){display:none}.nav-sep{display:none}}

/* ── HERO ── */
.hero{position:relative;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:32px;padding:88px 72px 64px;overflow:hidden}
@media(max-width:960px){.hero{grid-template-columns:1fr;padding:88px 24px 56px;text-align:center}}
.hero-bg{position:absolute;inset:0;z-index:0;pointer-events:none}
.hero-bg::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 65% 55% at 68% 50%,rgba(72,128,180,.13),transparent),radial-gradient(ellipse 45% 65% at 18% 85%,rgba(80,40,160,.1),transparent)}
[data-theme="light"] .hero-bg::before{background:radial-gradient(ellipse 65% 55% at 68% 50%,rgba(26,90,144,.07),transparent),radial-gradient(ellipse 45% 65% at 18% 85%,rgba(60,30,130,.05),transparent)}
#starsCv{position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none}
[data-theme="light"] #starsCv{opacity:.2}
.hero-content{position:relative;z-index:2}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(200,168,75,.12);border:1px solid rgba(200,168,75,.28);border-radius:40px;padding:6px 18px;font-size:.72rem;font-weight:800;color:var(--gold);letter-spacing:1.4px;text-transform:uppercase;margin-bottom:22px}
.hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.6rem,5vw,4rem);font-weight:700;line-height:1.08;margin-bottom:18px;color:var(--text)}
.hero-title em{color:var(--gold);font-style:italic}
.hero-sub{font-size:1.05rem;color:var(--text-m);margin-bottom:32px;line-height:1.78;max-width:480px}
@media(max-width:960px){.hero-sub{margin-inline:auto;margin-bottom:28px}}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
@media(max-width:960px){.hero-btns{justify-content:center}}
.btn-p{background:var(--gold);color:#fff;padding:14px 32px;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:opacity .2s,transform .15s,box-shadow .2s;box-shadow:0 6px 22px rgba(200,140,26,.4)}
.btn-p:hover{opacity:.88;transform:translateY(-2px);box-shadow:0 10px 28px rgba(200,140,26,.55)}
.btn-g{color:var(--text-m);border:1.5px solid var(--card-b);padding:13px 26px;border-radius:50px;font-size:.92rem;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:border-color .2s,color .2s}
.btn-g:hover{border-color:var(--gold);color:var(--gold)}
/* orrery */
.hero-visual{position:relative;z-index:2;display:flex;align-items:center;justify-content:center}
.orr-wrap{position:relative;width:500px;height:500px;max-width:min(90vw,500px)}
@media(max-width:960px){.orr-wrap{width:320px;height:320px}}
#zodSvg{position:absolute;inset:0;width:100%;height:100%;z-index:1;animation:zspin 100s linear infinite}
#orrCv{position:absolute;inset:0;width:100%;height:100%;z-index:2}
@keyframes zspin{to{transform:rotate(360deg)}}

/* ── SECTION BASE ── */
.sec{padding:72px 72px}
@media(max-width:700px){.sec{padding:52px 22px}}
.sec-alt{background:var(--bg2)}
.ey{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:2.5px;color:var(--gold);margin-bottom:10px;display:flex;align-items:center;gap:10px}
.ey::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,var(--gold-d),transparent)}
.ey i{font-size:1rem}
.stitle{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,3.2vw,2.6rem);font-weight:700;color:var(--text);margin-bottom:10px;line-height:1.2}
.ssub{color:var(--text-m);font-size:.96rem;max-width:540px;line-height:1.78;margin-bottom:44px}
.divider{height:1px;background:linear-gradient(90deg,transparent,var(--card-b),transparent);margin:0 72px}
@media(max-width:700px){.divider{margin:0 22px}}

/* ── PANCHANGA ── */
.day-nav{display:flex;align-items:center;gap:14px;margin-bottom:32px;flex-wrap:wrap}
.dn-btn{width:40px;height:40px;border-radius:50%;border:1.5px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-m);font-size:1.1rem;transition:all .2s;flex-shrink:0}
.dn-btn:hover{border-color:var(--gold);color:var(--gold)}
.dn-date{font-family:'Playfair Display',serif;font-size:1.65rem;font-weight:700;color:var(--text);line-height:1;display:flex;flex-direction:column;gap:4px}
.dn-date small{font-family:'DM Mono',monospace;font-size:.72rem;font-weight:400;color:var(--text-m)}
.dn-today{margin-left:auto;font-size:.78rem;font-weight:700;color:var(--gold);border:1.5px solid rgba(200,168,75,.3);background:rgba(200,168,75,.08);padding:6px 16px;border-radius:20px;cursor:pointer;transition:all .2s}
.dn-today:hover{background:rgba(200,168,75,.18)}
.pa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.pa-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:22px;position:relative;transition:transform .2s,box-shadow .2s}
.pa-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.pa-card[data-a="tithi"] {border-top:2.5px solid #7050b8}
.pa-card[data-a="vara"]  {border-top:2.5px solid #c8901a}
.pa-card[data-a="nak"]   {border-top:2.5px solid #4a90c4}
.pa-card[data-a="yoga"]  {border-top:2.5px solid #28b870}
.pa-card[data-a="karana"]{border-top:2.5px solid #e87828}
.pa-card[data-a="sun"]   {border-top:2.5px solid #d4921e}
.pa-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.pa-lbl{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-d);display:flex;align-items:center;gap:6px}
.pa-lbl i{font-size:.95rem}
.pa-num{width:24px;height:24px;border-radius:50%;background:var(--card-b);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:var(--text-d)}
.pa-diag{width:100%;display:flex;justify-content:center;margin-bottom:14px;border-radius:10px;overflow:hidden;background:var(--bg3)}
[data-theme="light"] .pa-diag{background:rgba(0,0,0,.04)}
.pa-diag canvas{display:block}
.pa-name{font-family:'Playfair Display',serif;font-size:1.25rem;font-weight:700;color:var(--text);margin-bottom:4px}
.pa-sub{font-size:.85rem;color:var(--text-m);line-height:1.5}
.pa-pills{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.pa-pill{font-size:.72rem;font-weight:600;background:var(--card-b);border-radius:20px;padding:3px 10px;color:var(--text-m)}

/* ── PLANETS ── */
.pl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
.plc{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:18px;display:flex;gap:14px;transition:transform .2s,box-shadow .2s}
.plc:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
.plc-ic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.45rem;flex-shrink:0}
.plc-b{flex:1;min-width:0}
.plc-lbl{font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:2px}
.plc-en{font-size:.82rem;color:var(--text-m);font-weight:500}
.plc-sign{font-family:'Playfair Display',serif;font-size:1.12rem;font-weight:700;color:var(--text);margin-top:5px}
.plc-nak{font-size:.78rem;color:var(--text-m);margin-top:2px}
.plc-deg{font-family:'DM Mono',monospace;font-size:.72rem;color:var(--text-d)}
.plc-ret{display:inline-block;font-size:.65rem;font-weight:800;background:rgba(232,120,40,.12);color:var(--accent);border:1px solid rgba(232,120,40,.28);border-radius:20px;padding:2px 8px;margin-top:5px}

/* ── CHARTS SECTION ── */
.charts-wrap{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start}
@media(max-width:900px){.charts-wrap{grid-template-columns:1fr}}
.chart-box{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:26px}
.chart-title{font-family:'Playfair Display',serif;font-size:1.18rem;font-weight:700;color:var(--text);margin-bottom:4px}
.chart-sub{font-size:.8rem;color:var(--text-m);margin-bottom:18px}
.chart-cv-wrap{display:flex;justify-content:center}

/* ── SHADBALA ── */
.shad-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start}
@media(max-width:900px){.shad-grid{grid-template-columns:1fr}}
.shad-chart{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:26px}
.shad-rows{display:flex;flex-direction:column;gap:14px;margin-top:16px}
.shad-row{display:flex;align-items:center;gap:12px}
.shad-sym{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.shad-label{font-size:.85rem;font-weight:700;color:var(--text);width:80px;flex-shrink:0}
.shad-bar-bg{flex:1;height:14px;background:var(--card-b);border-radius:7px;overflow:hidden;position:relative}
.shad-bar-fill{height:100%;border-radius:7px;transition:width 1.2s cubic-bezier(.4,0,.2,1)}
.shad-val{font-family:'DM Mono',monospace;font-size:.78rem;color:var(--text-m);width:40px;text-align:right;flex-shrink:0}

/* ── DASHA ── */
.dasha-split{display:grid;grid-template-columns:260px 1fr;gap:48px;align-items:center}
@media(max-width:800px){.dasha-split{grid-template-columns:1fr;text-align:center}.dasha-wheel{margin:0 auto}}
.dasha-wheel{width:260px;height:260px;flex-shrink:0}
.dasha-legend{display:flex;flex-direction:column;gap:11px}
.dl-row{display:grid;grid-template-columns:26px 1fr 48px 1fr;gap:10px;align-items:center}
.dl-dot{width:12px;height:12px;border-radius:50%}
.dl-name{font-size:.88rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px}
.dl-yr{font-family:'DM Mono',monospace;font-size:.78rem;color:var(--text-d);text-align:right}
.dl-bg{height:6px;background:var(--card-b);border-radius:4px;overflow:hidden}
.dl-fill{height:100%;border-radius:4px}

/* ── MUHURAT / CHOGHADIYA ── */
.cho-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start}
@media(max-width:900px){.cho-grid{grid-template-columns:1fr}}
.cho-chart{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:26px}
.cho-bars{display:flex;flex-direction:column;gap:10px;margin-top:18px}
.cho-bar-row{display:flex;align-items:center;gap:12px}
.cho-time{font-family:'DM Mono',monospace;font-size:.72rem;color:var(--text-d);width:88px;flex-shrink:0}
.cho-name-wrap{flex:1;border-radius:8px;padding:9px 14px;display:flex;align-items:center;justify-content:space-between}
.cho-name{font-size:.9rem;font-weight:700}
.cho-badge{font-size:.64rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;padding:2px 8px;border-radius:12px}
.cho-info{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:26px}
.cho-info-title{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--text);margin-bottom:14px}
.cho-legend{display:flex;flex-direction:column;gap:10px}
.cho-leg-row{display:flex;align-items:center;gap:10px}
.cho-leg-dot{width:14px;height:14px;border-radius:4px;flex-shrink:0}
.cho-leg-text{font-size:.85rem;color:var(--text-m)}
.cho-leg-text strong{color:var(--text);font-weight:700}

/* ── FESTIVALS ── */
.fest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.fest-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:18px;display:flex;gap:14px;align-items:flex-start;transition:transform .2s}
.fest-card:hover{transform:translateY(-2px)}
.fest-ic{width:44px;height:44px;border-radius:12px;background:var(--card-b);border:1px solid var(--card-b);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.35rem;color:var(--gold)}
.fest-b{flex:1;min-width:0}
.fest-date{font-family:'DM Mono',monospace;font-size:.72rem;font-weight:600;color:var(--gold);margin-bottom:4px}
.fest-name{font-weight:700;font-size:.95rem;color:var(--text);line-height:1.3;margin-bottom:2px}
.fest-hi{font-size:.8rem;color:var(--text-m)}
.fest-masa{font-size:.72rem;color:var(--text-d);margin-top:2px}
.fest-badge{display:inline-block;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;padding:2px 9px;border-radius:20px;margin-top:7px;border:1px solid transparent}
.fb-f{background:rgba(200,168,75,.1);color:var(--gold);border-color:rgba(200,168,75,.22)}
.fb-v{background:rgba(72,140,200,.1);color:var(--sky-l);border-color:rgba(72,140,200,.22)}

/* ── CTA ── */
.cta{text-align:center;padding:100px 24px;position:relative;overflow:hidden}
.cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 60% at 50% 50%,rgba(200,168,75,.07),transparent)}
[data-theme="light"] .cta::before{background:radial-gradient(ellipse 60% 60% at 50% 50%,rgba(122,90,8,.06),transparent)}

/* ── FOOTER ── */
footer{background:var(--bg2);border-top:1px solid var(--card-b);padding:28px 72px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
@media(max-width:700px){footer{padding:28px 22px;flex-direction:column;text-align:center}}
.fn{font-size:.82rem;color:var(--text-m);line-height:1.7}
.fl{display:flex;gap:18px}
.fl a{font-size:.82rem;color:var(--text-d);text-decoration:none;transition:color .2s}
.fl a:hover{color:var(--gold)}

/* ── REVEAL ── */
.rv{opacity:0;transform:translateY(22px);transition:opacity .6s cubic-bezier(.4,0,.2,1),transform .6s cubic-bezier(.4,0,.2,1)}
.rv.in{opacity:1;transform:none}
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">Vedic Astro <small>Calculator</small></a>
  <div class="nav-links">
    <a href="#panchanga" class="t" data-en="Panchanga" data-hi="पंचांग">Panchanga</a>
    <a href="#charts"   class="t" data-en="Charts"    data-hi="कुंडली">Charts</a>
    <a href="#dasha"    class="t" data-en="Dasha"     data-hi="दशा">Dasha</a>
    <a href="#muhurat"  class="t" data-en="Muhurat"   data-hi="मुहूर्त">Muhurat</a>
    <a href="#festivals" class="t" data-en="Festivals" data-hi="उत्सव">Festivals</a>
    <div class="nav-sep"></div>
    <button class="nav-ib" id="langBtn" onclick="toggleLang()">EN</button>
    <button class="nav-ib" id="themeBtn" onclick="toggleTheme()"><i class="ph ph-moon-stars" id="themeIco"></i></button>
    <div class="nav-sep"></div>
    <a href="/astro" class="nav-cta t" data-en="Open Calculator" data-hi="कैलकुलेटर खोलें">Open Calculator</a>
  </div>
</nav>

{{-- ══ HERO ══ --}}
<section class="hero">
  <div class="hero-bg"></div>
  <canvas id="starsCv"></canvas>
  <div class="hero-content">
    <div class="hero-badge"><i class="ph ph-star-four" style="font-size:.85rem"></i>&nbsp;<span class="t" data-en="Vedic Jyotish · Jean Meeus Algorithms" data-hi="वैदिक ज्योतिष · जीन मेउस एल्गोरिदम">Vedic Jyotish · Jean Meeus Algorithms</span></div>
    <h1 class="hero-title" id="heroT">Vedic <em>Astro</em><br><span class="t" data-en="Calculator" data-hi="कैलकुलेटर">Calculator</span></h1>
    <p class="hero-sub t" data-en="Precise planetary calculations, live Panchanga, Vimshottari Dasha, Shodashvarga charts and Muhurat — all computed in real time from first principles." data-hi="सटीक ग्रह गणना, जीवंत पंचांग, विंशोत्तरी दशा, सोलह वर्ग कुंडली और मुहूर्त — सभी रियल टाइम में।">Precise planetary calculations, live Panchanga, Vimshottari Dasha, Shodashvarga charts and Muhurat — all computed in real time from first principles.</p>
    <div class="hero-btns">
      <a href="/astro" class="btn-p"><i class="ph ph-star-four"></i><span class="t" data-en="Calculate Your Chart" data-hi="कुंडली बनाएं">Calculate Your Chart</span></a>
      <a href="#panchanga" class="btn-g"><span class="t" data-en="Today's Panchanga" data-hi="आज का पंचांग">Today's Panchanga</span> <i class="ph ph-arrow-down"></i></a>
    </div>
    {{-- live stats strip --}}
    <div style="margin-top:36px;display:flex;flex-wrap:wrap;gap:14px">
      <div style="background:var(--card);border:1px solid var(--card-b);border-radius:12px;padding:11px 18px">
        <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:var(--gold);margin-bottom:3px" class="t" data-en="Today's Tithi" data-hi="आज की तिथि">Today's Tithi</div>
        <div style="font-size:.98rem;font-weight:700;color:var(--text)">{{ $tithi['name'] }} · {{ $tithi['paksha'] }}</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--card-b);border-radius:12px;padding:11px 18px">
        <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:var(--sky-l);margin-bottom:3px" class="t" data-en="Moon Nakshatra" data-hi="चंद्र नक्षत्र">Moon Nakshatra</div>
        <div style="font-size:.98rem;font-weight:700;color:var(--text)">{{ $nakshatra['name'] }} Pada {{ $nakshatra['pada'] }}</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--card-b);border-radius:12px;padding:11px 18px">
        <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:var(--text-d);margin-bottom:3px" class="t" data-en="Sunrise · Sunset" data-hi="सूर्योदय · अस्त">Sunrise · Sunset</div>
        <div style="font-family:'DM Mono',monospace;font-size:.98rem;font-weight:700;color:var(--text)">{{ $sunrise }} — {{ $sunset }}</div>
      </div>
    </div>
  </div>
  <div class="hero-visual">
    <div class="orr-wrap">
      <svg id="zodSvg" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg"></svg>
      <canvas id="orrCv" width="500" height="500"></canvas>
    </div>
  </div>
</section>

{{-- ══ PANCHANGA ══ --}}
<section class="sec sec-alt" id="panchanga">
  <div class="ey"><i class="ph ph-calendar-dots"></i> <span class="t" data-en="Live Panchanga · New Delhi" data-hi="जीवंत पंचांग · नई दिल्ली">Live Panchanga · New Delhi</span></div>
  <div class="day-nav">
    <button class="dn-btn" id="btnPrev" onclick="shiftDay(-1)"><i class="ph ph-caret-left"></i></button>
    <button class="dn-btn" id="btnNext" onclick="shiftDay(1)"><i class="ph ph-caret-right"></i></button>
    <div class="dn-date" id="ddDate">{{ $dateDisplay }}<small id="ddSub">{{ $dayName }} · Ayanamsa {{ $ayan }}°</small></div>
    <button class="dn-today" onclick="gotoToday()" class="t" data-en="Today" data-hi="आज">Today</button>
  </div>
  <div class="pa-grid rv" id="paGrid">
    <div class="pa-card" data-a="tithi">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-moon"></i><span class="t" data-en="Tithi — Lunar Day" data-hi="तिथि — चंद्र दिवस">Tithi — Lunar Day</span></span><span class="pa-num">①</span></div>
      <div class="pa-diag"><canvas id="cvT" width="220" height="130"></canvas></div>
      <div class="pa-name" id="pvTN">{{ $tithi['name'] }}</div>
      <div class="pa-sub" id="pvTS">{{ $tithi['paksha'] }} Paksha · {{ $tithi['num'] }}/15 · {{ $tithi['elong'] }}°</div>
      <div class="pa-pills"><span class="pa-pill" id="pvTL">Lord: {{ $tithi['lord'] }}</span><span class="pa-pill">{{ $tithi['nature'] }}</span></div>
    </div>
    <div class="pa-card" data-a="vara">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-sun"></i><span class="t" data-en="Vara — Weekday" data-hi="वार — सप्ताह का दिन">Vara — Weekday</span></span><span class="pa-num">②</span></div>
      <div class="pa-diag"><canvas id="cvV" width="220" height="130"></canvas></div>
      <div class="pa-name" id="pvVN">{{ $vara['name'] }}</div>
      <div class="pa-sub" id="pvVS">{{ $vara['en'] }} · Lord: {{ $vara['lord'] }}</div>
      <div class="pa-pills"><span class="pa-pill" id="pvVNat">{{ $vara['nature'] }}</span></div>
    </div>
    <div class="pa-card" data-a="nak">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-star"></i><span class="t" data-en="Nakshatra — Moon Mansion" data-hi="नक्षत्र — चंद्र भवन">Nakshatra — Moon Mansion</span></span><span class="pa-num">③</span></div>
      <div class="pa-diag"><canvas id="cvN" width="220" height="130"></canvas></div>
      <div class="pa-name" id="pvNN">{{ $nakshatra['name'] }}</div>
      <div class="pa-sub" id="pvNS">Pada {{ $nakshatra['pada'] }} · Lord: {{ $nakshatra['lord'] }}</div>
      <div class="pa-pills"><span class="pa-pill">{{ $nakshatra['gana'] }}</span><span class="pa-pill" id="pvND">{{ $nakshatra['deity'] }}</span></div>
    </div>
    <div class="pa-card" data-a="yoga">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-compass"></i><span class="t" data-en="Yoga — Luni-Solar" data-hi="योग — चंद्र-सौर">Yoga — Luni-Solar</span></span><span class="pa-num">④</span></div>
      <div class="pa-diag"><canvas id="cvY" width="220" height="130"></canvas></div>
      <div class="pa-name" id="pvYN">{{ $yoga['name'] }}</div>
      <div class="pa-sub" id="pvYS">{{ $yoga['nature'] }} · Lord: {{ $yoga['lord'] }}</div>
      <div class="pa-pills"><span class="pa-pill" id="pvYC">{{ $yoga['cls'] }}</span><span class="pa-pill">{{ $yoga['idx']+1 }}/27</span></div>
    </div>
    <div class="pa-card" data-a="karana">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-circle-half"></i><span class="t" data-en="Karana — Half Tithi" data-hi="करण — अर्ध तिथि">Karana — Half Tithi</span></span><span class="pa-num">⑤</span></div>
      <div class="pa-diag"><canvas id="cvK" width="220" height="130"></canvas></div>
      <div class="pa-name" id="pvKN">{{ $karana['name'] }}</div>
      <div class="pa-sub" id="pvKS">{{ $karana['type'] }} · Slot {{ $karana['slot'] }}/60</div>
      <div class="pa-pills"><span class="pa-pill">Lord: {{ $karana['lord'] }}</span><span class="pa-pill">{{ $karana['nature'] }}</span></div>
    </div>
    <div class="pa-card" data-a="sun">
      <div class="pa-top"><span class="pa-lbl"><i class="ph ph-sunrise"></i><span class="t" data-en="Sunrise &amp; Sunset" data-hi="सूर्योदय और सूर्यास्त">Sunrise &amp; Sunset</span></span><span class="pa-num">☀</span></div>
      <div class="pa-diag"><canvas id="cvSun" width="220" height="130"></canvas></div>
      <div class="pa-name" style="font-family:'DM Mono',monospace;font-size:1.1rem" id="pvSunT">{{ $sunrise }} — {{ $sunset }}</div>
      <div class="pa-sub t" data-en="New Delhi · IST +5:30" data-hi="नई दिल्ली · IST +5:30">New Delhi · IST +5:30</div>
      <div class="pa-pills"><span class="pa-pill">Rise: <span id="pvSunR">{{ $sunrise }}</span></span><span class="pa-pill">Set: <span id="pvSunS">{{ $sunset }}</span></span></div>
    </div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ PLANETS ══ --}}
<section class="sec" id="planets">
  <div class="ey"><i class="ph ph-planet"></i> <span class="t" data-en="Nava Graha · Sidereal Positions" data-hi="नव ग्रह · सायन स्थितियाँ">Nava Graha · Sidereal Positions</span></div>
  <h2 class="stitle t" data-en="Nine Planets — Live Positions" data-hi="नव ग्रह — जीवंत स्थितियाँ">Nine Planets — Live Positions</h2>
  <p class="ssub t" data-en="Geocentric sidereal longitudes for New Delhi, India (Lahiri Ayanamsa)." data-hi="नई दिल्ली के लिए भू-केंद्रीय सायन देशांतर (लाहिरी अयनांश)।">Geocentric sidereal longitudes for New Delhi, India (Lahiri Ayanamsa).</p>
  <div class="pl-grid rv">
    @foreach($planets as $pid => $p)
    <div class="plc" style="border-left:3px solid {{ $p['color'] }}55">
      <div class="plc-ic" style="background:{{ $p['color'] }}20;color:{{ $p['color'] }}">{{ $p['sym'] }}</div>
      <div class="plc-b">
        <div class="plc-lbl" style="color:{{ $p['color'] }}">{{ $p['label'] }}</div>
        <div class="plc-en">{{ ucfirst($pid) }}</div>
        <div class="plc-sign">{{ $p['sign'] }}</div>
        <div class="plc-nak">{{ $p['nak'] }} · Lord: {{ $p['lord'] }}</div>
        <div class="plc-deg">{{ $p['deg'] }} · H{{ $p['house'] }}</div>
        @if($p['retro'])<div class="plc-ret">↺ Retrograde</div>@endif
      </div>
    </div>
    @endforeach
  </div>
</section>

<div class="divider"></div>

{{-- ══ VEDIC CHARTS ══ --}}
<section class="sec sec-alt" id="charts">
  <div class="ey"><i class="ph ph-chart-donut-slice"></i> <span class="t" data-en="Vedic Birth Charts" data-hi="वैदिक जन्म कुंडली">Vedic Birth Charts</span></div>
  <h2 class="stitle t" data-en="North &amp; South Indian Kundali" data-hi="उत्तर और दक्षिण भारतीय कुंडली">North &amp; South Indian Kundali</h2>
  <p class="ssub t" data-en="Current planetary positions mapped on both chart styles. House 1 = Lagna sign at sunrise, New Delhi." data-hi="दोनों कुंडली शैलियों पर वर्तमान ग्रह स्थितियाँ।">Current planetary positions mapped on both chart styles. House 1 = Lagna sign at sunrise, New Delhi.</p>
  <div class="charts-wrap rv">
    <div class="chart-box">
      <div class="chart-title t" data-en="North Indian (Shalivahana)" data-hi="उत्तर भारतीय (शालिवाहन)">North Indian (Shalivahana)</div>
      <div class="chart-sub t" data-en="House 1 at top · houses go clockwise" data-hi="House 1 शीर्ष पर · घड़ी की दिशा में">House 1 at top · houses go clockwise</div>
      <div class="chart-cv-wrap"><canvas id="niCv" width="320" height="320"></canvas></div>
    </div>
    <div class="chart-box">
      <div class="chart-title t" data-en="South Indian (Kerala)" data-hi="दक्षिण भारतीय (केरल)">South Indian (Kerala)</div>
      <div class="chart-sub t" data-en="Fixed signs · Lagna marked with border" data-hi="स्थिर राशियाँ · लग्न मोटी रेखा से">Fixed signs · Lagna marked with border</div>
      <div class="chart-cv-wrap"><canvas id="siCv" width="320" height="320"></canvas></div>
    </div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ SHADBALA ══ --}}
<section class="sec" id="shadbala">
  <div class="ey"><i class="ph ph-scales"></i> <span class="t" data-en="Shadbala — Six-Fold Strength" data-hi="षड्बल — छः प्रकार की शक्ति">Shadbala — Six-Fold Strength</span></div>
  <h2 class="stitle t" data-en="Natural Planetary Strengths" data-hi="नैसर्गिक ग्रह बल">Natural Planetary Strengths</h2>
  <p class="ssub t" data-en="Naisargika Bala — the permanent, natural strength each planet holds in the cosmic hierarchy." data-hi="नैसर्गिक बल — प्रत्येक ग्रह का स्थायी प्राकृतिक बल।">Naisargika Bala — the permanent, natural strength each planet holds in the cosmic hierarchy.</p>
  <div class="shad-grid rv">
    <div class="shad-chart">
      <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--text);margin-bottom:4px" class="t" data-en="Naisargika Bala (Shashtiamshas)" data-hi="नैसर्गिक बल (षष्ट्यंश)">Naisargika Bala (Shashtiamshas)</div>
      <div style="font-size:.8rem;color:var(--text-m);margin-bottom:18px" class="t" data-en="Higher value = greater natural strength" data-hi="अधिक मान = अधिक प्राकृतिक बल">Higher value = greater natural strength</div>
      <div class="shad-rows" id="shadRows"></div>
    </div>
    <div class="shad-chart">
      <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--text);margin-bottom:14px" class="t" data-en="Current Sign Positions" data-hi="वर्तमान राशि स्थितियाँ">Current Sign Positions</div>
      <canvas id="shadCv" width="320" height="300" style="width:100%;border-radius:10px"></canvas>
    </div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ DASHA ══ --}}
<section class="sec sec-alt" id="dasha">
  <div class="ey"><i class="ph ph-hourglass"></i> <span class="t" data-en="Vimshottari Dasha Chakra" data-hi="विंशोत्तरी दशा चक्र">Vimshottari Dasha Chakra</span></div>
  <h2 class="stitle t" data-en="120-Year Planetary Cycle" data-hi="120-वर्षीय ग्रहीय चक्र">120-Year Planetary Cycle</h2>
  <p class="ssub t" data-en="Each lord rules for a fixed period based on the Moon's birth nakshatra. The cycle totals exactly 120 years." data-hi="प्रत्येक ग्रह जन्म नक्षत्र के आधार पर एक निश्चित अवधि शासन करता है।">Each lord rules for a fixed period based on the Moon's birth nakshatra. The cycle totals exactly 120 years.</p>
  <div class="dasha-split rv">
    <div class="dasha-wheel"><svg id="dashaSvg" viewBox="0 0 260 260" width="260" height="260"></svg></div>
    <div class="dasha-legend" id="dashaLeg"></div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ MUHURAT / CHOGHADIYA ══ --}}
<section class="sec" id="muhurat">
  <div class="ey"><i class="ph ph-clock"></i> <span class="t" data-en="Muhurat — Auspicious Timing" data-hi="मुहूर्त — शुभ समय">Muhurat — Auspicious Timing</span></div>
  <h2 class="stitle t" data-en="Today's Choghadiya — {{ $dayName }}" data-hi="आज का चौघड़िया — {{ $vara['n'] ?? $dayName }}">Today's Choghadiya — {{ $dayName }}</h2>
  <p class="ssub t" data-en="Eight time periods from sunrise to sunset, classified by auspiciousness for activities. New Delhi · {{ $sunrise }} to {{ $sunset }}." data-hi="सूर्योदय से सूर्यास्त तक आठ समय-खंड।">Eight time periods from sunrise to sunset, classified by auspiciousness for activities. New Delhi · {{ $sunrise }} to {{ $sunset }}.</p>
  <div class="cho-grid rv">
    <div class="cho-chart">
      <div style="font-family:'Playfair Display',serif;font-size:1.12rem;font-weight:700;color:var(--text);margin-bottom:4px" class="t" data-en="Day Choghadiya" data-hi="दिन चौघड़िया">Day Choghadiya</div>
      <div style="font-size:.8rem;color:var(--text-m);margin-bottom:6px">{{ $sunrise }} — {{ $sunset }}</div>
      <div class="cho-bars">
        @foreach($choghadiya as $ch)
        @php
          $qClass = $ch['quality'] === 'best' ? '#28b870' : ($ch['quality'] === 'good' ? '#4a90c4' : ($ch['quality'] === 'neutral' ? '#a08060' : ($ch['quality'] === 'bad' ? '#d83820' : '#7060a8')));
          $qBg    = $ch['quality'] === 'best' ? 'rgba(40,184,112,.15)' : ($ch['quality'] === 'good' ? 'rgba(74,144,196,.15)' : ($ch['quality'] === 'neutral' ? 'rgba(160,128,96,.12)' : 'rgba(216,56,32,.12)'));
          $qLabel = $ch['quality'] === 'best' ? 'Best' : ucfirst($ch['quality']);
        @endphp
        <div class="cho-bar-row">
          <div class="cho-time">{{ $ch['start'] }}–{{ $ch['end'] }}</div>
          <div class="cho-name-wrap" style="background:{{ $qBg }};border:1px solid {{ $qClass }}30">
            <div>
              <div class="cho-name" style="color:{{ $qClass }}">{{ $ch['name'] }}</div>
              <div style="font-size:.72rem;color:var(--text-m)">{{ $ch['nameHi'] }}</div>
            </div>
            <div class="cho-badge" style="background:{{ $qClass }}20;color:{{ $qClass }};border:1px solid {{ $qClass }}40">{{ $qLabel }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    <div class="cho-info">
      <div class="cho-info-title t" data-en="Choghadiya Guide" data-hi="चौघड़िया मार्गदर्शिका">Choghadiya Guide</div>
      <div class="cho-legend">
        @foreach([
          ['#28b870','Amrit','अमृत','Moon — Best for all activities'],
          ['#4a90c4','Shubh','शुभ','Venus/Jupiter — Auspicious'],
          ['#6ab04c','Labh','लाभ','Mercury — Good for gain'],
          ['#a08060','Chal','चल','Mercury/Venus — Neutral, travel'],
          ['#d83820','Rog','रोग','Mars — Avoid new starts'],
          ['#7060a8','Kaal','काल','Saturn — Inauspicious'],
          ['#c05018','Udveg','उद्वेग','Sun — Inauspicious, avoid'],
        ] as [$col,$nm,$hi,$desc])
        <div class="cho-leg-row">
          <div class="cho-leg-dot" style="background:{{ $col }}"></div>
          <div class="cho-leg-text"><strong>{{ $nm }} ({{ $hi }})</strong> — {{ $desc }}</div>
        </div>
        @endforeach
      </div>
      <div style="margin-top:24px;padding:16px;background:var(--card-b);border-radius:12px">
        <div style="font-size:.78rem;font-weight:700;color:var(--gold);margin-bottom:6px" class="t" data-en="Open Full Muhurat Calculator" data-hi="पूर्ण मुहूर्त कैलकुलेटर खोलें">Full Muhurat Calculator</div>
        <div style="font-size:.82rem;color:var(--text-m);margin-bottom:12px" class="t" data-en="Vivah, Griha Pravesh, Vahana and more — with month &amp; year scan." data-hi="विवाह, गृह प्रवेश, वाहन और अधिक।">Vivah, Griha Pravesh, Vahana and more — with month &amp; year scan.</div>
        <a href="/astro" class="btn-p" style="font-size:.85rem;padding:10px 22px;display:inline-flex"><i class="ph ph-clock"></i>&nbsp;<span class="t" data-en="Open Muhurat" data-hi="मुहूर्त खोलें">Open Muhurat</span></a>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ FESTIVALS ══ --}}
<section class="sec sec-alt" id="festivals">
  <div class="ey"><i class="ph ph-candle"></i> <span class="t" data-en="Upcoming Festivals &amp; Vratas" data-hi="आगामी त्योहार और व्रत">Upcoming Festivals &amp; Vratas</span></div>
  <h2 class="stitle t" data-en="Festival Calendar" data-hi="त्योहार कैलेंडर">Festival Calendar</h2>
  <p class="ssub t" data-en="Computed from astronomical tithi positions for New Delhi, India." data-hi="नई दिल्ली के लिए तिथि स्थितियों से गणना।">Computed from astronomical tithi positions for New Delhi, India.</p>
  <div class="fest-grid rv">
    @php
    $catIcon = ['ekadashi'=>'ph-moon','purnima'=>'ph-circle','amavasya'=>'ph-moon-stars',
                'festival'=>'ph-candle','navratri'=>'ph-star-four','pradosh'=>'ph-triangle',
                'chaturthi'=>'ph-shapes','shraddha'=>'ph-hands-praying',
                'kalashtami'=>'ph-moon','sankranti'=>'ph-sun-horizon',
                'national'=>'ph-flag','satyanarayan'=>'ph-flower',
                'jayanti'=>'ph-star-four','masik_shivratri'=>'ph-moon',
                'durgaashtami'=>'ph-sun','amavasya'=>'ph-moon-stars'];
    @endphp
    @foreach($upcoming as $f)
    @php $fd = \DateTime::createFromFormat('Y-m-d', $f['date']); @endphp
    <div class="fest-card">
      <div class="fest-ic"><i class="ph {{ $catIcon[$f['category'] ?? ''] ?? 'ph-star' }}"></i></div>
      <div class="fest-b">
        <div class="fest-date">{{ $fd ? $fd->format('d M Y') : $f['date'] }}</div>
        <div class="fest-name">{{ $f['name'] }}</div>
        @if(!empty($f['name_hi']))<div class="fest-hi">{{ $f['name_hi'] }}</div>@endif
        <div class="fest-masa">{{ $f['masa'] ?? '' }}</div>
        <span class="fest-badge {{ $f['type']==='festival'?'fb-f':'fb-v' }}">{{ $f['type'] }}</span>
      </div>
    </div>
    @endforeach
  </div>
  <div style="text-align:center;margin-top:28px"><a href="/astro" class="btn-g"><span class="t" data-en="View Full Calendar →" data-hi="पूरा कैलेंडर →">View Full Calendar →</span></a></div>
</section>

{{-- ══ CTA ══ --}}
<section class="cta">
  <div style="position:relative;z-index:1">
    <div class="ey" style="justify-content:center;margin-bottom:16px"><i class="ph ph-star-four"></i>&nbsp;<span class="t" data-en="Get Started" data-hi="शुरू करें">Get Started</span></div>
    <h2 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,2.8rem);font-weight:700;color:var(--text);margin-bottom:14px"><span class="t" data-en="Calculate Your Birth Chart" data-hi="अपनी जन्म कुंडली बनाएं">Calculate Your Birth Chart</span></h2>
    <p style="color:var(--text-m);font-size:1rem;margin:0 auto 34px;max-width:500px"><span class="t" data-en="Enter your date, time and location to generate a complete Jyotish reading." data-hi="पूर्ण ज्योतिष पठन के लिए जन्म तिथि, समय और स्थान दर्ज करें।">Enter your date, time and location to generate a complete Jyotish reading.</span></p>
    <div style="display:flex;justify-content:center"><a href="/astro" class="btn-p" style="font-size:1rem;padding:15px 42px"><i class="ph-bold ph-star-four"></i>&nbsp;<span class="t" data-en="Open Calculator" data-hi="कैलकुलेटर खोलें">Open Calculator</span></a></div>
  </div>
</section>

<footer>
  <div class="fn"><strong>Vedic Astro Calculator</strong> · Jean Meeus Astronomical Algorithms (2nd Ed.)<br><span class="t" data-en="Lahiri Ayanamsa · BPHS Rules" data-hi="लाहिरी अयनांश · BPHS नियम">Lahiri Ayanamsa · BPHS Rules</span></div>
  <div class="fl"><a href="/astro">Calculator</a><a href="/astro">Festivals</a><a href="/astro">Muhurat</a></div>
</footer>

{{-- ══ DATA ══ --}}
@php
$_b=['date'=>$date,'sunrise'=>$sunrise,'sunset'=>$sunset,'tithi'=>$tithi,'vara'=>$vara,'nakshatra'=>$nakshatra,'yoga'=>$yoga,'karana'=>$karana,'ascSignIdx'=>$ascSignIdx,'choghadiya'=>$choghadiya];
@endphp
<script>
const PD=@json($_b);
const PL=@json($planets);
const ASI={{ $ascSignIdx }};
const TODAY='{{ $date }}';
</script>

<script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
'use strict';

// ── TRANSLATIONS ─────────────────────────────────────────────────────────
const LNG={
  tithi:{'Pratipada':'प्रतिपदा','Dwitiya':'द्वितीया','Tritiya':'तृतीया','Chaturthi':'चतुर्थी','Panchami':'पंचमी','Shashthi':'षष्ठी','Saptami':'सप्तमी','Ashtami':'अष्टमी','Navami':'नवमी','Dashami':'दशमी','Ekadashi':'एकादशी','Dwadashi':'द्वादशी','Trayodashi':'त्रयोदशी','Chaturdashi':'चतुर्दशी','Purnima':'पूर्णिमा','Amavasya':'अमावस्या'},
  paksha:{'Shukla':'शुक्ल','Krishna':'कृष्ण'},
  vara:{'Sunday':'रविवार','Monday':'सोमवार','Tuesday':'मंगलवार','Wednesday':'बुधवार','Thursday':'गुरुवार','Friday':'शुक्रवार','Saturday':'शनिवार'},
  nak:{'Ashwini':'अश्विनी','Bharani':'भरणी','Krittika':'कृत्तिका','Rohini':'रोहिणी','Mrigashira':'मृगशिरा','Ardra':'आर्द्रा','Punarvasu':'पुनर्वसु','Pushya':'पुष्य','Ashlesha':'आश्लेषा','Magha':'मघा','Purva Phalguni':'पूर्व फाल्गुनी','Uttara Phalguni':'उत्तर फाल्गुनी','Hasta':'हस्त','Chitra':'चित्रा','Swati':'स्वाती','Vishakha':'विशाखा','Anuradha':'अनुराधा','Jyeshtha':'ज्येष्ठा','Moola':'मूल','Purva Ashadha':'पूर्व आषाढ़','Uttara Ashadha':'उत्तर आषाढ़','Shravana':'श्रवण','Dhanishta':'धनिष्ठा','Shatabhisha':'शतभिषा','Purva Bhadrapada':'पूर्व भाद्रपदा','Uttara Bhadrapada':'उत्तर भाद्रपदा','Revati':'रेवती'},
  sign:{'Mesha':'मेष','Vrishabha':'वृषभ','Mithuna':'मिथुन','Karka':'कर्क','Simha':'सिंह','Kanya':'कन्या','Tula':'तुला','Vrishchika':'वृश्चिक','Dhanu':'धनु','Makara':'मकर','Kumbha':'कुंभ','Meena':'मीन'},
};
function tr(v,cat){return(document.documentElement.dataset.lang==='hi'&&LNG[cat]&&LNG[cat][v])||v;}

// ── THEME ─────────────────────────────────────────────────────────────────
function toggleTheme(){
  const d=document.documentElement,dark=d.dataset.theme==='dark';
  d.dataset.theme=dark?'light':'dark';
  document.getElementById('themeIco').className=dark?'ph ph-sun':'ph ph-moon-stars';
  localStorage.setItem('theme',dark?'light':'dark');
  setTimeout(redrawAll,50);
}
(()=>{const s=localStorage.getItem('theme')||'dark';document.documentElement.dataset.theme=s;const i=document.getElementById('themeIco');if(i)i.className=s==='light'?'ph ph-sun':'ph ph-moon-stars';})();

// ── LANGUAGE ──────────────────────────────────────────────────────────────
function toggleLang(){
  const d=document.documentElement,hi=d.dataset.lang==='en';
  d.dataset.lang=hi?'hi':'en';
  document.getElementById('langBtn').textContent=hi?'हि':'EN';
  document.querySelectorAll('.t[data-en]').forEach(el=>{el.textContent=el.dataset[d.dataset.lang]||el.dataset.en;});
  localStorage.setItem('lang',hi?'hi':'en');
}
(()=>{const s=localStorage.getItem('lang')||'en';document.documentElement.dataset.lang=s;const b=document.getElementById('langBtn');if(b)b.textContent=s==='hi'?'हि':'EN';if(s==='hi')document.querySelectorAll('.t[data-en]').forEach(el=>el.textContent=el.dataset.hi||el.dataset.en);})();

// ── HELPERS ───────────────────────────────────────────────────────────────
const isDark=()=>document.documentElement.dataset.theme==='dark';
const cv=(id)=>document.getElementById(id);
const cx2d=(id)=>{const c=cv(id);return c?c.getContext('2d'):null;};
const rgb=(h,a=1)=>{const r=parseInt(h.slice(1,3),16),g=parseInt(h.slice(3,5),16),b=parseInt(h.slice(5,7),16);return`rgba(${r},${g},${b},${a})`;};
const vr=(n)=>getComputedStyle(document.documentElement).getPropertyValue(n).trim();

// ── STARS ─────────────────────────────────────────────────────────────────
(()=>{
  const c=cv('starsCv');if(!c)return;
  const resize=()=>{c.width=innerWidth;c.height=innerHeight;};
  resize();addEventListener('resize',resize);
  const ctx=c.getContext('2d');
  const S=Array.from({length:220},()=>({x:Math.random(),y:Math.random(),r:.2+Math.random()*1.1,a:Math.random(),s:.3+Math.random()*.6,p:Math.random()*Math.PI*2}));
  let t=0;
  (function f(){ctx.clearRect(0,0,c.width,c.height);S.forEach(s=>{const a=s.a*.65*(.5+.5*Math.sin(t*s.s+s.p));ctx.fillStyle=`rgba(220,210,185,${a})`;ctx.beginPath();ctx.arc(s.x*c.width,s.y*c.height,s.r,0,Math.PI*2);ctx.fill();});t+=.007;requestAnimationFrame(f);})();
})();

// ── ZODIAC SVG RING ───────────────────────────────────────────────────────
(()=>{
  const svg=cv('zodSvg');if(!svg)return;
  const ns='http://www.w3.org/2000/svg',cx=250,cy=250,ro=238,ri=202;
  const G=['♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓'];
  for(let i=0;i<12;i++){
    const a1=(i*30-90)*Math.PI/180,a2=((i+1)*30-90)*Math.PI/180,am=((i+.5)*30-90)*Math.PI/180,rm=(ro+ri)/2;
    const p=document.createElementNS(ns,'path');
    p.setAttribute('d',`M${cx+ro*Math.cos(a1)},${cy+ro*Math.sin(a1)} A${ro},${ro} 0 0,1 ${cx+ro*Math.cos(a2)},${cy+ro*Math.sin(a2)} L${cx+ri*Math.cos(a2)},${cy+ri*Math.sin(a2)} A${ri},${ri} 0 0,0 ${cx+ri*Math.cos(a1)},${cy+ri*Math.sin(a1)} Z`);
    p.setAttribute('fill',`hsla(${i*30},38%,${isDark()?18:72}%,.7)`);
    p.setAttribute('stroke',`hsla(${i*30},50%,${isDark()?45:35}%,.2)`);p.setAttribute('stroke-width','0.8');
    svg.appendChild(p);
    const tx=document.createElementNS(ns,'text');
    tx.setAttribute('x',cx+rm*Math.cos(am));tx.setAttribute('y',cy+rm*Math.sin(am));
    tx.setAttribute('text-anchor','middle');tx.setAttribute('dominant-baseline','central');
    tx.setAttribute('fill',`hsla(${i*30},60%,${isDark()?72:30}%,.65)`);tx.setAttribute('font-size','16');
    tx.textContent=G[i];svg.appendChild(tx);
  }
  const ring=document.createElementNS(ns,'circle');ring.setAttribute('cx',cx);ring.setAttribute('cy',cy);ring.setAttribute('r',ro+5);ring.setAttribute('fill','none');ring.setAttribute('stroke','rgba(200,168,75,.18)');ring.setAttribute('stroke-width','1.5');svg.appendChild(ring);
})();

// ── ORRERY ────────────────────────────────────────────────────────────────
const ORB={moon:56,mercury:86,venus:114,sun:142,mars:164,jupiter:186,saturn:208,rahu:228};
const OCOL={sun:'#d4921e',moon:'#5ab8e8',mercury:'#28b870',venus:'#c060a8',mars:'#e03020',jupiter:'#c89020',saturn:'#7868b8',rahu:'#28a858',ketu:'#b83820'};
const OLBL={sun:'Su',moon:'Mo',mercury:'Me',venus:'Ve',mars:'Ma',jupiter:'Ju',saturn:'Sa',rahu:'Ra',ketu:'Ke'};
let drift=0,orTip=null,orPL=PL;

function drawOrr(pd){
  const ctx=cx2d('orrCv');if(!ctx)return;
  ctx.clearRect(0,0,500,500);
  const CX=250,CY=250,dark=isDark();
  // Earth
  const eg=ctx.createRadialGradient(CX,CY,0,CX,CY,26);
  eg.addColorStop(0,dark?'rgba(80,150,220,.9)':'rgba(40,100,200,.85)');eg.addColorStop(.5,'rgba(30,70,150,.5)');eg.addColorStop(1,'transparent');
  ctx.fillStyle=eg;ctx.beginPath();ctx.arc(CX,CY,26,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=dark?'#4a9ae0':'#2878c8';ctx.beginPath();ctx.arc(CX,CY,8,0,Math.PI*2);ctx.fill();
  // Orbits
  Object.values(ORB).forEach(r=>{
    ctx.beginPath();ctx.arc(CX,CY,r,0,Math.PI*2);
    ctx.strokeStyle=dark?'rgba(150,190,230,.2)':'rgba(0,80,150,.15)';ctx.lineWidth=1;ctx.stroke();
  });
  // Planets
  Object.keys(ORB).forEach(pid=>{
    if(!pd[pid])return;
    const r=ORB[pid],lon=pd[pid].lon+drift,ang=(lon-90)*Math.PI/180;
    const x=CX+r*Math.cos(ang),y=CY+r*Math.sin(ang),col=OCOL[pid]||'#888';
    const sz=pid==='sun'?11:pid==='moon'?8:6;
    const grd=ctx.createRadialGradient(x,y,0,x,y,sz*3);
    grd.addColorStop(0,col+'cc');grd.addColorStop(1,'transparent');
    ctx.fillStyle=grd;ctx.beginPath();ctx.arc(x,y,sz*3,0,Math.PI*2);ctx.fill();
    ctx.fillStyle=col;ctx.beginPath();ctx.arc(x,y,sz,0,Math.PI*2);ctx.fill();
    const lr=r+17,lx=CX+lr*Math.cos(ang),ly=CY+lr*Math.sin(ang);
    ctx.fillStyle=col;ctx.font=`bold 11px DM Sans,sans-serif`;ctx.textAlign='center';ctx.textBaseline='middle';
    ctx.fillText(OLBL[pid]||pid.slice(0,2).toUpperCase(),lx,ly);
  });
  // Ketu (opposite Rahu)
  if(pd.rahu){
    const r=ORB.rahu,lon=pd.rahu.lon+180+drift,ang=(lon-90)*Math.PI/180;
    const x=CX+r*Math.cos(ang),y=CY+r*Math.sin(ang),col=OCOL.ketu;
    const grd=ctx.createRadialGradient(x,y,0,x,y,18);grd.addColorStop(0,col+'aa');grd.addColorStop(1,'transparent');
    ctx.fillStyle=grd;ctx.beginPath();ctx.arc(x,y,18,0,Math.PI*2);ctx.fill();
    ctx.fillStyle=col;ctx.beginPath();ctx.arc(x,y,6,0,Math.PI*2);ctx.fill();
    const lx=CX+(r+17)*Math.cos(ang),ly=CY+(r+17)*Math.sin(ang);
    ctx.fillStyle=col;ctx.font='bold 11px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('Ke',lx,ly);
  }
  // Tooltip
  if(orTip){
    const tw=140,th=50,tx=Math.min(orTip.x+14,496-tw),ty=Math.max(orTip.y-th-6,2);
    ctx.fillStyle=dark?'rgba(8,14,26,.92)':'rgba(245,240,230,.96)';ctx.strokeStyle='rgba(200,168,75,.5)';ctx.lineWidth=1;
    ctx.beginPath();ctx.roundRect(tx,ty,tw,th,8);ctx.fill();ctx.stroke();
    ctx.fillStyle=dark?'#f0e8d4':'#1a1208';ctx.font='bold 12px DM Sans,sans-serif';ctx.textAlign='left';ctx.textBaseline='top';ctx.fillText(orTip.name,tx+8,ty+7);
    ctx.fillStyle='rgba(200,168,75,.9)';ctx.font='11px DM Mono,monospace';ctx.fillText(orTip.info,tx+8,ty+26);
  }
  drift+=.004;requestAnimationFrame(()=>drawOrr(orPL));
}
// Hover tooltip
const orrCvEl=cv('orrCv');
if(orrCvEl){
  orrCvEl.addEventListener('mousemove',e=>{
    const rect=orrCvEl.getBoundingClientRect(),sx=500/rect.width,sy=500/rect.height;
    const mx=(e.clientX-rect.left)*sx,my=(e.clientY-rect.top)*sy;
    orTip=null;
    const all=[...Object.keys(ORB),'ketu'];
    for(const pid of all){
      const src=pid==='ketu'?PL.rahu:PL[pid];if(!src)continue;
      const r=ORB[pid==='ketu'?'rahu':pid],lon=src.lon+(pid==='ketu'?180:0);
      const ang=(lon-90)*Math.PI/180,x=250+r*Math.cos(ang),y=250+r*Math.sin(ang);
      if(Math.hypot(mx-x,my-y)<16){const d=pid==='ketu'?(PL.ketu||src):src;orTip={x,y,name:pid[0].toUpperCase()+pid.slice(1),info:`${d.sign} · ${d.deg}`};break;}
    }
  });
  orrCvEl.addEventListener('mouseleave',()=>{orTip=null;});
}

// ── PANCHANGA DIAGRAMS ────────────────────────────────────────────────────
function tclr(){return isDark()?'#ede4d0':'#14100a';}
function dclr(){return isDark()?'rgba(255,255,255,.07)':'rgba(0,0,0,.06)';}

function drawTithi(p){
  const ctx=cx2d('cvT');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const CX=110,CY=80,ro=62,ri=46,dark=isDark();
  for(let i=0;i<30;i++){
    const a1=(i*12-90)*Math.PI/180,a2=((i+1)*12-90)*Math.PI/180;
    ctx.beginPath();ctx.moveTo(CX+(ri+1)*Math.cos(a1),CY+(ri+1)*Math.sin(a1));
    ctx.arc(CX,CY,ro,a1,a2);ctx.arc(CX,CY,ri,a2,a1,true);ctx.closePath();
    ctx.fillStyle=i<15?`rgba(200,168,75,${.05+i*.004})`:`rgba(80,130,200,${.04+(29-i)*.003})`;
    ctx.fill();
  }
  // tick marks
  for(let i=0;i<30;i++){const a=(i*12-90)*Math.PI/180;ctx.beginPath();ctx.moveTo(CX+(ri-2)*Math.cos(a),CY+(ri-2)*Math.sin(a));ctx.lineTo(CX+(ro+2)*Math.cos(a),CY+(ro+2)*Math.sin(a));ctx.strokeStyle=i===0||i===15?'rgba(200,168,75,.7)':dark?'rgba(255,255,255,.14)':'rgba(0,0,0,.1)';ctx.lineWidth=i===0||i===15?2:1;ctx.stroke();}
  // fill arc
  const ga=(-90)*Math.PI/180,ea=(p.tithi.elong-90)*Math.PI/180;
  if(p.tithi.elong>0){const rm=(ro+ri)/2,grd=ctx.createLinearGradient(CX-ro,0,CX+ro,0);grd.addColorStop(0,'rgba(200,168,75,.7)');grd.addColorStop(1,'rgba(200,168,75,.2)');ctx.beginPath();ctx.arc(CX,CY,rm,ga,ea);ctx.strokeStyle=grd;ctx.lineWidth=ro-ri-4;ctx.stroke();}
  // dot
  const da=(p.tithi.elong-90)*Math.PI/180,dm=(ro+ri)/2;
  const dx=CX+dm*Math.cos(da),dy=CY+dm*Math.sin(da);
  const dg=ctx.createRadialGradient(dx,dy,0,dx,dy,10);dg.addColorStop(0,'#c8a84b');dg.addColorStop(1,'transparent');
  ctx.fillStyle=dg;ctx.beginPath();ctx.arc(dx,dy,10,0,Math.PI*2);ctx.fill();
  ctx.fillStyle='#c8a84b';ctx.beginPath();ctx.arc(dx,dy,5,0,Math.PI*2);ctx.fill();
  // center text
  ctx.fillStyle=tclr();ctx.font='bold 15px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(Math.round(p.tithi.elong)+'°',CX,CY-7);
  ctx.font='11px DM Sans,sans-serif';ctx.fillStyle=p.tithi.elong<=180?'rgba(200,168,75,.8)':'rgba(80,130,200,.8)';ctx.fillText(p.tithi.elong<=180?'Shukla':'Krishna',CX,CY+9);
}

function drawVara(p){
  const ctx=cx2d('cvV');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const CX=110,CY=70,ro=60,ri=30,dark=isDark();
  const SYM=['☀','☽','♂','☿','♃','♀','♄'],COLS=['#d4921e','#5ab8e8','#e03020','#28b870','#c89020','#c060a8','#7868b8'];
  const NAMES=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  for(let i=0;i<7;i++){
    const a1=(i*360/7-90)*Math.PI/180,a2=((i+1)*360/7-90)*Math.PI/180,am=((i+.5)*360/7-90)*Math.PI/180;
    const act=i===p.vara.idx;
    ctx.beginPath();ctx.moveTo(CX+ri*Math.cos(a1),CY+ri*Math.sin(a1));ctx.arc(CX,CY,ro,a1,a2);ctx.arc(CX,CY,ri,a2,a1,true);ctx.closePath();
    ctx.fillStyle=act?rgb(COLS[i],.38):dclr();ctx.fill();
    ctx.strokeStyle=act?COLS[i]:dark?'rgba(255,255,255,.09)':'rgba(0,0,0,.08)';ctx.lineWidth=act?2:.5;ctx.stroke();
    const rm=(ro+ri)/2,tx=CX+rm*Math.cos(am),ty=CY+rm*Math.sin(am);
    ctx.fillStyle=act?COLS[i]:dark?'rgba(255,255,255,.35)':'rgba(0,0,0,.3)';ctx.font=(act?'bold ':'')+' 15px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(SYM[i],tx,ty);
  }
  ctx.fillStyle=dark?'rgba(255,255,255,.06)':'rgba(0,0,0,.04)';ctx.beginPath();ctx.arc(CX,CY,ri-2,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=COLS[p.vara.idx];ctx.font='bold 12px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(NAMES[p.vara.idx],CX,CY);
}

function drawNak(p){
  const ctx=cx2d('cvN');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const CX=110,CY=72,ro=62,ri=44,dark=isDark();
  for(let i=0;i<27;i++){
    const a1=(i*360/27-90)*Math.PI/180,a2=((i+1)*360/27-90)*Math.PI/180;
    const act=i===p.nakshatra.idx,near=Math.abs(i-p.nakshatra.idx)<=1||Math.abs(i-p.nakshatra.idx)>=26;
    ctx.beginPath();ctx.moveTo(CX+ri*Math.cos(a1),CY+ri*Math.sin(a1));ctx.arc(CX,CY,ro,a1,a2);ctx.arc(CX,CY,ri,a2,a1,true);ctx.closePath();
    ctx.fillStyle=act?'rgba(74,144,196,.45)':near?'rgba(74,144,196,.12)':dclr();ctx.fill();
    ctx.strokeStyle=act?'rgba(120,184,228,.8)':dark?'rgba(255,255,255,.07)':'rgba(0,0,0,.08)';ctx.lineWidth=act?1.5:.5;ctx.stroke();
  }
  const pa=(p.nakshatra.idx*360/27-90)*Math.PI/180,ps=(360/27)*Math.PI/180,fe=pa+ps*(p.nakshatra.prog/100);
  const rm=(ro+ri)/2;ctx.beginPath();ctx.arc(CX,CY,rm,pa,fe);ctx.strokeStyle='rgba(74,144,196,.85)';ctx.lineWidth=ro-ri-4;ctx.stroke();
  const dang=pa+ps*(p.nakshatra.prog/100),dx=CX+rm*Math.cos(dang),dy=CY+rm*Math.sin(dang);
  const dg=ctx.createRadialGradient(dx,dy,0,dx,dy,9);dg.addColorStop(0,'#78b8e0');dg.addColorStop(1,'transparent');ctx.fillStyle=dg;ctx.beginPath();ctx.arc(dx,dy,9,0,Math.PI*2);ctx.fill();ctx.fillStyle='#78b8e0';ctx.beginPath();ctx.arc(dx,dy,4,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=tclr();ctx.font='bold 14px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText((p.nakshatra.idx+1)+'/27',CX,CY-7);
  ctx.font='11px DM Sans,sans-serif';ctx.fillStyle='rgba(74,144,196,.8)';ctx.fillText('Pd '+p.nakshatra.pada,CX,CY+8);
}

function drawYoga(p){
  const ctx=cx2d('cvY');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const CX=110,CY=105,r=68,dark=isDark();
  const sa=Math.PI,sweep=Math.PI;
  for(let i=0;i<27;i++){
    const a1=sa+(i/27)*sweep,a2=sa+((i+1)/27)*sweep;
    const isMV=(i===0||i===16||i===26),isAsh=(i===5||i===8||i===9||i===12||i===13||i===14||i===17);
    ctx.beginPath();ctx.arc(CX,CY,r,a1,a2);ctx.strokeStyle=isMV?'rgba(216,56,32,.5)':isAsh?'rgba(232,120,40,.4)':'rgba(40,184,112,.3)';ctx.lineWidth=14;ctx.stroke();
  }
  const yi=p.yoga.idx,yp=p.yoga.prog;
  const ya1=sa+(yi/27)*sweep,ya2=sa+((yi+1)/27)*sweep;
  const ycol=p.yoga.cls==='Mahavisha'?'#d83820':p.yoga.cls==='Ashubha'?'#e87828':'#28b870';
  ctx.beginPath();ctx.arc(CX,CY,r,ya1,ya2);ctx.strokeStyle=ycol;ctx.lineWidth=14;ctx.stroke();
  const na=sa+((yi+yp/100)/27)*sweep,nx=CX+r*Math.cos(na),ny=CY+r*Math.sin(na);
  ctx.beginPath();ctx.moveTo(CX,CY);ctx.lineTo(nx,ny);ctx.strokeStyle=ycol;ctx.lineWidth=2.5;ctx.stroke();
  const dg=ctx.createRadialGradient(nx,ny,0,nx,ny,10);dg.addColorStop(0,ycol);dg.addColorStop(1,'transparent');
  ctx.fillStyle=dg;ctx.beginPath();ctx.arc(nx,ny,10,0,Math.PI*2);ctx.fill();ctx.fillStyle=ycol;ctx.beginPath();ctx.arc(nx,ny,5,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=tclr();ctx.font='bold 14px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText((yi+1)+'/27',CX,CY-22);
  ctx.font='11px DM Sans,sans-serif';ctx.fillStyle=ycol;ctx.fillText(p.yoga.cls==='Subha'?'Auspicious':p.yoga.cls==='Mahavisha'?'Inauspicious':p.yoga.cls,CX,CY-8);
}

function drawKarana(p){
  const ctx=cx2d('cvK');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const CX=110,CY=82,r=58,dark=isDark();
  const sa=(210)*Math.PI/180,sweep=(300)*Math.PI/180;
  ctx.beginPath();ctx.arc(CX,CY,r,sa,sa+sweep);ctx.strokeStyle=dclr();ctx.lineWidth=14;ctx.stroke();
  const fa=sa+sweep*(p.karana.prog/100);
  ctx.beginPath();ctx.arc(CX,CY,r,sa,fa);ctx.strokeStyle='#e87828';ctx.lineWidth=14;ctx.stroke();
  for(let i=0;i<=60;i++){if(i%10===0){const a=sa+(sweep*i/60),x1=CX+(r-9)*Math.cos(a),y1=CY+(r-9)*Math.sin(a),x2=CX+(r+9)*Math.cos(a),y2=CY+(r+9)*Math.sin(a);ctx.beginPath();ctx.moveTo(x1,y1);ctx.lineTo(x2,y2);ctx.strokeStyle='rgba(200,168,75,.4)';ctx.lineWidth=1.5;ctx.stroke();}}
  const ta=sa+sweep*(p.karana.prog/100),dx=CX+r*Math.cos(ta),dy=CY+r*Math.sin(ta);
  const dg=ctx.createRadialGradient(dx,dy,0,dx,dy,12);dg.addColorStop(0,'#e87828');dg.addColorStop(1,'transparent');ctx.fillStyle=dg;ctx.beginPath();ctx.arc(dx,dy,12,0,Math.PI*2);ctx.fill();ctx.fillStyle='#e87828';ctx.beginPath();ctx.arc(dx,dy,5,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=tclr();ctx.font='bold 15px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(p.karana.slot+'/60',CX,CY-6);
  ctx.font='11px DM Sans,sans-serif';ctx.fillStyle='rgba(200,168,75,.8)';ctx.fillText('Karana',CX,CY+9);
}

function drawSun(p){
  const ctx=cx2d('cvSun');if(!ctx)return;
  ctx.clearRect(0,0,220,130);
  const dark=isDark(),W=220,H=130,pad=20,CX=W/2,CY=H-28,r=62;
  ctx.beginPath();ctx.moveTo(pad,CY);ctx.lineTo(W-pad,CY);ctx.strokeStyle=dark?'rgba(255,255,255,.15)':'rgba(0,0,0,.15)';ctx.lineWidth=1;ctx.stroke();
  const grd=ctx.createLinearGradient(pad,0,W-pad,0);grd.addColorStop(0,'rgba(232,120,40,.3)');grd.addColorStop(.5,'rgba(255,210,40,.8)');grd.addColorStop(1,'rgba(232,120,40,.3)');
  ctx.beginPath();ctx.arc(CX,CY,r,Math.PI,0);ctx.strokeStyle=grd;ctx.lineWidth=3;ctx.stroke();
  const toH=t=>{if(!t||t==='—')return null;const[h,m]=t.split(':').map(Number);return h+m/60;};
  const rH=toH(p.sunrise),sH=toH(p.sunset);
  const now=new Date(),nH=now.getHours()+now.getMinutes()/60;
  const prog=rH&&sH?Math.max(0,Math.min(1,(nH-rH)/(sH-rH))):.5;
  const sa=Math.PI+prog*Math.PI,sx=CX+r*Math.cos(sa),sy=CY+r*Math.sin(sa);
  const sg=ctx.createRadialGradient(sx,sy,0,sx,sy,18);sg.addColorStop(0,'rgba(255,220,50,.9)');sg.addColorStop(1,'transparent');ctx.fillStyle=sg;ctx.beginPath();ctx.arc(sx,sy,18,0,Math.PI*2);ctx.fill();
  ctx.fillStyle='#ffd830';ctx.beginPath();ctx.arc(sx,sy,7,0,Math.PI*2);ctx.fill();
  ctx.fillStyle=dark?'rgba(200,168,75,.75)':'rgba(120,90,8,.8)';ctx.font='bold 11px DM Mono,monospace';ctx.textAlign='center';ctx.textBaseline='top';
  ctx.fillText(p.sunrise||'—',pad+12,CY+5);ctx.fillText(p.sunset||'—',W-pad-12,CY+5);
  const gg=ctx.createLinearGradient(0,CY,0,H);gg.addColorStop(0,dark?'rgba(40,100,200,.18)':'rgba(100,150,220,.22)');gg.addColorStop(1,'transparent');
  ctx.fillStyle=gg;ctx.fillRect(pad,CY,W-2*pad,H-CY);
}

function redrawAll(){
  const p=window.__pd||PD;
  drawTithi(p);drawVara(p);drawNak(p);drawYoga(p);drawKarana(p);drawSun(p);
  drawNorthChart(ASI,PL);drawSouthChart(ASI,PL);
  drawShadbala();drawDashaWheel();
}
window.__pd=PD;

// ── NORTH INDIAN CHART ────────────────────────────────────────────────────
// 4×4 grid: houses around border, center 2×2 empty
// House positions: H1=(0,1),H2=(0,2),H3=(0,3),H4=(1,3),H5=(2,3),H6=(3,3),H7=(3,2),H8=(3,1),H9=(3,0),H10=(2,0),H11=(1,0),H12=(0,0)
const NI_CELLS=[[0,0,12],[0,1,1],[0,2,2],[0,3,3],[1,3,4],[2,3,5],[3,3,6],[3,2,7],[3,1,8],[3,0,9],[2,0,10],[1,0,11]];
const VSIGNS=['Me','Vr','Mi','Ka','Si','Kn','Tu','Vr2','Dh','Ma','Ku','Me2'];
const VSIGN_F=['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya','Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena'];
const VSIGN_S=['Me','Vr','Mi','Ka','Si','Kn','Tu','Vr','Dh','Mk','Ku','Me'];

function drawNorthChart(asi,pl){
  const ctx=cx2d('niCv');if(!ctx)return;
  const W=320,cell=W/4,dark=isDark();
  ctx.clearRect(0,0,W,W);
  // background
  ctx.fillStyle=dark?'#0c1220':'#f0ead8';ctx.fillRect(0,0,W,W);
  // build planets-per-house
  const byH={};
  for(const[pid,p]of Object.entries(pl)){const h=((p.signIdx-asi+12)%12)+1;if(!byH[h])byH[h]=[];byH[h].push({pid,abbr:p.abbr,col:p.color,retro:p.retro});}
  // draw cells
  NI_CELLS.forEach(([row,col,hnum])=>{
    const x=col*cell,y=row*cell;
    const isLagna=hnum===1;
    ctx.fillStyle=isLagna?(dark?'rgba(200,168,75,.1)':'rgba(200,168,75,.15)'):dark?'rgba(255,255,255,.025)':'rgba(255,255,255,.6)';
    ctx.fillRect(x+1,y+1,cell-2,cell-2);
    ctx.strokeStyle=isLagna?(dark?'rgba(200,168,75,.6)':'rgba(160,120,10,.7)'):dark?'rgba(255,255,255,.12)':'rgba(0,0,0,.12)';
    ctx.lineWidth=isLagna?2:1;ctx.strokeRect(x+1,y+1,cell-2,cell-2);
    // house number
    ctx.font=`bold 10px DM Mono,monospace`;ctx.fillStyle=dark?'rgba(255,255,255,.25)':'rgba(0,0,0,.2)';ctx.textAlign='left';ctx.textBaseline='top';ctx.fillText(hnum,x+5,y+5);
    // sign
    const signIdx=(asi+hnum-1)%12;
    ctx.font=`10px DM Sans,sans-serif`;ctx.fillStyle=dark?'rgba(200,168,75,.55)':'rgba(120,90,8,.65)';ctx.textAlign='right';ctx.textBaseline='top';ctx.fillText(VSIGN_S[signIdx],x+cell-5,y+5);
    // planets
    const planets=byH[hnum]||[];
    const cx2=x+cell/2,cy2=y+cell/2;
    planets.forEach((p,i)=>{
      const nx=planets.length===1?cx2:cx2+(i-planets.length/2+.5)*18;
      const ny=cy2+(planets.length>3?Math.floor(i/3)*14:0);
      ctx.font=`bold 12px DM Sans,sans-serif`;ctx.fillStyle=p.col;ctx.textAlign='center';ctx.textBaseline='middle';
      ctx.fillText(p.abbr+(p.retro?'℞':''),Math.min(Math.max(nx,x+12),x+cell-12),Math.min(Math.max(ny,y+18),y+cell-10));
    });
    if(isLagna){ctx.font='bold 10px DM Sans,sans-serif';ctx.fillStyle=dark?'rgba(200,168,75,.7)':'rgba(160,120,10,.7)';ctx.textAlign='center';ctx.textBaseline='bottom';ctx.fillText('As',x+cell/2,y+cell-4);}
  });
  // center decoration
  ctx.fillStyle=dark?'rgba(200,168,75,.05)':'rgba(200,168,75,.08)';
  ctx.fillRect(cell+1,cell+1,2*cell-2,2*cell-2);
  ctx.strokeStyle=dark?'rgba(200,168,75,.12)':'rgba(160,120,10,.18)';ctx.lineWidth=1;
  ctx.strokeRect(cell+1,cell+1,2*cell-2,2*cell-2);
  ctx.font='bold 13px Playfair Display,serif';ctx.fillStyle=dark?'rgba(200,168,75,.3)':'rgba(160,120,10,.3)';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('D1',W/2,W/2-8);
  ctx.font='10px DM Sans,sans-serif';ctx.fillStyle=dark?'rgba(255,255,255,.2)':'rgba(0,0,0,.2)';ctx.fillText('Rashi',W/2,W/2+8);
  // border
  ctx.strokeStyle=dark?'rgba(200,168,75,.25)':'rgba(160,120,10,.3)';ctx.lineWidth=1.5;ctx.strokeRect(1,1,W-2,W-2);
}

// ── SOUTH INDIAN CHART ────────────────────────────────────────────────────
// Fixed layout: Pi(0,0),Ar(0,1),Ta(0,2),Ge(0,3),Ca(1,3),Le(2,3),Vi(3,3),Li(3,2),Sc(3,1),Sa(3,0),Cp(2,0),Aq(1,0)
const SI_CELLS=[[0,0,11],[0,1,0],[0,2,1],[0,3,2],[1,3,3],[2,3,4],[3,3,5],[3,2,6],[3,1,7],[3,0,8],[2,0,9],[1,0,10]];
// si_cells: [row,col,signIdx(0=Aries)]

function drawSouthChart(asi,pl){
  const ctx=cx2d('siCv');if(!ctx)return;
  const W=320,cell=W/4,dark=isDark();
  ctx.clearRect(0,0,W,W);
  ctx.fillStyle=dark?'#0c1220':'#f0ead8';ctx.fillRect(0,0,W,W);
  // planets-per-sign
  const byS={};
  for(const[pid,p]of Object.entries(pl)){const si=p.signIdx;if(!byS[si])byS[si]=[];byS[si].push({pid,abbr:p.abbr,col:p.color,retro:p.retro});}
  SI_CELLS.forEach(([row,col,signIdx])=>{
    const x=col*cell,y=row*cell,isLagna=signIdx===asi;
    ctx.fillStyle=isLagna?(dark?'rgba(200,168,75,.1)':'rgba(200,168,75,.15)'):dark?'rgba(255,255,255,.025)':'rgba(255,255,255,.6)';
    ctx.fillRect(x+1,y+1,cell-2,cell-2);
    ctx.strokeStyle=isLagna?(dark?'rgba(200,168,75,.65)':'rgba(160,120,10,.7)'):dark?'rgba(255,255,255,.12)':'rgba(0,0,0,.12)';
    ctx.lineWidth=isLagna?2.5:1;ctx.strokeRect(x+1,y+1,cell-2,cell-2);
    ctx.font='bold 10px DM Sans,sans-serif';ctx.fillStyle=dark?'rgba(200,168,75,.55)':'rgba(120,90,8,.65)';ctx.textAlign='center';ctx.textBaseline='top';ctx.fillText(VSIGN_S[signIdx],x+cell/2,y+5);
    const planets=byS[signIdx]||[];
    planets.forEach((p,i)=>{
      const nx=x+cell/2+(i-planets.length/2+.5)*18,ny=y+cell/2+6;
      ctx.font=`bold 12px DM Sans,sans-serif`;ctx.fillStyle=p.col;ctx.textAlign='center';ctx.textBaseline='middle';
      ctx.fillText(p.abbr+(p.retro?'℞':''),Math.min(Math.max(nx,x+12),x+cell-12),Math.min(Math.max(ny,y+20),y+cell-8));
    });
  });
  // center
  ctx.fillStyle=dark?'rgba(200,168,75,.05)':'rgba(200,168,75,.08)';ctx.fillRect(cell+1,cell+1,2*cell-2,2*cell-2);
  ctx.strokeStyle=dark?'rgba(200,168,75,.12)':'rgba(160,120,10,.18)';ctx.lineWidth=1;ctx.strokeRect(cell+1,cell+1,2*cell-2,2*cell-2);
  ctx.font='bold 13px Playfair Display,serif';ctx.fillStyle=dark?'rgba(200,168,75,.3)':'rgba(160,120,10,.3)';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('D1',W/2,W/2-8);
  ctx.font='10px DM Sans,sans-serif';ctx.fillStyle=dark?'rgba(255,255,255,.2)':'rgba(0,0,0,.2)';ctx.fillText('Rashi',W/2,W/2+8);
  ctx.strokeStyle=dark?'rgba(200,168,75,.25)':'rgba(160,120,10,.3)';ctx.lineWidth=1.5;ctx.strokeRect(1,1,W-2,W-2);
}

// ── SHADBALA ──────────────────────────────────────────────────────────────
const SHAD=[
  {pid:'sun',    sym:'☀',nm:'Sun (Surya)',   col:'#d4921e',nb:60},
  {pid:'moon',   sym:'☽',nm:'Moon (Chandra)',col:'#5ab8e8',nb:51.43},
  {pid:'venus',  sym:'♀',nm:'Venus (Shukra)',col:'#c060a8',nb:42.86},
  {pid:'jupiter',sym:'♃',nm:'Jupiter (Guru)',col:'#c89020',nb:34.29},
  {pid:'mercury',sym:'☿',nm:'Mercury (Budha)',col:'#28b870',nb:25.71},
  {pid:'mars',   sym:'♂',nm:'Mars (Mangala)',col:'#e03020',nb:17.14},
  {pid:'saturn', sym:'♄',nm:'Saturn (Shani)',col:'#7868b8',nb:8.57},
];
function drawShadbala(){
  const wrap=document.getElementById('shadRows');if(!wrap)return;
  wrap.innerHTML='';
  const dark=isDark();
  SHAD.forEach(s=>{
    const pct=Math.round(s.nb/60*100);
    wrap.innerHTML+=`<div class="shad-row">
      <div class="shad-sym" style="background:${s.col}22;color:${s.col}">${s.sym}</div>
      <div class="shad-label" style="color:${dark?'#ede4d0':'#14100a'}">${s.nm.split(' ')[0]}</div>
      <div class="shad-bar-bg"><div class="shad-bar-fill" style="width:${pct}%;background:linear-gradient(90deg,${s.col},${s.col}88)"></div></div>
      <div class="shad-val" style="color:${dark?'#a09070':'#5a4828'}">${s.nb}</div>
    </div>`;
  });
  // Also draw sign-position canvas
  const ctx=cx2d('shadCv');if(!ctx)return;
  ctx.clearRect(0,0,320,300);
  ctx.fillStyle=dark?'#0c1220':'#f0ead8';ctx.fillRect(0,0,320,300);
  const CX=160,CY=155,rOuter=120,rInner=60;
  // 12 sign arcs
  for(let i=0;i<12;i++){
    const a1=(i*30-90)*Math.PI/180,a2=((i+1)*30-90)*Math.PI/180,am=((i+.5)*30-90)*Math.PI/180,rm=(rOuter+rInner)/2;
    ctx.beginPath();ctx.moveTo(CX+rInner*Math.cos(a1),CY+rInner*Math.sin(a1));
    ctx.arc(CX,CY,rOuter,a1,a2);ctx.arc(CX,CY,rInner,a2,a1,true);ctx.closePath();
    ctx.fillStyle=`hsla(${i*30},${dark?35:45}%,${dark?14:80}%,.7)`;ctx.fill();
    ctx.strokeStyle=dark?'rgba(255,255,255,.08)':'rgba(0,0,0,.1)';ctx.lineWidth=.5;ctx.stroke();
    // sign label
    ctx.font='bold 10px DM Sans,sans-serif';ctx.fillStyle=dark?`hsla(${i*30},60%,72%,.7)`:`hsla(${i*30},55%,30%,.8)`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(VSIGN_S[i],CX+rm*Math.cos(am),CY+rm*Math.sin(am));
  }
  // center
  ctx.fillStyle=dark?'rgba(7,12,22,.9)':'rgba(240,235,225,.9)';ctx.beginPath();ctx.arc(CX,CY,rInner-2,0,Math.PI*2);ctx.fill();
  ctx.font='bold 12px Playfair Display,serif';ctx.fillStyle=dark?'rgba(200,168,75,.5)':'rgba(120,90,8,.5)';ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText('Naisargika',CX,CY-8);ctx.font='10px DM Sans,sans-serif';ctx.fillText('Bala',CX,CY+8);
  // planet dots
  SHAD.forEach(s=>{
    const p=PL[s.pid];if(!p)return;
    const ang=(p.lon-90)*Math.PI/180,r2=rInner+5+(rOuter-rInner-10)*(s.nb/60);
    const px=CX+r2*Math.cos(ang),py=CY+r2*Math.sin(ang);
    const dg=ctx.createRadialGradient(px,py,0,px,py,12);dg.addColorStop(0,s.col);dg.addColorStop(1,'transparent');
    ctx.fillStyle=dg;ctx.beginPath();ctx.arc(px,py,12,0,Math.PI*2);ctx.fill();
    ctx.fillStyle=s.col;ctx.beginPath();ctx.arc(px,py,5,0,Math.PI*2);ctx.fill();
    ctx.font='bold 11px DM Sans,sans-serif';ctx.fillStyle=s.col;ctx.textAlign='center';ctx.textBaseline='middle';
    const la=ang+(r2<100?-.3:.3);ctx.fillText(s.sym,CX+(r2+18)*Math.cos(la),CY+(r2+18)*Math.sin(la));
  });
}

// ── DASHA WHEEL ───────────────────────────────────────────────────────────
const DLORDS=[{n:'Ketu',y:7,c:'#a03818',s:'☋'},{n:'Venus',y:20,c:'#b84ca0',s:'♀'},{n:'Sun',y:6,c:'#d4921e',s:'☀'},{n:'Moon',y:10,c:'#5ab8e8',s:'☽'},{n:'Mars',y:7,c:'#e03020',s:'♂'},{n:'Rahu',y:18,c:'#208048',s:'☊'},{n:'Jupiter',y:16,c:'#c89020',s:'♃'},{n:'Saturn',y:19,c:'#7868b8',s:'♄'},{n:'Mercury',y:17,c:'#28b870',s:'☿'}];
function drawDashaWheel(){
  const svg=cv('dashaSvg'),leg=cv('dashaLeg');if(!svg||!leg)return;
  const ns='http://www.w3.org/2000/svg';svg.innerHTML='';leg.innerHTML='';
  const CX=130,CY=130,ro=118,ri=52,dark=isDark();let angle=-90;
  DLORDS.forEach(l=>{
    const sw=l.y/120*360,a1=angle*Math.PI/180,a2=(angle+sw)*Math.PI/180,am=(angle+sw/2)*Math.PI/180,lg=sw>180?1:0;
    const x1=CX+ro*Math.cos(a1),y1=CY+ro*Math.sin(a1),x2=CX+ro*Math.cos(a2),y2=CY+ro*Math.sin(a2);
    const x3=CX+ri*Math.cos(a2),y3=CY+ri*Math.sin(a2),x4=CX+ri*Math.cos(a1),y4=CY+ri*Math.sin(a1);
    const p=document.createElementNS(ns,'path');
    p.setAttribute('d',`M${x1},${y1} A${ro},${ro} 0 ${lg},1 ${x2},${y2} L${x3},${y3} A${ri},${ri} 0 ${lg},0 ${x4},${y4} Z`);
    p.setAttribute('fill',l.c+'2a');p.setAttribute('stroke',l.c+'70');p.setAttribute('stroke-width','1.2');svg.appendChild(p);
    if(sw>16){const rm=(ro+ri)/2,tx=document.createElementNS(ns,'text');tx.setAttribute('x',CX+rm*Math.cos(am));tx.setAttribute('y',CY+rm*Math.sin(am));tx.setAttribute('text-anchor','middle');tx.setAttribute('dominant-baseline','central');tx.setAttribute('fill',l.c);tx.setAttribute('font-size',sw>32?'15':'11');tx.textContent=l.s;svg.appendChild(tx);}
    const pct=l.y/20*100;
    leg.innerHTML+=`<div class="dl-row"><div class="dl-dot" style="background:${l.c}"></div><div class="dl-name" style="color:${l.c}">${l.s} ${l.n}</div><div class="dl-yr" style="color:${dark?'#a09070':'#5a4828'}">${l.y} yrs</div><div class="dl-bg"><div class="dl-fill" style="width:${pct}%;background:${l.c}60"></div></div></div>`;
    angle+=sw;
  });
  // center
  const cc=document.createElementNS(ns,'circle');cc.setAttribute('cx',CX);cc.setAttribute('cy',CY);cc.setAttribute('r',ri-2);cc.setAttribute('fill',dark?'#07090f':'#f5f0e6');cc.setAttribute('stroke','rgba(200,168,75,.2)');cc.setAttribute('stroke-width','1');svg.appendChild(cc);
  const t1=document.createElementNS(ns,'text');t1.setAttribute('x',CX);t1.setAttribute('y',CY-7);t1.setAttribute('text-anchor','middle');t1.setAttribute('fill','rgba(200,168,75,.75)');t1.setAttribute('font-size','14');t1.setAttribute('font-family','Playfair Display,serif');t1.textContent='120';svg.appendChild(t1);
  const t2=document.createElementNS(ns,'text');t2.setAttribute('x',CX);t2.setAttribute('y',CY+9);t2.setAttribute('text-anchor','middle');t2.setAttribute('fill','rgba(200,168,75,.4)');t2.setAttribute('font-size','9');t2.textContent='years';svg.appendChild(t2);
}

// ── DAY NAVIGATION ────────────────────────────────────────────────────────
let _curDate=TODAY;
async function shiftDay(dir){const d=new Date(_curDate+'T00:00:00');d.setDate(d.getDate()+dir);await loadDay(d.toISOString().slice(0,10));}
async function gotoToday(){await loadDay(TODAY);}
async function loadDay(dt){
  _curDate=dt;cv('btnPrev').disabled=true;cv('btnNext').disabled=true;
  try{
    const r=await fetch('/panchanga-data?date='+dt),data=await r.json();
    window.__pd=data;
    cv('ddDate').childNodes[0].textContent=data.dateDisplay;
    const sub=cv('ddSub');if(sub)sub.textContent=data.dayName+' · Ayanamsa '+data.ayan+'°';
    const hi=document.documentElement.dataset.lang==='hi';
    const s=(id,v)=>{const e=cv(id);if(e)e.textContent=v;};
    s('pvTN',hi?tr(data.tithi.name,'tithi'):data.tithi.name);
    s('pvTS',(hi?tr(data.tithi.paksha,'paksha'):data.tithi.paksha)+' Paksha · '+data.tithi.num+'/15 · '+data.tithi.elong+'°');
    s('pvTL','Lord: '+data.tithi.lord);
    s('pvVN',hi?tr(data.vara.name,'varaName'):data.vara.name);
    s('pvVS',(hi?tr(data.vara.en,'vara'):data.vara.en)+' · Lord: '+data.vara.lord);
    s('pvVNat',data.vara.nature);
    s('pvNN',hi?tr(data.nakshatra.name,'nak'):data.nakshatra.name);
    s('pvNS','Pada '+data.nakshatra.pada+' · Lord: '+data.nakshatra.lord);
    s('pvND',data.nakshatra.deity);
    s('pvYN',hi?tr(data.yoga.name,'yoga'):data.yoga.name);
    s('pvYS',data.yoga.nature+' · Lord: '+data.yoga.lord);
    s('pvYC',data.yoga.cls);
    s('pvKN',data.karana.name);s('pvKS',data.karana.type+' · Slot '+data.karana.slot+'/60');
    s('pvSunT',data.sunrise+' — '+data.sunset);s('pvSunR',data.sunrise);s('pvSunS',data.sunset);
    const grid=cv('paGrid');grid.style.opacity='.4';grid.style.transition='opacity .2s';
    setTimeout(()=>{redrawAll();grid.style.opacity='1';},240);
  }catch(e){console.error(e);}
  finally{cv('btnPrev').disabled=false;cv('btnNext').disabled=false;}
}

// ── GSAP ANIMATIONS ───────────────────────────────────────────────────────
window.addEventListener('load',()=>{
  // start everything on load
  drawOrr(PL);
  redrawAll();
  if(typeof gsap==='undefined')return;
  gsap.registerPlugin(ScrollTrigger);
  gsap.from('#heroT',{opacity:0,y:48,duration:1.1,ease:'power3.out'});
  gsap.from('.hero-badge',{opacity:0,y:20,duration:.8,delay:.15});
  gsap.from('.hero-sub',{opacity:0,y:28,duration:.9,delay:.28});
  gsap.from('.hero-btns',{opacity:0,y:18,duration:.75,delay:.45});
  gsap.from('.orr-wrap',{opacity:0,scale:.85,duration:1.2,delay:.2,ease:'power2.out'});
  gsap.from('nav',{opacity:0,y:-16,duration:.6});
  document.querySelectorAll('.rv').forEach(el=>{
    ScrollTrigger.create({trigger:el,start:'top 87%',onEnter:()=>{
      el.classList.add('in');
      const kids=el.querySelectorAll('.pa-card,.plc,.fest-card,.dl-row,.shad-row,.cho-bar-row');
      if(kids.length)gsap.from(kids,{opacity:0,y:18,stagger:.05,duration:.55,ease:'power2.out',clearProps:'all'});
    },once:true});
  });
});
</script>

<script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
</body>
</html>
