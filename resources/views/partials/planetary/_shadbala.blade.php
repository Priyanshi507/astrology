{{-- Shadbala (six-fold strength) panel — AJAX partial
     Expects: $data = ShadBalaCalculator::prepareForView($shadbala)
--}}
<div style="font-family:'DM Sans',sans-serif">

    {{-- SVG bar chart --}}
    <div class="rounded-2xl mb-5 overflow-x-auto"
         style="background:#fff;border:1.5px solid #e0e8f0;box-shadow:0 2px 12px rgba(13,40,70,.07);padding:14px 10px 10px">
        <div class="flex items-center gap-4 flex-wrap mb-3 px-1">
            <span class="font-extrabold uppercase" style="font-size:.7rem;color:#3a5a78;letter-spacing:.8px">Shadbala — Rupas</span>
            <span class="flex items-center gap-1" style="font-size:.7rem;color:#1e7a3e">
                <span class="inline-block rounded" style="width:11px;height:11px;background:#c8eecb;border:1px solid #1e7a3e"></span>
                Strong zone
            </span>
            <span class="flex items-center gap-1" style="font-size:.7rem;color:#c03030">
                <span class="inline-block rounded" style="width:11px;height:11px;background:#f5c6c6;border:1px solid #c03030"></span>
                Below minimum
            </span>
            <span class="ml-auto" style="font-size:.65rem;color:#999">Dashed line = planet's minimum Rupas</span>
        </div>
        {!! $data['svg'] !!}
    </div>

    {{-- Detail table --}}
    <div class="rounded-2xl overflow-hidden" style="border:1.5px solid #e0e8f0;box-shadow:0 2px 12px rgba(13,40,70,.07)">
        <table class="w-full" style="border-collapse:collapse;font-size:.78rem">
            <thead>
                <tr style="background:linear-gradient(120deg,#f0f4f8,#e4ecf4)">
                    @foreach(['Planet','Sthana','Dig','Kaala','Chesta','Naisargika','Drig','Total','Rupas','Grade'] as $col)
                    <th class="text-center font-extrabold uppercase whitespace-nowrap"
                        style="padding:10px 8px;color:#3a5a78;font-size:.63rem;letter-spacing:.8px;border-bottom:2px solid #d0dce8">
                        {{ $col }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $r)
                <tr style="background:{{ $r['isAlt'] ? '#f8fafb' : '#ffffff' }};border-bottom:1px solid #eaeff4">
                    <td class="text-center" style="padding:10px 8px">
                        <div class="inline-flex items-center justify-center rounded-lg mb-0.5"
                             style="width:30px;height:30px;background:{{ $r['style']['bg'] }};border:1.5px solid {{ $r['style']['border'] }}">
                            <span class="font-black" style="color:{{ $r['style']['accent'] }};font-size:.88rem">{{ $r['style']['abbr'] }}</span>
                        </div>
                        <div style="color:{{ $r['style']['text'] }};font-size:.6rem;text-transform:capitalize;opacity:.8">{{ $r['pid'] }}</div>
                    </td>
                    <td class="text-center" title="{{ $r['sthanaTip'] }}" style="padding:10px 6px">
                        <strong style="color:#2d4a62">{{ $r['sthanaBala']['total'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:#2d4a62">{{ $r['digBala'] }}</strong>
                    </td>
                    <td class="text-center" title="{{ $r['kaalaTip'] }}" style="padding:10px 6px">
                        <strong style="color:#2d4a62">{{ $r['kaalaBala']['total'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:#2d4a62">{{ $r['chestaBala'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:#2d4a62">{{ $r['naisargikaBala'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:{{ $r['drigPositive'] ? '#2e7a4e' : '#c04040' }}">{{ $r['drigBala'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:{{ $r['style']['accent'] }};font-size:.92rem">{{ $r['total'] }}</strong>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <strong style="color:{{ $r['isStrong'] ? $r['style']['accent'] : '#d04040' }};font-size:.88rem">{{ $r['rupas'] }}</strong>
                        <div style="color:#9aabbf;font-size:.6rem">/ {{ $r['minRupas'] }}</div>
                    </td>
                    <td class="text-center" style="padding:10px 6px">
                        <span class="font-bold whitespace-nowrap"
                              style="background:{{ $r['gradeBg'] }};color:{{ $r['gradeColor'] }};border:1px solid {{ $r['gradeColor'] }}40;border-radius:20px;padding:3px 10px;font-size:.65rem">
                            {{ $r['grade'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="rounded-xl mt-3" style="font-size:.72rem;color:#5a7a93;line-height:1.9;background:#f4f8fc;padding:10px 16px;border:1px solid #dde8f0">
        <strong style="color:#2d4a62">Shadbala Components:</strong>
        Sthana = Positional · Dig = Directional · Kaala = Temporal ·
        Chesta = Motional · Naisargika = Natural · Drig = Aspectual.
        All values in <em>Shashtiamshas (Virupas)</em>.
        Divide by 60 to get <em>Rupas</em>.
        Hover Sthana / Kaala columns for sub-component breakdown.
    </div>

</div>
