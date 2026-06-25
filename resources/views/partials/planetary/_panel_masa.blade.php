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

<div id="gocharPanel" style="display:none">
  <div class="sec-lbl">♆ Gochar Phal — Dynamic Planetary Transit</div>

  {{-- Controls bar --}}
  <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;
              background:var(--sky-wash);border:1.5px solid var(--sky-pale);
              border-radius:16px;padding:14px 20px;margin-bottom:20px">

    {{-- Mode toggle --}}
    <div style="display:inline-flex;background:var(--card);border:1.5px solid var(--sky-pale);
                border-radius:30px;padding:3px;flex-shrink:0">
      <button id="goMode_date"  class="go-mode-btn" onclick="setGoMode('date')"
              style="border:none;background:var(--sky);color:#fff;border-radius:24px;
                     padding:7px 18px;cursor:pointer;font-weight:700;font-size:.82rem;
                     font-family:'DM Sans',sans-serif;transition:all .15s">Date</button>
      <button id="goMode_month" class="go-mode-btn" onclick="setGoMode('month')"
              style="border:none;background:transparent;color:var(--text-mid);border-radius:24px;
                     padding:7px 18px;cursor:pointer;font-weight:700;font-size:.82rem;
                     font-family:'DM Sans',sans-serif;transition:all .15s">Month</button>
      <button id="goMode_year"  class="go-mode-btn" onclick="setGoMode('year')"
              style="border:none;background:transparent;color:var(--text-mid);border-radius:24px;
                     padding:7px 18px;cursor:pointer;font-weight:700;font-size:.82rem;
                     font-family:'DM Sans',sans-serif;transition:all .15s">Year</button>
    </div>

    {{-- Date / Month / Year inputs --}}
    <input type="date"   id="goDate"  onchange="gocharFetch()"
           style="padding:8px 14px;border:1.5px solid var(--sky-pale);border-radius:10px;
                  background:var(--card);color:var(--text);font-family:'DM Sans',sans-serif;font-size:.86rem;outline:none">
    <input type="month"  id="goMonth" onchange="gocharFetch()"
           style="display:none;padding:8px 14px;border:1.5px solid var(--sky-pale);border-radius:10px;
                  background:var(--card);color:var(--text);font-family:'DM Sans',sans-serif;font-size:.86rem;outline:none">
    <input type="number" id="goYear"  min="1900" max="2100" onchange="gocharFetch()" placeholder="Year"
           style="display:none;width:100px;padding:8px 14px;border:1.5px solid var(--sky-pale);border-radius:10px;
                  background:var(--card);color:var(--text);font-family:'DM Sans',sans-serif;font-size:.86rem;outline:none">

    {{-- Prev / Next arrows --}}
    <div style="display:inline-flex;gap:6px;margin-left:auto">
      <button onclick="goShift(-1)" title="Previous"
              style="width:36px;height:36px;border-radius:50%;
                     border:1.5px solid var(--sky-pale);background:var(--card);
                     color:var(--text-mid);cursor:pointer;font-size:1.1rem;line-height:1">‹</button>
      <button onclick="goShift(1)"  title="Next"
              style="width:36px;height:36px;border-radius:50%;
                     border:1.5px solid var(--sky-pale);background:var(--card);
                     color:var(--text-mid);cursor:pointer;font-size:1.1rem;line-height:1">›</button>
    </div>
  </div>

  <div id="gocharContent" style="font-family:'DM Sans',sans-serif">
    <div style="text-align:center;padding:60px 24px;color:var(--text-lt)">
      <div style="font-size:2rem;margin-bottom:12px">🪐</div>
      <div style="font-style:italic;font-size:.95rem">Calculate your chart above, then open this tab to see transit results</div>
    </div>
  </div>
</div>