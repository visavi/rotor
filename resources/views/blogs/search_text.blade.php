@extends('layout')

@section('title', __('main.search_request') . ' ' . $find)

@section('header')
    <h1>{{ __('main.search_request') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item"><a href="/blogs/search">{{ __('main.search') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>{{ __('blogs.found_in_text') }}: {{ $articles->total() }}</p>

    @foreach ($articles as $article)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-pencil-alt"></i>
                <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> ({!! formatNum($article->rating) !!})
            </div>

            <div class="section-content">
                {!! $article->shortText() !!}
                {{ __('blogs.blog') }}: <a href="/blogs/{{ $article->category_id }}">{{ $article->name }}</a><br>
                {{ __('main.author') }}: {!! $article->user->getProfile() !!}  ({{ dateFixed($article->created_at) }})
            </div>
        </div>
    @endforeach

    {{ $articles->links() }}
@stop
