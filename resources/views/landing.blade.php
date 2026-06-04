<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vedic Astro Calculator — Jyotish Calculation Engine</title>
  <meta name="description" content="Precise Vedic astrology calculations: planetary positions, Vimshottari Dasha, Panchanga, Muhurat, and Shodashvarga charts powered by Jean Meeus algorithms.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
<style>
:root {
  --bg: #05080f;
  --bg2: #090d1a;
  --card: rgba(255,255,255,.04);
  --card-b: rgba(255,255,255,.09);
  --gold: #c8a84b;
  --gold-l: #f0d470;
  --gold-d: #8a6820;
  --sky: #3a80b8;
  --sky-l: #6aaad8;
  --text: #e4dac8;
  --text-d: #8a8070;
  --accent: #e87828;
  --purple: #6040a8;
  --rx: 20px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',system-ui,sans-serif;overflow-x:hidden;line-height:1.6}

/* ── NAV ── */
nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:16px 40px;display:flex;align-items:center;justify-content:space-between;backdrop-filter:blur(16px);background:rgba(5,8,15,.7);border-bottom:1px solid rgba(200,168,75,.12)}
.nav-logo{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:var(--gold);letter-spacing:.5px;text-decoration:none}
.nav-logo span{color:var(--text-d);font-weight:400;font-size:.85rem;margin-left:8px}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{color:var(--text-d);font-size:.88rem;font-weight:500;text-decoration:none;transition:color .2s}
.nav-links a:hover{color:var(--gold)}
.nav-cta{background:linear-gradient(135deg,#c8901a,#7a4800);color:#fff!important;padding:8px 22px;border-radius:40px;font-weight:700!important;font-size:.84rem!important;transition:transform .15s,box-shadow .15s!important}
.nav-cta:hover{transform:translateY(-1px);box-shadow:0 6px 20px -6px rgba(200,144,26,.6)!important}

/* ── HERO ── */
.hero{position:relative;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:40px;padding:100px 80px 60px;overflow:hidden}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:100px 24px 60px;text-align:center}.hero-visual{order:-1}}
.hero-bg{position:absolute;inset:0;z-index:0;background:radial-gradient(ellipse at 70% 50%,rgba(58,128,184,.12) 0%,transparent 60%),radial-gradient(ellipse at 20% 80%,rgba(96,64,168,.1) 0%,transparent 50%)}
#starsCanvas{position:absolute;inset:0;z-index:0;opacity:.7}
.hero-content{position:relative;z-index:2}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(200,168,75,.1);border:1px solid rgba(200,168,75,.25);border-radius:40px;padding:6px 18px;font-size:.72rem;font-weight:700;color:var(--gold);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:24px}
.hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.4rem,5vw,3.8rem);font-weight:700;line-height:1.1;margin-bottom:18px;color:#f0e8d4}
.hero-title .gold{color:var(--gold);font-style:italic}
.hero-sub{font-size:1.08rem;color:var(--text-d);margin-bottom:32px;line-height:1.7;max-width:480px}
@media(max-width:900px){.hero-sub{margin:0 auto 32px}}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap}
@media(max-width:900px){.hero-btns{justify-content:center}}
.btn-primary{background:linear-gradient(135deg,#c8901a,#7a4800);color:#fff;padding:14px 32px;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:transform .15s,box-shadow .15s;box-shadow:0 8px 24px -8px rgba(200,144,26,.5)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 28px -8px rgba(200,144,26,.7)}
.btn-ghost{color:var(--text-d);border:1.5px solid rgba(255,255,255,.12);padding:13px 28px;border-radius:50px;font-weight:500;font-size:.92rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:border-color .2s,color .2s}
.btn-ghost:hover{border-color:var(--gold);color:var(--gold)}

/* Orrery */
.hero-visual{position:relative;z-index:2;display:flex;align-items:center;justify-content:center}
.orrery-wrap{position:relative;width:480px;height:480px;max-width:100%}
@media(max-width:900px){.orrery-wrap{width:320px;height:320px}}
#orreryCanvas{position:absolute;inset:0;width:100%;height:100%}
.zodiac-ring{position:absolute;inset:-20px;animation:spinSlow 120s linear infinite}
@keyframes spinSlow{to{transform:rotate(360deg)}}

/* ── SECTION BASE ── */
.section{padding:80px 80px}
@media(max-width:700px){.section{padding:60px 24px}}
.section-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:2.5px;font-weight:800;color:var(--gold);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.section-lbl::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(200,168,75,.4),transparent)}
.section-title{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,3.5vw,2.5rem);font-weight:700;color:#f0e8d4;margin-bottom:16px;line-height:1.2}
.section-sub{color:var(--text-d);font-size:1rem;max-width:560px;line-height:1.7;margin-bottom:48px}

