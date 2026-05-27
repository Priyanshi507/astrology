{{-- _js_festival.blade.php — v10 Panchang warm redesign --}}

<script>
// ══════════════════════════════════════════════════════
//  FESTIVAL STATE
// ══════════════════════════════════════════════════════
let _activeCat   = 'all';
let _activeSub   = '';
let _festYear    = null;
let _festLoading = false;
let _festData    = [];

const FMAIN_SUBS = {
  all: [],
  vrat: [
    {key:'ekadashi',        label:'एकादशी'},
    {key:'pradosh',         label:'प्रदोष व्रत'},
    {key:'satyanarayan',    label:'सत्यनारायण'},
    {key:'masik_shivratri', label:'मासिक शिवरात्रि'},
    {key:'chaturthi',       label:'चतुर्थी'},
    {key:'kalashtami',      label:'कालाष्टमी'},
    {key:'durgaashtami',    label:'दुर्गा अष्टमी'},
    {key:'amavasya',        label:'अमावस्या'},
    {key:'purnima',         label:'पूर्णिमा'},
  ],
  parv: [
    {key:'festival',  label:'प्रमुख उत्सव'},
    {key:'navratri',  label:'नवरात्रि'},
    {key:'sankranti', label:'संक्रांति'},
    {key:'shraddha',  label:'पितृ पक्ष'},
  ],
  jayanti: [
    {key:'jayanti', label:'सभी जयंती'},
  ],
  other: [
    {key:'national',  label:'राष्ट्रीय'},
    {key:'christian', label:'ईसाई'},
    {key:'sikh',      label:'सिख'},
    {key:'jain',      label:'जैन'},
    {key:'muslim',    label:'मुस्लिम'},
  ],
};

const MONTHS_EN = ['','January','February','March','April','May','June',
                   'July','August','September','October','November','December'];
const MONTHS_HI = ['','जनवरी','फ़रवरी','मार्च','अप्रैल','मई','जून',
                   'जुलाई','अगस्त','सितम्बर','अक्टूबर','नवम्बर','दिसम्बर'];
const DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const DAYS_HI   = ['रवि','सोम','मंगल','बुध','गुरु','शुक्र','शनि'];

// Category → CSS class + modal banner gradient
const CAT_INFO = {
  ekadashi        : { cls:'fpc-ekadashi',        bannerBg:'linear-gradient(150deg,#3b0764,#6d28d9)',  accent:'#6d28d9' },
  satyanarayan    : { cls:'fpc-satyanarayan',    bannerBg:'linear-gradient(150deg,#1e3a8a,#2563eb)',  accent:'#2563eb' },
  pradosh         : { cls:'fpc-pradosh',         bannerBg:'linear-gradient(150deg,#0a3028,#1a5a50)',  accent:'#1a5a50' },
  masik_shivratri : { cls:'fpc-masik_shivratri', bannerBg:'linear-gradient(150deg,#2e1065,#5b21b6)',  accent:'#5b21b6' },
  chaturthi       : { cls:'fpc-chaturthi',       bannerBg:'linear-gradient(150deg,#6b3a00,#9a6b0a)',  accent:'#9a5800' },
  kalashtami      : { cls:'fpc-kalashtami',      bannerBg:'linear-gradient(150deg,#111827,#374151)',  accent:'#475569' },
  durgaashtami    : { cls:'fpc-durgaashtami',    bannerBg:'linear-gradient(150deg,#5c0519,#be123c)',  accent:'#be123c' },
  festival        : { cls:'fpc-festival',        bannerBg:'linear-gradient(150deg,#7a2a08,#c8521a)',  accent:'#c8521a' },
  purnima         : { cls:'fpc-purnima',         bannerBg:'linear-gradient(150deg,#5a3800,#9a6b0a)',  accent:'#9a6b0a' },
  amavasya        : { cls:'fpc-amavasya',        bannerBg:'linear-gradient(150deg,#0f172a,#1e293b)',  accent:'#475569' },
  navratri        : { cls:'fpc-navratri',        bannerBg:'linear-gradient(150deg,#4a044e,#86198f)',  accent:'#86198f' },
  sankranti       : { cls:'fpc-sankranti',       bannerBg:'linear-gradient(150deg,#6b1a08,#a03818)',  accent:'#a03818' },
  jayanti         : { cls:'fpc-jayanti',         bannerBg:'linear-gradient(150deg,#1a2a5a,#1e3a8a)',  accent:'#1e3a8a' },
  shraddha        : { cls:'fpc-shraddha',        bannerBg:'linear-gradient(150deg,#1e293b,#334155)',  accent:'#64748b' },
  national        : { cls:'fpc-national',        bannerBg:'linear-gradient(150deg,#052e16,#14532d)',  accent:'#14532d' },
  christian       : { cls:'fpc-satyanarayan',    bannerBg:'linear-gradient(150deg,#1e3a8a,#2563eb)',  accent:'#1e3a8a' },
  sikh            : { cls:'fpc-sankranti',       bannerBg:'linear-gradient(150deg,#6b3a00,#9a5800)',  accent:'#9a5800' },
  jain            : { cls:'fpc-festival',        bannerBg:'linear-gradient(150deg,#1a3005,#2d5c0a)',  accent:'#2d5c0a' },
  muslim          : { cls:'fpc-satyanarayan',    bannerBg:'linear-gradient(150deg,#0a3a2a,#1a5a50)',  accent:'#1a5a50' },
};
const CAT_DEFAULT = { cls:'fpc-default', bannerBg:'linear-gradient(150deg,#5a3800,#9a6b0a)', accent:'#9a6b0a' };

