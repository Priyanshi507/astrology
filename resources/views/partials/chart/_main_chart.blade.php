{{-- Main birth chart panel
     Expects: $data = AstroChartRenderer::prepareForView(...)
     Keys: d1Svg, d9Svg, d10Svg, panel (array), dasha (array|null)
--}}
<div style="font-family:Georgia,'Times New Roman',serif;background:#ede8dc;padding:20px;border-radius:8px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:stretch">

        <div>
            <div style="font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7a5010;margin-bottom:8px">D1 &mdash; R&#x101;shi &middot; Birth Chart</div>
            <div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:8px;box-shadow:0 2px 10px rgba(0,0,0,.09)">{!! $data['d1Svg'] !!}</div>
        </div>

        <div style="display:flex;flex-direction:column">
            <div style="font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7a5010;margin-bottom:8px">Houses &middot; Planets</div>
            <div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:14px 16px;flex:1">
                @include('partials.chart._detail_panel', ['panel' => $data['panel']])
            </div>
        </div>

    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
        <div>
            <div style="font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7a5010;margin-bottom:8px">D9 &mdash; Navamsha</div>
            <div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:6px;box-shadow:0 2px 10px rgba(0,0,0,.09)">{!! $data['d9Svg'] !!}</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7a5010;margin-bottom:8px">D10 &mdash; Dashamsha</div>
            <div style="background:#fff9f0;border:1.5px solid #c0a878;border-radius:6px;padding:6px;box-shadow:0 2px 10px rgba(0,0,0,.09)">{!! $data['d10Svg'] !!}</div>
        </div>
    </div>

    @if($data['dasha'])
    <div style="margin-top:14px;background:#fdf5e0;border-radius:6px;padding:12px 20px;border-left:4px solid #b8900a;color:#5a3e00;font-size:.84rem;line-height:1.85">
        <strong style="color:#7a4f00;letter-spacing:.06em">&#9672; VIMSHOTTARI DASHA BALANCE AT BIRTH</strong><br>
        <strong style="color:#3a2000;font-size:1.02rem">{{ $data['dasha']['lord'] }} Mahadasha</strong>
        &mdash; {{ $data['dasha']['yrs'] }}y &nbsp;{{ $data['dasha']['mos'] }}m &nbsp;{{ $data['dasha']['days'] }}d remaining
        <span style="opacity:.45;font-size:.76rem">(total {{ $data['dasha']['lordYrs'] }} yrs)</span>
    </div>
    @endif
</div>
