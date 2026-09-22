@props(['label', 'value'])

{{-- Пара «подпись — значение» в анкете. Модули отдают её через хук userFields --}}
<dt>{{ $label }}</dt>
<dd>{!! $value !!}</dd>
