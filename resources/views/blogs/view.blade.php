@extends('layout')

@section('title')
    {{ $blog->title }}
@stop

@section('description', truncateDescription(bbCode($blog->text, false)))

@section('header')
    @if ($blog->user->id === getUser('id'))
        <div class="float-right">
            <a class="btn btn-success" href="/articles/edit/{{ $blog->id }}">{{ __('main.change') }}</a>
        </div><br>
    @endif

    <h1>{{ $blog->title }} <small>({{ __('main.rating') }}: {!! formatNum($blog->rating) !!})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category_id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $blog->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/articles/print/{{ $blog->id }}">{{ __('main.print') }}</a> /
    <a href="/articles/rss/{{ $blog->id }}">{{ __('main.rss') }}</a>

    @if (isAdmin())
        / <a href="/admin/articles/edit/{{ $blog->id }}">{{ __('main.edit') }}</a> /
        <a href="/admin/articles/move/{{ $blog->id }}">{{ __('main.move') }}</a> /
        <a href="/admin/articles/delete/{{ $blog->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')">{{ __('main.delete') }}</a>
    @endif
    <hr>

    {!! $blog->text !!}
    {{ __('main.author') }}: {!! $blog->user->getProfile() !!} ({{ dateFixed($blog->created_at) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}<hr>

    <div class="js-rating">{{ __('main.rating') }}:
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-down<?= $blog->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endif
        <span>{!! formatNum($blog->rating) !!}</span>
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-up<?= $blog->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endif
    </div>

    <i class="fa fa-eye"></i> {{ __('main.views') }}: {{ $blog->visits }}<br>
    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $blog->id }}">{{ __('main.comments') }}</a> ({{  $blog->count_comments }})
    <a href="/articles/end/{{ $blog->id }}">&raquo;</a><br><br>
@stop
