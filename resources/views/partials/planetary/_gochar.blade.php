{{-- Gochar Phala (Transit Result) — AJAX-rendered server-side
     All inline styles — no Tailwind classes (innerHTML injection safe)
--}}
<div style="font-family:'DM Sans',sans-serif;color:var(--text)">

{{-- ── Info banner ── --}}
<div style="background:var(--sky-wash);border:1.5px solid var(--sky-pale);border-left:5px solid var(--sky);border-radius:14px;padding:18px 22px;margin-bottom:20px">
    <div style="font-size:1.05rem;font-weight:800;color:var(--sky);margin-bottom:8px">◈ Transit Results (BPHS — Gochar Phala)</div>
    <div style="font-size:.9rem;color:var(--text-mid);line-height:1.75">
        Transit (Gochar) effects are judged by counting the house of each transiting planet
        <strong style="color:var(--text)">from your Janma Rashi (natal Moon sign)</strong>.
        Houses that are auspicious per Brihat Parashara Hora Shastra are shown in green.
    </div>
    <div style="margin-top:10px;font-size:.95rem;color:var(--text)">
        Janma Rashi:
        <strong style="color:var(--sky);font-size:1.05rem">{{ $data['natalName'] }}</strong>
    </div>
</div>

{{-- ── Rashi selector pills ── --}}
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
    @foreach($data['rashis'] as $r)
    <button
        class="gobtn"
        id="gobtn{{ $r['idx'] }}"
        onclick="gocharSwitch({{ $r['idx'] }})"
        style="background:{{ $r['isNatal'] ? 'var(--sky)' : 'var(--card)' }};
               color:{{ $r['isNatal'] ? '#fff' : 'var(--text-mid)' }};
               border:1.5px solid {{ $r['isNatal'] ? 'var(--sky)' : 'var(--sky-pale)' }};
               border-radius:20px;padding:7px 16px;cursor:pointer;
               font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:700;
               transition:all .15s">
        {{ $r['en'] }}@if($r['isNatal']) ★@endif
    </button>
    @endforeach
</div>

