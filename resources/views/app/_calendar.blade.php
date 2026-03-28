<div class="fw-bold">{{ dateFixed($time, 'j F Y') }}</div>
<div class="calendar-grid">
    <div class="calendar-head text-center">{{ __('main.mo') }}</div>
    <div class="calendar-head text-center">{{ __('main.tu') }}</div>
    <div class="calendar-head text-center">{{ __('main.we') }}</div>
    <div class="calendar-head text-center">{{ __('main.th') }}</div>
    <div class="calendar-head text-center">{{ __('main.fr') }}</div>
    <div class="calendar-head text-center text-danger">{{ __('main.sa') }}</div>
    <div class="calendar-head text-center text-danger">{{ __('main.su') }}</div>

    @foreach ($calendar as $week)
        @foreach ($week as $keyDay => $valDay)
            @if ($date['day'] === $valDay)
                <div class="calendar-cell text-center"><span class="text-white bg-danger px-1 fw-bold">{{ $valDay }}</span></div>
                @continue
            @endif

            @if (isset($newsIds[$valDay]))
                <div class="calendar-cell text-center"><a href="{{ route('news.view', ['id' => $newsIds[$valDay]]) }}"><span class="text-white bg-success px-1 fw-bold">{{ $valDay }}</span></a></div>
                @continue
            @endif

            @if ($keyDay === 5 || $keyDay === 6)
                <div class="calendar-cell text-center text-danger">{{ $valDay ?: '' }}</div>
                @continue
            @endif

            <div class="calendar-cell text-center">{{ $valDay ?: '' }}</div>
        @endforeach
    @endforeach
</div>