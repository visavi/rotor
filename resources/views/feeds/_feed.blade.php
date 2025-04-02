@forelse ($posts as $post)
    {{-- Посты --}}
    @if ($post instanceof \App\Models\Topic)
        @include('feeds/_topics')
    @endif

    {{-- Новости --}}
    @if ($post instanceof \App\Models\News)
        @include('feeds/_news')
    @endif

    {{-- Галерея --}}
    @if ($post instanceof \App\Models\Photo)
        @include('feeds/_photos')
    @endif

    {{-- Статьи --}}
    @if ($post instanceof \App\Models\Article)
        @include('feeds/_articles')
    @endif

    {{-- Загрузки --}}
    @if ($post instanceof \App\Models\Down)
        @include('feeds/_downs')
    @endif

    {{-- Объявления --}}
    @if ($post instanceof \App\Models\Item)
        @include('feeds/_boards')
    @endif

    {{-- Предложения / проблемы --}}
    @if ($post instanceof \App\Models\Offer)
        @include('feeds/_offers')
    @endif

    {{-- Комментарии --}}
    @if ($post instanceof \App\Models\Comment)
        @include('feeds/_comments')
    @endif
@empty
    {{ showError(__('forums.empty_posts')) }}
@endforelse

<div class="d-flex justify-content-center">
    {{ $posts->links() }}
</div>
