<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Akashology — Vedic Panchang & Hindu Calendar</title>
<meta name="description" content="Daily Panchang, Hindu festivals, Muhurat for marriage, griha pravesh, vehicle &amp; more. Vedic astrology, New Delhi IST.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<style>
/* ═══ TOKENS ═══ */
:root{
  --gold:#e8b848;--gold-l:#f5d070;--gold-d:#a07820;
  --green:#22c55e;--sky:#38bdf8;--purple:#a78bfa;--red:#f87171;--orange:#fbbf24;
  --rx:12px;
}
[data-theme="dark"]{
  --bg:#08060e;--bg2:#100d1c;--bg3:#181428;
  --card:#12101e;--card-b:rgba(255,255,255,.09);--card-h:#1c1830;
  --text:#f2ead8;--text-m:rgba(242,234,216,.92);--text-d:rgba(242,234,216,.72);
  --nav:#0a0814;--sh:0 4px 32px rgba(0,0,0,.6);
}
[data-theme="light"]{
  --bg:#faf6ee;--bg2:#f2e8d4;--bg3:#e8d8bc;
  --card:#fff;--card-b:rgba(120,80,8,.18);--card-h:#fdf6e4;
  --text:#18100a;--text-m:rgba(24,16,10,.88);--text-d:rgba(24,16,10,.68);
  --nav:#fff8ee;--sh:0 4px 20px rgba(80,40,0,.1);
}

/* ═══ RESET ═══ */
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);font-size:16px;line-height:1.6;overflow-x:hidden;transition:background .25s,color .25s}
a{text-decoration:none;color:inherit}
button{font-family:inherit;cursor:pointer;border:none;background:none;color:inherit}
img{display:block;max-width:100%}

/* ═══ ANIMATIONS ═══ */
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes shimmer{0%,100%{opacity:.92}50%{opacity:1}}
.fu{animation:fadeUp .45s ease both}
.fu:nth-child(1){animation-delay:0s}.fu:nth-child(2){animation-delay:.06s}
.fu:nth-child(3){animation-delay:.12s}.fu:nth-child(4){animation-delay:.17s}
.fu:nth-child(5){animation-delay:.22s}.fu:nth-child(6){animation-delay:.27s}
.rev{opacity:0;transform:translateY(22px);transition:opacity .5s ease,transform .5s ease}
.rev.in{opacity:1;transform:none}
.d1{transition-delay:.06s}.d2{transition-delay:.12s}.d3{transition-delay:.18s}

/* ═══ NAV ═══ */
.nav{position:sticky;top:0;z-index:100;background:var(--nav);border-bottom:1px solid var(--card-b);padding:0 clamp(16px,4vw,56px)}
.nav-inner{max-width:1480px;margin:0 auto;height:72px;display:flex;align-items:center;gap:10px}
.logo{display:flex;align-items:center;gap:13px;margin-right:6px}
.logo-glyphs{display:flex;align-items:center;gap:6px;font-size:2rem;line-height:1;background:rgba(232,184,72,.1);border:1px solid rgba(232,184,72,.22);border-radius:12px;padding:6px 10px}
.logo-sun{color:var(--gold);animation:shimmer 3s ease-in-out infinite;text-shadow:0 0 12px rgba(232,184,72,.5)}
.logo-moon{color:var(--purple);animation:shimmer 3s ease-in-out infinite .8s;font-size:1.7rem}
.logo-txt{font-family:'Playfair Display',serif;font-size:1.22rem;font-weight:700;letter-spacing:.3px;color:var(--text)}
.logo-sub{font-size:.80rem;font-weight:600;color:var(--text-m);letter-spacing:.7px;margin-top:2px}
.sp{flex:1}
.dnav{display:flex;align-items:center;gap:6px}
.dn-btn{width:36px;height:36px;border-radius:50%;border:1px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;font-size:1.05rem;color:var(--text-m);transition:.2s;font-weight:700}
.dn-btn:hover{border-color:var(--gold);color:var(--gold);background:rgba(232,184,72,.08)}
.dn-chip{display:flex;align-items:center;gap:6px;background:var(--card);border:1px solid var(--card-b);border-radius:22px;padding:6px 16px;font-size:.86rem;font-weight:700;color:var(--text);white-space:nowrap}
.dn-chip-sym{font-size:.95rem;color:var(--gold)}
.dn-today{background:var(--card);border:1px solid var(--card-b);border-radius:18px;padding:5px 14px;font-size:.72rem;font-weight:800;color:var(--text-m);text-transform:uppercase;letter-spacing:.9px;transition:.2s}
.dn-today:hover{border-color:var(--gold);color:var(--gold)}
.theme-btn{width:36px;height:36px;border-radius:50%;border:1px solid var(--card-b);background:var(--card);display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:.2s;color:var(--text-m)}
.theme-btn:hover{border-color:var(--gold);color:var(--gold)}
.cta-btn{display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,var(--gold),var(--gold-l));color:#0a0400;font-weight:800;font-size:.84rem;padding:9px 20px;border-radius:22px;white-space:nowrap;transition:.2s;letter-spacing:.2px;box-shadow:0 2px 10px rgba(232,184,72,.25)}
.cta-btn:hover{transform:translateY(-1px);box-shadow:0 5px 20px rgba(232,184,72,.4)}
@media(max-width:640px){.dn-today{display:none}}
@media(max-width:480px){.logo-sub{display:none}}

