@extends('layout')

@section('title', __('index.search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <form action="{{ route('search') }}" method="get">
            <div class="input-group">
                <input type="search" class="form-control" id="query" name="query" maxlength="64" placeholder="Поиск..." value="{{ $query }}" required>
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
        </form>
    </div>

    @if ($query)
        <h2>Результаты поиска "{{ $query }}"</h2>

        <p>Найдено результатов: {{ $posts->total() }}</p>
    @endif

    @if ($posts->isNotEmpty())
        @foreach($posts as $post)
            @php
                $post = $post->relate;
            @endphp

            @includeIf('search/_' . $post->getMorphClass())
        @endforeach

        {{ $posts->links() }}
    @endif
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            // Получаем поисковый запрос из URL
            const query = new URLSearchParams(window.location.search).get('query');

            if (query) {
                const searchWords = query.split(' ')
                    .filter(word => word.length >= 3)
                    .filter((word, index, self) => self.indexOf(word) === index);

                if (searchWords.length > 0) {
                    const regexPattern = '(' + searchWords.join('|') + ')';
                    const regex = new RegExp(regexPattern, 'gi');

                    $('.section').each(function() {
                        const originalHtml = $(this).html();
                        const highlightedHtml = originalHtml.replace(regex, '<mark>$1</mark>');
                        $(this).html(highlightedHtml);
                    });
                }
            }
        });
    </script>
@endpush
