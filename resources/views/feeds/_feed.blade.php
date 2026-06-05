@use('App\Classes\Registry')
@forelse ($posts as $post)
    @includeIf(Registry::$feeds[$post->getMorphClass()]['view'] ?? 'feeds._' . $post->getMorphClass())
@empty
    @if ($posts->currentPage() === 1)
        @include('feeds._welcome')
    @endif
@endforelse

<div class="feed-pagination"
     data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}"
     data-empty="{{ $posts->isEmpty() ? '1' : '0' }}">
</div>
