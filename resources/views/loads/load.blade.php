@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page->current }})
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/loads/{{ $category->id }}?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/downs/create?cid={{ $category->id }}">Добавить</a>
        </div><br>
    @endif

    <h1>{{ $category->name }}</h1>

    Сортировать:

    <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
    <a href="/loads/{{ $category->id }}?sort=time" class="badge badge-{{ $active }}">По дате</a>

    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/loads/{{ $category->id }}?sort=loads" class="badge badge-{{ $active }}">Скачивания</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/loads/{{ $category->id }}?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/loads/{{ $category->id }}?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($page->current === 1 && $category->children->isNotEmpty())
        <div class="act">
            @foreach ($category->children as $child)
                <div class="b">
                    <i class="fa fa-folder-open"></i>
                    <b><a href="/loads/{{ $child->id }}">{{ $child->name }}</a></b> ({{ $child->count_downs }})</div>
            @endforeach
        </div>
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                Рейтинг: {{ $rating }}<br>
                Скачиваний: {{ $data->loads }}<br>
                <a href="/downs/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/downs/end/{{ $data->id }}">&raquo;</a>
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

    <a href="/loads/top">Топ файлов</a> /
    <a href="/loads/search?cid={{ $category->id }}">Поиск</a>
@stop
