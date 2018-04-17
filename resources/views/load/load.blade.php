@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page->current }})
@stop

@section('content')

    @if (getUser() && ! $category->closed)
        <div class="float-right">
            <a class="btn btn-success" href="/down/create?cid={{ $category->id }}">Добавить файл</a>
        </div>
    @endif

    <h1>{{ $category->name }}</h1><br>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/load/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/load/{{ $category->id }}?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    Сортировать:

    <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
    <a href="/load/{{ $category->id }}?sort=time" class="badge badge-{{ $active }}">По дате</a>

    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/load/{{ $category->id }}?sort=loads" class="badge badge-{{ $active }}">Скачивания</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/load/{{ $category->id }}?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/load/{{ $category->id }}?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($category->children->isNotEmpty() && $page->current == 1)
        <div class="act">
            @foreach ($category->children as $child)
                <div class="b">
                    <i class="fa fa-folder-open"></i>
                    <b><a href="/load/{{ $child->id }}">{{ $child->name }}</a></b> ({{ $child->count_downs }})</div>
            @endforeach
        </div>
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $rating }})
            </div>

            <div>
                Скачиваний: {{ $data->loads }}<br>
                <a href="/down/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/down/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {!! showError('В данной категории еще нет файлов!') !!}
        @endif
    @endif

    @if ($category->closed)
        {!! showError('В данной категории запрещена загрузка файлов!') !!}
    @endif

    <a href="/load/top">Топ файлов</a> /
    <a href="/load/search">Поиск</a>
@stop
