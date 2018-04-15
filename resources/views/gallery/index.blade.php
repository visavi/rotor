@extends('layout')

@section('title')
    Галерея сайта (Стр. {{ $page->current }})
@stop

@section('content')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/gallery/create">Добавить фото</a><br>
        </div><br>
    @endif

    <h1>Галерея сайта</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Галерея сайта</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/gallery?page={{ $page->current }}">Управление</a></li>
            @endif
        </ol>
    </nav>

    @if (getUser())
        Мои:
        <a href="/gallery/album/{{ getUser('login') }}">фото</a>,
        <a href="/gallery/comments/{{ getUser('login') }}">комментарии</a> /
    @endif

    Все:
    <a href="/gallery/albums">альбомы</a>,
    <a href="/gallery/comments">комментарии</a> /
    <a href="/gallery/top">Топ фото</a>

    @if ($photos->isNotEmpty())
        @foreach ($photos as $data)

            <div class="b"><i class="fa fa-image"></i>
                <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b>
                ({{ formatFileSize(UPLOADS.'/pictures/'.$data->link) }}) (Рейтинг: {!! formatNum($data->rating) !!})
            </div>

            <div>
                <a href="/gallery/{{ $data->id }}">{!! resizeImage('/uploads/pictures/' . $data->link, ['alt' => $data->title]) !!}</a><br>

                @if ($data->text)
                    {!! bbCode($data->text) !!}<br>
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <a href="/gallery/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/gallery/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Фотографий нет, будь первым!') !!}
    @endif
@stop
