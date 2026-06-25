{{-- Transit aspects (Graha Drishti) — AJAX partial (inline styles only)
     Expects: $data = TransitCalculator::prepareAspectsView($aspects)
--}}
<div style="font-family:'DM Sans',sans-serif;color:var(--text);margin-top:8px">

    <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1.6px;
                font-weight:800;color:var(--gold);margin-bottom:6px">
        ◈ Transit Aspects on Your Natal Planets (Graha Drishti)
    </div>
    <div style="font-size:.9rem;color:var(--text-mid);line-height:1.65;margin-bottom:16px">
        How each transiting planet currently influences your birth planets through conjunction and Vedic aspects.
    </div>

    @if(empty($data['aspects']))
        <div style="font-style:italic;color:var(--text-lt);padding:0 16px;font-size:.92rem">
            No major transit aspects on natal planets at this date.
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($data['aspects'] as $a)
            <div style="display:flex;align-items:center;gap:16px;
                        background:{{ $a['bgColor'] }};border:1px solid {{ $a['borderColor'] }};
                        border-radius:12px;padding:12px 16px">
                <span style="flex-shrink:0;white-space:nowrap;font-size:1.35rem;color:{{ $a['tCol'] }}">
                    {{ $a['tSym'] }}<span style="color:var(--text-lt);font-size:.95rem">→</span>{{ $a['nSym'] }}
                </span>
                <div style="font-size:.95rem;color:var(--text);line-height:1.55">{{ $a['text'] }}</div>
            </div>
            @endforeach
        </div>
    @endif

</div>
