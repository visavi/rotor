@props([
    'values' => [],
    'color' => 'var(--bs-primary)',
    'width' => 180,
    'height' => 40,
    'type' => 'line',
])

@php
    $values = array_values(array_map('floatval', $values));
    $count = count($values);
    $max = $count ? max($values) : 0;
    // Столбики отсчитываются от нуля, иначе минимальное значение схлопывается в пустоту
    $min = $type === 'bar' ? 0 : ($count ? min($values) : 0);
    $range = ($max - $min) ?: 1;
    $id = 'spark-' . uniqid();

    // Точки нормализуются в координаты вьюпорта, чтобы график тянулся по ширине карточки
    $points = [];
    foreach ($values as $i => $value) {
        $x = $count > 1 ? round($i * $width / ($count - 1), 2) : $width / 2;
        $y = round($height - 2 - (($value - $min) / $range) * ($height - 4), 2);
        $points[] = [$x, $y];
    }
@endphp

@if ($count)
    <svg class="sparkline" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" role="img" aria-hidden="true">
        @if ($type === 'bar')
            @php $barWidth = $width / max($count, 1) * 0.7; @endphp
            @foreach ($points as $i => [$x, $y])
                <rect x="{{ round($i * $width / $count, 2) }}" y="{{ $y }}"
                      width="{{ round($barWidth, 2) }}" height="{{ round($height - $y, 2) }}"
                      fill="{{ $color }}" rx="1"></rect>
            @endforeach
        @else
            <defs>
                <linearGradient id="{{ $id }}" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="{{ $color }}" stop-opacity=".35"></stop>
                    <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0"></stop>
                </linearGradient>
            </defs>
            <polygon fill="url(#{{ $id }})"
                     points="0,{{ $height }} {{ implode(' ', array_map(static fn ($p) => implode(',', $p), $points)) }} {{ $width }},{{ $height }}"></polygon>
            <polyline fill="none" stroke="{{ $color }}" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"
                      points="{{ implode(' ', array_map(static fn ($p) => implode(',', $p), $points)) }}"></polyline>
        @endif
    </svg>
@endif
