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
  --bg:#f6f1e6;--bg2:#efe8d9;--bg3:#e7dec9;
  --card:#fffdf8;--card-b:rgba(120,92,30,.18);--card-h:#fffefb;
  --gold:#7a5608;--gold-l:#a87810;--gold-d:#4a3400;
  --sky:#175888;--sky-l:#2274b0;--sky-d:#0c3458;
  --text:#1a130a;--text-m:#5c4824;--text-d:#8c7448;
  --accent:#b04400;--green:#187a48;--red:#bc1c08;--purple:#42239a;
  --nav-bg:rgba(246,241,230,.94);--shadow:0 6px 24px rgba(90,66,20,.13);
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
.nav-cta{padding:8px 22px;border-radius:40px;background:linear-gradient(135deg,#8a6208,#4a3000);color:#fff;font-size:.85rem;font-weight:700;text-decoration:none;transition:transform .15s,box-shadow .2s;white-space:nowrap;border:none;cursor:pointer;box-shadow:0 3px 12px rgba(100,70,0,.4)}
.nav-cta:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(100,70,0,.55)}
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
.btn-p{background:linear-gradient(135deg,#a87808,#5a3c00);color:#fff;padding:14px 32px;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:transform .15s,box-shadow .2s;box-shadow:0 6px 22px rgba(120,80,0,.42)}
.btn-p:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(120,80,0,.6)}
.btn-p i,.btn-p span{color:#fff}
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
.charts-2col{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:stretch}
@media(max-width:820px){.charts-2col{grid-template-columns:1fr}}
.chart-box{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:24px;display:flex;flex-direction:column;box-shadow:var(--shadow)}
.chart-box-head{display:flex;align-items:center;gap:11px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--card-b)}
.chart-box-head .cbi{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.chart-title{font-family:'Playfair Display',serif;font-size:1.14rem;font-weight:700;color:var(--text)}
.chart-sub{font-size:.78rem;color:var(--text-m);margin-top:2px}
.chart-cv-wrap{display:flex;justify-content:center;align-items:center;flex:1}
.chart-cv-wrap canvas{width:100%;max-width:380px;height:auto;aspect-ratio:1/1}
.chart-legend-row{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:18px;padding-top:14px;border-top:1px solid var(--card-b)}
.clg{display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--text-m)}
.clg b{font-weight:700}
/* ── DASHA CARDS ── */
.dasha-timeline{display:flex;height:52px;border-radius:12px;overflow:hidden;margin-bottom:10px;box-shadow:0 4px 20px rgba(0,0,0,.3)}
.dt-seg{display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;cursor:default;transition:filter .2s;overflow:hidden}
.dt-seg:hover{filter:brightness(1.25);z-index:2}
.dt-lbl{font-size:.68rem;font-weight:800;color:rgba(255,255,255,.92);text-align:center;padding:0 4px;line-height:1.3;white-space:nowrap;overflow:hidden}
.dl-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px}
.dl-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:18px;transition:transform .2s,box-shadow .2s;position:relative;overflow:hidden}
.dl-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.dl-sym{font-size:2.2rem;line-height:1;margin-bottom:8px}
.dl-nm{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:2px}
.dl-hi{font-size:.82rem;color:var(--text-m);margin-bottom:6px}
.dl-yr{font-family:'DM Mono',monospace;font-size:.78rem;color:var(--text-d);margin-bottom:8px}
.dl-sig{font-size:.8rem;color:var(--text-m);line-height:1.55;margin-bottom:8px}
.dl-gem{font-size:.72rem;font-weight:600;color:var(--gold);display:flex;align-items:center;gap:4px}
.dl-nat{display:inline-block;font-size:.62rem;font-weight:800;text-transform:uppercase;padding:2px 8px;border-radius:20px;margin-top:6px;margin-left:6px}
/* ── MUHURAT EXTRAS ── */
.muh-tl-wrap{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:22px;margin-bottom:28px}
.muh-key-pills{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}
.muh-pill{border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:12px;flex:1;min-width:155px;border:1px solid transparent}
.muh-pill-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.muh-pill-lbl{font-size:.62rem;font-weight:900;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:3px}
.muh-pill-t{font-family:'DM Mono',monospace;font-size:.9rem;font-weight:700;color:var(--text)}
.muh-pill-d{font-size:.72rem;color:var(--text-m);margin-top:1px}

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
  <p class="ssub t" data-en="D1 Rashi (birth chart) · Live planetary positions · New Delhi · {{ $dateDisplay }}. Lagna sign is highlighted; planet abbreviations sit in their sign/house." data-hi="D1 राशि (जन्म कुंडली) · नई दिल्ली के लिए जीवंत ग्रह स्थितियाँ। लग्न राशि हाइलाइट है।">D1 Rashi (birth chart) · Live planetary positions · New Delhi · {{ $dateDisplay }}. Lagna sign is highlighted; planet abbreviations sit in their sign/house.</p>

  <div class="charts-2col rv">
    {{-- North Indian — diamond, LEFT --}}
    <div class="chart-box">
      <div class="chart-box-head">
        <div class="cbi" style="background:rgba(200,168,75,.14);color:var(--gold)"><i class="ph ph-diamond"></i></div>
        <div>
          <div class="chart-title t" data-en="North Indian — D1 Rashi" data-hi="उत्तर भारतीय — D1 राशि">North Indian — D1 Rashi</div>
          <div class="chart-sub t" data-en="Diamond style · House 1 fixed at top" data-hi="हीरा शैली · House 1 शीर्ष पर स्थिर">Diamond style · House 1 fixed at top</div>
        </div>
      </div>
      <div class="chart-cv-wrap"><canvas id="niCv" width="380" height="380"></canvas></div>
      <div class="chart-legend-row">
        <span class="clg"><b style="color:var(--gold)">La</b> <span class="t" data-en="Lagna (Ascendant)" data-hi="लग्न">Lagna (Ascendant)</span></span>
        <span class="clg"><b style="color:var(--text-m)">1–12</b> <span class="t" data-en="House numbers" data-hi="भाव">Houses</span></span>
        <span class="clg"><b style="color:var(--accent)">℞</b> <span class="t" data-en="Retrograde" data-hi="वक्री">Retrograde</span></span>
      </div>
    </div>

    {{-- South Indian — grid, RIGHT --}}
    <div class="chart-box">
      <div class="chart-box-head">
        <div class="cbi" style="background:rgba(72,144,196,.14);color:var(--sky-l)"><i class="ph ph-squares-four"></i></div>
        <div>
          <div class="chart-title t" data-en="South Indian — D1 Rashi" data-hi="दक्षिण भारतीय — D1 राशि">South Indian — D1 Rashi</div>
          <div class="chart-sub t" data-en="Fixed sign grid · Lagna highlighted" data-hi="स्थिर राशि ग्रिड · लग्न हाइलाइट">Fixed sign grid · Lagna highlighted</div>
        </div>
      </div>
      <div class="chart-cv-wrap"><canvas id="siCv" width="380" height="380"></canvas></div>
      <div class="chart-legend-row">
        <span class="clg"><b style="color:var(--gold)">La</b> <span class="t" data-en="Lagna sign" data-hi="लग्न राशि">Lagna sign</span></span>
        <span class="clg"><b style="color:var(--text-m)">Me–Pi</b> <span class="t" data-en="Fixed zodiac signs" data-hi="स्थिर राशियाँ">Fixed signs</span></span>
        <span class="clg"><b style="color:var(--accent)">℞</b> <span class="t" data-en="Retrograde" data-hi="वक्री">Retrograde</span></span>
      </div>
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
  {{-- Proportional timeline bar --}}
  <div class="rv">
    <div style="font-size:.84rem;color:var(--text-m);margin-bottom:12px" class="t" data-en="120-year cycle — each segment width proportional to the dasha period · hover for details" data-hi="120-वर्षीय चक्र — प्रत्येक खंड की चौड़ाई दशा अवधि के अनुपात में">120-year cycle — each segment width proportional to the dasha period · hover for details</div>
    <div class="dasha-timeline" id="dashaTimeline"></div>
    <div style="font-size:.72rem;color:var(--text-d);margin-top:6px;text-align:center">
      <span style="font-family:'DM Mono',monospace">Ke·7 → Ve·20 → Su·6 → Mo·10 → Ma·7 → Ra·18 → Ju·16 → Sa·19 → Me·17 = 120 years</span>
    </div>
  </div>

  {{-- 9 Lord cards --}}
  <div style="margin-top:36px">
    <div class="ey" style="margin-bottom:20px"><i class="ph ph-planet"></i> <span class="t" data-en="Nine Dasha Lords — Significations &amp; Characteristics" data-hi="नव दशा स्वामी — कारकत्व और विशेषताएं">Nine Dasha Lords — Significations &amp; Characteristics</span></div>
    <div class="dl-cards rv" id="dashaCards"></div>
  </div>
