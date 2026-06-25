{{-- Single varga chart (AJAX expand)
     Expects: $data = VargaChartRenderer::prepareSingleVargaData(...)
     Keys: svg (string), size (int)
--}}
<div style="display:inline-block;background:#f5f0eb;border-radius:12px;padding:8px">
    <svg viewBox="0 0 {{ $data['size'] }} {{ $data['size'] }}" xmlns="http://www.w3.org/2000/svg"
         style="width:100%;max-width:{{ $data['size'] }}px;font-family:'DM Sans',sans-serif">
        {!! $data['svg'] !!}
    </svg>
</div>