function getCatInfo(cat) { return CAT_INFO[cat] || CAT_DEFAULT; }

// Category chip label (Hindi-first)
const CAT_CHIP_LABEL = {
  ekadashi:'एकादशी', satyanarayan:'सत्यनारायण', pradosh:'प्रदोष',
  masik_shivratri:'शिवरात्रि', chaturthi:'चतुर्थी', kalashtami:'कालाष्टमी',
  durgaashtami:'अष्टमी', festival:'उत्सव', purnima:'पूर्णिमा',
  amavasya:'अमावस्या', navratri:'नवरात्रि', sankranti:'संक्रांति',
  jayanti:'जयंती', shraddha:'श्राद्ध', national:'राष्ट्रीय',
  christian:'ईसाई', sikh:'सिख', jain:'जैन', muslim:'मुस्लिम',
};

// ══════════════════════════════════════════════════════
//  SUB-PILLS RENDER
// ══════════════════════════════════════════════════════
function _renderFestSubPills() {
  const pills = FMAIN_SUBS[_activeCat] || [];
  const sp    = document.getElementById('festSubPills');
  if (!sp) return;
  if (!pills.length) { sp.innerHTML = ''; sp.style.display = 'none'; return; }
  sp.style.display = 'flex';
  sp.innerHTML = pills.map(p =>
    `<button class="fp-sub-pill ${_activeSub === p.key ? 'fp-sub-on' : ''}"
        onclick="_festSubSwitch('${p.key}')">
      ${p.label}
    </button>`
  ).join('');
}

function _festSubSwitch(sub) {
  _activeSub = (_activeSub === sub) ? '' : sub;
  _renderFestSubPills();
  _fetchFest(_activeSub || _activeCat);
}

function fmainSwitch(cat, el) {
  if (_festLoading) return;
  _activeCat = cat; _activeSub = '';
  document.querySelectorAll('.fp-tab').forEach(b => b.classList.remove('fp-active'));
  if (el) el.classList.add('fp-active');
  _renderFestSubPills();
  if (_masaLat !== null) _fetchFest(cat);
}

// ══════════════════════════════════════════════════════
//  YEAR CONTROLS
// ══════════════════════════════════════════════════════
function _toDevanagariDigits(n) {
  return String(n).replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
}
function _updateYearDisplay(year) {
  const lbl = document.getElementById('festYearLabel');
  if (lbl) lbl.textContent = _toDevanagariDigits(year);
  const inp = document.getElementById('festYear');
  if (inp) inp.value = year;
}
function festLoad() {
  const yr = parseInt(document.getElementById('festYear')?.value)
           || _masaYr || new Date().getFullYear();
  _updateYearDisplay(yr);
  _fetchFest(_activeSub || _activeCat);
}
function festNav(delta) {
  if (_festLoading) return;
  const el = document.getElementById('festYear');
  const yr = (parseInt(el?.value) || _masaYr || new Date().getFullYear()) + delta;
  _updateYearDisplay(yr);
  if (_masaLat !== null) _fetchFest(_activeSub || _activeCat);
}
function festYearChanged() {
  const yr = parseInt(document.getElementById('festYear')?.value);
  if (!yr || yr < 1900 || yr > 2100) return;
  _updateYearDisplay(yr);
  if (_masaLat !== null && !_festLoading) {
    clearTimeout(window._festYearTimer);
    window._festYearTimer = setTimeout(() => _fetchFest(_activeSub || _activeCat), 700);
  }
}
function festAutoLoad() {
  if (_masaLat === null) return;
  const yr = parseInt(document.getElementById('festYear')?.value)
           || _masaYr || new Date().getFullYear();
  _updateYearDisplay(yr);
  _renderFestSubPills();
  if (!_festYear || _festYear !== yr) _fetchFest(_activeSub || _activeCat);
}

