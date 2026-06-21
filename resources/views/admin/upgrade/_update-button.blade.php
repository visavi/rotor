@php
    /** @var string $tag Тег релиза */
    /** @var string $label Текст кнопки */
    $full ??= false;       // скачать полный архив (с vendor)
    $badge ??= null;       // 'upgrade' | 'full' | null
    $class ??= 'btn-warning';
    $confirm ??= __('admin.upgrade.update_confirm');
@endphp
<button class="btn {{ $class }} btn-update"
    data-tag="{{ $tag }}"
    data-full="{{ $full ? 1 : 0 }}"
    data-confirm="{{ $confirm }}"
    data-download-url="{{ route('admin.upgrade.download') }}"
    data-apply-url="{{ route('admin.upgrade.apply') }}"
    data-label-progress="{{ __('admin.upgrade.update_progress') }}"
    data-label-applying="{{ __('admin.upgrade.update_applying') }}"
    data-label-done="{{ __('admin.upgrade.update_done') }}"
    data-label-error="{{ __('admin.upgrade.request_error') }}"
    data-label-reload="{{ __('admin.upgrade.update_reload') }}"
    onclick="runUpdate(this)">
    <i class="fa fa-download"></i> {{ $label }}
    @if ($badge)
        <span class="badge {{ $badge === 'upgrade' ? 'bg-info' : 'bg-secondary' }} ms-1">{{ $badge }}</span>
    @endif
</button>
