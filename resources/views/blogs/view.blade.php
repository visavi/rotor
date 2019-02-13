@extends('layout')

@section('title')
    {{ $blog->title }}
@stop

@section('keywords')
    {{ $blog->tags }}
@stop

@section('description')
    {{ stripString(bbCode($blog->text)) }}
@stop

@section('header')
    @if ($blog->user->id === getUser('id'))
        <div class="float-right">
            <a class="btn btn-success" href="/articles/edit/{{ $blog->id }}">{{ trans('common.change') }}</a>
        </div><br>
    @endif

    <h1>{{ $blog->title }} <small>({{ trans('common.ratings') }}: {!! formatNum($blog->rating) !!})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category_id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $blog->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/articles/print/{{ $blog->id }}">{{ trans('common.print') }}</a> /
    <a href="/articles/rss/{{ $blog->id }}">{{ trans('common.rss') }}</a>

    @if (isAdmin())
        / <a href="/admin/articles/edit/{{ $blog->id }}">{{ trans('common.edit') }}</a> /
        <a href="/admin/articles/move/{{ $blog->id }}">{{ trans('common.move') }}</a> /
        <a href="/admin/articles/delete/{{ $blog->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('blogs.confirm_delete_article') }}')">{{ trans('common.delete') }}</a>
    @endif
    <hr>

    {!! $blog->text !!}

    @if ($page->total > 1)
        {!! pagination($page) !!}
    @endif

    {{ trans('common.author') }}: {!! $blog->user->getProfile() !!} ({{ dateFixed($blog->created_at) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}<hr>

    <div class="js-rating">{{ trans('common.ratings') }}:
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-down<?= $blog->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endif
        <span>{!! formatNum($blog->rating) !!}</span>
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-up<?= $blog->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endif
    </div>

    <i class="fa fa-eye"></i> {{ trans('common.views') }}: {{ $blog->visits }}<br>
    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $blog->id }}">{{ trans('common.comments') }}</a> ({{  $blog->count_comments }})
    <a href="/articles/end/{{ $blog->id }}">&raquo;</a><br><br>
@stop
