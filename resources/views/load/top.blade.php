@extends('layout')

@section('title')
    Топ популярных файлов (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Топ популярных файлов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item active">Топ файлов</li>
        </ol>
    </nav>

    Сортировать:

    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/load/top?sort=loads" class="badge badge-{{ $active }}">Скачивания</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/load/top?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/load/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($downs->isNotEmpty())

        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $rating }})
            </div>

            <div>
                Категория: <a href="/load/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                Скачиваний: {{ $data->loads }}<br>
                <a href="/down/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/down/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {!! showError('Файлы не найдены!') !!}
        @endif
    @endif
@stop
