@extends('layout')

@section('title')
    {{ $category['name'] }} (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    @if (is_user())
        <div class="float-right">
            <a class="btn btn-success" href="/blog/create?cid={{ $category['id'] }}">Добавить статью</a>
        </div>
    @endif

    <h1>{{ $category['name'] }} <small>(Статей: {{ $category['count'] }})</small></h1>
    <a href="/blog">Блоги</a>

    @if (is_admin())
        / <a href="/admin/blog?act=blog&amp;cid={{ $category['id'] }}&amp;page={{ $page['current'] }}">Управление</a>
    @endif
    <hr>

    @if ($blogs)
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil"></i>
                <b><a href="/article/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({!! format_num($data['rating']) !!})
            </div>
            <div>
                Автор: {!! profile($data['user']) !!} ({{ date_fixed($data['created_at']) }})<br>
                Просмотров: {{ $data['visits'] }}<br>
                <a href="/article/{{ $data['id'] }}/comments">Комментарии</a> ({{ $data['comments'] }})
                <a href="/article/{{ $data['id'] }}/end">&raquo;</a>
            </div>
        @endforeach

        {{ App::pagination($page) }}
    @else
        {{ show_error('Статей еще нет, будь первым!') }}
    @endif

    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blog?act=blogs">Все статьи</a> /
@stop
