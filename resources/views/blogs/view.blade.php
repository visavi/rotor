@extends('layout')

@section('title', $article->title)

@section('description', truncateDescription(bbCode($article->text, false)))

@section('header')
    @if (getUser() && getUser('id') === $article->user->id)
        <div class="float-right">
            <a class="btn btn-success" href="/articles/edit/{{ $article->id }}">{{ __('main.change') }}</a>
        </div><br>
    @endif

    <h1>{{ $article->title }} <small>({{ __('main.rating') }}: {!! formatNum($article->rating) !!})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @if ($article->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $article->category->parent->id }}">{{ $article->category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="/blogs/{{ $article->category_id }}">{{ $article->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $article->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/articles/print/{{ $article->id }}">{{ __('main.print') }}</a> /
    <a href="/articles/rss/{{ $article->id }}">{{ __('main.rss') }}</a>

    @if (isAdmin())
        / <a href="/admin/articles/edit/{{ $article->id }}">{{ __('main.edit') }}</a> /
        <a href="/admin/articles/move/{{ $article->id }}">{{ __('main.move') }}</a> /
        <a href="/admin/articles/delete/{{ $article->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')">{{ __('main.delete') }}</a>
    @endif
    <hr>

    {!! $article->text !!}
    {{ __('main.author') }}: {!! $article->user->getProfile() !!} ({{ dateFixed($article->created_at) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}<hr>

    <div class="js-rating">{{ __('main.rating') }}:
        @if (getUser() && getUser('id') !== $article->user_id)
            <a class="post-rating-down<?= $article->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endif
        <b>{!! formatNum($article->rating) !!}</b>
        @if (getUser() && getUser('id') !== $article->user_id)
            <a class="post-rating-up<?= $article->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endif
    </div>

    <i class="fa fa-eye"></i> {{ __('main.views') }}: {{ $article->visits }}<br>
    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{  $article->count_comments }})
    <a href="/articles/end/{{ $article->id }}">&raquo;</a><br><br>
@stop
