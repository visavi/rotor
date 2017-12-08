@extends('layout')

@section('title')
    Галерея сайта (Стр. {{ $page['current'] }})
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/gallery/create">Добавить фото</a><br>
        </div>
    @endif

    <h1>Галерея сайта</h1>

    <br>
    <ol class="breadcrumb">

        @if (getUser())
            <li class="breadcrumb-item"><a href="/gallery/album/{{ getUser('login') }}">Мои альбом</a></li>
            <li class="breadcrumb-item"><a href="/gallery/comments/{{ getUser('login') }}">Мои комментарии</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/gallery/albums">Все альбомы</a></li>
        <li class="breadcrumb-item"><a href="/gallery/comments">Все комментарии</a></li>
        <li class="breadcrumb-item"><a href="/gallery/top">Топ фото</a></li>

        @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/gallery?page={{ $page['current'] }}">Управление</a></li>
        @endif
    </ol>

    @if ($photos->isNotEmpty())
        @foreach ($photos as $data)

            <div class="b"><i class="fa fa-image"></i>
                <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b>
                ({{ formatFileSize(UPLOADS.'/pictures/'.$data->link) }}) (Рейтинг: {!! formatNum($data->rating) !!})
            </div>

            <div>
                <a href="/gallery/{{ $data->id }}">{!! resizeImage('uploads/pictures/', $data->link, setting('previewsize'), ['alt' => $data->title]) !!}</a><br>

                @if ($data->text)
                    {!! bbCode($data->text) !!}<br>
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <a href="/gallery/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
                <a href="/gallery/{{ $data->id }}/end">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page['total'] }}</b><br><br>

    @else
        {!! showError('Фотографий нет, будь первым!') !!}
    @endif
@stop
