@if ($paginator->hasPages())
    <nav>
    <ul class="pagination">
        {{-- Previous Page Link --}}
        {{--@if ($paginator->onFirstPage())
            <li class="page-item disabled d-none d-md-block"><span class="page-link">«</span></li>
        @else
            <li class="page-item d-none d-md-block"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a></li>
        @endif--}}

        @if ($paginator->currentPage() > 3)
            <li class="page-item"><a class="page-link" href="{{ preg_replace('/(\?|\&)page=[1]$/', '', $paginator->url(1)) }}">1</a></li>
        @endif

        @if ($paginator->currentPage() > 4)
            <li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>
        @endif

        @php
            $startPage = ($paginator->currentPage() - 2) < 1 ? 1 : ($paginator->currentPage() - 2);
            $endPage   = ($paginator->currentPage() + 2) > $paginator->lastPage() ? $paginator->lastPage() : ($paginator->currentPage() + 2);
            $startPage = $startPage > $endPage ? $endPage : $startPage;
        @endphp

        @foreach (range($startPage, $endPage) as $page)
            @if ($page === $paginator->currentPage())
                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ preg_replace('/(\?|\&)page=[1]$/', '', $paginator->url($page)) }}">{{ $page }}</a></li>
            @endif
        @endforeach

        @if ($paginator->currentPage() < $paginator->lastPage() - 3)
            <li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>
        @endif

        @if ($paginator->currentPage() < $paginator->lastPage() - 2)
            <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
        @endif

        {{-- Next Page Link --}}
        {{--@if ($paginator->hasMorePages())
            <li class="page-item d-none d-md-block"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">»</a></li>
        @else
            <li class="page-item disabled d-none d-md-block"><span class="page-link">»</span></li>
        @endif--}}
    </ul>
    </nav>
@endif
