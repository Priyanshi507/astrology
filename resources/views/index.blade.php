<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Vedic Astro Calculator</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
/* ═══════════════════════════════════════════════════════
   ALL CSS INLINE — no public/css file needed
   ═══════════════════════════════════════════════════════ */
:root {
  --bg:#eef3f9;--card:#fff;--panel:#e3ecf6;
  --sky:#1d4e6f;--sky-mid:#2a6d9c;--sky-lt:#5d8bb8;--sky-pale:#d0e4f5;--sky-wash:#eaf0f8;
  --text:#0e2d3f;--text-mid:#2d4a62;--text-lt:#5a7a93;
  --gold:#c48a2f;--gold-pale:#fdf3de;
  --lagna:#2e4a8e;--lagna-pale:#eaeefc;
  --sun:#d4760a;--sun-pale:#fff8ee;
  --merc:#2e7a6e;--merc-pale:#e8f7f5;
  --venus:#8e3a7a;--venus-pale:#f9edf7;
  --mars:#b83020;--mars-pale:#fce8e6;
  --jup:#7a5a10;--jup-pale:#fdf6e3;
  --sat:#4a4060;--sat-pale:#eeecf5;
  --rahu:#1a3a1a;--rahu-pale:#e6f0e6;
  --ketu:#5a1a0a;--ketu-pale:#f5e8e4;
  --house:#3a6080;--house-pale:#e4f0f8;
  --rx:32px;--pill:60px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);min-height:100vh;font-family:'DM Sans',system-ui,sans-serif;color:var(--text);display:flex;justify-content:center;padding:32px 16px 60px}
