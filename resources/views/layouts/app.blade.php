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

@yield('content')

</div>{{-- /wrap --}}

@yield('scripts')

</body>
</html>