/* ═══ HERO ═══ */
.hero{
  position:relative;overflow:hidden;
  padding:clamp(24px,4vw,52px) clamp(16px,4vw,56px) clamp(24px,4vw,48px);
}
[data-theme="dark"] .hero{
  background:
    radial-gradient(ellipse 70% 80% at 15% 50%,rgba(100,40,200,.18) 0%,transparent 55%),
    radial-gradient(ellipse 50% 60% at 85% 30%,rgba(232,184,72,.1) 0%,transparent 50%),
    radial-gradient(ellipse 80% 40% at 50% 100%,rgba(30,60,120,.2) 0%,transparent 60%),
    linear-gradient(160deg,#0e0820 0%,#090c24 45%,#160820 100%);
}
[data-theme="light"] .hero{
  background:linear-gradient(160deg,#2a1060 0%,#182060 50%,#601828 100%);
}
.hero-inner{max-width:1480px;margin:0 auto;position:relative;z-index:1}

/* Festival pill — TOP of hero, most visible */
.hero-fest{
  display:inline-flex;align-items:center;flex-wrap:wrap;gap:7px;
  margin-bottom:16px;padding:10px 18px;
  border-radius:30px;border:1px solid rgba(232,184,72,.4);
  background:rgba(232,184,72,.1);
  backdrop-filter:none;
}
.hf-label{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:1.4px;color:var(--gold);white-space:nowrap}
.hf-sep{width:1px;height:14px;background:rgba(232,184,72,.3);flex-shrink:0}
.hf-chip{font-size:.88rem;font-weight:700;color:#fff;display:flex;align-items:center;gap:5px}
.hf-chip .dot{width:6px;height:6px;border-radius:50%;background:var(--gold);flex-shrink:0}
.hf-none{font-size:.84rem;color:rgba(255,255,255,.88)}

.hero-date{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3.5vw,2.8rem);font-weight:700;color:#fff;line-height:1.1;margin-bottom:5px;letter-spacing:-.3px}
.hero-loc{font-size:.9rem;color:rgba(255,255,255,.82);margin-bottom:22px}

/* 5 panchang pills */
.panch-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.pp{display:flex;align-items:baseline;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:8px;padding:9px 15px}
.pp-lbl{font-size:.64rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.75)}
.pp-val{font-size:.92rem;font-weight:700;color:#fff}
.pp-sub{font-size:.72rem;color:rgba(255,255,255,.82)}

/* Times strip */
.times-strip{display:flex;flex-wrap:wrap;gap:0;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;overflow:hidden;margin-bottom:12px}
.ts-item{flex:1;min-width:120px;display:flex;align-items:center;gap:9px;padding:12px 15px;border-right:1px solid rgba(255,255,255,.08)}
.ts-item:last-child{border-right:none}
.ts-sym{font-size:1.1rem;flex-shrink:0;width:20px;text-align:center}
.ts-lbl{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.75);margin-bottom:1px}
.ts-val{font-size:.9rem;font-weight:700;color:#fff;font-variant-numeric:tabular-nums}
@media(max-width:680px){.ts-item{min-width:50%}.ts-item:nth-child(2n){border-right:none}}

.sun-bar{height:4px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden}
.sun-bar-fill{height:100%;background:linear-gradient(90deg,rgba(251,191,36,.4),#fbbf24,rgba(125,211,252,.5));border-radius:3px;transition:width .8s ease}

/* ═══ PAGE WRAPPER ═══ */
.page{padding:28px clamp(16px,4vw,56px) 80px;max-width:1480px;margin:0 auto}
.sh{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.sh-t{font-family:'Playfair Display',serif;font-size:1.22rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:10px}
.sh-ico{font-size:1.1rem}
.sh-link{font-size:.82rem;font-weight:700;color:var(--gold);transition:.2s}
.sh-link:hover{color:var(--gold-l)}
.mb{margin-bottom:36px}
.card{background:var(--card);border:1px solid var(--card-b);border-radius:var(--rx)}

/* ═══ PROGRESS BAR ═══ */
.nbar{position:fixed;top:0;left:0;right:0;height:3px;z-index:99999;transform-origin:left;transform:scaleX(0);opacity:0;transition:none;background:linear-gradient(90deg,var(--gold),var(--gold-l),var(--sky))}
.nbar.go{opacity:1;transform:scaleX(.7);transition:transform 8s cubic-bezier(.08,.82,.17,1)}
.nbar.done{opacity:0;transform:scaleX(1);transition:transform .15s ease,opacity .4s .15s ease}

/* ═══ FESTIVALS ═══ */
/* Today's festival banner */
.ftb-wrap{margin-bottom:14px}
.ftb{display:flex;align-items:stretch;border-radius:var(--rx);overflow:hidden;border:1px solid var(--card-b);background:var(--card);min-height:88px}
[data-theme="dark"] .ftb{box-shadow:0 4px 24px rgba(0,0,0,.3)}
[data-theme="light"] .ftb{box-shadow:0 2px 14px rgba(0,0,0,.07)}
.ftb-stripe{width:5px;flex-shrink:0;background:var(--ftc,var(--gold))}
.ftb-body{flex:1;padding:16px 20px;display:flex;flex-direction:column;justify-content:center;gap:4px}
.ftb-tag{font-size:.6rem;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;color:var(--gold);display:flex;align-items:center;gap:5px}
.ftb-name{font-family:'Playfair Display',serif;font-size:1.45rem;font-weight:700;color:var(--ftc,var(--gold));line-height:1.15}
.ftb-type{font-size:.78rem;color:var(--text-m);font-weight:600}
.ftb-also{display:flex;flex-wrap:wrap;gap:5px;margin-top:3px}
.ftb-also-chip{font-size:.7rem;font-weight:700;background:var(--bg2);border:1px solid var(--card-b);border-radius:6px;padding:2px 9px;color:var(--text-m)}
.ftb-right{flex-shrink:0;padding:16px 20px;display:flex;flex-direction:column;align-items:flex-end;justify-content:space-between;gap:8px;min-width:120px}
.ftb-today{background:linear-gradient(135deg,var(--gold),var(--gold-l));color:#0a0400;font-weight:900;font-size:.65rem;padding:5px 14px;border-radius:20px;text-transform:uppercase;letter-spacing:.8px;white-space:nowrap}
.ftb-next{font-size:.7rem;color:var(--text-d);text-align:right;line-height:1.4}
.ftb-next strong{color:var(--text-m);font-weight:700}
.ftb-none .ftb-name{font-size:1.05rem;color:var(--text-m)}
.ftb-none .ftb-type{color:var(--gold)}
/* Upcoming strip */
.fstrip-outer{overflow-x:auto;padding-bottom:8px;-webkit-overflow-scrolling:touch}
.fstrip-outer::-webkit-scrollbar{height:3px}
.fstrip-outer::-webkit-scrollbar-track{background:var(--bg2)}
.fstrip-outer::-webkit-scrollbar-thumb{background:var(--gold-d);border-radius:2px}
.fstrip{display:flex;gap:8px;min-width:max-content;padding:2px 2px 2px 0}
.fc{flex-shrink:0;width:120px;border-radius:10px;padding:13px 12px;border:1px solid var(--card-b);background:var(--card);border-left:3px solid var(--fc,var(--gold));position:relative;transition:.18s;cursor:default}
.fc:hover{transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.25)}
.fc-date{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--fc,var(--gold));margin-bottom:6px}
.fc-name{font-weight:800;font-size:.88rem;color:var(--text);line-height:1.25;margin-bottom:4px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.fc-type{font-size:.68rem;color:var(--text-m);margin-bottom:7px}
.fc-cd{font-size:.64rem;font-weight:800;color:var(--text-m);background:var(--bg2);border-radius:5px;padding:2px 7px;display:inline-block}
.fc-cd.today{background:rgba(34,197,94,.14);color:var(--green);border:1px solid rgba(34,197,94,.2)}
.fc-cd.soon{background:rgba(232,184,72,.1);color:var(--gold);border:1px solid rgba(232,184,72,.2)}
/* More list */
.fmore{border-radius:var(--rx);overflow:hidden;border:1px solid var(--card-b);margin-top:10px;display:none}
.fmore.open{display:block}
.fmore-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--card-b);background:var(--card);transition:.15s}
.fmore-row:last-child{border-bottom:none}
.fmore-row:hover{background:var(--card-h)}
.fmr-date{font-size:.78rem;font-weight:800;color:var(--text-m);min-width:52px;flex-shrink:0}
.fmr-name{flex:1;font-weight:700;font-size:.9rem;color:var(--text)}
.fmr-type{font-size:.72rem;color:var(--text-m)}
.fmr-badge{font-size:.6rem;font-weight:800;padding:2px 8px;border-radius:8px;flex-shrink:0;white-space:nowrap}
.badge-v{background:rgba(56,189,248,.12);color:var(--sky);border:1px solid rgba(56,189,248,.22)}
.badge-p{background:rgba(167,139,250,.12);color:var(--purple);border:1px solid rgba(167,139,250,.22)}
.badge-m{background:rgba(232,184,72,.1);color:var(--gold);border:1px solid rgba(232,184,72,.2)}
.fest-actions{display:flex;align-items:center;gap:10px;margin-top:12px;flex-wrap:wrap}
.btn-outline{display:inline-flex;align-items:center;gap:6px;border:1.5px solid var(--gold);color:var(--gold);background:transparent;padding:8px 18px;border-radius:20px;font-weight:700;font-size:.78rem;transition:.2s}
.btn-outline:hover{background:rgba(232,184,72,.08)}

/* ═══ MUHURAT ═══ */
.mu-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
@media(max-width:1000px){.mu-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.mu-grid{grid-template-columns:1fr}}
.mu-card{border-radius:var(--rx);border:1px solid var(--card-b);background:var(--card);overflow:hidden;transition:.18s;display:flex;flex-direction:column}
.mu-card:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.22)}
.mu-hdr{display:flex;align-items:flex-start;gap:14px;padding:16px 16px 12px}
.mu-ico-circle{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mu-ico-svg{width:26px;height:26px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.mu-hdr-mid{flex:1;min-width:0}
.mu-name{font-weight:800;font-size:1.02rem;color:var(--text);line-height:1.2}
.mu-hi{font-size:.80rem;color:var(--text-m);margin-top:1px}
.mu-desc{font-size:.80rem;color:var(--text-d);line-height:1.45;margin-top:4px}
.mu-badge{flex-shrink:0;font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.8px;padding:5px 12px;border-radius:20px;white-space:nowrap}
.mu-badge.good{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.25)}
.mu-badge.bad{background:rgba(248,113,113,.1);color:var(--red);border:1px solid rgba(248,113,113,.2)}
.mu-factors{display:flex;align-items:center;flex-wrap:wrap;gap:6px;padding:8px 16px;background:var(--bg2);border-top:1px solid var(--card-b)}
.mu-f-lbl{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.9px;color:var(--text-d);margin-right:2px}
.mu-fpill{font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:10px;display:inline-flex;align-items:center;gap:3px}
.mu-fpill.ok{background:rgba(34,197,94,.1);color:var(--green);border:1px solid rgba(34,197,94,.18)}
.mu-fpill.no{background:rgba(248,113,113,.08);color:var(--red);border:1px solid rgba(248,113,113,.16)}
.mu-tip{font-size:.73rem;color:var(--text-d);line-height:1.45;padding:8px 16px}
.mu-times{display:grid;grid-template-columns:1fr 1fr 1fr;border-top:1px solid var(--card-b);margin-top:auto}
.mu-ti{padding:10px 12px;border-right:1px solid var(--card-b);display:flex;flex-direction:column;gap:2px}
.mu-ti:last-child{border-right:none}
.mu-ti-lbl{font-size:.60rem;font-weight:800;text-transform:uppercase;letter-spacing:.9px;margin-bottom:1px}
.mu-ti-lbl.best{color:var(--green)}
.mu-ti-lbl.ab{color:var(--gold)}
.mu-ti-lbl.rk{color:var(--red)}
.mu-ti-val{font-size:.76rem;font-weight:700;color:var(--text);font-variant-numeric:tabular-nums;line-height:1.35}

/* ═══ HOROSCOPE ═══ */
.horo-banner{border-radius:var(--rx);padding:18px 22px;margin-bottom:16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;border:1px solid rgba(167,139,250,.28)}
[data-theme="dark"] .horo-banner{background:linear-gradient(135deg,rgba(100,40,200,.14) 0%,rgba(20,10,40,.8) 100%)}
[data-theme="light"] .horo-banner{background:linear-gradient(135deg,rgba(167,139,250,.12),rgba(212,190,255,.05))}
.hb-moon-sym{font-size:2.8rem;line-height:1;flex-shrink:0}
.hb-title{font-family:'Playfair Display',serif;font-size:1.08rem;font-weight:700;color:var(--text);margin-bottom:4px}
.hb-pills{display:flex;flex-wrap:wrap;gap:5px;margin-top:6px}
.hb-pill{display:inline-flex;align-items:center;gap:4px;background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.22);border-radius:20px;padding:3px 11px;font-size:.72rem;font-weight:600;color:var(--purple);white-space:nowrap}
[data-theme="light"] .hb-pill{background:rgba(124,58,237,.08);border-color:rgba(124,58,237,.2);color:#5b21b6}
.rg{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
@media(max-width:860px){.rg{grid-template-columns:repeat(3,1fr)}}
@media(max-width:540px){.rg{grid-template-columns:repeat(2,1fr)}}
.rc{border-radius:11px;border:1px solid var(--card-b);background:var(--card);overflow:hidden;transition:.18s}
.rc:hover{transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.2)}
/* colored quality header */
.rc-hdr{display:flex;align-items:center;gap:8px;padding:9px 12px;border-bottom:1px solid var(--card-b)}
.rcq-excellent .rc-hdr{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.18)}
.rcq-good      .rc-hdr{background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.16)}
.rcq-neutral   .rc-hdr{background:rgba(100,116,139,.08)}
.rcq-caution   .rc-hdr{background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.15)}
.rc-sym-wrap{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;line-height:1;flex-shrink:0}
.rc-names{flex:1;min-width:0}
.rc-skt{font-weight:800;font-size:.9rem;color:var(--text)}
.rc-en{font-size:.66rem;color:var(--text-m)}
.rc-qlbl{font-size:.66rem;font-weight:900;letter-spacing:.4px;white-space:nowrap;flex-shrink:0}
.rcq-excellent .rc-qlbl{color:var(--green)}
.rcq-good      .rc-qlbl{color:var(--sky)}
.rcq-neutral   .rc-qlbl{color:#94a3b8}
.rcq-caution   .rc-qlbl{color:var(--orange)}
.rc-body{padding:10px 12px}
.rc-house{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-m);margin-bottom:5px}
.rc-guide{font-size:.75rem;color:var(--text);line-height:1.48}
.rc-planets{display:flex;flex-wrap:wrap;gap:4px;margin-top:7px;padding-top:6px;border-top:1px solid var(--card-b)}
.rcp{font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:6px;background:var(--bg2);white-space:nowrap}
.rc.moon-rashi{border-color:rgba(232,184,72,.4);box-shadow:0 0 0 1px rgba(232,184,72,.2)}
.rc.moon-rashi .rc-hdr{border-bottom-color:rgba(232,184,72,.2)}

