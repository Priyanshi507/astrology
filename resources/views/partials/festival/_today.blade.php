{{-- Today Panel — rendered server-side for AJAX response
     Expects: $d = TodayPanelService::prepareForView(TodayPanelService::build(...))
     All display fields are pre-computed by the service — zero @php blocks here.
--}}
<div class="tp">

{{-- Divider --}}
<div class="ta" style="display:flex;align-items:center;gap:18px;margin-bottom:28px">
    <div style="flex:1;height:1.5px;background:linear-gradient(90deg,transparent,var(--gold-lt,#e0c068),transparent)"></div>
    <span style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.9rem;letter-spacing:2px;color:var(--ink4)">Vedic Panchanga</span>
    <div style="flex:1;height:1.5px;background:linear-gradient(90deg,var(--gold-lt,#e0c068),transparent)"></div>
</div>

{{-- ══ 1. HERO ══ --}}
<div class="tp-hero ta">
    <div class="tp-hero-inner">

        {{-- Left: date --}}
        <div style="flex-shrink:0">
            <div class="tp-date-num">{{ $d['dy'] }}</div>
            <div class="tp-date-month">{{ $d['monthName'] }}</div>
            <div class="tp-date-year">{{ $d['yr'] }}</div>
        </div>

        {{-- Centre: vara + sun + festival pills --}}
        <div>
            <div class="tp-vara-lbl">Day of the Week</div>
            <div class="tp-vara-name">{{ $d['varaName'] }}</div>
            <div class="tp-vara-sub">{{ $d['varaLord'] }} · {{ $d['varaNature'] }}</div>
            <div class="tp-sun-row">
                @foreach(['Sunrise' => $d['sunrise'], 'Sunset' => $d['sunset'], 'Day Length' => $d['dayLength']] as $lbl => $val)
                <div class="tp-sun-chip">
                    <span class="tp-sun-chip-lbl">{{ $lbl }}</span>
                    <span class="tp-sun-chip-val">{{ $val }}</span>
                </div>
                @endforeach
            </div>
            @if(!empty($d['allTodayFests']))
            <div class="tp-fest-pills">
                @foreach(array_slice($d['allTodayFests'], 0, 5) as $f)
                <span class="tp-fest-pill">{{ $f['name'] ?? '—' }}</span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right: muhurta quality --}}
        <div class="tp-hero-right" style="text-align:right;flex-shrink:0;min-width:140px">
            <div class="tp-mq-lbl">Muhurta Quality</div>
            <div class="tp-mq-hi" style="color:{{ $d['mqColor'] }}">{{ $d['mqHi'] }}</div>
            <div class="tp-mq-bar">
                <div class="tp-mq-track">
                    <div class="tp-mq-fill" style="width:{{ $d['mqPct'] }}%;background:{{ $d['mqColor'] }}"></div>
                </div>
                <span class="tp-mq-pct" style="color:{{ $d['mqColor'] }}">{{ $d['mqPct'] }}%</span>
            </div>
        </div>

    </div>
</div>

{{-- ══ 1b. SUN ARC + PROGRESS RINGS ══ --}}
<div class="ta ta1" style="display:flex;gap:16px;flex-wrap:wrap;align-items:stretch;margin-bottom:28px">

    {{-- Sun arc SVG --}}
    <div style="flex:2;min-width:240px;background:#fff;border:1px solid var(--rule);border-radius:16px;box-shadow:0 2px 14px rgba(20,60,100,.05);padding:14px 20px 10px;display:flex;flex-direction:column;align-items:center;justify-content:center">
        <svg width="100%" height="96" viewBox="0 0 260 96" preserveAspectRatio="xMidYMid meet">
            <defs><linearGradient id="tpSun" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#e8902a"/><stop offset="0.5" stop-color="#f5c130"/><stop offset="1" stop-color="#e8902a"/>
            </linearGradient></defs>
            <line x1="20" y1="74" x2="240" y2="74" stroke="rgba(120,90,30,.18)" stroke-width="1.5"/>
            <path d="M24 74 Q130 -6 236 74" fill="none" stroke="url(#tpSun)" stroke-width="3.5" stroke-linecap="round"/>
            <circle cx="130" cy="18" r="16" fill="#f5c130" opacity="0.25"/>
            <circle cx="130" cy="18" r="8" fill="#f3b12a"/>
            <circle cx="24"  cy="74" r="4" fill="#e8902a"/>
            <circle cx="236" cy="74" r="4" fill="#c06028"/>
            <text x="24"  y="90" text-anchor="middle" font-family="DM Mono,monospace" font-size="11" font-weight="600" fill="#a05818">{{ $d['sr'] }}</text>
            <text x="236" y="90" text-anchor="middle" font-family="DM Mono,monospace" font-size="11" font-weight="600" fill="#7a4080">{{ $d['st'] }}</text>
            <text x="24"  y="62" text-anchor="middle" font-family="Tiro Devanagari Sanskrit,serif" font-size="10" fill="#b07840">Rise</text>
            <text x="236" y="62" text-anchor="middle" font-family="Tiro Devanagari Sanskrit,serif" font-size="10" fill="#8060a0">Set</text>
        </svg>
        <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:.95rem;color:#8a6020;margin-top:2px">
            Day Length · {{ $d['dayLength'] }}
        </div>
    </div>

    {{-- Progress rings --}}
    <div style="flex:3;min-width:300px;background:#fff;border:1px solid var(--rule);border-radius:16px;box-shadow:0 2px 14px rgba(20,60,100,.05);padding:16px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-around">
        @foreach($d['rings'] as $ring)
        @include('partials.festival._progress_ring', ['ring' => $ring])
        @endforeach
    </div>

