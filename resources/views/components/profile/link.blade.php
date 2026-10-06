@props(['icon', 'label', 'url', 'count' => null, 'extra' => null, 'guests' => true])

{{-- Карточка раздела в анкете. Модули отдают её через хук userProfileLinks.
     guests = false — страницы раздела закрыты для гостей, гостю карточка без ссылок --}}
@php
    $linked = $guests || auth()->check();
    $tag = $linked ? 'a' : 'span';
@endphp
<div @class(['profile-link', 'is-static' => ! $linked])>
    <{{ $tag }} class="profile-link-main" @if ($linked) href="{{ $url }}" @endif>
        <i class="{{ $icon }}"></i>
        <span class="profile-link-label">{{ $label }}</span>

        @isset($count)
            <b class="profile-link-count">{{ formatShortNum($count) }}</b>
        @endisset
    </{{ $tag }}>

    @isset($extra)
        <{{ $tag }} class="profile-link-extra" @if ($linked) href="{{ $extra['url'] }}" @endif>
            {{ $extra['label'] }}

            @isset($extra['count'])
                <span class="profile-link-extra-count">{{ formatShortNum($extra['count']) }}</span>
            @endisset
        </{{ $tag }}>
    @endisset
</div>