/* ═══ PLANET STRIP ═══ */
.ps{display:grid;grid-template-columns:repeat(9,1fr);gap:8px}
@media(max-width:1100px){.ps{grid-template-columns:repeat(5,1fr)}}
@media(max-width:600px){.ps{grid-template-columns:repeat(3,1fr)}}
.pchip{border-radius:10px;border:1px solid var(--card-b);background:var(--card);padding:12px 8px;text-align:center;transition:.18s}
.pchip:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,.18);border-color:rgba(232,184,72,.2)}
.pc-sym{font-size:1.35rem;line-height:1;margin-bottom:5px}
.pc-lbl{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-m);margin-bottom:4px}
.pc-sign{font-size:.82rem;font-weight:700;color:var(--text)}
.pc-deg{font-size:.65rem;color:var(--text-m);margin-top:2px;font-variant-numeric:tabular-nums}
.pc-retro{font-size:.62rem;color:var(--red);font-weight:800;margin-top:2px}

/* ═══ CHOGHADIYA ═══ */
.cho-bar{display:flex;border-radius:10px;overflow:hidden;height:58px;margin:12px 0 6px;box-shadow:var(--sh)}
.cho-seg{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:default;position:relative;transition:.15s;filter:brightness(.78)}
[data-theme="light"] .cho-seg{filter:saturate(1.1) brightness(.75)}
.cho-seg.now{filter:brightness(1.05)!important;z-index:2;box-shadow:inset 0 0 0 3px rgba(255,255,255,.6)}
[data-theme="light"] .cho-seg.now{box-shadow:inset 0 0 0 3px rgba(0,0,0,.35)}
.cho-seg-n{font-size:.72rem;font-weight:900;color:#fff;text-shadow:0 1px 5px rgba(0,0,0,.7);text-align:center;line-height:1.2}
.cho-seg-q{font-size:.6rem;font-weight:700;color:rgba(255,255,255,.9);text-align:center;margin-top:2px}
.cho-now-tag{position:absolute;top:-1px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.6);color:#fff;font-size:.5rem;font-weight:900;padding:1px 5px;border-radius:0 0 4px 4px;letter-spacing:.4px;white-space:nowrap}
.cho-lbls{display:flex;margin-bottom:12px}
.cho-lbl{flex:1;font-size:.65rem;font-weight:600;color:var(--text-m);text-align:center;white-space:nowrap}
.cho-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px}
.cho-card{border-radius:10px;padding:11px 13px;border:1px solid var(--card-b);background:var(--bg2);transition:.15s}
.cho-card.now{border-color:var(--cc,var(--gold));background:var(--cbg,var(--bg2));box-shadow:0 0 18px var(--cglow,transparent)}
.cho-now-dot{font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;margin-bottom:3px}
.cho-cn{font-size:.98rem;font-weight:800;color:var(--text)}
.cho-ct{font-size:.78rem;color:var(--text-m);margin-top:1px;font-variant-numeric:tabular-nums}
.cho-cq{font-size:.76rem;font-weight:700;margin-top:3px}
.cho-legend{display:flex;flex-wrap:wrap;gap:9px;margin-top:12px;padding-top:12px;border-top:1px solid var(--card-b)}
.cho-lgd{display:flex;align-items:center;gap:5px;font-size:.74rem;color:var(--text)}
.cho-lgd-dot{width:9px;height:9px;border-radius:3px;flex-shrink:0}

/* ═══ PANCHANG TABLE ═══ */
.pct-grid{display:grid;grid-template-columns:repeat(5,1fr)}
@media(max-width:860px){.pct-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:520px){.pct-grid{grid-template-columns:repeat(2,1fr)}}
.pct-cell{padding:15px 16px;border-right:1px solid var(--card-b);border-bottom:1px solid var(--card-b);background:var(--card)}
.pct-cell:nth-child(5n){border-right:none}
@media(max-width:860px){.pct-cell:nth-child(3n){border-right:none}}
@media(max-width:520px){.pct-cell:nth-child(2n){border-right:none}}
.pct-lbl{font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;color:var(--text-m);margin-bottom:4px}
.pct-bar{width:24px;height:2px;border-radius:1px;margin-bottom:7px}
.pct-name{font-weight:800;font-size:1rem;color:var(--text);margin-bottom:2px}
.pct-sub{font-size:.78rem;color:var(--text-m);line-height:1.4}
.pct-prog{height:2px;background:var(--bg3);border-radius:1px;margin-top:9px;overflow:hidden}
.pct-fill{height:100%;border-radius:1px;transition:width .7s ease}
.pct-explainers{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:10px}
@media(max-width:860px){.pct-explainers{grid-template-columns:repeat(3,1fr)}}
@media(max-width:520px){.pct-explainers{grid-template-columns:repeat(2,1fr)}}
.pe{padding:12px 14px;border-radius:9px;border:1px solid var(--card-b);background:var(--bg2)}
.pe-t{font-size:.75rem;font-weight:800;color:var(--gold);margin-bottom:3px}
.pe-d{font-size:.74rem;color:var(--text);line-height:1.45}

/* ═══ FOOTER ═══ */
.footer{text-align:center;padding:24px;font-size:.72rem;color:var(--text-d);border-top:1px solid var(--card-b)}
.footer a{color:var(--gold)}.footer a:hover{color:var(--gold-l)}

@media(max-width:480px){
  .ps{grid-template-columns:repeat(3,1fr)}
}
</style>
</head>
<body>

