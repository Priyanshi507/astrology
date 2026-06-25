{{-- Dasha planet details tab panel
     Expects: $detailData = VimshottariDashaCalculator::prepareDetailsData($lord, $style)
--}}
@if(!$detailData['empty'])
<div style="padding:14px 18px 16px;background:#fafbfc;border-top:1px solid {{ $detailData['style']['border'] }}">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 20px;margin-bottom:12px">
        @foreach($detailData['chips'] as $chip)
        <div style="display:flex;flex-direction:column;gap:2px">
            <span style="font-size:.58rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;color:{{ $detailData['style']['accent'] }}">{{ $chip['label'] }}</span>
            <span style="font-size:.75rem;font-weight:600;color:#1a2535">{{ $chip['value'] }}</span>
        </div>
        @endforeach
    </div>
    <div style="background:{{ $detailData['style']['light'] }};border-radius:8px;padding:10px 12px;margin-bottom:10px;border:1px solid {{ $detailData['style']['border'] }}">
        <div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;color:{{ $detailData['style']['accent'] }};margin-bottom:5px">Significations (Karakatwa)</div>
        <div style="font-size:.75rem;font-weight:600;color:#1a2535;line-height:1.5">{{ $detailData['signif'] }}</div>
    </div>
    <div style="background:#fff;border-radius:8px;padding:10px 12px;border:1px solid {{ $detailData['style']['border'] }}">
        <div style="font-size:.6rem;text-transform:uppercase;letter-spacing:.9px;font-weight:800;color:{{ $detailData['style']['accent'] }};margin-bottom:5px">Dasha Themes &amp; Effects</div>
        <div style="font-size:.75rem;color:#283040;line-height:1.65">{{ $detailData['themes'] }}</div>
    </div>
</div>
@endif
