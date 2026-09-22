@props(['icon', 'label', 'url', 'badge' => null])

{{-- Строка действия в анкете. Модули отдают её через хуки userAction*/userNotPersonal* --}}
<a class="profile-action" href="{{ $url }}">
    <i class="{{ $icon }}"></i>
    <span class="profile-action-label">{{ $label }}</span>

    @isset($badge)
        <span class="badge bg-adaptive">{{ $badge }}</span>
    @endisset
</a>
