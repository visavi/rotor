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
    <div class="section-form mb-3 shadow">
        <form action="{{ route('search') }}" method="get" id="search-form">
            <div class="input-group mb-3">
                <input type="search" class="form-control" id="query" name="query" minlength="3" maxlength="64" placeholder="{{ __('main.search') }}..." value="{{ $query }}" required>
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>

            <!-- Блок фильтров -->
            <div class="row g-2">
                <div class="col-md-6">
                    <label for="type" class="form-label">{{ __('main.where_to_look') }}:</label>
                    <select id="type" class="form-select" name="type" onchange="this.form.submit()">
                        <option value="">{{ __('main.everywhere') }}</option>
                        @foreach($types as $typeKey => $typeName)
                            <option value="{{ $typeKey }}" {{ $type === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="sort" class="form-label">{{ __('main.sort') }}:</label>
                    <select id="sort" class="form-select" name="sort" onchange="this.form.submit()">
                        <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>{{ __('main.relevance') }}</option>
                        <option value="date" {{ $sort === 'date' ? 'selected' : '' }}>{{ __('main.new_first') }}</option>
                        <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>{{ __('main.old_first') }}</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    @if ($query)
        <h2>{{ __('main.search_results', ['query' => $query]) }}</h2>

        <p>{{ __('main.total_found') }}: {{ $posts->total() }}</p>
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
