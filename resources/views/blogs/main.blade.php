@extends('layout')

@section('title', __('blogs.articles_all') . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')

    @if (getUser())
        <div class="float-end">
            <a class="btn btn-success" href="/blogs/create">{{ __('blogs.add') }}</a>
        </div>
    @endif

    <h1>{{ __('blogs.articles_all') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.articles_all') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a> <small>(Рейтинг: {{ formatNum($article->rating) }})</small></h5>

                    @if ($article->category->parent->id)
                        <a href="/blogs/{{ $article->category->parent->id }}"><span class="badge bg-light text-dark">{{ $article->category->parent->name }}</span></a> /
                    @endif

                    <a href="/blogs/{{ $article->category->id }}"><span class="badge bg-light text-dark">{{ $article->category->name }}</span></a>

                    <p class="card-text">
                        {{ $article->shortText() }}
                    </p>
                </div>
                <div class="card-footer text-muted">
                    {{ __('main.author') }}: {{ $article->user->getProfile() }} ({{ dateFixed($article->created_at) }})
                    {{ __('main.views') }}: {{ $article->visits }}
                    <div class="float-end">
                        <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                        <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                    </div>
                </div>
            </div>
        @endforeach

        {{ $articles->links() }}
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a>
@stop