</section>

<div class="divider"></div>

{{-- ══ MUHURAT / CHOGHADIYA ══ --}}
<section class="sec" id="muhurat">
  <div class="ey"><i class="ph ph-clock"></i> <span class="t" data-en="Muhurat — Auspicious Timing" data-hi="मुहूर्त — शुभ समय">Muhurat — Auspicious Timing</span></div>
  <h2 class="stitle t" data-en="Today's Choghadiya &amp; Key Muhurat Times" data-hi="आज का चौघड़िया और मुख्य मुहूर्त समय">Today's Choghadiya &amp; Key Muhurat Times</h2>
  <p class="ssub t" data-en="Visual day timeline with 8 Choghadiya periods · Rahu Kaal · Abhijit Muhurat · Yam Ghantam · New Delhi" data-hi="सूर्योदय से सूर्यास्त तक आठ चौघड़िया · राहुकाल · अभिजित मुहूर्त">Visual day timeline · 8 Choghadiya periods · New Delhi · {{ $sunrise }} — {{ $sunset }}</p>

  {{-- Visual day timeline --}}
  <div class="muh-tl-wrap rv">
    <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--text);margin-bottom:4px" class="t" data-en="Day Timeline — Sunrise to Sunset" data-hi="दिन टाइमलाइन — सूर्योदय से सूर्यास्त">Day Timeline — Sunrise to Sunset</div>
    <div style="font-size:.8rem;color:var(--text-m);margin-bottom:14px">{{ $sunrise }} → {{ $sunset }} · {{ $dayName }}</div>
    <canvas id="muhCv" width="900" height="72" style="width:100%;display:block;border-radius:10px"></canvas>
    {{-- Key timing pills --}}
    <div class="muh-key-pills">
      <div class="muh-pill" style="background:rgba(216,56,32,.1);border-color:rgba(216,56,32,.28)">
        <div class="muh-pill-ic" style="background:rgba(216,56,32,.2);color:#d83820"><i class="ph ph-warning"></i></div>
        <div>
          <div class="muh-pill-lbl" style="color:#d83820" class="t" data-en="Rahu Kaal" data-hi="राहुकाल">Rahu Kaal</div>
          <div class="muh-pill-t">{{ $rahuKaal['start'] }} – {{ $rahuKaal['end'] }}</div>
          <div class="muh-pill-d t" data-en="Avoid new beginnings · inauspicious" data-hi="नए कार्य वर्जित · अशुभ">Avoid new beginnings · inauspicious</div>
        </div>
      </div>
      <div class="muh-pill" style="background:rgba(40,184,112,.1);border-color:rgba(40,184,112,.28)">
        <div class="muh-pill-ic" style="background:rgba(40,184,112,.2);color:#28b870"><i class="ph ph-star-four"></i></div>
        <div>
          <div class="muh-pill-lbl" style="color:#28b870" class="t" data-en="Abhijit Muhurat" data-hi="अभिजित मुहूर्त">Abhijit Muhurat</div>
          <div class="muh-pill-t">{{ $abhijit['start'] }} – {{ $abhijit['end'] }}</div>
          <div class="muh-pill-d t" data-en="Best window of the day · always auspicious" data-hi="दिन का सर्वश्रेष्ठ समय · सदा शुभ">Best window of the day · always auspicious</div>
        </div>
      </div>
      <div class="muh-pill" style="background:rgba(112,96,168,.1);border-color:rgba(112,96,168,.28)">
        <div class="muh-pill-ic" style="background:rgba(112,96,168,.2);color:#7060a8"><i class="ph ph-prohibit"></i></div>
        <div>
          <div class="muh-pill-lbl" style="color:#7060a8" class="t" data-en="Yam Ghantam" data-hi="यम घंटम">Yam Ghantam</div>
          <div class="muh-pill-t">{{ $yamghantam['start'] }} – {{ $yamghantam['end'] }}</div>
          <div class="muh-pill-d t" data-en="Inauspicious · avoid travel" data-hi="अशुभ · यात्रा वर्जित">Inauspicious · avoid travel</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Choghadiya bars + guide --}}
  <div class="cho-grid rv">
    <div class="cho-chart">
      <div style="font-family:'Playfair Display',serif;font-size:1.12rem;font-weight:700;color:var(--text);margin-bottom:4px" class="t" data-en="Day Choghadiya (8 Periods)" data-hi="दिन चौघड़िया (8 काल)">Day Choghadiya (8 Periods)</div>
      <div style="font-size:.8rem;color:var(--text-m);margin-bottom:14px">{{ $sunrise }} — {{ $sunset }} · {{ $dayName }}</div>
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
              <div style="font-size:.76rem;color:var(--text-m)">{{ $ch['nameHi'] }}</div>
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
          ['#6ab04c','Labh','लाभ','Mercury — Good for gain &amp; commerce'],
          ['#a08060','Chal','चल','Mercury/Venus — Neutral, good for travel'],
          ['#d83820','Rog','रोग','Mars — Avoid new starts, inauspicious'],
          ['#7060a8','Kaal','काल','Saturn — Inauspicious, avoid all activities'],
          ['#c05018','Udveg','उद्वेग','Sun — Inauspicious, avoid beginnings'],
        ] as [$col,$nm,$hi,$desc])
        <div class="cho-leg-row">
          <div class="cho-leg-dot" style="background:{{ $col }}"></div>
          <div class="cho-leg-text"><strong>{{ $nm }} ({{ $hi }})</strong> — {{ $desc }}</div>
        </div>
        @endforeach
      </div>
      <div style="margin-top:24px;padding:18px;background:var(--card-b);border-radius:12px">
        <div style="font-size:.82rem;font-weight:700;color:var(--gold);margin-bottom:6px" class="t" data-en="Full Muhurat Calculator" data-hi="पूर्ण मुहूर्त कैलकुलेटर">Full Muhurat Calculator</div>
        <div style="font-size:.85rem;color:var(--text-m);margin-bottom:14px" class="t" data-en="Vivah, Griha Pravesh, Vahana, Mundan and more — with month &amp; year scan." data-hi="विवाह, गृह प्रवेश, वाहन, मुंडन और अधिक।">Vivah, Griha Pravesh, Vahana, Mundan and more — with month &amp; year scan.</div>
        <a href="/astro" class="btn-p" style="font-size:.88rem;padding:11px 24px;display:inline-flex;gap:8px"><i class="ph ph-clock"></i><span class="t" data-en="Open Muhurat" data-hi="मुहूर्त खोलें">Open Muhurat</span></a>
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
$_b=['date'=>$date,'sunrise'=>$sunrise,'sunset'=>$sunset,'tithi'=>$tithi,'vara'=>$vara,'nakshatra'=>$nakshatra,'yoga'=>$yoga,'karana'=>$karana,'ascSignIdx'=>$ascSignIdx,'choghadiya'=>$choghadiya,'rahuKaal'=>$rahuKaal,'abhijit'=>$abhijit,'yamghantam'=>$yamghantam];
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
  drawShadbala();buildDashaSections();drawMuhuratTimeline();
}
window.__pd=PD;

