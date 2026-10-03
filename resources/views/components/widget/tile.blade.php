@props(['widget'])

@php
    $tag = $widget['url'] ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($widget['url']) href="{{ $widget['url'] }}" @endif class="tile stat-tile">
    <div class="stat-tile-head">
        <span class="stat-tile-icon" style="background: {{ $widget['color'] }}1a; color: {{ $widget['color'] }}">
            <i class="{{ $widget['icon'] }}"></i>
        </span>
        <span class="stat-tile-label">{{ $widget['label'] }}</span>
    </div>

    <div class="stat-tile-value">
        {{ number_format($widget['value'], 0, '.', ' ') }}
        @isset($widget['unit'])
            <span class="stat-tile-unit">{{ $widget['unit'] }}</span>
        @endisset
    </div>

    {{-- Период и рост в одной строке: длинное число с единицей не помещалось рядом --}}
    <div class="stat-tile-meta">
        <span class="stat-tile-period">{{ __('index.widget_period', ['days' => $widget['days']]) }}</span>

        @isset($widget['diff'])
            {{-- Ноль (и -0.0 после округления) — без изменений, не рост --}}
            @php
                $diff = $widget['diff'];
                $good = $widget['inverse'] ? $diff < 0 : $diff > 0;
                $color = $diff == 0 ? 'text-secondary' : ($good ? 'text-success' : 'text-danger');
                $icon = $diff == 0 ? 'minus' : ($diff > 0 ? 'caret-up' : 'caret-down');
            @endphp

            <span class="stat-tile-diff {{ $color }}"
                  title="{{ __('index.widget_previous', ['value' => $widget['previous']]) }}">
                <i class="fas fa-{{ $icon }}"></i>
                {{ abs($diff) }}%
            </span>
        @endisset
    </div>

    <x-widget.sparkline :series="$widget['series']" :type="$widget['type']" />

    @if (count($widget['series']) > 1)
        <div class="stat-tile-legend">
            @foreach ($widget['series'] as $line)
                <span><i class="fas fa-circle" style="color: {{ $line['color'] }}"></i> {{ $line['label'] }}</span>
            @endforeach
        </div>
    @endif
</{{ $tag }}>
