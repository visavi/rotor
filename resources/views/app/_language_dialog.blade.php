{{-- Шаблон, а не готовый диалог: разметка инертна и флаги не грузятся, пока окно не открыли --}}
<template>
    <dialog class="dialog dialog-sm" id="languageDialog" aria-labelledby="languageDialogTitle">
        <div class="dialog-header">
            <h5 class="dialog-title" id="languageDialogTitle"><i class="fas fa-globe-americas"></i> {{ __('users.language') }}</h5>
            <button type="button" class="btn-close" data-dialog-close aria-label="{{ __('main.close') }}"></button>
        </div>
        <div class="dialog-body">
            <div class="list-group">
                @foreach (\App\Support\Locale::available() as $lang)
                    @php($name = __('main.lang', [], $lang))
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center{{ $lang === app()->getLocale() ? ' active' : '' }}" data-lang="{{ $lang }}">
                        <span>
                            <img src="/assets/flags/{{ $lang }}.svg" alt="{{ $lang }}" width="24" class="me-2 flag" onerror="this.remove()">
                            {{ $name === 'main.lang' ? strtoupper($lang) : $name }}
                        </span>
                        @if ($lang === app()->getLocale())
                            <i class="fas fa-check"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </dialog>
</template>