const VSIGN_S=['Me','Vr','Mi','Ka','Si','Kn','Tu','Vr','Dh','Mk','Ku','Me'];

// ── SHARED CHART PALETTE (matches landing-page theme, dark + light) ───────
function chartPal(){
  const dark=isDark();
  return {
    dark,
    bg:        dark ? '#0e1626' : '#fbf6ea',
    border:    dark ? 'rgba(200,168,75,.6)'  : 'rgba(150,110,18,.6)',
    line:      dark ? 'rgba(200,168,75,.34)' : 'rgba(150,110,18,.4)',
    cellLine:  dark ? 'rgba(255,255,255,.13)' : 'rgba(120,92,30,.22)',
    cellFill:  dark ? 'rgba(255,255,255,.022)' : 'rgba(255,252,244,.7)',
    lagnaFill: dark ? 'rgba(200,168,75,.15)' : 'rgba(200,150,30,.16)',
    lagnaLine: dark ? 'rgba(200,168,75,.75)' : 'rgba(150,108,12,.8)',
    sign:      dark ? 'rgba(200,168,75,.78)' : 'rgba(120,86,12,.9)',
    centre:    dark ? 'rgba(200,168,75,.34)' : 'rgba(150,108,18,.4)',
  };
}

// ── NORTH INDIAN CHART (diamond / kite style · D1 Rashi) ──────────────────
function drawNorthChart(asi,pl){
  const ctx=cx2d('niCv');if(!ctx)return;
  const W=ctx.canvas.width,S=W,P=chartPal();
  ctx.clearRect(0,0,W,W);
  ctx.fillStyle=P.bg;ctx.fillRect(0,0,W,W);
  const TL=[0,0],TR=[S,0],BR=[S,S],BL=[0,S];
  const MT=[S/2,0],MR=[S,S/2],MB=[S/2,S],ML=[0,S/2],C=[S/2,S/2];
  const P1=[S/4,S/4],P2=[3*S/4,3*S/4],P3=[3*S/4,S/4],P4=[S/4,3*S/4];
  const H=[
    {p:[MT,P3,C,P1], a:[S/2,S/4]},
    {p:[TL,MT,P1],   a:[S/4,S*0.11]},
    {p:[TL,P1,ML],   a:[S*0.11,S/4]},
    {p:[ML,P1,C,P4], a:[S/4,S/2]},
    {p:[ML,P4,BL],   a:[S*0.11,3*S/4]},
    {p:[BL,P4,MB],   a:[S/4,S*0.89]},
    {p:[MB,P4,C,P2], a:[S/2,3*S/4]},
    {p:[MB,P2,BR],   a:[3*S/4,S*0.89]},
    {p:[BR,P2,MR],   a:[S*0.89,3*S/4]},
    {p:[MR,P2,C,P3], a:[3*S/4,S/2]},
    {p:[MR,P3,TR],   a:[S*0.89,S/4]},
    {p:[TR,P3,MT],   a:[3*S/4,S*0.11]},
  ];
  const byH={};
  for(const[pid,p]of Object.entries(pl)){const h=((p.signIdx-asi+12)%12)+1;(byH[h]=byH[h]||[]).push({abbr:p.abbr,col:p.color,retro:p.retro});}
  ctx.beginPath();H[0].p.forEach((pt,i)=>i?ctx.lineTo(pt[0],pt[1]):ctx.moveTo(pt[0],pt[1]));ctx.closePath();
  ctx.fillStyle=P.lagnaFill;ctx.fill();
  ctx.strokeStyle=P.border;ctx.lineWidth=1.8;ctx.strokeRect(1.5,1.5,S-3,S-3);
  ctx.strokeStyle=P.line;ctx.lineWidth=1;
  ctx.beginPath();ctx.moveTo(0,0);ctx.lineTo(S,S);ctx.moveTo(S,0);ctx.lineTo(0,S);ctx.stroke();
  ctx.beginPath();ctx.moveTo(MT[0],MT[1]);ctx.lineTo(MR[0],MR[1]);ctx.lineTo(MB[0],MB[1]);ctx.lineTo(ML[0],ML[1]);ctx.closePath();ctx.stroke();
  H.forEach((h,idx)=>{
    const hnum=idx+1,signIdx=(asi+hnum-1)%12,ax=h.a[0],ay=h.a[1];
    const planets=byH[hnum]||[];
    ctx.font='600 11px DM Sans,sans-serif';ctx.fillStyle=P.sign;ctx.textAlign='center';ctx.textBaseline='middle';
    ctx.fillText(VSIGN_S[signIdx],ax,ay-(planets.length?16:0));
    planets.forEach((p,i)=>{
      const perRow=2,row=Math.floor(i/perRow),colp=i%perRow;
      const inRow=Math.min(planets.length-row*perRow,perRow);
      const px=ax+(colp-(inRow-1)/2)*24,py=ay+2+row*15;
      ctx.font='bold 13px DM Sans,sans-serif';ctx.fillStyle=p.col;
      ctx.fillText(p.abbr+(p.retro?'℞':''),px,py);
    });
    if(hnum===1){ctx.font='bold 9px DM Sans,sans-serif';ctx.fillStyle=P.lagnaLine;ctx.fillText('La',ax,ay+(planets.length?Math.ceil(planets.length/2)*15+8:18));}
  });
}

