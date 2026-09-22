@props(['icon', 'label', 'url', 'count' => null, 'extra' => null])

{{-- Карточка раздела в анкете. Модули отдают её через хук userProfileLinks --}}
<div class="profile-link">
    <a class="profile-link-main" href="{{ $url }}">
        <i class="{{ $icon }}"></i>
        <span class="profile-link-label">{{ $label }}</span>

        @isset($count)
            <b class="profile-link-count">{{ formatShortNum($count) }}</b>
        @endisset
    </a>

    @isset($extra)
        <a class="profile-link-extra" href="{{ $extra['url'] }}">
            {{ $extra['label'] }}

            @isset($extra['count'])
                <span class="profile-link-extra-count">{{ formatShortNum($extra['count']) }}</span>
            @endisset
        </a>
    @endisset
</div>
