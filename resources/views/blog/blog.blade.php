@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page->current }})
@stop

@section('content')

    @if (getUser() && ! $category->closed)
        <div class="float-right">
            <a class="btn btn-success" href="/blog/create?cid={{ $category->id }}">Добавить статью</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>(Статей: {{ $category->count_blogs }})</small></h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/blog/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/blog/{{ $category->id }}?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>
            <div>
                Автор: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                Просмотров: {{ $data->visits }}<br>
                <a href="/article/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/article/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Статей еще нет, будь первым!') !!}
    @endif

    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blogs">Все статьи</a>
@stop
