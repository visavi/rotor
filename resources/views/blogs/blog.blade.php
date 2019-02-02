@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page->current }})
@stop

@section('header')
    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">Добавить</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>(Статей: {{ $category->count_blogs }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->id }}?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>
            <div>
                {!! stripString(bbCode($data->text), 50) !!}<br>
                Автор: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                Просмотров: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Статей еще нет, будь первым!') !!}
    @endif

    <a href="/blogs/top">Топ статей</a> /
    <a href="/blogs/tags">Облако тегов</a> /
    <a href="/blogs/search">Поиск</a> /
    <a href="/blogs/authors">Авторы</a>
@stop
