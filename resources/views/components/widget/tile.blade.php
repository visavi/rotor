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
            @php $good = $widget['inverse'] ? $widget['diff'] <= 0 : $widget['diff'] >= 0; @endphp

            <span class="stat-tile-diff {{ $good ? 'text-success' : 'text-danger' }}"
                  title="{{ __('index.widget_previous', ['value' => $widget['previous']]) }}">
                <i class="fas fa-caret-{{ $widget['diff'] >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($widget['diff']) }}%
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
