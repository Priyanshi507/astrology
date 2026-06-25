{{-- All varga charts grid + dignity matrix
     Expects:
       $chartSvg (string)  — from VargaChartRenderer::prepareVargaGridData()['chartSvg']
       $dm       (array)   — from VargaChartRenderer::prepareVargaGridData()['dignityMatrix']
--}}
<div style="overflow-x:auto;width:100%;background:#f5f0eb;border-radius:16px;padding:16px;box-sizing:border-box">

    {{-- Chart SVG grid --}}
    {!! $chartSvg !!}

    {{-- ══ Dignity Matrix ══ --}}
    <div style="margin-top:28px;overflow-x:auto">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:800;color:#7a5a30">&#9672; Varga Dignity Matrix</div>
            <div style="flex:1;height:1px;background:linear-gradient(90deg,#d0c0a8,transparent)"></div>
            <div style="font-size:.68rem;color:#a08060;font-style:italic">Planet positions across all divisional charts</div>
        </div>

        {{-- Legend --}}
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;padding:10px 14px;background:#faf6f0;border-radius:10px;border:1px solid #e8ddd0">
            <span style="font-size:.65rem;font-weight:800;color:#9a7a50;text-transform:uppercase;letter-spacing:1px;margin-right:4px;align-self:center">Dignity</span>
            @foreach($dm['legend'] as $leg)
            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;background:{{ $leg['bg'] }};color:{{ $leg['tc'] }};font-size:.68rem;font-weight:700;border:1px solid {{ $leg['tc'] }}22">{{ $leg['short'] === '—' ? 'Neutral' : $leg['dig'] }}</span>
            @endforeach
        </div>

        {{-- Planet summary cards --}}
        <div style="display:grid;grid-template-columns:repeat(9,1fr);gap:6px;margin-bottom:16px">
            @foreach($dm['planets'] as $pc)
            <div style="background:#fffdf9;border:1px solid #e8ddd0;border-radius:10px;padding:8px 6px;text-align:center">
                <div style="font-size:1.1rem;line-height:1;margin-bottom:3px">{{ $pc['sym'] }}</div>
                <div style="font-size:.6rem;font-weight:800;color:#7a5a30;text-transform:uppercase;letter-spacing:.5px">{{ $pc['label'] }}</div>
                <div style="margin:6px 0;height:5px;border-radius:4px;overflow:hidden;display:flex;background:#eee">
                    @if($pc['exlPct'])<div style="width:{{ $pc['exlPct'] }}%;background:#2a9a50;min-width:2px"></div>@endif
                    @if($pc['ownPct'])<div style="width:{{ $pc['ownPct'] }}%;background:#2a6aae;min-width:2px"></div>@endif
                    @if($pc['friPct'])<div style="width:{{ $pc['friPct'] }}%;background:#5a9a5a;min-width:2px"></div>@endif
                    @if($pc['neuPct'])<div style="width:{{ $pc['neuPct'] }}%;background:#c0b0a0;min-width:2px"></div>@endif
                    @if($pc['badPct'])<div style="width:{{ $pc['badPct'] }}%;background:#c85030;min-width:2px"></div>@endif
                </div>
                <div style="font-size:.6rem;color:#9a8060">{{ $pc['goodPct'] }}%<span style="color:#2a9a50">&#8593;</span> {{ $pc['badPct'] }}%<span style="color:#c85030">&#8595;</span></div>
            </div>
            @endforeach
        </div>

        {{-- Main matrix table --}}
        <div style="overflow-x:auto;border-radius:12px;border:1.5px solid #ddd0c0;box-shadow:0 2px 12px rgba(120,80,20,.06)">
            <table style="width:100%;border-collapse:collapse;font-family:'DM Mono',monospace;font-size:.73rem;min-width:780px">
                <thead>
                    <tr style="background:#e8ddd0">
                        <th style="padding:10px 14px;text-align:left;font-size:.65rem;text-transform:uppercase;letter-spacing:1px;font-weight:800;color:#7a5a30;white-space:nowrap;min-width:110px">Varga</th>
                        @foreach($dm['planetHeaders'] as $ph)
                        <th style="padding:8px 6px;text-align:center;font-size:.65rem;color:#5a3a10;font-weight:700;white-space:nowrap">
                            {{ $ph['sym'] }}<br><span style="font-size:.55rem;opacity:.7;font-family:'DM Sans',sans-serif">{{ $ph['label'] }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($dm['matrixRows'] as $row)
                    <tr style="border-bottom:1px solid #ede0cc;background:{{ $row['altBg'] }}">
                        <td style="padding:7px 14px;vertical-align:middle">
                            <div style="font-weight:800;color:#4a2800;font-size:.76rem;font-family:'DM Sans',sans-serif">D{{ $row['division'] }}</div>
                            <div style="font-size:.6rem;color:#9a7a50;font-family:'DM Sans',sans-serif;margin-top:1px">{{ $row['signif'] }}</div>
                        </td>
                        @foreach($row['cells'] as $cell)
                        <td style="padding:5px 4px;text-align:center;vertical-align:middle" title="{{ $cell['tooltip'] }}">
                            <div style="display:inline-flex;flex-direction:column;align-items:center;background:{{ $cell['bg'] }};border-radius:6px;padding:4px 6px;min-width:36px">
                                <span style="color:{{ $cell['tc'] }};font-weight:700;font-size:.7rem;line-height:1">{{ $cell['signShort'] }}{{ $cell['retro'] ? '℞' : '' }}</span>
                                <span style="color:{{ $cell['tc'] }};font-size:.58rem;opacity:.8;margin-top:1px">{{ $cell['digShort'] }}</span>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
