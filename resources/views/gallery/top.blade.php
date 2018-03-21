@extends('layout')

@section('title')
    Топ популярных фотографий
@stop

@section('content')

    <h1>Топ популярных фотографий</h1>

    @if ($photos->isNotEmpty())

        Сортировать:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/gallery/top?sort=rating" class="badge badge-{{ $active }}">Оценки</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/gallery/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
        <hr>


        @foreach ($photos as $data)
            <div class="b">
                <i class="fa fa-image"></i>
                <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b> ({{ formatFileSize(UPLOADS.'/pictures/'.$data->link) }}) ({!! formatNum($data->rating) !!})
            </div>

            <div><a href="/gallery/{{ $data->id }}">{!! resizeImage('uploads/pictures/', $data->link, ['alt' => $data->title]) !!}</a>

                <br>{!! bbCode($data->text) !!}<br>

                Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <a href="/gallery/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/gallery/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Загруженных фотографий еще нет!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