/* ── PANCHANGA STRIP ── */
.pancha-section{background:linear-gradient(180deg,var(--bg),var(--bg2))}
.pancha-header{display:flex;align-items:baseline;gap:16px;margin-bottom:32px;flex-wrap:wrap}
.pancha-date{font-family:'DM Mono',monospace;font-size:.78rem;color:var(--text-d)}
.pancha-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.pa-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:20px 22px;position:relative;overflow:hidden;transition:transform .2s,border-color .2s}
.pa-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.pa-card:hover{transform:translateY(-3px);border-color:rgba(200,168,75,.3)}
.pa-card.gold-t::before{background:linear-gradient(90deg,var(--gold),transparent)}
.pa-card.sky-t::before{background:linear-gradient(90deg,var(--sky-l),transparent)}
.pa-card.purple-t::before{background:linear-gradient(90deg,var(--purple),transparent)}
.pa-card.accent-t::before{background:linear-gradient(90deg,var(--accent),transparent)}
.pa-anga{font-size:.58rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:var(--text-d);margin-bottom:4px}
.pa-name{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;color:#f0e8d4;margin-bottom:4px}
.pa-sub{font-size:.75rem;color:var(--text-d)}
.pa-sym{position:absolute;top:16px;right:18px;font-size:1.6rem;opacity:.3}
.sun-row{display:flex;gap:20px;margin-top:20px;flex-wrap:wrap}
.sun-pill{display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--card-b);border-radius:50px;padding:10px 20px}
.sun-pill .sp-icon{font-size:1.1rem}
.sun-pill .sp-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;color:var(--text-d)}
.sun-pill .sp-val{font-family:'DM Mono',monospace;font-size:.92rem;font-weight:700;color:var(--gold)}

