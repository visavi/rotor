@props([
    'series' => [],
    'width' => 180,
    'height' => 40,
    'type' => 'line',
])

@php
    // Шкала общая для всех серий, иначе линии врут друг относительно друга
    $values = array_merge(...array_map(static fn ($s) => $s['values'], $series)) ?: [0];
    $count = max(array_map(static fn ($s) => count($s['values']), $series) ?: [0]);

    $max = max($values);
    // Столбики отсчитываются от нуля, иначе минимальное значение схлопывается в пустоту
    $min = $type === 'bar' ? 0 : min($values);
    $range = ($max - $min) ?: 1;

    // Точки нормализуются в координаты вьюпорта, чтобы график тянулся по ширине карточки
    $points = static function (array $line) use ($count, $width, $height, $min, $range) {
        $result = [];

        foreach (array_values($line) as $i => $value) {
            $x = $count > 1 ? round($i * $width / ($count - 1), 2) : $width / 2;
            $y = round($height - 2 - (($value - $min) / $range) * ($height - 4), 2);
            $result[] = [$x, $y];
        }

        return $result;
    };

    $path = static fn (array $line) => implode(' ', array_map(static fn ($p) => implode(',', $p), $line));

    $lines = array_map(static fn ($line) => $line + ['points' => $points($line['values'])], $series);

    // Заливки нескольких серий накладываются, поэтому они бледнее одиночной
    $opacity = count($lines) > 1 ? '.18' : '.35';
    $id = 'spark-' . uniqid();
@endphp

@if ($count)
    <svg class="sparkline" viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" role="img" aria-hidden="true">
        @if ($type === 'bar')
            @php
                // Столбики серий делят день между собой, чтобы не перекрывать друг друга
                $slot = $width / max($count, 1);
                $barWidth = $slot * 0.7 / count($lines);
            @endphp

            @foreach ($lines as $index => $line)
                @foreach ($line['points'] as $i => [$x, $y])
                    <rect x="{{ round($i * $slot + $index * $barWidth, 2) }}" y="{{ $y }}"
                          width="{{ round($barWidth, 2) }}" height="{{ round($height - $y, 2) }}"
                          fill="{{ $line['color'] }}" rx="1"></rect>
                @endforeach
            @endforeach
        @else
            <defs>
                @foreach ($lines as $index => $line)
                    <linearGradient id="{{ $id }}-{{ $index }}" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="{{ $line['color'] }}" stop-opacity="{{ $opacity }}"></stop>
                        <stop offset="100%" stop-color="{{ $line['color'] }}" stop-opacity="0"></stop>
                    </linearGradient>
                @endforeach
            </defs>

            {{-- Сперва все заливки, затем линии: иначе соседняя заливка перекрывает линию --}}
            @foreach ($lines as $index => $line)
                <polygon fill="url(#{{ $id }}-{{ $index }})"
                         points="0,{{ $height }} {{ $path($line['points']) }} {{ $width }},{{ $height }}"></polygon>
            @endforeach

            @foreach ($lines as $line)
                <polyline fill="none" stroke="{{ $line['color'] }}" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"
                          points="{{ $path($line['points']) }}"></polyline>
            @endforeach
        @endif
    </svg>
@endif
