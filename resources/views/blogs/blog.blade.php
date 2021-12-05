@extends('layout')

@section('title', $category->name . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')
    @if (getUser())
        <div class="float-end">
            @if (! $category->closed)
                <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">{{ __('blogs.add') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/blogs/{{ $category->id }}?page={{ $articles->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        </div>
    @endif

    <h1>{{ $category->name }} <small>({{ __('blogs.all_articles') }}: {{ $category->count_articles }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-pencil-alt"></i>
                            <a href="/articles/{{ $article->id }}">{{ $article->title }}</a>
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        @if (getUser() && getUser('id') !== $article->user_id)
                            <a class="post-rating-down<?= $article->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                        @endif
                        <b>{{ formatNum($article->rating) }}</b>
                        @if (getUser() && getUser('id') !== $article->user_id)
                            <a class="post-rating-up<?= $article->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    {{ $article->shortText() }}
                    {{ __('main.author') }}: {{ $article->user->getProfile() }} ({{ dateFixed($article->created_at) }})<br>
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

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a>
@stop
