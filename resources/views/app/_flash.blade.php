{{-- Сводим обе системы флеш-уведомлений к одной --}}
@php
    $flashBag = (array) session('flash', []);

    $grouped = collect(['success', 'danger', 'warning', 'info'])
        ->mapWithKeys(fn ($status) => [$status => array_unique(array_merge(
            (array) session()->get($status, []),
            (array) ($flashBag[$status] ?? []),
        ))])
        ->filter()
        ->all();

    session()->forget('flash');
@endphp

{{-- Ошибки валидации --}}
@if ($errors = session()->get('errors'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@foreach ($grouped as $status => $items)
    <div class="alert alert-{{ $status }} alert-dismissible fade show" role="alert">
        @foreach ($items as $message)
            <div>{{ $message }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endforeach