{{-- ── Per-rashi panels ── --}}
@foreach($data['rashis'] as $r)
<div class="gochar-detail" id="gochar-rashi-{{ $r['idx'] }}" style="display:{{ $r['visible'] ? 'block' : 'none' }}">

    {{-- Rashi header card --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;
                background:var(--sky-wash);border:1.5px solid var(--sky-pale);
                border-radius:16px;padding:20px 24px;margin-bottom:16px">
        <div style="flex:1;min-width:160px">
            <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;
                        color:var(--sky);line-height:1.2">{{ $r['en'] }}</div>
            <div style="font-size:.88rem;color:var(--text-mid);margin-top:4px">
                Lord: <strong style="color:var(--text)">{{ $r['lord'] }}</strong>
            </div>
        </div>
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.2px;
                        font-weight:700;color:var(--text-lt);margin-bottom:6px">Overall Transit</div>
            <div style="display:inline-block;border-radius:20px;padding:5px 18px;font-weight:800;
                        background:{{ $r['vCol'] }}1a;color:{{ $r['vCol'] }};
                        border:2px solid {{ $r['vCol'] }}55;font-size:.95rem">
                {{ $r['vLabel'] }}
            </div>
            <div style="margin-top:8px;font-size:.88rem;color:var(--text-lt)">
                <strong style="font-size:1rem;color:{{ $r['vCol'] }}">{{ $r['good'] }}</strong> / 9 auspicious
            </div>
        </div>
    </div>

    {{-- Sade Sati / Dhaiya banner --}}
    @if($r['sade'])
    <div style="background:#fbeae6;border-left:5px solid #b83020;border-radius:12px;
                padding:14px 18px;margin-bottom:16px">
        <div style="font-weight:800;color:#8a1810;font-size:.95rem;margin-bottom:4px">♄ Saturn Sade Sati</div>
        <div style="color:#6a3020;font-size:.88rem;line-height:1.7">
            {{ $r['sadePhase'] }}. Saturn is in the {{ $r['satHouseOrdinal'] }} house from your Moon.
            This period calls for patience, discipline and steady effort.
        </div>
    </div>
    @elseif($r['dhaiya'])
    <div style="background:#fdf3e0;border-left:5px solid #c48a2f;border-radius:12px;
                padding:14px 18px;margin-bottom:16px">
        <div style="font-weight:800;color:#8a5a10;font-size:.95rem;margin-bottom:4px">♄ Saturn Dhaiya</div>
        <div style="color:#6a4a10;font-size:.88rem;line-height:1.7">
            {{ $r['dhaiyaType'] }}. A 2.5-year Saturn phase requiring extra care and vigilance.
        </div>
    </div>
    @endif

    {{-- Life-area outlook --}}
    <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.6px;
                font-weight:800;color:var(--gold);margin-bottom:12px">◈ Life-Area Outlook</div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
                gap:12px;margin-bottom:24px">
        @foreach($r['cats'] as $c)
        <div style="background:var(--card);border:1.5px solid var(--sky-pale);
                    border-top:4px solid {{ $c['ratingColor'] }};border-radius:14px;padding:16px 18px">
            <div style="display:flex;align-items:center;justify-content:space-between;
                        gap:10px;margin-bottom:10px">
                <div style="font-weight:800;color:var(--text);font-size:.95rem">{{ $c['en'] }}</div>
                <span style="display:inline-block;border-radius:20px;padding:3px 12px;font-weight:700;
                             white-space:nowrap;flex-shrink:0;
                             background:{{ $c['ratingColor'] }}18;color:{{ $c['ratingColor'] }};
                             border:1.5px solid {{ $c['ratingColor'] }}44;font-size:.75rem">
                    {{ $c['rating'] }}
                </span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">
                @if(empty($c['members']))
                    <span style="color:var(--text-lt);font-size:.8rem;font-style:italic">— stable —</span>
                @else
                    @foreach($c['members'] as $m)
                    <span style="display:inline-flex;align-items:center;gap:4px;
                                 background:{{ $m['mc'] }}14;color:{{ $m['mc'] }};
                                 border:1px solid {{ $m['mc'] }}44;border-radius:12px;
                                 padding:3px 10px;font-size:.78rem;font-weight:700">
                        <span style="color:{{ $m['col'] }}">{{ $m['sym'] }}</span>
                        {{ $m['en'] }} · {{ $m['ordinalText'] }}
                    </span>
                    @endforeach
                @endif
            </div>
            <div style="font-size:.84rem;color:var(--text-mid);line-height:1.65">{{ $c['noteEn'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Planet-by-planet transit --}}
    <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.6px;
                font-weight:800;color:var(--gold);margin-bottom:12px">◈ Planet-by-Planet Transit</div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px">
        @foreach($r['rows'] as $row)
        <div style="background:var(--card);border:1.5px solid {{ $row['auspColor'] }}28;
                    border-left:5px solid {{ $row['col'] }};border-radius:14px;padding:16px 18px">

            {{-- Planet header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
                <span style="font-size:1.5rem;line-height:1;color:{{ $row['col'] }};flex-shrink:0">{{ $row['sym'] }}</span>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:800;color:var(--text);font-size:.95rem;
                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $row['en'] }}
                        @if($row['retro'])
                            <span style="color:#b83020;font-size:.85rem;font-weight:700"> ℞</span>
                        @endif
                    </div>
                    <div style="font-size:.82rem;color:var(--text-mid);margin-top:2px">
                        {{ $row['signName'] }} ·
                        <strong style="color:var(--text)">{{ $row['ordinalText'] }} house</strong>
                    </div>
                </div>
                <span style="display:inline-block;border-radius:20px;padding:3px 10px;
                             font-weight:700;white-space:nowrap;flex-shrink:0;
                             background:{{ $row['auspBg'] }};color:{{ $row['auspColor'] }};
                             border:1px solid {{ $row['auspColor'] }}40;font-size:.72rem">
                    {{ $row['auspLabel'] }}
                </span>
            </div>

            {{-- Effect text --}}
            <div style="font-size:.85rem;color:var(--text-mid);line-height:1.65;
                        border-top:1px solid var(--sky-pale);padding-top:10px">
                {{ $row['phalEn'] }}
            </div>
        </div>
        @endforeach
    </div>

</div>{{-- /gochar-detail --}}
@endforeach

</div>