</div>

{{-- ══ 2. PANCHANGA ══ --}}
<div class="ta ta1">
    <div class="tp-div">
        <div class="tp-div-line"></div>
        <span class="tp-div-hi">Panchanga</span>
        <span class="tp-div-en">Five Limbs of the Day</span>
        <div class="tp-div-line"></div>
    </div>

    <div class="tp-anga-grid">
        @foreach($d['angas'] as [$lbl, $en, $name, $sub, $feat])
        <div class="tp-anga{{ $feat ? ' feat' : '' }}">
            <div class="tp-anga-label">{{ $en }}</div>
            <div class="tp-anga-hi">{{ $name }}</div>
            <div class="tp-anga-sub">{{ $sub }}</div>
        </div>
        @endforeach
    </div>

    <div class="tp-detail-grid">
        @foreach($d['detailCards'] as [$accent, $title, $rows])
        <div class="tp-detail-card" style="border-top-color:{{ $accent }}">
            <div class="tp-detail-head" style="color:{{ $accent }}">{{ $title }}</div>
            @foreach($rows as [$k, $v])
                @if($v && $v !== '—')
                <div class="tp-detail-row">
                    <span class="tp-detail-key">{{ $k }}</span>
                    <span class="tp-detail-val">{{ $v }}</span>
                </div>
                @endif
            @endforeach
        </div>
        @endforeach
    </div>
</div>

{{-- ══ 3. TODAY OBSERVANCES ══ --}}
<div class="ta ta2">
    <div class="tp-div">
        <div class="tp-div-line"></div>
        <span class="tp-div-hi">Today's Festivals &amp; Vrats</span>
        <span class="tp-div-en">Today's Observances</span>
        <div class="tp-div-line"></div>
    </div>

    <div class="tp-obs-grid">
        @foreach($d['obsCats'] as $cat)
        <div class="tp-obs" style="border-left:4px solid {{ $cat['accent'] }}">
            <div class="tp-obs-head" style="background:{{ $cat['bg'] }}">
                <div>
                    <div class="tp-obs-head-hi" style="color:{{ $cat['accent'] }}">{{ $cat['label'] }}</div>
                    <div class="tp-obs-head-en">{{ $cat['enLabel'] }}</div>
                </div>
                <div class="tp-obs-cnt"
                     style="background:{{ $cat['cntBg'] }};color:{{ $cat['cntColor'] }}">
                    {{ $cat['cntDisplay'] }}
                </div>
            </div>
            @if(!$cat['cnt'])
                <div class="tp-obs-empty">No {{ $cat['label'] }} today</div>
            @else
                <div class="tp-obs-body">
                    @foreach($cat['items'] as $f)
                    <div class="tp-fest-row">
                        <div class="tp-fest-dot" style="background:{{ $cat['accent'] }}"></div>
                        <div style="flex:1">
                            <div class="tp-fest-name">{{ $f['name'] }}</div>
                            @if($f['sigTrunc'])<div class="tp-fest-desc">{{ $f['sigTrunc'] }}{{ $f['sigMore'] ? '…' : '' }}</div>@endif
                            @if($f['mantra'])<div class="tp-fest-mantra" style="color:{{ $cat['accent'] }}">{{ $f['mantra'] }}</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ══ 4. NAVAGRAHA POSITIONS ══ --}}
<div class="ta ta3">
    <div class="tp-div">
        <div class="tp-div-line"></div>
        <span class="tp-div-hi">Navagraha Positions</span>
        <span class="tp-div-en">Current Position of Nine Planets</span>
        <div class="tp-div-line"></div>
    </div>

    <div class="tp-planet-grid">
        @foreach($d['planetDisplay'] as $pd)
        <div class="tp-pc"
             style="background:{{ $pd['bg'] }};border-color:{{ $pd['bd'] }};border-top-color:{{ $pd['clr'] }}">
            <div class="tp-pc-hi" style="color:{{ $pd['clr'] }}">{{ $pd['label'] }}</div>
            <div class="tp-pc-sign">{{ $pd['sign'] ?? '—' }}</div>
            <div class="tp-pc-nak">
                {{ $pd['nak'] ?? '—' }}<br>
                Pada {{ (int)($pd['pada'] ?? 0) }} · {{ number_format((float)($pd['deg'] ?? 0), 1) }}°
            </div>
            @if(!empty($pd['retro']))
            <div class="tp-retro" style="color:{{ $pd['clr'] }};border-color:{{ $pd['clr'] }}">Retrograde</div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ══ 5. UPCOMING SIGN CHANGES ══ --}}
