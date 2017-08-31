@extends('layout')

@section('title')
    Топ популярных фотографий - @parent
@stop

@section('content')

    <h1>Топ популярных фотографий</h1>

    @if ($photos->isNotEmpty())

        Сортировать:

        @if ($order == 'rating')
            <b><a href="/gallery/top?sort=rating">Оценки</a></b>,
        @else
            <a href="/gallery/top?sort=rating">Оценки</a>,
        @endif

        @if ($order == 'comments')
            <b><a href="/gallery/top?sort=comments">Комментарии</a></b>
        @else
            <a href="/gallery/top?sort=comments">Комментарии</a>
        @endif

        <hr>

        @foreach ($photos as $data)
            <div class="b">
                <i class="fa fa-picture-o"></i>
                <b><a href="/gallery/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ formatFileSize(HOME.'/uploads/pictures/'.$data['link']) }}) ({!! format_num($data['rating']) !!})
            </div>

            <div><a href="/gallery/{{ $data['id'] }}">{!! resize_image('uploads/pictures/', $data['link'], setting('previewsize'), ['alt' => $data['title']]) !!}</a>

                <br>{!! bbCode($data['text']) !!}<br>

                Добавлено: {!! profile($data['user']) !!} ({{ dateFixed($data['time']) }})<br>
                <a href="/gallery/{{ $data['id'] }}/comments">Комментарии</a> ({{ $data['comments'] }})
                <a href="/gallery/{{ $data['id'] }}/end">&raquo;</a>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Загруженных фотографий еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
