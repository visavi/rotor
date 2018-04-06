@extends('layout')

@section('title')
    Топ статей (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Топ статей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>
            <li class="breadcrumb-item active">Топ статей</li>
        </ol>
    </nav>

    Сортировать:

    <?php $active = ($order === 'visits') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=visits" class="badge badge-{{ $active }}">Просмотры</a>

    <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/blog/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)

            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>
                Автор: {!! profile($data->user) !!}<br>
                Категория: <a href="/blog/{{ $data->category_id }}">{{ $data->name }}</a><br>
                Просмотров: {{ $data->visits }}<br>
                <a href="/article/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/article/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных статей еще нет!') !!}
    @endif
@stop
