@extends('layout')

@section('title', __('blogs.top_articles') . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')
    <h1>{{ __('blogs.top_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.top_articles') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.sort') }}:

    <?php $active = ($order === 'visits') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=visits" class="badge badge-{{ $active }}">{{ __('main.views') }}</a>

    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=rated" class="badge badge-{{ $active }}">{{ __('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/blogs/top?sort=comments" class="badge badge-{{ $active }}">{{ __('main.comments') }}</a>
    <hr>

    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <b><a href="/articles/{{ $article->id }}">{{ $article->title }}</a></b> ({!! formatNum($article->rating) !!})
                </div>

                <div class="section-content">
                    {{ __('blogs.blog') }}: <a href="/blogs/{{ $article->category_id }}">{{ $article->name }}</a><br>
                    {{ __('main.author') }}: {!! $article->user->getProfile() !!}<br>
                    {{ __('main.views') }}: {{ $article->visits }}<br>
                    <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                    <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $articles->links() }}
@stop
