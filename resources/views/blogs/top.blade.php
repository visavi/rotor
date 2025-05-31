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
    @if ($articles->isNotEmpty())
        {{ __('main.sort') }}:

            <?php $active = ($order === 'visits') ? 'success' : 'adaptive'; ?>
        <a href="/blogs/top?sort=visits" class="badge bg-{{ $active }}">{{ __('main.views') }}</a>

            <?php $active = ($order === 'rating') ? 'success' : 'adaptive'; ?>
        <a href="/blogs/top?sort=rating" class="badge bg-{{ $active }}">{{ __('main.rating') }}</a>

            <?php $active = ($order === 'count_comments') ? 'success' : 'adaptive'; ?>
        <a href="/blogs/top?sort=comments" class="badge bg-{{ $active }}">{{ __('main.comments') }}</a>
        <hr>

        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> ({{ formatNum($article->rating) }})
                </div>

                <div class="section-content">
                    {{ __('blogs.blog') }}: <a href="/blogs/{{ $article->category_id }}">{{ $article->name }}</a><br>
                    {{ __('main.author') }}: {{ $article->user->getProfile() }}<br>
                    {{ __('main.views') }}: {{ $article->visits }}<br>
                    <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                    <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    {{ $articles->links() }}
@stop
