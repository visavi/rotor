@props(['title' => null, 'icon' => null, 'bodyClass' => null])

{{-- Секция рисуется, только когда внутри что-то есть: содержимое собирается
     из хуков и условной разметки, заранее его не проверить.
     Комментарии-маркеры хуков содержимым не считаются --}}
@php($filled = trim(preg_replace('/<!--.*?-->/s', '', $slot)) !== '')

@if ($filled)
    <div {{ $attributes->class(['section mb-3 shadow']) }}>
        @isset($title)
            <div class="section-title">@isset($icon)<i class="{{ $icon }}"></i> @endisset{{ $title }}</div>
        @endisset

        <div @class(['section-body', $bodyClass])>{{ $slot }}</div>
    </div>
@endif
