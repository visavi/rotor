@forelse ($posts as $post)
    {!! $post !!}
@empty
    @include('feeds._welcome')
@endforelse

<div class="feed-pagination"
     data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}"
     data-empty="{{ $posts->isEmpty() ? '1' : '0' }}">
</div>
