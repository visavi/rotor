{{--
    Дерево рисуется плоским списком: вложенность выражена отступом строки, а не
    вложенными <ul>. Так глубину можно менять горизонтальным движением мыши,
    и не нужно держать под каждым разделом пустую мишень для дропа
--}}
<ul class="sortable-tree-list"
    @if ($sortable) data-sortable-tree data-sortable-target="#{{ $orderId }}" @endif>
    @foreach ($items as $item)
        <li data-key="{{ $item->id }}" data-depth="{{ $item->depth }}" style="--depth: {{ $item->depth }}">
            <div class="sortable-row sortable-tree-item">
                @if ($sortable)
                    <span class="sortable-handle text-muted" data-sortable-handle title="{{ __('main.drag_reorder') }}">
                        <i class="fas fa-grip-vertical"></i>
                    </span>
                @endif

                @include($row, ['item' => $item])
            </div>
        </li>
    @endforeach
</ul>
