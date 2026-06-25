{{--
  _panel_muhrat.blade.php  v6
  Light festival theme · All 15 sections · Large Hindi · Pure Math (no API)
  Layout: Header → Two-row Nav → Dashboard/Content
--}}
<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Sanskrit:ital@0;1&family=Crimson+Pro:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet"/>
<style>
/* ══════════════════════════════════════════════════════════
   MUHURAT PANEL v6 — 15 Sections · Large Hindi · Pure Math
   ══════════════════════════════════════════════════════════ */
#muhratPanel{
  --bg0:#fdf6ec; --bg1:#f9edd8; --bg2:#f2e0c4;
  --sur:#fffbf5; --bdr:rgba(168,112,40,.18); --bdr2:rgba(168,112,40,.35);
  --gold:#9a6b0a; --glt:#f5e6c0; --gmd:#c89020; --gdk:#6b4800;
  --saffron:#c8521a; --slt:#fde8dc; --ink:#1c1008; --ink2:#3a2410;
  --ink3:#7a5830; --ink4:#b09070;
  --font:'Tiro Devanagari Sanskrit',serif;
  --serif:'Crimson Pro',Georgia,serif;
  background:var(--bg0); color:var(--ink);
  font-family:var(--font); font-size:19px;
}
#muhratPanel *{box-sizing:border-box;}

