{{-- Planet positions summary list
     Expects: $data = AstroChartRenderer::buildPlanetSummaryData(...)
     Keys: items (array of {abbr, color, retro, sign, house, deg})
--}}
<div style="font-size:.8rem;font-weight:700;color:#6b4c1a;margin:8px 0 6px">Planet Positions</div>
@foreach($data['items'] as $p)
<div style="font-size:.75rem;margin-bottom:3px">
    <span style="color:{{ $p['color'] }};font-weight:700">{{ $p['abbr'] }}{{ $p['retro'] ? 'R' : '' }}</span>
    <span style="color:#4a3520">{{ $p['sign'] }} H{{ $p['house'] }}</span>
    <span style="color:#8a7050">{{ $p['deg'] }}</span>
</div>
@endforeach
