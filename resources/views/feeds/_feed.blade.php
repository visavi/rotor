@if ($posts->currentPage() > 1)
<div class="d-flex justify-content-center feed-pagination-top feed-pagination-top--initial">
    {{ $posts->links('feeds._paginator') }}
</div>
@endif

@forelse ($posts as $post)
    @includeIf(\App\Models\Feed::$viewMap[$post->getMorphClass()] ?? 'feeds._' . $post->getMorphClass())
@empty
    {{ showError(__('forums.empty_posts')) }}
@endforelse

<div class="d-flex justify-content-center feed-pagination" data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}">
    {{ $posts->links('feeds._paginator') }}
</div>
