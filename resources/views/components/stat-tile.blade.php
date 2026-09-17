@props(['widget'])

@php
    $tag = $widget['url'] ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($widget['url']) href="{{ $widget['url'] }}" @endif class="stat-tile shadow-sm">
    <div class="stat-tile-head">
        <span class="stat-tile-icon" style="background: {{ $widget['color'] }}1a; color: {{ $widget['color'] }}">
            <i class="{{ $widget['icon'] }}"></i>
        </span>
        <span class="stat-tile-label">{{ $widget['label'] }}</span>
    </div>

    <div class="stat-tile-value">
        {{ number_format($widget['value'], 0, '.', ' ') }}
        <span class="stat-tile-period">{{ __('index.widget_period', ['days' => App\Services\DashboardService::days()]) }}</span>
    </div>

    @isset($widget['diff'])
        <div class="stat-tile-diff {{ $widget['diff'] >= 0 ? 'text-success' : 'text-danger' }}"
             title="{{ __('index.widget_previous', ['value' => $widget['previous']]) }}">
            <i class="fas fa-caret-{{ $widget['diff'] >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($widget['diff']) }}%
        </div>
    @endisset

    <x-sparkline :values="$widget['series']" :color="$widget['color']" :type="$widget['type']" />
</{{ $tag }}>