body::before{content:'';position:fixed;inset:0;z-index:0;background-image:radial-gradient(circle,#b8cfe0 1px,transparent 1px);background-size:28px 28px;opacity:.3;pointer-events:none}
.wrap{position:relative;z-index:1;max-width:1400px;width:100%}

/* Hero */
.hero{display:flex;align-items:center;gap:20px;margin-bottom:28px}
.hero-moon,.hero-sun{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.9rem}
.hero-moon{background:linear-gradient(135deg,#f5e4c0,#e0c070);box-shadow:0 0 0 7px #f5e9cc,0 8px 24px -6px rgba(196,138,47,.4)}
.hero-sun{background:linear-gradient(135deg,#ffe680,#ff9f2d);box-shadow:0 0 0 7px #fff0cc,0 8px 24px -6px rgba(212,118,10,.5)}
.hero h1{font-family:'Playfair Display',serif;font-weight:700;font-size:2rem;color:var(--sky)}
.hero h1 em{font-style:italic;color:var(--gold)}
.hero p{color:var(--text-mid);font-size:.95rem;margin-top:4px}

/* Card */
.card{background:var(--card);border-radius:var(--rx);padding:40px;box-shadow:0 24px 60px -16px rgba(13,40,70,.18),inset 0 2px 0 rgba(255,255,255,.9);margin-bottom:24px;width:100%}
.sec-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:1px;font-weight:600;color:var(--sky-lt);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.sec-lbl::after{content:'';flex:1;height:1px;background:var(--panel)}

/* Input panel */
.input-panel{
  background:var(--panel);
  border-radius:24px;
  padding:24px;
  display:grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
  gap:16px;
  align-items:end;
}
@media(max-width:900px){
  .input-panel{grid-template-columns:1fr 1fr;}
}
@media(max-width:560px){
  .input-panel{grid-template-columns:1fr;}
}
.coords-display{
  grid-column:1/-1;
  display:flex;align-items:center;gap:16px;flex-wrap:wrap;
  padding:10px 16px;
  background:rgba(255,255,255,.6);
  border-radius:16px;
  border:1.5px solid var(--sky-pale);
  font-size:.82rem;color:var(--text-mid);
}
.coord-pill{
  display:flex;align-items:center;gap:6px;
  background:#fff;
  border:1px solid var(--sky-pale);
  border-radius:20px;
  padding:5px 12px;
}
.coord-pill span:first-child{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--sky-lt)}
.coord-pill code{font-family:'DM Mono',monospace;font-weight:600;color:var(--sky);font-size:.85rem}
.geo-status{margin-left:auto;font-size:.75rem;font-weight:600}
.geo-status.ok{color:#2e7d52}.geo-status.err{color:#b13e3e}

.ig{display:flex;flex-direction:column;gap:6px}
.ig label{font-size:.72rem;text-transform:uppercase;letter-spacing:.7px;font-weight:600;color:var(--sky)}
.ig input,.ig select{width:100%;padding:12px 16px;border:1.5px solid transparent;border-radius:var(--pill);background:#fff;font-family:'DM Sans',sans-serif;font-size:.92rem;color:var(--text);box-shadow:0 2px 6px rgba(0,0,0,.05);transition:border-color .2s,box-shadow .2s;outline:none}

.ig select{
  padding:12px 36px 12px 16px;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235d8bb8' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right 12px center;
  -webkit-appearance:none;
  -moz-appearance:none;
  appearance:none;
}
.ig input:focus{border-color:var(--sky-mid);box-shadow:0 0 0 3px rgba(42,109,156,.18)}
.ig input::placeholder{color:#9ab3c8}
.city-wrap{position:relative}
.city-status{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:.85rem;pointer-events:none}
.geo-hint{font-size:.74rem;color:var(--text-lt);margin-top:4px;padding-left:4px;min-height:1.1em}
.geo-hint.ok{color:#2e7d52}.geo-hint.err{color:#b13e3e}
.btn-calc{grid-column:1/-1;background:linear-gradient(135deg,var(--sky),#0f3b55);border:none;color:#fff;padding:15px 28px;border-radius:var(--pill);font-family:'DM Sans',sans-serif;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 8px 20px -8px rgba(10,40,70,.4);transition:transform .15s,box-shadow .15s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-calc:hover{transform:translateY(-1px);box-shadow:0 12px 24px -8px rgba(10,40,70,.5)}
.btn-calc:disabled{opacity:.7;cursor:not-allowed}
.err-pill{background:#ffe1e1;color:#b13e3e;border-radius:var(--pill);padding:10px 20px;font-size:.88rem;font-weight:500;margin-top:12px;display:none}

/* Tabs */
.tabs{margin-bottom:28px;background:linear-gradient(170deg,#f8f4ff,#eef4fb);border-radius:24px;border:1.5px solid rgba(130,100,200,.15);box-shadow:0 4px 24px -6px rgba(80,40,140,.1),inset 0 1px 0 rgba(255,255,255,.95);padding:10px 12px 12px}
.tab-row{display:flex;align-items:center;gap:4px;padding:0}
.tab-row-views{margin-bottom:10px;background:rgba(80,40,140,.06);border-radius:16px;padding:6px 8px;border:1px solid rgba(130,100,200,.12)}
.tab-row-planets{background:rgba(13,40,90,.04);border-radius:16px;padding:5px 8px;border:1px solid rgba(13,40,90,.08)}
.tab-lbl{font-size:.62rem;text-transform:uppercase;letter-spacing:1px;font-weight:900;color:#1a2040;width:52px;flex-shrink:0;text-align:right;padding:0 10px 0 0;margin-right:4px;border-right:1.5px solid rgba(130,100,200,.18)}
.tab-btns{display:flex;align-items:center;gap:3px;flex:1;flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none;padding:1px 0}
.tab-btns::-webkit-scrollbar{display:none}
.tab-hint{font-size:.56rem;font-weight:700;color:rgba(80,40,140,.4);white-space:nowrap;margin-left:auto;padding:2px 8px;background:rgba(80,40,140,.07);border-radius:20px;letter-spacing:.5px}
.tab-btn{border:1.5px solid transparent;background:rgba(255,255,255,.5);font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--text-lt);padding:6px 13px;border-radius:50px;cursor:pointer;transition:all .18s;display:flex;align-items:center;gap:5px;white-space:nowrap;position:relative;flex-shrink:0}
.tab-btn:hover:not([class*="act-"]){background:#fff;border-color:rgba(130,100,200,.25);color:var(--sky);box-shadow:0 2px 10px -3px rgba(80,40,140,.18);transform:translateY(-1px)}
.tab-row-views .tab-btn{padding:7px 18px;font-size:.85rem;font-weight:700}
.tab-sym{font-size:1.05rem;line-height:1;flex-shrink:0}
.tab-k{display:inline-flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:800;padding:1px 4px;border-radius:4px;background:rgba(13,40,70,.08);color:var(--text-lt);border:1px solid rgba(13,40,70,.12);margin-left:3px;opacity:.6;text-transform:uppercase}

/* Active tab styles */
.act-today {background:linear-gradient(135deg,#c56408,#7a3400)!important;color:#ffd890!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(197,100,8,.8)!important}
.act-muhrat{background:linear-gradient(135deg,#c56408,#7a3400)!important;color:#ffd890!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(197,100,8,.8)!important}
.act-chart{background:linear-gradient(135deg,#1d5a7a,#0a2e44)!important;color:#a8d8f0!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(29,90,122,.7)!important}
.act-lagna{background:linear-gradient(135deg,#2e4a8e,#162856)!important;color:#c8d8ff!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(46,74,142,.7)!important}
.act-tithi{background:linear-gradient(135deg,#5a3080,#2a1050)!important;color:#d8b8ff!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(90,48,128,.7)!important}
.act-masa{background:linear-gradient(135deg,#5a3080,#2a1050)!important;color:#d8b8ff!important;font-weight:800!important}
.act-sun{background:linear-gradient(135deg,#c56408,#7a3400)!important;color:#ffd890!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(197,100,8,.8)!important}
.act-moon{background:linear-gradient(135deg,#1d5a8e,#0e2e50)!important;color:#b0d8f8!important;font-weight:800!important}
.act-merc{background:linear-gradient(135deg,#2e7a6e,#124438)!important;color:#90e0d4!important;font-weight:800!important}
.act-venus{background:linear-gradient(135deg,#8e3a7a,#4a1248)!important;color:#f0b0e0!important;font-weight:800!important}
.act-mars{background:linear-gradient(135deg,#b83020,#680e04)!important;color:#ffa090!important;font-weight:800!important}
.act-jup{background:linear-gradient(135deg,#7a5a10,#3e2800)!important;color:#f0d080!important;font-weight:800!important}
.act-sat{background:linear-gradient(135deg,#4a4070,#201838)!important;color:#c0b0e8!important;font-weight:800!important}
.act-rahu{background:linear-gradient(135deg,#1e4a1e,#061006)!important;color:#90d090!important;font-weight:800!important}
.act-ketu{background:linear-gradient(135deg,#6a2210,#300800)!important;color:#f0a090!important;font-weight:800!important}
.act-varga{background:linear-gradient(135deg,#6a4a20,#3a2008)!important;color:#f0d090!important;font-weight:800!important;box-shadow:0 2px 12px -3px rgba(106,74,32,.7)!important}

/* Result grid tiles */
.rg{display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:14px}
.tile{background:var(--sky-wash);border:1.5px solid var(--sky-pale);border-radius:20px;padding:16px 18px;transition:transform .15s,box-shadow .15s}
.tile:hover{transform:translateY(-2px);box-shadow:0 8px 20px -8px rgba(10,40,70,.15)}
.tl{font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;font-weight:600;color:var(--sky-lt);margin-bottom:6px}
.tv{font-family:'Playfair Display',serif;font-size:1.25rem;font-weight:700;color:var(--sky);line-height:1.1}
.ts{font-size:.78rem;color:var(--text-lt);margin-top:3px;font-style:italic}
.gold{background:var(--gold-pale);border-color:#e8c97a}.gold .tl{color:var(--gold)}.gold .tv{color:#7a4a0a}
.house-tile{background:var(--house-pale);border-color:#90c0e0}.house-tile .tl{color:var(--house)}.house-tile .tv{color:#1e4060;font-size:1.15rem}
.lagna-tile{background:var(--lagna-pale);border-color:#9090d8}.lagna-tile .tl{color:var(--lagna)}.lagna-tile .tv{color:#1e3070}
.desc-tile{background:#fcf0ee;border-color:#d8a090}.desc-tile .tl{color:#8e3020}.desc-tile .tv{color:#6e1808}
.sun-tile{background:var(--sun-pale);border-color:#f5c870}.sun-tile .tl{color:var(--sun)}.sun-tile .tv{color:#7a3a02}
.merc-tile{background:var(--merc-pale);border-color:#a0d8d0}.merc-tile .tl{color:var(--merc)}.merc-tile .tv{color:#1e5a52}
.venus-tile{background:var(--venus-pale);border-color:#d8a0d0}.venus-tile .tl{color:var(--venus)}.venus-tile .tv{color:#6a1858}
.mars-tile{background:var(--mars-pale);border-color:#e8a0a0}.mars-tile .tl{color:var(--mars)}.mars-tile .tv{color:#8a1810}
.jup-tile{background:var(--jup-pale);border-color:#d8c080}.jup-tile .tl{color:var(--jup)}.jup-tile .tv{color:#5a4000}
.sat-tile{background:var(--sat-pale);border-color:#b0a8d0}.sat-tile .tl{color:var(--sat)}.sat-tile .tv{color:#30284a}
.rahu-tile{background:var(--rahu-pale);border-color:#90b890}.rahu-tile .tl{color:var(--rahu)}.rahu-tile .tv{color:#0e280e}
.ketu-tile{background:var(--ketu-pale);border-color:#d8a898}.ketu-tile .tl{color:var(--ketu)}.ketu-tile .tv{color:#3e0e04}
.daylength-tile{background:linear-gradient(135deg,#f0faff,#d0eeff);border-color:#80c4ee}.daylength-tile .tl{color:#1a6090}.daylength-tile .tv{color:#104060}

/* Result strips */
.strip{display:flex;align-items:center;gap:20px;border-radius:20px;padding:22px 28px;margin-bottom:20px;color:#fff}
.strip-sym{font-size:3rem;line-height:1;flex-shrink:0;filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))}
.strip h2{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;line-height:1.1}
.strip p{font-size:.88rem;opacity:.75;margin-top:3px}
.strip-lon{margin-left:auto;text-align:right;flex-shrink:0}
.strip-lon .big{font-family:'Courier New',monospace;font-size:1.05rem;font-weight:700;background:rgba(255,255,255,.18);padding:5px 14px;border-radius:40px;display:inline-block}
.strip-lon .sm{font-size:.7rem;opacity:.6;margin-top:4px}
.lagna-strip{background:linear-gradient(120deg,#2e4a8e,#0e2050)}
.moon-strip{background:linear-gradient(120deg,var(--sky),#154260)}
.sun-strip{background:linear-gradient(120deg,#c56408,#7a3a02)}
.merc-strip{background:linear-gradient(120deg,#2e7a6e,#1a4840)}
.venus-strip{background:linear-gradient(120deg,#8e3a7a,#541848)}
.mars-strip{background:linear-gradient(120deg,#b83020,#6c1408)}
.jup-strip{background:linear-gradient(120deg,#7a5a10,#4a3400)}
.sat-strip{background:linear-gradient(120deg,#4a4060,#26203a)}
.rahu-strip{background:linear-gradient(120deg,#1e4a1e,#0a200a)}
.ketu-strip{background:linear-gradient(120deg,#602010,#300800)}
.tithi-strip{background:linear-gradient(120deg,#5a3080,#2a0e50)}
.vara-strip{background:linear-gradient(120deg,#c56408,#7a3a02)}


/* Progress bars */
.prog-wrap{margin-top:16px;background:var(--panel);border-radius:16px;padding:16px 20px}
.prog-wrap .tl{margin-bottom:10px}
.prog-track{background:var(--sky-pale);border-radius:8px;height:8px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,var(--sky-mid),var(--sky-lt));border-radius:8px;transition:width .7s cubic-bezier(.4,0,.2,1)}
.prog-padas{display:flex;justify-content:space-between;margin-top:6px;font-size:.7rem;color:var(--text-lt)}

/* Angles banner */
.angles-banner{display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:start;margin-bottom:16px}
.angle-block{background:var(--sky-wash);border:1.5px solid var(--sky-pale);border-radius:20px;padding:20px}
.asc-block{border-color:#8090d8;background:#f0f2fc}
.desc-block{border-color:#d89080;background:#fcf0ee}
.angle-icon{font-size:2rem;margin-bottom:6px;line-height:1}
.angle-head{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--sky);margin-bottom:2px}
.asc-block .angle-head{color:#2e4a8e}.desc-block .angle-head{color:#8e3020}
.angle-sub{font-size:.75rem;color:var(--text-lt);font-style:italic}
.angle-div{font-size:1.8rem;color:var(--sky-lt);display:flex;align-items:center;justify-content:center;padding-top:60px;user-select:none}
.mc-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:1px;font-weight:600;color:var(--sky-lt);margin-bottom:10px;display:flex;align-items:center;gap:8px}
.mc-lbl::after{content:'';flex:1;height:1px;background:var(--sky-pale)}
.info-box{margin-top:16px;border-radius:16px;padding:16px 20px;font-size:.85rem;line-height:1.6;color:var(--text-mid)}
.lagna-box{background:#eef1fc;border-left:4px solid #4060c0}.lagna-box strong{color:#2e4a8e}
.rahu-box{background:#e6f4e6;border-left:4px solid #2e7a2e}.rahu-box strong{color:#1a4a1a}
.ketu-box{background:#f5ece8;border-left:4px solid #8a3010}.ketu-box strong{color:#5a1a0a}

/* Separator */
.sep{display:flex;align-items:center;gap:12px;margin:28px 0 20px;color:var(--text-lt);font-size:.75rem;text-transform:uppercase;letter-spacing:1px;font-weight:600}
.sep::before,.sep::after{content:'';flex:1;height:1px;background:var(--sky-pale)}

/* Footnote */
.fn{background:var(--panel);border-radius:var(--pill);padding:12px 24px;text-align:center;font-size:.82rem;color:var(--text-mid);margin-top:14px}
.fn code{background:var(--sky-pale);padding:2px 10px;border-radius:20px;font-family:'Courier New',monospace;font-size:.8rem}

/* Sun event banner */
.sun-banner{background:linear-gradient(120deg,#1a1035,#3d1e6e 30%,#c56408 65%,#ffbb44);border-radius:20px;padding:22px 28px;margin-bottom:16px;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px;color:#fff}
.se-col{display:flex;flex-direction:column;gap:4px}
.se-col.rise{text-align:left}.se-col.set{text-align:right}
.se-lbl{font-size:.68rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;opacity:.75}
.se-time{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;line-height:1}
.se-icon{font-size:2.5rem;text-align:center}
.se-sub{font-size:.78rem;opacity:.65;font-style:italic}

/* Chart layout */
.chart-container{display:flex;gap:24px;align-items:stretch;flex-wrap:wrap;margin-bottom:28px;width:100%}
#chartSvgWrap>*{width:100%!important;max-width:100%!important;box-sizing:border-box}
#chartSvgWrap {
  flex: 1 1 100%;  
  width: 100%;
  max-width: 100%;
}

.chart-legend{flex:1;min-width:200px;background:linear-gradient(160deg,#1a2d4a,#0e1e30);border:1.5px solid rgba(80,140,200,.22);border-radius:18px;padding:16px 18px;box-shadow:0 4px 18px -6px rgba(10,30,60,.35);max-height:560px;overflow-y:auto}
.cl-title{font-size:.62rem;text-transform:uppercase;letter-spacing:2px;font-weight:900;color:rgba(150,200,248,.8);margin-bottom:12px;border-bottom:1px solid rgba(80,140,200,.18);padding-bottom:8px}
.cl-div{height:1px;background:rgba(80,140,200,.15);margin:14px 0}
.cl-val{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;color:rgba(220,240,255,.95);line-height:1.2}
.cl-sub{font-size:.73rem;color:rgba(160,205,248,.75);margin-top:4px;font-style:italic}
.cl-note{font-size:.72rem;color:rgba(160,200,240,.72);line-height:1.7;border-top:1px solid rgba(80,140,200,.12);padding-top:12px;margin-top:2px}
.cl-note strong{color:rgba(200,225,255,.9)}
.cl-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px 12px;font-size:.8rem;font-weight:700;margin-bottom:16px}
.cl-grid span{display:flex;align-items:center;gap:7px;color:rgba(210,230,255,.92);font-size:.78rem;font-weight:600}
.cl-grid span b{font-size:.9rem;line-height:1}

/* Panchanga */
.tithi-mode-bar{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px}
.tmb{border:2px solid rgba(90,48,128,.25);background:linear-gradient(160deg,#1a0f2e,#120a22);border-radius:20px;padding:16px 12px 14px;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:3px;color:rgba(220,200,255,.7);transition:all .35s;font-family:'DM Sans',sans-serif;text-align:center}
#tmb_sunrise {
  background: linear-gradient(160deg, #ffb347, #ff8c00, #e65c00);
  border-color: rgba(255,200,80,.6);
  box-shadow: 0 8px 28px -6px rgba(255,140,0,.6);
  color: #fff8e1;
}
#tmb_sunrise.active {
  background: linear-gradient(160deg, #ffc94a, #ff9d00, #f06000);
  border-color: rgba(255,220,100,.8);
  box-shadow: 0 8px 32px -4px rgba(255,150,0,.75);
  outline: 2px solid rgba(255,230,150,.5);
  outline-offset: 2px;
}
#tmb_now{background:linear-gradient(160deg,#0a0a1e,#181830,#0e0e26);border-color:rgba(100,100,200,.35)}
#tmb_now.active{background:linear-gradient(160deg,#1a1a40,#2a2a60,#1e1e50);border-color:rgba(120,120,240,.6);box-shadow:0 8px 28px -6px rgba(60,60,180,.55)}
#tmb_sunset {
  background: linear-gradient(160deg, #bf360c, #e64a19, #ff7043);
  border-color: rgba(255,120,50,.5);
  box-shadow: 0 8px 28px -6px rgba(200,60,0,.5);
  color: #fff3e0;
}
#tmb_sunset.active {
  background: linear-gradient(160deg, #d84315, #f4511e, #ff8a65);
  border-color: rgba(255,140,70,.75);
  box-shadow: 0 8px 32px -4px rgba(220,80,10,.75);
  outline: 2px solid rgba(255,160,100,.5);
  outline-offset: 2px;
}
.tmb:hover{transform:translateY(-3px);box-shadow:0 10px 28px -6px rgba(0,0,0,.35)}
.tmb.active{transform:translateY(-3px);outline:2px solid rgba(255,255,255,.35);outline-offset:2px}
.tmb-icon{font-size:1.5rem;line-height:1}
.tmb-lbl{font-size:.78rem;font-weight:800;letter-spacing:.3px;margin-top:2px}
.tmb-time{font-size:.75rem;font-weight:700;opacity:.9;display:block;margin-top:2px}
.tmb-sub{font-size:.64rem;opacity:.65;line-height:1.2;font-style:italic;margin-top:1px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lunar-arc{background:linear-gradient(160deg,#f0eafc,#e2d4f5);border-radius:20px;padding:16px 24px 10px;margin-bottom:20px;border:1.5px solid rgba(140,90,200,.28)}
.anga-badge{display:flex;align-items:center;gap:14px;padding:12px 18px;border-radius:14px;margin:24px 0 12px;border-left:5px solid;font-family:'Playfair Display',serif}
.anga-badge.tithi{background:linear-gradient(120deg,#f0e8ff,#e8d8f8);border-color:#7040c0}
.anga-badge.vara{background:linear-gradient(120deg,#fff3e0,#ffe0b0);border-color:#d4760a}
.anga-badge.naksh{background:linear-gradient(120deg,#e8f4fb,#d0e8f8);border-color:#1d6aa0}
.anga-badge.yoga{background:linear-gradient(120deg,#f0e8ff,#d8c8f0);border-color:#5a3080}
.anga-badge.karana{background:linear-gradient(120deg,#fff8e0,#fde8b0);border-color:#9a6010}
.ab-num{font-size:2rem;font-weight:700;line-height:1;opacity:.35}
.ab-body{flex:1}
.ab-name{font-size:1.25rem;font-weight:700;color:#1a1040;letter-spacing:.5px}
.ab-sub{font-size:.72rem;color:#6050a0;text-transform:uppercase;letter-spacing:1.5px;margin-top:2px;font-family:'DM Sans',sans-serif}
.ab-sym{font-size:1.8rem;opacity:.7}
.tithi-tile{background:linear-gradient(135deg,#2a0a50,#3d1580);border-color:#a07ad0;border-width:2px}
.tithi-tile .tl{color:#d0a8ff;font-weight:900;font-size:.65rem}.tithi-tile .tv{color:#fff;font-size:1.4rem;font-weight:900}
.karana-tile{background:linear-gradient(135deg,#4a2800,#7a4800);border-color:#d09040;border-width:2px}
.karana-tile .tl{color:#ffd090;font-weight:900;font-size:.65rem}.karana-tile .tv{color:#fff;font-size:1.4rem;font-weight:900}
.vara-tile{background:linear-gradient(135deg,#6a3800,#a05000);border-color:#e0a040;border-width:2px}
.vara-tile .tl{color:#ffd080;font-weight:900;font-size:.65rem}.vara-tile .tv{color:#fff;font-size:1.4rem;font-weight:900}
.nak-tile{background:linear-gradient(135deg,#0a2050,#1a4090);border-color:#4080c0;border-width:2px}
.nak-tile .tl{color:#90c8ff;font-weight:900;font-size:.65rem}.nak-tile .tv{color:#fff;font-size:1.4rem;font-weight:900}
.yoga-tile{background:linear-gradient(135deg,#1a0848,#3a1890);border-color:#8070c0;border-width:2px}
.yoga-tile .tl{color:#c0a8ff;font-weight:900;font-size:.65rem}.yoga-tile .tv{color:#fff;font-size:1.4rem;font-weight:900}
.tithi-prog{background:#ede4fb;border:1.5px solid #c0a0e8}
.tithi-prog .tl{color:#5a2090;font-weight:800;font-size:.72rem}
.tithi-prog .prog-track{background:#d0b8f0}
.tithi-prog .prog-fill{background:linear-gradient(90deg,#7a30c0,#c080ff)}
.karana-prog{background:#fef2de;border:1.5px solid #e0c080}
.karana-prog .prog-track{background:#f0d8a0}
.karana-prog .prog-fill{background:linear-gradient(90deg,#c08020,#f0c060)}
.nak-prog{background:#deeeff;border:1.5px solid #80b0e0}
.nak-prog .tl{color:#1a4880;font-weight:800}
.nak-prog .prog-track{background:#b0d0f0}
.nak-prog .prog-fill{background:linear-gradient(90deg,#1a6ab0,#50a0e0)}
.yoga-prog{background:#eae4f8;border:1.5px solid #b0a0e0}
.yoga-prog .tl{color:#3a2080;font-weight:800}
.yoga-prog .prog-track{background:#c8c0f0}
.yoga-prog .prog-fill{background:linear-gradient(90deg,#5030a0,#9070d0)}
.info-dark{margin-top:16px;border-radius:16px;padding:18px 22px;font-size:.88rem;line-height:1.7;background:linear-gradient(135deg,#2a0a50,#1a0840);color:#e8d8ff;border-left:5px solid #c090ff;font-weight:500}
.info-dark strong{color:#e0b0ff;font-weight:900;font-size:.95rem}
.info-vara{background:linear-gradient(135deg,#fff3e0,#ffe8c0);border-left-color:#d4760a;color:#3a1800;border-radius:16px;padding:18px 22px;font-size:.88rem;line-height:1.7;margin-top:16px}
.info-nak{background:linear-gradient(135deg,#e8f4ff,#d0e8fc);border-left:5px solid #1a6ab0;color:#0a1e40;border-radius:16px;padding:18px 22px;font-size:.88rem;line-height:1.7;margin-top:16px}
.info-yoga{background:linear-gradient(135deg,#f0e8ff,#e8d8f8);border-left:5px solid #8040c0;color:#1a0a30;border-radius:16px;padding:18px 22px;font-size:.88rem;line-height:1.7;margin-top:16px}
.pancha-sum{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:20px}
.pa{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px 12px;text-align:center;border-radius:18px;cursor:default;transition:transform .22s,box-shadow .22s}
.pa:hover{transform:translateY(-3px);box-shadow:0 10px 28px -6px rgba(0,0,0,.18)}
.pa:nth-child(1){background:linear-gradient(160deg,#f0e8ff,#e0d0f8);border:1.5px solid rgba(140,80,220,.22)}
.pa:nth-child(2){background:linear-gradient(160deg,#fff8e8,#feecc8);border:1.5px solid rgba(200,140,20,.22)}
.pa:nth-child(3){background:linear-gradient(160deg,#e8f4ff,#d0e8fc);border:1.5px solid rgba(30,120,200,.2)}
.pa:nth-child(4){background:linear-gradient(160deg,#e8faf4,#c8f0e0);border:1.5px solid rgba(20,160,100,.2)}
.pa:nth-child(5){background:linear-gradient(160deg,#fff3e8,#fde0c4);border:1.5px solid rgba(200,100,30,.2)}
.pa-num{font-size:.5rem;font-weight:900;text-transform:uppercase;opacity:.4;line-height:1}
.pa:nth-child(1) .pa-num{color:#6020a0}.pa:nth-child(2) .pa-num{color:#8a5000}.pa:nth-child(3) .pa-num{color:#0a5080}.pa:nth-child(4) .pa-num{color:#006040}.pa:nth-child(5) .pa-num{color:#7a3000}
.pa-lbl{font-size:.62rem;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;line-height:1;padding:3px 10px;border-radius:20px}
.pa:nth-child(1) .pa-lbl{background:rgba(120,60,200,.14);color:#5a10a0;border:1px solid rgba(120,60,200,.2)}
.pa:nth-child(2) .pa-lbl{background:rgba(190,120,0,.12);color:#7a4800;border:1px solid rgba(190,120,0,.2)}
.pa:nth-child(3) .pa-lbl{background:rgba(20,100,200,.12);color:#0a3880;border:1px solid rgba(20,100,200,.2)}
.pa:nth-child(4) .pa-lbl{background:rgba(10,140,80,.12);color:#004830;border:1px solid rgba(10,140,80,.2)}
.pa:nth-child(5) .pa-lbl{background:rgba(180,80,10,.12);color:#6a2800;border:1px solid rgba(180,80,10,.2)}
.pa-val{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;line-height:1.25;word-break:break-word}
.pa:nth-child(1) .pa-val{color:#3a0880}.pa:nth-child(2) .pa-val{color:#5a3000}.pa:nth-child(3) .pa-val{color:#082060}.pa:nth-child(4) .pa-val{color:#003828}.pa:nth-child(5) .pa-val{color:#541800}
.sun-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px}
.sri{display:flex;flex-direction:column;align-items:center;gap:4px;border-radius:20px;padding:16px 12px 14px;text-align:center;transition:transform .22s}
.sri:hover{transform:translateY(-2px)}
.sri.rise{background:linear-gradient(160deg,#fff3d0,#ffe0a0,#ffd080);border:1.5px solid rgba(220,150,20,.3)}
.sri.set{background:linear-gradient(160deg,#fff0e8,#ffd0b0,#ffb890);border:1.5px solid rgba(210,100,40,.28)}
.sri.day{background:linear-gradient(160deg,#e8f6ff,#c8e8ff,#b0daff);border:1.5px solid rgba(30,120,200,.22)}
.sri-icon{font-size:1.6rem;line-height:1}
.sri-lbl{font-size:.6rem;text-transform:uppercase;letter-spacing:1px;font-weight:900;line-height:1}
.sri.rise .sri-lbl{color:#7a4000}.sri.set .sri-lbl{color:#6a2800}.sri.day .sri-lbl{color:#083870}
.sri-val{font-family:'DM Mono','Courier New',monospace;font-size:1.05rem;font-weight:700;margin-top:2px}
.sri.rise .sri-val{color:#5a2a00}.sri.set .sri-val{color:#501800}.sri.day .sri-val{color:#052050}

/* Masa */
.masa-sum{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.masa-card{background:linear-gradient(135deg,#f2eeff,#e8e0f8);border-radius:14px;padding:14px 16px;border-left:4px solid #9080c0}
.masa-card .mc-l{font-size:.65rem;text-transform:uppercase;letter-spacing:1px;color:#7060a0;margin-bottom:5px;font-weight:700}
.masa-card .mc-v{font-size:1.05rem;font-weight:800;color:#3a2080}
.masa-ctrl{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px;background:linear-gradient(135deg,#f4f0ff,#ece5f8);border-radius:16px;padding:14px 18px;border:1.5px solid #ccc0e8}
.masa-ctrl select,.masa-ctrl input{padding:9px 14px;border-radius:10px;border:1.5px solid #c0b0e0;font-size:.88rem;background:#fff;color:#3a2070;font-weight:600;font-family:'DM Sans',sans-serif}
.masa-ctrl button{padding:9px 18px;border-radius:10px;background:linear-gradient(120deg,#7050b0,#5030a0);color:#fff;border:none;cursor:pointer;font-size:.88rem;font-weight:700;font-family:'DM Sans',sans-serif;transition:all .15s}
.masa-ctrl button:hover{background:linear-gradient(120deg,#8060c0,#6040b0);transform:translateY(-1px)}
.masa-ph{background:linear-gradient(120deg,#e8dff8,#d8ccf0);border-radius:14px;padding:12px 20px;margin:16px 0 8px;font-weight:800;color:#4a2f88;font-size:.9rem;display:flex;align-items:center;gap:10px;border-left:4px solid #7b5ea7}
.masa-pk{background:linear-gradient(120deg,#1a1040,#2a1860);color:#d0c8f8;border-left-color:#5040a0}
.masa-tw{overflow-x:auto;border-radius:14px;box-shadow:0 3px 16px rgba(80,40,140,.1);margin-bottom:16px;border:1.5px solid #ccc0e8}
.masa-t{width:100%;border-collapse:collapse;font-size:.82rem;min-width:720px}
.masa-t th{background:linear-gradient(120deg,#7b5ea7,#5e4480);color:rgba(255,245,255,.97);padding:11px 10px;text-align:center;font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;font-weight:800;border:none;position:sticky;top:0;z-index:1;white-space:nowrap;line-height:1.4}
.masa-t th .th-sub{font-size:.6rem;font-weight:500;opacity:.75;display:block;margin-top:2px;text-transform:none;font-family:'DM Mono','Courier New',monospace}
.masa-t td{padding:10px;border-bottom:1px solid rgba(160,130,220,.14);vertical-align:top;line-height:1.5}
.masa-t tr:hover td{background:rgba(140,100,200,.06)}
.masa-t tr:nth-child(even) td{background:rgba(140,120,200,.03)}
.td-day{font-weight:900;font-size:1.05rem;color:#3020a0;text-align:center;min-width:52px;vertical-align:middle;padding:10px 6px}
.td-vara-sm{display:block;font-size:.68rem;font-weight:700;color:#c56408;margin-top:2px}
.masa-an{font-weight:900;font-size:.96rem;color:#1a0840;line-height:1.3}
.td-tithi .masa-an{color:#4a1888}.td-karana .masa-an{color:#7a4000}.td-nak .masa-an{color:#1a4888}
.masa-sam{font-size:.71rem;color:#7558a8;font-weight:600;margin-top:3px;display:flex;align-items:center;gap:3px;font-family:'DM Mono','Courier New',monospace}
.masa-rp{margin-top:4px;font-size:.7rem;font-weight:700;color:#1a5090;background:#ddeeff;border-radius:6px;padding:2px 7px;display:inline-block;border:1px solid #aaccee}
.td-rise{color:#b04010;font-size:.84rem;font-weight:700;vertical-align:middle;white-space:nowrap}
.td-set{color:#4030a0;font-size:.84rem;font-weight:700;vertical-align:middle;white-space:nowrap}
.masa-t .amavasya td{background:rgba(60,30,100,.07)!important}.masa-t .amavasya td:first-child{border-left:3px solid #6030a0}
.masa-t .purnima td{background:rgba(240,220,80,.08)!important}.masa-t .purnima td:first-child{border-left:3px solid #c8a020}
.masa-t .ekadashi td{background:rgba(60,180,80,.05)!important}.masa-t .ekadashi td:first-child{border-left:3px solid #408040}
.masa-loading{text-align:center;padding:40px;color:#8070b0;font-style:italic;font-size:1rem}

/* Planet info bar */
.pib{display:flex;align-items:center;gap:8px;padding:9px 18px;margin:-10px 0 20px;background:var(--panel);border-radius:14px;border:1.5px solid var(--sky-pale);font-size:.82rem;font-weight:600;color:var(--text-mid);flex-wrap:wrap;min-height:38px}
.pib-sep{color:var(--sky-pale);font-size:1rem}
.pib-name{font-weight:800;color:var(--text);font-size:.85rem}
.pib-sign{color:var(--sky);font-weight:700}
.pib-nak{color:var(--text-lt);font-size:.78rem;font-style:italic}
.pib-retro{color:var(--mars);font-weight:800;font-size:.8rem}
.pib-deg{margin-left:auto;color:var(--sky-lt);font-size:.76rem;font-weight:600;font-family:'DM Mono',monospace}

/* SVG arcs */
.arc-wrap{text-align:center;margin:8px 0 16px}
.arc-wrap svg{width:100%;max-width:340px;display:inline-block}

@media(max-width:640px){
  .hero{flex-direction:column;text-align:center}
  .strip{flex-direction:column;text-align:center}
  .strip-lon{margin:0 auto;text-align:center}
  .angles-banner{grid-template-columns:1fr;gap:12px}
  .angle-div{padding-top:0;font-size:1.4rem}
  .chart-container {
  display: flex;
  gap: 24px;
  align-items: stretch;
  flex-wrap: wrap;
  margin-bottom: 28px;
  width: 100%;   
}
  #chartSvgWrap{flex:1 1 100%;width:100%;max-width:100%}
  .chart-legend{width:100%}
  .pancha-sum{grid-template-columns:repeat(3,1fr);gap:6px}
  .sun-row{grid-template-columns:1fr;gap:7px}
  .tithi-mode-bar{grid-template-columns:1fr;gap:8px}
  .tmb{flex-direction:row;padding:12px 16px;justify-content:flex-start;gap:10px}
}

/* ── Varga Charts ───────────────────────────────────── */
.varga-grid-wrap{overflow-x:auto;margin-bottom:20px;border-radius:16px;box-shadow:0 4px 24px -6px rgba(10,30,60,.4)}
.varga-grid-wrap svg{min-width:600px;width:100%;display:block}
.varga-btn{
  border:1.5px solid rgba(160,110,40,.3);
  background:rgba(200,160,80,.12);
  color:#7a5a30;
  font-family:'DM Sans',sans-serif;
  font-size:.76rem;
  font-weight:700;
  padding:5px 12px;
  border-radius:20px;
  cursor:pointer;
  transition:all .18s;
  white-space:nowrap;
}
.varga-btn:hover{
  background:rgba(180,120,40,.22);
  border-color:rgba(180,130,50,.55);
  color:#5a3a10;
  transform:translateY(-1px);
  box-shadow:0 3px 10px rgba(120,80,20,.18);
}
.varga-btn.active{
  background:linear-gradient(120deg,#8a5a20,#5a3008);
  border-color:rgba(200,150,60,.7);
  color:#f8e8b0;
  box-shadow:0 2px 10px rgba(100,60,10,.4);
  transform:translateY(-1px);
}
.varga-badge-wrap{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px}

/* Vargas tab active state — warm gold/brown to match chart theme */
.act-varga{
  background:linear-gradient(135deg,#6a4a20,#3a2008)!important;
  color:#f0d090!important;
  font-weight:800!important;
  box-shadow:0 2px 12px -3px rgba(106,74,32,.7)!important;
  border-color:rgba(200,140,40,.4)!important;
}
 
/* Varga card grid inside vargaPanel */
.varga-card { transition: box-shadow .2s; }
.varga-card:hover { box-shadow: 0 6px 20px rgba(120,80,20,.14) !important; }

card-wrap { transition: transform .15s, box-shadow .15s; }
.festival-card-wrap:hover { transform: translateY(-2px); }
.fest-month-header {
display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
}
.fest-month-badge {
background: linear-gradient(135deg,#f5e6c8,#ecdab0);
border-radius: 10px; padding: 8px 16px; border: 1.5px solid #d4b878;
}
.fest-loading {
text-align: center; padding: 48px; color: #7a5a30;
font-style: italic; font-size: .9rem;
}
.fest-year-ctrl {
display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
margin-bottom: 20px; background: linear-gradient(135deg,#fdf8ee,#faf0e0);
border-radius: 14px; padding: 14px 18px;
border: 1.5px solid #e8d8a8;
}
.fest-year-ctrl select,
.fest-year-ctrl input {
padding: 9px 14px; border-radius: 10px; border: 1.5px solid #d4b878;
font-size: .88rem; background: #fff; color: #5a3800;
font-weight: 600; font-family: 'DM Sans', sans-serif;
}
.fest-year-ctrl button {
padding: 9px 18px; border-radius: 10px;
background: linear-gradient(120deg,#c47a20,#9a5800);
color: #fff; border: none; cursor: pointer;
font-size: .88rem; font-weight: 700;
font-family: 'DM Sans', sans-serif; transition: all .15s;
}
.fest-year-ctrl button:hover {
background: linear-gradient(120deg,#d48a30,#b06800);
transform: translateY(-1px);
}

.act-festival {
background: linear-gradient(135deg,#c47a20,#7a4400) !important;
color: #ffecc8 !important; font-weight: 800 !important;
box-shadow: 0 2px 12px -3px rgba(180,100,20,.7) !important;
}
/.fest-rel-btn {
  background: #fdf8ee; color: #7a5000;
  font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer;
  transition: background .15s, color .15s; white-space: nowrap;
}
.fest-rel-btn:hover { background: #fff3d8; color: #5a3000; }
.fest-rel-btn.active { background: linear-gradient(120deg,#c47a20,#9a5800); color: #fff; }

.fest-month-btn {
  background: #fdf8ee; color: #7a5000;
  font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer;
  transition: background .15s, color .15s;
}
.fest-month-btn:hover { background: #fff3d8; color: #5a3000; }
.fest-month-btn.active { background: linear-gradient(120deg,#c47a20,#9a5800); color: #fff; }

/* ── Quick Actions strip ───────────────────────── */
.tab-row-actions {
  margin-bottom: 10px;
  background: rgba(13,40,90,.04);
  border-radius: 16px;
  padding: 6px 8px;
  border: 1px solid rgba(13,40,90,.08);
  display: flex;
  align-items: center;
  gap: 4px;
}
.action-btn {
  border: 1.5px solid transparent;
  background: rgba(255,255,255,.6);
  font-family: 'DM Sans', sans-serif;
  font-size: .82rem;
  font-weight: 700;
  color: var(--text-mid);
  padding: 7px 18px;
  border-radius: 50px;
  cursor: pointer;
  transition: all .18s;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  flex-shrink: 0;
}
.action-btn:hover {
  background: #fff;
  border-color: rgba(196,138,47,.4);
  color: var(--gold);
  box-shadow: 0 2px 12px -3px rgba(196,138,47,.25);
  transform: translateY(-1px);
}
.action-btn.today-btn {
  background: linear-gradient(135deg, #1d4e6f, #0f3b55);
  color: #a8d8f0;
  border-color: rgba(29,78,111,.4);
  box-shadow: 0 2px 10px -3px rgba(29,78,111,.5);
}
.action-btn.today-btn:hover {
  background: linear-gradient(135deg, #2a6d9c, #1a4e6f);
  color: #c8eaff;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px -4px rgba(29,78,111,.6);
}
.action-btn.festival-btn {
  background: linear-gradient(135deg, #c47a20, #9a5800);
  color: #ffecc8;
  border-color: rgba(196,122,32,.4);
  box-shadow: 0 2px 10px -3px rgba(180,100,20,.5);
}
.action-btn.festival-btn:hover {
  background: linear-gradient(135deg, #d48a30, #b06800);
  color: #fff8e0;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px -4px rgba(180,100,20,.6);
}
.action-sep {
  width: 1px;
  height: 24px;
  background: rgba(196,138,47,.2);
  margin: 0 4px;
  flex-shrink: 0;
}
.action-hint {
  font-size: .56rem;
  font-weight: 700;
  color: rgba(80,40,140,.4);
  white-space: nowrap;
  margin-left: auto;
  padding: 2px 8px;
  background: rgba(80,40,140,.07);
  border-radius: 20px;
  letter-spacing: .5px;
}

/* ── Festival panel ──────────────────────────────────────── */
 .fest-year-bar {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px;
  background: linear-gradient(135deg, #1a0e2e 0%, #2a1850 50%, #1e1040 100%);
  border-radius: 18px 18px 0 0;
  border: 1.5px solid rgba(160,120,255,.25); border-bottom: none;
  position: relative; overflow: hidden;
}
.fest-year-bar::before {
  content: ''; position: absolute; inset: 0; pointer-events: none;
  background: repeating-linear-gradient(90deg, transparent, transparent 40px,
    rgba(255,255,255,.015) 40px, rgba(255,255,255,.015) 41px);
}
.fest-year-title {
  font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700;
  color: rgba(220,200,255,.9); letter-spacing: .5px; flex-shrink: 0;
}
.fest-year-title span {
  font-size: .6rem; font-family: 'DM Sans', sans-serif;
  text-transform: uppercase; letter-spacing: 2px;
  color: rgba(160,130,255,.6); display: block; margin-bottom: 2px;
}
.fest-year-divider { width: 1px; height: 28px; background: rgba(160,120,255,.2); flex-shrink: 0; }
.year-nav-group { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.yr-btn {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid rgba(160,120,255,.3); background: rgba(255,255,255,.07);
  color: rgba(200,180,255,.8); font-size: .85rem; font-weight: 800;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all .15s; font-family: 'DM Sans', sans-serif;
}
.yr-btn:hover { background: rgba(160,120,255,.2); border-color: rgba(160,120,255,.6); color: #fff; transform: scale(1.08); }
.yr-input {
  width: 80px; padding: 7px 10px; border-radius: 10px;
  border: 1.5px solid rgba(160,120,255,.3); background: rgba(255,255,255,.08);
  color: rgba(220,200,255,.95); font-size: .95rem; font-weight: 800;
  font-family: 'DM Sans', sans-serif; text-align: center; outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.yr-input:focus { border-color: rgba(160,120,255,.7); box-shadow: 0 0 0 3px rgba(130,80,220,.18); }
.load-year-btn {
  padding: 8px 20px; border-radius: 50px;
  background: linear-gradient(135deg, #7a40c0, #5a20a0);
  color: #e8d8ff; border: 1.5px solid rgba(180,120,255,.4);
  font-size: .85rem; font-weight: 800; font-family: 'DM Sans', sans-serif;
  cursor: pointer; box-shadow: 0 4px 14px -4px rgba(100,40,200,.5);
  transition: all .15s; letter-spacing: .3px; flex-shrink: 0;
}
.load-year-btn:hover { background: linear-gradient(135deg, #8a50d0, #6a30b0); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(100,40,200,.6); }
.yr-hint { margin-left: auto; font-size: .6rem; color: rgba(140,110,200,.5); letter-spacing: .8px; text-transform: uppercase; font-weight: 700; flex-shrink: 0; }
 
.fest-cats { border: 1.5px solid rgba(160,120,255,.18); border-top: 1px solid rgba(160,120,255,.1); border-radius: 0 0 18px 18px; overflow: hidden; background: #fff; }
.fest-cat-row { border-bottom: 1px solid rgba(0,0,0,.06); transition: background .15s; }
.fest-cat-row:last-child { border-bottom: none; }
.fest-cat-header { display: flex; align-items: center; gap: 0; cursor: pointer; user-select: none; transition: background .15s; }
.fch-accent { width: 5px; align-self: stretch; flex-shrink: 0; transition: width .15s; }
.fest-cat-row.open .fch-accent { width: 6px; }
.fch-icon { width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; margin: 0 2px; }
.fch-label-zone { flex: 1; padding: 14px 12px 14px 4px; min-width: 0; }
.fch-hi { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; line-height: 1; display: flex; align-items: baseline; gap: 8px; }
.fch-en { font-family: 'DM Sans', sans-serif; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; opacity: .65; }
.fch-sub { font-size: .72rem; color: rgba(0,0,0,.4); margin-top: 3px; font-style: italic; }
.fch-badge { flex-shrink: 0; padding: 5px 16px 5px 12px; display: flex; align-items: center; gap: 10px; }
.fch-count { font-size: .68rem; font-weight: 900; padding: 3px 10px; border-radius: 50px; letter-spacing: .5px; }
.fch-chevron { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .7rem; transition: transform .25s cubic-bezier(.4,0,.2,1), background .15s; flex-shrink: 0; }
.fest-cat-row.open .fch-chevron { transform: rotate(180deg); }
.fest-cat-body { max-height: 0; overflow: hidden; transition: max-height .3s cubic-bezier(.4,0,.2,1); }
.fest-cat-row.open .fest-cat-body { max-height: 320px; }
.fest-btn-grid { padding: 10px 14px 14px 14px; display: flex; flex-wrap: wrap; gap: 7px; border-top: 1px solid rgba(0,0,0,.05); }
.fb { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 50px; border: 1.5px solid transparent; font-family: 'DM Sans', sans-serif; font-size: .85rem; font-weight: 700; cursor: pointer; transition: all .15s; white-space: nowrap; background: rgba(0,0,0,.04); color: rgba(0,0,0,.6); line-height: 1.2; }
.fb .fb-sym { font-size: 1rem; line-height: 1; flex-shrink: 0; }
.fb:hover { background: rgba(0,0,0,.08); color: rgba(0,0,0,.85); transform: translateY(-1px); box-shadow: 0 3px 10px -2px rgba(0,0,0,.1); }
.fb.act-v { background: linear-gradient(135deg,#0d5c3a,#063d26); color: #80f0b8; border-color: rgba(13,92,58,.4); box-shadow: 0 2px 10px -3px rgba(13,92,58,.5); }
.fb.act-p { background: linear-gradient(135deg,#7a3800,#521800); color: #ffc890; border-color: rgba(122,56,0,.4); box-shadow: 0 2px 10px -3px rgba(122,56,0,.5); }
.fb.act-j { background: linear-gradient(135deg,#1d3a8a,#0a1848); color: #90c0ff; border-color: rgba(29,58,138,.4); box-shadow: 0 2px 10px -3px rgba(29,58,138,.5); }
.fb.act-o { background: linear-gradient(135deg,#0a4a2a,#042a18); color: #80f0b0; border-color: rgba(10,74,42,.4); box-shadow: 0 2px 10px -3px rgba(10,74,42,.5); }
.cat-vrat .fch-accent { background: linear-gradient(180deg,#7040c0,#4a20a0); }
.cat-vrat .fch-hi { color: #4a2090; } .cat-vrat .fch-en { color: #6a40b0; }
.cat-vrat .fch-count { background: rgba(112,64,192,.12); color: #4a2090; }
.cat-vrat .fch-chevron { background: rgba(112,64,192,.1); color: #7040c0; }
.cat-vrat.open .fch-chevron { background: rgba(112,64,192,.18); }
.cat-vrat.open .fest-cat-header { background: rgba(112,64,192,.04); }
.cat-vrat .fb:hover { border-color: rgba(112,64,192,.3); color: #4a2090; }
.cat-parv .fch-accent { background: linear-gradient(180deg,#c47a20,#8a4400); }
.cat-parv .fch-hi { color: #8a4400; } .cat-parv .fch-en { color: #b06020; }
.cat-parv .fch-count { background: rgba(196,122,32,.12); color: #8a4400; }
.cat-parv .fch-chevron { background: rgba(196,122,32,.1); color: #c47a20; }
.cat-parv.open .fch-chevron { background: rgba(196,122,32,.18); }
.cat-parv.open .fest-cat-header { background: rgba(196,122,32,.04); }
.cat-parv .fb:hover { border-color: rgba(196,122,32,.35); color: #8a4400; }
.cat-jayanti .fch-accent { background: linear-gradient(180deg,#2a5ab0,#162a70); }
.cat-jayanti .fch-hi { color: #1a3a8a; } .cat-jayanti .fch-en { color: #2a4aa0; }
.cat-jayanti .fch-count { background: rgba(42,90,176,.12); color: #1a3a8a; }
.cat-jayanti .fch-chevron { background: rgba(42,90,176,.1); color: #2a5ab0; }
.cat-jayanti.open .fch-chevron { background: rgba(42,90,176,.18); }
.cat-jayanti.open .fest-cat-header { background: rgba(42,90,176,.04); }
.cat-jayanti .fb:hover { border-color: rgba(42,90,176,.3); color: #1a3a8a; }
.cat-other .fch-accent { background: linear-gradient(180deg,#1a8060,#0a4a30); }
.cat-other .fch-hi { color: #0a5030; } .cat-other .fch-en { color: #1a7050; }
.cat-other .fch-count { background: rgba(26,128,96,.12); color: #0a5030; }
.cat-other .fch-chevron { background: rgba(26,128,96,.1); color: #1a8060; }
.cat-other.open .fch-chevron { background: rgba(26,128,96,.18); }
.cat-other.open .fest-cat-header { background: rgba(26,128,96,.04); }
.cat-other .fb:hover { border-color: rgba(26,128,96,.3); color: #0a5030; }

.td-loading{
  display:flex;align-items:center;gap:14px;
  padding:48px 24px;
  font-size:1rem;color:var(--text-lt);font-style:italic;
}
.td-spinner{
  font-size:1.8rem;
  animation:td-spin 2s linear infinite;
  display:inline-block;
}
@keyframes td-spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

/* Today panel wraps the server-rendered .tp-root HTML */
#todayContent{
  /* tp-root is dark-themed; give it a subtle rounded wrapper */
  border-radius:20px;
  overflow:hidden;
}

/* Error state */
.td-error{
  display:flex;align-items:center;gap:12px;
  padding:32px 24px;
  background:#fff5f5;border-radius:16px;
  border-left:4px solid #c03030;
  color:#8a2020;font-size:.9rem;
}
/* ── Festival main tabs ── */
</style>
</head>
<body>
<div class="wrap">

{{-- ── HERO ── --}}
<div class="hero">
  <div style="display:flex;gap:6px;flex-shrink:0">
    <div class="hero-moon">☽</div>
    <div class="hero-sun">☀</div>
  </div>
  <div>
    <h1>Vedic Astro <em>Calculator</em></h1>
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

  {{-- ═══════════ TODAY PANEL ═══════════ --}}
<div id="todayPanel" style="display:none">
  <div class="sec-lbl">🌅 Today at a Glance</div>
  <div id="todayContent">
    <div class="td-loading" style="justify-content:center;min-height:120px;align-items:center;flex-direction:column;gap:12px">
      <span class="td-spinner" style="font-size:2.2rem">🪷</span>
      <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:var(--text-mid)">आज का पंचांग गणना हो रहा है…</span>
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

  {{-- ═══════════ MASA PANEL ═══════════ --}}
  <div id="masaPanel" style="display:none">
    <div class="sec-lbl">📅 Masa Panchanga — Monthly Calendar</div>
    <div class="masa-sum">
      <div class="masa-card"><div class="mc-l">Month</div><div class="mc-v" id="ms_month">—</div></div>
      <div class="masa-card"><div class="mc-l">Location</div><div class="mc-v" id="ms_loc">—</div></div>
      <div class="masa-card"><div class="mc-l">Shukla Days</div><div class="mc-v" id="ms_shukla">—</div></div>
      <div class="masa-card"><div class="mc-l">Krishna Days</div><div class="mc-v" id="ms_krishna">—</div></div>
    </div>
    <div class="masa-ctrl">
      <button onclick="masaNav(-1)">◀ Prev</button>
      <select id="masaSel" onchange="masaLoad()">
        <option value="1">Chaitra (Mar/Apr)</option><option value="2">Vaishakha (Apr/May)</option>
        <option value="3">Jyeshtha (May/Jun)</option><option value="4">Ashadha (Jun/Jul)</option>
        <option value="5">Shravana (Jul/Aug)</option><option value="6">Bhadrapada (Aug/Sep)</option>
        <option value="7">Ashwin (Sep/Oct)</option><option value="8">Kartik (Oct/Nov)</option>
        <option value="9">Margashirsha (Nov/Dec)</option><option value="10">Pausha (Dec/Jan)</option>
        <option value="11">Magha (Jan/Feb)</option><option value="12">Phalguna (Feb/Mar)</option>
      </select>
      <input type="number" id="masaYr" min="1900" max="2100" style="width:80px" placeholder="Year"/>
      <button onclick="masaLoad()">✦ Calculate</button>
      <button onclick="masaNav(1)">Next ▶</button>
    </div>
    <div style="font-size:.68rem;color:#7058a0;margin-bottom:10px;padding:6px 12px;background:rgba(123,94,167,.06);border-radius:8px;display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
      <span><b style="font-family:'DM Mono',monospace;color:#7558a8">🕐 HH:MM</b> — ends same day</span>
      <span><b style="font-family:'DM Mono',monospace;color:#1d5a8a">▶HH:MM</b> — ends next day</span>
      <span><b style="color:#1d5a8a">☽ Rashi</b> — Moon enters new sign</span>
    </div>
    <div id="masaContent"><div class="masa-loading">✦ Calculate your chart above to generate the monthly Panchanga</div></div>
  </div>
  <div id="dashaPanel" style="display:none">
  <div class="sec-lbl">⏳ Vimshottari Dasha — 120-Year Cycle</div>
  <div id="dashaContent" style="font-family:'DM Sans',sans-serif">
  </div>
</div>

<div id="shadbalaPanel" style="display:none">
  <div class="sec-lbl">⚖ Shadbala — Six-fold Planetary Strength</div>
  <div id="shadbalaContent" style="font-family:'DM Sans',sans-serif">
  </div>
</div>
  
  @include('partials._panel_muhrat')

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

@include('partials._panel_festival')

</div>

</div>{{-- /wrap --}}
<script>

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── State ──────────────────────────────────────────────────────────
let _tkData = {sunrise:null, now:null, sunset:null};
let _mode = 'sunrise';
let _masaLat=null, _masaLon=null, _masaOff=null, _masaYr=null, _masaMo=null;
let _planets = {};
let _muhratData = null;
let _muhratActivity = 'religious';
let _muhratLoaded = false;

let _todayLoaded  = false; 
let _todayLoading = false;  

// ── Set current date/time + auto-recalc listeners ─────────────────
const STATE_CITIES = {
 
  // ── ANDHRA PRADESH ──────────────────────────────────────────────────────────
  AP: [
    ['Visakhapatnam',17.6868,83.2185],['Vijayawada',16.5062,80.6480],
    ['Guntur',16.3067,80.4365],['Tirupati',13.6288,79.4192],
    ['Kurnool',15.8281,78.0373],['Nellore',14.4426,79.9865],
    ['Kakinada',16.9891,82.2475],['Rajahmundry',17.0005,81.8040],
    ['Kadapa',14.4674,78.8241],['Anantapur',14.6819,77.6006],
    ['Vizianagaram',18.1066,83.3956],['Eluru',16.7107,81.0952],
    ['Ongole',15.5057,80.0499],['Nandyal',15.4786,78.4839],
    ['Machilipatnam',16.1875,81.1389],['Adoni',15.6281,77.2732],
    ['Tenali',16.2427,80.6404],['Proddatur',14.7500,78.5500],
    ['Chittoor',13.2172,79.1003],['Hindupur',13.8286,77.4918],
    ['Bhimavaram',16.5444,81.5212],['Madanapalle',13.5500,78.5000],
    ['Guntakal',15.1667,77.3667],['Dharmavaram',14.4167,77.7167],
    ['Tadepalligudem',16.8167,81.5333],['Chilakaluripet',16.0903,80.1671],
    ['Gudivada',16.4350,80.9938],['Narasaraopet',16.2341,80.0492],
    ['Tadipatri',14.9060,78.0124],['Srikakulam',18.2949,83.8975],
    ['Bobbili',18.5718,83.3625],['Palasa',18.7724,84.4148],
    ['Rajam',18.4667,83.6500],['Ponnur',16.0667,80.5500],
    ['Bapatla',15.9045,80.4673],['Narasapuram',16.4347,81.6991],
    ['Pithapuram',17.1155,82.2538],['Anakapalle',17.6909,83.0048],
    ['Gajuwaka',17.6841,83.2026],['Bheemunipatnam',17.8917,83.4528],
    ['Mangalagiri',16.4304,80.5552],['Puttaparthi',14.1641,77.8162],
    ['Amaravati',16.5739,80.3567],['Naidupeta',13.9048,79.9028],
    ['Sullurpeta',13.7700,80.0200],['Srikalahasti',13.7500,79.7000],
    ['Puttur',13.4400,79.5500],['Pileru',13.6500,78.9500],
    ['Nagari',13.3200,79.5800],['Gudur',14.1500,79.8500],
  ],
 
  // ── ARUNACHAL PRADESH ───────────────────────────────────────────────────────
  AR: [
    ['Itanagar',27.0844,93.6053],['Naharlagun',27.1007,93.6957],
    ['Pasighat',28.0659,95.3299],['Tawang',27.5859,91.8687],
    ['Ziro',27.5333,93.8333],['Along',28.1667,94.8000],
    ['Bomdila',27.2648,92.4230],['Tezu',27.9167,96.1667],
    ['Khonsa',27.1000,95.5333],['Changlang',27.1167,95.7667],
    ['Namsai',27.6667,95.8333],['Deomali',27.6167,95.9167],
    ['Anini',28.7833,98.0667],['Roing',28.1400,95.8300],
    ['Daporijo',27.9833,94.2167],['Sagalee',27.0000,93.5833],
    ['Seppa',27.3333,92.9833],['Longding',27.3667,95.5833],
    ['Yingkiong',28.6333,95.0167],['Aalo',28.1667,94.8000],
  ],
 
  // ── ASSAM ───────────────────────────────────────────────────────────────────
  AS: [
    ['Guwahati',26.1445,91.7362],['Dibrugarh',27.4728,94.9120],
    ['Silchar',24.8333,92.7789],['Jorhat',26.7509,94.2037],
    ['Nagaon',26.3509,92.6843],['Tinsukia',27.4894,95.3673],
    ['Dimapur',25.9044,93.7276],['Tezpur',26.6338,92.7924],
    ['Karimganj',24.8654,92.3597],['Hailakandi',24.6823,92.5574],
    ['Barpeta',26.3222,91.0028],['Dhubri',26.0200,89.9800],
    ['Goalpara',26.1667,90.6167],['Bongaigaon',26.4761,90.5584],
    ['Kokrajhar',26.4028,90.2701],['North Lakhimpur',27.2333,94.1000],
    ['Haflong',25.1667,93.0167],['Diphu',25.8421,93.4328],
    ['Golaghat',26.5167,93.9667],['Sivasagar',26.9833,94.6333],
    ['Namrup',27.1833,95.3167],['Doom Dooma',27.4500,95.5667],
    ['Margherita',27.2833,95.6833],['Digboi',27.3833,95.6167],
    ['Duliajan',27.3667,95.3167],['Lakhimpur',27.2333,94.1000],
    ['Charaideo',26.9833,94.6333],['Majuli',26.9500,94.1667],
    ['Dhemaji',27.4833,94.5667],['Morigaon',26.2500,92.3333],
    ['Baksa',26.6333,91.2167],['Chirang',26.5000,90.4667],
    ['Kamrup',26.3500,91.6500],['Nalbari',26.4500,91.4333],
    ['Biswanath',26.7333,93.1500],['Hojai',26.0000,92.8667],
    ['Udalguri',26.7500,92.1000],['Darrang',26.4500,92.0333],
    ['Sonitpur',26.6333,92.7833],['Cachar',24.8500,92.7500],
  ],
 
  // ── BIHAR ───────────────────────────────────────────────────────────────────
  BR: [
    ['Patna',25.5941,85.1376],['Gaya',24.7955,85.0002],
    ['Bhagalpur',25.2425,86.9842],['Muzaffarpur',26.1197,85.3910],
    ['Purnia',25.7771,87.4753],['Darbhanga',26.1542,85.8918],
    ['Bihar Sharif',25.2000,85.5200],['Arrah',25.5567,84.6636],
    ['Begusarai',25.4182,86.1272],['Katihar',25.5392,87.5764],
    ['Munger',25.3743,86.4735],['Chhapra',25.7836,84.7333],
    ['Danapur',25.6167,85.0500],['Saharsa',25.8838,86.5952],
    ['Hajipur',25.6894,85.2093],['Sasaram',24.9500,84.0333],
    ['Dehri',24.9000,84.1833],['Siwan',26.2214,84.3572],
    ['Motihari',26.6500,84.9167],['Nawada',24.8866,85.5416],
    ['Bagaha',27.1000,84.0833],['Buxar',25.5667,83.9833],
    ['Kishanganj',26.1056,87.9417],['Sitamarhi',26.5922,85.4839],
    ['Sheohar',26.5167,85.3000],['Supaul',26.1233,86.6025],
    ['Madhubani',26.3557,86.0721],['Samastipur',25.8660,85.7789],
    ['Khagaria',25.5016,86.4649],['Lakhisarai',25.1538,86.0978],
    ['Sheikhpura',25.1400,85.8500],['Nalanda',25.0000,85.5000],
    ['Jehanabad',25.2033,84.9916],['Aurangabad',24.7527,84.3729],
    ['Rohtas',24.9500,84.0333],['Kaimur',25.0500,83.6000],
    ['Gopalganj',26.4677,84.4376],['East Champaran',26.6500,84.9167],
    ['West Champaran',27.1000,84.3667],['Vaishali',25.6894,85.2093],
    ['Madhepura',25.9220,86.7915],['Araria',26.1477,87.4726],
    ['Jamui',24.9246,86.2239],['Banka',24.8868,86.9180],
    ['Bhojpur',25.5667,84.6636],['Saran',25.7836,84.7333],
  ],
 
  // ── CHANDIGARH ──────────────────────────────────────────────────────────────
  CH: [
    ['Chandigarh',30.7333,76.7794],['Manimajra',30.7167,76.8167],
    ['Panchkula',30.6942,76.8606],['Mohali',30.7046,76.7179],
    ['Zirakpur',30.6444,76.8178],
  ],
 
  // ── CHHATTISGARH ────────────────────────────────────────────────────────────
  CG: [
    ['Raipur',21.2514,81.6296],['Bhilai',21.2094,81.4285],
    ['Bilaspur',22.0797,82.1391],['Korba',22.3595,82.7501],
    ['Durg',21.1904,81.2849],['Rajnandgaon',21.0971,81.0336],
    ['Jagdalpur',19.0716,82.0219],['Ambikapur',23.1200,83.2000],
    ['Raigarh',21.9000,83.3958],['Kawardha',22.0167,81.2333],
    ['Kanker',20.2667,81.4833],['Kondagaon',19.5967,81.6635],
    ['Dantewada',18.8967,81.3483],['Narayanpur',19.6967,81.2485],
    ['Bijapur',18.8333,80.8167],['Sukma',18.3833,81.6667],
    ['Balod',20.7167,81.2167],['Baloda Bazar',21.6567,82.1619],
    ['Bemetara',21.7167,81.5333],['Gariaband',20.6333,82.0667],
    ['Mahasamund',21.1058,82.0978],['Dhamtari',20.7083,81.5483],
    ['Kabirdham',22.0167,81.2333],['Mungeli',22.0667,81.6833],
    ['Janjgir',22.0167,82.5667],['Jashpur',22.8833,84.1333],
    ['Korea',23.3000,82.7000],['Surajpur',23.2167,82.8667],
    ['Surguja',23.1200,83.2000],['Balrampur',23.5333,83.5833],
    ['Manendragarh',23.2167,82.2167],['Baikunthpur',23.2500,82.5500],
    ['Champa',22.0333,82.6500],['Sakti',22.0167,82.9667],
    ['Patan',21.7167,81.7000],
  ],
 
  // ── DADRA & NAGAR HAVELI / DAMAN & DIU ─────────────────────────────────────
  DD: [
    ['Silvassa',20.2766,73.0169],['Daman',20.3974,72.8328],
    ['Diu',20.7144,70.9874],['Amli',20.2667,73.0333],
    ['Naroli',20.3167,73.0167],['Khanvel',20.3500,73.0167],
  ],
 
  // ── DELHI ───────────────────────────────────────────────────────────────────
  DL: [
    ['New Delhi',28.6139,77.2090],['Old Delhi',28.6562,77.2308],
    ['Dwarka',28.5920,77.0460],['Rohini',28.7361,77.1180],
    ['Janakpuri',28.6215,77.0824],['Pitampura',28.7008,77.1399],
    ['Saket',28.5244,77.2090],['Lajpat Nagar',28.5700,77.2400],
    ['Nehru Place',28.5494,77.2522],['Karol Bagh',28.6514,77.1909],
    ['Connaught Place',28.6304,77.2177],['Chandni Chowk',28.6507,77.2300],
    ['Shahdara',28.6700,77.2900],['Preet Vihar',28.6433,77.3014],
    ['Mayur Vihar',28.6083,77.2967],['Vasant Kunj',28.5200,77.1570],
    ['Sarojini Nagar',28.5757,77.1981],['Narela',28.8500,77.1000],
    ['Bawana',28.7833,77.0333],['Alipur',28.7833,77.1333],
    ['Najafgarh',28.6083,76.9833],['Palam',28.5953,77.1000],
    ['Mehrauli',28.5200,77.1900],['Chattarpur',28.4976,77.1677],
    ['Badarpur',28.5031,77.2973],['Okhla',28.5500,77.2700],
    ['Mustafabad',28.7167,77.2833],['Seelampur',28.6667,77.2833],
    ['Burari',28.7333,77.1833],['Timarpur',28.7167,77.2167],
    ['Model Town',28.7208,77.1933],['Wazirabad',28.7500,77.2333],
    ['Badli',28.7500,77.1333],['Bhalaswa',28.7500,77.1667],
    ['Mustafabad',28.7167,77.2833],['Shakarpur',28.6333,77.2833],
  ],
 
  // ── GOA ─────────────────────────────────────────────────────────────────────
  GA: [
    ['Panaji',15.4989,73.8278],['Margao',15.2832,73.9862],
    ['Vasco da Gama',15.3960,73.8145],['Mapusa',15.5918,73.8145],
    ['Ponda',15.4024,74.0154],['Bicholim',15.5921,73.9627],
    ['Calangute',15.5440,73.7540],['Candolim',15.5183,73.7600],
    ['Colva',15.2792,73.9226],['Anjuna',15.5773,73.7403],
    ['Arambol',15.6843,73.7048],['Morjim',15.6343,73.7333],
    ['Pernem',15.7208,73.8000],['Sanguem',15.2333,74.1333],
    ['Quepem',15.2108,74.0708],['Canacona',14.9836,74.0324],
    ['Valpoi',15.5167,74.1333],['Sanquelim',15.5667,74.0000],
    ['Curchorem',15.2500,74.1000],['Verna',15.3667,73.9500],
  ],
 
  // ── GUJARAT ─────────────────────────────────────────────────────────────────
  GJ: [
    ['Ahmedabad',23.0225,72.5714],['Surat',21.1702,72.8311],
    ['Vadodara',22.3072,73.1812],['Rajkot',22.3039,70.8022],
    ['Bhavnagar',21.7645,72.1519],['Jamnagar',22.4707,70.0577],
    ['Junagadh',21.5222,70.4579],['Gandhinagar',23.2156,72.6369],
    ['Anand',22.5645,72.9289],['Navsari',20.9467,72.9520],
    ['Morbi',22.8173,70.8375],['Surendranagar',22.7272,71.6480],
    ['Bharuch',21.7051,72.9959],['Amreli',21.6040,71.2210],
    ['Porbandar',21.6422,69.6093],['Mehsana',23.5879,72.3693],
    ['Gandhidham',23.0793,70.1337],['Valsad',20.5992,72.9342],
    ['Patan',23.8493,72.1266],['Palanpur',24.1720,72.4380],
    ['Godhra',22.7768,73.6143],['Nadiad',22.6915,72.8634],
    ['Dahod',22.8363,74.2584],['Himatnagar',23.6015,72.9647],
    ['Kalol',23.2500,72.5000],['Botad',22.1693,71.6650],
    ['Dwarka',22.2394,68.9678],['Somnath',20.8880,70.4010],
    ['Veraval',20.9070,70.3670],['Bhuj',23.2420,69.6669],
    ['Anjar',23.1167,70.0167],['Mundra',22.8400,69.7167],
    ['Mandvi',22.8333,69.3500],['Vapi',20.3718,72.9054],
    ['Silvassa (border)',20.2766,73.0169],['Vyara',21.1042,73.3960],
    ['Tapi',21.1042,73.3960],['Bardoli',21.1209,73.1115],
    ['Karjan',22.0500,73.1167],['Khambhat',22.3233,72.6175],
    ['Kapadvanj',23.0167,73.0667],['Dholka',22.7167,72.4667],
    ['Dholera',22.2500,72.2000],['Sanand',22.9833,72.3833],
    ['Deesa',24.2564,72.1860],['Radhanpur',23.8340,71.5980],
    ['Unjha',23.8001,72.3948],['Kadi',23.2985,72.3316],
    ['Vijapur',23.5667,72.7500],['Idar',23.8333,73.0000],
    ['Modasa',23.4667,73.3000],['Lunawada',23.1320,73.6167],
    ['Halol',22.5000,73.4667],['Kalol Panch',22.6000,73.4500],
    ['Chhota Udaipur',22.3000,74.0167],['Jetpur',21.7502,70.6241],
    ['Gondal',21.9614,70.8016],['Wankaner',22.6100,71.0000],
    ['Dhrangadhra',22.9900,71.4700],['Limbdi',22.5667,71.8333],
    ['Wadhwan',22.7000,71.6833],['Sidhpur',23.9214,72.3760],
    ['Chanasma',23.7167,72.1000],['Harij',23.6833,71.9000],
    ['Visnagar',23.7000,72.5333],['Becharaji',23.6333,72.3667],
  ],
 
  // ── HARYANA ─────────────────────────────────────────────────────────────────
  HR: [
    ['Faridabad',28.4089,77.3178],['Gurgaon',28.4595,77.0266],
    ['Panipat',29.3909,76.9635],['Ambala',30.3752,76.7821],
    ['Yamunanagar',30.1290,77.2674],['Rohtak',28.8955,76.6066],
    ['Hisar',29.1492,75.7217],['Karnal',29.6857,76.9905],
    ['Sonipat',28.9931,77.0151],['Panchkula',30.6942,76.8606],
    ['Bhiwani',28.7960,76.1315],['Sirsa',29.5365,75.0211],
    ['Bahadurgarh',28.6920,76.9257],['Jind',29.3167,76.3167],
    ['Thanesar',29.9741,76.8177],['Kaithal',29.8014,76.3997],
    ['Rewari',28.1900,76.6200],['Palwal',28.1434,77.3313],
    ['Fatehabad',29.5122,75.4559],['Jhajjar',28.6100,76.6600],
    ['Mewat',27.9833,77.0000],['Mahendragarh',28.2736,76.1452],
    ['Charkhi Dadri',28.5917,76.2716],['Nuh',28.1042,77.0001],
    ['Narnaul',28.0500,76.1100],['Narwana',29.6000,76.1167],
    ['Tohana',29.6980,75.9120],['Pehowa',29.9800,76.5767],
    ['Hansi',29.1020,75.9660],['Siwani',28.9560,75.6260],
    ['Kalanaur',29.7833,76.4333],['Gharaunda',29.5333,76.9667],
    ['Taraori',29.7167,76.8667],['Assandh',29.5000,76.5667],
    ['Gohana',29.1333,76.7000],['Rai',28.9667,77.0167],
    ['Mundlana',29.1000,77.0833],['Samalkha',29.2333,76.9667],
    ['Hodal',27.8973,77.3718],['Hathin',27.9000,77.2500],
    ['Punhana',28.1000,77.0500],['Firozpur Jhirka',27.7833,77.0833],
    ['Manesar',28.3560,76.9368],['Dharuhera',28.2150,76.7913],
    ['Bawal',28.0745,76.5782],['Kosli',28.4167,76.4000],
    ['Nangal Chaudhary',28.0600,76.3900],['Mohindergarh',28.2736,76.1452],
  ],
 
  // ── HIMACHAL PRADESH ────────────────────────────────────────────────────────
  HP: [
    ['Shimla',31.1048,77.1734],['Solan',30.9045,77.0967],
    ['Dharamsala',32.2190,76.3234],['Mandi',31.7081,76.9318],
    ['Kullu',31.9579,77.1095],['Hamirpur HP',31.6862,76.5212],
    ['Una',31.4683,76.2699],['Chamba',32.5530,76.1233],
    ['Bilaspur HP',31.3337,76.7607],['Nahan',30.5598,77.2958],
    ['Palampur',32.1096,76.5353],['Nurpur',32.3000,75.8833],
    ['Dalhousie',32.5370,75.9750],['Kangra',32.0992,76.2680],
    ['Yol',32.1167,76.1833],['Jogindernagar',31.9955,76.7888],
    ['Sundernagar',31.5344,76.8982],['Rampur HP',31.4500,77.6333],
    ['Reckong Peo',31.5333,78.2667],['Sangla',31.4167,78.1833],
    ['Sarahan',31.5167,77.7833],['Arki',30.9667,76.9667],
    ['Baddi',30.9500,76.7833],['Nalagarh',31.0437,76.7229],
    ['Kasauli',30.8950,76.9625],['Subathu',30.9167,77.0000],
    ['Kufri',31.0987,77.2627],['Manali',32.2432,77.1892],
    ['Rohtang',32.3687,77.2427],['Keylong',32.5667,77.0333],
    ['Lahaul',32.5000,77.0000],['Spiti',31.9167,78.0000],
    ['Kaza',32.2271,78.0714],['Narkanda',31.2703,77.4494],
    ['Rohru',31.2000,77.7500],['Kotkhai',31.1167,77.5833],
  ],
 
  // ── JAMMU & KASHMIR ─────────────────────────────────────────────────────────
  JK: [
    ['Srinagar',34.0836,74.7973],['Jammu',32.7266,74.8570],
    ['Anantnag',33.7311,75.1487],['Baramulla',34.2093,74.3432],
    ['Sopore',34.3000,74.4667],['Kathua',32.3836,75.5157],
    ['Udhampur',32.9167,75.1333],['Rajouri',33.3786,74.3089],
    ['Punch',33.7700,74.1000],['Doda',33.1500,75.5500],
    ['Kishtwar',33.3167,75.7667],['Bhaderwah',32.9833,75.7167],
    ['Katra',32.9853,74.9321],['Akhnoor',32.8667,74.7333],
    ['Samba',32.5667,75.1167],['Hiranagar',32.4500,75.2667],
    ['Ramban',33.2381,75.2236],['Banihal',33.4833,75.2000],
    ['Kulgam',33.6500,75.0167],['Pulwama',33.8742,74.8980],
    ['Shopian',33.7167,74.8333],['Ganderbal',34.2272,74.7778],
    ['Budgam',33.9167,74.7167],['Bandipore',34.4167,74.6500],
    ['Gurez',34.6167,74.8333],['Kupwara',34.5333,74.2667],
    ['Handwara',34.3989,74.2781],['Tangmarg',34.0500,74.4333],
    ['Pahalgam',34.0167,75.3167],['Gulmarg',34.0500,74.3833],
  ],
 
  // ── JHARKHAND ───────────────────────────────────────────────────────────────
  JH: [
    ['Ranchi',23.3441,85.3096],['Jamshedpur',22.8046,86.2029],
    ['Dhanbad',23.7957,86.4304],['Bokaro',23.6693,85.9915],
    ['Deoghar',24.4853,86.6951],['Hazaribagh',23.9925,85.3637],
    ['Giridih',24.1900,86.3000],['Ramgarh',23.6333,85.5167],
    ['Lohardaga',23.4333,84.6833],['Simdega',22.6167,84.5167],
    ['Gumla',23.0500,84.5333],['Dumka',24.2667,87.2500],
    ['Pakur',24.6333,87.8500],['Godda',24.8167,87.2167],
    ['Sahibganj',25.2500,87.6333],['Koderma',24.4667,85.6000],
    ['Chaibasa',22.5500,85.8167],['Seraikela',22.6000,85.9833],
    ['Khunti',23.0667,85.2833],['Palamu',24.0333,84.0833],
    ['Garhwa',24.1667,83.8167],['Chatra',24.2167,84.8667],
    ['Latehar',23.7333,84.5000],['Chakradharpur',22.6833,85.6167],
    ['Jhuriu',23.6667,85.9833],['Phusro',23.7775,86.4222],
    ['Sindri',23.7699,86.4259],['Nirsa',23.8333,86.5667],
    ['Jharia',23.7500,86.4333],['Baghmara',23.7333,86.5333],
    ['Chas',23.6333,86.0000],['Bermo',23.7667,85.9833],
    ['Petarbar',23.5667,84.9000],['Macluskieganj',23.5000,85.3333],
  ],
 
  // ── KARNATAKA ───────────────────────────────────────────────────────────────
  KA: [
    ['Bengaluru',12.9716,77.5946],['Mysuru',12.2958,76.6394],
    ['Hubballi',15.3647,75.1240],['Mangaluru',12.9141,74.8560],
    ['Belagavi',15.8497,74.4977],['Davangere',14.4644,75.9218],
    ['Ballari',15.1394,76.9214],['Tumkur',13.3379,77.1010],
    ['Udupi',13.3409,74.7421],['Shivamogga',13.9299,75.5681],
    ['Raichur',16.2120,77.3566],['Bidar',17.9104,77.5199],
    ['Vijayapura',16.8302,75.7100],['Kalaburagi',17.3297,76.8343],
    ['Dharwad',15.4589,75.0078],['Hassan',13.0068,76.1004],
    ['Chitradurga',14.2251,76.3980],['Gadag',15.4167,75.6333],
    ['Bagalkote',16.1833,75.7000],['Koppal',15.3500,76.1500],
    ['Mandya',12.5244,76.8962],['Chikkamagaluru',13.3161,75.7720],
    ['Kodagu',12.4244,75.7382],['Chamarajanagar',11.9237,76.9438],
    ['Chikkaballapura',13.4355,77.7315],['Ramanagara',12.7157,77.2823],
    ['Kolar',13.1368,78.1292],['Yadgir',16.7636,77.1384],
    ['Haveri',14.7941,75.4025],['Uttara Kannada',14.7993,74.1389],
    ['Karwar',14.8013,74.1288],['Ankola',14.6667,74.3000],
    ['Kumta',14.4167,74.4167],['Honavar',14.2833,74.4500],
    ['Sirsi',14.6211,74.8371],['Yellapur',14.9667,74.7167],
    ['Haliyal',15.3333,74.7500],['Dandeli',15.2667,74.6167],
    ['Dharmasthala',12.9667,75.3833],['Sullia',12.5593,75.3865],
    ['Puttur',12.7624,75.2018],['Bantwal',12.8897,75.0258],
    ['Moodbidri',13.0667,74.9833],['Kundapura',13.6284,74.6897],
    ['Byndoor',13.8667,74.6667],['Manipal',13.3500,74.7833],
    ['Karkala',13.2141,74.9929],['Brahmavar',13.4333,74.7667],
    ['Sagara',14.1667,75.0333],['Thirthahalli',13.6833,75.2333],
    ['Soraba',14.0000,75.0833],['Shimoga',13.9299,75.5681],
    ['Tarikere',13.7167,75.8167],['Kadur',13.5500,76.0167],
    ['Arsikere',13.3131,76.2519],['Tiptur',13.2500,76.4833],
    ['Turuvekere',13.1667,76.6667],['Sira',13.7500,76.9000],
    ['Madhugiri',13.6667,77.2167],['Koratagere',13.5167,77.2333],
    ['Mulbagal',13.1612,78.3891],['Bangarpet',12.9833,78.1833],
    ['Robertsonpet',12.9636,78.2749],['Bethamangala',13.2167,77.9667],
    ['Srinivaspur',13.3333,78.2167],['Pavagada',14.0948,77.2760],
    ['Gauribidanur',13.6104,77.5169],['Doddaballapura',13.2951,77.5369],
    ['Nelamangala',13.1000,77.4000],['Devanahalli',13.2487,77.7096],
    ['Hoskote',13.0702,77.7980],['Malur',13.0040,77.9363],
    ['Channapatna',12.6520,77.2049],['Maddur',12.5833,77.0500],
    ['Malavalli',12.3833,77.0500],['Krishnarajpet',12.6628,76.4872],
    ['Pandavapura',12.4833,76.6667],['Nagamangala',12.8167,76.7500],
    ['Belur',13.1622,75.8620],['Alur',13.0500,75.9833],
    ['Sakleshpur',12.9450,75.7867],['Mudigere',13.1333,75.6333],
    ['Koppa',13.5333,75.3667],['Sringeri',13.4167,75.2500],
  ],
 
  // ── KERALA ──────────────────────────────────────────────────────────────────
  KL: [
    ['Thiruvananthapuram',8.5241,76.9366],['Kochi',9.9312,76.2673],
    ['Kozhikode',11.2588,75.7804],['Thrissur',10.5276,76.2144],
    ['Kollam',8.8932,76.6141],['Palakkad',10.7867,76.6548],
    ['Alappuzha',9.4981,76.3388],['Malappuram',11.0730,76.0740],
    ['Kannur',11.8745,75.3704],['Kottayam',9.5916,76.5222],
    ['Kasaragod',12.4996,74.9869],['Pathanamthitta',9.2648,76.7870],
    ['Idukki',9.9189,76.9613],['Wayanad',11.6854,76.1320],
    ['Ernakulam',9.9816,76.2999],['Munnar',10.0889,77.0595],
    ['Thrissur City',10.5276,76.2144],['Palakkad City',10.7867,76.6548],
    ['Manjeri',11.1209,76.1195],['Tirur',10.9099,75.9225],
    ['Kalpetta',11.6099,76.0825],['Mananthavady',11.8009,76.0048],
    ['Sulthan Bathery',11.6576,76.2574],['Thalassery',11.7511,75.4921],
    ['Payyanur',12.0983,75.2020],['Iritty',11.8980,75.5256],
    ['Vatakara',11.5983,75.5915],['Koyilandy',11.4416,75.7065],
    ['Feroke',11.1726,75.8565],['Beypore',11.1657,75.8100],
    ['Erattupetta',9.6806,76.7840],['Pala',9.7087,76.6882],
    ['Changanacherry',9.4466,76.5404],['Vaikom',9.7500,76.3833],
    ['Ettumanoor',9.6667,76.5667],['Thiruvalla',9.3831,76.5734],
    ['Adoor',9.1500,76.7333],['Pandalam',9.2167,76.6667],
    ['Kayamkulam',9.1729,76.5018],['Mavelikkara',9.2680,76.5545],
    ['Chengannur',9.3167,76.6167],['Haripad',9.2333,76.4833],
    ['Karunagappalli',9.0621,76.5279],['Kottarakkara',9.0000,76.7667],
    ['Punalur',9.0167,76.9167],['Anchal',8.9000,76.9000],
    ['Varkala',8.7333,76.7167],['Attingal',8.6948,76.8119],
    ['Neyyattinkara',8.4016,77.0877],['Nagercoil (Kerala border)',8.1799,77.4303],
    ['Kazhakkoottam',8.5667,76.9167],['Peroorkada',8.5667,76.9167],
    ['Kowdiar',8.5333,76.9500],['Killippalam',8.5333,76.9500],
    ['Pongummoodu',8.5000,76.9167],['Kanjirappally',9.5500,76.7833],
    ['Thodupuzha',9.8893,76.7149],['Muvattupuzha',9.9833,76.5833],
    ['Aluva',10.1064,76.3519],['Angamaly',10.1978,76.3849],
    ['Perumbavoor',10.1136,76.4717],['Kothamangalam',10.0585,76.6272],
    ['Mala',10.3667,76.5167],['Irinjalakuda',10.3426,76.2120],
    ['Kodungallur',10.2349,76.1944],['Chalakudy',10.3000,76.3333],
    ['Guruvayur',10.5941,76.0479],['Kunnamkulam',10.6500,76.0833],
    ['Chavakkad',10.5667,76.0167],['Ponnani',10.7714,75.9195],
    ['Perinthalmanna',10.9778,76.2264],['Tiruvambadi',11.2833,76.0000],
  ],
 
  // ── LADAKH ──────────────────────────────────────────────────────────────────
  LA: [
    ['Leh',34.1526,77.5771],['Kargil',34.5539,76.1349],
    ['Diskit',34.7333,77.5833],['Padum',33.4667,76.9167],
    ['Nubra',34.7000,77.5500],['Zanskar',33.5000,76.8333],
    ['Drass',34.4333,75.7667],['Sankoo',34.3667,76.3000],
    ['Turtuk',34.8493,76.8369],['Nyoma',33.1833,78.6833],
    ['Hanle',32.7833,78.9667],['Tangtse',34.0167,78.2833],
  ],
 
  // ── LAKSHADWEEP ─────────────────────────────────────────────────────────────
  LD: [
    ['Kavaratti',10.5626,72.6369],['Agatti',10.8595,72.1905],
    ['Andrott',10.8233,73.6672],['Minicoy',8.2885,73.0431],
    ['Amini',11.1233,72.7392],['Kiltan',11.4783,73.0022],
    ['Kadmat',11.2333,72.7833],['Chetlat',11.6883,72.7135],
    ['Bitra',11.5958,72.1767],
  ],
 
  // ── MADHYA PRADESH ──────────────────────────────────────────────────────────
  MP: [
    ['Bhopal',23.2599,77.4126],['Indore',22.7196,75.8577],
    ['Jabalpur',23.1815,79.9864],['Gwalior',26.2183,78.1828],
    ['Ujjain',23.1765,75.7885],['Sagar MP',23.8388,78.7378],
    ['Dewas',22.9676,76.0534],['Satna',24.5793,80.8322],
    ['Ratlam',23.3315,75.0367],['Rewa',24.5362,81.3036],
    ['Murwara',23.8413,80.3973],['Singrauli',24.1985,82.6720],
    ['Burhanpur',21.3089,76.2282],['Khandwa',21.8234,76.3527],
    ['Bhind',26.5608,78.7890],['Shivpuri',25.4270,77.6580],
    ['Vidisha',23.5228,77.8098],['Chhindwara',22.0572,78.9327],
    ['Chhatarpur',24.9184,79.5874],['Damoh',23.8334,79.4406],
    ['Mandsaur',24.0766,75.0702],['Neemuch',24.4760,74.8713],
    ['Pithampur',22.6151,75.6932],['Hoshangabad',22.7500,77.7333],
    ['Itarsi',22.6154,77.7611],['Seoni',22.0877,79.5347],
    ['Balaghat',21.8131,80.1870],['Tikamgarh',24.7486,78.8296],
    ['Betul',21.9078,77.9003],['Raisen',23.3311,77.7893],
    ['Rajgarh',23.6440,76.7310],['Shajapur',23.4278,76.2774],
    ['Agar Malwa',23.7104,76.0161],['Ashok Nagar',24.5785,77.7310],
    ['Guna',24.6481,77.3164],['Sheopur',25.6666,76.7013],
    ['Morena',26.4959,78.0006],['Datia',25.6644,78.4637],
    ['Panna',24.7200,80.1847],['Umaria',23.5280,80.8345],
    ['Katni',23.8332,80.3948],['Dindori',22.9500,81.0833],
    ['Mandla',22.5971,80.3753],['Anuppur',23.1040,81.6930],
    ['Shahdol',23.2970,81.3539],['Sidhi',24.4172,81.8802],
    ['Alirajpur',22.3044,74.3544],['Jhabua',22.7672,74.5997],
    ['Dhar',22.5983,75.2985],['Khargone',21.8227,75.6160],
    ['Barwani',22.0344,74.9008],['Harda',22.3400,77.0900],
  ],
 
  // ── MAHARASHTRA ─────────────────────────────────────────────────────────────
  MH: [
    ['Mumbai',19.0760,72.8777],['Pune',18.5204,73.8567],
    ['Nagpur',21.1458,79.0882],['Nashik',19.9975,73.7898],
    ['Aurangabad',19.8762,75.3433],['Solapur',17.6805,75.9064],
    ['Thane',19.2183,72.9781],['Navi Mumbai',19.0330,73.0297],
    ['Kolhapur',16.7050,74.2433],['Amravati',20.9320,77.7523],
    ['Nanded',19.1383,77.3210],['Akola',20.7002,77.0082],
    ['Latur',18.4088,76.5604],['Dhule',20.9042,74.7749],
    ['Sangli',16.8524,74.5815],['Malegaon',20.5579,74.5089],
    ['Jalgaon',21.0077,75.5626],['Bhiwandi',19.2967,73.0631],
    ['Vasai-Virar',19.3919,72.8397],['Kalyan',19.2403,73.1305],
    ['Ulhasnagar',19.2167,73.1500],['Ahmednagar',19.0952,74.7498],
    ['Parbhani',19.2704,76.7763],['Satara',17.6805,74.0183],
    ['Jalna',19.8410,75.8857],['Osmanabad',18.1813,76.0386],
    ['Bid',18.9870,75.7560],['Buldana',20.5290,76.1840],
    ['Hingoli',19.7194,77.1500],['Washim',20.1109,77.1337],
    ['Yavatmal',20.3888,78.1204],['Wardha',20.7453,78.6022],
    ['Bhandara',21.1667,79.6500],['Chandrapur',19.9556,79.2961],
    ['Gadchiroli',20.1809,80.0038],['Gondia',21.4632,80.1960],
    ['Raigad',18.5167,73.1167],['Ratnagiri',16.9902,73.3120],
    ['Sindhudurg',16.3500,73.7333],['Palghar',19.6967,72.7676],
    ['Washim',20.1109,77.1337],['Lonavala',18.7481,73.4073],
    ['Khandala',18.7601,73.3899],['Matheran',18.9820,73.2680],
    ['Mahabaleshwar',17.9237,73.6571],['Panchgani',17.9237,73.8000],
    ['Alibag',18.6411,72.8725],['Murud',18.3267,72.9637],
    ['Ganpatipule',17.1453,73.2680],['Dapoli',17.7667,73.1833],
    ['Chiplun',17.5333,73.5167],['Sawantwadi',15.9054,73.8225],
    ['Kudal',16.0219,73.6857],['Vengurla',15.8667,73.6333],
    ['Malvan',16.0607,73.4657],['Devrukh',17.0539,73.6167],
    ['Sangamner',19.5769,74.2097],['Nevasa',19.5667,74.9833],
    ['Shrirampur',19.6217,74.6614],['Kopargaon',19.9042,74.4769],
    ['Manmad',20.2519,74.4361],['Nandurbar',21.3667,74.2333],
    ['Shahada',21.5500,74.4833],['Navapur',21.1667,73.7833],
    ['Dondaicha',21.3333,74.5500],['Shirpur',21.3500,74.8833],
    ['Chopda',21.2500,75.3000],['Amalner',21.0500,75.0667],
    ['Pachora',20.6500,75.3333],['Jamner',20.8000,75.7833],
    ['Bhadgaon',20.8833,75.6333],['Raver',21.2500,76.0500],
    ['Muktainagar',21.0333,75.6000],['Mehkar',20.1497,76.5689],
    ['Sindkhed Raja',20.4333,76.4833],['Lonar',20.0833,76.5167],
    ['Khamgaon',20.7018,76.5680],['Shegaon',20.7991,76.6945],
    ['Malkapur',20.8833,76.2167],['Nandura',20.8333,76.4667],
    ['Motala',20.6500,76.3500],['Jalgaon Jamod',20.9000,76.5333],
    ['Risod',20.1167,76.9167],['Mangrulpir',20.3167,77.3167],
    ['Pusad',19.9167,77.5833],['Umarkhed',19.6000,77.6833],
    ['Wani',20.0500,78.9500],['Arni',20.2833,78.6833],
    ['Pulgaon',20.7167,78.3000],['Deoli',20.6667,78.4667],
    ['Hinganghat',20.5500,78.8333],['Selu',19.4667,76.2167],
    ['Jintur',19.6000,76.7333],['Parbhani City',19.2704,76.7763],
    ['Manwath',19.3000,76.5000],['Ambajogai',18.7333,76.3833],
    ['Kaij',18.8333,76.0333],['Dharur',18.8167,76.2167],
    ['Ausa',18.2500,76.5000],['Barshi',18.2361,75.6958],
    ['Pandharpur',17.6780,75.3249],['Mangalvedhe',17.5167,75.4833],
    ['Karmala',18.4000,75.1833],['Mohol',17.9333,75.7167],
    ['Akkalkot',17.4333,76.2000],['Tuljapur',17.9667,76.0667],
    ['Omerga',18.0500,76.0500],['Kallam',17.9833,76.3500],
  ],
 
  // ── MANIPUR ─────────────────────────────────────────────────────────────────
  MN: [
    ['Imphal',24.8170,93.9368],['Thoubal',24.6333,93.9833],
    ['Bishnupur',24.6167,93.7667],['Churachandpur',24.3333,93.6833],
    ['Senapati',25.2667,94.0167],['Ukhrul',25.1000,94.3667],
    ['Tamenglong',24.9833,93.5333],['Chandel',24.3333,93.9833],
    ['Jiribam',24.8000,93.1167],['Moreh',24.2167,94.2667],
    ['Kakching',24.5000,94.0167],['Tengnoupal',24.2333,94.0167],
    ['Kamjong',25.1167,94.5667],['Noney',24.7667,93.5333],
    ['Pherzawl',24.0833,93.2667],['Kangpokpi',25.1167,93.9667],
  ],
 
  // ── MEGHALAYA ───────────────────────────────────────────────────────────────
  ML: [
    ['Shillong',25.5788,91.8933],['Tura',25.5140,90.2160],
    ['Jowai',25.4500,92.2000],['Nongstoin',25.5167,91.2667],
    ['Williamnagar',25.4833,90.6167],['Baghmara',25.2000,90.6333],
    ['Resubelpara',25.8667,90.6167],['Khliehriat',25.3500,92.3500],
    ['Cherrapunji',25.2833,91.7167],['Mawsynram',25.2833,91.5833],
    ['Dawki',25.2000,92.0167],['Nongpoh',25.9000,92.0000],
    ['Mairang',25.5333,91.6667],['Nongkrem',25.5000,91.8667],
    ['Sohra',25.2833,91.7167],
  ],
 
  // ── MIZORAM ─────────────────────────────────────────────────────────────────
  MZ: [
    ['Aizawl',23.7271,92.7176],['Lunglei',22.8874,92.7456],
    ['Saiha',22.4833,92.9667],['Champhai',23.4667,93.3167],
    ['Kolasib',24.2167,92.6833],['Serchhip',23.3167,92.8500],
    ['Lawngtlai',22.5000,92.9000],['Mamit',23.9333,92.4833],
    ['Siaha',22.4833,92.9667],['Khawzawl',23.6167,93.2167],
    ['Saitual',23.7833,93.0500],['Hnahthial',22.9833,92.8500],
  ],
 
  // ── NAGALAND ────────────────────────────────────────────────────────────────
  NL: [
    ['Kohima',25.6751,94.1086],['Dimapur',25.9044,93.7276],
    ['Mokokchung',26.3333,94.5167],['Tuensang',26.2667,94.8333],
    ['Wokha',26.1000,94.2667],['Zunheboto',25.9833,94.5167],
    ['Phek',25.6667,94.4667],['Mon',26.7333,95.0000],
    ['Longleng',26.5167,94.9833],['Kiphire',25.9000,94.9333],
    ['Peren',25.5167,93.7333],['Noklak',26.7000,95.3667],
  ],
 
  // ── ODISHA ──────────────────────────────────────────────────────────────────
  OD: [
    ['Bhubaneswar',20.2961,85.8245],['Cuttack',20.4625,85.8830],
    ['Rourkela',22.2604,84.8536],['Brahmapur',19.3150,84.7941],
    ['Sambalpur',21.4669,83.9756],['Puri',19.8135,85.8312],
    ['Balasore',21.4942,86.9334],['Bhadrak',21.0548,86.4994],
    ['Baripada',21.9333,86.7167],['Jharsuguda',21.8569,84.0052],
    ['Jeypore',18.8570,82.5716],['Bargarh',21.3333,83.6167],
    ['Sundargarh',22.1167,84.0333],['Kendujhar',21.6333,85.5333],
    ['Angul',20.8500,85.1000],['Dhenkanal',20.6572,85.5979],
    ['Jajapur',20.8500,86.3333],['Khordha',20.1833,85.6167],
    ['Nayagarh',20.1167,85.1167],['Ganjam',19.3833,85.0500],
    ['Phulbani',20.4833,84.2333],['Kalahandi',19.9167,83.1667],
    ['Nuapada',20.5333,82.5333],['Bolangir',20.7000,83.4833],
    ['Sonepur',20.8333,83.9167],['Boudh',20.8500,84.3333],
    ['Koraput',18.8133,82.7124],['Malkangiri',18.3500,81.8833],
    ['Nabarangpur',19.2333,82.5500],['Rayagada',19.1667,83.4167],
    ['Gajapati',19.3833,84.1000],['Kandhamal',20.4833,84.2333],
    ['Kendrapara',20.5000,86.4333],['Jagatsinghpur',20.2667,86.1833],
    ['Puri City',19.8135,85.8312],['Subarnapur',20.8333,83.9167],
    ['Deogarh',21.5333,84.7333],['Mayurbhanj',22.1333,86.6833],
    ['Keonjhar',21.6333,85.5333],['Debagarh',21.5333,84.7333],
  ],
 
  // ── PUDUCHERRY ──────────────────────────────────────────────────────────────
  PY: [
    ['Puducherry',11.9416,79.8083],['Karaikal',10.9254,79.8380],
    ['Mahe',11.7015,75.5307],['Yanam',16.7333,82.2167],
    ['Ozhukara',11.9667,79.7833],['Villianur',11.9167,79.7833],
    ['Ariyankuppam',11.8833,79.8167],['Kalapet',11.9833,79.8667],
  ],
 
  // ── PUNJAB ──────────────────────────────────────────────────────────────────
  PB: [
    ['Ludhiana',30.9010,75.8573],['Amritsar',31.6340,74.8723],
    ['Jalandhar',31.3260,75.5762],['Patiala',30.3398,76.3869],
    ['Bathinda',30.2110,74.9455],['Mohali',30.7046,76.7179],
    ['Hoshiarpur',31.5143,75.9115],['Pathankot',32.2643,75.6565],
    ['Moga',30.8167,75.1667],['Abohar',30.1500,74.2000],
    ['Malerkotla',30.5333,75.8833],['Khanna',30.7035,76.2169],
    ['Phagwara',31.2167,75.7667],['Muktsar',30.4737,74.5147],
    ['Fazilka',30.4000,74.0167],['Ferozpur',30.9333,74.6167],
    ['Kapurthala',31.3786,75.3786],['Sangrur',30.2500,75.8333],
    ['Barnala',30.3800,75.5500],['Fatehgarh Sahib',30.6500,76.3833],
    ['Rupnagar',30.9681,76.5236],['Nawanshahr',31.1239,76.1153],
    ['Gurdaspur',32.0397,75.4028],['Tarn Taran',31.4500,74.9333],
    ['Mansa',29.9833,75.3833],['Faridkot',30.6667,74.7500],
    ['Firozpur Cantt',30.9333,74.6167],['Dera Baba Nanak',32.0333,75.0333],
    ['Anandpur Sahib',31.2333,76.5000],['Ropar',30.9681,76.5236],
    ['Morinda',30.7915,76.4986],['Chamkaur Sahib',30.8892,76.4039],
    ['Kiratpur Sahib',31.1797,76.5608],['Balachaur',31.2083,76.2708],
    ['Garhshankar',31.2167,76.1500],['Nawanshahr Town',31.1239,76.1153],
    ['Nangal',31.3850,76.3744],['Fatehpur Sahib',30.6500,76.3833],
    ['Rajpura',30.4833,76.6000],['Lalru',30.5000,76.7167],
    ['Dera Bassi',30.5833,76.8333],['Banur',30.5500,76.6667],
    ['Zirakpur',30.6444,76.8178],['Kharar',30.7452,76.6441],
    ['Kurali',30.8333,76.5333],['Sirhind',30.6333,76.3833],
    ['Bassi Pathana',30.6667,76.3167],['Amloh',30.6000,76.2333],
    ['Dhuri',30.3667,75.8667],['Moonak',29.8333,75.9000],
    ['Sunam',30.1333,75.7833],['Dirba',29.9833,75.8333],
    ['Lehra Gaga',29.9500,75.9833],['Nabha',30.3667,76.1500],
    ['Samana',30.1333,76.1833],['Ghanaur',30.2500,76.3000],
  ],
 
  // ── RAJASTHAN ───────────────────────────────────────────────────────────────
  RJ: [
    ['Jaipur',26.9124,75.7873],['Jodhpur',26.2389,73.0243],
    ['Kota',25.2138,75.8648],['Bikaner',28.0229,73.3119],
    ['Ajmer',26.4499,74.6399],['Udaipur',24.5854,73.7125],
    ['Bhilwara',25.3478,74.6313],['Alwar',27.5530,76.6346],
    ['Bharatpur',27.2152,77.4938],['Sikar',27.6094,75.1399],
    ['Sri Ganganagar',29.9038,73.8772],['Jhunjhunu',28.1286,75.3990],
    ['Pali',25.7726,73.3233],['Nagaur',27.2044,73.7284],
    ['Tonk',26.1665,75.7897],['Dausa',26.8878,76.3334],
    ['Sawai Madhopur',26.0145,76.3509],['Karauli',26.4873,77.0223],
    ['Dhaulpur',26.7009,77.8967],['Baran',25.1037,76.5149],
    ['Jhalawar',24.5943,76.1620],['Kota City',25.2138,75.8648],
    ['Bundi',25.4393,75.6461],['Chittorgarh',24.8887,74.6269],
    ['Rajsamand',25.0600,73.8841],['Dungarpur',23.8432,73.7133],
    ['Banswara',23.5460,74.4399],['Pratapgarh RJ',24.0333,74.7833],
    ['Sirohi',24.8840,72.8587],['Mount Abu',24.5927,72.7156],
    ['Jalor',25.3500,72.6167],['Barmer',25.7500,71.3833],
    ['Jaisalmer',26.9157,70.9083],['Bikaner City',28.0229,73.3119],
    ['Hanumangarh',29.5833,74.3333],['Churu',28.2983,74.9686],
    ['Sujangarh',27.7000,74.4667],['Ratangarh',28.0500,74.6167],
    ['Sardarshahar',28.4333,74.4833],['Nohar',29.1833,74.7667],
    ['Bhadra',29.0833,75.1667],['Pilibanga',29.4500,74.0833],
    ['Suratgarh',29.3167,73.9000],['Rawatsar',29.2667,74.3833],
    ['Anupgarh',29.2000,72.6167],['Raisinghnagar',29.5333,73.4500],
    ['Padampur',29.1167,73.5833],['Gharsana',29.5833,73.0167],
    ['Kesrisinghpur',29.5167,73.7833],['Hindumalkot',28.8500,73.3500],
    ['Fatehpur',27.9908,74.9551],['Khandela',27.6167,75.5167],
    ['Neem Ka Thana',27.7406,75.7924],['Losal',27.4000,75.0333],
    ['Shrimadhopur',27.4667,75.0833],['Khetri',28.0000,75.7833],
    ['Pilani',28.3639,75.6050],['Chirawa',28.2333,75.7000],
    ['Nawalgarh',27.8585,75.2748],['Ramgarh',27.2667,75.5667],
    ['Mandawa',28.0500,75.1333],['Laxmangarh',27.8333,75.0333],
    ['Lachhmangarh',27.8333,75.0333],['Srimadhopur',27.4667,75.0833],
    ['Kishangarh',26.5986,74.8648],['Nasirabad',26.3167,74.7333],
    ['Beawar',26.1018,74.3225],['Kekri',25.9833,75.1500],
    ['Merta',26.6500,74.0333],['Degana',26.9167,73.9000],
    ['Makrana',27.0333,74.7333],['Nawa City',26.7167,75.0000],
    ['Phulera',26.8667,75.2333],['Chomu',27.1667,75.7167],
    ['Shahpura',27.3833,75.9667],['Viratnagar',27.6333,76.0333],
    ['Kotputli',27.7058,76.1993],['Bansur',27.6833,76.3333],
    ['Thanagazi',27.4167,76.5333],['Sariska',27.3833,76.3833],
    ['Tijara',27.9330,76.8278],['Kherli',27.6000,76.6833],
    ['Laxmangarh',27.8333,76.3167],['Ramgarh Alwar',27.2667,76.6167],
    ['Kishangarh Bas',27.8333,77.0167],['Nagar',27.4333,77.1000],
    ['Deeg',27.4725,77.3424],['Kumher',27.3000,77.4000],
    ['Nadbai',27.2000,77.1833],['Weir',27.1167,77.1000],
    ['Bayana',26.9067,77.2965],['Hindaun',26.7313,77.0425],
    ['Gangapur City',26.4717,76.7115],['Bonli',26.0333,76.5667],
    ['Bhandarej',26.6667,76.3833],['Lalsot',26.5667,76.3500],
    ['Baswa',27.2333,76.4833],['Bandikui',27.0500,76.5667],
    ['Mahua',27.0167,76.3333],['Sikandra',26.7167,76.8333],
    ['Mitrapura',26.6833,76.5000],['Nadoti',26.6667,76.6167],
    ['Khandar',25.7333,76.7167],['Sheopur Kalan',25.6667,76.7013],
    ['Baran City',25.1037,76.5149],['Kelwara',25.0333,76.6167],
    ['Kishanganj RJ',24.8833,76.5000],['Shahabad',24.7333,76.9167],
    ['Ramganj Mandi',24.6500,75.9667],['Sangod',24.5500,75.8667],
    ['Kaithoon',24.9333,75.7167],['Itawa',25.0167,76.0667],
    ['Pipalda',25.2167,75.7833],['Borkhera',25.2167,75.8500],
    ['Ladpura',25.2333,75.8333],
  ],
 
  // ── SIKKIM ──────────────────────────────────────────────────────────────────
  SK: [
    ['Gangtok',27.3389,88.6065],['Namchi',27.1663,88.3647],
    ['Geyzing',27.3333,88.2667],['Mangan',27.5167,88.5167],
    ['Jorethang',27.1000,88.3167],['Ravangla',27.3000,88.3500],
    ['Pelling',27.3000,88.2333],['Yuksom',27.3333,88.2167],
    ['Lachen',27.7333,88.5500],['Lachung',27.6833,88.7500],
    ['Nathula',27.3667,88.8333],['Rangpo',27.1667,88.5333],
    ['Singtam',27.2333,88.5000],['Rongli',27.2000,88.7167],
    ['Pakyong',27.2333,88.6333],['Dentam',27.3333,88.1833],
  ],
 
  // ── TAMIL NADU ──────────────────────────────────────────────────────────────
  TN: [
    ['Chennai',13.0827,80.2707],['Coimbatore',11.0168,76.9558],
    ['Madurai',9.9252,78.1198],['Tiruchirappalli',10.7905,78.7047],
    ['Salem',11.6643,78.1460],['Tirunelveli',8.7139,77.7567],
    ['Vellore',12.9165,79.1325],['Erode',11.3410,77.7172],
    ['Thoothukudi',8.7642,78.1348],['Dindigul',10.3673,77.9803],
    ['Thanjavur',10.7870,79.1378],['Tirupur',11.1085,77.3411],
    ['Cuddalore',11.7480,79.7680],['Kanchipuram',12.8333,79.7000],
    ['Nagercoil',8.1799,77.4303],['Kumbakonam',10.9602,79.3845],
    ['Tiruvannamalai',12.2253,79.0747],['Sivakasi',9.4568,77.7997],
    ['Karur',10.9601,78.0766],['Rajapalayam',9.4562,77.5562],
    ['Namakkal',11.2196,78.1677],['Ambattur',13.1143,80.1548],
    ['Avadi',13.1000,80.0833],['Kancheepuram',12.8333,79.7000],
    ['Tambaram',12.9249,80.1000],['Chengalpet',12.6921,79.9759],
    ['Ariyalur',11.1400,79.0780],['Perambalur',11.2333,78.8833],
    ['Villupuram',11.9399,79.4933],['Kallakurichi',11.7385,78.9548],
    ['Ranipet',12.9227,79.3326],['Tirupattur',12.4969,78.5667],
    ['Krishnagiri',12.5186,78.2137],['Dharmapuri',12.1281,78.1631],
    ['Hosur',12.7376,77.8295],['The Nilgiris',11.4000,76.7000],
    ['Udhagamandalam',11.4102,76.6950],['Kotagiri',11.4247,76.8558],
    ['Gudalur',11.5000,76.4833],['Coonoor',11.3527,76.7950],
    ['Palani',10.4501,77.5230],['Theni',10.0167,77.4833],
    ['Bodinayakanur',10.0167,77.3500],['Periyakulam',10.1167,77.5500],
    ['Andipatti',10.0000,77.6333],['Usilampatti',9.9667,77.8000],
    ['Melur',10.0500,78.3500],['Tirumangalam',9.8167,77.9833],
    ['Virudhunagar',9.5806,77.9563],['Sattur',9.3667,77.9167],
    ['Srivilliputhur',9.5092,77.6245],['Sankarankoil',9.1833,77.5500],
    ['Tenkasi',8.9590,77.3148],['Shenkottai',8.9833,77.2500],
    ['Courtallam',8.9167,77.2833],['Valliyur',8.3833,77.6167],
    ['Nanguneri',8.4833,77.6500],['Tiruvallur',13.1451,79.9143],
    ['Ponneri',13.3391,80.1993],['Gummidipoondi',13.4038,80.1186],
    ['Thiruvottiyur',13.1500,80.3000],['Ennore',13.2167,80.3167],
    ['Tiruvottriyur',13.1500,80.3000],['Perambur',13.1167,80.2500],
    ['Poonamallee',13.0500,80.1167],['Pallavaram',12.9693,80.1495],
    ['Chromepet',12.9500,80.1500],['Sholinganallur',12.9000,80.2333],
    ['Velachery',13.0000,80.2167],['Adyar',13.0067,80.2572],
    ['Mylapore',13.0333,80.2667],['Egmore',13.0833,80.2667],
    ['T Nagar',13.0417,80.2333],['Anna Nagar',13.0850,80.2101],
    ['Guindy',13.0100,80.2167],['Alandur',13.0000,80.2000],
    ['Maraimalai Nagar',12.7957,80.0257],['Chengalpattu City',12.6921,79.9759],
    ['Madurantakam',12.4929,79.8864],['Tindivanam',12.2500,79.6500],
    ['Sankarapuram',11.9000,79.0000],['Ulundurpet',11.6833,79.3167],
    ['Vriddachalam',11.5000,79.3167],['Chidambaram',11.3994,79.6938],
    ['Sirkazhi',11.2333,79.7667],['Mayiladuthurai',11.1028,79.6514],
    ['Nagapattinam',10.7651,79.8427],['Vedaranyam',10.3787,79.8505],
    ['Thiruvarur',10.7718,79.6368],['Papanasam',10.9167,79.2667],
    ['Kuttalam',11.0000,79.5167],['Orathanadu',10.7500,79.3000],
    ['Pattukottai',10.4333,79.3167],['Aranthangi',10.1833,79.0833],
    ['Karaikudi',10.0728,78.7720],['Devakottai',9.9500,78.8333],
    ['Ramanathapuram',9.3739,78.8347],['Paramakudi',9.5167,78.5833],
    ['Rameswaram',9.2881,79.3129],['Mandapam',9.2833,79.1167],
    ['Kilakkarai',9.2333,78.8000],['Sayalkudi',9.1500,78.4833],
    ['Uchipuli',9.1500,79.1000],['Ervadi',9.1167,78.7500],
    ['Thiruchendur',8.4920,78.1218],['Mukkudal',8.6833,77.3000],
    ['Ambasamudram',8.7000,77.4500],['Cheranmahadevi',8.6833,77.5000],
    ['Alangulam',8.8167,77.5500],['Sivagiri',8.9333,77.7167],
    ['Keeranur',10.3333,78.7833],['Marungapuri',10.9500,78.2667],
    ['Aravakurichi',10.7000,78.0833],['Kulithalai',10.9333,78.4167],
    ['Musiri',10.9333,78.4333],['Lalgudi',10.8667,78.8167],
    ['Manachanallur',10.8000,78.8333],['Srirangam',10.8637,78.6897],
    ['Thiruverumbur',10.8333,78.7667],['Ariyamangalam',10.9167,78.6500],
    ['Kovilpatti',9.1722,77.8715],['Ettayapuram',9.0833,78.0000],
    ['Ottapidaram',8.8833,77.9833],['Kayathar',8.9500,77.9333],
    ['Eral',8.7833,78.0333],['Nazareth',8.9000,78.0833],
    ['Ilaiyangudi',9.6667,78.6167],['Manamadurai',9.7167,78.4667],
    ['Sivaganga',9.8469,78.4810],['Ilayangudi',9.6667,78.6167],
    ['Tirupattur TN',12.4969,78.5667],
    ['Ambur',12.7934,78.7152],['Vaniyambadi',12.6833,78.6167],
    ['Jolarpet',12.5635,78.5766],['Gudiyatham',12.9437,78.8724],
    ['Arakkonam',13.0833,79.6667],['Sholavandan',10.0833,78.1167],
    ['Natham',10.1833,78.0000],['Batlagundu',10.1333,77.7500],
    ['Oddanchatram',10.1667,77.7167],['Vedasandur',10.5333,77.9333],
    ['Dharapuram',10.7333,77.5167],['Uthamapalayam',9.8000,77.3333],
    ['Kumili',9.6833,77.1667],['Cumbum',9.7333,77.2833],
    ['Gudalur Nilgiris',11.5000,76.4833],
  ],
 
  // ── TELANGANA ───────────────────────────────────────────────────────────────
  TG: [
    ['Hyderabad',17.3850,78.4867],['Warangal',17.9689,79.5941],
    ['Nizamabad',18.6725,78.0941],['Karimnagar',18.4386,79.1288],
    ['Khammam',17.2473,80.1514],['Ramagundam',18.7543,79.4728],
    ['Nalgonda',17.0500,79.2667],['Adilabad',19.6667,78.5333],
    ['Suryapet',17.1428,79.6228],['Miryalaguda',16.8667,79.5667],
    ['Mancherial',18.8685,79.4560],['Jagtial',18.7933,78.9167],
    ['Sircilla',18.3833,78.8333],['Kamareddy',18.3167,78.3333],
    ['Sangareddy',17.6167,78.0833],['Medak',18.0500,78.2667],
    ['Siddipet',18.1012,78.8528],['Jogulamba',16.5000,77.5000],
    ['Narayanpet',16.7408,77.4972],['Wanaparthy',16.3643,78.0606],
    ['Gadwal',16.2343,77.7995],['Nagarkurnool',16.4822,78.3235],
    ['Mahabubnagar',16.7333,77.9833],['Kothagudem',17.5500,80.6167],
    ['Bhadrachalam',17.6700,80.8933],['Yellandu',17.5933,80.3150],
    ['Palwancha',17.6167,80.7000],['Asifabad',19.3667,79.2833],
    ['Bhainsa',18.9333,77.9667],['Nirmal',19.1000,78.3500],
    ['Mudhole',18.9833,78.0000],['Bellampalli',18.9500,79.5000],
    ['Mancherial City',18.8685,79.4560],['Luxettipet',18.8833,79.5833],
    ['Chennur',18.8500,79.5333],['Bhupalpally',18.4167,79.9833],
    ['Mulugu',18.1833,79.9333],['Mahabubabad',17.5897,80.0028],
    ['Narsampet',17.9333,79.9000],['Jangaon',17.7333,79.1500],
    ['Ghanpur Station',17.9833,79.4167],['Palakurthi',17.7833,79.5500],
    ['Narasimhapeta',18.0500,79.4667],['Parkal',18.2000,79.7167],
    ['Hasanparthy',18.0000,79.5667],['Kazipet',17.9833,79.5333],
    ['Narsapur TG',17.6000,78.2833],['Ameerpet',17.4333,78.4500],
    ['LB Nagar',17.3500,78.5500],['Uppal',17.4000,78.5583],
    ['Kukatpally',17.4833,78.4000],['Secunderabad',17.4500,78.5000],
    ['Malkajgiri',17.4500,78.5167],['Alwal',17.5000,78.5000],
    ['Ghatkesar',17.4333,78.6833],['Medchal',17.6333,78.4833],
    ['Shamirpet',17.6333,78.5167],['Kompally',17.5667,78.4500],
    ['Nizampet',17.5167,78.3833],['Miyapur',17.5000,78.3667],
    ['Patancheru',17.5333,78.2667],['Toopran',18.0333,78.4833],
    ['Andole',17.9333,77.9167],['Narayankhed',17.7500,77.6833],
    ['Zahirabad',17.6833,77.6000],['Sadasivpet',17.6167,77.9667],
    ['Jogipet',17.8333,77.9667],['Banswada',18.3833,77.8667],
    ['Bodhan',18.6667,77.9000],['Armoor',18.7833,78.2833],
    ['Varni',18.7333,77.9333],['Dichpally',18.5667,78.0167],
    ['Yellareddy',18.1833,78.0167],['Koratla',18.8167,78.7167],
    ['Metpally',18.8333,78.5833],['Peddapalli',18.6167,79.3833],
    ['Sultanabad',18.5167,79.3500],['Huzurabad',18.2000,79.4167],
    ['Vemulawada',18.4608,79.5345],['Husnabad',18.2167,79.3667],
    ['Sircilla City',18.3833,78.8333],['Saidapur',18.1167,79.0667],
    ['Choppadandi',18.2667,79.1333],['Shankarapatnam',18.4500,79.2167],
  ],
 
  // ── TRIPURA ─────────────────────────────────────────────────────────────────
  TR: [
    ['Agartala',23.8315,91.2868],['Dharmanagar',24.3797,92.1680],
    ['Udaipur TR',23.5333,91.4833],['Kailasahar',24.3333,92.0000],
    ['Belonia',23.2500,91.4500],['Sabroom',23.0167,91.7333],
    ['Ambassa',23.9333,91.8167],['Kumarghat',24.1167,92.0167],
    ['Kamalpur',24.2000,91.8500],['Sonamura',23.4667,91.2667],
    ['Melaghar',23.6167,91.4000],['Khowai',24.0667,91.6000],
    ['Teliamura',23.7500,91.5333],['Mohanpur',23.6667,91.2000],
    ['Bishalgarh',23.6833,91.3833],['Amarpur TR',23.5167,91.6500],
    ['Gomati',23.3833,91.6333],['Sepahijala',23.6833,91.4000],
    ['Unakoti',24.3333,92.0000],
  ],
 
  // ── UTTAR PRADESH ───────────────────────────────────────────────────────────
  UP: [
    ['Lucknow',26.8467,80.9462],['Kanpur',26.4499,80.3319],
    ['Ghaziabad',28.6692,77.4538],['Agra',27.1767,78.0081],
    ['Meerut',28.9845,77.7064],['Varanasi',25.3176,82.9739],
    ['Prayagraj',25.4358,81.8463],['Bareilly',28.3670,79.4304],
    ['Aligarh',27.8974,78.0880],['Moradabad',28.8386,78.7733],
    ['Noida',28.5355,77.3910],['Firozabad',27.1592,78.3957],
    ['Mathura',27.4924,77.6737],['Saharanpur',29.9680,77.5510],
    ['Gorakhpur',26.7606,83.3732],['Jhansi',25.4484,78.5685],
    ['Muzaffarnagar',29.4727,77.7085],['Hapur',28.7296,77.7757],
    ['Ayodhya',26.7922,82.1998],['Vrindavan',27.5806,77.7003],
    ['Shahjahanpur',27.8833,79.9053],['Rampur UP',28.8118,79.0241],
    ['Amroha',28.9039,78.4670],['Etawah',26.7745,79.0201],
    ['Sambhal',28.5878,78.5698],['Badaun',28.0318,79.1283],
    ['Lakhimpur Kheri',27.9476,80.7826],['Hardoi',27.3982,80.1322],
    ['Fatehpur',25.9300,80.8172],['Unnao',26.5479,80.4882],
    ['Rae Bareli',26.2294,81.2410],['Sitapur',27.5600,80.6800],
    ['Barabanki',26.9386,81.1851],['Gonda',27.1333,81.9667],
    ['Faizabad',26.7751,82.1477],['Sultanpur',26.2648,82.0730],
    ['Ambedkar Nagar',26.4389,82.5458],['Basti',26.8000,82.7000],
    ['Sant Kabir Nagar',27.0000,83.0333],['Maharajganj',27.1167,83.5667],
    ['Kushinagar',26.7406,83.8960],['Deoria',26.5000,83.7833],
    ['Azamgarh',26.0667,83.1833],['Mau',25.9392,83.5613],
    ['Ballia',25.7500,84.1500],['Ghazipur',25.5829,83.5762],
    ['Jaunpur',25.7459,82.6844],['Mirzapur',25.1453,82.5739],
    ['Sonbhadra',24.6872,82.7751],['Allahabad (Prayagraj City)',25.4358,81.8463],
    ['Kaushambi',25.5167,81.3833],['Pratapgarh UP',25.8961,81.9801],
    ['Chitrakoot',25.1832,80.8553],['Banda',25.4767,80.3358],
    ['Hamirpur UP',25.9522,80.1459],['Mahoba',25.2933,79.8709],
    ['Lalitpur',24.6877,78.4167],['Jalaun',26.1461,79.3294],
    ['Orai',25.9795,79.4488],['Etah',27.5595,78.6636],
    ['Mainpuri',27.2281,79.0230],['Kannauj',27.0548,79.9212],
    ['Auraiya',26.4656,79.5123],['Kanpur Dehat',26.4135,79.9027],
    ['Farrukhabad',27.3893,79.5747],['Bulandshahr',28.4082,77.8504],
    ['Khurja',28.2527,77.8553],['Sikandra Rao',27.6975,78.3985],
    ['Iglas',27.7210,77.9594],['Hathras',27.5887,78.0603],
    ['Kasganj',27.8100,78.6600],['Shikohabad',27.0977,78.5875],
    ['Tundla',27.2103,78.2511],['Ferozabad',27.1592,78.3957],
    ['Gabhana',27.8903,77.6475],['Mursan',27.5731,77.9611],
    ['Sadabad',27.4382,78.0439],['Jalesar',27.1525,78.3048],
    ['Jasrana',27.2258,78.5300],['Sirsaganj',27.0583,78.6847],
    ['Mahuwa',27.0333,78.6500],['Aliganj',27.4900,79.1774],
    ['Tilhar',27.9667,79.7333],['Powayan',27.8667,79.8500],
    ['Jalalabad UP',30.1667,77.3833],['Deoband',29.6982,77.6779],
    ['Roorkee',29.8543,77.8880],['Nakur',29.9167,77.3000],
    ['Gangoh',29.7833,77.2833],['Nanauta',29.6667,77.4667],
    ['Haridwar',29.9457,78.1642],['Rishikesh',30.0869,78.2676],
    ['Muzaffarnagar City',29.4727,77.7085],['Kairana',29.3950,77.1900],
    ['Thana Bhawan',29.5833,77.3667],['Shamli',29.4500,77.3000],
    ['Budhana',29.2833,77.4667],['Jansath',29.3167,77.8500],
    ['Khatauli',29.2833,77.7333],['Bijnor',29.3725,78.1344],
    ['Nagina',29.4427,78.4369],['Najibabad',29.6133,78.3478],
    ['Dhampur',29.3088,78.5029],['Nehtaur',29.6167,78.2500],
    ['Chandpur',29.1444,78.2703],['Kiratpur',29.5333,78.2333],
    ['Seohara',29.2146,78.5680],['Afzalgarh',29.2833,78.6167],
    ['Ujhani',28.3561,79.0099],['Milak',28.3906,79.3286],
    ['Bilari',28.6167,78.8000],['Thakurdwara',29.0781,78.7036],
    ['Moradabad City',28.8386,78.7733],['Chandausi',28.4544,78.7779],
    ['Sambhal City',28.5878,78.5698],['Bahjoi',28.5167,78.6167],
    ['Gajraula',28.8667,78.2833],['Dhanaura',28.9167,78.2333],
    ['Hasanpur',28.7167,78.2833],['Joya',28.9167,78.4167],
    ['Loni',28.7518,77.2889],['Modi Nagar',28.8270,77.5476],
    ['Pilkhuwa',28.6917,77.6567],['Dasna',28.7000,77.5500],
    ['Muradnagar',28.7761,77.4999],['Nandgaon',28.8333,77.7167],
    ['Dibai',28.2156,78.2513],['Sikandrabad',28.4526,77.6955],
    ['Jewar',28.1500,77.5667],['Khair',27.9411,77.8389],
    ['Tappal',27.7694,78.0125],['Atrauli',27.9312,78.2862],
    ['Gabhana UP',27.8903,77.6475],['Fatehabad UP',27.3833,77.3333],
    ['Iglas Town',27.7210,77.9594],
    ['Kanth',28.9000,78.6000],['Bilaspur UP',29.1167,78.8333],
    ['Rampur City',28.8118,79.0241],['Milak UP',28.3906,79.3286],
    ['Shahabad UP',27.6500,79.9333],['Tilhar City',27.9667,79.7333],
    ['Khutar',28.1167,80.3167],['Powayan UP',27.8667,79.8500],
    ['Puwayan',27.9000,80.1167],['Shahjahanpur City',27.8833,79.9053],
    ['Jalalabad Shahjahanpur',27.7167,79.7667],['Banda City',25.4767,80.3358],
    ['Naraini',25.1833,80.4833],['Mau Aima',25.4333,81.8833],
    ['Phulpur',25.5498,82.0880],['Handia',25.3667,82.2000],
    ['Sirsa UP',25.8667,81.8333],['Soraon',25.5000,81.8333],
    ['Jhunsi',25.3833,81.9500],['Mirzapur City',25.1453,82.5739],
    ['Chunar',25.1267,82.8872],['Robertsganj',24.6872,83.0615],
    ['Chopan',24.5167,83.0667],['Obra',24.4500,82.9833],
    ['Renukoot',24.2047,83.0494],['Anpara',24.2167,82.7833],
    ['Pipri',24.3833,82.9167],['Dudhi',24.2167,83.2667],
  ],
 
  // ── UTTARAKHAND ─────────────────────────────────────────────────────────────
  UK: [
    ['Dehradun',30.3165,78.0322],['Haridwar',29.9457,78.1642],
    ['Roorkee',29.8543,77.8880],['Haldwani',29.2183,79.5130],
    ['Rudrapur',28.9770,79.4026],['Rishikesh',30.0869,78.2676],
    ['Nainital',29.3803,79.4636],['Mussoorie',30.4598,78.0644],
    ['Almora',29.5971,79.6591],['Pithoragarh',29.5833,80.2167],
    ['Chamoli',30.3833,79.3167],['Rudraprayag',30.2833,78.9833],
    ['Pauri',30.1500,78.7833],['Tehri',30.3833,78.4833],
    ['Uttarkashi',30.7167,78.4500],['Champawat',29.3333,80.1000],
    ['Bageshwar',29.8333,79.7667],['Dharchula',29.8500,80.5333],
    ['Munsiari',30.0667,80.2333],['Kashipur',29.2144,78.9644],
    ['Ramnagar',29.3948,79.1236],['Tanakpur',29.0667,80.1167],
    ['Champawat City',29.3333,80.1000],['Lohaghat',29.4000,80.0667],
    ['Kotdwara',29.7500,78.5167],['Lansdowne',29.8381,78.6822],
    ['Srinagar Garhwal',30.2167,78.7833],['Devprayag',30.1333,78.5833],
    ['Gopeshwar',30.3833,79.3167],['Joshimath',30.5546,79.5602],
    ['Badrinath',30.7433,79.4932],['Kedarnath',30.7333,79.0667],
    ['Gaurikund',30.6500,79.0167],['Ukhimath',30.5667,79.2167],
    ['Augustmuni',30.3667,78.9667],['Guptakashi',30.5333,79.1167],
    ['Trijuginarayan',30.6667,79.0833],['Chopta',30.5167,79.2833],
    ['Ghansali',30.4333,78.6667],['Pratapnagar',30.3667,78.5000],
    ['Narendranagar',30.1667,78.3000],['Chamba UK',30.3333,78.3833],
    ['Dhanaulti',30.4370,78.2508],['Chakrata',30.7000,77.8667],
    ['Herbertpur',30.4167,77.8833],['Vikasnagar',30.4633,77.7748],
    ['Kalsi',30.5333,77.8333],['Sahaspur',30.3000,77.9833],
    ['Doiwala',30.1833,78.1167],['Raipur UK',30.2333,78.0500],
    ['Raiwala',29.9833,78.0833],['Laksar',29.7500,77.9833],
    ['Landhaura',29.8500,77.9000],['Manglaur',29.7833,77.9333],
    ['Jwalapur',29.8833,78.0833],['Bhagwanpur',29.8833,77.9500],
    ['Bahdrabad',29.8500,78.0167],['Kankhal',29.9167,78.1500],
    ['Shyampur',29.9167,78.1000],['Bhimtal',29.3500,79.5667],
    ['Ramgarh',29.2833,79.3833],['Mukteshwar',29.4667,79.6333],
    ['Ranikhet',29.6479,79.4339],['Dwahali',29.6333,79.3667],
    ['Chaukhutia',29.8667,79.5333],['Garur',29.9833,79.7000],
    ['Bageshwar City',29.8333,79.7667],['Kapkot',30.0000,80.0167],
    ['Berinag',29.8167,80.0667],['Gangolihat',29.7500,80.0000],
    ['Pithoragarh City',29.5833,80.2167],['Didihat',29.7833,80.3000],
    ['Askot',29.7667,80.4167],['Dharchula City',29.8500,80.5333],
  ],
 
  // ── WEST BENGAL ─────────────────────────────────────────────────────────────
  WB: [
    ['Kolkata',22.5726,88.3639],['Howrah',22.5958,88.2636],
    ['Asansol',23.6889,86.9661],['Siliguri',26.7271,88.3953],
    ['Durgapur',23.4800,87.3119],['Bardhaman',23.2324,87.8615],
    ['Malda',25.0108,88.1406],['Baharampur',24.1040,88.2520],
    ['Kharagpur',22.3460,87.2320],['Haldia',22.0667,88.0800],
    ['Raiganj',25.6108,88.1241],['Balurghat',25.2167,88.7833],
    ['Bankura',23.2333,87.0667],['Purulia',23.3333,86.3667],
    ['Jalpaiguri',26.5167,88.7167],['Coochbehar',26.3333,89.4500],
    ['Alipurduar',26.4833,89.5167],['Darjeeling',27.0360,88.2627],
    ['Kalimpong',27.0667,88.4667],['Kurseong',26.8833,88.2667],
    ['Mirik',26.9000,88.1833],['Gangtok (border)',27.3389,88.6065],
    ['Krishnanagar',23.4000,88.5000],['Ranaghat',23.1833,88.5500],
    ['Chakdaha',23.0833,88.5167],['Kalyani',22.9750,88.4333],
    ['Haringhata',22.9333,88.5667],['Bizpur',22.9000,88.5000],
    ['Barasat',22.7205,88.4796],['Basirhat',22.6573,88.8685],
    ['Baduria',22.7367,88.7876],['Habra',22.8328,88.6546],
    ['Bangaon',23.0500,88.8333],['Bongaon',23.0500,88.8333],
    ['North 24 Parganas (Barasat)',22.7205,88.4796],
    ['Diamond Harbour',22.1917,88.1833],['Kakdwip',21.8785,88.1811],
    ['Namkhana',21.7667,88.2500],['Sagar Island',21.6500,88.0833],
    ['Budge Budge',22.4797,88.1756],['Maheshtala',22.5000,88.2333],
    ['Rajpur',22.4500,88.3833],['Baruipur',22.3642,88.4282],
    ['Sonarpur',22.4333,88.4167],['Narendrapur',22.4333,88.4000],
    ['Garia',22.4667,88.3833],['Regent Park',22.4833,88.3833],
    ['Behala',22.5000,88.3000],['Jadavpur',22.5000,88.3833],
    ['Salt Lake',22.5833,88.4167],['New Town Kolkata',22.6000,88.4667],
    ['Rajarhat',22.6167,88.4667],['Madhyamgram',22.7000,88.4333],
    ['Birati',22.6833,88.4333],['Belgharia',22.6667,88.3833],
    ['Kamarhati',22.6667,88.3667],['Titagarh',22.7333,88.3833],
    ['Panihati',22.6833,88.3667],['Khardaha',22.7167,88.3833],
    ['Naihati',22.8833,88.4167],['Bhatpara',22.8667,88.4000],
    ['Garulia',22.8500,88.3833],['Noapara',22.8333,88.4167],
    ['Kanchrapara',22.9500,88.4333],['Halisahar',22.9500,88.4333],
    ['Shyamnagar',22.9833,88.3833],['Hooghly',22.9000,88.3833],
    ['Serampore',22.7500,88.3333],['Chandannagar',22.8667,88.3667],
    ['Baidyabati',22.8000,88.3167],['Uttarpara',22.6667,88.3500],
    ['Konnagar',22.6833,88.3500],['Rishra',22.7167,88.3500],
    ['Sreerampur',22.7500,88.3333],['Bandel',23.0167,88.3667],
    ['Chinsurah',22.9000,88.3833],['Chandan Nagar',22.8667,88.3667],
    ['Tribeni',22.9833,88.4000],['Haripal',22.9667,88.1833],
    ['Singur',22.8167,88.2333],['Polba',22.9667,88.2500],
    ['Memari',23.2333,88.1000],['Katwa',23.6475,88.1336],
    ['Kalna',23.2167,88.3667],['Manteswar',23.4167,88.0333],
    ['Ausgram',23.5667,87.8667],['Galsi',23.4000,87.7167],
    ['Faridpur',23.3333,87.8167],['Jamalpur',23.4333,87.9833],
    ['Bhatar',23.3500,87.9333],['Kanksa',23.5167,87.3500],
    ['Andal',23.6000,87.2000],['Kulti',23.7278,86.8493],
    ['Raniganj',23.6167,87.1167],['Jamuria',23.6667,87.0500],
    ['Neamatpur',23.7500,86.9167],['Barakar',23.7833,86.9667],
    ['Chittaranjan',23.8667,86.8833],['Barjora',23.3667,87.1167],
    ['Bishnupur WB',23.0833,87.3167],['Sonamukhi',23.3000,87.4167],
    ['Indus',23.1833,87.3000],['Khatra',23.1333,86.8500],
    ['Raipur WB',23.0833,87.2167],['Hirbandh',23.1667,86.6833],
    ['Taldangra',23.3333,87.0000],['Simlapal',23.2000,87.0000],
    ['Ranibandh',23.0000,86.7833],['Joypur',23.2167,87.2167],
    ['Manbazar',23.0667,86.6500],['Arsha',23.4000,86.3333],
    ['Balarampur PU',23.3667,86.2500],['Kashipur PU',23.3500,86.3167],
    ['Bagmundi',23.2833,86.5667],['Puncha',23.4667,86.5833],
    ['Jhalda',23.3833,86.4833],['Hura',23.4000,86.8167],
    ['Barabazar',23.4000,86.7000],['Para',23.5500,87.0833],
    ['Raghunathpur PU',23.5500,86.6667],['Santaldih',23.4833,86.5667],
    ['Neturia',23.5000,86.8167],['Salanpur',23.7000,86.9500],
    ['Barasat UP (Burdwan)',23.5833,87.6667],
    ['Memari UP',23.2333,88.1000],
    ['Nalhati',24.2884,87.8652],['Rampurhat',24.1719,87.7853],
    ['Suri',23.9167,87.5167],['Bolpur',23.6667,87.7167],
    ['Shantiniketan',23.6833,87.6833],['Labhpur',23.8500,87.8333],
    ['Illambazar',23.7833,87.6167],['Dubrajpur',23.8000,87.3833],
    ['Siuri',23.9167,87.5167],['Khoyrasol',23.9333,87.5500],
    ['Rajnagar WB',23.7333,87.8000],['Murarai',24.4667,87.8167],
    ['Harishchandrapur',25.3000,88.3667],['Chanchal',25.3833,88.0500],
    ['Gazole',25.0500,88.4167],['Habibpur',25.1833,88.2833],
    ['Bamongola',25.1500,88.4333],['Old Malda',25.0333,88.1500],
    ['English Bazar',25.0108,88.1406],['Kaliachak',24.9333,88.0833],
    ['Manickchak',25.1000,87.9500],['Ratua',24.7333,88.1000],
    ['Farakka',24.8000,87.9167],['Samsherganj',24.7167,88.0167],
    ['Jangipur',24.4667,88.0833],['Lalgola',24.4167,88.2500],
    ['Murshidabad',24.1833,88.2667],['Jiaganj',24.2167,88.2500],
    ['Azimganj',24.2333,88.2333],['Kandi',23.9500,88.0333],
    ['Berhampore',24.1040,88.2520],['Hariharpara',23.8667,88.4167],
    ['Plassey',23.8167,88.2500],['Beldanga',23.9333,88.3167],
    ['Berhampur UP',24.1040,88.2520],
    ['Islampur WB',26.2624,88.1956],['Dalkhola',25.8667,87.8333],
    ['Kishanganj WB border',26.1056,87.9417],
    ['Chopra',26.3333,88.2000],['Goalpokhar',26.1667,88.0833],
    ['Karandighi',26.0667,88.1000],['Hemtabad',25.9000,88.1333],
    ['Itahar',25.5500,88.1833],['Kaliaganj',25.6333,88.3333],
    ['Raiganj City',25.6108,88.1241],['Buniadpur',25.6500,88.0333],
    ['Gangarampur',25.4000,88.5167],['Tapan',25.2333,88.6000],
    ['Hili',25.2833,88.9667],['Kumargram',26.5833,89.7167],
    ['Dhupguri',26.5833,89.0167],['Mainaguri',26.5833,88.8167],
    ['Falakata',26.5167,89.2000],['Madarihat',26.6333,89.5333],
    ['Dinhata',26.1333,89.4667],['Mekhliganj',26.3333,89.7333],
    ['Sitai',26.0500,89.2833],['Tufanganj',26.3167,89.6667],
    ['Mathabhanga',26.3333,89.2167],['Changrabandha',26.3500,89.6333],
  ],
 
  // ── ANDAMAN & NICOBAR ───────────────────────────────────────────────────────
  AN: [
    ['Port Blair',11.6234,92.7265],['Diglipur',13.2600,92.9800],
    ['Rangat',12.5200,92.5800],['Mayabunder',12.9300,92.7500],
    ['Ferrargunj',11.5800,92.7300],['Hut Bay',10.6000,92.5400],
    ['Car Nicobar',9.1700,92.8200],['Nancowry',8.0000,93.5500],
    ['Campbell Bay',6.9700,93.9200],['Bamboo Flat',11.6200,92.6900],
    ['Nimbudera',11.6000,92.7200],['Garacharma',11.5800,92.7100],
  ],
};
 

function onStateChange() {
  const state = document.getElementById('stateSelect').value;
  const citySelect = document.getElementById('citySelect');
  citySelect.innerHTML = '<option value="">— Select City —</option>';
  if (!state || !STATE_CITIES[state]) {
    citySelect.disabled = true;
    return;
  }
  STATE_CITIES[state].forEach(([name]) => {
    const opt = document.createElement('option');
    opt.value = name;
    opt.textContent = name;
    citySelect.appendChild(opt);
  });
  citySelect.disabled = false;
}
 
async function onCitySelect() {
  const state = document.getElementById('stateSelect').value;
  const cityName = document.getElementById('citySelect').value;
  if (!cityName || !state || !STATE_CITIES[state]) return;

  const cityData = STATE_CITIES[state].find(([n]) => n === cityName);
  if (cityData) {
    const [name, lat, lon] = cityData;
    document.getElementById('lat').value = lat.toFixed(4);
    document.getElementById('lon').value = lon.toFixed(4);
    document.getElementById('utcOffset').value = '5.5';
    document.getElementById('cityInput').value = name;
    document.getElementById('cityStatus').textContent = '✓';

    // ← ADD THESE LINES
    const coordsRow = document.getElementById('coordsRow');
    if (coordsRow) {
      coordsRow.style.display = 'flex';
      document.getElementById('latDisplay').textContent = lat.toFixed(4) + '°N';
      document.getElementById('lonDisplay').textContent = lon.toFixed(4) + '°E';
      document.getElementById('geoStatusMsg').textContent = '✓ ' + name;
    }
  }
}
 
/* ─── DD/MM/YYYY ↔ ISO date sync ───────────────────────────────── */
function displayDateToISO(dmy) {
  // accepts DD/MM/YYYY or DD-MM-YYYY
  const parts = dmy.trim().split(/[\/\-]/);
  if (parts.length !== 3) return '';
  const [dd, mm, yyyy] = parts.map(p => p.trim().padStart(2, '0'));
  if (!dd || !mm || !yyyy || yyyy.length !== 4) return '';
  return `${yyyy}-${mm}-${dd}`;
}
 
function isoToDisplay(iso) {
  if (!iso || !iso.includes('-')) return '';
  const [yyyy, mm, dd] = iso.split('-');
  return `${dd}/${mm}/${yyyy}`;
}
 
function syncDateFromDisplay() {
  const display = document.getElementById('dateDisplay').value;
  // Allow partial typing — only sync when we have a full date
  const cleaned = display.replace(/[^0-9\/\-]/g, '');
  // Auto-insert slashes
  let formatted = cleaned;
  if (/^\d{2}$/.test(cleaned)) formatted = cleaned + '/';
  if (/^\d{2}\/\d{2}$/.test(cleaned)) formatted = cleaned + '/';
  if (formatted !== display) document.getElementById('dateDisplay').value = formatted;
 
  const iso = displayDateToISO(cleaned);
  if (iso) {
    document.getElementById('dateInput').value = iso;
    _userPickedDate = true;
  }
}
let _userPickedDate = false;   // true once user manually sets a date
let _recalcTimer    = null;
let _clockInterval  = null;
 
(function init() {
  const now = new Date();
  const isoToday = now.toISOString().slice(0, 10);
 
  // Set display date as DD/MM/YYYY
  document.getElementById('dateDisplay').value = isoToDisplay(isoToday);
  document.getElementById('dateInput').value   = isoToday;
 
  document.getElementById('timeInput').value =
    String(now.getHours()).padStart(2, '0') + ':' +
    String(now.getMinutes()).padStart(2, '0');
  document.getElementById('utcOffset').value = -(now.getTimezoneOffset() / 60);
 
  // Live clock — ONLY updates time, NEVER date if user has changed it
  _clockInterval = setInterval(() => {
    if (!_userPickedDate) {
      const n = new Date();
      const isoNow = n.toISOString().slice(0, 10);
      document.getElementById('dateDisplay').value = isoToDisplay(isoNow);
      document.getElementById('dateInput').value   = isoNow;
    }
    document.getElementById('timeInput').value =
      String(new Date().getHours()).padStart(2, '0') + ':' +
      String(new Date().getMinutes()).padStart(2, '0');
  }, 30000);
 
  // Mark date as user-edited when they touch the display field
  document.getElementById('dateDisplay').addEventListener('focus', () => {
    _userPickedDate = true;
    clearInterval(_clockInterval); // stop auto-tick entirely once user edits
  });
  document.getElementById('timeInput').addEventListener('focus', () => {
    // Don't stop the clock just for time focus, only stop date auto-update
  });
  document.getElementById('timeInput').addEventListener('change', () => {
    // intentional manual time change — leave as-is
  });
 
  // Auto-recalculate only AFTER first manual calculation
  const RECALC_FIELDS = [
    { id: 'lat',         delay: 1200 },
    { id: 'lon',         delay: 1200 },
    { id: 'dateDisplay', delay: 600  },
    { id: 'timeInput',   delay: 400  },
    { id: 'utcOffset',   delay: 900  },
  ];
 
  function scheduleRecalc(delay) {
    if (document.getElementById('resultCard').style.display === 'none') return;
    clearTimeout(_recalcTimer);
    const btn = document.getElementById('calcBtn');
    if (btn && !btn.disabled) btn.style.opacity = '0.65';
    _recalcTimer = setTimeout(() => {
      if (btn) btn.style.opacity = '';
      doCalculate();
    }, delay);
  }
 
  RECALC_FIELDS.forEach(({ id, delay }) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input',  () => scheduleRecalc(delay));
    el.addEventListener('change', () => scheduleRecalc(delay));
  });
})();

function goToday() {
  _userPickedDate = false;
  const now = new Date();
  const isoNow = now.toISOString().slice(0, 10);
  document.getElementById('dateDisplay').value = isoToDisplay(isoNow);
  document.getElementById('dateInput').value   = isoNow;
  document.getElementById('timeInput').value =
    String(now.getHours()).padStart(2, '0') + ':' +
    String(now.getMinutes()).padStart(2, '0');
  document.getElementById('utcOffset').value = -(now.getTimezoneOffset() / 60);
  doCalculate();
}

// ── City lookup ────────────────────────────────────────────────────
document.getElementById('cityInput').addEventListener('blur', async function () {
  const city = this.value.trim();
  if (!city) return;
  const hint   = document.getElementById('geoHint');
  const stat   = document.getElementById('cityStatus');
  hint.className = 'geo-hint';
  hint.textContent = '🔍 Looking up…';
  stat.textContent = '⏳';
  try {
    const dateStr = document.getElementById('dateInput').value;
    const res = await fetch('{{ route("astro.city") }}?name=' + encodeURIComponent(city));
    if (!res.ok) throw new Error();
    const g = await res.json();
    document.getElementById('lat').value = g.lat.toFixed(4);
    document.getElementById('lon').value = g.lon.toFixed(4);
    try {
      const ref = dateStr ? new Date(dateStr + 'T12:00:00') : new Date();
      const a = new Date(ref.toLocaleString('en-US', { timeZone: g.timezone }));
      const b = new Date(ref.toLocaleString('en-US', { timeZone: 'UTC' }));
      document.getElementById('utcOffset').value = Math.round((a - b) / 1800000) * 0.5;
    } catch (e) {}
    hint.className = 'geo-hint ok';
    hint.textContent = `✓ ${g.lat.toFixed(4)}°N, ${g.lon.toFixed(4)}°E — ${g.timezone}`;
    stat.textContent = '✓';
  } catch {
    hint.className = 'geo-hint err';
    hint.textContent = '✗ Not found — enter coordinates manually';
    stat.textContent = '✗';
  }
});

// ── Main calculate ─────────────────────────────────────────────────
async function doCalculate(){
  const err = document.getElementById('errPill');
  err.style.display='none';
  const date=document.getElementById('dateInput').value;
  const time=document.getElementById('timeInput').value;
  const off=document.getElementById('utcOffset').value;
  const lat=document.getElementById('lat').value;
  const lon=document.getElementById('lon').value;
  if(!date||!time||off===''){err.textContent='⚠ Please fill in date, time, and UTC offset.';err.style.display='block';return;}
  if(!lat||!lon){err.textContent='⚠ Please enter or look up valid coordinates.';err.style.display='block';return;}

  const btn=document.getElementById('calcBtn');
  const orig=btn.innerHTML;
  btn.innerHTML='⏳ &nbsp;Calculating…';
  btn.disabled=true;

  // Dim chart during recalculation for visual feedback
  const chartWrap = document.getElementById('chartSvgWrap');
  if (chartWrap && document.getElementById('resultCard').style.display !== 'none') {
    chartWrap.style.opacity = '0.35';
    chartWrap.style.transition = 'opacity 0.15s';
  }

  try {
    const res = await fetch('{{ route("astro.calculate") }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({date,time,utcOffset:parseFloat(off),lat:parseFloat(lat),lon:parseFloat(lon)})
    });
    if(!res.ok){const e=await res.json();throw new Error(e.message||'Error');}
    const d = await res.json();
    renderAll(d, date, time, parseFloat(off), parseFloat(lat), parseFloat(lon));
  } catch(e){
    err.textContent='⚠ '+(e.message||'Unknown error');
    err.style.display='block';
    console.error(e);
  } finally {
    btn.innerHTML=orig;
    btn.disabled=false;
    if (chartWrap) chartWrap.style.opacity = '1';
  }
}

// ── Render all data from PHP response ──────────────────────────────
function renderAll(d, dateStr, timeStr, off, lat, lon){

  _todayLoaded = false;
  _planets = d.planets;

  const _csw=document.getElementById('chartSvgWrap'); if(_csw) _csw.innerHTML=d.chartHtml;

  // Varga charts
    if (d.vargaGridHtml) {
      const vgw = document.getElementById('vargaGridWrap');
      if (vgw) vgw.innerHTML = d.vargaGridHtml;
    }
    // Per-planet varga badges
    if (d.planetVargaBadges) {
      Object.entries(d.planetVargaBadges).forEach(([pid, html]) => {
        const el = document.getElementById(pid + '_vargaBadges');
        if (el) el.innerHTML = html;
      });
    }

    if (d.dashaHtml) {
  const dc = document.getElementById('dashaContent');
  if (dc) dc.innerHTML = d.dashaHtml;
}
if (d.shadBalaHtml) {
  const sc = document.getElementById('shadbalaContent');
  if (sc) sc.innerHTML = d.shadBalaHtml;
}
 
 

  // Lagna
  set('l_ascStrip',   d.lagna.ascStrip);
  set('l_ascStripSub',d.lagna.ascStripSub);
  set('l_ascTrop',    d.lagna.ascTrop);
  set('l_ascWSign',   d.lagna.ascWSign);
  set('l_ascWDeg',    d.lagna.ascWDeg);
  set('l_ascVSign',   d.lagna.ascVSign);
  set('l_ascVDeg',    d.lagna.ascVDeg);
  set('l_ascNak',     d.lagna.ascNak);
  set('l_ascNakLord', d.lagna.ascNakLord);
  set('l_ascSider',   d.lagna.ascSider);
  ['desc','mc','ic'].forEach(k=>{
    const a = d.lagna[k];
    set(`l_${k}WSign`,  a.wSign);
    set(`l_${k}WDeg`,   a.wDeg);
    set(`l_${k}VSign`,  a.vSign);
    set(`l_${k}VDeg`,   a.vDeg);
    set(`l_${k}Nak`,    a.nakName);
    set(`l_${k}NakLord`,a.nakLord);
    set(`l_${k}Sider`,  a.sider);
    set(`l_${k}Trop`,   a.trop);
  });
  set('l_ayanNote','Lahiri Ayanamsa '+d.ayan.toFixed(4)+'° · '+d.lagna.ayanNote);

  // Planet panels
  const SYMS = {sun:'☀',moon:'☽',mercury:'☿',venus:'♀',mars:'♂',jupiter:'♃',saturn:'♄',rahu:'☊',ketu:'☋'};
  Object.entries(d.planets).forEach(([pid,p])=>{
    const sym = SYMS[pid]||'';
    set(pid+'_stripTitle', sym+' '+pid.charAt(0).toUpperCase()+pid.slice(1)+' in '+p.nakName+(p.retro==='* Retrograde'?'  *':''));
    set(pid+'_stripSub',  p.stripSub);
    set(pid+'_stripLon',  p.stripLon);
    set(pid+'_vSign',     p.vSign);
    set(pid+'_vDeg',      p.vDeg);
    set(pid+'_nakName',   p.nakName);
    set(pid+'_nakPada',   p.nakPada);
    set(pid+'_nakLord',   p.nakLord);
    set(pid+'_nakDeity',  p.nakDeity);
    set(pid+'_retro',     p.retro);
    set(pid+'_house',     p.house);
    set(pid+'_houseSig',  p.houseSig);
    set(pid+'_ayanNote',  'Lahiri Ayanamsa '+d.ayan.toFixed(4)+'° · '+p.ayanNote);
    const bar = document.getElementById(pid+'_nakProg');
    if(bar){bar.style.width='0%';setTimeout(()=>bar.style.width=p.nakProg+'%',100);}
  });

  // Sun extras
  set('sunDec',       d.sunDec);
  set('sunRA',        d.sunRA);
  set('sunriseTime',  d.sunrise);
  set('sunsetTime',   d.sunset);
  set('dayLength',    d.dayLength);

  // Tithi/Panchanga
  _tkData.sunrise = d.tkRise;
  _tkData.now     = d.tk;
  _tkData.sunset  = d.tkSet;
  const [hr,mn]   = timeStr.split(':').map(Number);
  set('t_riseTime', d.ssRise);
  set('t_setTime',  d.ssSet);
  set('t_nowTime',  String(hr).padStart(2,'0')+':'+String(mn).padStart(2,'0'));
  set('t_riseSub',  d.tkRise ? fmtTKShort(d.tkRise) : 'No sunrise');
  set('t_nowSub',   fmtTKShort(d.tk));
  set('t_setSub',   d.tkSet  ? fmtTKShort(d.tkSet)  : 'No sunset');
  switchMode('sunrise');

  // Panchanga
  renderPancha(d.pancha);

  // Masa state
  _masaLat=lat; _masaLon=lon; _masaOff=off;
  const [y,mo,dy] = dateStr.split('-').map(Number);
  _masaYr=y; _masaMo=mo;
  const masaYrEl = document.getElementById('masaYr');
  if(masaYrEl&&!masaYrEl.value) masaYrEl.value=y;
  const festYrEl = document.getElementById('festYear');
  if(festYrEl) festYrEl.value = y;
  const masaSel = document.getElementById('masaSel');
  const civToVed={3:1,4:2,5:3,6:4,7:5,8:6,9:7,10:8,11:9,12:10,1:11,2:12};
  if(masaSel) masaSel.value=civToVed[mo]||12;

  document.getElementById('resultCard').style.display='block';
  document.getElementById('resultCard').scrollIntoView({behavior:'smooth',block:'start'});
  showTab('today');
}

async function loadTodayPanel() {
  if (_todayLoaded) return;          // don't double-fire
  if (_masaLat === null) return;      // no chart calculated yet

  _todayLoading = true;

  const content = document.getElementById('todayContent');
  if (!content) { _todayLoading = false; return; }

  // Show spinner
  content.innerHTML = `
    <div class="td-loading" style="justify-content:center;min-height:180px;align-items:center;flex-direction:column;gap:14px;width:100%">
      <span class="td-spinner" style="font-size:2.4rem">🪷</span>
      <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:var(--text-mid);text-align:center">आज का पंचांग गणना हो रहा है…</span>
    </div>`;

// Force max 2 seconds — show fallback if server takes longer
const _loadTimeout = setTimeout(() => {
  if (!_todayLoaded) {
    const el = document.getElementById('todayContent');
    if (el && el.querySelector('.td-spinner')) {
      el.innerHTML = buildTodayFallback({
        dateISO: document.getElementById('dateInput').value,
        pancha: {}, planetPositions: _planets || {},
        sunrise: document.getElementById('sunriseTime')?.textContent || '—',
        sunset:  document.getElementById('sunsetTime')?.textContent  || '—',
        dayLength: document.getElementById('dayLength')?.textContent || '—',
      });
      _todayLoaded = true;
    }
  }
}, 2000);

  try {
    const date = document.getElementById('dateInput').value;
    const time = document.getElementById('timeInput').value;
    const off  = document.getElementById('utcOffset').value;

    const res = await fetch('{{ route("astro.today") }}', {
      method  : 'POST',
      headers : {
        'Content-Type' : 'application/json',
        'X-CSRF-TOKEN' : CSRF,
        'Accept'       : 'application/json',
      },
      body: JSON.stringify({
        date,
        time,
        utcOffset : parseFloat(off),
        lat       : _masaLat,
        lon       : _masaLon,
      }),
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || 'Server error ' + res.status);
    }

    const data = await res.json();

    // The controller returns { html: '...' }  (renderHtml output)
    const html = data.html || data.todayHtml || '';

    if (!html) {
      throw new Error('Empty response from server');
    }

    clearTimeout(_loadTimeout);
    content.innerHTML = html;
    _todayLoaded  = true;

  } catch (e) {
    const content2 = document.getElementById('todayContent');
    if (content2) {
      content2.innerHTML = `
        <div class="td-error">
          <span style="font-size:1.4rem">⚠</span>
          <div>
            <strong>Could not load Today panel</strong><br>
            <span style="font-size:.82rem;opacity:.8">${e.message}</span>
          </div>
        </div>`;
    }
  } finally {
    _todayLoading = false;
  }
}


function fmtTKShort(tk){
  return tk.tithiName+' '+tk.tithiNum.replace(' / 15','')+' · '+Number(tk.elong).toFixed(1)+'°';
}

function renderPancha(p){
  set('v_strip',    p.varaStripTitle);
  set('v_stripSub', p.varaStripSub);
  set('v_day',      p.varaEn);
  set('v_name',     p.varaName);
  set('v_en',       p.varaEn);
  set('v_lord',     p.varaLord);
  set('v_nature',   p.varaNature);
  set('v_hora',     p.varaHora);
  set('v_class',    p.varaClass);
  set('v_cNote',    p.varaClassNote);
  set('v_deity',    p.varaDeity);
  set('v_dNote',    p.varaDNote);
  set('v_ausp',     p.varaAusp);
  set('v_act',      p.varaAct);
  set('v_infoTitle',p.varaSym+' '+p.varaName+' ('+p.varaEn+')');
  set('v_info',     p.varaInfo);
  const vsym = document.getElementById('v_sym');
  if(vsym){vsym.textContent=p.varaSym;vsym.style.color=p.varaColor;}
  arcAnimate('varaFill','varaDot','varaLbl',p.varaIdx/6,p.varaName,
    ['#c56408','#1d4e6f','#b83020','#2e7a6e','#7a5a10','#8e3a7a','#4a4060'][p.varaIdx]||'#c56408',340);

  set('n_strip',    p.nakStripTitle);
  set('n_stripSub', p.nakStripSub);
  set('n_num',      p.nakStripNum);
  set('n_name',     p.nakName);
  set('n_num2',     p.nakNum);
  set('n_lord',     p.nakLord);
  set('n_deity',    p.nakDeity);
  set('n_pada',     p.nakPada);
  set('n_prog',     p.nakProg+'% elapsed');
  set('n_gana',     p.nakGana);
  set('n_ganaSub',  p.nakGanaSub);
  set('n_yoni',     p.nakYoni);
  set('n_nadi',     p.nakNadi);
  set('n_nadiSub',  p.nakNadiSub);
  set('n_tattva',   p.nakTattva);
  set('n_quality',  p.nakQuality);
  set('n_infoTitle','✦ '+p.nakName);
  set('n_info',     p.nakInfo);
  const nb=document.getElementById('n_bar');
  if(nb){nb.style.width='0%';setTimeout(()=>nb.style.width=p.nakProg+'%',100);}
  arcAnimate('nakFill','nakDot','nakLbl',p.nakIdx/26,p.nakName+'· Pada '+(Math.floor(p.nakProg/25)+1),'#1d6aa0',340);

  set('y_strip',    p.yogaStripTitle);
  set('y_stripSub', p.yogaStripSub);
  set('y_num',      p.yogaStripNum);
  set('y_name',     p.yogaName);
  set('y_num2',     p.yogaNum);
  set('y_nature',   p.yogaNature);
  set('y_lord',     p.yogaLord);
  set('y_deity',    p.yogaDeity);
  set('y_class',    p.yogaClass);
  set('y_cSub',     p.yogaClassSub);
  set('y_sum',      p.yogaSum);
  set('y_prog',     p.yogaProg+'%');
  set('y_infoTitle','✧ '+p.yogaName+' Yoga');
  set('y_info',     p.yogaInfo);

  const yb=document.getElementById('y_bar');
  if(yb){yb.style.width='0%';setTimeout(()=>yb.style.width=p.yogaProg+'%',140);}
  set('y_pct',p.yogaProg+'%');
  arcAnimate('yogaFill','yogaDot','yogaLbl',p.yogaIdx/26,p.yogaName,
    p.yogaNature==='Auspicious'?'#5a30a0':'#c03030',340);

  setTimeout(()=>{
    const _tn=document.getElementById('t_name');
    const _kn=document.getElementById('k_name');
    set('ps_tithi',  _tn?_tn.textContent||'—':'—');
    set('ps_karana', _kn?_kn.textContent||'—':'—');
  },200);

  set('ps_vara',   p.sumVara);
  set('ps_nak',    p.sumNak);
  set('ps_yoga',   p.sumYoga);
  set('ps_rise',   p.rise);
  set('ps_set',    p.set);
  set('ps_day',    p.dayLen);
}

// ── Tithi mode switching ───────────────────────────────────────────
function switchMode(mode){
  _mode = mode;
  ['sunrise','now','sunset'].forEach(m=>{
    const btn=document.getElementById('tmb_'+m);
    if(btn) btn.classList.toggle('active',m===mode);
  });
  const tk = _tkData[mode];
  if(tk) renderTK(tk);
  else {set('t_strip','Not available');set('t_stripSub','Polar day/night');set('t_elong','—');}
}

function renderTK(tk){
  set('t_strip',   tk.stripTitle);
  set('t_stripSub',tk.stripSub);
  set('t_elong',   tk.stripElong);
  set('t_name',    tk.tithiName);
  set('t_paksha',  tk.tithiPaksha);
  set('t_num',     tk.tithiNum);
  set('t_lord',    tk.tithiLord);
  set('t_deity',   tk.tithiDeity);
  set('t_nature',  tk.tithiNature);
  set('t_prog',    tk.tithiProg+'% elapsed');
  set('k_strip',   tk.karanaName+' Karana');
  set('k_stripSub',tk.karanaType+' · Slot '+tk.karanaSlotNum+' of 60');
  set('k_slot',    tk.karanaSlot);
  set('k_name',    tk.karanaName);
  set('k_type',    tk.karanaType);
  set('k_lord',    tk.karanaLord);
  set('k_nature',  tk.karanaNature);
  set('k_slotEl',  tk.karanaSlot);
  set('k_prog',    tk.karanaProg+'% elapsed');
  set('k_deity',   tk.karanaDeity);
  set('k_favour',  tk.karanaFavour);
  set('k_class',   tk.karanaClass);
  set('ps_karana', tk.karanaName);
  set('ps_tithi',  tk.pakshaShort+' '+tk.tithiName);
  const tb=document.getElementById('t_bar');
  if(tb){tb.style.width='0%';setTimeout(()=>tb.style.width=tk.tithiProg+'%',80);}
  const kb=document.getElementById('k_bar');
  if(kb){kb.style.width='0%';setTimeout(()=>kb.style.width=tk.karanaProg+'%',120);}
  const pct=Math.min(Number(tk.elong)/360,1);
  const fill=document.getElementById('lunarFill');
  const dot=document.getElementById('lunarDot');
  const lbl=document.getElementById('lunarLbl');
  if(fill){
    fill.style.strokeDashoffset=320*(1-pct);
    fill.style.transition='stroke-dashoffset .7s cubic-bezier(.4,0,.2,1)';
    fill.setAttribute('stroke',tk.tithiPaksha.includes('Krishna')?'#6040a0':'#a060e0');
  }
  if(dot){
    const t2=pct;
    dot.setAttribute('cx',((1-t2)*(1-t2)*20+2*(1-t2)*t2*160+t2*t2*300).toFixed(1));
    dot.setAttribute('cy',((1-t2)*(1-t2)*60+2*(1-t2)*t2*(-20)+t2*t2*60).toFixed(1));
  }
  if(lbl) lbl.textContent=tk.tithiPaksha.replace(' Paksha','')+' '+tk.tithiName+' · '+Number(tk.elong).toFixed(1)+'°';
  arcAnimate('karanaFill','karanaDot','karanaLbl',(tk.karanaSlotNum-1)/59,tk.karanaName+' · Slot '+tk.karanaSlotNum,'#c07020',340);
}

// ── SVG arc animator ───────────────────────────────────────────────
function arcAnimate(fillId,dotId,lblId,pct,label,color,arcLen){
  const fill=document.getElementById(fillId);
  const dot=document.getElementById(dotId);
  const lbl=document.getElementById(lblId);
  if(!fill||!dot||!lbl) return;
  fill.style.strokeDashoffset=arcLen*(1-pct);
  fill.style.transition='stroke-dashoffset .7s cubic-bezier(.4,0,.2,1)';
  if(color){fill.setAttribute('stroke',color);dot.setAttribute('fill',color);}
  const t=pct;
  const bx=((1-t)*(1-t)*20+2*(1-t)*t*170+t*t*320).toFixed(1);
  const by=((1-t)*(1-t)*95+2*(1-t)*t*(-5)+t*t*95).toFixed(1);
  dot.setAttribute('cx',bx);dot.setAttribute('cy',by);
  lbl.textContent=label;
}

// ── Masa calendar ──────────────────────────────────────────────────
const MASA_NAMES=['Chaitra','Vaishakha','Jyeshtha','Ashadha','Shravana',
  'Bhadrapada','Ashwin','Kartik','Margashirsha','Pausha','Magha','Phalguna'];

function masaNav(delta){
  const sel=document.getElementById('masaSel');
  const yr=document.getElementById('masaYr');
  let m=parseInt(sel.value)+delta;
  let y=parseInt(yr.value)||_masaYr||new Date().getFullYear();
  if(m>12){m=1;y++;}
  if(m<1){m=12;y--;}
  sel.value=m; yr.value=y;
  masaLoad();
}

async function masaLoad(){
  if(_masaLat===null){const _a=document.getElementById('masaContent');if(_a)_a.innerHTML='<div class="masa-loading">⚠ Please calculate a chart first.</div>';return;}
  const vedMon=parseInt(document.getElementById('masaSel').value)||12;
  const year=parseInt(document.getElementById('masaYr').value)||_masaYr||new Date().getFullYear();
  const _b=document.getElementById('masaContent');if(_b)_b.innerHTML='<div class="masa-loading">⏳ Computing accurate Panchanga…</div>';
  try {
    const res=await fetch('{{ route("astro.masa") }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({year,vedMon,lat:_masaLat,lon:_masaLon,utcOff:_masaOff})
    });
    const data=await res.json();
    buildMasa(data,vedMon,year);
  } catch{
    const _c=document.getElementById('masaContent');if(_c)_c.innerHTML='<div class="masa-loading">⚠ Failed to load Masa data.</div>';
  }
}

function buildMasa(data,vedMon,year){
  const rows=data.rows;
  const shukla=rows.filter(r=>r.paksha==='Shukla');
  const krishna=rows.filter(r=>r.paksha==='Krishna');
  set('ms_month',MASA_NAMES[vedMon-1]+' '+year);
  set('ms_loc',(_masaLat).toFixed(2)+'°, '+(_masaLon).toFixed(2)+'°');
  set('ms_shukla',shukla.length);
  set('ms_krishna',krishna.length);

  const bRows=arr=>arr.map(r=>{
    const yc=r.yogaNature==='Inauspicious'?'#a02020':'#1a6020';
    const rp=r.rashiPravesh?`<div class="masa-rp">☽ ${r.rashiPravesh}</div>`:'';
    const rc=r.isAmavasya?'amavasya':r.isPurnima?'purnima':r.isEkadashi?'ekadashi':'';
    return `<tr class="${rc}">
      <td class="td-day">${r.day}<br><span class="td-vara-sm">${r.varaShort}</span></td>
      <td class="td-tithi"><div class="masa-an">${r.tithiName} ${r.tithiNum}</div><div class="masa-sam">🕐 ${r.tithiSamapti}</div></td>
      <td class="td-karana"><div class="masa-an">${r.karanaName}</div><div class="masa-sam">🕐 ${r.karanaSamapti}</div></td>
      <td class="td-nak"><div class="masa-an">${r.nakName}</div><div class="masa-sam">🕐 ${r.nakSamapti}</div>${rp}</td>
      <td style="color:${yc}"><div class="masa-an">${r.yogaName}</div><div class="masa-sam" style="color:#777">🕐 ${r.yogaSamapti}</div></td>
      <td class="td-rise">🌅 ${r.riseStr}</td>
      <td class="td-set">🌇 ${r.setStr}</td>
    </tr>`;
  }).join('');

  const th=`<thead><tr>
    <th>Date<br>Vara</th>
    <th>Tithi<br><span class="th-sub">Samapti Kaal</span></th>
    <th>Karana<br><span class="th-sub">Samapti Kaal</span></th>
    <th>Nakshatra<br><span class="th-sub">Samapti · Rashi Pravesh</span></th>
    <th>Yoga<br><span class="th-sub">Samapti Kaal</span></th>
    <th>Sunrise</th><th>Sunset</th>
  </tr></thead>`;

  const sHtml=shukla.length?`<div class="masa-ph">🌕 Shukla Paksha — ${shukla.length} days</div><div class="masa-tw"><table class="masa-t">${th}<tbody>${bRows(shukla)}</tbody></table></div>`:'';
  const kHtml=krishna.length?`<div class="masa-ph masa-pk">🌑 Krishna Paksha — ${krishna.length} days</div><div class="masa-tw"><table class="masa-t">${th}<tbody>${bRows(krishna)}</tbody></table></div>`:'';
  document.getElementById('masaContent').innerHTML=sHtml+kHtml;
 const festYrEl = document.getElementById('festYear');
if (festYrEl && !festYrEl.value) festYrEl.value = y;
}


// ── Tab navigation ─────────────────────────────────────────────────
const ALL_TABS=['today','chart','varga','lagna','tithi','masa','dasha','shadbala','festival','muhrat','sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
const TAB_CLASS={
  chart:'act-chart',varga:'act-varga',lagna:'act-lagna',tithi:'act-tithi',masa:'act-masa',
  sun:'act-sun',moon:'act-moon',mercury:'act-merc',venus:'act-venus',
  mars:'act-mars',jupiter:'act-jup',saturn:'act-sat',rahu:'act-rahu',ketu:'act-ketu',
  dasha:    'act-tithi',  
  shadbala: 'act-varga', 
  festival: 'act-festival', 
  muhrat: 'act-muhrat',
  today:  'act-today', 
};
const PLANET_SYMS={sun:'☀',moon:'☽',mercury:'☿',venus:'♀',mars:'♂',jupiter:'♃',saturn:'♄',rahu:'☊',ketu:'☋'};

function showTab(pid){
  ALL_TABS.forEach(p=>{
    const panel=document.getElementById(p+'Panel');
    const btn=document.getElementById('tab_'+p);
    if(panel) panel.style.display=p===pid?'':'none';
    if(btn){btn.className='tab-btn';if(p===pid)btn.classList.add(TAB_CLASS[p]);}
  });

if (pid === 'festival') {
  _renderFestSubPills();
  if (_masaLat !== null && !_festYear) {
    festLoad(); 
  } else if (_festYear) {
    _renderFestList(); 
  }
}

  if (pid === 'today' && _masaLat !== null && !_todayLoaded) {
    loadTodayPanel();
  }
  if (pid === 'muhrat' && _masaLat !== null) {
    initMuhratPanel();
  }

  const pib=document.getElementById('pib');
  const isPlanet=['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'].includes(pid);
  if(isPlanet&&_planets[pid]){
    const p=_planets[pid];
    document.getElementById('pib_sym').textContent=PLANET_SYMS[pid]||'';
    document.getElementById('pib_name').textContent=pid.charAt(0).toUpperCase()+pid.slice(1);
    document.getElementById('pib_sign').textContent=p.vSign;
    document.getElementById('pib_nak').textContent=p.nakName+' Pada '+(p.nakPada?p.nakPada.split(' ')[1]||'—':'—');
    document.getElementById('pib_retro').textContent=p.retro==='* Retrograde'?'  ℞ Retrograde':'';
    document.getElementById('pib_deg').textContent=p.ayanNote.split('·').pop().trim();
    pib.style.display='flex';
  } else {
    pib.style.display='none';
  }
}

// ── Keyboard navigation ────────────────────────────────────────────
document.addEventListener('keydown',e=>{
  if(['INPUT','SELECT','TEXTAREA'].includes(document.activeElement.tagName)) return;
  if(e.ctrlKey||e.metaKey||e.altKey) return;
  if(document.getElementById('resultCard').style.display==='none') return;
  const map={l:'lagna',t:'tithi',p:'masa',c:'chart',d:'dasha',b:'shadbala',f:'festival',h:'muhrat',g:'varga',s:'sun',m:'moon',e:'mercury',v:'venus',a:'mars',j:'jupiter',k:'saturn',r:'rahu',u:'ketu'};
  const tab=map[e.key.toLowerCase()];
  if(tab){showTab(tab);e.preventDefault();return;}
  if(['ArrowRight','ArrowLeft'].includes(e.key)){
    const cur=ALL_TABS.findIndex(t=>{const b=document.getElementById('tab_'+t);return b&&b.className.includes('act-');});
    if(cur===-1) return;
    let next=e.key==='ArrowRight'?cur+1:cur-1;
    next=((next%ALL_TABS.length)+ALL_TABS.length)%ALL_TABS.length;
    showTab(ALL_TABS[next]);
    e.preventDefault();
  }
});

// ── Utility ────────────────────────────────────────────────────────
function set(id,val){
  const el=document.getElementById(id);
  if(el) el.textContent=val;
}

    function scrollToVarga(vKey) {
      showTab('varga');
      setTimeout(() => {
        const wrap = document.getElementById('vargaGridWrap');
        if (wrap) wrap.scrollIntoView({behavior:'smooth', block:'start'});
      }, 100);
    }

/**
 * Minimal fallback — shown when PHP doesn't return an 'html' key.
 * Renders core panchanga + planets in the dark temple style.
 */
function buildTodayFallback(data) {
    const p       = data.panchanga || data.panchaDetails || {};
    const tithi   = p.tithi      || {};
    const vara    = p.vara       || {};
    const nak     = p.nakshatra  || {};
    const yoga    = p.yoga       || {};
    const karana  = p.karana     || {};
    const moon    = data.moon    || {};
    const mq      = data.muhurtaQuality || {};
    const sunrise = data.sunrise  || '—';
    const sunset  = data.sunset   || '—';
    const dayLen  = data.dayLength|| '—';
 
    const mqColor = {'Excellent':'#2e7a40','Good':'#1d4e6f','Mixed':'#c47a20','Challenging':'#b83020'}[mq.label] || '#1d4e6f';
    const mqPct   = mq.pct || 0;
 
    const todayFests = data.todayFestivals || {};
    const allFests   = [
        ...(todayFests.vrat    || []),
        ...(todayFests.parv    || []),
        ...(todayFests.jayanti || []),
        ...(todayFests.other   || []),
    ];
 
    const MONTHS_HI = ['','जनवरी','फरवरी','मार्च','अप्रैल','मई','जून',
                       'जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'];
    const [yr, mo, dy2] = (data.dateISO || '2000-01-01').split('-').map(Number);
 
    const PORDER = ['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
    const PHINDI = {sun:'सूर्य',moon:'चन्द्र',mercury:'बुध',venus:'शुक्र',
                    mars:'मंगल',jupiter:'गुरु',saturn:'शनि',rahu:'राहु',ketu:'केतु'};
    const PCOLOR = {sun:'#c8a84b',moon:'#7ab8d8',mercury:'#5ac8a8',venus:'#e890d0',
                    mars:'#d86040',jupiter:'#d4a840',saturn:'#9080c0',rahu:'#80b880',ketu:'#c08870'};
 
    const planets = data.planetPositions || {};
 
    const planetCards = PORDER.map(pid => {
        const pp = planets[pid]; if (!pp) return '';
        const clr = PCOLOR[pid];
        return `<div style="background:linear-gradient(160deg,#1a1710,#24201a);
                    border:1px solid ${clr}33;border-top:3px solid ${clr};
                    border-radius:13px;padding:11px 12px;">
            <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;
                        color:${clr};font-weight:700;margin-bottom:1px">${PHINDI[pid]}</div>
            <div style="font-size:.62rem;color:#7a7060;letter-spacing:1px;
                        text-transform:uppercase;margin-bottom:5px">
                ${pp.name||pid}${pp.retro ? ' <span style="color:#d86040">ℛ</span>' : ''}
            </div>
            <div style="font-size:.86rem;color:#f5f0e8;font-weight:600">${pp.sign||'—'}</div>
            <div style="font-size:.66rem;color:#a89878;margin-top:2px;line-height:1.5">
                ${pp.nak||'—'} · Pada ${pp.pada||'?'}<br>${Number(pp.deg||0).toFixed(2)}°
            </div>
        </div>`;
    }).join('');
 
    const upFests  = (data.upcomingFestivals||[]).slice(0,10);
    const pastFests= (data.pastFestivals    ||[]).slice(0,10);
    const today    = data.dateISO || '';
 
    const festRow = (f, isPast) => {
        const daysA = (!isPast && f.date && today)
            ? Math.round((new Date(f.date) - new Date(today)) / 86400000) : 0;
        const dLbl = isPast ? '' : (daysA===0?'आज':daysA===1?'कल':daysA+' दिन');
        return `<div style="display:flex;align-items:center;gap:8px;padding:6px 0;
                            border-bottom:1px solid rgba(200,168,75,.07)">
            <div style="flex:1;font-size:.78rem;color:${isPast?'#a89878':'#e0d8c8'}">${f.name||'—'}</div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:.62rem;color:#7a7060">${f.date||''}</div>
                ${dLbl?`<div style="font-size:.6rem;font-weight:800;color:#c8a84b">${dLbl}</div>`:''}
            </div>
        </div>`;
    };
 
    return `
<style>
@import url('https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit&family=Playfair+Display:wght@700;900&family=Crimson+Pro:wght@400;600&display=swap');
.tpf{background:#0e0c08;color:#e0d8c8;padding:22px 18px 30px;border-radius:16px;
     font-family:'Crimson Pro',Georgia,serif;}
.tpf *{box-sizing:border-box}
.tpf-card{background:linear-gradient(135deg,#1a1710,#24201a);
          border:1px solid rgba(200,168,75,.18);border-radius:15px;padding:15px 17px;margin-bottom:10px;}
.tpf-div{display:flex;align-items:center;gap:12px;margin:22px 0 12px}
.tpf-line{flex:1;height:1px;background:linear-gradient(90deg,transparent,#8a6820,transparent)}
.tpf-lbl{font-size:.58rem;letter-spacing:3px;text-transform:uppercase;color:#c8a84b;
         font-family:'Playfair Display',serif;font-weight:700;white-space:nowrap}
.tpf-pg{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px}
.tpf-ag{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin-bottom:8px}
.tpf-ac{background:linear-gradient(160deg,#1a1710,#24201a);border:1px solid rgba(200,168,75,.2);
        border-top:3px solid #8a6820;border-radius:11px;padding:11px 9px;text-align:center}
.tpf-2col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:680px){
    .tpf-ag{grid-template-columns:repeat(3,1fr)}
    .tpf-pg{grid-template-columns:repeat(3,1fr)}
    .tpf-2col{grid-template-columns:1fr}
}
</style>
<div class="tpf">
 
  <!-- HERO -->
  <div class="tpf-card" style="background:linear-gradient(135deg,#1a1500,#0e0c00);
       border-color:rgba(200,168,75,.3);padding:18px 20px;margin-bottom:14px">
    <div style="display:flex;align-items:flex-start;gap:18px;flex-wrap:wrap">
      <div style="text-align:center;flex-shrink:0">
        <div style="font-family:'Playfair Display',serif;font-size:3.2rem;font-weight:900;
                    line-height:1;color:#c8a84b">${dy2}</div>
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;color:#e8cc80">${MONTHS_HI[mo]}</div>
        <div style="font-size:.65rem;color:#7a7060">${yr}</div>
      </div>
      <div style="flex:1;min-width:170px">
        <div style="font-size:.56rem;letter-spacing:2px;text-transform:uppercase;color:#7a7060;margin-bottom:3px">Vedic Day · वार</div>
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.4rem;color:#c8a84b;font-weight:700;margin-bottom:4px">
            ${vara.name||'—'} <span style="font-size:.8rem;color:#a89878;font-style:italic">· ${vara.en||''}</span>
        </div>
        <div style="font-size:.75rem;color:#a89878;margin-bottom:9px">${allFests.length?allFests[0].name:'कोई विशेष पर्व नहीं'}</div>
        <div style="display:flex;gap:7px;flex-wrap:wrap">
          ${['उदय|'+sunrise,'अस्त|'+sunset,'दिन|'+dayLen].map(x=>{const[l,v]=x.split('|');return`
          <div style="background:rgba(200,168,75,.08);border:1px solid rgba(200,168,75,.2);border-radius:9px;padding:5px 11px;font-size:.73rem">
            <span style="color:#7a7060">${l} </span><strong style="color:#c8a84b">${v}</strong>
          </div>`;}).join('')}
        </div>
      </div>
      <div style="flex-shrink:0;text-align:right;min-width:120px">
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.15rem;color:${mqColor};font-weight:700;margin-bottom:2px">
            ${{'Excellent':'श्रेष्ठ','Good':'शुभ','Mixed':'मिश्रित','Challenging':'साधारण'}[mq.label]||'शुभ'}
        </div>
        <div style="font-size:.58rem;letter-spacing:1.5px;text-transform:uppercase;color:#7a7060;margin-bottom:6px">मुहूर्त गुणवत्ता</div>
        <div style="display:flex;align-items:center;gap:6px">
          <div style="background:rgba(255,255,255,.08);border-radius:4px;height:5px;flex:1;overflow:hidden">
            <div style="width:${mqPct}%;height:100%;background:${mqColor};border-radius:4px"></div>
          </div>
          <span style="font-size:.76rem;font-weight:700;color:${mqColor}">${mqPct}%</span>
        </div>
      </div>
    </div>
    ${allFests.length?`<div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:4px">
      ${allFests.slice(0,5).map(f=>`<span style="display:inline-flex;align-items:center;padding:3px 11px;
        background:rgba(200,168,75,.12);border:1px solid rgba(200,168,75,.25);border-radius:50px;
        font-size:.7rem;font-weight:700;color:#c8a84b;white-space:nowrap">${f.name||'—'}</span>`).join('')}
    </div>`:''}
  </div>
 
  <!-- PANCHANGA -->
  <div class="tpf-div"><div class="tpf-line"></div><span class="tpf-lbl">पञ्चाङ्ग — Panchanga</span><div class="tpf-line"></div></div>
  <div class="tpf-ag">
    ${[['①','तिथि','Tithi',`${tithi.paksha||''} ${tithi.name||'—'}`,`${tithi.lord||''} · ${tithi.nature||''}`],
       ['②','वार','Vara',vara.name||'—',`${vara.lord||''} · ${vara.nature||''}`],
       ['③','नक्षत्र','Nakshatra',nak.name||'—',`Pada ${nak.pada||'?'} · ${nak.lord||''}`],
       ['④','योग','Yoga',yoga.name||'—',`${yoga.nature||''} · ${yoga.lord||''}`],
       ['⑤','करण','Karana',karana.name||'—',`${karana.type||''} · ${karana.lord||''}`]
    ].map(([n,h,e,nm,sb])=>`
    <div class="tpf-ac">
      <div style="font-size:.52rem;letter-spacing:1.5px;color:#7a7060;margin-bottom:2px;text-transform:uppercase">${n} ${e}</div>
      <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.68rem;color:#c8a84b;margin-bottom:4px">${h}</div>
      <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:#f5f0e8;font-weight:700;line-height:1.2;margin-bottom:2px">${nm}</div>
      <div style="font-size:.62rem;color:#a89878;line-height:1.35">${sb}</div>
    </div>`).join('')}
  </div>
 
  <!-- TODAY OBSERVANCES -->
  ${allFests.length?`
  <div class="tpf-div"><div class="tpf-line"></div><span class="tpf-lbl">आज के पर्व एवं व्रत</span><div class="tpf-line"></div></div>
  <div style="display:flex;flex-wrap:wrap;gap:9px;margin-bottom:8px">
    ${allFests.map(f=>`
    <div style="background:linear-gradient(135deg,#120828,#1e0f40);border:1.5px solid rgba(160,80,255,.3);
                border-radius:13px;padding:11px 14px;flex:1;min-width:190px;max-width:340px">
      <div style="font-family:'Playfair Display',serif;font-size:.9rem;color:#b878ff;font-weight:700;margin-bottom:4px">${f.name||'—'}</div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.42);line-height:1.5">${(f.significance||f.desc||'').slice(0,85)}</div>
      ${f.mantra?`<div style="margin-top:6px;padding:4px 8px;background:rgba(255,255,255,.05);border-radius:6px;
                              font-family:'Tiro Devanagari Sanskrit',serif;font-size:.76rem;color:rgba(255,255,255,.48)">${f.mantra}</div>`:''}
    </div>`).join('')}
  </div>`:''}
 
  <!-- PLANETS -->
  <div class="tpf-div"><div class="tpf-line"></div><span class="tpf-lbl">ग्रह स्थिति — Planetary Positions</span><div class="tpf-line"></div></div>
  <div class="tpf-pg" style="margin-bottom:10px">${planetCards}</div>
 
  <!-- FESTIVAL CALENDAR -->
  <div class="tpf-div"><div class="tpf-line"></div><span class="tpf-lbl">पर्व कैलेंडर — Festival Calendar</span><div class="tpf-line"></div></div>
  <div class="tpf-2col">
    <div class="tpf-card" style="padding:13px">
      <div style="display:flex;align-items:center;gap:7px;margin-bottom:11px">
        <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;color:#a89878;font-weight:700">विगत पर्व</span>
        <span style="font-size:.58rem;color:#7a7060;text-transform:uppercase;letter-spacing:1px">Last 15 Days</span>
      </div>
      ${pastFests.length ? pastFests.map(f=>festRow(f,true)).join('') : '<div style="font-size:.76rem;color:#7a7060;font-style:italic">No recent festivals</div>'}
    </div>
    <div class="tpf-card" style="padding:13px">
      <div style="display:flex;align-items:center;gap:7px;margin-bottom:11px">
        <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;color:#c8a84b;font-weight:700">आगामी पर्व</span>
        <span style="font-size:.58rem;color:#7a7060;text-transform:uppercase;letter-spacing:1px">Next 15 Days</span>
      </div>
      ${upFests.length ? upFests.map(f=>festRow(f,false)).join('') : '<div style="font-size:.76rem;color:#7a7060;font-style:italic">No upcoming festivals</div>'}
    </div>
  </div>
 
  <!-- FOOTER -->
  <div class="tpf-card" style="margin-top:12px;padding:13px 16px;display:flex;gap:14px;flex-wrap:wrap;align-items:center">
    <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;color:#7ab8d8;font-weight:700">चन्द्र · Moon</div>
    <div style="font-size:.74rem;color:#a89878">${moon.sign||'—'} · ${moon.nakshatra||'—'} · ${moon.paksha||''} Tithi ${moon.tithiNum||''}</div>
    <div style="margin-left:auto;font-size:.74rem;color:#a89878">
      <span style="color:#7a7060;font-size:.6rem;text-transform:uppercase;letter-spacing:1px">दशा · </span>${data.dasha||'—'}
    </div>
  </div>
 
</div>`;
}

</script>
@include('partials._js_festival')

</body>
</html>