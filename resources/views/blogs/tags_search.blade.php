@extends('layout')

@section('title', __('main.search_results', ['query' => $tag]))
@section('description', __('main.search_results', ['query' => $tag]))
@section('header')
    <h1>{{ __('blogs.title_tags') }}</h1>
@stop


@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.tags') }}">{{ __('blogs.tag_cloud') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_tags') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h2>{{ __('main.search_results', ['query' => $tag]) }}</h2>

    <p>{{ __('main.total_found') }}: {{ $articles->total() }}</p>

    @foreach ($articles as $article)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-pencil-alt"></i>
                <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ formatNum($article->rating) }}</span>
            </div>

            <div class="section-content">
                {{ __('blogs.blog') }}: <a href="{{ route('blogs.blog', ['id' => $article->category_id]) }}">{{ $article->name }}</a><br>
                {{ __('main.views') }}: {{ $article->visits }}<br>

                <div class="mb-3">
                    {{ __('blogs.tags') }}:
                    @foreach ($article->tags as $tag)
                        {{ $tag->name }}{{ ! $loop->last ? ', ' : '' }}
                    @endforeach
                </div>

                {{ __('main.author') }}: {{ $article->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small>
            </div>
        </div>
    @endforeach

    {{ $articles->links() }}
@stop