{{-- ═══ SVG ICON SPRITE ═══ --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
  <symbol id="ico-ring" viewBox="0 0 24 24"><circle cx="12" cy="14" r="6"/><path d="M9 9.5l1.5-3h3l1.5 3"/><polyline points="9 9.5 12 12 15 9.5"/></symbol>
  <symbol id="ico-house" viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/></symbol>
  <symbol id="ico-car" viewBox="0 0 24 24"><path d="M7 11l2.5-6h5L17 11"/><rect x="2" y="11" width="20" height="8" rx="2"/><circle cx="6.5" cy="19" r="1.5"/><circle cx="17.5" cy="19" r="1.5"/><line x1="2" y1="15" x2="22" y2="15"/></symbol>
  <symbol id="ico-scissors" viewBox="0 0 24 24"><circle cx="6" cy="7" r="3"/><circle cx="6" cy="17" r="3"/><line x1="8.5" y1="8.5" x2="21" y2="4"/><line x1="8.5" y1="15.5" x2="21" y2="20"/></symbol>
  <symbol id="ico-baby" viewBox="0 0 24 24"><circle cx="12" cy="6" r="3"/><path d="M4 21v-1a8 8 0 0116 0v1"/><path d="M9 21h6"/></symbol>
  <symbol id="ico-store" viewBox="0 0 24 24"><path d="M3 9l1.5-5h15L21 9"/><rect x="2" y="9" width="20" height="12" rx="1"/><line x1="2" y1="9" x2="22" y2="9"/><path d="M9 21v-6h6v6"/></symbol>
  <symbol id="ico-land" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></symbol>
  <symbol id="ico-plane" viewBox="0 0 24 24"><path d="M22 2l-7 20-4-9-9-4 20-7z"/><path d="M22 2L11 13"/></symbol>
</svg>

{{-- ═══ NAV ═══ --}}
<nav class="nav">
  <div class="nav-inner">
    <a href="/" class="logo">
      <div class="logo-glyphs"><span class="logo-sun">☀</span><span class="logo-moon">☽</span></div>
      <div><div class="logo-txt">Akashology</div><div class="logo-sub">ज्योतिष पंचांग</div></div>
    </a>
    <div class="sp"></div>
    <div class="dnav">
      <button class="dn-btn" onclick="shiftDay(-1)">‹</button>
      <div class="dn-chip"><span class="dn-chip-sym">◈</span><span id="navDate">{{ $dateDisplay }}</span></div>
      <button class="dn-btn" onclick="shiftDay(1)">›</button>
      <button class="dn-today" onclick="gotoToday()">Today</button>
    </div>
    <button class="theme-btn" onclick="toggleTheme()" id="themeBtn" title="Toggle theme">◑</button>
    <a href="/astro" class="cta-btn">✦ Kundali & Horoscope</a>
  </div>
</nav>

{{-- ═══ HERO ═══ --}}
@php
$todayFests = array_values(array_filter($upcoming??[], fn($f)=>($f['date']??'')===$date));
$upFests    = array_values(array_filter($upcoming??[], fn($f)=>($f['date']??'')>$date));
function _fc(string $n, string $t): string {
    if(stripos($n,'ekadashi')!==false||stripos($t,'fast')!==false||stripos($t,'vrat')!==false) return '#38bdf8';
    if(stripos($n,'purnima')!==false||stripos($n,'amavasya')!==false) return '#a78bfa';
    if(stripos($n,'navratri')!==false||stripos($n,'diwali')!==false||stripos($n,'holi')!==false) return '#fb923c';
    return '#e8b848';
}
function _fb(string $n, string $t): array {
    if(stripos($n,'ekadashi')!==false||stripos($t,'fast')!==false||stripos($t,'vrat')!==false) return ['Vrat','badge-v'];
    if(stripos($n,'purnima')!==false) return ['Purnima','badge-p'];
    return [($t?:'Festival'),'badge-m'];
}
@endphp
<section class="hero">
  <div class="hero-inner">
    {{-- Today's festival — MOST PROMINENT, first thing seen --}}
    <div class="hero-fest fu" id="heroFest">
      <span class="hf-label">✦ Aaj Ka Parv</span>
      <div class="hf-sep"></div>
      @if(count($todayFests)>0)
        @foreach($todayFests as $tf)
          <span class="hf-chip"><span class="dot"></span>{{ $tf['name'] }}</span>
        @endforeach
      @else
        <span class="hf-none">No festival today — next: <strong>{{ $upFests[0]['name']??'' }}</strong>
          @if(!empty($upFests[0]['date'])) ({{ \DateTime::createFromFormat('Y-m-d',$upFests[0]['date'])->format('d M') }}) @endif
        </span>
      @endif
    </div>

    <div class="hero-date fu" id="heroDate">{{ $dateDisplay }}</div>
    <div class="hero-loc fu">{{ $dayName }} &bull; New Delhi, India &bull; IST +5:30</div>

    <div class="panch-row">
      <div class="pp fu"><span class="pp-lbl">Tithi</span><span class="pp-val" id="phT">{{ $tithi['name'] }}</span><span class="pp-sub">{{ $tithi['paksha'] }}</span></div>
      <div class="pp fu"><span class="pp-lbl">Nakshatra</span><span class="pp-val" id="phN">{{ $nakshatra['name'] }}</span><span class="pp-sub">Pada {{ $nakshatra['pada'] }}</span></div>
      <div class="pp fu"><span class="pp-lbl">Yoga</span><span class="pp-val" id="phY">{{ $yoga['name'] }}</span><span class="pp-sub" id="phYC" style="color:{{ $yoga['cls']==='Subha'?'#22c55e':($yoga['cls']==='Mahavisha'?'#f87171':'#fbbf24') }}">{{ $yoga['cls'] }}</span></div>
      <div class="pp fu"><span class="pp-lbl">Karana</span><span class="pp-val" id="phK">{{ $karana['name'] }}</span><span class="pp-sub">{{ $karana['type'] }}</span></div>
      <div class="pp fu"><span class="pp-lbl">Vara</span><span class="pp-val" id="phV">{{ $vara['name'] }}</span><span class="pp-sub">{{ $vara['lord'] }}</span></div>
    </div>

    <div class="times-strip fu">
      <div class="ts-item"><div class="ts-sym" style="color:#fbbf24">☀</div><div><div class="ts-lbl">Sunrise</div><div class="ts-val" id="tsSr">{{ $sunrise }}</div></div></div>
      <div class="ts-item"><div class="ts-sym" style="color:var(--gold)">◈</div><div><div class="ts-lbl">Abhijit</div><div class="ts-val" id="tsAb">{{ $abhijit['start'] }}–{{ $abhijit['end'] }}</div></div></div>
      <div class="ts-item"><div class="ts-sym" style="color:#f87171">⚠</div><div><div class="ts-lbl">Rahu Kaal</div><div class="ts-val" id="tsRk">{{ $rahuKaal['start'] }}–{{ $rahuKaal['end'] }}</div></div></div>
      <div class="ts-item"><div class="ts-sym" style="color:var(--purple)">◐</div><div><div class="ts-lbl">Yamaganda</div><div class="ts-val" id="tsYg">{{ $yamghantam['start'] }}–{{ $yamghantam['end'] }}</div></div></div>
      <div class="ts-item"><div class="ts-sym" style="color:#7dd3fc">☽</div><div><div class="ts-lbl">Sunset</div><div class="ts-val" id="tsSs">{{ $sunset }}</div></div></div>
    </div>
    <div class="sun-bar fu"><div class="sun-bar-fill" id="sunBar" style="width:0%"></div></div>
  </div>
</section>

<div class="page">

{{-- ═══ 1. FESTIVALS ═══ --}}
@php
$festStrip = array_slice($upFests, 0, 18);
$festMore  = array_slice($upFests, 18);
@endphp
<div class="rev mb">
  <div class="sh">
    <div class="sh-t"><span class="sh-ico">🪔</span> Festivals &amp; Auspicious Days</div>
  
  </div>

  <div class="ftb-wrap" id="festTodayBanner"></div>

  <div class="fstrip-outer">
    <div class="fstrip" id="festStrip">
      @forelse($festStrip as $f)
      @php
        $fd  = \DateTime::createFromFormat('Y-m-d', $f['date']??'');
        $fn  = $f['name']??''; $ft = $f['type']??'';
        $col = _fc($fn,$ft);
        $today2 = new \DateTime('now', new \DateTimeZone('Asia/Kolkata')); $today2->setTime(0,0,0);
        $fdDay  = $fd ? clone $fd : null; if($fdDay) $fdDay->setTime(0,0,0);
        $diff   = $fd ? (int)$today2->diff($fdDay)->format('%a') : null;
        $cdTxt  = $diff===null?'':($diff===0?'Today!':($diff===1?'Tomorrow':$diff.'d away'));
      @endphp
      <div class="fc" style="--fc:{{ $col }}">
        <div class="fc-date">{{ $fd?strtoupper($fd->format('d M')):'—' }}</div>
        <div class="fc-name">{{ $fn?:'Festival' }}</div>
        <div class="fc-type">{{ $ft }}</div>
        <div class="fc-cd{{ $diff===0?' today':'' }}">{{ $cdTxt }}</div>
      </div>
      @empty
      <p style="color:var(--text-m);font-size:.85rem;padding:10px">No upcoming festivals found.</p>
      @endforelse
    </div>
  </div>

  <div class="fest-actions">
    @if(count($festMore)>0)
    <button class="btn-outline" id="btnMore" onclick="toggleMore()">
      <span id="btnMoreTxt">+ {{ count($festMore) }} More Festivals</span> ↓
    </button>
    @endif
  
  </div>

  @if(count($festMore)>0)
  <div class="fmore" id="festMore">
    @foreach($festMore as $f)
    @php
      $fd3 = \DateTime::createFromFormat('Y-m-d', $f['date']??'');
      $fn3=$f['name']??''; $ft3=$f['type']??'';
      [$bl3,$bc3] = _fb($fn3,$ft3);
    @endphp
    <div class="fmore-row">
      <div class="fmr-date">{{ $fd3?$fd3->format('d M'):'—' }}</div>
      <div style="flex:1;min-width:0"><div class="fmr-name">{{ $fn3 }}</div>@if($ft3)<div class="fmr-type">{{ $ft3 }}</div>@endif</div>
      <span class="fmr-badge {{ $bc3 }}">{{ $bl3 }}</span>
    </div>
    @endforeach
  </div>
  @endif
</div>

{{-- ═══ 2. MUHURAT ═══ --}}
@php
$vi=$vara['idx']; $tn=$tithi['num']; $ycl=$yoga['cls'];
$gCho=array_values(array_filter($choghadiya,fn($c)=>in_array($c['name'],['Amrit','Shubha','Labha'])));
$bw=count($gCho)?implode(', ',array_map(fn($c)=>$c['start'].'–'.$c['end'],array_slice($gCho,0,2))):'None today';
$ab=$abhijit['start'].'–'.$abhijit['end'];
$rk=$rahuKaal['start'].'–'.$rahuKaal['end'];
// [ico, name, hi, col, desc, good, fVara, fTithi, fYoga, tip]
$MU=[
  ['ico-ring',    'Vivah Muhurat','विवाह',      '#d4a0c0','Marriage, engagement & roka',
   !in_array($vi,[2,6])&&!in_array($tn,[4,8,9,14])&&$ycl!=='Mahavisha'&&in_array($vi,[0,1,3,4,5]),
   !in_array($vi,[2,6])&&in_array($vi,[0,1,3,4,5]),  !in_array($tn,[4,8,9,14]),  $ycl!=='Mahavisha',
   'Sun, Mon, Wed, Thu, Fri best. Avoid Chaturthi, Ashtami & Chaturdashi tithis.'],
  ['ico-house',   'Griha Pravesh','गृह प्रवेश','#40c880','Housewarming & property entry',
   !in_array($vi,[2,6])&&!in_array($tn,[4,8,9,14])&&$ycl!=='Mahavisha',
   !in_array($vi,[2,6]),  !in_array($tn,[4,8,9,14]),  $ycl!=='Mahavisha',
   'Avoid Tue & Sat. Rikta (4th, 8th, 14th) tithis inauspicious. Uttaraphalguni nakshatra ideal.'],
  ['ico-car',     'Vehicle Buy',  'वाहन',       '#38bdf8','Car, bike or any vehicle purchase',
   $ycl!=='Mahavisha'&&in_array($vi,[0,1,3,4,5])&&!in_array($tn,[4,8,14]),
   in_array($vi,[0,1,3,4,5]),  !in_array($tn,[4,8,14]),  $ycl!=='Mahavisha',
   'Sun–Fri favoured. Hastha & Rohini nakshatras highly auspicious for vehicle purchase.'],
  ['ico-scissors','Mundan',       'मुंडन',      '#f59e0b','Child\'s first tonsure ceremony',
   !in_array($vi,[2,6])&&$ycl!=='Mahavisha'&&in_array($tn,[2,3,5,6,7,10,11,12]),
   !in_array($vi,[2,6]),  in_array($tn,[2,3,5,6,7,10,11,12]),  $ycl!=='Mahavisha',
   'Dvitiya, Tritiya, Panchami, Saptami or Dashami tithi preferred. Avoid Tue & Sat.'],
  ['ico-baby',    'Namkaran',     'नामकरण',    '#f0c820','Newborn naming ceremony (11th day)',
   !in_array($vi,[2,6])&&$ycl!=='Mahavisha',
   !in_array($vi,[2,6]),  true,  $ycl!=='Mahavisha',
   'Performed on the 11th day after birth. Avoid Tuesday & Saturday. Any tithi acceptable.'],
  ['ico-store',   'Business',     'व्यापार',   '#e8b848','Shop opening & new venture launch',
   !in_array($vi,[2,6])&&$ycl!=='Mahavisha'&&in_array($vi,[1,3,4,5]),
   !in_array($vi,[2,6])&&in_array($vi,[1,3,4,5]),  true,  $ycl!=='Mahavisha',
   'Mon, Wed, Thu, Fri most auspicious for business. Labha choghadiya window is ideal.'],
  ['ico-land',    'Land / Plot',  'भूमि पूजन','#a78bfa','Land purchase & bhoomi pujan',
   !in_array($vi,[2,6])&&!in_array($tn,[4,8,9,14])&&$ycl!=='Mahavisha',
   !in_array($vi,[2,6]),  !in_array($tn,[4,8,9,14]),  $ycl!=='Mahavisha',
   'Avoid Rikta tithis & Amavasya. Hasta, Rohini & Uttara nakshatras most favoured.'],
  ['ico-plane',   'Travel',       'यात्रा',    '#7dd3fc','Journey, pilgrimage, long travel',
   $ycl!=='Mahavisha'&&in_array($vi,[1,3,4])&&!in_array($tn,[4,8,14]),
   in_array($vi,[1,3,4]),  !in_array($tn,[4,8,14]),  $ycl!=='Mahavisha',
   'Mon, Wed, Thu best for journeys. Mrigashira & Hasta nakshatras very auspicious.'],
];
@endphp
<div class="rev mb">
  <div class="sh">
    <div class="sh-t"><span class="sh-ico">✦</span> Today&apos;s Muhurat</div>
    <a href="/astro" class="sh-link">Full Details ›</a>
  </div>
  <div class="mu-grid" id="muGrid">
    @foreach($MU as $mu)
    @php [$ico,$name,$hi,$col,$desc,$good,$fVara,$fTithi,$fYoga,$tip]=$mu; @endphp
    <div class="mu-card">
      <div class="mu-hdr">
        <div class="mu-ico-circle" style="background:{{ $col }}22;color:{{ $col }}">
          <svg class="mu-ico-svg"><use href="#{{ $ico }}"/></svg>
        </div>
        <div class="mu-hdr-mid">
          <div class="mu-name">{{ $name }}</div>
          <div class="mu-hi">{{ $hi }}</div>
          <div class="mu-desc">{{ $desc }}</div>
        </div>
        <span class="mu-badge {{ $good?'good':'bad' }}">{{ $good?'✓ Good':'✗ Avoid' }}</span>
      </div>
      <div class="mu-factors">
        <span class="mu-f-lbl">Factors:</span>
        <span class="mu-fpill {{ $fVara?'ok':'no' }}">{{ $fVara?'✓':'✗' }} Vara</span>
        <span class="mu-fpill {{ $fTithi?'ok':'no' }}">{{ $fTithi?'✓':'✗' }} Tithi</span>
        <span class="mu-fpill {{ $fYoga?'ok':'no' }}">{{ $fYoga?'✓':'✗' }} Yoga</span>
      </div>
      <div class="mu-tip">{{ $tip }}</div>
      <div class="mu-times">
        <div class="mu-ti"><div class="mu-ti-lbl best">★ Best Time</div><div class="mu-ti-val">{{ $bw }}</div></div>
        <div class="mu-ti"><div class="mu-ti-lbl ab">◈ Abhijit</div><div class="mu-ti-val">{{ $ab }}</div></div>
        <div class="mu-ti"><div class="mu-ti-lbl rk">✗ Rahu Kaal</div><div class="mu-ti-val">{{ $rk }}</div></div>
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- ═══ 3. HOROSCOPE ═══ --}}
<div class="rev mb">
  <div class="sh">
    <div class="sh-t"><span class="sh-ico">◎</span> Rashi Daily Horoscope</div>
    <a href="/astro" class="sh-link">Birth Chart ›</a>
  </div>
  <div class="horo-banner">
    <div class="hb-moon-sym">☽</div>
    <div style="flex:1;min-width:0">
      <div class="hb-title">Moon Transit · Daily Rashi Reading</div>
      <div id="horoNote"></div>
    </div>
  </div>
  <div class="rg" id="rashibGrid"><div style="grid-column:1/-1;padding:20px;color:var(--text-m);font-size:.82rem;text-align:center">Loading horoscope…</div></div>
</div>

{{-- ═══ 4. PLANET POSITIONS ═══ --}}
<div class="rev mb">
  <div class="sh">
    <div class="sh-t"><span class="sh-ico">◉</span> Today&apos;s Planet Positions</div>
    <a href="/astro" class="sh-link">Full Horoscope ›</a>
  </div>
  <div class="ps" id="planetStrip"><div style="grid-column:1/-1;padding:12px;color:var(--text-m);font-size:.82rem;opacity:.6">Loading…</div></div>
</div>

{{-- ═══ 5. CHOGHADIYA ═══ --}}
@php
$CC=['Amrit'=>'#22c55e','Shubha'=>'#38bdf8','Labha'=>'#e8b848','Char'=>'#64748b','Udveg'=>'#fbbf24','Kaal'=>'#f87171','Rog'=>'#f43f5e'];
$CQ=['Amrit'=>'Best','Shubha'=>'Good','Labha'=>'Good','Char'=>'Neutral','Udveg'=>'Caution','Kaal'=>'Avoid','Rog'=>'Avoid'];
$nowH=(float)date('G')+(float)date('i')/60;
@endphp
<div class="rev mb">
  <div class="sh"><div class="sh-t"><span class="sh-ico">⏱</span> Today&apos;s Choghadiya</div></div>
  <div class="card" style="padding:18px 20px">
    <div class="cho-bar" id="choBar">
      @foreach($choghadiya as $ch)
        @php $isN=$ch['startHr']<=$nowH&&$nowH<$ch['endHr']; $c=$CC[$ch['name']]??'#888'; @endphp
        <div class="cho-seg{{ $isN?' now':'' }}" style="background:{{ $c }}" title="{{ $ch['name'] }} {{ $ch['start'] }}–{{ $ch['end'] }}">
          @if($isN)<div class="cho-now-tag">NOW</div>@endif
          <div class="cho-seg-n">{{ $ch['name'] }}</div>
          <div class="cho-seg-q">{{ $CQ[$ch['name']]??'' }}</div>
        </div>
      @endforeach
    </div>
    <div class="cho-lbls" id="choLbls">
      @foreach($choghadiya as $ch)<div class="cho-lbl">{{ $ch['start'] }}</div>@endforeach
    </div>
    <div class="cho-cards" id="choCards">
      @foreach($choghadiya as $ch)
        @php $isN=$ch['startHr']<=$nowH&&$nowH<$ch['endHr']; $c=$CC[$ch['name']]??'#888'; @endphp
        <div class="cho-card{{ $isN?' now':'' }}" @if($isN) style="--cc:{{ $c }};--cbg:{{ $c }}18;--cglow:{{ $c }}40" @endif>
          @if($isN)<div class="cho-now-dot" style="color:{{ $c }}">● Now</div>@endif
          <div class="cho-cn" style="{{ $isN?'color:'.$c:'' }}">{{ $ch['name'] }}</div>
          <div class="cho-ct">{{ $ch['start'] }} – {{ $ch['end'] }}</div>
          <div class="cho-cq" style="color:{{ $c }}">{{ $CQ[$ch['name']]??'' }}</div>
        </div>
      @endforeach
    </div>
    <div class="cho-legend">
      @foreach([['#22c55e','Amrit','Best'],['#38bdf8','Shubha','Good'],['#e8b848','Labha','Good'],['#64748b','Char','Neutral'],['#fbbf24','Udveg','Caution'],['#f87171','Kaal','Avoid'],['#f43f5e','Rog','Avoid']] as $lg)
        <div class="cho-lgd"><div class="cho-lgd-dot" style="background:{{ $lg[0] }}"></div><span>{{ $lg[1] }}</span><span style="color:var(--text-d);margin-left:2px">{{ $lg[2] }}</span></div>
      @endforeach
    </div>
  </div>
</div>

{{-- ═══ 6. FULL PANCHANG ═══ --}}
<div class="rev mb">
  <div class="sh">
    <div class="sh-t"><span class="sh-ico">📋</span> Full Panchang</div>
    <a href="/astro" class="sh-link">Open Kundali Tool ›</a>
  </div>
  <div class="card" style="overflow:hidden;border-radius:var(--rx)">
    <div style="padding:13px 18px;border-bottom:1px solid var(--card-b);background:var(--bg2);font-weight:700;font-size:.86rem;display:flex;align-items:center;gap:8px">
      <span style="color:var(--gold)">◈</span><span id="pctDate">{{ $dateDisplay }}</span><span style="color:var(--text-d);font-weight:400"> — New Delhi, India</span>
    </div>
    <div class="pct-grid">
      <div class="pct-cell"><div class="pct-lbl">Tithi</div><div class="pct-bar" style="background:var(--purple)"></div><div class="pct-name" id="pctT">{{ $tithi['name'] }}</div><div class="pct-sub" id="pctTS">{{ $tithi['paksha'] }} · {{ $tithi['num'] }}/15 · {{ $tithi['lord'] }}</div><div class="pct-prog"><div class="pct-fill" id="pctTPf" style="width:{{ $tithi['prog'] }}%;background:var(--purple)"></div></div></div>
      <div class="pct-cell"><div class="pct-lbl">Vara</div><div class="pct-bar" id="pctVBar" style="background:{{ $vara['color']??'var(--gold)' }}"></div><div class="pct-name" id="pctV">{{ $vara['name'] }}</div><div class="pct-sub" id="pctVS">{{ $vara['en'] }} · Lord: {{ $vara['lord'] }} · {{ $vara['nature'] }}</div><div class="pct-prog"></div></div>
      <div class="pct-cell"><div class="pct-lbl">Nakshatra</div><div class="pct-bar" style="background:var(--sky)"></div><div class="pct-name" id="pctN">{{ $nakshatra['name'] }}</div><div class="pct-sub" id="pctNS">Pada {{ $nakshatra['pada'] }} · {{ $nakshatra['lord'] }} · {{ $nakshatra['gana'] }}</div><div class="pct-prog"><div class="pct-fill" id="pctNPf" style="width:{{ $nakshatra['prog'] }}%;background:var(--sky)"></div></div></div>
      <div class="pct-cell"><div class="pct-lbl">Yoga</div><div class="pct-bar" style="background:var(--green)"></div><div class="pct-name" id="pctY">{{ $yoga['name'] }}</div><div class="pct-sub" id="pctYS">{{ $yoga['nature'] }} · {{ $yoga['lord'] }} · {{ $yoga['cls'] }}</div><div class="pct-prog"><div class="pct-fill" id="pctYPf" style="width:{{ $yoga['prog'] }}%;background:var(--green)"></div></div></div>
      <div class="pct-cell" style="border-right:none"><div class="pct-lbl">Karana</div><div class="pct-bar" style="background:var(--orange)"></div><div class="pct-name" id="pctK">{{ $karana['name'] }}</div><div class="pct-sub" id="pctKS">{{ $karana['type'] }} · Slot {{ $karana['slot'] }}/60 · {{ $karana['lord'] }}</div><div class="pct-prog"><div class="pct-fill" id="pctKPf" style="width:{{ $karana['prog'] }}%;background:var(--orange)"></div></div></div>
    </div>
  </div>
  <div class="pct-explainers">
    <div class="pe"><div class="pe-t">Tithi</div><div class="pe-d">Lunar day (Sun–Moon angle). Governs auspiciousness.</div></div>
    <div class="pe"><div class="pe-t">Vara</div><div class="pe-d">Weekday ruled by a planet that colours the day's energy.</div></div>
    <div class="pe"><div class="pe-t">Nakshatra</div><div class="pe-d">Moon's star mansion (1 of 27). Determines mood and timing.</div></div>
    <div class="pe"><div class="pe-t">Yoga</div><div class="pe-d">Sun+Moon longitude combined — overall quality of the day.</div></div>
    <div class="pe"><div class="pe-t">Karana</div><div class="pe-d">Half a Tithi. Rotates 60× per month for micro timing.</div></div>
  </div>
</div>

</div>{{-- /.page --}}

<footer class="footer">
  <p>Akashology · Vedic Panchang · New Delhi · Lahiri Ayanamsa · IST +5:30 · <a href="/astro">Kundali & Vedic Chart →</a></p>
</footer>

<div class="nbar" id="nBar"></div>

@php
$_ld=['tithi'=>$tithi,'vara'=>$vara,'nakshatra'=>$nakshatra,'yoga'=>$yoga,'karana'=>$karana,
      'choghadiya'=>$choghadiya,'sunrise'=>$sunrise,'sunset'=>$sunset,'rahuKaal'=>$rahuKaal,
      'abhijit'=>$abhijit,'yamghantam'=>$yamghantam,'date'=>$date,'dateDisplay'=>$dateDisplay,
      'dayName'=>$dayName,'planets'=>$planets,'ascSignIdx'=>$ascSignIdx];
@endphp
<script>
const PD=@json($_ld), UPF=@json($upcoming??[]);
let curDate='{{ $date }}';
const el=id=>document.getElementById(id);
const st=(id,v)=>{const e=el(id);if(e)e.textContent=v};
const sw=(id,v,u='%')=>{const e=el(id);if(e)e.style.width=v+u};

/* ── THEME ── */
(()=>{
  const t=localStorage.getItem('th')||'dark';
  document.documentElement.dataset.theme=t;
  const b=el('themeBtn');if(b)b.textContent=t==='dark'?'◑':'◐';
})();
function toggleTheme(){
  const d=document.documentElement,t=d.dataset.theme==='dark'?'light':'dark';
  d.dataset.theme=t;localStorage.setItem('th',t);
  const b=el('themeBtn');if(b)b.textContent=t==='dark'?'◑':'◐';
}

/* ── SCROLL REVEAL ── */
const obs=new IntersectionObserver(es=>{es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');obs.unobserve(e.target)}})},{threshold:.06});
document.querySelectorAll('.rev').forEach(e=>obs.observe(e));

/* ── SUN BAR ── */
function sunBar(d){
  const h=t=>{if(!t||t==='—')return null;const[a,b]=t.split(':').map(Number);return a+b/60};
  const r=h(d.sunrise),s=h(d.sunset),now=new Date().getHours()+new Date().getMinutes()/60;
  const p=r&&s&&s>r?Math.max(0,Math.min(100,(now-r)/(s-r)*100)):0;
  sw('sunBar',p);
}

/* ── DATE NAV ── */
const _DC={};
const _dtFmt=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
function nStart(){const b=el('nBar');if(!b)return;b.classList.remove('done');b.classList.add('go')}
function nEnd(){const b=el('nBar');if(!b)return;b.classList.remove('go');b.classList.add('done');setTimeout(()=>b.classList.remove('done'),900)}
function shiftDay(n){const d=new Date(curDate+'T12:00:00');d.setDate(d.getDate()+n);loadDay(_dtFmt(d))}
function gotoToday(){loadDay(_dtFmt(new Date()))}
function prefetchDay(dt){
  if(_DC[dt])return;
  fetch(`/panchanga-data?date=${dt}`).then(r=>r.ok?r.json():null).then(d=>{if(d)_DC[dt]=d}).catch(()=>{});
}
async function loadDay(dt){
  if(_DC[dt]){
    curDate=dt;
    if(Array.isArray(_DC[dt].upcoming)){UPF.length=0;_DC[dt].upcoming.forEach(f=>UPF.push(f))}
    applyData(_DC[dt]);
    const nx=new Date(dt+'T12:00:00');nx.setDate(nx.getDate()+1);
    const pv=new Date(dt+'T12:00:00');pv.setDate(pv.getDate()-1);
    prefetchDay(_dtFmt(nx));prefetchDay(_dtFmt(pv));
    return;
  }
  nStart();
  try{
    const r=await fetch(`/panchanga-data?date=${dt}`);if(!r.ok)throw 0;
    const d=await r.json();_DC[dt]=d;curDate=dt;
    if(Array.isArray(d.upcoming)){UPF.length=0;d.upcoming.forEach(f=>UPF.push(f))}
    applyData(d);
    const nx=new Date(dt+'T12:00:00');nx.setDate(nx.getDate()+1);
    const pv=new Date(dt+'T12:00:00');pv.setDate(pv.getDate()-1);
    prefetchDay(_dtFmt(nx));prefetchDay(_dtFmt(pv));
  }catch(e){console.warn(e)}
  finally{nEnd()}
}

/* ── FESTIVAL HELPERS ── */
const FCOL={v:'#38bdf8',p:'#a78bfa',f:'#fb923c',d:'#e8b848'};
function fCol(n,t){
  if(/ekadashi|fast|vrat/i.test(n+t))return FCOL.v;
  if(/purnima|amavasya/i.test(n))return FCOL.p;
  if(/navratri|diwali|holi/i.test(n))return FCOL.f;
  return FCOL.d;
}
function fBadge(n,t){
  if(/ekadashi|fast|vrat/i.test(n+t))return['Vrat','badge-v'];
  if(/purnima/i.test(n))return['Purnima','badge-p'];
  return[t||'Festival','badge-m'];
}

/* ── TODAY'S FESTIVAL BANNER ── */
function buildFestBanner(date){
  const wrap=el('festTodayBanner');if(!wrap)return;
  const todays=UPF.filter(f=>f.date===date);
  const nextUp=UPF.filter(f=>f.date>date)[0];
  if(todays.length){
    const f=todays[0],col=fCol(f.name||'',f.type||'');
    const also=todays.slice(1).map(f2=>`<span class="ftb-also-chip">${f2.name}</span>`).join('');
    const nxt=nextUp?`<div class="ftb-next">Next: <strong>${nextUp.name}</strong> · ${new Date(nextUp.date+'T12:00:00').toLocaleDateString('en',{day:'numeric',month:'short'})}</div>`:'';
    wrap.innerHTML=`<div class="ftb" style="--ftc:${col}">
      <div class="ftb-stripe"></div>
      <div class="ftb-body">
        <div class="ftb-tag">✦ Aaj Ka Parv</div>
        <div class="ftb-name">${f.name||'Festival'}</div>
        ${f.type?`<div class="ftb-type">${f.type}</div>`:''}
        ${also?`<div class="ftb-also">${also}</div>`:''}
      </div>
      <div class="ftb-right"><span class="ftb-today">Today</span>${nxt}</div>
    </div>`;
  } else {
    const col=nextUp?fCol(nextUp.name||'',nextUp.type||''):'#e8b848';
    const days=nextUp?Math.floor((new Date(nextUp.date+'T00:00:00')-new Date(date+'T00:00:00'))/864e5):null;
    wrap.innerHTML=`<div class="ftb ftb-none" style="--ftc:${col}">
      <div class="ftb-stripe"></div>
      <div class="ftb-body">
        <div class="ftb-tag">✦ Aaj Ka Parv</div>
        <div class="ftb-name">No Festival Today</div>
        ${nextUp?`<div class="ftb-type">Next: <strong>${nextUp.name}</strong>${days?' — in '+days+' day'+(days!==1?'s':''):''}</div>`:''}
      </div>
    </div>`;
  }
}

/* ── HERO FESTIVAL ── */
function buildHeroFest(date){
  const wrap=el('heroFest');if(!wrap)return;
  const todays=UPF.filter(f=>f.date===date);
  const up=UPF.filter(f=>f.date>date);
  let html=`<span class="hf-label">✦ Aaj Ka Parv</span><div class="hf-sep"></div>`;
  if(todays.length){
    html+=todays.map(f=>`<span class="hf-chip"><span class="dot"></span>${f.name||''}</span>`).join('');
  } else {
    const nx=up[0];
    const nxTxt=nx?(nx.name+' ('+(new Date(nx.date+'T12:00:00').toLocaleDateString('en',{day:'numeric',month:'short'}))+')'):'—';
    html+=`<span class="hf-none">No festival today — next: <strong>${nxTxt}</strong></span>`;
  }
  wrap.innerHTML=html;
}

/* ── FESTIVAL STRIP ── */
function buildFestStrip(date){
  const strip=el('festStrip');if(!strip)return;
  const up=UPF.filter(f=>f.date>date).slice(0,18);
  const today=new Date();today.setHours(0,0,0,0);
  if(!up.length){strip.innerHTML='<p style="color:var(--text-m);font-size:.85rem;padding:10px">No upcoming festivals found.</p>';return}
  strip.innerHTML=up.map(f=>{
    const fd=f.date?new Date(f.date+'T00:00:00'):null;
    const dateStr=fd?fd.toLocaleDateString('en',{day:'2-digit',month:'short'}).toUpperCase():'—';
    const diff=fd?Math.floor((fd-today)/864e5):null;
    const cdTxt=diff===null?'':diff===0?'Today!':diff===1?'Tomorrow':diff+'d away';
    const c=fCol(f.name||'',f.type||'');
    return`<div class="fc" style="--fc:${c}">
      <div class="fc-date">${dateStr}</div>
      <div class="fc-name">${f.name||'Festival'}</div>
      <div class="fc-type">${f.type||''}</div>
      <div class="fc-cd${diff===0?' today':''}">${cdTxt}</div>
    </div>`;
  }).join('');
}

/* ── TOGGLE MORE FESTIVALS ── */
function toggleMore(){
  const m=el('festMore'),b=el('btnMore'),t=el('btnMoreTxt');
  if(!m)return;const open=m.classList.toggle('open');
  if(t)t.textContent=open?'Show Less':'+ More Festivals';
}

/* ── MUHURAT GRID ── */
const MU_DEF=[
  {ico:'ico-ring',    n:'Vivah Muhurat',h:'विवाह',     c:'#d4a0c0',d:'Marriage, engagement & roka',
   ok:(v,t,y)=>![2,6].includes(v)&&![4,8,9,14].includes(t)&&y!=='Mahavisha'&&[0,1,3,4,5].includes(v),
   fv:(v)=>![2,6].includes(v)&&[0,1,3,4,5].includes(v),ft:(t)=>![4,8,9,14].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Sun, Mon, Wed, Thu, Fri best. Avoid Chaturthi, Ashtami & Chaturdashi tithis.'},
  {ico:'ico-house',   n:'Griha Pravesh',h:'गृह प्रवेश',c:'#40c880',d:'Housewarming & property entry',
   ok:(v,t,y)=>![2,6].includes(v)&&![4,8,9,14].includes(t)&&y!=='Mahavisha',
   fv:(v)=>![2,6].includes(v),ft:(t)=>![4,8,9,14].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Avoid Tue & Sat. Rikta (4th, 8th, 14th) tithis inauspicious. Uttaraphalguni nakshatra ideal.'},
  {ico:'ico-car',     n:'Vehicle Buy',  h:'वाहन',      c:'#38bdf8',d:'Car, bike or any vehicle purchase',
   ok:(v,t,y)=>y!=='Mahavisha'&&[0,1,3,4,5].includes(v)&&![4,8,14].includes(t),
   fv:(v)=>[0,1,3,4,5].includes(v),ft:(t)=>![4,8,14].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Sun–Fri favoured. Hastha & Rohini nakshatras highly auspicious for vehicle purchase.'},
  {ico:'ico-scissors',n:'Mundan',       h:'मुंडन',     c:'#f59e0b',d:"Child's first tonsure ceremony",
   ok:(v,t,y)=>![2,6].includes(v)&&y!=='Mahavisha'&&[2,3,5,6,7,10,11,12].includes(t),
   fv:(v)=>![2,6].includes(v),ft:(t)=>[2,3,5,6,7,10,11,12].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Dvitiya, Tritiya, Panchami, Saptami or Dashami tithi preferred. Avoid Tue & Sat.'},
  {ico:'ico-baby',    n:'Namkaran',     h:'नामकरण',    c:'#f0c820',d:'Newborn naming ceremony (11th day)',
   ok:(v,t,y)=>![2,6].includes(v)&&y!=='Mahavisha',
   fv:(v)=>![2,6].includes(v),ft:()=>true,fy:(y)=>y!=='Mahavisha',
   tip:'Performed on the 11th day after birth. Avoid Tuesday & Saturday. Any tithi acceptable.'},
  {ico:'ico-store',   n:'Business',     h:'व्यापार',   c:'#e8b848',d:'Shop opening & new venture launch',
   ok:(v,t,y)=>![2,6].includes(v)&&y!=='Mahavisha'&&[1,3,4,5].includes(v),
   fv:(v)=>![2,6].includes(v)&&[1,3,4,5].includes(v),ft:()=>true,fy:(y)=>y!=='Mahavisha',
   tip:'Mon, Wed, Thu, Fri most auspicious for business. Labha choghadiya window is ideal.'},
  {ico:'ico-land',    n:'Land / Plot',  h:'भूमि पूजन',c:'#a78bfa',d:'Land purchase & bhoomi pujan',
   ok:(v,t,y)=>![2,6].includes(v)&&![4,8,9,14].includes(t)&&y!=='Mahavisha',
   fv:(v)=>![2,6].includes(v),ft:(t)=>![4,8,9,14].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Avoid Rikta tithis & Amavasya. Hasta, Rohini & Uttara nakshatras most favoured.'},
  {ico:'ico-plane',   n:'Travel',       h:'यात्रा',    c:'#7dd3fc',d:'Journey, pilgrimage, long travel',
   ok:(v,t,y)=>y!=='Mahavisha'&&[1,3,4].includes(v)&&![4,8,14].includes(t),
   fv:(v)=>[1,3,4].includes(v),ft:(t)=>![4,8,14].includes(t),fy:(y)=>y!=='Mahavisha',
   tip:'Mon, Wed, Thu best for journeys. Mrigashira & Hasta nakshatras very auspicious.'},
];
function buildMuGrid(d){
  const g=el('muGrid');if(!g)return;
  const vi=d.vara?.idx??0,tn=d.tithi?.num??1,ycl=d.yoga?.cls||'';
  const cho=d.choghadiya||[];
  const gc=cho.filter(c=>['Amrit','Shubha','Labha'].includes(c.name));
  const bw=gc.slice(0,2).map(c=>`${c.start}–${c.end}`).join(', ')||'None today';
  const ab=`${d.abhijit?.start||'—'}–${d.abhijit?.end||'—'}`;
  const rk=`${d.rahuKaal?.start||'—'}–${d.rahuKaal?.end||'—'}`;
  g.innerHTML=MU_DEF.map(m=>{
    const ok=m.ok(vi,tn,ycl),fv=m.fv(vi),ft=m.ft(tn),fy=m.fy(ycl);
    return`<div class="mu-card">
      <div class="mu-hdr">
        <div class="mu-ico-circle" style="background:${m.c}22;color:${m.c}">
          <svg class="mu-ico-svg"><use href="#${m.ico}"/></svg>
        </div>
        <div class="mu-hdr-mid">
          <div class="mu-name">${m.n}</div>
          <div class="mu-hi">${m.h}</div>
          <div class="mu-desc">${m.d}</div>
        </div>
        <span class="mu-badge ${ok?'good':'bad'}">${ok?'✓ Good':'✗ Avoid'}</span>
      </div>
      <div class="mu-factors">
        <span class="mu-f-lbl">Factors:</span>
        <span class="mu-fpill ${fv?'ok':'no'}">${fv?'✓':'✗'} Vara</span>
        <span class="mu-fpill ${ft?'ok':'no'}">${ft?'✓':'✗'} Tithi</span>
        <span class="mu-fpill ${fy?'ok':'no'}">${fy?'✓':'✗'} Yoga</span>
      </div>
      <div class="mu-tip">${m.tip}</div>
      <div class="mu-times">
        <div class="mu-ti"><div class="mu-ti-lbl best">★ Best Time</div><div class="mu-ti-val">${bw}</div></div>
        <div class="mu-ti"><div class="mu-ti-lbl ab">◈ Abhijit</div><div class="mu-ti-val">${ab}</div></div>
        <div class="mu-ti"><div class="mu-ti-lbl rk">✗ Rahu Kaal</div><div class="mu-ti-val">${rk}</div></div>
      </div>
    </div>`;
  }).join('');
}

/* ── HOROSCOPE ── */
const RASHIS=[
  {sym:'♈',skt:'मेष',    en:'Aries',       c:'#ef4444'},
  {sym:'♉',skt:'वृषभ',   en:'Taurus',      c:'#84cc16'},
  {sym:'♊',skt:'मिथुन',  en:'Gemini',      c:'#facc15'},
  {sym:'♋',skt:'कर्क',   en:'Cancer',      c:'#38bdf8'},
  {sym:'♌',skt:'सिंह',   en:'Leo',         c:'#f97316'},
  {sym:'♍',skt:'कन्या',  en:'Virgo',       c:'#22c55e'},
  {sym:'♎',skt:'तुला',   en:'Libra',       c:'#f43f5e'},
  {sym:'♏',skt:'वृश्चिक',en:'Scorpio',     c:'#a78bfa'},
  {sym:'♐',skt:'धनु',    en:'Sagittarius', c:'#fbbf24'},
  {sym:'♑',skt:'मकर',    en:'Capricorn',   c:'#94a3b8'},
  {sym:'♒',skt:'कुंभ',   en:'Aquarius',    c:'#7c3aed'},
  {sym:'♓',skt:'मीन',    en:'Pisces',      c:'#0ea5e9'},
];
const MOON_H=[
  {q:'excellent',txt:'Bold new starts are favoured. Assertive energy, sharp instincts — step forward.'},
  {q:'caution',  txt:'Watch finances and family matters. Patience with spending brings rewards.'},
  {q:'excellent',txt:'Communication and short travel flow beautifully. Write, connect, negotiate.'},
  {q:'neutral',  txt:'Home and heart take centre stage. Rest, domestic harmony, and self-care.'},
  {q:'good',     txt:'Romance and creativity bloom. Pour energy into what you truly love.'},
  {q:'excellent',txt:'Health focus and daily work pay dividends. Methodical effort wins the day.'},
  {q:'good',     txt:'Partnerships and alliances shine. New bonds form; existing ones deepen.'},
  {q:'caution',  txt:'Look inward before acting. Avoid impulsive decisions or hidden risks.'},
  {q:'good',     txt:'Wisdom and long-distance ventures are rewarding. Seek higher learning.'},
  {q:'excellent',txt:'Career recognition is high. Step into leadership — your efforts are seen.'},
  {q:'excellent',txt:'Gains, groups, and social connections all flow. Friends bring unexpected luck.'},
  {q:'good',     txt:'Rest and solitude are nourishing. Reflection brings surprising clarity.'},
];
const QLBL={excellent:'Excellent',good:'Good',neutral:'Neutral',caution:'Caution'};
function buildHoroscope(moonIdx, planets){
  const g=el('rashibGrid');if(!g)return;
  moonIdx=moonIdx??0;
  const mr=RASHIS[moonIdx];
  const moonPl=planets?.moon;
  const moonNak=moonPl?.nak||'';
  const nakLord=moonPl?.lord||'';
  const note=el('horoNote');
  if(note&&mr){
    note.innerHTML=`<div class="hb-pills">
      <span class="hb-pill">☽ ${mr.skt} — ${mr.en}</span>
      ${moonNak?`<span class="hb-pill">✦ ${moonNak}</span>`:''}
      ${nakLord?`<span class="hb-pill">Lord: ${nakLord}</span>`:''}
    </div>`;
  }
  /* build sign → planets map */
  const signPl={};
  if(planets){
    Object.entries(planets).forEach(([,p])=>{
      const si=p.signIdx??-1;
      if(si>=0&&si<12){if(!signPl[si])signPl[si]=[];signPl[si].push(p);}
    });
  }
  g.innerHTML=RASHIS.map((r,i)=>{
    const h=((moonIdx-i+12)%12)+1;
    const hd=MOON_H[h-1];
    const isMoon=i===moonIdx;
    const pls=signPl[i]||[];
    const plHtml=pls.length?`<div class="rc-planets">${pls.map(p=>`<span class="rcp" style="color:${p.color}">${p.sym} ${p.abbr}${p.retro?' ℞':''}</span>`).join('')}</div>`:'';
    return`<div class="rc rcq-${hd.q}${isMoon?' moon-rashi':''}" style="border-color:${isMoon?'rgba(232,184,72,.4)':r.c+'28'}">
      <div class="rc-hdr" style="background:${r.c}12">
        <div class="rc-sym-wrap" style="background:${r.c}22;color:${r.c}">${r.sym}</div>
        <div class="rc-names">
          <div class="rc-skt">${r.skt}${isMoon?' ☽':''}</div>
          <div class="rc-en">${r.en}</div>
        </div>
        <span class="rc-qlbl">${QLBL[hd.q]}</span>
      </div>
      <div class="rc-body">
        <div class="rc-house">☽ Moon — House ${h}</div>
        <div class="rc-guide">${hd.txt}</div>
        ${plHtml}
      </div>
    </div>`;
  }).join('');
}

/* ── PLANET STRIP ── */
const P_ORD=['sun','moon','mercury','venus','mars','jupiter','saturn','rahu','ketu'];
function buildPlanets(planets){
  const s=el('planetStrip');if(!s||!planets)return;
  s.innerHTML=P_ORD.map(pid=>{
    const p=planets[pid];if(!p)return'';
    return`<div class="pchip">
      <div class="pc-sym" style="color:${p.color}">${p.sym}</div>
      <div class="pc-lbl">${p.label}</div>
      <div class="pc-sign">${p.sign}</div>
      <div class="pc-deg">${p.deg}</div>
      ${p.retro?'<div class="pc-retro">℞ Retro</div>':''}
    </div>`;
  }).join('');
}

/* ── CHOGHADIYA ── */
const CC={Amrit:'#22c55e',Shubha:'#38bdf8',Labha:'#e8b848',Char:'#64748b',Udveg:'#fbbf24',Kaal:'#f87171',Rog:'#f43f5e'};
const CQ={Amrit:'Best',Shubha:'Good',Labha:'Good',Char:'Neutral',Udveg:'Caution',Kaal:'Avoid',Rog:'Avoid'};
function buildCho(cho){
  const bar=el('choBar'),lbls=el('choLbls'),cards=el('choCards');
  if(!bar||!cho)return;
  const nowH=new Date().getHours()+new Date().getMinutes()/60;
  bar.innerHTML=cho.map(c=>{
    const isN=c.startHr<=nowH&&nowH<c.endHr,col=CC[c.name]||'#888';
    return`<div class="cho-seg${isN?' now':''}" style="background:${col}" title="${c.name} ${c.start}–${c.end}">
      ${isN?'<div class="cho-now-tag">NOW</div>':''}
      <div class="cho-seg-n">${c.name}</div><div class="cho-seg-q">${CQ[c.name]||''}</div></div>`;
  }).join('');
  if(lbls)lbls.innerHTML=cho.map(c=>`<div class="cho-lbl">${c.start}</div>`).join('');
  if(cards)cards.innerHTML=cho.map(c=>{
    const isN=c.startHr<=nowH&&nowH<c.endHr,col=CC[c.name]||'#888';
    return`<div class="cho-card${isN?' now':''}"${isN?` style="--cc:${col};--cbg:${col}18;--cglow:${col}40"`:''}>
      ${isN?`<div class="cho-now-dot" style="color:${col}">● Now</div>`:''}
      <div class="cho-cn"${isN?` style="color:${col}"`:''}}>${c.name}</div>
      <div class="cho-ct">${c.start} – ${c.end}</div>
      <div class="cho-cq" style="color:${col}">${CQ[c.name]||''}</div></div>`;
  }).join('');
}

/* ── APPLY ALL DATA ── */
function applyData(d){
  st('navDate',d.dateDisplay||d.date);
  el('heroDate')&&(el('heroDate').textContent=d.dateDisplay||d.date);
  const loc=document.querySelector('.hero-loc');if(loc)loc.textContent=(d.dayName||'')+' · New Delhi, India · IST +5:30';
  st('phT',d.tithi?.name||'');st('phN',d.nakshatra?.name||'');
  st('phY',d.yoga?.name||'');st('phK',d.karana?.name||'');st('phV',d.vara?.name||'');
  const yc=el('phYC');if(yc){yc.textContent=d.yoga?.cls||'';const c=d.yoga?.cls;yc.style.color=c==='Subha'?'#22c55e':c==='Mahavisha'?'#f87171':'#fbbf24'}
  const pr=document.querySelectorAll('.pp-sub');if(pr[0])pr[0].textContent=d.tithi?.paksha||'';if(pr[1])pr[1].textContent='Pada '+(d.nakshatra?.pada||'');if(pr[3])pr[3].textContent=d.karana?.type||'';if(pr[4])pr[4].textContent=d.vara?.lord||'';
  st('tsSr',d.sunrise||'—');st('tsSs',d.sunset||'—');
  st('tsAb',(d.abhijit?.start||'—')+'–'+(d.abhijit?.end||'—'));
  st('tsRk',(d.rahuKaal?.start||'—')+'–'+(d.rahuKaal?.end||'—'));
  st('tsYg',(d.yamghantam?.start||'—')+'–'+(d.yamghantam?.end||'—'));
  sunBar(d);
  buildHeroFest(d.date);buildFestStrip(d.date);buildFestBanner(d.date);
  buildMuGrid(d);buildHoroscope(d.planets?.moon?.signIdx??0,d.planets);buildPlanets(d.planets);buildCho(d.choghadiya||[]);
  st('pctDate',d.dateDisplay||d.date);
  st('pctT',d.tithi?.name||'');st('pctTS',(d.tithi?.paksha||'')+' · '+(d.tithi?.num||'')+'/15 · '+(d.tithi?.lord||''));
  st('pctV',d.vara?.name||'');st('pctVS',(d.vara?.en||'')+' · Lord: '+(d.vara?.lord||'')+' · '+(d.vara?.nature||''));
  st('pctN',d.nakshatra?.name||'');st('pctNS','Pada '+(d.nakshatra?.pada||'')+' · '+(d.nakshatra?.lord||'')+' · '+(d.nakshatra?.gana||''));
  st('pctY',d.yoga?.name||'');st('pctYS',(d.yoga?.nature||'')+' · '+(d.yoga?.lord||'')+' · '+(d.yoga?.cls||''));
  st('pctK',d.karana?.name||'');st('pctKS',(d.karana?.type||'')+' · Slot '+(d.karana?.slot||'')+'/60 · '+(d.karana?.lord||''));
  sw('pctTPf',d.tithi?.prog||0);sw('pctNPf',d.nakshatra?.prog||0);
  sw('pctYPf',d.yoga?.prog||0);sw('pctKPf',d.karana?.prog||0);
  const vb=el('pctVBar');if(vb&&d.vara?.color)vb.style.background=d.vara.color;
}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded',()=>{
  sunBar(PD);
  buildHeroFest(curDate);
  buildFestStrip(curDate);
  buildFestBanner(curDate);
  buildHoroscope(PD.planets?.moon?.signIdx??0,PD.planets);
  buildPlanets(PD.planets);
  /* prefetch adjacent days for instant navigation */
  setTimeout(()=>{
    const nx=new Date(curDate+'T12:00:00');nx.setDate(nx.getDate()+1);
    const pv=new Date(curDate+'T12:00:00');pv.setDate(pv.getDate()-1);
    prefetchDay(_dtFmt(nx));prefetchDay(_dtFmt(pv));
  },800);
});
</script>
</body>
</html>
