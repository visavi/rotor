@if ($paginator->hasPages())
    @php
        $current  = $paginator->currentPage();
        $last     = $paginator->lastPage();
        $pageUrl  = fn(int $page) => $page === 1 ? url('/') : $paginator->url($page);
    @endphp
    <nav>
        <ul class="pagination pagination-feed mb-0 flex-wrap mb-3">
            {{-- Первая --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $pageUrl(1) }}">&laquo;</a>
            </li>

            {{-- Предыдущая --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $pageUrl($current - 1) }}">&lsaquo;</a>
            </li>

            {{-- Страница current-1 --}}
            @if ($current > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $pageUrl($current - 1) }}">{{ $current - 1 }}</a>
                </li>
            @endif

            {{-- Текущая --}}
            <li class="page-item active">
                <span class="page-link">{{ $current }}</span>
            </li>

            {{-- Страница current+1 --}}
            @if ($current < $last)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($current + 1) }}">{{ $current + 1 }}</a>
                </li>
            @endif

            {{-- Следующая --}}
            <li class="page-item {{ ! $paginator->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() ?? '#' }}">&rsaquo;</a>
            </li>

            {{-- Последняя --}}
            <li class="page-item {{ ! $paginator->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->url($last) }}">&raquo;</a>
            </li>
        </ul>
    </nav>
@endif
