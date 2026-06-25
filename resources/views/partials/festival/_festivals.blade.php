{{-- Festival card grid — AJAX partial
     Expects: $view = HinduFestivalCalculator::prepareForView($festivals, $category)
--}}
@if($view['empty'])
    <div class="fest-loading">{{ $view['message'] }}</div>
@else
<div id="festRenderRoot">

    <div class="fp-info-strip">
        <div>
            <div class="fp-strip-title">{{ $view['year'] }} — All Festivals &amp; Vrats</div>
            <div class="fp-strip-sub">Lahiri Ayanamsa · Meeus Precision · {{ $view['count'] }} events</div>
        </div>
        <div class="fp-strip-count">{{ $view['count'] }} Events</div>
    </div>

    @foreach($view['months'] as $month)
    <div class="fp-month-hdr">
        <span class="fp-month-en">{{ $month['name'] }}</span>
        <span class="fp-month-ct">{{ $month['count'] }} event{{ $month['count'] > 1 ? 's' : '' }}</span>
    </div>
    <div class="fest-card-grid">
        @foreach($month['cards'] as $card)
        <div class="fest-card {{ $card['cssClass'] }}" onclick="openFestDetail({{ $card['detailJson'] }})">
            <div class="fc-stripe fpc-bar"></div>
            <div class="fc-head">
                <div class="fc-date-box">
                    <div class="fc-date-num">{{ $card['dayNum'] }}</div>
                    <div class="fc-date-day">{{ $card['dayAbbr'] }}</div>
                </div>
                <div class="fc-icon-name">
                    <div class="fc-badges">
                        <span class="fc-cat-chip">{{ $card['chipLabel'] }}</span>
                        @if($card['tithi'])<span class="fc-tithi-pill">{{ $card['tithi'] }}</span>@endif
                    </div>
                    <div class="fc-name-en">{{ $card['name'] }}</div>
                </div>
            </div>
            <div class="fc-body">
                @if($card['significance'])<div class="fc-sig">{{ $card['significance'] }}</div>@endif
                @if($card['sunrise'] || $card['sunset'])
                <div class="fc-timing">
                    @if($card['sunrise'])<span class="fc-time-chip">🌅 {{ $card['sunrise'] }}</span>@endif
                    @if($card['sunset'])<span class="fc-time-chip">🌇 {{ $card['sunset'] }}</span>@endif
                </div>
                @endif
            </div>
            <div class="fc-footer">
                <span class="fc-masa">{{ $card['masa'] }}</span>
                <span class="fc-detail-hint">{{ $card['hasDetail'] ? 'View Details →' : 'Details →' }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

</div>
@endif