// ── SOUTH INDIAN CHART (fixed sign grid · D1 Rashi) ───────────────────────
const SI_CELLS=[[0,0,11],[0,1,0],[0,2,1],[0,3,2],[1,3,3],[2,3,4],[3,3,5],[3,2,6],[3,1,7],[3,0,8],[2,0,9],[1,0,10]];
function drawSouthChart(asi,pl){
  const ctx=cx2d('siCv');if(!ctx)return;
  const W=ctx.canvas.width,cell=W/4,P=chartPal();
  ctx.clearRect(0,0,W,W);
  ctx.fillStyle=P.bg;ctx.fillRect(0,0,W,W);
  const byS={};
  for(const[pid,p]of Object.entries(pl)){const si=p.signIdx;(byS[si]=byS[si]||[]).push({abbr:p.abbr,col:p.color,retro:p.retro});}
  SI_CELLS.forEach(function(arr){
    const row=arr[0],col=arr[1],signIdx=arr[2];
    const x=col*cell,y=row*cell,isLagna=signIdx===asi;
    ctx.fillStyle=isLagna?P.lagnaFill:P.cellFill;ctx.fillRect(x,y,cell,cell);
    ctx.strokeStyle=isLagna?P.lagnaLine:P.cellLine;ctx.lineWidth=isLagna?2.5:1;
    const o=isLagna?1.5:0.5;ctx.strokeRect(x+o,y+o,cell-2*o,cell-2*o);
    ctx.font='600 11px DM Sans,sans-serif';ctx.fillStyle=P.sign;ctx.textAlign='center';ctx.textBaseline='top';
    ctx.fillText(VSIGN_S[signIdx],x+cell/2,y+6);
    const planets=byS[signIdx]||[];
    planets.forEach((p,i)=>{
      const perRow=2,r=Math.floor(i/perRow),colp=i%perRow;
      const inRow=Math.min(planets.length-r*perRow,perRow);
      const px=x+cell/2+(colp-(inRow-1)/2)*24,py=y+cell/2+4+r*15;
      ctx.font='bold 13px DM Sans,sans-serif';ctx.fillStyle=p.col;ctx.textAlign='center';ctx.textBaseline='middle';
      ctx.fillText(p.abbr+(p.retro?'℞':''),px,py);
    });
    if(isLagna){ctx.font='bold 9px DM Sans,sans-serif';ctx.fillStyle=P.lagnaLine;ctx.textAlign='center';ctx.textBaseline='bottom';ctx.fillText('La',x+cell/2,y+cell-5);}
  });
  ctx.fillStyle=P.dark?'rgba(200,168,75,.045)':'rgba(200,150,30,.06)';ctx.fillRect(cell,cell,2*cell,2*cell);
  ctx.font='bold 15px Playfair Display,serif';ctx.fillStyle=P.centre;ctx.textAlign='center';ctx.textBaseline='middle';
  ctx.fillText('D1',W/2,W/2-9);
  ctx.font='10px DM Sans,sans-serif';ctx.fillText('Rashi',W/2,W/2+9);
  ctx.strokeStyle=P.border;ctx.lineWidth=1.8;ctx.strokeRect(1.5,1.5,W-3,W-3);
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

// ── DASHA ─────────────────────────────────────────────────────────────────
const DLORDS=[{n:'Ketu',y:7,c:'#a03818',s:'☋'},{n:'Venus',y:20,c:'#b84ca0',s:'♀'},{n:'Sun',y:6,c:'#d4921e',s:'☀'},{n:'Moon',y:10,c:'#5ab8e8',s:'☽'},{n:'Mars',y:7,c:'#e03020',s:'♂'},{n:'Rahu',y:18,c:'#208048',s:'☊'},{n:'Jupiter',y:16,c:'#c89020',s:'♃'},{n:'Saturn',y:19,c:'#7868b8',s:'♄'},{n:'Mercury',y:17,c:'#28b870',s:'☿'}];
const DLORD_DETAIL=[
  {n:'Ketu',   hi:'केतु',   s:'☋',y:7, c:'#a03818',nat:'Malefic', gem:"Cat's Eye (Lehsunia)",  sig:'Spirituality · Detachment · Past karma · Mysticism · Liberation',  rules:'Co-rules Scorpio'},
  {n:'Venus',  hi:'शुक्र',  s:'♀',y:20,c:'#b84ca0',nat:'Benefic', gem:'Diamond / White Sapphire',sig:'Love · Beauty · Marriage · Arts · Luxury · Comforts · Vehicles',   rules:'Taurus & Libra'},
  {n:'Sun',    hi:'सूर्य',  s:'☀',y:6, c:'#d4921e',nat:'Malefic', gem:'Ruby (Manik)',            sig:'Soul · Authority · Father · Government · Health · Vitality',        rules:'Leo (Simha)'},
  {n:'Moon',   hi:'चंद्र',  s:'☽',y:10,c:'#5ab8e8',nat:'Benefic', gem:'Pearl / Moonstone',       sig:'Mind · Mother · Emotions · Fluids · Home · Nourishment · Travel',  rules:'Cancer (Karka)'},
  {n:'Mars',   hi:'मंगल',   s:'♂',y:7, c:'#e03020',nat:'Malefic', gem:'Red Coral (Moonga)',      sig:'Energy · Courage · Land · Siblings · Surgery · Engineering',        rules:'Aries & Scorpio'},
  {n:'Rahu',   hi:'राहु',   s:'☊',y:18,c:'#208048',nat:'Malefic', gem:'Hessonite (Gomed)',       sig:'Ambition · Foreign · Technology · Illusion · Sudden events',       rules:'Co-rules Aquarius'},
  {n:'Jupiter',hi:'गुरु',   s:'♃',y:16,c:'#c89020',nat:'Benefic', gem:'Yellow Sapphire (Pukhraj)',sig:'Wisdom · Dharma · Children · Guru · Wealth · Higher education',   rules:'Sagittarius & Pisces'},
  {n:'Saturn', hi:'शनि',    s:'♄',y:19,c:'#7868b8',nat:'Malefic', gem:'Blue Sapphire (Neelam)',  sig:'Karma · Discipline · Delays · Longevity · Servants · Renunciation',rules:'Capricorn & Aquarius'},
  {n:'Mercury',hi:'बुध',    s:'☿',y:17,c:'#28b870',nat:'Benefic', gem:'Emerald (Panna)',         sig:'Intellect · Communication · Business · Trade · Siblings · Skin',   rules:'Gemini & Virgo'},
];

function buildDashaSections(){
  // Horizontal timeline
  const tl=document.getElementById('dashaTimeline');
  if(tl){
    tl.innerHTML='';
    DLORDS.forEach(l=>{
      const pct=(l.y/120*100).toFixed(3);
      const lblFull=l.y>=14;const lblShort=l.y>=8;
      tl.innerHTML+=`<div class="dt-seg" title="${l.n} (${l.y} yrs)" style="width:${pct}%;background:${l.c}dd;min-width:2px">
        <div class="dt-lbl">${lblFull?l.s+' '+l.n+'<br>'+l.y+'y':lblShort?l.s+'<br>'+l.y+'y':''}</div>
      </div>`;
    });
  }
  // Lord cards
  const cards=document.getElementById('dashaCards');
  if(!cards)return;
  cards.innerHTML='';
  const dark=isDark(),lang=document.documentElement.dataset.lang;
  DLORD_DETAIL.forEach(l=>{
    const natCol=l.nat==='Benefic'?'#28b870':'#d83820';
    const natBg=l.nat==='Benefic'?'rgba(40,184,112,.14)':'rgba(216,56,32,.14)';
    cards.innerHTML+=`<div class="dl-card" style="border-top:3px solid ${l.c}">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
        <div class="dl-sym" style="color:${l.c}">${l.s}</div>
        <div><div style="font-family:'DM Mono',monospace;font-size:.78rem;font-weight:700;color:${dark?'#a09070':'#5a4828'}">${l.y} yrs</div>
          <div class="dl-nat" style="background:${natBg};color:${natCol}">${l.nat}</div>
        </div>
      </div>
      <div class="dl-nm" style="color:${dark?'#ede4d0':'#14100a'}">${l.n}</div>
      <div class="dl-hi" style="color:${dark?'#a09070':'#7a6040'}">${l.hi}</div>
      <div class="dl-sig" style="color:${dark?'#a09070':'#5a4828'}">${l.sig}</div>
      <div class="dl-gem" style="color:${dark?'#c8a84b':'#8a6010'}"><i class="ph ph-diamond" style="font-size:.85rem"></i> ${l.gem}</div>
      <div style="font-size:.72rem;color:${dark?'#60503a':'#8a7450'};margin-top:5px">Rules: ${l.rules}</div>
    </div>`;
  });
}
function drawDashaWheel(){ buildDashaSections(); }

// ── MUHURAT TIMELINE CANVAS ────────────────────────────────────────────────
function drawMuhuratTimeline(){
  const ctx=cx2d('muhCv');if(!ctx)return;
  const W=900,H=72,dark=isDark();
  ctx.clearRect(0,0,W,H);
  ctx.fillStyle=dark?'#0c1220':'#f0ead8';ctx.fillRect(0,0,W,H);
  const cho=PD.choghadiya;if(!cho||!cho.length)return;
  const QCOL={'best':'#28b870','good':'#4a90c4','neutral':'#a08060','bad':'#d83820'};
  const QNAMES={'Amrit':'#28b870','Shubh':'#4a90c4','Labh':'#6ab04c','Chal':'#a08060','Rog':'#d83820','Kaal':'#7060a8','Udveg':'#c05018'};
  const segW=W/8,bH=44,bY=(H-bH)/2;
  // Draw 8 segments
  cho.forEach((c,i)=>{
    const x=i*segW;
    const col=QNAMES[c.name]||'#888';
    ctx.fillStyle=col+(dark?'44':'33');ctx.fillRect(x,bY,segW,bH);
    ctx.strokeStyle=col+'60';ctx.lineWidth=1;ctx.strokeRect(x,bY,segW,bH);
    // segment label
    ctx.fillStyle=col;ctx.font='bold 13px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='middle';
    ctx.fillText(c.name,x+segW/2,bY+bH/2-6);
    ctx.font='10px DM Sans,sans-serif';ctx.fillStyle=dark?'rgba(255,255,255,.5)':'rgba(0,0,0,.45)';
    ctx.fillText(c.start,x+segW/2,bY+bH/2+9);
  });
  // Time axis labels
  ctx.font='10px DM Mono,monospace';ctx.fillStyle=dark?'rgba(255,255,255,.4)':'rgba(0,0,0,.35)';ctx.textAlign='center';
  cho.forEach((c,i)=>{if(i%2===0)ctx.fillText(c.start,i*segW+2,bY-4);});
  ctx.textAlign='right';ctx.fillText(cho[7].end,W-2,bY-4);
  // Highlight Rahu Kaal
  const rk=PD.rahuKaal;
  if(rk&&rk.startHr&&rk.endHr){
    const rStart=cho[0].startHr,rEnd=cho[7].endHr,dayLen=rEnd-rStart;
    const rx=((rk.startHr-rStart)/dayLen)*W,rw=((rk.endHr-rk.startHr)/dayLen)*W;
    ctx.fillStyle='rgba(216,56,32,.18)';ctx.fillRect(rx,0,rw,H);
    ctx.strokeStyle='#d83820';ctx.lineWidth=2;ctx.strokeRect(rx,0,rw,H);
    ctx.fillStyle='#d83820';ctx.font='bold 11px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='top';
    ctx.fillText('Rahu Kaal',rx+rw/2,4);
  }
  // Highlight Abhijit
  const ab=PD.abhijit;
  if(ab&&ab.startHr&&ab.endHr){
    const rStart=cho[0].startHr,rEnd=cho[7].endHr,dayLen=rEnd-rStart;
    const ax=((ab.startHr-rStart)/dayLen)*W,aw=((ab.endHr-ab.startHr)/dayLen)*W;
    ctx.fillStyle='rgba(40,184,112,.18)';ctx.fillRect(ax,0,aw,H);
    ctx.strokeStyle='#28b870';ctx.lineWidth=2;ctx.strokeRect(ax,0,aw,H);
    ctx.fillStyle='#28b870';ctx.font='bold 11px DM Sans,sans-serif';ctx.textAlign='center';ctx.textBaseline='bottom';
    ctx.fillText('Abhijit ✦',ax+aw/2,H-4);
  }
  // Current time marker
  const now=new Date(),nH=now.getHours()+now.getMinutes()/60;
  const rStart=cho[0].startHr,rEnd=cho[7].endHr;
  if(nH>=rStart&&nH<=rEnd){
    const nx=((nH-rStart)/(rEnd-rStart))*W;
    ctx.strokeStyle='rgba(255,255,255,.9)';ctx.lineWidth=2;
    ctx.setLineDash([4,3]);ctx.beginPath();ctx.moveTo(nx,0);ctx.lineTo(nx,H);ctx.stroke();ctx.setLineDash([]);
    ctx.fillStyle='rgba(255,255,255,.9)';ctx.font='bold 9px DM Mono,monospace';ctx.textAlign='center';ctx.textBaseline='top';
    ctx.fillText('Now',nx,2);
  }
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
