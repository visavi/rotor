@php
    $validation = session()->get('errors')?->all() ?? [];

    // Ошибки валидации — одним уведомлением с остальными ошибками. Списки
    // переиндексированы: с дырявыми ключами JSON стал бы объектом, а main.js ждёт массив
    $grouped = collect(['success', 'danger', 'warning', 'info'])
        ->mapWithKeys(fn ($status) => [$status => array_values(array_unique([
            ...($status === 'danger' ? $validation : []),
            ...array_values((array) session()->get($status, [])),
        ]))])
        ->filter();
@endphp

@if ($grouped->isNotEmpty())
    {{-- Сообщения показывает notyf (main.js): данные, а не разметка --}}
    <script type="application/json" class="js-flash">{!! json_encode($grouped->all(), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

    <noscript>
        @foreach ($grouped as $status => $items)
            <div class="alert alert-{{ $status }}" role="alert">
                @foreach ($items as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endforeach
    </noscript>
@endif
