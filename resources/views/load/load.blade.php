@extends('layout')

@section('title')
    {{ $category->name }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    @if (getUser() && ! $category->closed)
        <div class="float-right">
            <a class="btn btn-success" href="/down/create?cid={{ $category->id }}">Добавить файл</a>
        </div>
    @endif

    <h1>{{ $category->name }}</h1>

    <br>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

        @if ($category->parent)
            <li class="breadcrumb-item"><a href="/load/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item active">{{ $category->name }}</li>

        @if (isAdmin('admin'))
            <li class="breadcrumb-item"><a href="/admin/load?act=down&amp;cid={{ $category->id }}&amp;page={{ $page['current'] }}">Управление</a></li>
        @endif
    </ol>

    Сортировать:
    @if ($order == 'created_at')
        <b>По дате</b> /
    @else
        <a href="/load/{{ $category->id }}?sort=time">По дате</a> /
    @endif

    @if ($order == 'loads')
        <b>Скачивания</b> /
    @else
        <a href="/load/{{ $category->id }}?sort=loads">Скачивания</a> /
    @endif

    @if ($order == 'rated')
        <b>Оценки</b> /
    @else
        <a href="/load/{{ $category->id }}?sort=rated">Оценки</a> /
    @endif

    @if ($order == 'comments')
        <b>Комментарии</b>
    @else
        <a href="/load/{{ $category->id }}?sort=comments">Комментарии</a>
    @endif

    @if ($category->children->isNotEmpty() && $page['current'] == 1)
        <div class="act">
            @foreach ($category->children as $child)
                <div class="b">
                    <i class="fa fa-folder-open"></i>
                    <b><a href="/load/{{ $child->id }}">{{ $child->name }}</a></b> ({{ $child->count }})</div>
            @endforeach
        </div>
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        <?php $folder = $category->folder ? $category->folder.'/' : '' ?>

        @foreach ($downs as $data)
            <?php $filesize = $data->link ? formatFileSize(UPLOADS.'/files/'.$folder.$data->link) : 0; ?>

            <div class="b">
                <i class="fa fa-file-o"></i>
                <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $filesize }})</div>
            <div>

            Скачиваний: {{ $data->loads }}<br>

            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            Рейтинг: <b>{{ $rating }}</b> (Голосов: {{ $data->rated }})<br>
            <a href="/down/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
            <a href="/down/{{ $data->id }}/end">&raquo;</a></div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {{ showError('В данной категории еще нет файлов!') }}
        @endif
    @endif

    @if ($category->closed)
        {{ showError('В данной категории запрещена загрузка файлов!') }}
    @endif

    <a href="/load/top">Топ файлов</a> /
    <a href="/load/search">Поиск</a>
@stop
