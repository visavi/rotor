@extends('layout')

@section('title')
    {{ $blog->title }}
@stop

@section('keywords')
    {{ $blog->tags }}
@stop

@section('description')
    {{ stripString($blog->text) }}
@stop

@section('header')
    @if ($blog->user->id === getUser('id'))
        <div class="float-right">
            <a class="btn btn-success" href="/articles/edit/{{ $blog->id }}">Изменить</a>
        </div><br>
    @endif

    <h1>{{ $blog->title }} <small>(Оценка: {!! formatNum($blog->rating) !!})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>

            @if ($blog->category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item"><a href="/blogs/{{ $blog->category_id }}">{{ $blog->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $blog->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/articles/print/{{ $blog->id }}">Печать</a> /
    <a href="/articles/rss/{{ $blog->id }}">RSS-лента</a>

    @if (isAdmin())
        / <a href="/admin/articles/edit/{{ $blog->id }}">Редактировать</a> /
        <a href="/admin/articles/move/{{ $blog->id }}">Перенести</a> /
        <a href="/admin/articles/delete/{{ $blog->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
    @endif
    <hr>

    {!! $blog->text !!}

    @if ($page->total > 1)
        {!! pagination($page) !!}
    @endif

    Автор статьи: {!! $blog->user->getProfile() !!} ({{ dateFixed($blog->created_at) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}<hr>

    <div class="js-rating">Рейтинг:
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-down<?= $blog->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endif
        <span>{!! formatNum($blog->rating) !!}</span>
        @if (getUser() && getUser('id') !== $blog->user_id)
            <a class="post-rating-up<?= $blog->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endif
    </div>

    <i class="fa fa-eye"></i> Просмотров: {{ $blog->visits }}<br>
    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $blog->id }}">Комментарии</a> ({{  $blog->count_comments }})
    <a href="/articles/end/{{ $blog->id }}">&raquo;</a><br><br>
@stop
