@extends('layout')

@section('title')
    {{ $blog['title'] }} - @parent
@stop

@section('keywords')
    {{ $blog['tags'] }}
@stop

@section('description')
    {{ strip_str($blog['text']) }}
@stop

@section('content')

    <h1>{{ $blog['title'] }} <small>(Оценка: {!! format_num($blog['rating']) !!})</small></h1>

    <a href="/blog">Блоги</a> / <a href="/blog/{{ $blog['category_id'] }}">{{ $blog['name'] }}</a> / <a href="/article/{{ $blog['id'] }}/print">Печать</a> / <a href="/article/{{ $blog['id'] }}/rss">RSS-лента</a>

    @if ($blog->getUser()->id == getUserId())
         / <a href="/article/{{ $blog['id'] }}/edit">Изменить</a>
    @endif

    <br>

    @if (is_admin())
        <br> <a href="/admin/blog?act=editblog&amp;cid={{ $blog['category_id'] }}&amp;id={{ $blog['id'] }}">Редактировать</a> /
        <a href="/admin/blog?act=moveblog&amp;cid={{ $blog['category_id'] }}&amp;id={{ $blog['id'] }}">Переместить</a> /
        <a href="/admin/blog?act=delblog&amp;cid={{ $blog['category_id'] }}&amp;del={{ $blog['id'] }}&amp;uid={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
    @endif
    <hr>

    {!! $blog['text'] !!}

    {{ pagination($page) }}

    Автор статьи: {!! profile($blog['user']) !!} ({{ date_fixed($blog['created_at']) }})<br>

    <i class="fa fa-tag"></i> {!! $tags !!}

    <hr>

    <div class="js-rating">Рейтинг:
        @unless (getUserId() == $blog['user_id'])
            <a class="post-rating-down<?= $blog->vote == -1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog['id'] }}" data-type="{{ Blog::class }}" data-vote="-1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
        @endunless
        <span>{!! format_num($blog['rating']) !!}</span>
        @unless (getUserId() == $blog['user_id'])
            <a class="post-rating-up<?= $blog->vote == 1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $blog['id'] }}" data-type="{{ Blog::class }}" data-vote="1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
        @endunless
    </div>

    <i class="fa fa-eye"></i> Просмотров: {{ $blog['visits'] }}<br>
    <i class="fa fa-comment"></i> <a href="/article/{{ $blog['id'] }}/comments">Комментарии</a> ({{  $blog['comments'] }})
    <a href="/article/{{ $blog['id'] }}/end">&raquo;</a><br><br>
@stop
