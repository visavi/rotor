@extends('layout')

@section('title')
    Все публикации ({{ __('main.page_num', ['page' => $articles->currentPage()]) }})
@stop

@section('header')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create">{{ __('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>Все публикации</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">Все публикации</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="card-title"><a href="/articles/{{ $article->id }}">{{ $article->title }}</a> <small>(Рейтинг: {!! formatNum($article->rating) !!})</small></h2>

                    @if ($article->category->parent->id)
                        <a href="/blogs/{{ $category->parent->id }}"><span class="badge badge-light">{{ $category->parent->name }}</span></a> /
                    @endif

                    <a href="/blogs/{{ $article->category->id }}"><span class="badge badge-light">{{ $article->category->name }}</span></a>

                    <p class="card-text">
                        {!! $article->shortText() !!}
                    </p>
                </div>
                <div class="card-footer text-muted">
                    {{ __('main.author') }}: {!! $article->user->getProfile() !!} ({{ dateFixed($article->created_at) }})
                    {{ __('main.views') }}: {{ $article->visits }}
                    <div class="float-right">
                        <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                        <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $articles->links() }}

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a>
@stop
