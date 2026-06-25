{{-- Chart detail panel: Houses list + Planet positions grid
     Expects: $panel = AstroChartRenderer::buildDetailPanelData(...)
     Keys: houses (array), planetPositions (array)
--}}
<div style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7a5010;border-bottom:1px solid #d0b880;padding-bottom:5px;margin-bottom:8px">Houses &middot; Signs</div>

@foreach($panel['houses'] as $house)
<div style="display:flex;align-items:center;gap:6px;padding:4px 2px;border-bottom:1px solid rgba(180,150,80,.14);background:{{ $house['isKendra'] ? 'rgba(180,150,60,.09)' : 'transparent' }}">
    <span style="font-size:11px;font-weight:700;color:#7a5010;background:rgba(180,140,40,.18);border-radius:3px;padding:1px 7px;min-width:24px;text-align:center;flex-shrink:0">{{ $house['h'] }}</span>
    <span style="font-size:12.5px;font-weight:600;color:#3a2800;flex:1;min-width:0">{{ $house['signFull'] }}<span style="opacity:.45;font-weight:400;font-size:10.5px"> &middot; {{ $house['signLord'] }}</span></span>
    @foreach($house['planets'] as $p)
    <span style="font-size:12.5px;font-weight:700;color:{{ $p['color'] }};flex-shrink:0;margin-left:2px">{{ $p['abbr'] }}@if($p['retro'])<sup style="font-size:7px;font-style:italic">R</sup>@endif</span>
    @endforeach
</div>
@endforeach

<div style="margin-top:16px">
    <div style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7a5010;border-bottom:1px solid #d0b880;padding-bottom:5px;margin-bottom:8px">Planet Positions</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px 10px">
        @foreach($panel['planetPositions'] as $p)
        <div style="display:flex;align-items:center;gap:5px;padding:2px 0;border-bottom:1px solid rgba(180,150,80,.12)">
            <span style="color:{{ $p['color'] }};font-size:13px;font-weight:700;min-width:26px;flex-shrink:0">{{ $p['abbr'] }}@if($p['retro'])<sup style="font-size:7px;font-style:italic">R</sup>@endif</span>
            <span style="color:#5a3800;font-size:12px;font-weight:600">{{ $p['signShort'] }} H{{ $p['house'] }}</span>
            <span style="color:#9a7840;font-size:10.5px;margin-left:auto">{{ $p['degDisplay'] }}</span>
        </div>
        @endforeach
    </div>
</div>
