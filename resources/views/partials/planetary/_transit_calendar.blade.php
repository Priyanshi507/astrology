{{-- Transit calendar (sign-change events) — AJAX partial (inline styles only)
     Expects: $data = TransitCalculator::prepareCalendarView($events, $title, $utcOff, $groupByMonth)
--}}
<div style="font-family:'DM Sans',sans-serif;color:var(--text)">

    <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1.6px;
                font-weight:800;color:var(--gold);margin-bottom:6px">
        ◈ {{ $data['title'] }}
    </div>
    <div style="font-size:.92rem;color:var(--text-mid);line-height:1.65;margin-bottom:20px">
        <strong style="color:var(--text);font-size:1rem">{{ $data['count'] }}</strong>
        planetary sign-changes (Sankranti / ingress) in this period.
        Each row marks when a graha leaves one rashi and enters the next.
    </div>

    @if(empty($data['rows']))
        <div style="font-style:italic;color:var(--text-lt);padding:0 16px;font-size:.92rem">
            No sign-changes in this period.
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($data['rows'] as $e)

            @if($e['monthHeading'])
            <div style="font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;
                        color:var(--sky);margin:18px 0 4px">
                {{ $e['monthHeading'] }}
            </div>
            @endif

            <div style="display:flex;align-items:center;gap:16px;
                        background:var(--card);border:1px solid var(--sky-pale);
                        border-left:5px solid {{ $e['col'] }};border-radius:12px;
                        padding:12px 16px">
                <span style="font-size:1.7rem;color:{{ $e['col'] }};
                             flex-shrink:0;width:32px;text-align:center;line-height:1">{{ $e['sym'] }}</span>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:700;color:var(--text);font-size:1rem">
                        {{ $e['en'] }}
                        @if($e['retro'])
                            <span style="font-weight:800;color:#b83020;font-size:.83rem"> ℞</span>
                        @endif
                        <span style="font-weight:600;color:var(--text-mid)"> enters {{ $e['to'] }}</span>
                    </div>
                    <div style="font-size:.84rem;color:var(--text-lt);margin-top:2px">
                        leaves {{ $e['from'] }} → {{ $e['to'] }}
                    </div>
                </div>
                <div style="flex-shrink:0;text-align:right;font-family:'DM Mono',monospace;
                            font-size:.92rem;font-weight:600;color:var(--text-mid);white-space:nowrap">
                    {{ $e['when'] }}
                </div>
            </div>

            @endforeach
        </div>
    @endif
</div>
