@forelse ($posts as $post)
    {!! $post !!}

    {{-- Реклама: одна на порцию ленты, и при подгрузке тоже --}}
    @if ($loop->iteration === 5)
        @hook('feedAdvert')
    @endif
@empty
    @include('feeds._welcome')
@endforelse

<div class="feed-pagination"
     data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}"
     data-empty="{{ $posts->isEmpty() ? '1' : '0' }}">
</div>