/* Tithi arc */
.tithi-arc-wrap{margin-top:24px;background:rgba(255,255,255,.02);border:1px solid var(--card-b);border-radius:var(--rx);padding:20px 24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.tithi-arc-svg{flex-shrink:0}
.tithi-arc-info .tai-main{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:#f0e8d4}
.tithi-arc-info .tai-sub{font-size:.78rem;color:var(--text-d);margin-top:4px}

/* ── PLANETS SECTION ── */
.planets-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
.pcard{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:18px 20px;display:flex;gap:14px;align-items:flex-start;transition:transform .2s,border-color .2s;cursor:default;position:relative;overflow:hidden}
.pcard::after{content:'';position:absolute;inset:0;opacity:0;transition:opacity .3s;border-radius:var(--rx)}
.pcard:hover{transform:translateY(-3px)}
.pcard:hover::after{opacity:1}
.pc-sym{font-size:1.8rem;line-height:1;flex-shrink:0;filter:drop-shadow(0 0 6px currentColor)}
.pc-body{flex:1;min-width:0}
.pc-label{font-size:.58rem;text-transform:uppercase;letter-spacing:1.2px;font-weight:800;margin-bottom:3px}
.pc-name{font-weight:700;font-size:.9rem;color:#f0e8d4;margin-bottom:6px}
.pc-sign{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:600;color:#f0e8d4}
.pc-deg{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-d);margin-top:2px}
.pc-nak{font-size:.72rem;color:var(--text-d);margin-top:3px}
.pc-retro{display:inline-block;font-size:.58rem;font-weight:800;background:rgba(232,120,40,.15);color:var(--accent);border:1px solid rgba(232,120,40,.3);border-radius:20px;padding:1px 7px;margin-top:5px}

/* ── FEATURES SECTION ── */
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.feat-card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx);padding:28px;position:relative;overflow:hidden;transition:transform .2s,border-color .2s}
.feat-card:hover{transform:translateY(-4px);border-color:rgba(200,168,75,.35)}
.feat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:0;transition:opacity .3s}
.feat-card:hover::before{opacity:1}
.feat-icon{font-size:2.4rem;margin-bottom:14px;display:block;line-height:1}
.feat-title{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;color:#f0e8d4;margin-bottom:8px}
.feat-desc{font-size:.85rem;color:var(--text-d);line-height:1.7}
.feat-link{display:inline-flex;align-items:center;gap:6px;margin-top:16px;font-size:.8rem;font-weight:700;color:var(--gold);text-decoration:none;opacity:.8;transition:opacity .2s}
.feat-link:hover{opacity:1}

/* ── FESTIVALS SECTION ── */
.fest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.fest-card{background:var(--card);border:1px solid var(--card-b);border-radius:16px;padding:18px 20px;display:flex;gap:14px;align-items:flex-start;transition:transform .2s,border-color .2s}
.fest-card:hover{transform:translateY(-2px);border-color:rgba(200,168,75,.25)}
.fest-icon{font-size:1.8rem;line-height:1;flex-shrink:0}
.fest-body{flex:1;min-width:0}
.fest-date{font-family:'DM Mono',monospace;font-size:.68rem;color:var(--gold);font-weight:600;margin-bottom:4px}
.fest-name{font-weight:700;font-size:.88rem;color:#f0e8d4;line-height:1.3;margin-bottom:3px}
.fest-masa{font-size:.72rem;color:var(--text-d)}
.fest-badge{display:inline-block;font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;padding:2px 8px;border-radius:20px;margin-top:6px}
.badge-festival{background:rgba(200,168,75,.12);color:var(--gold);border:1px solid rgba(200,168,75,.25)}
.badge-vrat{background:rgba(58,128,184,.12);color:var(--sky-l);border:1px solid rgba(58,128,184,.25)}

/* ── DASHA SECTION ── */
.dasha-section{background:linear-gradient(180deg,var(--bg2),var(--bg))}
.dasha-layout{display:grid;grid-template-columns:auto 1fr;gap:60px;align-items:center}
@media(max-width:800px){.dasha-layout{grid-template-columns:1fr;text-align:center}.dasha-wheel-wrap{margin:0 auto}}
.dasha-wheel-wrap{position:relative;width:280px;height:280px;flex-shrink:0}
.dasha-labels{display:flex;flex-direction:column;gap:10px}
.dl-item{display:flex;align-items:center;gap:12px}
.dl-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.dl-lord{font-weight:700;font-size:.85rem;color:#f0e8d4;min-width:70px}
.dl-yrs{font-size:.78rem;color:var(--text-d);font-family:'DM Mono',monospace}
.dl-bar-wrap{flex:1;background:rgba(255,255,255,.06);border-radius:4px;height:5px;overflow:hidden;min-width:80px}
.dl-bar{height:100%;border-radius:4px}

/* ── CTA SECTION ── */
.cta-section{background:linear-gradient(135deg,#0a0d1a,#0f1828);border-top:1px solid rgba(200,168,75,.1);text-align:center;padding:100px 24px}
.cta-glow{width:300px;height:300px;background:radial-gradient(circle,rgba(200,168,75,.08),transparent 70%);position:absolute;left:50%;transform:translateX(-50%);pointer-events:none;margin-top:-120px}
.cta-title{font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,2.8rem);font-weight:700;color:#f0e8d4;margin-bottom:14px}
.cta-sub{color:var(--text-d);font-size:1rem;margin-bottom:36px;max-width:500px;margin-left:auto;margin-right:auto}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

/* ── FOOTER ── */
footer{background:var(--bg);border-top:1px solid rgba(255,255,255,.06);padding:32px 80px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
@media(max-width:700px){footer{padding:32px 24px;flex-direction:column;text-align:center}}
.footer-note{font-size:.8rem;color:var(--text-d);line-height:1.6}
.footer-note strong{color:var(--text-d);font-weight:600}
.footer-links{display:flex;gap:20px}
.footer-links a{font-size:.8rem;color:var(--text-d);text-decoration:none;transition:color .2s}
.footer-links a:hover{color:var(--gold)}

/* ── SCROLL REVEAL ── */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .7s cubic-bezier(.4,0,.2,1),transform .7s cubic-bezier(.4,0,.2,1)}
.reveal.visible{opacity:1;transform:none}

/* ── MISC ── */
.dot-divider{display:flex;align-items:center;gap:16px;margin:60px 0;color:var(--text-d)}
.dot-divider::before,.dot-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07)}
.dot-divider span{font-size:.6rem;letter-spacing:3px;text-transform:uppercase;font-weight:700;color:var(--gold-d)}
</style>
</head>
<body>

{{-- ── NAV ── --}}
<nav>
  <a href="/" class="nav-logo">Vedic Astro <span>Calculator</span></a>
  <div class="nav-links">
    <a href="#panchanga">Panchanga</a>
    <a href="#planets">Planets</a>
    <a href="#festivals">Festivals</a>
    <a href="#features">Features</a>
    <a href="/astro" class="nav-cta">✦ Open Calculator</a>
  </div>
</nav>

{{-- ── HERO ── --}}
<section class="hero">
  <div class="hero-bg"></div>
  <canvas id="starsCanvas"></canvas>

  <div class="hero-content">
    <div class="hero-badge">✦ Vedic Jyotish · Jean Meeus Algorithms</div>
    <h1 class="hero-title" id="heroTitle">
      Vedic <em class="gold">Astro</em><br>Calculator
    </h1>
    <p class="hero-sub">
      Precise planetary calculations, Panchanga, Vimshottari Dasha,
      Shodashvarga charts and Muhurat — computed in real time.
    </p>
    <div class="hero-btns">
      <a href="/astro" class="btn-primary">✦ Calculate Your Chart</a>
      <a href="#panchanga" class="btn-ghost">Today's Panchanga ↓</a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="orrery-wrap" id="orreryWrap">
      <svg class="zodiac-ring" id="zodiacRing" viewBox="0 0 500 500" style="position:absolute;inset:0;width:100%;height:100%">
        {{-- 12 zodiac arc segments generated by JS --}}
      </svg>
      <canvas id="orreryCanvas" width="500" height="500" style="position:absolute;inset:0;width:100%;height:100%"></canvas>
    </div>
  </div>
</section>

{{-- ── LIVE PANCHANGA ── --}}
<section class="section pancha-section" id="panchanga">
  <div class="section-lbl">◈ Live Panchanga · New Delhi</div>
  <div class="pancha-header">
    <div class="section-title" style="margin-bottom:0">{{ $dateStr }}</div>
    <div class="pancha-date">{{ $dayName }} · IST +5:30 · Lahiri Ayanamsa {{ $ayan }}°</div>
  </div>

  <div class="pancha-grid reveal">
    {{-- Tithi --}}
    <div class="pa-card gold-t">
      <div class="pa-sym">🌙</div>
      <div class="pa-anga">① Tithi</div>
      <div class="pa-name">{{ $tithi['n'] }}</div>
      <div class="pa-sub">{{ $tithi['paksha'] }} Paksha · {{ $tithi['num'] }}/15 · {{ $elong }}° elongation</div>
      <div style="margin-top:12px">
        <div style="background:rgba(255,255,255,.06);border-radius:8px;height:5px;overflow:hidden">
          <div style="width:{{ $tithiProg }}%;height:100%;background:linear-gradient(90deg,var(--gold),var(--gold-l));border-radius:8px"></div>
        </div>
        <div style="font-size:.62rem;color:var(--text-d);margin-top:4px">{{ $tithiProg }}% through</div>
      </div>
    </div>

    {{-- Vara --}}
    <div class="pa-card sky-t">
      <div class="pa-sym">☀</div>
      <div class="pa-anga">② Vara</div>
      <div class="pa-name">{{ $pancha['vara']['n'] }}</div>
      <div class="pa-sub">{{ $pancha['vara']['en'] }} · Lord: {{ $pancha['vara']['lord'] }} · {{ $pancha['vara']['nature'] }}</div>
    </div>

    {{-- Nakshatra --}}
    <div class="pa-card gold-t">
      <div class="pa-sym">✦</div>
      <div class="pa-anga">③ Nakshatra</div>
      <div class="pa-name">{{ $pancha['moonNak']['n'] }}</div>
      <div class="pa-sub">Lord: {{ $pancha['moonNak']['l'] }} · Deity: {{ $pancha['moonNak']['d'] }} · Pada {{ $pancha['nakPada'] }}</div>
    </div>

    {{-- Yoga --}}
    <div class="pa-card purple-t">
      <div class="pa-sym">✧</div>
      <div class="pa-anga">④ Yoga</div>
      <div class="pa-name">{{ $pancha['yoga']['n'] }}</div>
      <div class="pa-sub">{{ $pancha['yoga']['nature'] }} · Lord: {{ $pancha['yoga']['lord'] }}</div>
    </div>

    {{-- Karana --}}
    <div class="pa-card accent-t">
      <div class="pa-sym">⬡</div>
      <div class="pa-anga">⑤ Karana</div>
      <div class="pa-name">{{ $karana['n'] }}</div>
      <div class="pa-sub">{{ $karana['type'] }} · Lord: {{ $karana['lord'] }}</div>
    </div>

    {{-- Sunrise / Sunset --}}
    <div class="pa-card sky-t">
      <div class="pa-sym">🌅</div>
      <div class="pa-anga">☀ Surya</div>
      <div class="pa-name" style="font-family:'DM Mono',monospace;font-size:1rem">{{ $sunrise }} — {{ $sunset }}</div>
      <div class="pa-sub">Sunrise · Sunset · New Delhi</div>
    </div>
  </div>
</section>

<div class="dot-divider" style="margin:0 80px"><span>◈ &nbsp; Graha Sthiti &nbsp; ◈</span></div>

{{-- ── PLANETARY POSITIONS ── --}}
<section class="section" id="planets" style="padding-top:40px">
  <div class="section-lbl">◈ Current Planetary Positions · Sidereal (Lahiri)</div>
  <h2 class="section-title">Nine Grahas — Live Positions</h2>
  <p class="section-sub">Geocentric sidereal longitudes calculated for New Delhi, India at the time this page was rendered.</p>

  <div class="planets-grid reveal">
    @foreach($planetDisplay as $pid => $p)
    <div class="pcard" style="border-left:3px solid {{ $p['color'] }}40">
      <div class="pc-sym" style="color:{{ $p['color'] }}">{{ $p['sym'] }}</div>
      <div class="pc-body">
        <div class="pc-label" style="color:{{ $p['color'] }}">{{ $p['label'] }}</div>
        <div class="pc-name">{{ ucfirst($pid) }}</div>
        <div class="pc-sign">{{ $p['sign'] }}</div>
        <div class="pc-deg">{{ $p['deg'] }} in sign</div>
        <div class="pc-nak">{{ $p['nak'] }} · {{ $p['lord'] }}</div>
        @if($p['retro'])<div class="pc-retro">↺ Retrograde</div>@endif
      </div>
    </div>
    @endforeach
  </div>
</section>

<div class="dot-divider" style="margin:0 80px"><span>◈ &nbsp; Visheshata &nbsp; ◈</span></div>

{{-- ── FEATURES ── --}}
<section class="section" id="features" style="padding-top:40px">
  <div class="section-lbl">◈ Calculation Engine</div>
  <h2 class="section-title">Full Jyotish Suite</h2>
  <p class="section-sub">Every module uses the same precision planetary engine — Jean Meeus Astronomical Algorithms with Lahiri Ayanamsa.</p>

  <div class="features-grid reveal">
    @foreach([
      ['◈', 'D1 Birth Chart', 'North-Indian Kundali with 9 planets, Lagna, Ascendant, Descendant, MC and IC. Whole-sign house system, Lahiri ayanamsa.', '/astro'],
      ['🔳', 'Shodashvarga', '20 divisional charts (D1–D60): Navamsha, Dashamsha, Drekkana and more — all rendered as North-Indian charts.', '/astro'],
      ['⚖', 'Shadbala', 'Complete six-fold planetary strength: Sthana, Dig, Kaala, Chesta, Naisargika, Drig Bala per Brihat Parashara.', '/astro'],
      ['⏳', 'Vimshottari Dasha', '120-year cycle with nested Mahadasha → Antardasha → Pratyantar → Sookshma levels with exact dates.', '/astro'],
      ['✦', 'Muhurat Calculator', 'Vivah, Griha Pravesh, Vahana, Mundan and Sampatti muhurtas — day scan, month scan, and full year scan.', '/astro'],
      ['🪔', 'Festival Calendar', '228+ Hindu festivals, vratas and ekadashis with significance, rituals and mantras. Sankranti, Purnima, Amavasya.', '/astro'],
    ] as [$icon, $title, $desc, $link])
    <div class="feat-card">
      <span class="feat-icon">{{ $icon }}</span>
      <div class="feat-title">{{ $title }}</div>
      <div class="feat-desc">{{ $desc }}</div>
      <a href="{{ $link }}" class="feat-link">Open Calculator →</a>
    </div>
    @endforeach
  </div>
</section>

<div class="dot-divider" style="margin:0 80px"><span>◈ &nbsp; Utsav &nbsp; ◈</span></div>

{{-- ── UPCOMING FESTIVALS ── --}}
<section class="section" id="festivals" style="padding-top:40px">
  <div class="section-lbl">◈ Upcoming Hindu Festivals & Vratas</div>
  <h2 class="section-title">Festival Calendar</h2>
  <p class="section-sub">Computed from astronomical tithi positions for New Delhi, India.</p>

  <div class="fest-grid reveal">
    @foreach($upcoming as $f)
    @php
      $fdate = \DateTime::createFromFormat('Y-m-d', $f['date']);
      $fdisplay = $fdate ? $fdate->format('d M') : $f['date'];
    @endphp
    <div class="fest-card">
      <div class="fest-icon">{{ $f['icon'] ?? '✦' }}</div>
      <div class="fest-body">
        <div class="fest-date">{{ $fdisplay ?? $f['date'] }}</div>
        <div class="fest-name">{{ $f['name'] }}</div>
        <div class="fest-masa">{{ $f['masa'] ?? '' }}{{ isset($f['paksha']) && $f['paksha'] ? ' · ' . $f['paksha'] : '' }}</div>
        <span class="fest-badge {{ $f['type'] === 'festival' ? 'badge-festival' : 'badge-vrat' }}">{{ $f['type'] }}</span>
      </div>
    </div>
    @endforeach
  </div>
  <div style="text-align:center;margin-top:32px">
    <a href="/astro" class="btn-ghost" style="font-size:.85rem">View Full Calendar in Calculator →</a>
  </div>
</section>

<div class="dot-divider" style="margin:0 80px"><span>◈ &nbsp; Dasha Chakra &nbsp; ◈</span></div>

{{-- ── DASHA WHEEL ── --}}
<section class="section dasha-section" id="dasha" style="padding-top:40px">
  <div class="section-lbl">◈ Vimshottari Dasha</div>
  <h2 class="section-title">120-Year Planetary Cycle</h2>
  <p class="section-sub" style="margin-bottom:40px">Each of the 9 lords rules for a fixed period based on the Moon's nakshatra at birth. The cycle totals 120 years.</p>

  <div class="dasha-layout reveal">
    <div class="dasha-wheel-wrap">
      <svg id="dashaWheel" viewBox="0 0 280 280" style="width:100%;height:100%"></svg>
    </div>
    <div class="dasha-labels" id="dashaLegend"></div>
  </div>
</section>

{{-- ── CTA ── --}}
<section class="cta-section" style="position:relative">
  <div class="cta-glow"></div>
  <div style="position:relative;z-index:1">
    <div class="section-lbl" style="justify-content:center;margin-bottom:20px">◈ Get Started</div>
    <h2 class="cta-title">Calculate Your Birth Chart</h2>
    <p class="cta-sub">Enter your date, time and location to generate a complete Jyotish reading — planets, lagna, dasha, panchanga and more.</p>
    <div class="cta-btns">
      <a href="/astro" class="btn-primary" style="font-size:1rem;padding:16px 40px">✦ Open Calculator</a>
    </div>
  </div>
</section>

{{-- ── FOOTER ── --}}
<footer>
  <div class="footer-note">
    <strong>Vedic Astro Calculator</strong> · Planetary positions calculated using <strong>Jean Meeus Astronomical Algorithms (2nd Ed.)</strong><br>
    Lahiri Ayanamsa · BPHS rules · Brihat Parashara Hora Shastra
  </div>
  <div class="footer-links">
    <a href="/astro">Calculator</a>
    <a href="/astro#festivals">Festivals</a>
    <a href="/astro#muhrat">Muhurat</a>
  </div>
</footer>

{{-- ── DATA BRIDGE ── --}}
<script>
const PLANET_DATA = @json($planetDisplay);
const AYAN        = {{ $ayan }};
</script>

{{-- ── GSAP + SCROLL TRIGGER ── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

<script>
// ── Stars ─────────────────────────────────────────────────────────────────
(function () {
  const c = document.getElementById('starsCanvas');
  const resize = () => { c.width = window.innerWidth; c.height = window.innerHeight; };
  resize();
  window.addEventListener('resize', resize);
  const ctx = c.getContext('2d');
  const stars = Array.from({length: 220}, () => ({
    x: Math.random(), y: Math.random(),
    r: Math.random() * 1.2 + 0.2,
    a: Math.random(),
    s: 0.3 + Math.random() * 0.7,
    p: Math.random() * Math.PI * 2,
  }));
  let t = 0;
  function draw() {
    ctx.clearRect(0, 0, c.width, c.height);
    stars.forEach(s => {
      const alpha = s.a * 0.7 * (0.5 + 0.5 * Math.sin(t * s.s + s.p));
      ctx.fillStyle = `rgba(220,210,190,${alpha})`;
      ctx.beginPath();
      ctx.arc(s.x * c.width, s.y * c.height, s.r, 0, Math.PI * 2);
      ctx.fill();
    });
    t += 0.008;
    requestAnimationFrame(draw);
  }
  draw();
})();

// ── Zodiac Ring SVG ──────────────────────────────────────────────────────
(function () {
  const svg = document.getElementById('zodiacRing');
  const ns  = 'http://www.w3.org/2000/svg';
  const cx  = 250, cy = 250, ro = 238, ri = 200;
  const signs  = ['♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓'];
  const rashis = ['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya','Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena'];
  const hues   = [0,30,60,90,120,150,180,210,240,270,300,330];

  for (let i = 0; i < 12; i++) {
    const a1 = ((i * 30) - 90) * Math.PI / 180;
    const a2 = (((i + 1) * 30) - 90) * Math.PI / 180;
    const path = document.createElementNS(ns, 'path');
    const x1o = cx + ro * Math.cos(a1), y1o = cy + ro * Math.sin(a1);
    const x2o = cx + ro * Math.cos(a2), y2o = cy + ro * Math.sin(a2);
    const x1i = cx + ri * Math.cos(a2), y1i = cy + ri * Math.sin(a2);
    const x2i = cx + ri * Math.cos(a1), y2i = cy + ri * Math.sin(a1);
    path.setAttribute('d', `M${x1o},${y1o} A${ro},${ro} 0 0,1 ${x2o},${y2o} L${x1i},${y1i} A${ri},${ri} 0 0,0 ${x2i},${y2i} Z`);
    path.setAttribute('fill', `hsla(${hues[i]},40%,18%,0.7)`);
    path.setAttribute('stroke', `hsla(${hues[i]},50%,40%,0.25)`);
    path.setAttribute('stroke-width', '0.8');
    svg.appendChild(path);

    // Symbol
    const am = ((i + 0.5) * 30 - 90) * Math.PI / 180;
    const rm = (ri + ro) / 2;
    const tx = document.createElementNS(ns, 'text');
    tx.setAttribute('x', cx + rm * Math.cos(am));
    tx.setAttribute('y', cy + rm * Math.sin(am));
    tx.setAttribute('text-anchor', 'middle');
    tx.setAttribute('dominant-baseline', 'central');
    tx.setAttribute('fill', `hsla(${hues[i]},60%,70%,0.6)`);
    tx.setAttribute('font-size', '14');
    tx.textContent = signs[i];
    svg.appendChild(tx);
  }

  // Outer glow ring
  const ring = document.createElementNS(ns, 'circle');
  ring.setAttribute('cx', cx); ring.setAttribute('cy', cy);
  ring.setAttribute('r', ro + 6);
  ring.setAttribute('fill', 'none');
  ring.setAttribute('stroke', 'rgba(200,168,75,0.15)');
  ring.setAttribute('stroke-width', '1.5');
  svg.appendChild(ring);

  // Inner boundary
  const inner = document.createElementNS(ns, 'circle');
  inner.setAttribute('cx', cx); inner.setAttribute('cy', cy);
  inner.setAttribute('r', ri - 1);
  inner.setAttribute('fill', 'none');
  inner.setAttribute('stroke', 'rgba(200,168,75,0.1)');
  inner.setAttribute('stroke-width', '1');
  svg.appendChild(inner);
})();

// ── Orrery Canvas ────────────────────────────────────────────────────────
(function () {
  const canvas = document.getElementById('orreryCanvas');
  const ctx    = canvas.getContext('2d');
  const cx = 250, cy = 250;

  const orbits = {
    moon: 58, mercury: 88, venus: 116, sun: 144,
    mars: 166, jupiter: 188, saturn: 210, rahu: 232
  };
  const colors = {
    sun: '#d4921e', moon: '#90b8d8', mercury: '#28a870',
    venus: '#c060a0', mars: '#e83820', jupiter: '#c89030',
    saturn: '#7060a8', rahu: '#208048', ketu: '#a03818'
  };
  const labels = {
    sun:'Su', moon:'Mo', mercury:'Me', venus:'Ve', mars:'Ma',
    jupiter:'Ju', saturn:'Sa', rahu:'Ra', ketu:'Ke'
  };

  let drift = 0;
  let tooltip = null;

  function draw() {
    ctx.clearRect(0, 0, 500, 500);

    // Earth glow at center
    const eg = ctx.createRadialGradient(cx, cy, 0, cx, cy, 22);
    eg.addColorStop(0, 'rgba(60,120,200,0.9)');
    eg.addColorStop(0.5, 'rgba(30,60,120,0.5)');
    eg.addColorStop(1, 'rgba(10,20,50,0)');
    ctx.fillStyle = eg;
    ctx.beginPath(); ctx.arc(cx, cy, 22, 0, Math.PI * 2); ctx.fill();
    ctx.fillStyle = '#3a78c8';
    ctx.beginPath(); ctx.arc(cx, cy, 7, 0, Math.PI * 2); ctx.fill();

    // Orbit rings with glow
    Object.values(orbits).forEach(r => {
      ctx.beginPath();
      ctx.arc(cx, cy, r, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(150,180,220,0.1)';
      ctx.lineWidth = 1;
      ctx.stroke();
    });

    // Planets
    const placed = [];
    Object.keys(orbits).forEach(pid => {
      if (!PLANET_DATA[pid]) return;
      const r   = orbits[pid];
      const lon = PLANET_DATA[pid].lon + drift;
      const ang = (lon - 90) * Math.PI / 180;
      const x   = cx + r * Math.cos(ang);
      const y   = cy + r * Math.sin(ang);
      const col = colors[pid] || '#888';
      const sz  = pid === 'sun' ? 9 : pid === 'moon' ? 6 : 5;

      // Glow
      const grd = ctx.createRadialGradient(x, y, 0, x, y, sz * 2.5);
      grd.addColorStop(0, col);
      grd.addColorStop(1, 'transparent');
      ctx.fillStyle = grd;
      ctx.beginPath(); ctx.arc(x, y, sz * 2.5, 0, Math.PI * 2); ctx.fill();

      // Dot
      ctx.fillStyle = col;
      ctx.beginPath(); ctx.arc(x, y, sz, 0, Math.PI * 2); ctx.fill();

      // Label (keep outside)
      const lr = r + 14;
      const lx = cx + lr * Math.cos(ang);
      const ly = cy + lr * Math.sin(ang);
      ctx.fillStyle = col;
      ctx.font = 'bold 9px DM Sans, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(labels[pid] || pid.substring(0,2).toUpperCase(), lx, ly);

      placed.push({pid, x, y, sz, col});
    });

    // Ketu (opposite Rahu)
    if (PLANET_DATA.rahu) {
      const r   = orbits.rahu;
      const lon = PLANET_DATA.rahu.lon + 180 + drift;
      const ang = (lon - 90) * Math.PI / 180;
      const x   = cx + r * Math.cos(ang);
      const y   = cy + r * Math.sin(ang);
      const col = colors.ketu;
      const grd = ctx.createRadialGradient(x, y, 0, x, y, 12);
      grd.addColorStop(0, col); grd.addColorStop(1, 'transparent');
      ctx.fillStyle = grd;
      ctx.beginPath(); ctx.arc(x, y, 12, 0, Math.PI * 2); ctx.fill();
      ctx.fillStyle = col;
      ctx.beginPath(); ctx.arc(x, y, 5, 0, Math.PI * 2); ctx.fill();
      ctx.fillStyle = col;
      ctx.font = 'bold 9px DM Sans, sans-serif';
      ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      const lr = r + 14, lx = cx + lr * Math.cos(ang), ly = cy + lr * Math.sin(ang);
      ctx.fillText('Ke', lx, ly);
    }

    // Tooltip
    if (tooltip) {
      ctx.fillStyle = 'rgba(10,15,28,0.9)';
      ctx.strokeStyle = 'rgba(200,168,75,0.5)';
      ctx.lineWidth = 1;
      const tw = 120, th = 44, tx2 = Math.min(tooltip.x + 12, 500 - tw - 4), ty2 = Math.max(tooltip.y - th - 4, 4);
      ctx.beginPath();
      ctx.roundRect(tx2, ty2, tw, th, 8);
      ctx.fill(); ctx.stroke();
      ctx.fillStyle = '#f0e8d4';
      ctx.font = 'bold 11px DM Sans, sans-serif';
      ctx.textAlign = 'left'; ctx.textBaseline = 'top';
      ctx.fillText(tooltip.name, tx2 + 8, ty2 + 7);
      ctx.fillStyle = 'rgba(200,168,75,0.8)';
      ctx.font = '10px DM Mono, monospace';
      ctx.fillText(tooltip.info, tx2 + 8, ty2 + 24);
    }

    drift += 0.004;
    requestAnimationFrame(draw);
  }
  draw();

  // Hover tooltip
  function getScale() {
    const rect = canvas.getBoundingClientRect();
    return { sx: 500 / rect.width, sy: 500 / rect.height, rect };
  }
  canvas.addEventListener('mousemove', e => {
    const { sx, sy, rect } = getScale();
    const mx = (e.clientX - rect.left) * sx;
    const my = (e.clientY - rect.top)  * sy;
    tooltip = null;
    const allPids = Object.keys(orbits).concat(['ketu']);
    for (const pid of allPids) {
      const src = pid === 'ketu' ? PLANET_DATA.rahu : PLANET_DATA[pid];
      if (!src) continue;
      const r = orbits[pid === 'ketu' ? 'rahu' : pid];
      const lon = src.lon + (pid === 'ketu' ? 180 : 0);
      const ang = (lon - 90) * Math.PI / 180;
      const x = cx + r * Math.cos(ang), y = cy + r * Math.sin(ang);
      if (Math.hypot(mx - x, my - y) < 14) {
        const d = pid === 'ketu' ? PLANET_DATA.ketu || src : src;
        tooltip = { x, y, name: pid.charAt(0).toUpperCase() + pid.slice(1), info: d.sign + ' ' + d.deg };
        break;
      }
    }
  });
  canvas.addEventListener('mouseleave', () => { tooltip = null; });
})();

// ── Dasha Wheel ──────────────────────────────────────────────────────────
(function () {
  const lords = [
    {n:'Ketu',    yrs:7,  col:'#a03818', sym:'☋'},
    {n:'Venus',   yrs:20, col:'#c060a0', sym:'♀'},
    {n:'Sun',     yrs:6,  col:'#d4921e', sym:'☀'},
    {n:'Moon',    yrs:10, col:'#90b8d8', sym:'☽'},
    {n:'Mars',    yrs:7,  col:'#e83820', sym:'♂'},
    {n:'Rahu',    yrs:18, col:'#208048', sym:'☊'},
    {n:'Jupiter', yrs:16, col:'#c89030', sym:'♃'},
    {n:'Saturn',  yrs:19, col:'#7060a8', sym:'♄'},
    {n:'Mercury', yrs:17, col:'#28a870', sym:'☿'},
  ];
  const svg = document.getElementById('dashaWheel');
  const legend = document.getElementById('dashaLegend');
  const ns = 'http://www.w3.org/2000/svg';
  const cx = 140, cy = 140, ro = 120, ri = 58;
  let angle = -90;

  lords.forEach(l => {
    const sweep = (l.yrs / 120) * 360;
    const a1r = angle * Math.PI / 180;
    const a2r = (angle + sweep) * Math.PI / 180;
    const am  = (angle + sweep / 2) * Math.PI / 180;

    // Arc segment
    const x1 = cx + ro * Math.cos(a1r), y1 = cy + ro * Math.sin(a1r);
    const x2 = cx + ro * Math.cos(a2r), y2 = cy + ro * Math.sin(a2r);
    const x3 = cx + ri * Math.cos(a2r), y3 = cy + ri * Math.sin(a2r);
    const x4 = cx + ri * Math.cos(a1r), y4 = cy + ri * Math.sin(a1r);
    const lg = sweep > 180 ? 1 : 0;
    const path = document.createElementNS(ns, 'path');
    path.setAttribute('d', `M${x1},${y1} A${ro},${ro} 0 ${lg},1 ${x2},${y2} L${x3},${y3} A${ri},${ri} 0 ${lg},0 ${x4},${y4} Z`);
    path.setAttribute('fill', l.col + '30');
    path.setAttribute('stroke', l.col + '80');
    path.setAttribute('stroke-width', '1');
    svg.appendChild(path);

    // Symbol label in segment
    if (sweep > 18) {
      const rm = (ri + ro) / 2;
      const tx = cx + rm * Math.cos(am), ty = cy + rm * Math.sin(am);
      const lbl = document.createElementNS(ns, 'text');
      lbl.setAttribute('x', tx); lbl.setAttribute('y', ty);
      lbl.setAttribute('text-anchor', 'middle');
      lbl.setAttribute('dominant-baseline', 'central');
      lbl.setAttribute('fill', l.col);
      lbl.setAttribute('font-size', sweep > 35 ? '14' : '10');
      lbl.textContent = l.sym;
      svg.appendChild(lbl);
    }

    // Legend row
    legend.innerHTML += `<div class="dl-item">
      <div class="dl-dot" style="background:${l.col}"></div>
      <div class="dl-lord">${l.sym} ${l.n}</div>
      <div class="dl-yrs">${l.yrs} yrs</div>
      <div class="dl-bar-wrap"><div class="dl-bar" style="width:${l.yrs / 20 * 100}%;background:${l.col}60"></div></div>
    </div>`;

    angle += sweep;
  });

  // Center
  const cCircle = document.createElementNS(ns, 'circle');
  cCircle.setAttribute('cx', cx); cCircle.setAttribute('cy', cy);
  cCircle.setAttribute('r', ri - 2);
  cCircle.setAttribute('fill', '#06080f');
  cCircle.setAttribute('stroke', 'rgba(200,168,75,0.2)');
  cCircle.setAttribute('stroke-width', '1');
  svg.appendChild(cCircle);

  const cText = document.createElementNS(ns, 'text');
  cText.setAttribute('x', cx); cText.setAttribute('y', cy - 8);
  cText.setAttribute('text-anchor', 'middle');
  cText.setAttribute('fill', 'rgba(200,168,75,0.7)');
  cText.setAttribute('font-size', '11');
  cText.setAttribute('font-family', 'Playfair Display, serif');
  cText.textContent = '120';
  svg.appendChild(cText);
  const cSub = document.createElementNS(ns, 'text');
  cSub.setAttribute('x', cx); cSub.setAttribute('y', cy + 8);
  cSub.setAttribute('text-anchor', 'middle');
  cSub.setAttribute('fill', 'rgba(200,168,75,0.4)');
  cSub.setAttribute('font-size', '8');
  cSub.textContent = 'years';
  svg.appendChild(cSub);
})();

// ── GSAP Animations ──────────────────────────────────────────────────────
window.addEventListener('load', () => {
  if (typeof gsap === 'undefined') return;
  gsap.registerPlugin(ScrollTrigger);

  // Hero entrance
  gsap.from('#heroTitle', { opacity: 0, y: 50, duration: 1.1, ease: 'power3.out' });
  gsap.from('.hero-badge', { opacity: 0, y: 20, duration: 0.8, delay: 0.15, ease: 'power3.out' });
  gsap.from('.hero-sub',   { opacity: 0, y: 30, duration: 0.9, delay: 0.3,  ease: 'power3.out' });
  gsap.from('.hero-btns',  { opacity: 0, y: 20, duration: 0.8, delay: 0.5,  ease: 'power3.out' });
  gsap.from('.orrery-wrap',{ opacity: 0, scale: 0.88, duration: 1.2, delay: 0.2, ease: 'power2.out' });

  // Scroll reveals
  document.querySelectorAll('.reveal').forEach(el => {
    ScrollTrigger.create({
      trigger: el,
      start: 'top 85%',
      onEnter: () => {
        el.classList.add('visible');
        // Stagger children if grid
        const kids = el.querySelectorAll('.pa-card,.pcard,.feat-card,.fest-card,.dl-item');
        if (kids.length) {
          gsap.from(kids, { opacity: 0, y: 22, stagger: 0.06, duration: 0.55, ease: 'power2.out', clearProps: 'all' });
        }
      },
      once: true,
    });
  });

  // Nav entrance
  gsap.from('nav', { opacity: 0, y: -20, duration: 0.6, ease: 'power2.out' });
});
</script>

</body>
</html>
