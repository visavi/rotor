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

@section('content')

    @if ($blog->user->id == getUser('id'))
        <div class="float-right">
            <a class="btn btn-success" href="/article/edit/{{ $blog->id }}">Изменить</a>
        </div>
    @endif

    <h1>{{ $blog->title }} <small>(Оценка: {!! formatNum($blog->rating) !!})</small></h1><br>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>

        @if ($blog->category->parent)
            <li class="breadcrumb-item"><a href="/blog/{{ $blog->category->parent->id }}">{{ $blog->category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/blog/{{ $blog->category_id }}">{{ $blog->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $blog->title }}</li>
        <li class="breadcrumb-item"><a href="/article/print/{{ $blog->id }}">Печать</a></li>
        <li class="breadcrumb-item"><a href="/article/rss/{{ $blog->id }}">RSS-лента</a></li>
    </ol>

    @if (isAdmin())
        <a href="/admin/blog?act=editblog&amp;cid={{ $blog->category_id }}&amp;id={{ $blog->id }}">Редактировать</a> /
        <a href="/admin/blog?act=moveblog&amp;cid={{ $blog->category_id }}&amp;id={{ $blog->id }}">Переместить</a> /
        <a href="/admin/blog?act=delblog&amp;cid={{ $blog->category_id }}&amp;del={{ $blog->id }}&amp;uid={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
    @endif
    <hr>

    {!! $blog->text !!}

    {!! pagination($page) !!}

    Автор статьи: {!! profile($blog->user) !!} ({{ dateFixed($blog->created_at) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}<hr>

    <div class="js-rating">Рейтинг:
        @unless (getUser('id') == $blog->user_id)
            <a class="post-rating-down<?= $blog->vote == '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endunless
        <span>{!! formatNum($blog->rating) !!}</span>
        @unless (getUser('id') == $blog->user_id)
            <a class="post-rating-up<?= $blog->vote == '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog->id }}" data-type="{{ App\Models\Blog::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endunless
    </div>

    <i class="fa fa-eye"></i> Просмотров: {{ $blog->visits }}<br>
    <i class="fa fa-comment"></i> <a href="/article/comments/{{ $blog->id }}">Комментарии</a> ({{  $blog->comments }})
    <a href="/article/end/{{ $blog->id }}">&raquo;</a><br><br>
@stop
