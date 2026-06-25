{{-- House-signs-planets sidebar list
     Expects: $data = AstroChartRenderer::buildHouseSignsData(...)
     Keys: houses (array of {h, signFull, planets[]})
--}}
<div style="font-size:.8rem;font-weight:700;color:#6b4c1a;margin-bottom:8px">Houses &middot; Signs &middot; Planets</div>
@foreach($data['houses'] as $house)
<div style="font-size:.75rem;padding:3px 0;border-bottom:1px solid rgba(160,130,80,.15)">
    <strong style="color:#3a2a00">{{ $house['h'] }}</strong>
    <span style="color:#5a3a00">{{ $house['signFull'] }}</span>
    @foreach($house['planets'] as $p)
    <span style="color:{{ $p['color'] }};font-weight:700">{{ $p['abbr'] }}</span>
    @endforeach
</div>
@endforeach
