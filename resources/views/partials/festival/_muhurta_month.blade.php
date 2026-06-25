{{-- Muhurta month calendar — AJAX partial
     Expects: $data = MuhratCalculator::prepareMonthView($dates, $type, $mo, $yr)
--}}
@if($data['isEmpty'])
<div style="padding:18px 22px;font-family:'Tiro Devanagari Sanskrit',serif;font-size:1rem;color:#7a5830;background:#fff8ee;border-radius:8px;border:1px solid rgba(168,112,40,.2)">
    @if($data['head'])<strong>{{ $data['head'] }}</strong><br><br>@endif
    No auspicious dates found this month. Try changing Rashi or category.
</div>
@else
<div style="font-family:'Tiro Devanagari Sanskrit',serif;overflow-x:auto">
    @if($data['head'])
    <div style="padding:10px 16px;background:linear-gradient(135deg,#f9edd8,#f2e0c4);border-bottom:2px solid rgba(168,112,40,.3);font-size:1.1rem;font-weight:600;color:#6b4800;letter-spacing:.04em">{{ $data['head'] }}</div>
    @endif
    <table style="width:100%;border-collapse:collapse;font-size:1.08rem">
        <thead>
            <tr style="background:#f2e0c4;border-bottom:2px solid rgba(168,112,40,.4)">
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.9rem;border-right:1px solid rgba(168,112,40,.2)">Paksha<br>Tithi</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Vara<br>Date</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Nakshatra</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Sun<br>Rashi</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Moon<br>Rashi</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;white-space:nowrap;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Chandrabala<br>Tarabala</th>
                <th style="padding:9px 8px;text-align:left;color:#6b4800;font-size:.95rem;border-right:1px solid rgba(168,112,40,.2)">Yoga · Karana · Dosha</th>
                <th style="padding:9px 8px;text-align:center;color:#6b4800;font-size:.95rem">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $d)
            <tr style="background:{{ $d['gradeBg'] }};border-bottom:1px solid rgba(168,112,40,.12)">
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12)">
                    <div style="font-size:.95rem;color:#6b4800;font-weight:600">{{ $d['pakshaHi'] }}</div>
                    <div style="font-size:.9rem;color:#3a2410">{{ $d['tithiHi'] }}</div>
                </td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12)">
                    <div style="font-size:1.05rem;font-weight:600;color:#1c0e04">{{ $d['varaHi'] }}</div>
                    <div style="font-family:'Crimson Pro',serif;font-size:1.15rem;font-weight:700;color:#6b4800">{{ $d['day'] }}</div>
                    <div style="font-size:.82rem;color:#9a6b0a">{{ $d['sunrise'] }}</div>
                </td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12)">
                    <div style="font-size:1.05rem;color:#1c0e04;font-weight:600">{{ $d['nakHi'] }}</div>
                    @if($d['choStr'])<div style="font-size:.92rem;color:#1a5a28;margin-top:3px">{{ $d['choStr'] }}</div>@endif
                    <div style="font-size:.92rem;color:#7a5830">Abhijit {{ $d['abhi'] }}</div>
                </td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);font-size:1.05rem;color:#3a2410">{{ $d['sunRashiHi'] }}</td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12);font-size:1.05rem;color:#3a2410">{{ $d['moonRashiHi'] }}</td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top;border-right:1px solid rgba(168,112,40,.12)">
                    @if($d['cbCell'])
                    <span style="background:{{ $d['cbCell']['bg'] }};color:{{ $d['cbCell']['color'] }};padding:2px 6px;border-radius:4px;font-size:1rem;display:inline-block;margin-bottom:2px">{{ $d['cbCell']['icon'] }} #{{ $d['cbCell']['dist'] }}</span>
                    @if($d['cbCell']['tb'])
                    <br><span style="background:{{ $d['cbCell']['tb']['bg'] }};color:{{ $d['cbCell']['tb']['color'] }};padding:2px 6px;border-radius:4px;font-size:.88rem;display:inline-block">{{ $d['cbCell']['tb']['icon'] }} {{ $d['cbCell']['tb']['name'] }}</span>
                    @endif
                    @else
                    —
                    @endif
                </td>
                <td style="padding:12px 10px;vertical-align:top;border-right:1px solid rgba(168,112,40,.12)">
                    <div style="color:{{ $d['yogaColor'] }};font-size:.95rem">{{ $d['yogaHi'] }} · {{ $d['karanaHi'] }}</div>
                    <div style="margin-top:3px">
                        @if(empty($d['doshaItems']))
                        <span style="color:#1a5a28;font-size:.95rem">No Dosha</span>
                        @else
                        @foreach($d['doshaItems'] as $item)
                        <span style="color:{{ $item['color'] }};font-size:.95rem">{{ $item['text'] }}</span>
                        @if(!$loop->last) &nbsp; @endif
                        @endforeach
                        @endif
                    </div>
                    @if($d['milanBadge'])
                    <br><span style="background:{{ $d['milanBadge']['color'] }};color:#fff;padding:1px 7px;border-radius:8px;font-size:.95rem">Milan {{ $d['milanBadge']['total'] }}/36</span>
                    @endif
                    <div style="font-size:.82rem;color:#c0302a;margin-top:2px">Rahukala {{ $d['rahuStr'] }}</div>
                </td>
                <td style="padding:12px 10px;text-align:center;vertical-align:top">
                    <div style="font-family:'Crimson Pro',serif;font-size:2rem;font-weight:700;color:{{ $d['gradeColor'] }};line-height:1">{{ $d['score'] }}</div>
                    <div style="font-size:.9rem;color:{{ $d['gradeColor'] }};font-weight:600;margin-top:2px">{{ $d['gradeLabel'] }}</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
