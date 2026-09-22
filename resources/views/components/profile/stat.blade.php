@props(['value', 'label', 'url' => null, 'class' => null])

{{-- Плитка метрики в шапке анкеты. Модули добавляют свои через хук userStats --}}
<{{ $url ? 'a' : 'span' }} class="profile-stat"@if ($url) href="{{ $url }}"@endif>
    <b @class([$class])>{{ $value }}</b>
    <small>{{ $label }}</small>
</{{ $url ? 'a' : 'span' }}>
