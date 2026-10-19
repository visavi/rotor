{{-- Места для рекламы только в первой порции: в начале, в середине и в конце. Какое занять, решает модуль.
     Середина и конец — не ближе трёх записей к соседней точке, чтобы короткая лента не превращалась в рекламу --}}
@php
    $advertPoints = [];

    if ($posts->currentPage() === 1) {
        $count = $posts->count();

        if ($count >= 6) {
            $advertPoints[intdiv($count, 2)] = 'middle';
        }

        if ($count >= 3) {
            $advertPoints[$count] = 'end';
        }
    }
@endphp

@forelse ($posts as $post)
    @if ($posts->currentPage() === 1 && $loop->first)
        @hook('feedAdvert', 'start')
    @endif

    {!! $post !!}

    @if (isset($advertPoints[$loop->iteration]))
        @hook('feedAdvert', $advertPoints[$loop->iteration])
    @endif
@empty
    @include('feeds._welcome')
@endforelse

<div class="feed-pagination"
     data-next="{{ $posts->hasMorePages() ? route('feed', ['page' => $posts->currentPage() + 1]) : '' }}"
     data-empty="{{ $posts->isEmpty() ? '1' : '0' }}">
</div>
