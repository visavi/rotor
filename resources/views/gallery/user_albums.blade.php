@extends('layout')

@section('title')
    Альбом {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Альбом {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item active">Альбом {{ $user->login }}</li>
        </ol>
    </nav>

    @if ($photos->isNotEmpty())

        @foreach ($photos as $data)
            <div class="b">
                <i class="fa fa-image"></i>
                <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b> ({{ formatFileSize(UPLOADS.'/pictures/'.$data->link) }})<br>

                @if ($moder)
                    <a href="/gallery/edit/{{ $data->id }}?page={{ $page->current }}">Редактировать</a> /
                    <a href="/gallery/delete/{{ $data->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление изображения?')">Удалить</a>
                @endif
            </div>
            <div>
                <a href="/gallery/{{ $data->id }}">{!! resizeImage('uploads/pictures/', $data->link, ['alt' => $data->title]) !!}</a><br>

                @if ($data->text)
                   {!! bbCode($data->text) !!}<br>
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <a href="/gallery/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего фотографий: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Фотографий в альбоме еще нет!') !!}
    @endif
@stop
