{{-- Transit detail cards — AJAX partial (inline styles only, no Tailwind)
     Expects: $data = TransitCalculator::prepareDetailView($details, $dateLabel)
--}}
<div style="font-family:'DM Sans',sans-serif;color:var(--text)">

    <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1.6px;
                font-weight:800;color:var(--gold);margin-bottom:16px">
        ◈ Planetary Transits · {{ $data['dateLabel'] }}
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:28px">
        @foreach($data['cards'] as $d)
        <div style="background:var(--card);border:1.5px solid var(--sky-pale);
                    border-left:5px solid {{ $d['col'] }};border-radius:14px;padding:16px 18px">

            {{-- Planet header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <span style="font-size:1.8rem;line-height:1;color:{{ $d['col'] }};
                             flex-shrink:0;width:36px;text-align:center">{{ $d['sym'] }}</span>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-weight:800;color:var(--text);font-size:1rem">{{ $d['en'] }}</span>
                        @if($d['retro'])
                            <span style="font-weight:800;color:#b83020;font-size:.88rem">℞</span>
                        @endif
                    </div>
                    <div style="font-size:.83rem;color:var(--text-mid);margin-top:2px">
                        {{ $d['hi'] }} · in
                        <strong style="color:var(--text)">{{ $d['sign'] }}</strong>
                        <span style="font-family:'DM Mono',monospace;font-size:.78rem;margin-left:4px">{{ $d['deg'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <div style="border-top:1px solid var(--sky-pale);padding-top:12px;
                        display:grid;grid-template-columns:1fr 1fr;gap:10px 14px">
                <div>
                    <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;
                                font-weight:700;color:var(--text-lt);margin-bottom:3px">Entered</div>
                    <div style="font-weight:600;color:var(--text);font-size:.86rem;line-height:1.35">{{ $d['entry'] }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;
                                font-weight:700;color:var(--text-lt);margin-bottom:3px">Exits</div>
                    <div style="font-weight:600;color:var(--text);font-size:.86rem;line-height:1.35">{{ $d['exit'] }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;
                                font-weight:700;color:var(--text-lt);margin-bottom:3px">Remaining</div>
                    <div style="font-weight:700;color:#2e7a6e;font-size:.92rem">{{ $d['remLabel'] }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;
                                font-weight:700;color:var(--text-lt);margin-bottom:3px">Next sign</div>
                    <div style="font-weight:700;color:{{ $d['col'] }};font-size:.92rem">{{ $d['nextSign'] }}</div>
                </div>
            </div>

        </div>
        @endforeach
    </div>
</div>
