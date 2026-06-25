{{-- Vimshottari Dasha panel — AJAX partial
     Expects: $data = VimshottariDashaCalculator::prepareForView($dashaData)
--}}

{{-- 1. Birth balance card --}}
<div class="rounded-2xl mb-4"
     style="background:{{ $data['birthCard']['style']['bg'] }};border:1.5px solid {{ $data['birthCard']['style']['border'] }};border-left:4px solid {{ $data['birthCard']['style']['accent'] }};padding:18px 22px">
    <div class="uppercase font-extrabold mb-3" style="font-size:.65rem;letter-spacing:1.5px;color:{{ $data['birthCard']['style']['accent'] }}">
        ◈ Vimshottari Dasha — 120-Year Cycle
    </div>
    <div class="grid gap-3" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
        <div class="rounded-xl" style="background:#fff;border:1px solid {{ $data['birthCard']['style']['border'] }};padding:10px 14px">
            <div class="uppercase font-bold mb-1" style="font-size:.6rem;letter-spacing:1px;color:{{ $data['birthCard']['style']['accent'] }}">Moon Nakshatra</div>
            <div class="font-extrabold" style="font-size:.9rem;color:{{ $data['birthCard']['style']['text'] }}">{{ $data['birthCard']['nakSym'] }} {{ $data['birthCard']['nakName'] }}</div>
        </div>
        <div class="rounded-xl" style="background:#fff;border:1px solid {{ $data['birthCard']['style']['border'] }};padding:10px 14px">
            <div class="uppercase font-bold mb-1" style="font-size:.6rem;letter-spacing:1px;color:{{ $data['birthCard']['style']['accent'] }}">Nakshatra elapsed</div>
            <div class="font-extrabold" style="font-size:.9rem;color:{{ $data['birthCard']['style']['text'] }}">{{ $data['birthCard']['nakProg'] }}%</div>
        </div>
        <div class="rounded-xl" style="background:#fff;border:1px solid {{ $data['birthCard']['style']['border'] }};padding:10px 14px">
            <div class="uppercase font-bold mb-1" style="font-size:.6rem;letter-spacing:1px;color:{{ $data['birthCard']['style']['accent'] }}">Birth Dasha lord</div>
            <div class="font-extrabold" style="font-size:.9rem;color:{{ $data['birthCard']['style']['text'] }}">{{ strtoupper($data['birthCard']['birthLord']) }} ({{ $data['birthCard']['birthYears'] }} yrs)</div>
        </div>
        <div class="rounded-xl" style="background:#fff;border:1px solid {{ $data['birthCard']['style']['border'] }};padding:10px 14px">
            <div class="uppercase font-bold mb-1" style="font-size:.6rem;letter-spacing:1px;color:{{ $data['birthCard']['style']['accent'] }}">Balance at birth</div>
            <div class="font-extrabold" style="font-size:.9rem;color:{{ $data['birthCard']['style']['text'] }}">{{ $data['birthCard']['balance'] }}</div>
        </div>
    </div>
</div>

