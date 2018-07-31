@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page->current }})
@stop

@section('content')

    @if (getUser() && ! $category->closed)
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">Добавить</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>(Статей: {{ $category->count_blogs }})</small></h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">Блоги</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->id }}?page={{ $page->current }}">Обзор</a></li>
            @endif
        </ol>
    </nav>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})

                <div class="float-right">
                    <a href="/admin/articles/edit/{{ $data->id }}" title="Редактировать"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/articles/move/{{ $data->id }}" title="Перенести"><i class="fa fa-arrows-alt text-muted"></i></a>
                    <a href="/admin/articles/delete/{{ $data->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную статью?')" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                </div>

            </div>
            <div>
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
@stop