@if(!empty($d['upcomingPlanetsDisplay']))
<div class="ta ta4">
    <div class="tp-div">
        <div class="tp-div-line"></div>
        <span class="tp-div-hi">Rashi Changes</span>
        <span class="tp-div-en">Upcoming Planetary Sign Changes</span>
        <div class="tp-div-line"></div>
    </div>
    <div class="tp-card">
        @foreach($d['upcomingPlanetsDisplay'] as $ch)
        <div class="tp-sc-row">
            <div style="min-width:90px">
                <div class="tp-sc-hi" style="color:{{ $ch['clr'] }}">{{ $ch['pmLabel'] }}</div>
            </div>
            <div style="flex:1;display:flex;align-items:center;gap:10px">
                <span class="tp-sc-hi" style="color:var(--ink3)">{{ $ch['fromSign'] ?? '—' }}</span>
                <span class="tp-sc-arrow">&rarr;</span>
                <span class="tp-sc-to" style="color:{{ $ch['clr'] }}">{{ $ch['toSign'] ?? '—' }}</span>
            </div>
            <div style="text-align:right">
                <div class="tp-sc-date">{{ $ch['dateDisplay'] }}</div>
                <div class="tp-sc-days">{{ $ch['daysLabel'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ 6. FESTIVAL CALENDAR ══ --}}
<div class="ta ta5">
    <div class="tp-div">
        <div class="tp-div-line"></div>
        <span class="tp-div-hi">Festival Calendar</span>
        <span class="tp-div-en">Past &amp; Upcoming 15 Days</span>
        <div class="tp-div-line"></div>
    </div>

    <div class="tp-fest-cal-grid">

        {{-- Past 15 --}}
        <div>
            <div class="tp-cal-head">
                Past Festivals
                <span style="font-size:.8rem;color:var(--ink4);font-weight:400"> · Last 15 Days</span>
            </div>
            @forelse($d['pastFestsDisplay'] as $f)
            <div class="tp-cal-row" style="opacity:.72">
                <div class="tp-fest-dot" style="background:{{ $f['dotColor'] }};flex-shrink:0"></div>
                <div class="tp-cal-name">{{ $f['name'] ?? '—' }}</div>
                <div class="tp-cal-date">{{ $f['dateDisplay'] }}</div>
            </div>
            @empty
            <div style="font-style:italic;color:var(--ink4);font-size:1.05rem">No past festivals</div>
            @endforelse
        </div>

        {{-- Upcoming 15 --}}
        <div>
            <div class="tp-cal-head" style="border-bottom-color:var(--terra);color:var(--terra)">
                Upcoming Festivals
                <span style="font-size:.8rem;font-weight:400"> · Next 15 Days</span>
            </div>
            @forelse($d['upcomingFestsDisplay'] as $f)
            <div class="tp-cal-row">
                <div class="tp-fest-dot" style="background:{{ $f['dotColor'] }};flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="tp-cal-name">{{ $f['name'] ?? '—' }}</div>
                    @if($f['sigTrunc'])
                    <div style="font-size:.9rem;color:var(--ink3);line-height:1.5">{{ $f['sigTrunc'] }}{{ $f['sigMore'] ? '…' : '' }}</div>
                    @endif
                </div>
                <div style="text-align:right;flex-shrink:0;padding-left:8px">
                    <div class="tp-cal-date">{{ $f['dateDisplay'] }}</div>
                    <div class="tp-cal-days">{{ $f['daysLabel'] }}</div>
                </div>
            </div>
            @empty
            <div style="font-style:italic;color:var(--ink4);font-size:1.05rem">No upcoming festivals</div>
            @endforelse
        </div>

    </div>
</div>

{{-- ══ 7. FOOTER ══ --}}
<div class="tp-footer">
    <div class="tp-fi">
        <div class="tp-fi-lbl">Moon Rashi</div>
        <div class="tp-fi-hi">{{ $d['moonSignKey'] }}</div>
        <div class="tp-fi-sub">{{ $d['moonNakDisplay'] }} · {{ $d['moonPaksha'] }} Tithi {{ $d['tithiNumD'] }}</div>
    </div>
    <div class="tp-fi-div"></div>
    <div class="tp-fi">
        <div class="tp-fi-lbl">Lagna</div>
        <div class="tp-fi-hi">{{ $d['lagnaSign'] }}</div>
        <div class="tp-fi-sub">Lahiri Ayanamsa · Sidereal</div>
    </div>
    <div class="tp-fi-div"></div>
    <div class="tp-fi">
        <div class="tp-fi-lbl">Dasha</div>
        <div class="tp-fi-hi" style="font-size:1.2rem">{{ $d['dashaSum'] }}</div>
    </div>
</div>

</div>{{-- /.tp --}}