{{-- 2. Currently active dasha summary --}}
@if($data['currentCard'])
<div class="rounded-2xl mb-5"
     style="background:{{ $data['cur rentCard']['mahaStyle']['bg'] }};border:1.5px solid {{ $data['currentCard']['mahaStyle']['border'] }};border-left:4px solid {{ $data['currentCard']['mahaStyle']['accent'] }};padding:16px 20px">
    <div class="uppercase font-extrabold mb-3" style="font-size:.65rem;letter-spacing:1.5px;color:{{ $data['currentCard']['mahaStyle']['accent'] }}">
        ▶ Currently Active Dasha — All Levels
    </div>
    <div class="flex flex-wrap gap-3 items-stretch mb-4">
        @foreach($data['currentCard']['periods'] as $p)
        <div class="rounded-xl flex-1" style="background:#fff;min-width:130px;border:1.5px solid {{ $p['style']['border'] }};padding:10px 14px">
            <div class="uppercase font-extrabold mb-2" style="font-size:.58rem;letter-spacing:.8px;color:{{ $p['style']['accent'] }}">{{ $p['label'] }}</div>
            <div class="flex items-center gap-2 mb-2">
                <span style="font-size:1.2rem;line-height:1">{{ $p['sym'] }}</span>
                <div>
                    <div class="font-black" style="font-size:1rem;line-height:1;color:{{ $p['style']['accent'] }}">{{ $p['lordUC'] }}</div>
                    <div class="font-semibold" style="font-size:.65rem;color:{{ $p['style']['text'] }}">{{ $p['lordFull'] }}</div>
                </div>
            </div>
            <div class="font-semibold leading-relaxed" style="font-size:.65rem;color:#3a4a5a;border-top:1px solid {{ $p['style']['border'] }};padding-top:5px">
                {{ $p['startDate'] }} → {{ $p['endDate'] }}<br>
                <span class="font-bold" style="color:{{ $p['style']['accent'] }}">{{ $p['durationStr']['y'] }}y {{ $p['durationStr']['m'] }}m total</span>
            </div>
        </div>
        @endforeach
    </div>

    @if($data['currentCard']['elapsed'] !== null)
    <div class="flex justify-between items-center mb-2">
        <span class="font-bold" style="font-size:.68rem;color:{{ $data['currentCard']['mahaStyle']['accent'] }}">{{ $data['currentCard']['mahaLordUC'] }} Mahadasha — {{ $data['currentCard']['elapsed'] }}% elapsed</span>
        <span class="font-semibold" style="font-size:.68rem;color:#3a4a5a">{{ $data['currentCard']['remaining'] }}% remaining · ends {{ $data['currentCard']['mahaEndDate'] }}</span>
    </div>
    <div class="rounded-full overflow-hidden mb-2" style="height:7px;background:rgba(0,0,0,.1)">
        <div class="rounded-full" style="width:{{ $data['currentCard']['elapsed'] }}%;height:100%;background:{{ $data['currentCard']['mahaStyle']['accent'] }}"></div>
    </div>

    @if($data['currentCard']['antarElapsed'] !== null)
    <div class="flex justify-between items-center mt-2 mb-1">
        <span class="font-bold" style="font-size:.66rem;color:{{ $data['currentCard']['antarStyle']['accent'] }}">{{ $data['currentCard']['antarLordUC'] }} Antardasha — {{ $data['currentCard']['antarElapsed'] }}% elapsed</span>
        <span class="font-semibold" style="font-size:.66rem;color:#3a4a5a">ends {{ $data['currentCard']['antarEndDate'] }}</span>
    </div>
    <div class="rounded-full overflow-hidden" style="height:5px;background:rgba(0,0,0,.08)">
        <div class="rounded-full" style="width:{{ $data['currentCard']['antarElapsed'] }}%;height:100%;background:{{ $data['currentCard']['antarStyle']['accent'] }};opacity:.85"></div>
    </div>
    @endif
    @endif
</div>
@endif

{{-- 3. All 9 Mahadashas --}}
<div class="uppercase font-extrabold mb-3" style="font-size:.65rem;letter-spacing:1.5px;color:#2a3a4a">
    ◈ All 9 Mahadashas — Complete 120-Year Sequence
</div>
<div class="font-medium mb-4" style="font-size:.72rem;color:#3a4a5a">
    Click any Mahadasha row to expand Antardasha sub-periods. Active periods are pre-expanded and highlighted.
</div>

