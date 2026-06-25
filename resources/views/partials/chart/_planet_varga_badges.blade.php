{{-- Planet varga dignity badges
     Expects: $data = VargaChartRenderer::buildPlanetVargaSummaryData(...)
     Keys: badges (array of {d, signShort, digShort, bg, tc})
--}}
<div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:8px">
    @foreach($data['badges'] as $badge)
    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:16px;background:{{ $badge['bg'] }};border:1px solid {{ $badge['tc'] }}44;font-size:.72rem;font-weight:700;color:{{ $badge['tc'] }};white-space:nowrap">
        D{{ $badge['d'] }} <span style="opacity:.7">{{ $badge['signShort'] }}</span> {{ $badge['digShort'] }}
    </span>
    @endforeach
</div>
