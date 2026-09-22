@props(['items', 'row', 'action' => null, 'sortable' => true])

@php
    use App\Support\CategoryTree;

    // Нормализация входа — зона компонента: и скрытое поле, и разметка строятся
    // из одного плоского списка, иначе порядок в поле разойдётся с нарисованным.
    // Вызывающему достаточно отдать коллекцию разделов как есть
    $items = CategoryTree::flatten($items);

    // Деревьев на странице может быть несколько, общий id увёл бы их в одно поле
    $orderId = 'category-tree-order-' . uniqid();

    $tree = view('components.category-tree.node', [
        'items'    => $items,
        'row'      => $row,
        'sortable' => $sortable,
        'orderId'  => $orderId,
    ]);
@endphp

@if ($sortable)
    <form action="{{ $action }}" method="post" class="mb-3">
        @csrf

        {{-- Порядок собирает Sortable; без JS отправится порядок, отрисованный сервером --}}
        <input type="hidden" name="order" id="{{ $orderId }}"
               value="{{ $items->map(fn ($item) => $item->id . ':' . $item->parent_id)->implode(',') }}">

        {{ $tree }}

        <button class="btn btn-primary mt-3">{{ __('main.save_order') }}</button>
    </form>
@else
    <div class="mb-3">{{ $tree }}</div>
@endif