// ══════════════════════════════════════════════════════
//  FETCH
// ══════════════════════════════════════════════════════
async function _fetchFest(category = 'all') {
  const fc = document.getElementById('festivalContent');
  if (!fc || _masaLat === null) return;

  const yr = parseInt(document.getElementById('festYear')?.value)
           || _masaYr || new Date().getFullYear();
  _updateYearDisplay(yr);
  _festLoading = true;

  fc.innerHTML = `<div class="fp-loading">
    <div class="fp-loader"></div>
    <div class="fp-loading-text">
      पंचांग गणना हो रही है…<br>
      <span style="font-family:'IBM Plex Mono',monospace;font-size:.68rem;color:var(--fp-ink4)">
        ${yr} · Jean Meeus Algorithms</span>
    </div>
  </div>`;

  try {
    const res = await fetch('/astro/festivals', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body: JSON.stringify({year:yr, lat:_masaLat, lon:_masaLon, utcOff:_masaOff, category})
    });
    if (!res.ok) throw new Error('Server error ' + res.status);
    const data = await res.json();
    _festData = data.festivals || [];
    _festYear = yr;
    _renderCards(_festData, category);
  } catch(e) {
    fc.innerHTML = `<div class="fp-empty">
      <div style="font-size:2rem;margin-bottom:12px">⚠</div>
      त्रुटि: ${e.message}
    </div>`;
  } finally {
    _festLoading = false;
  }
}

// ══════════════════════════════════════════════════════
//  CARD RENDERER
// ══════════════════════════════════════════════════════
function _renderCards(festivals, activeCategory) {
  const fc = document.getElementById('festivalContent');
  if (!fc) return;

  if (!festivals || !festivals.length) {
    fc.innerHTML = `<div class="fp-empty">
      <div style="font-size:2.5rem;margin-bottom:12px;opacity:.4">🪔</div>
      कोई उत्सव नहीं मिला।
    </div>`; return;
  }

  const groupMap = {
    vrat:    ['ekadashi','pradosh','satyanarayan','masik_shivratri','chaturthi',
              'kalashtami','durgaashtami','amavasya','purnima','shraddha'],
    parv:    ['festival','navratri','sankranti'],
    jayanti: ['jayanti'],
    other:   ['national','christian','sikh','jain','muslim'],
  };

  const filtered = (activeCategory === 'all') ? festivals : festivals.filter(f => {
    const c = f.category || '';
    if (c === activeCategory) return true;
    if (groupMap[activeCategory]) return groupMap[activeCategory].includes(c);
    return false;
  });

  if (!filtered.length) {
    fc.innerHTML = `<div class="fp-empty">
      <div style="font-size:2.5rem;margin-bottom:12px;opacity:.4">🪷</div>
      इस श्रेणी में कोई उत्सव नहीं।
    </div>`; return;
  }

  const byMonth = {};
  filtered.forEach(f => {
    const mo = parseInt(f.date.slice(5,7));
    (byMonth[mo] = byMonth[mo] || []).push(f);
  });

  let html = `
  <div class="fp-info-strip">
    <div>
      <div class="fp-strip-title">${_festYear} — ${_getCatLabel(activeCategory)}</div>
      <div class="fp-strip-sub">Lahiri Ayanamsa · Meeus Precision · ${filtered.length} events</div>
    </div>
    <div class="fp-strip-count">${filtered.length} उत्सव</div>
  </div>`;

  Object.keys(byMonth).sort((a,b)=>+a-+b).forEach(mo => {
    const items = byMonth[mo];
    html += `
    <div class="fp-month-hdr">
      <span class="fp-month-en">${MONTHS_EN[mo]}</span>
      <span class="fp-month-hi">${MONTHS_HI[mo]}</span>
      <span class="fp-month-ct">${items.length} उत्सव</span>
    </div>
    <div class="fp-card-grid">
      ${items.map(f => _buildCard(f)).join('')}
    </div>`;
  });

  fc.innerHTML = html;
}

