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
            @if ($post->getMorphClass() === 'post')
                @include('search/_posts')
            @endif

            @if ($post->getMorphClass() === 'article')
                @include('search/_articles')
            @endif

            @if ($post->getMorphClass() === 'comment')
                @include('search/_comments')
            @endif
        @endforeach

        {{ $posts->links() }}
    @endif
@stop