@foreach($data['mahaRows'] as $maha)
<div class="rounded-2xl mb-2 overflow-hidden"
     style="opacity:{{ $maha['opacity'] }};border:1.5px solid {{ $maha['isCurrent'] ? $maha['style']['border'] : ($maha['isPast'] ? '#dde0e6' : '#e0e4ec') }};border-left:4px solid {{ $maha['isCurrent'] ? $maha['style']['accent'] : ($maha['isPast'] ? '#b8c0cc' : '#c8d0de') }}">

    {{-- Mahadasha header --}}
    <div id="{{ $maha['htmlId'] }}_hdr" class="flex items-center gap-4 cursor-pointer select-none"
         style="background:{{ $maha['isCurrent'] ? $maha['style']['bg'] : ($maha['isPast'] ? '#f5f6f8' : '#f8f9fb') }};padding:14px 18px"
         onclick="(function(){var b=document.getElementById('{{ $maha['htmlId'] }}_body'),d=document.getElementById('{{ $maha['htmlId'] }}_det'),btnA=document.getElementById('{{ $maha['htmlId'] }}_btn_antar');if(!b)return;var open=b.style.display!=='none';b.style.display=open?'none':'block';d.style.display='none';btnA.style.background=open?'{{ addslashes($maha['style']['light']) }}':'{{ addslashes($maha['style']['accent']) }}';btnA.style.color=open?'{{ addslashes($maha['style']['accent']) }}':'#fff'})()">

        <div class="flex-shrink-0 text-center rounded-xl" style="background:{{ $maha['style']['bg'] }};border:1.5px solid {{ $maha['style']['border'] }};padding:8px 14px;min-width:54px">
            <div style="font-size:1.2rem;line-height:1;margin-bottom:3px">{{ $maha['sym'] }}</div>
            <div class="font-black" style="font-size:.75rem;line-height:1;color:{{ $maha['style']['accent'] }}">{{ $maha['abbr'] }}</div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center flex-wrap gap-2 mb-1">
                <span class="font-extrabold" style="font-size:.95rem;color:#0f1c2d">{{ $maha['lordFull'] }} Mahadasha</span>
                @if($maha['isCurrent'])
                    <span class="font-extrabold rounded-full" style="font-size:.58rem;background:{{ $maha['style']['accent'] }};color:#fff;padding:2px 9px"> ▶ ACTIVE NOW</span>
                @elseif($maha['isPast'])
                    <span class="font-bold rounded-full" style="font-size:.58rem;background:#dde2e8;color:#3a4a5a;padding:2px 8px">PAST</span>
                @else
                    <span class="font-bold rounded-full" style="font-size:.58rem;background:{{ $maha['style']['light'] }};color:{{ $maha['style']['accent'] }};padding:2px 8px">UPCOMING</span>
                @endif
            </div>
            <div class="font-semibold" style="font-size:.75rem;color:#3a4a5a">
                {{ $maha['startDate'] }} → {{ $maha['endDate'] }} &nbsp;·&nbsp; {{ $maha['years'] }} yrs total &nbsp;·&nbsp; {{ $maha['durationStr']['y'] }}y {{ $maha['durationStr']['m'] }}m in this cycle
            </div>
        </div>

        <div class="flex gap-2 flex-shrink-0">
            <button id="{{ $maha['htmlId'] }}_btn_antar"
                    onclick="event.stopPropagation();(function(){var b=document.getElementById('{{ $maha['htmlId'] }}_body'),d=document.getElementById('{{ $maha['htmlId'] }}_det'),btnA=document.getElementById('{{ $maha['htmlId'] }}_btn_antar'),btnD=document.getElementById('{{ $maha['htmlId'] }}_btn_det');var open=b.style.display!=='none';b.style.display=open?'none':'block';d.style.display='none';btnA.style.background=open?'{{ addslashes($maha['style']['light']) }}':'{{ addslashes($maha['style']['accent']) }}';btnA.style.color=open?'{{ addslashes($maha['style']['accent']) }}':'#fff';btnD.style.background='{{ addslashes($maha['style']['light']) }}';btnD.style.color='{{ addslashes($maha['style']['accent']) }}';})()"
                    style="color:{{ $maha['isCurrent'] ? '#fff' : $maha['style']['accent'] }};font-size:.7rem;font-weight:800;padding:5px 12px;background:{{ $maha['isCurrent'] ? $maha['style']['accent'] : $maha['style']['light'] }};border-radius:20px;border:1.5px solid {{ $maha['style']['border'] }};cursor:pointer;white-space:nowrap">
                ▼ Antardashas
            </button>
            <button id="{{ $maha['htmlId'] }}_btn_det"
                    onclick="event.stopPropagation();(function(){var b=document.getElementById('{{ $maha['htmlId'] }}_body'),d=document.getElementById('{{ $maha['htmlId'] }}_det'),btnA=document.getElementById('{{ $maha['htmlId'] }}_btn_antar'),btnD=document.getElementById('{{ $maha['htmlId'] }}_btn_det');var open=d.style.display!=='none';d.style.display=open?'none':'block';b.style.display='none';btnD.style.background=open?'{{ addslashes($maha['style']['light']) }}':'{{ addslashes($maha['style']['accent']) }}';btnD.style.color=open?'{{ addslashes($maha['style']['accent']) }}':'#fff';btnA.style.background='{{ addslashes($maha['style']['light']) }}';btnA.style.color='{{ addslashes($maha['style']['accent']) }}';})()"
                    style="color:{{ $maha['style']['accent'] }};font-size:.7rem;font-weight:800;padding:5px 12px;background:{{ $maha['style']['light'] }};border-radius:20px;border:1.5px solid {{ $maha['style']['border'] }};cursor:pointer;white-space:nowrap">
                ◈ Details
            </button>
        </div>
    </div>

    {{-- Details panel --}}
    <div id="{{ $maha['htmlId'] }}_det" style="display:none">@include('partials.dasha._details_tab', ['detailData' => $maha['detailData']])</div>

    {{-- Antardasha body --}}
    <div id="{{ $maha['htmlId'] }}_body"
         style="display:{{ $maha['isCurrent'] ? 'block' : 'none' }};background:#f8f9fb;border-top:1px solid {{ $maha['isCurrent'] ? $maha['style']['border'] : '#e0e4ec' }}">

        {{-- Column headers --}}
        <div class="grid uppercase font-extrabold"
             style="grid-template-columns:48px 1fr 100px 100px 100px 90px;padding:10px 18px 8px;font-size:.6rem;letter-spacing:1px;color:#3a4a5a;border-bottom:1px solid #dde2ea">
            <div></div>
            <div>Antardasha (Bhukti)</div>
            <div class="text-center">Duration</div>
            <div class="text-center">Start date</div>
            <div class="text-center">End date</div>
            <div class="text-center">Info</div>
        </div>

        @foreach($maha['antarRows'] as $antar)
        <div class="overflow-hidden" style="border-bottom:1px solid #e8ecf0">

            {{-- Antardasha row --}}
            <div id="{{ $antar['antarId'] }}_hdr" class="grid items-center"
                 style="grid-template-columns:48px 1fr 100px 100px 100px 90px;padding:10px 18px;background:{{ $antar['isCurrent'] ? $antar['style']['bg'] : 'transparent' }};opacity:{{ $antar['isPast'] ? '.60' : '1' }}">
                <div class="text-center" style="font-size:1.1rem;line-height:1">{{ $antar['sym'] }}</div>
                <div>
                    <span class="font-extrabold" style="font-size:.82rem;color:{{ $antar['style']['accent'] }}">{{ $antar['lordUC'] }}</span>
                    <span class="font-semibold" style="font-size:.72rem;color:#283040"> {{ $antar['lordFull'] }}</span>
                    @if($antar['isCurrent'])
                    <span class="font-extrabold rounded-full ml-1" style="font-size:.55rem;background:{{ $antar['style']['accent'] }};color:#fff;padding:1px 6px"> ▶ ACTIVE</span>
                    @endif
                </div>
                <div class="text-center font-semibold" style="font-size:.72rem;color:#283040">{{ $antar['durationStr']['y'] }}y {{ $antar['durationStr']['m'] }}m</div>
                <div class="text-center font-semibold" style="font-size:.7rem;color:#283040;font-family:monospace">{{ $antar['startDate'] }}</div>
                <div class="text-center font-semibold" style="font-size:.7rem;color:#283040;font-family:monospace">{{ $antar['endDate'] }}</div>
                <div class="text-center flex gap-1 justify-center">
                    <button id="{{ $antar['antarId'] }}_btn_sub"
                            onclick="event.stopPropagation();(function(){var b=document.getElementById('{{ $antar['antarId'] }}_body'),d=document.getElementById('{{ $antar['antarId'] }}_det');var open=b.style.display!=='none';b.style.display=open?'none':'block';d.style.display='none';})()"
                            style="font-size:.6rem;font-weight:800;padding:3px 7px;background:{{ $antar['isCurrent'] ? $antar['style']['accent'] : $antar['style']['light'] }};color:{{ $antar['isCurrent'] ? '#fff' : $antar['style']['accent'] }};border-radius:12px;border:1px solid {{ $antar['style']['border'] }};cursor:pointer;white-space:nowrap">
                        ▼ Sub
                    </button>
                    <button id="{{ $antar['antarId'] }}_btn_det"
                            onclick="event.stopPropagation();(function(){var b=document.getElementById('{{ $antar['antarId'] }}_body'),d=document.getElementById('{{ $antar['antarId'] }}_det');var open=d.style.display!=='none';d.style.display=open?'none':'block';b.style.display='none';})()"
                            style="font-size:.6rem;font-weight:800;padding:3px 7px;background:{{ $antar['style']['light'] }};color:{{ $antar['style']['accent'] }};border-radius:12px;border:1px solid {{ $antar['style']['border'] }};cursor:pointer">
                        ◈
                    </button>
                </div>
            </div>

            {{-- Antardasha details --}}
            <div id="{{ $antar['antarId'] }}_det" style="display:none">@include('partials.dasha._details_tab', ['detailData' => $antar['detailData']])</div>

            {{-- Pratyantar grid --}}
            <div id="{{ $antar['antarId'] }}_body"
                 style="display:{{ $antar['isCurrent'] ? 'block' : 'none' }};background:{{ $antar['isCurrent'] ? '#fff' : '#f8f9fb' }};border-top:1px solid {{ $antar['style']['border'] }};padding:12px 18px 14px 66px">
                <div class="uppercase font-extrabold mb-3" style="font-size:.6rem;letter-spacing:1px;color:{{ $antar['style']['accent'] }}">
                    Pratyantar Dasha — Sub-Sub Periods
                </div>
                <div class="grid gap-2" style="grid-template-columns:repeat(auto-fill,minmax(210px,1fr))">
                    @foreach($antar['prRows'] as $prat)
                    <div class="rounded-lg overflow-hidden"
                         style="background:{{ $prat['isCurrent'] ? $prat['style']['bg'] : ($prat['isPast'] ? '#f2f4f6' : '#f7f8fa') }};border:1.5px solid {{ $prat['isCurrent'] ? $prat['style']['border'] : '#dde2e8' }};opacity:{{ $prat['isPast'] ? '.60' : '1' }}">
                        <div style="padding:9px 12px 0">
                            <div class="flex items-center gap-2 mb-2">
                                <span style="font-size:1rem;line-height:1">{{ $prat['sym'] }}</span>
                                <span class="font-extrabold" style="font-size:.82rem;color:{{ $prat['style']['accent'] }}">{{ $prat['lordUC'] }}</span>
                                <span class="font-semibold" style="font-size:.7rem;color:#283040">{{ $prat['lordFull'] }}</span>
                                @if($prat['isCurrent'])
                                <span class="font-extrabold rounded-full ml-auto" style="font-size:.55rem;background:{{ $prat['style']['accent'] }};color:#fff;padding:1px 6px">▶</span>
                                @endif
                            </div>
                            <div class="font-semibold leading-relaxed"
                                 style="font-size:.66rem;color:#283040;border-top:1px solid {{ $prat['isCurrent'] ? $prat['style']['border'] : '#e4e8ec' }};padding-top:5px">
                                {{ $prat['startDate'] }} → {{ $prat['endDate'] }}<br>
                                <span class="font-bold" style="color:{{ $prat['style']['accent'] }}">{{ $prat['durationStr']['m'] }}m {{ $prat['durationStr']['d'] }}d</span>
                            </div>
                            <div style="padding:5px 0 6px">
                                <button id="{{ $prat['pratId'] }}_btn"
                                        onclick="event.stopPropagation();(function(){var d=document.getElementById('{{ $prat['pratId'] }}_det');var open=d.style.display!=='none';d.style.display=open?'none':'block';this.textContent=open?'◈ Details':'▲ Close';}).call(this,event)"
                                        style="font-size:.6rem;font-weight:800;padding:2px 8px;background:{{ $prat['style']['light'] }};color:{{ $prat['style']['accent'] }};border-radius:10px;border:1px solid {{ $prat['style']['border'] }};cursor:pointer">
                                    ◈ Details
                                </button>
                            </div>
                        </div>
                        <div id="{{ $prat['pratId'] }}_det" style="display:none">@include('partials.dasha._details_tab', ['detailData' => $prat['detailData']])</div>

                        {{-- Sookshma pills (current pratyantar only) --}}
                        @if($prat['isCurrent'] && !empty($prat['sookRows']))
                        <div style="padding:0 12px 10px;border-top:1px solid {{ $prat['style']['border'] }};margin-top:2px">
                            <div class="uppercase font-extrabold mb-2" style="font-size:.56rem;letter-spacing:.8px;color:{{ $prat['style']['accent'] }};margin-top:8px">Sookshma Dasha</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($prat['sookRows'] as $s)
                                <span class="inline-flex items-center gap-1 rounded-xl"
                                      style="font-size:.63rem;padding:2px 8px;background:{{ $s['isCurrent'] ? $s['style']['bg'] : ($s['isPast'] ? '#eaedf0' : '#f0f3f6') }};color:{{ $s['isCurrent'] ? $s['style']['accent'] : '#3a4a5a' }};border:1px solid {{ $s['isCurrent'] ? $s['style']['border'] : '#d8dde4' }};font-weight:{{ $s['isCurrent'] ? '800' : '600' }};opacity:{{ $s['isPast'] ? '.55' : '1' }}">
                                    {{ $s['sym'] }} {{ $s['abbr'] }}{{ $s['isCurrent'] ? ' ▶' : '' }}
                                    <span style="font-size:.55rem;opacity:.7;font-weight:500">·{{ $s['durationStr']['d'] }}d</span>
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
        @endforeach

    </div>
</div>
@endforeach

{{-- 4. Legend reference --}}
<div class="rounded-xl mt-2" style="background:#eef1f5;border:1px solid #cdd4de;padding:16px 20px">
    <div class="uppercase font-extrabold mb-3" style="font-size:.6rem;letter-spacing:1.5px;color:#3a4a5a">
        ◈ Dasha Sequence &amp; Durations (BPHS Ch. 46)
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($data['legendCards'] as $lc)
        <div class="flex items-center gap-2 rounded-full"
             style="background:{{ $lc['style']['bg'] }};border:1px solid {{ $lc['style']['border'] }};padding:4px 12px">
            <span style="font-size:.9rem">{{ $lc['sym'] }}</span>
            <span class="font-extrabold" style="font-size:.72rem;color:{{ $lc['style']['accent'] }}">{{ $lc['lordUC'] }}</span>
            <span class="font-semibold" style="font-size:.72rem;color:{{ $lc['style']['text'] }}">{{ $lc['years'] }} yrs</span>
        </div>
        @endforeach
    </div>
</div>