function _getCatLabel(cat) {
  return ({
    all:'सभी उत्सव व व्रत', vrat:'व्रत', parv:'पर्व',
    jayanti:'जयंती', other:'अन्य',
    ekadashi:'एकादशी', pradosh:'प्रदोष', purnima:'पूर्णिमा',
    amavasya:'अमावस्या', festival:'प्रमुख उत्सव', navratri:'नवरात्रि',
    sankranti:'संक्रांति', shraddha:'पितृ पक्ष', national:'राष्ट्रीय दिवस',
    chaturthi:'चतुर्थी', kalashtami:'कालाष्टमी', durgaashtami:'दुर्गा अष्टमी',
    satyanarayan:'सत्यनारायण', masik_shivratri:'मासिक शिवरात्रि',
  })[cat] || cat;
}

function _buildCard(f) {
  const [,, dy] = f.date.split('-');
  const dayNum  = parseInt(dy);
  const dow     = new Date(f.date + 'T12:00:00').getDay();
  const dayEn   = DAYS_EN[dow];
  const dayHi   = DAYS_HI[dow];
  const cat     = f.category || 'default';
  const ci      = getCatInfo(cat);

  const nameHi    = _esc(f.name_hi || '');
  const nameEn    = _esc(f.name    || '');
  const sigHi     = (f.significance || '').slice(0, 140);
  const tithi     = _esc(f.tithi   || '');
  const masa      = _esc(f.masa    || '');
  const sunrise   = f.sunrise ? f.sunrise.slice(0,5) : '';
  const sunset    = f.sunset  ? f.sunset.slice(0,5)  : '';
  const chipLabel = CAT_CHIP_LABEL[cat] || cat;

  const data = JSON.stringify({
    name: f.name||'', name_hi: f.name_hi||'',
    date: f.date||'', tithi: f.tithi||'', masa: f.masa||'',
    significance: f.significance||'',
    details: f.details||'', rituals: f.rituals||[],
    mantra: f.mantra||'',
    sunrise: f.sunrise||'', sunset: f.sunset||'',
    type: f.type||'festival', cat: cat,
    bannerBg: ci.bannerBg,
    accent: ci.accent,
    vidhiTitle: f.vidhiTitle||'',
  }).replace(/'/g, '&#39;');

  return `
  <div class="fp-card ${ci.cls}" onclick='openFestDetail(${data})'>
    <div class="fpc-bar"></div>
    <div class="fc-head">
      <div class="fc-date-box">
        <div class="fc-date-num">${dayNum}</div>
        <div class="fc-date-day">${dayEn}</div>
        <div class="fc-date-hi">${dayHi}</div>
      </div>
      <div class="fc-icon-name">
        <div class="fc-badges">
          <span class="fc-cat-chip">${chipLabel}</span>
          ${tithi ? `<span class="fc-tithi-chip">${tithi}</span>` : ''}
        </div>
        <div class="fc-name-hi">${nameHi}</div>
        <div class="fc-name-en">${nameEn}</div>
      </div>
    </div>
    <div class="fc-body">
      ${sigHi ? `<div class="fc-sig">${_esc(sigHi)}${(f.significance||'').length>140?'…':''}</div>` : ''}
      ${(sunrise||sunset) ? `<div class="fc-timing">
        ${sunrise ? `<span class="fc-time-chip">🌅 ${sunrise}</span>` : ''}
        ${sunset  ? `<span class="fc-time-chip">🌇 ${sunset}</span>`  : ''}
      </div>` : ''}
    </div>
    <div class="fc-footer">
      <span class="fc-masa">${masa || '—'}</span>
      <span class="fc-detail-hint">विवरण देखें →</span>
    </div>
  </div>`;
}

function _esc(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ══════════════════════════════════════════════════════
//  DETAIL MODAL — warm panchang style
// ══════════════════════════════════════════════════════
function openFestDetail(f) {
  const old = document.getElementById('festDetailOverlay');
  if (old) old.remove();

  const [, mo, dy] = (f.date || '2026-01-01').split('-');
  const dow      = new Date(f.date + 'T12:00:00').getDay();
  const ci       = getCatInfo(f.cat || f.category || 'default');
  const bannerBg = f.bannerBg || ci.bannerBg;
  const accent   = f.accent   || ci.accent || '#9a6b0a';

  // Format date in Hindi
  const moHi = MONTHS_HI[parseInt(mo)] || '';
  const dateHi = `${parseInt(dy)} ${moHi}`;

  // Rituals
  let ritualsHtml = '';
  if (f.rituals && f.rituals.length) {
    ritualsHtml = `
    <div class="fp-modal-section">
      <div class="fp-modal-sec-title">पूजन विधि</div>
      <div class="fp-modal-rituals">
        ${f.rituals.filter(r=>r).map((r,i) => `
        <div class="fp-modal-ritual">
          <div class="fp-ritual-num">${i+1}</div>
          <div class="fp-ritual-text">${_esc(r)}</div>
        </div>`).join('')}
      </div>
    </div>`;
  }

  let mantraHtml = '';
  if (f.mantra) {
    mantraHtml = `
    <div class="fp-modal-section">
      <div class="fp-modal-sec-title">मंत्र</div>
      <div class="fp-modal-mantra">${_esc(f.mantra)}</div>
    </div>`;
  }

  let detailsHtml = '';
  if (f.details) {
    detailsHtml = `
    <div class="fp-modal-section">
      <div class="fp-modal-sec-title">${f.vidhiTitle ? _esc(f.vidhiTitle) : 'विशेष जानकारी'}</div>
      <div class="fp-modal-details">${f.details}</div>
    </div>`;
  }

  let timingHtml = '';
  if (f.sunrise || f.sunset) {
    timingHtml = `
    <div class="fp-modal-section">
      <div class="fp-modal-sec-title">समय</div>
      <div class="fp-modal-timing">
        ${f.sunrise ? `<div class="fp-modal-time-chip">🌅 <span style="color:var(--fp-ink3);margin-right:8px">उदय</span> <strong>${f.sunrise}</strong></div>` : ''}
        ${f.sunset  ? `<div class="fp-modal-time-chip">🌇 <span style="color:var(--fp-ink3);margin-right:8px">अस्त</span> <strong>${f.sunset}</strong></div>`  : ''}
      </div>
    </div>`;
  }

  const overlay = document.createElement('div');
  overlay.id = 'festDetailOverlay';
  overlay.innerHTML = `
  <div class="fp-modal" onclick="event.stopPropagation()">
    <button class="fp-modal-close" onclick="closeFestDetail()">✕</button>

    <div class="fp-modal-banner" style="background:${bannerBg}">
      <div class="fp-modal-hero">
        <div class="fp-modal-date">
          <div class="fp-modal-date-num">${parseInt(dy)}</div>
          <div class="fp-modal-date-dow-en">${DAYS_EN[dow]}</div>
          <div class="fp-modal-date-dow-hi">${DAYS_HI[dow]}</div>
        </div>
        <div class="fp-modal-names">
          <div class="fp-modal-name-hi">${_esc(f.name_hi || '')}</div>
          <div class="fp-modal-name-en">${_esc(f.name || '')}</div>
          <div class="fp-modal-meta">
            ${f.tithi ? `<span class="fp-modal-badge">${_esc(f.tithi)}</span>` : ''}
            ${f.masa  ? `<span class="fp-modal-badge">${_esc(f.masa)} मास</span>` : ''}
            ${f.date  ? `<span class="fp-modal-badge">${dateHi}</span>` : ''}
            ${f.type  ? `<span class="fp-modal-badge">${f.type === 'vrat' ? 'व्रत' : (f.type === 'festival' ? 'पर्व' : f.type)}</span>` : ''}
          </div>
        </div>
      </div>
    </div>

    <div class="fp-modal-body">
      ${f.significance ? `
      <div class="fp-modal-section">
        <div class="fp-modal-sec-title">महत्व</div>
        <div class="fp-modal-sig" style="border-left-color:${accent}">${_esc(f.significance)}</div>
      </div>` : ''}
      ${ritualsHtml}
      ${mantraHtml}
      ${detailsHtml}
      ${timingHtml}
    </div>
  </div>`;

  overlay.addEventListener('click', closeFestDetail);
  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';
}

function closeFestDetail() {
  const el = document.getElementById('festDetailOverlay');
  if (el) {
    el.style.opacity = '0';
    el.style.transition = 'opacity .18s';
    setTimeout(() => { el.remove(); document.body.style.overflow = ''; }, 180);
  }
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeFestDetail();
});

// Legacy aliases
function _fetchFestHtml(category) { _fetchFest(category); }
function _renderFestList() { _fetchFest(_activeSub || _activeCat); }
</script>