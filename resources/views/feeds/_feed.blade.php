@use('App\Classes\Registry')
@if ($posts->currentPage() > 1)
    <div class="d-flex justify-content-center feed-pagination-top feed-pagination-top--initial">
        {{ $posts->links('feeds._paginator') }}
    </div>
@endif

@forelse ($posts as $post)
    @includeIf(Registry::$feeds[$post->getMorphClass()]['view'] ?? 'feeds._' . $post->getMorphClass())
@empty
    @if ($posts->currentPage() === 1)
        @include('feeds._welcome')
    @endif
@endforelse

<div class="d-flex justify-content-center feed-pagination"
     data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}"
     data-empty="{{ $posts->isEmpty() ? '1' : '0' }}">
    {{ $posts->links('feeds._paginator') }}
</div>
