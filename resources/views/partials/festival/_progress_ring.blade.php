{{-- Progress ring SVG partial
     Expects: $ring = ['pct','col','dasharray','dashoffset','val','label']
--}}
<div style="display:flex;flex-direction:column;align-items:center;gap:7px;flex:1;min-width:96px">
    <svg width="88" height="88" viewBox="0 0 84 84">
        <circle cx="42" cy="42" r="34" fill="none" stroke="rgba(120,90,30,.12)" stroke-width="7"/>
        <circle cx="42" cy="42" r="34" fill="none" stroke="{{ $ring['col'] }}" stroke-width="7"
            stroke-linecap="round" stroke-dasharray="{{ $ring['dasharray'] }}"
            stroke-dashoffset="{{ $ring['dashoffset'] }}" transform="rotate(-90 42 42)"/>
        <text x="42" y="40" text-anchor="middle" font-family="Playfair Display,serif"
            font-size="17" font-weight="700" fill="{{ $ring['col'] }}">{{ $ring['pct'] }}%</text>
        <text x="42" y="56" text-anchor="middle" font-size="8.5" fill="#9a8a6a">{{ $ring['val'] }}</text>
    </svg>
    <div style="font-family:'Tiro Devanagari Sanskrit',serif;font-size:1.05rem;color:#5a4a30;font-weight:600">{{ $ring['label'] }}</div>
</div>