/* ── Header ─────────────────────────────────────────── */
.mhp-header{
  background:linear-gradient(180deg,var(--sur) 0%,var(--bg1) 55%,var(--bg2) 100%);
  border-bottom:2px solid var(--bdr2); padding:18px 28px 16px;
}
.mhp-title{font-family:var(--font);font-size:2.2rem;color:var(--gdk);margin:0 0 2px;}
.mhp-shastra{font-family:var(--serif);font-size:.78rem;color:var(--ink4);letter-spacing:.1em;margin-bottom:14px;}
.mhp-inputs{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;}
.mhp-ig{display:flex;flex-direction:column;gap:5px;}
.mhp-lbl{font-family:var(--font);font-size:.95rem;color:var(--ink3);}
.mhp-input{
  padding:10px 14px;background:var(--sur);border:1.5px solid var(--bdr2);
  border-radius:6px;color:var(--ink);font-family:var(--font);font-size:1.05rem;outline:none;
}
.mhp-input:focus{border-color:var(--saffron);}
.mhp-calc-btn{
  padding:11px 28px;background:linear-gradient(160deg,var(--saffron),#7a3202);
  color:#fff;border:none;border-radius:6px;font-family:var(--font);font-size:1.1rem;
  cursor:pointer;box-shadow:0 4px 12px rgba(200,82,26,.3);transition:all .15s;
}
.mhp-calc-btn:hover{filter:brightness(1.08);}
.mhp-calc-btn:disabled{opacity:.5;cursor:not-allowed;}
.mhp-yr-btn{
  padding:10px 18px;border:1.5px solid var(--bdr2);border-radius:6px;
  background:var(--sur);color:var(--ink3);font-family:var(--font);font-size:1rem;
  cursor:pointer;transition:all .15s;
}
.mhp-yr-btn:hover{background:var(--glt);color:var(--gdk);}

/* ── Nav — two rows ─────────────────────────────────── */
.mhp-catnav{background:var(--bg2);border-bottom:2px solid var(--bdr2);}
.mhp-cgrow{display:flex;gap:0;overflow-x:auto;border-bottom:1px solid var(--bdr);}
.mhp-cg-btn{
  padding:12px 24px;border:none;background:transparent;
  font-family:var(--font);font-size:1.05rem;color:var(--ink3);
  cursor:pointer;border-bottom:3px solid transparent;transition:all .12s;white-space:nowrap;
}
.mhp-cg-btn:hover{color:var(--gdk);}
.mhp-cg-btn.act{color:var(--saffron);border-bottom-color:var(--saffron);font-weight:600;background:rgba(200,82,26,.06);}
.mhp-cirow{
  display:flex;gap:6px;padding:8px 20px 10px;flex-wrap:wrap;
  min-height:50px;align-items:center;background:var(--bg1);
}
.mhp-ci{
  padding:7px 16px;border:1.5px solid var(--bdr2);border-radius:20px;
  background:var(--sur);color:var(--ink2);font-family:var(--font);font-size:1rem;
  cursor:pointer;transition:all .12s;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;
}
.mhp-ci:hover{border-color:var(--saffron);background:var(--glt);color:var(--gdk);}
.mhp-ci.act{background:var(--saffron);border-color:var(--saffron);color:#fff;font-weight:600;}
.mhp-badge{font-size:.68rem;padding:1px 6px;border-radius:7px;font-family:var(--serif);font-weight:700;}
.mhp-ci.act .mhp-badge{background:rgba(255,255,255,.25);color:#fff;}
.mhp-ci:not(.act) .mhp-badge{background:rgba(26,90,40,.1);color:#1a5a28;}
.mhp-ci:not(.act) .mhp-badge.a{background:rgba(160,32,20,.1);color:#841808;}

/* ── Var-Kanya inputs ───────────────────────────────── */
.mhp-vi{background:var(--bg1);border-bottom:1.5px solid var(--bdr);padding:16px 28px 18px;display:none;}
.mhp-vi.show{display:block;}
.mhp-vi-head{font-family:var(--font);font-size:1.25rem;color:var(--gdk);font-weight:600;margin-bottom:14px;border-bottom:1px solid var(--bdr);padding-bottom:9px;}
.mhp-vi-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.mhp-vi-block{background:var(--sur);border:1.5px solid var(--bdr2);border-radius:10px;padding:14px;}
.mhp-vi-lbl{font-family:var(--font);font-size:1.1rem;font-weight:600;margin-bottom:10px;}
.mhp-vi-lbl.g{color:#a02060;} .mhp-vi-lbl.b{color:#204090;}
.mhp-rg{display:grid;grid-template-columns:repeat(6,1fr);gap:5px;margin-bottom:10px;}
.mhp-rc{
  padding:8px 4px;border:1.5px solid var(--bdr);border-radius:6px;
  background:var(--bg1);color:var(--ink2);font-family:var(--font);font-size:1.05rem;
  cursor:pointer;text-align:center;transition:all .12s;
}
.mhp-rc:hover{border-color:var(--saffron);background:var(--slt);color:var(--saffron);}
.mhp-rc.sg{background:#fce8f0;border-color:#c04080;color:#7a1040;font-weight:600;}
.mhp-rc.sb{background:#e8effe;border-color:#4070d0;color:#1a3880;font-weight:600;}
.mhp-nsel{
  width:100%;padding:9px 11px;background:var(--bg1);border:1.5px solid var(--bdr);
  border-radius:7px;color:var(--ink);font-family:var(--font);font-size:1rem;outline:none;
}
.mhp-hint{margin-top:10px;font-size:.9rem;color:var(--ink4);background:var(--glt);border-radius:6px;padding:8px 12px;line-height:1.6;}

/* ── Content ────────────────────────────────────────── */
.mhp-content{background:var(--bg0);min-height:500px;}

/* ── Dashboard — all 15 categories ─────────────────── */
.mhp-dash{padding:24px 28px;}
.mhp-dash-head{font-family:var(--font);font-size:1.6rem;color:var(--gdk);margin-bottom:6px;font-weight:600;}
.mhp-dash-sub{font-size:.95rem;color:var(--ink4);margin-bottom:22px;font-family:var(--serif);}
.mhp-dash-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
.mhp-dcard{
  background:var(--sur);border:1.5px solid var(--bdr2);border-radius:12px;
  padding:18px;cursor:pointer;transition:all .15s;
}
.mhp-dcard:hover{border-color:var(--saffron);background:var(--glt);transform:translateY(-2px);box-shadow:0 4px 14px rgba(168,112,40,.15);}
.mhp-dcard-icon{font-size:2rem;margin-bottom:8px;}
.mhp-dcard-title{font-family:var(--font);font-size:1.15rem;color:var(--gdk);font-weight:600;margin-bottom:5px;}
.mhp-dcard-desc{font-size:.88rem;color:var(--ink4);line-height:1.7;}

/* ── Loading / empty ────────────────────────────────── */
.mhp-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 24px;gap:12px;}
.mhp-empty-t{font-family:var(--font);font-size:1.6rem;color:var(--gold);text-align:center;}
.mhp-spin{display:flex;flex-direction:column;align-items:center;padding:60px;gap:16px;}
.mhp-spinner{width:36px;height:36px;border:2px solid var(--bdr2);border-top-color:var(--saffron);border-radius:50%;animation:mhspin 1s linear infinite;}
@keyframes mhspin{to{transform:rotate(360deg)}}
.mhp-spin-t{font-family:var(--font);font-size:1.2rem;color:var(--gold);}

/* ── Month view ─────────────────────────────────────── */
.mhp-mv-head{
  background:linear-gradient(135deg,var(--bg2),var(--glt));border-bottom:2px solid var(--bdr2);
  padding:14px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;
}
.mhp-mv-title{font-family:var(--font);font-size:1.35rem;color:var(--gdk);font-weight:600;flex:1;}
.mhp-mv-sel{
  padding:8px 12px;background:var(--sur);border:1.5px solid var(--bdr2);
  border-radius:6px;font-family:var(--font);font-size:1rem;color:var(--ink);outline:none;
}
.mhp-mv-btn{
  padding:9px 22px;background:linear-gradient(160deg,var(--saffron),#7a3202);
  color:#fff;border:none;border-radius:6px;font-family:var(--font);font-size:1rem;cursor:pointer;
}
.mhp-rf-row{background:var(--bg1);border-bottom:1px solid var(--bdr);padding:10px 22px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;}
.mhp-rfb{padding:6px 14px;border:1.5px solid var(--bdr);border-radius:18px;background:var(--sur);color:var(--ink2);font-family:var(--font);font-size:.95rem;cursor:pointer;transition:all .12s;}
.mhp-rfb:hover{border-color:var(--saffron);color:var(--saffron);}
.mhp-rfb.act{background:var(--saffron);border-color:var(--saffron);color:#fff;font-weight:600;}

/* ── Year scan modal ────────────────────────────────── */
.mhp-modal{position:fixed;inset:0;z-index:9999;display:none;background:rgba(90,40,0,.6);backdrop-filter:blur(5px);align-items:center;justify-content:center;}
.mhp-modal.open{display:flex;}
.mhp-mbox{background:#fdf6ec;border:2px solid rgba(168,112,40,.35);border-radius:16px;width:min(97vw,1100px);max-height:90vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(90,40,0,.3);overflow:hidden;}
.mhp-mhead{background:linear-gradient(180deg,#f5e6c0,#f2e0c4);border-bottom:2px solid rgba(168,112,40,.35);padding:16px 24px;display:flex;align-items:center;gap:12px;}
.mhp-mhead-t{font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.4rem;color:#6b4800;flex:1;font-weight:600;}
.mhp-mclose{background:#fffbf5;border:1.5px solid rgba(168,112,40,.35);border-radius:8px;padding:7px 16px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;color:#7a5830;cursor:pointer;}
.mhp-mclose:hover{background:#fde8dc;color:#c8521a;}
.mhp-mctrl{padding:14px 24px;border-bottom:2px solid rgba(168,112,40,.35);display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;background:linear-gradient(180deg,#f9edd8,#fffbf5);}
.mhp-ml{font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;color:#7a5830;margin-bottom:4px;display:block;}
.mhp-mi,.mhp-ms{padding:9px 12px;background:#fffbf5;border:1.5px solid rgba(168,112,40,.35);border-radius:6px;color:#1c1008;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;outline:none;}
.mhp-mscan{padding:10px 24px;background:linear-gradient(160deg,#c8521a,#7a3202);color:#fff;border:none;border-radius:6px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;cursor:pointer;}
.mhp-mscan:disabled{opacity:.5;}
.mhp-mbody{overflow-y:auto;flex:1;background:#fdf6ec;}
.mhp-mspinner{width:32px;height:32px;border:2px solid rgba(168,112,40,.3);border-top-color:#c8521a;border-radius:50%;animation:mhspin 1s linear infinite;}

@media(max-width:768px){
  .mhp-vi-grid{grid-template-columns:1fr;} .mhp-rg{grid-template-columns:repeat(4,1fr);}
  .mhp-dash-grid{grid-template-columns:repeat(2,1fr);} .mhp-title{font-size:1.7rem;}
}
@media(max-width:500px){.mhp-dash-grid{grid-template-columns:1fr;}}
</style>

<div id="muhratPanel" style="display:none">

  <!-- Header -->
  <div class="mhp-header">
      <div class="mhp-inputs">
      <div class="mhp-ig">
        <label class="mhp-lbl">Date (DD/MM/YYYY)</label>
        <input type="text" id="mhDateDisplay" class="mhp-input" placeholder="24/05/2026"
               maxlength="10" style="width:175px" oninput="mhSyncDate()"/>
        <input type="hidden" id="mhDateISO"/>
      </div>
      <div class="mhp-ig" style="margin-top:auto">
        <button class="mhp-calc-btn" id="mhCalcBtn" onclick="mhCalculate()">Calculate Muhurat</button>
      </div>
      <div class="mhp-ig" style="margin-top:auto">
        <button class="mhp-yr-btn" onclick="openYearScan()">📅 Year Scan</button>
      </div>
    </div>
  </div>

  <!-- Two-row nav -->
  <div class="mhp-catnav" id="mhCatNav">
    <div class="mhp-cgrow">
      <button class="mhp-cg-btn act" id="mhcg_kaal"    onclick="selectGroup('kaal')">⏰ Kaal Division</button>
      <button class="mhp-cg-btn"     id="mhcg_vivah_g" onclick="selectGroup('vivah_g')">💍 Vivah Muhurat</button>
      <button class="mhp-cg-btn"     id="mhcg_samskar" onclick="selectGroup('samskar')">🏠 Samskar Muhurat</button>
    </div>
    <div class="mhp-cirow" id="mhCiRow"><!-- Filled by JS --></div>
  </div>

  <!-- Var-Kanya inputs -->
  <div class="mhp-vi" id="mhVivahInputs">
    <div class="mhp-vi-head">Var-Kanya Details — Kundali Matching</div>
    <div class="mhp-vi-grid">
      <div class="mhp-vi-block">
        <div class="mhp-vi-lbl g">♀ Bride's Rashi</div>
        <div class="mhp-rg" id="mhGRG">
          @foreach($rashis as $i=>$r)
          <button class="mhp-rc" id="mhGR_{{$i}}" onclick="selGR({{$i}})">{{$r}}</button>
          @endforeach
        </div>
        <select class="mhp-nsel" id="mhGN">
          <option value="">— Bride's Nakshatra (optional) —</option>
          @foreach($nakshatras as $i=>$n)<option value="{{$i}}">{{$n}}</option>@endforeach
        </select>
        <div id="mhGSt" style="display:none;margin-top:8px">
          <div style="display:flex;align-items:center;justify-content:space-between;background:#fce8f0;border:1.5px solid rgba(200,80,160,.3);border-radius:8px;padding:9px 14px">
            <div><div style="font-size:.82rem;color:#a02060">♀ Bride</div><div id="mhGStT" style="font-size:1.15rem;font-weight:600;color:#7a1050"></div></div>
            <button onclick="clrGR()" style="background:#841808;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-family:var(--font);font-size:.95rem;cursor:pointer">✕ Remove</button>
          </div>
        </div>
      </div>
      <div class="mhp-vi-block">
        <div class="mhp-vi-lbl b">♂ Groom's Rashi</div>
        <div class="mhp-rg" id="mhBRG">
          @foreach($rashis as $i=>$r)
          <button class="mhp-rc" id="mhBR_{{$i}}" onclick="selBR({{$i}})">{{$r}}</button>
          @endforeach
        </div>
        <select class="mhp-nsel" id="mhBN">
          <option value="">— Groom's Nakshatra (optional) —</option>
          @foreach($nakshatras as $i=>$n)<option value="{{$i}}">{{$n}}</option>@endforeach
        </select>
        <div id="mhBSt" style="display:none;margin-top:8px">
          <div style="display:flex;align-items:center;justify-content:space-between;background:#e8effe;border:1.5px solid rgba(80,120,200,.3);border-radius:8px;padding:9px 14px">
            <div><div style="font-size:.82rem;color:#204080">♂ Groom</div><div id="mhBStT" style="font-size:1.15rem;font-weight:600;color:#1a3080"></div></div>
            <button onclick="clrBR()" style="background:#841808;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-family:var(--font);font-size:.95rem;cursor:pointer">✕ Remove</button>
          </div>
        </div>
      </div>
    </div>
    <div class="mhp-hint">💡 Selecting a Rashi will filter Chandrabala, Tarabala and Kundali matching results accordingly. Leave unselected for general calculation.</div>
  </div>

  <!-- Content (Dashboard / Results) -->
  <div class="mhp-content" id="mhContent">
    <!-- Filled by JS -->
  </div>

</div><!-- #muhratPanel -->

<!-- Year Scan Modal -->
<div class="mhp-modal" id="mhYearModal" onclick="if(event.target===this)closeYearScan()">
  <div class="mhp-mbox">
    <div class="mhp-mhead">
      <div class="mhp-mhead-t">Year Scan — Auspicious Muhurat Dates</div>
      <button class="mhp-mclose" onclick="closeYearScan()">✕ Close</button>
    </div>
    <div class="mhp-mctrl">
      <div><label class="mhp-ml">Year</label><input type="number" id="mhYI" class="mhp-mi" value="2026" min="2020" max="2050" style="width:110px"/></div>
      <div><label class="mhp-ml">Category</label>
        <select id="mhYT" class="mhp-ms">
          <option value="vivah">Vivah Muhurat</option>
          <option value="griha_pravesh">Griha Pravesh</option>
          <option value="vahana">Vahana Purchase</option>
          <option value="mundan">Mundan</option>
          <option value="sampatti">Sampatti Purchase</option>
        </select></div>
      <div><label class="mhp-ml">Min Score</label>
        <select id="mhYMS" class="mhp-ms">
          <option value="85">85+ (Excellent)</option>
          <option value="70">70+ (Good)</option>
          <option value="55" selected>55+ (Auspicious)</option>
          <option value="40">40+ (Average)</option>
        </select></div>
      <div style="display:flex;flex-direction:column;gap:4px">
        <label class="mhp-ml">Rashi (from panel)</label>
        <div id="mhYRD" style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;color:#6b4800;padding:9px 0">Not selected</div>
      </div>
      <div style="margin-top:auto;margin-left:auto"><button class="mhp-mscan" onclick="runYearScan()" id="mhYSBtn">Search Year</button></div>
    </div>
    <div class="mhp-mbody" id="mhYB">
      <div style="display:flex;align-items:center;justify-content:center;padding:60px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:#9a6b0a">Select year and category, then click "Search Year"</div>
    </div>
  </div>
</div>

<script>
// ═══════════════════════════════════════════════════════
//  MUHURAT PANEL v6 — Complete Vedic Muhurta · Pure Math
// ═══════════════════════════════════════════════════════
let _mhCat=null, _mhGR=null, _mhBR=null, _mhLoading=false;

const RHI = ['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya','Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena'];
const MHI = ['','January','February','March','April','May','June','July','August','September','October','November','December'];

// ── All 15 categories defined ──────────────────────────
const CATS = {
  kaal: [
    {id:'choghadiya', hi:'Choghadiya',     icon:'⏱', desc:'8 day + 8 night Kaal periods. Amrit, Shubh, Labh — auspicious; Kaal, Udveg, Rog — inauspicious.', badge:'8+8'},
    {id:'hora',       hi:'Shubha Hora',    icon:'🌙', desc:'24 planetary Horas. Jupiter, Venus, Mercury, Moon Hora — for auspicious work.', badge:'24'},
    {id:'abhijit',    hi:'Abhijit Muhurat', icon:'☀', desc:'48 minutes at midday. Destroys all doshas.', badge:'48m'},
    {id:'rahu',       hi:'Rahukaal',        icon:'🚫', desc:'Daily inauspicious period. Avoid starting new work.', badge:'Avoid', badgeCls:'a'},
    {id:'yoga',       hi:'Shubha Yoga',     icon:'✨', desc:'Siddhi, Amrit Siddhi, Sarvartha Siddhi, Raj Yoga — special auspicious combinations.'},
    {id:'lagna',      hi:'Lagna Table',     icon:'♈', desc:'Lagna changes throughout the day. Fixed Lagna — best for Vivah, Griha Pravesh.'},
    {id:'panchak',    hi:'Panchak',         icon:'⚠', desc:'Five nakshatras from Dhanishta to Revati. Auspicious work is prohibited in these.'},
    {id:'doghati',    hi:'Do Ghati Muhurat', icon:'🕰', desc:'Central part of Abhijit Muhurat. The best 48-minute period of the day.'},
    {id:'gowri',      hi:'Gowri Panchanga', icon:'🌸', desc:'South Indian tradition. 8 Kaal periods — Amrit, Kaal, Shubh, Rog etc.'},
  ],
  vivah_g: [
    {id:'vivah',        hi:'Vivah Muhurat',         icon:'💍', desc:'MC Vivah Prakarana. Jupiter/Venus combustion, Latta dosha, complete Tithi-Nakshatra-Vara check.'},
    {id:'milan',        hi:'Ashtakoot Milan',        icon:'❤', desc:'Varna, Vasya, Tara, Yoni, Graha Maitri, Gana, Bhakoot, Nadi — 36-point compatibility.'},
    {id:'melap_sarini', hi:'Melapak Sarini',          icon:'📊', desc:'Traditional table — Nakshatra × Nakshatra, all 36 points in one grid. Complete compatibility view.'},
    {id:'month_vivah',  hi:'Monthly Auspicious Dates', icon:'📅', desc:'Auspicious Vivah dates for the entire month. With Chandrabala + Tarabala by Rashi.'},
  ],
  samskar: [
    {id:'griha',         hi:'Griha Pravesh',  icon:'🏠', desc:'MC: Rohini, Punarvasu, Hasta, Shravana — best. Fixed Lagna required.'},
    {id:'vahana',        hi:'Vahana Purchase', icon:'🚗', desc:'MC: Ashwini, Rohini, Hasta — best. Avoid vehicle purchase during Rahukaal.'},
    {id:'mundan',        hi:'Mundan Samskar', icon:'✂', desc:'MC: 1st/3rd/5th year. Jyeshtha, Moola, Ashlesha — prohibited.'},
    {id:'sampatti',      hi:'Sampatti Purchase', icon:'🏗', desc:'MC: Rohini, Punarvasu, Anuradha, Shravana — best. Jupiter/Venus Hora auspicious.'},
    {id:'month_samskar', hi:'Samskar Monthly Dates', icon:'📋', desc:'Griha Pravesh/Vahana/Mundan/Sampatti — auspicious dates for the whole month.'},
  ],
};

// ── Date init ────────────────────────────────────
(function(){
  const now=new Date();
  const y=now.getFullYear(),m=String(now.getMonth()+1).padStart(2,'0'),d=String(now.getDate()).padStart(2,'0');
  const el=document.getElementById('mhDateDisplay');
  const el2=document.getElementById('mhDateISO');
  if(el) el.value=`${d}/${m}/${y}`;
  if(el2) el2.value=`${y}-${m}-${d}`;
  const yi=document.getElementById('mhYI');
  if(yi) yi.value=y;
})();

function mhSyncDate(){
  const raw=document.getElementById('mhDateDisplay').value;
  const parts=raw.trim().split(/[\/\-]/);
  if(parts.length===3){
    const[dd,mm,yyyy]=parts.map(p=>p.trim().padStart(2,'0'));
    if(yyyy.length===4) document.getElementById('mhDateISO').value=`${yyyy}-${mm}-${dd}`;
  }
}

// ── Dashboard — all 15 categories ──────────────────────
function mhGoHome(){
  _mhCat=null;
  document.querySelectorAll('.mhp-ci').forEach(b=>b.classList.remove('act'));
  document.querySelectorAll('.mhp-cg-btn').forEach(b=>b.classList.remove('act'));
  document.getElementById('mhVivahInputs').classList.remove('show');

  const allGroups = [
    {group:'kaal',    label:'⏰ Kaal Division', cats: CATS.kaal},
    {group:'vivah_g', label:'💍 Vivah Muhurat', cats: CATS.vivah_g},
    {group:'samskar', label:'🏠 Samskar Muhurat', cats: CATS.samskar},
  ];

  let html = `<div class="mhp-dash">
    <div class="mhp-dash-head">All Muhurat Categories</div>
    <div class="mhp-dash-sub">Muhurta Chintamani · Jean Meeus Algorithms · Lahiri Ayanamsa · Complete Vedic Calculation</div>`;

  for(const {group, label, cats} of allGroups){
    html += `<div style="margin-bottom:24px;">
      <div style="font-family:var(--font);font-size:1.2rem;color:var(--gold);margin-bottom:12px;font-weight:600;border-bottom:1px solid var(--bdr);padding-bottom:8px;">${label}</div>
      <div class="mhp-dash-grid">`;
    for(const c of cats){
      html += `<div class="mhp-dcard" onclick="selectCat('${c.id}')">
        <div class="mhp-dcard-icon">${c.icon}</div>
        <div class="mhp-dcard-title">${c.hi}</div>
        <div class="mhp-dcard-desc">${c.desc}</div>
      </div>`;
    }
    html += `</div></div>`;
  }
  html += `</div>`;
  document.getElementById('mhContent').innerHTML = html;
}

// ── Nav — group selection ────────────────────────────
let _mhGrp = 'kaal';
function selectGroup(grp){
  _mhGrp = grp;
  document.querySelectorAll('.mhp-cg-btn').forEach(b=>b.classList.remove('act'));
  document.getElementById('mhcg_'+grp)?.classList.add('act');
  const cats = CATS[grp] || [];
  const row = document.getElementById('mhCiRow');
  if(!row) return;
  row.innerHTML = cats.map(c=>{
    const badge = c.badge?`<span class="mhp-badge${c.badgeCls?' '+c.badgeCls:''}">${c.badge}</span>`:'';
    return `<button class="mhp-ci${_mhCat===c.id?' act':''}" id="mhci_${c.id}" onclick="selectCat('${c.id}')">${c.hi}${badge}</button>`;
  }).join('');
}

function selectCat(cat){
  _mhCat = cat;
  // Find group
  const grp = Object.entries(CATS).find(([g,cs])=>cs.find(c=>c.id===cat));
  if(grp && grp[0]!==_mhGrp) selectGroup(grp[0]);
  // Update active
  document.querySelectorAll('.mhp-ci').forEach(b=>b.classList.remove('act'));
  document.getElementById('mhci_'+cat)?.classList.add('act');
  document.querySelectorAll('.mhp-cg-btn').forEach(b=>b.classList.remove('act'));
  document.getElementById('mhcg_'+(grp?grp[0]:'kaal'))?.classList.add('act');
  // Vivah inputs
  const vi=document.getElementById('mhVivahInputs');
  if(vi) vi.classList.toggle('show', ['vivah','milan','month_vivah','melap_sarini'].includes(cat));
  // Month views
  if(cat==='month_vivah'||cat==='month_samskar'){ showMonthView(cat); return; }
  // Melap Sarini — static table (no API)
  if(cat==='melap_sarini'){ showMelapSarini(); return; }
  // Show empty prompt
  document.getElementById('mhContent').innerHTML=`
    <div class="mhp-empty">
      <div class="mhp-empty-t">${CATS[grp?grp[0]:'kaal'].find(c=>c.id===cat)?.hi||cat}</div>
      <div style="font-size:1rem;color:var(--ink4);font-family:var(--font);text-align:center;line-height:2;">Enter date and click <strong>Calculate Muhurat</strong></div>
    </div>`;
}

// ── Rashi selection ───────────────────────────────────
function selGR(i){ _mhGR=i; document.querySelectorAll('[id^="mhGR_"]').forEach(b=>b.classList.remove('sg')); document.getElementById('mhGR_'+i)?.classList.add('sg'); const s=document.getElementById('mhGSt'); const t=document.getElementById('mhGStT'); if(s) s.style.display='block'; if(t) t.textContent=RHI[i]+' Rashi ✓'; }
function selBR(i){ _mhBR=i; document.querySelectorAll('[id^="mhBR_"]').forEach(b=>b.classList.remove('sb')); document.getElementById('mhBR_'+i)?.classList.add('sb'); const s=document.getElementById('mhBSt'); const t=document.getElementById('mhBStT'); if(s) s.style.display='block'; if(t) t.textContent=RHI[i]+' Rashi ✓'; }
function clrGR(){ _mhGR=null; document.querySelectorAll('[id^="mhGR_"]').forEach(b=>b.classList.remove('sg')); const s=document.getElementById('mhGSt'); if(s) s.style.display='none'; document.getElementById('mhGN').value=''; document.querySelectorAll('[id^="mhrf_"]').forEach(b=>b.classList.remove('act')); document.getElementById('mhrf_all')?.classList.add('act'); }
function clrBR(){ _mhBR=null; document.querySelectorAll('[id^="mhBR_"]').forEach(b=>b.classList.remove('sb')); const s=document.getElementById('mhBSt'); if(s) s.style.display='none'; document.getElementById('mhBN').value=''; }

// ── Month view ────────────────────────────────────────
function showMonthView(cat){
  const type=(cat==='month_samskar')?'griha_pravesh':'vivah';
  // Read year/month from user-entered date (not system date)
  const dateISO = document.getElementById('mhDateISO').value;
  let selY, selM;
  if(dateISO && dateISO.length===10){
    const parts = dateISO.split('-');
    selY = parseInt(parts[0]);
    selM = parseInt(parts[1]);
  } else {
    const now = new Date();
    selY = now.getFullYear();
    selM = now.getMonth()+1;
  }
  // Build year options: 3 years before and 3 after entered year
  const yearOpts = [-2,-1,0,1,2,3].map(d=>`<option value="${selY+d}"${d===0?' selected':''}>${selY+d}</option>`).join('');
  const rfChips=RHI.map((r,i)=>`<button class="mhp-rfb${i===_mhGR?' act':''}" onclick="mhRFilt(${i},'${type}')" id="mhrf_${i}">${r}</button>`).join('');
  document.getElementById('mhContent').innerHTML=`
    <div class="mhp-mv-head">
      <div class="mhp-mv-title">${cat==='month_vivah'?'Vivah — Auspicious Dates of the Month':'Samskar — Auspicious Dates of the Month'}</div>
      <div style="display:flex;gap:8px;align-items:center">
        <select class="mhp-mv-sel" id="mhMvM">${MHI.slice(1).map((m,i)=>`<option value="${i+1}"${i+1===selM?' selected':''}>${m}</option>`).join('')}</select>
        <select class="mhp-mv-sel" id="mhMvY">${yearOpts}</select>
        <button class="mhp-mv-btn" onclick="loadMonthD('${type}')">Search</button>
      </div>
    </div>
    <div style="background:#f9edd8;border-bottom:1px solid rgba(168,112,40,.2);padding:10px 20px">
      <div style="font-size:1rem;color:#7a5830;margin-bottom:7px">Filter by Rashi — Chandrabala + Tarabala will change according to your Rashi:</div>
      <div class="mhp-rf-row" style="background:transparent;border:none;padding:0">
        <button class="mhp-rfb${_mhGR===null?' act':''}" onclick="mhRFilt(null,'${type}')" id="mhrf_all">All Rashis</button>
        ${rfChips}
      </div>
    </div>
    <div id="mhMvBody"><div class="mhp-spin"><div class="mhp-spinner"></div><div class="mhp-spin-t">Searching dates…</div></div></div>`;
  loadMonthD(type);
}

let _mhMT='vivah';
async function mhRFilt(idx, type){
  document.querySelectorAll('[id^="mhrf_"]').forEach(b=>b.classList.remove('act'));
  document.getElementById(idx===null?'mhrf_all':'mhrf_'+idx)?.classList.add('act');
  if(idx!==null){ _mhGR=idx; document.querySelectorAll('[id^="mhGR_"]').forEach(b=>b.classList.remove('sg')); document.getElementById('mhGR_'+idx)?.classList.add('sg'); const s=document.getElementById('mhGSt'); const t=document.getElementById('mhGStT'); if(s) s.style.display='block'; if(t) t.textContent=RHI[idx]+' Rashi ✓'; }
  else { _mhGR=null; document.querySelectorAll('[id^="mhGR_"]').forEach(b=>b.classList.remove('sg')); const s=document.getElementById('mhGSt'); if(s) s.style.display='none'; }
  await loadMonthD(type||_mhMT);
}

async function loadMonthD(type){
  _mhMT=type;
  if(typeof _masaLat==='undefined'||_masaLat===null){
    document.getElementById('mhMvBody').innerHTML='<div style="padding:30px;font-family:var(--font);color:#841808">Please calculate the chart first.</div>';
    return;
  }
  const month=parseInt(document.getElementById('mhMvM')?.value||new Date().getMonth()+1);
  const year =parseInt(document.getElementById('mhMvY')?.value||new Date().getFullYear());
  const gn=document.getElementById('mhGN')?.value;
  const bn=document.getElementById('mhBN')?.value;
  document.getElementById('mhMvBody').innerHTML=`<div class="mhp-spin"><div class="mhp-spinner"></div><div class="mhp-spin-t">${MHI[month]} ${year}${_mhGR!==null?' — '+RHI[_mhGR]+' Rashi':''} auspicious dates…</div></div>`;
  try{
    const r=await fetch('{{ route("astro.muhrat.month") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({year,month,type,lat:_masaLat,lon:_masaLon,utcOff:_masaOff,girlRashiIdx:_mhGR,boyRashiIdx:_mhBR,
        girlNakIdx:(gn!==''&&gn!=null)?parseInt(gn):null,boyNakIdx:(bn!==''&&bn!=null)?parseInt(bn):null,minScore:40})});
    if(!r.ok) throw new Error('Error '+r.status);
    const d=await r.json();
    let banner='';
    if(_mhGR!==null) banner=`<div style="background:linear-gradient(135deg,#fdf0f8,#f0f0ff);border:1.5px solid rgba(160,80,200,.2);border-radius:8px;padding:10px 18px;margin:14px 16px 0;font-family:var(--font);font-size:.95rem;color:#5a2080">♀ <strong>${RHI[_mhGR]} Rashi</strong> — Chandrabala + Tarabala. This data is <strong>only for ${RHI[_mhGR]} Rashi</strong>.${_mhBR!==null?` ♂ <strong>${RHI[_mhBR]} Groom</strong>`:''}  Change the Rashi above for other rashis.</div>`;
    document.getElementById('mhMvBody').innerHTML=banner+(d.html||'<div style="padding:20px;color:#841808;font-family:var(--font)">No auspicious dates found.</div>');
  }catch(e){ document.getElementById('mhMvBody').innerHTML=`<div style="padding:20px;color:#841808;font-family:var(--font)">Error: ${e.message}</div>`; }
}

// ── Var-Kanya Melapak Sarini (Traditional Ashtakoot Table) ──
// Pure client-side — no API needed
function showMelapSarini(){
  // All 27 nakshatras
  const NAKS=['Ashwini','Bharani','Krittika','Rohini','Mrigashira','Ardra','Punarvasu','Pushya','Ashlesha','Magha','Purva Phalguni','Uttara Phalguni','Hasta','Chitra','Swati','Vishakha','Anuradha','Jyeshtha','Moola','Purva Ashadha','Uttara Ashadha','Shravana','Dhanishta','Shatabhisha','Purva Bhadrapada','Uttara Bhadrapada','Revati'];
  const RASHI_HI=['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanya','Tula','Vrishchika','Dhanu','Makara','Kumbha','Meena'];
  // Nakshatra → Rashi (each nak belongs to a rashi by its starting degree)
  const NAK_RASHI=[0,0,0,1,1,2,2,3,3,4,4,4,5,5,6,6,7,7,8,8,8,9,9,10,10,11,11];
  // Nakshatra properties
  const NAK_GANA=[0,1,2,1,0,1,0,0,2,2,1,1,0,2,0,2,0,2,2,1,1,0,2,2,1,1,0]; // 0=Deva 1=Manushya 2=Rakshasa
  const NAK_NADI=[0,1,2,2,1,0,0,1,2,2,1,0,0,1,2,2,1,0,0,1,2,2,1,0,0,1,2]; // 0=Adi 1=Madhya 2=Antya
  const NAK_YONI=[0,1,2,3,3,4,5,2,5,6,6,7,8,9,8,9,10,10,4,11,12,11,13,0,13,7,1];
  const YONI_HI=['Ashva','Gaja','Mesha','Sarpa','Shvana','Marjara','Mushaka','Go','Mahisha','Vyaghra','Mruga','Vanara','Nakula','Simha'];
  const RASHI_VARNA=[1,2,3,0,1,2,3,0,1,2,3,0]; // 0=Brahmin 1=Kshatriya 2=Vaishya 3=Shudra
  const VARNA_HI=['Brahmin','Kshatriya','Vaishya','Shudra'];
  const GANA_HI=['Deva','Manushya','Rakshasa'];
  const NADI_HI=['Adi','Madhya','Antya'];
  const RASHI_LORD=[0,1,2,3,4,2,1,0,5,6,6,5]; // 0=Mars 1=Venus 2=Mercury 3=Moon 4=Sun 5=Jupiter 6=Saturn
  const GRAHA_MITRA=[[1,2,4],[0,2,4],[0,1,4],[0,5,6],[0,1,2],[2,3,6],[3,5]];
  const GRAHA_SHATRU=[[5,6],[3],[3,5,6],[1,2],[3,5,6],[0,1],[0,1,2,4]];
  const BHAKOOT_DOSHA=[2,12,5,9,6,8];
  const TARA_SHUBHA=[2,4,6,8,9];
  const YONI_SHATRU=[[0,4],[1,13],[2,9],[3,12],[5,11],[6,14],[7,8],[10,14]];

  function calcAshtakoot(gN, bN){
    const gR=NAK_RASHI[gN], bR=NAK_RASHI[bN];
    let total=0, koot={};

    // 1. Varna
    const gV=RASHI_VARNA[gR], bV=RASHI_VARNA[bR];
    const vGot=(bV<=gV)?1:0; total+=vGot;
    koot.varna={got:vGot,max:1,dosha:!vGot};

    // 2. Vasya (simplified)
    const vasya=0; total+=vasya; // complex - simplified to 0 for table
    koot.vasya={got:vasya,max:2,dosha:false};

    // 3. Tara
    const tDist=((gN-bN)+27)%27, tNum=(tDist%9)+1;
    const tGot=TARA_SHUBHA.includes(tNum)?3:0; total+=tGot;
    koot.tara={got:tGot,max:3,dosha:!tGot};

    // 4. Yoni
    const gY=NAK_YONI[gN], bY=NAK_YONI[bN];
    let yGot=4;
    if(gY!==bY){ const sh=YONI_SHATRU.some(([a,b])=>(gY===a&&bY===b)||(gY===b&&bY===a)); yGot=sh?0:2; }
    total+=yGot; koot.yoni={got:yGot,max:4,dosha:yGot===0};

    // 5. Graha Maitri
    const gL=RASHI_LORD[gR], bL=RASHI_LORD[bR];
    const gMit=GRAHA_MITRA[gL]||[], bMit=GRAHA_MITRA[bL]||[];
    const gSha=GRAHA_SHATRU[gL]||[], bSha=GRAHA_SHATRU[bL]||[];
    const gB=gMit.includes(bL)?'Mitra':(gSha.includes(bL)?'Shatru':'Sama');
    const bB=bMit.includes(gL)?'Mitra':(bSha.includes(gL)?'Shatru':'Sama');
    const mMap={['Mitra,Mitra']:5,['Mitra,Sama']:3,['Sama,Mitra']:3,['Sama,Sama']:3,['Mitra,Shatru']:1,['Shatru,Mitra']:1};
    const mGot=mMap[`${gB},${bB}`]??0; total+=mGot;
    koot.maitri={got:mGot,max:5,dosha:mGot===0};

    // 6. Gana
    const gG=NAK_GANA[gN], bG=NAK_GANA[bN];
    const gGot=gG===bG?6:(bG===0&&gG===1?5:0); total+=gGot;
    koot.gana={got:gGot,max:6,dosha:gGot===0};

    // 7. Bhakoot
    const bDistF=((gR-bR)+12)%12, bDistR=((bR-gR)+12)%12;
    const bDosha=BHAKOOT_DOSHA.includes(bDistF)||BHAKOOT_DOSHA.includes(bDistR);
    const bhGot=bDosha?0:7; total+=bhGot;
    koot.bhakoot={got:bhGot,max:7,dosha:bDosha};

    // 8. Nadi
    const nGot=NAK_NADI[gN]!==NAK_NADI[bN]?8:0; total+=nGot;
    koot.nadi={got:nGot,max:8,dosha:!nGot};

    return {total, koot};
  }

  // Filter by selected rashis
  const gRFilter=_mhGR, bRFilter=_mhBR;
  const gNaks=gRFilter!==null ? NAKS.map((_,i)=>i).filter(i=>NAK_RASHI[i]===gRFilter) : NAKS.map((_,i)=>i);
  const bNaks=bRFilter!==null ? NAKS.map((_,i)=>i).filter(i=>NAK_RASHI[i]===bRFilter) : NAKS.map((_,i)=>i);

  // Build table
  const font="font-family:'Tiro Devanagari Sanskrit',serif";
  let html=`<div style="background:var(--bg0);${font}">
  <div style="padding:14px 20px;background:linear-gradient(135deg,var(--bg2),var(--glt));border-bottom:2px solid var(--bdr2);">
    <div style="${font};font-size:1.4rem;font-weight:600;color:var(--gdk);">Var-Kanya Ashtakoot Compatibility Table</div>
    <div style="font-size:.9rem;color:var(--ink3);margin-top:4px;">Ashtakoot Compatibility · Full Table · ${gRFilter!==null?'♀ '+RASHI_HI[gRFilter]+' Rashi':'All Bride Nakshatras'} × ${bRFilter!==null?'♂ '+RASHI_HI[bRFilter]+' Rashi':'All Groom Nakshatras'}</div>
    ${(gRFilter===null||bRFilter===null)?`<div style="font-size:.85rem;color:var(--saffron);margin-top:6px;">💡 Select Rashi above — filter will show only that Rashi's nakshatras</div>`:''}
  </div>
  <div style="overflow-x:auto;">
  <table style="border-collapse:collapse;${font};font-size:.9rem;min-width:100%;">
  <thead>
    <tr style="background:#2a1848;color:#f0d8ff;position:sticky;top:0;z-index:5;">
      <th style="padding:10px 12px;text-align:left;border:1px solid rgba(255,255,255,.15);min-width:120px;font-size:.95rem;">
        ♀ Bride ↓<br>♂ Groom →
      </th>`;

  // Boy nak headers
  for(const bN of bNaks){
    const bR=NAK_RASHI[bN];
    html+=`<th style="padding:8px 6px;border:1px solid rgba(255,255,255,.15);text-align:center;min-width:70px;font-size:.82rem;">
      <div style="color:#d8eaff;font-weight:600;">${NAKS[bN]}</div>
      <div style="color:rgba(200,180,255,.6);font-size:.72rem;">${RASHI_HI[bR]}</div>
      <div style="color:rgba(200,180,255,.5);font-size:.7rem;">${GANA_HI[NAK_GANA[bN]]}</div>
    </th>`;
  }
  html+=`</tr></thead><tbody>`;

  // Rows for each girl nak
  for(const gN of gNaks){
    const gR=NAK_RASHI[gN];
    const rowBg=(gN%2===0)?'#fdf8ee':'#fffbf5';
    html+=`<tr style="background:${rowBg};">
      <td style="padding:9px 12px;border:1px solid rgba(168,112,40,.15);white-space:nowrap;background:linear-gradient(135deg,#f2e0c4,#f9edd8);position:sticky;left:0;z-index:2;">
        <div style="font-weight:600;color:#5a2800;font-size:.95rem;">${NAKS[gN]}</div>
        <div style="color:#9a6b0a;font-size:.75rem;">${RASHI_HI[gR]}</div>
        <div style="font-size:.72rem;color:#7a5830;">${GANA_HI[NAK_GANA[gN]]} · ${NADI_HI[NAK_NADI[gN]]} · ${YONI_HI[NAK_YONI[gN]]}</div>
      </td>`;

    for(const bN of bNaks){
      const r=calcAshtakoot(gN,bN);
      const t=r.total;
      const clr=t>=28?'#1a6a2a':(t>=22?'#2d7a3a':(t>=18?'#9a6b0a':(t>=14?'#8a5010':'#c0302a')));
      const bg=t>=28?'#f0faf2':(t>=22?'#f5faf0':(t>=18?'#fffdf0':(t>=14?'#fffbf5':'#fff5f0')));
      const nadDosha=r.koot.nadi.dosha;
      const bhDosha=r.koot.bhakoot.dosha;
      const ganDosha=r.koot.gana.dosha;
      const dBadge=nadDosha?'<span style="color:#841808;font-size:.6rem;display:block;">Nadi✗</span>':(bhDosha?'<span style="color:#841808;font-size:.6rem;display:block;">Bhakoot✗</span>':'');
      html+=`<td style="padding:7px 5px;border:1px solid rgba(168,112,40,.12);text-align:center;background:${bg};cursor:pointer;"
             title="${NAKS[gN]} × ${NAKS[bN]}: ${t}/36 — Varna:${r.koot.varna.got} Vasya:${r.koot.vasya.got} Tara:${r.koot.tara.got} Yoni:${r.koot.yoni.got} Maitri:${r.koot.maitri.got} Gana:${r.koot.gana.got} Bhakoot:${r.koot.bhakoot.got} Nadi:${r.koot.nadi.got}">
        <div style="font-family:'Crimson Pro',serif;font-size:1.15rem;font-weight:700;color:${clr};line-height:1;">${t}</div>
        ${dBadge}
      </td>`;
    }
    html+=`</tr>`;
  }

  html+=`</tbody></table></div>
  <div style="padding:12px 20px;background:#f9edd8;border-top:2px solid rgba(168,112,40,.2);display:flex;gap:16px;flex-wrap:wrap;font-size:.85rem;">
    <span style="background:#f0faf2;color:#1a6a2a;padding:3px 10px;border-radius:5px;border:1px solid #1a6a2a;font-weight:600;">● 28+ Excellent</span>
    <span style="background:#f5faf0;color:#2d7a3a;padding:3px 10px;border-radius:5px;border:1px solid #2d7a3a;font-weight:600;">● 22+ Good</span>
    <span style="background:#fffdf0;color:#9a6b0a;padding:3px 10px;border-radius:5px;border:1px solid #9a6b0a;font-weight:600;">● 18+ Average</span>
    <span style="background:#fffbf5;color:#8a5010;padding:3px 10px;border-radius:5px;border:1px solid #8a5010;font-weight:600;">● 14+ Below Average</span>
    <span style="background:#fff5f0;color:#c0302a;padding:3px 10px;border-radius:5px;border:1px solid #c0302a;font-weight:600;">● <14 Incompatible</span>
    <span style="color:#7a5830;margin-left:auto;">Hover for details | Nadi✗ = Nadi Dosha | Bhakoot✗ = Bhakoot Dosha</span>
  </div>
  </div>`;

  document.getElementById('mhContent').innerHTML=html;
}

// ── Calculate Muhurat ──────────────────────────────────
async function mhCalculate(){
  if(_mhLoading) return;
  if(!_mhCat){ mhGoHome(); return; }
  if(typeof _masaLat==='undefined'||_masaLat===null){
    document.getElementById('mhContent').innerHTML='<div class="mhp-empty"><div class="mhp-empty-t">Please calculate the chart first</div></div>'; return;
  }
  const dateISO=document.getElementById('mhDateISO').value;
  if(!dateISO){ document.getElementById('mhContent').innerHTML='<div class="mhp-empty"><div class="mhp-empty-t">Please enter a valid date</div></div>'; return; }
  if(_mhCat==='month_vivah'||_mhCat==='month_samskar'){ showMonthView(_mhCat); return; }
  _mhLoading=true;
  const btn=document.getElementById('mhCalcBtn');
  if(btn){btn.disabled=true;btn.textContent='Calculating…';}
  document.getElementById('mhContent').innerHTML='<div class="mhp-spin"><div class="mhp-spinner"></div><div class="mhp-spin-t">Computing Vedic Muhurta…</div></div>';
  const[yr,mo,dy]=dateISO.split('-').map(Number);
  const apiType={choghadiya:'vivah',hora:'vivah',abhijit:'vivah',rahu:'vivah',yoga:'vivah',lagna:'vivah',panchak:'vivah',doghati:'vivah',gowri:'vivah',vivah:'vivah',milan:'vivah',griha:'griha_pravesh',vahana:'vahana',mundan:'mundan',sampatti:'sampatti'}[_mhCat]||'vivah';
  const gn=document.getElementById('mhGN')?.value; const bn=document.getElementById('mhBN')?.value;
  try{
    const r=await fetch('{{ route("astro.muhrat") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({year:yr,month:mo,day:dy,lat:_masaLat,lon:_masaLon,utcOff:_masaOff,type:apiType,
        girlRashiIdx:_mhGR,boyRashiIdx:_mhBR,
        girlNakIdx:(gn!==''&&gn!=null)?parseInt(gn):null,boyNakIdx:(bn!==''&&bn!=null)?parseInt(bn):null,displayCat:_mhCat})});
    if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||'Error '+r.status);}
    const d=await r.json();
    document.getElementById('mhContent').innerHTML=d.html||'<div class="mhp-empty"><div class="mhp-empty-t">No results found</div></div>';
  }catch(e){
    document.getElementById('mhContent').innerHTML=`<div class="mhp-empty"><div class="mhp-empty-t">Error: ${e.message}</div></div>`;
  }finally{
    _mhLoading=false;
    if(btn){btn.disabled=false;btn.textContent='Calculate Muhurat';}
  }
}

// ── Year Scan ──────────────────────────────────────────
function openYearScan(){
  const rd=document.getElementById('mhYRD');
  if(rd) rd.innerHTML=(_mhGR!==null||_mhBR!==null)?`${_mhGR!==null?`<span style="background:#fce8f0;color:#7a1050;padding:2px 9px;border-radius:6px;font-size:.88rem;font-weight:600">♀ ${RHI[_mhGR]}</span> `:''} ${_mhBR!==null?`<span style="background:#e8effe;color:#1a3080;padding:2px 9px;border-radius:6px;font-size:.88rem;font-weight:600">♂ ${RHI[_mhBR]}</span>`:''}` :'<span style="color:#b09070;font-size:.88rem">Rashi not selected</span>';
  document.getElementById('mhYearModal').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeYearScan(){ document.getElementById('mhYearModal').classList.remove('open'); document.body.style.overflow=''; }

async function runYearScan(){
  if(typeof _masaLat==='undefined'||_masaLat===null){ document.getElementById('mhYB').innerHTML='<div style="padding:30px;font-family:\'Tiro Devanagari Sanskrit\',serif;color:#841808">Please calculate the chart first.</div>'; return; }
  const year=parseInt(document.getElementById('mhYI').value)||new Date().getFullYear();
  const type=document.getElementById('mhYT').value;
  const minScore=parseInt(document.getElementById('mhYMS').value)||55;
  const btn=document.getElementById('mhYSBtn');
  const gn=document.getElementById('mhGN')?.value; const bn=document.getElementById('mhBN')?.value;
  const THI={vivah:'Vivah Muhurat',griha_pravesh:'Griha Pravesh',vahana:'Vahana Purchase',mundan:'Mundan',sampatti:'Sampatti Purchase'};
  if(btn){btn.disabled=true;btn.textContent='Calculating…';}
  document.getElementById('mhYB').innerHTML=`<div style="display:flex;flex-direction:column;align-items:center;padding:60px;gap:16px"><div class="mhp-mspinner"></div><div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.1rem;color:#9a6b0a">${year} — ${THI[type]} auspicious dates…</div><div style="font-size:.88rem;color:#b09070;font-family:'Tiro Devanagari Sanskrit',serif;text-align:center">${_mhGR!==null?'<strong style="color:#7a1050">♀ '+RHI[_mhGR]+'</strong> Rashi Chandrabala computing…':'General calculation (Rashi not selected)'}</div></div>`;
  try{
    const r=await fetch('{{ route("astro.muhrat.year") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({year,type,lat:_masaLat,lon:_masaLon,utcOff:_masaOff,girlRashiIdx:_mhGR,boyRashiIdx:_mhBR,
        girlNakIdx:(gn!==''&&gn!=null)?parseInt(gn):null,boyNakIdx:(bn!==''&&bn!=null)?parseInt(bn):null,minScore})});
    if(!r.ok) throw new Error('Error '+r.status);
    const d=await r.json();
    const RI=(_mhGR!==null||_mhBR!==null)?`<div style="display:flex;gap:8px;flex-wrap:wrap">${_mhGR!==null?`<span style="background:#fce8f0;border:1.5px solid rgba(200,80,160,.3);color:#7a1050;padding:4px 12px;border-radius:8px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;font-weight:600">♀ ${RHI[_mhGR]}</span>`:''} ${_mhBR!==null?`<span style="background:#e8effe;border:1.5px solid rgba(80,120,200,.3);color:#1a3080;padding:4px 12px;border-radius:8px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;font-weight:600">♂ ${RHI[_mhBR]}</span>`:''}</div>`:`<span style="font-size:.85rem;color:#b09070;font-family:'Tiro Devanagari Sanskrit',serif">Rashi not selected</span>`;
    document.getElementById('mhYB').innerHTML=`<div style="padding:14px 20px;background:linear-gradient(135deg,#f9edd8,#f5e6c0);border-bottom:2px solid rgba(168,112,40,.35);display:flex;align-items:center;gap:14px;flex-wrap:wrap;position:sticky;top:0;z-index:10;box-shadow:0 2px 8px rgba(90,40,0,.1)">
      <div><div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.15rem;color:#6b4800;font-weight:600">${year} — ${THI[type]}</div>
      <div style="font-size:.88rem;color:#7a5830;margin-top:2px">Total <strong style="color:#c8521a">${d.total}</strong> auspicious dates · Min score <strong>${minScore}+</strong></div></div>
      <div style="margin-left:auto">${RI}</div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;font-size:.82rem;width:100%;border-top:1px solid rgba(168,112,40,.2);padding-top:8px">
        <span style="background:#f0faf2;border:1px solid #1a5a28;color:#1a5a28;padding:3px 10px;border-radius:5px;font-weight:600">● Excellent 85+</span>
        <span style="background:#f5faf0;border:1px solid #2d7a3a;color:#2d7a3a;padding:3px 10px;border-radius:5px;font-weight:600">● Good 70+</span>
        <span style="background:#fffdf0;border:1px solid #9a6b0a;color:#9a6b0a;padding:3px 10px;border-radius:5px;font-weight:600">● Auspicious 55+</span>
        <span style="background:#fffbf5;border:1px solid #8a5010;color:#8a5010;padding:3px 10px;border-radius:5px;font-weight:600">● Average 40+</span>
      </div></div>
      <div style="background:#fdf6ec">${d.html||''}</div>`;
  }catch(e){ document.getElementById('mhYB').innerHTML=`<div style="padding:30px;font-family:'Tiro Devanagari Sanskrit',serif;color:#841808">Error: ${e.message}</div>`; }
  finally{ if(btn){btn.disabled=false;btn.textContent='Search Year';} }
}

// ── Init ────────────────────────────────────────────
function initMuhratPanel(){
  const dateISO=document.getElementById('mhDateISO').value||document.getElementById('dateInput')?.value;
  if(dateISO){
    const[y,m,d]=dateISO.split('-');
    document.getElementById('mhDateDisplay').value=`${d}/${m}/${y}`;
    document.getElementById('mhDateISO').value=dateISO;
  }
  selectGroup('vivah_g');
  selectCat('month_vivah');
}
async function calcMuhrat(){return mhCalculate();}
</script>