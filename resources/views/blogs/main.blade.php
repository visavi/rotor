@extends('layout')

@section('title', __('blogs.articles_all') . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')

    @if (getUser())
        <div class="float-end">
            <a class="btn btn-success" href="{{ route('blogs.create') }}">{{ __('blogs.add') }}</a>
        </div>
    @endif

    <h1>{{ __('blogs.articles_all') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.articles_all') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><a href="{{ route('articles.view', ['id' => $article->id]) }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ formatNum($article->rating) }}</span></h5>

                    @if ($article->category->parent->id)
                        <a href="{{ route('blogs.blog', ['id' => $article->category->parent->id]) }}">{{ $article->category->parent->name }}</a> /
                    @endif

                    <a href="{{ route('blogs.blog', ['id' => $article->category->id]) }}">{{ $article->category->name }}</a>

                    <p class="card-text">
                        {{ $article->shortText() }}
                    </p>
                </div>
                <div class="card-footer text-muted">
                    {{ __('main.author') }}: {{ $article->user->getProfile() }} ({{ dateFixed($article->created_at) }})
                    {{ __('main.views') }}: {{ $article->visits }}
                    <div class="float-end">
                        <a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        {{ $articles->links() }}
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    <a href="{{ route('blogs.tags') }}">{{ __('blogs.tag_cloud') }}</a> /
    <a href="{{ route('blogs.authors') }}">{{ __('blogs.authors') }}</a>
@stop
