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